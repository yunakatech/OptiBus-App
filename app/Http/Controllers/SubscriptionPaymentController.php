<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateway;
use App\Support\PoolScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPaymentController extends Controller
{
    /**
     * Show the tenant's subscription + payment page.
     * GET /subscription
     */
    public function index(): Response
    {
        $tenantSub = PoolScope::tenantSubscription();
        $invoices = [];
        $currentPlan = null;

        if ($tenantSub && Schema::hasTable('invoice_subscriptions')) {
            $invoices = DB::table('invoice_subscriptions')
                ->where('tenant_id', $tenantSub['tenant_id'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($inv) {
                    return [
                        'id' => (int) $inv->id,
                        'invoice_number' => (string) $inv->invoice_number,
                        'amount' => (float) $inv->amount,
                        'status' => (string) $inv->status,
                        'due_date' => $inv->due_date,
                        'paid_at' => $inv->paid_at,
                        'payment_method' => (string) ($inv->payment_method ?? ''),
                        'payment_proof' => $inv->payment_proof ?? null,
                        'created_at' => $inv->created_at,
                    ];
                })
                ->all();

            if (Schema::hasTable('plans')) {
                $currentPlan = DB::table('plans')->where('id', $tenantSub['plan_id'])->first();
            }
        }

        $plans = Schema::hasTable('plans')
            ? DB::table('plans')->where('is_active', true)->orderBy('sort_order')->get()
            : collect();

        // Manual payment config
        $paymentConfig = [
            'qris' => [
                'enabled' => config('payment.qris.enabled', true),
                'merchant_name' => config('payment.qris.merchant_name', 'Qbus Indonesia'),
                'image_url' => asset(config('payment.qris.image_path', 'images/qris.png')),
                'note' => config('payment.qris.note', ''),
            ],
            'bank_transfer' => [
                'enabled' => config('payment.bank_transfer.enabled', true),
                'accounts' => collect(config('payment.bank_transfer.accounts', []))
                    ->filter(fn ($acc) => ! empty($acc['account_number']) && ! empty($acc['bank_name']))
                    ->values()
                    ->all(),
            ],
            'upload_max_kb' => (int) config('payment.upload.max_size_kb', 2048),
        ];

        return Inertia::render('Subscription', [
            'tenant_subscription' => $tenantSub,
            'invoices' => $invoices,
            'current_plan' => $currentPlan ? [
                'id' => (int) $currentPlan->id,
                'name' => (string) $currentPlan->name,
                'slug' => (string) $currentPlan->slug,
                'price_monthly' => (float) $currentPlan->price_monthly,
                'price_yearly' => (float) $currentPlan->price_yearly,
                'description' => (string) ($currentPlan->description ?? ''),
            ] : null,
            'plans' => $plans->map(fn ($p) => [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'slug' => (string) $p->slug,
                'price_monthly' => (float) $p->price_monthly,
                'price_yearly' => (float) $p->price_yearly,
                'description' => (string) ($p->description ?? ''),
            ])->all(),
            'payment_config' => $paymentConfig,
        ]);
    }

    /**
     * Upload bukti pembayaran untuk invoice.
     * POST /api/subscription/upload-proof/{invoiceId}
     */
    public function uploadProof(int $invoiceId, Request $request): \Illuminate\Http\JsonResponse
    {
        $maxKb = (int) config('payment.upload.max_size_kb', 2048);

        $request->validate([
            'proof_file' => ['required', 'file', 'max:'.$maxKb, 'mimes:jpg,jpeg,png,pdf'],
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        $invoice = DB::table('invoice_subscriptions')->where('id', $invoiceId)->first();
        if (! $invoice) {
            return response()->json(['success' => false, 'error' => 'Invoice tidak ditemukan.'], 404);
        }

        // Verify tenant owns this invoice
        $tenantSub = PoolScope::tenantSubscription();
        if (! $tenantSub || $tenantSub['tenant_id'] !== (int) $invoice->tenant_id) {
            return response()->json(['success' => false, 'error' => 'Akses ditolak.'], 403);
        }

        // Store the proof file
        $file = $request->file('proof_file');
        $path = $file->storeAs(
            'payment-proofs',
            'inv-'.$invoiceId.'-'.time().'.'.$file->getClientOriginalExtension(),
            'public',
        );

        // Update invoice with proof
        DB::table('invoice_subscriptions')->where('id', $invoiceId)->update([
            'payment_proof' => $path,
            'payment_method' => trim((string) $request->input('payment_method')),
            'status' => 'pending', // waiting for admin verification
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.',
            'proof_url' => Storage::disk('public')->url($path),
        ]);
    }
}
