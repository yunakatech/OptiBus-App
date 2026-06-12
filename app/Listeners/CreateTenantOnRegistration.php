<?php

namespace App\Listeners;

use App\Services\TenantProvisioningService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class CreateTenantOnRegistration
{
    public function __construct(
        private readonly TenantProvisioningService $provisioning,
    ) {}

    /**
     * Handle the Registered event by creating the tenant billing workspace.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        $input = request()?->all() ?? [];
        $input['plan'] = $input['plan'] ?? session('registration_plan');
        $input['registration_intent'] = $input['registration_intent'] ?? session('registration_intent');

        $result = $this->provisioning->provisionForUser($user, $input);

        session()->forget(['registration_plan', 'registration_intent']);
        if (! empty($result['redirect_route'])) {
            session()->put('url.intended', (string) $result['redirect_route']);
        }

        Log::info('Registration provisioning handled', [
            'user_id' => (int) $user->id,
            'provisioned' => (bool) ($result['provisioned'] ?? false),
            'subscription_status' => $result['subscription_status'] ?? null,
            'tenant_status' => $result['tenant_status'] ?? null,
        ]);
    }
}
