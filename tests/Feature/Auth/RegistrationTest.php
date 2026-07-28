<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_registration_screen_keeps_payment_intent_from_pricing(): void
    {
        $response = $this->get(route('register', ['plan' => 'pro', 'intent' => 'payment']));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/Register')
                ->where('selectedPlan', 'pro')
                ->where('registrationIntent', 'paid')
                ->has('plans')
            );
    }

    public function test_authenticated_user_without_dashboard_permission_is_not_redirected_to_forbidden_dashboard(): void
    {
        $user = User::factory()->create(['is_super_admin' => false]);

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('subscription.index', absolute: false));
    }

    public function test_trial_registration_creates_trial_subscription_without_invoice(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'Trial User',
            'email' => 'trial@example.com',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding', absolute: false));
        Notification::assertSentToTimes(
            User::where('email', 'trial@example.com')->firstOrFail(),
            VerifyEmail::class,
            1,
        );

        $this->get(route('onboarding'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Onboarding')
                ->where('defaults.travel_name', '')
                ->where('defaults.phone', '')
                ->where('defaults.origin', '')
                ->where('defaults.destination', '')
            );

        $tenantId = (int) DB::table('users')->where('email', 'trial@example.com')->value('tenant_id');
        $starterPlanId = (int) DB::table('plans')->where('slug', 'starter')->value('id');

        $this->assertGreaterThan(0, $tenantId);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenantId,
            'plan_id' => $starterPlanId,
            'status' => 'trial',
        ]);
        $this->assertTrue(AccessControl::can((int) auth()->id(), 'dashboard.view'));
        $this->assertFalse(DB::table('invoice_subscriptions')->where('tenant_id', $tenantId)->exists());
    }

    public function test_registration_repairs_default_role_permissions_before_assigning_role(): void
    {
        DB::table('role_permission')->delete();

        $response = $this->post(route('register.store'), [
            'name' => 'Fixed Role User',
            'email' => 'fixed-role@example.com',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding', absolute: false));
        $this->assertTrue(AccessControl::can((int) auth()->id(), 'dashboard.view'));
    }

    public function test_payment_registration_creates_pending_invoice_and_redirects_to_subscription(): void
    {
        $plan = DB::table('plans')->where('slug', 'pro')->first();

        $response = $this->post(route('register.store'), [
            'name' => 'Payment User',
            'email' => 'payment@example.com',
            'plan' => 'pro',
            'registration_intent' => 'payment',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding', absolute: false));

        $tenantId = (int) DB::table('users')->where('email', 'payment@example.com')->value('tenant_id');
        $subscription = DB::table('subscriptions')->where('tenant_id', $tenantId)->first();
        $invoice = DB::table('invoice_subscriptions')->where('tenant_id', $tenantId)->first();

        $this->assertNotNull($plan);
        $this->assertNotNull($subscription);
        $this->assertSame((int) $plan->id, (int) $subscription->plan_id);
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        $this->assertNotNull($tenant);
        $this->assertSame('pending_payment', (string) $tenant->status);
        $this->assertSame('pending_payment', (string) $subscription->status);
        $this->assertNull($subscription->trial_ends_at);

        $this->assertNotNull($invoice);
        $this->assertSame('pending', (string) $invoice->status);
        $this->assertSame((int) $subscription->id, (int) $invoice->subscription_id);
        $this->assertEquals((float) $plan->price_monthly, (float) $invoice->amount);
    }

    public function test_payment_registration_is_locked_to_subscription_until_paid(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Locked User',
            'email' => 'locked@example.com',
            'plan' => 'pro',
            'registration_intent' => 'paid',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        User::where('email', 'locked@example.com')->update(['email_verified_at' => now()]);
        $this->actingAs(User::where('email', 'locked@example.com')->firstOrFail());

        $this->get(route('dashboard'))->assertRedirect(route('subscription.index', absolute: false));
    }
}
