<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Data Keberangkatan',
                href: '/bookings',
            },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import {
        Armchair,
        BusFront,
        CalendarDays,
        CarFront,
        Clock3,
        Copy,
        CheckCircle2,
        LayoutGrid,
        ListFilter,
        MoreHorizontal,
        MoreVertical,
        Pencil,
        Phone,
        Plus,
        Printer,
        RefreshCw,
        Route,
        Rows3,
        Save,
        Search,
        UserRound,
        WalletCards,
        X,
    } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
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
    import { Skeleton } from '@/components/ui/skeleton';
    import { runWithFeedback } from '@/lib/action-feedback';
    import {
        formatCurrencyDisplay,
        formatCurrencyInput,
        parseCurrencyInput,
    } from '@/lib/currency';
    import { consumeDataStale, markDataStale } from '@/lib/data-invalidation';
    import { loadFlatpickr, type FlatpickrInstance } from '@/lib/flatpickr';
    import { readUiPreferences, saveUiPreferences } from '@/lib/ui-preferences';

    type Totals = {
        bookings: number;
        customers: number;
        routes: number;
        schedules: number;
    };

    type BookingRow = {
        id: number;
        group_key?: string;
        name: string;
        phone: string;
        rute: string;
        tanggal: string;
        jam: string;
        unit: number;
        seat: string;
        status: string;
        pembayaran: string;
        departure_code?: string;
        ticket_code?: string;
    };

    type BookingGroup = {
        key: string;
        rute: string;
        tanggal: string;
        jam: string;
        unit: number;
        assignment_id?: number | null;
        departure_code: string;
        driver_name: string;
        armada_nopol: string;
        total: number;
        active: number;
        canceled: number;
        lunas: number;
        refund: number;
        belum_lunas: number;
        bop: number;
        departure_status?: string;
        departure_can_arrive?: boolean;
        bookings: Array<{
            id: number;
            ticket_code: string;
            name: string;
            phone: string;
            seat: string;
            status: string;
            pembayaran: string;
            pickup_point: string;
            segment_name: string;
            gmaps: string;
            price: number;
            discount: number;
        }>;
    };

    type BookingDateSection = {
        key: string;
        tanggal: string;
        label: string;
        totalSchedules: number;
        totalPassengers: number;
        totalUnpaid: number;
        unpaidAmount: number;
        groups: BookingGroup[];
    };

    type BookingListSummary = {
        schedules: number;
        activePassengers: number;
        unpaidSchedules: number;
        unpaidPassengers: number;
    };

    type GroupRiturRow = {
        id: number;
        kode_resi: string;
        sender_name: string;
        receiver_name: string;
        rute?: string;
        tanggal?: string;
        quantity: number;
        price: number;
        status: string;
        payment_status: string;
        notes?: string;
        trip_assignment_id?: number | null;
    };

    type ScheduleItem = {
        jam: string;
        units: number;
        seats: number;
        layout: unknown[];
        unit_id: number;
        nopol: string;
        unit_label: string;
        bop: number;
        segment_matches?: SegmentItem[];
        unit_options?: Array<{
            unit_no: number;
            label: string;
            unit_id: number | null;
            layout?: unknown[];
            seats?: number;
            nopol?: string;
        }>;
    };

    type SeatDetail = {
        id: number;
        name: string;
        phone: string;
        pembayaran: string;
        pickup_point: string;
        segment_id: number;
        segment_name: string;
        segment_jam: string;
        segment_jam_pickups: string[];
        price: number;
        discount: number;
    };

    type SegmentItem = {
        id: number;
        rute: string;
        jam: string | null;
        jam_pickups: string[] | null;
        harga: number;
    };

    type LayoutCell = {
        kind: 'seat' | 'driver' | 'empty';
        seat?: string;
    };

    type CustomerLookupItem = {
        name: string;
        phone: string;
        pickup_point: string;
        address: string;
    };

    type BookedSeatRow = SeatDetail & {
        seat: string;
    };

    type RekapItem = {
        booking_id: number;
        seat: string;
        name: string;
        phone: string;
        pickup_point: string;
        segment_name: string;
        segment_jam?: string;
        segment_jam_pickups?: string[];
        pembayaran: string;
        final_price: number;
    };

    type CreatedBookingRecord = {
        seat: string;
        departure_code?: string;
        ticket_code?: string;
    };

    type BookingSuccessItem = {
        seat: string;
        name: string;
        phone: string;
        pickup_point: string;
        segment_name: string;
        segment_jam: string;
        segment_jam_pickups: string[];
        pembayaran: string;
        final_price: number;
    };

    type BookingSuccessSnapshot = {
        tanggal: string;
        jam: string;
        segment_jam: string;
        segment_jam_pickups: string[];
        rute: string;
        unit: number;
        total: number;
        items: BookingSuccessItem[];
    };

    type DriverItem = {
        id: number;
        nama: string;
        phone?: string;
    };

    type ArmadaItem = {
        id: number;
        nopol: string;
        merk?: string;
        kategori?: string;
        warna?: string;
        tahun?: number;
        nomor_rangka?: string;
    };

    let {
        totals,
        latestBookings = [],
        bookingGroups = [],
        bookingRouteOptions = [],
        bookingListReady = false,
        migrationChecklist,
        consoleOnly = false,
        listOnly = false,
        groupDetailPage = false,
        groupDetailKey = '',
        serverNow = '',
    }: {
        totals: Totals;
        latestBookings: BookingRow[];
        bookingGroups?: BookingGroup[];
        bookingRouteOptions?: string[];
        bookingListReady?: boolean;
        migrationChecklist: string[];
        consoleOnly?: boolean;
        listOnly?: boolean;
        groupDetailPage?: boolean;
        groupDetailKey?: string;
        serverNow?: string;
    } = $props();

    const toDateKey = (date: Date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };
    const initialClientClockMs = Date.now();
    let clientClockTickMs = $state(initialClientClockMs);

    $effect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const timer = window.setInterval(() => {
            clientClockTickMs = Date.now();
        }, 30000);

        return () => window.clearInterval(timer);
    });

    const addDays = (date: Date, days: number) => {
        return new Date(date.getTime() + days * 24 * 60 * 60 * 1000);
    };
    const formatDateHuman = (value: string) => {
        if (!value) {
            return '-';
        }

        const date = new Date(`${value}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }).format(date);
    };

    const today = (() => {
        const serverToday = String(
            page.props.server_today ?? serverNow ?? '',
        ).slice(0, 10);

        if (/^\d{4}-\d{2}-\d{2}$/.test(serverToday)) {
            return serverToday;
        }

        return toDateKey(new Date());
    })();
    const initialUiPreferences = readUiPreferences();
    const isIsoDateKey = (value: string) => /^\d{4}-\d{2}-\d{2}$/.test(value);
    const initialBookingListDate = isIsoDateKey(
        String(initialUiPreferences.defaultDateRange ?? ''),
    )
        ? String(initialUiPreferences.defaultDateRange)
        : today;
    const initialBookingListDesktopView =
        initialUiPreferences.defaultViewMode === 'cards' ? 'cards' : 'sheet';
    const paymentOptions = [
        'Belum Lunas',
        'Lunas',
        'QRIS',
        'Transfer',
        'Transfer BJU',
        'Tunai',
    ];
    const initialTotals = () => totals;
    const initialLatestBookings = () => latestBookings;
    const initialBookingGroups = () => bookingGroups;
    const initialBookingRouteOptions = () => bookingRouteOptions;

    let localTotals = $state<Totals>($state.snapshot(initialTotals()));
    let localLatestBookings = $state<BookingRow[]>(
        $state.snapshot(initialLatestBookings()),
    );
    let localBookingGroups = $state<BookingGroup[]>(
        $state.snapshot(initialBookingGroups()),
    );
    let localBookingRouteOptions = $state<string[]>(
        $state.snapshot(initialBookingRouteOptions()),
    );
    let bookingListHydrated = $state(false);

    $effect(() => {
        localTotals = $state.snapshot(totals);
        localLatestBookings = $state.snapshot(latestBookings);
        localBookingGroups = $state.snapshot(bookingGroups);
        localBookingRouteOptions = $state.snapshot(bookingRouteOptions);

        if (!listOnly) {
            bookingListHydrated = true;
        } else if (bookingListReady) {
            bookingListHydrated = true;
        } else {
            bookingListHydrated = false;
        }
    });

    let bookingListRoute = $state('all');
    let bookingListDateFrom = $state(initialBookingListDate);
    let bookingListDateTo = $state(initialBookingListDate);
    let bookingListPayment = $state<'all' | 'lunas' | 'belum_lunas'>('all');
    let bookingListDesktopView = $state<'sheet' | 'cards'>(
        initialBookingListDesktopView,
    );
    let bookingListVisibleCount = $state(24);
    let lastBookingListFilterSignature = $state('');
    let bookingListFiltersExpanded = $state(false);
    let bookingListReloadTimer: ReturnType<typeof setTimeout> | null = null;
    let emptyDepartureOpen = $state(false);
    let emptyDepartureDate = $state(today);
    let emptyDepartureRoute = $state('');
    let emptyDepartureJam = $state('');
    let emptyDepartureUnit = $state(1);
    let emptyDepartureRoutes = $state<string[]>([]);
    let emptyDepartureSchedules = $state<ScheduleItem[]>([]);
    let openGroupDetail = $state<BookingGroup | null>(null);

    $effect(() => {
        if (!openGroupDetail) {
            return;
        }

        const refreshedGroup = localBookingGroups.find(
            (group) => group.key === openGroupDetail!.key,
        );

        if (refreshedGroup && refreshedGroup !== openGroupDetail) {
            openGroupDetail = refreshedGroup;
        }
    });

    let bookingDate = $state(today);
    let selectedRoute = $state('');
    let selectedJam = $state('');
    let selectedUnit = $state(1);
    let mobileBookingStep = $state<1 | 2 | 3>(1);

    let availableRoutes = $state<string[]>([]);
    let schedules = $state<ScheduleItem[]>([]);
    let seatDetailsMap = $state<Record<string, SeatDetail>>({});
    let segments = $state<SegmentItem[]>([]);

    let loadingRoutes = $state(false);
    let loadingSchedules = $state(false);
    let loadingSeatDetails = $state(false);
    let loadingSegments = $state(false);
    let submittingBooking = $state(false);
    let cancelingSeatId = $state<number | null>(null);
    let markingPaidSeatId = $state<number | null>(null);
    let loadingCustomerLookup = $state(false);
    let loadingEmptyDepartureRoutes = $state(false);
    let loadingEmptyDepartureSchedules = $state(false);
    let savingEmptyDeparture = $state(false);
    let cancelingDepartureKey = $state('');

    let routeError = $state('');
    let scheduleError = $state('');
    let detailError = $state('');
    let formError = $state('');
    let formSuccess = $state('');
    let bookingSuccessModalOpen = $state(false);
    let bookingSuccessFeedback = $state('');
    let bookingSuccessSnapshot = $state<BookingSuccessSnapshot | null>(null);

    let formName = $state('');
    let formPhone = $state('');
    let formPickupPoint = $state('');
    let formAddress = $state('');
    let formSeat = $state('');
    let selectedSeats = $state<string[]>([]);
    let lastTappedSeat = $state('');
    let lastSelectedPulseSeat = $state('');
    let formSegmentId = $state(0);
    let formDiscount = $state<string | number>('');
    let formPayment = $state('Belum Lunas');
    let detailModalOpen = $state(false);
    let detailSeat = $state<BookedSeatRow | null>(null);
    let detailEditMode = $state(false);
    let savingDetailEdit = $state(false);
    let detailEditSeat = $state('');
    let detailEditName = $state('');
    let detailEditPhone = $state('');
    let detailEditPickupPoint = $state('');
    let detailEditPayment = $state('Belum Lunas');
    let detailEditSegmentId = $state(0);
    let detailEditDiscount = $state<string | number>('');
    let rekapModalOpen = $state(false);
    let rekapItems = $state<RekapItem[]>([]);
    let groupDrivers = $state<DriverItem[]>([]);
    let groupDriverSelectedId = $state('0');
    let groupDriverName = $state('-');
    let groupDriverSearch = $state('');
    let groupDriverAssignmentId = $state<number | null>(null);
    let loadingGroupDriver = $state(false);
    let savingGroupDriver = $state(false);
    let groupDriverLookupOpen = $state(false);
    let groupArmadas = $state<ArmadaItem[]>([]);
    let groupArmadaSelectedId = $state('0');
    let groupArmadaNopol = $state('-');
    let groupArmadaSearch = $state('');
    let loadingGroupArmada = $state(false);
    let groupArmadaLookupOpen = $state(false);
    let groupPassengerTab = $state<'active' | 'ritur'>('active');
    let loadingGroupRiturs = $state(false);
    let savingGroupRiturId = $state<number | null>(null);
    let groupRiturSearch = $state('');
    let groupRiturFilter = $state<
        'all' | 'same_route' | 'same_date' | 'same_route_date'
    >('all');
    let groupRiturFiltersExpanded = $state(false);
    let groupMappedRiturs = $state<GroupRiturRow[]>([]);
    let groupAvailableRiturs = $state<GroupRiturRow[]>([]);
    let groupEditModalOpen = $state(false);
    let GroupEditModalComponent = $state<any>(null);
    let savingGroupRowEdit = $state(false);
    let groupEditBookingId = $state<number | null>(null);
    const luggageReceivedStatus = 'Diterima';
    const luggagePickedUpStatus = 'Dalam Perjalanan';
    const luggageArrivedStatus = 'Tiba di Tujuan';
    let groupEditSeat = $state('');
    let groupEditCurrentSeat = $state('');
    let groupEditName = $state('');
    let groupEditPhone = $state('');
    let groupEditPickupPoint = $state('');
    let groupEditPayment = $state('Belum Lunas');
    let groupEditDiscount = $state<string | number>('');

    let lastBookingListPageMode: boolean | null = null;
    let lastBookingListDetailMode: boolean | null = null;

    $effect(() => {
        if (
            lastBookingListPageMode === null &&
            lastBookingListDetailMode === null
        ) {
            lastBookingListPageMode = listOnly;
            lastBookingListDetailMode = groupDetailPage;
            return;
        }

        if (
            lastBookingListPageMode !== listOnly ||
            lastBookingListDetailMode !== groupDetailPage
        ) {
            bookingListFiltersExpanded = false;
            lastBookingListPageMode = listOnly;
            lastBookingListDetailMode = groupDetailPage;
        }
    });
    let groupEditRoute = $state('');
    let groupEditDate = $state('');
    let groupEditJam = $state('');
    let groupEditUnit = $state(1);
    let groupEditSeatOptions = $state<string[]>([]);
    let groupEditBookedSeatTokens = $state<string[]>([]);
    let groupEditLayoutSeatTokens = $state<string[]>([]);
    let groupEditLayoutRows = $state<LayoutCell[][]>([]);
    let groupEditSeatWarning = $state('');
    let loadingGroupEditSeats = $state(false);
    let groupRescheduleModalOpen = $state(false);
    let GroupRescheduleModalComponent = $state<any>(null);
    let savingGroupReschedule = $state(false);
    let loadingGroupRescheduleSchedules = $state(false);
    let loadingGroupRescheduleSeats = $state(false);
    let groupRescheduleBookingId = $state<number | null>(null);
    let groupRescheduleBookingName = $state('');
    let groupRescheduleRoute = $state('');
    let groupRescheduleCurrentDate = $state('');
    let groupRescheduleCurrentJam = $state('');
    let groupRescheduleCurrentUnit = $state(1);
    let groupRescheduleCurrentSeat = $state('');
    let groupRescheduleDate = $state('');
    let groupRescheduleJam = $state('');
    let groupRescheduleUnit = $state(1);
    let groupRescheduleSeat = $state('');
    let groupRescheduleSchedules = $state<ScheduleItem[]>([]);
    let groupRescheduleSeatOptions = $state<string[]>([]);
    let groupRescheduleBookedSeatTokens = $state<string[]>([]);
    let groupRescheduleLayoutSeatTokens = $state<string[]>([]);
    let groupRescheduleLayoutRows = $state<LayoutCell[][]>([]);
    let groupRescheduleSeatWarning = $state('');
    let customerLookupQuery = $state('');
    let customerSuggestions = $state<CustomerLookupItem[]>([]);
    let customerSuggestOpen = $state(false);
    let customerLookupMessage = $state('');
    let customerSearchRequestId = 0;
    let customerSearchTimer: ReturnType<typeof setTimeout> | null = null;
    let groupDriverLookupTimer: ReturnType<typeof setTimeout> | null = null;
    let groupArmadaLookupTimer: ReturnType<typeof setTimeout> | null = null;
    let groupRiturSearchTimer: ReturnType<typeof setTimeout> | null = null;
    const ensureGroupEditModalLoaded = async () => {
        if (!GroupEditModalComponent) {
            GroupEditModalComponent = (
                await import('@/components/bookings/BookingGroupEditModal.svelte')
            ).default;
        }
    };
    const ensureGroupRescheduleModalLoaded = async () => {
        if (!GroupRescheduleModalComponent) {
            GroupRescheduleModalComponent = (
                await import('@/components/bookings/BookingGroupRescheduleModal.svelte')
            ).default;
        }
    };
    let seatTapTimer: ReturnType<typeof setTimeout> | null = null;
    let seatPulseTimer: ReturnType<typeof setTimeout> | null = null;
    let bookingDateInput = $state<HTMLInputElement | null>(null);
    let bookingListDateInput = $state<HTMLInputElement | null>(null);
    let bookingDatePicker: FlatpickrInstance | null = null;
    let bookingListDatePicker: FlatpickrInstance | null = null;
    const API_TIMEOUT_MS = 15000;
    const BOOKING_LIST_PAGE_SIZE = 24;
    const BOOKING_LIST_DATA_PROPS = [
        'bookingGroups',
        'bookingRouteOptions',
        'bookingListReady',
    ];
    const BOOKING_LIST_STATE_PROPS = [
        'listOnly',
        'groupDetailPage',
        'groupDetailKey',
    ];
    const bookingListSkeletonRows = Array.from({ length: 6 });
    const bookingListSkeletonStats = Array.from({ length: 6 });

    const bookingDateLabel = $derived(formatDateHuman(bookingDate));
    const bookingListLoading = $derived(listOnly && !bookingListHydrated);
    const quickDatePresets = $derived.by(() => {
        const base = new Date();
        const presets = [
            { label: 'Hari Ini', offset: 0 },
            { label: 'Besok', offset: 1 },
            { label: 'Lusa', offset: 2 },
        ];

        return presets.map((item) => {
            const dateValue = toDateKey(addDays(base, item.offset));
            const pretty = new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
            }).format(new Date(`${dateValue}T00:00:00`));

            return {
                label: item.label,
                value: dateValue,
                pretty,
            };
        });
    });

    const isCanceledBooking = (status: string | null | undefined) =>
        String(status || '')
            .trim()
            .toLowerCase() === 'canceled';
    const statusVariant = (status: string) =>
        isCanceledBooking(status) ? 'secondary' : 'default';
    const paymentVariant = (pembayaran: string) => {
        const normalized = String(pembayaran || '')
            .trim()
            .toLowerCase();

        if (normalized === 'lunas') {
            return 'default';
        }

        if (normalized === 'refund') {
            return 'outline';
        }

        return 'secondary';
    };
    const isLunasPayment = (pembayaran: string) =>
        String(pembayaran || '')
            .trim()
            .toLowerCase() === 'lunas';
    const isRefundPayment = (pembayaran: string) =>
        String(pembayaran || '')
            .trim()
            .toLowerCase() === 'refund';
    const isBelumLunasPayment = (pembayaran: string) =>
        String(pembayaran || '')
            .trim()
            .toLowerCase() === 'belum lunas';
    const isHiddenCanceledUnpaidBooking = (
        row: BookingGroup['bookings'][number],
    ) => isCanceledBooking(row.status) && isBelumLunasPayment(row.pembayaran);
    const visibleGroupBookingRows = (rows: BookingGroup['bookings']) =>
        rows.filter((row) => !isHiddenCanceledUnpaidBooking(row));
    const isSettledPayment = (pembayaran: string) =>
        isLunasPayment(pembayaran) || isRefundPayment(pembayaran);
    const canRefundCanceledBooking = (row: BookingGroup['bookings'][number]) =>
        isCanceledBooking(row.status) &&
        !isRefundPayment(row.pembayaran) &&
        !isBelumLunasPayment(row.pembayaran);
    const payableBookingRows = (group: BookingGroup) =>
        visibleGroupBookingRows(group.bookings).filter(
            (row) => !isSettledPayment(row.pembayaran),
        );

    const bookedSeats = () =>
        Object.entries(seatDetailsMap)
            .map(([seat, detail]) => ({ seat, ...detail }))
            .sort((a, b) => {
                const aNum = Number(a.seat);
                const bNum = Number(b.seat);

                if (Number.isFinite(aNum) && Number.isFinite(bNum)) {
                    return aNum - bNum;
                }

                return a.seat.localeCompare(b.seat);
            });

    const activeSchedule = () =>
        schedules.find((item) => item.jam === selectedJam) ?? null;
    const scheduleSegmentCount = (schedule: ScheduleItem | null | undefined) =>
        Array.isArray(schedule?.segment_matches)
            ? schedule.segment_matches.length
            : 0;
    const scheduleSegmentHint = (schedule: ScheduleItem | null | undefined) => {
        const count = scheduleSegmentCount(schedule);

        if (count > 0) {
            return `${count} segment terhubung`;
        }

        return 'Belum ada segment terhubung untuk jam ini';
    };
    const scheduleSegmentOptions = () => {
        return activeSchedule()?.segment_matches ?? [];
    };
    const syncSegmentSelectionToSchedule = () => {
        const options = scheduleSegmentOptions();

        if (options.length === 0) {
            formSegmentId = 0;

            return;
        }

        if (!options.some((item) => item.id === Number(formSegmentId))) {
            formSegmentId = options[0].id;
        }
    };
    const emptyDepartureSchedule = () =>
        emptyDepartureSchedules.find(
            (item) => item.jam === emptyDepartureJam,
        ) ?? null;
    const emptyDepartureUnitOptions = () => {
        const schedule = emptyDepartureSchedule();
        const options = Array.isArray(schedule?.unit_options)
            ? schedule.unit_options
            : [];

        if (options.length > 0) {
            return options.map((option, idx) => {
                const value = Math.max(1, Number(option.unit_no || idx + 1));
                const label = String(option.label ?? '').trim();
                const nopol = String(option.nopol ?? '').trim();
                const suffix = [label, nopol]
                    .filter((item) => item !== '')
                    .join(' • ');

                return {
                    value,
                    label:
                        suffix !== ''
                            ? `Unit ${value} - ${suffix}`
                            : `Unit ${value}`,
                };
            });
        }

        const units = Math.max(1, Number(schedule?.units ?? 1));

        return Array.from({ length: units }, (_, index) => ({
            value: index + 1,
            label: `Unit ${index + 1}`,
        }));
    };
    const activeScheduleUnitOption = () => {
        const schedule = activeSchedule();
        const options = Array.isArray(schedule?.unit_options)
            ? schedule.unit_options
            : [];

        return (
            options.find(
                (option) =>
                    Number(option.unit_no || 0) === Number(selectedUnit || 1),
            ) ?? null
        );
    };
    const groupRescheduleActiveSchedule = () =>
        groupRescheduleSchedules.find(
            (item) => item.jam === groupRescheduleJam,
        ) ?? null;
    const groupRescheduleUnitOptions = () => {
        const schedule = groupRescheduleActiveSchedule();
        const options = Array.isArray(schedule?.unit_options)
            ? schedule.unit_options
            : [];

        if (options.length > 0) {
            return options.map((option, idx) => {
                const value = Math.max(1, Number(option.unit_no || idx + 1));
                const label = String(option.label ?? '').trim();
                const nopol = String(option.nopol ?? '').trim();
                const suffix = [label, nopol]
                    .filter((item) => item !== '')
                    .join(' • ');

                return {
                    value,
                    label:
                        suffix !== ''
                            ? `Unit ${value} - ${suffix}`
                            : `Unit ${value}`,
                };
            });
        }

        const units = Math.max(1, Number(schedule?.units ?? 1));

        return Array.from({ length: units }, (_, index) => ({
            value: index + 1,
            label: `Unit ${index + 1}`,
        }));
    };
    const hasSelectedTrip = () => Boolean(selectedRoute && selectedJam);
    const defaultPreviewSeats = 11;
    const selectedCount = () => selectedSeats.length;
    const totalSeats = () =>
        Number(
            activeScheduleUnitOption()?.seats ?? activeSchedule()?.seats ?? 0,
        );
    const bookedCount = () => bookedSeats().length;
    const activeSegment = () => {
        const options = scheduleSegmentOptions();
        const found = options.find((item) => item.id === Number(formSegmentId));
        return (
            found ??
            segments.find((item) => item.id === Number(formSegmentId)) ??
            null
        );
    };
    const selectedTotal = () => {
        const price = Number(activeSegment()?.harga ?? 0);
        const discount = parseCurrencyInput(formDiscount);

        return Math.max(price * selectedCount() - discount, 0);
    };
    const rekapTotal = () =>
        rekapItems.reduce((sum, item) => sum + item.final_price, 0);
    const grandTotal = () => rekapTotal() + selectedTotal();
    const grandCount = () => rekapItems.length + selectedCount();
    const unitOptions = () => {
        const schedule = activeSchedule();
        const options = Array.isArray(schedule?.unit_options)
            ? schedule.unit_options
            : [];

        if (options.length > 0) {
            return options.map((option, idx) => {
                const value = Math.max(1, Number(option.unit_no || idx + 1));
                const label = String(option.label ?? '').trim();

                return {
                    value,
                    label:
                        label !== ''
                            ? `Unit ${value} - ${label}`
                            : `Unit ${value}`,
                };
            });
        }

        const units = Math.max(1, Number(schedule?.units ?? 1));
        const label = String(schedule?.unit_label ?? '').trim();

        return Array.from({ length: units }, (_, idx) => {
            const value = idx + 1;

            return {
                value,
                label:
                    label !== '' ? `Unit ${value} - ${label}` : `Unit ${value}`,
            };
        });
    };
    const normalizeSeatToken = (value: string | number) =>
        String(value).trim().toUpperCase();
    const normalizePhoneToken = (value: string) =>
        (value || '').replace(/\D+/g, '');
    const normalizePhoneForBooking = (value: string) => {
        let digits = normalizePhoneToken(value);

        if (digits.startsWith('62')) {
            digits = `0${digits.slice(2)}`;
        } else if (digits.startsWith('8')) {
            digits = `0${digits}`;
        }

        if (digits.length > 13) {
            digits = digits.slice(0, 13);
        }

        return digits;
    };
    const normalizeNameForBooking = (value: string) =>
        String(value || '').toUpperCase();

    const bookedSeatSet = () =>
        new Set(
            Object.keys(seatDetailsMap).map((seat) => normalizeSeatToken(seat)),
        );
    const selectedSeatSet = () =>
        new Set(
            selectedSeats
                .map((seat) => normalizeSeatToken(seat))
                .filter((seat) => seat !== ''),
        );

    const parseSeatInput = (value: string) =>
        Array.from(
            new Set(
                value
                    .split(/[\s,;|]+/)
                    .map((item) => normalizeSeatToken(item))
                    .filter((item) => item !== ''),
            ),
        );

    const mobileStepItems = [
        { step: 1, label: 'Jadwal' },
        { step: 2, label: 'Kursi' },
        { step: 3, label: 'Data' },
    ] as const;
    const mobileTripSummary = () =>
        [
            bookingDate || '-',
            selectedRoute || 'Rute belum dipilih',
            normalizeJamToken(selectedJam) || 'Jam belum dipilih',
            `Unit ${Number(selectedUnit || 1) || 1}`,
        ].join(' • ');
    const mobileScheduleReady = () =>
        Boolean(bookingDate && selectedRoute && selectedJam && selectedUnit);
    const goMobileScheduleNext = () => {
        formError = '';

        if (!mobileScheduleReady()) {
            formError = 'Lengkapi tanggal, rute, jam, dan unit dulu.';

            return;
        }

        mobileBookingStep = 2;
    };
    const goMobileSeatNext = () => {
        formError = '';

        if (!mobileScheduleReady()) {
            formError = 'Lengkapi jadwal terlebih dahulu.';
            mobileBookingStep = 1;

            return;
        }

        const requestedSeats =
            selectedSeats.length > 0 ? selectedSeats : parseSeatInput(formSeat);

        if (requestedSeats.length === 0) {
            formError = 'Pilih minimal 1 kursi kosong sebelum isi data.';

            return;
        }

        mobileBookingStep = 3;
    };

    const syncFormSeatFromSelected = () => {
        formSeat = selectedSeats.join(', ');
    };

    const toLayoutCell = (value: unknown): LayoutCell => {
        if (value === null || value === undefined || value === false) {
            return { kind: 'empty' };
        }

        if (typeof value === 'string' || typeof value === 'number') {
            const token = normalizeSeatToken(value);

            if (
                token === '' ||
                token === '-' ||
                token === '_' ||
                token === 'AISLE'
            ) {
                return { kind: 'empty' };
            }

            if (token === 'DRIVER') {
                return { kind: 'driver' };
            }

            if (token === 'BAGASI') {
                return { kind: 'empty' };
            }

            return { kind: 'seat', seat: token };
        }

        if (typeof value === 'object') {
            const row = value as Record<string, unknown>;
            const typeToken = String(row.type ?? '')
                .trim()
                .toLowerCase();
            const isHidden = Boolean(row.hidden);

            if (isHidden) {
                return { kind: 'empty' };
            }

            if (typeToken === 'driver') {
                return { kind: 'driver' };
            }

            if (typeToken === 'bagasi' || typeToken === 'bagasi-custom') {
                return { kind: 'empty' };
            }

            if (typeToken === 'seat') {
                const seatValue =
                    row.label ??
                    row.seatNumber ??
                    row.seat ??
                    row.code ??
                    row.value ??
                    row.number ??
                    row.no ??
                    row.id;

                if (
                    typeof seatValue === 'string' ||
                    typeof seatValue === 'number'
                ) {
                    const token = normalizeSeatToken(seatValue);

                    return token === ''
                        ? { kind: 'empty' }
                        : { kind: 'seat', seat: token };
                }

                return { kind: 'empty' };
            }

            const seatValue =
                row.seat ??
                row.label ??
                row.code ??
                row.value ??
                row.number ??
                row.no ??
                row.id;

            if (
                typeof seatValue === 'string' ||
                typeof seatValue === 'number'
            ) {
                const token = normalizeSeatToken(seatValue);

                return token === ''
                    ? { kind: 'empty' }
                    : { kind: 'seat', seat: token };
            }

            return { kind: 'empty' };
        }

        return { kind: 'empty' };
    };

    const buildFallbackSeatLayout = (count: number): LayoutCell[][] => {
        const safeCount = Math.max(count, 0);

        if (safeCount === 0) {
            return [];
        }

        const fallback: LayoutCell[][] = [];
        fallback.push([
            { kind: 'seat', seat: '1' },
            { kind: 'seat', seat: '2' },
            { kind: 'driver' },
        ]);

        let seatNumber = 3;

        while (seatNumber <= safeCount) {
            const row: LayoutCell[] = [];

            for (let col = 0; col < 3; col += 1) {
                if (seatNumber <= safeCount) {
                    row.push({ kind: 'seat', seat: String(seatNumber) });
                    seatNumber += 1;
                } else {
                    row.push({ kind: 'empty' });
                }
            }

            fallback.push(row);
        }

        return fallback;
    };

    const resolveLayoutRows = (
        rawLayout: unknown,
        fallbackCount = 0,
    ): LayoutCell[][] => {
        const parsedRows: LayoutCell[][] = [];

        if (Array.isArray(rawLayout) && rawLayout.length > 0) {
            if (Array.isArray(rawLayout[0])) {
                for (const row of rawLayout as unknown[][]) {
                    parsedRows.push(row.map((item) => toLayoutCell(item)));
                }
            } else {
                const flat = (rawLayout as unknown[]).map((item) =>
                    toLayoutCell(item),
                );

                if (
                    flat.some(
                        (cell) =>
                            cell.kind === 'seat' || cell.kind === 'driver',
                    )
                ) {
                    const rowSize = 4;

                    for (let i = 0; i < flat.length; i += rowSize) {
                        parsedRows.push(flat.slice(i, i + rowSize));
                    }
                }
            }
        }

        return parsedRows.length > 0
            ? parsedRows
            : buildFallbackSeatLayout(Math.max(Number(fallbackCount) || 0, 0));
    };
    const seatLayoutRows = (): LayoutCell[][] => {
        const schedule = activeSchedule();
        const unitOption = activeScheduleUnitOption();
        const layoutSource =
            Array.isArray(unitOption?.layout) && unitOption.layout.length > 0
                ? unitOption.layout
                : schedule?.layout;

        return resolveLayoutRows(layoutSource, totalSeats());
    };

    const compareSeatTokens = (a: string, b: string) => {
        const aNum = Number(a);
        const bNum = Number(b);

        if (Number.isFinite(aNum) && Number.isFinite(bNum)) {
            return aNum - bNum;
        }

        return a.localeCompare(b);
    };

    const extractSeatTokensFromLayout = (
        rawLayout: unknown,
        fallbackCount = 0,
    ): string[] => {
        const sourceRows = resolveLayoutRows(rawLayout, fallbackCount);
        const seatTokens = sourceRows
            .flatMap((row) => row)
            .filter(
                (cell) => cell.kind === 'seat' && typeof cell.seat === 'string',
            )
            .map((cell) => normalizeSeatToken(cell.seat || ''))
            .filter((seat) => seat !== '');

        return Array.from(new Set(seatTokens)).sort(compareSeatTokens);
    };
    const groupEditSeatLabel = (seat: string) => {
        const normalized = normalizeSeatToken(seat);
        const current = normalizeSeatToken(groupEditCurrentSeat);

        if (normalized === current) {
            return `Seat ${seat} - saat ini`;
        }

        return `Seat ${seat} - kosong`;
    };
    const groupEditSeatHelpText = () => {
        if (loadingGroupEditSeats) {
            return 'Memuat seat kosong sesuai layout keberangkatan...';
        }

        if (groupEditSeatWarning !== '') {
            return groupEditSeatWarning;
        }

        const current = normalizeSeatToken(groupEditCurrentSeat);
        const emptySeats = groupEditSeatOptions.filter(
            (seat) => normalizeSeatToken(seat) !== current,
        );

        if (groupEditLayoutSeatTokens.length === 0) {
            return current !== ''
                ? 'Layout tidak ditemukan. Seat saat ini tetap bisa dipertahankan.'
                : 'Seat tidak tersedia pada jadwal ini.';
        }

        if (emptySeats.length === 0) {
            return `Tidak ada seat kosong lain. Total layout ${groupEditLayoutSeatTokens.length} seat.`;
        }

        return `${emptySeats.length} seat kosong tersedia dari ${groupEditLayoutSeatTokens.length} seat layout.`;
    };
    const groupEditSeatStatus = (seat: string) => {
        const normalized = normalizeSeatToken(seat);
        const current = normalizeSeatToken(groupEditCurrentSeat);

        if (normalized === current) {
            return 'current';
        }

        return groupEditBookedSeatTokens.includes(normalized)
            ? 'taken'
            : 'open';
    };
    const groupEditSeatButtonClass = (seat: string) => {
        const status = groupEditSeatStatus(seat);
        const isSelected =
            normalizeSeatToken(groupEditSeat) === normalizeSeatToken(seat);

        if (status === 'taken') {
            return 'border-rose-300 bg-rose-50 text-rose-600 dark:border-rose-500/30 dark:bg-rose-950/30 dark:text-rose-200';
        }

        if (isSelected) {
            return 'border-primary bg-primary text-primary-foreground shadow-sm';
        }

        if (status === 'current') {
            return 'border-cyan-300 bg-cyan-50 text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-950/30 dark:text-cyan-200';
        }

        return 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:border-emerald-400 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-950/30 dark:text-emerald-200';
    };
    const selectGroupEditSeat = (seat: string) => {
        if (
            groupEditSeatStatus(seat) === 'taken' ||
            savingGroupRowEdit ||
            loadingGroupEditSeats
        ) {
            return;
        }

        groupEditSeat = normalizeSeatToken(seat);
    };
    const groupRescheduleSeatLabel = (seat: string) => {
        const normalized = normalizeSeatToken(seat);
        const current = normalizeSeatToken(groupRescheduleCurrentSeat);

        if (normalized === current) {
            return `Seat ${seat} - seat saat ini`;
        }

        return `Seat ${seat} - tersedia`;
    };
    const groupRescheduleSeatHelpText = () => {
        if (loadingGroupRescheduleSeats) {
            return 'Memuat seat tersedia sesuai keberangkatan tujuan...';
        }

        if (groupRescheduleSeatWarning !== '') {
            return groupRescheduleSeatWarning;
        }

        if (groupRescheduleLayoutSeatTokens.length === 0) {
            return 'Belum ada layout seat pada keberangkatan tujuan.';
        }

        const current = normalizeSeatToken(groupRescheduleCurrentSeat);
        const emptySeats = groupRescheduleSeatOptions.filter(
            (seat) => normalizeSeatToken(seat) !== current,
        );

        if (emptySeats.length === 0) {
            return `Tidak ada seat kosong lain. Total layout ${groupRescheduleLayoutSeatTokens.length} seat.`;
        }

        return `${emptySeats.length} seat tersedia dari ${groupRescheduleLayoutSeatTokens.length} seat layout.`;
    };
    const groupRescheduleSeatStatus = (seat: string) => {
        const normalized = normalizeSeatToken(seat);
        const current = normalizeSeatToken(groupRescheduleCurrentSeat);

        if (normalized === current) {
            return 'current';
        }

        return groupRescheduleBookedSeatTokens.includes(normalized)
            ? 'taken'
            : 'open';
    };
    const groupRescheduleSeatButtonClass = (seat: string) => {
        const status = groupRescheduleSeatStatus(seat);
        const isSelected =
            normalizeSeatToken(groupRescheduleSeat) ===
            normalizeSeatToken(seat);

        if (status === 'taken') {
            return 'border-rose-300 bg-rose-50 text-rose-600 dark:border-rose-500/30 dark:bg-rose-950/30 dark:text-rose-200';
        }

        if (isSelected) {
            return 'border-primary bg-primary text-primary-foreground shadow-sm';
        }

        if (status === 'current') {
            return 'border-cyan-300 bg-cyan-50 text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-950/30 dark:text-cyan-200';
        }

        return 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:border-emerald-400 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-950/30 dark:text-emerald-200';
    };
    const selectGroupRescheduleSeat = (seat: string) => {
        if (
            groupRescheduleSeatStatus(seat) === 'taken' ||
            savingGroupReschedule ||
            loadingGroupRescheduleSeats
        ) {
            return;
        }

        groupRescheduleSeat = normalizeSeatToken(seat);
    };

    const visibleSeatLayoutRows = () =>
        hasSelectedTrip()
            ? seatLayoutRows()
            : buildFallbackSeatLayout(defaultPreviewSeats);
    const totalSeatsDisplay = () =>
        hasSelectedTrip() ? totalSeats() : defaultPreviewSeats;
    const bookedCountDisplay = () => (hasSelectedTrip() ? bookedCount() : 0);
    const emptyCountDisplay = () =>
        Math.max(totalSeatsDisplay() - bookedCountDisplay(), 0);
    const isSeatTapAnimating = (seat: string) =>
        normalizeSeatToken(seat) === lastTappedSeat;
    const isSeatSelectedPulseAnimating = (seat: string) =>
        normalizeSeatToken(seat) === lastSelectedPulseSeat;

    const triggerSeatTap = (seat: string) => {
        const token = normalizeSeatToken(seat);

        if (!token) {
            return;
        }

        lastTappedSeat = token;

        if (seatTapTimer) {
            clearTimeout(seatTapTimer);
        }

        seatTapTimer = setTimeout(() => {
            if (lastTappedSeat === token) {
                lastTappedSeat = '';
            }
        }, 220);
    };

    const triggerSeatSelectedPulse = (seat: string) => {
        const token = normalizeSeatToken(seat);

        if (!token) {
            return;
        }

        lastSelectedPulseSeat = token;

        if (seatPulseTimer) {
            clearTimeout(seatPulseTimer);
        }

        seatPulseTimer = setTimeout(() => {
            if (lastSelectedPulseSeat === token) {
                lastSelectedPulseSeat = '';
            }
        }, 340);
    };

    const selectSeat = (seat: string) => {
        const token = normalizeSeatToken(seat);

        if (token === '' || bookedSeatSet().has(token)) {
            return;
        }

        if (selectedSeats.includes(token)) {
            selectedSeats = selectedSeats.filter((item) => item !== token);
        } else {
            selectedSeats = [...selectedSeats, token];
            triggerSeatSelectedPulse(token);
        }

        syncFormSeatFromSelected();
        formError = '';
    };

    const openSeatDetail = (seat: string) => {
        const token = normalizeSeatToken(seat);
        const detail = seatDetailsMap[token];

        if (!detail) {
            return;
        }

        detailSeat = {
            seat: token,
            ...detail,
        };
        detailEditMode = false;
        detailEditSeat = token;
        detailEditName = detail.name || '';
        detailEditPhone = detail.phone || '';
        detailEditPickupPoint = detail.pickup_point || '';
        detailEditPayment = detail.pembayaran || 'Belum Lunas';
        detailEditSegmentId = Number(detail.segment_id || 0);
        detailEditDiscount = Number(detail.discount || 0);
        detailModalOpen = true;
    };

    const closeSeatDetail = () => {
        detailModalOpen = false;
        detailSeat = null;
        detailEditMode = false;
        savingDetailEdit = false;
    };

    const startDetailEdit = () => {
        if (!detailSeat) {
            return;
        }

        if (isSelectedTripManifestClosed()) {
            formError = 'Manifest sudah ditutup. Data penumpang tidak bisa diedit.';
            formSuccess = '';

            return;
        }

        detailEditMode = true;
        formError = '';
    };

    const cancelDetailEdit = () => {
        if (!detailSeat || savingDetailEdit) {
            return;
        }

        detailEditMode = false;
        detailEditSeat = detailSeat.seat || '';
        detailEditName = detailSeat.name || '';
        detailEditPhone = detailSeat.phone || '';
        detailEditPickupPoint = detailSeat.pickup_point || '';
        detailEditPayment = detailSeat.pembayaran || 'Belum Lunas';
        detailEditSegmentId = Number(detailSeat.segment_id || 0);
        detailEditDiscount = Number(detailSeat.discount || 0);
        formError = '';
    };

    const onSeatCellClick = (seat: string) => {
        triggerSeatTap(seat);

        if (isSeatBooked(seat)) {
            openSeatDetail(seat);

            return;
        }

        selectSeat(seat);
    };

    const seatAriaLabel = (seat: string) => {
        const token = normalizeSeatToken(seat);

        if (token === '') {
            return 'Kursi';
        }

        if (isSeatBooked(token)) {
            const detail = seatDetailsMap[token];

            if (detail?.name) {
                return `Kursi ${token}, terisi oleh ${detail.name}`;
            }

            return `Kursi ${token}, terisi`;
        }

        if (isSeatSelected(token)) {
            return `Kursi ${token}, terpilih`;
        }

        return `Kursi ${token}, tersedia`;
    };

    const focusAdjacentSeat = (seat: string, step: number) => {
        const nodes = Array.from(
            document.querySelectorAll<HTMLButtonElement>(
                'button[data-seat-button="true"][data-seat-token]',
            ),
        );

        if (nodes.length === 0) {
            return;
        }

        const currentToken = normalizeSeatToken(seat);
        const currentIndex = nodes.findIndex(
            (node) =>
                normalizeSeatToken(node.dataset.seatToken ?? '') ===
                currentToken,
        );

        if (currentIndex < 0) {
            return;
        }

        const nextIndex = Math.max(
            0,
            Math.min(nodes.length - 1, currentIndex + step),
        );
        nodes[nextIndex]?.focus();
    };

    const onSeatKeydown = (event: KeyboardEvent, seat: string) => {
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
            event.preventDefault();
            focusAdjacentSeat(seat, 1);

            return;
        }

        if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
            event.preventDefault();
            focusAdjacentSeat(seat, -1);
        }
    };

    const addCurrentDetailToRekap = () => {
        if (!detailSeat) {
            return;
        }

        const currentDetail = detailSeat;

        const exists = rekapItems.some(
            (item) => item.booking_id === currentDetail.id,
        );

        if (exists) {
            formError = `Kursi ${currentDetail.seat} sudah ada di rekap.`;

            return;
        }

        const finalPrice = Math.max(
            Number(currentDetail.price || 0) -
                Number(currentDetail.discount || 0),
            0,
        );
        rekapItems = [
            ...rekapItems,
            {
                booking_id: currentDetail.id,
                seat: currentDetail.seat,
                name: currentDetail.name,
                phone: currentDetail.phone,
                pickup_point: currentDetail.pickup_point,
                segment_name: currentDetail.segment_name || '-',
                segment_jam: currentDetail.segment_jam,
                segment_jam_pickups: currentDetail.segment_jam_pickups,
                pembayaran: currentDetail.pembayaran,
                final_price: finalPrice,
            },
        ];
        formSuccess = `Kursi ${currentDetail.seat} ditambahkan ke rekap.`;
    };

    const copyCurrentDetail = async () => {
        if (!detailSeat) {
            return;
        }

        const finalPrice = Math.max(
            Number(detailSeat.price || 0) - Number(detailSeat.discount || 0),
            0,
        );
        const payment = String(detailSeat.pembayaran || '').trim();
        const includePaymentOptions = payment.toLowerCase() === 'belum lunas';

        const lines = [
            'BOOKING BERHASIL ✅',
            '',
            `Tanggal: ${bookingDate || '-'}`,
            `Jam Segment: ${segmentJamSummary(detailSeat.segment_jam_pickups) || segmentJamLabel(detailSeat.segment_jam) || '-'}`,
            `Kursi: ${detailSeat.seat || '-'}`,
            `Nama: ${detailSeat.name || '-'}`,
            `Telepon: ${detailSeat.phone || '-'}`,
            `Segment: ${detailSeat.segment_name || '-'}`,
            `Jemput: ${detailSeat.pickup_point || '-'}`,
            '',
            `Harga: Rp ${finalPrice.toLocaleString('id-ID')}`,
        ];

        if (includePaymentOptions) {
            lines.push('_*Opsi Pembayaran: Transfer / QRIS / Tunai_');
        }

        const text = lines.join('\n');

        try {
            await copyText(text);
            formSuccess = `Data penumpang kursi ${detailSeat.seat} disalin.`;
            formError = '';
        } catch {
            formError = 'Gagal menyalin detail penumpang.';
        }
    };

    const saveDetailEdit = async () => {
        if (!detailSeat || savingDetailEdit) {
            return;
        }

        if (isSelectedTripManifestClosed()) {
            formError = 'Manifest sudah ditutup. Data penumpang tidak bisa diubah lagi.';
            formSuccess = '';

            return;
        }

        const currentDetailSeat = detailSeat;

        const seat = normalizeSeatToken(detailEditSeat);
        const name = normalizeNameForBooking(detailEditName);
        const phone = normalizePhoneForBooking(detailEditPhone);
        const pickupPoint = String(detailEditPickupPoint || '').trim();
        const pembayaran = String(detailEditPayment || 'Belum Lunas').trim();
        const segmentId =
            Number(detailEditSegmentId) > 0 ? Number(detailEditSegmentId) : 0;
        const discount = parseCurrencyInput(detailEditDiscount);

        if (seat === '' || name === '' || phone === '' || pickupPoint === '') {
            formError = 'Seat, nama, telepon, dan jemput wajib diisi.';

            return;
        }

        if (
            groupEditSeatOptions.length > 0 &&
            !groupEditSeatOptions.includes(seat)
        ) {
            formError =
                'Seat yang dipilih tidak tersedia. Pilih dari daftar seat kosong.';

            return;
        }

        if (phone.length > 13) {
            formError = 'Nomor HP maksimal 13 digit.';

            return;
        }

        savingDetailEdit = true;
        formError = '';
        formSuccess = '';

        try {
            await apiPost('/api/bookings/update', {
                booking_id: currentDetailSeat.id,
                seat,
                name,
                phone,
                pickup_point: pickupPoint,
                pembayaran,
                segment_id: segmentId,
                discount,
            });

            await loadSeatDetails();
            const refreshed = seatDetailsMap[seat];

            if (refreshed) {
                detailSeat = {
                    seat,
                    ...refreshed,
                };
            } else {
                detailSeat = {
                    ...currentDetailSeat,
                    seat,
                    name,
                    phone,
                    pickup_point: pickupPoint,
                    pembayaran,
                    segment_id: segmentId,
                    discount,
                };
            }

            detailEditMode = false;
            formSuccess = `Data kursi ${seat} berhasil diperbarui.`;
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memperbarui booking.';
        } finally {
            savingDetailEdit = false;
        }
    };

    const markDetailAsPaid = async () => {
        if (!detailSeat || !detailSeat.id || markingPaidSeatId !== null) {
            return;
        }

        if (isSelectedTripManifestClosed()) {
            formError = 'Manifest sudah ditutup. Status pembayaran tidak bisa diubah lagi.';
            formSuccess = '';

            return;
        }

        if (isLunasPayment(detailSeat.pembayaran)) {
            formSuccess = 'Pembayaran sudah berstatus Lunas.';
            formError = '';

            return;
        }

        const bookingId = detailSeat.id;
        const seatToken = detailSeat.seat;
        const currentDetailSeat = detailSeat;

        markingPaidSeatId = bookingId;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/update', {
                        booking_id: bookingId,
                        pembayaran: 'Lunas',
                    });

                    await loadSeatDetails();
                    const refreshed = seatDetailsMap[seatToken];

                    if (refreshed) {
                        detailSeat = {
                            seat: seatToken,
                            ...refreshed,
                        };
                    } else {
                        detailSeat = {
                            ...currentDetailSeat,
                            pembayaran: 'Lunas',
                        };
                    }

                    rekapItems = rekapItems.map((item) =>
                        item.booking_id === bookingId
                            ? { ...item, pembayaran: 'Lunas' }
                            : item,
                    );
                    localLatestBookings = localLatestBookings.map((row) =>
                        row.id === bookingId
                            ? { ...row, pembayaran: 'Lunas' }
                            : row,
                    );
                    detailEditPayment = 'Lunas';
                },
                {
                    loadingMessage: `Memproses pembayaran kursi ${seatToken}...`,
                    successMessage: `Pembayaran kursi ${seatToken} berhasil diubah ke Lunas.`,
                    errorMessage: 'Gagal mengubah status pembayaran.',
                },
            );
            formSuccess = `Pembayaran kursi ${seatToken} berhasil diubah ke Lunas.`;
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal mengubah status pembayaran.';
        } finally {
            markingPaidSeatId = null;
        }
    };

    const removeRekapItem = (bookingId: number) => {
        rekapItems = rekapItems.filter((item) => item.booking_id !== bookingId);
    };

    const resetRekap = () => {
        rekapItems = [];
    };

    const copyRekap = async () => {
        if (rekapItems.length === 0) {
            formError = 'Rekap masih kosong.';

            return;
        }

        const header = `BOOKING BERHASIL ✅\n\nTanggal : ${bookingDate}`;
        const lines = rekapItems.map(
            (item, index) =>
                `${index + 1}. Kursi ${item.seat} - ${item.name} - ${item.phone}\n` +
                `   Segment: ${item.segment_name}\n` +
                `   Jam Segment: ${segmentJamSummary(item.segment_jam_pickups) || segmentJamLabel(item.segment_jam) || '-'}\n` +
                `   Jemput: ${item.pickup_point || '-'}\n` +
                `   Pembayaran: ${item.pembayaran}\n` +
                `   Harga: Rp ${item.final_price.toLocaleString('id-ID')}`,
        );
        const footer = `Total: Rp ${rekapTotal().toLocaleString('id-ID')}`;
        const hasBelumLunas = rekapItems.some(
            (item) =>
                String(item.pembayaran || '')
                    .trim()
                    .toLowerCase() === 'belum lunas',
        );
        const paymentHint = hasBelumLunas
            ? '_*Opsi Pembayaran: Transfer / QRIS / Tunai_'
            : '';
        const text = [header, ...lines, footer, paymentHint]
            .filter((part) => part !== '')
            .join('\n\n');

        try {
            await copyText(text);
            formSuccess = 'Rekap berhasil disalin.';
        } catch {
            formError = 'Gagal menyalin rekap.';
        }
    };

    const buildBookingSuccessCopyText = (snapshot: BookingSuccessSnapshot) => {
        if (snapshot.items.length === 0) {
            return '';
        }

        if (snapshot.items.length === 1) {
            const item = snapshot.items[0];
            const lines = [
                'BOOKING BERHASIL ✅',
                '',
                `Tanggal: ${snapshot.tanggal || '-'}`,
                `Jam Segment: ${segmentJamSummary(snapshot.segment_jam_pickups) || segmentJamLabel(snapshot.segment_jam) || '-'}`,
                `Kursi: ${item.seat || '-'}`,
                `Nama: ${item.name || '-'}`,
                `Telepon: ${item.phone || '-'}`,
                `Segment: ${item.segment_name || '-'}`,
                `Jemput: ${item.pickup_point || '-'}`,
                '',
                `Harga: Rp ${item.final_price.toLocaleString('id-ID')}`,
            ];

            if (
                String(item.pembayaran || '')
                    .trim()
                    .toLowerCase() === 'belum lunas'
            ) {
                lines.push('_*Opsi Pembayaran: Transfer / QRIS / Tunai_');
            }

            return lines.join('\n');
        }

        const header = `BOOKING BERHASIL ✅\n\nTanggal : ${snapshot.tanggal}`;
        const lines = snapshot.items.map(
            (item, index) =>
                `${index + 1}. Kursi ${item.seat} - ${item.name} - ${item.phone}\n` +
                `   Segment: ${item.segment_name || '-'}\n` +
                `   Jam Segment: ${segmentJamSummary(item.segment_jam_pickups) || segmentJamLabel(item.segment_jam) || segmentJamSummary(snapshot.segment_jam_pickups) || segmentJamLabel(snapshot.segment_jam) || '-'}\n` +
                `   Jemput: ${item.pickup_point || '-'}\n` +
                `   Pembayaran: ${item.pembayaran || '-'}\n` +
                `   Harga: Rp ${item.final_price.toLocaleString('id-ID')}`,
        );
        const footer = `Total: Rp ${snapshot.total.toLocaleString('id-ID')}`;
        const hasBelumLunas = snapshot.items.some(
            (item) =>
                String(item.pembayaran || '')
                    .trim()
                    .toLowerCase() === 'belum lunas',
        );
        const paymentHint = hasBelumLunas
            ? '_*Opsi Pembayaran: Transfer / QRIS / Tunai_'
            : '';

        return [header, ...lines, footer, paymentHint]
            .filter((part) => part !== '')
            .join('\n\n');
    };

    const copyBookingSuccessDetail = async () => {
        if (!bookingSuccessSnapshot) {
            return;
        }

        try {
            await copyText(buildBookingSuccessCopyText(bookingSuccessSnapshot));
            bookingSuccessFeedback = 'Detail booking berhasil disalin.';
        } catch {
            bookingSuccessFeedback = 'Gagal menyalin detail booking.';
        }
    };

    const closeBookingSuccessModal = () => {
        bookingSuccessModalOpen = false;
        bookingSuccessFeedback = '';
    };

    const syncSelectedSeatsFromInput = () => {
        const selected = parseSeatInput(formSeat).filter(
            (seat) => !bookedSeatSet().has(seat),
        );
        selectedSeats = selected;
    };

    const toSeatTokenList = (value: unknown): string[] => {
        if (!Array.isArray(value)) {
            return [];
        }

        return value
            .map((seat) => normalizeSeatToken(String(seat ?? '')))
            .filter((seat) => seat !== '');
    };

    const uniqueSortedSeatTokens = (tokens: string[]): string[] =>
        Array.from(new Set(tokens)).sort(compareSeatTokens);

    const clearSeatSelection = () => {
        selectedSeats = [];
        formSeat = '';
        formError = '';
    };

    const applyCustomerLookup = (item: CustomerLookupItem) => {
        customerSearchRequestId += 1;

        if (customerSearchTimer) {
            clearTimeout(customerSearchTimer);
            customerSearchTimer = null;
        }

        formName = normalizeNameForBooking(item.name || '');
        formPhone = normalizePhoneForBooking(item.phone || '');
        formPickupPoint = item.pickup_point || '';
        formAddress = item.address || '';
        customerLookupQuery = `${item.name} (${item.phone})`;
        customerSuggestOpen = false;
        customerSuggestions = [];
        customerLookupMessage = '';
        loadingCustomerLookup = false;
        formError = '';
    };

    const onFormNameInput = () => {
        formName = normalizeNameForBooking(formName);
    };

    const onFormPhoneInput = () => {
        formPhone = normalizePhoneForBooking(formPhone);
    };

    const searchCustomers = async (query: string, requestId: number) => {
        const q = query.trim();

        if (q.length < 2 || requestId !== customerSearchRequestId) {
            return;
        }

        loadingCustomerLookup = true;

        try {
            const json = await apiGet('/api/master/customers/search', { q });

            if (requestId !== customerSearchRequestId) {
                return;
            }

            const rows = Array.isArray(json.customers) ? json.customers : [];
            customerSuggestions = rows
                .slice(0, 20)
                .map((row: Record<string, unknown>) => ({
                    name: String(row.name ?? ''),
                    phone: String(row.phone ?? ''),
                    pickup_point: String(row.pickup_point ?? ''),
                    address: String(row.address ?? row.gmaps ?? ''),
                }));
            customerSuggestOpen = customerSuggestions.length > 0;
            const scopeName = String(json.scope_name ?? '').trim();
            const scopeSuffix =
                Boolean(json.scope_limited) && scopeName
                    ? ` pada ${scopeName}`
                    : '';

            if (customerSuggestions.length === 0) {
                customerLookupMessage = `Customer tidak ditemukan${scopeSuffix}.`;
            } else if (json.has_more) {
                customerLookupMessage =
                    'Menampilkan 20 hasil teratas. Tambahkan nama atau nomor telepon agar lebih spesifik.';
            } else if (scopeSuffix) {
                customerLookupMessage = `Hasil pencarian dibatasi${scopeSuffix}.`;
            } else {
                customerLookupMessage = '';
            }
        } catch {
            if (requestId !== customerSearchRequestId) {
                return;
            }

            customerSuggestions = [];
            customerSuggestOpen = false;
            customerLookupMessage = 'Pencarian customer gagal dimuat.';
        } finally {
            if (requestId === customerSearchRequestId) {
                loadingCustomerLookup = false;
            }
        }
    };

    const onCustomerLookupInput = () => {
        if (customerSearchTimer) {
            clearTimeout(customerSearchTimer);
            customerSearchTimer = null;
        }

        customerSearchRequestId += 1;
        const requestId = customerSearchRequestId;
        const query = customerLookupQuery.trim();
        customerSuggestions = [];
        customerSuggestOpen = false;
        customerLookupMessage =
            query.length === 1 ? 'Ketik minimal 2 karakter untuk mencari.' : '';
        loadingCustomerLookup = false;

        if (query.length < 2) {
            return;
        }

        customerSearchTimer = setTimeout(() => {
            customerSearchTimer = null;
            void searchCustomers(customerLookupQuery, requestId);
        }, 220);
    };

    const onCustomerLookupBlur = () => {
        setTimeout(() => {
            customerSuggestOpen = false;
        }, 120);
    };

    const autofillByPhone = async () => {
        formPhone = normalizePhoneForBooking(formPhone);
        const phone = normalizePhoneToken(formPhone);

        if (phone.length < 6) {
            return;
        }

        try {
            const json = await apiGet('/api/master/customers/search', {
                q: phone,
            });
            const rows = Array.isArray(json.customers) ? json.customers : [];
            const exact = rows.find(
                (row: Record<string, unknown>) =>
                    normalizePhoneToken(String(row.phone ?? '')) === phone,
            );

            if (exact) {
                applyCustomerLookup({
                    name: String(exact.name ?? ''),
                    phone: String(exact.phone ?? ''),
                    pickup_point: String(exact.pickup_point ?? ''),
                    address: String(exact.address ?? ''),
                });
            }
        } catch {
            // no-op, user can keep manual input
        }
    };

    const normalizeJamToken = (value: string) =>
        String(value || '').slice(0, 5);
    const segmentJamLabel = (value: string | null | undefined) =>
        normalizeJamToken(String(value ?? ''));
    const segmentJamList = (value: string[] | string | null | undefined) =>
        Array.isArray(value)
            ? value
                  .map((item) => segmentJamLabel(item))
                  .filter((item) => item !== '')
            : segmentJamLabel(value)
              ? [segmentJamLabel(value)]
              : [];
    const segmentJamSummary = (value: string[] | string | null | undefined) =>
        segmentJamList(value).join(', ');
    const formatCurrency = (value: number) => formatCurrencyDisplay(value);
    const parseGroupDateTime = (tanggal: string, jam: string) => {
        const datePart = String(tanggal || '').slice(0, 10);
        const timePart = normalizeJamToken(jam) || '00:00';
        const parsed = new Date(`${datePart}T${timePart}:00`);

        return Number.isNaN(parsed.getTime()) ? null : parsed;
    };
    const clockKeyFromDate = (date: Date) => {
        const hour = String(date.getHours()).padStart(2, '0');
        const minute = String(date.getMinutes()).padStart(2, '0');

        return `${toDateKey(date)} ${hour}:${minute}`;
    };
    const currentClockKey = () => {
        const normalizedServerNow = String(serverNow || '')
            .trim()
            .replace('T', ' ');
        const serverMatch = normalizedServerNow.match(
            /^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})/,
        );

        if (serverMatch) {
            const serverClock = new Date(
                `${serverMatch[1]}T${serverMatch[2]}:00`,
            );
            const elapsedMs = clientClockTickMs - initialClientClockMs;

            return clockKeyFromDate(
                new Date(serverClock.getTime() + elapsedMs),
            );
        }

        return clockKeyFromDate(new Date(clientClockTickMs));
    };
    const isArrivedDeparture = (group: BookingGroup | null | undefined) =>
        String(group?.departure_status || '')
            .trim()
            .toLowerCase() === 'arrived';
    const isDepartedDeparture = (group: BookingGroup | null | undefined) =>
        String(group?.departure_status || '')
            .trim()
            .toLowerCase() === 'departed';
    const isManifestClosed = (group: BookingGroup | null | undefined) =>
        String(group?.departure_status || '')
            .trim()
            .toLowerCase() === 'closed';
    const isManifestLocked = (group: BookingGroup | null | undefined) =>
        isCanceledDeparture(group) || isManifestClosed(group);
    const isHistoryGroup = (group: BookingGroup) =>
        isArrivedDeparture(group) ||
        isCanceledDeparture(group) ||
        isManifestClosed(group);
    const isCanceledDeparture = (group: BookingGroup | null | undefined) =>
        String(group?.departure_status || '')
            .trim()
            .toLowerCase() === 'canceled';
    const hasDepartureDateReached = (
        group: BookingGroup | null | undefined,
    ) => {
        if (!group) {
            return false;
        }

        const datePart = String(group.tanggal || '').slice(0, 10);

        if (!/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
            return false;
        }

        const today = currentClockKey().slice(0, 10);

        return datePart <= today;
    };
    const hasMeaningfulAssignmentValue = (value: string | null | undefined) => {
        const normalized = String(value || '')
            .trim()
            .toLowerCase();

        return ![
            '',
            '-',
            'null',
            'undefined',
            'n/a',
            'na',
            'belum diisi',
            'belum ada',
            'belum dipilih',
        ].includes(normalized);
    };
    const hasRequiredDepartureAssignmentMeta = (
        group: BookingGroup | null | undefined,
    ) => {
        if (!group) {
            return false;
        }

        return (
            hasMeaningfulAssignmentValue(group.driver_name) &&
            hasMeaningfulAssignmentValue(group.armada_nopol)
        );
    };
    const canCloseManifest = (group: BookingGroup | null | undefined) => {
        if (!group || isCanceledDeparture(group) || isManifestClosed(group)) {
            return false;
        }

        return isArrivedDeparture(group);
    };
    const canMarkDepartureDeparted = (
        group: BookingGroup | null | undefined,
    ) => {
        if (
            !group ||
            isCanceledDeparture(group) ||
            isDepartedDeparture(group) ||
            isArrivedDeparture(group) ||
            isManifestClosed(group)
        ) {
            return false;
        }

        if (!hasRequiredDepartureAssignmentMeta(group)) {
            return false;
        }

        return hasDepartureDateReached(group);
    };
    const canMarkDepartureArrived = (
        group: BookingGroup | null | undefined,
    ) => {
        if (
            !group ||
            isCanceledDeparture(group) ||
            isArrivedDeparture(group) ||
            isManifestClosed(group)
        ) {
            return false;
        }

        if (!isDepartedDeparture(group)) {
            return false;
        }

        if (!hasRequiredDepartureAssignmentMeta(group)) {
            return false;
        }

        return hasDepartureDateReached(group);
    };
    const selectedTripGroup = () =>
        localBookingGroups.find((group) => {
            return (
                normalizeRiturRoute(group.rute) ===
                    normalizeRiturRoute(selectedRoute) &&
                String(group.tanggal || '').slice(0, 10) ===
                    String(bookingDate || '').slice(0, 10) &&
                normalizeJamToken(group.jam) === normalizeJamToken(selectedJam) &&
                Number(group.unit || 1) === Number(selectedUnit || 1)
            );
        }) ?? null;
    const isSelectedTripManifestClosed = () =>
        isManifestLocked(selectedTripGroup());
    const hasSavedGroupDriverMapping = () =>
        !!openGroupDetail &&
        hasMeaningfulAssignmentValue(openGroupDetail.driver_name) &&
        hasMeaningfulAssignmentValue(openGroupDetail.armada_nopol);
    const isReadonlyHistoryGroup = (group: BookingGroup | null | undefined) => {
        return !!group && isHistoryGroup(group);
    };
    const isReadonlyHistoryBooking = (bookingId: number) => {
        if (!openGroupDetail || bookingId <= 0) {
            return false;
        }

        if (!isHistoryGroup(openGroupDetail)) {
            return false;
        }

        return openGroupDetail.bookings.some((row) => row.id === bookingId);
    };
    const formatGroupDateLabel = (tanggal: string) => {
        const parsed = new Date(
            `${String(tanggal || '').slice(0, 10)}T00:00:00`,
        );

        if (Number.isNaN(parsed.getTime())) {
            return tanggal;
        }

        return parsed.toLocaleDateString('id-ID', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    };
    const formatGroupTimeLabel = (jam: string) =>
        normalizeJamToken(jam) || '--:--';
    const activeGroupBookings = () =>
        visibleGroupBookingRows(
            (openGroupDetail?.bookings ?? []).filter(
                (row) => !isCanceledBooking(row.status),
            ),
        );
    const visibleGroupBookings = () => {
        if (groupPassengerTab === 'ritur') {
            return [];
        }

        return activeGroupBookings();
    };
    const groupMappedRiturRevenue = () =>
        groupMappedRiturs.reduce(
            (total, row) => total + Number(row.price || 0),
            0,
        );
    const bookingRowFinalPrice = (row: BookingGroup['bookings'][number]) =>
        Math.max(Number(row.price || 0) - Number(row.discount || 0), 0);
    const groupPaymentTotals = () => {
        const rows = activeGroupBookings();

        return {
            lunas: rows
                .filter((row) => isLunasPayment(row.pembayaran))
                .reduce((total, row) => total + bookingRowFinalPrice(row), 0),
            belumLunas: rows
                .filter((row) => isBelumLunasPayment(row.pembayaran))
                .reduce((total, row) => total + bookingRowFinalPrice(row), 0),
        };
    };
    const bookingGroupUnpaidAmount = (group: BookingGroup) =>
        visibleGroupBookingRows(group.bookings)
            .filter(
                (row) =>
                    !isCanceledBooking(row.status) &&
                    isBelumLunasPayment(row.pembayaran),
            )
            .reduce((total, row) => total + bookingRowFinalPrice(row), 0);
    const bookingGroupPaidAmount = (group: BookingGroup) =>
        visibleGroupBookingRows(group.bookings)
            .filter(
                (row) =>
                    !isCanceledBooking(row.status) &&
                    isLunasPayment(row.pembayaran),
            )
            .reduce((total, row) => total + bookingRowFinalPrice(row), 0);
    const bookingAssignmentText = (
        value: string | null | undefined,
        fallback: string,
    ) =>
        hasMeaningfulAssignmentValue(value) ? String(value).trim() : fallback;
    const bookingAssignmentMissing = (value: string | null | undefined) =>
        !hasMeaningfulAssignmentValue(value);
    const normalizeRiturRoute = (value: string | null | undefined) =>
        String(value || '')
            .trim()
            .toUpperCase()
            .replace(/\s+/g, ' ')
            .replace(/\s*=>\s*/g, ' TO ')
            .replace(/\s*->\s*/g, ' TO ')
            .replace(/\s*-\s*/g, ' TO ');
    const luggageStatusLabel = (status: string | null | undefined) => {
        const normalized = String(status || '')
            .trim()
            .toLowerCase();

        if (
            normalized === '' ||
            normalized === 'pending' ||
            normalized === 'done' ||
            normalized === 'diterima' ||
            normalized === 'barang sudah diterima' ||
            normalized === luggageReceivedStatus.toLowerCase()
        ) {
            return luggageReceivedStatus;
        }

        if (
            normalized === 'active' ||
            normalized === 'sent' ||
            normalized === 'barang sudah dipickup' ||
            normalized === luggagePickedUpStatus.toLowerCase()
        ) {
            return luggagePickedUpStatus;
        }

        if (
            normalized === 'barang sudah tiba' ||
            normalized === luggageArrivedStatus.toLowerCase()
        ) {
            return luggageArrivedStatus;
        }

        if (normalized === 'canceled') {
            return 'Batal';
        }

        return normalized
            ? normalized.charAt(0).toUpperCase() + normalized.slice(1)
            : '-';
    };
    const matchesGroupRiturFilter = (row: GroupRiturRow) => {
        if (!openGroupDetail || groupRiturFilter === 'all') {
            return true;
        }

        const sameRoute =
            normalizeRiturRoute(row.rute) !== '' &&
            normalizeRiturRoute(row.rute) ===
                normalizeRiturRoute(openGroupDetail.rute);
        const sameDate =
            String(row.tanggal || '') === String(openGroupDetail.tanggal || '');

        if (groupRiturFilter === 'same_route') {
            return sameRoute;
        }

        if (groupRiturFilter === 'same_date') {
            return sameDate;
        }

        return sameRoute && sameDate;
    };
    const filteredMappedRiturs = () =>
        groupMappedRiturs.filter((row) => matchesGroupRiturFilter(row));
    const filteredAvailableRiturs = () =>
        groupAvailableRiturs.filter((row) => matchesGroupRiturFilter(row));
    const filteredGroupDrivers = () => {
        const q = groupDriverSearch.trim().toLowerCase();

        if (!q) {
            return groupDrivers;
        }

        return groupDrivers.filter((item) =>
            [item.nama, item.phone ?? ''].join(' ').toLowerCase().includes(q),
        );
    };
    const filteredGroupArmadas = () => {
        const q = groupArmadaSearch.trim().toLowerCase();

        if (!q) {
            return groupArmadas;
        }

        return groupArmadas.filter((item) =>
            [
                item.nopol,
                item.merk ?? '',
                item.kategori ?? '',
                item.warna ?? '',
                item.nomor_rangka ?? '',
            ]
                .join(' ')
                .toLowerCase()
                .includes(q),
        );
    };
    const bookingListRoutesMemo = $derived.by(() =>
        localBookingRouteOptions.length > 0
            ? localBookingRouteOptions
            : Array.from(
                  new Set(
                      localBookingGroups
                          .map((group) => String(group.rute || '').trim())
                          .filter((route) => route !== ''),
                  ),
              ).sort((a, b) => a.localeCompare(b, 'id-ID')),
    );
    const bookingListRoutes = () => bookingListRoutesMemo;
    const filteredBookingGroupsMemo = $derived.by(() => {
        const filtered = localBookingGroups.filter((group) => {
            const groupDate = String(group.tanggal || '').slice(0, 10);
            if (!groupDate) {
                return false;
            }

            const dateFrom = bookingListDateFrom || today;
            const dateTo = bookingListDateTo || dateFrom;
            const startDate = dateFrom <= dateTo ? dateFrom : dateTo;
            const endDate = dateFrom <= dateTo ? dateTo : dateFrom;

            if (groupDate < startDate || groupDate > endDate) {
                return false;
            }

            if (bookingListRoute !== 'all' && group.rute !== bookingListRoute) {
                return false;
            }

            if (
                bookingListPayment === 'lunas' &&
                (group.belum_lunas > 0 || group.refund > 0)
            ) {
                return false;
            }

            if (
                bookingListPayment === 'belum_lunas' &&
                group.belum_lunas === 0
            ) {
                return false;
            }

            return true;
        });

        return filtered.sort((a, b) => {
            const ta = parseGroupDateTime(a.tanggal, a.jam)?.getTime() ?? 0;
            const tb = parseGroupDateTime(b.tanggal, b.jam)?.getTime() ?? 0;

            return ta - tb;
        });
    });
    const filteredBookingGroups = () => filteredBookingGroupsMemo;
    const visibleBookingGroupsMemo = $derived(
        filteredBookingGroupsMemo.slice(0, bookingListVisibleCount),
    );
    const visibleBookingGroups = () => visibleBookingGroupsMemo;
    const remainingBookingGroupsCount = $derived(
        Math.max(
            filteredBookingGroupsMemo.length - visibleBookingGroupsMemo.length,
            0,
        ),
    );
    const bookingListSummary = $derived.by<BookingListSummary>(() => {
        const summary: BookingListSummary = {
            schedules: filteredBookingGroupsMemo.length,
            activePassengers: 0,
            unpaidSchedules: 0,
            unpaidPassengers: 0,
        };

        for (const group of filteredBookingGroupsMemo) {
            summary.activePassengers += Number(group.active || 0);

            if (Number(group.belum_lunas || 0) > 0) {
                summary.unpaidSchedules += 1;
                summary.unpaidPassengers += Number(group.belum_lunas || 0);
            }
        }

        return summary;
    });
    const bookingDateSectionsMemo = $derived.by<BookingDateSection[]>(() => {
        const sections = new Map<string, BookingDateSection>();

        for (const group of visibleBookingGroupsMemo) {
            const key = String(group.tanggal || '');
            let section = sections.get(key);

            if (!section) {
                section = {
                    key,
                    tanggal: key,
                    label: formatGroupDateLabel(key),
                    totalSchedules: 0,
                    totalPassengers: 0,
                    totalUnpaid: 0,
                    unpaidAmount: 0,
                    groups: [],
                };
                sections.set(key, section);
            }

            section.groups.push(group);
            section.totalSchedules += 1;
            section.totalPassengers += Number(group.active || 0);
            section.totalUnpaid += Number(group.belum_lunas || 0);
            section.unpaidAmount += bookingGroupUnpaidAmount(group);
        }

        return Array.from(sections.values());
    });
    const bookingListFilterSignature = $derived(
        [
            bookingListRoute,
            bookingListDateFrom,
            bookingListDateTo,
            bookingListPayment,
            localBookingGroups.length,
        ].join('|'),
    );

    $effect(() => {
        if (lastBookingListFilterSignature === bookingListFilterSignature) {
            return;
        }

        lastBookingListFilterSignature = bookingListFilterSignature;
        bookingListVisibleCount = BOOKING_LIST_PAGE_SIZE;
    });
    const persistUiPreferences = (
        preferences: Parameters<typeof saveUiPreferences>[0],
    ) => {
        void saveUiPreferences(preferences).catch((error) => {
            if (typeof console !== 'undefined') {
                console.warn('Gagal menyimpan preferensi UI.', error);
            }
        });
    };
    const setBookingListDesktopView = (viewMode: 'sheet' | 'cards') => {
        if (bookingListDesktopView === viewMode) {
            return;
        }

        bookingListDesktopView = viewMode;
        persistUiPreferences({ defaultViewMode: viewMode });
    };
    const setBookingListDate = (value: string) => {
        const safeDate = isIsoDateKey(value) ? value : today;

        if (
            bookingListDateFrom === safeDate &&
            bookingListDateTo === safeDate
        ) {
            return;
        }

        bookingListDateFrom = safeDate;
        bookingListDateTo = safeDate;
        persistUiPreferences({ defaultDateRange: safeDate });
    };
    const reloadBookingListData = () => {
        if (bookingListReloadTimer) {
            clearTimeout(bookingListReloadTimer);
            bookingListReloadTimer = null;
        }

        consumeDataStale(['bookings']);
        bookingListHydrated = false;
        router.reload({
            only: [...BOOKING_LIST_DATA_PROPS],
            preserveScroll: true,
        } as Parameters<typeof router.reload>[0] & {
            only: string[];
            preserveScroll: boolean;
        });
    };
    const scheduleBookingListReload = () => {
        markDataStale(['bookings', 'payments', 'flows', 'dashboard']);

        if (bookingListReloadTimer) {
            clearTimeout(bookingListReloadTimer);
        }

        bookingListReloadTimer = setTimeout(() => {
            bookingListReloadTimer = null;
            reloadBookingListData();
        }, 180);
    };
    const resetBookingListFilters = () => {
        bookingListRoute = 'all';
        bookingListDateFrom = today;
        bookingListDateTo = today;
        bookingListPayment = 'all';
        bookingListDatePicker?.setDate(today, true, 'Y-m-d');
        persistUiPreferences({ defaultDateRange: today });
    };

    const loadEmptyDepartureSchedules = async () => {
        emptyDepartureSchedules = [];
        emptyDepartureJam = '';
        emptyDepartureUnit = 1;

        if (!emptyDepartureRoute) {
            return;
        }

        loadingEmptyDepartureSchedules = true;

        try {
            const json = await apiGet('/api/bookings/schedules', {
                rute: emptyDepartureRoute,
                tanggal: emptyDepartureDate,
            });
            emptyDepartureSchedules = Array.isArray(json.schedules)
                ? json.schedules
                : [];
            emptyDepartureJam = emptyDepartureSchedules[0]?.jam ?? '';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memuat jam jadwal.';
            formSuccess = '';
        } finally {
            loadingEmptyDepartureSchedules = false;
        }
    };

    const loadEmptyDepartureRoutes = async () => {
        loadingEmptyDepartureRoutes = true;
        emptyDepartureRoutes = [];

        try {
            const json = await apiGet('/api/bookings/routes-by-date', {
                tanggal: emptyDepartureDate,
            });
            emptyDepartureRoutes = Array.isArray(json.routes)
                ? json.routes
                : [];

            if (
                emptyDepartureRoute &&
                !emptyDepartureRoutes.includes(emptyDepartureRoute)
            ) {
                emptyDepartureRoute = '';
            }

            if (!emptyDepartureRoute && emptyDepartureRoutes.length > 0) {
                emptyDepartureRoute = emptyDepartureRoutes[0];
            }

            await loadEmptyDepartureSchedules();
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memuat rute jadwal.';
            formSuccess = '';
        } finally {
            loadingEmptyDepartureRoutes = false;
        }
    };

    const openEmptyDepartureForm = async () => {
        emptyDepartureOpen = !emptyDepartureOpen;

        if (!emptyDepartureOpen) {
            return;
        }

        emptyDepartureDate = bookingListDateFrom || today;
        emptyDepartureRoute =
            bookingListRoute !== 'all' ? bookingListRoute : '';
        emptyDepartureJam = '';
        emptyDepartureUnit = 1;
        formError = '';
        formSuccess = '';
        await loadEmptyDepartureRoutes();
    };

    const onEmptyDepartureDateChange = async () => {
        emptyDepartureRoute = '';
        await loadEmptyDepartureRoutes();
    };

    const onEmptyDepartureRouteChange = async () => {
        await loadEmptyDepartureSchedules();
    };

    const saveEmptyDeparture = async () => {
        if (savingEmptyDeparture) {
            return;
        }

        if (!emptyDepartureDate || !emptyDepartureRoute || !emptyDepartureJam) {
            formError =
                'Lengkapi tanggal, rute, dan jam jadwal terlebih dahulu.';
            formSuccess = '';

            return;
        }

        savingEmptyDeparture = true;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/empty-departure', {
                        rute: emptyDepartureRoute,
                        tanggal: emptyDepartureDate,
                        jam: normalizeJamToken(emptyDepartureJam),
                        unit: Number(emptyDepartureUnit) || 1,
                    });
                },
                {
                    loadingMessage: 'Membuat jadwal tanpa penumpang...',
                    successMessage: 'Jadwal tanpa penumpang berhasil dibuat.',
                    errorMessage: 'Gagal membuat jadwal tanpa penumpang.',
                },
            );

            emptyDepartureOpen = false;
            formSuccess = 'Jadwal tanpa penumpang berhasil dibuat.';
            reloadBookingListData();
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal membuat jadwal tanpa penumpang.';
        } finally {
            savingEmptyDeparture = false;
        }
    };

    const cancelDeparture = async (group: BookingGroup) => {
        if (
            !group ||
            isCanceledDeparture(group) ||
            isDepartedDeparture(group) ||
            isArrivedDeparture(group) ||
            cancelingDepartureKey
        ) {
            return;
        }

        cancelingDepartureKey = group.key;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/cancel-departure', {
                        rute: group.rute,
                        tanggal: group.tanggal,
                        jam: normalizeJamToken(group.jam),
                        unit: Number(group.unit) || 1,
                    });
                },
                {
                    loadingMessage: `Membatalkan jadwal ${group.rute} ${formatGroupTimeLabel(group.jam)}...`,
                    successMessage:
                        'Jadwal berhasil dibatalkan. Driver, nopol, dan BOP dikosongkan.',
                    errorMessage: 'Gagal membatalkan jadwal.',
                },
            );

            localBookingGroups = localBookingGroups.map((item) =>
                item.key === group.key
                    ? {
                          ...item,
                          departure_status: 'canceled',
                          departure_can_arrive: false,
                          driver_name: '-',
                          armada_nopol: '-',
                          bop: 0,
                      }
                    : item,
            );

            if (openGroupDetail?.key === group.key) {
                openGroupDetail = {
                    ...openGroupDetail,
                    departure_status: 'canceled',
                    departure_can_arrive: false,
                    driver_name: '-',
                    armada_nopol: '-',
                    bop: 0,
                };
                groupDriverSelectedId = '0';
                groupDriverName = '-';
                groupDriverSearch = '';
                groupArmadaSelectedId = '0';
                groupArmadaNopol = '-';
                groupArmadaSearch = '';
            }

            formSuccess =
                'Jadwal berhasil dibatalkan. Driver, nopol, dan BOP dikosongkan.';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal membatalkan jadwal.';
        } finally {
            cancelingDepartureKey = '';
        }
    };

    const markDepartureDeparted = async (group: BookingGroup) => {
        if (!canMarkDepartureDeparted(group) || cancelingDepartureKey) {
            return;
        }

        cancelingDepartureKey = group.key;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/depart-departure', {
                        rute: group.rute,
                        tanggal: group.tanggal,
                        jam: normalizeJamToken(group.jam),
                        unit: Number(group.unit) || 1,
                    });
                },
                {
                    loadingMessage: `Menandai armada berangkat untuk ${group.rute} ${formatGroupTimeLabel(group.jam)}...`,
                    successMessage: 'Armada berhasil ditandai sudah berangkat.',
                    errorMessage: 'Gagal menandai armada sudah berangkat.',
                },
            );

            localBookingGroups = localBookingGroups.map((item) =>
                item.key === group.key
                    ? {
                          ...item,
                          departure_status: 'departed',
                          departure_can_arrive: true,
                      }
                    : item,
            );

            if (openGroupDetail?.key === group.key) {
                openGroupDetail = {
                    ...openGroupDetail,
                    departure_status: 'departed',
                    departure_can_arrive: true,
                };
            }

            formSuccess = 'Armada berhasil ditandai sudah berangkat.';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal menandai armada sudah berangkat.';
        } finally {
            cancelingDepartureKey = '';
        }
    };

    const markDepartureArrived = async (group: BookingGroup) => {
        if (!canMarkDepartureArrived(group) || cancelingDepartureKey) {
            return;
        }

        cancelingDepartureKey = group.key;
        formError = '';
        formSuccess = '';

        try {
            const response = await runWithFeedback(
                async () => {
                    return apiPost('/api/bookings/arrive-departure', {
                        rute: group.rute,
                        tanggal: group.tanggal,
                        jam: normalizeJamToken(group.jam),
                        unit: Number(group.unit) || 1,
                    });
                },
                {
                    loadingMessage: `Menandai armada tiba untuk ${group.rute} ${formatGroupTimeLabel(group.jam)}...`,
                    successMessage:
                        'Armada berhasil ditandai sudah tiba dan manifest otomatis ditutup.',
                    errorMessage: 'Gagal menandai armada sudah tiba.',
                },
            );
            const arrivedLuggageCount =
                Number(response?.luggage_arrived_count ?? 0) || 0;
            const nextDepartureStatus = String(response?.status ?? 'closed')
                .trim()
                .toLowerCase() || 'closed';

            localBookingGroups = localBookingGroups.map((item) =>
                item.key === group.key
                    ? {
                          ...item,
                          departure_status: nextDepartureStatus,
                          departure_can_arrive: false,
                      }
                    : item,
            );

            if (openGroupDetail?.key === group.key) {
                const updatedGroup = {
                    ...openGroupDetail,
                    departure_status: nextDepartureStatus,
                    departure_can_arrive: false,
                };
                openGroupDetail = updatedGroup;
                groupMappedRiturs = groupMappedRiturs.map((row) =>
                    String(row.status || '')
                        .trim()
                        .toLowerCase() === 'canceled'
                        ? row
                        : { ...row, status: luggageArrivedStatus },
                );
                await loadGroupRiturs(updatedGroup, groupRiturSearch);
            }

            formSuccess =
                arrivedLuggageCount > 0
                    ? `Armada berhasil ditandai sudah tiba dan manifest otomatis ditutup. ${arrivedLuggageCount} bagasi ikut ditandai ${luggageArrivedStatus}.`
                    : 'Armada berhasil ditandai sudah tiba dan manifest otomatis ditutup.';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal menandai armada sudah tiba.';
        } finally {
            cancelingDepartureKey = '';
        }
    };

    const closeManifest = async (group: BookingGroup) => {
        if (!canCloseManifest(group) || cancelingDepartureKey) {
            return;
        }

        cancelingDepartureKey = group.key;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/close-manifest', {
                        rute: group.rute,
                        tanggal: group.tanggal,
                        jam: normalizeJamToken(group.jam),
                        unit: Number(group.unit) || 1,
                    });
                },
                {
                    loadingMessage: `Menutup manifest untuk ${group.rute} ${formatGroupTimeLabel(group.jam)}...`,
                    successMessage: 'Manifest berhasil ditutup.',
                    errorMessage: 'Gagal menutup manifest.',
                },
            );

            localBookingGroups = localBookingGroups.map((item) =>
                item.key === group.key
                    ? {
                          ...item,
                          departure_status: 'closed',
                          departure_can_arrive: false,
                      }
                    : item,
            );

            if (openGroupDetail?.key === group.key) {
                openGroupDetail = {
                    ...openGroupDetail,
                    departure_status: 'closed',
                    departure_can_arrive: false,
                };
            }

            formSuccess = 'Manifest berhasil ditutup.';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal menutup manifest.';
        } finally {
            cancelingDepartureKey = '';
        }
    };

    const recalculateGroupSummary = (group: BookingGroup): BookingGroup => {
        const visibleRows = visibleGroupBookingRows(group.bookings);
        const total = visibleRows.length;
        const active = visibleRows.filter(
            (row) => String(row.status || '').toLowerCase() !== 'canceled',
        ).length;
        const canceled = total - active;
        const lunas = visibleRows.filter((row) =>
            isLunasPayment(row.pembayaran),
        ).length;
        const refund = visibleRows.filter((row) =>
            isRefundPayment(row.pembayaran),
        ).length;
        const belumLunas = visibleRows.filter(
            (row) => !isSettledPayment(row.pembayaran),
        ).length;

        return {
            ...group,
            total,
            active,
            canceled,
            lunas,
            refund,
            belum_lunas: belumLunas,
        };
    };

    const updateBookingRowInLocalGroups = (
        bookingId: number,
        patch: Partial<BookingGroup['bookings'][number]>,
    ) => {
        if (bookingId <= 0) {
            return;
        }

        localBookingGroups = localBookingGroups.map((group) => {
            let touched = false;
            const nextRows = group.bookings.map((row) => {
                if (row.id !== bookingId) {
                    return row;
                }

                touched = true;

                return { ...row, ...patch };
            });

            if (!touched) {
                return group;
            }

            return recalculateGroupSummary({
                ...group,
                bookings: nextRows,
            });
        });

        if (openGroupDetail) {
            const refreshedGroup = localBookingGroups.find(
                (group) => group.key === openGroupDetail!.key,
            );

            if (refreshedGroup) {
                openGroupDetail = refreshedGroup;
            }
        }
    };

    const sortPassengerRowsBySeat = (rows: BookingGroup['bookings']) =>
        [...rows].sort((a, b) =>
            compareSeatTokens(String(a.seat || ''), String(b.seat || '')),
        );

    const moveBookingRowToAnotherGroup = (
        bookingId: number,
        targetTrip: {
            rute: string;
            tanggal: string;
            jam: string;
            unit: number;
            departure_code?: string;
        },
        patch: Partial<BookingGroup['bookings'][number]> = {},
    ) => {
        if (bookingId <= 0) {
            return;
        }

        let movedRow: BookingGroup['bookings'][number] | null = null;
        let sourceGroupKey = '';

        localBookingGroups = localBookingGroups
            .map((group) => {
                const existingRow = group.bookings.find(
                    (row) => row.id === bookingId,
                );

                if (!existingRow) {
                    return group;
                }

                movedRow = { ...existingRow, ...patch };
                sourceGroupKey = group.key;

                return recalculateGroupSummary({
                    ...group,
                    bookings: group.bookings.filter(
                        (row) => row.id !== bookingId,
                    ),
                });
            })
            .filter(
                (group) =>
                    group.bookings.length > 0 ||
                    group.key === openGroupDetail?.key,
            );

        if (!movedRow) {
            return;
        }

        localBookingGroups = localBookingGroups.map((group) => {
            const isTargetGroup =
                group.rute === targetTrip.rute &&
                group.tanggal === targetTrip.tanggal &&
                normalizeJamToken(group.jam) ===
                    normalizeJamToken(targetTrip.jam) &&
                Number(group.unit || 1) === Number(targetTrip.unit || 1);

            if (!isTargetGroup || group.key === sourceGroupKey) {
                return group;
            }

            return recalculateGroupSummary({
                ...group,
                departure_code:
                    targetTrip.departure_code || group.departure_code,
                bookings: sortPassengerRowsBySeat([
                    ...group.bookings,
                    movedRow!,
                ]),
            });
        });

        if (openGroupDetail) {
            const refreshedGroup = localBookingGroups.find(
                (group) => group.key === openGroupDetail!.key,
            );

            if (refreshedGroup) {
                openGroupDetail = refreshedGroup;
            }
        }
    };

    const markBookingRowAsPaid = async (
        bookingId: number,
        seatToken: string,
        currentPayment: string,
    ) => {
        if (!bookingId || markingPaidSeatId !== null) {
            return;
        }

        if (isManifestLocked(openGroupDetail)) {
            formError = 'Manifest sudah ditutup. Status pembayaran tidak bisa diubah lagi.';
            formSuccess = '';

            return;
        }

        if (isRefundPayment(currentPayment)) {
            formSuccess = 'Pembayaran sudah berstatus Refund.';
            formError = '';

            return;
        }

        if (isLunasPayment(currentPayment)) {
            formSuccess = 'Pembayaran sudah berstatus Lunas.';
            formError = '';

            return;
        }

        markingPaidSeatId = bookingId;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/update', {
                        booking_id: bookingId,
                        pembayaran: 'Lunas',
                    });

                    updateBookingRowInLocalGroups(bookingId, {
                        pembayaran: 'Lunas',
                    });
                    localLatestBookings = localLatestBookings.map((row) =>
                        row.id === bookingId
                            ? { ...row, pembayaran: 'Lunas' }
                            : row,
                    );
                },
                {
                    loadingMessage: `Memperbarui status pembayaran kursi ${seatToken}...`,
                    successMessage: `Pembayaran kursi ${seatToken} berhasil diubah ke Lunas.`,
                    errorMessage: 'Gagal mengubah status pembayaran.',
                },
            );
            formSuccess = `Pembayaran kursi ${seatToken} berhasil diubah ke Lunas.`;
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal mengubah status pembayaran.';
        } finally {
            markingPaidSeatId = null;
        }
    };

    const markBookingRowAsRefund = async (
        bookingId: number,
        seatToken: string,
        currentPayment: string,
    ) => {
        if (!bookingId || markingPaidSeatId !== null) {
            return;
        }

        if (isManifestLocked(openGroupDetail)) {
            formError = 'Manifest sudah ditutup. Status pembayaran tidak bisa diubah lagi.';
            formSuccess = '';

            return;
        }

        if (isRefundPayment(currentPayment)) {
            formSuccess = 'Pembayaran sudah berstatus Refund.';
            formError = '';

            return;
        }

        if (isBelumLunasPayment(currentPayment)) {
            formError =
                'Booking yang belum dibayar tidak bisa ditandai refund.';
            formSuccess = '';

            return;
        }

        markingPaidSeatId = bookingId;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/update', {
                        booking_id: bookingId,
                        pembayaran: 'Refund',
                    });

                    updateBookingRowInLocalGroups(bookingId, {
                        pembayaran: 'Refund',
                    });
                    localLatestBookings = localLatestBookings.map((row) =>
                        row.id === bookingId
                            ? { ...row, pembayaran: 'Refund' }
                            : row,
                    );
                },
                {
                    loadingMessage: `Memproses refund kursi ${seatToken}...`,
                    successMessage: `Pembayaran kursi ${seatToken} berhasil diubah ke Refund.`,
                    errorMessage: 'Gagal mengubah status pembayaran ke Refund.',
                },
            );
            formSuccess = `Pembayaran kursi ${seatToken} berhasil diubah ke Refund.`;
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal mengubah status pembayaran ke Refund.';
        } finally {
            markingPaidSeatId = null;
        }
    };

    const markBookingGroupAsPaid = async (group: BookingGroup) => {
        if (markingPaidSeatId !== null) {
            return;
        }

        if (isManifestLocked(group)) {
            formError = 'Manifest sudah ditutup. Status pembayaran tidak bisa diubah lagi.';
            formSuccess = '';

            return;
        }

        const payableRows = payableBookingRows(group);

        if (payableRows.length === 0) {
            formSuccess =
                'Tidak ada pembayaran yang perlu dilunaskan pada keberangkatan ini.';
            formError = '';

            return;
        }

        markingPaidSeatId = -group.bookings[0]?.id || -1;
        formError = '';
        formSuccess = '';

        try {
            const bookingIds = payableRows.map((row) => row.id);
            const response = await runWithFeedback(
                async () => {
                    return apiPost('/api/bookings/bulk-payment', {
                        booking_ids: bookingIds,
                        pembayaran: 'Lunas',
                    });
                },
                {
                    loadingMessage: `Memproses ${payableRows.length} pembayaran keberangkatan...`,
                    successMessage: `Pembayaran ${payableRows.length} penumpang berhasil diubah ke Lunas.`,
                    errorMessage: 'Gagal mengubah seluruh status pembayaran.',
                },
            );
            const updatedCount =
                Number(response?.updated_count ?? bookingIds.length) ||
                bookingIds.length;

            const paidIds = new Set(bookingIds);

            localBookingGroups = localBookingGroups.map((item) => {
                if (item.key !== group.key) {
                    return item;
                }

                return recalculateGroupSummary({
                    ...item,
                    bookings: item.bookings.map((row) =>
                        paidIds.has(row.id)
                            ? { ...row, pembayaran: 'Lunas' }
                            : row,
                    ),
                });
            });

            if (openGroupDetail?.key === group.key) {
                const refreshedGroup = localBookingGroups.find(
                    (item) => item.key === group.key,
                );

                if (refreshedGroup) {
                    openGroupDetail = refreshedGroup;
                }
            }

            localLatestBookings = localLatestBookings.map((row) =>
                paidIds.has(row.id) ? { ...row, pembayaran: 'Lunas' } : row,
            );
            markDataStale(['bookings', 'payments', 'flows', 'dashboard']);

            formSuccess = `Pembayaran ${updatedCount} penumpang berhasil diubah ke Lunas.`;
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal mengubah seluruh status pembayaran.';
        } finally {
            markingPaidSeatId = null;
        }
    };

    const cancelBookingRow = async (
        bookingId: number,
        seatToken: string,
        currentStatus: string,
    ) => {
        if (!bookingId || cancelingSeatId !== null) {
            return;
        }

        if (isManifestLocked(openGroupDetail)) {
            formError = 'Manifest sudah ditutup. Booking tidak bisa dibatalkan lagi.';
            formSuccess = '';

            return;
        }

        if (isReadonlyHistoryBooking(bookingId)) {
            formError = 'Data history tidak bisa dibatalkan.';
            formSuccess = '';

            return;
        }

        if (String(currentStatus || '').toLowerCase() === 'canceled') {
            formSuccess = 'Booking sudah berstatus canceled.';
            formError = '';

            return;
        }

        cancelingSeatId = bookingId;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/cancel', {
                        booking_id: bookingId,
                        reason: 'Canceled from bookings list detail page',
                    });

                    updateBookingRowInLocalGroups(bookingId, {
                        status: 'canceled',
                    });
                    localLatestBookings = localLatestBookings.map((row) =>
                        row.id === bookingId
                            ? { ...row, status: 'canceled' }
                            : row,
                    );
                },
                {
                    loadingMessage: `Membatalkan booking kursi ${seatToken}...`,
                    successMessage: `Booking kursi ${seatToken} berhasil dibatalkan.`,
                    errorMessage: 'Gagal membatalkan booking.',
                },
            );
            formSuccess = `Booking kursi ${seatToken} berhasil dibatalkan.`;
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal membatalkan booking.';
        } finally {
            cancelingSeatId = null;
        }
    };

    const openGroupRowEdit = async (
        group: BookingGroup,
        row: BookingGroup['bookings'][number],
    ) => {
        if (isReadonlyHistoryGroup(group)) {
            formError = 'Data history tidak bisa diedit.';
            formSuccess = '';

            return;
        }

        const currentSeat = normalizeSeatToken(row.seat || '');
        const targetUnit = Number(group.unit) || 1;
        const fallbackBookedSeats = new Set(
            group.bookings
                .filter(
                    (item) =>
                        item.id !== row.id &&
                        String(item.status || '').toLowerCase() !== 'canceled',
                )
                .map((item) => normalizeSeatToken(item.seat || ''))
                .filter((seat) => seat !== ''),
        );
        groupEditBookedSeatTokens = [];

        groupEditBookingId = row.id;
        groupEditSeat = currentSeat;
        groupEditCurrentSeat = currentSeat;
        groupEditName = normalizeNameForBooking(row.name || '');
        groupEditPhone = normalizePhoneForBooking(row.phone || '');
        groupEditPickupPoint = String(row.pickup_point || '');
        groupEditPayment = String(row.pembayaran || 'Belum Lunas');
        groupEditDiscount = Number(row.discount || 0);
        groupEditRoute = group.rute;
        groupEditDate = group.tanggal;
        groupEditJam = group.jam;
        groupEditUnit = targetUnit;
        groupEditSeatOptions = currentSeat !== '' ? [currentSeat] : [];
        groupEditLayoutSeatTokens = [];
        groupEditLayoutRows = [];
        groupEditSeatWarning = '';
        loadingGroupEditSeats = true;
        groupEditModalOpen = true;
        formError = '';
        void ensureGroupEditModalLoaded();

        try {
            const seatOptionsJson = await apiGet(
                '/api/bookings/edit-seat-options',
                {
                    rute: group.rute,
                    tanggal: group.tanggal,
                    jam: normalizeJamToken(group.jam),
                    unit: targetUnit,
                    booking_id: row.id,
                    current_seat: currentSeat,
                },
            );
            const backendLayout = Array.isArray(seatOptionsJson.layout)
                ? seatOptionsJson.layout
                : [];
            const backendSeatTokens: string[] = toSeatTokenList(
                seatOptionsJson.layout_seats,
            );
            const backendBookedSeats: string[] = toSeatTokenList(
                seatOptionsJson.booked_seats,
            );
            const backendAvailableSeats: string[] = toSeatTokenList(
                seatOptionsJson.available_seats,
            );
            const totalSeats = Math.max(
                Number(seatOptionsJson.total_seats ?? 0),
                backendSeatTokens.length,
            );
            groupEditSeatWarning = String(
                seatOptionsJson.layout_warning ?? '',
            ).trim();

            groupEditBookedSeatTokens =
                uniqueSortedSeatTokens(backendBookedSeats);
            groupEditLayoutSeatTokens =
                uniqueSortedSeatTokens(backendSeatTokens);
            groupEditLayoutRows = resolveLayoutRows(backendLayout, totalSeats);

            let availableSeats = uniqueSortedSeatTokens(backendAvailableSeats);

            if (availableSeats.length === 0 && currentSeat !== '') {
                availableSeats = [currentSeat];
            }

            groupEditSeatOptions = availableSeats;

            if (
                !groupEditSeatOptions.includes(groupEditSeat) &&
                groupEditSeatOptions.length > 0
            ) {
                groupEditSeat = groupEditSeatOptions[0];
            }
        } catch {
            try {
                const scheduleJson = await apiGet('/api/bookings/schedules', {
                    rute: group.rute,
                    tanggal: group.tanggal,
                });
                const scheduleRows = Array.isArray(scheduleJson.schedules)
                    ? scheduleJson.schedules
                    : [];
                const targetJam = normalizeJamToken(group.jam);
                const matchedSchedule = (scheduleRows.find(
                    (item: Record<string, unknown>) => {
                        const rowJam = normalizeJamToken(
                            String(item.jam ?? ''),
                        );
                        const unitOptions = Array.isArray(item.unit_options)
                            ? item.unit_options
                            : [];

                        return (
                            rowJam === targetJam &&
                            unitOptions.some(
                                (option) =>
                                    Number(
                                        (option as Record<string, unknown>)
                                            .unit_no ?? 0,
                                    ) === targetUnit,
                            )
                        );
                    },
                ) ??
                    scheduleRows.find((item: Record<string, unknown>) => {
                        const rowJam = normalizeJamToken(
                            String(item.jam ?? ''),
                        );
                        const rowUnits = Number(item.units ?? 0);

                        return (
                            rowJam === targetJam &&
                            (rowUnits <= 0 || targetUnit <= rowUnits)
                        );
                    }) ??
                    scheduleRows.find(
                        (item: Record<string, unknown>) =>
                            normalizeJamToken(String(item.jam ?? '')) ===
                            targetJam,
                    )) as Record<string, unknown> | undefined;

                const fallbackSeatCount = Number(matchedSchedule?.seats ?? 0);
                const layoutRows = resolveLayoutRows(
                    matchedSchedule?.layout,
                    fallbackSeatCount,
                );
                const seatTokens = extractSeatTokensFromLayout(
                    matchedSchedule?.layout,
                    fallbackSeatCount,
                );
                groupEditBookedSeatTokens =
                    Array.from(fallbackBookedSeats).sort(compareSeatTokens);
                groupEditLayoutSeatTokens = seatTokens;
                groupEditLayoutRows = layoutRows;
                groupEditSeatWarning =
                    seatTokens.length === 0
                        ? 'Layout keberangkatan belum ditemukan pada Jadwal untuk jam ini.'
                        : '';

                let availableSeats = seatTokens.filter(
                    (seat) =>
                        seat === currentSeat || !fallbackBookedSeats.has(seat),
                );

                if (availableSeats.length === 0 && currentSeat !== '') {
                    availableSeats = [currentSeat];
                }

                groupEditSeatOptions = Array.from(new Set(availableSeats)).sort(
                    compareSeatTokens,
                );

                if (
                    !groupEditSeatOptions.includes(groupEditSeat) &&
                    groupEditSeatOptions.length > 0
                ) {
                    groupEditSeat = groupEditSeatOptions[0];
                }
            } catch {
                groupEditBookedSeatTokens =
                    Array.from(fallbackBookedSeats).sort(compareSeatTokens);
                groupEditSeatOptions = currentSeat !== '' ? [currentSeat] : [];
                groupEditLayoutSeatTokens =
                    currentSeat !== '' ? [currentSeat] : [];
                groupEditLayoutRows =
                    currentSeat !== ''
                        ? [[{ kind: 'seat', seat: currentSeat }]]
                        : [];
                groupEditSeatWarning =
                    'Layout keberangkatan belum tersedia. Saat ini hanya kursi aktif penumpang yang bisa dipertahankan.';
            }
        } finally {
            loadingGroupEditSeats = false;
        }
    };

    const closeGroupRowEdit = () => {
        if (savingGroupRowEdit) {
            return;
        }

        groupEditModalOpen = false;
        groupEditBookingId = null;
        groupEditCurrentSeat = '';
        groupEditSeatOptions = [];
        groupEditBookedSeatTokens = [];
        groupEditLayoutSeatTokens = [];
        groupEditLayoutRows = [];
        groupEditSeatWarning = '';
        loadingGroupEditSeats = false;
    };

    const closeGroupRescheduleModal = () => {
        if (savingGroupReschedule) {
            return;
        }

        groupRescheduleModalOpen = false;
        groupRescheduleBookingId = null;
        groupRescheduleBookingName = '';
        groupRescheduleRoute = '';
        groupRescheduleCurrentDate = '';
        groupRescheduleCurrentJam = '';
        groupRescheduleCurrentUnit = 1;
        groupRescheduleCurrentSeat = '';
        groupRescheduleDate = '';
        groupRescheduleJam = '';
        groupRescheduleUnit = 1;
        groupRescheduleSeat = '';
        groupRescheduleSchedules = [];
        groupRescheduleSeatOptions = [];
        groupRescheduleBookedSeatTokens = [];
        groupRescheduleLayoutSeatTokens = [];
        groupRescheduleLayoutRows = [];
        groupRescheduleSeatWarning = '';
        loadingGroupRescheduleSchedules = false;
        loadingGroupRescheduleSeats = false;
    };

    const loadGroupRescheduleSeatOptions = async () => {
        if (
            !groupRescheduleRoute ||
            !groupRescheduleDate ||
            !groupRescheduleJam ||
            !groupRescheduleBookingId
        ) {
            groupRescheduleSeatOptions = [];
            groupRescheduleBookedSeatTokens = [];
            groupRescheduleLayoutSeatTokens = [];
            groupRescheduleLayoutRows = [];
            groupRescheduleSeatWarning = '';

            return;
        }

        loadingGroupRescheduleSeats = true;
        groupRescheduleSeatWarning = '';

        try {
            const sameTrip =
                groupRescheduleDate === groupRescheduleCurrentDate &&
                normalizeJamToken(groupRescheduleJam) ===
                    normalizeJamToken(groupRescheduleCurrentJam) &&
                Number(groupRescheduleUnit || 1) ===
                    Number(groupRescheduleCurrentUnit || 1);
            const seatOptionsJson = await apiGet(
                '/api/bookings/edit-seat-options',
                {
                    rute: groupRescheduleRoute,
                    tanggal: groupRescheduleDate,
                    jam: normalizeJamToken(groupRescheduleJam),
                    unit: Number(groupRescheduleUnit) || 1,
                    booking_id: groupRescheduleBookingId,
                    current_seat: sameTrip ? groupRescheduleCurrentSeat : '',
                },
            );

            const backendSeatTokens: string[] = toSeatTokenList(
                seatOptionsJson.layout_seats,
            );
            const backendBookedSeats: string[] = toSeatTokenList(
                seatOptionsJson.booked_seats,
            );
            const backendAvailableSeats: string[] = toSeatTokenList(
                seatOptionsJson.available_seats,
            );
            const backendLayout = Array.isArray(seatOptionsJson.layout)
                ? seatOptionsJson.layout
                : [];
            const totalSeats = Math.max(
                Number(seatOptionsJson.total_seats ?? 0),
                backendSeatTokens.length,
            );

            groupRescheduleSeatWarning = String(
                seatOptionsJson.layout_warning ?? '',
            ).trim();
            groupRescheduleBookedSeatTokens =
                uniqueSortedSeatTokens(backendBookedSeats);
            groupRescheduleLayoutSeatTokens =
                uniqueSortedSeatTokens(backendSeatTokens);
            groupRescheduleLayoutRows = resolveLayoutRows(
                backendLayout,
                totalSeats,
            );
            groupRescheduleSeatOptions = uniqueSortedSeatTokens(
                backendAvailableSeats,
            );

            if (
                !groupRescheduleSeatOptions.includes(groupRescheduleSeat) &&
                groupRescheduleSeatOptions.length > 0
            ) {
                groupRescheduleSeat = groupRescheduleSeatOptions[0];
            }
        } catch (error) {
            groupRescheduleSeatOptions = [];
            groupRescheduleBookedSeatTokens = [];
            groupRescheduleLayoutSeatTokens = [];
            groupRescheduleLayoutRows = [];
            groupRescheduleSeatWarning =
                error instanceof Error
                    ? error.message
                    : 'Gagal memuat seat tujuan.';
        } finally {
            loadingGroupRescheduleSeats = false;
        }
    };

    const loadGroupRescheduleSchedules = async () => {
        if (!groupRescheduleRoute || !groupRescheduleDate) {
            groupRescheduleSchedules = [];
            groupRescheduleJam = '';
            groupRescheduleUnit = 1;
            groupRescheduleSeatOptions = [];

            return;
        }

        loadingGroupRescheduleSchedules = true;
        groupRescheduleSeatWarning = '';

        try {
            const json = await apiGet('/api/bookings/schedules', {
                rute: groupRescheduleRoute,
                tanggal: groupRescheduleDate,
            });
            groupRescheduleSchedules = Array.isArray(json.schedules)
                ? json.schedules
                : [];

            if (groupRescheduleSchedules.length === 0) {
                groupRescheduleJam = '';
                groupRescheduleUnit = 1;
                groupRescheduleSeatOptions = [];
                groupRescheduleBookedSeatTokens = [];
                groupRescheduleLayoutSeatTokens = [];
                groupRescheduleLayoutRows = [];
                groupRescheduleSeatWarning =
                    'Belum ada jadwal keberangkatan pada tanggal yang dipilih.';

                return;
            }

            const matchedSchedule =
                groupRescheduleSchedules.find(
                    (item) => item.jam === groupRescheduleCurrentJam,
                ) ?? groupRescheduleSchedules[0];
            groupRescheduleJam = String(matchedSchedule?.jam ?? '');

            const targetUnitOptions = (() => {
                const options = Array.isArray(matchedSchedule?.unit_options)
                    ? matchedSchedule.unit_options
                    : [];

                if (options.length > 0) {
                    return options.map((option, idx) =>
                        Math.max(1, Number(option.unit_no || idx + 1)),
                    );
                }

                const units = Math.max(1, Number(matchedSchedule?.units ?? 1));

                return Array.from({ length: units }, (_, index) => index + 1);
            })();

            groupRescheduleUnit = targetUnitOptions.includes(
                groupRescheduleCurrentUnit,
            )
                ? groupRescheduleCurrentUnit
                : (targetUnitOptions[0] ?? 1);

            await loadGroupRescheduleSeatOptions();
        } catch (error) {
            groupRescheduleSchedules = [];
            groupRescheduleJam = '';
            groupRescheduleUnit = 1;
            groupRescheduleSeatOptions = [];
            groupRescheduleSeatWarning =
                error instanceof Error
                    ? error.message
                    : 'Gagal memuat jadwal tujuan.';
        } finally {
            loadingGroupRescheduleSchedules = false;
        }
    };

    const onGroupRescheduleDateChange = async () => {
        await loadGroupRescheduleSchedules();
    };

    const onGroupRescheduleScheduleChange = async () => {
        const schedule = groupRescheduleActiveSchedule();
        const availableUnits = (() => {
            const options = Array.isArray(schedule?.unit_options)
                ? schedule.unit_options
                : [];

            if (options.length > 0) {
                return options.map((option, idx) =>
                    Math.max(1, Number(option.unit_no || idx + 1)),
                );
            }

            const units = Math.max(1, Number(schedule?.units ?? 1));

            return Array.from({ length: units }, (_, index) => index + 1);
        })();

        if (!availableUnits.includes(groupRescheduleUnit)) {
            groupRescheduleUnit = availableUnits[0] ?? 1;
        }

        await loadGroupRescheduleSeatOptions();
    };

    const openGroupRowReschedule = async (
        group: BookingGroup,
        row: BookingGroup['bookings'][number],
    ) => {
        if (isReadonlyHistoryGroup(group)) {
            formError = 'Data history tidak bisa di-reschedule.';
            formSuccess = '';

            return;
        }

        groupRescheduleBookingId = row.id;
        groupRescheduleBookingName = String(row.name || '');
        groupRescheduleRoute = group.rute;
        groupRescheduleCurrentDate = group.tanggal;
        groupRescheduleCurrentJam = normalizeJamToken(group.jam);
        groupRescheduleCurrentUnit = Number(group.unit) || 1;
        groupRescheduleCurrentSeat = normalizeSeatToken(row.seat || '');
        groupRescheduleDate = group.tanggal;
        groupRescheduleJam = normalizeJamToken(group.jam);
        groupRescheduleUnit = Number(group.unit) || 1;
        groupRescheduleSeat = normalizeSeatToken(row.seat || '');
        groupRescheduleSchedules = [];
        groupRescheduleSeatOptions = [];
        groupRescheduleBookedSeatTokens = [];
        groupRescheduleLayoutSeatTokens = [];
        groupRescheduleLayoutRows = [];
        groupRescheduleSeatWarning = '';
        groupRescheduleModalOpen = true;
        void ensureGroupRescheduleModalLoaded();

        await loadGroupRescheduleSchedules();
    };

    const saveGroupRowEdit = async () => {
        if (!groupEditModalOpen || !groupEditBookingId || savingGroupRowEdit) {
            return;
        }

        if (isManifestLocked(openGroupDetail)) {
            formError = 'Manifest sudah ditutup. Data penumpang tidak bisa diubah lagi.';
            formSuccess = '';

            return;
        }

        const seat = normalizeSeatToken(groupEditSeat);
        const name = normalizeNameForBooking(groupEditName);
        const phone = normalizePhoneForBooking(groupEditPhone);
        const pickupPoint = String(groupEditPickupPoint || '').trim();
        const pembayaran = String(groupEditPayment || 'Belum Lunas').trim();
        const discount = parseCurrencyInput(groupEditDiscount);

        if (seat === '' || name === '' || phone === '' || pickupPoint === '') {
            formError = 'Seat, nama, telepon, dan jemput wajib diisi.';

            return;
        }

        if (phone.length > 13) {
            formError = 'Nomor HP maksimal 13 digit.';

            return;
        }

        savingGroupRowEdit = true;
        formError = '';

        try {
            await apiPost('/api/bookings/update', {
                booking_id: groupEditBookingId,
                seat,
                name,
                phone,
                pickup_point: pickupPoint,
                pembayaran,
                discount,
            });

            updateBookingRowInLocalGroups(groupEditBookingId, {
                seat,
                name,
                phone,
                pickup_point: pickupPoint,
                pembayaran,
                discount,
            });

            localLatestBookings = localLatestBookings.map((row) =>
                row.id === groupEditBookingId
                    ? { ...row, seat, name, phone, pembayaran }
                    : row,
            );

            formSuccess = `Data kursi ${seat} berhasil diperbarui.`;
            groupEditModalOpen = false;
            groupEditBookingId = null;
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memperbarui booking.';
        } finally {
            savingGroupRowEdit = false;
        }
    };

    const saveGroupReschedule = async () => {
        if (
            !groupRescheduleModalOpen ||
            !groupRescheduleBookingId ||
            savingGroupReschedule
        ) {
            return;
        }

        const seat = normalizeSeatToken(groupRescheduleSeat);
        const jam = normalizeJamToken(groupRescheduleJam);
        const unit = Math.max(1, Number(groupRescheduleUnit) || 1);

        if (!groupRescheduleDate || !jam || seat === '') {
            formError = 'Tanggal, jam, unit, dan seat tujuan wajib dipilih.';

            return;
        }

        if (
            groupRescheduleSeatOptions.length > 0 &&
            !groupRescheduleSeatOptions.includes(seat)
        ) {
            formError =
                'Seat tujuan tidak tersedia. Pilih dari daftar seat kosong.';

            return;
        }

        savingGroupReschedule = true;
        formError = '';

        try {
            const response = await apiPost('/api/bookings/update', {
                booking_id: groupRescheduleBookingId,
                tanggal: groupRescheduleDate,
                jam,
                unit,
                seat,
            });

            moveBookingRowToAnotherGroup(
                groupRescheduleBookingId,
                {
                    rute: String(response.rute ?? groupRescheduleRoute),
                    tanggal: String(response.tanggal ?? groupRescheduleDate),
                    jam: String(response.jam ?? jam),
                    unit: Number(response.unit ?? unit) || unit,
                    departure_code: String(response.departure_code ?? ''),
                },
                {
                    seat,
                },
            );

            localLatestBookings = localLatestBookings.map((row) =>
                row.id === groupRescheduleBookingId
                    ? {
                          ...row,
                          rute: String(response.rute ?? groupRescheduleRoute),
                          tanggal: String(
                              response.tanggal ?? groupRescheduleDate,
                          ),
                          jam: String(response.jam ?? jam),
                          unit: Number(response.unit ?? unit) || unit,
                          seat,
                          departure_code: String(
                              response.departure_code ??
                                  row.departure_code ??
                                  '',
                          ),
                      }
                    : row,
            );

            savingGroupReschedule = false;
            formSuccess = `Penumpang ${groupRescheduleBookingName || ''} berhasil di-reschedule ke ${formatGroupDateLabel(String(response.tanggal ?? groupRescheduleDate))} • ${String(response.jam ?? jam)} • Unit ${Number(response.unit ?? unit) || unit} • Seat ${seat}.`;
            closeGroupRescheduleModal();
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal mereschedule booking.';
        } finally {
            savingGroupReschedule = false;
        }
    };

    const loadGroupArmadas = async (query = '') => {
        loadingGroupArmada = true;

        try {
            const json = await apiGet('/api/admin/armadas', {
                q: query.trim(),
            });
            const armadaRows = Array.isArray(json.armadas) ? json.armadas : [];
            groupArmadas = armadaRows
                .slice(0, 40)
                .map((row: Record<string, unknown>) => ({
                    id: Number(row.id ?? 0),
                    nopol: String(row.nopol ?? '').toUpperCase(),
                    merk: String(row.merk ?? ''),
                    kategori: String(row.kategori ?? ''),
                    warna: String(row.warna ?? ''),
                    tahun: Number(row.tahun ?? 0),
                    nomor_rangka: String(row.nomor_rangka ?? ''),
                }));
        } finally {
            loadingGroupArmada = false;
        }
    };

    const selectGroupArmada = (item: ArmadaItem) => {
        groupArmadaSelectedId = String(item.id);
        groupArmadaNopol = item.nopol || '-';
        groupArmadaSearch = item.nopol || '';
        groupArmadaLookupOpen = false;
    };

    const selectGroupDriver = (item: DriverItem) => {
        groupDriverSelectedId = String(item.id);
        groupDriverName = item.nama || '-';
        groupDriverSearch = item.nama || '';
        groupDriverLookupOpen = false;
    };

    const queueGroupDriverSearch = () => {
        groupDriverLookupOpen = true;

        if (groupDriverLookupTimer) {
            clearTimeout(groupDriverLookupTimer);
        }

        if (
            groupDriverSelectedId !== '0' &&
            groupDriverSearch.trim() !== groupDriverName.trim()
        ) {
            groupDriverSelectedId = '0';
        }

        groupDriverLookupTimer = setTimeout(() => {
            groupDriverLookupOpen = true;
        }, 120);
    };

    const onGroupDriverBlur = () => {
        setTimeout(() => {
            groupDriverLookupOpen = false;
        }, 120);
    };

    const queueGroupArmadaSearch = () => {
        groupArmadaLookupOpen = true;

        if (groupArmadaLookupTimer) {
            clearTimeout(groupArmadaLookupTimer);
        }

        if (
            groupArmadaSelectedId !== '0' &&
            groupArmadaSearch.trim() !== groupArmadaNopol.trim()
        ) {
            groupArmadaSelectedId = '0';
        }

        groupArmadaLookupTimer = setTimeout(() => {
            void loadGroupArmadas(groupArmadaSearch);
        }, 220);
    };

    const onGroupArmadaBlur = () => {
        setTimeout(() => {
            groupArmadaLookupOpen = false;
        }, 120);
    };

    const loadGroupDriverState = async (group: BookingGroup) => {
        loadingGroupDriver = true;
        groupDrivers = [];
        groupDriverSelectedId = '0';
        groupDriverName = group.driver_name?.trim() || '-';
        groupDriverSearch =
            group.driver_name && group.driver_name !== '-'
                ? group.driver_name
                : '';
        groupDriverAssignmentId = null;
        groupArmadas = [];
        groupArmadaSelectedId = '0';
        groupArmadaNopol = group.armada_nopol?.trim() || '-';
        groupArmadaSearch =
            group.armada_nopol && group.armada_nopol !== '-'
                ? group.armada_nopol
                : '';
        groupArmadaLookupOpen = false;

        if (isCanceledDeparture(group)) {
            loadingGroupDriver = false;

            return;
        }

        try {
            const [driverJson, assignmentJson] = await Promise.all([
                apiGet('/api/admin/drivers', {}),
                apiGet('/api/admin/assignments', {
                    rute: group.rute,
                    tanggal: group.tanggal,
                    page: 1,
                    per_page: 200,
                }),
            ]);

            await loadGroupArmadas(groupArmadaSearch);

            const driverRows = Array.isArray(driverJson.drivers)
                ? driverJson.drivers
                : [];
            groupDrivers = driverRows.map((row: Record<string, unknown>) => ({
                id: Number(row.id ?? 0),
                nama: String(row.nama ?? ''),
                phone: String(row.phone ?? ''),
            }));

            const assignmentRows = Array.isArray(assignmentJson.assignments)
                ? assignmentJson.assignments
                : [];
            const targetJam = normalizeJamToken(group.jam);
            const targetUnit = Number(group.unit) || 1;
            const matched = assignmentRows.find(
                (row: Record<string, unknown>) => {
                    const rowJam = normalizeJamToken(String(row.jam ?? ''));
                    const rowUnit = Number(row.unit ?? 0);

                    return rowJam === targetJam && rowUnit === targetUnit;
                },
            ) as Record<string, unknown> | undefined;

            if (matched) {
                const matchedDriverId = Number(matched.driver_id ?? 0);
                const matchedArmadaId = Number(matched.armada_id ?? 0);
                const matchedArmadaNopol = String(matched.armada_nopol ?? '')
                    .trim()
                    .toUpperCase();
                groupDriverAssignmentId = Number(matched.id ?? 0) || null;
                groupDriverSelectedId =
                    matchedDriverId > 0 ? String(matchedDriverId) : '0';
                const matchedDriver = groupDrivers.find(
                    (driver) => driver.id === matchedDriverId,
                );
                groupDriverName =
                    matchedDriver?.nama?.trim() || String(matched.nama ?? '-');
                groupDriverSearch =
                    groupDriverName !== '-' ? groupDriverName : '';

                if (matchedArmadaId > 0) {
                    groupArmadaSelectedId = String(matchedArmadaId);
                }

                if (matchedArmadaNopol !== '') {
                    groupArmadaNopol = matchedArmadaNopol;
                    groupArmadaSearch = matchedArmadaNopol;
                }
            }
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memuat mapping keberangkatan.';
        } finally {
            loadingGroupDriver = false;
        }
    };

    const saveGroupDriverMapping = async () => {
        if (!openGroupDetail) {
            return;
        }

        if (isManifestLocked(openGroupDetail)) {
            formError = 'Manifest sudah ditutup. Driver dan armada tidak bisa diubah lagi.';
            formSuccess = '';

            return;
        }

        let driverId = Number(groupDriverSelectedId || '0');
        let armadaId = Number(groupArmadaSelectedId || '0');
        let typedArmadaNopol = groupArmadaSearch.trim().toUpperCase();

        if (driverId <= 0 && groupDriverSearch.trim() !== '') {
            const normalizedDriver = groupDriverSearch.trim().toLowerCase();
            const matchedDriver = groupDrivers.find(
                (driver) =>
                    driver.nama.trim().toLowerCase() === normalizedDriver,
            );

            if (matchedDriver) {
                driverId = matchedDriver.id;
                groupDriverSelectedId = String(matchedDriver.id);
                groupDriverName = matchedDriver.nama || '-';
                groupDriverSearch = matchedDriver.nama || '';
            }
        }

        if (armadaId <= 0 && groupArmadaSearch.trim() !== '') {
            typedArmadaNopol = groupArmadaSearch.trim().toUpperCase();
            let matchedArmada = groupArmadas.find(
                (armada) =>
                    armada.nopol.trim().toUpperCase() === typedArmadaNopol,
            );

            if (!matchedArmada) {
                await loadGroupArmadas(groupArmadaSearch);
                matchedArmada = groupArmadas.find(
                    (armada) =>
                        armada.nopol.trim().toUpperCase() === typedArmadaNopol,
                );
            }

            if (matchedArmada) {
                armadaId = matchedArmada.id;
                groupArmadaSelectedId = String(matchedArmada.id);
                groupArmadaNopol = matchedArmada.nopol || '-';
                groupArmadaSearch = matchedArmada.nopol || '';
            }
        }

        if (driverId <= 0) {
            formError = 'Pilih nama driver terlebih dahulu.';

            return;
        }

        savingGroupDriver = true;

        try {
            const payload: Record<string, unknown> = {
                rute: openGroupDetail.rute,
                tanggal: openGroupDetail.tanggal,
                jam: normalizeJamToken(openGroupDetail.jam),
                unit: Number(openGroupDetail.unit) || 1,
                driver_id: driverId,
                armada_id: armadaId > 0 ? armadaId : undefined,
                armada_nopol: typedArmadaNopol || undefined,
            };

            if (groupDriverAssignmentId && groupDriverAssignmentId > 0) {
                payload.id = groupDriverAssignmentId;
            }

            const json = await apiPost('/api/admin/assignments', payload);
            groupDriverAssignmentId =
                Number(json.id ?? groupDriverAssignmentId ?? 0) || null;
            const selectedDriver = groupDrivers.find(
                (driver) => driver.id === driverId,
            );
            const selectedArmada = groupArmadas.find(
                (armada) => armada.id === armadaId,
            );
            groupDriverName = selectedDriver?.nama?.trim() || '-';
            groupDriverSearch = groupDriverName !== '-' ? groupDriverName : '';
            groupArmadaNopol =
                String(json.armada_nopol ?? '').trim() ||
                selectedArmada?.nopol?.trim() ||
                typedArmadaNopol ||
                '-';
            groupArmadaSearch =
                groupArmadaNopol !== '-' ? groupArmadaNopol : '';
            localBookingGroups = localBookingGroups.map((group) =>
                group.key === openGroupDetail!.key
                    ? {
                          ...group,
                          driver_name: groupDriverName,
                          armada_nopol: groupArmadaNopol,
                          departure_can_arrive:
                              isDepartedDeparture(group) &&
                              hasDepartureDateReached(group),
                      }
                    : group,
            );
            const updatedGroupDetail = {
                ...openGroupDetail,
                driver_name: groupDriverName,
                armada_nopol: groupArmadaNopol,
                departure_can_arrive:
                    isDepartedDeparture(openGroupDetail) &&
                    hasDepartureDateReached(openGroupDetail),
            };
            openGroupDetail = updatedGroupDetail;
            formSuccess = 'Mapping driver dan armada berhasil disimpan.';
            formError = '';
        } catch (error) {
            if (
                error instanceof Error &&
                error.message === 'assignment_conflict'
            ) {
                formError =
                    'Driver bentrok di waktu yang sama. Pilih driver lain atau atur di menu Assignment.';
            } else {
                formError =
                    error instanceof Error
                        ? error.message
                        : 'Gagal menyimpan mapping keberangkatan.';
            }
        } finally {
            savingGroupDriver = false;
        }
    };

    const loadGroupRiturs = async (
        group: BookingGroup,
        query = groupRiturSearch,
    ) => {
        loadingGroupRiturs = true;

        try {
            const json = await apiGet('/api/bookings/departure-riturs', {
                rute: group.rute,
                tanggal: group.tanggal,
                jam: normalizeJamToken(group.jam),
                unit: Number(group.unit) || 1,
                q: query.trim(),
            });

            groupMappedRiturs = Array.isArray(json.mapped_luggages)
                ? json.mapped_luggages.map((row: Record<string, unknown>) => ({
                      id: Number(row.id ?? 0),
                      kode_resi: String(row.kode_resi ?? ''),
                      sender_name: String(row.sender_name ?? ''),
                      receiver_name: String(row.receiver_name ?? ''),
                      rute: String(row.rute ?? ''),
                      tanggal: String(row.tanggal ?? ''),
                      quantity: Number(row.quantity ?? 0),
                      price: Number(row.price ?? 0),
                      status: luggageStatusLabel(String(row.status ?? '')),
                      payment_status: String(row.payment_status ?? ''),
                      notes: String(row.notes ?? ''),
                      trip_assignment_id:
                          Number(row.trip_assignment_id ?? 0) || null,
                  }))
                : [];

            groupAvailableRiturs = Array.isArray(json.available_luggages)
                ? json.available_luggages.map(
                      (row: Record<string, unknown>) => ({
                          id: Number(row.id ?? 0),
                          kode_resi: String(row.kode_resi ?? ''),
                          sender_name: String(row.sender_name ?? ''),
                          receiver_name: String(row.receiver_name ?? ''),
                          rute: String(row.rute ?? ''),
                          tanggal: String(row.tanggal ?? ''),
                          quantity: Number(row.quantity ?? 0),
                          price: Number(row.price ?? 0),
                          status: luggageStatusLabel(String(row.status ?? '')),
                          payment_status: String(row.payment_status ?? ''),
                          notes: String(row.notes ?? ''),
                          trip_assignment_id:
                              Number(row.trip_assignment_id ?? 0) || null,
                      }),
                  )
                : [];
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memuat data bagasi keberangkatan.';
        } finally {
            loadingGroupRiturs = false;
        }
    };

    const queueGroupRiturSearch = () => {
        if (!openGroupDetail) {
            return;
        }

        if (groupRiturSearchTimer) {
            clearTimeout(groupRiturSearchTimer);
        }

        groupRiturSearchTimer = setTimeout(() => {
            void loadGroupRiturs(openGroupDetail!, groupRiturSearch);
        }, 240);
    };

    const mapGroupRitur = async (row: GroupRiturRow) => {
        if (!openGroupDetail || savingGroupRiturId !== null) {
            return;
        }

        savingGroupRiturId = row.id;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/departure-riturs/map', {
                        rute: openGroupDetail!.rute,
                        tanggal: openGroupDetail!.tanggal,
                        jam: normalizeJamToken(openGroupDetail!.jam),
                        unit: Number(openGroupDetail!.unit) || 1,
                        luggage_id: row.id,
                    });
                },
                {
                    loadingMessage: `Memasang bagasi ${row.kode_resi} ke keberangkatan...`,
                    successMessage:
                        'Bagasi berhasil dimapping ke keberangkatan.',
                    errorMessage: 'Gagal memetakan bagasi ke keberangkatan.',
                },
            );

            await loadGroupRiturs(openGroupDetail);
            formSuccess = 'Bagasi berhasil dimapping ke keberangkatan.';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memetakan bagasi ke keberangkatan.';
        } finally {
            savingGroupRiturId = null;
        }
    };

    const unmapGroupRitur = async (row: GroupRiturRow) => {
        if (!openGroupDetail || savingGroupRiturId !== null) {
            return;
        }

        savingGroupRiturId = row.id;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/departure-riturs/unmap', {
                        rute: openGroupDetail!.rute,
                        tanggal: openGroupDetail!.tanggal,
                        jam: normalizeJamToken(openGroupDetail!.jam),
                        unit: Number(openGroupDetail!.unit) || 1,
                        luggage_id: row.id,
                    });
                },
                {
                    loadingMessage: `Melepas bagasi ${row.kode_resi} dari keberangkatan...`,
                    successMessage: 'Mapping bagasi berhasil dilepas.',
                    errorMessage: 'Gagal melepas mapping bagasi.',
                },
            );

            await loadGroupRiturs(openGroupDetail);
            formSuccess = 'Mapping bagasi berhasil dilepas.';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal melepas mapping bagasi.';
        } finally {
            savingGroupRiturId = null;
        }
    };

    const showGroupDetail = async (group: BookingGroup) => {
        openGroupDetail = group;
        groupDriverName = group.driver_name?.trim() || '-';
        groupPassengerTab = 'active';
        groupRiturSearch = '';
        groupRiturFilter = 'same_route';
        groupMappedRiturs = [];
        groupAvailableRiturs = [];
        await Promise.all([
            loadGroupDriverState(group),
            loadGroupRiturs(group, ''),
        ]);
    };

    const manifestPrintUrl = (groupKey: string) =>
        `/bookings/manifest/${encodeURIComponent(groupKey)}/print`;
    const manifestPdfUrl = (groupKey: string) =>
        `/bookings/manifest/${encodeURIComponent(groupKey)}/pdf?inline=1`;
    const ticketPrintUrl = (bookingId: number) =>
        `/bookings/ticket/${bookingId}/print`;
    const ticketPdfUrl = (bookingId: number) =>
        `/bookings/ticket/${bookingId}/pdf?inline=1`;
    const withAutoPrint = (url: string) => {
        if (typeof window === 'undefined') {
            return url;
        }

        const nextUrl = new URL(url, window.location.origin);
        nextUrl.searchParams.set('auto_print', '1');

        return `${nextUrl.pathname}${nextUrl.search}${nextUrl.hash}`;
    };

    const openManifestPrint = (group: BookingGroup) => {
        window.open(
            withAutoPrint(manifestPrintUrl(group.key)),
            '_blank',
            'noopener,noreferrer',
        );
    };

    const openManifestPrintByKey = (groupKey: string) => {
        if (!groupKey) {
            return;
        }

        window.open(
            withAutoPrint(manifestPrintUrl(groupKey)),
            '_blank',
            'noopener,noreferrer',
        );
    };

    const openManifestPdfByKey = (groupKey: string) => {
        if (!groupKey) {
            return;
        }

        window.open(manifestPdfUrl(groupKey), '_blank', 'noopener,noreferrer');
    };

    const openTicketPrint = (bookingId: number) => {
        if (bookingId <= 0) {
            return;
        }

        window.open(
            withAutoPrint(ticketPrintUrl(bookingId)),
            '_blank',
            'noopener,noreferrer',
        );
    };

    const openTicketPdf = (bookingId: number) => {
        if (bookingId <= 0) {
            return;
        }

        window.open(ticketPdfUrl(bookingId), '_blank', 'noopener,noreferrer');
    };

    const navigateToGroupDetail = (group: BookingGroup) => {
        void showGroupDetail(group);
        router.visit(`/bookings/detail/${encodeURIComponent(group.key)}`, {
            only: [...BOOKING_LIST_STATE_PROPS],
            preserveScroll: true,
            preserveState: true,
        } as Parameters<typeof router.visit>[1] & {
            only: string[];
            preserveScroll: boolean;
            preserveState: boolean;
        });
    };

    const navigateBackToBookingList = () => {
        openGroupDetail = null;
        router.visit('/bookings', {
            only: [...BOOKING_LIST_STATE_PROPS],
            preserveScroll: true,
            preserveState: true,
        } as Parameters<typeof router.visit>[1] & {
            only: string[];
            preserveScroll: boolean;
            preserveState: boolean;
        });
    };

    const formatDepartDateTime = (tanggal: string, jam: string) => {
        const date = new Date(`${tanggal}T00:00:00`);
        const dateLabel = Number.isNaN(date.getTime())
            ? tanggal
            : date.toLocaleDateString('en-US', {
                  month: 'long',
                  day: 'numeric',
                  year: 'numeric',
              });
        const jamLabel = normalizeJamToken(jam).replace(':', '.');

        return `${dateLabel} - ${jamLabel}`;
    };

    const resolveDriverNameForGroup = async (
        group: Pick<
            BookingGroup,
            'key' | 'rute' | 'tanggal' | 'jam' | 'unit' | 'driver_name'
        >,
    ) => {
        if (
            openGroupDetail &&
            openGroupDetail.key === group.key &&
            groupDriverName.trim() !== ''
        ) {
            return groupDriverName.trim();
        }

        const fromGroup = String(group.driver_name ?? '').trim();

        if (fromGroup !== '' && fromGroup !== '-') {
            return fromGroup;
        }

        try {
            const assignmentJson = await apiGet('/api/admin/assignments', {
                rute: group.rute,
                tanggal: group.tanggal,
                page: 1,
                per_page: 200,
            });
            const assignmentRows = Array.isArray(assignmentJson.assignments)
                ? assignmentJson.assignments
                : [];
            const targetJam = normalizeJamToken(group.jam);
            const targetUnit = Number(group.unit) || 1;
            const matched = assignmentRows.find(
                (row: Record<string, unknown>) => {
                    const rowJam = normalizeJamToken(String(row.jam ?? ''));
                    const rowUnit = Number(row.unit ?? 0);

                    return rowJam === targetJam && rowUnit === targetUnit;
                },
            ) as Record<string, unknown> | undefined;

            return String(matched?.nama ?? '-').trim() || '-';
        } catch {
            return '-';
        }
    };

    const copyBookingGroup = async (group: BookingGroup) => {
        try {
            const driverName = await resolveDriverNameForGroup(group);
            const copyableBookings = group.bookings.filter(
                (row) => !isCanceledBooking(row.status),
            );
            const lines = copyableBookings.map(
                (row) =>
                    `- Kursi: ${row.seat}\n` +
                    `Nama: ${row.name || '-'}\n` +
                    `No. HP: ${row.phone || '-'}\n` +
                    `Titik Jemput: ${row.pickup_point || '-'}\n` +
                    `Gmaps: ${row.gmaps || ''}\n` +
                    `Pembayaran: ${row.pembayaran || '-'}`,
            );
            const text =
                `Info Pemberangkatan\n` +
                `Tanggal & Jam: ${formatDepartDateTime(group.tanggal, group.jam)}\n` +
                `Rute: ${group.rute}\n` +
                `Total Penumpang: ${copyableBookings.length}\n` +
                `Driver: ${driverName}\n\n` +
                lines.join('\n\n');

            await copyText(text);
            formSuccess = 'Data jadwal berhasil disalin.';
            formError = '';
        } catch {
            formError = 'Gagal menyalin data jadwal.';
        }
    };

    const copyBookingPassenger = async (
        group: BookingGroup,
        row: BookingGroup['bookings'][number],
    ) => {
        const finalPrice = Math.max(
            Number(row.price || 0) - Number(row.discount || 0),
            0,
        );
        const payment = String(row.pembayaran || '').trim();
        const includePaymentOptions = payment.toLowerCase() === 'belum lunas';
        const lines = [
            'BOOKING BERHASIL ✅',
            '',
            `Tanggal: ${formatGroupDateLabel(group.tanggal)}`,
            `Jam: ${formatGroupTimeLabel(group.jam)}`,
            `Kursi: ${row.seat || '-'}`,
            `Nama: ${row.name || '-'}`,
            `Telepon: ${row.phone || '-'}`,
            `Segment: ${row.segment_name || '-'}`,
            `Jemput: ${row.pickup_point || '-'}`,
        ];

        if (row.gmaps) {
            lines.push(`Gmaps: ${row.gmaps}`);
        }

        lines.push('', `Harga: Rp ${finalPrice.toLocaleString('id-ID')}`);

        if (includePaymentOptions) {
            lines.push('_*Opsi Pembayaran: Transfer / QRIS / Tunai_');
        }

        try {
            await copyText(lines.join('\n'));
            formSuccess = `Data penumpang kursi ${row.seat} disalin.`;
            formError = '';
        } catch {
            formError = 'Gagal menyalin data penumpang.';
        }
    };

    const copyText = async (text: string) => {
        if (
            typeof navigator !== 'undefined' &&
            navigator.clipboard &&
            typeof navigator.clipboard.writeText === 'function' &&
            window.isSecureContext
        ) {
            await navigator.clipboard.writeText(text);

            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', 'true');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.pointerEvents = 'none';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        const copied = document.execCommand('copy');
        document.body.removeChild(textarea);

        if (!copied) {
            throw new Error('copy_failed');
        }
    };

    const isSeatSelected = (seat: string) =>
        selectedSeatSet().has(normalizeSeatToken(seat));
    const isSeatBooked = (seat: string) =>
        bookedSeatSet().has(normalizeSeatToken(seat));

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

    const buildQuery = (
        params: Record<string, string | number | undefined>,
    ) => {
        const query = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                query.set(key, String(value));
            }
        });

        return query.toString();
    };

    const apiGet = async (
        path: string,
        params: Record<string, string | number | undefined>,
    ) => {
        const query = buildQuery(params);
        const url = query ? `${path}?${query}` : path;
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), API_TIMEOUT_MS);

        try {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                },
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.success === false) {
                throw new Error(
                    data.error || `Request gagal (${response.status})`,
                );
            }

            return data;
        } catch (error) {
            if (controller.signal.aborted) {
                throw new Error('Request timeout saat memuat data.');
            }

            throw error;
        } finally {
            clearTimeout(timeoutId);
        }
    };

    const apiPost = async (path: string, payload: Record<string, unknown>) => {
        const postOnce = async () => {
            const token = csrfToken();
            const xsrfToken = xsrfTokenFromCookie();

            return fetch(path, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token || xsrfToken,
                    'X-XSRF-TOKEN': xsrfToken || token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    _token: token,
                    ...payload,
                }),
            });
        };

        let response = await postOnce();

        if (response.status === 419 && (await refreshCsrfToken())) {
            response = await postOnce();
        }

        const data = await response.json().catch(() => ({}));

        if (!response.ok || data.success === false) {
            throw new Error(data.error || `Request gagal (${response.status})`);
        }

        if (
            path.startsWith('/api/bookings/') ||
            path.startsWith('/api/admin/assignments')
        ) {
            scheduleBookingListReload();
        }

        return data;
    };

    const resetScheduleState = () => {
        schedules = [];
        selectedJam = '';
        selectedUnit = 1;
        seatDetailsMap = {};
        segments = [];
        formSegmentId = 0;
        formDiscount = '';
        selectedSeats = [];
        formSeat = '';
        scheduleError = '';
        detailError = '';
    };

    const resetBookingForm = () => {
        customerSearchRequestId += 1;

        if (customerSearchTimer) {
            clearTimeout(customerSearchTimer);
            customerSearchTimer = null;
        }

        selectedSeats = [];
        formSeat = '';
        formName = '';
        formPhone = '';
        formPickupPoint = '';
        formAddress = '';
        customerLookupQuery = '';
        customerSuggestions = [];
        customerSuggestOpen = false;
        customerLookupMessage = '';
        loadingCustomerLookup = false;
        formSegmentId = 0;
        formDiscount = '';
        formPayment = 'Belum Lunas';
        formError = '';
        formSuccess = '';
        rekapItems = [];
        lastTappedSeat = '';
        lastSelectedPulseSeat = '';
    };

    const loadRoutesByDate = async () => {
        loadingRoutes = true;
        routeError = '';
        availableRoutes = [];

        try {
            const json = await apiGet('/api/bookings/routes-by-date', {
                tanggal: bookingDate,
            });
            availableRoutes = Array.isArray(json.routes) ? json.routes : [];
        } catch (error) {
            routeError =
                error instanceof Error ? error.message : 'Gagal memuat rute.';
        } finally {
            loadingRoutes = false;
        }
    };

    const loadSchedules = async () => {
        if (!selectedRoute) {
            resetScheduleState();

            return;
        }

        loadingSchedules = true;
        scheduleError = '';
        schedules = [];
        selectedJam = '';
        selectedUnit = 1;
        seatDetailsMap = {};
        formSegmentId = 0;
        formDiscount = '';
        selectedSeats = [];
        formSeat = '';

        try {
            const json = await apiGet('/api/bookings/schedules', {
                rute: selectedRoute,
                tanggal: bookingDate,
            });
            schedules = Array.isArray(json.schedules) ? json.schedules : [];

            if (schedules.length > 0) {
                selectedJam = schedules[0].jam;
                selectedUnit = 1;
                syncSegmentSelectionToSchedule();
                await loadSeatDetails();
            }
        } catch (error) {
            scheduleError =
                error instanceof Error ? error.message : 'Gagal memuat jadwal.';
        } finally {
            loadingSchedules = false;
        }
    };

    const loadSegments = async () => {
        segments = [];

        if (!selectedRoute) {
            return;
        }

        loadingSegments = true;

        try {
            const json = await apiGet('/api/master/segments', {
                route_name: selectedRoute,
            });
            segments = Array.isArray(json.segments)
                ? (json.segments as SegmentItem[])
                : [];
        } catch {
            segments = [];
        } finally {
            loadingSegments = false;
        }
    };

    const loadSeatDetails = async () => {
        if (!selectedRoute || !selectedJam) {
            seatDetailsMap = {};

            return;
        }

        loadingSeatDetails = true;
        detailError = '';
        seatDetailsMap = {};

        try {
            const json = await apiGet('/api/bookings/seats-detail', {
                rute: selectedRoute,
                tanggal: bookingDate,
                jam: selectedJam,
                unit: Number(selectedUnit) || 1,
            });
            seatDetailsMap = (json.details ?? {}) as Record<string, SeatDetail>;

            if (selectedSeats.length > 0) {
                selectedSeats = selectedSeats.filter(
                    (seat) => !isSeatBooked(seat),
                );
                syncFormSeatFromSelected();
            } else if (formSeat) {
                syncSelectedSeatsFromInput();
                syncFormSeatFromSelected();
            }
        } catch (error) {
            detailError =
                error instanceof Error
                    ? error.message
                    : 'Gagal memuat detail kursi.';
        } finally {
            loadingSeatDetails = false;
        }
    };

    const onDateChange = async () => {
        selectedRoute = '';
        resetScheduleState();
        await loadRoutesByDate();
    };

    const selectQuickDate = async (nextDate: string) => {
        if (!nextDate || nextDate === bookingDate) {
            return;
        }

        bookingDate = nextDate;
        bookingDatePicker?.setDate(nextDate, false, 'Y-m-d');
        await onDateChange();
    };

    const onRouteChange = async () => {
        segments = [];
        await loadSchedules();
        await loadSegments();
        syncSegmentSelectionToSchedule();
    };

    const onScheduleChange = async () => {
        selectedUnit = 1;
        syncSegmentSelectionToSchedule();
        await loadSeatDetails();
    };

    const submitBooking = async () => {
        formError = '';
        formSuccess = '';
        formName = normalizeNameForBooking(formName);
        formPhone = normalizePhoneForBooking(formPhone);

        if (!selectedRoute || !selectedJam) {
            formError = 'Pilih rute dan jadwal terlebih dahulu.';

            return;
        }

        if (!formName.trim() || !formPhone.trim()) {
            formError = 'Nama dan telepon wajib diisi.';

            return;
        }

        if (formPhone.length > 13) {
            formError = 'Nomor HP maksimal 13 digit.';

            return;
        }

        if (segments.length > 0 && Number(formSegmentId) <= 0) {
            formError = 'Pilih segment rute terlebih dahulu.';

            return;
        }

        const requestedSeats =
            selectedSeats.length > 0 ? selectedSeats : parseSeatInput(formSeat);

        if (requestedSeats.length === 0) {
            formError = 'Seat wajib diisi.';

            return;
        }

        const unavailableSeats = requestedSeats.filter((seat) =>
            isSeatBooked(seat),
        );
        const seatCandidates = requestedSeats.filter(
            (seat) => !isSeatBooked(seat),
        );

        if (seatCandidates.length === 0) {
            formError = `Kursi ${unavailableSeats.join(', ')} sudah terisi. Pilih kursi lain.`;

            return;
        }

        submittingBooking = true;

        try {
            const payload = {
                rute: selectedRoute,
                tanggal: bookingDate,
                jam: selectedJam,
                unit: Number(selectedUnit) || 1,
                name: formName,
                phone: formPhone,
                pickup_point: formPickupPoint,
                address: formAddress,
                pembayaran: formPayment,
                segment_id:
                    Number(formSegmentId) > 0
                        ? Number(formSegmentId)
                        : undefined,
                discount: parseCurrencyInput(formDiscount),
                seats: seatCandidates,
            };
            const json = await runWithFeedback(
                async () => {
                    return apiPost('/api/bookings/submit', payload);
                },
                {
                    loadingMessage: 'Menyimpan booking penumpang...',
                    successMessage: `${seatCandidates.length} booking berhasil disimpan.`,
                    errorMessage: 'Gagal menyimpan booking.',
                },
            );

            formSuccess = `${json.message ?? 'Booking berhasil dibuat.'} (${seatCandidates.length} kursi)`;
            localTotals = {
                ...localTotals,
                bookings: localTotals.bookings + seatCandidates.length,
            };

            const createdIds = Array.isArray(json.booking_ids)
                ? json.booking_ids.map((item) => Number(item))
                : [];
            const createdRecords: CreatedBookingRecord[] = Array.isArray(
                json.booking_records,
            )
                ? json.booking_records.map((item): CreatedBookingRecord => {
                      const record = item as Record<string, unknown>;

                      return {
                          seat: String(record.seat ?? ''),
                          departure_code: record.departure_code
                              ? String(record.departure_code)
                              : undefined,
                          ticket_code: record.ticket_code
                              ? String(record.ticket_code)
                              : undefined,
                      };
                  })
                : [];
            const recordBySeat = new Map<string, CreatedBookingRecord>(
                createdRecords.map((item) => [item.seat, item]),
            );
            const seatPrice = Math.max(Number(activeSegment()?.harga ?? 0), 0);
            const totalDiscount = parseCurrencyInput(formDiscount);
            const seatDiscount =
                seatCandidates.length > 0
                    ? totalDiscount / seatCandidates.length
                    : 0;
            const activeSegmentJamPickups = segmentJamList(
                activeSegment()?.jam_pickups,
            );
            const segmentJamPickups =
                activeSegmentJamPickups.length > 0
                    ? activeSegmentJamPickups
                    : segmentJamList(activeSegment()?.jam);
            const successSnapshot: BookingSuccessSnapshot = {
                tanggal: bookingDate,
                jam: selectedJam,
                segment_jam:
                    segmentJamSummary(segmentJamPickups) ||
                    segmentJamLabel(activeSegment()?.jam) ||
                    '',
                segment_jam_pickups: segmentJamPickups,
                rute: selectedRoute,
                unit: Number(selectedUnit) || 1,
                total: Math.max(
                    seatPrice * seatCandidates.length - totalDiscount,
                    0,
                ),
                items: seatCandidates.map((seat) => ({
                    seat,
                    name: formName,
                    phone: formPhone,
                    pickup_point: formPickupPoint,
                    segment_name: activeSegment()?.rute || selectedRoute || '-',
                    segment_jam:
                        segmentJamSummary(segmentJamPickups) ||
                        segmentJamLabel(activeSegment()?.jam) ||
                        '',
                    segment_jam_pickups: segmentJamPickups,
                    pembayaran: formPayment,
                    final_price: Math.max(seatPrice - seatDiscount, 0),
                })),
            };
            const newRows = seatCandidates.map((seat, index) => ({
                id: Number.isFinite(createdIds[index])
                    ? createdIds[index]
                    : Date.now() + index,
                group_key: '',
                name: formName.toUpperCase(),
                phone: formPhone,
                rute: selectedRoute,
                tanggal: bookingDate,
                jam: selectedJam,
                unit: Number(selectedUnit) || 1,
                seat,
                status: 'active',
                pembayaran: formPayment,
                departure_code: String(
                    recordBySeat.get(seat)?.departure_code ??
                        json.departure_code ??
                        '',
                ),
                ticket_code: String(recordBySeat.get(seat)?.ticket_code ?? ''),
            }));

            localLatestBookings = [...newRows, ...localLatestBookings].slice(
                0,
                20,
            );

            bookingSuccessSnapshot = successSnapshot;
            bookingSuccessFeedback = '';
            bookingSuccessModalOpen = true;
            resetBookingForm();
            mobileBookingStep = 2;
            await loadSeatDetails();
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal menyimpan booking.';
        } finally {
            submittingBooking = false;
        }
    };

    const cancelSeat = async (bookingId: number) => {
        if (!bookingId || cancelingSeatId !== null) {
            return;
        }

        if (isSelectedTripManifestClosed()) {
            formError = 'Manifest sudah ditutup. Booking tidak bisa dibatalkan lagi.';
            formSuccess = '';

            return;
        }

        cancelingSeatId = bookingId;
        formError = '';
        formSuccess = '';

        try {
            await runWithFeedback(
                async () => {
                    await apiPost('/api/bookings/cancel', {
                        booking_id: bookingId,
                        reason: 'Canceled from booking form detail',
                    });
                    rekapItems = rekapItems.filter(
                        (item) => item.booking_id !== bookingId,
                    );

                    if (detailSeat && detailSeat.id === bookingId) {
                        closeSeatDetail();
                    }

                    await loadSeatDetails();
                    localLatestBookings = localLatestBookings.map((row) =>
                        row.id === bookingId
                            ? { ...row, status: 'canceled' }
                            : row,
                    );
                },
                {
                    loadingMessage: 'Membatalkan booking penumpang...',
                    successMessage: 'Booking berhasil dibatalkan.',
                    errorMessage: 'Gagal membatalkan booking.',
                },
            );
            formSuccess = 'Booking berhasil dibatalkan.';
        } catch (error) {
            formError =
                error instanceof Error
                    ? error.message
                    : 'Gagal membatalkan booking.';
        } finally {
            cancelingSeatId = null;
        }
    };

    onMount(() => {
        let disposed = false;

        const initPickers = async () => {
            const flatpickr = await loadFlatpickr();
            if (disposed) {
                return;
            }

            if (bookingDateInput && !bookingDatePicker) {
                bookingDatePicker = flatpickr(bookingDateInput, {
                    minDate: 'today',
                    dateFormat: 'Y-m-d',
                    defaultDate: bookingDate,
                    disableMobile: true,
                    onChange: (_selectedDates, dateStr) => {
                        if (dateStr && dateStr !== bookingDate) {
                            bookingDate = dateStr;
                            void onDateChange();
                        }
                    },
                });
            }

            if (bookingListDateInput && !bookingListDatePicker) {
                bookingListDatePicker = flatpickr(bookingListDateInput, {
                    dateFormat: 'Y-m-d',
                    defaultDate: bookingListDateFrom || today,
                    disableMobile: true,
                    onChange: (_selectedDates, dateStr) => {
                        if (dateStr && dateStr !== bookingListDateFrom) {
                            setBookingListDate(dateStr);
                        }
                    },
                });
            }
        };

        void initPickers();

        if (listOnly) {
            if (groupDetailPage && groupDetailKey) {
                const target = localBookingGroups.find(
                    (group) => group.key === groupDetailKey,
                );

                if (target) {
                    void showGroupDetail(target);
                } else {
                    formError = 'Detail keberangkatan tidak ditemukan.';
                }
            }

            if (consumeDataStale(['bookings'])) {
                reloadBookingListData();
            }
        } else {
            void loadRoutesByDate();
        }

        return () => {
            disposed = true;

            if (groupDriverLookupTimer) {
                clearTimeout(groupDriverLookupTimer);
            }

            if (groupArmadaLookupTimer) {
                clearTimeout(groupArmadaLookupTimer);
            }

            if (groupRiturSearchTimer) {
                clearTimeout(groupRiturSearchTimer);
            }

            if (bookingListReloadTimer) {
                clearTimeout(bookingListReloadTimer);
                bookingListReloadTimer = null;
            }

            bookingDatePicker?.destroy();
            bookingDatePicker = null;
            bookingListDatePicker?.destroy();
            bookingListDatePicker = null;
        };
    });
</script>

<AppHead title={consoleOnly ? 'Booking Console' : 'Data Keberangkatan'} />

<div
    data-content-density="compact"
    class="flex h-full flex-1 flex-col gap-4 overflow-x-hidden rounded-xl p-4 pb-32"
>
    {#if !listOnly}
        <Card
            class="overflow-hidden border-sidebar-border/70 bg-gradient-to-b from-background to-muted/10 dark:border-sidebar-border"
        >
            {#if !consoleOnly}
                <CardHeader
                    class="space-y-3 border-b bg-background/90 backdrop-blur"
                >
                    <div
                        class="flex flex-wrap items-start justify-between gap-3"
                    >
                        <div class="space-y-1">
                            <CardTitle class="text-lg md:text-xl"
                                >Live Booking Console</CardTitle
                            >
                            <CardDescription>
                                Workflow cepat untuk pilih jadwal, tentukan
                                kursi, dan simpan keberangkatan.
                            </CardDescription>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Badge
                                variant="secondary"
                                class="rounded-full px-3 py-1 text-[11px]"
                                ><Armchair class="mr-1 h-3.5 w-3.5" />Seat Map</Badge
                            >
                            <Badge
                                variant="secondary"
                                class="rounded-full px-3 py-1 text-[11px]"
                                ><WalletCards class="mr-1 h-3.5 w-3.5" />Fast
                                Checkout</Badge
                            >
                        </div>
                    </div>
                </CardHeader>
            {/if}
            <CardContent class="space-y-5 p-4 md:p-5">
                <div class="space-y-3 md:hidden">
                    <div
                        class="rounded-2xl border border-cyan-200/70 bg-cyan-50/70 p-3 shadow-sm dark:border-cyan-900/60 dark:bg-cyan-950/20"
                    >
                        <div class="grid grid-cols-3 gap-1.5">
                            {#each mobileStepItems as item (item.step)}
                                <button
                                    type="button"
                                    class={`rounded-xl border px-2 py-2 text-center transition ${mobileBookingStep === item.step ? 'border-cyan-500 bg-cyan-600 text-white shadow-sm' : mobileBookingStep > item.step ? 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200' : 'border-border bg-background text-muted-foreground'}`}
                                    onclick={() => {
                                        if (item.step === 1) {
                                            mobileBookingStep = 1;
                                        } else if (item.step === 2) {
                                            goMobileScheduleNext();
                                        } else {
                                            goMobileSeatNext();
                                        }
                                    }}
                                >
                                    <span
                                        class="block text-[10px] font-black uppercase tracking-wide"
                                        >Tab {item.step}</span
                                    >
                                    <span class="block text-xs font-semibold"
                                        >{item.label}</span
                                    >
                                </button>
                            {/each}
                        </div>
                        <div class="mt-3 rounded-xl bg-background/75 px-3 py-2">
                            <p
                                class="text-[10px] font-bold uppercase tracking-wide text-muted-foreground"
                            >
                                Ringkasan Jadwal
                            </p>
                            <p
                                class="truncate text-xs font-semibold text-foreground"
                            >
                                {mobileTripSummary()}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class={`gap-3 lg:grid-cols-[minmax(280px,0.9fr)_minmax(0,1.35fr)] ${mobileBookingStep === 1 ? 'grid' : 'hidden md:grid'}`}
                >
                    <div>
                        <div
                            class="rounded-xl border border-input bg-background/70 p-2.5 shadow-sm"
                        >
                            <div
                                class="mb-2 flex items-center justify-between gap-2"
                            >
                                <p
                                    class="truncate text-xs font-semibold capitalize text-foreground"
                                >
                                    {bookingDateLabel}
                                </p>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    class="h-7 rounded-md px-2 text-[11px]"
                                    onclick={() => void selectQuickDate(today)}
                                >
                                    Hari ini
                                </Button>
                            </div>

                            <div class="relative">
                                <input
                                    id="booking-date"
                                    bind:this={bookingDateInput}
                                    class="flex h-11 w-full rounded-lg border border-input bg-background px-3 py-1 text-center text-sm font-semibold shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    type="text"
                                    value={bookingDate}
                                    readonly
                                    autocomplete="off"
                                />
                            </div>

                            <div
                                class="mt-2 flex gap-1.5 overflow-x-auto pb-0.5"
                            >
                                {#each quickDatePresets as preset (`quick-date-${preset.value}`)}
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={bookingDate === preset.value
                                            ? 'default'
                                            : 'outline'}
                                        class="h-8 shrink-0 rounded-full px-3 text-[11px]"
                                        onclick={() =>
                                            void selectQuickDate(preset.value)}
                                    >
                                        {preset.label} · {preset.pretty}
                                    </Button>
                                {/each}
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <div>
                            <div
                                class="rounded-xl border border-input bg-background/70 p-2.5 shadow-sm lg:p-1.5"
                            >
                                <select
                                    id="booking-route"
                                    class="flex h-12 w-full rounded-xl border border-input bg-background px-3 py-1 text-center text-base font-semibold lg:h-10 lg:text-sm"
                                    bind:value={selectedRoute}
                                    onchange={() => void onRouteChange()}
                                    disabled={loadingRoutes}
                                >
                                    <option value="">Pilih rute</option>
                                    {#each availableRoutes as route (route)}
                                        <option value={route}>{route}</option>
                                    {/each}
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2 lg:gap-2">
                            <div>
                                <div
                                    class="rounded-xl border border-input bg-background/70 p-2.5 shadow-sm lg:p-1.5"
                                >
                                    <select
                                        id="booking-jam"
                                        class="flex h-12 w-full rounded-xl border border-input bg-background px-3 py-1 text-center text-base font-semibold lg:h-10 lg:text-sm"
                                        bind:value={selectedJam}
                                        onchange={() => void onScheduleChange()}
                                        disabled={loadingSchedules ||
                                            schedules.length === 0}
                                    >
                                        <option value="">Pilih jam</option>
                                        {#each schedules as schedule (schedule.jam)}
                                            <option value={schedule.jam}>
                                                {schedule.jam}{schedule.unit_label
                                                    ? ` | ${schedule.unit_label}`
                                                    : ''}{schedule.nopol
                                                    ? ` | ${schedule.nopol}`
                                                    : ''}{scheduleSegmentCount(
                                                    schedule,
                                                ) > 0
                                                    ? ` | ${scheduleSegmentCount(schedule)} segment`
                                                    : ''}{!consoleOnly
                                                    ? ` | BOP ${formatCurrency(Number(schedule.bop || 0))}`
                                                    : ''}
                                            </option>
                                        {/each}
                                    </select>
                                </div>
                                <p
                                    class="mt-1 text-[11px] text-muted-foreground"
                                >
                                    {scheduleSegmentHint(activeSchedule())}
                                </p>
                            </div>

                            <div>
                                <div
                                    class="rounded-xl border border-input bg-background/70 p-2.5 shadow-sm lg:p-1.5"
                                >
                                    <select
                                        id="booking-unit"
                                        class="flex h-12 w-full rounded-xl border border-input bg-background px-3 py-1 text-center text-sm lg:h-10 lg:text-xs"
                                        bind:value={selectedUnit}
                                        onchange={() => void loadSeatDetails()}
                                    >
                                        {#each unitOptions() as option (`unit-opt-${option.value}`)}
                                            <option value={option.value}
                                                >{option.label}</option
                                            >
                                        {/each}
                                    </select>
                                </div>
                            </div>
                        </div>
                        {#if selectedJam && !consoleOnly}
                            <div
                                class="rounded-xl border border-border/70 bg-muted/25 px-3 py-2 text-xs text-muted-foreground"
                            >
                                BOP jadwal {selectedJam}:
                                <span class="font-semibold text-foreground"
                                    >{formatCurrency(
                                        Number(activeSchedule()?.bop ?? 0),
                                    )}</span
                                >
                            </div>
                        {/if}
                    </div>
                </div>

                {#if mobileBookingStep === 1}
                    <div
                        class="sticky bottom-3 z-20 rounded-2xl border border-border/80 bg-background/95 p-3 shadow-lg backdrop-blur md:hidden"
                    >
                        <Button
                            type="button"
                            class="h-11 w-full rounded-xl"
                            onclick={goMobileScheduleNext}
                            disabled={!mobileScheduleReady()}
                        >
                            Next: Pilih Kursi
                        </Button>
                    </div>
                {/if}

                {#if routeError}
                    <p class="text-sm text-destructive">{routeError}</p>
                {/if}
                {#if scheduleError}
                    <p class="text-sm text-destructive">{scheduleError}</p>
                {/if}
                {#if detailError}
                    <p class="text-sm text-destructive">{detailError}</p>
                {/if}
                {#if formError}
                    <p
                        class="rounded-xl border border-destructive/20 bg-destructive/10 px-3 py-2 text-sm text-destructive md:hidden"
                    >
                        {formError}
                    </p>
                {/if}
                <p class="sr-only" aria-live="polite">{formSuccess}</p>
                <p class="sr-only" aria-live="assertive">{formError}</p>

                <div
                    class={`gap-4 xl:grid-cols-[1.3fr_1fr] ${mobileBookingStep === 1 ? 'hidden md:grid' : 'grid'}`}
                >
                    <div
                        class={`space-y-3 rounded-2xl border border-border/70 bg-linear-to-b from-background to-cyan-500/[0.03] p-4 shadow-sm ${mobileBookingStep === 2 ? 'block' : 'hidden md:block'}`}
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-sm font-semibold tracking-tight"
                                >
                                    Peta Kursi
                                </h3>
                                <p class="text-[11px] text-muted-foreground">
                                    Tap kursi untuk pilih, tap kursi terisi
                                    untuk detail.
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="rounded-lg"
                                onclick={() => void loadSeatDetails()}
                                ><RefreshCw
                                    class="mr-1.5 h-3.5 w-3.5"
                                />Refresh</Button
                            >
                        </div>
                        {#if hasSelectedTrip() && loadingSeatDetails}
                            <div class="space-y-2">
                                <p class="text-sm text-muted-foreground">
                                    Memuat layout kursi...
                                </p>
                                <div
                                    class="grid gap-2"
                                    style="grid-template-columns: repeat(4, minmax(0, 1fr));"
                                >
                                    {#each Array.from( { length: 12 }, ) as _, idx (`seat-skeleton-${idx}`)}
                                        <div
                                            class="h-14 animate-pulse rounded-xl border bg-muted/40"
                                        ></div>
                                    {/each}
                                </div>
                            </div>
                        {:else if visibleSeatLayoutRows().length === 0}
                            <p class="text-sm text-muted-foreground">
                                Layout kursi belum tersedia untuk jadwal ini.
                            </p>
                        {:else}
                            <div
                                class="space-y-3 rounded-2xl border border-border/70 bg-background/70 p-3"
                            >
                                {#if !hasSelectedTrip()}
                                    <p class="text-xs text-muted-foreground">
                                        Preview layout default. Pilih rute dan
                                        jadwal untuk mulai booking.
                                    </p>
                                {/if}
                                <div class="grid gap-2 text-xs md:grid-cols-3">
                                    <div
                                        class="flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-2.5 py-1 text-primary"
                                    >
                                        <Armchair class="size-3.5" />
                                        Terpilih
                                    </div>
                                    <div
                                        class="flex items-center gap-2 rounded-full border border-destructive/30 bg-destructive/10 px-2.5 py-1 text-destructive"
                                    >
                                        <Armchair class="size-3.5" />
                                        Terisi
                                    </div>
                                    <div
                                        class="flex items-center gap-2 rounded-full border border-border bg-background px-2.5 py-1 text-muted-foreground"
                                    >
                                        <Armchair class="size-3.5" />
                                        Tersedia
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    {#each visibleSeatLayoutRows() as row, rowIndex (`seat-row-${rowIndex}`)}
                                        <div
                                            class="grid gap-2"
                                            style={`grid-template-columns: repeat(${Math.max(row.length, 1)}, minmax(0, 1fr));`}
                                        >
                                            {#each row as cell, colIndex (`seat-col-${rowIndex}-${colIndex}`)}
                                                {#if cell.kind === 'empty'}
                                                    <div
                                                        class="h-14 rounded-xl border border-dashed border-muted-foreground/20 bg-muted/20"
                                                    ></div>
                                                {:else if cell.kind === 'driver'}
                                                    <div
                                                        class="flex h-14 flex-col items-center justify-center rounded-xl border border-dashed border-muted-foreground/30 bg-muted/30 text-[10px] font-semibold text-muted-foreground"
                                                    >
                                                        <BusFront
                                                            class="mb-1 size-4"
                                                        />
                                                        DRIVER
                                                    </div>
                                                {:else}
                                                    <Button
                                                        type="button"
                                                        data-seat-button="true"
                                                        data-seat-token={cell.seat ??
                                                            ''}
                                                        aria-label={seatAriaLabel(
                                                            cell.seat ?? '',
                                                        )}
                                                        aria-pressed={isSeatSelected(
                                                            cell.seat ?? '',
                                                        )}
                                                        aria-haspopup={isSeatBooked(
                                                            cell.seat ?? '',
                                                        )
                                                            ? 'dialog'
                                                            : undefined}
                                                        variant="outline"
                                                        class={`h-14 w-full rounded-xl border transition-all duration-200 ${isSeatSelected(cell.seat ?? '') ? 'border-primary bg-primary/15 text-primary ring-1 ring-primary/20' : ''} ${isSeatBooked(cell.seat ?? '') ? 'border-destructive/40 bg-destructive/15 text-destructive' : ''} ${!isSeatSelected(cell.seat ?? '') && !isSeatBooked(cell.seat ?? '') ? 'bg-background hover:bg-muted/40' : ''} ${isSeatTapAnimating(cell.seat ?? '') ? 'seat-tap-pop' : ''} ${isSeatSelectedPulseAnimating(cell.seat ?? '') ? 'seat-selected-pulse' : ''} ${!hasSelectedTrip() ? 'opacity-70' : ''}`}
                                                        disabled={!hasSelectedTrip()}
                                                        onclick={() =>
                                                            onSeatCellClick(
                                                                cell.seat ?? '',
                                                            )}
                                                        onkeydown={(event) =>
                                                            onSeatKeydown(
                                                                event,
                                                                cell.seat ?? '',
                                                            )}
                                                    >
                                                        <span
                                                            class="flex flex-col items-center justify-center gap-0.5 leading-none"
                                                        >
                                                            <Armchair
                                                                class="size-4"
                                                            />
                                                            <span
                                                                class="text-[11px] font-semibold"
                                                                >{cell.seat}</span
                                                            >
                                                        </span>
                                                    </Button>
                                                {/if}
                                            {/each}
                                        </div>
                                    {/each}
                                </div>
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        Seat terpilih:
                                        <span
                                            class="font-semibold text-foreground"
                                            >{selectedSeats.length > 0
                                                ? selectedSeats.join(', ')
                                                : '-'}</span
                                        >
                                    </p>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onclick={clearSeatSelection}
                                        disabled={selectedSeats.length === 0}
                                    >
                                        Clear
                                    </Button>
                                </div>
                            </div>
                        {/if}

                        <div
                            class="rounded-2xl border bg-background/80 p-3 shadow-sm"
                        >
                            <p
                                class="mb-2 text-xs font-medium text-muted-foreground"
                            >
                                Info Kursi
                            </p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                <div
                                    class="rounded-xl bg-muted/30 px-2 py-2 text-center"
                                >
                                    <p
                                        class="text-[11px] text-muted-foreground"
                                    >
                                        Total
                                    </p>
                                    <p class="text-lg font-semibold">
                                        {totalSeatsDisplay()}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl bg-muted/30 px-2 py-2 text-center"
                                >
                                    <p
                                        class="text-[11px] text-muted-foreground"
                                    >
                                        Booked
                                    </p>
                                    <p class="text-lg font-semibold">
                                        {bookedCountDisplay()}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl bg-muted/30 px-2 py-2 text-center"
                                >
                                    <p
                                        class="text-[11px] text-muted-foreground"
                                    >
                                        Empty
                                    </p>
                                    <p class="text-lg font-semibold">
                                        {emptyCountDisplay()}
                                    </p>
                                </div>
                                <div
                                    class="rounded-xl bg-muted/30 px-2 py-2 text-center"
                                >
                                    <p
                                        class="text-[11px] text-muted-foreground"
                                    >
                                        Selected
                                    </p>
                                    <p
                                        class="text-lg font-semibold text-primary"
                                    >
                                        {selectedCount()}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="space-y-3 rounded-2xl border bg-background/90 p-3 shadow-sm md:hidden"
                        >
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Rekap Sesi
                                    </h3>
                                    <p class="text-xs text-muted-foreground">
                                        {grandCount()} kursi • Total Rp {grandTotal().toLocaleString(
                                            'id-ID',
                                        )}
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onclick={() => void copyRekap()}
                                    disabled={rekapItems.length === 0}
                                >
                                    Salin
                                </Button>
                            </div>

                            {#if rekapItems.length === 0}
                                <p
                                    class="rounded-xl border border-dashed bg-muted/20 px-3 py-3 text-sm text-muted-foreground"
                                >
                                    Belum ada item rekap. Tap kursi terisi lalu
                                    pilih Tambah ke Rekap.
                                </p>
                            {:else}
                                <div class="max-h-56 space-y-2 overflow-auto">
                                    {#each rekapItems as item (item.booking_id)}
                                        <div
                                            class="rounded-xl border bg-card px-3 py-2"
                                        >
                                            <div
                                                class="flex items-start justify-between gap-2"
                                            >
                                                <div class="min-w-0">
                                                    <p
                                                        class="truncate text-sm font-semibold"
                                                    >
                                                        Kursi {item.seat} • {item.name}
                                                    </p>
                                                    <p
                                                        class="truncate text-xs text-muted-foreground"
                                                    >
                                                        {item.segment_name}
                                                    </p>
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    class="h-8 shrink-0 px-2"
                                                    onclick={() =>
                                                        removeRekapItem(
                                                            item.booking_id,
                                                        )}
                                                >
                                                    Hapus
                                                </Button>
                                            </div>
                                            <p
                                                class="mt-1 text-xs font-semibold text-foreground"
                                            >
                                                Rp {item.final_price.toLocaleString(
                                                    'id-ID',
                                                )}
                                            </p>
                                        </div>
                                    {/each}
                                </div>
                            {/if}

                            <div class="grid grid-cols-2 gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-11 rounded-xl"
                                    onclick={() => (mobileBookingStep = 1)}
                                >
                                    Back
                                </Button>
                                <Button
                                    type="button"
                                    class="h-11 rounded-xl"
                                    onclick={goMobileSeatNext}
                                    disabled={selectedCount() === 0}
                                >
                                    Next: Isi Data
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div
                        class={`space-y-3 ${mobileBookingStep === 3 ? 'block' : 'hidden md:block'}`}
                    >
                        <div
                            class="space-y-3 rounded-2xl border bg-background/80 p-4 shadow-sm"
                        >
                            <h3 class="text-sm font-semibold">
                                Tambah Booking Cepat
                            </h3>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label
                                        for="booking-customer-lookup"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Cari Database Customer</label
                                    >
                                    <div class="relative">
                                        <Input
                                            id="booking-customer-lookup"
                                            placeholder="Cari customer lama (nama / telepon)"
                                            class="h-11 rounded-xl !pl-10"
                                            bind:value={customerLookupQuery}
                                            oninput={onCustomerLookupInput}
                                            onfocus={() =>
                                                (customerSuggestOpen =
                                                    customerSuggestions.length >
                                                    0)}
                                            onblur={onCustomerLookupBlur}
                                        />
                                        <Search
                                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                        />
                                        {#if loadingCustomerLookup}
                                            <p
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                Mencari customer...
                                            </p>
                                        {:else if customerLookupMessage}
                                            <p
                                                class="mt-1 text-xs text-muted-foreground"
                                                role="status"
                                            >
                                                {customerLookupMessage}
                                            </p>
                                        {/if}
                                        {#if customerSuggestOpen}
                                            <div
                                                class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-md border bg-background p-1 shadow-lg"
                                            >
                                                {#each customerSuggestions as item, idx (`cust-suggest-${idx}-${item.phone}`)}
                                                    <button
                                                        type="button"
                                                        class="flex w-full flex-col items-start rounded px-2 py-2 text-left hover:bg-muted"
                                                        onmousedown={() =>
                                                            applyCustomerLookup(
                                                                item,
                                                            )}
                                                    >
                                                        <span
                                                            class="text-sm font-medium"
                                                            >{item.name}</span
                                                        >
                                                        <span
                                                            class="text-xs text-muted-foreground"
                                                            >{item.phone}
                                                            {item.pickup_point
                                                                ? `- ${item.pickup_point}`
                                                                : ''}</span
                                                        >
                                                    </button>
                                                {/each}
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                                <div>
                                    <label
                                        for="booking-form-name"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Nama Penumpang</label
                                    >
                                    <div class="relative">
                                        <Input
                                            id="booking-form-name"
                                            class="h-11 rounded-xl !pl-10"
                                            placeholder="Cth: BUDI"
                                            bind:value={formName}
                                            oninput={onFormNameInput}
                                        />
                                        <UserRound
                                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label
                                        for="booking-form-phone"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Telepon</label
                                    >
                                    <div class="relative">
                                        <Input
                                            id="booking-form-phone"
                                            class="h-11 rounded-xl !pl-10"
                                            placeholder="Cth: 08123456789"
                                            bind:value={formPhone}
                                            oninput={onFormPhoneInput}
                                            onblur={() =>
                                                void autofillByPhone()}
                                        />
                                        <Phone
                                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label
                                        for="booking-form-seat"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Pilih Kursi</label
                                    >
                                    <div class="relative">
                                        <Input
                                            id="booking-form-seat"
                                            class="h-11 rounded-xl !pl-10"
                                            placeholder="Cth: 1, 2, 3"
                                            bind:value={formSeat}
                                            oninput={syncSelectedSeatsFromInput}
                                        />
                                        <Armchair
                                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label
                                        for="booking-form-segment"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Segment & Harga</label
                                    >
                                    <select
                                        id="booking-form-segment"
                                        class="flex h-11 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm text-foreground"
                                        bind:value={formSegmentId}
                                        disabled={loadingSegments}
                                    >
                                        <option value={0}
                                            >{loadingSegments
                                                ? 'Memuat segment...'
                                                : 'Pilih segment rute'}
                                        </option>
                                        {#each scheduleSegmentOptions() as segment (`segment-opt-${segment.id}`)}
                                            <option value={segment.id}>
                                                {segment.rute}{segmentJamSummary(
                                                    segment.jam_pickups,
                                                ) || segment.jam
                                                    ? ` • ${segmentJamSummary(segment.jam_pickups) || segmentJamLabel(segment.jam)}`
                                                    : ''} (Rp {Number(
                                                    segment.harga,
                                                ).toLocaleString('id-ID')})
                                            </option>
                                        {/each}
                                    </select>
                                </div>
                                <div>
                                    <label
                                        for="booking-form-payment"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Status Pembayaran</label
                                    >
                                    <select
                                        id="booking-form-payment"
                                        class="flex h-11 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm text-foreground"
                                        bind:value={formPayment}
                                    >
                                        {#each paymentOptions as option (option)}
                                            <option value={option}
                                                >{option}</option
                                            >
                                        {/each}
                                    </select>
                                </div>
                                <div>
                                    <label
                                        for="booking-form-discount"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Diskon Spesial</label
                                    >
                                    <Input
                                        id="booking-form-discount"
                                        class="h-11 rounded-xl"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="Cth: 10.000"
                                        value={formatCurrencyInput(
                                            formDiscount,
                                        )}
                                        oninput={(event) => {
                                            formDiscount = parseCurrencyInput(
                                                (
                                                    event.currentTarget as HTMLInputElement
                                                ).value,
                                            );
                                        }}
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        for="booking-form-pickup"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Titik Jemput (Pickup Point)</label
                                    >
                                    <Input
                                        id="booking-form-pickup"
                                        class="h-11 rounded-xl"
                                        placeholder="Cth: Depan Indomaret"
                                        bind:value={formPickupPoint}
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        for="booking-form-address"
                                        class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >Catatan Tambahan</label
                                    >
                                    <Input
                                        id="booking-form-address"
                                        class="h-11 rounded-xl"
                                        placeholder="URL Google Map (opsional)"
                                        bind:value={formAddress}
                                    />
                                </div>
                                <div
                                    class="rounded-xl border bg-muted/20 p-3 text-sm"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        Ringkasan
                                    </p>
                                    <p class="font-semibold">
                                        {selectedCount()} kursi - Total Rp {selectedTotal().toLocaleString(
                                            'id-ID',
                                        )}
                                    </p>
                                </div>
                                <div
                                    class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:gap-3"
                                >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-11 w-full rounded-xl md:hidden"
                                        onclick={() => (mobileBookingStep = 2)}
                                    >
                                        Back
                                    </Button>
                                    <LoadingButton
                                        class="h-11 w-full rounded-xl px-5 text-sm sm:w-auto"
                                        onclick={() => void submitBooking()}
                                        disabled={submittingBooking ||
                                            !selectedRoute ||
                                            !selectedJam}
                                        loading={submittingBooking}
                                        loadingText="Menyimpan..."
                                    >
                                        Simpan Booking
                                    </LoadingButton>
                                    {#if formSuccess}
                                        <span class="text-sm text-green-600"
                                            >{formSuccess}</span
                                        >
                                    {/if}
                                    {#if formError}
                                        <span class="text-sm text-destructive"
                                            >{formError}</span
                                        >
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        {#if selectedRoute && selectedJam}
            {#if consoleOnly}
                <div
                    class="hidden space-y-3 rounded-xl border bg-background/95 p-3 md:block"
                >
                    <div
                        class="flex flex-col items-stretch justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div class="min-w-0">
                            <p
                                class="text-[11px] uppercase tracking-wide text-muted-foreground"
                            >
                                Rekap Sesi
                            </p>
                            <p class="text-sm font-semibold">
                                {grandCount()} kursi - Total Rp {grandTotal().toLocaleString(
                                    'id-ID',
                                )}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-muted/20 p-3">
                        <div
                            class="mb-2 flex items-center justify-between gap-2"
                        >
                            <div>
                                <h3 class="text-sm font-semibold">
                                    Rekap Booking
                                </h3>
                                <p class="text-xs text-muted-foreground">
                                    Ringkasan sesi aktif
                                </p>
                            </div>
                        </div>

                        {#if rekapItems.length === 0}
                            <p class="text-sm text-muted-foreground">
                                Belum ada item rekap dari kursi terisi.
                            </p>
                        {:else}
                            <div class="max-h-72 space-y-2 overflow-auto">
                                {#each rekapItems as item (item.booking_id)}
                                    <div
                                        class="rounded-md border bg-background p-2.5"
                                    >
                                        <div
                                            class="flex items-center justify-between gap-2"
                                        >
                                            <p class="text-sm font-semibold">
                                                Kursi {item.seat} - {item.name}
                                            </p>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onclick={() =>
                                                    removeRekapItem(
                                                        item.booking_id,
                                                    )}
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {item.segment_name}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Rp {item.final_price.toLocaleString(
                                                'id-ID',
                                            )}
                                        </p>
                                    </div>
                                {/each}
                            </div>
                            <p class="mt-3 text-sm font-semibold">
                                Total Rekap: Rp {rekapTotal().toLocaleString(
                                    'id-ID',
                                )}
                            </p>
                        {/if}

                        <div class="mt-3 flex flex-wrap gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onclick={() => void copyRekap()}
                                disabled={rekapItems.length === 0}
                                >Salin Rekap</Button
                            >
                            <Button
                                type="button"
                                variant="outline"
                                onclick={resetRekap}
                                disabled={rekapItems.length === 0}
                                >Reset Rekap</Button
                            >
                        </div>
                    </div>
                </div>
            {:else}
                <div
                    class="fixed inset-x-0 bottom-0 z-50 hidden border-t bg-background/95 backdrop-blur md:block"
                >
                    <div
                        class="mx-auto flex w-full max-w-7xl flex-col items-stretch justify-between gap-3 px-4 py-3 sm:flex-row sm:items-center"
                    >
                        <div class="min-w-0">
                            <p
                                class="text-[11px] uppercase tracking-wide text-muted-foreground"
                            >
                                Rekap Sesi
                            </p>
                            <p class="text-sm font-semibold">
                                {grandCount()} kursi - Total Rp {grandTotal().toLocaleString(
                                    'id-ID',
                                )}
                            </p>
                        </div>
                        <div
                            class="grid w-full gap-2 sm:flex sm:w-auto sm:items-center"
                        >
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full sm:w-auto"
                                onclick={() => (rekapModalOpen = true)}
                            >
                                Lihat Rekap
                            </Button>
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
    {/if}

    {#if !listOnly && detailModalOpen && detailSeat}
        <div
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/55 p-0 sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex max-h-[calc(100svh-0.75rem)] w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl border bg-background shadow-xl sm:max-h-[calc(100svh-2rem)] sm:rounded-2xl"
            >
                <div
                    class="flex shrink-0 items-start justify-between gap-3 border-b bg-background/95 px-3 py-3 backdrop-blur md:px-5"
                >
                    <div class="min-w-0 space-y-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <Badge
                                variant="secondary"
                                class="rounded-full px-2 py-0.5 text-[9px] sm:text-[10px]"
                                >Detail Penumpang</Badge
                            >
                            <Badge
                                variant="secondary"
                                class="rounded-full px-2 py-0.5 text-[9px] sm:text-[10px]"
                                >Kursi {detailSeat.seat}</Badge
                            >
                            <Badge
                                variant={paymentVariant(detailSeat.pembayaran)}
                                class="rounded-full px-2 py-0.5 text-[9px] sm:text-[10px]"
                            >
                                {detailSeat.pembayaran || '-'}
                            </Badge>
                        </div>
                        <h3
                            class="truncate text-sm font-semibold sm:text-base md:text-lg"
                        >
                            {detailSeat.name || '-'}
                        </h3>
                        <p class="text-[11px] text-muted-foreground sm:text-xs">
                            {selectedRoute} · {bookingDate} · {selectedJam} · Unit
                            {selectedUnit}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        class="h-7 w-7 shrink-0 sm:h-8 sm:w-8"
                        onclick={closeSeatDetail}
                        aria-label="Tutup detail"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>

                <div
                    class="min-h-0 flex-1 overflow-y-auto px-3 py-3 md:px-5 md:py-4"
                >
                    {#if !detailEditMode}
                        <div class="grid gap-2.5 text-sm sm:grid-cols-2">
                            <div
                                class="rounded-xl border border-primary/15 bg-primary/5 p-3 sm:col-span-2"
                            >
                                <p
                                    class="text-[10px] font-medium uppercase tracking-wide text-muted-foreground sm:text-[11px]"
                                >
                                    Ringkasan Harga
                                </p>
                                <div
                                    class="mt-1 flex flex-wrap items-end justify-between gap-2"
                                >
                                    <p
                                        class="text-xl font-semibold text-foreground sm:text-2xl"
                                    >
                                        Rp {Math.max(
                                            Number(detailSeat.price || 0) -
                                                Number(
                                                    detailSeat.discount || 0,
                                                ),
                                            0,
                                        ).toLocaleString('id-ID')}
                                    </p>
                                    <p
                                        class="text-[11px] text-muted-foreground sm:text-xs"
                                    >
                                        Diskon Rp {Number(
                                            detailSeat.discount || 0,
                                        ).toLocaleString('id-ID')}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="sm:col-span-2 grid gap-px bg-border/40 border rounded-xl overflow-hidden shadow-sm"
                            >
                                <div class="grid grid-cols-2 gap-px">
                                    <div class="bg-background p-3">
                                        <p
                                            class="text-[10px] text-muted-foreground uppercase tracking-wider mb-1"
                                        >
                                            Nama
                                        </p>
                                        <p
                                            class="break-words text-sm font-semibold"
                                        >
                                            {detailSeat.name || '-'}
                                        </p>
                                    </div>
                                    <div class="bg-background p-3">
                                        <p
                                            class="text-[10px] text-muted-foreground uppercase tracking-wider mb-1"
                                        >
                                            Telepon
                                        </p>
                                        <p
                                            class="break-words text-sm font-semibold"
                                        >
                                            {detailSeat.phone || '-'}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-px">
                                    <div class="bg-background p-3">
                                        <p
                                            class="text-[10px] text-muted-foreground uppercase tracking-wider mb-1"
                                        >
                                            Segment
                                        </p>
                                        <p
                                            class="break-words text-sm font-semibold"
                                        >
                                            {detailSeat.segment_name || '-'}
                                        </p>
                                    </div>
                                    <div class="bg-background p-3">
                                        <p
                                            class="text-[10px] text-muted-foreground uppercase tracking-wider mb-1"
                                        >
                                            Jam Segment
                                        </p>
                                        <p
                                            class="break-words text-sm font-semibold"
                                        >
                                            {segmentJamSummary(
                                                detailSeat.segment_jam_pickups,
                                            ) ||
                                                segmentJamLabel(
                                                    detailSeat.segment_jam,
                                                ) ||
                                                '-'}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-px">
                                    <div class="bg-background p-3">
                                        <p
                                            class="text-[10px] text-muted-foreground uppercase tracking-wider mb-1"
                                        >
                                            Pembayaran
                                        </p>
                                        <p
                                            class="break-words text-sm font-semibold"
                                        >
                                            {detailSeat.pembayaran || '-'}
                                        </p>
                                    </div>
                                    <div class="bg-background p-3">
                                        <p
                                            class="text-[10px] text-muted-foreground uppercase tracking-wider mb-1"
                                        >
                                            Pickup Point
                                        </p>
                                        <p
                                            class="break-words text-sm font-semibold"
                                        >
                                            {detailSeat.pickup_point || '-'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {:else}
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <Input
                                class="h-10 rounded-xl sm:h-11"
                                placeholder="Seat"
                                bind:value={detailEditSeat}
                            />
                            <Input
                                class="h-10 rounded-xl sm:h-11"
                                placeholder="Nama"
                                bind:value={detailEditName}
                            />
                            <Input
                                class="h-10 rounded-xl sm:h-11"
                                placeholder="Telepon"
                                bind:value={detailEditPhone}
                            />
                            <Input
                                class="h-10 rounded-xl sm:h-11"
                                placeholder="Pickup Point"
                                bind:value={detailEditPickupPoint}
                            />
                            <select
                                class="flex h-10 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm sm:h-11"
                                bind:value={detailEditPayment}
                            >
                                {#each paymentOptions as option (option)}
                                    <option value={option}>{option}</option>
                                {/each}
                            </select>
                            <Input
                                class="h-10 rounded-xl sm:h-11"
                                type="text"
                                inputmode="numeric"
                                placeholder="Diskon"
                                value={formatCurrencyInput(detailEditDiscount)}
                                oninput={(event) => {
                                    detailEditDiscount = parseCurrencyInput(
                                        (
                                            event.currentTarget as HTMLInputElement
                                        ).value,
                                    );
                                }}
                            />
                            <select
                                class="flex h-10 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm sm:col-span-2 sm:h-11"
                                bind:value={detailEditSegmentId}
                            >
                                <option value={0}
                                    >{segments.length === 0
                                        ? detailSeat.segment_name ||
                                          'Tanpa Segment'
                                        : 'Pilih segment rute'}</option
                                >
                                {#each scheduleSegmentOptions() as segment (`detail-segment-${segment.id}`)}
                                    <option value={segment.id}>
                                        {segment.rute}{segmentJamSummary(
                                            segment.jam_pickups,
                                        ) || segment.jam
                                            ? ` • ${segmentJamSummary(segment.jam_pickups) || segmentJamLabel(segment.jam)}`
                                            : ''} (Rp {Number(
                                            segment.harga,
                                        ).toLocaleString('id-ID')})
                                    </option>
                                {/each}
                            </select>
                        </div>
                    {/if}

                    {#if formSuccess}
                        <p
                            class="mt-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-300"
                        >
                            {formSuccess}
                        </p>
                    {/if}
                    {#if formError}
                        <p
                            class="mt-3 rounded-xl border border-destructive/20 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                        >
                            {formError}
                        </p>
                    {/if}
                </div>

                <div
                    class="shrink-0 border-t bg-background/95 px-3 py-3 backdrop-blur md:px-5"
                >
                    {#if detailEditMode}
                        <div class="grid grid-cols-2 gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                class="h-10"
                                onclick={cancelDetailEdit}
                                disabled={savingDetailEdit}
                            >
                                Batal
                            </Button>
                            <Button
                                type="button"
                                class="h-10"
                                onclick={() => void saveDetailEdit()}
                                disabled={savingDetailEdit}
                            >
                                <Save class="mr-1.5 h-3.5 w-3.5" />
                                {savingDetailEdit ? 'Menyimpan...' : 'Simpan'}
                            </Button>
                        </div>
                    {:else}
                        <div
                            class="flex flex-wrap gap-2 justify-end sm:justify-start"
                        >
                            <div class="flex flex-1 sm:flex-none gap-2">
                                <Button
                                    type="button"
                                    class="h-10 flex-1 sm:flex-none whitespace-nowrap"
                                    onclick={() => void copyCurrentDetail()}
                                >
                                    <Copy class="mr-1.5 h-3.5 w-3.5" />
                                    Salin Detail
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10 flex-1 sm:flex-none whitespace-nowrap"
                                    onclick={addCurrentDetailToRekap}
                                >
                                    <Plus class="mr-1.5 h-3.5 w-3.5" />
                                    Msk Rekap
                                </Button>
                                {#if !isSelectedTripManifestClosed()}
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-10 sm:flex-none"
                                        onclick={startDetailEdit}
                                        aria-label="Edit detail"
                                        size="icon"
                                    >
                                        <Pencil
                                            class="h-3.5 w-3.5 text-muted-foreground"
                                        />
                                    </Button>
                                {/if}
                            </div>

                            {#if !isSelectedTripManifestClosed()}
                                <div class="flex w-full sm:w-auto gap-2">
                                    {#if !isLunasPayment(detailSeat.pembayaran)}
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="h-10 flex-1 sm:flex-none border-emerald-500/40 text-emerald-700 hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-950/30"
                                            onclick={() => void markDetailAsPaid()}
                                            disabled={markingPaidSeatId ===
                                                detailSeat.id ||
                                                cancelingSeatId === detailSeat.id}
                                        >
                                            <WalletCards
                                                class="mr-1.5 h-3.5 w-3.5"
                                            />
                                            {markingPaidSeatId === detailSeat.id
                                                ? 'Loading'
                                                : 'Tandai Lunas'}
                                        </Button>
                                    {/if}

                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-10 sm:flex-none border-rose-500/40 text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950/30 {isLunasPayment(
                                            detailSeat.pembayaran,
                                        )
                                            ? 'flex-1 sm:flex-none'
                                            : 'px-3'}"
                                        onclick={() =>
                                            void cancelSeat(detailSeat!.id)}
                                        disabled={cancelingSeatId ===
                                            detailSeat!.id ||
                                            markingPaidSeatId === detailSeat.id}
                                        aria-label="Cancel seat"
                                    >
                                        <X
                                            class="h-3.5 w-3.5 {isLunasPayment(
                                                detailSeat.pembayaran,
                                            )
                                                ? 'mr-1.5'
                                                : ''}"
                                        />
                                        {isLunasPayment(detailSeat.pembayaran)
                                            ? 'Cancel Kursi'
                                            : ''}
                                    </Button>
                                </div>
                            {/if}
                        </div>
                        {#if isSelectedTripManifestClosed()}
                            <p class="mt-2 text-xs text-muted-foreground sm:text-right">
                                Manifest sudah ditutup. Hanya print manifest yang masih aktif.
                            </p>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    {/if}

    {#if !listOnly && bookingSuccessModalOpen && bookingSuccessSnapshot}
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="w-full max-w-xl overflow-hidden rounded-2xl border border-emerald-200/70 bg-background shadow-2xl dark:border-emerald-500/20"
            >
                <div
                    class="h-1 bg-linear-to-r from-emerald-400 via-emerald-500 to-lime-400"
                ></div>
                <div class="p-4 md:p-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"
                        >
                            <CheckCircle2 class="h-7 w-7" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-[11px] font-medium uppercase tracking-[0.22em] text-emerald-600 dark:text-emerald-300"
                            >
                                Booking sukses
                            </p>
                            <h3 class="truncate text-lg font-semibold">
                                {bookingSuccessSnapshot.items.length} kursi berhasil
                                tersimpan
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {bookingSuccessSnapshot.tanggal} · {segmentJamSummary(
                                    bookingSuccessSnapshot.segment_jam_pickups,
                                ) ||
                                    segmentJamLabel(
                                        bookingSuccessSnapshot.segment_jam,
                                    ) ||
                                    normalizeJamToken(
                                        bookingSuccessSnapshot.jam,
                                    ) ||
                                    '-'} · Unit {bookingSuccessSnapshot.unit}
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            class="h-8 w-8 shrink-0"
                            onclick={closeBookingSuccessModal}
                            aria-label="Tutup popup sukses"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>

                    <div class="mt-4 rounded-2xl border bg-muted/20 p-4">
                        <p
                            class="text-xs uppercase tracking-wide text-muted-foreground"
                        >
                            Ringkasan booking
                        </p>
                        <div class="mt-2 grid gap-2 text-sm md:grid-cols-2">
                            <div>
                                <span class="text-muted-foreground">Rute</span>
                                <p class="font-medium">
                                    {bookingSuccessSnapshot.rute || '-'}
                                </p>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Total</span>
                                <p class="font-medium">
                                    Rp {bookingSuccessSnapshot.total.toLocaleString(
                                        'id-ID',
                                    )}
                                </p>
                            </div>
                        </div>
                        <div
                            class="mt-3 rounded-xl border bg-background p-3 text-sm"
                        >
                            <p class="text-xs text-muted-foreground">
                                Kursi tersimpan
                            </p>
                            <p class="mt-1 font-semibold">
                                {bookingSuccessSnapshot.items
                                    .map((item) => item.seat)
                                    .join(', ')}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <Button
                            type="button"
                            class="w-full sm:w-auto"
                            onclick={() => void copyBookingSuccessDetail()}
                        >
                            <Copy class="mr-1.5 h-3.5 w-3.5" />
                            {bookingSuccessSnapshot.items.length > 1
                                ? 'Salin Rekap'
                                : 'Salin Detail'}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="w-full sm:w-auto"
                            onclick={closeBookingSuccessModal}
                        >
                            Tutup
                        </Button>
                    </div>

                    {#if bookingSuccessFeedback}
                        <p
                            class="mt-3 text-sm text-emerald-700 dark:text-emerald-300"
                        >
                            {bookingSuccessFeedback}
                        </p>
                    {/if}
                </div>
            </div>
        </div>
    {/if}

    {#if !listOnly && !consoleOnly && rekapModalOpen}
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="w-full max-w-2xl rounded-lg border bg-background p-4 shadow-xl"
            >
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold">Rekap Booking</h3>
                        <p class="text-xs text-muted-foreground">
                            Ringkasan sesi aktif
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onclick={() => (rekapModalOpen = false)}>Tutup</Button
                    >
                </div>
                {#if rekapItems.length === 0}
                    <p class="text-sm text-muted-foreground">
                        Belum ada item rekap dari kursi terisi.
                    </p>
                {:else}
                    <div class="max-h-80 overflow-auto rounded-md border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/40 text-left">
                                    <th class="px-3 py-2">Kursi</th>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Segment</th>
                                    <th class="px-3 py-2">Harga</th>
                                    <th class="px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each rekapItems as item (item.booking_id)}
                                    <tr class="border-b/70">
                                        <td class="px-3 py-2 font-medium"
                                            >{item.seat}</td
                                        >
                                        <td class="px-3 py-2">{item.name}</td>
                                        <td class="px-3 py-2"
                                            >{item.segment_name}</td
                                        >
                                        <td class="px-3 py-2"
                                            >Rp {item.final_price.toLocaleString(
                                                'id-ID',
                                            )}</td
                                        >
                                        <td class="px-3 py-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onclick={() =>
                                                    removeRekapItem(
                                                        item.booking_id,
                                                    )}
                                            >
                                                Hapus
                                            </Button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-sm font-semibold">
                        Total Rekap: Rp {rekapTotal().toLocaleString('id-ID')}
                    </p>
                {/if}
                <div class="mt-4 flex flex-wrap gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        onclick={() => void copyRekap()}
                        disabled={rekapItems.length === 0}>Salin Rekap</Button
                    >
                    <Button
                        type="button"
                        variant="outline"
                        onclick={resetRekap}
                        disabled={rekapItems.length === 0}>Reset Rekap</Button
                    >
                </div>
            </div>
        </div>
    {/if}

    {#if listOnly && groupDetailPage}
        {#if openGroupDetail}
            <div
                class="w-full rounded-2xl border border-border/70 bg-background p-4 shadow-sm md:p-5"
            >
                <div
                    class="mb-4 flex flex-wrap items-start justify-between gap-3"
                >
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge
                                variant="secondary"
                                class="rounded-full px-3 py-1 text-[11px]"
                            >
                                {openGroupDetail.departure_code}
                            </Badge>
                            <Badge
                                variant="secondary"
                                class="rounded-full px-3 py-1 text-[11px]"
                            >
                                Unit {openGroupDetail.unit}
                            </Badge>
                            {#if !consoleOnly}
                                <Badge
                                    variant="secondary"
                                    class="rounded-full border-cyan-200 bg-cyan-50 px-3 py-1 text-[11px] text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-950/25 dark:text-cyan-200"
                                >
                                    BOP {formatCurrency(
                                        Number(openGroupDetail.bop || 0),
                                    )}
                                </Badge>
                            {/if}
                            {#if isCanceledDeparture(openGroupDetail)}
                                <Badge
                                    variant="destructive"
                                    class="rounded-full px-3 py-1 text-[11px]"
                                >
                                    Batal
                                </Badge>
                            {:else if isDepartedDeparture(openGroupDetail)}
                                <Badge
                                    variant="secondary"
                                    class="rounded-full border-amber-200 bg-amber-50 px-3 py-1 text-[11px] text-amber-700 dark:border-amber-500/30 dark:bg-amber-950/25 dark:text-amber-200"
                                >
                                    Berangkat
                                </Badge>
                            {:else if isArrivedDeparture(openGroupDetail)}
                                <Badge
                                    variant="secondary"
                                    class="rounded-full border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/25 dark:text-emerald-200"
                                >
                                    Armada Tiba
                                </Badge>
                            {:else if isManifestClosed(openGroupDetail)}
                                <Badge
                                    variant="secondary"
                                    class="rounded-full border-slate-200 bg-slate-50 px-3 py-1 text-[11px] text-slate-700 dark:border-slate-500/30 dark:bg-slate-950/25 dark:text-slate-200"
                                >
                                    Manifest Tertutup
                                </Badge>
                            {/if}
                            <Badge
                                variant="secondary"
                                class="rounded-full px-3 py-1 text-[11px]"
                            >
                                {openGroupDetail.armada_nopol || '-'}
                            </Badge>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">
                                {openGroupDetail.rute}
                            </h3>
                            <p class="text-sm font-semibold text-foreground">
                                {formatGroupDateLabel(openGroupDetail.tanggal)} -
                                {formatGroupTimeLabel(openGroupDetail.jam)}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Driver {openGroupDetail.driver_name || '-'} • Nopol
                                {openGroupDetail.armada_nopol || '-'}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="rounded-full"
                            onclick={() => openManifestPrint(openGroupDetail!)}
                        >
                            <Printer class="mr-1 h-3.5 w-3.5" />
                            Print Manifest
                        </Button>
                        {#if !isManifestLocked(openGroupDetail)}
                            <Button
                                type="button"
                                size="sm"
                                class="rounded-full"
                                onclick={() =>
                                    void markBookingGroupAsPaid(openGroupDetail!)}
                                disabled={markingPaidSeatId !== null ||
                                    isCanceledDeparture(openGroupDetail) ||
                                    payableBookingRows(openGroupDetail).length ===
                                        0}
                            >
                                <WalletCards class="mr-1 h-3.5 w-3.5" />
                                {markingPaidSeatId !== null &&
                                markingPaidSeatId < 0
                                    ? 'Melunaskan...'
                                    : 'Lunaskan Semua'}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="rounded-full"
                                onclick={() =>
                                    void copyBookingGroup(openGroupDetail!)}
                            >
                                <Copy class="mr-1 h-3.5 w-3.5" />
                                Copy Data
                            </Button>
                        {/if}
                {#if isArrivedDeparture(openGroupDetail) && !isManifestLocked(openGroupDetail)}
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="rounded-full border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-900/40"
                                onclick={() => void closeManifest(openGroupDetail!)}
                            >
                                Close Manifest
                            </Button>
                        {/if}
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="rounded-full"
                            onclick={navigateBackToBookingList}>Kembali</Button
                        >
                    </div>
                </div>

                <div
                    class="mb-4 rounded-[28px] border border-border/80 bg-linear-to-br from-background via-background to-cyan-50/35 p-4 shadow-sm dark:to-cyan-950/15 md:p-5"
                >
                    <div
                        class="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]"
                    >
                        <div class="space-y-4">
                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300"
                                >
                                    Ringkasan Keberangkatan
                                </p>
                                <div
                                    class="mt-3 grid grid-cols-3 gap-1.5 sm:grid-cols-3 xl:grid-cols-6"
                                >
                                    <div
                                        class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2"
                                    >
                                        <p
                                            class="text-[9px] uppercase tracking-[0.1em] text-muted-foreground"
                                        >
                                            Total
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold sm:text-sm"
                                        >
                                            {openGroupDetail.total}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2"
                                    >
                                        <p
                                            class="text-[9px] uppercase tracking-[0.1em] text-muted-foreground"
                                        >
                                            Aktif
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold sm:text-sm"
                                        >
                                            {openGroupDetail.active}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-rose-200/80 bg-rose-50/80 px-2.5 py-2 dark:border-rose-500/20 dark:bg-rose-950/20"
                                    >
                                        <p
                                            class="text-[9px] uppercase tracking-[0.1em] text-rose-700 dark:text-rose-300"
                                        >
                                            Cancel
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold text-rose-700 dark:text-rose-200 sm:text-sm"
                                        >
                                            {openGroupDetail.canceled}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-2.5 py-2 dark:border-emerald-500/20 dark:bg-emerald-950/20"
                                    >
                                        <p
                                            class="text-[9px] uppercase tracking-[0.1em] text-emerald-700 dark:text-emerald-300"
                                        >
                                            Lunas
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold text-emerald-700 dark:text-emerald-200 sm:text-sm"
                                        >
                                            {openGroupDetail.lunas}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-sky-200/80 bg-sky-50/80 px-2.5 py-2 dark:border-sky-500/20 dark:bg-sky-950/20"
                                    >
                                        <p
                                            class="text-[9px] uppercase tracking-[0.1em] text-sky-700 dark:text-sky-300"
                                        >
                                            Refund
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold text-sky-700 dark:text-sky-200 sm:text-sm"
                                        >
                                            {openGroupDetail.refund}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-amber-200/80 bg-amber-50/80 px-2.5 py-2 dark:border-amber-500/20 dark:bg-amber-950/20"
                                    >
                                        <p
                                            class="text-[9px] uppercase tracking-[0.1em] text-amber-700 dark:text-amber-300"
                                        >
                                            Belum Lunas
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold text-amber-700 dark:text-amber-200 sm:text-sm"
                                        >
                                            {openGroupDetail.belum_lunas}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <div
                                        class="rounded-2xl border border-emerald-200/70 bg-emerald-50/70 px-3 py-2.5 dark:border-emerald-500/20 dark:bg-emerald-950/20"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.12em] text-emerald-700 dark:text-emerald-300"
                                        >
                                            Total Pembayaran Lunas
                                        </p>
                                        <p
                                            class="mt-1 text-sm font-semibold text-emerald-700 dark:text-emerald-200"
                                        >
                                            Rp {groupPaymentTotals().lunas.toLocaleString(
                                                'id-ID',
                                            )}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-2xl border border-amber-200/70 bg-amber-50/70 px-3 py-2.5 dark:border-amber-500/20 dark:bg-amber-950/20"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.12em] text-amber-700 dark:text-amber-300"
                                        >
                                            Total Pembayaran Belum Lunas
                                        </p>
                                        <p
                                            class="mt-1 text-sm font-semibold text-amber-700 dark:text-amber-200"
                                        >
                                            Rp {groupPaymentTotals().belumLunas.toLocaleString(
                                                'id-ID',
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300"
                                >
                                    Mapping Keberangkatan
                                </p>
                                <div
                                    class="mt-3 grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]"
                                >
                                    <div class="relative">
                                        <label
                                            for="group-driver-search"
                                            class="mb-1 block text-[11px] font-medium text-muted-foreground"
                                            >Driver</label
                                        >
                                        <div class="relative">
                                            <UserRound
                                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                            />
                                            <Input
                                                id="group-driver-search"
                                                bind:value={groupDriverSearch}
                                                class="h-10 rounded-xl !pl-9 text-sm"
                                                placeholder={loadingGroupDriver
                                                    ? 'Memuat data driver...'
                                                    : 'Cari nama driver'}
                                                oninput={queueGroupDriverSearch}
                                                onfocus={() => {
                                                    groupDriverLookupOpen = true;
                                                }}
                                                onblur={onGroupDriverBlur}
                                                disabled={loadingGroupDriver ||
                                                    savingGroupDriver ||
                                                    isCanceledDeparture(
                                                        openGroupDetail,
                                                    )}
                                            />
                                        </div>

                                        {#if groupDriverLookupOpen}
                                            <div
                                                class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl"
                                            >
                                                {#if loadingGroupDriver}
                                                    <p
                                                        class="px-2 py-2 text-xs text-muted-foreground"
                                                    >
                                                        Memuat driver...
                                                    </p>
                                                {:else if filteredGroupDrivers().length === 0}
                                                    <p
                                                        class="px-2 py-2 text-xs text-muted-foreground"
                                                    >
                                                        Driver tidak ditemukan.
                                                    </p>
                                                {:else}
                                                    <div class="space-y-1">
                                                        {#each filteredGroupDrivers() as driver (`group-driver-${driver.id}`)}
                                                            <button
                                                                type="button"
                                                                class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70 dark:hover:border-cyan-500/20 dark:hover:bg-cyan-950/20"
                                                                onmousedown={(
                                                                    event,
                                                                ) => {
                                                                    event.preventDefault();
                                                                    selectGroupDriver(
                                                                        driver,
                                                                    );
                                                                }}
                                                            >
                                                                <span>
                                                                    <span
                                                                        class="block text-sm font-semibold text-foreground"
                                                                        >{driver.nama}</span
                                                                    >
                                                                    <span
                                                                        class="block text-[11px] text-muted-foreground"
                                                                        >{driver.phone ||
                                                                            'Driver aktif'}</span
                                                                    >
                                                                </span>
                                                            </button>
                                                        {/each}
                                                    </div>
                                                {/if}
                                            </div>
                                        {/if}
                                    </div>

                                    <div class="relative">
                                        <label
                                            for="group-armada-search"
                                            class="mb-1 block text-[11px] font-medium text-muted-foreground"
                                            >Nomor Polisi</label
                                        >
                                        <div class="relative">
                                            <CarFront
                                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                            />
                                            <Input
                                                id="group-armada-search"
                                                bind:value={groupArmadaSearch}
                                                class="h-10 rounded-xl !pl-9 text-sm"
                                                placeholder={loadingGroupArmada
                                                    ? 'Memuat data armada...'
                                                    : 'Cari nopol armada'}
                                                oninput={queueGroupArmadaSearch}
                                                onfocus={() => {
                                                    groupArmadaLookupOpen = true;

                                                    if (
                                                        groupArmadas.length ===
                                                        0
                                                    ) {
                                                        void loadGroupArmadas(
                                                            groupArmadaSearch,
                                                        );
                                                    }
                                                }}
                                                onblur={onGroupArmadaBlur}
                                                disabled={loadingGroupDriver ||
                                                    savingGroupDriver ||
                                                    isCanceledDeparture(
                                                        openGroupDetail,
                                                    )}
                                            />
                                        </div>

                                        {#if groupArmadaLookupOpen}
                                            <div
                                                class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl"
                                            >
                                                {#if loadingGroupArmada}
                                                    <p
                                                        class="px-2 py-2 text-xs text-muted-foreground"
                                                    >
                                                        Memuat armada...
                                                    </p>
                                                {:else if filteredGroupArmadas().length === 0}
                                                    <p
                                                        class="px-2 py-2 text-xs text-muted-foreground"
                                                    >
                                                        Armada tidak ditemukan.
                                                    </p>
                                                {:else}
                                                    <div class="space-y-1">
                                                        {#each filteredGroupArmadas() as armada (`group-armada-${armada.id}`)}
                                                            <button
                                                                type="button"
                                                                class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70 dark:hover:border-cyan-500/20 dark:hover:bg-cyan-950/20"
                                                                onmousedown={(
                                                                    event,
                                                                ) => {
                                                                    event.preventDefault();
                                                                    selectGroupArmada(
                                                                        armada,
                                                                    );
                                                                }}
                                                            >
                                                                <span>
                                                                    <span
                                                                        class="block text-sm font-semibold text-foreground"
                                                                        >{armada.nopol}</span
                                                                    >
                                                                    <span
                                                                        class="block text-[11px] text-muted-foreground"
                                                                        >{[
                                                                            armada.merk,
                                                                            armada.kategori,
                                                                            armada.tahun,
                                                                        ]
                                                                            .filter(
                                                                                Boolean,
                                                                            )
                                                                            .join(
                                                                                ' • ',
                                                                            ) ||
                                                                            'Armada aktif'}</span
                                                                    >
                                                                </span>
                                                            </button>
                                                        {/each}
                                                    </div>
                                                {/if}
                                            </div>
                                        {/if}
                                    </div>

                                    <div class="flex items-end">
                                        <Button
                                            type="button"
                                            class="h-10 rounded-xl px-4"
                                            onclick={() =>
                                                void saveGroupDriverMapping()}
                                            disabled={loadingGroupDriver ||
                                                savingGroupDriver ||
                                                isCanceledDeparture(
                                                    openGroupDetail,
                                                )}
                                        >
                                            {savingGroupDriver
                                                ? hasSavedGroupDriverMapping()
                                                    ? 'Mengedit...'
                                                    : 'Menyimpan...'
                                                : hasSavedGroupDriverMapping()
                                                  ? 'Edit'
                                                  : 'Simpan'}
                                        </Button>
                                    </div>
                                </div>

                                {#if canMarkDepartureDeparted(openGroupDetail) || canMarkDepartureArrived(openGroupDetail)}
                                    <div
                                        class="mt-3 flex flex-wrap items-center gap-2 rounded-2xl border border-emerald-200/70 bg-emerald-50/70 p-3 dark:border-emerald-500/20 dark:bg-emerald-950/20"
                                    >
                                        {#if canMarkDepartureDeparted(openGroupDetail)}
                                            <Button
                                                type="button"
                                                class="h-10 rounded-xl bg-amber-600 px-4 text-white hover:bg-amber-700"
                                                onclick={() =>
                                                    openGroupDetail &&
                                                    void markDepartureDeparted(
                                                        openGroupDetail,
                                                    )}
                                                disabled={cancelingDepartureKey ===
                                                    openGroupDetail?.key}
                                            >
                                                <BusFront
                                                    class="mr-1.5 h-4 w-4"
                                                />
                                                {cancelingDepartureKey ===
                                                openGroupDetail?.key
                                                    ? 'Memproses...'
                                                    : 'Armada Sudah Berangkat'}
                                            </Button>
                                        {/if}
                                        {#if canMarkDepartureArrived(openGroupDetail)}
                                            <Button
                                                type="button"
                                                class="h-10 rounded-xl bg-emerald-600 px-4 text-white hover:bg-emerald-700"
                                                onclick={() =>
                                                    openGroupDetail &&
                                                    void markDepartureArrived(
                                                        openGroupDetail,
                                                    )}
                                                disabled={cancelingDepartureKey ===
                                                    openGroupDetail?.key}
                                            >
                                                <CheckCircle2
                                                    class="mr-1.5 h-4 w-4"
                                                />
                                                {cancelingDepartureKey ===
                                                openGroupDetail?.key
                                                    ? 'Memproses...'
                                                    : 'Armada Sudah Tiba'}
                                            </Button>
                                        {/if}
                                        <p
                                            class="text-xs leading-relaxed text-emerald-800 dark:text-emerald-100"
                                        >
                                            {#if canMarkDepartureDeparted(openGroupDetail)}
                                                Aksi berangkat tersedia untuk
                                                keberangkatan hari ini atau
                                                sebelumnya setelah driver dan
                                                nopol dipilih.
                                            {:else}
                                                Armada sudah berangkat. Sekarang
                                                aksi tiba sudah bisa dipakai.
                                            {/if}
                                        </p>
                                    </div>
                                {/if}
                            </div>
                        </div>

                        <div
                            class="rounded-[24px] border border-cyan-200/70 bg-background/90 p-4 dark:border-cyan-500/20"
                        >
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300"
                            >
                                Info Perjalanan
                            </p>
                            <div class="mt-3 space-y-2">
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/80 px-3 py-3"
                                >
                                    <p
                                        class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                    >
                                        Kode
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold break-all"
                                    >
                                        {openGroupDetail.departure_code}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/80 px-3 py-3"
                                >
                                    <p
                                        class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                    >
                                        Jadwal
                                    </p>
                                    <p class="mt-1 text-sm font-semibold">
                                        {formatGroupDateLabel(
                                            openGroupDetail.tanggal,
                                        )} • {formatGroupTimeLabel(
                                            openGroupDetail.jam,
                                        )}
                                    </p>
                                </div>
                                {#if !consoleOnly}
                                    <div
                                        class="rounded-2xl border border-border/70 bg-background/80 px-3 py-3"
                                    >
                                        <p
                                            class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                        >
                                            Nilai sudah lunas
                                        </p>
                                        <p class="mt-1 text-sm font-semibold">
                                            {formatCurrency(
                                                bookingGroupPaidAmount(
                                                    openGroupDetail,
                                                ),
                                            )}
                                        </p>
                                    </div>
                                {/if}
                                <div
                                    class="rounded-2xl border border-border/70 bg-background/80 px-3 py-3"
                                >
                                    <p
                                        class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                    >
                                        Rute
                                    </p>
                                    <p class="mt-1 text-sm font-semibold">
                                        {openGroupDetail.rute}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="mb-3 flex flex-wrap items-center justify-between gap-2"
                >
                    <div
                        class="inline-flex rounded-full border border-border/70 bg-muted/30 p-1"
                    >
                        <button
                            type="button"
                            class={`rounded-full px-4 py-1.5 text-sm transition ${groupPassengerTab === 'active' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'}`}
                            onclick={() => {
                                groupPassengerTab = 'active';
                            }}
                        >
                            Penumpang {openGroupDetail.active}
                        </button>
                        <button
                            type="button"
                            class={`rounded-full px-4 py-1.5 text-sm transition ${groupPassengerTab === 'ritur' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'}`}
                            onclick={() => {
                                groupPassengerTab = 'ritur';
                            }}
                        >
                            Bagasi {groupMappedRiturs.length}
                        </button>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        {groupPassengerTab === 'active'
                            ? 'Daftar penumpang aktif pada keberangkatan ini.'
                            : `Bagasi dengan status ${luggageReceivedStatus} dan rute yang sama muncul otomatis agar lebih cepat dimapping.`}
                    </p>
                </div>

                {#if groupPassengerTab === 'ritur'}
                    <div
                        class="grid gap-4 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]"
                    >
                        <div
                            class="rounded-[24px] border border-border/80 bg-card/95 p-4 shadow-sm"
                        >
                            <div
                                class="mb-3 flex flex-wrap items-start justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300"
                                    >
                                        Bagasi Terpasang
                                    </p>
                                    <p
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        Bagasi yang sudah dimapping ke
                                        keberangkatan ini.
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-3 py-2 text-right dark:border-emerald-500/20 dark:bg-emerald-950/20"
                                >
                                    <p
                                        class="text-[10px] uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300"
                                    >
                                        Revenue Bagasi
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-emerald-700 dark:text-emerald-200"
                                    >
                                        {formatCurrency(
                                            groupMappedRiturRevenue(),
                                        )}
                                    </p>
                                </div>
                            </div>

                            {#if filteredMappedRiturs().length === 0}
                                <div
                                    class="rounded-2xl border border-dashed border-border/80 bg-muted/20 px-4 py-8 text-center text-sm text-muted-foreground"
                                >
                                    Belum ada bagasi yang terpasang pada
                                    keberangkatan ini.
                                </div>
                            {:else}
                                <div class="space-y-2">
                                    {#each filteredMappedRiturs() as row (row.id)}
                                        <div
                                            class="rounded-2xl border border-border/70 bg-background/85 p-3"
                                        >
                                            <div
                                                class="flex flex-wrap items-start justify-between gap-3"
                                            >
                                                <div class="min-w-0">
                                                    <p
                                                        class="text-sm font-semibold text-foreground"
                                                    >
                                                        {row.kode_resi ||
                                                            `Bagasi ${row.id}`}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-xs text-muted-foreground"
                                                    >
                                                        {row.sender_name || '-'} →
                                                        {row.receiver_name ||
                                                            '-'}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-[11px] text-muted-foreground"
                                                    >
                                                        {row.rute || '-'} • {row.tanggal ||
                                                            '-'}
                                                    </p>
                                                </div>
                                                <div
                                                    class="flex flex-col items-end gap-1"
                                                >
                                                    <Badge
                                                        variant="secondary"
                                                        class="rounded-full px-2 py-0 text-[10px]"
                                                        >{luggageStatusLabel(
                                                            row.status,
                                                        )}</Badge
                                                    >
                                                    <Badge
                                                        variant="secondary"
                                                        class="rounded-full px-2 py-0 text-[10px]"
                                                        >{row.payment_status ||
                                                            '-'}</Badge
                                                    >
                                                </div>
                                            </div>
                                            <div
                                                class="mt-3 flex flex-wrap items-center justify-between gap-2"
                                            >
                                                <div
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    <span
                                                        class="font-medium text-foreground"
                                                        >Qty {Number(
                                                            row.quantity || 0,
                                                        )}</span
                                                    >
                                                    <span class="mx-1.5">•</span
                                                    >
                                                    <span
                                                        class="font-medium text-foreground"
                                                        >{formatCurrency(
                                                            Number(
                                                                row.price || 0,
                                                            ),
                                                        )}</span
                                                    >
                                                </div>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="outline"
                                                    class="rounded-full"
                                                    onclick={() =>
                                                        void unmapGroupRitur(
                                                            row,
                                                        )}
                                                    disabled={savingGroupRiturId ===
                                                        row.id}
                                                >
                                                    {savingGroupRiturId ===
                                                    row.id
                                                        ? 'Melepas...'
                                                        : 'Lepas'}
                                                </Button>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>

                        <div
                            class="rounded-[24px] border border-border/80 bg-card/95 p-4 shadow-sm"
                        >
                            <div
                                class="mb-3 flex flex-wrap items-center justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-300"
                                    >
                                        Bagasi Siap Dimapping
                                    </p>
                                    <p
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        Hanya bagasi berstatus {luggageReceivedStatus}
                                        dengan rute yang sama dan belum dipasang ke
                                        keberangkatan lain.
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex md:hidden">
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            class="h-8 rounded-full px-3 text-xs"
                                            onclick={() =>
                                                (groupRiturFiltersExpanded =
                                                    !groupRiturFiltersExpanded)}
                                            aria-expanded={groupRiturFiltersExpanded}
                                        >
                                            {groupRiturFiltersExpanded
                                                ? 'Sembunyikan Filter'
                                                : 'Tampilkan Filter'}
                                        </Button>
                                    </div>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        class="rounded-full"
                                        onclick={() =>
                                            void loadGroupRiturs(
                                                openGroupDetail!,
                                                groupRiturSearch,
                                            )}
                                        disabled={loadingGroupRiturs}
                                    >
                                        {loadingGroupRiturs
                                            ? 'Memuat...'
                                            : 'Refresh'}
                                    </Button>
                                </div>
                            </div>

                            <div
                                class={groupRiturFiltersExpanded
                                    ? 'block'
                                    : 'hidden md:block'}
                            >
                                <div class="mb-3">
                                    <div class="relative">
                                        <Search
                                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                        />
                                        <Input
                                            bind:value={groupRiturSearch}
                                            class="h-10 rounded-xl !pl-9 text-sm"
                                            placeholder="Cari resi, pengirim, penerima, nomor HP, atau rute"
                                            oninput={queueGroupRiturSearch}
                                        />
                                    </div>
                                </div>

                                <div class="mb-3 flex flex-wrap gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={groupRiturFilter === 'all'
                                            ? 'default'
                                            : 'outline'}
                                        class="rounded-full"
                                        onclick={() =>
                                            (groupRiturFilter = 'all')}
                                    >
                                        Semua
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={groupRiturFilter ===
                                        'same_route'
                                            ? 'default'
                                            : 'outline'}
                                        class="rounded-full"
                                        onclick={() =>
                                            (groupRiturFilter = 'same_route')}
                                    >
                                        Rute Sama
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={groupRiturFilter ===
                                        'same_date'
                                            ? 'default'
                                            : 'outline'}
                                        class="rounded-full"
                                        onclick={() =>
                                            (groupRiturFilter = 'same_date')}
                                    >
                                        Tanggal Sama
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={groupRiturFilter ===
                                        'same_route_date'
                                            ? 'default'
                                            : 'outline'}
                                        class="rounded-full"
                                        onclick={() =>
                                            (groupRiturFilter =
                                                'same_route_date')}
                                    >
                                        Rute + Tanggal
                                    </Button>
                                </div>

                                <div
                                    class="mb-3 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground"
                                >
                                    <Badge
                                        variant="secondary"
                                        class="rounded-full px-2.5 py-0.5 text-[11px]"
                                    >
                                        Rute {openGroupDetail.rute}
                                    </Badge>
                                    <Badge
                                        variant="secondary"
                                        class="rounded-full px-2.5 py-0.5 text-[11px]"
                                    >
                                        Tanggal {formatGroupDateLabel(
                                            openGroupDetail.tanggal,
                                        )}
                                    </Badge>
                                </div>
                            </div>

                            {#if filteredAvailableRiturs().length === 0}
                                <div
                                    class="rounded-2xl border border-dashed border-border/80 bg-muted/20 px-4 py-8 text-center text-sm text-muted-foreground"
                                >
                                    {loadingGroupRiturs
                                        ? 'Memuat daftar bagasi...'
                                        : `Belum ada bagasi dengan status ${luggageReceivedStatus} dan rute yang sama.`}
                                </div>
                            {:else}
                                <div class="space-y-2">
                                    {#each filteredAvailableRiturs() as row (row.id)}
                                        <div
                                            class="rounded-2xl border border-border/70 bg-background/85 p-3"
                                        >
                                            <div
                                                class="flex flex-wrap items-start justify-between gap-3"
                                            >
                                                <div class="min-w-0">
                                                    <p
                                                        class="text-sm font-semibold text-foreground"
                                                    >
                                                        {row.kode_resi ||
                                                            `Bagasi ${row.id}`}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-xs text-muted-foreground"
                                                    >
                                                        {row.sender_name || '-'} →
                                                        {row.receiver_name ||
                                                            '-'}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-[11px] text-muted-foreground"
                                                    >
                                                        {row.rute || '-'} • {row.tanggal ||
                                                            '-'}
                                                    </p>
                                                </div>
                                                <div
                                                    class="flex flex-col items-end gap-1"
                                                >
                                                    <Badge
                                                        variant="secondary"
                                                        class="rounded-full px-2 py-0 text-[10px]"
                                                        >{luggageStatusLabel(
                                                            row.status,
                                                        )}</Badge
                                                    >
                                                    <Badge
                                                        variant="secondary"
                                                        class="rounded-full px-2 py-0 text-[10px]"
                                                        >{row.payment_status ||
                                                            '-'}</Badge
                                                    >
                                                </div>
                                            </div>
                                            <div
                                                class="mt-3 flex flex-wrap items-center justify-between gap-2"
                                            >
                                                <div
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    <span
                                                        class="font-medium text-foreground"
                                                        >Qty {Number(
                                                            row.quantity || 0,
                                                        )}</span
                                                    >
                                                    <span class="mx-1.5">•</span
                                                    >
                                                    <span
                                                        class="font-medium text-foreground"
                                                        >{formatCurrency(
                                                            Number(
                                                                row.price || 0,
                                                            ),
                                                        )}</span
                                                    >
                                                </div>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    class="rounded-full"
                                                    onclick={() =>
                                                        void mapGroupRitur(row)}
                                                    disabled={savingGroupRiturId ===
                                                        row.id}
                                                >
                                                    {savingGroupRiturId ===
                                                    row.id
                                                        ? 'Memasang...'
                                                        : 'Pasang ke Keberangkatan'}
                                                </Button>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    </div>
                {:else}
                    <div class="space-y-2 md:hidden">
                        {#if visibleGroupBookings().length === 0}
                            <div
                                class="rounded-2xl border border-dashed border-border/80 bg-muted/20 px-4 py-6 text-center text-sm text-muted-foreground"
                            >
                                Belum ada penumpang aktif pada keberangkatan
                                ini.
                            </div>
                        {/if}
                        {#each visibleGroupBookings() as row (row.id)}
                            <div
                                class="rounded-2xl border border-border/80 bg-linear-to-br from-background via-background to-cyan-50/25 p-3.5 shadow-xs dark:to-cyan-950/15"
                            >
                                <div
                                    class="mb-2.5 flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <p class="text-sm font-semibold">
                                            Kursi {row.seat} • {row.name}
                                        </p>
                                        <p
                                            class="text-[11px] text-muted-foreground"
                                        >
                                            {row.phone || '-'} • {row.ticket_code ||
                                                '-'}
                                        </p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div
                                            class="flex flex-col items-end gap-1"
                                        >
                                            <Badge
                                                variant={statusVariant(
                                                    row.status,
                                                )}
                                                class="rounded-full px-2 py-0 text-[11px]"
                                                >{row.status}</Badge
                                            >
                                            <Badge
                                                variant={paymentVariant(
                                                    row.pembayaran,
                                                )}
                                                class="rounded-full px-2 py-0 text-[11px]"
                                                >{row.pembayaran}</Badge
                                            >
                                        </div>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                {#snippet children(props)}
                                                    <Button
                                                        type="button"
                                                        size="icon"
                                                        variant="outline"
                                                        class="h-8 w-8 rounded-full"
                                                        onclick={props.onclick}
                                                        aria-expanded={props[
                                                            'aria-expanded'
                                                        ]}
                                                        data-state={props[
                                                            'data-state'
                                                        ]}
                                                    >
                                                        <MoreVertical
                                                            class="h-4 w-4"
                                                        />
                                                    </Button>
                                                {/snippet}
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                sideOffset={6}
                                                class="z-[120] w-44 text-[11px] shadow-2xl"
                                            >
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        void copyBookingPassenger(
                                                            openGroupDetail!,
                                                            row,
                                                        )}
                                                >
                                                    Copy Data
                                                </DropdownMenuItem>
                                                {#if !isManifestLocked(openGroupDetail)}
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            void openGroupRowEdit(
                                                                openGroupDetail!,
                                                                row,
                                                            )}
                                                    >
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            void openGroupRowReschedule(
                                                                openGroupDetail!,
                                                                row,
                                                            )}
                                                    >
                                                        Reschedule
                                                    </DropdownMenuItem>
                                                {/if}
                                                {#if !isManifestLocked(openGroupDetail) && !isSettledPayment(row.pembayaran)}
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            void markBookingRowAsPaid(
                                                                row.id,
                                                                row.seat,
                                                                row.pembayaran,
                                                            )}
                                                    >
                                                        Lunas
                                                    </DropdownMenuItem>
                                                {/if}
                                                {#if !isManifestLocked(openGroupDetail) && canRefundCanceledBooking(row)}
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            void markBookingRowAsRefund(
                                                                row.id,
                                                                row.seat,
                                                                row.pembayaran,
                                                            )}
                                                    >
                                                        Refund
                                                    </DropdownMenuItem>
                                                {/if}
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        openTicketPrint(row.id)}
                                                >
                                                    Print Tiket
                                                </DropdownMenuItem>
                                                {#if !isManifestLocked(openGroupDetail)}
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            void cancelBookingRow(
                                                                row.id,
                                                                row.seat,
                                                                row.status,
                                                            )}
                                                    >
                                                        Cancel
                                                    </DropdownMenuItem>
                                                {/if}
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>

                                <div
                                    class="grid gap-2 rounded-xl border border-border/70 bg-background/80 p-3 text-xs text-muted-foreground"
                                >
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <p
                                                class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                            >
                                                Jemput
                                            </p>
                                            <p
                                                class="mt-1 font-medium text-foreground"
                                            >
                                                {row.pickup_point || '-'}
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                            >
                                                Segment
                                            </p>
                                            <p
                                                class="mt-1 font-medium text-foreground"
                                            >
                                                {row.segment_name || '-'}
                                            </p>
                                        </div>
                                    </div>
                                    {#if row.gmaps}
                                        <p class="truncate">
                                            <span
                                                class="font-medium text-foreground"
                                                >Gmaps:</span
                                            >
                                            {row.gmaps}
                                        </p>
                                    {/if}
                                </div>

                                <div
                                    class="mt-2 grid grid-cols-3 gap-1 rounded-xl border border-border/70 bg-background/80 p-1.5 text-[11px]"
                                >
                                    <div
                                        class="rounded-lg bg-muted/35 px-2 py-1"
                                    >
                                        <span class="text-muted-foreground"
                                            >Harga</span
                                        >
                                        <span
                                            class="block font-semibold text-foreground"
                                            >Rp {Number(
                                                row.price || 0,
                                            ).toLocaleString('id-ID')}</span
                                        >
                                    </div>
                                    <div
                                        class="rounded-lg bg-muted/35 px-2 py-1"
                                    >
                                        <span class="text-muted-foreground"
                                            >Diskon</span
                                        >
                                        <span
                                            class="block font-semibold text-foreground"
                                            >Rp {Number(
                                                row.discount || 0,
                                            ).toLocaleString('id-ID')}</span
                                        >
                                    </div>
                                    <div
                                        class="rounded-lg bg-cyan-50 px-2 py-1 text-cyan-800 dark:bg-cyan-950/30 dark:text-cyan-200"
                                    >
                                        <span>Total</span>
                                        <span class="block font-semibold"
                                            >Rp {Math.max(
                                                Number(row.price || 0) -
                                                    Number(row.discount || 0),
                                                0,
                                            ).toLocaleString('id-ID')}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>

                    <div
                        class="hidden overflow-visible rounded-2xl border border-border/80 bg-card/95 md:block"
                    >
                        <table class="w-full min-w-[980px] text-xs">
                            <thead>
                                <tr
                                    class="border-b bg-muted/45 text-left text-[11px] tracking-wide text-muted-foreground uppercase"
                                >
                                    <th class="px-2 py-3">Aksi</th>
                                    <th class="px-3 py-3">Seat</th>
                                    <th class="px-3 py-3">Penumpang</th>
                                    <th class="px-3 py-3">Operasional</th>
                                    <th class="px-3 py-3">Tarif</th>
                                    <th class="px-3 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#if visibleGroupBookings().length === 0}
                                    <tr>
                                        <td
                                            colspan="6"
                                            class="px-3 py-6 text-center text-sm text-muted-foreground"
                                        >
                                            Belum ada penumpang aktif pada
                                            keberangkatan ini.
                                        </td>
                                    </tr>
                                {/if}
                                {#each visibleGroupBookings() as row (row.id)}
                                    <tr
                                        class="border-b/70 align-top transition-colors hover:bg-muted/20"
                                    >
                                        <td class="px-2 py-3">
                                            <div
                                                class="flex flex-col items-center gap-2"
                                            >
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
                                                        {#snippet children(
                                                            props,
                                                        )}
                                                            <Button
                                                                type="button"
                                                                size="icon"
                                                                variant="outline"
                                                                class="h-7 w-7 rounded-full"
                                                                onclick={props.onclick}
                                                                aria-expanded={props[
                                                                    'aria-expanded'
                                                                ]}
                                                                data-state={props[
                                                                    'data-state'
                                                                ]}
                                                            >
                                                                <MoreVertical
                                                                    class="h-3.5 w-3.5"
                                                                />
                                                            </Button>
                                                        {/snippet}
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="start"
                                                        side="right"
                                                        sideOffset={6}
                                                        class="z-[120] w-44 text-[11px] shadow-2xl"
                                                    >
                                                        <DropdownMenuItem
                                                            onclick={() =>
                                                                void copyBookingPassenger(
                                                                    openGroupDetail!,
                                                                    row,
                                                                )}
                                                        >
                                                            Copy Data
                                                        </DropdownMenuItem>
                                                        {#if !isManifestLocked(openGroupDetail)}
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void openGroupRowEdit(
                                                                        openGroupDetail!,
                                                                        row,
                                                                    )}
                                                            >
                                                                Edit
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void openGroupRowReschedule(
                                                                        openGroupDetail!,
                                                                        row,
                                                                    )}
                                                            >
                                                                Reschedule
                                                            </DropdownMenuItem>
                                                            {#if !isSettledPayment(row.pembayaran)}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void markBookingRowAsPaid(
                                                                            row.id,
                                                                            row.seat,
                                                                            row.pembayaran,
                                                                        )}
                                                                >
                                                                    Lunas
                                                                </DropdownMenuItem>
                                                            {/if}
                                                            {#if canRefundCanceledBooking(row)}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void markBookingRowAsRefund(
                                                                            row.id,
                                                                            row.seat,
                                                                            row.pembayaran,
                                                                        )}
                                                                >
                                                                    Refund
                                                                </DropdownMenuItem>
                                                            {/if}
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void cancelBookingRow(
                                                                        row.id,
                                                                        row.seat,
                                                                        row.status,
                                                                    )}
                                                            >
                                                                Cancel
                                                            </DropdownMenuItem>
                                                        {/if}
                                                        <DropdownMenuItem
                                                            onclick={() =>
                                                                openTicketPrint(
                                                                    row.id,
                                                                )}
                                                        >
                                                            Print Tiket
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div
                                                class="inline-flex min-w-11 items-center justify-center rounded-2xl border border-cyan-200/70 bg-cyan-50/80 px-3 py-2 text-sm font-semibold text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-950/20 dark:text-cyan-200"
                                            >
                                                {row.seat}
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <p
                                                class="text-sm font-semibold leading-tight"
                                            >
                                                {row.name || '-'}
                                            </p>
                                            <p
                                                class="mt-1 text-[11px] text-muted-foreground"
                                            >
                                                {row.phone || '-'}
                                            </p>
                                            <div
                                                class="mt-2 inline-flex rounded-full border border-border/70 bg-background/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-muted-foreground"
                                            >
                                                {row.ticket_code || '-'}
                                            </div>
                                        </td>
                                        <td
                                            class="px-3 py-3 text-[11px] leading-relaxed"
                                        >
                                            <div
                                                class="grid gap-2 rounded-2xl border border-border/70 bg-background/80 p-3"
                                            >
                                                <div
                                                    class="grid grid-cols-2 gap-2"
                                                >
                                                    <div>
                                                        <p
                                                            class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                                        >
                                                            Jemput
                                                        </p>
                                                        <p
                                                            class="mt-1 font-medium text-foreground"
                                                        >
                                                            {row.pickup_point ||
                                                                '-'}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground"
                                                        >
                                                            Segment
                                                        </p>
                                                        <p
                                                            class="mt-1 font-medium text-foreground"
                                                        >
                                                            {row.segment_name ||
                                                                '-'}
                                                        </p>
                                                    </div>
                                                </div>
                                                {#if row.gmaps}
                                                    <p
                                                        class="max-w-[280px] truncate text-muted-foreground"
                                                    >
                                                        <span
                                                            class="font-medium text-foreground"
                                                            >Gmaps:</span
                                                        >
                                                        {row.gmaps}
                                                    </p>
                                                {/if}
                                            </div>
                                        </td>
                                        <td
                                            class="px-2 py-3 text-[11px] leading-relaxed"
                                        >
                                            <div
                                                class="min-w-40 rounded-xl border border-border/70 bg-background/80 p-2"
                                            >
                                                <div
                                                    class="flex items-center justify-between gap-2"
                                                >
                                                    <span
                                                        class="text-muted-foreground"
                                                        >Harga</span
                                                    >
                                                    <span
                                                        class="font-medium text-foreground"
                                                        >Rp {Number(
                                                            row.price || 0,
                                                        ).toLocaleString(
                                                            'id-ID',
                                                        )}</span
                                                    >
                                                </div>
                                                <div
                                                    class="mt-0.5 flex items-center justify-between gap-2"
                                                >
                                                    <span
                                                        class="text-muted-foreground"
                                                        >Diskon</span
                                                    >
                                                    <span
                                                        class="font-medium text-foreground"
                                                        >Rp {Number(
                                                            row.discount || 0,
                                                        ).toLocaleString(
                                                            'id-ID',
                                                        )}</span
                                                    >
                                                </div>
                                                <div
                                                    class="mt-1 flex items-center justify-between gap-2 rounded-lg bg-cyan-50 px-2 py-1 dark:bg-cyan-950/25"
                                                >
                                                    <span
                                                        class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted-foreground"
                                                        >Total</span
                                                    >
                                                    <span
                                                        class="text-xs font-semibold text-foreground"
                                                        >Rp {Math.max(
                                                            Number(
                                                                row.price || 0,
                                                            ) -
                                                                Number(
                                                                    row.discount ||
                                                                        0,
                                                                ),
                                                            0,
                                                        ).toLocaleString(
                                                            'id-ID',
                                                        )}</span
                                                    >
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div
                                                class="flex flex-col items-start gap-1"
                                            >
                                                <Badge
                                                    variant={statusVariant(
                                                        row.status,
                                                    )}
                                                    class="rounded-full px-2 py-0 text-[10px]"
                                                    >{row.status}</Badge
                                                >
                                                <Badge
                                                    variant={paymentVariant(
                                                        row.pembayaran,
                                                    )}
                                                    class="rounded-full px-2 py-0 text-[10px]"
                                                    >{row.pembayaran}</Badge
                                                >
                                            </div>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>
        {:else}
            <div
                class="rounded-2xl border border-border/70 bg-background p-4 shadow-sm"
            >
                <p class="text-sm text-muted-foreground">
                    Detail keberangkatan tidak ditemukan atau sudah berubah.
                </p>
                <div class="mt-3">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onclick={navigateBackToBookingList}
                        >Kembali ke Data Keberangkatan</Button
                    >
                </div>
            </div>
        {/if}
    {/if}

    {#if listOnly && groupEditModalOpen}
        {#if GroupEditModalComponent}
            <GroupEditModalComponent
                bind:groupEditSeat
                bind:groupEditPayment
                bind:groupEditName
                bind:groupEditPhone
                bind:groupEditPickupPoint
                bind:groupEditDiscount
                {groupEditRoute}
                {groupEditDate}
                {groupEditJam}
                {groupEditUnit}
                {groupEditSeatOptions}
                {paymentOptions}
                {loadingGroupEditSeats}
                {savingGroupRowEdit}
                {formError}
                {formatGroupDateLabel}
                {formatGroupTimeLabel}
                {groupEditSeatLabel}
                {groupEditSeatHelpText}
                {closeGroupRowEdit}
                {saveGroupRowEdit}
            />
        {:else}
            <div
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4"
                role="dialog"
                aria-modal="true"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-border/80 bg-background p-6 text-center shadow-2xl"
                >
                    <p class="text-sm font-medium text-foreground">
                        Memuat editor penumpang...
                    </p>
                </div>
            </div>
        {/if}
    {/if}

    {#if listOnly && groupRescheduleModalOpen}
        {#if GroupRescheduleModalComponent}
            <GroupRescheduleModalComponent
                bind:groupRescheduleDate
                bind:groupRescheduleJam
                bind:groupRescheduleUnit
                bind:groupRescheduleSeat
                {groupRescheduleBookingName}
                {groupRescheduleRoute}
                {groupRescheduleCurrentDate}
                {groupRescheduleCurrentJam}
                {groupRescheduleCurrentUnit}
                {groupRescheduleCurrentSeat}
                {groupRescheduleSchedules}
                {groupRescheduleSeatOptions}
                {loadingGroupRescheduleSchedules}
                {loadingGroupRescheduleSeats}
                {savingGroupReschedule}
                {formError}
                {formatGroupDateLabel}
                {formatGroupTimeLabel}
                {groupRescheduleUnitOptions}
                {groupRescheduleSeatLabel}
                {groupRescheduleSeatHelpText}
                {onGroupRescheduleDateChange}
                {onGroupRescheduleScheduleChange}
                {loadGroupRescheduleSeatOptions}
                {closeGroupRescheduleModal}
                {saveGroupReschedule}
            />
        {:else}
            <div
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4"
                role="dialog"
                aria-modal="true"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-border/80 bg-background p-6 text-center shadow-2xl"
                >
                    <p class="text-sm font-medium text-foreground">
                        Memuat form reschedule...
                    </p>
                </div>
            </div>
        {/if}
    {/if}

    {#if !consoleOnly && !listOnly}
        <Card class="border-sidebar-border/70 dark:border-sidebar-border">
            <CardHeader>
                <CardTitle>Migration Checklist</CardTitle>
                <CardDescription>
                    Fondasi modul booking untuk perpindahan dari aplikasi lama.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ul
                    class="list-disc space-y-2 pl-5 text-sm text-muted-foreground"
                >
                    {#each migrationChecklist as item (item)}
                        <li>{item}</li>
                    {/each}
                </ul>
            </CardContent>
        </Card>
    {/if}

    {#if !consoleOnly && !groupDetailPage}
        <Card class="border-sidebar-border/70 dark:border-sidebar-border">
            <CardHeader>
                <CardTitle
                    >{listOnly
                        ? 'Keberangkatan'
                        : 'Keberangkatan Terbaru'}</CardTitle
                >
                {#if !listOnly}
                    <CardDescription>
                        Preview data keberangkatan terbaru dari tabel
                        `bookings`.
                    </CardDescription>
                {/if}
            </CardHeader>
            <CardContent>
                {#if listOnly}
                    <div
                        class="sticky top-0 z-20 -mx-3 mb-4 space-y-3 border-b border-border/70 bg-background/95 px-3 py-3 backdrop-blur supports-[backdrop-filter]:bg-background/90 md:top-2 md:mx-0 md:rounded-2xl md:border md:border-border/80 md:bg-linear-to-br md:from-background md:via-background md:to-cyan-50/35 md:p-3 md:shadow-sm md:dark:to-cyan-950/15"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] border border-border/70 bg-[linear-gradient(135deg,rgba(8,145,178,0.07),rgba(15,23,42,0.02))] px-3 py-3 shadow-sm"
                        >
                            <Button
                                type="button"
                                size="sm"
                                variant={emptyDepartureOpen
                                    ? 'secondary'
                                    : 'default'}
                                class="h-9 rounded-xl px-4 text-xs"
                                onclick={() => void openEmptyDepartureForm()}
                            >
                                {#if emptyDepartureOpen}
                                    <X class="mr-1.5 h-3.5 w-3.5" />
                                    Tutup
                                {:else}
                                    <Plus class="mr-1.5 h-3.5 w-3.5" />
                                    Tambah Jadwal
                                {/if}
                            </Button>
                        </div>
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-border/70 bg-muted/10 px-3 py-2.5 shadow-sm"
                        >
                            <div class="space-y-1">
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Tanggal aktif {bookingListDateFrom}
                                </p>
                                <div
                                    class="flex flex-wrap items-center gap-1.5 text-[11px]"
                                >
                                    <span
                                        class="rounded-full border border-border/70 bg-background px-2 py-0.5 font-medium text-muted-foreground"
                                    >
                                        {bookingListSummary.schedules} jadwal
                                    </span>
                                    <span
                                        class="rounded-full border border-border/70 bg-background px-2 py-0.5 font-medium text-muted-foreground"
                                    >
                                        {bookingListSummary.activePassengers} penumpang
                                        aktif
                                    </span>
                                    <span
                                        class="rounded-full border border-amber-200/80 bg-amber-50 px-2 py-0.5 font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-950/20 dark:text-amber-200"
                                    >
                                        {bookingListSummary.unpaidSchedules} jadwal
                                        belum lunas
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <div
                                    class="hidden items-center rounded-xl border border-border/70 bg-background/80 p-1 lg:inline-flex"
                                >
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={bookingListDesktopView ===
                                        'sheet'
                                            ? 'default'
                                            : 'ghost'}
                                        class="h-7 gap-1.5 rounded-lg px-2.5 text-[11px]"
                                        onclick={() =>
                                            setBookingListDesktopView('sheet')}
                                    >
                                        <Rows3 class="h-3.5 w-3.5" />
                                        Sheet
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={bookingListDesktopView ===
                                        'cards'
                                            ? 'default'
                                            : 'ghost'}
                                        class="h-7 gap-1.5 rounded-lg px-2.5 text-[11px]"
                                        onclick={() =>
                                            setBookingListDesktopView('cards')}
                                    >
                                        <LayoutGrid class="h-3.5 w-3.5" />
                                        Card
                                    </Button>
                                </div>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="h-8 rounded-full px-3 text-xs md:hidden"
                                    onclick={() =>
                                        (bookingListFiltersExpanded =
                                            !bookingListFiltersExpanded)}
                                    aria-expanded={bookingListFiltersExpanded}
                                >
                                    <ListFilter class="mr-1.5 h-3.5 w-3.5" />
                                    {bookingListFiltersExpanded
                                        ? 'Sembunyikan Filter'
                                        : 'Tampilkan Filter'}
                                </Button>
                            </div>
                        </div>
                        <div
                            class={`${bookingListFiltersExpanded ? 'block' : 'hidden'} rounded-2xl border border-border/70 bg-muted/10 p-2.5 shadow-sm md:block md:rounded-none md:border-0 md:bg-transparent md:p-0 md:shadow-none`}
                        >
                            <div
                                class="grid gap-2 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]"
                            >
                                <select
                                    class="flex h-10 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm md:h-9"
                                    bind:value={bookingListRoute}
                                >
                                    <option value="all">Semua Rute</option>
                                    {#each bookingListRoutes() as route (`booking-route-filter-${route}`)}
                                        <option value={route}>{route}</option>
                                    {/each}
                                </select>
                                <input
                                    bind:this={bookingListDateInput}
                                    type="text"
                                    value={bookingListDateFrom}
                                    readonly
                                    autocomplete="off"
                                    placeholder="Pilih tanggal"
                                    class="flex h-10 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:h-9"
                                />
                                <select
                                    class="flex h-10 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm md:h-9"
                                    bind:value={bookingListPayment}
                                >
                                    <option value="all">Semua Pembayaran</option
                                    >
                                    <option value="lunas">Lunas Semua</option>
                                    <option value="belum_lunas"
                                        >Masih Belum Lunas</option
                                    >
                                </select>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="h-10 rounded-xl px-3 text-xs md:h-9 md:rounded-full"
                                    onclick={resetBookingListFilters}
                                >
                                    Reset
                                </Button>
                            </div>
                        </div>
                        {#if emptyDepartureOpen}
                            <div
                                class="mt-3 rounded-2xl border border-cyan-200/70 bg-background/95 p-3 shadow-sm dark:border-cyan-500/20"
                            >
                                <div
                                    class="mb-2 flex flex-wrap items-center justify-between gap-2"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-foreground"
                                        >
                                            Tambah jadwal tanpa penumpang
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Dipakai untuk membuat card
                                            keberangkatan kosong berdasarkan
                                            data Jadwal.
                                        </p>
                                    </div>
                                    {#if emptyDepartureSchedule()}
                                        <Badge
                                            variant="secondary"
                                            class="rounded-full px-2.5 py-0.5 text-[11px]"
                                        >
                                            BOP {formatCurrency(
                                                Number(
                                                    emptyDepartureSchedule()
                                                        ?.bop || 0,
                                                ),
                                            )}
                                        </Badge>
                                    {/if}
                                </div>
                                <div class="grid gap-2 md:grid-cols-5">
                                    <input
                                        type="date"
                                        bind:value={emptyDepartureDate}
                                        onchange={() =>
                                            void onEmptyDepartureDateChange()}
                                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    />
                                    <select
                                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm md:col-span-2"
                                        bind:value={emptyDepartureRoute}
                                        onchange={() =>
                                            void onEmptyDepartureRouteChange()}
                                        disabled={loadingEmptyDepartureRoutes}
                                    >
                                        <option value=""
                                            >{loadingEmptyDepartureRoutes
                                                ? 'Memuat rute...'
                                                : 'Pilih rute'}</option
                                        >
                                        {#each emptyDepartureRoutes as route (`empty-departure-route-${route}`)}
                                            <option value={route}
                                                >{route}</option
                                            >
                                        {/each}
                                    </select>
                                    <select
                                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                                        bind:value={emptyDepartureJam}
                                        disabled={!emptyDepartureRoute ||
                                            loadingEmptyDepartureSchedules}
                                    >
                                        <option value=""
                                            >{loadingEmptyDepartureSchedules
                                                ? 'Memuat jam...'
                                                : 'Pilih jam'}</option
                                        >
                                        {#each emptyDepartureSchedules as schedule (`empty-departure-schedule-${schedule.jam}`)}
                                            <option value={schedule.jam}>
                                                {schedule.jam} - BOP {formatCurrency(
                                                    Number(schedule.bop || 0),
                                                )}
                                            </option>
                                        {/each}
                                    </select>
                                    <select
                                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                                        bind:value={emptyDepartureUnit}
                                        disabled={!emptyDepartureJam}
                                    >
                                        {#each emptyDepartureUnitOptions() as option (`empty-departure-unit-${option.value}`)}
                                            <option value={option.value}
                                                >{option.label}</option
                                            >
                                        {/each}
                                    </select>
                                </div>
                                <div
                                    class="mt-3 flex flex-wrap items-center justify-between gap-2"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        Card akan muncul di Data Keberangkatan
                                        walaupun belum ada penumpang.
                                    </p>
                                    <LoadingButton
                                        type="button"
                                        size="sm"
                                        class="h-8 rounded-full px-3 text-xs"
                                        onclick={() =>
                                            void saveEmptyDeparture()}
                                        loading={savingEmptyDeparture}
                                        loadingText="Menyimpan..."
                                        disabled={savingEmptyDeparture ||
                                            !emptyDepartureRoute ||
                                            !emptyDepartureJam}
                                    >
                                        Simpan Jadwal
                                    </LoadingButton>
                                </div>
                            </div>
                        {/if}
                        {#if formError}
                            <p
                                class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700 dark:border-rose-500/30 dark:bg-rose-950/25 dark:text-rose-200"
                            >
                                {formError}
                            </p>
                        {:else if formSuccess}
                            <p
                                class="mt-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/25 dark:text-emerald-200"
                            >
                                {formSuccess}
                            </p>
                        {/if}
                    </div>

                    {#if bookingListLoading}
                        <div
                            class="grid gap-2.5 md:grid-cols-2 xl:grid-cols-3"
                            aria-label="Memuat data keberangkatan"
                        >
                            {#each bookingListSkeletonRows as _row, index (`booking-list-skeleton-${index}`)}
                                <div
                                    class="overflow-hidden rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm"
                                    aria-hidden="true"
                                >
                                    <div
                                        class="mb-3 flex items-center justify-between gap-3"
                                    >
                                        <Skeleton
                                            class="h-4 w-32 rounded-full"
                                        />
                                        <Skeleton
                                            class="h-7 w-16 rounded-full"
                                        />
                                    </div>
                                    <div
                                        class="rounded-xl border border-cyan-100/70 bg-cyan-50/35 px-3 py-3 dark:border-cyan-500/15 dark:bg-cyan-950/10"
                                    >
                                        <Skeleton
                                            class="h-3 w-44 rounded-full"
                                        />
                                        <Skeleton
                                            class="mt-3 h-5 w-56 rounded-full"
                                        />
                                        <div
                                            class="mt-4 grid gap-2 sm:grid-cols-3"
                                        >
                                            <Skeleton
                                                class="h-3.5 rounded-full"
                                            />
                                            <Skeleton
                                                class="h-3.5 rounded-full"
                                            />
                                            <Skeleton
                                                class="h-3.5 rounded-full"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="mt-3 grid grid-cols-3 gap-1.5 sm:grid-cols-6"
                                    >
                                        {#each bookingListSkeletonStats as _stat, statIndex (`booking-list-skeleton-stat-${index}-${statIndex}`)}
                                            <Skeleton class="h-7 rounded-md" />
                                        {/each}
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {:else if filteredBookingGroups().length === 0}
                        <p class="text-sm text-muted-foreground">
                            Tidak ada data keberangkatan yang cocok dengan
                            filter.
                        </p>
                    {:else}
                        <div class="space-y-4">
                            {#if bookingListDesktopView === 'sheet'}
                                <div class="hidden space-y-4 lg:block">
                                    {#each bookingDateSectionsMemo as section (section.key)}
                                        <section
                                            class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                                        >
                                            <div
                                                class="flex flex-wrap items-center justify-between gap-3 border-b border-border/60 bg-muted/20 px-4 py-3"
                                            >
                                                <div class="space-y-1">
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                                                    >
                                                        Tanggal Operasional
                                                    </p>
                                                    <h3
                                                        class="text-base font-semibold text-foreground"
                                                    >
                                                        {section.label}
                                                    </h3>
                                                    <p
                                                        class="text-[11px] text-muted-foreground"
                                                    >
                                                        {section.totalSchedules}
                                                        keberangkatan aktif di hari
                                                        ini.
                                                    </p>
                                                </div>
                                                <div
                                                    class="flex flex-wrap items-center gap-1.5 text-[11px]"
                                                >
                                                    <span
                                                        class="rounded-md border border-border/70 bg-background px-2 py-1 font-medium text-muted-foreground"
                                                    >
                                                        {section.totalPassengers}
                                                        penumpang aktif
                                                    </span>
                                                    <span
                                                        class="rounded-md border border-amber-200/80 bg-amber-50 px-2 py-1 font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-950/20 dark:text-amber-200"
                                                    >
                                                        {section.totalUnpaid}
                                                        belum lunas
                                                    </span>
                                                    <span
                                                        class="rounded-md border border-amber-200/80 bg-background px-2 py-1 font-medium text-amber-700 dark:border-amber-500/30 dark:text-amber-200"
                                                    >
                                                        {formatCurrency(
                                                            section.unpaidAmount,
                                                        )}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="table-container">
                                                <table
                                                    class="min-w-full table-fixed text-sm"
                                                >
                                                    <thead
                                                        class="sticky top-0 z-10 bg-background/95 text-left text-[11px] uppercase tracking-[0.14em] text-muted-foreground backdrop-blur"
                                                    >
                                                        <tr>
                                                            <th
                                                                class="w-[128px] px-4 py-3 font-medium"
                                                            >
                                                                Jam
                                                            </th>
                                                            <th
                                                                class="w-[300px] px-4 py-3 font-medium"
                                                            >
                                                                Rute
                                                            </th>
                                                            <th
                                                                class="w-[260px] px-4 py-3 font-medium"
                                                            >
                                                                Mapping
                                                            </th>
                                                            <th
                                                                class="w-[340px] px-4 py-3 font-medium"
                                                            >
                                                                Manifest &
                                                                Pembayaran
                                                            </th>
                                                            <th
                                                                class="w-[176px] px-4 py-3 text-right font-medium"
                                                            >
                                                                Aksi
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-[13px]">
                                                        {#each section.groups as group (group.key)}
                                                            {@const unpaidAmount =
                                                                bookingGroupUnpaidAmount(
                                                                    group,
                                                                )}
                                                            {@const driverMissing =
                                                                bookingAssignmentMissing(
                                                                    group.driver_name,
                                                                )}
                                                            {@const armadaMissing =
                                                                bookingAssignmentMissing(
                                                                    group.armada_nopol,
                                                                )}
                                                            <tr
                                                                class={`border-t border-border/60 align-top transition-colors hover:bg-muted/20 ${isCanceledDeparture(group) ? 'bg-rose-50/30 dark:bg-rose-950/10' : ''}`}
                                                            >
                                                                <td
                                                                    class="px-4 py-3.5"
                                                                >
                                                                    <div
                                                                        class="space-y-1.5"
                                                                    >
                                                                        <div
                                                                            class="inline-flex items-center gap-2 text-sm font-semibold text-foreground"
                                                                        >
                                                                            <Clock3
                                                                                class="h-3.5 w-3.5 text-primary"
                                                                            />
                                                                            {formatGroupTimeLabel(
                                                                                group.jam,
                                                                            )}
                                                                        </div>
                                                                        <div
                                                                            class="flex flex-wrap gap-1.5"
                                                                        >
                                                                            <Badge
                                                                                variant="secondary"
                                                                                class="rounded-md px-2 py-0.5 text-[10px]"
                                                                            >
                                                                                Unit
                                                                                {group.unit}
                                                                            </Badge>
                                                                            {#if isCanceledDeparture(group)}
                                                                                <Badge
                                                                                    variant="destructive"
                                                                                    class="rounded-md px-2 py-0.5 text-[10px]"
                                                                                >
                                                                                    Batal
                                                                                </Badge>
                                                                            {:else if isDepartedDeparture(group)}
                                                                                <Badge
                                                                                    variant="secondary"
                                                                                    class="rounded-md border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] text-amber-700 dark:border-amber-500/30 dark:bg-amber-950/25 dark:text-amber-200"
                                                                                >
                                                                                    Berangkat
                                                                                </Badge>
                                                                            {:else if isArrivedDeparture(group)}
                                                                                <Badge
                                                                                    variant="secondary"
                                                                                    class="rounded-md border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/25 dark:text-emerald-200"
                                                                                >
                                                                                    Tiba
                                                                                </Badge>
                                                                            {/if}
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="px-4 py-3.5"
                                                                >
                                                                    <div
                                                                        class="space-y-1.5"
                                                                    >
                                                                        <div>
                                                                            <p
                                                                                class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted-foreground"
                                                                            >
                                                                                {group.departure_code}
                                                                            </p>
                                                                            <p
                                                                                class="mt-1 text-sm font-semibold leading-snug text-foreground"
                                                                            >
                                                                                {group.rute}
                                                                            </p>
                                                                        </div>
                                                                        <div
                                                                            class="flex flex-wrap gap-1.5 text-[11px]"
                                                                        >
                                                                            <span
                                                                                class="rounded-md border border-border/70 bg-background px-2 py-1 font-medium text-muted-foreground"
                                                                            >
                                                                                Total
                                                                                {group.total}
                                                                            </span>
                                                                            <span
                                                                                class="rounded-md border border-border/70 bg-background px-2 py-1 font-medium text-muted-foreground"
                                                                            >
                                                                                Aktif
                                                                                {group.active}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="px-4 py-3.5"
                                                                >
                                                                    <div
                                                                        class="space-y-2 text-xs"
                                                                    >
                                                                        <div
                                                                            class={driverMissing
                                                                                ? 'text-amber-700 dark:text-amber-200'
                                                                                : 'text-foreground'}
                                                                        >
                                                                            <span
                                                                                class="font-medium text-muted-foreground"
                                                                                >Driver:</span
                                                                            >
                                                                            <span
                                                                                class="ml-1 font-semibold"
                                                                            >
                                                                                {bookingAssignmentText(
                                                                                    group.driver_name,
                                                                                    'Belum ditugaskan',
                                                                                )}
                                                                            </span>
                                                                        </div>
                                                                        <div
                                                                            class={armadaMissing
                                                                                ? 'text-amber-700 dark:text-amber-200'
                                                                                : 'text-foreground'}
                                                                        >
                                                                            <span
                                                                                class="font-medium text-muted-foreground"
                                                                                >Armada:</span
                                                                            >
                                                                            <span
                                                                                class="ml-1 font-semibold"
                                                                            >
                                                                                {bookingAssignmentText(
                                                                                    group.armada_nopol,
                                                                                    'Belum ditugaskan',
                                                                                )}
                                                                            </span>
                                                                        </div>
                                                                        {#if !consoleOnly}
                                                                            <div class="text-foreground">
                                                                                <span class="font-medium text-muted-foreground">BOP:</span>
                                                                                <span class="ml-1 font-semibold">
                                                                                    {formatCurrency(
                                                                                        Number(
                                                                                            group.bop ||
                                                                                                0,
                                                                                        ),
                                                                                    )}
                                                                                </span>
                                                                            </div>
                                                                        {/if}
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="px-4 py-3.5"
                                                                >
                                                                    <div
                                                                        class="space-y-2"
                                                                    >
                                                                        <div
                                                                            class="flex flex-wrap gap-1.5 text-[11px]"
                                                                        >
                                                                            <span
                                                                                class="rounded-md border border-border/70 bg-background px-2 py-1 font-medium text-muted-foreground"
                                                                            >
                                                                                Total
                                                                                {group.total}
                                                                            </span>
                                                                            <span
                                                                                class="rounded-md border border-emerald-200/80 bg-emerald-50 px-2 py-1 font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/20 dark:text-emerald-200"
                                                                            >
                                                                                Lunas
                                                                                {group.lunas}
                                                                            </span>
                                                                            <span
                                                                                class="rounded-md border border-amber-200/80 bg-amber-50 px-2 py-1 font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-950/20 dark:text-amber-200"
                                                                            >
                                                                                Belum
                                                                                Lunas
                                                                                {group.belum_lunas}
                                                                            </span>
                                                                            <span
                                                                                class="rounded-md border border-sky-200/80 bg-sky-50 px-2 py-1 font-medium text-sky-700 dark:border-sky-500/30 dark:bg-sky-950/20 dark:text-sky-200"
                                                                            >
                                                                                Refund
                                                                                {group.refund}
                                                                            </span>
                                                                            {#if group.canceled > 0}
                                                                                <span
                                                                                    class="rounded-md border border-rose-200/80 bg-rose-50 px-2 py-1 font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-950/20 dark:text-rose-200"
                                                                                >
                                                                                    Cancel
                                                                                    {group.canceled}
                                                                                </span>
                                                                            {/if}
                                                                        </div>
                                                                        <div
                                                                            class="grid gap-1 rounded-xl border border-border/70 bg-muted/20 px-3 py-2 text-[11px]"
                                                                        >
                                                                            <div
                                                                                class="flex items-center justify-between gap-3"
                                                                            >
                                                                                <span
                                                                                    class="text-muted-foreground"
                                                                                    >Nilai
                                                                                    sudah
                                                                                    lunas</span
                                                                                >
                                                                                <span
                                                                                    class="font-semibold text-foreground"
                                                                                >
                                                                                    {formatCurrency(
                                                                                        bookingGroupPaidAmount(
                                                                                            group,
                                                                                        ),
                                                                                    )}
                                                                                </span>
                                                                            </div>
                                                                            <div
                                                                                class="flex items-center justify-between gap-3"
                                                                            >
                                                                                <span
                                                                                    class="text-muted-foreground"
                                                                                    >Nilai
                                                                                    belum
                                                                                    lunas</span
                                                                                >
                                                                                <span
                                                                                    class={`font-semibold ${unpaidAmount > 0 ? 'text-amber-700 dark:text-amber-200' : 'text-foreground'}`}
                                                                                >
                                                                                    {formatCurrency(
                                                                                        unpaidAmount,
                                                                                    )}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="w-[190px] px-4 py-3.5"
                                                                >
                                                                    <div
                                                                        class="flex flex-col items-end gap-1.5"
                                                                    >
                                                                        {#if canMarkDepartureDeparted(group)}
                                                                            <Button
                                                                                type="button"
                                                                                size="sm"
                                                                                class="h-7 w-full justify-center rounded-md px-2.5 text-[11px]"
                                                                                onclick={() =>
                                                                                    void markDepartureDeparted(
                                                                                        group,
                                                                                    )}
                                                                            >
                                                                                Armada
                                                                                Sudah
                                                                                Berangkat
                                                                            </Button>
                                                                        {/if}
                                                                        {#if canMarkDepartureArrived(group)}
                                                                            <Button
                                                                                type="button"
                                                                                size="sm"
                                                                                variant="outline"
                                                                                class="h-7 w-full justify-center rounded-md px-2.5 text-[11px]"
                                                                                onclick={() =>
                                                                                    void markDepartureArrived(
                                                                                        group,
                                                                                    )}
                                                                            >
                                                                                Armada
                                                                                Sudah
                                                                                Tiba
                                                                            </Button>
                                                                        {/if}
                                                                        <DropdownMenu
                                                                        >
                                                                            <DropdownMenuTrigger
                                                                                asChild
                                                                            >
                                                                                {#snippet children(
                                                                                    props,
                                                                                )}
                                                                                    <Button
                                                                                        type="button"
                                                                                        size="icon"
                                                                                        variant="outline"
                                                                                        class="h-8 w-8 rounded-md bg-background/90"
                                                                                        onclick={props.onclick}
                                                                                        aria-expanded={props[
                                                                                            'aria-expanded'
                                                                                        ]}
                                                                                        data-state={props[
                                                                                            'data-state'
                                                                                        ]}
                                                                                    >
                                                                                        <MoreHorizontal
                                                                                            class="h-3.5 w-3.5"
                                                                                        />
                                                                                    </Button>
                                                                                {/snippet}
                                                                            </DropdownMenuTrigger>
                                                                            <DropdownMenuContent
                                                                                align="end"
                                                                                sideOffset={6}
                                                                                class="z-[120] w-44 text-[11px] shadow-2xl"
                                                                            >
                                                                                <DropdownMenuItem
                                                                                    onclick={() =>
                                                                                        navigateToGroupDetail(
                                                                                            group,
                                                                                        )}
                                                                                >
                                                                                    Detail
                                                                                </DropdownMenuItem>
                                                                                {#if !isManifestLocked(group)}
                                                                                    <DropdownMenuItem
                                                                                        onclick={() =>
                                                                                            void copyBookingGroup(
                                                                                                group,
                                                                                            )}
                                                                                    >
                                                                                        Copy
                                                                                        Data
                                                                                    </DropdownMenuItem>
                                                                                {/if}
                                                                                <DropdownMenuItem
                                                                                    onclick={() =>
                                                                                        openManifestPrint(
                                                                                            group,
                                                                                        )}
                                                                                >
                                                                                    Print
                                                                                    Manifest
                                                                                </DropdownMenuItem>
                                                                                {#if canMarkDepartureDeparted(group)}
                                                                                    <DropdownMenuItem
                                                                                        onclick={() =>
                                                                                            void markDepartureDeparted(
                                                                                                group,
                                                                                            )}
                                                                                    >
                                                                                        {cancelingDepartureKey ===
                                                                                        group.key
                                                                                            ? 'Memproses...'
                                                                                            : 'Armada Sudah Berangkat'}
                                                                                    </DropdownMenuItem>
                                                                                {/if}
                                                                                {#if canMarkDepartureArrived(group)}
                                                                                    <DropdownMenuItem
                                                                                        onclick={() =>
                                                                                            void markDepartureArrived(
                                                                                                group,
                                                                                            )}
                                                                                    >
                                                                                        {cancelingDepartureKey ===
                                                                                        group.key
                                                                                            ? 'Memproses...'
                                                                                            : 'Armada Sudah Tiba'}
                                                                                    </DropdownMenuItem>
                                                                                {/if}
                                                                                {#if isArrivedDeparture(group) && !isManifestLocked(group)}
                                                                                    <DropdownMenuItem
                                                                                        onclick={() =>
                                                                                            void closeManifest(
                                                                                                group,
                                                                                            )}
                                                                                    >
                                                                                        Close
                                                                                        Manifest
                                                                                    </DropdownMenuItem>
                                                                                {/if}
                                                                                {#if !isManifestLocked(group) && !isCanceledDeparture(group) && !isDepartedDeparture(group) && !isArrivedDeparture(group)}
                                                                                    <DropdownMenuItem
                                                                                        onclick={() =>
                                                                                            void cancelDeparture(
                                                                                                group,
                                                                                            )}
                                                                                    >
                                                                                        {cancelingDepartureKey ===
                                                                                        group.key
                                                                                            ? 'Membatalkan...'
                                                                                            : 'Batalkan Jadwal'}
                                                                                    </DropdownMenuItem>
                                                                                {/if}
                                                                                {#if !isManifestLocked(group) && !isCanceledDeparture(group) && group.belum_lunas > 0}
                                                                                    <DropdownMenuItem
                                                                                        onclick={() =>
                                                                                            void markBookingGroupAsPaid(
                                                                                                group,
                                                                                            )}
                                                                                    >
                                                                                        Lunas
                                                                                        Semua
                                                                                    </DropdownMenuItem>
                                                                                {/if}
                                                                            </DropdownMenuContent>
                                                                        </DropdownMenu>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        {/each}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>
                                    {/each}
                                </div>
                            {/if}

                            <div
                                class={bookingListDesktopView === 'sheet'
                                    ? 'space-y-2.5 lg:hidden'
                                    : 'grid gap-2 md:grid-cols-2 xl:grid-cols-3'}
                            >
                                {#each visibleBookingGroups() as group (group.key)}
                                    {@const unpaidAmount =
                                        bookingGroupUnpaidAmount(group)}
                                    <div
                                        class={`group relative overflow-hidden rounded-2xl border border-border/80 bg-card/95 p-2.5 shadow-sm transition-all duration-200 motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-1 motion-safe:duration-300 hover:-translate-y-0.5 hover:border-cyan-300/60 hover:shadow-md hover:shadow-cyan-950/10 ${isCanceledDeparture(group) ? 'border-rose-300/70 bg-rose-50/60 hover:border-rose-300/90 dark:border-rose-500/35 dark:bg-rose-950/15' : ''}`}
                                    >
                                        <div
                                            class="pointer-events-none absolute inset-x-0 top-0 h-14 bg-linear-to-r from-cyan-500/12 via-sky-500/10 to-transparent opacity-85 transition-opacity duration-200 group-hover:opacity-100"
                                        ></div>
                                        <div class="relative">
                                            <div
                                                class="mb-2 flex items-start justify-between gap-2"
                                            >
                                                <p
                                                    class="inline-flex min-w-0 items-center gap-1.5 text-[11px] font-medium text-muted-foreground"
                                                >
                                                    <CalendarDays
                                                        class="h-3.5 w-3.5 shrink-0"
                                                    />
                                                    <span class="truncate"
                                                        >{formatGroupDateLabel(
                                                            group.tanggal,
                                                        )}</span
                                                    >
                                                </p>
                                                <div
                                                    class="flex shrink-0 items-center gap-1"
                                                >
                                                    {#if isCanceledDeparture(group)}
                                                        <Badge
                                                            variant="destructive"
                                                            class="rounded-full px-2 py-0.5 text-[10px]"
                                                        >
                                                            Batal
                                                        </Badge>
                                                    {:else if isDepartedDeparture(group)}
                                                        <Badge
                                                            variant="secondary"
                                                            class="rounded-full border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] text-amber-700 dark:border-amber-500/30 dark:bg-amber-950/25 dark:text-amber-200"
                                                        >
                                                            Berangkat
                                                        </Badge>
                                                    {:else if isArrivedDeparture(group)}
                                                        <Badge
                                                            variant="secondary"
                                                            class="rounded-full border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/25 dark:text-emerald-200"
                                                        >
                                                            Tiba
                                                        </Badge>
                                                    {/if}
                                                    <Badge
                                                        variant="secondary"
                                                        class="rounded-full px-2 py-0.5 text-[10px]"
                                                        >Unit {group.unit}</Badge
                                                    >
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger
                                                            asChild
                                                        >
                                                            {#snippet children(
                                                                props,
                                                            )}
                                                                <Button
                                                                    type="button"
                                                                    size="icon"
                                                                    variant="outline"
                                                                    class="h-7 w-7 rounded-full bg-background/90"
                                                                    onclick={props.onclick}
                                                                    aria-expanded={props[
                                                                        'aria-expanded'
                                                                    ]}
                                                                    data-state={props[
                                                                        'data-state'
                                                                    ]}
                                                                >
                                                                    <MoreHorizontal
                                                                        class="h-3.5 w-3.5"
                                                                    />
                                                                </Button>
                                                            {/snippet}
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent
                                                            align="end"
                                                            sideOffset={6}
                                                            class="z-[120] w-44 text-[11px] shadow-2xl"
                                                        >
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    navigateToGroupDetail(
                                                                        group,
                                                                    )}
                                                            >
                                                                Detail
                                                            </DropdownMenuItem>
                                                            {#if !isManifestLocked(group)}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void copyBookingGroup(
                                                                            group,
                                                                        )}
                                                                >
                                                                    Copy Data
                                                                </DropdownMenuItem>
                                                            {/if}
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    openManifestPrint(
                                                                        group,
                                                                    )}
                                                            >
                                                                Print Manifest
                                                            </DropdownMenuItem>
                                                            {#if canMarkDepartureDeparted(group)}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void markDepartureDeparted(
                                                                            group,
                                                                        )}
                                                                >
                                                                    {cancelingDepartureKey ===
                                                                    group.key
                                                                        ? 'Memproses...'
                                                                        : 'Armada Sudah Berangkat'}
                                                                </DropdownMenuItem>
                                                            {/if}
                                                            {#if canMarkDepartureArrived(group)}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void markDepartureArrived(
                                                                            group,
                                                                        )}
                                                                >
                                                                    {cancelingDepartureKey ===
                                                                    group.key
                                                                        ? 'Memproses...'
                                                                        : 'Armada Sudah Tiba'}
                                                                </DropdownMenuItem>
                                                            {/if}
                                                            {#if isArrivedDeparture(group) && !isManifestLocked(group)}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void closeManifest(
                                                                            group,
                                                                        )}
                                                                >
                                                                    Close Manifest
                                                                </DropdownMenuItem>
                                                            {/if}
                                                            {#if !isManifestLocked(group) && !isCanceledDeparture(group) && !isDepartedDeparture(group) && !isArrivedDeparture(group)}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void cancelDeparture(
                                                                            group,
                                                                        )}
                                                                >
                                                                    {cancelingDepartureKey ===
                                                                    group.key
                                                                        ? 'Membatalkan...'
                                                                        : 'Batalkan Jadwal'}
                                                                </DropdownMenuItem>
                                                            {/if}
                                                            {#if !isManifestLocked(group) && !isCanceledDeparture(group) && group.belum_lunas > 0}
                                                                <DropdownMenuItem
                                                                    onclick={() =>
                                                                        void markBookingGroupAsPaid(
                                                                            group,
                                                                        )}
                                                                >
                                                                    Lunas Semua
                                                                </DropdownMenuItem>
                                                            {/if}
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </div>
                                            </div>

                                            <div class="space-y-2.5">
                                                <div
                                                    class="rounded-xl border border-cyan-200/45 bg-linear-to-r from-cyan-50/80 via-sky-50/70 to-transparent px-3 py-2.5 transition-colors duration-200 dark:border-cyan-500/20 dark:from-cyan-950/25 dark:via-sky-950/20"
                                                >
                                                    <div
                                                        class="flex items-center justify-between gap-3"
                                                    >
                                                        <div class="min-w-0">
                                                            <p
                                                                class="inline-flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-muted-foreground"
                                                            >
                                                                <Route
                                                                    class="h-3.5 w-3.5"
                                                                />
                                                                {group.departure_code}
                                                            </p>
                                                            <p
                                                                class="mt-1 truncate text-sm font-semibold leading-snug text-foreground"
                                                            >
                                                                {group.rute}
                                                            </p>
                                                        </div>
                                                        <div
                                                            class="rounded-xl border border-cyan-200/60 bg-background/90 px-3 py-2 text-center dark:border-cyan-500/20"
                                                        >
                                                            <p
                                                                class="inline-flex items-center justify-center gap-1.5 text-lg font-extrabold leading-none tracking-tight text-foreground"
                                                            >
                                                                <Clock3
                                                                    class="h-3.5 w-3.5 text-primary"
                                                                />
                                                                <span
                                                                    >{formatGroupTimeLabel(
                                                                        group.jam,
                                                                    )}</span
                                                                >
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="mt-2 grid gap-1 text-[11px] text-foreground sm:grid-cols-3"
                                                    >
                                                        <p
                                                            class={`inline-flex min-w-0 items-center gap-1.5 ${bookingAssignmentMissing(group.driver_name) ? 'text-amber-700 dark:text-amber-200' : ''}`}
                                                        >
                                                            <UserRound
                                                                class="h-3.5 w-3.5 shrink-0 text-primary"
                                                            />
                                                            <span
                                                                class="truncate"
                                                                >Driver:
                                                                {bookingAssignmentText(
                                                                    group.driver_name,
                                                                    'Belum ditugaskan',
                                                                )}</span
                                                            >
                                                        </p>
                                                        <p
                                                            class={`inline-flex min-w-0 items-center gap-1.5 ${bookingAssignmentMissing(group.armada_nopol) ? 'text-amber-700 dark:text-amber-200' : ''}`}
                                                        >
                                                            <CarFront
                                                                class="h-3.5 w-3.5 shrink-0 text-primary"
                                                            />
                                                            <span
                                                                class="truncate"
                                                                >Nopol:
                                                                {bookingAssignmentText(
                                                                    group.armada_nopol,
                                                                    'Belum ditugaskan',
                                                                )}</span
                                                            >
                                                        </p>
                                                        {#if !consoleOnly}
                                                            <p
                                                                class="inline-flex min-w-0 items-center gap-1.5"
                                                            >
                                                                <WalletCards
                                                                    class="h-3.5 w-3.5 shrink-0 text-primary"
                                                                />
                                                                <span
                                                                    class="truncate"
                                                                    >BOP:
                                                                    {formatCurrency(
                                                                        Number(
                                                                            group.bop ||
                                                                                0,
                                                                        ),
                                                                    )}</span
                                                                >
                                                            </p>
                                                        {/if}
                                                    </div>
                                                </div>

                                                <div
                                                    class="rounded-xl border border-border/70 bg-background/82 px-3 py-2.5 transition-colors duration-200 group-hover:bg-background/92"
                                                >
                                                    <div
                                                        class="grid grid-cols-2 gap-1.5 text-[11px] sm:grid-cols-3"
                                                    >
                                                        <span
                                                            class="inline-flex items-center justify-center rounded-md border border-border/70 bg-muted/45 px-1.5 py-1 font-medium text-foreground"
                                                            >Total
                                                            {group.total}</span
                                                        >
                                                        <span
                                                            class="inline-flex items-center justify-center rounded-md border border-border/70 bg-muted/45 px-1.5 py-1 font-medium text-foreground"
                                                            >Aktif
                                                            {group.active}</span
                                                        >
                                                        <span
                                                            class="inline-flex items-center justify-center rounded-md border border-rose-300/70 bg-rose-50 px-1.5 py-1 font-medium text-rose-700 dark:border-rose-500/40 dark:bg-rose-950/30 dark:text-rose-300"
                                                            >Cancel
                                                            {group.canceled}</span
                                                        >
                                                        <span
                                                            class="inline-flex items-center justify-center rounded-md border border-emerald-300/70 bg-emerald-50 px-1.5 py-1 font-medium text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-950/30 dark:text-emerald-300"
                                                            >Lunas
                                                            {group.lunas}</span
                                                        >
                                                        <span
                                                            class="inline-flex items-center justify-center rounded-md border border-sky-300/70 bg-sky-50 px-1.5 py-1 font-medium text-sky-700 dark:border-sky-500/40 dark:bg-sky-950/30 dark:text-sky-300"
                                                            >Refund
                                                            {group.refund}</span
                                                        >
                                                        <span
                                                            class="inline-flex items-center justify-center rounded-md border border-amber-300/70 bg-amber-50 px-1.5 py-1 font-medium text-amber-700 dark:border-amber-500/40 dark:bg-amber-950/30 dark:text-amber-300"
                                                            >Belum
                                                            {group.belum_lunas}</span
                                                        >
                                                    </div>
                                                    {#if unpaidAmount > 0}
                                                        <div
                                                            class="mt-2 rounded-xl border border-amber-200/80 bg-amber-50/80 px-2.5 py-2 text-[11px] text-amber-700 dark:border-amber-500/30 dark:bg-amber-950/20 dark:text-amber-200"
                                                        >
                                                            Nilai belum lunas
                                                            {formatCurrency(
                                                                unpaidAmount,
                                                            )}
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                        {#if remainingBookingGroupsCount > 0}
                            <div class="mt-4 flex justify-center">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="rounded-full px-4 text-xs"
                                    onclick={() =>
                                        (bookingListVisibleCount +=
                                            BOOKING_LIST_PAGE_SIZE)}
                                >
                                    Tampilkan {Math.min(
                                        BOOKING_LIST_PAGE_SIZE,
                                        remainingBookingGroupsCount,
                                    )}
                                    jadwal lagi
                                </Button>
                            </div>
                        {/if}
                    {/if}
                {:else if localLatestBookings.length === 0}
                    <p class="text-sm text-muted-foreground">
                        Belum ada data keberangkatan. Jalankan proses
                        import/migrasi data lama untuk melihat isi tabel.
                    </p>
                {:else}
                    <div class="space-y-2.5 md:hidden">
                        {#each localLatestBookings as booking (booking.id)}
                            <div
                                class="group relative overflow-hidden rounded-2xl border border-border/70 bg-card/95 shadow-sm motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-1 motion-safe:duration-300 motion-safe:transition-all hover:shadow-md hover:shadow-cyan-900/10 active:scale-[0.99]"
                            >
                                <div
                                    class="pointer-events-none absolute inset-x-0 top-0 h-16 bg-linear-to-r from-cyan-500/10 via-sky-500/8 to-transparent opacity-90 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                                <div class="relative p-3.5">
                                    <div
                                        class="mb-3 flex items-start justify-between gap-2"
                                    >
                                        <div class="min-w-0">
                                            <p
                                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-foreground"
                                            >
                                                <UserRound
                                                    class="h-3.5 w-3.5 text-primary"
                                                />
                                                <span class="truncate"
                                                    >{booking.name}</span
                                                >
                                            </p>
                                            <p
                                                class="mt-0.5 inline-flex items-center gap-1.5 text-xs text-muted-foreground"
                                            >
                                                <Phone class="h-3.5 w-3.5" />
                                                {booking.phone}
                                            </p>
                                            <p
                                                class="mt-1 text-[11px] text-muted-foreground"
                                            >
                                                {booking.ticket_code || '-'} • {booking.departure_code ||
                                                    '-'}
                                            </p>
                                        </div>
                                        <div
                                            class="flex flex-col items-end gap-1"
                                        >
                                            <Badge
                                                variant={statusVariant(
                                                    booking.status,
                                                )}
                                                class="rounded-full px-2.5 py-0.5 text-[11px]"
                                            >
                                                {booking.status}
                                            </Badge>
                                            <Badge
                                                variant={paymentVariant(
                                                    booking.pembayaran,
                                                )}
                                                class="rounded-full px-2.5 py-0.5 text-[11px]"
                                            >
                                                {booking.pembayaran}
                                            </Badge>
                                        </div>
                                    </div>

                                    <div
                                        class="mb-3 rounded-xl border border-border/60 bg-background/70 p-2.5 transition-colors duration-300 group-hover:bg-background/80"
                                    >
                                        <p
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground"
                                        >
                                            <Route class="h-3.5 w-3.5" />
                                            Rute
                                        </p>
                                        <p
                                            class="mt-1 text-sm font-semibold leading-snug text-foreground"
                                        >
                                            {booking.rute}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <div
                                            class="rounded-lg border border-border/60 bg-muted/40 px-2 py-1.5 transition-colors duration-300 group-hover:bg-muted/55"
                                        >
                                            <p
                                                class="text-[10px] uppercase tracking-wide text-muted-foreground"
                                            >
                                                Tanggal
                                            </p>
                                            <p
                                                class="mt-0.5 inline-flex items-center gap-1 font-semibold text-foreground"
                                            >
                                                <CalendarDays class="h-3 w-3" />
                                                {booking.tanggal}
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-lg border border-border/60 bg-muted/40 px-2 py-1.5 transition-colors duration-300 group-hover:bg-muted/55"
                                        >
                                            <p
                                                class="text-[10px] uppercase tracking-wide text-muted-foreground"
                                            >
                                                Jam
                                            </p>
                                            <p
                                                class="mt-0.5 inline-flex items-center gap-1 font-semibold text-foreground"
                                            >
                                                <Clock3 class="h-3 w-3" />
                                                {booking.jam}
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-lg border border-border/60 bg-muted/40 px-2 py-1.5 transition-colors duration-300 group-hover:bg-muted/55"
                                        >
                                            <p
                                                class="text-[10px] uppercase tracking-wide text-muted-foreground"
                                            >
                                                Unit
                                            </p>
                                            <p
                                                class="mt-0.5 inline-flex items-center gap-1 font-semibold text-foreground"
                                            >
                                                <BusFront class="h-3 w-3" />
                                                {booking.unit}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center gap-2">
                                        <Badge
                                            variant="secondary"
                                            class="rounded-full px-2.5 py-0.5 text-[11px]"
                                        >
                                            <Armchair
                                                class="mr-1 h-3.5 w-3.5"
                                            />
                                            Seat {booking.seat}
                                        </Badge>
                                    </div>

                                    <div
                                        class="mt-3 flex flex-wrap items-center gap-2"
                                    >
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                {#snippet children(props)}
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        variant="outline"
                                                        class="h-8 rounded-lg text-xs"
                                                        onclick={props.onclick}
                                                        aria-expanded={props[
                                                            'aria-expanded'
                                                        ]}
                                                        data-state={props[
                                                            'data-state'
                                                        ]}
                                                    >
                                                        <Printer
                                                            class="mr-1 h-3.5 w-3.5"
                                                        />
                                                        Print
                                                    </Button>
                                                {/snippet}
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="start"
                                                class="w-40"
                                            >
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        openTicketPrint(
                                                            booking.id,
                                                        )}
                                                >
                                                    Print Tiket
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        openTicketPdf(
                                                            booking.id,
                                                        )}
                                                >
                                                    Buka PDF
                                                </DropdownMenuItem>
                                                {#if booking.group_key}
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            openManifestPrintByKey(
                                                                booking.group_key ||
                                                                    '',
                                                            )}
                                                    >
                                                        Print Manifest
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            openManifestPdfByKey(
                                                                booking.group_key ||
                                                                    '',
                                                            )}
                                                    >
                                                        Buka PDF Manifest
                                                    </DropdownMenuItem>
                                                {/if}
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>
                    <div class="hidden overflow-x-auto md:block">
                        <table class="w-full min-w-[840px] text-sm">
                            <thead>
                                <tr
                                    class="border-b text-left text-muted-foreground"
                                >
                                    <th class="px-2 py-2 font-medium">Kode</th>
                                    <th class="px-2 py-2 font-medium">Nama</th>
                                    <th class="px-2 py-2 font-medium"
                                        >Telepon</th
                                    >
                                    <th class="px-2 py-2 font-medium">Rute</th>
                                    <th class="px-2 py-2 font-medium"
                                        >Tanggal</th
                                    >
                                    <th class="px-2 py-2 font-medium">Jam</th>
                                    <th class="px-2 py-2 font-medium">Unit</th>
                                    <th class="px-2 py-2 font-medium">Seat</th>
                                    <th class="px-2 py-2 font-medium">Status</th
                                    >
                                    <th class="px-2 py-2 font-medium"
                                        >Pembayaran</th
                                    >
                                    <th class="px-2 py-2 font-medium"
                                        >Dokumen</th
                                    >
                                </tr>
                            </thead>
                            <tbody>
                                {#each localLatestBookings as booking (booking.id)}
                                    <tr class="border-b/70">
                                        <td class="px-2 py-2">
                                            <p class="text-xs font-medium">
                                                {booking.ticket_code || '-'}
                                            </p>
                                            <p
                                                class="text-[11px] text-muted-foreground"
                                            >
                                                {booking.departure_code || '-'}
                                            </p>
                                        </td>
                                        <td class="px-2 py-2 font-medium"
                                            >{booking.name}</td
                                        >
                                        <td class="px-2 py-2"
                                            >{booking.phone}</td
                                        >
                                        <td class="px-2 py-2">{booking.rute}</td
                                        >
                                        <td class="px-2 py-2"
                                            >{booking.tanggal}</td
                                        >
                                        <td class="px-2 py-2">{booking.jam}</td>
                                        <td class="px-2 py-2">{booking.unit}</td
                                        >
                                        <td class="px-2 py-2">{booking.seat}</td
                                        >
                                        <td class="px-2 py-2">
                                            <Badge
                                                variant={statusVariant(
                                                    booking.status,
                                                )}>{booking.status}</Badge
                                            >
                                        </td>
                                        <td class="px-2 py-2">
                                            <Badge
                                                variant={paymentVariant(
                                                    booking.pembayaran,
                                                )}>{booking.pembayaran}</Badge
                                            >
                                        </td>
                                        <td class="px-2 py-2">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    {#snippet children(props)}
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            class="h-8 rounded-lg px-2.5"
                                                            onclick={props.onclick}
                                                            aria-expanded={props[
                                                                'aria-expanded'
                                                            ]}
                                                            data-state={props[
                                                                'data-state'
                                                            ]}
                                                        >
                                                            <MoreHorizontal
                                                                class="h-4 w-4"
                                                            />
                                                        </Button>
                                                    {/snippet}
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent
                                                    align="end"
                                                    class="w-44"
                                                >
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            openTicketPrint(
                                                                booking.id,
                                                            )}
                                                    >
                                                        Print Tiket
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onclick={() =>
                                                            openTicketPdf(
                                                                booking.id,
                                                            )}
                                                    >
                                                        Buka PDF Tiket
                                                    </DropdownMenuItem>
                                                    {#if booking.group_key}
                                                        <DropdownMenuItem
                                                            onclick={() =>
                                                                openManifestPrintByKey(
                                                                    booking.group_key ||
                                                                        '',
                                                                )}
                                                        >
                                                            Print Manifest
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onclick={() =>
                                                                openManifestPdfByKey(
                                                                    booking.group_key ||
                                                                        '',
                                                                )}
                                                        >
                                                            Buka PDF Manifest
                                                        </DropdownMenuItem>
                                                    {/if}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </CardContent>
        </Card>
    {/if}
</div>

<style>
    @keyframes seat-tap-pop {
        0% {
            transform: scale(1);
        }
        45% {
            transform: scale(0.92);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes seat-selected-pulse {
        0% {
            box-shadow: 0 0 0 0 hsl(201 96% 40% / 0.45);
        }
        100% {
            box-shadow: 0 0 0 10px hsl(201 96% 40% / 0);
        }
    }

    :global(.seat-tap-pop) {
        animation: seat-tap-pop 220ms cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    :global(.seat-selected-pulse) {
        animation: seat-selected-pulse 340ms ease-out;
    }
</style>
