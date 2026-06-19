<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin Ops',
                href: '/admin-ops',
            },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import {
        Armchair,
        ArrowUpRight,
        CalendarDays,
        CheckCircle2,
        Check,
        Clock3,
        ChevronDown,
        ChevronUp,
        MailX,
        MoreHorizontal,
        Pencil,
        Plus,
        Route,
        SlidersHorizontal,
        Send,
        Trash2,
        Wallet,
    } from 'lucide-svelte';
    import { onDestroy, onMount, tick } from 'svelte';
    import AdminOpsSection from '@/components/admin-ops/AdminOpsSection.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import DataTable from '@/components/terminal/DataTable.svelte';
    import EntityBadge from '@/components/terminal/EntityBadge.svelte';
    import TerminalFilter from '@/components/terminal/TerminalFilter.svelte';
    import { hasPermission } from '@/lib/access';
    import { confirmAndRun, runWithFeedback } from '@/lib/action-feedback';
    import {
        formatCurrencyDisplay,
        formatCurrencyInput as formatSharedCurrencyInput,
        parseCurrencyInput as parseSharedCurrencyInput,
    } from '@/lib/currency';
    import { loadFlatpickr } from '@/lib/flatpickr';
    import type { FlatpickrInstance } from '@/lib/flatpickr';

    type Stats = {
        routes: number;
        schedules: number;
        drivers: number;
        luggage_services: number;
        segments: number;
        customers: number;
        armadas: number;
        pools: number;
        cancellations: number;
    };
    type RouteRow = {
        id: number;
        name: string;
        origin: string | null;
        destination: string | null;
        bop: number;
        charter_revenue: number;
        departure_revenue: number;
        luggage_revenue: number;
        revenue: number;
        charter_bop: number;
        departure_bop: number;
        fixed_cost: number;
        target_revenue: number;
    };
    type UnitRow = {
        id: number;
        nopol: string;
        merek: string | null;
        type: string | null;
        category: string | null;
        tahun: number | null;
        warna: string | null;
        kapasitas: number | null;
        status: string | null;
        layout: string | null;
        pool_id?: number | null;
        pool_name?: string | null;
    };
    type ScheduleRow = {
        id: number;
        route_id: number | null;
        route_name: string | null;
        rute: string;
        dow: number;
        jam: string;
        units: number;
        bop: number;
        unit_label: string | null;
        unit_id: number | null;
        nopol: string | null;
        unit_options?: Array<{
            unit_no: number;
            label: string;
            unit_id: number | null;
            nopol?: string | null;
        }>;
    };
    type ScheduleDayGroup = {
        dow: number;
        rows: ScheduleRow[];
        totalUnits: number;
        bopTotal: number;
        firstDeparture: string | null;
        lastDeparture: string | null;
    };
    type ScheduleRouteGroup = {
        key: string;
        routeId: number;
        route: string;
        total: number;
        totalUnits: number;
        bopTotal: number;
        activeDays: number;
        firstDeparture: string | null;
        lastDeparture: string | null;
        days: ScheduleDayGroup[];
    };
    type ScheduleRouteSelectOption = {
        id: number;
        name: string;
        value: string;
    };
    type ScheduleOverview = {
        routes: number;
        total: number;
        totalUnits: number;
        bopTotal: number;
        activeDays: number;
        firstDeparture: string | null;
        lastDeparture: string | null;
    };
    type DriverRow = {
        id: number;
        nama: string;
        phone: string | null;
        unit_id: number | null;
        armada_id: number | null;
        nopol: string | null;
        target_revenue_bulanan: number;
        charter_revenue: number;
        departure_revenue: number;
        luggage_revenue: number;
        revenue: number;
        charter_bop: number;
        departure_bop: number;
        bop: number;
        fixed_cost: number;
        pool_id?: number | null;
        pool_name?: string | null;
    };
    type ServiceRow = { id: number; name: string };
    type SegmentRow = {
        id: number;
        route_id: number;
        rute: string;
        origin: string | null;
        destination: string | null;
        harga: number;
        route_name: string | null;
    };
    type CustomerRow = {
        id: number;
        name: string;
        phone: string;
        pickup_point: string | null;
        gmaps: string | null;
        pool_id?: number | null;
        pool_name?: string | null;
    };
    type Pagination = {
        page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
    type SettingsQuery = {
        q?: string;
        page?: number;
        per_page?: number;
        route_id?: number;
        rute?: string;
    };
    type SettingsDataPayload = {
        tab?: string;
        schedules?: ScheduleRow[];
        drivers?: DriverRow[];
        segments?: SegmentRow[];
        armadas?: ArmadaRow[];
        units?: UnitRow[];
        pools?: PoolRow[];
        users?: UserRow[];
        roles?: RoleOption[];
        routes?: RouteRow[];
        can_manage?: boolean;
        pagination?: Pagination;
        route_id?: number;
        rute?: string;
    };
    type SettingsMastersPayload = {
        tab?: string;
        routes?: RouteRow[];
        units?: UnitRow[];
        armadas?: ArmadaRow[];
        pools?: PoolRow[];
        roles?: RoleOption[];
        categories?: string[];
        can_manage_pools?: boolean;
    };
    type CustomerImportSummary = {
        created: number;
        updated: number;
        skipped: number;
        errors: string[];
    };
    type ArmadaRow = {
        id: number;
        merk: string | null;
        tahun: number | null;
        warna: string | null;
        nopol: string;
        nomor_rangka: string | null;
        kategori: string | null;
        ac_type: string;
        platform_gps: string | null;
        api_gps: string | null;
        charter_revenue: number;
        departure_revenue: number;
        luggage_revenue: number;
        revenue: number;
        charter_bop: number;
        departure_bop: number;
        bop: number;
        fixed_cost: number;
        target_bulanan: number;
        pool_id?: number | null;
        pool_name?: string | null;
    };
    type UserRow = {
        id: number;
        name: string;
        email: string;
        email_verified_at: string | null;
        created_at: string | null;
        is_super_admin: boolean;
        pool_ids: number[];
        pool_names: string[];
        role_ids: number[];
        role_names: string[];
    };
    type RoleOption = {
        id: number;
        name: string;
        slug: string;
        description: string;
    };
    type PoolRow = {
        id: number;
        name: string;
        code: string;
        target_revenue: number;
        status: string;
        notes: string;
        created_at: string;
        route_ids: number[];
        route_names: string[];
        charter_revenue: number;
        departure_revenue: number;
        luggage_revenue: number;
        revenue: number;
        charter_bop: number;
        departure_bop: number;
        bop: number;
        fixed_cost: number;
    };
    type CancellationRow = {
        tag: string;
        title: string;
        meta: string;
        actor: string;
        created_at: string;
    };
    type ReportKind = 'booking' | 'charter' | 'bagasi';
    type ReportSummary = {
        from: string;
        to: string;
        type: ReportKind;
        total_rows: number;
        revenue_total: number;
        pool_id?: number;
        pool_name?: string;
        target_revenue?: number;
    };
    type BookingReportRow = {
        id: number;
        tanggal: string;
        jam: string;
        name: string;
        phone: string;
        rute: string;
        pickup_point: string;
        unit: string;
        seat: string;
        pembayaran: string;
        status: string;
        discount: number;
        total: number;
    };
    type CharterReportRow = {
        id: number;
        start_date: string;
        end_date: string;
        departure_time: string;
        name: string;
        phone: string;
        pickup_point: string;
        drop_point: string;
        driver_name: string;
        layanan: string;
        payment_status: string;
        bop_status: string;
        status: string;
        unit_nopol: string;
        armada_nopol: string;
        total: number;
    };
    type LuggageReportRow = {
        id: number;
        tanggal: string;
        created_at: string;
        kode_resi: string;
        sender_name: string;
        sender_phone: string;
        receiver_name: string;
        receiver_phone: string;
        quantity: number;
        payment_status: string;
        status: string;
        service_name: string;
        total: number;
    };
    type ReportRow = BookingReportRow | CharterReportRow | LuggageReportRow;
    type LayoutCellType = 'seat' | 'empty' | 'driver';
    type LayoutCell = {
        type: LayoutCellType;
        label: string;
        fixed?: boolean;
        hidden?: boolean;
        seatNumber?: number;
        colspan?: number;
        marker?: 'aisle' | 'slot';
        seatStyle?: 'standard' | 'sleeper';
    };
    type LayoutGrid = LayoutCell[][];
    type LayoutPattern =
        | '2-2'
        | '2-1'
        | '1-1'
        | '2-3'
        | '3-0'
        | '4-0'
        | 'sleep'
        | 'empty';

    let {
        stats,
        initialTab = null,
        lockedMenuView: lockedFromServer = false,
        initialMode = null,
        initialRecordId = null,
        settingsQuery = null,
        settingsData = null,
        settingsMasters = null,
    }: {
        stats: Stats;
        initialTab?: TabName | null;
        lockedMenuView?: boolean;
        initialMode?: string | null;
        initialRecordId?: number | null;
        settingsQuery?: SettingsQuery | null;
        settingsData?: SettingsDataPayload | null;
        settingsMasters?: SettingsMastersPayload | null;
    } = $props();

    const days = [
        'Minggu',
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        "Jum'at",
        'Sabtu',
    ];
    const tabs = [
        'routes',
        'schedules',
        'drivers',
        'services',
        'segments',
        'customers',
        'units',
        'armadas',
        'pools',
        'users',
        'cancellations',
        'reports',
    ] as const;
    type TabName = (typeof tabs)[number];
    type ViewMode = 'data' | 'form' | 'view' | 'layout';
    const hybridSettingsTabs: TabName[] = [
        'schedules',
        'drivers',
        'segments',
        'units',
        'armadas',
        'pools',
        'users',
    ];
    const isHybridSettingsTab = (tab: TabName) =>
        hybridSettingsTabs.includes(tab);
    const tabTitle = (tab: TabName) => {
        if (tab === 'routes') {
            return 'Rute Induk';
        }

        if (tab === 'schedules') {
            return 'Jadwal';
        }

        if (tab === 'drivers') {
            return 'Driver';
        }

        if (tab === 'services') {
            return 'Tarif Bagasi';
        }

        if (tab === 'segments') {
            return 'Segment';
        }

        if (tab === 'customers') {
            return 'Reguler';
        }

        if (tab === 'units') {
            return 'Kategori Armada';
        }

        if (tab === 'armadas') {
            return 'Armada';
        }

        if (tab === 'pools') {
            return 'Pool';
        }

        if (tab === 'users') {
            return 'Users';
        }

        if (tab === 'cancellations') {
            return 'Logs';
        }

        return 'Laporan';
    };

    type TabGroup = {
        title: string;
        description: string;
        tabs: Array<{
            tab: TabName;
            label: string;
            permission: string | string[];
        }>;
    };

    const tabGroups: TabGroup[] = [
        {
            title: 'Master Data',
            description: 'Referensi tenant yang dipakai oleh jadwal dan operasional harian.',
            tabs: [
                { tab: 'routes', label: 'Rute Induk', permission: 'master.view' },
                { tab: 'schedules', label: 'Jadwal', permission: 'master.view' },
                { tab: 'segments', label: 'Segment', permission: 'master.view' },
                { tab: 'services', label: 'Tarif Bagasi', permission: 'master.view' },
                { tab: 'customers', label: 'Reguler', permission: 'customer.view' },
            ],
        },
        {
            title: 'Armada & Akses',
            description: 'Kelola driver, kategori armada, unit, armada, dan pool.',
            tabs: [
                { tab: 'drivers', label: 'Driver', permission: 'driver.view' },
                { tab: 'units', label: 'Kategori Armada', permission: 'master.view' },
                { tab: 'armadas', label: 'Armada', permission: 'armada.view' },
                { tab: 'pools', label: 'Pool', permission: 'pool.manage' },
            ],
        },
        {
            title: 'Tenant',
            description: 'Kelola user tenant, laporan, dan log aktivitas tenant aktif.',
            tabs: [
                { tab: 'users', label: 'Users', permission: 'user.manage' },
                { tab: 'cancellations', label: 'Logs', permission: 'logs.view' },
                { tab: 'reports', label: 'Laporan', permission: 'report.view' },
            ],
        },
    ];

    const permissions = $derived(page.props.auth?.permissions ?? []);
    const visibleTabGroups = $derived(
        tabGroups
            .map((group) => ({
                ...group,
                tabs: group.tabs.filter((item) =>
                    hasPermission(permissions, item.permission),
                ),
            }))
            .filter((group) => group.tabs.length > 0),
    );
    const tabGroupFor = (tab: TabName) =>
        visibleTabGroups.find((group) =>
            group.tabs.some((item) => item.tab === tab),
        ) ??
        tabGroups.find((group) => group.tabs.some((item) => item.tab === tab)) ??
        tabGroups[0];
    const canOpenTab = (tab: TabName) =>
        visibleTabGroups.some((group) =>
            group.tabs.some((item) => item.tab === tab),
        );
    const firstVisibleTab = () => visibleTabGroups[0]?.tabs[0]?.tab ?? 'routes';

    let activeTab = $state<TabName>('routes');
    let activeMode = $state<ViewMode>('data');
    let lockedMenuView = $state(false);
    let busy = $state(false);
    let message = $state('');
    let error = $state('');
    let savingService = $state(false);
    let pendingDeleteKey = $state('');
    let activeSubmitKey = $state('');

    const setSubmitKey = (key: string) => {
        activeSubmitKey = key;
    };

    const clearSubmitKey = (key: string) => {
        if (activeSubmitKey === key) {
            activeSubmitKey = '';
        }
    };

    const isSubmitActive = (key: string) => activeSubmitKey === key;

    let routes = $state<RouteRow[]>([]);
    let schedules = $state<ScheduleRow[]>([]);
    let drivers = $state<DriverRow[]>([]);
    let services = $state<ServiceRow[]>([]);
    let segments = $state<SegmentRow[]>([]);
    let customers = $state<CustomerRow[]>([]);
    let armadas = $state<ArmadaRow[]>([]);
    let pools = $state<PoolRow[]>([]);
    let canManagePools = $state(true);
    let users = $state<UserRow[]>([]);
    let roles = $state<RoleOption[]>([]);
    let cancellations = $state<CancellationRow[]>([]);
    let units = $state<UnitRow[]>([]);
    let customerImportInput = $state<HTMLInputElement | null>(null);
    let customerImporting = $state(false);
    let customerImportSummary = $state<CustomerImportSummary | null>(null);
    let customerMeta = $state<Pagination>({
        page: 1,
        per_page: 20,
        total: 0,
        last_page: 1,
    });
    let settingsMeta = $state<Pagination>({
        page: 1,
        per_page: 20,
        total: 0,
        last_page: 1,
    });
    let settingsQueryHydrated = $state(false);

    let routeForm = $state({
        id: 0,
        name: '',
        origin: '',
        destination: '',
        target_revenue: '',
        fixed_cost: '',
    });
    let scheduleForm = $state({
        id: 0,
        rute: '',
        dow: 1,
        jam: '08:00',
        units: 1,
        bop: '',
        unit_id: 0,
        unit_ids: [0],
        unit_labels: ['Unit 1'],
    });
    let selectedScheduleRoute = $state('');
    let selectedScheduleRouteId = $state(0);
    let selectedSegmentRouteId = $state(0);
    let driverForm = $state({
        id: 0,
        nama: '',
        phone: '',
        pool_id: 0,
        armada_id: 0,
        target_revenue_bulanan: '',
        fixed_cost: '',
    });
    let serviceForm = $state({ id: 0, name: '' });
    let segmentForm = $state({
        id: 0,
        route_id: 0,
        rute: '',
        origin: '',
        destination: '',
        harga: 0,
    });
    let customerForm = $state({
        id: 0,
        name: '',
        phone: '',
        pickup_point: '',
        gmaps: '',
        pool_id: 0,
    });
    const unitCategoryOptions = ['Minibus', 'Mediumbus', 'Bigbus'] as const;
    type UnitCategory = (typeof unitCategoryOptions)[number];
    type UnitForm = {
        id: number;
        nama_model: string;
        pool_id: number;
        category: UnitCategory;
        kapasitas: number;
        status: string;
        layout: string;
    };
    const defaultUnitCategory = unitCategoryOptions[0];
    const normalizeUnitCategory = (
        value: string | null | undefined,
    ): UnitCategory => {
        const normalized = String(value ?? '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '');

        if (normalized === 'mediumbus') {
            return 'Mediumbus';
        }

        if (normalized === 'bigbus' || normalized === 'bigbun') {
            return 'Bigbus';
        }

        if (normalized === 'minibus') {
            return 'Minibus';
        }

        return defaultUnitCategory;
    };
    let unitForm = $state<UnitForm>({
        id: 0,
        nama_model: '',
        pool_id: 0,
        category: defaultUnitCategory,
        kapasitas: 0,
        status: 'Aktif',
        layout: '',
    });
    let armadaForm = $state({
        id: 0,
        pool_id: 0,
        merk: '',
        tahun: '',
        warna: '',
        nopol: '',
        nomor_rangka: '',
        kategori: '',
        ac_type: 'AC',
        platform_gps: '',
        api_gps: '',
        revenue: '',
        bop: '',
        fixed_cost: '',
        target_bulanan: '',
    });
    let poolForm = $state({
        id: 0,
        name: '',
        code: '',
        target_revenue: '',
        fixed_cost: '',
        status: 'active',
        notes: '',
        route_ids: [] as number[],
    });
    let userForm = $state({
        id: 0,
        name: '',
        email: '',
        password: '',
        is_super_admin: false,
        pool_ids: [] as number[],
        role_ids: [] as number[],
    });

    let customerSearch = $state('');
    let customerFiltersExpanded = $state(false);
    let driverSearch = $state('');
    let driverUnitSearch = $state('');
    let armadaSearch = $state('');
    let armadaCategories = $state<string[]>([]);
    let armadaTemplateSearch = $state('');
    let armadaTemplateLookupOpen = $state(false);
    let armadaTemplateBlurTimer: ReturnType<typeof setTimeout> | null = null;
    let armadaViewId = $state<number>(0);
    let layoutUnitId = $state<number>(0);
    let layoutTemplateSearch = $state('');
    let layoutTemplateChoice = $state('');
    let armadaDetail = $state<ArmadaRow | null>(null);
    let poolSearch = $state('');
    let userSearch = $state('');
    let userFiltersExpanded = $state(false);
    const today = new Date().toISOString().slice(0, 10);
    let reportFrom = $state(today);
    let reportTo = $state(today);
    let reportType = $state<ReportKind>('booking');
    let reportPoolId = $state(0);
    let reportRouteId = $state(0);
    let scheduleTimeInput = $state<HTMLInputElement | null>(null);
    let scheduleTimePicker: FlatpickrInstance | null = null;
    let reportFromInput = $state<HTMLInputElement | null>(null);
    let reportToInput = $state<HTMLInputElement | null>(null);
    let reportFromPicker: FlatpickrInstance | null = null;
    let reportToPicker: FlatpickrInstance | null = null;
    let reportSummary = $state<ReportSummary | null>(null);
    let reportRows = $state<ReportRow[]>([]);
    let reportMeta = $state<Pagination>({
        page: 1,
        per_page: 50,
        total: 0,
        last_page: 1,
    });
    let reportLoading = $state(false);
    let layoutEditorBusy = $state(false);
    let layoutEditorMessage = $state('');
    let layoutUnit = $state<UnitRow | null>(null);
    let seatLayoutDraft = $state<LayoutGrid>([]);
    let ReportsPanelComponent = $state<any>(null);
    let UnitsLayoutPanelComponent = $state<any>(null);
    let ArmadasPanelComponent = $state<any>(null);

    const ensureReportsPanelLoaded = async () => {
        if (!ReportsPanelComponent) {
            ReportsPanelComponent = (
                await import('@/components/admin-ops/AdminOpsReportsPanel.svelte')
            ).default;
        }
    };

    const ensureUnitsLayoutPanelLoaded = async () => {
        if (!UnitsLayoutPanelComponent) {
            UnitsLayoutPanelComponent = (
                await import('@/components/admin-ops/AdminOpsUnitsLayoutPanel.svelte')
            ).default;
        }
    };

    const ensureArmadasPanelLoaded = async () => {
        if (!ArmadasPanelComponent) {
            ArmadasPanelComponent = (
                await import('@/components/admin-ops/AdminOpsArmadasPanel.svelte')
            ).default;
        }
    };

    $effect(() => {
        if (activeTab === 'reports') {
            void ensureReportsPanelLoaded();
        }

        if (activeTab === 'units' && activeMode === 'layout') {
            void ensureUnitsLayoutPanelLoaded();
        }

        if (activeTab === 'armadas' && activeMode !== 'form') {
            void ensureArmadasPanelLoaded();
        }
    });

    $effect(() => {
        try {
            if (typeof window !== 'undefined') {
                window.dispatchEvent(
                    new CustomEvent('set-theme', {
                        detail: { theme: null },
                    }),
                );
            }
        } catch {
            // ignore
        }
    });

    const collectScheduleRouteNames = (
        routeRows: RouteRow[],
        scheduleRows: ScheduleRow[],
    ): string[] => {
        const names = new Set<string>();
        routeRows.forEach((row) => {
            const name = String(row.name ?? '').trim();

            if (name !== '') {
                names.add(name);
            }
        });
        scheduleRows.forEach((row) => {
            const name = String(row.route_name ?? row.rute ?? '').trim();

            if (name !== '') {
                names.add(name);
            }
        });

        return Array.from(names).sort((a, b) => a.localeCompare(b, 'id'));
    };
    const scheduleRouteValue = (routeId: number, routeName: string) =>
        Number(routeId || 0) > 0
            ? `id:${Number(routeId)}`
            : `name:${scheduleRouteKey(routeName)}`;
    const scheduleRouteBucketKey = (routeId: number, routeName: string) =>
        scheduleRouteValue(routeId, routeName);
    const createScheduleDayGroup = (dow: number): ScheduleDayGroup => ({
        dow,
        rows: [],
        totalUnits: 0,
        bopTotal: 0,
        firstDeparture: null,
        lastDeparture: null,
    });
    const emptyScheduleDayGroups = Array.from({ length: 7 }, (_, dow) =>
        createScheduleDayGroup(dow),
    );
    const updateScheduleWindow = (
        current: string | null,
        next: string,
        direction: 'min' | 'max',
    ) => {
        if (next === '') {
            return current;
        }

        if (current === null) {
            return next;
        }

        return direction === 'min'
            ? current.localeCompare(next) <= 0
                ? current
                : next
            : current.localeCompare(next) >= 0
              ? current
              : next;
    };
    const collectScheduleRouteSelectOptions = (
        routeRows: RouteRow[],
        scheduleRows: ScheduleRow[],
    ): ScheduleRouteSelectOption[] => {
        const options: ScheduleRouteSelectOption[] = [];
        const seen = new Set<string>();
        const pushOption = (routeId: number, routeName: string) => {
            const name = String(routeName ?? '').trim();

            if (name === '') {
                return;
            }

            const id = Number(routeId || 0);
            const value = scheduleRouteValue(id, name);
            const key = id > 0 ? value : `name:${scheduleRouteKey(name)}`;

            if (seen.has(key)) {
                return;
            }

            seen.add(key);
            options.push({ id, name, value });
        };

        routeRows.forEach((row) => pushOption(Number(row.id ?? 0), row.name));
        scheduleRows.forEach((row) =>
            pushOption(
                Number(row.route_id ?? 0),
                String(row.route_name ?? row.rute ?? ''),
            ),
        );

        return options.sort((a, b) => a.name.localeCompare(b.name, 'id'));
    };
    const scheduleRouteKey = (value: string | null | undefined) =>
        String(value ?? '')
            .trim()
            .toUpperCase()
            .replace(/=>|->|\u2192|\u2013|\u2014/g, '-')
            .replace(/\s+/g, '');
    const scheduleRouteNameLookup = $derived.by(() => {
        const lookup = new Map<number, string>();

        routes.forEach((row) => {
            const id = Number(row.id || 0);
            const name = String(row.name ?? '').trim();

            if (id > 0 && name !== '' && !lookup.has(id)) {
                lookup.set(id, name);
            }
        });

        schedules.forEach((row) => {
            const id = Number(row.route_id ?? 0);
            const name = String(row.route_name ?? row.rute ?? '').trim();

            if (id > 0 && name !== '' && !lookup.has(id)) {
                lookup.set(id, name);
            }
        });

        return lookup;
    });
    const scheduleRouteIdLookup = $derived.by(() => {
        const lookup = new Map<string, number>();
        const remember = (routeName: string, routeId: number) => {
            const key = scheduleRouteKey(routeName);
            const id = Number(routeId || 0);

            if (key !== '' && id > 0 && !lookup.has(key)) {
                lookup.set(key, id);
            }
        };

        routes.forEach((row) => remember(String(row.name ?? ''), Number(row.id)));
        schedules.forEach((row) =>
            remember(
                String(row.route_name ?? row.rute ?? ''),
                Number(row.route_id ?? 0),
            ),
        );

        return lookup;
    });
    const scheduleRouteCanonicalNameLookup = $derived.by(() => {
        const lookup = new Map<string, string>();
        const remember = (routeName: string) => {
            const name = String(routeName ?? '').trim();
            const key = scheduleRouteKey(name);

            if (key !== '' && name !== '' && !lookup.has(key)) {
                lookup.set(key, name);
            }
        };

        routes.forEach((row) => remember(String(row.name ?? '')));
        schedules.forEach((row) =>
            remember(String(row.route_name ?? row.rute ?? '')),
        );

        return lookup;
    });
    const scheduleRouteNameById = (routeId: number): string =>
        String(scheduleRouteNameLookup.get(Number(routeId || 0)) ?? '').trim();
    const scheduleRouteIdByName = (routeName: string): number => {
        const key = scheduleRouteKey(routeName);

        if (key === '') {
            return 0;
        }

        return Number(scheduleRouteIdLookup.get(key) ?? 0);
    };
    const canonicalScheduleRouteName = (
        routeName: string,
        routeId = 0,
    ): string => {
        const nameById = scheduleRouteNameById(routeId);

        if (nameById !== '') {
            return nameById;
        }

        const key = scheduleRouteKey(routeName);

        return String(
            scheduleRouteCanonicalNameLookup.get(key) ?? routeName,
        ).trim();
    };
    const findScheduleRouteOption = (
        routeName: string,
        routeId = 0,
    ): ScheduleRouteSelectOption | null => {
        const normalizedId = Number(routeId || 0);

        if (normalizedId > 0) {
            const optionById = scheduleRouteSelectOptions.find(
                (option) => Number(option.id) === normalizedId,
            );

            if (optionById) {
                return optionById;
            }
        }

        const key = scheduleRouteKey(
            canonicalScheduleRouteName(routeName, normalizedId) || routeName,
        );

        if (key === '') {
            return null;
        }

        return (
            scheduleRouteSelectOptions.find(
                (option) => scheduleRouteKey(option.name) === key,
            ) ?? null
        );
    };
    const formatScheduleWindow = (
        firstDeparture: string | null,
        lastDeparture: string | null,
    ) => {
        if (firstDeparture && lastDeparture) {
            return firstDeparture === lastDeparture
                ? firstDeparture
                : `${firstDeparture} - ${lastDeparture}`;
        }

        return firstDeparture ?? lastDeparture ?? 'Belum diatur';
    };

    const scheduleRouteOptions = $derived(
        collectScheduleRouteNames(routes, schedules),
    );
    const scheduleRouteSelectOptions = $derived(
        collectScheduleRouteSelectOptions(routes, schedules),
    );
    const selectedScheduleRouteValue = $derived(
        selectedScheduleRoute !== '' || selectedScheduleRouteId > 0
            ? scheduleRouteValue(
                  selectedScheduleRouteId,
                  selectedScheduleRoute,
              )
            : '',
    );
    const scheduleRouteGroups = $derived.by<ScheduleRouteGroup[]>(() => {
        const groups = new Map<string, ScheduleRouteGroup>();

        for (const row of schedules) {
            const routeId = Number(
                row.route_id ??
                    scheduleRouteIdByName(row.route_name ?? row.rute),
            );
            const routeName = canonicalScheduleRouteName(
                String(row.route_name ?? row.rute ?? ''),
                routeId,
            );

            if (routeName === '') {
                continue;
            }

            const key = scheduleRouteBucketKey(routeId, routeName);
            const jam = String(row.jam ?? '').slice(0, 5);
            const units = Number(row.units || 0);
            const bop = Number(row.bop || 0);
            const dayIndex = Math.max(0, Math.min(6, Number(row.dow ?? 0)));
            let group = groups.get(key);

            if (!group) {
                group = {
                    key,
                    routeId,
                    route: routeName,
                    total: 0,
                    totalUnits: 0,
                    bopTotal: 0,
                    activeDays: 0,
                    firstDeparture: null,
                    lastDeparture: null,
                    days: Array.from({ length: 7 }, (_, dow) =>
                        createScheduleDayGroup(dow),
                    ),
                };
                groups.set(key, group);
            }

            const day = group.days[dayIndex];
            day.rows.push(row);
            day.totalUnits += units;
            day.bopTotal += bop;
            day.firstDeparture = updateScheduleWindow(
                day.firstDeparture,
                jam,
                'min',
            );
            day.lastDeparture = updateScheduleWindow(
                day.lastDeparture,
                jam,
                'max',
            );

            group.total += 1;
            group.totalUnits += units;
            group.bopTotal += bop;
            group.firstDeparture = updateScheduleWindow(
                group.firstDeparture,
                jam,
                'min',
            );
            group.lastDeparture = updateScheduleWindow(
                group.lastDeparture,
                jam,
                'max',
            );
        }

        return Array.from(groups.values())
            .map((group) => ({
                ...group,
                activeDays: group.days.filter((day) => day.rows.length > 0)
                    .length,
                days: group.days.map((day) => ({
                    ...day,
                    rows: day.rows
                        .slice()
                        .sort((a, b) => a.jam.localeCompare(b.jam)),
                })),
            }))
            .sort((a, b) => a.route.localeCompare(b.route, 'id'));
    });
    const activeScheduleGroup = $derived.by(() => {
        const selected = findScheduleRouteOption(
            selectedScheduleRoute,
            selectedScheduleRouteId,
        );

        if (!selected) {
            return null;
        }

        const key = scheduleRouteBucketKey(selected.id, selected.name);

        return (
            scheduleRouteGroups.find((group) => group.key === key) ?? null
        );
    });
    const scheduleOverview = $derived.by<ScheduleOverview>(() => {
        const summary: ScheduleOverview = {
            routes: scheduleRouteGroups.length,
            total: 0,
            totalUnits: 0,
            bopTotal: 0,
            activeDays: 0,
            firstDeparture: null,
            lastDeparture: null,
        };

        for (const group of scheduleRouteGroups) {
            summary.total += group.total;
            summary.totalUnits += group.totalUnits;
            summary.bopTotal += group.bopTotal;
            summary.activeDays += group.activeDays;
            summary.firstDeparture = updateScheduleWindow(
                summary.firstDeparture,
                group.firstDeparture ?? '',
                'min',
            );
            summary.lastDeparture = updateScheduleWindow(
                summary.lastDeparture,
                group.lastDeparture ?? '',
                'max',
            );
        }

        return summary;
    });
    const scheduleSummary = $derived.by<
        ScheduleRouteGroup | ScheduleOverview
    >(() => activeScheduleGroup ?? scheduleOverview);
    const selectedSegmentRoute = $derived(
        routes.find((route) => route.id === Number(selectedSegmentRouteId)) ??
            null,
    );
    const poolOptions = $derived(
        pools.filter((pool) => String(pool.status ?? 'active') === 'active'),
    );
    const activePoolId = $derived(
        Number((page.props.auth as any)?.active_pool?.id ?? 0),
    );
    const activePoolName = $derived(
        String((page.props.auth as any)?.active_pool?.name ?? 'Semua Pool'),
    );
    const isAllPoolMode = $derived(activePoolId <= 0);
    const roleOptions = $derived(roles);

    const defaultPoolId = () => (activePoolId > 0 ? activePoolId : 0);

    const poolNameById = (poolId: number | null | undefined) => {
        const id = Number(poolId || 0);

        if (id <= 0) {
            return activePoolName;
        }

        return (
            poolOptions.find((pool) => Number(pool.id) === id)?.name ??
            'Pool'
        );
    };

    const rowPoolName = (row: { pool_id?: number | null; pool_name?: string | null }) =>
        String(row.pool_name ?? poolNameById(row.pool_id)).trim() ||
        'Semua Pool';

    const poolPayloadValue = (poolId: number) =>
        Number(poolId || 0) > 0 ? Number(poolId) : undefined;

    const formatPoolRoutes = (pool: PoolRow) => {
        const names = Array.isArray(pool.route_names) ? pool.route_names : [];

        return names.length > 0 ? names.join(', ') : 'Belum ada rute';
    };

    const togglePoolRoute = (routeId: number, checked: boolean) => {
        const id = Number(routeId || 0);

        if (id <= 0) {
            return;
        }

        const current = Array.isArray(poolForm.route_ids)
            ? poolForm.route_ids
            : [];
        poolForm = {
            ...poolForm,
            route_ids: checked
                ? Array.from(new Set([...current, id]))
                : current.filter((item) => Number(item) !== id),
        };
    };

    const toggleUserPool = (poolId: number, checked: boolean) => {
        const id = Number(poolId || 0);

        if (id <= 0) {
            return;
        }

        const current = Array.isArray(userForm.pool_ids)
            ? userForm.pool_ids
            : [];
        userForm = {
            ...userForm,
            pool_ids: checked
                ? Array.from(new Set([...current, id]))
                : current.filter((item) => Number(item) !== id),
        };
    };

    const toggleUserRole = (roleId: number, checked: boolean) => {
        const id = Number(roleId || 0);

        if (id <= 0) {
            return;
        }

        const current = Array.isArray(userForm.role_ids)
            ? userForm.role_ids
            : [];
        userForm = {
            ...userForm,
            role_ids: checked
                ? Array.from(new Set([...current, id]))
                : current.filter((item) => Number(item) !== id),
        };
    };
    const armadaCategoryOptions = $derived.by<string[]>(() => {
        const categories = [...unitCategoryOptions];

        for (const category of armadaCategories) {
            const value = normalizeUnitCategory(category);

            if (
                !categories.includes(
                    value as (typeof unitCategoryOptions)[number],
                )
            ) {
                categories.push(value as (typeof unitCategoryOptions)[number]);
            }
        }

        return categories;
    });
    const driversColumns = [
        { key: 'nama', label: 'Nama Driver', width: 'w-[150px]', sticky: 'left' },
        { key: 'phone', label: 'Kontak', width: 'w-[120px]' },
        { key: 'nopol', label: 'Nopol Unit', width: 'w-[130px]' },
        { key: 'pool', label: 'Pool', width: 'w-[130px]' },
        { key: 'charter_revenue', label: 'Charter', align: 'right', numeric: true },
        { key: 'departure_revenue', label: 'Keberangkatan', align: 'right', numeric: true },
        { key: 'luggage_revenue', label: 'Bagasi', align: 'right', numeric: true },
        { key: 'revenue', label: 'Total Revenue', align: 'right', numeric: true },
        { key: 'charter_bop', label: 'Charter BOP', align: 'right', numeric: true },
        { key: 'departure_bop', label: 'Keberangkatan BOP', align: 'right', numeric: true },
        { key: 'bop', label: 'Total BOP', align: 'right', numeric: true },
        { key: 'gross', label: 'Gross', align: 'right', numeric: true },
        { key: 'fixed_cost', label: 'Fixed Cost', align: 'right', numeric: true },
        { key: 'net', label: 'Net Margin', align: 'right', numeric: true },
        { key: 'target_revenue_bulanan', label: 'Target Revenue', align: 'right', numeric: true },
        { key: 'achievement', label: 'Achievement', align: 'right', numeric: true },
        { key: 'status', label: 'Status', align: 'center' },
    ];

    const driverColumnOptions = [
        { key: 'phone', label: 'Kontak' },
        { key: 'nopol', label: 'Nopol Unit' },
        { key: 'pool', label: 'Pool' },
        { key: 'charter_revenue', label: 'Charter' },
        { key: 'departure_revenue', label: 'Keberangkatan' },
        { key: 'luggage_revenue', label: 'Bagasi' },
        { key: 'revenue', label: 'Total Revenue' },
        { key: 'charter_bop', label: 'Charter BOP' },
        { key: 'departure_bop', label: 'Keberangkatan BOP' },
        { key: 'bop', label: 'Total BOP' },
        { key: 'gross', label: 'Gross' },
        { key: 'fixed_cost', label: 'Fixed Cost' },
        { key: 'net', label: 'Net Margin' },
        { key: 'target_revenue_bulanan', label: 'Target Revenue' },
        { key: 'achievement', label: 'Achievement' },
        { key: 'status', label: 'Status' },
    ] as const;

    type DriverColumnKey = (typeof driverColumnOptions)[number]['key'];

    let driverVisibleColumnKeys = $state<DriverColumnKey[]>(
        driverColumnOptions.map((column) => column.key),
    );
    let expandedDriverRowIds = $state<number[]>([]);

    const visibleDriverColumns = $derived(
        driversColumns.filter(
            (column) =>
                column.key === 'nama' ||
                driverVisibleColumnKeys.includes(column.key as DriverColumnKey),
        ),
    );
    const hiddenDriverColumnCount = $derived(
        driverColumnOptions.length - driverVisibleColumnKeys.length,
    );
    const toggleDriverColumn = (key: DriverColumnKey) => {
        driverVisibleColumnKeys = driverVisibleColumnKeys.includes(key)
            ? driverVisibleColumnKeys.filter((item) => item !== key)
            : [...driverVisibleColumnKeys, key];
    };
    const resetDriverColumns = () => {
        driverVisibleColumnKeys = driverColumnOptions.map((column) => column.key);
    };
    const isDriverExpanded = (rowId: number) =>
        expandedDriverRowIds.includes(Number(rowId || 0));
    const toggleDriverDetail = (rowId: number) => {
        const id = Number(rowId || 0);

        if (id <= 0) {
            return;
        }

        expandedDriverRowIds = expandedDriverRowIds.includes(id)
            ? expandedDriverRowIds.filter((item) => item !== id)
            : [...expandedDriverRowIds, id];
    };

    const poolsColumns = [
        { key: 'name', label: 'Pool', width: 'w-[150px]', sticky: 'left' },
        { key: 'routes', label: 'Rute', width: 'w-[200px]', sticky: 'left' },
        { key: 'charter_revenue', label: 'Charter', align: 'right', numeric: true },
        { key: 'departure_revenue', label: 'Keberangkatan', align: 'right', numeric: true },
        { key: 'luggage_revenue', label: 'Bagasi', align: 'right', numeric: true },
        { key: 'revenue', label: 'Total Revenue', align: 'right', numeric: true },
        { key: 'charter_bop', label: 'Charter BOP', align: 'right', numeric: true },
        { key: 'departure_bop', label: 'Keberangkatan BOP', align: 'right', numeric: true },
        { key: 'bop', label: 'Total BOP', align: 'right', numeric: true },
        { key: 'gross', label: 'Gross', align: 'right', numeric: true },
        { key: 'fixed_cost', label: 'Fixed Cost', align: 'right', numeric: true },
        { key: 'net', label: 'Net Margin', align: 'right', numeric: true },
        { key: 'target_revenue', label: 'Target Revenue', align: 'right', numeric: true },
        { key: 'achievement', label: 'Achievement', align: 'right', numeric: true },
        { key: 'status', label: 'Status', align: 'center' },
    ];
    const routesColumns = [
        { key: 'name', label: 'Rute Induk', width: 'w-[220px]', sticky: 'left' },
        { key: 'direction', label: 'Arah Perjalanan', width: 'w-[300px]', sticky: 'left' },
        { key: 'charter_revenue', label: 'Charter', align: 'right', numeric: true },
        { key: 'departure_revenue', label: 'Keberangkatan', align: 'right', numeric: true },
        { key: 'luggage_revenue', label: 'Bagasi', align: 'right', numeric: true },
        { key: 'revenue', label: 'Total Revenue', align: 'right', numeric: true },
        { key: 'charter_bop', label: 'Charter BOP', align: 'right', numeric: true },
        { key: 'departure_bop', label: 'Keberangkatan BOP', align: 'right', numeric: true },
        { key: 'bop', label: 'Total BOP', align: 'right', numeric: true },
        { key: 'gross', label: 'Gross', align: 'right', numeric: true },
        { key: 'fixed_cost', label: 'Fixed Cost', align: 'right', numeric: true },
        { key: 'net', label: 'Net Margin', align: 'right', numeric: true },
        { key: 'target_revenue', label: 'Target Revenue', align: 'right', numeric: true },
        { key: 'achievement', label: 'Achievement', align: 'right', numeric: true },
        { key: 'status', label: 'Status', align: 'center' },
    ];
    const filteredArmadaTemplateOptions = $derived.by<UnitRow[]>(() => {
        const keyword = armadaTemplateSearch.trim().toLowerCase();
        const selectedPoolId = Number(armadaForm.pool_id || 0);

        return units
            .filter((unit) =>
                selectedPoolId > 0
                    ? Number(unit.pool_id ?? 0) === selectedPoolId
                    : true,
            )
            .filter((unit) => {
                const nopol = String(unit.nopol ?? '').toLowerCase();
                const category = String(unit.category ?? '').toLowerCase();
                const merek = String(unit.merek ?? '').toLowerCase();
                const type = String(unit.type ?? '').toLowerCase();

                return keyword === ''
                    ? true
                    : nopol.includes(keyword) ||
                          category.includes(keyword) ||
                          merek.includes(keyword) ||
                          type.includes(keyword);
            })
            .slice(0, 12);
    });
    const driverUnitOptions = $derived.by<ArmadaRow[]>(() => {
        const keyword = driverUnitSearch.trim().toLowerCase();
        const selectedPoolId = Number(driverForm.pool_id || 0);
        const rows = armadas.filter(
            (armada) => String(armada.nopol ?? '').trim() !== '',
        ).filter((armada) =>
            selectedPoolId > 0
                ? Number(armada.pool_id ?? 0) === selectedPoolId
                : true,
        );

        if (keyword === '') {
            return rows.slice(0, 12);
        }

        return rows
            .filter((armada) => {
                const nopol = String(armada.nopol ?? '').toLowerCase();
                const category = String(armada.kategori ?? '').toLowerCase();
                const merek = String(armada.merk ?? '').toLowerCase();
                const rangka = String(armada.nomor_rangka ?? '').toLowerCase();

                return (
                    nopol.includes(keyword) ||
                    category.includes(keyword) ||
                    merek.includes(keyword) ||
                    rangka.includes(keyword)
                );
            })
            .slice(0, 12);
    });
    const selectedDriverUnit = $derived(
        armadas.find(
            (armada) =>
                armada.id === Number(driverForm.armada_id || 0) &&
                (Number(driverForm.pool_id || 0) <= 0 ||
                    Number(armada.pool_id ?? 0) ===
                        Number(driverForm.pool_id || 0)),
        ) ?? null,
    );
    const layoutTemplateOptions = $derived.by<UnitRow[]>(() => {
        const keyword = layoutTemplateSearch.trim().toLowerCase();
        const rows = units.filter(
            (unit) => Number(unit.id) !== Number(layoutUnitId),
        );

        if (keyword === '') {
            return rows.slice(0, 80);
        }

        return rows
            .filter((unit) => {
                const nopol = String(unit.nopol ?? '').toLowerCase();
                const category = String(unit.category ?? '').toLowerCase();
                const merek = String(unit.merek ?? '').toLowerCase();
                const type = String(unit.type ?? '').toLowerCase();

                return (
                    nopol.includes(keyword) ||
                    category.includes(keyword) ||
                    merek.includes(keyword) ||
                    type.includes(keyword)
                );
            })
            .slice(0, 80);
    });
    const formatCurrency = (value: number) => formatCurrencyDisplay(value);
    const parseRupiahInput = parseSharedCurrencyInput;
    const formatRupiahInput = formatSharedCurrencyInput;
    type FinancialRow = {
        revenue: number;
        bop: number;
        fixed_cost: number;
        target_revenue?: number;
        target_bulanan?: number;
        target_revenue_bulanan?: number;
    };
    const financialGrossMargin = (row: FinancialRow) =>
        Number(row.revenue || 0) - Number(row.bop || 0);
    const financialNetMargin = (row: FinancialRow) =>
        financialGrossMargin(row) - Number(row.fixed_cost || 0);
    const financialTarget = (row: FinancialRow) =>
        Number(
            row.target_revenue ??
                row.target_bulanan ??
                row.target_revenue_bulanan ??
                0,
        );
    const financialAchievement = (row: FinancialRow) => {
        const target = financialTarget(row);

        if (target <= 0) {
            return 0;
        }

        return (Number(row.revenue || 0) / target) * 100;
    };
    const financialStatus = (row: FinancialRow) =>
        financialAchievement(row) >= 100 ? 'Tercapai' : 'Kurang';
    const armadaGrossMargin = (row: ArmadaRow) =>
        financialGrossMargin(row);
    const armadaNetMargin = (row: ArmadaRow) =>
        financialNetMargin(row);
    const armadaAchievement = (row: ArmadaRow) => financialAchievement(row);
    const armadaStatus = (row: ArmadaRow) =>
        financialStatus(row);
    const driverGrossMargin = (row: DriverRow) =>
        financialGrossMargin(row);
    const driverNetMargin = (row: DriverRow) =>
        financialNetMargin(row);
    const driverAchievement = (row: DriverRow) => financialAchievement(row);
    const driverStatus = (row: DriverRow) =>
        financialStatus(row);
    const selectDriverUnit = (armada: ArmadaRow) => {
        driverForm.armada_id = Number(armada.id);
        driverUnitSearch = String(armada.nopol ?? '');
    };
    const syncDriverUnitSearch = () => {
        const keyword = driverUnitSearch.trim().toLowerCase();

        if (keyword === '') {
            driverForm.armada_id = 0;

            return;
        }

        const exact = armadas.find(
            (armada) =>
                String(armada.nopol ?? '')
                    .trim()
                    .toLowerCase() === keyword,
        );

        driverForm.armada_id = exact ? Number(exact.id) : 0;
    };

    const isTabName = (value: string | null): value is TabName => {
        return value !== null && tabs.includes(value as TabName);
    };

    const syncTabQuery = (tab: TabName) => {
        if (typeof window === 'undefined') {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState(
            {},
            '',
            `${url.pathname}?${url.searchParams.toString()}`,
        );
    };

    const setTab = async (tab: TabName) => {
        if (!canOpenTab(tab)) {
            return;
        }

        activeTab = tab;
        activeMode = 'data';
        syncTabQuery(tab);
        await loadActiveTab();
    };

    const hasFormTab = (tab: TabName) =>
        !['cancellations', 'reports'].includes(tab);
    const tabWritePermission: Record<TabName, string | string[] | null> = {
        routes: 'master.manage',
        schedules: 'master.manage',
        drivers: 'driver.manage',
        services: 'master.manage',
        segments: 'master.manage',
        customers: ['customer.create', 'customer.update'],
        units: 'master.manage',
        armadas: 'armada.manage',
        pools: 'pool.manage',
        users: 'user.manage',
        cancellations: null,
        reports: null,
    };
    const canWriteTab = (tab: TabName) =>
        hasPermission(permissions, tabWritePermission[tab]);
    const setFormMode = (mode: ViewMode) => {
        if (
            activeTab === 'armadas' &&
            activeMode === 'view' &&
            mode === 'data'
        ) {
            router.visit('/admin-ops/armadas', {
                preserveScroll: true,
                preserveState: false,
            });

            return;
        }

        if (
            activeTab === 'units' &&
            activeMode === 'layout' &&
            mode === 'data'
        ) {
            router.visit('/admin-ops/units', {
                preserveScroll: true,
                preserveState: false,
            });

            return;
        }

        activeMode = mode;
    };
    const openCreateForm = () => {
        message = '';
        error = '';

        if (activeTab === 'routes') {
            resetRouteForm();
        }

        if (activeTab === 'schedules') {
            resetScheduleForm();
        }

        if (activeTab === 'drivers') {
            resetDriverForm();
        }

        if (activeTab === 'services') {
            resetServiceForm();
        }

        if (activeTab === 'segments') {
            resetSegmentForm();
        }

        if (activeTab === 'customers') {
            resetCustomerForm();
        }

        if (activeTab === 'units') {
            resetUnitForm();
        }

        if (activeTab === 'armadas') {
            resetArmadaForm();
        }

        if (activeTab === 'pools') {
            resetPoolForm();
        }

        if (activeTab === 'users') {
            resetUserForm();
        }

        activeMode = 'form';
    };

    const destroyScheduleTimePicker = () => {
        scheduleTimePicker?.destroy();
        scheduleTimePicker = null;
    };

    const destroyReportPickers = () => {
        reportFromPicker?.destroy();
        reportFromPicker = null;
        reportToPicker?.destroy();
        reportToPicker = null;
    };

    const initScheduleTimePicker = async () => {
        if (
            typeof window === 'undefined' ||
            !scheduleTimeInput ||
            scheduleTimePicker
        ) {
            return;
        }

        const flatpickr = await loadFlatpickr();

        if (!scheduleTimeInput || scheduleTimePicker) {
            return;
        }

        scheduleTimePicker = flatpickr(scheduleTimeInput, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            disableMobile: true,
            defaultDate: scheduleForm.jam || '08:00',
            onChange: (_selectedDates, dateStr) => {
                scheduleForm.jam = dateStr || '08:00';
            },
        });
    };

    const initReportPickers = async () => {
        if (typeof window === 'undefined') {
            return;
        }

        await tick();

        if (!reportFromInput || !reportToInput) {
            return;
        }

        const flatpickr = await loadFlatpickr();

        if (reportFromInput && !reportFromPicker) {
            reportFromPicker = flatpickr(reportFromInput, {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                defaultDate: reportFrom || today,
                onChange: (_selectedDates, dateStr) => {
                    reportFrom = dateStr || today;
                },
            });
        }

        if (reportToInput && !reportToPicker) {
            reportToPicker = flatpickr(reportToInput, {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                defaultDate: reportTo || today,
                onChange: (_selectedDates, dateStr) => {
                    reportTo = dateStr || today;
                },
            });
        }
    };

    const csrfToken = () => {
        const node = document.querySelector(
            'meta[name="csrf-token"]',
        ) as HTMLMetaElement | null;

        return node?.content ?? '';
    };

    const xsrfTokenFromCookie = () => {
        if (typeof document === 'undefined') {
            return '';
        }

        const part = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='));

        if (!part) {
            return '';
        }

        try {
            return decodeURIComponent(part.split('=')[1] ?? '');
        } catch {
            return '';
        }
    };

    const refreshCsrfToken = async () => {
        if (typeof window === 'undefined') {
            return false;
        }

        try {
            const response = await fetch(window.location.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    Accept: 'text/html',
                },
            });
            const html = await response.text();
            const match = html.match(
                /<meta\s+name=["']csrf-token["']\s+content=["']([^"']+)["']/i,
            );

            if (!match?.[1]) {
                return false;
            }

            let node = document.querySelector(
                'meta[name="csrf-token"]',
            ) as HTMLMetaElement | null;

            if (!node) {
                node = document.createElement('meta');
                node.name = 'csrf-token';
                document.head.appendChild(node);
            }

            node.content = match[1];

            return true;
        } catch {
            return false;
        }
    };

    const sendApiRequest = async (
        method: 'GET' | 'POST' | 'DELETE',
        url: string,
        body?: Record<string, unknown>,
    ) => {
        const token = csrfToken() || xsrfTokenFromCookie();
        const isDelete = method === 'DELETE';
        const requestMethod = isDelete ? 'POST' : method;
        const payload =
            method === 'GET'
                ? body
                : {
                      ...(body ?? {}),
                      ...(isDelete ? { _method: 'DELETE' } : {}),
                      _token: token,
                  };

        return fetch(url, {
            method: requestMethod,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-XSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: payload ? JSON.stringify(payload) : undefined,
        });
    };

    const api = async (
        method: 'GET' | 'POST' | 'DELETE',
        url: string,
        body?: Record<string, unknown>,
    ) => {
        let response = await sendApiRequest(method, url, body);

        if (
            response.status === 419 &&
            method !== 'GET' &&
            (await refreshCsrfToken())
        ) {
            response = await sendApiRequest(method, url, body);
        }

        const json = await response.json().catch(() => ({}));
        const firstValidationError = (() => {
            const errors = json?.errors;

            if (!errors || typeof errors !== 'object') {
                return '';
            }

            for (const value of Object.values(errors)) {
                if (Array.isArray(value) && value.length > 0) {
                    return String(value[0] ?? '').trim();
                }
            }

            return '';
        })();

        if (!response.ok || json.success === false) {
            throw new Error(
                json.error ||
                    json.message ||
                    firstValidationError ||
                    `Request gagal (${response.status})`,
            );
        }

        return json;
    };

    const apiForm = async (url: string, formData: FormData) => {
        const send = async () => {
            const token = csrfToken() || xsrfTokenFromCookie();
            formData.set('_token', token);

            return fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-XSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
        };

        let response = await send();

        if (response.status === 419 && (await refreshCsrfToken())) {
            response = await send();
        }

        const json = (await response.json().catch(() => ({}))) as Record<
            string,
            any
        >;
        const firstValidationError = (() => {
            const errors = json?.errors;

            if (!errors || typeof errors !== 'object') {
                return '';
            }

            for (const value of Object.values(errors)) {
                if (Array.isArray(value) && value.length > 0) {
                    return String(value[0] ?? '').trim();
                }
            }

            return '';
        })();

        if (!response.ok || json.success === false) {
            throw new Error(
                json.error ||
                    json.message ||
                    firstValidationError ||
                    `Request gagal (${response.status})`,
            );
        }

        return json;
    };

    const initDefaultSeatLayout = (): LayoutGrid => {
        const grid: LayoutGrid = [];
        grid.push([
            {
                type: 'seat',
                label: '1',
                seatNumber: 1,
                fixed: false,
                marker: 'slot',
                seatStyle: 'standard',
            },
            {
                type: 'seat',
                label: '2',
                seatNumber: 2,
                fixed: false,
                marker: 'slot',
                seatStyle: 'standard',
            },
            { type: 'driver', label: 'Driver', fixed: true },
        ]);

        for (let i = 0; i < 4; i += 1) {
            grid.push([
                {
                    type: 'seat',
                    label: '',
                    fixed: false,
                    marker: 'slot',
                    seatStyle: 'standard',
                },
                {
                    type: 'seat',
                    label: '',
                    fixed: false,
                    marker: 'slot',
                    seatStyle: 'standard',
                },
                { type: 'empty', label: '', fixed: false, marker: 'aisle' },
                {
                    type: 'seat',
                    label: '',
                    fixed: false,
                    marker: 'slot',
                    seatStyle: 'standard',
                },
                {
                    type: 'seat',
                    label: '',
                    fixed: false,
                    marker: 'slot',
                    seatStyle: 'standard',
                },
            ]);
        }

        return grid;
    };

    const cloneLayout = (layout: LayoutGrid): LayoutGrid =>
        layout.map((row) => row.map((cell) => ({ ...cell })));

    const normalizeLayout = (raw: unknown): LayoutGrid => {
        if (!Array.isArray(raw) || raw.length === 0) {
            return initDefaultSeatLayout();
        }

        const normalized = raw
            .map((row) => {
                if (!Array.isArray(row) || row.length === 0) {
                    return null;
                }

                return row.map((cell): LayoutCell => {
                    const source =
                        typeof cell === 'object' && cell !== null
                            ? (cell as Record<string, unknown>)
                            : {};
                    const typeRaw = String(source.type ?? 'empty');
                    const validType: LayoutCellType = [
                        'seat',
                        'empty',
                        'driver',
                    ].includes(typeRaw)
                        ? (typeRaw as LayoutCellType)
                        : 'empty';

                    return {
                        type: validType,
                        label: String(source.label ?? ''),
                        fixed: Boolean(source.fixed),
                        hidden: Boolean(source.hidden),
                        seatNumber:
                            source.seatNumber !== undefined
                                ? Number(source.seatNumber)
                                : undefined,
                        colspan:
                            source.colspan !== undefined
                                ? Number(source.colspan)
                                : undefined,
                        marker: source.marker === 'aisle' ? 'aisle' : 'slot',
                        seatStyle:
                            source.seatStyle === 'sleeper'
                                ? 'sleeper'
                                : 'standard',
                    };
                });
            })
            .filter(
                (row): row is LayoutCell[] =>
                    Array.isArray(row) && row.length > 0,
            );

        if (normalized.length === 0) {
            return initDefaultSeatLayout();
        }

        return normalized;
    };

    const parseUnitLayout = (layout: string | null): LayoutGrid => {
        if (!layout || layout.trim() === '') {
            return initDefaultSeatLayout();
        }

        try {
            return normalizeLayout(JSON.parse(layout));
        } catch {
            return initDefaultSeatLayout();
        }
    };

    const seatCountOf = (layout: LayoutGrid): number =>
        layout.reduce(
            (total, row) =>
                total + row.filter((cell) => cell.type === 'seat').length,
            0,
        );

    const layoutSeatCount = () => seatCountOf(seatLayoutDraft);
    const unitSeatCount = (layout: string | null) =>
        seatCountOf(parseUnitLayout(layout));
    const layoutCapacity = $derived(Number(layoutUnit?.kapasitas ?? 0));
    const layoutRemainingSeats = $derived(
        Math.max(0, layoutCapacity - layoutSeatCount()),
    );
    const layoutOverCapacity = $derived(
        layoutCapacity > 0 && layoutSeatCount() > layoutCapacity,
    );
    const categoryTone = (category: string | null | undefined) => {
        const normalized = normalizeUnitCategory(category);

        if (normalized === 'Bigbus') {
            return 'border-sky-200 bg-sky-50 text-sky-700';
        }

        if (normalized === 'Mediumbus') {
            return 'border-amber-200 bg-amber-50 text-amber-700';
        }

        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    };

    const resolveSeatNumber = (cell: LayoutCell): number => {
        if (
            typeof cell.seatNumber === 'number' &&
            Number.isFinite(cell.seatNumber) &&
            cell.seatNumber > 0
        ) {
            return Math.trunc(cell.seatNumber);
        }

        const parsed = Number.parseInt(String(cell.label ?? '').trim(), 10);

        return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
    };

    const assignSeatNumber = (cell: LayoutCell, number: number) => {
        cell.label = String(number);
        cell.seatNumber = number;
    };

    const nextSeatNumber = (layout: LayoutGrid = seatLayoutDraft): number => {
        let max = 0;

        layout.forEach((row) => {
            row.forEach((cell) => {
                if (cell.type !== 'seat') {
                    return;
                }

                max = Math.max(max, resolveSeatNumber(cell));
            });
        });

        return max + 1;
    };

    const renumberSeats = () => {
        const seen: number[] = [];
        let next = nextSeatNumber();

        seatLayoutDraft.forEach((row) => {
            row.forEach((cell) => {
                if (cell.type !== 'seat') {
                    return;
                }

                const current = resolveSeatNumber(cell);

                if (current > 0 && !seen.includes(current)) {
                    assignSeatNumber(cell, current);
                    seen.push(current);

                    return;
                }

                assignSeatNumber(cell, next);
                seen.push(next);
                next += 1;
            });
        });

        seatLayoutDraft = cloneLayout(seatLayoutDraft);
    };

    const resolveDriverRow = (): LayoutCell[] => {
        const firstRow = seatLayoutDraft[0] ?? [];
        const hasDriver = firstRow.some((cell) => cell.type === 'driver');

        if (hasDriver) {
            return firstRow.map((cell) => ({ ...cell }));
        }

        return initDefaultSeatLayout()[0].map((cell) => ({ ...cell }));
    };

    const createPatternRow = (pattern: LayoutPattern): LayoutCell[] => {
        const seat = (
            seatStyle: 'standard' | 'sleeper' = 'standard',
        ): LayoutCell => ({
            type: 'seat',
            label: '',
            fixed: false,
            marker: 'slot',
            seatStyle,
        });
        const aisle = (): LayoutCell => ({
            type: 'empty',
            label: '',
            fixed: false,
            marker: 'aisle',
        });
        const slot = (): LayoutCell => ({
            type: 'empty',
            label: '',
            fixed: false,
            marker: 'slot',
        });

        if (pattern === '2-2') {
            return [seat(), seat(), aisle(), seat(), seat()];
        }

        if (pattern === '2-1') {
            return [seat(), seat(), aisle(), seat()];
        }

        if (pattern === '1-1') {
            return [seat(), aisle(), seat()];
        }

        if (pattern === '2-3') {
            return [seat(), seat(), aisle(), seat(), seat(), seat()];
        }

        if (pattern === '3-0') {
            return [seat(), seat(), seat()];
        }

        if (pattern === '4-0') {
            return [seat(), seat(), seat(), seat()];
        }

        if (pattern === 'sleep') {
            return [seat('sleeper'), aisle(), seat('sleeper')];
        }

        return [slot(), slot(), aisle(), slot(), slot()];
    };

    const applyLayoutUnit = (row: UnitRow) => {
        layoutUnitId = Number(row.id ?? 0);
        layoutUnit = { ...row, category: normalizeUnitCategory(row.category) };
        layoutTemplateChoice = '';
        layoutTemplateSearch = '';
        layoutEditorMessage = '';
        seatLayoutDraft = parseUnitLayout(row.layout);
        renumberSeats();
        activeMode = 'layout';
    };

    const openUnitEditor = (row: UnitRow) => {
        unitForm = {
            id: row.id,
            nama_model: row.nopol,
            pool_id: Number(row.pool_id ?? defaultPoolId()),
            category: normalizeUnitCategory(row.category),
            kapasitas: Number(row.kapasitas ?? 0),
            status: row.status ?? 'Aktif',
            layout: row.layout ?? '',
        };
        setFormMode('form');
    };

    const openLayoutEditor = (row: UnitRow) => {
        router.visit(`/admin-ops/units/layout/${row.id}`, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    const syncLayoutUnitById = (id: number) => {
        const unit = units.find((row) => Number(row.id) === Number(id));

        if (!unit) {
            layoutUnit = null;
            layoutEditorMessage = 'Data kategori armada tidak ditemukan.';

            return;
        }

        applyLayoutUnit(unit);
    };

    const rowSeatCount = (row: LayoutCell[]) =>
        row.filter((cell) => cell.type === 'seat').length;

    const rowPatternLabel = (row: LayoutCell[]): string => {
        const sleeperSeats = row.filter(
            (cell) => cell.type === 'seat' && cell.seatStyle === 'sleeper',
        ).length;

        if (sleeperSeats > 0) {
            return 'Sleep Seat';
        }

        const aisleCount = row.filter((cell) => cell.marker === 'aisle').length;

        if (aisleCount > 0) {
            const parts = row
                .reduce<number[]>((result, cell) => {
                    if (cell.marker === 'aisle') {
                        if (
                            result.length === 0 ||
                            result[result.length - 1] !== 0
                        ) {
                            result.push(0);
                        }

                        return result;
                    }

                    if (cell.type === 'seat') {
                        if (result.length === 0) {
                            result.push(1);
                        } else if (result[result.length - 1] === 0) {
                            result.push(1);
                        } else {
                            result[result.length - 1] += 1;
                        }
                    }

                    return result;
                }, [])
                .filter((value) => value > 0);

            if (parts.length > 1) {
                return parts.join('-');
            }
        }

        if (rowSeatCount(row) === 3) {
            return '3-0';
        }

        if (rowSeatCount(row) === 4) {
            return '4-0';
        }

        return `${rowSeatCount(row)} kursi`;
    };

    const cloneLayoutFromTemplate = (templateId: number) => {
        const template = units.find(
            (unit) => Number(unit.id) === Number(templateId),
        );

        if (!template) {
            layoutEditorMessage =
                'Template layout yang dipilih tidak ditemukan.';

            return;
        }

        seatLayoutDraft = parseUnitLayout(template.layout);
        renumberSeats();
        layoutTemplateChoice = String(templateId);
        layoutEditorMessage = `Layout disalin dari ${template.nopol}.`;
    };

    const addLayoutItem = (rowIdx: number, colIdx: number) => {
        const row = seatLayoutDraft[rowIdx];
        const cell = row?.[colIdx];

        if (
            !cell ||
            cell.fixed ||
            cell.type !== 'empty' ||
            cell.marker === 'aisle'
        ) {
            return;
        }

        const capacity = Number(layoutUnit?.kapasitas ?? 0);
        const currentSeats = layoutSeatCount();

        if (capacity > 0 && currentSeats >= capacity) {
            layoutEditorMessage = `Maksimal ${capacity} kursi sesuai kapasitas unit.`;

            return;
        }

        cell.type = 'seat';
        cell.seatStyle = cell.seatStyle === 'sleeper' ? 'sleeper' : 'standard';
        assignSeatNumber(cell, nextSeatNumber());
        seatLayoutDraft = cloneLayout(seatLayoutDraft);
        layoutEditorMessage = '';
    };

    const removeLayoutItem = (rowIdx: number, colIdx: number) => {
        const row = seatLayoutDraft[rowIdx];
        const cell = row?.[colIdx];

        if (!cell || cell.fixed || cell.type === 'empty') {
            return;
        }

        cell.type = 'empty';
        cell.label = '';
        cell.seatNumber = undefined;
        renumberSeats();
        layoutEditorMessage = '';
    };

    const addLayoutRow = (pattern: LayoutPattern = '2-2') => {
        seatLayoutDraft = [...seatLayoutDraft, createPatternRow(pattern)];
        renumberSeats();
        layoutEditorMessage = '';
    };

    const removeLayoutRow = (rowIdx?: number) => {
        let indexToRemove = typeof rowIdx === 'number' ? rowIdx : -1;

        if (indexToRemove <= 0) {
            for (let i = seatLayoutDraft.length - 1; i >= 1; i -= 1) {
                const row = seatLayoutDraft[i];

                if (row.every((cell) => cell.type !== 'driver')) {
                    indexToRemove = i;
                    break;
                }
            }
        }

        if (indexToRemove < 1) {
            layoutEditorMessage = 'Tidak ada baris yang bisa dihapus.';

            return;
        }

        seatLayoutDraft.splice(indexToRemove, 1);
        renumberSeats();
        layoutEditorMessage = '';
    };

    const replaceLayoutRowPattern = (
        rowIdx: number,
        pattern: LayoutPattern,
    ) => {
        if (rowIdx <= 0 || !seatLayoutDraft[rowIdx]) {
            return;
        }

        seatLayoutDraft[rowIdx] = createPatternRow(pattern);
        renumberSeats();
        layoutEditorMessage = '';
    };

    const duplicateLayoutRow = (rowIdx: number) => {
        if (rowIdx <= 0 || !seatLayoutDraft[rowIdx]) {
            return;
        }

        seatLayoutDraft.splice(
            rowIdx + 1,
            0,
            seatLayoutDraft[rowIdx].map((cell) => ({ ...cell })),
        );
        renumberSeats();
        layoutEditorMessage = '';
    };

    const applyPatternToAllRows = (pattern: LayoutPattern) => {
        const rowCount = Math.max(1, seatLayoutDraft.length - 1);
        const driverRow = resolveDriverRow();

        seatLayoutDraft = [
            driverRow,
            ...Array.from({ length: rowCount }, () =>
                createPatternRow(pattern),
            ),
        ];
        renumberSeats();
        layoutEditorMessage = `Semua baris kursi diubah ke pola ${pattern}.`;
    };

    const resetLayoutDraft = () => {
        seatLayoutDraft = initDefaultSeatLayout();
        renumberSeats();
        layoutEditorMessage = 'Layout direset ke template default 2-2.';
    };

    const saveUnitLayout = async () => {
        const currentLayoutUnit = layoutUnit;

        if (!currentLayoutUnit) {
            return;
        }

        const seats = layoutSeatCount();

        if (seats <= 0) {
            layoutEditorMessage = 'Tambahkan minimal 1 kursi.';

            return;
        }

        const capacity = Number(currentLayoutUnit.kapasitas ?? 0);

        if (capacity > 0 && seats > capacity) {
            layoutEditorMessage = `Jumlah kursi (${seats}) melebihi kapasitas unit (${capacity}).`;

            return;
        }

        layoutEditorBusy = true;
        layoutEditorMessage = '';

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/units', {
                        id: currentLayoutUnit.id,
                        nopol: currentLayoutUnit.nopol,
                        category: normalizeUnitCategory(
                            currentLayoutUnit.category,
                        ),
                        kapasitas: Number(currentLayoutUnit.kapasitas ?? 0),
                        status: currentLayoutUnit.status ?? 'Aktif',
                        layout: JSON.stringify(seatLayoutDraft),
                    });
                },
                {
                    loadingMessage: `Menyimpan layout ${currentLayoutUnit.nopol}...`,
                    successMessage: `Layout ${currentLayoutUnit.nopol} berhasil disimpan.`,
                    errorMessage: 'Gagal menyimpan layout unit.',
                },
            );
            layoutEditorMessage = 'Layout unit berhasil disimpan.';

            if (unitForm.id === currentLayoutUnit.id) {
                unitForm.layout = JSON.stringify(seatLayoutDraft);
            }

            await loadUnits();
            message = `Layout ${currentLayoutUnit.nopol} diperbarui.`;
        } catch (e) {
            layoutEditorMessage =
                e instanceof Error ? e.message : 'Gagal menyimpan layout unit.';
        } finally {
            layoutEditorBusy = false;
        }
    };

    const usesHybridSettings = (tab = activeTab) =>
        lockedFromServer &&
        activeTab === tab &&
        initialTab === tab &&
        isHybridSettingsTab(tab);

    const syncScheduleSelection = () => {
        const fallbackOption = scheduleRouteSelectOptions[0] ?? null;

        if (!fallbackOption) {
            selectedScheduleRoute = '';
            selectedScheduleRouteId = 0;
            return;
        }

        const selectedOption =
            findScheduleRouteOption(
                selectedScheduleRoute,
                selectedScheduleRouteId,
            ) ?? fallbackOption;
        selectedScheduleRoute = selectedOption.name;
        selectedScheduleRouteId = selectedOption.id;

        const formOption = findScheduleRouteOption(
            scheduleForm.rute,
            scheduleRouteIdByName(scheduleForm.rute),
        );
        const canonicalRoute = canonicalScheduleRouteName(
            selectedScheduleRoute,
            selectedScheduleRouteId,
        );

        scheduleForm.rute =
            formOption?.name ?? canonicalRoute ?? fallbackOption.name;
    };

    const hydrateSettingsData = (payload: SettingsDataPayload) => {
        if (!payload.tab || !isTabName(payload.tab)) {
            return;
        }

        if (payload.tab === 'schedules') {
            selectedScheduleRouteId = Number(
                payload.route_id ?? selectedScheduleRouteId,
            );
            selectedScheduleRoute = canonicalScheduleRouteName(
                payload.rute ?? selectedScheduleRoute,
                selectedScheduleRouteId,
            );
            schedules = payload.schedules ?? [];
            syncScheduleSelection();
        }

        if (payload.tab === 'drivers') {
            drivers = payload.drivers ?? [];
        }

        if (payload.tab === 'segments') {
            selectedSegmentRouteId = Number(
                payload.route_id ?? selectedSegmentRouteId,
            );
            segments = payload.segments ?? [];
        }

        if (payload.tab === 'units') {
            units = payload.units ?? [];
        }

        if (payload.tab === 'armadas') {
            armadas = payload.armadas ?? [];
        }

        if (payload.tab === 'pools') {
            pools = payload.pools ?? [];
            canManagePools = Boolean(payload.can_manage ?? true);
        }

        if (payload.tab === 'users') {
            users = payload.users ?? [];
            roles = payload.roles ?? roles;
        }

        if (payload.pagination) {
            settingsMeta = payload.pagination;
        }

        busy = false;
    };

    const hydrateSettingsMasters = (payload: SettingsMastersPayload) => {
        if (!payload.tab || !isTabName(payload.tab)) {
            return;
        }

        if (payload.tab === 'schedules') {
            routes = payload.routes ?? [];
            units = payload.units ?? [];
            syncScheduleSelection();
        }

        if (payload.tab === 'drivers') {
            armadas = payload.armadas ?? [];
            pools = payload.pools ?? pools;
        }

        if (payload.tab === 'segments') {
            routes = payload.routes ?? [];
        }

        if (payload.tab === 'armadas') {
            armadaCategories = payload.categories ?? [];
            units = payload.units ?? [];
            pools = payload.pools ?? pools;
        }

        if (payload.tab === 'units') {
            pools = payload.pools ?? pools;
        }

        if (payload.tab === 'pools') {
            routes = payload.routes ?? [];
        }

        if (payload.tab === 'users') {
            pools = payload.pools ?? [];
            roles = payload.roles ?? [];
            canManagePools = Boolean(payload.can_manage_pools ?? true);
        }
    };

    const reloadSettingsWithInertia = (
        targetPage = settingsMeta.page,
        includeMasters = false,
    ) => {
        if (typeof window === 'undefined' || !usesHybridSettings()) {
            return;
        }

        const query: Record<string, string | number> = {
            page: targetPage,
            per_page: settingsMeta.per_page,
        };

        if (activeTab === 'users' && userSearch.trim() !== '') {
            query.q = userSearch.trim();
        }

        if (activeTab === 'drivers' && driverSearch.trim() !== '') {
            query.q = driverSearch.trim();
        }

        if (activeTab === 'armadas' && armadaSearch.trim() !== '') {
            query.q = armadaSearch.trim();
        }

        if (activeTab === 'pools' && poolSearch.trim() !== '') {
            query.q = poolSearch.trim();
        }

        if (
            activeTab === 'schedules' &&
            (selectedScheduleRouteId > 0 || selectedScheduleRoute !== '')
        ) {
            const routeId =
                selectedScheduleRouteId ||
                scheduleRouteIdByName(selectedScheduleRoute);

            if (routeId > 0) {
                query.route_id = routeId;
            } else {
                query.rute = selectedScheduleRoute;
            }
        }

        if (activeTab === 'segments' && selectedSegmentRouteId > 0) {
            query.route_id = selectedSegmentRouteId;
        }

        busy = true;
        error = '';

        router.get(window.location.pathname, query, {
            only: includeMasters
                ? ['settingsData', 'settingsMasters']
                : ['settingsData'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onError: () => {
                error = 'Gagal memuat data pengaturan.';
            },
            onFinish: () => {
                busy = false;
            },
        });
    };

    $effect(() => {
        if (!settingsQuery || settingsQueryHydrated) {
            return;
        }

        if (initialTab === 'users') {
            userSearch = settingsQuery.q ?? '';
        }

        if (initialTab === 'drivers') {
            driverSearch = settingsQuery.q ?? '';
        }

        if (initialTab === 'armadas') {
            armadaSearch = settingsQuery.q ?? '';
        }

        if (initialTab === 'pools') {
            poolSearch = settingsQuery.q ?? '';
        }

        selectedScheduleRouteId = Number(settingsQuery.route_id ?? 0);
        selectedScheduleRoute = canonicalScheduleRouteName(
            settingsQuery.rute ?? '',
            selectedScheduleRouteId,
        );
        selectedSegmentRouteId = Number(settingsQuery.route_id ?? 0);
        settingsMeta = {
            ...settingsMeta,
            page: Number(settingsQuery.page ?? 1),
            per_page: Number(settingsQuery.per_page ?? 20),
        };
        settingsQueryHydrated = true;
    });

    $effect(() => {
        if (settingsData) {
            hydrateSettingsData(settingsData);
        }
    });

    $effect(() => {
        if (settingsMasters) {
            hydrateSettingsMasters(settingsMasters);
        }
    });

    const loadRoutes = async () => {
        const r = await api('GET', '/api/admin/routes');
        routes = r.routes ?? [];
    };

    const loadSchedules = async () => {
        if (usesHybridSettings('schedules')) {
            reloadSettingsWithInertia();

            return;
        }

        const [s, u, r] = await Promise.all([
            api('GET', '/api/admin/schedules'),
            api('GET', '/api/admin/units'),
            api('GET', '/api/admin/routes'),
        ]);
        schedules = s.schedules ?? [];
        units = u.units ?? [];
        routes = r.routes ?? [];

        syncScheduleSelection();
    };

    const loadDrivers = async () => {
        if (usesHybridSettings('drivers')) {
            reloadSettingsWithInertia();

            return;
        }

        const [d, armadaResponse] = await Promise.all([
            api('GET', '/api/admin/drivers'),
            api('GET', '/api/admin/armadas'),
        ]);
        drivers = d.drivers ?? [];
        armadas = armadaResponse.armadas ?? [];
    };

    const loadServices = async () => {
        const r = await api('GET', '/api/admin/luggage-services');
        services = r.services ?? [];
    };

    const loadSegments = async () => {
        if (usesHybridSettings('segments')) {
            reloadSettingsWithInertia(1);

            return;
        }

        const r = await api('GET', '/api/admin/routes');
        routes = r.routes ?? [];

        const routeExists = routes.some(
            (row) => row.id === Number(selectedSegmentRouteId),
        );

        if (!routeExists) {
            selectedSegmentRouteId = Number(routes[0]?.id ?? 0);
        }

        const activeRouteId = Number(selectedSegmentRouteId || 0);
        const segmentsUrl =
            activeRouteId > 0
                ? `/api/admin/segments?route_id=${activeRouteId}`
                : '/api/admin/segments';
        const s = await api('GET', segmentsUrl);
        segments = s.segments ?? [];

        if (Number(segmentForm.route_id || 0) <= 0) {
            segmentForm.route_id = activeRouteId;
        }
    };

    const loadCustomers = async (page = customerMeta.page) => {
        try {
            const query = customerSearch.trim();
            const params = new URLSearchParams();
            params.set('page', String(page));

            if (query !== '') {
                params.set('q', query);
            }

            const url = `/api/admin/customers?${params.toString()}`;
            const r = await api('GET', url);
            customers = r.customers ?? [];
            customerMeta = r.pagination ?? customerMeta;
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat customers.';
        }
    };

    const jumpCustomerPage = async (page: number) => {
        if (page < 1 || page > customerMeta.last_page) {
            return;
        }

        await loadCustomers(page);
    };

    const openCustomerImportPicker = () => {
        customerImportInput?.click();
    };

    const importCustomers = async (event: Event) => {
        const input = event.currentTarget as HTMLInputElement;
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        message = '';
        error = '';
        customerImportSummary = null;
        customerImporting = true;

        try {
            let result: Record<string, any> = {};
            await runWithFeedback(
                async () => {
                    const formData = new FormData();
                    formData.append('file', file);
                    result = await apiForm(
                        '/api/admin/customers/import',
                        formData,
                    );
                },
                {
                    loadingMessage: 'Mengimpor customer reguler...',
                    successMessage: 'Import customer selesai.',
                    errorMessage: 'Gagal import customer.',
                },
            );

            customerImportSummary = {
                created: Number(result.created ?? 0),
                updated: Number(result.updated ?? 0),
                skipped: Number(result.skipped ?? 0),
                errors: Array.isArray(result.errors)
                    ? result.errors.map((item: unknown) => String(item))
                    : [],
            };
            message = `Import selesai: ${customerImportSummary.created} baru, ${customerImportSummary.updated} diperbarui, ${customerImportSummary.skipped} dilewati.`;
            await loadCustomers(1);
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal import customer.';
        } finally {
            customerImporting = false;
            input.value = '';
        }
    };

    const loadUsers = async () => {
        if (usesHybridSettings('users')) {
            reloadSettingsWithInertia(1);

            return;
        }

        try {
            const query = userSearch.trim();
            const url =
                query === ''
                    ? '/api/admin/users'
                    : `/api/admin/users?q=${encodeURIComponent(query)}`;
            const [userResponse, poolResponse] = await Promise.all([
                api('GET', url),
                api('GET', '/api/admin/pools'),
            ]);
            users = userResponse.users ?? [];
            roles = userResponse.roles ?? [];
            pools = poolResponse.pools ?? [];
            canManagePools = Boolean(poolResponse.can_manage ?? true);
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat users.';
        }
    };

    const loadUnits = async () => {
        if (usesHybridSettings('units')) {
            reloadSettingsWithInertia();

            return;
        }

        const r = await api('GET', '/api/admin/units');
        units = r.units ?? [];

        if (activeMode === 'layout' && layoutUnitId > 0) {
            syncLayoutUnitById(layoutUnitId);
        }
    };

    const loadArmadas = async () => {
        if (usesHybridSettings('armadas')) {
            reloadSettingsWithInertia(1);

            return;
        }

        const query = armadaSearch.trim();
        const searchSuffix =
            query === '' ? '' : `?q=${encodeURIComponent(query)}`;
        const [armadasResponse, categoriesResponse, unitsResponse] =
            await Promise.all([
                api('GET', `/api/admin/armadas${searchSuffix}`),
                api('GET', '/api/admin/armada-categories'),
                api('GET', '/api/admin/units'),
            ]);
        armadas = armadasResponse.armadas ?? [];
        armadaCategories = categoriesResponse.categories ?? [];
        units = unitsResponse.units ?? [];

        if (
            Number(armadaForm.id || 0) === 0 &&
            armadaForm.kategori === '' &&
            armadaCategoryOptions.length > 0
        ) {
            armadaForm.kategori = armadaCategoryOptions[0];
        }
    };

    const loadArmadaDetail = async (id: number) => {
        if (id <= 0) {
            armadaDetail = null;

            return;
        }

        const r = await api('GET', `/api/admin/armadas/${id}`);
        armadaDetail = r.armada ?? null;
    };

    const loadPools = async () => {
        if (usesHybridSettings('pools')) {
            reloadSettingsWithInertia();

            return;
        }

        const r = await api('GET', '/api/admin/pools');
        pools = r.pools ?? [];
        routes = r.routes ?? routes;
        canManagePools = Boolean(r.can_manage ?? true);
    };

    const loadCancellations = async () => {
        const r = await api('GET', '/api/admin/cancellations?limit=50');
        cancellations = r.cancellations ?? [];
    };

    const loadActiveTab = async () => {
        busy = true;
        error = '';

        try {
            if (activeTab === 'routes') {
                await loadRoutes();
            }

            if (activeTab === 'schedules') {
                await loadSchedules();
            }

            if (activeTab === 'drivers') {
                await loadDrivers();
            }

            if (activeTab === 'services') {
                await loadServices();
            }

            if (activeTab === 'segments') {
                await loadSegments();
            }

            if (activeTab === 'customers') {
                await loadCustomers(customerMeta.page);
            }

            if (activeTab === 'units') {
                await loadUnits();
            }

            if (activeTab === 'armadas') {
                await loadArmadas();
            }

            if (
                activeTab === 'armadas' &&
                activeMode === 'view' &&
                armadaViewId > 0
            ) {
                await loadArmadaDetail(armadaViewId);
            }

            if (activeTab === 'pools') {
                await loadPools();
            }

            if (activeTab === 'users') {
                await loadUsers();
            }

            if (activeTab === 'cancellations') {
                await loadCancellations();
            }

            if (activeTab === 'reports') {
                await loadPools();
                await loadReport();
            }
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat data.';
        } finally {
            busy = false;
        }
    };

    const loadReport = async (page = 1) => {
        reportLoading = true;

        try {
            const url = `/api/admin/reports/summary?from=${encodeURIComponent(reportFrom)}&to=${encodeURIComponent(reportTo)}&type=${encodeURIComponent(reportType)}&pool_id=${encodeURIComponent(String(reportPoolId || 0))}&route_id=${encodeURIComponent(String(reportRouteId || 0))}&page=${encodeURIComponent(String(page))}&per_page=${encodeURIComponent(String(reportMeta.per_page || 50))}`;
            const r = await api('GET', url);
            reportSummary = r.summary ?? null;
            reportRows = Array.isArray(r.rows) ? r.rows : [];
            reportMeta = r.pagination ?? {
                page,
                per_page: reportMeta.per_page || 50,
                total: reportRows.length,
                last_page: 1,
            };
        } catch (e) {
            reportSummary = null;
            reportRows = [];
            reportMeta = {
                page: 1,
                per_page: reportMeta.per_page || 50,
                total: 0,
                last_page: 1,
            };
            error = e instanceof Error ? e.message : 'Gagal memuat report.';
        } finally {
            reportLoading = false;
        }
    };

    const jumpReportPage = async (page: number) => {
        if (page < 1 || page > reportMeta.last_page) {
            return;
        }

        await loadReport(page);
    };

    const resetRouteForm = () =>
        (routeForm = {
            id: 0,
            name: '',
            origin: '',
            destination: '',
            target_revenue: '',
            fixed_cost: '',
        });
    const editRoute = (row: RouteRow) => {
        routeForm = {
            id: row.id,
            name: row.name,
            origin: row.origin ?? '',
            destination: row.destination ?? '',
            target_revenue: formatRupiahInput(row.target_revenue),
            fixed_cost: formatRupiahInput(row.fixed_cost),
        };
        setFormMode('form');
    };
    const buildDefaultUnitLabels = (count: number, existing: string[] = []) => {
        const total = Math.max(1, Number(count || 1));

        return Array.from({ length: total }, (_, idx) => {
            const value = String(existing[idx] ?? '').trim();

            return value !== '' ? value : `Unit ${idx + 1}`;
        });
    };
    const buildDefaultUnitIds = (
        count: number,
        existing: Array<number | string | null | undefined> = [],
    ) => {
        const total = Math.max(1, Number(count || 1));

        return Array.from({ length: total }, (_, idx) =>
            Math.max(0, Number(existing[idx] || 0)),
        );
    };

    const updateScheduleUnitCount = (value: number) => {
        const total = Math.max(1, Number(value || 1));
        scheduleForm = {
            ...scheduleForm,
            units: total,
            unit_ids: buildDefaultUnitIds(total, scheduleForm.unit_ids),
            unit_labels: buildDefaultUnitLabels(
                total,
                scheduleForm.unit_labels,
            ),
        };
    };

    const updateScheduleUnitLabel = (idx: number, value: string) => {
        const next = [...scheduleForm.unit_labels];
        next[idx] = value;
        scheduleForm = { ...scheduleForm, unit_labels: next };
    };
    const updateScheduleUnitId = (idx: number, value: number) => {
        const next = buildDefaultUnitIds(
            Number(scheduleForm.units),
            scheduleForm.unit_ids,
        );
        next[idx] = Math.max(0, Number(value || 0));
        scheduleForm = {
            ...scheduleForm,
            unit_ids: next,
            unit_id: idx === 0 ? next[0] : scheduleForm.unit_id,
        };
    };

    const resetScheduleForm = () =>
        (scheduleForm = {
            id: 0,
            rute:
                canonicalScheduleRouteName(
                    selectedScheduleRoute,
                    selectedScheduleRouteId,
                ) ||
                scheduleRouteOptions[0] ||
                '',
            dow: 1,
            jam: '08:00',
            units: 1,
            bop: '',
            unit_id: 0,
            unit_ids: [0],
            unit_labels: ['Unit 1'],
        });
    const selectScheduleRoute = async (value: string) => {
        const selected = scheduleRouteSelectOptions.find(
            (option) => option.value === value,
        );

        if (!selected) {
            selectedScheduleRoute = '';
            selectedScheduleRouteId = 0;
            resetScheduleForm();
            await loadSchedules();

            return;
        }

        selectedScheduleRoute = selected.name;
        selectedScheduleRouteId = selected.id;
        resetScheduleForm();
        await loadSchedules();
    };
    const resetDriverForm = () => (
        (driverUnitSearch = ''),
        (driverForm = {
            id: 0,
            nama: '',
            phone: '',
            pool_id: defaultPoolId(),
            armada_id: 0,
            target_revenue_bulanan: '',
            fixed_cost: '',
        })
    );
    const editDriver = (row: DriverRow) => {
        driverForm = {
            id: row.id,
            nama: row.nama,
            phone: row.phone ?? '',
            pool_id: Number(row.pool_id ?? defaultPoolId()),
            armada_id: row.armada_id ?? 0,
            fixed_cost: formatRupiahInput(row.fixed_cost),
            target_revenue_bulanan: formatRupiahInput(
                row.target_revenue_bulanan,
            ),
        };
        driverUnitSearch = row.nopol ?? '';
        setFormMode('form');
    };
    const resetServiceForm = () => (serviceForm = { id: 0, name: '' });
    const resetSegmentForm = () =>
        (segmentForm = {
            id: 0,
            route_id: Number(selectedSegmentRouteId || 0),
            rute: '',
            origin: '',
            destination: '',
            harga: 0,
        });
    const editSegment = (row: SegmentRow) => {
        selectedSegmentRouteId = row.route_id;
        segmentForm = {
            id: row.id,
            route_id: row.route_id,
            rute: row.rute,
            origin: row.origin ?? '',
            destination: row.destination ?? '',
            harga: Number(row.harga),
        };
        setFormMode('form');
    };
    const resetCustomerForm = () =>
        (customerForm = {
            id: 0,
            name: '',
            phone: '',
            pickup_point: '',
            gmaps: '',
            pool_id: defaultPoolId(),
        });
    const resetUnitForm = () =>
        (unitForm = {
            id: 0,
            nama_model: '',
            pool_id: defaultPoolId(),
            category: defaultUnitCategory,
            kapasitas: 0,
            status: 'Aktif',
            layout: '',
        });
    const resetArmadaForm = () => {
        armadaForm = {
            id: 0,
            pool_id: defaultPoolId(),
            merk: '',
            tahun: '',
            warna: '',
            nopol: '',
            nomor_rangka: '',
            kategori: armadaCategoryOptions[0] ?? 'Mediumbus',
            ac_type: 'AC',
            platform_gps: '',
            api_gps: '',
            revenue: '',
            bop: '',
            fixed_cost: '',
            target_bulanan: '',
        };
        armadaTemplateSearch = '';
        armadaTemplateLookupOpen = false;
    };
    const openArmadaView = (id: number) => {
        router.visit(`/admin-ops/armadas/view/${id}`, {
            preserveScroll: true,
            preserveState: false,
        });
    };
    const openArmadaEditor = (row: ArmadaRow) => {
        armadaForm = {
            id: row.id,
            pool_id: Number(row.pool_id ?? defaultPoolId()),
            merk: row.merk ?? '',
            tahun: Number(row.tahun ?? 0) > 0 ? String(row.tahun) : '',
            warna: row.warna ?? '',
            nopol: row.nopol,
            nomor_rangka: row.nomor_rangka ?? '',
            kategori: normalizeUnitCategory(row.kategori),
            ac_type: row.ac_type || 'AC',
            platform_gps: row.platform_gps ?? '',
            api_gps: row.api_gps ?? '',
            revenue: '',
            bop: '',
            fixed_cost: formatRupiahInput(row.fixed_cost),
            target_bulanan: formatRupiahInput(row.target_bulanan),
        };
        armadaTemplateSearch = row.nopol ?? '';
        armadaTemplateLookupOpen = false;
        setFormMode('form');
    };
    const selectArmadaTemplate = (unit: UnitRow) => {
        const selected = normalizeUnitCategory(unit.category);

        armadaForm.kategori = selected;
        armadaTemplateSearch = unit.nopol ?? selected;
        armadaTemplateLookupOpen = false;
    };

    const queueArmadaTemplateSearch = () => {
        armadaTemplateLookupOpen = true;
    };

    const onArmadaTemplateBlur = () => {
        if (armadaTemplateBlurTimer) {
            clearTimeout(armadaTemplateBlurTimer);
        }

        armadaTemplateBlurTimer = setTimeout(() => {
            armadaTemplateLookupOpen = false;
            armadaTemplateBlurTimer = null;
        }, 120);
    };
    const resetPoolForm = () =>
        (poolForm = {
            id: 0,
            name: '',
            code: '',
            target_revenue: '',
            fixed_cost: '',
            status: 'active',
            notes: '',
            route_ids: [],
        });
    const resetUserForm = () =>
        (userForm = {
            id: 0,
            name: '',
            email: '',
            password: '',
            is_super_admin: false,
            pool_ids: [],
            role_ids: [],
        });

    const saveRoute = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('route');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/routes', {
                        id: routeForm.id || undefined,
                        name: routeForm.name,
                        origin: routeForm.origin,
                        destination: routeForm.destination,
                        target_revenue: parseRupiahInput(
                            routeForm.target_revenue,
                        ),
                        fixed_cost: parseRupiahInput(routeForm.fixed_cost),
                    });
                },
                {
                    loadingMessage: routeForm.id
                        ? 'Memperbarui rute induk...'
                        : 'Menyimpan rute induk...',
                    successMessage: routeForm.id
                        ? 'Rute induk berhasil diperbarui.'
                        : 'Rute induk berhasil dibuat.',
                    errorMessage: 'Gagal simpan route.',
                },
            );
            message = routeForm.id ? 'Route updated.' : 'Route created.';
            resetRouteForm();
            await loadActiveTab();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan route.';
        } finally {
            clearSubmitKey('route');
        }
    };

    const saveSchedule = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('schedule');

        try {
            const activeRoute = (
                canonicalScheduleRouteName(
                    scheduleForm.rute || selectedScheduleRoute,
                    selectedScheduleRouteId,
                ) ||
                selectedScheduleRoute ||
                ''
            ).trim();

            if (activeRoute === '') {
                throw new Error('Pilih rute terlebih dahulu.');
            }

            const selectedRouteId = scheduleRouteIdByName(activeRoute);
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/schedules', {
                        id: scheduleForm.id || undefined,
                        route_id: selectedRouteId || undefined,
                        rute: activeRoute,
                        dow: Number(scheduleForm.dow),
                        jam: scheduleForm.jam,
                        units: Number(scheduleForm.units),
                        bop: parseRupiahInput(scheduleForm.bop),
                        unit_label: scheduleForm.unit_labels[0] || undefined,
                        unit_labels: buildDefaultUnitLabels(
                            Number(scheduleForm.units),
                            scheduleForm.unit_labels,
                        ),
                        unit_id: (() => {
                            const value = Number(
                                scheduleForm.unit_ids[0] ||
                                    scheduleForm.unit_id ||
                                    0,
                            );

                            return value > 0 ? value : undefined;
                        })(),
                        unit_ids: buildDefaultUnitIds(
                            Number(scheduleForm.units),
                            scheduleForm.unit_ids,
                        ).map((value) => {
                            const normalized = Number(value || 0);

                            return normalized > 0 ? normalized : null;
                        }),
                    });
                },
                {
                    loadingMessage: scheduleForm.id
                        ? 'Memperbarui jadwal...'
                        : 'Menyimpan jadwal...',
                    successMessage: scheduleForm.id
                        ? 'Jadwal berhasil diperbarui.'
                        : 'Jadwal berhasil dibuat.',
                    errorMessage: 'Gagal simpan schedule.',
                },
            );
            message = scheduleForm.id
                ? 'Schedule updated.'
                : 'Schedule created.';
            selectedScheduleRoute = activeRoute;
            selectedScheduleRouteId = selectedRouteId;
            resetScheduleForm();
            await loadActiveTab();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan schedule.';
        } finally {
            clearSubmitKey('schedule');
        }
    };

    const openCreateSchedule = (dow: number) => {
        const routeName =
            canonicalScheduleRouteName(
                selectedScheduleRoute,
                selectedScheduleRouteId,
            ) ||
            scheduleRouteOptions[0] ||
            '';
        scheduleForm = {
            id: 0,
            rute: routeName,
            dow,
            jam: '08:00',
            units: 1,
            bop: '',
            unit_id: 0,
            unit_ids: [0],
            unit_labels: ['Unit 1'],
        };
        activeMode = 'form';
    };

    const openEditSchedule = (row: ScheduleRow) => {
        selectedScheduleRouteId = Number(
            row.route_id ?? scheduleRouteIdByName(row.route_name ?? row.rute),
        );
        selectedScheduleRoute = canonicalScheduleRouteName(
            row.route_name ?? row.rute,
            selectedScheduleRouteId,
        );
        const unitOptions = Array.isArray(row.unit_options)
            ? row.unit_options
            : [];
        const labelsFromOptions = unitOptions
            .slice()
            .sort((a, b) => Number(a.unit_no) - Number(b.unit_no))
            .map((item) => String(item.label ?? '').trim())
            .filter((label) => label !== '');
        const unitIdsFromOptions = unitOptions
            .slice()
            .sort((a, b) => Number(a.unit_no) - Number(b.unit_no))
            .map((item) => Number(item.unit_id ?? 0));
        const labels =
            labelsFromOptions.length > 0
                ? labelsFromOptions
                : [row.unit_label ?? 'Unit 1'];
        const totalUnits = Math.max(
            1,
            Number(
                row.units || labels.length || unitIdsFromOptions.length || 1,
            ),
        );
        scheduleForm = {
            id: row.id,
            rute: selectedScheduleRoute,
            dow: row.dow,
            jam: row.jam,
            units: totalUnits,
            bop: formatRupiahInput(row.bop),
            unit_id: Number(row.unit_id ?? unitIdsFromOptions[0] ?? 0),
            unit_ids: buildDefaultUnitIds(
                totalUnits,
                unitIdsFromOptions.length > 0
                    ? unitIdsFromOptions
                    : [row.unit_id ?? 0],
            ),
            unit_labels: buildDefaultUnitLabels(totalUnits, labels),
        };
        activeMode = 'form';
    };

    const changeSegmentRoute = async (routeId: number) => {
        selectedSegmentRouteId = Number(routeId || 0);
        resetSegmentForm();

        if (activeTab === 'segments') {
            await loadSegments();
        }
    };

    const saveDriver = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('driver');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/drivers', {
                        id: driverForm.id || undefined,
                        nama: driverForm.nama,
                        phone: driverForm.phone,
                        pool_id: poolPayloadValue(driverForm.pool_id),
                        armada_id: Number(driverForm.armada_id) || undefined,
                        armada_nopol: driverUnitSearch.trim() || undefined,
                        target_revenue_bulanan: parseRupiahInput(
                            driverForm.target_revenue_bulanan,
                        ),
                        fixed_cost: parseRupiahInput(driverForm.fixed_cost),
                    });
                },
                {
                    loadingMessage: driverForm.id
                        ? 'Memperbarui data driver...'
                        : 'Menyimpan data driver...',
                    successMessage: driverForm.id
                        ? 'Driver berhasil diperbarui.'
                        : 'Driver berhasil dibuat.',
                    errorMessage: 'Gagal simpan driver.',
                },
            );
            message = driverForm.id ? 'Driver updated.' : 'Driver created.';
            resetDriverForm();
            await loadActiveTab();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan driver.';
        } finally {
            clearSubmitKey('driver');
        }
    };

    const saveService = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        savingService = true;

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/luggage-services', {
                        id: serviceForm.id || undefined,
                        name: serviceForm.name,
                    });
                },
                {
                    loadingMessage: serviceForm.id
                        ? 'Memperbarui konfigurasi tarif bagasi...'
                        : 'Menyimpan konfigurasi tarif bagasi...',
                    successMessage: serviceForm.id
                        ? 'Konfigurasi tarif bagasi berhasil diperbarui.'
                        : 'Konfigurasi tarif bagasi berhasil disimpan.',
                    errorMessage: 'Gagal simpan layanan.',
                },
            );
            message = serviceForm.id ? 'Service updated.' : 'Service created.';
            resetServiceForm();
            await loadActiveTab();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan layanan.';
        } finally {
            savingService = false;
        }
    };

    const saveSegment = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('segment');

        try {
            const activeRouteId = Number(
                selectedSegmentRouteId || segmentForm.route_id || 0,
            );

            if (activeRouteId <= 0) {
                throw new Error('Pilih rute induk terlebih dahulu.');
            }

            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/segments', {
                        id: segmentForm.id || undefined,
                        route_id: activeRouteId,
                        rute: segmentForm.rute,
                        origin: segmentForm.origin,
                        destination: segmentForm.destination,
                        harga: parseRupiahInput(segmentForm.harga),
                    });
                },
                {
                    loadingMessage: segmentForm.id
                        ? 'Memperbarui segment rute...'
                        : 'Menyimpan segment rute...',
                    successMessage: segmentForm.id
                        ? 'Segment berhasil diperbarui.'
                        : 'Segment berhasil dibuat.',
                    errorMessage: 'Gagal simpan segment.',
                },
            );
            message = segmentForm.id ? 'Segment updated.' : 'Segment created.';
            resetSegmentForm();
            await loadSegments();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan segment.';
        } finally {
            clearSubmitKey('segment');
        }
    };

    const saveCustomer = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('customer');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/customers', {
                        id: customerForm.id || undefined,
                        name: customerForm.name,
                        phone: customerForm.phone,
                        pickup_point: customerForm.pickup_point,
                        gmaps: customerForm.gmaps,
                        pool_id: poolPayloadValue(customerForm.pool_id),
                    });
                },
                {
                    loadingMessage: customerForm.id
                        ? 'Memperbarui data customer...'
                        : 'Menyimpan data customer...',
                    successMessage: customerForm.id
                        ? 'Customer berhasil diperbarui.'
                        : 'Customer berhasil dibuat.',
                    errorMessage: 'Gagal simpan customer.',
                },
            );
            message = customerForm.id
                ? 'Customer updated.'
                : 'Customer created.';
            resetCustomerForm();
            await loadCustomers(customerMeta.page);
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan customer.';
        } finally {
            clearSubmitKey('customer');
        }
    };

    const saveUnit = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('unit');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/units', {
                        id: unitForm.id || undefined,
                        nopol: unitForm.nama_model,
                        pool_id: poolPayloadValue(unitForm.pool_id),
                        category: normalizeUnitCategory(unitForm.category),
                        kapasitas: Number(unitForm.kapasitas || 0),
                        status: unitForm.status,
                        layout: unitForm.layout,
                    });
                },
                {
                    loadingMessage: unitForm.id
                        ? 'Memperbarui kategori armada...'
                        : 'Menyimpan kategori armada...',
                    successMessage: unitForm.id
                        ? 'Kategori armada berhasil diperbarui.'
                        : 'Kategori armada berhasil dibuat.',
                    errorMessage: 'Gagal simpan unit.',
                },
            );
            message = unitForm.id ? 'Unit updated.' : 'Unit created.';
            resetUnitForm();
            await loadActiveTab();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan unit.';
        } finally {
            clearSubmitKey('unit');
        }
    };

    const saveArmada = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('armada');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/armadas', {
                        id: armadaForm.id || undefined,
                        pool_id: poolPayloadValue(armadaForm.pool_id),
                        merk: armadaForm.merk,
                        tahun: Number(armadaForm.tahun || 0),
                        warna: armadaForm.warna,
                        nopol: armadaForm.nopol,
                        nomor_rangka: armadaForm.nomor_rangka,
                        kategori: armadaForm.kategori,
                        ac_type: armadaForm.ac_type,
                        platform_gps: armadaForm.platform_gps,
                        api_gps: armadaForm.api_gps,
                        fixed_cost: parseRupiahInput(armadaForm.fixed_cost),
                        target_revenue: parseRupiahInput(
                            armadaForm.target_bulanan,
                        ),
                    });
                },
                {
                    loadingMessage: armadaForm.id
                        ? 'Memperbarui data armada...'
                        : 'Menyimpan data armada...',
                    successMessage: armadaForm.id
                        ? 'Armada berhasil diperbarui.'
                        : 'Armada berhasil dibuat.',
                    errorMessage: 'Gagal simpan armada.',
                },
            );
            message = armadaForm.id ? 'Armada updated.' : 'Armada created.';
            resetArmadaForm();
            await loadArmadas();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan armada.';
        } finally {
            clearSubmitKey('armada');
        }
    };

    const savePool = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('pool');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/pools', {
                        id: poolForm.id || undefined,
                        name: poolForm.name,
                        code: poolForm.code,
                        target_revenue: parseRupiahInput(
                            poolForm.target_revenue,
                        ),
                        fixed_cost: parseRupiahInput(poolForm.fixed_cost),
                        status: poolForm.status,
                        notes: poolForm.notes,
                        route_ids: poolForm.route_ids,
                    });
                },
                {
                    loadingMessage: poolForm.id
                        ? 'Memperbarui pool...'
                        : 'Menyimpan pool...',
                    successMessage: poolForm.id
                        ? 'Pool berhasil diperbarui.'
                        : 'Pool berhasil dibuat.',
                    errorMessage: 'Gagal simpan pool.',
                },
            );
            message = poolForm.id ? 'Pool updated.' : 'Pool created.';
            resetPoolForm();
            await loadPools();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan pool.';
        } finally {
            clearSubmitKey('pool');
        }
    };

    const saveUser = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('user');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/users', {
                        id: userForm.id || undefined,
                        name: userForm.name,
                        email: userForm.email,
                        password: userForm.password,
                        is_super_admin: userForm.is_super_admin,
                        pool_ids: userForm.pool_ids,
                        role_ids: userForm.role_ids,
                    });
                },
                {
                    loadingMessage: userForm.id
                        ? 'Memperbarui user...'
                        : 'Menyimpan user...',
                    successMessage: userForm.id
                        ? 'User berhasil diperbarui.'
                        : 'User berhasil dibuat.',
                    errorMessage: 'Gagal simpan user.',
                },
            );
            message = userForm.id ? 'User updated.' : 'User created.';
            resetUserForm();
            await loadUsers();
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan user.';
        } finally {
            clearSubmitKey('user');
        }
    };

    const runUserVerificationAction = async (
        row: UserRow,
        action: 'verify' | 'unverify' | 'send-verification',
    ) => {
        message = '';
        error = '';

        const labels = {
            verify: {
                loading: 'Memverifikasi user...',
                success: 'User berhasil diverifikasi.',
                done: 'User verified.',
            },
            unverify: {
                loading: 'Membatalkan verifikasi user...',
                success: 'Verifikasi user dibatalkan.',
                done: 'User unverified.',
            },
            'send-verification': {
                loading: 'Mengirim link verifikasi...',
                success: 'Link verifikasi terkirim.',
                done: 'Verification email sent.',
            },
        }[action];

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', `/api/admin/users/${row.id}/${action}`);
                },
                {
                    loadingMessage: labels.loading,
                    successMessage: labels.success,
                    errorMessage: 'Gagal memperbarui status verifikasi user.',
                },
            );
            message = labels.done;
            await loadUsers();
        } catch (e) {
            error =
                e instanceof Error
                    ? e.message
                    : 'Gagal memperbarui status verifikasi user.';
        }
    };

    const removeItem = async (
        url: string,
        successMessage: string,
        options: {
            confirmMessage?: string;
            loadingMessage?: string;
            errorMessage?: string;
            pendingKey?: string;
        } = {},
    ) => {
        message = '';
        error = '';
        pendingDeleteKey = options.pendingKey ?? url;

        try {
            const result = await confirmAndRun(
                options.confirmMessage ?? 'Yakin ingin menghapus data ini?',
                async () => {
                    await api('DELETE', url);
                },
                {
                    loadingMessage:
                        options.loadingMessage ?? 'Menghapus data...',
                    successMessage,
                    errorMessage: options.errorMessage ?? 'Gagal hapus data.',
                },
            );

            if (result === null) {
                return;
            }

            message = successMessage;
            await loadActiveTab();
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal hapus data.';
        } finally {
            pendingDeleteKey = '';
        }
    };

    $effect(() => {
        const isScheduleFormActive =
            activeTab === 'schedules' && activeMode === 'form';

        if (!isScheduleFormActive) {
            destroyScheduleTimePicker();

            return;
        }

        void initScheduleTimePicker().then(() => {
            scheduleTimePicker?.setDate(
                scheduleForm.jam || '08:00',
                false,
                'H:i',
            );
        });
    });

    $effect(() => {
        const isReportTabActive = activeTab === 'reports';
        const reportPanelLoaded = ReportsPanelComponent;
        const fromInput = reportFromInput;
        const toInput = reportToInput;

        if (!isReportTabActive) {
            destroyReportPickers();

            return;
        }

        if (!reportPanelLoaded || !fromInput || !toInput) {
            return;
        }

        void initReportPickers().then(() => {
            reportFromPicker?.setDate(reportFrom || today, false, 'Y-m-d');
            reportToPicker?.setDate(reportTo || today, false, 'Y-m-d');
        });
    });

    onMount(() => {
        if (lockedFromServer) {
            lockedMenuView = true;
        }

        if (isTabName(initialTab)) {
            activeTab = initialTab;
            lockedMenuView = true;
        }

        if (initialMode === 'view' && Number(initialRecordId || 0) > 0) {
            activeTab = 'armadas';
            activeMode = 'view';
            armadaViewId = Number(initialRecordId);
            lockedMenuView = true;
        }

        if (initialMode === 'layout' && Number(initialRecordId || 0) > 0) {
            activeTab = 'units';
            activeMode = 'layout';
            layoutUnitId = Number(initialRecordId);
            lockedMenuView = true;
        }

        if (typeof window !== 'undefined') {
            const initialTab = new URLSearchParams(window.location.search).get(
                'tab',
            );

            if (isTabName(initialTab)) {
                activeTab = initialTab;
                lockedMenuView = true;
            }
        }

        if (!canOpenTab(activeTab)) {
            activeTab = firstVisibleTab();
        }

        if (usesHybridSettings()) {
            busy = settingsData === null;

            return;
        }

        void loadActiveTab();
    });

    onDestroy(() => {
        if (armadaTemplateBlurTimer) {
            clearTimeout(armadaTemplateBlurTimer);
        }
        destroyScheduleTimePicker();
        destroyReportPickers();
    });
</script>

<AppHead title={`Admin Ops • ${tabTitle(activeTab)}`} />

<div class="space-y-4 p-4">
    {#if !lockedMenuView}
        <div class="overflow-hidden rounded-[28px] border border-border/70 bg-[linear-gradient(135deg,rgba(15,23,42,0.98),rgba(8,145,178,0.12))] shadow-sm">
            <div class="grid gap-4 p-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] lg:items-end">
                <div class="space-y-4 text-slate-50">
                    <div class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-100">
                        Admin Operations
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-2xl font-semibold tracking-tight md:text-3xl">
                            {tabTitle(activeTab)}
                        </h2>
                        <p class="max-w-2xl text-sm leading-relaxed text-slate-200/80">
                            {tabGroupFor(activeTab).description}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-medium text-slate-100">
                            {tabGroupFor(activeTab).title}
                        </span>
                        <span class="inline-flex items-center rounded-full border border-emerald-200/30 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-100">
                            Kelola data inti dari satu panel
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Rute</p>
                        <p class="mt-1 text-lg font-semibold">{stats.routes}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Jadwal</p>
                        <p class="mt-1 text-lg font-semibold">{stats.schedules}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Driver</p>
                        <p class="mt-1 text-lg font-semibold">{stats.drivers}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Bagasi</p>
                        <p class="mt-1 text-lg font-semibold">{stats.luggage_services}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Segment</p>
                        <p class="mt-1 text-lg font-semibold">{stats.segments}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Reguler</p>
                        <p class="mt-1 text-lg font-semibold">{stats.customers}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Armada</p>
                        <p class="mt-1 text-lg font-semibold">{stats.armadas}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Pool</p>
                        <p class="mt-1 text-lg font-semibold">{stats.pools}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-3 text-slate-50">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-slate-200/70">Logs</p>
                        <p class="mt-1 text-lg font-semibold">{stats.cancellations}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            {#each visibleTabGroups as group (group.title)}
                <Card class={group.tabs.some((item) => item.tab === activeTab) ? 'border-cyan-300/60 shadow-md shadow-cyan-950/5' : 'border-border/70 shadow-sm'}>
                    <CardHeader class="space-y-1 pb-3">
                        <CardTitle class="text-sm font-semibold">{group.title}</CardTitle>
                        <p class="text-xs text-muted-foreground">{group.description}</p>
                    </CardHeader>
                    <CardContent class="flex flex-wrap gap-2 pt-0">
                        {#each group.tabs as item (item.tab)}
                            <Button
                                type="button"
                                variant={activeTab === item.tab ? 'default' : 'ghost'}
                                class={activeTab === item.tab
                                    ? 'shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'}
                                onclick={() => void setTab(item.tab)}
                            >
                                {item.label}
                            </Button>
                        {/each}
                    </CardContent>
                </Card>
            {/each}
        </div>
    {/if}

    <Card class="overflow-hidden border-border/70 shadow-sm">
        <CardHeader class="space-y-2 border-b border-border/70 bg-muted/20">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                        {tabGroupFor(activeTab).title}
                    </p>
                    <CardTitle>{tabTitle(activeTab)}</CardTitle>
                    <p class="max-w-3xl text-sm text-muted-foreground">
                        {tabGroupFor(activeTab).description}
                    </p>
                </div>
                <Badge variant="secondary" class="rounded-full px-3 py-1">
                    {lockedMenuView ? 'Locked View' : 'Active'}
                </Badge>
            </div>
        </CardHeader>
        <CardContent class="space-y-4 pt-6">
            {#if busy}<p class="text-sm text-muted-foreground">
                    Memuat data...
                </p>{/if}
            {#if error}<p class="text-sm text-red-600">{error}</p>{/if}
            {#if message}<p class="text-sm text-emerald-600">{message}</p>{/if}
            {#if usesHybridSettings('drivers') || usesHybridSettings('pools')}
                <div class="flex flex-col gap-2 md:flex-row">
                    {#if activeTab === 'drivers'}
                        <TerminalFilter bind:query={driverSearch} placeholder="Cari nama, telepon, atau nopol driver" on:search={() => reloadSettingsWithInertia(1)} />
                    {:else}
                        <TerminalFilter bind:query={poolSearch} placeholder="Cari nama, kode, atau catatan pool" on:search={() => reloadSettingsWithInertia(1)} />
                    {/if}
                </div>
            {/if}
            {#if hasFormTab(activeTab) && canWriteTab(activeTab)}
                <div class="flex items-center gap-2">
                    {#if activeMode === 'data'}
                        <Button type="button" size="sm" onclick={openCreateForm}
                            >Tambah Data Baru</Button
                        >
                    {:else}
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            onclick={() => setFormMode('data')}
                            >Kembali ke Data</Button
                        >
                    {/if}
                </div>
            {/if}

            {#if activeTab === 'routes'}
                {#if activeMode === 'form'}
                    <form
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={saveRoute}
                    >
                        <div class="border-b border-border/70 bg-muted/20 px-5 py-4">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                Form Rute Induk
                            </p>
                            <h3 class="mt-1 text-lg font-semibold">
                                {routeForm.id
                                    ? 'Perbarui rute utama'
                                    : 'Tambah rute utama baru'}
                            </h3>
                            <p
                                class="mt-1 max-w-2xl text-sm text-muted-foreground"
                            >
                                Data ini menjadi fondasi untuk jadwal dan
                                segment, jadi nama rute dan arah perjalanan
                                sebaiknya dibuat jelas sejak awal.
                            </p>
                        </div>
                        <div
                            class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Nama Rute</span
                                >
                                <Input
                                    placeholder="Contoh: Pinrang - Makassar"
                                    bind:value={routeForm.name}
                                    required
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Origin</span
                                >
                                <Input
                                    placeholder="Titik keberangkatan"
                                    bind:value={routeForm.origin}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Destination</span
                                >
                                <Input
                                    placeholder="Titik tujuan"
                                    bind:value={routeForm.destination}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Target Revenue</span
                                >
                                <Input
                                    placeholder="Rp 0"
                                    bind:value={routeForm.target_revenue}
                                    oninput={(event) => {
                                        routeForm.target_revenue =
                                            formatRupiahInput(
                                                (
                                                    event.currentTarget as HTMLInputElement
                                                ).value,
                                            );
                                    }}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Fixed Cost</span
                                >
                                <Input
                                    placeholder="Rp 0"
                                    bind:value={routeForm.fixed_cost}
                                    oninput={(event) => {
                                        routeForm.fixed_cost =
                                            formatRupiahInput(
                                                (
                                                    event.currentTarget as HTMLInputElement
                                                ).value,
                                            );
                                    }}
                                />
                            </label>
                        </div>
                        <div
                            class="flex flex-wrap gap-2 border-t border-border/70 bg-muted/20 p-5"
                        >
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('route')}
                                loadingText={routeForm.id
                                    ? 'Menyimpan...'
                                    : 'Membuat...'}
                                >{routeForm.id
                                    ? 'Update Rute'
                                    : 'Tambah Rute'}</LoadingButton
                            >
                            <Button
                                type="button"
                                variant="outline"
                                onclick={resetRouteForm}>Reset</Button
                            >
                        </div>
                    </form>
                {:else}
                    <div
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                    >
                        <div class="flex flex-col gap-3 border-b border-border/70 bg-muted/20 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    Rute Induk
                                </p>
                                <h3 class="mt-1 text-lg font-semibold">
                                    Peta perjalanan utama
                                </h3>
                                <p
                                    class="mt-1 max-w-3xl text-sm text-muted-foreground"
                                >
                                    Nama rute dipertahankan dominan, sedangkan
                                    origin dan destination digabung agar tabel
                                    tetap ringkas saat dibuka di laptop.
                                </p>
                            </div>
                            <Badge
                                variant="secondary"
                                class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                            >
                                {routes.length} rute aktif
                            </Badge>
                        </div>
                        <div class="grid gap-3 p-3 md:hidden">
                            {#if routes.length === 0}
                                <div class="rounded-2xl border border-dashed border-border/80 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Belum ada rute induk.
                                </div>
                            {/if}
                            {#each routes as row (row.id)}
                                {@const gross = financialGrossMargin(row)}
                                {@const net = financialNetMargin(row)}
                                {@const achievement = financialAchievement(row)}
                                {@const status = financialStatus(row)}
                                <article
                                    class="overflow-hidden rounded-2xl border border-border/80 bg-card/95 shadow-sm"
                                >
                                    <div class="bg-muted/20 p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-foreground">
                                                    {row.name}
                                                </p>
                                                <p class="mt-0.5 text-xs text-muted-foreground">
                                                    Rute master jadwal dan segment
                                                </p>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-1.5">
                                                <span
                                                    class={`rounded-full border px-2 py-0.5 text-[10px] font-semibold ${
                                                        status === 'Tercapai'
                                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
                                                            : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300'
                                                    }`}
                                                >
                                                    {status}
                                                </span>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-8 w-8 rounded-full border border-border/70 bg-background/80"
                                                        >
                                                            <MoreHorizontal class="h-4 w-4" />
                                                            <span class="sr-only">Aksi rute induk</span>
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                        sideOffset={8}
                                                        class="z-[120] w-44"
                                                    >
                                                        <DropdownMenuItem onclick={() => editRoute(row)}>
                                                            <Pencil class="mr-2 h-3.5 w-3.5" />
                                                            Edit
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onclick={() =>
                                                                void removeItem(
                                                                    `/api/admin/routes/${row.id}`,
                                                                    'Route deleted.',
                                                                )}
                                                        >
                                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                            Hapus
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center gap-2 text-xs">
                                            <span class="min-w-0 truncate rounded-full border border-border/70 bg-background/80 px-2.5 py-1 font-medium">
                                                {row.origin ?? 'Origin belum diatur'}
                                            </span>
                                            <span class="shrink-0 text-muted-foreground">→</span>
                                            <span class="min-w-0 truncate rounded-full border border-border/70 bg-background/80 px-2.5 py-1 font-medium">
                                                {row.destination ?? 'Destination belum diatur'}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 p-3 text-xs">
                                        <div class="rounded-xl bg-emerald-50/70 px-3 py-2 dark:bg-emerald-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                                Revenue
                                            </p>
                                            <p class="mt-1 font-semibold text-emerald-800 dark:text-emerald-200">
                                                {formatCurrency(Number(row.revenue || 0))}
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-amber-50/80 px-3 py-2 dark:bg-amber-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                                                BOP
                                            </p>
                                            <p class="mt-1 font-semibold text-amber-800 dark:text-amber-200">
                                                {formatCurrency(Number(row.bop || 0))}
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-sky-50/80 px-3 py-2 dark:bg-sky-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">
                                                Net Margin
                                            </p>
                                            <p
                                                class={`mt-1 font-semibold ${net >= 0 ? 'text-sky-800 dark:text-sky-200' : 'text-rose-700 dark:text-rose-300'}`}
                                            >
                                                {formatCurrency(net)}
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-muted/40 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                                Achievement
                                            </p>
                                            <p class="mt-1 font-semibold text-foreground">
                                                {achievement.toFixed(1)}%
                                            </p>
                                        </div>
                                    </div>

                                    <details class="border-t border-border/70 px-3 py-2 text-xs">
                                        <summary class="cursor-pointer select-none py-1 font-semibold text-muted-foreground">
                                            Detail breakdown
                                        </summary>
                                        <div class="mt-2 grid gap-2">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Charter</p>
                                                    <p class="mt-1 font-semibold">{formatCurrency(Number(row.charter_revenue || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Keberangkatan</p>
                                                    <p class="mt-1 font-semibold">{formatCurrency(Number(row.departure_revenue || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Bagasi</p>
                                                    <p class="mt-1 font-semibold">{formatCurrency(Number(row.luggage_revenue || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Gross</p>
                                                    <p class="mt-1 font-semibold">{formatCurrency(gross)}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Fixed Cost</p>
                                                    <p class="mt-1 font-semibold">{formatCurrency(Number(row.fixed_cost || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Target</p>
                                                    <p class="mt-1 font-semibold">{formatCurrency(Number(row.target_revenue || 0))}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </article>
                            {/each}
                        </div>
                        <div class="hidden overflow-x-auto md:block">
                            <DataTable columns={routesColumns} rows={routes} class="min-w-[1980px] w-full border-separate border-spacing-0 text-sm" tone="default">
                                {#snippet row({ row, columns })}
                                    {@const gross = financialGrossMargin(row as RouteRow)}
                                    {@const net = financialNetMargin(row as RouteRow)}
                                    {@const achievement = financialAchievement(row as RouteRow)}
                                    {@const status = financialStatus(row as RouteRow)}

                                    <td class="sticky left-0 z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15" style={`left: ${columns[0]?.leftOffset ?? '0px'}`}>
                                        <div class="font-semibold text-foreground">{row.name}</div>
                                        <div class="mt-1 text-[11px] text-muted-foreground">Rute master untuk jadwal dan segment</div>
                                    </td>

                                    <td class="sticky left-[220px] z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15" style={`left: ${columns[1]?.leftOffset ?? '0px'}`}>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium">{row.origin ?? 'Origin belum diatur'}</span>
                                            <span class="text-muted-foreground">→</span>
                                            <span class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium">{row.destination ?? 'Destination belum diatur'}</span>
                                        </div>
                                        <div class="mt-2 text-[11px] text-muted-foreground">Jalur ini dipakai sebagai acuan relasi jadwal keberangkatan dan segment harga.</div>
                                    </td>

                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.charter_revenue || 0))}</td>
                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.departure_revenue || 0))}</td>
                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.luggage_revenue || 0))}</td>

                                    <td class="border-b border-r border-border/60 bg-emerald-50/45 px-3 py-4 text-right">
                                        <div class="text-sm font-semibold text-emerald-800 tabular-nums">{formatCurrency(Number(row.revenue || 0))}</div>
                                        <div class="mt-1 text-[10px] uppercase tracking-wide text-emerald-700/80">Total</div>
                                    </td>

                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.charter_bop || 0))}</td>
                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.departure_bop || 0))}</td>

                                    <td class="border-b border-r border-border/60 bg-amber-50/50 px-3 py-4 text-right">
                                        <div class="text-sm font-semibold text-amber-800 tabular-nums">{formatCurrency(Number(row.bop || 0))}</div>
                                        <div class="mt-1 text-[10px] uppercase tracking-wide text-amber-700/80">Total</div>
                                    </td>

                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(gross)}</td>
                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.fixed_cost || 0))}</td>

                                    <td class="border-b border-r border-border/60 px-3 py-4 text-right">
                                        <div class={`text-sm font-semibold tabular-nums ${net >= 0 ? 'text-sky-800' : 'text-rose-700'}`}>{formatCurrency(net)}</div>
                                        <div class={`mt-1 text-[10px] uppercase tracking-wide ${net >= 0 ? 'text-sky-700/80' : 'text-rose-600/80'}`}>{net >= 0 ? 'Positif' : 'Minus'}</div>
                                    </td>

                                    <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.target_revenue || 0))}</td>
                                    <td class="border-b border-r border-border/60 px-3 py-4 text-right">
                                        <div class="text-sm font-semibold tabular-nums">{achievement.toFixed(1)}%</div>
                                        <div class="mt-1 text-[10px] uppercase tracking-wide text-muted-foreground">Pencapaian</div>
                                    </td>

                                    <td class="border-b border-r border-border/60 px-3 py-4 text-center">
                                        <span class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${status === 'Tercapai' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>{status}</span>
                                    </td>
                                {/snippet}

                                {#snippet actions({ row })}
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                                <MoreHorizontal class="h-4 w-4" />
                                                <span class="sr-only">Aksi rute induk</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                            <DropdownMenuItem onclick={() => editRoute(row as RouteRow)}>
                                                <Pencil class="mr-2 h-3.5 w-3.5" />
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem onclick={() => void removeItem(`/api/admin/routes/${row.id}`, 'Route deleted.') }>
                                                <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                Hapus
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                {/snippet}
                            </DataTable>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'schedules'}
                <div class="space-y-4">
                    <section class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm">
                        <div class="space-y-5 px-5 py-5">
                            <div
                                class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between"
                            >
                                <div class="space-y-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                                        Workspace Keberangkatan
                                    </p>
                                    <div class="space-y-2">
                                        <h3
                                            class="text-xl font-semibold tracking-tight text-foreground"
                                        >
                                            Jadwal mingguan lebih mudah dipindai
                                        </h3>
                                        <p
                                            class="max-w-3xl text-sm text-muted-foreground"
                                        >
                                            Pilih rute untuk mengatur jam
                                            berangkat, jumlah unit, layout kursi,
                                            dan BOP tanpa harus membaca tabel
                                            panjang.
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="grid gap-3 md:min-w-[420px] md:grid-cols-[minmax(0,1fr)_auto]"
                                >
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted-foreground"
                                            >Rute aktif</span
                                        >
                                        <select
                                            class="h-10 rounded-lg border border-input bg-background/95 px-3 text-sm shadow-sm"
                                            value={selectedScheduleRouteValue}
                                            disabled={scheduleRouteSelectOptions.length ===
                                                0}
                                            onchange={(event) => {
                                                void selectScheduleRoute(
                                                    (
                                                        event.currentTarget as HTMLSelectElement
                                                    ).value,
                                                );
                                            }}
                                        >
                                            <option value="">Pilih rute</option>
                                            {#each scheduleRouteSelectOptions as route (route.value)}
                                                <option value={route.value}
                                                    >{route.name}</option
                                                >
                                            {/each}
                                        </select>
                                    </label>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-10 self-end"
                                        onclick={() => void setTab('routes')}
                                    >
                                        <Route class="mr-2 h-4 w-4" />
                                        Kelola Rute
                                    </Button>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/90 p-4 shadow-sm"
                                >
                                    <div
                                        class="flex items-center gap-3 text-muted-foreground"
                                    >
                                        <Route class="h-4 w-4 text-sky-600" />
                                        <span
                                            class="text-[11px] font-semibold uppercase tracking-[0.18em]"
                                            >Rute tersedia</span
                                        >
                                    </div>
                                    <p
                                        class="mt-3 text-2xl font-semibold text-foreground"
                                    >
                                        {scheduleOverview.routes}
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {selectedScheduleRoute
                                            ? selectedScheduleRoute
                                            : 'Belum ada rute yang dipilih'}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/90 p-4 shadow-sm"
                                >
                                    <div
                                        class="flex items-center gap-3 text-muted-foreground"
                                    >
                                        <CalendarDays
                                            class="h-4 w-4 text-emerald-600"
                                        />
                                        <span
                                            class="text-[11px] font-semibold uppercase tracking-[0.18em]"
                                            >Jadwal aktif</span
                                        >
                                    </div>
                                    <p
                                        class="mt-3 text-2xl font-semibold text-foreground"
                                    >
                                        {scheduleSummary.total}
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {activeScheduleGroup
                                            ? `${activeScheduleGroup.activeDays} hari operasional`
                                            : 'Semua jadwal dari seluruh rute'}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/90 p-4 shadow-sm"
                                >
                                    <div
                                        class="flex items-center gap-3 text-muted-foreground"
                                    >
                                        <Armchair
                                            class="h-4 w-4 text-violet-600"
                                        />
                                        <span
                                            class="text-[11px] font-semibold uppercase tracking-[0.18em]"
                                            >Total unit</span
                                        >
                                    </div>
                                    <p
                                        class="mt-3 text-2xl font-semibold text-foreground"
                                    >
                                        {scheduleSummary.totalUnits}
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Alokasi unit terjadwal minggu ini
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/90 p-4 shadow-sm"
                                >
                                    <div
                                        class="flex items-center gap-3 text-muted-foreground"
                                    >
                                        <Wallet class="h-4 w-4 text-amber-600" />
                                        <span
                                            class="text-[11px] font-semibold uppercase tracking-[0.18em]"
                                            >BOP terjadwal</span
                                        >
                                    </div>
                                    <p
                                        class="mt-3 text-xl font-semibold text-foreground"
                                    >
                                        {formatCurrency(scheduleSummary.bopTotal)}
                                    </p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Window jam {formatScheduleWindow(
                                            scheduleSummary.firstDeparture,
                                            scheduleSummary.lastDeparture,
                                        )}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {#if activeMode === 'form'}
                        <form onsubmit={saveSchedule}>
                            <AdminOpsSection
                                eyebrow="Form Jadwal"
                                title={scheduleForm.id
                                    ? 'Perbarui jadwal keberangkatan'
                                    : 'Tambah jadwal keberangkatan baru'}
                                description="Atur hari, jam, jumlah unit, dan BOP. Konfigurasi kategori armada per unit akan dipakai langsung oleh dropdown kursi di booking."
                                toneClass="bg-muted/20"
                                bodyClass="space-y-4"
                            >
                                <input
                                    type="hidden"
                                    bind:value={scheduleForm.rute}
                                />
                                <div
                                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                                >
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Hari</span
                                        >
                                        <select
                                            class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                            bind:value={scheduleForm.dow}
                                        >
                                            {#each days as day, idx (idx)}
                                                <option value={idx}
                                                    >{day}</option
                                                >
                                            {/each}
                                        </select>
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Jam Keberangkatan</span
                                        >
                                        <input
                                            bind:this={scheduleTimeInput}
                                            type="text"
                                            value={scheduleForm.jam}
                                            readonly
                                            autocomplete="off"
                                            placeholder="Jam"
                                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Jumlah Unit</span
                                        >
                                        <Input
                                            type="number"
                                            min="1"
                                            placeholder="Jumlah unit"
                                            value={scheduleForm.units}
                                            oninput={(event) =>
                                                updateScheduleUnitCount(
                                                    Number(
                                                        (
                                                            event.currentTarget as HTMLInputElement
                                                        ).value || 1,
                                                    ),
                                                )}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >BOP</span
                                        >
                                        <Input
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="BOP"
                                            value={scheduleForm.bop}
                                            oninput={(event) => {
                                                scheduleForm = {
                                                    ...scheduleForm,
                                                    bop: formatRupiahInput(
                                                        (
                                                            event.currentTarget as HTMLInputElement
                                                        ).value,
                                                    ),
                                                };
                                            }}
                                        />
                                    </label>
                                </div>

                                <div
                                    class="rounded-2xl border border-input/70 bg-muted/10 p-4"
                                >
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted-foreground"
                                    >
                                        Konfigurasi Unit Keberangkatan
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Setiap unit bisa memakai kategori
                                        armada/layout berbeda. Dropdown kursi
                                        booking akan mengikuti setting ini.
                                    </p>
                                    <div class="mt-4 grid gap-3">
                                        {#each scheduleForm.unit_labels as label, idx (`unit-label-${idx}`)}
                                            <div
                                                class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.35fr)]"
                                            >
                                                <label class="space-y-1.5">
                                                    <span
                                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                                        >Label Unit {idx +
                                                            1}</span
                                                    >
                                                    <Input
                                                        placeholder={`Label Unit ${idx + 1}`}
                                                        value={label}
                                                        oninput={(event) =>
                                                            updateScheduleUnitLabel(
                                                                idx,
                                                                (
                                                                    event.currentTarget as HTMLInputElement
                                                                ).value,
                                                            )}
                                                    />
                                                </label>
                                                <label class="space-y-1.5">
                                                    <span
                                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                                        >Kategori Armada /
                                                        Layout</span
                                                    >
                                                    <select
                                                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                        value={scheduleForm
                                                            .unit_ids[idx] ?? 0}
                                                        onchange={(event) =>
                                                            updateScheduleUnitId(
                                                                idx,
                                                                Number(
                                                                    (
                                                                        event.currentTarget as HTMLSelectElement
                                                                    ).value ||
                                                                        0,
                                                                ),
                                                            )}
                                                    >
                                                        <option value={0}
                                                            >Pilih kategori
                                                            armada/layout</option
                                                        >
                                                        {#each units as unit (unit.id)}
                                                            <option
                                                                value={unit.id}
                                                                >{unit.nopol}{unit.category
                                                                    ? ` • ${normalizeUnitCategory(unit.category)}`
                                                                    : ''}</option
                                                            >
                                                        {/each}
                                                    </select>
                                                </label>
                                            </div>
                                        {/each}
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl border border-border/70 bg-muted/20 px-4 py-3 text-xs text-muted-foreground"
                                >
                                    BOP akan dipakai otomatis pada data
                                    keberangkatan. Jika jumlah unit berubah,
                                    label dan pilihan kategori armada akan ikut
                                    menyesuaikan.
                                </div>

                                <div
                                    class="flex flex-wrap gap-2 border-t border-border/70 pt-4"
                                >
                                    <LoadingButton
                                        type="submit"
                                        loading={isSubmitActive('schedule')}
                                        loadingText={scheduleForm.id
                                            ? 'Menyimpan jadwal...'
                                            : 'Menambah jadwal...'}
                                        >{scheduleForm.id
                                            ? 'Update Jadwal'
                                            : 'Tambah Jadwal'}</LoadingButton
                                    >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onclick={resetScheduleForm}
                                        >Reset</Button
                                    >
                                </div>
                            </AdminOpsSection>
                        </form>
                    {/if}

                    {#if activeMode === 'data' && !selectedScheduleRoute}
                        <div
                            class="rounded-2xl border border-dashed border-border/80 bg-muted/10 px-5 py-6"
                        >
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div class="space-y-1.5">
                                    <p class="text-sm font-semibold text-foreground">
                                        Pilih rute untuk mulai mengatur
                                        keberangkatan.
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Setelah rute dipilih, panel mingguan akan
                                        menampilkan semua slot keberangkatan dari
                                        Minggu sampai Sabtu dalam satu layar.
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onclick={() => void setTab('routes')}
                                >
                                    <ArrowUpRight class="mr-2 h-4 w-4" />
                                    Buka Master Rute
                                </Button>
                            </div>
                        </div>
                    {:else if activeMode === 'data'}
                        <div class="space-y-4">
                            <div
                                class="flex flex-wrap items-center gap-2 text-xs"
                            >
                                <Badge
                                    variant="secondary"
                                    class="rounded-full px-3 py-1 font-semibold uppercase tracking-wide"
                                >
                                    {selectedScheduleRoute}
                                </Badge>
                                <Badge
                                    variant="outline"
                                    class="rounded-full px-3 py-1 text-muted-foreground"
                                >
                                    {activeScheduleGroup?.total ?? 0} jadwal
                                </Badge>
                                <Badge
                                    variant="outline"
                                    class="rounded-full px-3 py-1 text-muted-foreground"
                                >
                                    {activeScheduleGroup?.totalUnits ?? 0} unit
                                </Badge>
                                <Badge
                                    variant="outline"
                                    class="rounded-full px-3 py-1 text-muted-foreground"
                                >
                                    <Clock3 class="mr-1.5 h-3.5 w-3.5" />
                                    {formatScheduleWindow(
                                        activeScheduleGroup?.firstDeparture ??
                                            null,
                                        activeScheduleGroup?.lastDeparture ??
                                            null,
                                    )}
                                </Badge>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                            >
                                {#each (activeScheduleGroup?.days ?? emptyScheduleDayGroups) as day (day.dow)}
                                    <div
                                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                                    >
                                        <div
                                            class="flex items-start justify-between gap-3 border-b border-border/60 bg-muted/10 px-4 py-3"
                                        >
                                            <div class="space-y-1">
                                                <p
                                                    class="text-sm font-semibold text-foreground"
                                                >
                                                    {days[day.dow]}
                                                </p>
                                                <p
                                                    class="text-[11px] text-muted-foreground"
                                                >
                                                    {day.rows.length > 0
                                                        ? `${formatScheduleWindow(day.firstDeparture, day.lastDeparture)} • ${day.totalUnits} unit`
                                                        : 'Belum ada jadwal'}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <Badge
                                                    variant="outline"
                                                    class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                >
                                                    {day.rows.length}
                                                </Badge>
                                                {#if canWriteTab('schedules')}
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        class="h-8 gap-1.5 rounded-full px-3"
                                                        onclick={() =>
                                                            openCreateSchedule(
                                                                day.dow,
                                                            )}
                                                    >
                                                        <Plus
                                                            class="h-3.5 w-3.5"
                                                        />
                                                        Tambah
                                                    </Button>
                                                {/if}
                                            </div>
                                        </div>
                                        {#if day.rows.length === 0}
                                            <div class="px-4 py-6">
                                                <div
                                                    class="rounded-2xl border border-dashed border-border/70 bg-muted/10 px-4 py-5 text-center"
                                                >
                                                    <p
                                                        class="text-sm font-medium text-foreground"
                                                    >
                                                        Belum ada slot
                                                        keberangkatan
                                                    </p>
                                                    <p
                                                        class="mt-1 text-xs text-muted-foreground"
                                                    >
                                                        Tambahkan jam
                                                        keberangkatan untuk hari
                                                        ini supaya tim operasional
                                                        bisa langsung memakainya.
                                                    </p>
                                                </div>
                                            </div>
                                        {:else}
                                            <div class="space-y-3 p-4">
                                                {#each day.rows as row (row.id)}
                                                    {@const rowOptions =
                                                        row.unit_options ?? []}
                                                    <article
                                                        class="rounded-2xl border border-border/70 bg-card/95 p-3 text-xs shadow-[0_18px_45px_-30px_rgba(15,23,42,0.16)]"
                                                    >
                                                        <div
                                                            class="flex items-start justify-between gap-3"
                                                        >
                                                            <div class="space-y-2">
                                                                <div
                                                                    class="flex items-center gap-2"
                                                                >
                                                                    <p
                                                                        class="text-lg font-semibold text-foreground"
                                                                    >
                                                                        {row.jam}
                                                                    </p>
                                                                    <span
                                                                        class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700"
                                                                    >
                                                                        {row.units}
                                                                        unit
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    class="flex flex-wrap gap-2"
                                                                >
                                                                    <span
                                                                        class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700"
                                                                    >
                                                                        {formatCurrency(
                                                                            Number(
                                                                                row.bop ||
                                                                                    0,
                                                                            ),
                                                                        )}
                                                                    </span>
                                                                    <span
                                                                        class="rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700"
                                                                    >
                                                                        {rowOptions.length >
                                                                        0
                                                                            ? `${rowOptions.length} layout`
                                                                            : 'Layout dasar'}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            {#if canWriteTab('schedules')}
                                                                <DropdownMenu>
                                                                    <DropdownMenuTrigger
                                                                        asChild
                                                                    >
                                                                        <Button
                                                                            type="button"
                                                                            variant="ghost"
                                                                            size="icon"
                                                                            class="h-8 w-8 rounded-full border border-border/70"
                                                                        >
                                                                            <MoreHorizontal
                                                                                class="h-4 w-4"
                                                                            />
                                                                            <span
                                                                                class="sr-only"
                                                                                >Aksi
                                                                                jadwal</span
                                                                            >
                                                                        </Button>
                                                                    </DropdownMenuTrigger>
                                                                    <DropdownMenuContent
                                                                        align="end"
                                                                        sideOffset={8}
                                                                        class="z-[120] w-44"
                                                                    >
                                                                        <DropdownMenuItem
                                                                            onclick={() =>
                                                                                openEditSchedule(
                                                                                    row,
                                                                                )}
                                                                        >
                                                                            <Pencil
                                                                                class="mr-2 h-3.5 w-3.5"
                                                                            />
                                                                            Edit
                                                                        </DropdownMenuItem>
                                                                        <DropdownMenuItem
                                                                            onclick={() =>
                                                                                void removeItem(
                                                                                    `/api/admin/schedules/${row.id}`,
                                                                                    'Schedule deleted.',
                                                                                )}
                                                                        >
                                                                            <Trash2
                                                                                class="mr-2 h-3.5 w-3.5"
                                                                            />
                                                                            Hapus
                                                                        </DropdownMenuItem>
                                                                    </DropdownMenuContent>
                                                                </DropdownMenu>
                                                            {/if}
                                                        </div>
                                                        <div
                                                            class="mt-3 rounded-xl border border-border/60 bg-background/85 p-3"
                                                        >
                                                            <div
                                                                class="flex items-center justify-between gap-3"
                                                            >
                                                                <p
                                                                    class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                                                                >
                                                                    Konfigurasi
                                                                    Unit
                                                                </p>
                                                                <span
                                                                    class="text-[11px] text-muted-foreground"
                                                                >
                                                                    {row.unit_label ||
                                                                        row.nopol ||
                                                                        'Belum ada label'}
                                                                </span>
                                                            </div>
                                                            {#if rowOptions.length > 0}
                                                                <div
                                                                    class="mt-3 space-y-2"
                                                                >
                                                                    {#each rowOptions as item (`schedule-unit-${row.id}-${item.unit_no}`)}
                                                                        <div
                                                                            class="flex items-start justify-between gap-3 rounded-xl border border-border/60 bg-muted/10 px-3 py-2"
                                                                        >
                                                                            <div
                                                                                class="space-y-0.5"
                                                                            >
                                                                                <p
                                                                                    class="font-medium text-foreground"
                                                                                >
                                                                                    {item.unit_no}.
                                                                                    {item.label}
                                                                                </p>
                                                                                <p
                                                                                    class="text-[11px] text-muted-foreground"
                                                                                >
                                                                                    Layout
                                                                                    keberangkatan
                                                                                </p>
                                                                            </div>
                                                                            <div
                                                                                class="text-right text-[11px] text-muted-foreground"
                                                                            >
                                                                                {item.nopol ||
                                                                                    'Layout belum dipilih'}
                                                                            </div>
                                                                        </div>
                                                                    {/each}
                                                                </div>
                                                            {:else}
                                                                <p
                                                                    class="mt-2 text-[11px] text-muted-foreground"
                                                                >
                                                                    Unit ini
                                                                    masih memakai
                                                                    label default
                                                                    dan belum
                                                                    memilih layout
                                                                    khusus.
                                                                </p>
                                                            {/if}
                                                        </div>
                                                    </article>
                                                {/each}
                                            </div>
                                        {/if}
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/if}
                </div>
            {/if}

            {#if activeTab === 'drivers'}
                {#if activeMode === 'form'}
                    <form onsubmit={saveDriver}>
                        <AdminOpsSection
                            eyebrow="Form Driver"
                            title={driverForm.id
                                ? 'Perbarui data driver'
                                : 'Tambah driver baru'}
                            description="Hubungkan driver ke armada lewat pencarian nopol, lalu atur target revenue dan fixed cost untuk perhitungan performa bulan berjalan."
                            toneClass="bg-[linear-gradient(135deg,rgba(16,185,129,0.08),rgba(15,23,42,0.03))]"
                            bodyClass="space-y-4"
                        >
                            <div class="rounded-2xl border border-border/70 bg-muted/20 p-4">
                                {#if isAllPoolMode}
                                    <label class="space-y-1.5">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Pool Target</span>
                                        <select
                                            class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm md:max-w-sm"
                                            bind:value={driverForm.pool_id}
                                            onchange={() => {
                                                driverForm.armada_id = 0;
                                                driverUnitSearch = '';
                                            }}
                                            required
                                        >
                                            <option value={0}>Pilih pool</option>
                                            {#each poolOptions as pool (pool.id)}
                                                <option value={pool.id}>{pool.name}</option>
                                            {/each}
                                        </select>
                                    </label>
                                {:else}
                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <span class="text-muted-foreground">Pool aktif</span>
                                        <Badge variant="secondary" class="rounded-full px-3 py-1">{activePoolName}</Badge>
                                    </div>
                                {/if}
                            </div>
                            <div
                                class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                            >
                                <label class="space-y-1.5">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Nama Driver</span
                                    >
                                    <Input
                                        placeholder="Nama driver"
                                        bind:value={driverForm.nama}
                                        required
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Phone</span
                                    >
                                    <Input
                                        placeholder="Nomor driver"
                                        bind:value={driverForm.phone}
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Fixed Cost</span
                                    >
                                    <Input
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="Fixed Cost"
                                        value={formatRupiahInput(
                                            driverForm.fixed_cost,
                                        )}
                                        oninput={(event) => {
                                            driverForm.fixed_cost =
                                                formatRupiahInput(
                                                    (
                                                        event.currentTarget as HTMLInputElement
                                                    ).value,
                                                );
                                        }}
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Target Revenue</span
                                    >
                                    <Input
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="Target Revenue"
                                        value={formatRupiahInput(
                                            driverForm.target_revenue_bulanan,
                                        )}
                                        oninput={(event) => {
                                            driverForm.target_revenue_bulanan =
                                                formatRupiahInput(
                                                    (
                                                        event.currentTarget as HTMLInputElement
                                                    ).value,
                                                );
                                        }}
                                    />
                                </label>
                            </div>

                            <div
                                class="rounded-2xl border border-input/70 bg-muted/10 p-4"
                            >
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted-foreground"
                                >
                                    Pencarian Armada
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Pilih nopol dari menu Armada agar relasi
                                    driver ke unit tersimpan dengan benar.
                                </p>
                                <div class="mt-4 space-y-3">
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Cari Nopol</span
                                        >
                                        <Input
                                            placeholder="Cari nopol dari menu Armada"
                                            bind:value={driverUnitSearch}
                                            list="driver-unit-options"
                                            oninput={syncDriverUnitSearch}
                                        />
                                    </label>
                                    <datalist id="driver-unit-options">
                                        {#each driverUnitOptions as unit (unit.id)}
                                            <option value={unit.nopol ?? ''}
                                                >{unit.nopol ?? ''}</option
                                            >
                                        {/each}
                                    </datalist>
                                    <div
                                        class="rounded-xl border border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {#if selectedDriverUnit}
                                            <span
                                                class="font-medium text-foreground"
                                                >{selectedDriverUnit.nopol}</span
                                            >
                                            {#if selectedDriverUnit.kategori}
                                                <span>
                                                    · {selectedDriverUnit.kategori}</span
                                                >
                                            {/if}
                                            {#if selectedDriverUnit.merk}
                                                <span>
                                                    · {selectedDriverUnit.merk}</span
                                                >
                                            {/if}
                                        {:else if driverUnitSearch.trim() !== ''}
                                            Pilih salah satu saran nopol dari
                                            menu Armada agar armada driver
                                            tersimpan.
                                        {:else}
                                            Ketik nopol untuk mencari armada
                                            yang dipakai driver.
                                        {/if}
                                    </div>
                                    {#if driverUnitOptions.length > 0}
                                        <div class="flex flex-wrap gap-2">
                                            {#each driverUnitOptions as unit (unit.id)}
                                                <button
                                                    type="button"
                                                    class={`rounded-full border px-3 py-1 text-xs transition ${
                                                        Number(
                                                            driverForm.armada_id ||
                                                                0,
                                                        ) === Number(unit.id)
                                                            ? 'border-primary bg-primary/10 text-primary'
                                                            : 'border-border/70 bg-background hover:border-primary/50 hover:text-foreground'
                                                    }`}
                                                    onclick={() =>
                                                        selectDriverUnit(unit)}
                                                >
                                                    {unit.nopol}
                                                </button>
                                            {/each}
                                        </div>
                                    {/if}
                                </div>
                            </div>

                            <div
                                class="rounded-xl border border-border/70 bg-muted/20 px-4 py-3 text-xs text-muted-foreground"
                            >
                                Semua metrik finansial driver dihitung untuk
                                bulan berjalan. Revenue driver dihitung otomatis
                                dari charter, penumpang keberangkatan, dan
                                bagasi ritur. Achievement = Total Revenue /
                                Target Revenue x 100%.
                            </div>

                            <div
                                class="flex flex-wrap gap-2 border-t border-border/70 pt-4"
                            >
                                <LoadingButton
                                    type="submit"
                                    loading={isSubmitActive('driver')}
                                    loadingText={driverForm.id
                                        ? 'Menyimpan...'
                                        : 'Membuat...'}
                                    >{driverForm.id
                                        ? 'Update Driver'
                                        : 'Tambah Driver'}</LoadingButton
                                >
                                <Button
                                    type="button"
                                    variant="outline"
                                    onclick={resetDriverForm}>Reset</Button
                                >
                            </div>
                        </AdminOpsSection>
                    </form>
                {:else}
                    <div
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(15,23,42,0.035),rgba(16,185,129,0.05))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between"
                        >
                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    Ringkasan Driver
                                </p>
                                <h3 class="mt-1 text-lg font-semibold">
                                    Kinerja pendapatan bulan berjalan
                                </h3>
                                <p
                                    class="mt-1 max-w-3xl text-sm text-muted-foreground"
                                >
                                    Nama driver tetap terkunci di kiri, kolom
                                    bisa disembunyikan lewat tombol Kolom, dan
                                    detail finansial lengkap muncul di baris
                                    expand agar tabel tetap enak dipindai.
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <Badge
                                    variant="secondary"
                                    class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                                >
                                    {drivers.length} driver aktif
                                </Badge>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        {#snippet children(props)}
                                            <Button
                                                type="button"
                                                variant="outline"
                                                class="h-9 rounded-full border-border/70 px-3 text-xs"
                                                {...props}
                                            >
                                                <SlidersHorizontal class="mr-2 h-3.5 w-3.5" />
                                                Kolom
                                                {#if hiddenDriverColumnCount > 0}
                                                    <span class="ml-2 rounded-full bg-muted px-2 py-0.5 text-[10px] font-semibold text-muted-foreground">
                                                        {hiddenDriverColumnCount}
                                                    </span>
                                                {/if}
                                            </Button>
                                        {/snippet}
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" sideOffset={8} class="w-64 p-1.5">
                                        <DropdownMenuLabel class="px-2 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                                            Tampilkan kolom
                                        </DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        {#each driverColumnOptions as option (option.key)}
                                            <DropdownMenuItem
                                                class="gap-2 rounded-md text-sm"
                                                onclick={() => toggleDriverColumn(option.key)}
                                            >
                                                <Check class={driverVisibleColumnKeys.includes(option.key) ? 'size-4 text-primary opacity-100' : 'size-4 opacity-0'} />
                                                <span class="min-w-0 flex-1 truncate">{option.label}</span>
                                            </DropdownMenuItem>
                                        {/each}
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem class="rounded-md text-sm" onclick={resetDriverColumns}>
                                            Tampilkan semua
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <div class="grid gap-3 p-3 md:hidden">
                            {#if drivers.length === 0}
                                <div class="rounded-2xl border border-dashed border-border/80 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Belum ada driver aktif.
                                </div>
                            {/if}
                            {#each drivers as row (row.id)}
                                {@const gross = driverGrossMargin(row)}
                                {@const net = driverNetMargin(row)}
                                {@const achievement = driverAchievement(row)}
                                {@const status = driverStatus(row)}
                                <article class="rounded-[24px] border border-border/80 bg-card/95 p-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-foreground">
                                                {row.nama}
                                            </p>
                                            <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                                {row.phone ?? 'Kontak belum diatur'}
                                            </p>
                                            <Badge variant="secondary" class="mt-2 w-fit rounded-full px-2.5 py-0.5 text-[10px]">
                                                {rowPoolName(row)}
                                            </Badge>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-1.5">
                                            <span
                                                class={`rounded-full border px-2 py-0.5 text-[10px] font-semibold ${
                                                    status === 'Tercapai'
                                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
                                                        : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300'
                                                }`}
                                            >
                                                {status}
                                            </span>
                                            {#if canWriteTab('drivers')}
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-8 w-8 rounded-full border border-border/70"
                                                    >
                                                        <MoreHorizontal class="h-4 w-4" />
                                                        <span class="sr-only">Aksi driver</span>
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent
                                                    align="end"
                                                    sideOffset={8}
                                                    class="z-[120] w-44"
                                                >
                                                    <DropdownMenuItem onclick={() => editDriver(row)}>
                                                        <Pencil class="mr-2 h-3.5 w-3.5" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            void removeItem(
                                                                `/api/admin/drivers/${row.id}`,
                                                                'Driver deleted.',
                                                            )}
                                                    >
                                                        <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                        Hapus
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                            {/if}
                                        </div>
                                    </div>

                                    <div class="mt-3 rounded-xl bg-muted/30 px-3 py-2 text-xs">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                            Armada Terpasang
                                        </p>
                                        <p class="mt-1 font-semibold text-foreground">
                                            {row.nopol ?? '-'}
                                        </p>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                        <div class="rounded-xl bg-emerald-50/70 px-3 py-2 dark:bg-emerald-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                                Revenue
                                            </p>
                                            <p class="mt-1 font-semibold text-emerald-800 dark:text-emerald-200">
                                                {formatCurrency(Number(row.revenue || 0))}
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-amber-50/80 px-3 py-2 dark:bg-amber-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                                                BOP
                                            </p>
                                            <p class="mt-1 font-semibold text-amber-800 dark:text-amber-200">
                                                {formatCurrency(Number(row.bop || 0))}
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-sky-50/80 px-3 py-2 dark:bg-sky-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">
                                                Net Margin
                                            </p>
                                            <p
                                                class={`mt-1 font-semibold ${net >= 0 ? 'text-sky-800 dark:text-sky-200' : 'text-rose-700 dark:text-rose-300'}`}
                                            >
                                                {formatCurrency(net)}
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-muted/40 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                                Achievement
                                            </p>
                                            <p class="mt-1 font-semibold text-foreground">
                                                {achievement.toFixed(1)}%
                                            </p>
                                        </div>
                                    </div>

                                    <details class="mt-3 border-t border-border/70 pt-2 text-xs">
                                        <summary class="cursor-pointer select-none py-1 font-semibold text-muted-foreground">
                                            Detail revenue dan biaya
                                        </summary>
                                        <div class="mt-2 grid grid-cols-2 gap-2">
                                            <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Charter</p>
                                                <p class="mt-1 font-semibold">{formatCurrency(Number(row.charter_revenue || 0))}</p>
                                            </div>
                                            <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Keberangkatan</p>
                                                <p class="mt-1 font-semibold">{formatCurrency(Number(row.departure_revenue || 0))}</p>
                                            </div>
                                            <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Bagasi</p>
                                                <p class="mt-1 font-semibold">{formatCurrency(Number(row.luggage_revenue || 0))}</p>
                                            </div>
                                            <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Gross</p>
                                                <p class="mt-1 font-semibold">{formatCurrency(gross)}</p>
                                            </div>
                                            <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Fixed Cost</p>
                                                <p class="mt-1 font-semibold">{formatCurrency(Number(row.fixed_cost || 0))}</p>
                                            </div>
                                            <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Target</p>
                                                <p class="mt-1 font-semibold">{formatCurrency(Number(row.target_revenue_bulanan || 0))}</p>
                                            </div>
                                        </div>
                                    </details>
                                </article>
                            {/each}
                        </div>
                        <div class="hidden overflow-x-auto md:block">
                            <DataTable columns={visibleDriverColumns} rows={drivers} class="min-w-[1640px]" expandedRows={expandedDriverRowIds}>
                                {#snippet row({ row })}
                                    {@const driver = row as DriverRow}
                                    {@const gross = driverGrossMargin(driver)}
                                    {@const net = driverNetMargin(driver)}
                                    {@const achievement = driverAchievement(driver)}
                                    {@const status = driverStatus(driver)}

                                    <td class="sticky left-0 z-20 w-[150px] border-b border-r border-border/60 bg-background px-3 py-2 align-top group-hover:bg-muted/15" style="left: 0px">
                                        <div class="flex items-start gap-2">
                                            <button
                                                type="button"
                                                class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-border/70 bg-background text-muted-foreground transition hover:border-primary/35 hover:text-primary"
                                                onclick={() => toggleDriverDetail(Number(driver.id))}
                                                aria-expanded={isDriverExpanded(Number(driver.id))}
                                                aria-label={isDriverExpanded(Number(driver.id)) ? 'Tutup detail driver' : 'Buka detail driver'}
                                            >
                                                {#if isDriverExpanded(Number(driver.id))}
                                                    <ChevronUp class="h-3.5 w-3.5" />
                                                {:else}
                                                    <ChevronDown class="h-3.5 w-3.5" />
                                                {/if}
                                            </button>
                                            <div class="min-w-0">
                                                <div class="truncate text-[11px] font-semibold text-foreground">{driver.nama}</div>
                                                <p class="mt-0.5 text-[10px] text-muted-foreground">Klik untuk detail finansial</p>
                                            </div>
                                        </div>
                                    </td>

                                    {#each visibleDriverColumns as column}
                                        {#if column.key !== 'nama'}
                                            {#if column.key === 'phone'}
                                                <td class="px-3 py-2 align-top">
                                                    <div class="truncate text-[11px] font-medium text-foreground">{driver.phone ?? '-'}</div>
                                                    <div class="mt-0.5 text-[10px] text-muted-foreground">Kontak operasional</div>
                                                </td>
                                            {:else if column.key === 'nopol'}
                                                <td class="px-3 py-2 align-top">
                                                    <EntityBadge code={driver.nopol ?? '-'} />
                                                </td>
                                            {:else if column.key === 'pool'}
                                                <td class="px-3 py-2 align-top">
                                                    <Badge variant="secondary" class="rounded-full px-2 py-0.5 text-[10px]">
                                                        {rowPoolName(driver)}
                                                    </Badge>
                                                </td>
                                            {:else if column.key === 'charter_revenue'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(driver.charter_revenue || 0))}</td>
                                            {:else if column.key === 'departure_revenue'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(driver.departure_revenue || 0))}</td>
                                            {:else if column.key === 'luggage_revenue'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(driver.luggage_revenue || 0))}</td>
                                            {:else if column.key === 'revenue'}
                                                <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(Number(driver.revenue || 0))}</td>
                                            {:else if column.key === 'charter_bop'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(driver.charter_bop || 0))}</td>
                                            {:else if column.key === 'departure_bop'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(driver.departure_bop || 0))}</td>
                                            {:else if column.key === 'bop'}
                                                <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(Number(driver.bop || 0))}</td>
                                            {:else if column.key === 'gross'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(gross)}</td>
                                            {:else if column.key === 'fixed_cost'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(driver.fixed_cost || 0))}</td>
                                            {:else if column.key === 'net'}
                                                <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(net)}</td>
                                            {:else if column.key === 'target_revenue_bulanan'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(driver.target_revenue_bulanan || 0))}</td>
                                            {:else if column.key === 'achievement'}
                                                <td class="px-3 py-2 text-right text-[11px] tabular-nums">{achievement.toFixed(1)}%</td>
                                            {:else if column.key === 'status'}
                                                <td class="px-3 py-2 text-center">
                                                    <span class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${status === 'Tercapai' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>{status}</span>
                                                </td>
                                            {/if}
                                        {/if}
                                    {/each}
                                {/snippet}

                                {#snippet detail({ row })}
                                    {@const driver = row as DriverRow}
                                    {@const gross = driverGrossMargin(driver)}
                                    {@const net = driverNetMargin(driver)}
                                    {@const achievement = driverAchievement(driver)}
                                    <div class="grid gap-3 xl:grid-cols-4">
                                        <div class="rounded-2xl border border-border/70 bg-background/80 p-3">
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-muted-foreground">Pendapatan</p>
                                            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                                <div class="rounded-xl bg-emerald-50/70 px-3 py-2 dark:bg-emerald-950/25">
                                                    <p class="text-[10px] uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Charter</p>
                                                    <p class="mt-1 font-semibold text-emerald-800 dark:text-emerald-200">{formatCurrency(Number(driver.charter_revenue || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-sky-50/70 px-3 py-2 dark:bg-sky-950/25">
                                                    <p class="text-[10px] uppercase tracking-wide text-sky-700 dark:text-sky-300">Keberangkatan</p>
                                                    <p class="mt-1 font-semibold text-sky-800 dark:text-sky-200">{formatCurrency(Number(driver.departure_revenue || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-violet-50/70 px-3 py-2 dark:bg-violet-950/25">
                                                    <p class="text-[10px] uppercase tracking-wide text-violet-700 dark:text-violet-300">Bagasi</p>
                                                    <p class="mt-1 font-semibold text-violet-800 dark:text-violet-200">{formatCurrency(Number(driver.luggage_revenue || 0))}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-border/70 bg-background/80 p-3">
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-muted-foreground">BOP & Biaya</p>
                                            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                                <div class="rounded-xl bg-amber-50/80 px-3 py-2 dark:bg-amber-950/25">
                                                    <p class="text-[10px] uppercase tracking-wide text-amber-700 dark:text-amber-300">Charter BOP</p>
                                                    <p class="mt-1 font-semibold text-amber-800 dark:text-amber-200">{formatCurrency(Number(driver.charter_bop || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-orange-50/80 px-3 py-2 dark:bg-orange-950/25">
                                                    <p class="text-[10px] uppercase tracking-wide text-orange-700 dark:text-orange-300">Keberangkatan BOP</p>
                                                    <p class="mt-1 font-semibold text-orange-800 dark:text-orange-200">{formatCurrency(Number(driver.departure_bop || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-rose-50/80 px-3 py-2 dark:bg-rose-950/25">
                                                    <p class="text-[10px] uppercase tracking-wide text-rose-700 dark:text-rose-300">Total BOP</p>
                                                    <p class="mt-1 font-semibold text-rose-800 dark:text-rose-200">{formatCurrency(Number(driver.bop || 0))}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-border/70 bg-background/80 p-3">
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-muted-foreground">Margin</p>
                                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Gross</p>
                                                    <p class="mt-1 font-semibold text-foreground">{formatCurrency(gross)}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Fixed Cost</p>
                                                    <p class="mt-1 font-semibold text-foreground">{formatCurrency(Number(driver.fixed_cost || 0))}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Net Margin</p>
                                                    <p class={`mt-1 font-semibold ${net >= 0 ? 'text-sky-800 dark:text-sky-200' : 'text-rose-700 dark:text-rose-300'}`}>{formatCurrency(net)}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Achievement</p>
                                                    <p class="mt-1 font-semibold text-foreground">{achievement.toFixed(1)}%</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-border/70 bg-background/80 p-3">
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-muted-foreground">Identitas</p>
                                            <div class="mt-3 space-y-2">
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Kontak</p>
                                                    <p class="mt-1 font-semibold text-foreground">{driver.phone ?? '-'}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Nopol</p>
                                                    <p class="mt-1 font-semibold text-foreground">{driver.nopol ?? '-'}</p>
                                                </div>
                                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Pool</p>
                                                    <p class="mt-1 font-semibold text-foreground">{rowPoolName(driver)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/snippet}

                                {#snippet actions({ row })}
                                    {#if canWriteTab('drivers')}
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                    <span class="sr-only">Aksi driver</span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                                <DropdownMenuItem onclick={() => editDriver(row as DriverRow)}>
                                                    <Pencil class="mr-2 h-3.5 w-3.5" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem onclick={() => void removeItem(`/api/admin/drivers/${row.id}`, 'Driver deleted.') }>
                                                    <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                    Hapus
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    {/if}
                                {/snippet}
                            </DataTable>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'services'}
                {#if activeMode === 'form'}
                    <form
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={saveService}
                    >
                        <div class="border-b border-border/70 bg-muted/20 px-5 py-4">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                Form Tarif Bagasi
                            </p>
                            <h3 class="mt-1 text-lg font-semibold">
                                {serviceForm.id
                                    ? 'Perbarui layanan bagasi'
                                    : 'Tambah layanan bagasi baru'}
                            </h3>
                            <p
                                class="mt-1 max-w-2xl text-sm text-muted-foreground"
                            >
                                Gunakan nama layanan yang mudah dikenali
                                operator agar pemetaan tarif bagasi tetap
                                konsisten.
                            </p>
                        </div>
                        <div
                            class="grid gap-4 p-5 md:grid-cols-[minmax(0,1fr)_auto] md:items-end"
                        >
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Nama Layanan</span
                                >
                                <Input
                                    placeholder="Contoh: Bagasi Kilat / Same Day"
                                    bind:value={serviceForm.name}
                                    required
                                />
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <LoadingButton
                                    type="submit"
                                    loading={savingService}
                                    loadingText={serviceForm.id
                                        ? 'Menyimpan...'
                                        : 'Membuat...'}
                                    >{serviceForm.id
                                        ? 'Update Layanan'
                                        : 'Tambah Layanan'}</LoadingButton
                                >
                                <Button
                                    type="button"
                                    variant="outline"
                                    onclick={resetServiceForm}>Reset</Button
                                >
                            </div>
                        </div>
                    </form>
                {:else}
                    <div
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                    >
                        <div class="flex flex-col gap-3 border-b border-border/70 bg-muted/20 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    Tarif Bagasi
                                </p>
                                <h3 class="mt-1 text-lg font-semibold">
                                    Daftar layanan bagasi
                                </h3>
                                <p
                                    class="mt-1 max-w-2xl text-sm text-muted-foreground"
                                >
                                    Struktur tabel dibuat singkat karena
                                    fokusnya hanya nama layanan dan aksi
                                    pengelolaannya.
                                </p>
                            </div>
                            <Badge
                                variant="secondary"
                                class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                            >
                                {services.length} layanan
                            </Badge>
                        </div>
                        <div class="grid gap-3 p-3 md:hidden">
                            {#if services.length === 0}
                                <div class="rounded-2xl border border-dashed border-border/80 bg-muted/20 p-4 text-sm text-muted-foreground">
                                    Belum ada layanan bagasi.
                                </div>
                            {/if}
                            {#each services as row (row.id)}
                                <article class="rounded-[24px] border border-border/80 bg-card/95 p-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-foreground">
                                                {row.name}
                                            </p>
                                            <p class="mt-0.5 text-xs text-muted-foreground">
                                                Referensi tarif dan layanan bagasi operasional.
                                            </p>
                                        </div>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 shrink-0 rounded-full border border-border/70"
                                                >
                                                    <MoreHorizontal class="h-4 w-4" />
                                                    <span class="sr-only">Aksi service</span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                sideOffset={8}
                                                class="z-[120] w-44"
                                            >
                                                <DropdownMenuItem
                                                    onclick={() => {
                                                        serviceForm = {
                                                            id: row.id,
                                                            name: row.name,
                                                        };
                                                        setFormMode('form');
                                                    }}
                                                >
                                                    <Pencil class="mr-2 h-3.5 w-3.5" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    disabled={pendingDeleteKey ===
                                                        `service-${row.id}`}
                                                    onclick={() =>
                                                        void removeItem(
                                                            `/api/admin/luggage-services/${row.id}`,
                                                            'Service deleted.',
                                                            {
                                                                confirmMessage:
                                                                    'Yakin ingin menghapus konfigurasi tarif bagasi ini?',
                                                                loadingMessage:
                                                                    'Menghapus konfigurasi tarif bagasi...',
                                                                errorMessage:
                                                                    'Gagal menghapus konfigurasi tarif bagasi.',
                                                                pendingKey: `service-${row.id}`,
                                                            },
                                                        )}
                                                >
                                                    <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                    {pendingDeleteKey ===
                                                    `service-${row.id}`
                                                        ? 'Menghapus...'
                                                        : 'Hapus'}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                    <div class="mt-3 rounded-xl bg-emerald-50/70 px-3 py-2 text-xs dark:bg-emerald-950/25">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                            Dipakai Untuk
                                        </p>
                                        <p class="mt-1 font-medium text-emerald-800 dark:text-emerald-200">
                                            Form transaksi dan laporan bagasi.
                                        </p>
                                    </div>
                                </article>
                            {/each}
                        </div>
                        <div class="hidden overflow-x-auto md:block">
                            <table
                                class="min-w-[720px] w-full border-separate border-spacing-0 text-sm"
                            >
                                <thead
                                    class="bg-muted/20 text-[11px] uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    <tr>
                                        <th
                                            class="w-[78%] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Nama Layanan</th
                                        >
                                        <th
                                            class="w-[22%] border-b border-border/70 px-4 py-3 text-center font-semibold"
                                            >Aksi</th
                                        >
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each services as row (row.id)}
                                        <tr
                                            class="group transition hover:bg-muted/15"
                                        >
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4"
                                            >
                                                <div
                                                    class="font-semibold text-foreground"
                                                >
                                                    {row.name}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Digunakan untuk referensi
                                                    tarif dan layanan bagasi
                                                    operasional.
                                                </div>
                                            </td>
                                            <td
                                                class="relative border-b border-border/60 px-4 py-4 text-center"
                                            >
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-8 w-8 rounded-full border border-border/70"
                                                        >
                                                            <MoreHorizontal
                                                                class="h-4 w-4"
                                                            />
                                                            <span
                                                                class="sr-only"
                                                                >Aksi service</span
                                                            >
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                        sideOffset={8}
                                                        class="z-[120] w-44"
                                                    >
                                                        <DropdownMenuItem
                                                            onclick={() => {
                                                                serviceForm = {
                                                                    id: row.id,
                                                                    name: row.name,
                                                                };
                                                                setFormMode(
                                                                    'form',
                                                                );
                                                            }}
                                                        >
                                                            <Pencil
                                                                class="mr-2 h-3.5 w-3.5"
                                                            />
                                                            Edit
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            disabled={pendingDeleteKey ===
                                                                `service-${row.id}`}
                                                            onclick={() =>
                                                                void removeItem(
                                                                    `/api/admin/luggage-services/${row.id}`,
                                                                    'Service deleted.',
                                                                    {
                                                                        confirmMessage:
                                                                            'Yakin ingin menghapus konfigurasi tarif bagasi ini?',
                                                                        loadingMessage:
                                                                            'Menghapus konfigurasi tarif bagasi...',
                                                                        errorMessage:
                                                                            'Gagal menghapus konfigurasi tarif bagasi.',
                                                                        pendingKey: `service-${row.id}`,
                                                                    },
                                                                )}
                                                        >
                                                            <Trash2
                                                                class="mr-2 h-3.5 w-3.5"
                                                            />
                                                            {pendingDeleteKey ===
                                                            `service-${row.id}`
                                                                ? 'Menghapus...'
                                                                : 'Hapus'}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'segments'}
                <div class="space-y-4">
                    <div
                        class="grid gap-3 md:grid-cols-[minmax(240px,1fr)_auto]"
                    >
                        <select
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                            bind:value={selectedSegmentRouteId}
                            onchange={async () => {
                                await changeSegmentRoute(
                                    Number(selectedSegmentRouteId),
                                );
                            }}
                        >
                            <option value={0}>Pilih rute induk</option>
                            {#each routes as row (row.id)}
                                <option value={row.id}>{row.name}</option>
                            {/each}
                        </select>
                        <Button
                            type="button"
                            variant="outline"
                            onclick={() => void setTab('routes')}
                            >Kelola rute induk</Button
                        >
                    </div>

                    {#if !selectedSegmentRoute}
                        <div
                            class="rounded-md border border-dashed p-3 text-sm text-muted-foreground"
                        >
                            Pilih rute induk dulu. Setelah dipilih, Anda bisa
                            menambah banyak segment dan harga tanpa memilih rute
                            berulang.
                        </div>
                    {:else if activeMode === 'form'}
                        <form onsubmit={saveSegment}>
                            <AdminOpsSection
                                eyebrow="Form Segment"
                                title={segmentForm.id
                                    ? 'Perbarui segment rute'
                                    : 'Tambah segment rute baru'}
                                description={`Segment ini akan ditautkan ke rute induk ${selectedSegmentRoute.name}, jadi arah perjalanan dan harga perlu dibuat sejelas mungkin.`}
                                badgeText={`${segments.length} segment aktif`}
                                toneClass="bg-muted/20"
                                bodyClass="space-y-4"
                            >
                                <input
                                    type="hidden"
                                    bind:value={segmentForm.route_id}
                                />
                                <div
                                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                                >
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Nama Segment</span
                                        >
                                        <Input
                                            placeholder="Nama segment"
                                            bind:value={segmentForm.rute}
                                            required
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Origin</span
                                        >
                                        <Input
                                            placeholder="Origin"
                                            bind:value={segmentForm.origin}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Destination</span
                                        >
                                        <Input
                                            placeholder="Destination"
                                            bind:value={segmentForm.destination}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Harga Segment</span
                                        >
                                        <Input
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="Harga segment"
                                            value={formatRupiahInput(
                                                segmentForm.harga,
                                            )}
                                            oninput={(event) => {
                                                segmentForm.harga =
                                                    parseRupiahInput(
                                                        (
                                                            event.currentTarget as HTMLInputElement
                                                        ).value,
                                                    );
                                            }}
                                            required
                                        />
                                    </label>
                                </div>
                                <div
                                    class="rounded-xl border border-border/70 bg-muted/20 px-4 py-3 text-xs text-muted-foreground"
                                >
                                    Segment ini akan muncul sebagai turunan dari
                                    rute induk yang sedang dipilih dan dipakai
                                    untuk referensi harga perjalanan parsial.
                                </div>
                                <div
                                    class="flex flex-wrap gap-2 border-t border-border/70 pt-4"
                                >
                                    <LoadingButton
                                        type="submit"
                                        loading={isSubmitActive('segment')}
                                        loadingText={segmentForm.id
                                            ? 'Menyimpan segment...'
                                            : 'Menambah segment...'}
                                        >{segmentForm.id
                                            ? 'Update Segment'
                                            : 'Tambah Segment'}</LoadingButton
                                    >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onclick={resetSegmentForm}>Reset</Button
                                    >
                                </div>
                            </AdminOpsSection>
                        </form>
                    {:else}
                        <div
                            class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        >
                            <div class="flex flex-col gap-3 border-b border-border/70 bg-muted/20 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                    >
                                        Segment Route
                                    </p>
                                    <h3 class="mt-1 text-lg font-semibold">
                                        {selectedSegmentRoute.name}
                                    </h3>
                                    <p
                                        class="mt-1 max-w-3xl text-sm text-muted-foreground"
                                    >
                                        Segment dibuat lebih ringkas dengan
                                        menggabungkan arah perjalanan dalam satu
                                        kolom dan menonjolkan harga agar cepat
                                        discan.
                                    </p>
                                </div>
                                <Badge
                                    variant="secondary"
                                    class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                                >
                                    {segments.length} segment aktif
                                </Badge>
                            </div>
                            <div class="grid gap-3 p-3 md:hidden">
                                {#if segments.length === 0}
                                    <div class="rounded-2xl border border-dashed border-border/80 bg-muted/20 p-4 text-sm text-muted-foreground">
                                        Belum ada segment untuk rute ini.
                                    </div>
                                {/if}
                                {#each segments as row (row.id)}
                                    <article class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-foreground">
                                                    {row.rute}
                                                </p>
                                                <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                                    {row.route_name ?? selectedSegmentRoute.name}
                                                </p>
                                            </div>
                                            {#if canWriteTab('segments')}
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-8 w-8 shrink-0 rounded-full border border-border/70"
                                                    >
                                                        <MoreHorizontal class="h-4 w-4" />
                                                        <span class="sr-only">Aksi segment</span>
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent
                                                    align="end"
                                                    sideOffset={8}
                                                    class="z-[120] w-44"
                                                >
                                                    <DropdownMenuItem onclick={() => editSegment(row)}>
                                                        <Pencil class="mr-2 h-3.5 w-3.5" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            void removeItem(
                                                                `/api/admin/segments/${row.id}`,
                                                                'Segment deleted.',
                                                            )}
                                                    >
                                                        <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                        Hapus
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                            {/if}
                                        </div>

                                        <div class="mt-3 rounded-xl bg-amber-50/80 px-3 py-2 text-xs dark:bg-amber-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                                                Harga Segment
                                            </p>
                                            <p class="mt-1 text-base font-semibold text-amber-800 dark:text-amber-200">
                                                {formatCurrency(Number(row.harga || 0))}
                                            </p>
                                        </div>

                                        <div class="mt-3 flex items-center gap-2 text-xs">
                                            <span class="min-w-0 truncate rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 font-medium">
                                                {row.origin ?? 'Origin belum diatur'}
                                            </span>
                                            <span class="shrink-0 text-muted-foreground">→</span>
                                            <span class="min-w-0 truncate rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 font-medium">
                                                {row.destination ?? 'Destination belum diatur'}
                                            </span>
                                        </div>
                                    </article>
                                {/each}
                            </div>
                            <div class="hidden overflow-x-auto md:block">
                                <table
                                    class="min-w-[920px] w-full border-separate border-spacing-0 text-sm"
                                >
                                    <thead
                                        class="bg-muted/20 text-[11px] uppercase tracking-[0.24em] text-muted-foreground"
                                    >
                                        <tr>
                                            <th
                                                class="w-[30%] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                                >Segment</th
                                            >
                                            <th
                                                class="w-[42%] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                                >Arah Perjalanan</th
                                            >
                                            <th
                                                class="w-[16%] border-b border-r border-border/70 px-4 py-3 text-right font-semibold"
                                                >Harga</th
                                            >
                                            <th
                                                class="w-[12%] border-b border-border/70 px-4 py-3 text-center font-semibold"
                                                >Aksi</th
                                            >
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#if segments.length === 0}
                                            <tr>
                                                <td
                                                    class="border-b border-border/60 px-4 py-4 text-muted-foreground"
                                                    colspan="4"
                                                    >Belum ada segment untuk
                                                    rute ini.</td
                                                >
                                            </tr>
                                        {:else}
                                            {#each segments as row (row.id)}
                                                <tr
                                                    class="group transition hover:bg-muted/15"
                                                >
                                                    <td
                                                        class="border-b border-r border-border/60 px-4 py-4 align-top"
                                                    >
                                                        <div
                                                            class="font-semibold text-foreground"
                                                        >
                                                            {row.rute}
                                                        </div>
                                                        <div
                                                            class="mt-1 text-[11px] text-muted-foreground"
                                                        >
                                                            Turunan dari rute
                                                            induk {selectedSegmentRoute.name}
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="border-b border-r border-border/60 px-4 py-4"
                                                    >
                                                        <div
                                                            class="flex flex-wrap items-center gap-2"
                                                        >
                                                            <span
                                                                class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium"
                                                                >{row.origin ??
                                                                    'Origin belum diatur'}</span
                                                            >
                                                            <span
                                                                class="text-muted-foreground"
                                                                >→</span
                                                            >
                                                            <span
                                                                class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium"
                                                                >{row.destination ??
                                                                    'Destination belum diatur'}</span
                                                            >
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="border-b border-r border-border/60 bg-amber-50/50 px-4 py-4 text-right"
                                                    >
                                                        <div
                                                            class="text-sm font-semibold text-amber-800 tabular-nums"
                                                        >
                                                            Rp {Number(
                                                                row.harga,
                                                            ).toLocaleString(
                                                                'id-ID',
                                                            )}
                                                        </div>
                                                        <div
                                                            class="mt-1 text-[10px] uppercase tracking-wide text-amber-700/80"
                                                        >
                                                            Harga Segment
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="relative border-b border-border/60 px-4 py-4 text-center"
                                                    >
                                                        {#if canWriteTab('segments')}
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger
                                                                asChild
                                                            >
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    class="h-8 w-8 rounded-full border border-border/70"
                                                                >
                                                                    <MoreHorizontal
                                                                        class="h-4 w-4"
                                                                    />
                                                                    <span
                                                                        class="sr-only"
                                                                        >Aksi
                                                                        segment</span
                                                                    >
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent
                                                                align="end"
                                                                sideOffset={8}
                                                                class="z-[120] w-44"
                                                            >
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        editSegment(
                                                                            row,
                                                                        )}
                                                                >
                                                                    <Pencil
                                                                        class="mr-2 h-3.5 w-3.5"
                                                                    />
                                                                    Edit
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void removeItem(
                                                                            `/api/admin/segments/${row.id}`,
                                                                            'Segment deleted.',
                                                                        )}
                                                                >
                                                                    <Trash2
                                                                        class="mr-2 h-3.5 w-3.5"
                                                                    />
                                                                    Hapus
                                                                </DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                        {/if}
                                                    </td>
                                                </tr>
                                            {/each}
                                        {/if}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {/if}
                </div>
            {/if}

            {#if activeTab === 'customers'}
                {#if activeMode === 'form'}
                    <form
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={saveCustomer}
                    >
                        <div class="border-b border-border/70 bg-muted/20 px-5 py-4">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                Form Customer Reguler
                            </p>
                            <h3 class="mt-1 text-lg font-semibold">
                                {customerForm.id
                                    ? 'Perbarui data customer'
                                    : 'Tambah customer reguler baru'}
                            </h3>
                            <p
                                class="mt-1 max-w-2xl text-sm text-muted-foreground"
                            >
                                Simpan data kontak, titik jemput, dan link Maps
                                agar tim operasional tidak perlu mencari ulang
                                saat proses booking.
                            </p>
                        </div>
                        <div
                            class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4"
                        >
                            {#if isAllPoolMode}
                                <label class="space-y-1.5">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Pool Target</span
                                    >
                                    <select
                                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                                        bind:value={customerForm.pool_id}
                                        required
                                    >
                                        <option value={0}>Pilih pool</option>
                                        {#each poolOptions as pool (pool.id)}
                                            <option value={pool.id}
                                                >{pool.name}</option
                                            >
                                        {/each}
                                    </select>
                                </label>
                            {:else}
                                <div class="space-y-1.5">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Pool Aktif</span
                                    >
                                    <div class="flex h-9 items-center rounded-md border border-input bg-muted/30 px-3 text-sm font-medium">
                                        {activePoolName}
                                    </div>
                                </div>
                            {/if}
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Nama Customer</span
                                >
                                <Input
                                    placeholder="Nama customer"
                                    bind:value={customerForm.name}
                                    required
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Phone</span
                                >
                                <Input
                                    placeholder="Nomor aktif"
                                    bind:value={customerForm.phone}
                                    required
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Pickup Point</span
                                >
                                <Input
                                    placeholder="Titik jemput utama"
                                    bind:value={customerForm.pickup_point}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Google Maps URL</span
                                >
                                <Input
                                    placeholder="URL Google Map (opsional)"
                                    bind:value={customerForm.gmaps}
                                />
                            </label>
                        </div>
                        <div
                            class="flex flex-wrap gap-2 border-t border-border/70 bg-muted/20 p-5"
                        >
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('customer')}
                                loadingText={customerForm.id
                                    ? 'Menyimpan...'
                                    : 'Membuat...'}
                                >{customerForm.id
                                    ? 'Update Customer'
                                    : 'Tambah Customer'}</LoadingButton
                            >
                            <Button
                                type="button"
                                variant="outline"
                                onclick={resetCustomerForm}>Reset</Button
                            >
                        </div>
                    </form>
                {:else}
                    <div
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                    >
                        <div class="flex flex-col gap-4 border-b border-border/70 bg-muted/20 px-5 py-4">
                            <div
                                class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                    >
                                        Customer Reguler
                                    </p>
                                    <h3 class="mt-1 text-lg font-semibold">
                                        Data pickup dan kontak pelanggan
                                    </h3>
                                    <p
                                        class="mt-1 max-w-3xl text-sm text-muted-foreground"
                                    >
                                        Informasi kontak, titik jemput, dan
                                        akses Maps diringkas agar operator bisa
                                        membaca dan mengedit data lebih cepat.
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <Badge
                                        variant="secondary"
                                        class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                                    >
                                        {customerMeta.total} customer
                                    </Badge>
                                    <a
                                        href="/api/admin/customers/template"
                                        class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        Download Template
                                    </a>
                                    <input
                                        bind:this={customerImportInput}
                                        type="file"
                                        accept=".csv,text/csv"
                                        class="hidden"
                                        onchange={importCustomers}
                                    />
                                    <LoadingButton
                                        type="button"
                                        variant="outline"
                                        loading={customerImporting}
                                        loadingText="Mengimpor..."
                                        onclick={openCustomerImportPicker}
                                    >
                                        Import Data
                                    </LoadingButton>
                                </div>
                            </div>
                            <div class="flex justify-end md:hidden">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="h-8 rounded-lg text-xs"
                                    onclick={() =>
                                        (customerFiltersExpanded =
                                            !customerFiltersExpanded)}
                                    aria-expanded={customerFiltersExpanded}
                                >
                                    {customerFiltersExpanded
                                        ? 'Sembunyikan Filter'
                                        : 'Tampilkan Filter'}
                                </Button>
                            </div>
                            <div class={customerFiltersExpanded
                                ? 'flex flex-col gap-2 md:flex-row'
                                : 'hidden md:flex md:flex-row'}>
                                <Input
                                    placeholder="Cari nama, phone, atau pickup point"
                                    bind:value={customerSearch}
                                />
                                <Button
                                    type="button"
                                    class="md:min-w-[120px]"
                                    onclick={() => void loadCustomers(1)}
                                    >Search</Button
                                >
                            </div>
                            {#if customerImportSummary}
                                <div
                                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                                >
                                    <p class="font-semibold">
                                        Import selesai:
                                        {customerImportSummary.created} data
                                        baru,
                                        {customerImportSummary.updated}
                                        diperbarui,
                                        {customerImportSummary.skipped}
                                        dilewati.
                                    </p>
                                    {#if customerImportSummary.errors.length > 0}
                                        <p class="mt-1 text-xs text-emerald-700">
                                            Catatan:
                                            {customerImportSummary.errors.join(
                                                ' | ',
                                            )}
                                        </p>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                        <div class="grid gap-3 p-3 md:hidden">
                            {#each customers as row (row.id)}
                                <article
                                    class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-foreground">
                                                {row.name}
                                            </p>
                                            <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                                {row.phone}
                                            </p>
                                            <Badge
                                                variant="secondary"
                                                class="mt-2 w-fit rounded-full px-2.5 py-0.5 text-[10px]"
                                            >
                                                {rowPoolName(row)}
                                            </Badge>
                                        </div>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 shrink-0 rounded-full border border-border/70"
                                                >
                                                    <MoreHorizontal class="h-4 w-4" />
                                                    <span class="sr-only">Aksi customer</span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                sideOffset={8}
                                                class="z-[120] w-44"
                                            >
                                                <DropdownMenuItem
                                                    onclick={() => {
                                                        customerForm = {
                                                            id: row.id,
                                                            name: row.name,
                                                            phone: row.phone,
                                                            pickup_point:
                                                                row.pickup_point ?? '',
                                                            gmaps: row.gmaps ?? '',
                                                            pool_id: Number(
                                                                row.pool_id ??
                                                                    defaultPoolId(),
                                                            ),
                                                        };
                                                        setFormMode('form');
                                                    }}
                                                >
                                                    <Pencil class="mr-2 h-3.5 w-3.5" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        void removeItem(
                                                            `/api/admin/customers/${row.id}`,
                                                            'Customer deleted.',
                                                        )}
                                                >
                                                    <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                    Hapus
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>

                                    <div class="mt-3 grid gap-2 text-xs">
                                        <div class="rounded-xl bg-muted/30 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                                Pickup Point
                                            </p>
                                            <p class="mt-1 break-words font-medium text-foreground">
                                                {row.pickup_point ?? '-'}
                                            </p>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 rounded-xl bg-muted/30 px-3 py-2">
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                                    Google Maps
                                                </p>
                                                <p class="mt-1 truncate font-medium text-foreground">
                                                    {row.gmaps ? 'Link tersedia' : 'Belum ada link'}
                                                </p>
                                            </div>
                                            {#if row.gmaps}
                                                <a
                                                    href={row.gmaps}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="shrink-0 rounded-full border border-primary/25 bg-primary/5 px-3 py-1 text-[11px] font-semibold text-primary"
                                                >
                                                    Maps
                                                </a>
                                            {/if}
                                        </div>
                                    </div>
                                </article>
                            {/each}
                        </div>
                        <div class="hidden overflow-x-auto md:block">
                            <table
                                class="min-w-[1180px] w-full border-separate border-spacing-0 text-sm"
                            >
                                <thead
                                    class="bg-muted/20 text-[11px] uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    <tr>
                                        <th
                                            class="sticky left-0 z-20 w-[240px] border-b border-r border-border/70 bg-background px-4 py-3 text-left font-semibold"
                                            >Customer</th
                                        >
                                        <th
                                            class="w-[190px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Phone</th
                                        >
                                        <th
                                            class="w-[180px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Pool</th
                                        >
                                        <th
                                            class="w-[310px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Pickup Point</th
                                        >
                                        <th
                                            class="w-[250px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Google Maps</th
                                        >
                                        <th
                                            class="sticky right-0 z-20 w-[90px] border-b border-l border-border/70 bg-background px-4 py-3 text-center font-semibold"
                                            >Aksi</th
                                        >
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each customers as row (row.id)}
                                        <tr
                                            class="group transition hover:bg-muted/15"
                                        >
                                            <td
                                                class="sticky left-0 z-10 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15"
                                            >
                                                <div
                                                    class="font-semibold text-foreground"
                                                >
                                                    {row.name}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Pelanggan reguler terdaftar
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4"
                                            >
                                                <div
                                                    class="font-medium text-foreground"
                                                >
                                                    {row.phone}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Kontak utama
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4"
                                            >
                                                <Badge
                                                    variant="secondary"
                                                    class="rounded-full px-2.5 py-1 text-[11px]"
                                                >
                                                    {rowPoolName(row)}
                                                </Badge>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4"
                                            >
                                                <div
                                                    class="font-medium text-foreground"
                                                >
                                                    {row.pickup_point ?? '-'}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Titik jemput operasional
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4"
                                            >
                                                {#if row.gmaps}
                                                    <a
                                                        href={row.gmaps}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex rounded-full border border-primary/25 bg-primary/5 px-3 py-1 text-xs font-semibold text-primary transition hover:bg-primary/10"
                                                    >
                                                        Buka Maps
                                                    </a>
                                                {:else}
                                                    <span
                                                        class="text-muted-foreground"
                                                        >Belum ada link</span
                                                    >
                                                {/if}
                                            </td>
                                            <td
                                                class="relative sticky right-0 z-10 border-b border-l border-border/60 bg-background px-4 py-4 text-center group-hover:bg-muted/15"
                                            >
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-8 w-8 rounded-full border border-border/70"
                                                        >
                                                            <MoreHorizontal
                                                                class="h-4 w-4"
                                                            />
                                                            <span
                                                                class="sr-only"
                                                                >Aksi customer</span
                                                            >
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                        sideOffset={8}
                                                        class="z-[120] w-44"
                                                    >
                                                        <DropdownMenuItem
                                                            onclick={() => {
                                                        customerForm = {
                                                            id: row.id,
                                                            name: row.name,
                                                            phone: row.phone,
                                                            pickup_point:
                                                                row.pickup_point ??
                                                                '',
                                                            gmaps:
                                                                row.gmaps ??
                                                                '',
                                                            pool_id: Number(
                                                                row.pool_id ??
                                                                    defaultPoolId(),
                                                            ),
                                                        };
                                                                setFormMode(
                                                                    'form',
                                                                );
                                                            }}
                                                        >
                                                            <Pencil
                                                                class="mr-2 h-3.5 w-3.5"
                                                            />
                                                            Edit
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onclick={() =>
                                                                void removeItem(
                                                                    `/api/admin/customers/${row.id}`,
                                                                    'Customer deleted.',
                                                                )}
                                                        >
                                                            <Trash2
                                                                class="mr-2 h-3.5 w-3.5"
                                                            />
                                                            Hapus
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex flex-col gap-3 border-t border-border/70 bg-muted/10 px-5 py-4 text-sm sm:flex-row sm:items-center sm:justify-between"
                        >
                            <p class="text-muted-foreground">
                                Total {customerMeta.total} customer · halaman
                                {customerMeta.page} dari
                                {customerMeta.last_page}
                            </p>
                            <div class="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    disabled={customerMeta.page <= 1}
                                    onclick={() =>
                                        void jumpCustomerPage(
                                            customerMeta.page - 1,
                                        )}>Prev</Button
                                >
                                <span
                                    class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-semibold text-foreground"
                                >
                                    {customerMeta.page} / {customerMeta.last_page}
                                </span>
                                <Button
                                    type="button"
                                    variant="outline"
                                    disabled={customerMeta.page >=
                                        customerMeta.last_page}
                                    onclick={() =>
                                        void jumpCustomerPage(
                                            customerMeta.page + 1,
                                        )}>Next</Button
                                >
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'units'}
                {#if activeMode === 'form'}
                    <form
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={saveUnit}
                    >
                        <div
                            class="border-b border-border/70 bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.12),transparent_36%),linear-gradient(135deg,rgba(248,250,252,0.94),rgba(255,255,255,0.82))] p-5"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                Template Kabin
                            </p>
                            <h3 class="mt-1 text-xl font-semibold">
                                Kategori Armada
                            </h3>
                            <p
                                class="mt-1 max-w-2xl text-sm text-muted-foreground"
                            >
                                Simpan tipe kabin sebagai template. Setelah data
                                dibuat, atur susunan kursi lewat tombol Atur
                                Layout di tabel.
                            </p>
                        </div>

                        <div
                            class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4"
                        >
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Nama Template</span
                                >
                                <Input
                                    placeholder="Contoh: Unit 1 / Bigbus 40 Seat"
                                    bind:value={unitForm.nama_model}
                                    required
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Kategori</span
                                >
                                <select
                                    class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                                    bind:value={unitForm.category}
                                    required
                                >
                                    {#each unitCategoryOptions as category (category)}
                                        <option value={category}
                                            >{category}</option
                                        >
                                    {/each}
                                </select>
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Kapasitas</span
                                >
                                <Input
                                    type="number"
                                    min="0"
                                    placeholder="Jumlah kursi"
                                    bind:value={unitForm.kapasitas}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Status</span
                                >
                                <select
                                    class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                                    bind:value={unitForm.status}
                                >
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </label>
                        </div>

                        <div
                            class="flex flex-wrap gap-2 border-t border-border/70 bg-muted/20 p-5"
                        >
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('unit')}
                                loadingText={unitForm.id
                                    ? 'Menyimpan...'
                                    : 'Membuat...'}
                            >
                                {unitForm.id
                                    ? 'Update Kategori'
                                    : 'Tambah Kategori'}
                            </LoadingButton>
                            <Button
                                type="button"
                                variant="outline"
                                onclick={resetUnitForm}>Reset</Button
                            >
                            <Button
                                type="button"
                                variant="ghost"
                                onclick={() => setFormMode('data')}
                                >Kembali</Button
                            >
                        </div>
                    </form>
                {:else if activeMode === 'layout'}
                    {#if UnitsLayoutPanelComponent}
                        <UnitsLayoutPanelComponent
                            {layoutUnit}
                            {layoutCapacity}
                            {layoutSeatCount}
                            {layoutOverCapacity}
                            {layoutRemainingSeats}
                            bind:layoutTemplateSearch
                            bind:layoutTemplateChoice
                            {layoutTemplateOptions}
                            {layoutEditorMessage}
                            {seatLayoutDraft}
                            {layoutEditorBusy}
                            {normalizeUnitCategory}
                            {unitSeatCount}
                            {rowPatternLabel}
                            {rowSeatCount}
                            {applyPatternToAllRows}
                            {resetLayoutDraft}
                            {addLayoutRow}
                            {removeLayoutRow}
                            {cloneLayoutFromTemplate}
                            {replaceLayoutRowPattern}
                            {duplicateLayoutRow}
                            {addLayoutItem}
                            {removeLayoutItem}
                            {saveUnitLayout}
                            goBackToData={() => setFormMode('data')}
                        />
                    {:else}
                        <div
                            class="rounded-xl border border-dashed border-border/70 bg-muted/20 p-4"
                        >
                            <p class="text-sm text-muted-foreground">
                                Memuat editor layout armada...
                            </p>
                        </div>
                    {/if}
                {:else}
                    <div class="space-y-4">
                        <div
                            class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        >
                            <div
                                class="grid gap-4 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.13),transparent_34%),linear-gradient(135deg,rgba(248,250,252,0.94),rgba(255,255,255,0.82))] p-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center"
                            >
                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                    >
                                        Kategori Armada
                                    </p>
                                    <h3 class="mt-1 text-xl font-semibold">
                                        Template layout kabin
                                    </h3>
                                    <p
                                        class="mt-1 max-w-2xl text-sm text-muted-foreground"
                                    >
                                        Kelola kapasitas dasar dan pola kursi.
                                        Gunakan Atur Layout untuk membentuk
                                        posisi kursi tanpa mengganggu data
                                        armada yang sudah berjalan.
                                    </p>
                                </div>
                                <Button type="button" onclick={openCreateForm}
                                    >Tambah Kategori</Button
                                >
                            </div>
                        </div>

                        <div class="grid gap-3 lg:hidden">
                            {#each units as row (row.id)}
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/95 p-4 shadow-sm"
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div>
                                            <p class="font-semibold">
                                                {row.nopol}
                                            </p>
                                            <div
                                                class="mt-2 flex flex-wrap gap-2"
                                            >
                                                <span
                                                    class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${categoryTone(row.category)}`}
                                                    >{normalizeUnitCategory(
                                                        row.category,
                                                    )}</span
                                                >
                                                <Badge
                                                    variant={row.status ===
                                                    'Aktif'
                                                        ? 'default'
                                                        : 'secondary'}
                                                    class="rounded-full px-2.5 py-1 text-[11px]"
                                                >
                                                    {row.status ?? '-'}
                                                </Badge>
                                                <Badge
                                                    variant="secondary"
                                                    class="rounded-full px-2.5 py-1 text-[11px]"
                                                >
                                                    {rowPoolName(row)}
                                                </Badge>
                                            </div>
                                        </div>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 rounded-full border border-border/70"
                                                >
                                                    <MoreHorizontal
                                                        class="h-4 w-4"
                                                    />
                                                    <span class="sr-only"
                                                        >Aksi kategori armada</span
                                                    >
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                sideOffset={8}
                                                class="z-[120] w-44"
                                            >
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        openUnitEditor(row)}
                                                >
                                                    <Pencil
                                                        class="mr-2 h-3.5 w-3.5"
                                                    />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        void removeItem(
                                                            `/api/admin/units/${row.id}`,
                                                            'Unit deleted.',
                                                        )}
                                                >
                                                    <Trash2
                                                        class="mr-2 h-3.5 w-3.5"
                                                    />
                                                    Hapus
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                    <div
                                        class="mt-4 grid grid-cols-2 gap-3 text-sm"
                                    >
                                        <div
                                            class="rounded-xl border border-border/70 bg-muted/20 p-3"
                                        >
                                            <p
                                                class="text-[11px] uppercase tracking-wide text-muted-foreground"
                                            >
                                                Kapasitas
                                            </p>
                                            <p class="mt-1 font-semibold">
                                                {row.kapasitas ?? 0} kursi
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-xl border border-border/70 bg-muted/20 p-3"
                                        >
                                            <p
                                                class="text-[11px] uppercase tracking-wide text-muted-foreground"
                                            >
                                                Layout
                                            </p>
                                            <p class="mt-1 font-semibold">
                                                {unitSeatCount(row.layout)} aktif
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="mt-4 w-full justify-center"
                                        onclick={() => openLayoutEditor(row)}
                                    >
                                        <Armchair class="mr-2 h-3.5 w-3.5" />
                                        Atur Layout
                                    </Button>
                                </div>
                            {/each}
                        </div>

                        <div
                            class="hidden overflow-hidden rounded-2xl border border-border/70 bg-background/90 lg:block"
                        >
                            <table class="w-full table-fixed text-sm">
                                <thead
                                    class="bg-muted/40 text-xs uppercase tracking-wide text-muted-foreground"
                                >
                                    <tr>
                                        <th class="w-[28%] px-4 py-3 text-left"
                                            >Nama Kategori Armada</th
                                        >
                                        <th class="w-[16%] px-4 py-3 text-left"
                                            >Kategori</th
                                        >
                                        <th class="w-[14%] px-4 py-3 text-left"
                                            >Pool</th
                                        >
                                        <th class="w-[18%] px-4 py-3 text-left"
                                            >Kapasitas/Layout</th
                                        >
                                        <th class="w-[12%] px-4 py-3 text-left"
                                            >Status</th
                                        >
                                        <th class="w-[12%] px-4 py-3 text-left"
                                            >Aksi</th
                                        >
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each units as row (row.id)}
                                        <tr
                                            class="border-t border-border/60 align-top transition hover:bg-muted/20"
                                        >
                                            <td class="px-4 py-3">
                                                <div class="font-medium">
                                                    {row.nopol}
                                                </div>
                                                <div
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    Template layout kabin
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${categoryTone(row.category)}`}
                                                    >{normalizeUnitCategory(
                                                        row.category,
                                                    )}</span
                                                >
                                            </td>
                                            <td class="px-4 py-3">
                                                <Badge
                                                    variant="secondary"
                                                    class="rounded-full px-2.5 py-1 text-[11px]"
                                                >
                                                    {rowPoolName(row)}
                                                </Badge>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium">
                                                    {row.kapasitas ?? 0} kursi
                                                </div>
                                                <div
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    {unitSeatCount(row.layout)} kursi
                                                    aktif di layout
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <Badge
                                                    variant={row.status ===
                                                    'Aktif'
                                                        ? 'default'
                                                        : 'secondary'}
                                                    class="rounded-full px-2.5 py-1 text-[11px]"
                                                >
                                                    {row.status ?? '-'}
                                                </Badge>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div
                                                    class="flex items-center justify-end gap-2"
                                                >
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        class="shrink-0"
                                                        onclick={() =>
                                                            openLayoutEditor(
                                                                row,
                                                            )}
                                                    >
                                                        <Armchair
                                                            class="mr-2 h-3.5 w-3.5"
                                                        />
                                                        Atur Layout
                                                    </Button>
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger
                                                            asChild
                                                        >
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="icon"
                                                                class="h-8 w-8 rounded-full border border-border/70"
                                                            >
                                                                <MoreHorizontal
                                                                    class="h-4 w-4"
                                                                />
                                                                <span
                                                                    class="sr-only"
                                                                    >Aksi
                                                                    kategori
                                                                    armada</span
                                                                >
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent
                                                            align="end"
                                                            sideOffset={8}
                                                            class="z-[120] w-44"
                                                        >
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    openUnitEditor(
                                                                        row,
                                                                    )}
                                                            >
                                                                <Pencil
                                                                    class="mr-2 h-3.5 w-3.5"
                                                                />
                                                                Edit
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void removeItem(
                                                                        `/api/admin/units/${row.id}`,
                                                                        'Unit deleted.',
                                                                    )}
                                                            >
                                                                <Trash2
                                                                    class="mr-2 h-3.5 w-3.5"
                                                                />
                                                                Hapus
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </div>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'armadas'}
                {#if activeMode === 'form'}
                    <form onsubmit={saveArmada}>
                        <AdminOpsSection
                            eyebrow="Form Armada"
                            title={armadaForm.id
                                ? 'Perbarui data armada'
                                : 'Tambah armada baru'}
                            description="Atur identitas armada, hubungkan dengan kategori layout, lalu tetapkan target revenue dan fixed cost untuk evaluasi performa bulanan."
                            toneClass="bg-[linear-gradient(135deg,rgba(14,165,233,0.08),rgba(15,23,42,0.03))]"
                            bodyClass="space-y-4"
                        >
                            <div
                                class="rounded-2xl border border-border/70 bg-background/90 p-4"
                            >
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted-foreground"
                                >
                                    Info Armada
                                </p>
                                <div class="mt-4 rounded-xl border border-border/70 bg-muted/20 p-3">
                                    {#if isAllPoolMode}
                                        <label class="space-y-1.5">
                                            <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Pool Target</span>
                                            <select
                                                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm md:max-w-sm"
                                                bind:value={armadaForm.pool_id}
                                                onchange={() => {
                                                    armadaForm.kategori = '';
                                                    armadaTemplateSearch = '';
                                                    armadaTemplateLookupOpen = false;
                                                }}
                                                required
                                            >
                                                <option value={0}>Pilih pool</option>
                                                {#each poolOptions as pool (pool.id)}
                                                    <option value={pool.id}>{pool.name}</option>
                                                {/each}
                                            </select>
                                        </label>
                                    {:else}
                                        <div class="flex flex-wrap items-center gap-2 text-sm">
                                            <span class="text-muted-foreground">Pool aktif</span>
                                            <Badge variant="secondary" class="rounded-full px-3 py-1">{activePoolName}</Badge>
                                        </div>
                                    {/if}
                                </div>
                                <div
                                    class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                                >
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Merk</span
                                        >
                                        <Input
                                            placeholder="Merk"
                                            bind:value={armadaForm.merk}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >No. Polisi</span
                                        >
                                        <Input
                                            placeholder="No. Polisi"
                                            bind:value={armadaForm.nopol}
                                            required
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Nomor Rangka</span
                                        >
                                        <Input
                                            placeholder="Nomor rangka"
                                            bind:value={armadaForm.nomor_rangka}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Tahun</span
                                        >
                                        <Input
                                            type="number"
                                            min="0"
                                            placeholder="Tahun"
                                            bind:value={armadaForm.tahun}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Warna</span
                                        >
                                        <Input
                                            placeholder="Warna"
                                            bind:value={armadaForm.warna}
                                        />
                                    </label>
                                    <label class="space-y-1.5 relative">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Cari Nama Layout Armada</span
                                        >
                                        <Input
                                            placeholder="Cari nama template layout dari menu Kategori Armada"
                                            bind:value={armadaTemplateSearch}
                                            oninput={queueArmadaTemplateSearch}
                                            onfocus={() => {
                                                armadaTemplateLookupOpen = true;
                                            }}
                                            onblur={onArmadaTemplateBlur}
                                        />
                                        {#if armadaTemplateLookupOpen}
                                            <div
                                                class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl"
                                            >
                                                {#if filteredArmadaTemplateOptions.length === 0}
                                                    <p
                                                        class="px-2 py-2 text-xs text-muted-foreground"
                                                    >
                                                        Template layout armada tidak ditemukan.
                                                    </p>
                                                {:else}
                                                    <div class="space-y-1">
                                                        {#each filteredArmadaTemplateOptions as unit (unit.id)}
                                                            <button
                                                                type="button"
                                                                class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70 dark:hover:border-cyan-500/20 dark:hover:bg-cyan-950/20"
                                                                onmousedown={(
                                                                    event,
                                                                ) => {
                                                                    event.preventDefault();
                                                                    selectArmadaTemplate(
                                                                        unit,
                                                                    );
                                                                }}
                                                            >
                                                                <span>
                                                                    <span
                                                                        class="block text-sm font-semibold text-foreground"
                                                                        >{unit.nopol}</span
                                                                    >
                                                                    <span
                                                                        class="block text-[11px] text-muted-foreground"
                                                                        >{normalizeUnitCategory(unit.category)} · {unitSeatCount(unit.layout)} kursi</span
                                                                    >
                                                                </span>
                                                            </button>
                                                        {/each}
                                                    </div>
                                                {/if}
                                            </div>
                                        {/if}
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Kategori Armada</span
                                        >
                                        <select
                                            class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                            bind:value={armadaForm.kategori}
                                            required
                                        >
                                            <option value=""
                                                >Pilih kategori armada</option
                                            >
                                            {#each armadaCategoryOptions as category (category)}
                                                <option value={category}
                                                    >{category}</option
                                                >
                                            {/each}
                                        </select>
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Tipe AC</span
                                        >
                                        <select
                                            class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                            bind:value={armadaForm.ac_type}
                                        >
                                            <option value="AC">AC</option>
                                            <option value="Non-AC"
                                                >Non-AC</option
                                            >
                                        </select>
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Platform GPS</span
                                        >
                                        <Input
                                            placeholder="Platform GPS"
                                            bind:value={armadaForm.platform_gps}
                                        />
                                    </label>
                                    <label class="space-y-1.5 xl:col-span-2">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >API GPS</span
                                        >
                                        <Input
                                            placeholder="API GPS"
                                            bind:value={armadaForm.api_gps}
                                        />
                                    </label>
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-border/70 bg-background/90 p-4"
                            >
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted-foreground"
                                >
                                    Konfigurasi Finansial Armada
                                </p>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Target Revenue</span
                                        >
                                        <Input
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="Target Revenue (contoh: Rp 60.000.000)"
                                            value={formatRupiahInput(
                                                armadaForm.target_bulanan,
                                            )}
                                            oninput={(event) => {
                                                armadaForm.target_bulanan =
                                                    formatRupiahInput(
                                                        (
                                                            event.currentTarget as HTMLInputElement
                                                        ).value,
                                                    );
                                            }}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Fixed Cost</span
                                        >
                                        <Input
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="Fixed Cost"
                                            value={formatRupiahInput(
                                                armadaForm.fixed_cost,
                                            )}
                                            oninput={(event) => {
                                                armadaForm.fixed_cost =
                                                    formatRupiahInput(
                                                        (
                                                            event.currentTarget as HTMLInputElement
                                                        ).value,
                                                    );
                                            }}
                                        />
                                    </label>
                                </div>
                                <p
                                    class="mt-3 text-[11px] text-muted-foreground"
                                >
                                    Hasil finansial dihitung untuk bulan
                                    berjalan. Revenue otomatis dari carter,
                                    penumpang keberangkatan, dan bagasi ritur
                                    berdasarkan nopol. Gross Margin = Revenue -
                                    BOP; Net Margin = Gross Margin - Fixed Cost;
                                    Achievement = Revenue / Target Revenue x
                                    100%.
                                </p>
                            </div>

                            <div
                                class="flex flex-wrap gap-2 border-t border-border/70 pt-4"
                            >
                                <LoadingButton
                                    type="submit"
                                    loading={isSubmitActive('armada')}
                                    loadingText={armadaForm.id
                                        ? 'Menyimpan armada...'
                                        : 'Membuat armada...'}
                                    >{armadaForm.id
                                        ? 'Update Armada'
                                        : 'Tambah Armada'}</LoadingButton
                                >
                                <Button
                                    type="button"
                                    variant="outline"
                                    onclick={resetArmadaForm}>Reset</Button
                                >
                            </div>
                        </AdminOpsSection>
                    </form>
                {:else if activeMode === 'view'}
                    {#if ArmadasPanelComponent}
                        <ArmadasPanelComponent
                            activeMode="view"
                            {armadaDetail}
                            {formatCurrency}
                            {armadaGrossMargin}
                            {armadaNetMargin}
                            {armadaAchievement}
                            {armadaStatus}
                            {categoryTone}
                            {normalizeUnitCategory}
                            {loadArmadas}
                            {openArmadaView}
                            {openArmadaEditor}
                            canManage={canWriteTab('armadas')}
                            removeArmada={(id: number) =>
                                removeItem(
                                    `/api/admin/armadas/${id}`,
                                    'Armada deleted.',
                                )}
                            goBackToData={() => setFormMode('data')}
                        />
                    {:else}
                        <div
                            class="rounded-xl border border-dashed border-border/70 bg-muted/20 p-4"
                        >
                            <p class="text-sm text-muted-foreground">
                                Memuat detail armada...
                            </p>
                        </div>
                    {/if}
                {:else if ArmadasPanelComponent}
                    <ArmadasPanelComponent
                        activeMode="data"
                        {armadas}
                        bind:armadaSearch
                        {formatCurrency}
                        {armadaGrossMargin}
                        {armadaNetMargin}
                        {armadaAchievement}
                        {armadaStatus}
                        {categoryTone}
                        {normalizeUnitCategory}
                        {loadArmadas}
                        {openArmadaView}
                        {openArmadaEditor}
                        canManage={canWriteTab('armadas')}
                        removeArmada={(id: number) =>
                            removeItem(
                                `/api/admin/armadas/${id}`,
                                'Armada deleted.',
                            )}
                        goBackToData={() => setFormMode('data')}
                    />
                {:else}
                    <div
                        class="rounded-xl border border-dashed border-border/70 bg-muted/20 p-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            Memuat ringkasan armada...
                        </p>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'pools'}
                {#if activeMode === 'form'}
                    <form
                        class="overflow-hidden rounded-[28px] border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={savePool}
                    >
                        <div
                            class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(8,145,178,0.10),rgba(15,23,42,0.03))] px-5 py-4"
                        >
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                Form Pool
                            </p>
                            <h3 class="mt-1 text-xl font-semibold tracking-tight">
                                {poolForm.id
                                    ? 'Perbarui pool operasional'
                                    : 'Tambah pool operasional'}
                            </h3>
                            <p
                                class="mt-1 max-w-2xl text-sm text-muted-foreground"
                            >
                                Mapping pool menentukan rute yang bisa dilihat
                                user dan menjadi filter laporan per cabang atau
                                area operasional.
                            </p>
                        </div>
                        <div
                            class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4"
                        >
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Nama Pool</span
                                >
                                <Input
                                    placeholder="Contoh: Pool Makassar"
                                    bind:value={poolForm.name}
                                    required
                                    disabled={!canManagePools}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Kode</span
                                >
                                <Input
                                    placeholder="MKS"
                                    bind:value={poolForm.code}
                                    disabled={!canManagePools}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Target Revenue</span
                                >
                                <Input
                                    placeholder="Rp 0"
                                    bind:value={poolForm.target_revenue}
                                    oninput={(event) => {
                                        poolForm.target_revenue =
                                            formatRupiahInput(
                                                (
                                                    event.currentTarget as HTMLInputElement
                                                ).value,
                                            );
                                    }}
                                    disabled={!canManagePools}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Fixed Cost</span
                                >
                                <Input
                                    placeholder="Rp 0"
                                    bind:value={poolForm.fixed_cost}
                                    oninput={(event) => {
                                        poolForm.fixed_cost =
                                            formatRupiahInput(
                                                (
                                                    event.currentTarget as HTMLInputElement
                                                ).value,
                                            );
                                    }}
                                    disabled={!canManagePools}
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Status</span
                                >
                                <select
                                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-ring/20"
                                    bind:value={poolForm.status}
                                    disabled={!canManagePools}
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </label>
                            <label class="space-y-1.5 md:col-span-2 xl:col-span-4">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Catatan</span
                                >
                                <Input
                                    placeholder="Catatan internal pool"
                                    bind:value={poolForm.notes}
                                    disabled={!canManagePools}
                                />
                            </label>
                        </div>
                        <div class="border-t border-border/70 p-5">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Rute Yang Dikelola
                                    </p>
                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Pilih satu atau beberapa rute induk untuk
                                        pool ini.
                                    </p>
                                </div>
                                <Badge variant="secondary" class="rounded-full">
                                    {poolForm.route_ids.length} rute
                                </Badge>
                            </div>
                            <div
                                class="grid max-h-[320px] gap-2 overflow-auto rounded-2xl border border-border/70 bg-muted/10 p-3 md:grid-cols-2 xl:grid-cols-3"
                            >
                                {#each routes as route (route.id)}
                                    <label
                                        class="flex items-start gap-3 rounded-xl border border-border/70 bg-background/80 p-3 text-sm transition hover:border-cyan-300/60"
                                    >
                                        <input
                                            type="checkbox"
                                            class="mt-1"
                                            checked={poolForm.route_ids.includes(
                                                route.id,
                                            )}
                                            onchange={(event) =>
                                                togglePoolRoute(
                                                    route.id,
                                                    (
                                                        event.currentTarget as HTMLInputElement
                                                    ).checked,
                                                )}
                                            disabled={!canManagePools}
                                        />
                                        <span>
                                            <span class="block font-semibold">
                                                {route.name}
                                            </span>
                                            <span class="mt-1 block text-xs text-muted-foreground">
                                                {route.origin || '-'} - {route.destination ||
                                                    '-'}
                                            </span>
                                        </span>
                                    </label>
                                {/each}
                            </div>
                        </div>
                        <div
                            class="flex flex-wrap gap-2 border-t border-border/70 bg-muted/20 p-5"
                        >
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('pool')}
                                loadingText="Menyimpan..."
                                disabled={!canManagePools}
                                >{poolForm.id
                                    ? 'Update Pool'
                                    : 'Tambah Pool'}</LoadingButton
                            >
                            <Button
                                type="button"
                                variant="outline"
                                onclick={resetPoolForm}>Reset</Button
                            >
                        </div>
                    </form>
                {:else}
                    <div
                        class="overflow-hidden rounded-[28px] border border-border/70 bg-background/95 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(8,145,178,0.08),rgba(15,23,42,0.03))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between"
                        >
                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    Pool
                                </p>
                                <h3 class="mt-1 text-xl font-semibold tracking-tight">
                                    Area operasional dan rute terkelola
                                </h3>
                                <p
                                    class="mt-1 max-w-3xl text-sm text-muted-foreground"
                                >
                                    Pool menjadi pagar akses data untuk user dan
                                    filter laporan per area.
                                </p>
                            </div>
                            <Badge
                                variant="secondary"
                                class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                            >
                                {pools.length} pool
                            </Badge>
                        </div>
                        <div class="grid gap-3 p-3 lg:hidden">
                            {#each pools as row (row.id)}
                                {@const net = financialNetMargin(row)}
                                {@const achievement = financialAchievement(row)}
                                {@const healthStatus = financialStatus(row)}
                                <article class="rounded-[24px] border border-border/80 bg-card/95 p-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-foreground">{row.name}</p>
                                            <p class="mt-0.5 truncate text-xs text-muted-foreground">{row.code || 'Tanpa kode'}</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-1.5">
                                            <span class={`rounded-full border px-2 py-0.5 text-[10px] font-semibold ${row.status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700'}`}>
                                                {row.status === 'active' ? 'Aktif' : 'Nonaktif'}
                                            </span>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                        <span class="sr-only">Aksi pool</span>
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                                    <DropdownMenuItem onclick={() => {
                                                        poolForm = {
                                                            id: row.id,
                                                            name: row.name,
                                                            code: row.code,
                                                            target_revenue: formatRupiahInput(row.target_revenue),
                                                            fixed_cost: formatRupiahInput(row.fixed_cost),
                                                            status: row.status || 'active',
                                                            notes: row.notes || '',
                                                            route_ids: [...(row.route_ids ?? [])],
                                                        };
                                                        setFormMode('form');
                                                    }}>
                                                        <Pencil class="mr-2 h-3.5 w-3.5" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onclick={() => void removeItem(`/api/admin/pools/${row.id}`, 'Pool deleted.')}>
                                                        <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                        Hapus
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </div>

                                    <div class="mt-3 rounded-xl bg-muted/30 px-3 py-2 text-xs">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Rute</p>
                                        <p class="mt-1 line-clamp-2 font-medium text-foreground">{formatPoolRoutes(row)}</p>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                        <div class="rounded-xl bg-emerald-50/70 px-3 py-2 dark:bg-emerald-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Revenue</p>
                                            <p class="mt-1 font-semibold text-emerald-800 dark:text-emerald-200">{formatCurrency(Number(row.revenue || 0))}</p>
                                        </div>
                                        <div class="rounded-xl bg-amber-50/80 px-3 py-2 dark:bg-amber-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">BOP</p>
                                            <p class="mt-1 font-semibold text-amber-800 dark:text-amber-200">{formatCurrency(Number(row.bop || 0))}</p>
                                        </div>
                                        <div class="rounded-xl bg-sky-50/80 px-3 py-2 dark:bg-sky-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Net Margin</p>
                                            <p class={`mt-1 font-semibold ${net >= 0 ? 'text-sky-800 dark:text-sky-200' : 'text-rose-700 dark:text-rose-300'}`}>{formatCurrency(net)}</p>
                                        </div>
                                        <div class="rounded-xl bg-muted/40 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Target</p>
                                            <p class="mt-1 font-semibold text-foreground">{achievement.toFixed(1)}% · {healthStatus}</p>
                                        </div>
                                    </div>
                                </article>
                            {/each}
                        </div>
                        <div class="hidden overflow-x-auto lg:block">
                            <DataTable columns={poolsColumns} rows={pools} class="min-w-[1780px]">
                                {#snippet row({ row })}
                                    {@const gross = financialGrossMargin(row as PoolRow)}
                                    {@const net = financialNetMargin(row as PoolRow)}
                                    {@const achievement = financialAchievement(row as PoolRow)}
                                    {@const healthStatus = financialStatus(row as PoolRow)}

                                    <td class="px-3 py-2 align-top">
                                        <div class="truncate text-[11px] font-semibold text-foreground">{row.name}</div>
                                        <div class="mt-0.5 text-[10px] text-muted-foreground">{row.code || 'Tanpa kode'}</div>
                                        <span class={`mt-2 inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${row.status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700'}`}>{row.status === 'active' ? 'Aktif' : 'Nonaktif'}</span>
                                    </td>

                                    <td class="px-3 py-2 align-top"><div class="line-clamp-2 text-[11px] leading-4 text-foreground">{formatPoolRoutes(row as PoolRow)}</div></td>

                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.charter_revenue || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.departure_revenue || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.luggage_revenue || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(Number(row.revenue || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.charter_bop || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.departure_bop || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(Number(row.bop || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(gross)}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.fixed_cost || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(net)}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.target_revenue || 0))}</td>
                                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{achievement.toFixed(1)}%</td>
                                    <td class="px-3 py-2 text-center"><span class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${healthStatus === 'Tercapai' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>{healthStatus}</span></td>
                                {/snippet}

                                {#snippet actions({ row })}
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                                <MoreHorizontal class="h-4 w-4" />
                                                <span class="sr-only">Aksi pool</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                            <DropdownMenuItem onclick={() => {
                                                poolForm = {
                                                    id: row.id,
                                                    name: row.name,
                                                    code: row.code,
                                                    target_revenue: formatRupiahInput(row.target_revenue),
                                                    fixed_cost: formatRupiahInput(row.fixed_cost),
                                                    status: row.status || 'active',
                                                    notes: row.notes || '',
                                                    route_ids: [...(row.route_ids ?? [])],
                                                };
                                                setFormMode('form');
                                            }}>
                                                <Pencil class="mr-2 h-3.5 w-3.5" />
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem onclick={() => void removeItem(`/api/admin/pools/${row.id}`, 'Pool deleted.') }>
                                                <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                Hapus
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                {/snippet}
                            </DataTable>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'users'}
                {#if activeMode === 'form'}
                    <form
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={saveUser}
                    >
                        <div
                            class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(168,85,247,0.08),rgba(15,23,42,0.03))] px-5 py-4"
                        >
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                Form User
                            </p>
                            <h3 class="mt-1 text-lg font-semibold">
                                {userForm.id
                                    ? 'Perbarui akun pengguna'
                                    : 'Tambah akun pengguna baru'}
                            </h3>
                            <p
                                class="mt-1 max-w-2xl text-sm text-muted-foreground"
                            >
                                Atur identitas login dan gunakan password yang
                                kuat agar akses panel tetap aman.
                            </p>
                        </div>
                        <div
                            class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Nama User</span
                                >
                                <Input
                                    placeholder="Nama user"
                                    bind:value={userForm.name}
                                    required
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Email</span
                                >
                                <Input
                                    placeholder="Email aktif"
                                    type="email"
                                    bind:value={userForm.email}
                                    required
                                />
                            </label>
                            <label class="space-y-1.5">
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >Password</span
                                >
                                <Input
                                    type="password"
                                    placeholder={userForm.id
                                        ? 'Password baru (opsional)'
                                        : 'Password (minimal 8 karakter)'}
                                    bind:value={userForm.password}
                                />
                            </label>
                            <label
                                class="flex items-start gap-3 rounded-2xl border border-border/70 bg-muted/10 p-4"
                            >
                                <input
                                    type="checkbox"
                                    class="mt-1"
                                    bind:checked={userForm.is_super_admin}
                                    disabled={!canManagePools}
                                />
                                <span>
                                    <span class="block text-sm font-semibold">
                                        Super Admin
                                    </span>
                                    <span
                                        class="mt-1 block text-xs leading-5 text-muted-foreground"
                                    >
                                        Bisa melihat semua pool dan mengelola
                                        akses user.
                                    </span>
                                </span>
                            </label>
                            <div
                                class="space-y-2 rounded-2xl border border-border/70 bg-muted/10 p-4 md:col-span-2"
                            >
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Role Hak Akses
                                    </span>
                                    <Badge
                                        variant="secondary"
                                        class="rounded-full"
                                    >
                                        {userForm.role_ids.length} role
                                    </Badge>
                                </div>
                                <div
                                    class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3"
                                >
                                    {#each roleOptions as role (role.id)}
                                        <label
                                            class="flex items-start gap-2 rounded-xl border border-border/70 bg-background/80 p-3 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                class="mt-1"
                                                checked={userForm.role_ids.includes(
                                                    role.id,
                                                )}
                                                onchange={(event) =>
                                                    toggleUserRole(
                                                        role.id,
                                                        (
                                                            event.currentTarget as HTMLInputElement
                                                        ).checked,
                                                    )}
                                            />
                                            <span>
                                                <span class="font-semibold">
                                                    {role.name}
                                                </span>
                                                <span
                                                    class="mt-1 block text-xs leading-5 text-muted-foreground"
                                                >
                                                    {role.description ||
                                                        'Hak akses custom'}
                                                </span>
                                            </span>
                                        </label>
                                    {/each}
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Role menentukan menu dan aksi yang boleh
                                    dibuka. Pool tetap membatasi data yang
                                    terlihat.
                                </p>
                            </div>
                            <div
                                class="space-y-2 rounded-2xl border border-border/70 bg-muted/10 p-4 md:col-span-2"
                            >
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Pool Yang Dikelola
                                    </span>
                                    <Badge
                                        variant="secondary"
                                        class="rounded-full"
                                    >
                                        {userForm.pool_ids.length} pool
                                    </Badge>
                                </div>
                                <div
                                    class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3"
                                >
                                    {#each poolOptions as pool (pool.id)}
                                        <label
                                            class="flex items-start gap-2 rounded-xl border border-border/70 bg-background/80 p-3 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                class="mt-1"
                                                checked={userForm.pool_ids.includes(
                                                    pool.id,
                                                )}
                                                onchange={(event) =>
                                                    toggleUserPool(
                                                        pool.id,
                                                        (
                                                            event.currentTarget as HTMLInputElement
                                                        ).checked,
                                                    )}
                                                disabled={!canManagePools ||
                                                    userForm.is_super_admin}
                                            />
                                            <span>
                                                <span class="font-semibold">
                                                    {pool.name}
                                                </span>
                                                <span
                                                    class="mt-1 block text-xs text-muted-foreground"
                                                >
                                                    {pool.code || 'Tanpa kode'}
                                                </span>
                                            </span>
                                        </label>
                                    {/each}
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Jika user bukan Super Admin dan tidak punya
                                    pool, data route-based tidak akan terlihat.
                                </p>
                            </div>
                        </div>
                        <div
                            class="flex flex-wrap gap-2 border-t border-border/70 bg-muted/20 p-5"
                        >
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('user')}
                                loadingText={userForm.id
                                    ? 'Menyimpan...'
                                    : 'Membuat...'}
                                >{userForm.id
                                    ? 'Update User'
                                    : 'Tambah User'}</LoadingButton
                            >
                            <Button
                                type="button"
                                variant="outline"
                                onclick={resetUserForm}>Reset</Button
                            >
                        </div>
                    </form>
                {:else}
                    <div
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-4 border-b border-border/70 bg-[linear-gradient(135deg,rgba(168,85,247,0.05),rgba(15,23,42,0.03))] px-5 py-4"
                        >
                            <div
                                class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                    >
                                        Users
                                    </p>
                                    <h3 class="mt-1 text-lg font-semibold">
                                        Akses akun pengguna
                                    </h3>
                                    <p
                                        class="mt-1 max-w-3xl text-sm text-muted-foreground"
                                    >
                                        Status verifikasi dibuat lebih mudah
                                        discan, sementara aksi edit dan hapus
                                        dipindahkan ke meatball menu agar
                                        konsisten dengan menu lain.
                                    </p>
                                </div>
                                <Badge
                                    variant="secondary"
                                    class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                                >
                                    {users.length} akun
                                </Badge>
                            </div>
                            <div class="flex justify-end md:hidden">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="h-8 rounded-lg text-xs"
                                    onclick={() =>
                                        (userFiltersExpanded =
                                            !userFiltersExpanded)}
                                    aria-expanded={userFiltersExpanded}
                                >
                                    {userFiltersExpanded
                                        ? 'Sembunyikan Filter'
                                        : 'Tampilkan Filter'}
                                </Button>
                            </div>
                            <div class={userFiltersExpanded
                                ? 'flex flex-col gap-2 md:flex-row'
                                : 'hidden md:flex md:flex-row'}>
                                <Input
                                    placeholder="Cari nama atau email"
                                    bind:value={userSearch}
                                />
                                <Button
                                    type="button"
                                    class="md:min-w-[120px]"
                                    onclick={() => void loadUsers()}
                                    >Search</Button
                                >
                            </div>
                        </div>
                        <div class="grid gap-3 p-3 md:hidden">
                            {#each users as row (row.id)}
                                <article class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-foreground">{row.name}</p>
                                            <p class="mt-0.5 truncate text-xs text-muted-foreground">{row.email}</p>
                                        </div>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 shrink-0 rounded-full border border-border/70">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                    <span class="sr-only">Aksi user</span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                                <DropdownMenuItem onclick={() => {
                                                    userForm = {
                                                        id: row.id,
                                                        name: row.name,
                                                        email: row.email,
                                                        password: '',
                                                        is_super_admin: Boolean(row.is_super_admin),
                                                        pool_ids: [...(row.pool_ids ?? [])],
                                                        role_ids: [...(row.role_ids ?? [])],
                                                    };
                                                    setFormMode('form');
                                                }}>
                                                    <Pencil class="mr-2 h-3.5 w-3.5" />
                                                    Edit
                                                </DropdownMenuItem>
                                                {#if row.email_verified_at}
                                                    <DropdownMenuItem onclick={() => void runUserVerificationAction(row, 'unverify')}>
                                                        <MailX class="mr-2 h-3.5 w-3.5" />
                                                        Unverify
                                                    </DropdownMenuItem>
                                                {:else}
                                                    <DropdownMenuItem onclick={() => void runUserVerificationAction(row, 'verify')}>
                                                        <CheckCircle2 class="mr-2 h-3.5 w-3.5" />
                                                        Verify
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onclick={() => void runUserVerificationAction(row, 'send-verification')}>
                                                        <Send class="mr-2 h-3.5 w-3.5" />
                                                        Kirim Link
                                                    </DropdownMenuItem>
                                                {/if}
                                                <DropdownMenuItem onclick={() => void removeItem(`/api/admin/users/${row.id}`, 'User deleted.')}>
                                                    <Trash2 class="mr-2 h-3.5 w-3.5" />
                                                    Hapus
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <span class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${row.email_verified_at ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>
                                            {row.email_verified_at ? 'Verified' : 'Belum Verified'}
                                        </span>
                                        <span class="rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 text-[11px] font-semibold text-foreground">
                                            {row.is_super_admin ? 'Super Admin' : 'User Pool'}
                                        </span>
                                    </div>

                                    <div class="mt-3 grid gap-2 text-xs">
                                        <div class="rounded-xl bg-muted/30 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Role</p>
                                            <p class="mt-1 break-words font-medium text-foreground">
                                                {(row.role_names ?? []).length ? (row.role_names ?? []).join(', ') : 'Belum ada role'}
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-muted/30 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Pool</p>
                                            <p class="mt-1 break-words font-medium text-foreground">
                                                {row.is_super_admin
                                                    ? 'Semua Pool'
                                                    : (row.pool_names ?? []).length
                                                      ? (row.pool_names ?? []).join(', ')
                                                      : 'Belum dimapping'}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            {/each}
                        </div>
                        <div class="hidden overflow-x-auto md:block">
                            <table
                                class="min-w-[1380px] w-full border-separate border-spacing-0 text-sm"
                            >
                                <thead
                                    class="bg-muted/20 text-[11px] uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    <tr>
                                        <th
                                            class="sticky left-0 z-20 w-[240px] border-b border-r border-border/70 bg-background px-4 py-3 text-left font-semibold"
                                            >Nama</th
                                        >
                                        <th
                                            class="w-[390px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Email</th
                                        >
                                        <th
                                            class="w-[190px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Verified</th
                                        >
                                        <th
                                            class="w-[260px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Role</th
                                        >
                                        <th
                                            class="w-[260px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Pool</th
                                        >
                                        <th
                                            class="sticky right-0 z-20 w-[90px] border-b border-l border-border/70 bg-background px-4 py-3 text-center font-semibold"
                                            >Aksi</th
                                        >
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each users as row (row.id)}
                                        <tr
                                            class="group transition hover:bg-muted/15"
                                        >
                                            <td
                                                class="sticky left-0 z-10 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15"
                                            >
                                                <div
                                                    class="font-semibold text-foreground"
                                                >
                                                    {row.name}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    {row.is_super_admin
                                                        ? 'Super Admin'
                                                        : 'User Pool'}
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4"
                                            >
                                                <div
                                                    class="font-medium text-foreground"
                                                >
                                                    {row.email}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Alamat email login
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4"
                                            >
                                                <span
                                                    class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${row.email_verified_at ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}
                                                >
                                                    {row.email_verified_at
                                                        ? 'Sudah diverifikasi'
                                                        : 'Belum diverifikasi'}
                                                </span>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4 align-top"
                                            >
                                                <div
                                                    class="text-sm font-medium text-foreground"
                                                >
                                                    {(row.role_names ?? [])
                                                        .length
                                                        ? (
                                                              row.role_names ??
                                                              []
                                                          ).join(', ')
                                                        : 'Belum ada role'}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Hak akses menu dan aksi
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4 align-top"
                                            >
                                                <div
                                                    class="text-sm font-medium text-foreground"
                                                >
                                                    {row.is_super_admin
                                                        ? 'Semua Pool'
                                                        : (row.pool_names ?? [])
                                                                .length
                                                          ? (
                                                                row.pool_names ??
                                                                []
                                                            ).join(
                                                                ', ',
                                                            )
                                                          : 'Belum dimapping'}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Akses data operasional
                                                </div>
                                            </td>
                                            <td
                                                class="relative sticky right-0 z-10 border-b border-l border-border/60 bg-background px-4 py-4 text-center group-hover:bg-muted/15"
                                            >
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            class="h-8 w-8 rounded-full border border-border/70"
                                                        >
                                                            <MoreHorizontal
                                                                class="h-4 w-4"
                                                            />
                                                            <span
                                                                class="sr-only"
                                                                >Aksi user</span
                                                            >
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                        sideOffset={8}
                                                        class="z-[120] w-44"
                                                    >
                                                        <DropdownMenuItem
                                                            onclick={() => {
                                                                userForm = {
                                                                    id: row.id,
                                                                    name: row.name,
                                                                    email: row.email,
                                                                    password:
                                                                        '',
                                                                    is_super_admin:
                                                                        Boolean(
                                                                            row.is_super_admin,
                                                                        ),
                                                                    pool_ids: [
                                                                        ...(row.pool_ids ??
                                                                            []),
                                                                    ],
                                                                    role_ids: [
                                                                        ...(row.role_ids ??
                                                                            []),
                                                                    ],
                                                                };
                                                                setFormMode(
                                                                    'form',
                                                                );
                                                            }}
                                                        >
                                                            <Pencil
                                                                class="mr-2 h-3.5 w-3.5"
                                                            />
                                                            Edit
                                                        </DropdownMenuItem>
                                                        {#if row.email_verified_at}
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void runUserVerificationAction(
                                                                        row,
                                                                        'unverify',
                                                                    )}
                                                            >
                                                                <MailX class="mr-2 h-3.5 w-3.5" />
                                                                Unverify
                                                            </DropdownMenuItem>
                                                        {:else}
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void runUserVerificationAction(
                                                                        row,
                                                                        'verify',
                                                                    )}
                                                            >
                                                                <CheckCircle2 class="mr-2 h-3.5 w-3.5" />
                                                                Verify
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void runUserVerificationAction(
                                                                        row,
                                                                        'send-verification',
                                                                    )}
                                                            >
                                                                <Send class="mr-2 h-3.5 w-3.5" />
                                                                Kirim Link
                                                            </DropdownMenuItem>
                                                        {/if}
                                                        <DropdownMenuItem
                                                            onclick={() =>
                                                                void removeItem(
                                                                    `/api/admin/users/${row.id}`,
                                                                    'User deleted.',
                                                                )}
                                                        >
                                                            <Trash2
                                                                class="mr-2 h-3.5 w-3.5"
                                                            />
                                                            Hapus
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'cancellations'}
                <div
                    class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(239,68,68,0.05),rgba(15,23,42,0.03))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                Logs Aktivitas
                            </p>
                            <h3 class="mt-1 text-lg font-semibold">
                                Jejak perubahan operasional
                            </h3>
                            <p
                                class="mt-1 max-w-3xl text-sm text-muted-foreground"
                            >
                                Riwayat aktivitas dibuat lebih mudah discan
                                dengan pemisahan yang jelas antara waktu, tag,
                                aktivitas, detail, dan aktor.
                            </p>
                        </div>
                        <Badge
                            variant="secondary"
                            class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                        >
                            {cancellations.length} aktivitas
                        </Badge>
                    </div>
                    <div class="grid gap-3 p-3 md:hidden">
                        {#each cancellations as row (`mobile-${row.created_at}-${row.tag}-${row.title}-${row.actor}`)}
                            <article class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="line-clamp-2 text-sm font-semibold text-foreground">{row.title || '-'}</p>
                                        <p class="mt-1 truncate text-xs text-muted-foreground">{row.created_at || '-'}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                        {row.tag || '-'}
                                    </span>
                                </div>
                                <div class="mt-3 grid gap-2 text-xs">
                                    <div class="rounded-xl bg-muted/30 px-3 py-2">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Detail</p>
                                        <p class="mt-1 break-words font-medium text-foreground">{row.meta || '-'}</p>
                                    </div>
                                    <div class="rounded-xl bg-muted/30 px-3 py-2">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Aktor</p>
                                        <p class="mt-1 break-words font-medium text-foreground">{row.actor || '-'}</p>
                                    </div>
                                </div>
                            </article>
                        {/each}
                    </div>
                    <div class="hidden overflow-x-auto md:block">
                        <table
                            class="min-w-[1180px] w-full border-separate border-spacing-0 text-sm"
                        >
                            <thead
                                class="bg-muted/20 text-[11px] uppercase tracking-[0.24em] text-muted-foreground"
                            >
                                <tr>
                                    <th
                                        class="w-[180px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                        >Waktu</th
                                    >
                                    <th
                                        class="w-[140px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                        >Tag</th
                                    >
                                    <th
                                        class="w-[320px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                        >Aktivitas</th
                                    >
                                    <th
                                        class="w-[360px] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                        >Detail</th
                                    >
                                    <th
                                        class="w-[180px] border-b border-border/70 px-4 py-3 text-left font-semibold"
                                        >Aktor</th
                                    >
                                </tr>
                            </thead>
                            <tbody>
                                {#each cancellations as row (`${row.created_at}-${row.tag}-${row.title}-${row.actor}`)}
                                    <tr
                                        class="group transition hover:bg-muted/15"
                                    >
                                        <td
                                            class="border-b border-r border-border/60 px-4 py-4 align-top"
                                        >
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.created_at || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-[11px] text-muted-foreground"
                                            >
                                                Waktu tercatat
                                            </div>
                                        </td>
                                        <td
                                            class="border-b border-r border-border/60 px-4 py-4 align-top"
                                        >
                                            <span
                                                class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-700"
                                            >
                                                {row.tag || '-'}
                                            </span>
                                        </td>
                                        <td
                                            class="border-b border-r border-border/60 px-4 py-4 align-top"
                                        >
                                            <div
                                                class="font-semibold text-foreground"
                                            >
                                                {row.title || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-[11px] text-muted-foreground"
                                            >
                                                Aktivitas yang dilakukan sistem
                                                atau operator
                                            </div>
                                        </td>
                                        <td
                                            class="border-b border-r border-border/60 px-4 py-4 align-top text-sm text-muted-foreground"
                                        >
                                            {row.meta || '-'}
                                        </td>
                                        <td
                                            class="border-b border-border/60 px-4 py-4 align-top"
                                        >
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.actor || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-[11px] text-muted-foreground"
                                            >
                                                Pelaku perubahan
                                            </div>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>
            {/if}

            {#if activeTab === 'reports'}
                {#if ReportsPanelComponent}
                    <ReportsPanelComponent
                        {reportFrom}
                        {reportTo}
                        bind:reportType
                        {reportSummary}
                        {reportRows}
                        {reportMeta}
                        {reportLoading}
                        {pools}
                        bind:reportPoolId
                        bind:reportRouteId
                        {routes}
                        bind:reportFromInput
                        bind:reportToInput
                        {formatCurrency}
                        {loadReport}
                        {jumpReportPage}
                    />
                {:else}
                    <div
                        class="rounded-2xl border border-border/70 bg-background/95 p-5 shadow-sm"
                    >
                        <div
                            class="rounded-2xl border border-dashed border-border/80 bg-muted/10 px-4 py-5 text-sm text-muted-foreground"
                        >
                            Memuat panel laporan operasional...
                        </div>
                    </div>
                {/if}
            {/if}

            {#if usesHybridSettings() && activeMode === 'data' && settingsMeta.total > 0}
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-border/70 pt-4"
                >
                    <p class="text-xs text-muted-foreground">
                        Total {settingsMeta.total} data
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            disabled={settingsMeta.page <= 1 || busy}
                            onclick={() =>
                                reloadSettingsWithInertia(
                                    settingsMeta.page - 1,
                                )}
                        >
                            Prev
                        </Button>
                        <span
                            class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs text-muted-foreground"
                        >
                            {settingsMeta.page} / {settingsMeta.last_page}
                        </span>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            disabled={settingsMeta.page >=
                                settingsMeta.last_page || busy}
                            onclick={() =>
                                reloadSettingsWithInertia(
                                    settingsMeta.page + 1,
                                )}
                        >
                            Next
                        </Button>
                    </div>
                </div>
            {/if}
        </CardContent>
    </Card>
</div>
