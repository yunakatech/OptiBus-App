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
            'travel_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'origin' => ['nullable', 'string', 'max:80'],
            'destination' => ['nullable', 'string', 'max:80'],
            'plan' => ['nullable', 'string', 'max:50'],
            'plan_slug' => ['nullable', 'string', 'max:50'],
            'registration_intent' => ['nullable', 'string', 'in:trial,paid,payment'],
            'intent' => ['nullable', 'string', 'in:trial,paid,payment'],
            'billing_interval' => ['nullable', 'string', 'in:monthly,yearly'],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        session([
            'registration_onboarding_pending' => true,
            'registration_onboarding_defaults' => [
                'travel_name' => trim((string) ($input['travel_name'] ?? '')),
                'phone' => trim((string) ($input['phone'] ?? '')),
                'origin' => trim((string) ($input['origin'] ?? '')),
                'destination' => trim((string) ($input['destination'] ?? '')),
            ],
            'registration_plan' => trim((string) ($input['plan'] ?? $input['plan_slug'] ?? session('registration_plan') ?? config('saas.default_plan', 'starter'))),
            'registration_intent' => $this->registrationIntent($input),
        ]);

        // Immediately assign default role so user can access the app.
        // This is a belt-and-suspenders with CreateTenantOnRegistration listener.
        $this->assignDefaultRole((int) $user->id);

        return $user;
    }

    /**
     * Assign the tenant owner role to a newly registered user.
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
            AccessControl::ensureDefaultRoleReady('tenant-owner');

            // Skip if user already has a role (double-safety with event listener)
            if (DB::table('user_role')->where('user_id', $userId)->exists()) {
                return;
            }

            $roleId = DB::table('roles')->where('slug', 'tenant-owner')->value('id')
                ?? DB::table('roles')->where('slug', 'admin-pool')->value('id')
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

    /**
     * @param  array<string, string>  $input
     */
    private function registrationIntent(array $input): string
    {
        $intent = trim((string) ($input['registration_intent'] ?? $input['intent'] ?? session('registration_intent') ?? 'trial'));
        if ($intent === 'payment') {
            $intent = 'paid';
        }

        return in_array($intent, ['trial', 'paid'], true) ? $intent : 'trial';
    }
}
