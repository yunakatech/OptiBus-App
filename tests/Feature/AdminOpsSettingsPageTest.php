<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOpsSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): void
    {
        $this->actingAsSuperAdminWithTenantContext($this->defaultTenantId());
    }

    private function defaultTenantId(): int
    {
        return (int) DB::table('tenants')->where('slug', 'qbus-default')->value('id');
    }

    public function test_settings_pages_expose_separate_deferred_list_and_master_payloads(): void
    {
        $this->actingAsSuperAdmin();

        $pages = [
            ['admin-ops.pools', 'PengaturanPool', 'pools', ['routes']],
            ['admin-ops.users', 'PengaturanUsers', 'users', ['pools', 'roles']],
            ['admin-ops.drivers', 'PengaturanDriver', 'drivers', ['armadas']],
            ['admin-ops.armadas', 'PengaturanArmada', 'armadas', ['categories', 'units']],
            ['admin-ops.schedules', 'PengaturanJadwal', 'schedules', ['routes', 'units']],
        ];

        foreach ($pages as [$routeName, $component, $tab, $masterKeys]) {
            $this->get(route($routeName))
                ->assertInertia(fn (Assert $page) => $page
                    ->component($component)
                    ->where('initialTab', $tab)
                    ->missingAll(['settingsData', 'settingsMasters'])
                    ->loadDeferredProps('settings-data', fn (Assert $reload) => $reload
                        ->where('settingsData.tab', $tab)
                        ->has("settingsData.{$tab}")
                        ->has('settingsData.pagination'))
                    ->loadDeferredProps('settings-masters', function (Assert $reload) use ($tab, $masterKeys): void {
                        $reload->where('settingsMasters.tab', $tab);

                        foreach ($masterKeys as $key) {
                            $reload->has("settingsMasters.{$key}");
                        }
                    }),
                );
        }
    }

    public function test_settings_deferred_list_supports_search_and_pagination_without_changing_default_api_shape(): void
    {
        $this->actingAsSuperAdmin();

        for ($index = 1; $index <= 25; $index++) {
            DB::table('drivers')->insert([
                'nama' => $index === 25 ? 'DRIVER KHUSUS FILTER' : "DRIVER UJI {$index}",
                'phone' => "0812300{$index}",
                'target_revenue_bulanan' => 0,
                'created_at' => now(),
            ]);
        }

        $this->get(route('admin-ops.drivers', ['q' => 'khusus filter', 'per_page' => 10]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('settingsQuery.q', 'khusus filter')
                ->loadDeferredProps('settings-data', fn (Assert $reload) => $reload
                    ->has('settingsData.drivers', 1)
                    ->where('settingsData.drivers.0.nama', 'DRIVER KHUSUS FILTER')
                    ->where('settingsData.pagination.total', 1)
                    ->where('settingsData.pagination.per_page', 10)),
            );

        $this->getJson(route('api.admin.drivers.index'))
            ->assertOk()
            ->assertJsonCount(25, 'drivers')
            ->assertJsonMissingPath('pagination');

        $this->getJson(route('api.admin.drivers.index', ['paginate' => 1, 'per_page' => 10]))
            ->assertOk()
            ->assertJsonCount(10, 'drivers')
            ->assertJsonPath('pagination.total', 25);
    }

    public function test_read_only_operator_can_open_lists_but_cannot_use_manage_actions(): void
    {
        AccessControl::syncDefaults();

        $roleId = (int) DB::table('roles')->insertGetId([
            'name' => 'Operator Master Read Only',
            'slug' => 'operator-master-read-only',
            'description' => 'Hanya melihat data pengaturan.',
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $permissionIds = DB::table('permissions')
            ->whereIn('slug', ['master.view', 'driver.view', 'armada.view'])
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permission')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $operator = User::factory()->create(['is_super_admin' => false]);
        DB::table('user_role')->insert([
            'user_id' => $operator->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($operator);

        $this->get(route('admin-ops.drivers'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.permissions', fn ($permissions): bool => collect($permissions)->contains('driver.view')
                    && collect($permissions)->contains('armada.view')
                    && collect($permissions)->contains('master.view')
                    && ! collect($permissions)->contains('driver.manage')
                    && ! collect($permissions)->contains('armada.manage')
                    && ! collect($permissions)->contains('master.manage')),
            );

        $this->get(route('admin-ops.armadas'))->assertOk();
        $this->get(route('admin-ops.schedules'))->assertOk();
        $this->get(route('admin-ops.segments'))->assertOk();
        $this->get(route('admin-ops.pools'))->assertForbidden();
        $this->get(route('admin-ops.users'))->assertForbidden();

        $this->postJson(route('api.admin.drivers.save'), ['nama' => 'TIDAK BOLEH'])
            ->assertForbidden()
            ->assertJsonPath('error', 'Anda tidak memiliki akses untuk aksi ini.');
        $this->postJson(route('api.admin.armadas.save'), ['nopol' => 'DD 1000 XX'])
            ->assertForbidden()
            ->assertJsonPath('error', 'Anda tidak memiliki akses untuk aksi ini.');
        $this->postJson(route('api.admin.schedules.save'), [])
            ->assertForbidden()
            ->assertJsonPath('error', 'Anda tidak memiliki akses untuk aksi ini.');
        $this->postJson(route('api.admin.segments.save'), [])
            ->assertForbidden()
            ->assertJsonPath('error', 'Anda tidak memiliki akses untuk aksi ini.');
    }
}
