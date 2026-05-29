<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsMasterController extends Controller
{
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
        ]);
    }
}
