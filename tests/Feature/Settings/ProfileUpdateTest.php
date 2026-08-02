<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_picture_can_be_replaced_and_previous_file_is_deleted(): void
    {
        config()->set('filesystems.disks.supabase.key', 'test-key');
        config()->set('filesystems.disks.supabase.secret', 'test-secret');
        config()->set('filesystems.disks.supabase.region', 'us-east-1');
        config()->set('filesystems.disks.supabase.bucket', 'avatars');
        config()->set(
            'filesystems.disks.supabase.endpoint',
            'https://project.test/storage/v1/s3',
        );
        config()->set(
            'filesystems.disks.supabase.url',
            'https://project.test/storage/v1/object/public/avatars',
        );

        Storage::fake('supabase');

        $oldPath = 'old-avatar.jpg';
        Storage::disk('supabase')->put($oldPath, 'old-avatar');

        $user = User::factory()->create([
            'avatar' => config('filesystems.disks.supabase.url').'/'.ltrim($oldPath, '/'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.png'),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertStringContainsString(
            '/storage/v1/object/public/avatars/',
            $user->avatar ?? '',
        );
        $this->assertNotSame(
            config('filesystems.disks.supabase.url').'/'.ltrim($oldPath, '/'),
            $user->avatar,
        );
        Storage::disk('supabase')->assertMissing($oldPath);

        $newPath = Str::after($user->avatar ?? '', '/storage/v1/object/public/avatars/');
        $this->assertNotSame('', $newPath);
        Storage::disk('supabase')->assertExists($newPath);
    }

    public function test_profile_picture_update_returns_error_when_avatar_column_is_missing(): void
    {
        $user = User::factory()->create(['avatar' => null]);

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('avatar');
        });

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.png'),
            ]);

        $response
            ->assertSessionHasErrors('avatar')
            ->assertRedirect(route('profile.edit'));

        $this->assertFalse(Schema::hasColumn('users', 'avatar'));
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_super_admin_can_delete_their_account()
    {
        $user = User::factory()->create(['is_super_admin' => true]);

        $response = $this
            ->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
    }

    public function test_tenant_owner_cannot_delete_account(): void
    {
        AccessControl::syncDefaults();

        $user = User::factory()->create(['is_super_admin' => false]);
        $this->assignRole($user, 'tenant-owner');

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        $response->assertForbidden();

        $this->assertNotNull($user->fresh());
    }

    public function test_regular_user_cannot_delete_account(): void
    {
        $user = User::factory()->create(['is_super_admin' => false]);

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        $response->assertForbidden();

        $this->assertNotNull($user->fresh());
    }

    private function assignRole(User $user, string $slug): void
    {
        $roleId = (int) DB::table('roles')->where('slug', $slug)->value('id');

        if ($roleId <= 0) {
            $this->fail("Role [{$slug}] is missing.");
        }

        DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
