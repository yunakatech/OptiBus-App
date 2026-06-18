<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class VerificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::emailVerification());
    }

    public function test_sends_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect(route('home'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_does_not_send_verification_notification_if_email_is_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertNothingSent();
    }

    public function test_registration_event_sends_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        event(new Registered($user));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_verification_notification_gracefully_handles_mail_failures(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'id' => 999,
            'email' => 'broken-mail@example.com',
        ]);
        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);
        $user->shouldReceive('sendEmailVerificationNotification')
            ->once()
            ->andThrow(new RuntimeException('SMTP down'));

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        $this->assertSame('verification-link-failed', session('status'));
    }

    public function test_user_verification_notification_records_mail_failures(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'id' => 7,
            'email' => 'listener-fail@example.com',
        ]);
        $user->shouldReceive('notify')
            ->once()
            ->andThrow(new RuntimeException('SMTP down'));

        $user->sendEmailVerificationNotification();

        $this->assertTrue($user->emailVerificationSendFailed());
    }
}
