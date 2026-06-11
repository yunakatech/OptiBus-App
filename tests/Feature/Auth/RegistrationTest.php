<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
                ->where('registrationIntent', 'payment')
                ->has('plans')
            );
    }

    public function test_trial_registration_creates_trial_subscription_without_invoice(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Trial User',
            'email' => 'trial@example.com',
            'travel_name' => 'Trial Travel',
            'phone' => '085211112222',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'starter',
            'registration_intent' => 'trial',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $tenantId = (int) DB::table('users')->where('email', 'trial@example.com')->value('tenant_id');
        $starterPlanId = (int) DB::table('plans')->where('slug', 'starter')->value('id');

        $this->assertGreaterThan(0, $tenantId);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenantId,
            'plan_id' => $starterPlanId,
            'status' => 'trial',
        ]);
        $this->assertFalse(DB::table('invoice_subscriptions')->where('tenant_id', $tenantId)->exists());
    }

    public function test_payment_registration_creates_pending_invoice_and_redirects_to_subscription(): void
    {
        $plan = DB::table('plans')->where('slug', 'pro')->first();

        $response = $this->post(route('register.store'), [
            'name' => 'Payment User',
            'email' => 'payment@example.com',
            'travel_name' => 'Payment Travel',
            'phone' => '085233334444',
            'origin' => 'Pinrang',
            'destination' => 'Makassar',
            'plan' => 'pro',
            'registration_intent' => 'payment',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('subscription.index', absolute: false));

        $tenantId = (int) DB::table('users')->where('email', 'payment@example.com')->value('tenant_id');
        $subscription = DB::table('subscriptions')->where('tenant_id', $tenantId)->first();
        $invoice = DB::table('invoice_subscriptions')->where('tenant_id', $tenantId)->first();

        $this->assertNotNull($plan);
        $this->assertNotNull($subscription);
        $this->assertSame((int) $plan->id, (int) $subscription->plan_id);
        $this->assertSame('active', (string) $subscription->status);
        $this->assertNull($subscription->trial_ends_at);

        $this->assertNotNull($invoice);
        $this->assertSame('pending', (string) $invoice->status);
        $this->assertSame((int) $subscription->id, (int) $invoice->subscription_id);
        $this->assertEquals((float) $plan->price_monthly, (float) $invoice->amount);
    }
}
