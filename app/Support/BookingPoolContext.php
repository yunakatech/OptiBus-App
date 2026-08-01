<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingPoolContext
{
    public const MESSAGE = 'Pilih pool terlebih dahulu sebelum masuk Booking Console.';

    public static function resolve(?int $userId = null): int
    {
        $userId ??= (int) (auth()->id() ?? 0);
        if ($userId <= 0 || PoolScope::tenantId($userId) <= 0) {
            return 0;
        }

        $activePoolId = (int) session('active_pool_id', 0);
        if ($activePoolId > 0) {
            if (PoolScope::canAccessPool($activePoolId, $userId)) {
                return $activePoolId;
            }

            session()->forget('active_pool_id');
            PoolScope::flushRequestCache();
        }

        $defaultPoolId = self::defaultPoolId($userId);
        if ($defaultPoolId > 0 && PoolScope::canAccessPool($defaultPoolId, $userId)) {
            return self::activatePool($defaultPoolId);
        }

        $accessiblePoolIds = PoolScope::accessiblePoolIds($userId, false);
        if (count($accessiblePoolIds) === 1) {
            return self::activatePool((int) $accessiblePoolIds[0]);
        }

        return 0;
    }

    public static function requirePool(?int $userId = null): int
    {
        $poolId = self::resolve($userId);
        if ($poolId > 0) {
            return $poolId;
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => self::MESSAGE,
            'error' => self::MESSAGE,
            'action_required' => 'select_pool',
        ], 409));
    }

    public static function defaultPoolId(?int $userId = null): int
    {
        $userId ??= (int) (auth()->id() ?? 0);
        if ($userId <= 0 || ! SchemaCache::hasTable('users') || ! Schema::hasColumn('users', 'ui_preferences')) {
            return 0;
        }

        $preferences = DB::table('users')->where('id', $userId)->value('ui_preferences');
        if (is_string($preferences)) {
            $decoded = json_decode($preferences, true);
            $preferences = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($preferences)) {
            $model = User::query()->whereKey($userId)->first(['ui_preferences']);
            $preferences = is_array($model?->ui_preferences ?? null) ? $model->ui_preferences : [];
        }

        return max(0, (int) ($preferences['defaultPoolId'] ?? $preferences['default_pool_id'] ?? 0));
    }

    private static function activatePool(int $poolId): int
    {
        session(['active_pool_id' => $poolId]);
        PoolScope::flushRequestCache();

        return $poolId;
    }
}
