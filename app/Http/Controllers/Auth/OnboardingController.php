<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly TenantProvisioningService $provisioning,
    ) {}

    /**
     * Show onboarding form for Google OAuth users.
     * GET /onboarding
     */
    public function show(): Response
    {
        return Inertia::render('Onboarding', [
            'user_name' => Auth::user()?->name ?? '',
            'user_email' => Auth::user()?->email ?? '',
        ]);
    }

    /**
     * Process onboarding — provision tenant with user's travel data.
     * POST /onboarding
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'travel_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'origin' => ['required', 'string', 'max:80'],
            'destination' => ['required', 'string', 'max:80'],
        ]);

        $userId = (int) $user->id;

        try {
            $this->provisioning->provisionForUser($user, [
                ...$data,
                'plan' => 'starter',
                'registration_intent' => 'trial',
                'billing_interval' => 'monthly',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Let Inertia handle validation errors properly
        } catch (\Throwable $e) {
            Log::error("Onboarding failed for user #{$userId}: ".$e->getMessage()."\n".$e->getTraceAsString());

            return back()->withErrors(['travel_name' => 'Gagal: '.$e->getMessage()]);
        }

        Log::info("Onboarding complete for user #{$userId}: {$data['travel_name']}");

        // Redirect to subscription/payment page
        return redirect()->route('subscription.index');
    }
}
