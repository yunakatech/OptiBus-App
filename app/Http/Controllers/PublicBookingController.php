<?php

namespace App\Http\Controllers;

use App\Services\PublicBookingService;
use Inertia\Inertia;
use Inertia\Response;

class PublicBookingController extends Controller
{
    public function __construct(private readonly PublicBookingService $bookingService) {}

    public function show(string $tenantSlug): Response
    {
        return Inertia::render('PublicBooking', $this->bookingService->pageData($tenantSlug));
    }
}
