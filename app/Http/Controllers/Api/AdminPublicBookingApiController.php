<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PublicBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPublicBookingApiController extends Controller
{
    public function __construct(private readonly PublicBookingService $bookingService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'requests' => $this->bookingService->adminRequests(null, trim((string) $request->query('status', 'pending'))),
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'result' => $this->bookingService->approve($id)]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 409);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        try {
            return response()->json(['success' => true, 'result' => $this->bookingService->reject($id, (string) $data['reason'])]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 409);
        }
    }
}
