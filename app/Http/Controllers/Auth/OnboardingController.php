<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TenantProvisioningService;
use App\Support\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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
    public function show(): Response|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $user?->refresh();

        if ($user && AccessControl::userIsSuperAdmin((int) $user->id)) {
            session()->forget([
                'registration_plan',
                'registration_intent',
                'registration_onboarding_defaults',
                'registration_onboarding_pending',
                'active_tenant_id',
                'active_pool_id',
            ]);

            return redirect()->route('platform.dashboard');
        }

        $selectedPlan = (string) session('registration_plan', config('saas.default_plan', 'starter'));
        $registrationIntent = (string) session('registration_intent', 'trial');
        if ($registrationIntent === 'payment') {
            $registrationIntent = 'paid';
        }
        if (! in_array($registrationIntent, ['trial', 'paid'], true)) {
            $registrationIntent = $selectedPlan !== '' && $selectedPlan !== 'starter'
                ? 'paid'
                : 'trial';
        }
        $tenantId = (int) ($user?->tenant_id ?? 0);

        $defaults = $user ? $this->provisioning->onboardingDefaultsForUser($user) : [];
        $defaults = $this->mergeDefaults(
            $defaults,
            (array) session('registration_onboarding_defaults', []),
            (bool) session('registration_onboarding_pending', false),
        );

        return Inertia::render('Onboarding', [
            'user_name' => $user?->name ?? '',
            'user_email' => $user?->email ?? '',
            'selectedPlan' => $selectedPlan !== '' ? $selectedPlan : 'starter',
            'registrationIntent' => $registrationIntent,
            'continuationMode' => $tenantId > 0,
            'setupProgress' => $tenantId > 0 ? $this->provisioning->setupProgressForTenant($tenantId) : null,
            'defaults' => $defaults,
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
        $user->refresh();

        if (AccessControl::userIsSuperAdmin((int) $user->id)) {
            session()->forget([
                'registration_plan',
                'registration_intent',
                'registration_onboarding_defaults',
                'registration_onboarding_pending',
                'active_tenant_id',
                'active_pool_id',
            ]);

            return redirect()->route('platform.dashboard');
        }

        $data = $request->validate([
            'travel_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'origin' => ['required', 'string', 'max:80'],
            'destination' => ['required', 'string', 'max:80'],
            'plan' => ['nullable', 'string', 'max:50'],
            'registration_intent' => ['nullable', 'string', 'in:trial,paid,payment'],
            'billing_interval' => ['nullable', 'string', 'in:monthly,yearly'],
            'segment_origin' => ['nullable', 'string', 'max:120'],
            'segment_destination' => ['nullable', 'string', 'max:120'],
            'pickup_times' => ['nullable', 'array'],
            'pickup_times.*' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'ticket_price' => ['nullable', 'numeric', 'min:0'],
            'schedule_days' => ['nullable', 'array'],
            'schedule_days.*' => ['nullable', 'integer', 'min:0', 'max:6'],
            'departure_time' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'unit_template_name' => ['nullable', 'string', 'max:50'],
            'unit_category' => ['nullable', 'string', 'max:120'],
            'seat_capacity' => ['nullable', 'integer', 'min:0', 'max:200'],
            'unit_nopol' => ['nullable', 'string', 'max:50'],
            'armada_merk' => ['nullable', 'string', 'max:120'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $userId = (int) $user->id;
        $plan = trim((string) ($data['plan'] ?? session('registration_plan') ?? config('saas.default_plan', 'starter')));
        $intent = trim((string) ($data['registration_intent'] ?? session('registration_intent') ?? 'trial'));
        if ($intent === 'payment') {
            $intent = 'paid';
        }
        $intent = in_array($intent, ['trial', 'paid'], true) ? $intent : 'trial';

        try {
            if ((int) ($user->tenant_id ?? 0) > 0) {
                $result = $this->provisioning->completeSetupForUser($user, $data);
            } else {
                $result = $this->provisioning->provisionForUser($user, [
                    ...$data,
                    'plan' => $intent === 'trial' ? 'starter' : ($plan !== '' ? $plan : 'starter'),
                    'registration_intent' => $intent,
                    'billing_interval' => $data['billing_interval'] ?? 'monthly',
                ]);
            }
            session()->forget([
                'registration_plan',
                'registration_intent',
                'registration_onboarding_defaults',
                'registration_onboarding_pending',
            ]);
        } catch (ValidationException $e) {
            throw $e; // Let Inertia handle validation errors properly
        } catch (\Throwable $e) {
            Log::error("Onboarding failed for user #{$userId}: ".$e->getMessage()."\n".$e->getTraceAsString());

            return back()->withErrors(['travel_name' => 'Gagal: '.$e->getMessage()]);
        }

        Log::info("Onboarding complete for user #{$userId}: {$data['travel_name']}");

        return redirect()->to($result['redirect_route'] ?? route('subscription.index'));
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $draft
     * @return array<string, string>
     */
    private function mergeDefaults(array $base, array $draft, bool $preferDraft = false): array
    {
        $defaults = [
            'travel_name' => (string) ($base['travel_name'] ?? ''),
            'phone' => (string) ($base['phone'] ?? ''),
            'origin' => (string) ($base['origin'] ?? ''),
            'destination' => (string) ($base['destination'] ?? ''),
        ];

        foreach (array_keys($defaults) as $key) {
            $value = trim((string) ($draft[$key] ?? ''));
            if ($preferDraft || $value !== '') {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}
