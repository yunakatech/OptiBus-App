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
    import { router } from '@inertiajs/svelte';
    import {
        Armchair,
        Eye,
        MoreHorizontal,
        Pencil,
        Trash2,
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
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import { confirmAndRun, runWithFeedback } from '@/lib/action-feedback';
    import { loadFlatpickr, type FlatpickrInstance } from '@/lib/flatpickr';

    type Stats = {
        routes: number;
        schedules: number;
        drivers: number;
        luggage_services: number;
        segments: number;
        customers: number;
        armadas: number;
        cancellations: number;
    };
    type RouteRow = {
        id: number;
        name: string;
        origin: string | null;
        destination: string | null;
        bop: number;
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
    type ScheduleDayGroup = { dow: number; rows: ScheduleRow[] };
    type ScheduleRouteGroup = {
        route: string;
        total: number;
        days: ScheduleDayGroup[];
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
        address: string | null;
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
    };
    type UserRow = {
        id: number;
        name: string;
        email: string;
        email_verified_at: string | null;
        created_at: string | null;
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
        | '4-0'
        | 'sleep'
        | 'empty';

    let {
        stats,
        initialTab = null,
        lockedMenuView: lockedFromServer = false,
        initialMode = null,
        initialRecordId = null,
    }: {
        stats: Stats;
        initialTab?: TabName | null;
        lockedMenuView?: boolean;
        initialMode?: string | null;
        initialRecordId?: number | null;
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
        'users',
        'cancellations',
        'reports',
    ] as const;
    type TabName = (typeof tabs)[number];
    type ViewMode = 'data' | 'form' | 'view' | 'layout';
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

        if (tab === 'users') {
            return 'Users';
        }

        if (tab === 'cancellations') {
            return 'Logs';
        }

        return 'Laporan';
    };

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
    let users = $state<UserRow[]>([]);
    let cancellations = $state<CancellationRow[]>([]);
    let units = $state<UnitRow[]>([]);

    let routeForm = $state({ id: 0, name: '', origin: '', destination: '' });
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
    let selectedSegmentRouteId = $state(0);
    let driverForm = $state({
        id: 0,
        nama: '',
        phone: '',
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
        address: '',
    });
    const unitCategoryOptions = ['Minibus', 'Mediumbus', 'Bigbus'] as const;
    type UnitCategory = (typeof unitCategoryOptions)[number];
    type UnitForm = {
        id: number;
        nama_model: string;
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
        category: defaultUnitCategory,
        kapasitas: 0,
        status: 'Aktif',
        layout: '',
    });
    let armadaForm = $state({
        id: 0,
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
    let userForm = $state({ id: 0, name: '', email: '', password: '' });

    let customerSearch = $state('');
    let driverUnitSearch = $state('');
    let armadaSearch = $state('');
    let armadaCategories = $state<string[]>([]);
    let armadaLayoutSearch = $state('');
    let armadaLayoutChoice = $state('');
    let armadaViewId = $state<number>(0);
    let layoutUnitId = $state<number>(0);
    let layoutTemplateSearch = $state('');
    let layoutTemplateChoice = $state('');
    let armadaDetail = $state<ArmadaRow | null>(null);
    let userSearch = $state('');
    const today = new Date().toISOString().slice(0, 10);
    let reportFrom = $state(today);
    let reportTo = $state(today);
    let reportType = $state<ReportKind>('booking');
    let scheduleTimeInput = $state<HTMLInputElement | null>(null);
    let scheduleTimePicker: FlatpickrInstance | null = null;
    let reportFromInput = $state<HTMLInputElement | null>(null);
    let reportToInput = $state<HTMLInputElement | null>(null);
    let reportFromPicker: FlatpickrInstance | null = null;
    let reportToPicker: FlatpickrInstance | null = null;
    let reportSummary = $state<ReportSummary | null>(null);
    let reportRows = $state<ReportRow[]>([]);
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

    const collectScheduleRouteNames = (
        routeRows: RouteRow[],
        scheduleRows: ScheduleRow[],
    ): string[] => {
        const names: string[] = [];
        routeRows.forEach((row) => {
            const name = String(row.name ?? '').trim();

            if (name !== '' && !names.includes(name)) {
                names.push(name);
            }
        });
        scheduleRows.forEach((row) => {
            const name = String(row.route_name ?? row.rute ?? '').trim();

            if (name !== '' && !names.includes(name)) {
                names.push(name);
            }
        });

        return names.sort((a, b) => a.localeCompare(b, 'id'));
    };

    const scheduleRouteOptions = $derived(
        collectScheduleRouteNames(routes, schedules),
    );
    const scheduleRouteGroups = $derived.by<ScheduleRouteGroup[]>(() => {
        const groups: Record<string, ScheduleRow[]> = {};

        for (const row of schedules) {
            const routeName = String(row.route_name ?? row.rute ?? '').trim();

            if (routeName === '') {
                continue;
            }

            const bucket = groups[routeName] ?? [];
            bucket.push(row);
            groups[routeName] = bucket;
        }

        return Object.entries(groups)
            .sort((a, b) => a[0].localeCompare(b[0], 'id'))
            .map(([route, rows]) => ({
                route,
                total: rows.length,
                days: Array.from({ length: 7 }, (_, dow) => ({
                    dow,
                    rows: rows
                        .filter((row) => row.dow === dow)
                        .sort((a, b) => a.jam.localeCompare(b.jam)),
                })),
            }));
    });
    const activeScheduleGroup = $derived(
        scheduleRouteGroups.find(
            (group) => group.route === selectedScheduleRoute,
        ) ?? null,
    );
    const selectedSegmentRoute = $derived(
        routes.find((route) => route.id === Number(selectedSegmentRouteId)) ??
            null,
    );
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
    const armadaLayoutOptions = $derived.by<UnitRow[]>(() => {
        const keyword = armadaLayoutSearch.trim().toLowerCase();
        const rows = units;

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
    const driverUnitOptions = $derived.by<ArmadaRow[]>(() => {
        const keyword = driverUnitSearch.trim().toLowerCase();
        const rows = armadas.filter(
            (armada) => String(armada.nopol ?? '').trim() !== '',
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
            (armada) => armada.id === Number(driverForm.armada_id || 0),
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
    const formatCurrency = (value: number) =>
        `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
    const parseRupiahInput = (value: string | number | null | undefined) => {
        if (typeof value === 'number') {
            return Math.max(0, value);
        }

        return Number(String(value ?? '').replace(/\D/g, '') || 0);
    };
    const formatRupiahInput = (value: string | number | null | undefined) => {
        const amount = parseRupiahInput(value);

        return amount > 0 ? `Rp ${amount.toLocaleString('id-ID')}` : '';
    };
    const armadaGrossMargin = (row: ArmadaRow) =>
        Number(row.revenue || 0) - Number(row.bop || 0);
    const armadaNetMargin = (row: ArmadaRow) =>
        armadaGrossMargin(row) - Number(row.fixed_cost || 0);
    const armadaAchievement = (row: ArmadaRow) => {
        const target = Number(row.target_bulanan || 0);

        if (target <= 0) {
            return 0;
        }

        return (Number(row.revenue || 0) / target) * 100;
    };
    const armadaStatus = (row: ArmadaRow) =>
        armadaAchievement(row) >= 100 ? 'Tercapai' : 'Kurang';
    const driverGrossMargin = (row: DriverRow) =>
        Number(row.revenue || 0) - Number(row.bop || 0);
    const driverNetMargin = (row: DriverRow) =>
        driverGrossMargin(row) - Number(row.fixed_cost || 0);
    const driverAchievement = (row: DriverRow) => {
        const target = Number(row.target_revenue_bulanan || 0);

        if (target <= 0) {
            return 0;
        }

        return (Number(row.revenue || 0) / target) * 100;
    };
    const driverStatus = (row: DriverRow) =>
        driverAchievement(row) >= 100 ? 'Tercapai' : 'Kurang';
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
        activeTab = tab;
        activeMode = 'data';
        syncTabQuery(tab);
        await loadActiveTab();
    };

    const hasFormTab = (tab: TabName) =>
        !['cancellations', 'reports'].includes(tab);
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

    const loadRoutes = async () => {
        const r = await api('GET', '/api/admin/routes');
        routes = r.routes ?? [];
    };

    const loadSchedules = async () => {
        const [s, u, r] = await Promise.all([
            api('GET', '/api/admin/schedules'),
            api('GET', '/api/admin/units'),
            api('GET', '/api/admin/routes'),
        ]);
        schedules = s.schedules ?? [];
        units = u.units ?? [];
        routes = r.routes ?? [];

        const routeNames = collectScheduleRouteNames(routes, schedules);
        const fallbackRoute = routeNames[0] ?? '';
        const selectedExists =
            selectedScheduleRoute !== '' &&
            routeNames.includes(selectedScheduleRoute);

        if (!selectedExists) {
            selectedScheduleRoute = fallbackRoute;
        }

        const formRouteExists =
            scheduleForm.rute !== '' && routeNames.includes(scheduleForm.rute);

        if (!formRouteExists) {
            scheduleForm.rute = selectedScheduleRoute || fallbackRoute;
        }
    };

    const loadDrivers = async () => {
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

    const loadCustomers = async () => {
        try {
            const query = customerSearch.trim();
            const url =
                query === ''
                    ? '/api/admin/customers'
                    : `/api/admin/customers?q=${encodeURIComponent(query)}`;
            const r = await api('GET', url);
            customers = r.customers ?? [];
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat customers.';
        }
    };

    const loadUsers = async () => {
        try {
            const query = userSearch.trim();
            const url =
                query === ''
                    ? '/api/admin/users'
                    : `/api/admin/users?q=${encodeURIComponent(query)}`;
            const r = await api('GET', url);
            users = r.users ?? [];
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat users.';
        }
    };

    const loadUnits = async () => {
        const r = await api('GET', '/api/admin/units');
        units = r.units ?? [];

        if (activeMode === 'layout' && layoutUnitId > 0) {
            syncLayoutUnitById(layoutUnitId);
        }
    };

    const loadArmadas = async () => {
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
                await loadCustomers();
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

            if (activeTab === 'users') {
                await loadUsers();
            }

            if (activeTab === 'cancellations') {
                await loadCancellations();
            }

            if (activeTab === 'reports') {
                await loadReport();
            }
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat data.';
        } finally {
            busy = false;
        }
    };

    const loadReport = async () => {
        reportLoading = true;

        try {
            const url = `/api/admin/reports/summary?from=${encodeURIComponent(reportFrom)}&to=${encodeURIComponent(reportTo)}&type=${encodeURIComponent(reportType)}`;
            const r = await api('GET', url);
            reportSummary = r.summary ?? null;
            reportRows = Array.isArray(r.rows) ? r.rows : [];
        } catch (e) {
            reportSummary = null;
            reportRows = [];
            error = e instanceof Error ? e.message : 'Gagal memuat report.';
        } finally {
            reportLoading = false;
        }
    };

    const resetRouteForm = () =>
        (routeForm = { id: 0, name: '', origin: '', destination: '' });
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
            rute: selectedScheduleRoute || scheduleRouteOptions[0] || '',
            dow: 1,
            jam: '08:00',
            units: 1,
            bop: '',
            unit_id: 0,
            unit_ids: [0],
            unit_labels: ['Unit 1'],
        });
    const resetDriverForm = () => (
        (driverUnitSearch = ''),
        (driverForm = {
            id: 0,
            nama: '',
            phone: '',
            armada_id: 0,
            target_revenue_bulanan: '',
            fixed_cost: '',
        })
    );
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
    const resetCustomerForm = () =>
        (customerForm = {
            id: 0,
            name: '',
            phone: '',
            pickup_point: '',
            address: '',
        });
    const resetUnitForm = () =>
        (unitForm = {
            id: 0,
            nama_model: '',
            category: defaultUnitCategory,
            kapasitas: 0,
            status: 'Aktif',
            layout: '',
        });
    const resetArmadaForm = () => {
        armadaForm = {
            id: 0,
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
        resetArmadaLayoutPicker();
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
            fixed_cost:
                Number(row.fixed_cost || 0) > 0 ? String(row.fixed_cost) : '',
            target_bulanan:
                Number(row.target_bulanan || 0) > 0
                    ? String(row.target_bulanan)
                    : '',
        };
        setFormMode('form');
    };
    const resetArmadaLayoutPicker = () => {
        armadaLayoutSearch = '';
        armadaLayoutChoice = '';
    };
    const applyArmadaLayoutChoice = (value: string) => {
        armadaLayoutChoice = value;
        const selectedId = Number(value || 0);
        const selectedUnit = units.find((unit) => unit.id === selectedId);

        if (!selectedUnit) {
            return;
        }

        const selectedCategory = normalizeUnitCategory(selectedUnit.category);

        armadaForm.kategori = selectedCategory;
    };
    const resetUserForm = () =>
        (userForm = { id: 0, name: '', email: '', password: '' });

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
                scheduleForm.rute ||
                selectedScheduleRoute ||
                ''
            ).trim();

            if (activeRoute === '') {
                throw new Error('Pilih rute terlebih dahulu.');
            }

            const selectedRouteId = Number(
                routes.find((row) => row.name === activeRoute)?.id ?? 0,
            );
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
            selectedScheduleRoute || scheduleRouteOptions[0] || '';
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
        selectedScheduleRoute = row.route_name ?? row.rute;
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
            rute: row.route_name ?? row.rute,
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
                        armada_id: Number(driverForm.armada_id) || undefined,
                        armada_nopol: driverUnitSearch.trim() || undefined,
                        target_revenue_bulanan: Number(
                            driverForm.target_revenue_bulanan || 0,
                        ),
                        fixed_cost: Number(driverForm.fixed_cost || 0),
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
                        harga: Number(segmentForm.harga),
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
                        address: customerForm.address,
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
            await loadCustomers();
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
                        merk: armadaForm.merk,
                        tahun: Number(armadaForm.tahun || 0),
                        warna: armadaForm.warna,
                        nopol: armadaForm.nopol,
                        nomor_rangka: armadaForm.nomor_rangka,
                        kategori: armadaForm.kategori,
                        ac_type: armadaForm.ac_type,
                        platform_gps: armadaForm.platform_gps,
                        api_gps: armadaForm.api_gps,
                        fixed_cost: Number(armadaForm.fixed_cost || 0),
                        target_revenue: Number(armadaForm.target_bulanan || 0),
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

        void loadActiveTab();
    });

    onDestroy(() => {
        destroyScheduleTimePicker();
        destroyReportPickers();
    });
</script>

<AppHead title={tabTitle(activeTab)} />

<div class="space-y-4 p-4">
    {#if !lockedMenuView}
        <div class="grid gap-4 md:grid-cols-4 xl:grid-cols-8">
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Routes <Badge>{stats.routes}</Badge></CardTitle
                    ></CardHeader
                ></Card
            >
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Schedules <Badge>{stats.schedules}</Badge></CardTitle
                    ></CardHeader
                ></Card
            >
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Drivers <Badge>{stats.drivers}</Badge></CardTitle
                    ></CardHeader
                ></Card
            >
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Luggage <Badge>{stats.luggage_services}</Badge
                        ></CardTitle
                    ></CardHeader
                ></Card
            >
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Segments <Badge>{stats.segments}</Badge></CardTitle
                    ></CardHeader
                ></Card
            >
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Customers <Badge>{stats.customers}</Badge></CardTitle
                    ></CardHeader
                ></Card
            >
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Armada <Badge>{stats.armadas}</Badge></CardTitle
                    ></CardHeader
                ></Card
            >
            <Card
                ><CardHeader
                    ><CardTitle class="text-sm font-medium"
                        >Cancels <Badge>{stats.cancellations}</Badge></CardTitle
                    ></CardHeader
                ></Card
            >
        </div>
    {/if}

    <Card>
        <CardHeader>
            <CardTitle>{tabTitle(activeTab)}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4 pt-6">
            {#if !lockedMenuView}
                <div class="overflow-x-auto pb-1">
                    <div
                        class="flex min-w-max gap-2 rounded-2xl border border-border/70 bg-[linear-gradient(135deg,rgba(15,23,42,0.035),rgba(148,163,184,0.05))] p-2 shadow-sm"
                    >
                        <Button
                            type="button"
                            variant={activeTab === 'routes'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'routes'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('routes')}
                            >Rute Induk</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'schedules'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'schedules'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('schedules')}
                            >Jadwal</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'drivers'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'drivers'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('drivers')}
                            >Driver</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'services'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'services'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('services')}
                            >Tarif Bagasi</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'segments'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'segments'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('segments')}
                            >Segment</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'customers'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'customers'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('customers')}
                            >Reguler</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'units'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'units'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('units')}
                            >Kategori Armada</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'armadas'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'armadas'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('armadas')}
                            >Armada</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'users'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'users'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('users')}>Users</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'cancellations'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'cancellations'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('cancellations')}
                            >Logs</Button
                        >
                        <Button
                            type="button"
                            variant={activeTab === 'reports'
                                ? 'default'
                                : 'ghost'}
                            class={activeTab === 'reports'
                                ? 'shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'}
                            onclick={() => void setTab('reports')}
                            >Laporan</Button
                        >
                    </div>
                </div>
            {/if}
            {#if busy}<p class="text-sm text-muted-foreground">
                    Memuat data...
                </p>{/if}
            {#if error}<p class="text-sm text-red-600">{error}</p>{/if}
            {#if message}<p class="text-sm text-emerald-600">{message}</p>{/if}
            {#if hasFormTab(activeTab)}
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
                        <div
                            class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(249,115,22,0.08),rgba(15,23,42,0.03))] px-5 py-4"
                        >
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
                        <div
                            class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(15,23,42,0.03),rgba(249,115,22,0.06))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between"
                        >
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
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-[820px] w-full border-separate border-spacing-0 text-sm"
                            >
                                <thead
                                    class="bg-muted/20 text-[11px] uppercase tracking-[0.24em] text-muted-foreground"
                                >
                                    <tr>
                                        <th
                                            class="w-[30%] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Rute Induk</th
                                        >
                                        <th
                                            class="w-[54%] border-b border-r border-border/70 px-4 py-3 text-left font-semibold"
                                            >Arah Perjalanan</th
                                        >
                                        <th
                                            class="w-[16%] border-b border-border/70 px-4 py-3 text-center font-semibold"
                                            >Aksi</th
                                        >
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each routes as row (row.id)}
                                        <tr
                                            class="group transition hover:bg-muted/15"
                                        >
                                            <td
                                                class="border-b border-r border-border/60 px-4 py-4 align-top"
                                            >
                                                <div
                                                    class="font-semibold text-foreground"
                                                >
                                                    {row.name}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Rute master untuk jadwal dan
                                                    segment
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
                                                <div
                                                    class="mt-2 text-[11px] text-muted-foreground"
                                                >
                                                    Jalur ini dipakai sebagai
                                                    acuan relasi jadwal
                                                    keberangkatan dan segment
                                                    harga.
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
                                                                >Aksi rute induk</span
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
                                                                routeForm = {
                                                                    id: row.id,
                                                                    name: row.name,
                                                                    origin:
                                                                        row.origin ??
                                                                        '',
                                                                    destination:
                                                                        row.destination ??
                                                                        '',
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
                                                                    `/api/admin/routes/${row.id}`,
                                                                    'Route deleted.',
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

            {#if activeTab === 'schedules'}
                <div class="space-y-4">
                    <div
                        class="grid gap-3 md:grid-cols-[minmax(240px,1fr)_auto]"
                    >
                        <select
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                            bind:value={selectedScheduleRoute}
                            onchange={() => {
                                resetScheduleForm();
                            }}
                        >
                            <option value="">Pilih rute</option>
                            {#each scheduleRouteOptions as routeName (routeName)}
                                <option value={routeName}>{routeName}</option>
                            {/each}
                        </select>
                        <Button
                            type="button"
                            variant="outline"
                            onclick={() => void setTab('routes')}
                            >Kelola rute</Button
                        >
                    </div>

                    {#if activeMode === 'form'}
                        <form onsubmit={saveSchedule}>
                            <AdminOpsSection
                                eyebrow="Form Jadwal"
                                title={scheduleForm.id
                                    ? 'Perbarui jadwal keberangkatan'
                                    : 'Tambah jadwal keberangkatan baru'}
                                description="Atur hari, jam, jumlah unit, dan BOP. Konfigurasi kategori armada per unit akan dipakai langsung oleh dropdown kursi di booking."
                                toneClass="bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(15,23,42,0.03))]"
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
                            class="rounded-2xl border border-dashed border-border/80 bg-muted/10 px-4 py-5 text-sm text-muted-foreground"
                        >
                            Pilih rute dulu untuk mengatur jadwal Senin sampai
                            Minggu.
                        </div>
                    {:else if activeMode === 'data'}
                        <div
                            class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(37,99,235,0.05),rgba(15,23,42,0.03))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground"
                                    >
                                        Jadwal Mingguan
                                    </p>
                                    <h3 class="mt-1 text-lg font-semibold">
                                        {selectedScheduleRoute}
                                    </h3>
                                    <p
                                        class="mt-1 max-w-3xl text-sm text-muted-foreground"
                                    >
                                        Tiap kartu hari diringkas untuk
                                        menampilkan jam, unit, dan BOP tanpa
                                        membuat layar laptop terasa penuh.
                                    </p>
                                </div>
                                <Badge
                                    variant="secondary"
                                    class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                                >
                                    {activeScheduleGroup?.total ?? 0} jadwal aktif
                                </Badge>
                            </div>
                            <div
                                class="grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                            >
                                {#each Array.from({ length: 7 }, (_, dow) => dow) as dow (dow)}
                                    {@const dayRows =
                                        activeScheduleGroup?.days.find(
                                            (day) => day.dow === dow,
                                        )?.rows ?? []}
                                    <div
                                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/90 shadow-sm"
                                    >
                                        <div
                                            class="flex items-center justify-between gap-3 border-b border-border/60 bg-muted/15 px-4 py-3"
                                        >
                                            <div>
                                                <p
                                                    class="text-sm font-semibold"
                                                >
                                                    {days[dow]}
                                                </p>
                                                <p
                                                    class="text-[11px] text-muted-foreground"
                                                >
                                                    {dayRows.length} jadwal
                                                </p>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onclick={() =>
                                                    openCreateSchedule(dow)}
                                                >Tambah</Button
                                            >
                                        </div>
                                        {#if dayRows.length === 0}
                                            <div
                                                class="px-4 py-5 text-xs text-muted-foreground"
                                            >
                                                Belum ada jadwal.
                                            </div>
                                        {:else}
                                            <div class="space-y-3 p-4">
                                                {#each dayRows as row (row.id)}
                                                    {@const rowOptions = (
                                                        row.unit_options ?? []
                                                    )
                                                        .slice()
                                                        .sort(
                                                            (a, b) =>
                                                                Number(
                                                                    a.unit_no,
                                                                ) -
                                                                Number(
                                                                    b.unit_no,
                                                                ),
                                                        )}
                                                    <div
                                                        class="rounded-2xl border border-border/70 bg-[linear-gradient(135deg,rgba(248,250,252,0.95),rgba(241,245,249,0.82))] p-3 text-xs shadow-sm"
                                                    >
                                                        <div
                                                            class="flex items-start justify-between gap-3"
                                                        >
                                                            <div>
                                                                <p
                                                                    class="text-base font-semibold text-foreground"
                                                                >
                                                                    {row.jam}
                                                                </p>
                                                                <div
                                                                    class="mt-2 flex flex-wrap gap-2"
                                                                >
                                                                    <span
                                                                        class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700"
                                                                    >
                                                                        {row.units}
                                                                        unit
                                                                    </span>
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
                                                                </div>
                                                            </div>
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
                                                        </div>
                                                        <div
                                                            class="mt-3 rounded-xl border border-border/60 bg-background/80 p-3"
                                                        >
                                                            <p
                                                                class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                                                            >
                                                                Konfigurasi Unit
                                                            </p>
                                                            {#if rowOptions.length > 0}
                                                                <div
                                                                    class="mt-2 space-y-2"
                                                                >
                                                                    {#each rowOptions as item (`schedule-unit-${row.id}-${item.unit_no}`)}
                                                                        <div
                                                                            class="flex items-start justify-between gap-3"
                                                                        >
                                                                            <div
                                                                                class="font-medium text-foreground"
                                                                            >
                                                                                {item.unit_no}.
                                                                                {item.label}
                                                                            </div>
                                                                            <div
                                                                                class="text-right text-[11px] text-muted-foreground"
                                                                            >
                                                                                {item.nopol ??
                                                                                    'Layout belum dipilih'}
                                                                            </div>
                                                                        </div>
                                                                    {/each}
                                                                </div>
                                                            {:else}
                                                                <p
                                                                    class="mt-2 text-[11px] text-muted-foreground"
                                                                >
                                                                    {row.unit_label ||
                                                                        row.nopol ||
                                                                        'Belum ada label unit.'}
                                                                </p>
                                                            {/if}
                                                        </div>
                                                    </div>
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
                                        type="number"
                                        min="0"
                                        step="1000"
                                        placeholder="Fixed Cost"
                                        bind:value={driverForm.fixed_cost}
                                    />
                                </label>
                                <label class="space-y-1.5">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >Target Revenue</span
                                    >
                                    <Input
                                        type="number"
                                        min="0"
                                        step="1000"
                                        placeholder="Target Revenue"
                                        bind:value={
                                            driverForm.target_revenue_bulanan
                                        }
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
                                    Identitas driver dibuat tetap terlihat saat
                                    scroll, sementara grup revenue, BOP, dan
                                    margin dipisah agar angka penting lebih
                                    cepat terbaca.
                                </p>
                            </div>
                            <Badge
                                variant="secondary"
                                class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                            >
                                {drivers.length} driver aktif
                            </Badge>
                        </div>

                        <div class="overflow-x-auto">
                            <table
                                class="min-w-[1900px] w-full border-separate border-spacing-0 text-sm"
                            >
                                <thead>
                                    <tr
                                        class="text-[10px] uppercase tracking-[0.24em] text-muted-foreground"
                                    >
                                        <th
                                            rowspan="2"
                                            class="sticky left-0 z-30 w-[220px] border-b border-r border-border/70 bg-background px-4 py-4 text-left font-semibold"
                                            >Nama Driver</th
                                        >
                                        <th
                                            rowspan="2"
                                            class="sticky left-[220px] z-30 w-[160px] border-b border-r border-border/70 bg-background px-4 py-4 text-left font-semibold"
                                            >Kontak</th
                                        >
                                        <th
                                            rowspan="2"
                                            class="sticky left-[380px] z-30 w-[170px] border-b border-r border-border/70 bg-background px-4 py-4 text-left font-semibold"
                                            >Nopol Unit</th
                                        >
                                        <th
                                            colspan="4"
                                            class="border-b border-r border-border/70 bg-emerald-50/70 px-3 py-3 text-center font-semibold text-emerald-800"
                                            >Revenue</th
                                        >
                                        <th
                                            colspan="3"
                                            class="border-b border-r border-border/70 bg-amber-50/80 px-3 py-3 text-center font-semibold text-amber-800"
                                            >BOP</th
                                        >
                                        <th
                                            colspan="3"
                                            class="border-b border-r border-border/70 bg-sky-50/80 px-3 py-3 text-center font-semibold text-sky-800"
                                            >Margin</th
                                        >
                                        <th
                                            colspan="2"
                                            class="border-b border-r border-border/70 bg-slate-100/90 px-3 py-3 text-center font-semibold text-slate-700"
                                            >Target</th
                                        >
                                        <th
                                            rowspan="2"
                                            class="border-b border-r border-border/70 bg-slate-100/90 px-3 py-4 text-center font-semibold text-slate-700"
                                            >Status</th
                                        >
                                        <th
                                            rowspan="2"
                                            class="sticky right-0 z-30 w-[92px] border-b border-l border-border/70 bg-background px-3 py-4 text-center font-semibold"
                                            >Aksi</th
                                        >
                                    </tr>
                                    <tr
                                        class="bg-muted/20 text-[11px] font-semibold text-foreground/80"
                                    >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Charter</th
                                        >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Keberangkatan</th
                                        >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Bagasi</th
                                        >
                                        <th
                                            class="border-b border-r border-border/70 px-3 py-3 text-right"
                                            >Total Revenue</th
                                        >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Charter</th
                                        >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Keberangkatan</th
                                        >
                                        <th
                                            class="border-b border-r border-border/70 px-3 py-3 text-right"
                                            >Total BOP</th
                                        >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Gross</th
                                        >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Fixed Cost</th
                                        >
                                        <th
                                            class="border-b border-r border-border/70 px-3 py-3 text-right"
                                            >Net Margin</th
                                        >
                                        <th
                                            class="border-b border-border/70 px-3 py-3 text-right"
                                            >Target Revenue</th
                                        >
                                        <th
                                            class="border-b border-r border-border/70 px-3 py-3 text-right"
                                            >Achievement</th
                                        >
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each drivers as row (row.id)}
                                        {@const gross = driverGrossMargin(row)}
                                        {@const net = driverNetMargin(row)}
                                        {@const achievement =
                                            driverAchievement(row)}
                                        {@const status = driverStatus(row)}
                                        <tr
                                            class="group transition hover:bg-muted/15"
                                        >
                                            <td
                                                class="sticky left-0 z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15"
                                            >
                                                <div
                                                    class="font-semibold text-foreground"
                                                >
                                                    {row.nama}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Kontributor driver bulan ini
                                                </div>
                                            </td>
                                            <td
                                                class="sticky left-[220px] z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15"
                                            >
                                                <div
                                                    class="font-medium text-foreground"
                                                >
                                                    {row.phone ?? '-'}
                                                </div>
                                                <div
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    Kontak operasional
                                                </div>
                                            </td>
                                            <td
                                                class="sticky left-[380px] z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15"
                                            >
                                                <div
                                                    class="inline-flex rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 text-xs font-semibold"
                                                >
                                                    {row.nopol ?? '-'}
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(
                                                    Number(
                                                        row.charter_revenue ||
                                                            0,
                                                    ),
                                                )}</td
                                            >
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(
                                                    Number(
                                                        row.departure_revenue ||
                                                            0,
                                                    ),
                                                )}</td
                                            >
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(
                                                    Number(
                                                        row.luggage_revenue ||
                                                            0,
                                                    ),
                                                )}</td
                                            >
                                            <td
                                                class="border-b border-r border-border/60 bg-emerald-50/45 px-3 py-4 text-right"
                                            >
                                                <div
                                                    class="text-sm font-semibold text-emerald-800 tabular-nums"
                                                >
                                                    {formatCurrency(
                                                        Number(
                                                            row.revenue || 0,
                                                        ),
                                                    )}
                                                </div>
                                                <div
                                                    class="mt-1 text-[10px] uppercase tracking-wide text-emerald-700/80"
                                                >
                                                    Total
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(
                                                    Number(
                                                        row.charter_bop || 0,
                                                    ),
                                                )}</td
                                            >
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(
                                                    Number(
                                                        row.departure_bop || 0,
                                                    ),
                                                )}</td
                                            >
                                            <td
                                                class="border-b border-r border-border/60 bg-amber-50/50 px-3 py-4 text-right"
                                            >
                                                <div
                                                    class="text-sm font-semibold text-amber-800 tabular-nums"
                                                >
                                                    {formatCurrency(
                                                        Number(row.bop || 0),
                                                    )}
                                                </div>
                                                <div
                                                    class="mt-1 text-[10px] uppercase tracking-wide text-amber-700/80"
                                                >
                                                    Total
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(gross)}</td
                                            >
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(
                                                    Number(row.fixed_cost || 0),
                                                )}</td
                                            >
                                            <td
                                                class="border-b border-r border-border/60 px-3 py-4 text-right"
                                            >
                                                <div
                                                    class={`text-sm font-semibold tabular-nums ${net >= 0 ? 'text-sky-800' : 'text-rose-700'}`}
                                                >
                                                    {formatCurrency(net)}
                                                </div>
                                                <div
                                                    class={`mt-1 text-[10px] uppercase tracking-wide ${net >= 0 ? 'text-sky-700/80' : 'text-rose-600/80'}`}
                                                >
                                                    {net >= 0
                                                        ? 'Positif'
                                                        : 'Minus'}
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums"
                                                >{formatCurrency(
                                                    Number(
                                                        row.target_revenue_bulanan ||
                                                            0,
                                                    ),
                                                )}</td
                                            >
                                            <td
                                                class="border-b border-r border-border/60 px-3 py-4 text-right"
                                            >
                                                <div
                                                    class="text-sm font-semibold tabular-nums"
                                                >
                                                    {achievement.toFixed(1)}%
                                                </div>
                                                <div
                                                    class="mt-1 text-[10px] uppercase tracking-wide text-muted-foreground"
                                                >
                                                    Pencapaian
                                                </div>
                                            </td>
                                            <td
                                                class="border-b border-r border-border/60 px-3 py-4 text-center"
                                            >
                                                <span
                                                    class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${
                                                        status === 'Tercapai'
                                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                            : 'border-amber-200 bg-amber-50 text-amber-700'
                                                    }`}
                                                >
                                                    {status}
                                                </span>
                                            </td>
                                            <td
                                                class="sticky right-0 z-20 border-b border-l border-border/60 bg-background px-3 py-4 text-center group-hover:bg-muted/15"
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
                                                                >Aksi driver</span
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
                                                                driverForm = {
                                                                    id: row.id,
                                                                    nama: row.nama,
                                                                    phone:
                                                                        row.phone ??
                                                                        '',
                                                                    armada_id:
                                                                        row.armada_id ??
                                                                        0,
                                                                    fixed_cost:
                                                                        Number(
                                                                            row.fixed_cost ||
                                                                                0,
                                                                        ) > 0
                                                                            ? String(
                                                                                  row.fixed_cost,
                                                                              )
                                                                            : '',
                                                                    target_revenue_bulanan:
                                                                        Number(
                                                                            row.target_revenue_bulanan ||
                                                                                0,
                                                                        ) > 0
                                                                            ? String(
                                                                                  row.target_revenue_bulanan,
                                                                              )
                                                                            : '',
                                                                };
                                                                driverUnitSearch =
                                                                    row.nopol ??
                                                                    '';
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
                                                                    `/api/admin/drivers/${row.id}`,
                                                                    'Driver deleted.',
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

            {#if activeTab === 'services'}
                {#if activeMode === 'form'}
                    <form
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={saveService}
                    >
                        <div
                            class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(16,185,129,0.08),rgba(15,23,42,0.03))] px-5 py-4"
                        >
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
                        <div
                            class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(16,185,129,0.06),rgba(15,23,42,0.03))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between"
                        >
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
                        <div class="overflow-x-auto">
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
                                toneClass="bg-[linear-gradient(135deg,rgba(251,191,36,0.08),rgba(15,23,42,0.03))]"
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
                                            type="number"
                                            min="0"
                                            step="1000"
                                            placeholder="Harga segment"
                                            bind:value={segmentForm.harga}
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
                            <div
                                class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(251,191,36,0.08),rgba(15,23,42,0.03))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between"
                            >
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
                            <div class="overflow-x-auto">
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
                                                                    onclick={() => {
                                                                        selectedSegmentRouteId =
                                                                            row.route_id;
                                                                        segmentForm =
                                                                            {
                                                                                id: row.id,
                                                                                route_id:
                                                                                    row.route_id,
                                                                                rute: row.rute,
                                                                                origin:
                                                                                    row.origin ??
                                                                                    '',
                                                                                destination:
                                                                                    row.destination ??
                                                                                    '',
                                                                                harga: Number(
                                                                                    row.harga,
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
                        <div
                            class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(251,191,36,0.08),rgba(15,23,42,0.03))] px-5 py-4"
                        >
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
                                    bind:value={customerForm.address}
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
                        <div
                            class="flex flex-col gap-4 border-b border-border/70 bg-[linear-gradient(135deg,rgba(251,191,36,0.06),rgba(15,23,42,0.03))] px-5 py-4"
                        >
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
                                <Badge
                                    variant="secondary"
                                    class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide"
                                >
                                    {customers.length} customer
                                </Badge>
                            </div>
                            <div class="flex flex-col gap-2 md:flex-row">
                                <Input
                                    placeholder="Cari nama, phone, atau pickup point"
                                    bind:value={customerSearch}
                                />
                                <Button
                                    type="button"
                                    class="md:min-w-[120px]"
                                    onclick={() => void loadCustomers()}
                                    >Search</Button
                                >
                            </div>
                        </div>
                        <div class="overflow-x-auto">
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
                                                {#if row.address}
                                                    <a
                                                        href={row.address}
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
                                                                    address:
                                                                        row.address ??
                                                                        '',
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
                                        <th class="w-[32%] px-4 py-3 text-left"
                                            >Nama Kategori Armada</th
                                        >
                                        <th class="w-[18%] px-4 py-3 text-left"
                                            >Kategori</th
                                        >
                                        <th class="w-[22%] px-4 py-3 text-left"
                                            >Kapasitas/Layout</th
                                        >
                                        <th class="w-[14%] px-4 py-3 text-left"
                                            >Status</th
                                        >
                                        <th class="w-[14%] px-4 py-3 text-left"
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
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Cari Template Kategori Armada</span
                                        >
                                        <Input
                                            placeholder="Search kategori armada (nama/model)"
                                            bind:value={armadaLayoutSearch}
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Data Dari Kategori Armada</span
                                        >
                                        <select
                                            class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                            bind:value={armadaLayoutChoice}
                                            onchange={(event) =>
                                                applyArmadaLayoutChoice(
                                                    (
                                                        event.currentTarget as HTMLSelectElement
                                                    ).value,
                                                )}
                                        >
                                            <option value=""
                                                >Pilih data dari Kategori Armada</option
                                            >
                                            {#each armadaLayoutOptions as unit (unit.id)}
                                                <option value={unit.id}>
                                                    {normalizeUnitCategory(
                                                        unit.category,
                                                    )} - {unit.nopol}
                                                </option>
                                            {/each}
                                        </select>
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
                                            type="number"
                                            min="0"
                                            step="1000"
                                            placeholder="Target Revenue (contoh: 60000000)"
                                            bind:value={
                                                armadaForm.target_bulanan
                                            }
                                        />
                                    </label>
                                    <label class="space-y-1.5">
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >Fixed Cost</span
                                        >
                                        <Input
                                            type="number"
                                            min="0"
                                            step="1000"
                                            placeholder="Fixed Cost"
                                            bind:value={armadaForm.fixed_cost}
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
                            <div class="flex flex-col gap-2 md:flex-row">
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
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-[980px] w-full border-separate border-spacing-0 text-sm"
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
                                                    User panel admin
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
                    <div class="overflow-x-auto">
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
                        {reportLoading}
                        bind:reportFromInput
                        bind:reportToInput
                        {formatCurrency}
                        {loadReport}
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
        </CardContent>
    </Card>
</div>
