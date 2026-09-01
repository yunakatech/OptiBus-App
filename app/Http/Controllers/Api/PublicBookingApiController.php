<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PublicBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicBookingApiController extends Controller
{
    public function __construct(private readonly PublicBookingService $bookingService) {}

    public function availability(Request $request, string $tenantSlug): JsonResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'route_id' => ['nullable', 'integer', 'min:0'],
            'segment_id' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json([
            'success' => true,
            ...$this->bookingService->availability(
                $tenantSlug,
                $data['tanggal'],
                (int) ($data['route_id'] ?? 0),
                (int) ($data['segment_id'] ?? 0),
            ),
        ]);
    }

    public function store(Request $request, string $tenantSlug): JsonResponse
    {
        $data = $request->validate([
            'website' => ['nullable', 'string', 'max:255'],
            'route_id' => ['nullable', 'integer', 'min:0'],
            'segment_id' => ['nullable', 'integer', 'min:1'],
            'schedule_id' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'unit' => ['required', 'integer', 'min:1'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'passengers' => ['required', 'array', 'min:1', 'max:20'],
            'passengers.*.seat' => ['required', 'string', 'max:20'],
            'passengers.*.passenger_name' => ['required', 'string', 'max:255'],
        ]);

        return response()->json([
            'success' => true,
            ...$this->bookingService->createRequest($tenantSlug, $data),
        ], 201);
    }
}
