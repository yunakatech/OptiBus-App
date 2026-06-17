<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PaymentGateway;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_subscription_creates_invoice_and_mayar_checkout_url(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
        ]);

        Http::fake([
            'https://api.mayar.id/hl/v1/payment/create' => Http::response([
                'data' => [
                    'id' => 'pay_123',
                    'linkPayment' => 'https://mayar.test/pay/pay_123',
                    'status' => 'open',
                ],
            ]),
        ]);

        [$user, $tenantId, $subscriptionId] = $this->tenantWithPendingSubscription();

        $this->actingAs($user)
            ->get(route('subscription.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Subscription')
                ->where('tenant_subscription.tenant_id', $tenantId)
                ->where('tenant_subscription.subscription_id', $subscriptionId)
                ->where('account_access.tenant_id', $tenantId)
                ->where('invoices.0.payment_gateway', 'Mayar')
                ->where('invoices.0.gateway_checkout_url', 'https://mayar.test/pay/pay_123')
                ->missing('payment_config'));

        $this->assertDatabaseHas('invoice_subscriptions', [
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'status' => 'pending',
            'payment_gateway' => 'Mayar',
            'gateway_reference' => 'pay_123',
            'gateway_status' => 'pending',
            'gateway_checkout_url' => 'https://mayar.test/pay/pay_123',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.mayar.id/hl/v1/payment/create'
            && $request['metadata']['invoice_id'] > 0
            && $request['metadata']['tenant_id'] === $tenantId);
    }

    public function test_create_invoice_marks_payment_link_error_when_mayar_fails(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
        ]);

        Http::fake([
            'https://api.mayar.id/hl/v1/payment/create' => Http::response(['message' => 'Mayar unavailable'], 500),
        ]);

        [$user, $tenantId, $subscriptionId] = $this->tenantWithPendingSubscription();

        $invoiceId = PaymentGateway::createInvoice(
            $tenantId,
            $subscriptionId,
            99000,
            now()->addDay()->toDateString(),
        );

        $this->assertGreaterThan(0, $invoiceId);
        $this->assertDatabaseHas('invoice_subscriptions', [
            'id' => $invoiceId,
            'tenant_id' => $tenantId,
            'status' => 'pending',
            'payment_gateway' => 'Mayar',
            'gateway_status' => 'payment_link_error',
        ]);

        $this->actingAs($user)
            ->get(route('subscription.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('invoices.0.gateway_status', 'payment_link_error')
                ->where('invoices.0.gateway_checkout_url', ''));
    }

    public function test_mayar_paid_webhook_activates_pending_tenant_and_subscription(): void
    {
        [$user, $invoiceId, $tenantId, $subscriptionId] = $this->tenantWithPendingInvoice();

        $this->postJson(route('api.webhooks.mayar'), [
            'event_id' => 'evt_paid_1',
            'event' => 'payment.paid',
            'status' => 'paid',
            'data' => [
                'id' => 'pay_123',
                'metadata' => [
                    'invoice_id' => $invoiceId,
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('invoice_id', $invoiceId);

        $this->assertDatabaseHas('invoice_subscriptions', [
            'id' => $invoiceId,
            'status' => 'paid',
            'payment_method' => 'Mayar',
            'payment_gateway' => 'Mayar',
            'gateway_status' => 'paid',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscriptionId,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('payment_webhook_events', [
            'gateway' => 'mayar',
            'event_id' => 'evt_paid_1',
            'status' => 'processed',
        ]);
        $this->assertSame($tenantId, (int) DB::table('users')->where('id', $user->id)->value('tenant_id'));
    }

    public function test_mayar_duplicate_webhook_does_not_extend_subscription_twice(): void
    {
        [, $invoiceId, , $subscriptionId] = $this->tenantWithPendingInvoice();

        $payload = [
            'event_id' => 'evt_paid_duplicate',
            'event' => 'payment.paid',
            'status' => 'paid',
            'data' => [
                'id' => 'pay_123',
                'metadata' => ['invoice_id' => $invoiceId],
            ],
        ];

        $this->postJson(route('api.webhooks.mayar'), $payload)->assertOk();
        $endsAt = (string) DB::table('subscriptions')->where('id', $subscriptionId)->value('ends_at');

        $this->postJson(route('api.webhooks.mayar'), $payload)
            ->assertOk()
            ->assertJsonPath('status', 'duplicate');

        $this->assertSame($endsAt, (string) DB::table('subscriptions')->where('id', $subscriptionId)->value('ends_at'));
        $this->assertSame(1, DB::table('payment_webhook_events')->where('event_id', 'evt_paid_duplicate')->count());
    }

    public function test_mark_invoice_paid_is_internal_admin_correction_only(): void
    {
        [, $invoiceId, $tenantId, $subscriptionId] = $this->tenantWithPendingInvoice();
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->postJson(route('api.admin.invoices.mark-paid', ['id' => $invoiceId]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoice_subscriptions', [
            'id' => $invoiceId,
            'status' => 'paid',
            'payment_method' => 'Admin Correction',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscriptionId,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'status' => 'active',
        ]);
        $this->assertFalse(Route::has('api.subscription.upload-proof'));
    }

    /**
     * @return array{0: User, 1: int, 2: int}
     */
    private function tenantWithPendingSubscription(): array
    {
        $planId = (int) DB::table('plans')->where('slug', 'pro')->value('id');
        $tenantId = (int) DB::table('tenants')->insertGetId([
            'name' => 'Pending Travel',
            'slug' => 'pending-travel-'.uniqid(),
            'email' => 'pending@example.com',
            'status' => 'pending_payment',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'pending_payment',
            'starts_at' => null,
            'ends_at' => null,
            'billing_interval' => 'monthly',
            'grace_period_days' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        DB::table('users')->where('id', $user->id)->update(['tenant_id' => $tenantId]);

        return [$user->fresh(), $tenantId, $subscriptionId];
    }

    /**
     * @return array{0: User, 1: int, 2: int, 3: int}
     */
    private function tenantWithPendingInvoice(): array
    {
        [$user, $tenantId, $subscriptionId] = $this->tenantWithPendingSubscription();

        $invoiceId = (int) DB::table('invoice_subscriptions')->insertGetId([
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'amount' => 99000,
            'status' => 'pending',
            'due_date' => now()->addDay()->toDateString(),
            'payment_gateway' => 'Mayar',
            'gateway_reference' => 'pay_123',
            'gateway_checkout_url' => 'https://mayar.test/pay/pay_123',
            'gateway_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $invoiceId, $tenantId, $subscriptionId];
    }

    private function superAdmin(): User
    {
        AccessControl::syncDefaults();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $roleId = (int) DB::table('roles')->where('slug', 'super-admin')->value('id');
        DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }
}
