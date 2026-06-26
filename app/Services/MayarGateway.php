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

        if (! $response->successful()) {
            Log::warning('Mayar checkout failed', [
                'invoice_id' => $invoiceId,
                'status' => $response->status(),
                'body' => $body,
            ]);

            $this->markCheckoutError($invoiceId, [
                'status' => $response->status(),
                'response' => $body,
                'payload' => $payload,
            ]);

            return false;
        }

        $checkoutUrl = $this->extractCheckoutUrl($body);
        $reference = $this->extractReference($body) ?: (string) $invoice->invoice_number;

        if ($checkoutUrl === '') {
            $this->markCheckoutError($invoiceId, [
                'message' => 'Mayar response did not include a checkout URL.',
                'response' => $body,
                'payload' => $payload,
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
                'response' => $body,
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

            if ($event['reference'] !== '') {
                $this->stampInvoice((int) $invoice->id, [
                    'payment_gateway' => 'Mayar',
                    'gateway_reference' => $event['reference'],
                ]);
            }

            if ($this->isPaidStatus($event['status'], $event['event_type'])) {
                if ((string) $invoice->status === 'paid') {
                    $this->stampInvoice((int) $invoice->id, [
                        'gateway_status' => 'paid',
                        'gateway_paid_at' => $invoice->gateway_paid_at ?? now(),
                        'gateway_payload' => $this->json($payload),
                    ]);
                    $this->finishWebhookEvent($eventRowId, 'duplicate_paid');

                    return [
                        'status' => 'duplicate',
                        'message' => 'Invoice already paid.',
                        'invoice_id' => (int) $invoice->id,
                    ];
                }

                PaymentGateway::markInvoicePaid((int) $invoice->id, 'Mayar');
                $this->stampInvoice((int) $invoice->id, [
                    'payment_gateway' => 'Mayar',
                    'gateway_status' => 'paid',
                    'gateway_paid_at' => now(),
                    'gateway_payload' => $this->json($payload),
                ]);
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
                    'gateway_payload' => $this->json($payload),
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
                'gateway_payload' => $this->json($payload),
            ]);
            $this->finishWebhookEvent($eventRowId, 'received');

            return [
                'status' => 'received',
                'message' => 'Webhook received.',
                'invoice_id' => (int) $invoice->id,
            ];
        } catch (\Throwable $exception) {
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
            $email = 'billing+'.((int) $invoice->tenant_id).'@optibus.local';
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
            'amount' => (int) round((float) $invoice->amount),
            'mobile' => $phone,
            'redirectUrl' => route('subscription.index', absolute: true),
            'redirectURL' => route('subscription.index', absolute: true),
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
                'invoice_id' => (int) $invoice->id,
                'invoice_number' => $invoiceNumber,
                'tenant_id' => (int) $invoice->tenant_id,
                'subscription_id' => (int) $invoice->subscription_id,
            ],
            'metadata' => [
                'invoice_id' => (int) $invoice->id,
                'invoice_number' => (string) $invoice->invoice_number,
                'tenant_id' => (int) $invoice->tenant_id,
                'subscription_id' => (int) $invoice->subscription_id,
            ],
        ];
    }

    private function endpoint(): string
    {
        return rtrim((string) config('mayar.api_url', 'https://api.mayar.id'), '/')
            .'/'.ltrim((string) config('mayar.payment_create_path', '/hl/v1/payment/create'), '/');
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
     * @return array{event_id: string, event_type: string, reference: string, invoice_id: int, status: string}
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
            'data.status',
            'data.paymentStatus',
            'data.payment_status',
            'data.transaction.status',
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
            'invoice_id' => $invoiceId,
            'status' => $this->normalizeWebhookStatus($statusValue, $eventType),
        ];
    }

    private function recordWebhookEvent(string $eventId, array $event, array $payload): int
    {
        if (! Schema::hasTable('payment_webhook_events')) {
            return 1;
        }

        $existing = DB::table('payment_webhook_events')
            ->where('gateway', 'mayar')
            ->where('event_id', $eventId)
            ->first();

        if ($existing && $existing->processed_at !== null) {
            return 0;
        }

        try {
            return (int) DB::table('payment_webhook_events')->insertGetId([
                'gateway' => 'mayar',
                'event_id' => $eventId,
                'reference' => $event['reference'] ?: null,
                'event_type' => $event['event_type'] ?: null,
                'payload' => $this->json($payload),
                'status' => 'received',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException) {
            return 0;
        }
    }

    private function finishWebhookEvent(int $eventRowId, string $status, ?string $errorMessage = null): void
    {
        if ($eventRowId <= 0 || ! Schema::hasTable('payment_webhook_events')) {
            return;
        }

        DB::table('payment_webhook_events')
            ->where('id', $eventRowId)
            ->update([
                'status' => $status,
                'error_message' => $errorMessage,
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function findInvoiceForWebhook(array $event): ?object
    {
        if (! Schema::hasTable('invoice_subscriptions')) {
            return null;
        }

        if ($event['invoice_id'] > 0) {
            $invoice = DB::table('invoice_subscriptions')->where('id', $event['invoice_id'])->first();
            if ($invoice) {
                return $invoice;
            }
        }

        if ($event['reference'] === '') {
            return null;
        }

        return DB::table('invoice_subscriptions')
            ->where('gateway_reference', $event['reference'])
            ->orWhere('invoice_number', $event['reference'])
            ->first();
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

    private function extractCheckoutUrl(array $body): string
    {
        return $this->extractString($body, [
            'data.linkPayment',
            'data.link_payment',
            'data.checkoutUrl',
            'data.checkout_url',
            'data.paymentUrl',
            'data.payment_url',
            'data.url',
            'linkPayment',
            'link_payment',
            'checkoutUrl',
            'checkout_url',
            'paymentUrl',
            'payment_url',
            'url',
        ]);
    }

    private function extractReference(array $body): string
    {
        return $this->extractString($body, [
            'data.id',
            'data.paymentId',
            'data.reference',
            'id',
            'paymentId',
            'reference',
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
     * @return mixed
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

    /**
     * @param  mixed  $rawStatus
     */
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
