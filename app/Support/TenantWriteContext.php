<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class TenantWriteContext
{
    public const ACTION_REQUIRED = 'select_tenant';

    public const MESSAGE = 'Pilih tenant terlebih dahulu sebelum membuat atau mengubah data.';

    public static function requireTenant(?int $userId = null): int
    {
        $tenantId = PoolScope::tenantId($userId);
        if ($tenantId > 0) {
            return $tenantId;
        }

        abort(response()->json(self::errorPayload(), 409));
    }

    /**
     * @return array<string, int>
     */
    public static function payloadForTable(string $table, ?int $userId = null): array
    {
        if (! SchemaCache::hasColumn($table, 'tenant_id')) {
            return [];
        }

        return ['tenant_id' => self::requireTenant($userId)];
    }

    /**
     * @return array<string, mixed>
     */
    public static function errorPayload(): array
    {
        return [
            'success' => false,
            'error' => self::MESSAGE,
            'action_required' => self::ACTION_REQUIRED,
            'redirect_url' => Route::has('platform.dashboard')
                ? route('platform.dashboard', absolute: false)
                : '/',
        ];
    }
}
