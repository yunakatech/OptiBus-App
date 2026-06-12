<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateway;
use App\Support\PaymentSettings;
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

        $this->syncPendingPaymentInvoice($tenantSub);

        if ($tenantSub && Schema::hasTable('invoice_subscriptions')) {
            $hasPaymentProofColumn = Schema::hasColumn('invoice_subscriptions', 'payment_proof');
            $hasDueDateColumn = Schema::hasColumn('invoice_subscriptions', 'due_date');
            $hasPaidAtColumn = Schema::hasColumn('invoice_subscriptions', 'paid_at');
            $invoices = DB::table('invoice_subscriptions')
                ->where('tenant_id', $tenantSub['tenant_id'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($inv) use ($hasPaymentProofColumn, $hasDueDateColumn, $hasPaidAtColumn) {
                    $paymentProof = $hasPaymentProofColumn ? ($inv->payment_proof ?? null) : null;

                    $status = (string) $inv->status;
                    if ($status === 'pending' && $paymentProof !== null && trim($paymentProof) !== '') {
                        $status = 'verification';
                    }

                    return [
                        'id' => (int) $inv->id,
                        'invoice_number' => (string) $inv->invoice_number,
                        'amount' => (float) $inv->amount,
                        'status' => $status,
                        'due_date' => $hasDueDateColumn ? ($inv->due_date ?? null) : null,
                        'paid_at' => $hasPaidAtColumn ? ($inv->paid_at ?? null) : null,
                        'payment_method' => (string) ($inv->payment_method ?? ''),
                        'payment_proof' => $paymentProof,
                        'payment_proof_url' => $this->paymentProofUrl($paymentProof),
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
            'payment_config' => PaymentSettings::all(),
            'account_access' => $this->accountAccess(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $tenantSub
     */
    private function syncPendingPaymentInvoice(?array $tenantSub): void
    {
        if (! $tenantSub || ($tenantSub['subscription_status'] ?? '') !== 'pending_payment') {
            return;
        }

        if (! Schema::hasTable('invoice_subscriptions') || ! Schema::hasTable('subscriptions') || ! Schema::hasTable('plans')) {
            return;
        }

        $subscriptionId = (int) ($tenantSub['subscription_id'] ?? 0);
        if ($subscriptionId <= 0) {
            return;
        }

        $hasActiveInvoice = DB::table('invoice_subscriptions')
            ->where('subscription_id', $subscriptionId)
            ->whereIn('status', ['pending', 'verification', 'overdue'])
            ->exists();
        if ($hasActiveInvoice) {
            return;
        }

        $subscription = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.id', $subscriptionId)
            ->select('subscriptions.*', 'plans.price_monthly', 'plans.price_yearly')
            ->first();
        if (! $subscription) {
            return;
        }

        $amount = ($subscription->billing_interval ?? 'monthly') === 'yearly'
            ? (float) $subscription->price_yearly
            : (float) $subscription->price_monthly;
        if ($amount <= 0) {
            return;
        }

        PaymentGateway::createInvoice(
            (int) $subscription->tenant_id,
            (int) $subscription->id,
            $amount,
            now()->addDay()->toDateString(),
        );
    }

    /**
     * @return array{tenant_id: int, pool_count: int, role_names: array<int, string>}
     */
    private function accountAccess(): array
    {
        $userId = (int) (auth()->id() ?? 0);
        $roleNames = [];
        if ($userId > 0 && Schema::hasTable('user_role') && Schema::hasTable('roles')) {
            $roleNames = DB::table('user_role')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->where('user_role.user_id', $userId)
                ->orderBy('roles.name')
                ->pluck('roles.name')
                ->map(static fn ($value) => (string) $value)
                ->values()
                ->all();
        }

        return [
            'tenant_id' => $userId > 0 ? PoolScope::tenantId($userId) : 0,
            'pool_count' => $userId > 0 ? count(PoolScope::userPoolIds($userId)) : 0,
            'role_names' => $roleNames,
        ];
    }

    private function paymentProofUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Upload bukti pembayaran untuk invoice.
     * POST /api/subscription/upload-proof/{invoiceId}
     */
    public function uploadProof(int $invoiceId, Request $request): \Illuminate\Http\JsonResponse
    {
        if (! Schema::hasTable('invoice_subscriptions') || ! Schema::hasColumn('invoice_subscriptions', 'payment_proof')) {
            return response()->json(['success' => false, 'error' => 'Kolom bukti pembayaran belum tersedia. Jalankan migrasi database.'], 409);
        }

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
        if ((string) $invoice->status === 'paid') {
            return response()->json(['success' => false, 'error' => 'Invoice sudah lunas.'], 422);
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
            'status' => 'verification',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.',
            'proof_url' => Storage::disk('public')->url($path),
        ]);
    }
}
