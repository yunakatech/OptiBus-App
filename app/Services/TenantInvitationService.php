<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Support\AccessControl;
use App\Support\FeatureGate;
use App\Support\PoolScope;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantInvitationService
{
    /**
     * @param  array<int, int>  $roleIds
     * @param  array<int, int>  $poolIds
     * @return array{invitation: object, token: string}
     */
    public function create(int $tenantId, string $email, ?string $name, array $roleIds, array $poolIds, int $invitedByUserId): array
    {
        $this->ensureReady();

        $email = $this->normalizeEmail($email);
        $name = trim((string) $name);
        $roleIds = $this->validRoleIds($roleIds);
        $poolIds = $this->validPoolIds($tenantId, $poolIds);
        $token = $this->newToken();
        $now = now();
        $expiresAt = $now->copy()->addDays(7);

        return DB::transaction(function () use ($tenantId, $email, $name, $roleIds, $poolIds, $invitedByUserId, $token, $now, $expiresAt): array {
            $existing = DB::table('tenant_invitations')
                ->where('tenant_id', $tenantId)
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->orderByDesc('id')
                ->first();

            $payload = [
                'tenant_id' => $tenantId,
                'email' => $email,
                'name' => $name !== '' ? $name : null,
                'token_hash' => $this->hashToken($token),
                'role_ids' => json_encode($roleIds),
                'pool_ids' => json_encode($poolIds),
                'invited_by_user_id' => $invitedByUserId > 0 ? $invitedByUserId : null,
                'expires_at' => $expiresAt,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tenant_invitations')->where('id', (int) $existing->id)->update($payload);
                $invitationId = (int) $existing->id;
            } else {
                $payload['created_at'] = $now;
                $invitationId = (int) DB::table('tenant_invitations')->insertGetId($payload);
            }

            $invitation = DB::table('tenant_invitations')->where('id', $invitationId)->first();
            $this->send($invitation, $token);

            return ['invitation' => $invitation, 'token' => $token];
        });
    }

    public function resend(int $invitationId, int $tenantId): object
    {
        $this->ensureReady();
        $token = $this->newToken();
        $now = now();
        $expiresAt = $now->copy()->addDays(7);

        return DB::transaction(function () use ($invitationId, $tenantId, $token, $now, $expiresAt): object {
            $invitation = DB::table('tenant_invitations')
                ->where('id', $invitationId)
                ->where('tenant_id', $tenantId)
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->first();

            if (! $invitation) {
                throw new \RuntimeException('Invitation tidak ditemukan atau sudah selesai.');
            }

            DB::table('tenant_invitations')->where('id', $invitationId)->update([
                'token_hash' => $this->hashToken($token),
                'expires_at' => $expiresAt,
                'updated_at' => $now,
            ]);

            $updated = DB::table('tenant_invitations')->where('id', $invitationId)->first();
            $this->send($updated, $token);

            return $updated;
        });
    }

    public function revoke(int $invitationId, int $tenantId): void
    {
        $this->ensureReady();

        DB::table('tenant_invitations')
            ->where('id', $invitationId)
            ->where('tenant_id', $tenantId)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForTenant(int $tenantId): array
    {
        if ($tenantId <= 0 || ! Schema::hasTable('tenant_invitations')) {
            return [];
        }

        $roles = AccessControl::tablesReady()
            ? DB::table('roles')->pluck('name', 'id')->mapWithKeys(static fn ($name, $id): array => [(int) $id => (string) $name])->all()
            : [];
        $pools = Schema::hasTable('pools')
            ? DB::table('pools')->where('tenant_id', $tenantId)->pluck('name', 'id')->mapWithKeys(static fn ($name, $id): array => [(int) $id => (string) $name])->all()
            : [];

        return DB::table('tenant_invitations')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function (object $row) use ($roles, $pools): array {
                $roleIds = $this->integerArrayFromJson($row->role_ids ?? null);
                $poolIds = $this->integerArrayFromJson($row->pool_ids ?? null);

                return [
                    'id' => (int) $row->id,
                    'email' => (string) $row->email,
                    'name' => (string) ($row->name ?? ''),
                    'status' => $this->status($row),
                    'role_ids' => $roleIds,
                    'role_names' => array_values(array_filter(array_map(static fn (int $id): string => $roles[$id] ?? '', $roleIds))),
                    'pool_ids' => $poolIds,
                    'pool_names' => array_values(array_filter(array_map(static fn (int $id): string => $pools[$id] ?? '', $poolIds))),
                    'created_at' => $row->created_at,
                    'expires_at' => $row->expires_at,
                    'accepted_at' => $row->accepted_at,
                    'revoked_at' => $row->revoked_at,
                ];
            })
            ->all();
    }

    /**
     * @return array{status: string, user: User|null, invitation: object|null}
     */
    public function consumeForGoogle(string $email, string $name, ?string $avatar, ?string $token = null): array
    {
        if (! Schema::hasTable('tenant_invitations') || ! Schema::hasColumn('users', 'tenant_id')) {
            return ['status' => 'none', 'user' => null, 'invitation' => null];
        }

        $email = $this->normalizeEmail($email);
        $token = trim((string) $token);

        return DB::transaction(function () use ($email, $name, $avatar, $token): array {
            $invitation = $this->pendingInvitation($email, $token);
            if (! $invitation) {
                return ['status' => 'none', 'user' => null, 'invitation' => null];
            }

            if ((string) $invitation->email !== $email) {
                return ['status' => 'email_mismatch', 'user' => null, 'invitation' => $invitation];
            }

            $tenantId = (int) $invitation->tenant_id;
            $user = User::where('email', $email)->lockForUpdate()->first();
            $existingTenantId = (int) ($user?->tenant_id ?? 0);

            if ($user && $existingTenantId > 0 && $existingTenantId !== $tenantId) {
                return ['status' => 'tenant_conflict', 'user' => $user, 'invitation' => $invitation];
            }

            if (! $user && ! $this->tenantCanAcceptUser($tenantId)) {
                return ['status' => 'limit_reached', 'user' => null, 'invitation' => $invitation];
            }

            if (! $user) {
                $user = User::create([
                    'name' => $name !== '' ? $name : (string) ($invitation->name ?? 'Google User'),
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                ]);
                $payload = [
                    'tenant_id' => $tenantId,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ];
                if ($avatar && Schema::hasColumn('users', 'avatar')) {
                    $payload['avatar'] = $avatar;
                }
                DB::table('users')->where('id', (int) $user->id)->update($payload);
                $user = User::whereKey($user->id)->first();
            } else {
                $payload = [
                    'tenant_id' => $tenantId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'updated_at' => now(),
                ];
                if (! ($user->avatar ?? null) && $avatar && Schema::hasColumn('users', 'avatar')) {
                    $payload['avatar'] = $avatar;
                }
                DB::table('users')->where('id', (int) $user->id)->update($payload);
                $user = User::whereKey($user->id)->first();
            }

            $roleIds = $this->validRoleIds($this->integerArrayFromJson($invitation->role_ids ?? null));
            $poolIds = $this->validPoolIds($tenantId, $this->integerArrayFromJson($invitation->pool_ids ?? null));
            $this->syncUserRoles((int) $user->id, $roleIds);
            $this->syncUserPools((int) $user->id, $poolIds);

            DB::table('tenant_invitations')->where('id', (int) $invitation->id)->update([
                'accepted_by_user_id' => (int) $user->id,
                'accepted_at' => now(),
                'updated_at' => now(),
            ]);

            PoolScope::flushRequestCache();

            return ['status' => 'consumed', 'user' => $user, 'invitation' => $invitation];
        });
    }

    public function status(object $invitation): string
    {
        if ($invitation->accepted_at) {
            return 'accepted';
        }
        if ($invitation->revoked_at) {
            return 'revoked';
        }
        if ($invitation->expires_at && now()->greaterThan($invitation->expires_at)) {
            return 'expired';
        }

        return 'pending';
    }

    private function pendingInvitation(string $email, string $token): ?object
    {
        $query = DB::table('tenant_invitations')
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where(function ($builder): void {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if ($token !== '') {
            $query->where('token_hash', $this->hashToken($token));
        } else {
            $query->where('email', $email);
        }

        return $query->orderByDesc('created_at')->lockForUpdate()->first();
    }

    private function send(object $invitation, string $token): void
    {
        $tenantName = (string) (DB::table('tenants')->where('id', (int) $invitation->tenant_id)->value('name') ?? 'OptiBus');
        $url = route('google.redirect', ['invite' => $token], true);

        try {
            Notification::route('mail', (string) $invitation->email)
                ->notify(new TenantInvitationNotification($tenantName, $url, $invitation->expires_at ? (string) $invitation->expires_at : null));
        } catch (\Throwable $e) {
            Log::warning('tenant_invitation.email_failed', [
                'invitation_id' => (int) $invitation->id,
                'email' => (string) $invitation->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function ensureReady(): void
    {
        if (! Schema::hasTable('tenant_invitations')) {
            throw new \RuntimeException('Tabel tenant invitations belum tersedia.');
        }
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function newToken(): string
    {
        return Str::random(48);
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @param  array<int, int>  $roleIds
     * @return array<int, int>
     */
    private function validRoleIds(array $roleIds): array
    {
        $roleIds = array_values(array_unique(array_filter(array_map('intval', $roleIds), static fn (int $id): bool => $id > 0)));
        if ($roleIds === [] || ! AccessControl::tablesReady()) {
            return [];
        }

        return DB::table('roles')
            ->whereIn('id', $roleIds)
            ->where('slug', '!=', 'super-admin')
            ->pluck('id')
            ->map(static fn ($value): int => (int) $value)
            ->all();
    }

    /**
     * @param  array<int, int>  $poolIds
     * @return array<int, int>
     */
    private function validPoolIds(int $tenantId, array $poolIds): array
    {
        $poolIds = array_values(array_unique(array_filter(array_map('intval', $poolIds), static fn (int $id): bool => $id > 0)));
        if ($tenantId <= 0 || $poolIds === [] || ! Schema::hasTable('pools')) {
            return [];
        }

        $query = DB::table('pools')->whereIn('id', $poolIds);
        if (Schema::hasColumn('pools', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->pluck('id')->map(static fn ($value): int => (int) $value)->all();
    }

    /**
     * @return array<int, int>
     */
    private function integerArrayFromJson(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value), static fn (int $id): bool => $id > 0)));
    }

    /**
     * @param  array<int, int>  $roleIds
     */
    private function syncUserRoles(int $userId, array $roleIds): void
    {
        if ($userId <= 0 || ! AccessControl::tablesReady()) {
            return;
        }

        DB::table('user_role')->where('user_id', $userId)->delete();

        $now = now();
        $rows = array_map(static fn (int $roleId): array => [
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $roleIds);

        if ($rows !== []) {
            DB::table('user_role')->insert($rows);
        }
    }

    /**
     * @param  array<int, int>  $poolIds
     */
    private function syncUserPools(int $userId, array $poolIds): void
    {
        if ($userId <= 0 || ! Schema::hasTable('pool_user')) {
            return;
        }

        DB::table('pool_user')->where('user_id', $userId)->delete();

        $now = now();
        $rows = array_map(static fn (int $poolId): array => [
            'user_id' => $userId,
            'pool_id' => $poolId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $poolIds);

        if ($rows !== []) {
            DB::table('pool_user')->insert($rows);
        }
    }

    private function tenantCanAcceptUser(int $tenantId): bool
    {
        if ($tenantId <= 0 || ! FeatureGate::enabled() || ! SchemaCache::hasTable('plans')) {
            return true;
        }

        $plan = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.tenant_id', $tenantId)
            ->whereIn('subscriptions.status', ['trial', 'active', 'past_due'])
            ->orderByDesc('subscriptions.id')
            ->first(['plans.max_users']);

        if (! $plan || (int) ($plan->max_users ?? 0) <= 0) {
            return true;
        }

        $count = DB::table('users')->where('tenant_id', $tenantId)->count();

        return $count < (int) $plan->max_users;
    }
}
