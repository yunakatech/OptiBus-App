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

    public function test_trial_starter_shares_unlocked_billing_access(): void
    {
        [$user, $tenantId, $subscriptionId] = $this->tenantWithSubscription(
            planSlug: 'starter',
            tenantStatus: 'active',
            subscriptionStatus: 'trial',
            trialEndsAt: now()->addDays(14)->toDateString(),
            endsAt: now()->addDays(14)->toDateString(),
        );

        $this->actingAs($user)
            ->get(route('subscription.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('tenant_subscription.tenant_id', $tenantId)
                ->where('tenant_subscription.subscription_id', $subscriptionId)
                ->where('auth.billing_access.allowed', true)
                ->where('auth.billing_access.locked', false)
                ->where('auth.billing_access.reason', 'allowed')
                ->where('auth.billing_access.plan_slug', 'starter')
                ->where('auth.billing_access.is_trial', true));
    }

    public function test_expired_trial_locks_operational_routes_and_is_shared_to_frontend(): void
    {
        [$user] = $this->tenantWithSubscription(
            planSlug: 'starter',
            tenantStatus: 'active',
            subscriptionStatus: 'trial',
            trialEndsAt: now()->subDay()->toDateString(),
            endsAt: now()->subDay()->toDateString(),
        );

        $this->actingAs($user)
            ->getJson(route('api.bookings.routes-by-date'))
            ->assertStatus(402)
            ->assertJsonPath('billing_access.locked', true)
            ->assertJsonPath('billing_access.reason', 'trial_expired');

        $this->actingAs($user)
            ->get(route('subscription.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.billing_access.locked', true)
                ->where('auth.billing_access.reason', 'trial_expired'));
    }

    public function test_pending_payment_redirects_operational_html_to_subscription(): void
    {
        [$user] = $this->tenantWithPendingSubscription();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('subscription.index'));
    }

    public function test_pending_subscription_page_does_not_create_mayar_invoice(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
            'mayar.payment_create_path' => '/invoices/create',
        ]);

        Http::fake([
            'https://api.mayar.io/hl/v2/invoices/create' => Http::response([
                'statusCode' => 200,
                'data' => [
                    'id' => 'pay_123',
                    'transactionId' => 'txn_123',
                    'link' => 'https://mayar.test/pay/pay_123',
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
                ->where('invoices', [])
                ->missing('payment_config'));

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.mayar.id'));
        $this->assertDatabaseCount('invoice_subscriptions', 0);
    }

    public function test_pending_subscription_page_does_not_create_invoice_for_private_price(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
            'mayar.payment_create_path' => '/invoices/create',
        ]);

        Http::fake([
            'https://api.mayar.io/hl/v2/invoices/create' => Http::response([
                'statusCode' => 200,
                'data' => [
                    'id' => 'pay_private_123',
                    'transactionId' => 'txn_private_123',
                    'link' => 'https://mayar.test/pay/pay_private_123',
                    'status' => 'open',
                ],
            ]),
        ]);

        [$user, $tenantId, $subscriptionId] = $this->tenantWithPendingSubscription();
        DB::table('subscriptions')
            ->where('id', $subscriptionId)
            ->update([
                'custom_price_monthly' => 123456,
                'updated_at' => now(),
            ]);

        $this->actingAs($user)
            ->get(route('subscription.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Subscription')
                ->where('tenant_subscription.subscription_id', $subscriptionId)
                ->where('invoices', []));

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.mayar.id'));
        $this->assertDatabaseCount('invoice_subscriptions', 0);
    }

    public function test_create_invoice_marks_payment_link_error_when_mayar_fails(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
            'mayar.payment_create_path' => '/invoices/create',
        ]);

        Http::fake([
            'https://api.mayar.io/hl/v2/invoices/create' => Http::response(['statusCode' => 500, 'messages' => 'Mayar unavailable'], 500),
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
                ->where('invoices.0.gateway_checkout_url', '')
                ->where('invoices.0.gateway_error_message', 'Mayar unavailable'));
    }

    public function test_subscription_checkout_creates_mayar_invoice_for_selected_plan(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
            'mayar.payment_create_path' => '/invoices/create',
        ]);

        Http::fake([
            'https://api.mayar.io/hl/v2/invoices/create' => Http::response([
                'statusCode' => 200,
                'data' => [
                    'id' => 'pay_fleet_123',
                    'transactionId' => 'txn_fleet_123',
                    'link' => 'https://mayar.test/pay/pay_fleet_123',
                    'status' => 'open',
                ],
            ]),
        ]);

        [$user, $tenantId] = $this->tenantWithSubscription(
            planSlug: 'starter',
            tenantStatus: 'active',
            subscriptionStatus: 'trial',
            trialEndsAt: now()->subDay()->toDateString(),
            endsAt: now()->subDay()->toDateString(),
        );
        $fleetPlanId = (int) DB::table('plans')->where('slug', 'fleet')->value('id');

        $this->actingAs($user)
            ->post(route('subscription.checkout'), [
                'plan_slug' => 'fleet',
                'billing_interval' => 'monthly',
            ])
            ->assertRedirect('https://mayar.test/pay/pay_fleet_123');

        Http::assertSent(function ($request): bool {
            $payload = $request->data();

            return ! array_key_exists('redirectUrl', $payload)
                && ! array_key_exists('redirectURL', $payload)
                && ! array_key_exists('amount', $payload)
                && ! array_key_exists('metadata', $payload)
                && array_key_exists('extraData', $payload)
                && array_keys($payload['extraData']) === ['noCustomer', 'idProd'];
        });

        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenantId,
            'plan_id' => $fleetPlanId,
            'status' => 'pending_payment',
            'billing_interval' => 'monthly',
        ]);
        $this->assertDatabaseHas('invoice_subscriptions', [
            'tenant_id' => $tenantId,
            'payment_gateway' => 'Mayar',
            'gateway_reference' => 'txn_fleet_123',
            'gateway_checkout_url' => 'https://mayar.test/pay/pay_fleet_123',
        ]);
    }

    public function test_subscription_upgrade_keeps_existing_access_until_invoice_is_paid(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
            'mayar.payment_create_path' => '/invoices/create',
        ]);

        Http::fake([
            'https://api.mayar.io/hl/v2/invoices/create' => Http::response([
                'statusCode' => 200,
                'data' => [
                    'id' => 'pay_upgrade_123',
                    'transactionId' => 'txn_upgrade_123',
                    'link' => 'https://mayar.test/pay/pay_upgrade_123',
                    'status' => 'open',
                ],
            ]),
        ]);

        [$user, $tenantId, $starterSubscriptionId] = $this->tenantWithSubscription(
            planSlug: 'starter',
            tenantStatus: 'active',
            subscriptionStatus: 'active',
            trialEndsAt: null,
            endsAt: now()->addDays(14)->toDateString(),
        );
        $fleetPlanId = (int) DB::table('plans')->where('slug', 'fleet')->value('id');

        $this->actingAs($user)
            ->post(route('subscription.checkout'), [
                'plan_slug' => 'fleet',
                'billing_interval' => 'monthly',
            ])
            ->assertRedirect('https://mayar.test/pay/pay_upgrade_123');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $starterSubscriptionId,
            'tenant_id' => $tenantId,
            'status' => 'active',
        ]);

        $newSubscriptionId = (int) DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->where('plan_id', $fleetPlanId)
            ->value('id');

        $this->assertGreaterThan(0, $newSubscriptionId);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $newSubscriptionId,
            'tenant_id' => $tenantId,
            'plan_id' => $fleetPlanId,
            'status' => 'pending_payment',
            'billing_interval' => 'monthly',
        ]);

        $this->assertDatabaseHas('invoice_subscriptions', [
            'tenant_id' => $tenantId,
            'subscription_id' => $newSubscriptionId,
            'status' => 'pending',
            'payment_gateway' => 'Mayar',
            'gateway_reference' => 'txn_upgrade_123',
            'gateway_checkout_url' => 'https://mayar.test/pay/pay_upgrade_123',
        ]);
    }

    public function test_mayar_paid_webhook_activates_pending_tenant_and_subscription(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
            'mayar.payment_create_path' => '/invoices/create',
        ]);
        Http::fake([
            'https://api.mayar.io/hl/v2/transactions/txn_123' => Http::response([
                'statusCode' => 200,
                'messages' => 'success',
                'data' => [
                    'id' => 'txn_123',
                    'status' => 'paid',
                ],
            ]),
        ]);

        [$user, $invoiceId, $tenantId, $subscriptionId] = $this->tenantWithPendingInvoice();

        $this->postJson(route('api.webhooks.mayar'), [
            'event_id' => 'evt_paid_1',
            'event' => 'payment.received',
            'status' => 'SUCCESS',
            'data' => [
                'id' => 'pay_123',
                'transactionId' => 'txn_123',
                'status' => 'SUCCESS',
                'transactionStatus' => 'paid',
                'extraData' => [
                    'invoice_id' => $invoiceId,
                    'invoice_number' => 'INV-TEST-001',
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
        $this->assertDatabaseHas('mayar_fulfillments', [
            'transaction_id' => 'txn_123',
            'invoice_id' => $invoiceId,
            'status' => 'completed',
        ]);
        $this->assertSame($tenantId, (int) DB::table('users')->where('id', $user->id)->value('tenant_id'));
    }

    public function test_mayar_duplicate_webhook_does_not_extend_subscription_twice(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
        ]);
        Http::fake([
            'https://api.mayar.io/hl/v2/transactions/txn_123' => Http::response([
                'statusCode' => 200,
                'messages' => 'success',
                'data' => [
                    'id' => 'txn_123',
                    'status' => 'paid',
                ],
            ]),
        ]);

        [, $invoiceId, , $subscriptionId] = $this->tenantWithPendingInvoice();

        $payload = [
            'event_id' => 'evt_paid_duplicate',
            'event' => 'payment.received',
            'status' => 'SUCCESS',
            'data' => [
                'transactionId' => 'txn_123',
                'status' => 'SUCCESS',
                'transactionStatus' => 'paid',
                'extraData' => ['invoice_id' => $invoiceId],
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

    public function test_mayar_webhook_does_not_fulfill_when_transaction_is_not_paid(): void
    {
        config([
            'mayar.enabled' => true,
            'mayar.api_key' => 'test-mayar-key',
            'mayar.environment' => 'sandbox',
            'mayar.api_url' => 'https://api.mayar.io/hl/v2',
        ]);
        Http::fake([
            'https://api.mayar.io/hl/v2/transactions/txn_unpaid' => Http::response([
                'statusCode' => 200,
                'messages' => 'success',
                'data' => [
                    'id' => 'txn_unpaid',
                    'status' => 'unpaid',
                ],
            ]),
        ]);

        [, $invoiceId, , $subscriptionId] = $this->tenantWithPendingInvoice();

        $this->postJson(route('api.webhooks.mayar'), [
            'event_id' => 'evt_unpaid_1',
            'event' => 'payment.received',
            'data' => [
                'transactionId' => 'txn_unpaid',
                'status' => 'SUCCESS',
                'transactionStatus' => 'paid',
            ],
        ])
            ->assertOk()
            ->assertJsonPath('status', 'ignored');

        $this->assertDatabaseHas('invoice_subscriptions', [
            'id' => $invoiceId,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscriptionId,
            'status' => 'pending_payment',
        ]);
        $this->assertDatabaseMissing('mayar_fulfillments', [
            'transaction_id' => 'txn_unpaid',
        ]);
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
     * @return array{0: User, 1: int, 2: int}
     */
    private function tenantWithSubscription(
        string $planSlug,
        string $tenantStatus,
        string $subscriptionStatus,
        ?string $trialEndsAt,
        ?string $endsAt,
    ): array {
        $planId = (int) DB::table('plans')->where('slug', $planSlug)->value('id');
        $tenantId = (int) DB::table('tenants')->insertGetId([
            'name' => 'Billing Travel',
            'slug' => 'billing-travel-'.uniqid(),
            'email' => 'billing@example.com',
            'status' => $tenantStatus,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => $subscriptionStatus,
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => $subscriptionStatus === 'pending_payment' ? null : now()->subDays(2)->toDateString(),
            'ends_at' => $endsAt,
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
            'gateway_reference' => 'txn_123',
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
