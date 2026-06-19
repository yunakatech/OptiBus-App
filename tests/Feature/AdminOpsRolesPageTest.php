<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOpsRolesPageTest extends TestCase
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

    private function userWithRole(string $roleSlug): User
    {
        AccessControl::syncDefaults();

        $user = User::factory()->create(['is_super_admin' => false]);
        $roleId = (int) DB::table('roles')->where('slug', $roleSlug)->value('id');

        DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    public function test_roles_page_exposes_separate_deferred_role_and_permission_payloads(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(route('admin-ops.roles'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('PengaturanRoles')
                ->where('initialTab', 'roles')
                ->missingAll(['roleData', 'rolePermissions'])
                ->loadDeferredProps('role-data', fn (Assert $reload) => $reload
                    ->has('roleData.roles')
                    ->has('roleData.pagination')
                    ->where('roleData.pagination.page', 1))
                ->loadDeferredProps('role-permissions', fn (Assert $reload) => $reload
                    ->has('rolePermissions.permissions')
                    ->has('rolePermissions.permission_groups')),
            );
    }

    public function test_roles_page_filters_and_paginates_role_data(): void
    {
        $this->actingAsSuperAdmin();

        DB::table('roles')->insert([
            'name' => 'Operator Uji Filter',
            'slug' => 'operator-uji-filter',
            'description' => 'Role khusus pencarian deferred props.',
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('admin-ops.roles', ['q' => 'uji filter', 'per_page' => 10]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('roleQuery', 'uji filter')
                ->loadDeferredProps('role-data', fn (Assert $reload) => $reload
                    ->has('roleData.roles', 1)
                    ->where('roleData.roles.0.slug', 'operator-uji-filter')
                    ->where('roleData.pagination.total', 1)
                    ->where('roleData.pagination.per_page', 10)),
            );
    }

    public function test_operator_only_receives_and_can_use_actions_from_assigned_role(): void
    {
        $operator = $this->userWithRole('operator-carter');
        $this->actingAs($operator);

        $this->get(route('charters.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.permissions', fn ($permissions): bool => collect($permissions)->contains('charter.view')
                    && collect($permissions)->contains('charter.create')
                    && collect($permissions)->contains('charter.update')
                    && collect($permissions)->contains('charter.print')
                    && ! collect($permissions)->contains('charter.delete')
                    && ! collect($permissions)->contains('role.manage')),
            );

        $this->get(route('charters.form'))->assertOk();
        $this->deleteJson(route('api.admin.charters.delete', ['id' => 999999]))
            ->assertForbidden()
            ->assertJsonPath('error', 'Anda tidak memiliki akses untuk aksi ini.');
        $this->get(route('admin-ops.roles'))->assertForbidden();
    }

    public function test_role_crud_and_permission_updates_remain_on_api(): void
    {
        $this->actingAsSuperAdmin();

        $permissionIds = DB::table('permissions')
            ->whereIn('slug', ['charter.view', 'charter.create'])
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $create = $this->postJson(route('api.admin.roles.save'), [
            'name' => 'Operator Carter Uji',
            'slug' => 'operator-carter-uji',
            'description' => 'Role uji API.',
            'permission_ids' => $permissionIds,
        ])->assertCreated();

        $roleId = (int) $create->json('id');
        sort($permissionIds);
        $this->assertSame(count($permissionIds), DB::table('role_permission')->where('role_id', $roleId)->count());
        $this->assertSame($permissionIds, DB::table('role_permission')->where('role_id', $roleId)->orderBy('permission_id')->pluck('permission_id')->map(static fn ($id): int => (int) $id)->all());
        $this->getJson(route('api.admin.roles.index', ['q' => 'operator-carter-uji']))
            ->assertOk()
            ->assertJsonPath('roles.0.id', $roleId)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonStructure(['permissions', 'permission_groups']);

        $updatedPermissionId = (int) DB::table('permissions')->where('slug', 'charter.view')->value('id');
        $this->postJson(route('api.admin.roles.save'), [
            'id' => $roleId,
            'name' => 'Operator Carter Uji Update',
            'slug' => 'operator-carter-uji',
            'description' => 'Role uji API diperbarui.',
            'permission_ids' => [$updatedPermissionId],
        ])->assertOk();

        $this->assertDatabaseHas('roles', [
            'id' => $roleId,
            'name' => 'Operator Carter Uji Update',
            'slug' => 'operator-carter-uji',
        ]);
        $this->assertDatabaseHas('role_permission', [
            'role_id' => $roleId,
            'permission_id' => $updatedPermissionId,
        ]);

        $this->deleteJson(route('api.admin.roles.delete', ['id' => $roleId]))->assertOk();
        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }
}
