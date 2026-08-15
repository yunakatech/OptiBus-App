<?php

namespace Tests\Feature\Auth;

use App\Models\User;
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
        ])
            ->get(route('google.redirect'))
            ->assertRedirect('/oauth/google');

        $this->assertSame('trial', session('registration_intent'));
        $this->assertSame('starter', session('registration_plan'));
    }

    public function test_new_google_user_is_verified_and_redirected_to_onboarding(): void
    {
        $this->mockGoogleUser('new-google-user@example.com');

        $response = $this->get(route('google.callback'));

        $user = User::where('email', 'new-google-user@example.com')->first();

        $response->assertRedirect(route('onboarding', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user?->email_verified_at);
        $this->assertTrue((bool) session('registration_onboarding_pending'));
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
