<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TenantInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_ordinary_google_sign_in_resets_stale_registration_flow_to_trial(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('/oauth/google'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        $this->withSession([
            'registration_intent' => 'paid',
            'registration_plan' => 'pro',
            'google_registration' => true,
        ])
            ->get(route('google.redirect'))
            ->assertRedirect('/oauth/google');

        $this->assertSame('trial', session('registration_intent'));
        $this->assertSame('starter', session('registration_plan'));
        $this->assertFalse(session()->has('google_registration'));
    }

    public function test_unregistered_google_user_cannot_sign_in(): void
    {
        $this->mockGoogleUser('unknown-google@example.com');

        $this->withSession(['registration_intent' => 'trial', 'registration_plan' => 'starter'])
            ->get(route('google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Email Google ini belum terdaftar. Silakan daftar terlebih dahulu.');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unknown-google@example.com']);
    }

    public function test_new_google_user_can_register_and_is_redirected_to_onboarding(): void
    {
        $this->mockGoogleUser('new-google-user@example.com');

        $response = $this->withSession(['google_registration' => true])->get(route('google.callback'));

        $user = User::where('email', 'new-google-user@example.com')->first();

        $response->assertRedirect(route('onboarding', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user?->email_verified_at);
        $this->assertTrue((bool) session('registration_onboarding_pending'));
    }

    public function test_google_registration_redirect_sets_registration_mode(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('/oauth/google'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        $this->get(route('google.redirect', ['intent' => 'paid', 'plan' => 'pro']))
            ->assertRedirect('/oauth/google');

        $this->assertTrue(session('google_registration'));
        $this->assertSame('paid', session('registration_intent'));
        $this->assertSame('pro', session('registration_plan'));
    }

    public function test_existing_google_user_without_tenant_skips_verification_and_goes_to_onboarding(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'existing-google-user@example.com',
            'tenant_id' => null,
        ]);

        $this->mockGoogleUser('existing-google-user@example.com');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('onboarding', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->refresh()->email_verified_at);
        $this->assertTrue((bool) session('registration_onboarding_pending'));
    }

    public function test_google_invitation_can_authenticate_without_registration_mode(): void
    {
        $user = User::factory()->create(['email' => 'invited@example.com']);
        $this->mockGoogleUser('invited@example.com');
        $service = Mockery::mock(TenantInvitationService::class);
        $service->shouldReceive('consumeForGoogle')->once()
            ->with('invited@example.com', 'Google User', null, 'invite-token')
            ->andReturn(['status' => 'consumed', 'user' => $user]);
        $this->app->instance(TenantInvitationService::class, $service);

        $this->withSession(['google_invitation_token' => 'invite-token'])
            ->get(route('google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    private function mockGoogleUser(string $email): void
    {
        $googleUser = Mockery::mock('Laravel\\Socialite\\Contracts\\User');
        $googleUser->shouldReceive('getEmail')->once()->andReturn($email);
        $googleUser->shouldReceive('getName')->once()->andReturn('Google User');
        $googleUser->shouldReceive('getNickname')->zeroOrMoreTimes()->andReturn(null);
        $googleUser->shouldReceive('getAvatar')->once()->andReturn(null);

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver);
    }
}
