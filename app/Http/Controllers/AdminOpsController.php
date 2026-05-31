<?php

namespace App\Http\Controllers;

use App\Support\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminOpsController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $lockedMenuView = (bool) ($request->route('locked') ?? false);

        if (! $lockedMenuView) {
            $legacyRoutes = [
                'routes' => 'admin-ops.routes',
                'schedules' => 'admin-ops.schedules',
                'drivers' => 'admin-ops.drivers',
                'services' => 'admin-ops.services',
                'segments' => 'admin-ops.segments',
                'customers' => 'admin-ops.customers',
                'units' => 'admin-ops.units',
                'armadas' => 'admin-ops.armadas',
                'pools' => 'admin-ops.pools',
                'users' => 'admin-ops.users',
                'cancellations' => 'admin-ops.cancellations',
                'reports' => 'report.index',
            ];

            $legacyTab = trim((string) $request->query('tab', ''));
            if ($legacyTab !== '' && isset($legacyRoutes[$legacyTab])) {
                $query = $request->query();
                unset($query['tab']);

                return redirect()->to(route($legacyRoutes[$legacyTab], $query));
            }
        }

        $allowedTabs = ['routes', 'schedules', 'drivers', 'services', 'segments', 'customers', 'units', 'armadas', 'pools', 'users', 'cancellations', 'reports'];
        $requestedTab = (string) ($request->route('tab') ?? '');
        $initialTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : null;
        $initialMode = trim((string) ($request->route('mode') ?? ''));
        $recordId = (int) ($request->route('id') ?? 0);

        $stats = [
            'routes' => 0,
            'schedules' => 0,
            'drivers' => 0,
            'luggage_services' => 0,
            'segments' => 0,
            'customers' => 0,
            'armadas' => 0,
            'pools' => 0,
            'cancellations' => 0,
        ];

        if (! $lockedMenuView) {
            $stats = [
                'routes' => Schema::hasTable('routes') ? DB::table('routes')->count() : 0,
                'schedules' => Schema::hasTable('schedules') ? DB::table('schedules')->count() : 0,
                'drivers' => Schema::hasTable('drivers') ? DB::table('drivers')->count() : 0,
                'luggage_services' => Schema::hasTable('luggage_services') ? DB::table('luggage_services')->count() : 0,
                'segments' => Schema::hasTable('segments') ? DB::table('segments')->count() : 0,
                'customers' => Schema::hasTable('customers') ? DB::table('customers')->count() : 0,
                'armadas' => Schema::hasTable('armadas') ? DB::table('armadas')->count() : 0,
                'pools' => Schema::hasTable('pools') ? DB::table('pools')->count() : 0,
                'cancellations' => ActivityLog::count(),
            ];
        }

        $component = 'AdminOps';
        if ($lockedMenuView) {
            $componentMap = [
                'routes' => 'PengaturanRuteReguler',
                'schedules' => 'PengaturanJadwal',
                'drivers' => 'PengaturanDriver',
                'services' => 'PengaturanBagasi',
                'segments' => 'PengaturanSegment',
                'customers' => 'CustomerReguler',
                'units' => 'PengaturanKategoriArmada',
                'armadas' => 'PengaturanArmada',
                'pools' => 'PengaturanPool',
                'users' => 'PengaturanUsers',
                'cancellations' => 'PengaturanLogs',
            ];
            if (is_string($initialTab) && isset($componentMap[$initialTab])) {
                $component = $componentMap[$initialTab];
            }
        }

        return Inertia::render($component, [
            'stats' => $stats,
            'initialTab' => $initialTab,
            'lockedMenuView' => $lockedMenuView,
            'initialMode' => $initialMode !== '' ? $initialMode : null,
            'initialRecordId' => $recordId > 0 ? $recordId : null,
        ]);
    }
}
