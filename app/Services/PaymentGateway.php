<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PaymentGateway
{
    /**
     * Generate invoice number: INV-YYYYMMDD-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $last = DB::table('invoice_subscriptions')
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->value('invoice_number');

        $seq = 1;
        if ($last) {
            $seq = (int) substr($last, -4) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a subscription invoice for a tenant.
     */
    public static function createInvoice(int $tenantId, int $subscriptionId, float $amount, string $dueDate): int
    {
        if (! Schema::hasTable('invoice_subscriptions')) {
            return 0;
        }

        return (int) DB::table('invoice_subscriptions')->insertGetId([
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'invoice_number' => self::generateInvoiceNumber(),
            'amount' => $amount,
            'status' => 'pending',
            'due_date' => $dueDate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Mark an invoice as paid and extend the subscription.
     */
    public static function markInvoicePaid(int $invoiceId, string $paymentMethod = 'Midtrans'): bool
    {
        if (! Schema::hasTable('invoice_subscriptions')) {
            return false;
        }

        $invoice = DB::table('invoice_subscriptions')->where('id', $invoiceId)->first();
        if (! $invoice || $invoice->status === 'paid') {
            return false;
        }

        DB::transaction(function () use ($invoiceId, $invoice, $paymentMethod): void {
            DB::table('invoice_subscriptions')->where('id', $invoiceId)->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'updated_at' => now(),
            ]);

            if ($invoice->subscription_id > 0 && Schema::hasTable('subscriptions')) {
                $sub = DB::table('subscriptions')->where('id', (int) $invoice->subscription_id)->first();
                if ($sub) {
                    $base = now()->toDateString();
                    if (! empty($sub->ends_at) && CarbonImmutable::parse((string) $sub->ends_at)->isFuture()) {
                        $base = (string) $sub->ends_at;
                    }

                    $newEndsAt = ($sub->billing_interval ?? 'monthly') === 'yearly'
                        ? CarbonImmutable::parse($base)->addYear()->toDateString()
                        : CarbonImmutable::parse($base)->addMonth()->toDateString();

                    DB::table('subscriptions')->where('id', $sub->id)->update([
                        'status' => 'active',
                        'starts_at' => $sub->starts_at ?: now()->toDateString(),
                        'ends_at' => $newEndsAt,
                        'updated_at' => now(),
                    ]);
                }
            }

            if (Schema::hasTable('tenants')) {
                DB::table('tenants')->where('id', (int) $invoice->tenant_id)->update([
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
            }
        });

        Log::info("Invoice #{$invoice->invoice_number} marked as paid via {$paymentMethod}");

        return true;
    }

    /**
     * Create a Midtrans Snap token for the payment.
     * Falls back to manual transfer if Midtrans is not configured.
     */
    public static function createSnapToken(int $invoiceId): ?array
    {
        $serverKey = config('midtrans.server_key');
        if ($serverKey === '' || $serverKey === null) {
            // Midtrans not configured — fallback to manual
            return [
                'mode' => 'manual',
                'message' => 'Silakan transfer ke rekening yang tertera.',
            ];
        }

        $invoice = DB::table('invoice_subscriptions')
            ->join('tenants', 'invoice_subscriptions.tenant_id', '=', 'tenants.id')
            ->where('invoice_subscriptions.id', $invoiceId)
            ->select('invoice_subscriptions.*', 'tenants.name as tenant_name', 'tenants.email as tenant_email')
            ->first();

        if (! $invoice) {
            return null;
        }

        $isProduction = (bool) config('midtrans.is_production', false);
        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $invoice->invoice_number,
                'gross_amount' => (int) $invoice->amount,
            ],
            'customer_details' => [
                'first_name' => $invoice->tenant_name,
                'email' => $invoice->tenant_email ?? '',
            ],
            'callbacks' => [
                'finish' => url('/subscription/payment/finish'),
                'error' => url('/subscription/payment/error'),
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->withOptions(['verify' => false])
                ->post($baseUrl, $payload);

            if ($response->successful()) {
                $body = $response->json();

                return [
                    'mode' => 'midtrans',
                    'token' => $body['token'] ?? '',
                    'redirect_url' => $body['redirect_url'] ?? '',
                ];
            }

            Log::error('Midtrans Snap error', ['response' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap exception: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Handle Midtrans webhook notification.
     */
    public static function handleWebhook(array $payload): bool
    {
        $serverKey = config('midtrans.server_key');
        if ($serverKey === '' || $serverKey === null) {
            Log::warning('Midtrans webhook received but not configured');

            return false;
        }

        $orderId = $payload['order_id'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';
        $signatureKey = $payload['signature_key'] ?? '';

        // Verify signature
        $expectedSignature = hash('sha512', $orderId.$payload['status_code'].$payload['gross_amount'].$serverKey);
        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans webhook signature mismatch', ['order_id' => $orderId]);

            return false;
        }

        $invoice = DB::table('invoice_subscriptions')->where('invoice_number', $orderId)->first();
        if (! $invoice) {
            Log::warning('Invoice not found for Midtrans order', ['order_id' => $orderId]);

            return false;
        }

        if (in_array($transactionStatus, ['capture', 'settlement'], true) && $fraudStatus === 'accept') {
            self::markInvoicePaid((int) $invoice->id, 'Midtrans');

            return true;
        }

        if (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            DB::table('invoice_subscriptions')->where('id', $invoice->id)->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);
        }

        return false;
    }
}
