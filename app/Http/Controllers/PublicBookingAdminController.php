<?php

namespace App\Http\Controllers;

use App\Services\PublicBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicBookingAdminController extends Controller
{
    public function __construct(private readonly PublicBookingService $bookingService) {}

    public function settings(): Response
    {
        return Inertia::render('PublicBookingSettings', [
            'settings' => $this->bookingService->settings(),
        ]);
    }

    public function inbox(): Response
    {
        return Inertia::render('PublicBookingInbox');
    }

    public function settingsData(): JsonResponse
    {
        return response()->json(['success' => true, 'settings' => $this->bookingService->settings()]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);

        try {
            return response()->json(['success' => true, 'settings' => $this->bookingService->updateSettings((bool) $data['enabled'])]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}
