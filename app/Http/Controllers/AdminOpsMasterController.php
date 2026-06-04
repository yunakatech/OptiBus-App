<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AdminOpsApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsMasterController extends Controller
{
    public function __construct(
        private readonly AdminOpsApiController $adminOpsApi,
    ) {}

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $lockedMenuView = (bool) ($request->route('locked') ?? false);

        if (! $lockedMenuView) {
            $legacyRoutes = [
                'customer-bagasi' => 'admin-ops.master.customer-bagasi',
                'customer-charter' => 'admin-ops.master.customer-charter',
                'rute-carter' => 'admin-ops.master.rute-carter',
            ];

            $legacyTab = trim((string) $request->query('tab', ''));
            if ($legacyTab !== '' && isset($legacyRoutes[$legacyTab])) {
                $query = $request->query();
                unset($query['tab']);

                return redirect()->to(route($legacyRoutes[$legacyTab], $query));
            }
        }

        $allowedTabs = ['customer-bagasi', 'customer-charter', 'rute-carter'];
        $requestedTab = (string) ($request->route('tab') ?? '');
        $resolvedTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'customer-bagasi';
        $usesHybridInertia = $lockedMenuView && in_array($resolvedTab, $allowedTabs, true);

        $component = 'AdminOpsMaster';
        if ($lockedMenuView) {
            $componentMap = [
                'customer-bagasi' => 'CustomerBagasi',
                'customer-charter' => 'CustomerCarter',
                'rute-carter' => 'PengaturanRuteCarter',
            ];
            if (isset($componentMap[$requestedTab])) {
                $component = $componentMap[$requestedTab];
            }
        }

        return Inertia::render($component, [
            'initialTab' => in_array($requestedTab, $allowedTabs, true) ? $requestedTab : null,
            'lockedMenuView' => $lockedMenuView,
            'masterData' => $usesHybridInertia
                ? Inertia::defer(fn (): array => $this->masterData($request, $resolvedTab), 'master-data')
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function masterData(Request $request, string $tab): array
    {
        $payload = match ($tab) {
            'customer-charter' => $this->payload($this->adminOpsApi->customerCharterIndex($request)),
            'rute-carter' => $this->payload($this->adminOpsApi->charterRoutesMasterIndex($request)),
            default => $this->payload($this->adminOpsApi->customerBagasiIndex($request)),
        };

        return ['tab' => $tab, ...$payload];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(JsonResponse $response): array
    {
        return (array) $response->getData(true);
    }
}
