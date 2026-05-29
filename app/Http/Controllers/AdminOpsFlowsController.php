<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsFlowsController extends Controller
{
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

        $component = $requestedTab === 'luggages' ? 'Luggages' : 'AdminOpsFlows';

        return Inertia::render($component, [
            'initialTab' => in_array($requestedTab, $allowedTabs, true) ? $requestedTab : null,
            'initialMode' => in_array($requestedMode, $allowedModes, true) ? $requestedMode : null,
            'initialCharterId' => $requestedCharterId > 0 ? $requestedCharterId : null,
            'lockedMenuView' => $lockedMenuView,
        ]);
    }
}
