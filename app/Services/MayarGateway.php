<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MayarGateway
{
    public function createCheckoutForInvoice(int $invoiceId): bool
    {
        if (! Schema::hasTable('invoice_subscriptions')) {
            return false;
        }

        $invoice = DB::table('invoice_subscriptions')
            ->join('tenants', 'invoice_subscriptions.tenant_id', '=', 'tenants.id')
            ->leftJoin('subscriptions', 'invoice_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('invoice_subscriptions.id', $invoiceId)
            ->select(
                'invoice_subscriptions.*',
                'tenants.name as tenant_name',
                'tenants.email as tenant_email',
                'tenants.phone as tenant_phone',
                'tenants.mayar_customer_id',
                'plans.slug as plan_slug',
                'plans.name as plan_name',
            )
            ->first();

        if (! $invoice) {
            return false;
        }

        $this->stampInvoice($invoiceId, [
            'payment_gateway' => 'Mayar',
            'gateway_status' => 'creating_link',
        ]);

        if (! $this->isConfigured()) {
            $this->markCheckoutError($invoiceId, [
                'message' => 'Mayar API key is not configured.',
                'code' => 'mayar_not_configured',
            ]);

            return false;
        }

        $payload = $this->checkoutPayload($invoice);

        try {
            $response = Http::withToken((string) config('mayar.api_key'))
                ->acceptJson()
                ->asJson()
                ->timeout((int) config('mayar.timeout', 15))
                ->post($this->endpoint(), $payload);
        } catch (\Throwable $exception) {
            Log::error('Mayar checkout exception', [
                'invoice_id' => $invoiceId,
                'error' => $exception->getMessage(),
            ]);

            $this->markCheckoutError($invoiceId, [
                'message' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }

        $body = $response->json();
        $body = is_array($body) ? $body : ['raw' => $response->body()];

        $statusCode = (int) ($body['statusCode'] ?? $response->status());
        $message = (string) ($body['messages'] ?? $body['message'] ?? 'Mayar request failed.');

        if (! $response->successful() || $statusCode >= 400) {
            Log::warning('Mayar checkout failed', [
                'invoice_id' => $invoiceId,
                'status' => $response->status(),
                'message' => $message,
            ]);

            $this->markCheckoutError($invoiceId, [
                'status' => $response->status(),
                'status_code' => $statusCode,
                'message' => $message,
                'payload' => $payload,
            ]);

            return false;
        }

        $checkoutUrl = $this->extractString($body, ['data.link']);
        $reference = $this->extractString($body, ['data.transactionId']);

        if ($statusCode >= 400 || $checkoutUrl === '' || $reference === '') {
            $this->markCheckoutError($invoiceId, [
                'message' => 'Mayar response did not include the required checkout data.',
                'status_code' => $statusCode,
                'response' => [
                    'statusCode' => $statusCode,
                    'messages' => $message,
                    'data' => [
                        'link' => $checkoutUrl,
                        'transactionId' => $reference,
                    ],
                ],
            ]);

            return false;
        }

        $this->stampInvoice($invoiceId, [
            'payment_gateway' => 'Mayar',
            'gateway_reference' => $reference,
            'gateway_checkout_url' => $checkoutUrl,
            'gateway_status' => 'pending',
            'gateway_payload' => $this->json([
                'request' => $payload,
                'response' => [
                    'statusCode' => $statusCode,
                    'messages' => $message,
                    'data' => $body['data'] ?? [],
                ],
            ]),
        ]);

        $customerId = $this->extractString($body, [
            'data.customer.id',
            'data.customerId',
            'customer.id',
            'customerId',
        ]);

        if ($customerId !== '' && Schema::hasColumn('tenants', 'mayar_customer_id')) {
            DB::table('tenants')
                ->where('id', (int) $invoice->tenant_id)
                ->where(function ($query): void {
                    $query->whereNull('mayar_customer_id')->orWhere('mayar_customer_id', '');
                })
                ->update([
                    'mayar_customer_id' => $customerId,
                    'updated_at' => now(),
                ]);
        }

        return true;
    }

    /**
     * @return array{status: string, message: string, invoice_id?: int}
     */
    public function handleWebhook(array $payload): array
    {
        $event = $this->parseWebhookPayload($payload);
        $eventId = $event['event_id'] ?: hash('sha256', $this->json($payload).($event['event_type'] ?? ''));

        $eventRowId = $this->recordWebhookEvent($eventId, $event, $payload);
        if ($eventRowId === 0) {
            return [
                'status' => 'duplicate',
                'message' => 'Webhook already processed.',
            ];
        }

        try {
            $invoice = $this->findInvoiceForWebhook($event);
            if (! $invoice) {
                $this->finishWebhookEvent($eventRowId, 'ignored', 'Invoice reference not found.');

                return [
                    'status' => 'ignored',
                    'message' => 'Invoice reference not found.',
                ];
            }

            if (in_array((string) $invoice->status, ['canceled', 'failed', 'refunded'], true)) {
                $this->finishWebhookEvent($eventRowId, 'ignored', 'Invoice is no longer payable.');

                return [
                    'status' => 'ignored',
                    'message' => 'Invoice is no longer payable.',
                    'invoice_id' => (int) $invoice->id,
                ];
            }

            if ($this->isPaidStatus($event['status'], $event['event_type'])) {
                if ($event['transaction_id'] === '') {
                    $this->finishWebhookEvent($eventRowId, 'ignored', 'Transaction ID is required.');

                    return [
                        'status' => 'ignored',
                        'message' => 'Transaction ID is required before payment verification.',
                        'invoice_id' => (int) $invoice->id,
                    ];
                }

                $verification = $this->verifyPaidTransaction($event['transaction_id']);
                if (! $verification['paid']) {
                    $this->finishWebhookEvent($eventRowId, 'ignored', 'Transaction is not paid.');

                    return [
                        'status' => 'ignored',
                        'message' => 'Transaction is not paid.',
                        'invoice_id' => (int) $invoice->id,
                    ];
                }

                $claim = $this->claimFulfillment($event['transaction_id'], (int) $invoice->id);
                if ($claim === 'completed' || $claim === 'busy') {
                    $this->finishWebhookEvent($eventRowId, 'duplicate_paid');

                    return [
                        'status' => 'duplicate',
                        'message' => $claim === 'busy'
                            ? 'Payment fulfillment is already processing.'
                            : 'Payment fulfillment already completed.',
                        'invoice_id' => (int) $invoice->id,
                    ];
                }

                if (! PaymentGateway::markInvoicePaid((int) $invoice->id, 'Mayar')) {
                    $this->finishWebhookEvent($eventRowId, 'ignored', 'Invoice is no longer payable.');

                    return [
                        'status' => 'ignored',
                        'message' => 'Invoice is no longer payable.',
                        'invoice_id' => (int) $invoice->id,
                    ];
                }

                $this->stampInvoice((int) $invoice->id, [
                    'payment_gateway' => 'Mayar',
                    'gateway_status' => 'paid',
                    'gateway_paid_at' => now(),
                    'gateway_payload' => $this->json($this->safeWebhookPayload($payload)),
                ]);
                $this->completeFulfillment($event['transaction_id']);
                $this->finishWebhookEvent($eventRowId, 'processed');

                return [
                    'status' => 'ok',
                    'message' => 'Invoice marked paid.',
                    'invoice_id' => (int) $invoice->id,
                ];
            }

            if ($this->isFailedStatus($event['status'], $event['event_type'])) {
                $this->stampInvoice((int) $invoice->id, [
                    'payment_gateway' => 'Mayar',
                    'gateway_status' => $event['status'] ?: 'failed',
                    'gateway_payload' => $this->json($this->safeWebhookPayload($payload)),
                ]);
                $this->finishWebhookEvent($eventRowId, 'processed');

                return [
                    'status' => 'ok',
                    'message' => 'Gateway status updated.',
                    'invoice_id' => (int) $invoice->id,
                ];
            }

            $this->stampInvoice((int) $invoice->id, [
                'payment_gateway' => 'Mayar',
                'gateway_status' => $event['status'] ?: $event['event_type'] ?: 'received',
                'gateway_payload' => $this->json($this->safeWebhookPayload($payload)),
            ]);
            $this->finishWebhookEvent($eventRowId, 'received');

            return [
                'status' => 'received',
                'message' => 'Webhook received.',
                'invoice_id' => (int) $invoice->id,
            ];
        } catch (\Throwable $exception) {
            if ($event['transaction_id'] !== '') {
                $this->failFulfillment($event['transaction_id'], $exception->getMessage());
            }
            $this->finishWebhookEvent($eventRowId, 'failed', $exception->getMessage());
            Log::error('Mayar webhook failed', [
                'error' => $exception->getMessage(),
                'event' => $event,
            ]);

            return [
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function isConfigured(): bool
    {
        return (bool) config('mayar.enabled', false)
            && trim((string) config('mayar.api_key')) !== '';
    }

    private function checkoutPayload(object $invoice): array
    {
        $email = trim((string) ($invoice->tenant_email ?? ''));
        if ($email === '') {
            $email = 'billing+'.((int) $invoice->tenant_id).'@optibus.app';
        }

        $phone = preg_replace('/\D+/', '', (string) ($invoice->tenant_phone ?? ''));
        if ($phone === '') {
            $phone = '080000000000';
        }

        $dueDate = $invoice->due_date
            ? CarbonImmutable::parse((string) $invoice->due_date)->endOfDay()
            : now()->addDay()->endOfDay();

        $invoiceNumber = (string) ($invoice->invoice_number ?? '');
        $planSlug = (string) ($invoice->plan_slug ?? '');
        $planName = (string) ($invoice->plan_name ?? 'Subscription');
        $description = trim(sprintf(
            'OptiBus %s - %s',
            $planName !== '' ? $planName : 'Subscription',
            $invoiceNumber !== '' ? $invoiceNumber : 'invoice',
        ));

        return [
            'name' => (string) ($invoice->tenant_name ?? 'Tenant OptiBus'),
            'email' => $email,
            'mobile' => $phone,
            'description' => $description,
            'expiredAt' => $dueDate->toISOString(),
            'items' => [[
                'quantity' => 1,
                'rate' => (int) round((float) $invoice->amount),
                'description' => $description,
            ]],
            'extraData' => [
                'noCustomer' => $invoiceNumber !== '' ? $invoiceNumber : 'optibus-'.(int) $invoice->tenant_id,
                'idProd' => $planSlug !== '' ? $planSlug : $planName,
            ],
        ];
    }

    private function endpoint(): string
    {
        return $this->apiBaseUrl().'/'.ltrim((string) config('mayar.payment_create_path', '/invoices/create'), '/');
    }

    private function markCheckoutError(int $invoiceId, array $payload): void
    {
        $this->stampInvoice($invoiceId, [
            'payment_gateway' => 'Mayar',
            'gateway_status' => 'payment_link_error',
            'gateway_payload' => $this->json($payload),
        ]);
    }

    private function stampInvoice(int $invoiceId, array $values): void
    {
        $values['updated_at'] = now();

        DB::table('invoice_subscriptions')
            ->where('id', $invoiceId)
            ->update($values);
    }

    /**
     * @return array{event_id: string, event_type: string, reference: string, transaction_id: string, invoice_id: int, status: string}
     */
    private function parseWebhookPayload(array $payload): array
    {
        $eventType = $this->extractString($payload, [
            'event',
            'type',
            'event_type',
            'event.received',
            'status',
            'data.event',
            'data.event.received',
            'data.type',
            'data.eventType',
            'data.event_type',
            'data.status',
            'data.paymentStatus',
            'data.payment_status',
            'transaction_status',
        ]);

        $statusValue = $this->extractValue($payload, [
            'status',
            'payment_status',
            'transaction_status',
            'data.transactionStatus',
            'data.transaction_status',
            'data.status',
            'data.paymentStatus',
            'data.payment_status',
            'data.transaction.status',
        ]);

        $transactionId = $this->extractString($payload, [
            'data.transactionId',
            'data.transaction_id',
            'transactionId',
            'transaction_id',
            'paymentLinkTransactionId',
            'data.paymentLinkTransactionId',
        ]);

        $invoiceId = (int) ($this->extractString($payload, [
            'metadata.invoice_id',
            'data.metadata.invoice_id',
            'payment.metadata.invoice_id',
            'data.payment.metadata.invoice_id',
            'extraData.invoice_id',
            'data.extraData.invoice_id',
            'data.extra_data.invoice_id',
            'extra_data.invoice_id',
        ]) ?: 0);

        $reference = $this->extractString($payload, [
            'metadata.invoice_number',
            'data.metadata.invoice_number',
            'payment.metadata.invoice_number',
            'data.payment.metadata.invoice_number',
            'extraData.invoice_number',
            'data.extraData.invoice_number',
            'external_id',
            'externalId',
            'order_id',
            'invoice_number',
            'data.external_id',
            'data.externalId',
            'data.order_id',
            'data.invoice_number',
            'data.transactionId',
            'data.transaction_id',
            'transactionId',
            'transaction_id',
            'reference',
            'data.reference',
            'id',
            'data.id',
            'paymentId',
            'data.paymentId',
            'data.link',
            'link',
        ]);

        return [
            'event_id' => $this->extractString($payload, [
                'event_id',
                'eventId',
                'id',
                'data.event_id',
                'data.eventId',
                'data.id',
                'history_id',
            ]),
            'event_type' => Str::lower($eventType),
            'reference' => $reference,
            'transaction_id' => $transactionId,
            'invoice_id' => $invoiceId,
            'status' => $this->normalizeWebhookStatus($statusValue, $eventType),
        ];
    }

    private function recordWebhookEvent(string $eventId, array $event, array $payload): int
    {
        if (! Schema::hasTable('payment_webhook_events')) {
            throw new \RuntimeException('Webhook audit table is not available.');
        }

        $existing = DB::table('payment_webhook_events')
            ->where('gateway', 'mayar')
            ->where('event_id', $eventId)
            ->first();

        if ($existing && $existing->processed_at !== null) {
            return 0;
        }

        $now = now();
        $leaseUntil = $now->copy()->addMinutes(5);
        if ($existing) {
            if (
                Schema::hasColumn('payment_webhook_events', 'locked_until')
                && $existing->locked_until !== null
                && CarbonImmutable::parse((string) $existing->locked_until)->isFuture()
            ) {
                return 0;
            }

            $updates = [
                'status' => 'processing',
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('payment_webhook_events', 'attempt_count')) {
                $updates['attempt_count'] = ((int) ($existing->attempt_count ?? 0)) + 1;
            }
            if (Schema::hasColumn('payment_webhook_events', 'locked_until')) {
                $updates['locked_until'] = $leaseUntil;
            }

            DB::table('payment_webhook_events')->where('id', $existing->id)->update($updates);

            return (int) $existing->id;
        }

        try {
            $values = [
                'gateway' => 'mayar',
                'event_id' => $eventId,
                'reference' => $event['reference'] ?: null,
                'event_type' => $event['event_type'] ?: null,
                'payload' => $this->json($this->safeWebhookPayload($payload)),
                'status' => 'processing',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('payment_webhook_events', 'transaction_id')) {
                $values['transaction_id'] = $event['transaction_id'] ?: null;
            }
            if (Schema::hasColumn('payment_webhook_events', 'attempt_count')) {
                $values['attempt_count'] = 1;
            }
            if (Schema::hasColumn('payment_webhook_events', 'locked_until')) {
                $values['locked_until'] = $leaseUntil;
            }

            return (int) DB::table('payment_webhook_events')->insertGetId($values);
        } catch (QueryException) {
            return 0;
        }
    }

    private function finishWebhookEvent(int $eventRowId, string $status, ?string $errorMessage = null): void
    {
        if ($eventRowId <= 0 || ! Schema::hasTable('payment_webhook_events')) {
            return;
        }

        $updates = [
            'status' => $status,
            'error_message' => $errorMessage,
            'processed_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('payment_webhook_events', 'locked_until')) {
            $updates['locked_until'] = null;
        }

        DB::table('payment_webhook_events')->where('id', $eventRowId)->update($updates);
    }

    private function findInvoiceForWebhook(array $event): ?object
    {
        if (! Schema::hasTable('invoice_subscriptions')) {
            return null;
        }

        if ($event['transaction_id'] !== '') {
            $invoice = DB::table('invoice_subscriptions')
                ->where('gateway_reference', $event['transaction_id'])
                ->first();
            if ($invoice) {
                return $invoice;
            }
        }

        if ($event['invoice_id'] <= 0 || $event['transaction_id'] === '') {
            return null;
        }

        $invoice = DB::table('invoice_subscriptions')
            ->where('id', $event['invoice_id'])
            ->first();

        if (! $invoice || ($invoice->gateway_reference ?? '') !== $event['transaction_id']) {
            return null;
        }

        return $invoice;
    }

    private function verifyPaidTransaction(string $transactionId): array
    {
        $response = Http::withToken((string) config('mayar.api_key'))
            ->acceptJson()
            ->timeout((int) config('mayar.timeout', 15))
            ->get($this->apiBaseUrl().'/transactions/'.rawurlencode($transactionId));
        $body = $response->json();
        $body = is_array($body) ? $body : [];
        $statusCode = (int) ($body['statusCode'] ?? $response->status());
        $message = (string) ($body['messages'] ?? $body['message'] ?? 'Mayar transaction verification failed.');

        if (! $response->successful() || $statusCode >= 400) {
            throw new \RuntimeException($message);
        }

        $data = is_array($body['data'] ?? null) ? $body['data'] : [];
        $verifiedId = $this->extractString($data, ['id']);
        $status = Str::lower($this->extractString($data, ['status']));

        return [
            'paid' => $verifiedId === $transactionId && $status === 'paid',
            'status' => $status,
        ];
    }

    private function claimFulfillment(string $transactionId, int $invoiceId): string
    {
        if (! Schema::hasTable('mayar_fulfillments')) {
            throw new \RuntimeException('Mayar fulfillment table is not available.');
        }

        return DB::transaction(function () use ($transactionId, $invoiceId): string {
            $now = now();
            $leaseUntil = $now->copy()->addMinutes(5);
            $row = DB::table('mayar_fulfillments')
                ->where('transaction_id', $transactionId)
                ->lockForUpdate()
                ->first();

            if ($row?->status === 'completed') {
                return 'completed';
            }

            if (
                $row?->status === 'processing'
                && $row->locked_until !== null
                && CarbonImmutable::parse((string) $row->locked_until)->isFuture()
            ) {
                return 'busy';
            }

            if ($row) {
                DB::table('mayar_fulfillments')->where('id', $row->id)->update([
                    'invoice_id' => $invoiceId,
                    'status' => 'processing',
                    'attempt_count' => ((int) $row->attempt_count) + 1,
                    'last_error' => null,
                    'locked_until' => $leaseUntil,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('mayar_fulfillments')->insert([
                    'transaction_id' => $transactionId,
                    'invoice_id' => $invoiceId,
                    'status' => 'processing',
                    'attempt_count' => 1,
                    'locked_until' => $leaseUntil,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return 'claimed';
        });
    }

    private function completeFulfillment(string $transactionId): void
    {
        DB::table('mayar_fulfillments')
            ->where('transaction_id', $transactionId)
            ->update([
                'status' => 'completed',
                'last_error' => null,
                'locked_until' => null,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function failFulfillment(string $transactionId, string $message): void
    {
        if (! Schema::hasTable('mayar_fulfillments')) {
            return;
        }

        DB::table('mayar_fulfillments')
            ->where('transaction_id', $transactionId)
            ->where('status', 'processing')
            ->update([
                'status' => 'failed',
                'last_error' => Str::limit($message, 2000),
                'locked_until' => null,
                'updated_at' => now(),
            ]);
    }

    private function apiBaseUrl(): string
    {
        $configuredUrl = rtrim((string) config('mayar.api_url'), '/');
        if ($configuredUrl !== '') {
            return $configuredUrl;
        }

        return (string) config('mayar.environment', 'sandbox') === 'production'
            ? 'https://api.mayar.id/hl/v2'
            : 'https://api.mayar.io/hl/v2';
    }

    private function safeWebhookPayload(array $payload): array
    {
        $sensitiveKeys = [
            'email',
            'mobile',
            'customerEmail',
            'customerMobile',
            'merchantEmail',
            'paymentUrl',
        ];

        $redact = function (mixed $value) use (&$redact, $sensitiveKeys): mixed {
            if (! is_array($value)) {
                return $value;
            }

            $result = [];
            foreach ($value as $key => $child) {
                $result[$key] = in_array((string) $key, $sensitiveKeys, true)
                    ? '[redacted]'
                    : $redact($child);
            }

            return $result;
        };

        return $redact($payload);
    }

    private function isPaidStatus(string $status, string $eventType): bool
    {
        $needle = Str::lower($status.' '.$eventType);

        return Str::contains($needle, ['paid', 'settlement', 'success', 'completed', 'capture', 'received']);
    }

    private function isFailedStatus(string $status, string $eventType): bool
    {
        $needle = Str::lower($status.' '.$eventType);

        return Str::contains($needle, [
            'failed',
            'failure',
            'expired',
            'expire',
            'cancel',
            'denied',
            'deny',
            'rejected',
            'void',
            'chargeback',
        ]);
    }

    private function extractString(array $payload, array $paths): string
    {
        foreach ($paths as $path) {
            $value = Arr::get($payload, $path);
            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function extractValue(array $payload, array $paths): mixed
    {
        foreach ($paths as $path) {
            if (! Arr::has($payload, $path)) {
                continue;
            }

            return Arr::get($payload, $path);
        }

        return null;
    }

    private function normalizeWebhookStatus(mixed $rawStatus, string $eventType): string
    {
        $eventType = Str::lower($eventType);

        if ($eventType !== '' && Str::contains($eventType, 'received')) {
            return 'paid';
        }

        if (is_bool($rawStatus)) {
            return $rawStatus ? 'paid' : 'received';
        }

        $status = Str::lower(trim((string) $rawStatus));

        if ($status === '') {
            return $eventType !== '' ? $eventType : 'received';
        }

        if (in_array($status, ['true', '1', 'yes'], true)) {
            return 'paid';
        }

        if (in_array($status, ['false', '0', 'no'], true)) {
            return 'received';
        }

        return $status;
    }

    private function json(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }
}
