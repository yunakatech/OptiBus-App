<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccessControl
{
    /**
     * @return array<string, array{name: string, group: string}>
     */
    public static function defaultPermissions(): array
    {
        return [
            'dashboard.view' => ['name' => 'Lihat Dashboard', 'group' => 'Dashboard'],
            'booking.view' => ['name' => 'Lihat Booking', 'group' => 'Booking'],
            'booking.create' => ['name' => 'Tambah Booking', 'group' => 'Booking'],
            'booking.update' => ['name' => 'Edit Booking', 'group' => 'Booking'],
            'booking.delete' => ['name' => 'Hapus atau Cancel Booking', 'group' => 'Booking'],
            'booking.print' => ['name' => 'Cetak Tiket dan Manifest', 'group' => 'Booking'],
            'charter.view' => ['name' => 'Lihat Carter', 'group' => 'Carter'],
            'charter.create' => ['name' => 'Tambah Carter', 'group' => 'Carter'],
            'charter.update' => ['name' => 'Edit Carter', 'group' => 'Carter'],
            'charter.delete' => ['name' => 'Hapus Carter', 'group' => 'Carter'],
            'charter.print' => ['name' => 'Cetak Invoice Carter', 'group' => 'Carter'],
            'luggage.view' => ['name' => 'Lihat Bagasi', 'group' => 'Bagasi'],
            'luggage.create' => ['name' => 'Tambah Bagasi', 'group' => 'Bagasi'],
            'luggage.update' => ['name' => 'Edit Bagasi', 'group' => 'Bagasi'],
            'luggage.delete' => ['name' => 'Hapus Bagasi', 'group' => 'Bagasi'],
            'luggage.print' => ['name' => 'Cetak Resi Bagasi', 'group' => 'Bagasi'],
            'luggage.tracking' => ['name' => 'Update Tracking Bagasi', 'group' => 'Bagasi'],
            'customer.view' => ['name' => 'Lihat Customer', 'group' => 'Customer'],
            'customer.create' => ['name' => 'Tambah Customer', 'group' => 'Customer'],
            'customer.update' => ['name' => 'Edit Customer', 'group' => 'Customer'],
            'customer.delete' => ['name' => 'Hapus Customer', 'group' => 'Customer'],
            'customer.import' => ['name' => 'Import Customer', 'group' => 'Customer'],
            'report.view' => ['name' => 'Lihat Laporan', 'group' => 'Laporan'],
            'report.export' => ['name' => 'Export Laporan', 'group' => 'Laporan'],
            'payment.update' => ['name' => 'Update Pembayaran', 'group' => 'Keuangan'],
            'master.view' => ['name' => 'Lihat Master Data', 'group' => 'Master Data'],
            'master.manage' => ['name' => 'Kelola Master Data', 'group' => 'Master Data'],
            'driver.view' => ['name' => 'Lihat Driver', 'group' => 'Driver'],
            'driver.manage' => ['name' => 'Kelola Driver', 'group' => 'Driver'],
            'armada.view' => ['name' => 'Lihat Armada', 'group' => 'Armada'],
            'armada.manage' => ['name' => 'Kelola Armada', 'group' => 'Armada'],
            'pool.manage' => ['name' => 'Kelola Pool', 'group' => 'Akses'],
            'user.manage' => ['name' => 'Kelola User', 'group' => 'Akses'],
            'role.manage' => ['name' => 'Kelola Role', 'group' => 'Akses'],
            'logs.view' => ['name' => 'Lihat Logs', 'group' => 'Audit'],
        ];
    }

    /**
     * @return array<string, array{name: string, description: string, permissions: array<int, string>}>
     */
    public static function defaultRoles(): array
    {
        $all = array_keys(self::defaultPermissions());

        return [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Akses penuh semua menu, role, pool, dan data.',
                'permissions' => $all,
            ],
            'admin-pusat' => [
                'name' => 'Admin Pusat',
                'description' => 'Akses operasional pusat tanpa mengubah role inti.',
                'permissions' => array_values(array_diff($all, ['role.manage'])),
            ],
            'admin-pool' => [
                'name' => 'Admin Pool',
                'description' => 'Mengelola operasional pada pool yang dimapping.',
                'permissions' => [
                    'dashboard.view', 'booking.view', 'booking.create', 'booking.update', 'booking.print',
                    'charter.view', 'charter.create', 'charter.update', 'charter.print',
                    'luggage.view', 'luggage.create', 'luggage.update', 'luggage.print', 'luggage.tracking',
                    'customer.view', 'customer.create', 'customer.update', 'report.view', 'payment.update',
                    'master.view', 'driver.view', 'armada.view', 'logs.view',
                ],
            ],
            'operator-booking' => [
                'name' => 'Operator Booking',
                'description' => 'Fokus booking reguler dan customer reguler.',
                'permissions' => ['dashboard.view', 'booking.view', 'booking.create', 'booking.update', 'booking.print', 'customer.view', 'customer.create', 'customer.update'],
            ],
            'operator-bagasi' => [
                'name' => 'Operator Bagasi',
                'description' => 'Fokus transaksi dan tracking bagasi.',
                'permissions' => ['dashboard.view', 'luggage.view', 'luggage.create', 'luggage.update', 'luggage.print', 'luggage.tracking', 'customer.view', 'customer.create', 'customer.update'],
            ],
            'operator-carter' => [
                'name' => 'Operator Carter',
                'description' => 'Fokus reservasi carter dan customer carter.',
                'permissions' => ['dashboard.view', 'charter.view', 'charter.create', 'charter.update', 'charter.print', 'customer.view', 'customer.create', 'customer.update'],
            ],
            'keuangan' => [
                'name' => 'Keuangan',
                'description' => 'Melihat transaksi, update pembayaran, dan laporan.',
                'permissions' => ['dashboard.view', 'booking.view', 'charter.view', 'luggage.view', 'customer.view', 'report.view', 'report.export', 'payment.update'],
            ],
            'viewer' => [
                'name' => 'Viewer',
                'description' => 'Akses baca data sesuai pool.',
                'permissions' => ['dashboard.view', 'booking.view', 'charter.view', 'luggage.view', 'customer.view', 'report.view'],
            ],
        ];
    }

    public static function tablesReady(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_permission')
            && Schema::hasTable('user_role');
    }

    public static function syncDefaults(): void
    {
        if (! self::tablesReady()) {
            return;
        }

        $now = now();

        foreach (self::defaultPermissions() as $slug => $permission) {
            $exists = DB::table('permissions')->where('slug', $slug)->exists();
            if ($exists) {
                DB::table('permissions')->where('slug', $slug)->update([
                    'name' => $permission['name'],
                    'group' => $permission['group'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('permissions')->insert([
                    'slug' => $slug,
                    'name' => $permission['name'],
                    'group' => $permission['group'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach (self::defaultRoles() as $slug => $role) {
            $exists = DB::table('roles')->where('slug', $slug)->exists();
            if ($exists) {
                DB::table('roles')->where('slug', $slug)->update([
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_system' => true,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('roles')->insert([
                    'slug' => $slug,
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $roleId = (int) DB::table('roles')->where('slug', $slug)->value('id');
            $permissionIds = DB::table('permissions')
                ->whereIn('slug', $role['permissions'])
                ->pluck('id')
                ->map(static fn ($value) => (int) $value)
                ->all();

            DB::table('role_permission')->where('role_id', $roleId)->delete();
            $rows = array_map(static fn (int $permissionId): array => [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ], $permissionIds);

            if ($rows !== []) {
                DB::table('role_permission')->insert($rows);
            }
        }
    }

    public static function ensureDefaultRoleReady(string $roleSlug): void
    {
        if (! self::tablesReady()) {
            return;
        }

        $roleDefaults = self::defaultRoles()[$roleSlug] ?? null;
        if (! $roleDefaults) {
            return;
        }

        $roleId = (int) (DB::table('roles')->where('slug', $roleSlug)->value('id') ?? 0);
        if ($roleId <= 0) {
            self::syncDefaults();

            return;
        }

        $requiredPermissionSlugs = $roleDefaults['permissions'];
        $existingCount = (int) DB::table('role_permission')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('role_permission.role_id', $roleId)
            ->whereIn('permissions.slug', $requiredPermissionSlugs)
            ->count();

        if ($existingCount < count($requiredPermissionSlugs)) {
            self::syncDefaults();
        }
    }

    public static function bootstrapFirstSuperAdmin(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $firstUserId = (int) (DB::table('users')->orderBy('id')->value('id') ?? 0);
        if ($firstUserId <= 0) {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_super_admin')) {
            $hasSuperAdmin = DB::table('users')->where('is_super_admin', true)->exists();
            if (! $hasSuperAdmin) {
                DB::table('users')->where('id', $firstUserId)->update(['is_super_admin' => true]);
            }
        }

        if (self::tablesReady()) {
            $superAdminRoleId = (int) (DB::table('roles')->where('slug', 'super-admin')->value('id') ?? 0);
            $hasRole = DB::table('user_role')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->where('roles.slug', 'super-admin')
                ->exists();

            if ($superAdminRoleId > 0 && ! $hasRole) {
                DB::table('user_role')->updateOrInsert(
                    ['user_id' => $firstUserId, 'role_id' => $superAdminRoleId],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            }
        }
    }

    public static function userIsSuperAdmin(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_super_admin')) {
            $isSuperAdmin = DB::table('users')
                ->where('id', $userId)
                ->where('is_super_admin', true)
                ->exists();

            if ($isSuperAdmin) {
                return true;
            }
        }

        if (! self::tablesReady()) {
            return true;
        }

        return DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->where('user_role.user_id', $userId)
            ->where('roles.slug', 'super-admin')
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    public static function userPermissions(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        if (! self::tablesReady() || self::userIsSuperAdmin($userId)) {
            return array_keys(self::defaultPermissions());
        }

        return DB::table('user_role')
            ->join('role_permission', 'user_role.role_id', '=', 'role_permission.role_id')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('user_role.user_id', $userId)
            ->pluck('permissions.slug')
            ->map(static fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();
    }

    public static function can(int $userId, string $permission): bool
    {
        if ($permission === '') {
            return true;
        }

        if (self::userIsSuperAdmin($userId)) {
            return true;
        }

        return in_array($permission, self::userPermissions($userId), true);
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string, description: string}>
     */
    public static function rolesForSelect(): array
    {
        if (! self::tablesReady()) {
            return [];
        }

        return DB::table('roles')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description'])
            ->map(static fn ($role): array => [
                'id' => (int) $role->id,
                'name' => (string) $role->name,
                'slug' => (string) $role->slug,
                'description' => (string) ($role->description ?? ''),
            ])
            ->values()
            ->all();
    }
}
