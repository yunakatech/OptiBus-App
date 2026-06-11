<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        // Immediately assign default role so user can access the app.
        // This is a belt-and-suspenders with CreateTenantOnRegistration listener.
        $this->assignDefaultRole((int) $user->id);

        return $user;
    }

    /**
     * Assign the "Admin Pool" role to a newly registered user.
     * Runs synchronously before the login redirect.
     */
    private function assignDefaultRole(int $userId): void
    {
        try {
            if (! Schema::hasTable('roles') || ! Schema::hasTable('user_role')) {
                return;
            }

            // Safety net: sync roles if table is empty
            if ((int) DB::table('roles')->count() === 0) {
                AccessControl::syncDefaults();
            }
            AccessControl::ensureDefaultRoleReady('admin-pool');

            // Skip if user already has a role (double-safety with event listener)
            if (DB::table('user_role')->where('user_id', $userId)->exists()) {
                return;
            }

            $roleId = DB::table('roles')->where('slug', 'admin-pool')->value('id')
                ?? DB::table('roles')->where('slug', '!=', 'super-admin')->orderBy('id')->value('id');

            if ($roleId) {
                DB::table('user_role')->insert([
                    'user_id' => $userId,
                    'role_id' => (int) $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info("Role assigned in CreateNewUser for user #{$userId}");
            }
        } catch (\Throwable $e) {
            Log::error("Failed to assign role in CreateNewUser: {$e->getMessage()}");
        }
    }
}
