<?php

namespace App\Console\Commands;

use App\Support\AccessControl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixUserRoles extends Command
{
    protected $signature = 'saas:fix-user-roles';
    protected $description = 'Assign default role to all users who don\'t have one';

    public function handle(): int
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('user_role') || ! Schema::hasTable('users')) {
            $this->error('Required tables not found. Run migrations first.');

            return 1;
        }

        // Ensure roles exist
        if ((int) DB::table('roles')->count() === 0) {
            $this->info('No roles found. Syncing defaults...');
            AccessControl::syncDefaults();
        }

        $adminPoolRoleId = DB::table('roles')->where('slug', 'admin-pool')->value('id');
        if (! $adminPoolRoleId) {
            $adminPoolRoleId = DB::table('roles')
                ->where('slug', '!=', 'super-admin')
                ->orderBy('id')
                ->value('id');
        }

        if (! $adminPoolRoleId) {
            $this->error('No assignable role found.');

            return 1;
        }

        $roleName = DB::table('roles')->where('id', $adminPoolRoleId)->value('name');
        $this->info("Using role: {$roleName} (ID: {$adminPoolRoleId})");

        // Find users without any role (exclude super admins)
        $users = DB::table('users')
            ->where(function ($q) {
                $q->where('is_super_admin', '!=', 1)
                  ->orWhereNull('is_super_admin');
            })
            ->whereNotIn('id', function ($q) {
                $q->select('user_id')->from('user_role');
            })
            ->get(['id', 'name', 'email']);

        if ($users->isEmpty()) {
            $this->info('All users already have roles.');

            return 0;
        }

        $this->info("Found {$users->count()} users without roles:");

        foreach ($users as $user) {
            DB::table('user_role')->insert([
                'user_id' => $user->id,
                'role_id' => (int) $adminPoolRoleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->line("  ✓ #{$user->id} {$user->name} ({$user->email}) → {$roleName}");
        }

        $this->info('Done.');

        return 0;
    }
}
