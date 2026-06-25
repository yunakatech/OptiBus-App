<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserPreferencesTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        return $this->actingAsSuperAdminWithTenantContext($this->defaultTenantId());
    }

    private function defaultTenantId(): int
    {
        return (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
    }

    public function test_user_can_store_ui_preferences(): void
    {
        $user = $this->actingAsSuperAdmin();

        $response = $this->patchJson(route('user.ui_preferences.update'), [
            'preferences' => [
                'defaultViewMode' => 'cards',
                'defaultDateRange' => '2026-06-24',
                'itemsPerPage' => 12,
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('ui_preferences.defaultViewMode', 'cards')
            ->assertJsonPath('ui_preferences.defaultDateRange', '2026-06-24')
            ->assertJsonPath('ui_preferences.itemsPerPage', 12);

        $user->refresh();

        $this->assertSame('cards', $user->ui_preferences['defaultViewMode']);
        $this->assertSame('2026-06-24', $user->ui_preferences['defaultDateRange']);
        $this->assertSame(12, $user->ui_preferences['itemsPerPage']);
    }

    public function test_user_preferences_endpoint_rejects_invalid_view_mode(): void
    {
        $this->actingAsSuperAdmin();

        $this->patchJson(route('user.ui_preferences.update'), [
            'preferences' => [
                'defaultViewMode' => 'grid',
            ],
        ])->assertStatus(422);
    }

    public function test_bookings_page_shares_ui_preferences_and_server_today(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
        ]);
        $user->forceFill([
            'ui_preferences' => [
                'defaultViewMode' => 'cards',
                'defaultDateRange' => '2026-06-24',
            ],
        ])->save();

        $this->actingAs($user)->withSession([
            'active_tenant_id' => $this->defaultTenantId(),
        ]);

        $this->get(route('bookings.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookings')
                ->where('auth.user.ui_preferences.defaultViewMode', 'cards')
                ->where('auth.user.ui_preferences.defaultDateRange', '2026-06-24')
                ->where('server_today', now()->toDateString()),
            );
    }
}
