<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AdminOpsApiController;
use App\Http\Controllers\Api\OperationsApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsFlowsController extends Controller
{
    public function __construct(
        private readonly AdminOpsApiController $adminOpsApi,
        private readonly OperationsApiController $operationsApi,
    ) {}

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $lockedMenuView = (bool) ($request->route('locked') ?? false);

        if (! $lockedMenuView) {
            $legacyRoutes = [
                'charters' => 'charters.index',
                'luggages' => 'luggages.index',
                'assignments' => 'admin-ops.flows.assignments',
                'export' => 'admin-ops.flows.export',
            ];

            $legacyTab = trim((string) $request->query('tab', ''));
            if ($legacyTab !== '' && isset($legacyRoutes[$legacyTab])) {
                $query = $request->query();
                unset($query['tab']);

                return redirect()->to(route($legacyRoutes[$legacyTab], $query));
            }
        }

        $allowedTabs = ['charters', 'luggages', 'assignments', 'export'];
        $allowedModes = ['data', 'form', 'view'];
        $requestedTab = (string) ($request->route('tab') ?? '');
        $requestedMode = (string) ($request->route('mode') ?? '');
        $requestedCharterId = (int) ($request->route('id') ?? 0);
        $resolvedTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'charters';
        $usesHybridInertia = in_array($resolvedTab, ['charters', 'luggages'], true);

        $component = $requestedTab === 'luggages' ? 'Luggages' : 'AdminOpsFlows';

        return Inertia::render($component, [
            'initialTab' => in_array($requestedTab, $allowedTabs, true) ? $requestedTab : null,
            'initialMode' => in_array($requestedMode, $allowedModes, true) ? $requestedMode : null,
            'initialCharterId' => $requestedCharterId > 0 ? $requestedCharterId : null,
            'lockedMenuView' => $lockedMenuView,
            'flowData' => $usesHybridInertia
                ? Inertia::defer(fn (): array => $this->flowData($request, $resolvedTab), 'flow-data')
                : null,
            'flowMasters' => $usesHybridInertia
                ? Inertia::defer(fn (): array => $this->flowMasters($request, $resolvedTab), 'flow-masters')
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function flowData(Request $request, string $tab): array
    {
        try {
            $payload = match ($tab) {
                'luggages' => $this->payload($this->adminOpsApi->luggagesIndex($request)),
                default => $this->payload($this->adminOpsApi->chartersIndex($request)),
            };
        } catch (Throwable $exception) {
            if (! app()->environment('testing') && ! $exception instanceof QueryException) {
                report($exception);
            }

            return match ($tab) {
                'luggages' => [
                    'tab' => $tab,
                    'luggages' => [],
                    'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1],
                ],
                default => [
                    'tab' => $tab,
                    'charters' => [],
                    'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1],
                ],
            };
        }

        return ['tab' => $tab, ...$payload];
    }

    /**
     * @return array<string, mixed>
     */
    private function flowMasters(Request $request, string $tab): array
    {
        try {
            $pools = $this->payload($this->adminOpsApi->poolOptionsIndex($request));

            if ($tab === 'luggages') {
                $services = $this->payload($this->operationsApi->luggageServices());

                return [
                    'tab' => $tab,
                    'services' => $services['services'] ?? [],
                    'pools' => $pools['pools'] ?? [],
                    'routes' => $pools['routes'] ?? [],
                ];
            }

            $units = $this->payload($this->operationsApi->units());
            $drivers = $this->payload($this->operationsApi->drivers());
            $charterRoutes = $this->payload($this->operationsApi->charterRoutes());

            return [
                'tab' => $tab,
                'units' => $units['units'] ?? [],
                'drivers' => $drivers['drivers'] ?? [],
                'charterRoutes' => $charterRoutes['routes'] ?? [],
                'pools' => $pools['pools'] ?? [],
            ];
        } catch (Throwable $exception) {
            if (! app()->environment('testing') && ! $exception instanceof QueryException) {
                report($exception);
            }

            return $tab === 'luggages'
                ? ['tab' => $tab, 'services' => [], 'pools' => [], 'routes' => []]
                : ['tab' => $tab, 'units' => [], 'drivers' => [], 'charterRoutes' => [], 'pools' => []];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(JsonResponse $response): array
    {
        return (array) $response->getData(true);
    }
}
