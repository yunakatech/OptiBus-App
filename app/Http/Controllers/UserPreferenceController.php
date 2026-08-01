<?php

namespace App\Http\Controllers;

use App\Support\PoolScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserPreferenceController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => ['required', 'array', 'min:1'],
            'preferences.defaultViewMode' => ['sometimes', 'nullable', Rule::in(['sheet', 'cards'])],
            'preferences.defaultDateRange' => [
                'sometimes',
                'nullable',
                'string',
                'max:32',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
            ],
            'preferences.itemsPerPage' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:500'],
            'preferences.defaultPoolId' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        $user = $request->user();
        abort_unless($user !== null, 401);

        $current = is_array($user->ui_preferences ?? null) ? $user->ui_preferences : [];
        $incoming = (array) ($validated['preferences'] ?? []);

        if (array_key_exists('defaultPoolId', $incoming)) {
            $defaultPoolId = max(0, (int) ($incoming['defaultPoolId'] ?? 0));
            if ($defaultPoolId > 0 && ! PoolScope::canAccessPool($defaultPoolId, (int) $user->id)) {
                throw ValidationException::withMessages([
                    'preferences.defaultPoolId' => 'Default pool tidak tersedia untuk user ini.',
                ]);
            }

            $incoming['defaultPoolId'] = $defaultPoolId;
        }

        foreach ($incoming as $key => $value) {
            if ($value === null || $value === '') {
                unset($current[$key]);

                continue;
            }

            $current[$key] = $value;
        }

        $defaultPoolId = max(0, (int) ($current['defaultPoolId'] ?? 0));
        if ($defaultPoolId > 0 && PoolScope::canAccessPool($defaultPoolId, (int) $user->id)) {
            session(['active_pool_id' => $defaultPoolId]);
            PoolScope::flushRequestCache();
        }

        $user->forceFill([
            'ui_preferences' => $current,
        ])->save();

        return response()->json([
            'success' => true,
            'ui_preferences' => $current,
        ]);
    }
}
