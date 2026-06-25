<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        ]);

        $user = $request->user();
        abort_unless($user !== null, 401);

        $current = is_array($user->ui_preferences ?? null) ? $user->ui_preferences : [];
        $incoming = (array) ($validated['preferences'] ?? []);

        foreach ($incoming as $key => $value) {
            if ($value === null || $value === '') {
                unset($current[$key]);

                continue;
            }

            $current[$key] = $value;
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
