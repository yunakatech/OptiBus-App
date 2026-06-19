<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsSuperAdminWithTenantContext(?int $tenantId = null): User
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $this->actingAs($user);

        if ($tenantId !== null) {
            $this->withSession(['active_tenant_id' => $tenantId]);
        }

        return $user;
    }

    protected function defaultTestTenantId(string $slug = 'qbus-default'): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('tenants')->where('slug', $slug)->value('id');
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
