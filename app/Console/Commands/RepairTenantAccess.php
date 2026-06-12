<?php

namespace App\Console\Commands;

use App\Support\AccessControl;
use App\Support\PoolScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairTenantAccess extends Command
{
    protected $signature = 'qbus:repair-tenant-access {--user=} {--tenant=} {--pool=}';

    protected $description = 'Map an existing user to a tenant, pool, and tenant-owner role';

    public function handle(): int
    {
        $userId = (int) $this->option('user');
        $tenantId = (int) $this->option('tenant');
        $poolId = (int) $this->option('pool');

        if ($userId <= 0 || $tenantId <= 0) {
            $this->error('Use: php artisan qbus:repair-tenant-access --user=USER_ID --tenant=TENANT_ID');

            return self::FAILURE;
        }

        if (! $this->tablesReady()) {
            $this->error('Required users, tenants, pools, pool_user, roles, and user_role tables are not ready.');

            return self::FAILURE;
        }

        $user = DB::table('users')->where('id', $userId)->first(['id', 'name', 'email']);
        $tenant = DB::table('tenants')->where('id', $tenantId)->first(['id', 'name', 'slug']);
        if (! $user || ! $tenant) {
            $this->error('User or tenant not found.');

            return self::FAILURE;
        }

        AccessControl::syncDefaults();

        $poolId = $poolId > 0
            ? $this->verifiedPoolId($poolId, $tenantId)
            : $this->defaultPoolId($tenantId, (string) $tenant->name, (string) $tenant->slug);
        if ($poolId <= 0) {
            $this->error('No valid tenant pool found or created.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($userId, $tenantId, $poolId): void {
            if (Schema::hasColumn('users', 'tenant_id')) {
                DB::table('users')->where('id', $userId)->update([
                    'tenant_id' => $tenantId,
                    'updated_at' => now(),
                ]);
            }

            DB::table('pool_user')->updateOrInsert(
                ['user_id' => $userId, 'pool_id' => $poolId],
                ['created_at' => now(), 'updated_at' => now()],
            );

            $roleId = (int) (DB::table('roles')->where('slug', 'tenant-owner')->value('id') ?? 0);
            if ($roleId > 0) {
                DB::table('user_role')->updateOrInsert(
                    ['user_id' => $userId, 'role_id' => $roleId],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            }
        });

        Cache::forget("inertia:pools:user:{$userId}:v2");
        PoolScope::flushRequestCache();

        $this->info("User #{$userId} mapped to tenant #{$tenantId}, pool #{$poolId}, role tenant-owner.");

        return self::SUCCESS;
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasTable('tenants')
            && Schema::hasTable('pools')
            && Schema::hasTable('pool_user')
            && AccessControl::tablesReady();
    }

    private function verifiedPoolId(int $poolId, int $tenantId): int
    {
        $query = DB::table('pools')->where('id', $poolId);
        if (Schema::hasColumn('pools', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return (int) ($query->value('id') ?? 0);
    }

    private function defaultPoolId(int $tenantId, string $tenantName, string $tenantSlug): int
    {
        $query = DB::table('pools')->where('status', 'active');
        if (Schema::hasColumn('pools', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $poolId = (int) ($query->orderBy('id')->value('id') ?? 0);
        if ($poolId > 0) {
            return $poolId;
        }

        $payload = [
            'name' => strtoupper(trim($tenantName) !== '' ? $tenantName.' POOL' : 'TENANT POOL'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('pools', 'tenant_id')) {
            $payload['tenant_id'] = $tenantId;
        }

        if (Schema::hasColumn('pools', 'code')) {
            $payload['code'] = $this->uniquePoolCode($tenantSlug !== '' ? $tenantSlug : 'tenant-'.$tenantId);
        }

        return (int) DB::table('pools')->insertGetId($payload);
    }

    private function uniquePoolCode(string $base): string
    {
        $code = strtolower($base).'-pool';
        if (! DB::table('pools')->where('code', $code)->exists()) {
            return $code;
        }

        return strtolower($base).'-pool-'.now()->format('His');
    }
}
