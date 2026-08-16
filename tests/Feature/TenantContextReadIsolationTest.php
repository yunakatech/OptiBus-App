<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextReadIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_without_tenant_cannot_open_admin_ops_page(): void
    {
        $this->actingAsSuperAdminWithTenantContext(null);

        $this->get(route('settings.routes'))
            ->assertRedirect(route('platform.dashboard', absolute: false));
    }

    public function test_superadmin_without_tenant_cannot_read_operational_api(): void
    {
        $this->actingAsSuperAdminWithTenantContext(null);

        $this->getJson(route('api.admin.routes.index'))
            ->assertStatus(409)
            ->assertJsonPath('action_required', 'select_tenant');
    }

    public function test_superadmin_without_tenant_can_still_open_platform_role_page(): void
    {
        $this->actingAsSuperAdminWithTenantContext(null);

        $this->get(route('settings.roles'))
            ->assertOk();
    }
}
