<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Carter',
                href: '/charters',
            },
        ],
    };
</script>

<script lang="ts">
    import { MoreHorizontal } from 'lucide-svelte';
    import { onDestroy, onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import { confirmAndRun, runWithFeedback } from '@/lib/action-feedback';
    import {
        formatCurrencyDisplay,
        formatCurrencyInput,
        parseCurrencyInput,
    } from '@/lib/currency';
    import { loadFlatpickr, type FlatpickrInstance } from '@/lib/flatpickr';

    type TabName = 'charters' | 'luggages' | 'assignments' | 'export';
    type ViewMode = 'data' | 'form' | 'view';
    type CharterScope = 'active' | 'history';
    type RouteRow = { id: number; name: string };
    type Unit = {
        id: number;
        nopol: string;
        merek?: string | null;
        type?: string | null;
        category?: string | null;
        kapasitas?: number | null;
        status?: string | null;
        layout?: string | null;
    };
    type Armada = {
        id: number;
        nopol: string;
        merk: string | null;
        kategori: string | null;
        tahun: number | null;
        warna: string | null;
        ac_type: string | null;
    };
    type Driver = { id: number; nama: string; phone?: string | null };
    type Service = { id: number; name: string };
    type Pagination = { page: number; per_page: number; total: number; last_page: number };
    type PoolOption = { id: number; name: string; code?: string | null; status?: string | null; route_ids?: number[] };
    type Charter = { id: number; pool_id: number | null; master_carter_id: number | null; name: string; company_name: string | null; phone: string | null; start_date: string; end_date: string; departure_time: string | null; pickup_point: string | null; drop_point: string | null; unit_id: number | null; unit_nopol: string | null; unit_category: string | null; armada_id: number | null; armada_nopol: string | null; driver_name: string | null; price: number; layanan: string | null; bop_price: number; bop_status: string | null; down_payment: number; payment_status: string | null; status: string | null };
    type CharterCustomer = { id: number; nama: string; no_hp: string; alamat: string | null; company: string | null };
    type BagasiCustomer = { id: number; nama: string; no_hp: string; alamat: string | null; tipe: string | null };
    type Luggage = { id: number; pool_id: number | null; sender_name: string; sender_phone: string; sender_address: string | null; receiver_name: string; receiver_phone: string; receiver_address: string | null; service_id: number | null; service_name: string | null; rute_id: number | null; rute: string | null; route_name: string | null; tanggal: string | null; unit_id: number | null; trip_assignment_id?: number | null; departure_date?: string | null; departure_time?: string | null; departure_unit?: number | null; departure_driver_name?: string | null; departure_armada_nopol?: string | null; quantity: number; notes: string | null; price: number; status: string | null; payment_status: string | null; kode_resi: string | null; created_at: string | null };
    type Assignment = { id: number; rute: string; tanggal: string; jam: string; unit: number; driver_id: number; nama: string | null };
    type AssignmentConflict = { type: string; message: string; assignment_id: number; rute: string; tanggal: string; jam: string; unit: number; driver_id: number; driver_name: string | null };
    type CharterRoute = { id: number; name: string; origin: string | null; destination: string | null; duration: string | null; rental_price: number; bop_price: number };

    let {
        initialTab = null,
        initialMode = null,
        initialCharterId = null,
        lockedMenuView: lockedFromServer = false,
    }: {
        initialTab?: TabName | null;
        initialMode?: ViewMode | null;
        initialCharterId?: number | null;
        lockedMenuView?: boolean;
    } = $props();

    const today = new Date().toISOString().slice(0, 10);
    const charterServiceOptions = ['DROPOFF', 'FULLDAY', 'HALFDAY', '2D1N', '3D2N', '4D3N', '5D4N'];
    const defaultCharterService = charterServiceOptions[0];
    let activeTab = $state<TabName>('charters');
    let activeMode = $state<ViewMode>('data');
    let lockedMenuView = $state(false);
    let busy = $state(false);
    let error = $state('');
    let message = $state('');
    let savingCharter = $state(false);
    let savingLuggage = $state(false);
    let savingAssignment = $state(false);
    let pendingDeleteKey = $state('');
    let pendingLuggageActionKey = $state('');

    let routes = $state<RouteRow[]>([]);
    let units = $state<Unit[]>([]);
    let armadas = $state<Armada[]>([]);
    let drivers = $state<Driver[]>([]);
    let services = $state<Service[]>([]);
    let charterRoutes = $state<CharterRoute[]>([]);
    let pools = $state<PoolOption[]>([]);

    let charters = $state<Charter[]>([]);
    let luggages = $state<Luggage[]>([]);
    let assignments = $state<Assignment[]>([]);

    let charterMeta = $state<Pagination>({ page: 1, per_page: 20, total: 0, last_page: 1 });
    let luggageMeta = $state<Pagination>({ page: 1, per_page: 20, total: 0, last_page: 1 });
    let assignmentMeta = $state<Pagination>({ page: 1, per_page: 20, total: 0, last_page: 1 });

    let filterFrom = $state('');
    let filterTo = $state('');
    let filterQuery = $state('');
    let charterFilterUnitId = $state(0);
    let charterFilterUnitSearch = $state('');
    let charterFilterUnitLookupOpen = $state(false);
    let charterFilterArmadaId = $state(0);
    let charterFilterArmadaSearch = $state('');
    let charterFilterArmadaLookupOpen = $state(false);
    let charterFilterArmadaBusy = $state(false);
    let charterFilterArmadas = $state<Armada[]>([]);
    let charterFilterArmadaSearchTimer: ReturnType<typeof setTimeout> | null = null;
    let exportType = $state<'reguler' | 'bagasi' | 'charter'>('reguler');
    let filterPerPage = $state(20);
    let mobileFiltersExpanded = $state(false);
    let charterFilterDateInput = $state<HTMLInputElement | null>(null);
    let charterFilterDatePicker: FlatpickrInstance | null = null;
    let luggageFilterDateInput = $state<HTMLInputElement | null>(null);
    let luggageFilterDatePicker: FlatpickrInstance | null = null;
    let exportFromDateInput = $state<HTMLInputElement | null>(null);
    let exportToDateInput = $state<HTMLInputElement | null>(null);
    let exportFromDatePicker: FlatpickrInstance | null = null;
    let exportToDatePicker: FlatpickrInstance | null = null;

    let selectedAssignmentIds = $state<number[]>([]);
    let assignmentConflicts = $state<AssignmentConflict[]>([]);
    let assignmentConflictBusy = $state(false);
    let assignmentAllowConflict = $state(false);
    let charterViewId = $state<number | null>(null);
    let charterViewData = $state<Charter | null>(null);
    let charterViewBusy = $state(false);
    let charterScope = $state<CharterScope>('active');

    const charterPaymentStatusOptions = ['Belum Lunas', 'Lunas'];
    const luggageReceivedStatus = 'Diterima';
    const luggagePickedUpStatus = 'Dalam Perjalanan';
    const luggageArrivedStatus = 'Tiba di Tujuan';
    const luggagePaymentStatusOptions = ['Belum Bayar', 'Lunas'];
    const newCharterForm = () => ({ id: 0, pool_id: 0, master_carter_id: 0, name: '', company_name: '', phone: '', start_date: today, end_date: today, departure_time: '08:00', pickup_point: '', drop_point: '', unit_id: 0, armada_id: 0, armada_nopol: '', driver_name: '', price: 0, layanan: defaultCharterService, bop_price: 0, bop_status: 'pending', down_payment: 0, payment_status: 'Belum Lunas' });
    const newLuggageForm = () => ({ id: 0, pool_id: 0, sender_name: '', sender_phone: '', sender_address: '', receiver_name: '', receiver_phone: '', receiver_address: '', service_id: 0, rute_id: 0, tanggal: today, quantity: 1, notes: '', price: 0, status: luggageReceivedStatus, payment_status: 'Belum Bayar' });
    let charterForm = $state(newCharterForm());
    let charterUnitSearch = $state('');
    let charterUnitLookupOpen = $state(false);
    let charterArmadaSearch = $state('');
    let charterArmadaLookupOpen = $state(false);
    let charterArmadaBusy = $state(false);
    let charterDriverSearch = $state('');
    let charterDriverLookupOpen = $state(false);
    let charterCustomerQuery = $state('');
    let charterCustomerBusy = $state(false);
    let charterCustomerResults = $state<CharterCustomer[]>([]);
    let charterCustomerLookupOpen = $state(false);
    let charterRouteSearch = $state('');
    let charterRouteLookupOpen = $state(false);
    let charterCustomerSearchTimer: ReturnType<typeof setTimeout> | null = null;
    let charterArmadaSearchTimer: ReturnType<typeof setTimeout> | null = null;
    let luggageForm = $state(newLuggageForm());
    let luggageSenderLookupOpen = $state(false);
    let luggageSenderLookupBusy = $state(false);
    let luggageSenderLookupResults = $state<BagasiCustomer[]>([]);
    let luggageSenderSearchTimer: ReturnType<typeof setTimeout> | null = null;
    let luggageReceiverLookupOpen = $state(false);
    let luggageReceiverLookupBusy = $state(false);
    let luggageReceiverLookupResults = $state<BagasiCustomer[]>([]);
    let luggageReceiverSearchTimer: ReturnType<typeof setTimeout> | null = null;
    let assignmentForm = $state({ id: 0, rute: '', tanggal: today, jam: '08:00', unit: 1, driver_id: 0 });
    let charterStartDateInput = $state<HTMLInputElement | null>(null);
    let charterEndDateInput = $state<HTMLInputElement | null>(null);
    let charterDepartureTimeInput = $state<HTMLInputElement | null>(null);
    let charterStartDatePicker: FlatpickrInstance | null = null;
    let charterEndDatePicker: FlatpickrInstance | null = null;
    let charterDepartureTimePicker: FlatpickrInstance | null = null;
    let luggageDateInput = $state<HTMLInputElement | null>(null);
    let luggageDatePicker: FlatpickrInstance | null = null;
    let assignmentDateInput = $state<HTMLInputElement | null>(null);
    let assignmentDatePicker: FlatpickrInstance | null = null;
    let assignmentTimeInput = $state<HTMLInputElement | null>(null);
    let assignmentTimePicker: FlatpickrInstance | null = null;
    let CharterViewPanelComponent = $state<any>(null);
    let CharterFormPanelComponent = $state<any>(null);

    const flowTabs: TabName[] = ['charters', 'luggages', 'assignments', 'export'];
    const flowModes: ViewMode[] = ['data', 'form', 'view'];
    const ensureCharterViewPanelLoaded = async () => {
        if (!CharterViewPanelComponent) {
            CharterViewPanelComponent = (await import('@/components/admin-ops-flows/AdminOpsCharterViewPanel.svelte')).default;
        }
    };

    const ensureCharterFormPanelLoaded = async () => {
        if (!CharterFormPanelComponent) {
            CharterFormPanelComponent = (await import('@/components/admin-ops-flows/AdminOpsCharterFormPanel.svelte')).default;
        }
    };

    $effect(() => {
        if (activeTab !== 'charters') {
            return;
        }

        if (activeMode === 'view') {
            void ensureCharterViewPanelLoaded();
        }

        if (activeMode === 'form') {
            void ensureCharterFormPanelLoaded();
        }
    });

    const tabTitle = (tab: TabName) => {
        if (tab === 'charters') {
return 'Carter';
}

        if (tab === 'luggages') {
return 'Bagasi';
}

        if (tab === 'assignments') {
return 'Driver Assignments';
}

        return 'Export CSV';
    };

    const isFlowTab = (value: string | null): value is TabName => {
        return value !== null && flowTabs.includes(value as TabName);
    };

    const isFlowMode = (value: string | null): value is ViewMode => {
        return value !== null && flowModes.includes(value as ViewMode);
    };

    const hasDedicatedFormPage = (tab: TabName): tab is 'charters' | 'luggages' => {
        return tab === 'charters' || tab === 'luggages';
    };

    const lockedPathFor = (tab: TabName, mode: ViewMode, itemId?: number | null) => {
        if (tab === 'charters') {
            if (mode === 'form') {
return '/charters/form';
}

            if (mode === 'view' && itemId && itemId > 0) {
return `/charters/view/${itemId}`;
}

            return '/charters';
        }

        if (tab === 'luggages') {
return mode === 'form' ? '/luggages/form' : '/luggages';
}

        if (tab === 'assignments') {
return '/admin-ops/flows/assignments';
}

        return '/admin-ops/flows/export';
    };

    const setFormMode = (mode: ViewMode) => {
        if (mode === 'view') {
            return;
        }

        activeMode = mode;

        if (typeof window === 'undefined' || !lockedMenuView || !hasDedicatedFormPage(activeTab)) {
            return;
        }

        const targetPath = lockedPathFor(activeTab, mode);

        if (window.location.pathname !== targetPath) {
            window.history.pushState({}, '', targetPath);
        }
    };

    const syncTabQuery = (tab: TabName) => {
        if (typeof window === 'undefined') {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', `${url.pathname}?${url.searchParams.toString()}`);
    };

    const destroyCharterPickers = () => {
        charterStartDatePicker?.destroy();
        charterStartDatePicker = null;
        charterEndDatePicker?.destroy();
        charterEndDatePicker = null;
        charterDepartureTimePicker?.destroy();
        charterDepartureTimePicker = null;
    };

    const destroyCharterFilterDatePicker = () => {
        charterFilterDatePicker?.destroy();
        charterFilterDatePicker = null;
    };

    const destroyLuggageFilterDatePicker = () => {
        luggageFilterDatePicker?.destroy();
        luggageFilterDatePicker = null;
    };

    const destroyLuggageDatePicker = () => {
        luggageDatePicker?.destroy();
        luggageDatePicker = null;
    };

    const destroyAssignmentPickers = () => {
        assignmentDatePicker?.destroy();
        assignmentDatePicker = null;
        assignmentTimePicker?.destroy();
        assignmentTimePicker = null;
    };

    const destroyExportPickers = () => {
        exportFromDatePicker?.destroy();
        exportFromDatePicker = null;
        exportToDatePicker?.destroy();
        exportToDatePicker = null;
    };

    const initCharterFilterDatePicker = async () => {
        if (typeof window === 'undefined' || !charterFilterDateInput || charterFilterDatePicker) {
            return;
        }

        const flatpickr = await loadFlatpickr();
        if (!charterFilterDateInput || charterFilterDatePicker) {
            return;
        }

        charterFilterDatePicker = flatpickr(charterFilterDateInput, {
            dateFormat: 'Y-m-d',
            disableMobile: true,
            defaultDate: filterFrom || undefined,
            onChange: (selectedDates, dateStr) => {
                if (selectedDates.length === 0 || dateStr.trim() === '') {
                    filterFrom = '';
                    filterTo = '';

                    return;
                }

                filterFrom = dateStr;
                filterTo = dateStr;
            },
        });
    };

    const initLuggageFilterDatePicker = async () => {
        if (typeof window === 'undefined' || !luggageFilterDateInput || luggageFilterDatePicker) {
            return;
        }

        const flatpickr = await loadFlatpickr();
        if (!luggageFilterDateInput || luggageFilterDatePicker) {
            return;
        }

        luggageFilterDatePicker = flatpickr(luggageFilterDateInput, {
            dateFormat: 'Y-m-d',
            disableMobile: true,
            defaultDate: filterFrom || undefined,
            onChange: (selectedDates, dateStr) => {
                if (selectedDates.length === 0 || dateStr.trim() === '') {
                    filterFrom = '';
                    filterTo = '';

                    return;
                }

                filterFrom = dateStr;
                filterTo = dateStr;
            },
        });
    };

    const initLuggageDatePicker = async () => {
        if (typeof window === 'undefined' || !luggageDateInput || luggageDatePicker) {
            return;
        }

        const flatpickr = await loadFlatpickr();
        if (!luggageDateInput || luggageDatePicker) {
            return;
        }

        luggageDatePicker = flatpickr(luggageDateInput, {
            dateFormat: 'Y-m-d',
            disableMobile: true,
            defaultDate: luggageForm.tanggal || today,
            onChange: (_selectedDates, dateStr) => {
                luggageForm.tanggal = dateStr || today;
            },
        });
    };

    const initAssignmentPickers = async () => {
        if (typeof window === 'undefined') {
            return;
        }

        const flatpickr = await loadFlatpickr();

        if (assignmentDateInput && !assignmentDatePicker) {
            assignmentDatePicker = flatpickr(assignmentDateInput, {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                defaultDate: assignmentForm.tanggal || today,
                onChange: (_selectedDates, dateStr) => {
                    assignmentForm.tanggal = dateStr || today;
                },
            });
        }

        if (assignmentTimeInput && !assignmentTimePicker) {
            assignmentTimePicker = flatpickr(assignmentTimeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                disableMobile: true,
                defaultDate: assignmentForm.jam || '08:00',
                onChange: (_selectedDates, dateStr) => {
                    assignmentForm.jam = dateStr || '08:00';
                },
            });
        }
    };

    const initExportPickers = async () => {
        if (typeof window === 'undefined') {
            return;
        }

        const flatpickr = await loadFlatpickr();

        if (exportFromDateInput && !exportFromDatePicker) {
            exportFromDatePicker = flatpickr(exportFromDateInput, {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                defaultDate: filterFrom || undefined,
                onChange: (_selectedDates, dateStr) => {
                    filterFrom = dateStr || '';
                },
            });
        }

        if (exportToDateInput && !exportToDatePicker) {
            exportToDatePicker = flatpickr(exportToDateInput, {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                defaultDate: filterTo || undefined,
                onChange: (_selectedDates, dateStr) => {
                    filterTo = dateStr || '';
                },
            });
        }
    };

    const initCharterPickers = async () => {
        if (typeof window === 'undefined') {
            return;
        }

        const flatpickr = await loadFlatpickr();

        if (charterStartDateInput && !charterStartDatePicker) {
            charterStartDatePicker = flatpickr(charterStartDateInput, {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                minDate: today,
                defaultDate: charterForm.start_date || today,
                onChange: (_selectedDates, dateStr) => {
                    charterForm.start_date = dateStr || today;
                    charterEndDatePicker?.set('minDate', charterForm.start_date || today);

                    if (!charterForm.end_date || charterForm.end_date < charterForm.start_date) {
                        charterForm.end_date = charterForm.start_date;
                        charterEndDatePicker?.setDate(charterForm.end_date, false, 'Y-m-d');
                    }
                },
            });
        }

        if (charterEndDateInput && !charterEndDatePicker) {
            charterEndDatePicker = flatpickr(charterEndDateInput, {
                dateFormat: 'Y-m-d',
                disableMobile: true,
                minDate: charterForm.start_date || today,
                defaultDate: charterForm.end_date || today,
                onChange: (_selectedDates, dateStr) => {
                    charterForm.end_date = dateStr || charterForm.start_date || today;
                },
            });
        }

        if (charterDepartureTimeInput && !charterDepartureTimePicker) {
            charterDepartureTimePicker = flatpickr(charterDepartureTimeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                disableMobile: true,
                defaultDate: charterForm.departure_time || '08:00',
                onChange: (_selectedDates, dateStr) => {
                    charterForm.departure_time = dateStr || '08:00';
                },
            });
        }
    };

    const syncCharterPickerValues = () => {
        charterStartDatePicker?.set('minDate', today);
        charterEndDatePicker?.set('minDate', charterForm.start_date || today);
        charterStartDatePicker?.setDate(charterForm.start_date || today, false, 'Y-m-d');
        charterEndDatePicker?.setDate(charterForm.end_date || charterForm.start_date || today, false, 'Y-m-d');
        charterDepartureTimePicker?.setDate(charterForm.departure_time || '08:00', false, 'H:i');
    };

    const syncLuggageDatePickerValue = () => {
        luggageDatePicker?.setDate(luggageForm.tanggal || today, false, 'Y-m-d');
    };

    const syncAssignmentPickerValues = () => {
        assignmentDatePicker?.setDate(assignmentForm.tanggal || today, false, 'Y-m-d');
        assignmentTimePicker?.setDate(assignmentForm.jam || '08:00', false, 'H:i');
    };

    const csrfToken = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';

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
            const match = html.match(/<meta\s+name=["']csrf-token["']\s+content=["']([^"']+)["']/i);

            if (!match?.[1]) {
                return false;
            }

            let node = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;

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

    const sendApiRequest = async (method: 'GET' | 'POST' | 'DELETE', url: string, body?: Record<string, unknown>) => {
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

    const api = async (method: 'GET' | 'POST' | 'DELETE', url: string, body?: Record<string, unknown>) => {
        let res = await sendApiRequest(method, url, body);

        if (res.status === 419 && method !== 'GET' && await refreshCsrfToken()) {
            res = await sendApiRequest(method, url, body);
        }

        const json = await res.json().catch(() => ({}));
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

        if (!res.ok || json.success === false) {
            throw new Error(json.error || json.message || firstValidationError || `Request gagal (${res.status})`);
        }

        return json;
    };

    const queryString = (page: number) => {
        const q = new URLSearchParams();
        q.set('page', String(page));
        q.set('per_page', String(filterPerPage));
        q.set('from', filterFrom);
        q.set('to', filterTo);

        if (filterQuery.trim() !== '') {
q.set('q', filterQuery.trim());
}

        return q.toString();
    };

    const luggageQueryString = (page: number) => {
        const q = new URLSearchParams();
        q.set('page', String(page));
        q.set('per_page', String(luggageMeta.per_page || 20));
        q.set('from', filterFrom);
        q.set('to', filterTo);

        if (filterQuery.trim() !== '') {
q.set('q', filterQuery.trim());
}

        return q.toString();
    };

    const charterQueryString = (page: number) => {
        const q = new URLSearchParams();
        q.set('page', String(page));
        q.set('per_page', String(filterPerPage));
        q.set('from', filterFrom);
        q.set('to', filterTo);
        q.set('scope', charterScope);

        if (Number(charterFilterUnitId || 0) > 0) {
            q.set('unit_id', String(Number(charterFilterUnitId)));
        }

        if (Number(charterFilterArmadaId || 0) > 0) {
            q.set('armada_id', String(Number(charterFilterArmadaId)));
        }

        if (filterQuery.trim() !== '') {
q.set('q', filterQuery.trim());
}

        return q.toString();
    };

    const formatCurrencyId = (value: number | string | null | undefined) =>
        formatCurrencyDisplay(value);

    const activePools = () => pools.filter((pool) => String(pool.status ?? 'active').toLowerCase() === 'active');

    const defaultPoolId = () => {
        const options = activePools();

        return options.length === 1 ? Number(options[0].id || 0) : 0;
    };

    const poolLabel = (pool: PoolOption | null | undefined) => {
        if (!pool) {
            return '';
        }

        return [pool.name, pool.code ? `(${pool.code})` : ''].filter(Boolean).join(' ');
    };

    const poolNameById = (poolId: number | null | undefined) => {
        const id = Number(poolId || 0);
        const pool = pools.find((item) => Number(item.id || 0) === id);

        return poolLabel(pool) || '-';
    };

    const poolForRouteId = (routeId: number | null | undefined) => {
        const id = Number(routeId || 0);

        if (id <= 0) {
            return null;
        }

        return activePools().find((pool) => Array.isArray(pool.route_ids) && pool.route_ids.map(Number).includes(id)) ?? null;
    };

    const applyDefaultPoolToForms = () => {
        const id = defaultPoolId();

        if (id <= 0) {
            return;
        }

        if (Number(charterForm.pool_id || 0) <= 0) {
            charterForm.pool_id = id;
        }

        if (Number(luggageForm.pool_id || 0) <= 0) {
            luggageForm.pool_id = id;
        }
    };

    const syncLuggagePoolFromRoute = () => {
        const pool = poolForRouteId(Number(luggageForm.rute_id || 0));

        if (pool) {
            luggageForm.pool_id = pool.id;

            return;
        }

        if (Number(luggageForm.pool_id || 0) <= 0) {
            luggageForm.pool_id = defaultPoolId();
        }
    };

    const charterStatusClass = (status: string | null | undefined) => {
        const normalized = String(status ?? 'active').toLowerCase();

        if (normalized === 'canceled') {
            return 'bg-rose-100 text-rose-700 border border-rose-200';
        }

        if (normalized === 'done') {
            return 'bg-indigo-100 text-indigo-700 border border-indigo-200';
        }

        return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
    };

    const charterStatusLabel = (status: string | null | undefined) => {
        const normalized = String(status ?? 'active').toLowerCase();

        if (normalized === 'canceled') {
return 'Canceled';
}

        if (normalized === 'done') {
return 'Selesai';
}

        return 'Active';
    };

    const isPaymentLunas = (paymentStatus: string | null | undefined) => {
        return String(paymentStatus ?? '').trim().toLowerCase() === 'lunas';
    };

    const charterPaymentClass = (paymentStatus: string | null | undefined) => {
        const normalized = String(paymentStatus ?? '').toLowerCase();

        if (normalized.includes('lunas') || normalized.includes('paid')) {
            return 'bg-sky-100 text-sky-700 border border-sky-200';
        }

        if (normalized === 'dp') {
            return 'bg-violet-100 text-violet-700 border border-violet-200';
        }

        return 'bg-amber-100 text-amber-700 border border-amber-200';
    };

    const charterUnitLabel = (unit: Unit | null | undefined) => {
        if (!unit) {
            return '';
        }

        return [String(unit.category ?? '').trim(), String(unit.nopol ?? '').trim()].filter(Boolean).join(' | ');
    };

    const charterUnitMeta = (unit: Unit | null | undefined) => {
        if (!unit) {
            return '-';
        }

        return [String(unit.merek ?? '').trim(), String(unit.type ?? '').trim(), String(unit.kapasitas ?? '').trim() !== '' ? `${unit.kapasitas} seat` : '']
            .filter(Boolean)
            .join(' | ');
    };

    const selectedCharterUnit = () => units.find((unit) => unit.id === Number(charterForm.unit_id || 0)) ?? null;

    const selectedCharterUnitCategory = () => String(selectedCharterUnit()?.category ?? '').trim();

    const selectedCharterArmada = () => armadas.find((armada) => armada.id === Number(charterForm.armada_id || 0)) ?? null;
    const selectedCharterDriver = () => {
        const selectedName = String(charterForm.driver_name ?? '').trim().toLowerCase();

        if (selectedName === '') {
            return null;
        }

        return drivers.find((driver) => String(driver.nama ?? '').trim().toLowerCase() === selectedName) ?? null;
    };

    const selectedCharterFilterUnit = () => units.find((unit) => unit.id === Number(charterFilterUnitId || 0)) ?? null;

    const selectedCharterFilterUnitCategory = () => String(selectedCharterFilterUnit()?.category ?? '').trim();

    const sameArmadaCategory = (left: string | null | undefined, right: string | null | undefined) => {
        return String(left ?? '').trim().toLowerCase() === String(right ?? '').trim().toLowerCase();
    };

    const syncCharterArmadaLookupsFromForm = () => {
        charterUnitLookupOpen = false;
        charterArmadaLookupOpen = false;
        charterUnitSearch = charterUnitLabel(selectedCharterUnit());
        charterArmadaSearch = String(charterForm.armada_nopol ?? '').trim();
    };

    const syncCharterDriverLookupFromForm = () => {
        charterDriverLookupOpen = false;
        charterDriverSearch = String(charterForm.driver_name ?? '').trim();
    };

    const resetCharterArmadaLookup = () => {
        charterUnitSearch = '';
        charterUnitLookupOpen = false;
        charterArmadaSearch = '';
        charterArmadaLookupOpen = false;
        charterArmadaBusy = false;
        armadas = [];

        if (charterArmadaSearchTimer) {
            clearTimeout(charterArmadaSearchTimer);
            charterArmadaSearchTimer = null;
        }
    };

    const syncCharterFilterLookups = () => {
        charterFilterUnitLookupOpen = false;
        charterFilterArmadaLookupOpen = false;
        charterFilterUnitSearch = charterUnitLabel(selectedCharterFilterUnit());

        const selectedArmada = charterFilterArmadas.find((armada) => armada.id === Number(charterFilterArmadaId || 0));
        charterFilterArmadaSearch = selectedArmada?.nopol ?? '';
    };

    const resetCharterFilterLookups = () => {
        charterFilterUnitId = 0;
        charterFilterUnitSearch = '';
        charterFilterUnitLookupOpen = false;
        charterFilterArmadaId = 0;
        charterFilterArmadaSearch = '';
        charterFilterArmadaLookupOpen = false;
        charterFilterArmadaBusy = false;
        charterFilterArmadas = [];

        if (charterFilterArmadaSearchTimer) {
            clearTimeout(charterFilterArmadaSearchTimer);
            charterFilterArmadaSearchTimer = null;
        }
    };

    const filteredCharterUnits = () => {
        const keyword = charterUnitSearch.trim().toLowerCase();

        if (keyword === '') {
            return units;
        }

        return units.filter((unit) => {
            const haystacks = [
                String(unit.nopol ?? '').toLowerCase(),
                String(unit.category ?? '').toLowerCase(),
                String(unit.merek ?? '').toLowerCase(),
                String(unit.type ?? '').toLowerCase(),
            ];

            return haystacks.some((value) => value.includes(keyword));
        });
    };

    const filteredCharterFilterUnits = () => {
        const keyword = charterFilterUnitSearch.trim().toLowerCase();

        if (keyword === '') {
            return units;
        }

        return units.filter((unit) => {
            const haystacks = [
                String(unit.nopol ?? '').toLowerCase(),
                String(unit.category ?? '').toLowerCase(),
                String(unit.merek ?? '').toLowerCase(),
                String(unit.type ?? '').toLowerCase(),
            ];

            return haystacks.some((value) => value.includes(keyword));
        });
    };

    const filteredCharterDrivers = () => {
        const keyword = charterDriverSearch.trim().toLowerCase();

        if (keyword === '') {
            return drivers;
        }

        return drivers.filter((driver) => {
            const haystacks = [
                String(driver.nama ?? '').toLowerCase(),
                String(driver.phone ?? '').toLowerCase(),
            ];

            return haystacks.some((value) => value.includes(keyword));
        });
    };

    const loadCharterArmadas = async (keywordRaw = '') => {
        const params = new URLSearchParams();
        const keyword = keywordRaw.trim();
        const selectedCategory = selectedCharterUnitCategory();

        if (keyword !== '') {
            params.set('q', keyword);
        }

        if (selectedCategory !== '') {
            params.set('kategori', selectedCategory);
        }

        charterArmadaBusy = true;

        try {
            const suffix = params.toString();
            const r = await api('GET', `/api/admin/armadas${suffix ? `?${suffix}` : ''}`);
            armadas = (r.armadas ?? []) as Armada[];
        } catch {
            armadas = [];
        } finally {
            charterArmadaBusy = false;
        }
    };

    const loadCharterFilterArmadas = async (keywordRaw = '') => {
        const params = new URLSearchParams();
        const keyword = keywordRaw.trim();
        const selectedCategory = selectedCharterFilterUnitCategory();

        if (keyword !== '') {
            params.set('q', keyword);
        }

        if (selectedCategory !== '') {
            params.set('kategori', selectedCategory);
        }

        charterFilterArmadaBusy = true;

        try {
            const suffix = params.toString();
            const r = await api('GET', `/api/admin/armadas${suffix ? `?${suffix}` : ''}`);
            charterFilterArmadas = (r.armadas ?? []) as Armada[];
        } catch {
            charterFilterArmadas = [];
        } finally {
            charterFilterArmadaBusy = false;
        }
    };

    const filteredCharterArmadas = () => {
        const keyword = charterArmadaSearch.trim().toLowerCase();

        if (keyword === '') {
            return armadas;
        }

        return armadas.filter((armada) => {
            const haystacks = [
                String(armada.nopol ?? '').toLowerCase(),
                String(armada.kategori ?? '').toLowerCase(),
                String(armada.merk ?? '').toLowerCase(),
                String(armada.tahun ?? '').toLowerCase(),
            ];

            return haystacks.some((value) => value.includes(keyword));
        });
    };

    const filteredCharterFilterArmadas = () => {
        const keyword = charterFilterArmadaSearch.trim().toLowerCase();

        if (keyword === '') {
            return charterFilterArmadas;
        }

        return charterFilterArmadas.filter((armada) => {
            const haystacks = [
                String(armada.nopol ?? '').toLowerCase(),
                String(armada.kategori ?? '').toLowerCase(),
                String(armada.merk ?? '').toLowerCase(),
                String(armada.tahun ?? '').toLowerCase(),
            ];

            return haystacks.some((value) => value.includes(keyword));
        });
    };

    const selectCharterUnit = (unit: Unit) => {
        charterForm.unit_id = unit.id;
        charterUnitSearch = charterUnitLabel(unit);
        charterUnitLookupOpen = false;

        const currentArmada = selectedCharterArmada();

        if (
            currentArmada
            && String(currentArmada.kategori ?? '').trim() !== ''
            && String(unit.category ?? '').trim() !== ''
            && !sameArmadaCategory(currentArmada.kategori, unit.category)
        ) {
            charterForm.armada_id = 0;
            charterForm.armada_nopol = '';
            charterArmadaSearch = '';
            armadas = [];
        }
    };

    const selectCharterArmada = (armada: Armada) => {
        charterForm.armada_id = armada.id;
        charterForm.armada_nopol = armada.nopol;
        charterArmadaSearch = armada.nopol;
        charterArmadaLookupOpen = false;
    };

    const selectCharterDriver = (driver: Driver) => {
        charterForm.driver_name = driver.nama;
        charterDriverSearch = driver.nama;
        charterDriverLookupOpen = false;
    };

    const selectCharterFilterUnit = (unit: Unit) => {
        charterFilterUnitId = unit.id;
        charterFilterUnitSearch = charterUnitLabel(unit);
        charterFilterUnitLookupOpen = false;

        const selectedArmada = charterFilterArmadas.find((armada) => armada.id === Number(charterFilterArmadaId || 0));

        if (
            selectedArmada
            && String(selectedArmada.kategori ?? '').trim() !== ''
            && String(unit.category ?? '').trim() !== ''
            && !sameArmadaCategory(selectedArmada.kategori, unit.category)
        ) {
            charterFilterArmadaId = 0;
            charterFilterArmadaSearch = '';
        }

        charterFilterArmadas = [];
    };

    const selectCharterFilterArmada = (armada: Armada) => {
        charterFilterArmadaId = armada.id;
        charterFilterArmadaSearch = armada.nopol;
        charterFilterArmadaLookupOpen = false;
    };

    const queueCharterArmadaSearch = (value: string) => {
        charterArmadaSearch = value;

        if (charterArmadaSearchTimer) {
            clearTimeout(charterArmadaSearchTimer);
            charterArmadaSearchTimer = null;
        }

        charterArmadaSearchTimer = setTimeout(() => {
            void loadCharterArmadas(value);
        }, 250);
    };

    const queueCharterFilterArmadaSearch = (value: string) => {
        charterFilterArmadaSearch = value;

        if (charterFilterArmadaSearchTimer) {
            clearTimeout(charterFilterArmadaSearchTimer);
            charterFilterArmadaSearchTimer = null;
        }

        charterFilterArmadaSearchTimer = setTimeout(() => {
            void loadCharterFilterArmadas(value);
        }, 250);
    };

    const onCharterUnitBlur = () => {
        setTimeout(() => {
            charterUnitLookupOpen = false;
            charterUnitSearch = charterUnitLabel(selectedCharterUnit());
        }, 120);
    };

    const onCharterArmadaBlur = () => {
        setTimeout(() => {
            charterArmadaLookupOpen = false;
            charterArmadaSearch = String(charterForm.armada_nopol ?? '').trim();
        }, 120);
    };

    const onCharterDriverBlur = () => {
        setTimeout(() => {
            charterDriverLookupOpen = false;

            const typed = charterDriverSearch.trim();

            if (typed === '') {
                charterForm.driver_name = '';
                charterDriverSearch = '';

                return;
            }

            const exact = drivers.find((driver) => String(driver.nama ?? '').trim().toLowerCase() === typed.toLowerCase());

            if (exact) {
                charterForm.driver_name = exact.nama;
                charterDriverSearch = exact.nama;

                return;
            }

            charterDriverSearch = String(charterForm.driver_name ?? '').trim();
        }, 120);
    };

    const onCharterFilterUnitBlur = () => {
        setTimeout(() => {
            charterFilterUnitLookupOpen = false;
            charterFilterUnitSearch = charterUnitLabel(selectedCharterFilterUnit());
        }, 120);
    };

    const onCharterFilterArmadaBlur = () => {
        setTimeout(() => {
            charterFilterArmadaLookupOpen = false;

            const selectedArmada = charterFilterArmadas.find((armada) => armada.id === Number(charterFilterArmadaId || 0));
            charterFilterArmadaSearch = selectedArmada?.nopol ?? '';
        }, 120);
    };

    const charterCanceledCount = () =>
        charters.filter((row) => String(row.status ?? 'active').toLowerCase() === 'canceled').length;

    const charterActiveCount = () => Math.max(charters.length - charterCanceledCount(), 0);

    const charterIsDone = (status: string | null | undefined) => String(status ?? 'active').toLowerCase() === 'done';

    const charterIsCanceled = (status: string | null | undefined) => String(status ?? 'active').toLowerCase() === 'canceled';

    const charterCanEdit = (row: Charter | null | undefined) => !!row && !charterIsCanceled(row.status) && !charterIsDone(row.status);

    const charterCanCancel = (row: Charter | null | undefined) => !!row && !charterIsCanceled(row.status) && !charterIsDone(row.status);

    const charterCanMarkDone = (row: Charter | null | undefined) =>
        !!row && !charterIsCanceled(row.status) && !charterIsDone(row.status) && isPaymentLunas(row.payment_status);

    const charterMarkDoneHint = (row: Charter | null | undefined) => {
        if (!row) {
            return '';
        }

        if (charterIsDone(row.status)) {
            return 'Perjalanan sudah selesai dan data ini sudah masuk history.';
        }

        if (charterIsCanceled(row.status)) {
            return 'Perjalanan yang dibatalkan tidak bisa diselesaikan.';
        }

        if (!isPaymentLunas(row.payment_status)) {
            return 'Pelunasan pembayaran diperlukan sebelum perjalanan dipindahkan ke history.';
        }

        return 'Klik selesaikan perjalanan untuk memindahkan data ini ke history.';
    };

    const charterPaymentRemaining = (row: Charter | null | undefined) => {
        const price = Number(row?.price ?? 0);
        const downPayment = Number(row?.down_payment ?? 0);

        return Math.max(price - downPayment, 0);
    };

    const normalizeLuggageStatus = (status: string | null | undefined) => {
        const normalized = String(status ?? '').trim().toLowerCase();

        if (
            normalized === ''
            || normalized === 'pending'
            || normalized === 'done'
            || normalized === 'diterima'
            || normalized === 'barang sudah diterima'
            || normalized === luggageReceivedStatus.toLowerCase()
        ) {
            return luggageReceivedStatus;
        }

        if (
            normalized === 'active'
            || normalized === 'sent'
            || normalized === 'barang sudah dipickup'
            || normalized === luggagePickedUpStatus.toLowerCase()
        ) {
            return luggagePickedUpStatus;
        }

        if (normalized === 'barang sudah tiba' || normalized === luggageArrivedStatus.toLowerCase()) {
            return luggageArrivedStatus;
        }

        if (normalized === 'canceled') {
            return 'canceled';
        }

        return String(status ?? '').trim() || luggageReceivedStatus;
    };

    const luggageStatusLabel = (status: string | null | undefined) => {
        const normalized = normalizeLuggageStatus(status);

        return normalized === 'canceled' ? 'Canceled' : normalized;
    };

    const luggageStatusClass = (status: string | null | undefined) => {
        const normalized = normalizeLuggageStatus(status).toLowerCase();

        if (normalized === 'canceled') {
            return 'bg-rose-100 text-rose-700 border border-rose-200';
        }

        if (normalized === luggageArrivedStatus.toLowerCase()) {
            return 'bg-indigo-100 text-indigo-700 border border-indigo-200';
        }

        if (normalized === luggagePickedUpStatus.toLowerCase()) {
            return 'bg-sky-100 text-sky-700 border border-sky-200';
        }

        return 'bg-amber-100 text-amber-700 border border-amber-200';
    };

    const luggagePaymentClass = (paymentStatus: string | null | undefined) => {
        const normalized = String(paymentStatus ?? '').toLowerCase();

        if (normalized.includes('lunas') || normalized.includes('paid')) {
            return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
        }

        return 'bg-amber-100 text-amber-700 border border-amber-200';
    };

    const luggageDepartureInfo = (row: Luggage) => {
        const normalizedStatus = normalizeLuggageStatus(row.status);
        const departureDate = String(row.departure_date ?? '').trim();
        const departureTime = String(row.departure_time ?? '').trim().slice(0, 5);
        const departureUnit = Number(row.departure_unit ?? 0);

        if (![luggagePickedUpStatus, luggageArrivedStatus].includes(normalizedStatus) || departureDate === '') {
            return null;
        }

        const primary = [
            departureDate,
            departureTime !== '' ? departureTime : '',
            departureUnit > 0 ? `Unit ${departureUnit}` : '',
        ]
            .filter(Boolean)
            .join(' • ');

        const secondary = [String(row.departure_driver_name ?? '').trim(), String(row.departure_armada_nopol ?? '').trim()]
            .filter(Boolean)
            .join(' • ');

        return {
            primary: primary || '-',
            secondary: secondary || '-',
        };
    };

    const toCharterFormFromRow = (row: Charter) => ({
        id: row.id,
        pool_id: row.pool_id ?? defaultPoolId(),
        master_carter_id: row.master_carter_id ?? 0,
        name: row.name,
        company_name: row.company_name ?? '',
        phone: row.phone ?? '',
        start_date: row.start_date,
        end_date: row.end_date,
        departure_time: row.departure_time ? String(row.departure_time).slice(0, 5) : '08:00',
        pickup_point: row.pickup_point ?? '',
        drop_point: row.drop_point ?? '',
        unit_id: row.unit_id ?? 0,
        armada_id: row.armada_id ?? 0,
        armada_nopol: row.armada_nopol ?? '',
        driver_name: row.driver_name ?? '',
        price: Number(row.price ?? 0),
        layanan: row.layanan ?? defaultCharterService,
        bop_price: Number(row.bop_price ?? 0),
        bop_status: row.bop_status ?? 'pending',
        down_payment: Number(row.down_payment ?? 0),
        payment_status: charterPaymentStatusOptions.includes(String(row.payment_status ?? ''))
            ? String(row.payment_status)
            : 'Belum Lunas',
    });

    const resetCharterCustomerLookup = () => {
        charterCustomerQuery = '';
        charterCustomerResults = [];
        charterCustomerBusy = false;
        charterCustomerLookupOpen = false;

        if (charterCustomerSearchTimer) {
            clearTimeout(charterCustomerSearchTimer);
            charterCustomerSearchTimer = null;
        }
    };

    const resetCharterFormState = () => {
        charterForm = newCharterForm();
        charterForm.pool_id = defaultPoolId();
        resetCharterCustomerLookup();
        syncCharterRouteLookupFromForm();
        resetCharterArmadaLookup();
        syncCharterDriverLookupFromForm();
    };

    const resetLuggageFormState = () => {
        luggageForm = newLuggageForm();
        luggageForm.pool_id = defaultPoolId();
        luggageSenderLookupOpen = false;
        luggageSenderLookupBusy = false;
        luggageSenderLookupResults = [];
        luggageReceiverLookupOpen = false;
        luggageReceiverLookupBusy = false;
        luggageReceiverLookupResults = [];

        if (luggageSenderSearchTimer) {
            clearTimeout(luggageSenderSearchTimer);
            luggageSenderSearchTimer = null;
        }

        if (luggageReceiverSearchTimer) {
            clearTimeout(luggageReceiverSearchTimer);
            luggageReceiverSearchTimer = null;
        }
    };

    const openLuggageEditor = (row: Luggage) => {
        const normalizedLuggageStatus = normalizeLuggageStatus(row.status);
        resetLuggageFormState();
        luggageForm = {
            id: row.id,
            pool_id: row.pool_id ?? defaultPoolId(),
            sender_name: row.sender_name ?? '',
            sender_phone: row.sender_phone ?? '',
            sender_address: row.sender_address ?? '',
            receiver_name: row.receiver_name ?? '',
            receiver_phone: row.receiver_phone ?? '',
            receiver_address: row.receiver_address ?? '',
            service_id: row.service_id ?? 0,
            rute_id: row.rute_id ?? 0,
            tanggal: row.tanggal ?? today,
            quantity: Number(row.quantity ?? 1),
            notes: row.notes ?? '',
            price: Number(row.price ?? 0),
            status: normalizedLuggageStatus,
            payment_status: row.payment_status ?? 'Belum Bayar',
        };
        setFormMode('form');
    };

    const bagasiCustomerLabel = (customer: BagasiCustomer) => `${String(customer.nama ?? '').trim()} · ${String(customer.no_hp ?? '').trim()}`;

    const applyLuggageCustomer = (customer: BagasiCustomer, role: 'sender' | 'receiver') => {
        if (role === 'sender') {
            luggageForm.sender_name = customer.nama ?? '';
            luggageForm.sender_phone = customer.no_hp ?? '';
            luggageForm.sender_address = customer.alamat ?? '';
            luggageSenderLookupOpen = false;
            luggageSenderLookupResults = [];

            return;
        }

        luggageForm.receiver_name = customer.nama ?? '';
        luggageForm.receiver_phone = customer.no_hp ?? '';
        luggageForm.receiver_address = customer.alamat ?? '';
        luggageReceiverLookupOpen = false;
        luggageReceiverLookupResults = [];
    };

    const findExactBagasiCustomer = (keyword: string, customers: BagasiCustomer[]) => {
        const text = normalizeLookupText(keyword);
        const phone = normalizeLookupPhone(keyword);

        if (phone.length >= 5) {
            const phoneMatch = customers.find((customer) => normalizeLookupPhone(customer.no_hp) === phone);

            if (phoneMatch) {
                return phoneMatch;
            }
        }

        if (text.length >= 3) {
            return customers.find((customer) => normalizeLookupText(customer.nama) === text) ?? null;
        }

        return null;
    };

    const closeLuggageCustomerLookup = (role: 'sender' | 'receiver') => {
        if (role === 'sender') {
            luggageSenderLookupOpen = false;

            return;
        }

        luggageReceiverLookupOpen = false;
    };

    const searchLuggageCustomers = async (keywordRaw: string, role: 'sender' | 'receiver') => {
        const keyword = keywordRaw.trim();
        const minLength = 2;

        if (keyword.length < minLength) {
            closeLuggageCustomerLookup(role);

            if (role === 'sender') {
                luggageSenderLookupResults = [];
                luggageSenderLookupBusy = false;

                return;
            }

            luggageReceiverLookupResults = [];
            luggageReceiverLookupBusy = false;

            return;
        }

        if (role === 'sender') {
            luggageSenderLookupBusy = true;
        } else {
            luggageReceiverLookupBusy = true;
        }

        try {
            const r = await api('GET', `/api/admin/customer-bagasi?q=${encodeURIComponent(keyword)}&page=1&per_page=8`);
            const customers = (r.customers ?? []) as BagasiCustomer[];
            const exactCustomer = findExactBagasiCustomer(keyword, customers);

            if (exactCustomer) {
                applyLuggageCustomer(exactCustomer, role);

                return;
            }

            if (role === 'sender') {
                luggageSenderLookupResults = customers;
                luggageSenderLookupOpen = true;
            } else {
                luggageReceiverLookupResults = customers;
                luggageReceiverLookupOpen = true;
            }
        } catch {
            if (role === 'sender') {
                luggageSenderLookupResults = [];
                luggageSenderLookupOpen = false;
            } else {
                luggageReceiverLookupResults = [];
                luggageReceiverLookupOpen = false;
            }
        } finally {
            if (role === 'sender') {
                luggageSenderLookupBusy = false;
            } else {
                luggageReceiverLookupBusy = false;
            }
        }
    };

    const onLuggageCustomerInput = (value: string, role: 'sender' | 'receiver') => {
        if (role === 'sender') {
            luggageForm.sender_name = value;

            if (luggageSenderSearchTimer) {
                clearTimeout(luggageSenderSearchTimer);
                luggageSenderSearchTimer = null;
            }

            luggageSenderSearchTimer = setTimeout(() => {
                void searchLuggageCustomers(value, role);
            }, 220);

            return;
        }

        luggageForm.receiver_name = value;

        if (luggageReceiverSearchTimer) {
            clearTimeout(luggageReceiverSearchTimer);
            luggageReceiverSearchTimer = null;
        }

        luggageReceiverSearchTimer = setTimeout(() => {
            void searchLuggageCustomers(value, role);
        }, 220);
    };

    const onLuggageCustomerFocus = (role: 'sender' | 'receiver') => {
        const currentValue = role === 'sender' ? luggageForm.sender_name : luggageForm.receiver_name;

        if (currentValue.trim().length >= 2) {
            void searchLuggageCustomers(currentValue, role);
        }
    };

    const onLuggageCustomerBlur = (role: 'sender' | 'receiver') => {
        setTimeout(() => {
            closeLuggageCustomerLookup(role);
        }, 140);
    };

    const applyCharterCustomer = (customer: CharterCustomer) => {
        charterForm.name = customer.nama ?? '';
        charterForm.phone = customer.no_hp ?? '';
        charterForm.company_name = customer.company ?? '';

        if ((charterForm.pickup_point ?? '').trim() === '' && customer.alamat) {
            charterForm.pickup_point = customer.alamat;
        }

        charterCustomerQuery = `${customer.nama} - ${customer.no_hp}`;
        charterCustomerResults = [];
        charterCustomerLookupOpen = false;
    };

    const normalizeLookupText = (value: string | null | undefined) =>
        (value ?? '').trim().replace(/\s+/g, ' ').toLowerCase();

    const normalizeLookupPhone = (value: string | null | undefined) =>
        (value ?? '').replace(/\D+/g, '');

    const findExactCharterCustomer = (keyword: string, customers: CharterCustomer[]) => {
        const text = normalizeLookupText(keyword);
        const phone = normalizeLookupPhone(keyword);

        if (phone.length >= 5) {
            const phoneMatch = customers.find((customer) => normalizeLookupPhone(customer.no_hp) === phone);

            if (phoneMatch) {
                return phoneMatch;
            }
        }

        if (text.length >= 3) {
            return customers.find((customer) => normalizeLookupText(customer.nama) === text) ?? null;
        }

        return null;
    };

    const charterRouteLabel = (route: CharterRoute | null | undefined) => {
        if (!route) {
            return '';
        }

        const name = String(route.name ?? '').trim();
        if (name !== '') {
            return name;
        }

        return [route.origin, route.destination].filter(Boolean).join(' - ');
    };

    const charterRouteMeta = (route: CharterRoute | null | undefined) => {
        if (!route) {
            return '';
        }

        return [
            [route.origin, route.destination].filter(Boolean).join(' -> '),
            route.duration,
            `Harga ${formatCurrencyId(route.rental_price ?? 0)}`,
            `BOP ${formatCurrencyId(route.bop_price ?? 0)}`,
        ].filter(Boolean).join(' | ');
    };

    const filteredCharterRoutes = () => {
        const keyword = normalizeLookupText(charterRouteSearch);
        const source = keyword === ''
            ? charterRoutes
            : charterRoutes.filter((route) => {
                const haystack = [
                    route.name,
                    route.origin,
                    route.destination,
                    route.duration,
                ].map((value) => normalizeLookupText(value)).join(' ');

                return haystack.includes(keyword);
            });

        return source.slice(0, 12);
    };

    const syncCharterRouteLookupFromForm = () => {
        const origin = normalizeLookupText(charterForm.pickup_point);
        const destination = normalizeLookupText(charterForm.drop_point);

        const exactRoute = charterRoutes.find((route) =>
            origin !== ''
            && destination !== ''
            && normalizeLookupText(route.origin) === origin
            && normalizeLookupText(route.destination) === destination
        );

        charterRouteSearch = exactRoute
            ? charterRouteLabel(exactRoute)
            : [charterForm.pickup_point, charterForm.drop_point].filter(Boolean).join(' - ');
        charterForm.master_carter_id = exactRoute ? exactRoute.id : Number(charterForm.master_carter_id || 0);
    };

    const selectCharterRoute = (route: CharterRoute) => {
        charterRouteSearch = charterRouteLabel(route);
        charterRouteLookupOpen = false;
        charterForm.master_carter_id = route.id;
        charterForm.pickup_point = route.origin ?? '';
        charterForm.drop_point = route.destination ?? '';
        charterForm.layanan = route.duration ?? defaultCharterService;
        charterForm.price = Number(route.rental_price ?? 0);
        charterForm.bop_price = Number(route.bop_price ?? 0);
    };

    const onCharterRouteBlur = () => {
        setTimeout(() => {
            charterRouteLookupOpen = false;
        }, 120);
    };

    const searchCharterCustomers = async (keywordRaw: string) => {
        const keyword = keywordRaw.trim();

        if (keyword.length < 2) {
            charterCustomerResults = [];
            charterCustomerLookupOpen = false;
            charterCustomerBusy = false;

            return;
        }

        charterCustomerBusy = true;

        try {
            const r = await api('GET', `/api/admin/customer-charter?q=${encodeURIComponent(keyword)}&page=1&per_page=8`);
            const customers = (r.customers ?? []) as CharterCustomer[];
            const exactCustomer = findExactCharterCustomer(keyword, customers);

            charterCustomerResults = customers;

            if (exactCustomer) {
                applyCharterCustomer(exactCustomer);

                return;
            }

            charterCustomerLookupOpen = true;
        } catch {
            charterCustomerResults = [];
            charterCustomerLookupOpen = false;
        } finally {
            charterCustomerBusy = false;
        }
    };

    const onCharterCustomerQueryInput = (value: string) => {
        charterCustomerQuery = value;

        if (charterCustomerSearchTimer) {
            clearTimeout(charterCustomerSearchTimer);
            charterCustomerSearchTimer = null;
        }

        charterCustomerSearchTimer = setTimeout(() => {
            void searchCharterCustomers(value);
        }, 250);
    };

    const loadMasters = async () => {
        const [r, u, d, s, cr, p] = await Promise.all([
            api('GET', '/api/admin/routes'),
            api('GET', '/api/admin/units'),
            api('GET', '/api/admin/drivers'),
            api('GET', '/api/admin/luggage-services'),
            api('GET', '/api/master/charter-routes'),
            api('GET', '/api/admin/pools'),
        ]);
        routes = r.routes ?? [];
        units = u.units ?? [];
        drivers = d.drivers ?? [];
        services = s.services ?? [];
        charterRoutes = cr.routes ?? [];
        pools = p.pools ?? [];
        applyDefaultPoolToForms();
        syncCharterRouteLookupFromForm();
        syncCharterArmadaLookupsFromForm();
        syncCharterDriverLookupFromForm();
        syncCharterFilterLookups();
    };

    const loadCharters = async (page = 1) => {
        const r = await api('GET', `/api/admin/charters?${charterQueryString(page)}`);
        charters = r.charters ?? [];
        charterMeta = r.pagination ?? charterMeta;
    };

    const loadCharterView = async (id: number) => {
        if (id <= 0) {
            charterViewData = null;
            charterViewId = null;

            return;
        }

        charterViewBusy = true;

        try {
            const r = await api('GET', `/api/admin/charters/${id}`);
            charterViewData = (r.charter ?? null) as Charter | null;
            charterViewId = id;
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat detail charter.';
            charterViewData = null;
            charterViewId = null;
            activeMode = 'data';
        } finally {
            charterViewBusy = false;
        }
    };

    const openCharterView = async (id: number) => {
        if (id <= 0) {
return;
}

        activeMode = 'view';
        await loadCharterView(id);

        if (typeof window !== 'undefined' && lockedMenuView) {
            const targetPath = lockedPathFor('charters', 'view', id);

            if (window.location.pathname !== targetPath) {
                window.history.pushState({}, '', targetPath);
            }
        }
    };

    const closeCharterView = () => {
        activeMode = 'data';
        charterViewData = null;
        charterViewId = null;

        if (typeof window !== 'undefined' && lockedMenuView) {
            const targetPath = lockedPathFor('charters', 'data');

            if (window.location.pathname !== targetPath) {
                window.history.pushState({}, '', targetPath);
            }
        }
    };

    const openCharterEditor = (row: Charter) => {
        charterForm = toCharterFormFromRow(row);
        resetCharterCustomerLookup();
        syncCharterRouteLookupFromForm();
        syncCharterArmadaLookupsFromForm();
        syncCharterDriverLookupFromForm();
        setFormMode('form');
    };

    const loadLuggages = async (page = 1) => {
        const r = await api('GET', `/api/admin/luggages?${luggageQueryString(page)}`);
        luggages = Array.isArray(r.luggages)
            ? r.luggages.map((row: Luggage) => ({
                ...row,
                status: normalizeLuggageStatus(row.status),
            }))
            : [];
        luggageMeta = r.pagination ?? luggageMeta;
    };

    const loadAssignments = async (page = 1) => {
        const r = await api('GET', `/api/admin/assignments?${queryString(page)}`);
        assignments = r.assignments ?? [];
        assignmentMeta = r.pagination ?? assignmentMeta;
        selectedAssignmentIds = [];
    };

    const loadActiveTab = async () => {
        busy = true;
        error = '';

        try {
            if (activeTab === 'charters') {
await loadCharters(charterMeta.page);
}

            if (activeTab === 'luggages') {
await loadLuggages(luggageMeta.page);
}

            if (activeTab === 'assignments') {
await loadAssignments(assignmentMeta.page);
}
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat data tab.';
        } finally {
            busy = false;
        }
    };

    const applyFilters = async () => {
        charterMeta.page = 1;
        luggageMeta.page = 1;
        assignmentMeta.page = 1;
        await loadActiveTab();
    };

    const resetCharterFilters = async () => {
        filterFrom = '';
        filterTo = '';
        filterQuery = '';
        charterScope = 'active';
        charterMeta.page = 1;
        resetCharterFilterLookups();
        charterFilterDatePicker?.clear();
        await loadActiveTab();
    };

    const resetLuggageFilters = async () => {
        filterFrom = '';
        filterTo = '';
        filterQuery = '';
        luggageMeta.page = 1;
        luggageFilterDatePicker?.clear();
        await loadActiveTab();
    };

    const setTab = async (tab: TabName) => {
        activeTab = tab;
        activeMode = 'data';
        mobileFiltersExpanded = false;

        if (tab === 'charters') {
            charterScope = 'active';
        }

        charterViewData = null;
        charterViewId = null;
        syncTabQuery(tab);
        message = '';
        error = '';

        if (tab !== 'export') {
            await loadActiveTab();
        }
    };

    const toggleSelect = (list: number[], id: number): number[] => {
        return list.includes(id) ? list.filter((v) => v !== id) : [...list, id];
    };

    const selectAllAssignments = () => {
        selectedAssignmentIds = selectedAssignmentIds.length === assignments.length ? [] : assignments.map((row) => row.id);
    };

    const saveCharter = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        savingCharter = true;

        try {
            await runWithFeedback(async () => {
                await api('POST', '/api/admin/charters', {
                    id: charterForm.id || undefined,
                    pool_id: Number(charterForm.pool_id || 0) || undefined,
                    master_carter_id: Number(charterForm.master_carter_id || 0) || undefined,
                    name: charterForm.name,
                    company_name: charterForm.company_name,
                    phone: charterForm.phone,
                    start_date: charterForm.start_date,
                    end_date: charterForm.end_date,
                    departure_time: charterForm.departure_time,
                    pickup_point: charterForm.pickup_point,
                    drop_point: charterForm.drop_point,
                    unit_id: charterForm.unit_id || undefined,
                    armada_id: charterForm.armada_id || undefined,
                    armada_nopol: charterForm.armada_nopol || undefined,
                    driver_name: charterForm.driver_name,
                    price: parseCurrencyInput(charterForm.price),
                    layanan: charterForm.layanan,
                    bop_price: parseCurrencyInput(charterForm.bop_price),
                    bop_status: charterForm.bop_status,
                    down_payment: parseCurrencyInput(charterForm.down_payment),
                    payment_status: charterForm.payment_status,
                });
            }, {
                loadingMessage: charterForm.id ? 'Memperbarui data charter...' : 'Menyimpan data charter...',
                successMessage: charterForm.id ? 'Charter berhasil diperbarui.' : 'Charter berhasil disimpan.',
                errorMessage: 'Gagal simpan charter.',
            });
            message = charterForm.id ? 'Charter updated.' : 'Charter created.';
            resetCharterFormState();
            await loadCharters(charterMeta.page);
            await loadMasters();
            setFormMode('data');
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan charter.';
        } finally {
            savingCharter = false;
        }
    };

    const saveLuggage = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        savingLuggage = true;

        try {
            await runWithFeedback(async () => {
                await api('POST', '/api/admin/luggages', {
                    id: luggageForm.id || undefined,
                    pool_id: Number(luggageForm.pool_id || 0) || undefined,
                    sender_name: luggageForm.sender_name,
                    sender_phone: luggageForm.sender_phone,
                    sender_address: luggageForm.sender_address,
                    receiver_name: luggageForm.receiver_name,
                    receiver_phone: luggageForm.receiver_phone,
                    receiver_address: luggageForm.receiver_address,
                    service_id: Number(luggageForm.service_id || 0) || undefined,
                    layanan_id: Number(luggageForm.service_id || 0) || undefined,
                    rute_id: Number(luggageForm.rute_id || 0) || undefined,
                    tanggal: luggageForm.tanggal,
                    quantity: Number(luggageForm.quantity || 1),
                    notes: luggageForm.notes,
                    price: parseCurrencyInput(luggageForm.price),
                    status: luggageForm.id ? luggageForm.status : luggageReceivedStatus,
                    payment_status: luggageForm.payment_status,
                });
            }, {
                loadingMessage: luggageForm.id ? 'Memperbarui data bagasi...' : 'Menyimpan data bagasi baru...',
                successMessage: luggageForm.id ? 'Data bagasi berhasil diperbarui.' : 'Data bagasi berhasil disimpan.',
                errorMessage: 'Gagal simpan luggage.',
            });
            message = luggageForm.id ? 'Luggage updated.' : 'Luggage created.';
            resetLuggageFormState();
            await loadLuggages(luggageMeta.page);
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan luggage.';
        } finally {
            savingLuggage = false;
        }
    };

    const saveAssignment = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        savingAssignment = true;

        try {
            const conflicts = await checkAssignmentConflicts();

            if (conflicts.length > 0 && !assignmentAllowConflict) {
                error = 'Jadwal bentrok. Cek daftar konflik di bawah form atau aktifkan override.';

                return;
            }

            await runWithFeedback(async () => {
                await api('POST', '/api/admin/assignments', {
                    id: assignmentForm.id || undefined,
                    rute: assignmentForm.rute,
                    tanggal: assignmentForm.tanggal,
                    jam: assignmentForm.jam,
                    unit: Number(assignmentForm.unit),
                    driver_id: Number(assignmentForm.driver_id),
                    allow_conflict: assignmentAllowConflict,
                });
            }, {
                loadingMessage: assignmentForm.id ? 'Memperbarui assignment driver...' : 'Menyimpan assignment driver...',
                successMessage: assignmentForm.id ? 'Assignment driver berhasil diperbarui.' : 'Assignment driver berhasil dibuat.',
                errorMessage: 'Gagal simpan assignment.',
            });
            message = assignmentForm.id ? 'Assignment updated.' : 'Assignment created.';
            assignmentForm = { id: 0, rute: '', tanggal: today, jam: '08:00', unit: 1, driver_id: 0 };
            assignmentConflicts = [];
            assignmentAllowConflict = false;
            await loadAssignments(assignmentMeta.page);
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan assignment.';
        } finally {
            savingAssignment = false;
        }
    };

    const checkAssignmentConflicts = async (): Promise<AssignmentConflict[]> => {
        assignmentConflictBusy = true;

        try {
            const r = await api('POST', '/api/admin/assignments/conflicts', {
                id: assignmentForm.id || undefined,
                tanggal: assignmentForm.tanggal,
                jam: assignmentForm.jam,
                unit: Number(assignmentForm.unit),
                driver_id: Number(assignmentForm.driver_id),
            });
            assignmentConflicts = (r.conflicts ?? []) as AssignmentConflict[];

            return assignmentConflicts;
        } catch {
            assignmentConflicts = [];

            return [];
        } finally {
            assignmentConflictBusy = false;
        }
    };

    const removeItem = async (
        url: string,
        okMessage: string,
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
                    loadingMessage: options.loadingMessage ?? 'Menghapus data...',
                    successMessage: okMessage,
                    errorMessage: options.errorMessage ?? 'Gagal menghapus data.',
                },
            );

            if (result === null) {
                return;
            }

            message = okMessage;
            await loadActiveTab();
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal menghapus data.';
        } finally {
            pendingDeleteKey = '';
        }
    };

    const markCharterAsDone = async (row: Charter) => {
        const status = String(row.status ?? 'active').toLowerCase();

        if (status === 'canceled' || status === 'done') {
            return;
        }

        if (!isPaymentLunas(row.payment_status)) {
            error = 'Charter belum lunas, tidak bisa diselesaikan.';
            message = '';

            return;
        }

        message = '';
        error = '';

        try {
            await runWithFeedback(async () => {
                await api('POST', `/api/admin/charters/${row.id}/mark-done`);
            }, {
                loadingMessage: `Menyelesaikan charter #${row.id}...`,
                successMessage: `Charter #${row.id} ditandai selesai.`,
                errorMessage: 'Gagal menandai charter selesai.',
            });

            message = `Charter #${row.id} ditandai selesai.`;

            if (activeTab === 'charters' && activeMode === 'view' && charterViewId === row.id) {
                charterScope = 'history';
                await loadCharterView(row.id);
            }

            await loadCharters(charterMeta.page);
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal menandai charter selesai.';
        }
    };

    const setCharterScope = async (scope: CharterScope) => {
        if (charterScope === scope) {
return;
}

        charterScope = scope;
        charterMeta.page = 1;
        await loadCharters(1);
    };

    const luggageAction = async (row: Luggage, action: 'paid' | 'active' | 'done' | 'canceled') => {
        const actionMap = {
            paid: '/mark-paid',
            active: '/mark-active',
            done: '/mark-done',
            canceled: '/mark-canceled',
        } as const;
        pendingLuggageActionKey = `action-${row.id}-${action}`;
        message = '';
        error = '';

        try {
            await runWithFeedback(async () => {
                await api('POST', `/api/admin/luggages/${row.id}${actionMap[action]}`);
                await loadLuggages(luggageMeta.page);
            }, {
                loadingMessage: `Memproses aksi ${action} untuk bagasi #${row.id}...`,
                successMessage: `Bagasi #${row.id} berhasil diperbarui.`,
                errorMessage: 'Gagal memperbarui data bagasi.',
            });
            message = `Luggage #${row.id} updated (${action}).`;
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memperbarui data bagasi.';
        } finally {
            pendingLuggageActionKey = '';
        }
    };

    const openLuggagePrint = (id: number) => {
        if (id <= 0) {
return;
}

        window.open(`/luggages/${id}/print?auto_print=1`, '_blank', 'noopener,noreferrer');
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

    const charterCopyText = (row: Charter) => {
        const schedule = [
            row.start_date || '-',
            row.end_date || '-',
            row.departure_time ? String(row.departure_time).slice(0, 5) : '--:--',
        ].join(' • ');
        const totalPrice = formatCurrencyId(row.price ?? 0);

        return [
            'DATA CARTER',
            '',
            `Nama: ${row.name || '-'}`,
            `Company: ${row.company_name || '-'}`,
            `Kontak: ${row.phone || '-'}`,
            `Jadwal: ${schedule}`,
            `Rute: ${row.pickup_point || '-'} → ${row.drop_point || '-'}`,
            `Driver: ${row.driver_name || '-'}`,
            `Armada: ${row.armada_nopol || row.unit_nopol || '-'}`,
            `Layanan: ${row.layanan || defaultCharterService}`,
            `Harga: ${totalPrice}`,
        ].join('\n');
    };

    const copyCharterData = async (row: Charter) => {
        if (!row || row.id <= 0) {
            return;
        }

        try {
            await copyText(charterCopyText(row));
            message = 'Data charter berhasil disalin.';
            error = '';
        } catch {
            error = 'Gagal menyalin data charter.';
        }
    };

    const openCharterInvoice = (id: number) => {
        if (id <= 0) {
return;
}

        window.open(`/charters/${id}/invoice/print?auto_print=1`, '_blank', 'noopener,noreferrer');
    };

    const bulkDeleteAssignments = async () => {
        if (selectedAssignmentIds.length === 0) {
return;
}

        await api('POST', '/api/admin/assignments/bulk-delete', { ids: selectedAssignmentIds });
        message = `Deleted ${selectedAssignmentIds.length} assignment(s).`;
        await loadAssignments(assignmentMeta.page);
    };

    const jumpPage = async (target: number, type: 'charter' | 'luggage' | 'assignment') => {
        if (type === 'charter') {
await loadCharters(target);
}

        if (type === 'luggage') {
await loadLuggages(target);
}

        if (type === 'assignment') {
await loadAssignments(target);
}
    };

    const exportCsvUrl = () => {
        const params = new URLSearchParams();

        if (filterFrom.trim() !== '') {
params.set('from', filterFrom);
}

        if (filterTo.trim() !== '') {
params.set('to', filterTo);
}

        const query = params.toString();

        return query !== '' ? `/api/admin/reports/bookings-csv?${query}` : '/api/admin/reports/bookings-csv';
    };

    const exportRevenueCsvUrl = () => {
        const params = new URLSearchParams();

        if (filterFrom.trim() !== '') {
params.set('from', filterFrom);
}

        if (filterTo.trim() !== '') {
params.set('to', filterTo);
}

        params.set('type', exportType);

        return `/api/admin/reports/revenue-csv?${params.toString()}`;
    };

    $effect(() => {
        const isCharterListFilterVisible = activeTab === 'charters' && activeMode === 'data';

        if (!isCharterListFilterVisible) {
            destroyCharterFilterDatePicker();

            return;
        }

        void initCharterFilterDatePicker().then(() => {
            if (!charterFilterDatePicker) {
                return;
            }

            if (filterFrom.trim() !== '') {
                charterFilterDatePicker.setDate(filterFrom, false, 'Y-m-d');
            } else {
                charterFilterDatePicker.clear();
            }
        });
    });

    $effect(() => {
        const isCharterFormActive = activeTab === 'charters' && activeMode === 'form';
        const startInput = charterStartDateInput;
        const endInput = charterEndDateInput;
        const departureInput = charterDepartureTimeInput;

        if (!isCharterFormActive) {
            destroyCharterPickers();

            return;
        }

        if (!startInput || !endInput || !departureInput) {
            return;
        }

        void initCharterPickers().then(() => {
            syncCharterPickerValues();
        });
    });

    $effect(() => {
        const isLuggageListFilterVisible = activeTab === 'luggages' && activeMode === 'data';

        if (!isLuggageListFilterVisible) {
            destroyLuggageFilterDatePicker();

            return;
        }

        void initLuggageFilterDatePicker().then(() => {
            if (!luggageFilterDatePicker) {
                return;
            }

            if (filterFrom.trim() !== '') {
                luggageFilterDatePicker.setDate(filterFrom, false, 'Y-m-d');
            } else {
                luggageFilterDatePicker.clear();
            }
        });
    });

    $effect(() => {
        const isLuggageFormActive = activeTab === 'luggages' && activeMode === 'form';

        if (!isLuggageFormActive) {
            destroyLuggageDatePicker();

            return;
        }

        void initLuggageDatePicker().then(() => {
            syncLuggageDatePickerValue();
        });
    });

    $effect(() => {
        const isAssignmentFormActive = activeTab === 'assignments' && activeMode === 'data';

        if (!isAssignmentFormActive) {
            destroyAssignmentPickers();

            return;
        }

        void initAssignmentPickers().then(() => {
            syncAssignmentPickerValues();
        });
    });

    $effect(() => {
        const isRangeFilterActive =
            (activeTab === 'assignments' && activeMode === 'data') ||
            (activeTab === 'export' && activeMode === 'data');

        if (!isRangeFilterActive) {
            destroyExportPickers();

            return;
        }

        void initExportPickers().then(() => {
            if (exportFromDatePicker) {
                if (filterFrom.trim() !== '') {
                    exportFromDatePicker.setDate(filterFrom, false, 'Y-m-d');
                } else {
                    exportFromDatePicker.clear();
                }
            }

            if (exportToDatePicker) {
                if (filterTo.trim() !== '') {
                    exportToDatePicker.setDate(filterTo, false, 'Y-m-d');
                } else {
                    exportToDatePicker.clear();
                }
            }
        });
    });

    onMount(async () => {
        if (lockedFromServer) {
            lockedMenuView = true;
        }

        if (isFlowTab(initialTab)) {
            activeTab = initialTab;
            lockedMenuView = true;
        }

        if (isFlowMode(initialMode) && hasDedicatedFormPage(activeTab)) {
            activeMode = initialMode;
        }

        if (initialMode === 'view' && activeTab === 'charters') {
            activeMode = 'view';
            charterViewId = initialCharterId && initialCharterId > 0 ? initialCharterId : null;
        }

        if (typeof window !== 'undefined') {
            const params = new URLSearchParams(window.location.search);
            const routeTab = params.get('tab');
            const routeMode = params.get('mode');

            if (isFlowTab(routeTab)) {
                activeTab = routeTab;
                lockedMenuView = true;
            }

            const viewMatch = window.location.pathname.match(/^\/charters\/view\/(\d+)$/);

            if (viewMatch && activeTab === 'charters') {
                activeMode = 'view';
                charterViewId = Number(viewMatch[1]);
            } else if (window.location.pathname.endsWith('/form') && hasDedicatedFormPage(activeTab)) {
                activeMode = 'form';
            } else if (isFlowMode(routeMode) && hasDedicatedFormPage(activeTab)) {
                activeMode = routeMode;
            }
        }

        busy = true;

        try {
            await loadMasters();
            await loadActiveTab();

            if (activeTab === 'charters' && activeMode === 'view' && charterViewId && charterViewId > 0) {
                await loadCharterView(charterViewId);
            }
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat data awal.';
        } finally {
            busy = false;
        }
    });

    onDestroy(() => {
        destroyCharterPickers();
        destroyCharterFilterDatePicker();
        destroyLuggageFilterDatePicker();
        destroyLuggageDatePicker();
        destroyAssignmentPickers();
        destroyExportPickers();
        resetCharterCustomerLookup();
        resetCharterArmadaLookup();
        resetCharterFilterLookups();
        resetLuggageFormState();
    });
</script>

<AppHead title={tabTitle(activeTab)} />

<div class="space-y-4 p-3 pb-28 md:p-4">
    <Card>
        <CardHeader><CardTitle>{tabTitle(activeTab)}</CardTitle></CardHeader>
        <CardContent class="space-y-4">
            <div class="sticky top-0 z-10 space-y-3 border-b bg-background pb-3">
                {#if !((activeMode === 'form' && hasDedicatedFormPage(activeTab)) || (activeTab === 'charters' && activeMode === 'view'))}
                    <div class="flex justify-end md:hidden">
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 rounded-full px-3 text-xs"
                            onclick={() =>
                                (mobileFiltersExpanded = !mobileFiltersExpanded)}
                            aria-expanded={mobileFiltersExpanded}
                        >
                            {mobileFiltersExpanded
                                ? 'Sembunyikan Filter'
                                : 'Tampilkan Filter'}
                        </Button>
                    </div>
                    <div class={mobileFiltersExpanded ? 'block' : 'hidden md:block'}>
                        {#if activeTab === 'charters'}
                            <div class="grid gap-3 xl:grid-cols-[220px_minmax(0,1fr)_260px_240px_auto_auto]">
                            <input
                                bind:this={charterFilterDateInput}
                                type="text"
                                value={filterFrom}
                                readonly
                                autocomplete="off"
                                placeholder="Tanggal charter"
                                class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            />
                            <Input placeholder="Cari nama customer, driver, rute, armada" bind:value={filterQuery} />
                            <div class="relative">
                                <Input
                                    placeholder="Filter kategori armada"
                                    bind:value={charterFilterUnitSearch}
                                    class="rounded-xl"
                                    onfocus={() => {
                                        charterFilterUnitLookupOpen = true;
                                    }}
                                    oninput={() => {
                                        charterFilterUnitLookupOpen = true;
                                    }}
                                    onblur={onCharterFilterUnitBlur}
                                />
                                {#if charterFilterUnitLookupOpen}
                                    <div class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl">
                                        {#if filteredCharterFilterUnits().length === 0}
                                            <p class="px-2 py-2 text-xs text-muted-foreground">Kategori armada tidak ditemukan.</p>
                                        {:else}
                                            <div class="space-y-1">
                                                {#each filteredCharterFilterUnits() as unit (`charter-filter-unit-${unit.id}`)}
                                                    <button
                                                        type="button"
                                                        class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70"
                                                        onmousedown={(event) => {
                                                            event.preventDefault();
                                                            selectCharterFilterUnit(unit);
                                                        }}
                                                    >
                                                        <span>
                                                            <span class="block text-sm font-semibold text-foreground">{charterUnitLabel(unit) || unit.nopol}</span>
                                                            <span class="block text-[11px] text-muted-foreground">{charterUnitMeta(unit)}</span>
                                                        </span>
                                                    </button>
                                                {/each}
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                            <div class="relative">
                                <Input
                                    placeholder="Filter nopol armada"
                                    bind:value={charterFilterArmadaSearch}
                                    class="rounded-xl"
                                    onfocus={() => {
                                        charterFilterArmadaLookupOpen = true;

                                        if (charterFilterArmadas.length === 0) {
                                            void loadCharterFilterArmadas(charterFilterArmadaSearch);
                                        }
                                    }}
                                    oninput={(event) => {
                                        charterFilterArmadaLookupOpen = true;
                                        queueCharterFilterArmadaSearch((event.currentTarget as HTMLInputElement).value);
                                    }}
                                    onblur={onCharterFilterArmadaBlur}
                                />
                                {#if charterFilterArmadaLookupOpen}
                                    <div class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl">
                                        {#if charterFilterArmadaBusy}
                                            <p class="px-2 py-2 text-xs text-muted-foreground">Memuat armada...</p>
                                        {:else if filteredCharterFilterArmadas().length === 0}
                                            <p class="px-2 py-2 text-xs text-muted-foreground">Armada tidak ditemukan.</p>
                                        {:else}
                                            <div class="space-y-1">
                                                {#each filteredCharterFilterArmadas() as armada (`charter-filter-armada-${armada.id}`)}
                                                    <button
                                                        type="button"
                                                        class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70"
                                                        onmousedown={(event) => {
                                                            event.preventDefault();
                                                            selectCharterFilterArmada(armada);
                                                        }}
                                                    >
                                                        <span>
                                                            <span class="block text-sm font-semibold text-foreground">{armada.nopol}</span>
                                                            <span class="block text-[11px] text-muted-foreground">{[armada.kategori, armada.merk, armada.tahun].filter(Boolean).join(' | ') || 'Armada aktif'}</span>
                                                        </span>
                                                    </button>
                                                {/each}
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                            <Button type="button" onclick={() => void applyFilters()}>Apply Filters</Button>
                            <Button type="button" variant="outline" onclick={() => void resetCharterFilters()}>Reset</Button>
                        </div>
                        {:else if activeTab === 'luggages'}
                            <div class="grid gap-3 md:grid-cols-[220px_minmax(0,1fr)_auto_auto]">
                            <input
                                bind:this={luggageFilterDateInput}
                                type="text"
                                value={filterFrom}
                                readonly
                                autocomplete="off"
                                placeholder="Tanggal bagasi"
                                class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            />
                            <Input
                                placeholder="Cari pengirim, penerima, resi, layanan, atau rute"
                                bind:value={filterQuery}
                                onkeydown={(event) => event.key === 'Enter' && void applyFilters()}
                            />
                            <Button type="button" onclick={() => void applyFilters()}>Cari Data</Button>
                            <Button type="button" variant="outline" onclick={() => void resetLuggageFilters()}>Reset</Button>
                        </div>
                        {:else}
                            <div class="grid gap-3 md:grid-cols-5">
                            <input
                                bind:this={exportFromDateInput}
                                type="text"
                                value={filterFrom}
                                readonly
                                autocomplete="off"
                                placeholder="Tanggal mulai"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            />
                            <input
                                bind:this={exportToDateInput}
                                type="text"
                                value={filterTo}
                                readonly
                                autocomplete="off"
                                placeholder="Tanggal akhir"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            />
                            <Input placeholder="Cari nama / no hp / resi / rute" bind:value={filterQuery} />
                            <Input type="number" min="10" max="100" bind:value={filterPerPage} />
                            <Button type="button" onclick={() => void applyFilters()}>Apply Filters</Button>
                        </div>
                        {/if}
                    </div>
                {/if}

                {#if !lockedMenuView}
                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant={activeTab === 'charters' ? 'default' : 'outline'} onclick={() => void setTab('charters')}>Charters</Button>
                        <Button type="button" variant={activeTab === 'luggages' ? 'default' : 'outline'} onclick={() => void setTab('luggages')}>Luggages</Button>
                        <Button type="button" variant={activeTab === 'assignments' ? 'default' : 'outline'} onclick={() => void setTab('assignments')}>Driver Assignments</Button>
                        <Button type="button" variant={activeTab === 'export' ? 'default' : 'outline'} onclick={() => void setTab('export')}>Export CSV</Button>
                    </div>
                {/if}
            </div>

            {#if busy}
                <div class="space-y-2">
                    <div class="h-9 w-full animate-pulse rounded bg-muted"></div>
                    <div class="h-9 w-full animate-pulse rounded bg-muted"></div>
                    <div class="h-40 w-full animate-pulse rounded bg-muted"></div>
                </div>
            {/if}
            {#if error}<p class="text-sm text-red-600">{error}</p>{/if}
            {#if message}<p class="text-sm text-emerald-600">{message}</p>{/if}

            {#if activeTab === 'charters' && !busy}
                {#if activeMode === 'view'}
                    {#if CharterViewPanelComponent}
                        <CharterViewPanelComponent
                            charterViewBusy={charterViewBusy}
                            charterViewData={charterViewData}
                            defaultCharterService={defaultCharterService}
                            closeCharterView={closeCharterView}
                            charterStatusClass={charterStatusClass}
                            charterStatusLabel={charterStatusLabel}
                            charterPaymentClass={charterPaymentClass}
                            openCharterInvoice={openCharterInvoice}
                            copyCharterData={copyCharterData}
                            charterCanMarkDone={charterCanMarkDone}
                            markCharterAsDone={markCharterAsDone}
                            charterCanEdit={charterCanEdit}
                            openCharterEditor={openCharterEditor}
                            charterMarkDoneHint={charterMarkDoneHint}
                            formatCurrencyId={formatCurrencyId}
                            charterPaymentRemaining={charterPaymentRemaining}
                        />
                    {:else}
                        <div class="space-y-2 rounded-2xl border border-border/70 bg-card p-4 shadow-sm">
                            <div class="h-7 w-full animate-pulse rounded bg-muted"></div>
                            <div class="h-7 w-full animate-pulse rounded bg-muted"></div>
                            <div class="h-24 w-full animate-pulse rounded bg-muted"></div>
                        </div>
                    {/if}
                {:else if activeMode === 'form'}
                    {#if CharterFormPanelComponent}
                        <CharterFormPanelComponent
                            bind:charterForm
                            charterCustomerQuery={charterCustomerQuery}
                            charterCustomerBusy={charterCustomerBusy}
                            charterCustomerLookupOpen={charterCustomerLookupOpen}
                            charterCustomerResults={charterCustomerResults}
                            charterServiceOptions={charterServiceOptions}
                            bind:charterRouteSearch
                            bind:charterRouteLookupOpen
                            bind:charterStartDateInput
                            bind:charterEndDateInput
                            bind:charterDepartureTimeInput
                            bind:charterUnitSearch
                            bind:charterUnitLookupOpen
                            bind:charterArmadaSearch
                            bind:charterArmadaLookupOpen
                            charterArmadaBusy={charterArmadaBusy}
                            armadas={armadas}
                            bind:charterDriverSearch
                            bind:charterDriverLookupOpen
                            charterPaymentStatusOptions={charterPaymentStatusOptions}
                            activePools={activePools}
                            poolLabel={poolLabel}
                            poolNameById={poolNameById}
                            savingCharter={savingCharter}
                            onCharterCustomerQueryInput={onCharterCustomerQueryInput}
                            applyCharterCustomer={applyCharterCustomer}
                            onCharterRouteBlur={onCharterRouteBlur}
                            filteredCharterRoutes={filteredCharterRoutes}
                            selectCharterRoute={selectCharterRoute}
                            charterRouteLabel={charterRouteLabel}
                            charterRouteMeta={charterRouteMeta}
                            onCharterUnitBlur={onCharterUnitBlur}
                            filteredCharterUnits={filteredCharterUnits}
                            selectCharterUnit={selectCharterUnit}
                            charterUnitLabel={charterUnitLabel}
                            charterUnitMeta={charterUnitMeta}
                            loadCharterArmadas={loadCharterArmadas}
                            queueCharterArmadaSearch={queueCharterArmadaSearch}
                            onCharterArmadaBlur={onCharterArmadaBlur}
                            filteredCharterArmadas={filteredCharterArmadas}
                            selectCharterArmada={selectCharterArmada}
                            onCharterDriverBlur={onCharterDriverBlur}
                            filteredCharterDrivers={filteredCharterDrivers}
                            selectCharterDriver={selectCharterDriver}
                            selectedCharterDriver={selectedCharterDriver}
                            selectedCharterUnit={selectedCharterUnit}
                            formatCurrencyInput={formatCurrencyInput}
                            parseCurrencyInput={parseCurrencyInput}
                            formatCurrencyId={formatCurrencyId}
                            saveCharter={saveCharter}
                            setFormMode={setFormMode}
                            resetCharterFormState={resetCharterFormState}
                        />
                    {:else}
                        <div class="space-y-2 rounded-2xl border border-border/70 bg-card p-4 shadow-sm">
                            <div class="h-7 w-full animate-pulse rounded bg-muted"></div>
                            <div class="h-32 w-full animate-pulse rounded bg-muted"></div>
                            <div class="h-48 w-full animate-pulse rounded bg-muted"></div>
                        </div>
                    {/if}
                {:else}
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] border border-border/70 bg-[linear-gradient(135deg,rgba(8,145,178,0.07),rgba(15,23,42,0.02))] px-3 py-3 shadow-sm">
                        <Button type="button" onclick={() => {
 resetCharterFormState(); setFormMode('form'); 
}}>Tambah Carter</Button>
                        <div class="inline-flex items-center rounded-xl border border-border/70 bg-background/80 p-1">
                            <Button
                                type="button"
                                size="sm"
                                variant={charterScope === 'active' ? 'default' : 'ghost'}
                                class="h-7 rounded-lg px-2.5 text-[11px]"
                                onclick={() => void setCharterScope('active')}
                            >
                                Aktif
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                variant={charterScope === 'history' ? 'default' : 'ghost'}
                                class="h-7 rounded-lg px-2.5 text-[11px]"
                                onclick={() => void setCharterScope('history')}
                            >
                                History
                            </Button>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-border/70 bg-muted/10 px-3 py-2.5 shadow-sm">
                            <p class="text-xs font-medium text-muted-foreground">
                                {charterScope === 'history' ? 'Mode history: data carter selesai' : 'Mode aktif: data carter berjalan'}
                            </p>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full border border-border/70 bg-background px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                    Total {charters.length}
                                </span>
                                <span class="rounded-full border border-emerald-200 bg-emerald-100 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                    Aktif {charterActiveCount()}
                                </span>
                                <span class="rounded-full border border-rose-200 bg-rose-100 px-2 py-0.5 text-[11px] font-medium text-rose-700">
                                    Canceled {charterCanceledCount()}
                                </span>
                            </div>
                        </div>

                        {#if charters.length === 0}
                            <div class="rounded-2xl border border-dashed border-border/70 bg-muted/10 px-4 py-8 text-center text-sm text-muted-foreground">
                                Belum ada data carter sesuai filter.
                            </div>
                        {:else}
                            <div class="grid gap-3 md:grid-cols-2 2xl:grid-cols-3">
                                {#each charters as row (row.id)}
                                    <article class="group relative overflow-hidden rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm transition-all duration-200 motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-1 motion-safe:duration-300 hover:-translate-y-0.5 hover:border-cyan-300/60 hover:shadow-md hover:shadow-cyan-950/10 svelte-19f8rux">
                                        <div class="mb-2 flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold leading-tight text-foreground">{row.name}</p>
                                                <p class="truncate text-[11px] text-muted-foreground">{row.company_name ?? '-'}</p>
                                                <p class="truncate text-[11px] text-muted-foreground">{row.phone ?? '-'}</p>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-1.5">
                                                <span class={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${charterStatusClass(row.status)}`}>
                                                    {charterStatusLabel(row.status)}
                                                </span>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        {#snippet children(props)}
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="outline"
                                                                class="h-8 rounded-full border-border/70 px-2.5"
                                                                onclick={props.onclick}
                                                                aria-expanded={props['aria-expanded']}
                                                                data-state={props['data-state']}
                                                            >
                                                                <MoreHorizontal class="h-4 w-4" />
                                                            </Button>
                                                        {/snippet}
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" class="w-48">
                                                        <DropdownMenuItem onclick={() => void openCharterView(row.id)}>
                                                            View Detail
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem onclick={() => void copyCharterData(row)}>
                                                            Copy Data
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem onclick={() => openCharterInvoice(row.id)}>
                                                            Cetak Invoice
                                                        </DropdownMenuItem>
                                                        {#if charterCanEdit(row)}
                                                            <DropdownMenuItem onclick={() => openCharterEditor(row)}>
                                                                Edit Carter
                                                            </DropdownMenuItem>
                                                        {/if}
                                                        {#if charterCanMarkDone(row)}
                                                            <DropdownMenuItem onclick={() => void markCharterAsDone(row)}>
                                                                Tandai Selesai
                                                            </DropdownMenuItem>
                                                        {/if}
                                                        {#if charterCanCancel(row)}
                                                            <DropdownMenuItem
                                                                onclick={() =>
                                                                    void removeItem(`/api/admin/charters/${row.id}`, 'Charter canceled.', {
                                                                        confirmMessage: 'Yakin ingin membatalkan charter ini?',
                                                                        loadingMessage: 'Membatalkan charter...',
                                                                        errorMessage: 'Gagal membatalkan charter.',
                                                                        pendingKey: `charter-${row.id}`,
                                                                    })}
                                                            >
                                                                {pendingDeleteKey === `charter-${row.id}` ? 'Memproses...' : 'Cancel Charter'}
                                                            </DropdownMenuItem>
                                                        {/if}
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-border/60 bg-muted/15 px-2.5 py-2">
                                            <p class="text-[9px] uppercase tracking-[0.1em] text-muted-foreground">Rute Charter</p>
                                            <p class="mt-0.5 line-clamp-2 break-words text-xs font-semibold leading-snug text-foreground">{row.pickup_point ?? '-'} - {row.drop_point ?? '-'}</p>
                                        </div>

                                        <div class="mt-1.5 grid grid-cols-2 gap-1.5 text-xs md:gap-2">
                                            <div class="order-1 rounded-xl border border-border/60 bg-background/80 px-2.5 py-2">
                                                <p class="text-[9px] uppercase tracking-[0.08em] text-muted-foreground">Jadwal</p>
                                                <p class="mt-0.5 font-semibold leading-tight text-foreground">{row.start_date}</p>
                                                <p class="text-[11px] text-muted-foreground">{row.end_date} • {row.departure_time ? String(row.departure_time).slice(0, 5) : '--:--'}</p>
                                            </div>
                                            <div class="order-3 col-span-2 rounded-xl border border-border/60 bg-background/80 px-2.5 py-2">
                                                <p class="text-[9px] uppercase tracking-[0.08em] text-muted-foreground">Driver & Armada</p>
                                                <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                    <p class="text-xs font-semibold leading-tight text-foreground">{row.driver_name ?? '-'}</p>
                                                    <p class="text-[11px] text-muted-foreground">{[row.unit_category, row.unit_nopol, row.armada_nopol].filter(Boolean).join(' | ') || '-'}</p>
                                                </div>
                                            </div>
                                            <div class="order-2 rounded-xl border border-border/60 bg-background/80 px-2.5 py-2">
                                                <p class="text-[9px] uppercase tracking-[0.08em] text-muted-foreground">Biaya</p>
                                                <p class="mt-0.5 text-xs font-semibold leading-tight text-foreground">{formatCurrencyId(row.price)}</p>
                                                <div class="mt-0.5 flex flex-wrap gap-x-2 gap-y-0.5 text-[11px] leading-tight text-muted-foreground">
                                                    <span>DP {formatCurrencyId(row.down_payment ?? 0)}</span>
                                                    <span>BOP {formatCurrencyId(row.bop_price ?? 0)}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                            <span class={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${charterPaymentClass(row.payment_status)}`}>
                                                {row.payment_status ?? '-'}
                                            </span>
                                            {#if Number(row.down_payment ?? 0) > 0}
                                                <span class="rounded-full border border-violet-200 bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700">
                                                    DP {formatCurrencyId(row.down_payment ?? 0)}
                                                </span>
                                            {/if}
                                        </div>

                                        <div class="mt-2 flex items-center justify-end border-t border-border/70 pt-2">
                                            <p class="mr-auto hidden text-[11px] text-muted-foreground sm:block">
                                                {charterScope === 'history' ? 'Riwayat charter tampil ringkas untuk mobile.' : 'Aksi ada di menu kanan atas kartu.'}
                                            </p>
                                            <Button type="button" size="sm" variant="ghost" class="h-8 rounded-xl px-3 text-[11px]" onclick={() => void openCharterView(row.id)}>
                                                Buka Detail
                                            </Button>
                                        </div>
                                    </article>
                                {/each}
                            </div>
                        {/if}
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-border/70 bg-muted/10 px-3 py-2.5">
                        <p class="text-xs text-muted-foreground">Total data: {charterMeta.total}</p>
                        <div class="flex items-center gap-2">
                            <Button type="button" variant="outline" class="h-8 rounded-xl px-3 text-xs" disabled={charterMeta.page <= 1} onclick={() => void jumpPage(charterMeta.page - 1, 'charter')}>Prev</Button>
                            <span class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-foreground">{charterMeta.page} / {charterMeta.last_page}</span>
                            <Button type="button" variant="outline" class="h-8 rounded-xl px-3 text-xs" disabled={charterMeta.page >= charterMeta.last_page} onclick={() => void jumpPage(charterMeta.page + 1, 'charter')}>Next</Button>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'luggages' && !busy}
                {#if activeMode === 'form'}
                    <div class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2">
                        <p class="text-xs font-medium text-muted-foreground">Halaman Form Bagasi</p>
                        <Button type="button" size="sm" variant="outline" class="h-8 rounded-lg text-xs" onclick={() => setFormMode('data')}>
                            Kembali ke Data Bagasi
                        </Button>
                    </div>
                    <div class="overflow-hidden rounded-[28px] border border-border/70 bg-card shadow-sm">
                        <div class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(15,23,42,0.03),rgba(16,185,129,0.08))] px-4 py-4 md:px-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="text-lg font-semibold tracking-tight">{luggageForm.id ? 'Edit Data Bagasi' : 'Tambah Data Bagasi Baru'}</p>
                                    <p class="text-xs text-muted-foreground">Kelola pengirim, penerima, rute kirim, dan biaya bagasi dalam tampilan yang lebih padat.</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full border border-border/70 bg-background/80 px-2.5 py-1 text-[11px] font-medium text-muted-foreground">
                                        {luggageForm.id ? 'Mode Edit' : 'Data Baru'}
                                    </span>
                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                                        {luggageForm.payment_status || 'Belum Bayar'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <form class="grid gap-4 p-4 md:p-5 xl:grid-cols-[minmax(0,1.55fr)_320px]" onsubmit={saveLuggage}>
                            <div class="space-y-4">
                                <section class="rounded-2xl border border-border/70 bg-muted/10 p-4">
                                    <div class="mb-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">Pengirim</p>
                                        <p class="text-sm font-medium text-foreground">Cari customer bagasi pengirim supaya kontak terisi otomatis</p>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <div class="space-y-1 md:col-span-2">
                                            <label for="luggage-sender-name" class="text-xs font-medium text-muted-foreground">Nama Pengirim</label>
                                            <div class="relative">
                                                <Input
                                                    id="luggage-sender-name"
                                                    class="rounded-xl"
                                                    type="search"
                                                    placeholder="Cari nama customer bagasi pengirim"
                                                    value={luggageForm.sender_name}
                                                    oninput={(event) => onLuggageCustomerInput((event.currentTarget as HTMLInputElement).value, 'sender')}
                                                    onfocus={() => onLuggageCustomerFocus('sender')}
                                                    onblur={() => onLuggageCustomerBlur('sender')}
                                                    autocomplete="off"
                                                    required
                                                />
                                                {#if luggageSenderLookupOpen}
                                                    <div class="absolute left-0 top-full z-30 mt-2 max-h-72 w-full overflow-auto rounded-2xl border border-border/70 bg-background shadow-xl">
                                                        {#if luggageSenderLookupBusy}
                                                            <div class="px-3 py-3 text-xs text-muted-foreground">Mencari customer bagasi...</div>
                                                        {:else if luggageSenderLookupResults.length > 0}
                                                            {#each luggageSenderLookupResults as customer (customer.id)}
                                                                <button
                                                                    type="button"
                                                                    class="flex w-full flex-col gap-1 border-b border-border/60 px-3 py-2 text-left last:border-b-0 hover:bg-muted/40"
                                                                    onmousedown={(event) => event.preventDefault()}
                                                                    onclick={() => applyLuggageCustomer(customer, 'sender')}
                                                                >
                                                                    <span class="flex items-center justify-between gap-2">
                                                                        <span class="font-medium text-foreground">{customer.nama}</span>
                                                                        <span class="rounded-full border border-border/70 bg-muted/30 px-2 py-0.5 text-[10px] uppercase tracking-[0.12em] text-muted-foreground">{customer.tipe ?? '-'}</span>
                                                                    </span>
                                                                    <span class="text-xs text-muted-foreground">{customer.no_hp}</span>
                                                                    <span class="truncate text-xs text-muted-foreground">{customer.alamat || '-'}</span>
                                                                </button>
                                                            {/each}
                                                        {:else}
                                                            <div class="px-3 py-3 text-xs text-muted-foreground">Customer bagasi pengirim tidak ditemukan.</div>
                                                        {/if}
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                        <div class="space-y-1"><label for="luggage-sender-phone" class="text-xs font-medium text-muted-foreground">No HP Pengirim</label><Input id="luggage-sender-phone" class="rounded-xl" bind:value={luggageForm.sender_phone} required /></div>
                                        <div class="space-y-1 md:col-span-2"><label for="luggage-sender-address" class="text-xs font-medium text-muted-foreground">Alamat Pengirim</label><Input id="luggage-sender-address" class="rounded-xl" bind:value={luggageForm.sender_address} /></div>
                                    </div>
                                </section>

                                <section class="rounded-2xl border border-border/70 bg-muted/10 p-4">
                                    <div class="mb-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">Penerima</p>
                                        <p class="text-sm font-medium text-foreground">Cari customer bagasi penerima supaya kontak terisi otomatis</p>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <div class="space-y-1 md:col-span-2">
                                            <label for="luggage-receiver-name" class="text-xs font-medium text-muted-foreground">Nama Penerima</label>
                                            <div class="relative">
                                                <Input
                                                    id="luggage-receiver-name"
                                                    class="rounded-xl"
                                                    type="search"
                                                    placeholder="Cari nama customer bagasi penerima"
                                                    value={luggageForm.receiver_name}
                                                    oninput={(event) => onLuggageCustomerInput((event.currentTarget as HTMLInputElement).value, 'receiver')}
                                                    onfocus={() => onLuggageCustomerFocus('receiver')}
                                                    onblur={() => onLuggageCustomerBlur('receiver')}
                                                    autocomplete="off"
                                                    required
                                                />
                                                {#if luggageReceiverLookupOpen}
                                                    <div class="absolute left-0 top-full z-30 mt-2 max-h-72 w-full overflow-auto rounded-2xl border border-border/70 bg-background shadow-xl">
                                                        {#if luggageReceiverLookupBusy}
                                                            <div class="px-3 py-3 text-xs text-muted-foreground">Mencari customer bagasi...</div>
                                                        {:else if luggageReceiverLookupResults.length > 0}
                                                            {#each luggageReceiverLookupResults as customer (customer.id)}
                                                                <button
                                                                    type="button"
                                                                    class="flex w-full flex-col gap-1 border-b border-border/60 px-3 py-2 text-left last:border-b-0 hover:bg-muted/40"
                                                                    onmousedown={(event) => event.preventDefault()}
                                                                    onclick={() => applyLuggageCustomer(customer, 'receiver')}
                                                                >
                                                                    <span class="flex items-center justify-between gap-2">
                                                                        <span class="font-medium text-foreground">{customer.nama}</span>
                                                                        <span class="rounded-full border border-border/70 bg-muted/30 px-2 py-0.5 text-[10px] uppercase tracking-[0.12em] text-muted-foreground">{customer.tipe ?? '-'}</span>
                                                                    </span>
                                                                    <span class="text-xs text-muted-foreground">{customer.no_hp}</span>
                                                                    <span class="truncate text-xs text-muted-foreground">{customer.alamat || '-'}</span>
                                                                </button>
                                                            {/each}
                                                        {:else}
                                                            <div class="px-3 py-3 text-xs text-muted-foreground">Customer bagasi penerima tidak ditemukan.</div>
                                                        {/if}
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                        <div class="space-y-1"><label for="luggage-receiver-phone" class="text-xs font-medium text-muted-foreground">No HP Penerima</label><Input id="luggage-receiver-phone" class="rounded-xl" bind:value={luggageForm.receiver_phone} required /></div>
                                        <div class="space-y-1 md:col-span-2"><label for="luggage-receiver-address" class="text-xs font-medium text-muted-foreground">Alamat Penerima</label><Input id="luggage-receiver-address" class="rounded-xl" bind:value={luggageForm.receiver_address} /></div>
                                    </div>
                                </section>

                                <section class="rounded-2xl border border-border/70 bg-muted/10 p-4">
                                    <div class="mb-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">Pengiriman</p>
                                        <p class="text-sm font-medium text-foreground">Layanan, jadwal, dan biaya bagasi</p>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        <div class="space-y-1 xl:col-span-2">
                                            <label for="luggage-pool" class="text-xs font-medium text-muted-foreground">Perwakilan / Pool</label>
                                            <select id="luggage-pool" class="h-9 w-full rounded-xl border border-input bg-background px-3 text-sm" bind:value={luggageForm.pool_id} required={activePools().length > 0}>
                                                <option value={0}>Pilih pool</option>
                                                {#each activePools() as pool (pool.id)}
                                                    <option value={pool.id}>{poolLabel(pool)}</option>
                                                {/each}
                                            </select>
                                            <p class="text-[11px] text-muted-foreground">Dipakai untuk akses user dan laporan pool.</p>
                                        </div>
                                        <div class="space-y-1 xl:col-span-2">
                                            <label for="luggage-service" class="text-xs font-medium text-muted-foreground">Layanan</label>
                                            <select id="luggage-service" class="h-9 w-full rounded-xl border border-input bg-background px-3 text-sm" bind:value={luggageForm.service_id}>
                                                <option value={0}>Pilih layanan</option>
                                                {#each services as service (service.id)}
                                                    <option value={service.id}>{service.name}</option>
                                                {/each}
                                            </select>
                                        </div>
                                        <div class="space-y-1 xl:col-span-2">
                                            <label for="luggage-route" class="text-xs font-medium text-muted-foreground">Rute</label>
                                            <select
                                                id="luggage-route"
                                                class="h-9 w-full rounded-xl border border-input bg-background px-3 text-sm"
                                                bind:value={luggageForm.rute_id}
                                                onchange={syncLuggagePoolFromRoute}
                                            >
                                                <option value={0}>Pilih rute</option>
                                                {#each routes as route (route.id)}
                                                    <option value={route.id}>{route.name}</option>
                                                {/each}
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label for="luggage-date" class="text-xs font-medium text-muted-foreground">Tanggal</label>
                                            <input
                                                id="luggage-date"
                                                bind:this={luggageDateInput}
                                                type="text"
                                                value={luggageForm.tanggal}
                                                readonly
                                                autocomplete="off"
                                                placeholder="Tanggal bagasi"
                                                class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <label for="luggage-quantity" class="text-xs font-medium text-muted-foreground">Jumlah Barang</label>
                                            <Input id="luggage-quantity" class="rounded-xl" type="number" min="1" bind:value={luggageForm.quantity} required />
                                        </div>
                                        <div class="space-y-1">
                                            <label for="luggage-price" class="text-xs font-medium text-muted-foreground">Biaya Bagasi</label>
                                            <Input
                                                id="luggage-price"
                                                class="rounded-xl"
                                                type="text"
                                                inputmode="numeric"
                                                placeholder="Rp 0"
                                                value={formatCurrencyInput(luggageForm.price)}
                                                oninput={(event) => {
                                                    luggageForm.price = parseCurrencyInput((event.currentTarget as HTMLInputElement).value);
                                                }}
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <label for="luggage-payment-status" class="text-xs font-medium text-muted-foreground">Status Pembayaran</label>
                                            <select id="luggage-payment-status" class="h-9 w-full rounded-xl border border-input bg-background px-3 text-sm" bind:value={luggageForm.payment_status}>{#each luggagePaymentStatusOptions as status (status)}<option value={status}>{status}</option>{/each}</select>
                                        </div>
                                        <div class="space-y-1 xl:col-span-4">
                                            <label for="luggage-notes" class="text-xs font-medium text-muted-foreground">Catatan</label>
                                            <Input id="luggage-notes" class="rounded-xl" placeholder="Catatan tambahan" bind:value={luggageForm.notes} />
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <aside class="space-y-4">
                                <section class="rounded-2xl border border-border/70 bg-slate-950 p-4 text-slate-50 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Ringkasan Bagasi</p>
                                    <div class="mt-3 space-y-3">
                                        <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-xs">
                                            <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Pengirim</p>
                                            <p class="mt-1 font-semibold">{luggageForm.sender_name || 'Nama pengirim belum diisi'}</p>
                                            <p class="truncate text-slate-300">{luggageForm.sender_phone || '-'}</p>
                                            <p class="truncate text-slate-300">{luggageForm.sender_address || '-'}</p>
                                        </div>
                                        <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-xs">
                                            <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Penerima</p>
                                            <p class="mt-1 font-semibold">{luggageForm.receiver_name || 'Nama penerima belum diisi'}</p>
                                            <p class="truncate text-slate-300">{luggageForm.receiver_phone || '-'}</p>
                                            <p class="truncate text-slate-300">{luggageForm.receiver_address || '-'}</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                                                <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Tanggal</p>
                                                <p class="mt-1 font-medium">{luggageForm.tanggal || '-'}</p>
                                            </div>
                                            <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                                                <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Jumlah</p>
                                                <p class="mt-1 font-medium">{luggageForm.quantity || 0} barang</p>
                                            </div>
                                            <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                                                <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Status</p>
                                                <p class="mt-1 font-medium">{luggageStatusLabel(luggageForm.status)}</p>
                                            </div>
                                            <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                                                <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Bayar</p>
                                                <p class="mt-1 font-medium">{luggageForm.payment_status || '-'}</p>
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 p-3 text-xs">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-emerald-100">Rute</span>
                                                <span class="font-medium text-right">{routes.find((route) => route.id === Number(luggageForm.rute_id))?.name ?? '-'}</span>
                                            </div>
                                            <div class="mt-1 flex items-center justify-between gap-2">
                                                <span class="text-emerald-100">Layanan</span>
                                                <span class="font-medium text-right">{services.find((service) => service.id === Number(luggageForm.service_id))?.name ?? '-'}</span>
                                            </div>
                                            <div class="mt-1 flex items-center justify-between gap-2">
                                                <span class="text-emerald-100">Biaya</span>
                                                <span class="font-semibold">{formatCurrencyId(luggageForm.price)}</span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <div class="flex flex-wrap items-center gap-2 border-t border-border/70 pt-1 xl:border-0 xl:pt-0">
                                    <LoadingButton
                                        type="submit"
                                        class="h-9 rounded-xl px-4 text-xs"
                                        loading={savingLuggage}
                                        loadingText={luggageForm.id ? 'Menyimpan perubahan...' : 'Menyimpan bagasi...'}
                                    >
                                        {luggageForm.id ? 'Update Data Bagasi' : 'Simpan Bagasi Baru'}
                                    </LoadingButton>
                                    <Button type="button" variant="outline" class="h-9 rounded-xl px-4 text-xs" onclick={() => setFormMode('data')}>Batal</Button>
                                </div>
                            </aside>
                        </form>
                    </div>
                {:else}
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] border border-border/70 bg-[linear-gradient(135deg,rgba(16,185,129,0.08),rgba(15,23,42,0.02))] px-3 py-3 shadow-sm">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold tracking-tight text-foreground">Workspace Bagasi</p>
                            <p class="text-xs text-muted-foreground">Kelola transaksi bagasi, status kirim, dan tracking resi dalam tampilan yang lebih ringkas.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                type="button"
                                class="h-9 rounded-xl px-4 text-xs"
                                onclick={() => {
                                    resetLuggageFormState();
                                    setFormMode('form');
                                }}
                            >
                                Tambah Bagasi
                            </Button>
                        </div>
                    </div>
                    <div class="grid gap-3 md:hidden">
                        {#if luggages.length === 0}
                            <div class="rounded-2xl border border-dashed border-border/70 bg-muted/10 px-4 py-8 text-center text-sm text-muted-foreground">
                                Belum ada data bagasi yang cocok dengan filter saat ini.
                            </div>
                        {:else}
                            {#each luggages as row (row.id)}
                                {@const normalizedLuggageStatus = normalizeLuggageStatus(row.status)}
                                {@const departureInfo = luggageDepartureInfo(row)}
                                {@const lockedLuggageActions = [luggagePickedUpStatus, luggageArrivedStatus].includes(normalizedLuggageStatus)}
                                <article class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-foreground">
                                                {row.kode_resi ?? `Bagasi #${row.id}`}
                                            </p>
                                            <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                                {row.service_name ?? '-'} / {row.quantity} barang
                                            </p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-1.5">
                                            <span class={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${luggageStatusClass(row.status)}`}>
                                                {luggageStatusLabel(row.status)}
                                            </span>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    {#snippet children(props)}
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            class="h-8 rounded-full border-border/70 px-2.5"
                                                            onclick={props.onclick}
                                                            aria-expanded={props['aria-expanded']}
                                                            data-state={props['data-state']}
                                                        >
                                                            <MoreHorizontal class="h-4 w-4" />
                                                        </Button>
                                                    {/snippet}
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" class="w-52">
                                                    {#if !lockedLuggageActions}
                                                        <DropdownMenuItem onclick={() => openLuggageEditor(row)}>
                                                            Edit Data
                                                        </DropdownMenuItem>
                                                    {/if}
                                                    {#if normalizedLuggageStatus === luggageReceivedStatus}
                                                        <DropdownMenuItem disabled={pendingLuggageActionKey === `action-${row.id}-active`} onclick={() => void luggageAction(row, 'active')}>
                                                            {pendingLuggageActionKey === `action-${row.id}-active` ? 'Memproses...' : 'Tandai Dalam Perjalanan'}
                                                        </DropdownMenuItem>
                                                    {/if}
                                                    {#if row.payment_status !== 'Lunas'}
                                                        <DropdownMenuItem disabled={pendingLuggageActionKey === `action-${row.id}-paid`} onclick={() => void luggageAction(row, 'paid')}>
                                                            {pendingLuggageActionKey === `action-${row.id}-paid` ? 'Memproses...' : 'Tandai Lunas'}
                                                        </DropdownMenuItem>
                                                    {/if}
                                                    <DropdownMenuItem onclick={() => openLuggagePrint(row.id)}>
                                                        Print Resi
                                                    </DropdownMenuItem>
                                                    {#if !lockedLuggageActions}
                                                        <DropdownMenuItem disabled={pendingLuggageActionKey === `action-${row.id}-canceled`} onclick={() => void luggageAction(row, 'canceled')}>
                                                            {pendingLuggageActionKey === `action-${row.id}-canceled` ? 'Memproses...' : 'Batalkan Pengiriman'}
                                                        </DropdownMenuItem>
                                                    {/if}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                        <div class="rounded-xl bg-muted/30 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Pengirim</p>
                                            <p class="mt-1 truncate font-semibold text-foreground">{row.sender_name || '-'}</p>
                                            <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{row.sender_phone || '-'}</p>
                                        </div>
                                        <div class="rounded-xl bg-muted/30 px-3 py-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Penerima</p>
                                            <p class="mt-1 truncate font-semibold text-foreground">{row.receiver_name || '-'}</p>
                                            <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{row.receiver_phone || '-'}</p>
                                        </div>
                                    </div>

                                    <div class="mt-2 rounded-xl bg-muted/30 px-3 py-2 text-xs">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Rute</p>
                                        <p class="mt-1 font-semibold text-foreground">
                                            {row.route_name ?? row.rute ?? routes.find((route) => route.id === Number(row.rute_id || 0))?.name ?? '-'}
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-muted-foreground">{row.tanggal || '-'}</p>
                                    </div>

                                    <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                                        <div class="rounded-xl bg-emerald-50/70 px-3 py-2 dark:bg-emerald-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Biaya</p>
                                            <p class="mt-1 font-semibold text-emerald-800 dark:text-emerald-200">{formatCurrencyId(row.price)}</p>
                                        </div>
                                        <div class="rounded-xl bg-amber-50/80 px-3 py-2 dark:bg-amber-950/25">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Pembayaran</p>
                                            <p class="mt-1">
                                                <span class={`inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ${luggagePaymentClass(row.payment_status)}`}>
                                                    {row.payment_status ?? '-'}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-2 rounded-xl border border-border/60 bg-background/80 px-3 py-2 text-xs">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Keberangkatan</p>
                                        {#if departureInfo}
                                            <p class="mt-1 font-semibold text-foreground">{departureInfo.primary}</p>
                                            <p class="mt-0.5 text-[11px] text-muted-foreground">{departureInfo.secondary}</p>
                                        {:else if normalizedLuggageStatus === luggageReceivedStatus}
                                            <p class="mt-1 text-[11px] text-muted-foreground">Belum dimapping ke keberangkatan</p>
                                        {:else}
                                            <p class="mt-1 text-[11px] text-muted-foreground">-</p>
                                        {/if}
                                    </div>

                                    {#if row.notes}
                                        <p class="mt-2 line-clamp-2 rounded-xl bg-muted/20 px-3 py-2 text-[11px] text-muted-foreground">
                                            {row.notes}
                                        </p>
                                    {/if}
                                </article>
                            {/each}
                        {/if}
                    </div>

                    <div class="hidden overflow-x-auto rounded-[24px] border border-border/70 bg-card shadow-sm md:block">
                        <table class="min-w-full text-[12px] leading-snug">
                            <thead class="bg-muted/30 text-[10px] uppercase tracking-[0.08em] text-muted-foreground">
                                <tr>
                                    <th class="px-2.5 py-2 text-left">Pengirim</th>
                                    <th class="px-2.5 py-2 text-left">Penerima</th>
                                    <th class="px-2.5 py-2 text-left">Rute</th>
                                    <th class="px-2.5 py-2 text-left">Layanan / Resi</th>
                                    <th class="px-2.5 py-2 text-left">Jumlah</th>
                                    <th class="px-2.5 py-2 text-left">Status</th>
                                    <th class="px-2.5 py-2 text-left">Berangkat</th>
                                    <th class="px-2.5 py-2 text-left">Pembayaran</th>
                                    <th class="px-2.5 py-2 text-left">Biaya</th>
                                    <th class="px-2.5 py-2 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#if luggages.length === 0}
                                    <tr>
                                        <td colspan="10" class="px-4 py-8 text-center text-xs text-muted-foreground">
                                            Belum ada data bagasi yang cocok dengan filter saat ini.
                                        </td>
                                    </tr>
                                {:else}
                                    {#each luggages as row (row.id)}
                                        {@const normalizedLuggageStatus = normalizeLuggageStatus(row.status)}
                                        {@const departureInfo = luggageDepartureInfo(row)}
                                        <tr class="border-t border-border/60 align-top transition-colors hover:bg-muted/10">
                                            <td class="px-2.5 py-2">
                                                <span class="block font-semibold text-foreground">{row.sender_name}</span>
                                                <span class="mt-0.5 block text-[10px] text-muted-foreground">{row.sender_phone || '-'}</span>
                                                <span class="mt-0.5 block max-w-[18rem] truncate text-[10px] text-muted-foreground">{row.sender_address || '-'}</span>
                                            </td>
                                            <td class="px-2.5 py-2">
                                                <span class="block font-semibold text-foreground">{row.receiver_name}</span>
                                                <span class="mt-0.5 block text-[10px] text-muted-foreground">{row.receiver_phone || '-'}</span>
                                                <span class="mt-0.5 block max-w-[18rem] truncate text-[10px] text-muted-foreground">{row.receiver_address || '-'}</span>
                                            </td>
                                            <td class="px-2.5 py-2">
                                                <span class="block font-semibold text-foreground">{row.route_name ?? row.rute ?? routes.find((route) => route.id === Number(row.rute_id || 0))?.name ?? '-'}</span>
                                                <span class="mt-0.5 block text-[10px] text-muted-foreground">{row.tanggal || '-'}</span>
                                            </td>
                                            <td class="px-2.5 py-2">
                                                <span class="block font-semibold text-foreground">{row.service_name ?? '-'}</span>
                                                <span class="mt-0.5 block text-[10px] text-muted-foreground">{row.kode_resi ?? '-'}</span>
                                                <span class="mt-0.5 block max-w-[14rem] truncate text-[10px] text-muted-foreground">{row.notes ?? 'Tanpa catatan'}</span>
                                            </td>
                                            <td class="px-2.5 py-2">
                                                <span class="block font-semibold text-foreground">{row.quantity} barang</span>
                                            </td>
                                            <td class="px-2.5 py-2">
                                                <span class={`inline-flex rounded-full px-1.5 py-0.5 text-[9px] font-semibold ${luggageStatusClass(row.status)}`}>{luggageStatusLabel(row.status)}</span>
                                            </td>
                                            <td class="px-2.5 py-2">
                                                {#if departureInfo}
                                                    <span class="block font-semibold text-foreground">{departureInfo.primary}</span>
                                                    <span class="mt-0.5 block text-[10px] text-muted-foreground">{departureInfo.secondary}</span>
                                                {:else if normalizedLuggageStatus === luggageReceivedStatus}
                                                    <span class="text-[10px] text-muted-foreground">Belum dimapping ke keberangkatan</span>
                                                {:else}
                                                    <span class="text-[10px] text-muted-foreground">-</span>
                                                {/if}
                                            </td>
                                            <td class="px-2.5 py-2">
                                                <span class={`inline-flex rounded-full px-1.5 py-0.5 text-[9px] font-semibold ${luggagePaymentClass(row.payment_status)}`}>{row.payment_status ?? '-'}</span>
                                            </td>
                                            <td class="px-2.5 py-2 font-semibold text-foreground">Rp {Number(row.price).toLocaleString('id-ID')}</td>
                                            <td class="px-2.5 py-2">
                                            <div class="flex items-center justify-end gap-1.5">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    {#snippet children(props)}
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="outline"
                                                            class="h-7 rounded-lg px-2"
                                                            onclick={props.onclick}
                                                            aria-expanded={props['aria-expanded']}
                                                            data-state={props['data-state']}
                                                        >
                                                            <MoreHorizontal class="h-4 w-4" />
                                                        </Button>
                                                    {/snippet}
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" class="w-52">
                                                    {@const lockedLuggageActions = [luggagePickedUpStatus, luggageArrivedStatus].includes(normalizedLuggageStatus)}
                                                    {#if !lockedLuggageActions}
                                                        <DropdownMenuItem onclick={() => openLuggageEditor(row)}>
                                                            Edit Data
                                                        </DropdownMenuItem>
                                                    {/if}
                                                    {#if normalizedLuggageStatus === luggageReceivedStatus}
                                                        <DropdownMenuItem disabled={pendingLuggageActionKey === `action-${row.id}-active`} onclick={() => void luggageAction(row, 'active')}>
                                                            {pendingLuggageActionKey === `action-${row.id}-active` ? 'Memproses...' : 'Tandai Dalam Perjalanan'}
                                                        </DropdownMenuItem>
                                                    {/if}
                                                    {#if row.payment_status !== 'Lunas'}
                                                        <DropdownMenuItem disabled={pendingLuggageActionKey === `action-${row.id}-paid`} onclick={() => void luggageAction(row, 'paid')}>
                                                            {pendingLuggageActionKey === `action-${row.id}-paid` ? 'Memproses...' : 'Tandai Lunas'}
                                                        </DropdownMenuItem>
                                                    {/if}
                                                    <DropdownMenuItem onclick={() => openLuggagePrint(row.id)}>
                                                        Print Resi
                                                    </DropdownMenuItem>
                                                    {#if !lockedLuggageActions}
                                                        <DropdownMenuItem disabled={pendingLuggageActionKey === `action-${row.id}-canceled`} onclick={() => void luggageAction(row, 'canceled')}>
                                                            {pendingLuggageActionKey === `action-${row.id}-canceled` ? 'Memproses...' : 'Batalkan Pengiriman'}
                                                        </DropdownMenuItem>
                                                    {/if}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                            </div>
                                            </td>
                                        </tr>
                                    {/each}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Total: {luggageMeta.total}</p>
                        <div class="flex gap-2">
                            <Button type="button" variant="outline" disabled={luggageMeta.page <= 1} onclick={() => void jumpPage(luggageMeta.page - 1, 'luggage')}>Prev</Button>
                            <span class="px-2 py-1 text-sm">{luggageMeta.page} / {luggageMeta.last_page}</span>
                            <Button type="button" variant="outline" disabled={luggageMeta.page >= luggageMeta.last_page} onclick={() => void jumpPage(luggageMeta.page + 1, 'luggage')}>Next</Button>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'assignments' && !busy}
                <div class="flex gap-2">
                    <Button type="button" variant="outline" onclick={() => void bulkDeleteAssignments()} disabled={selectedAssignmentIds.length === 0}>Bulk Delete ({selectedAssignmentIds.length})</Button>
                </div>
                <form class="grid gap-3 md:grid-cols-4" onsubmit={saveAssignment}>
                    <Input placeholder="Rute" bind:value={assignmentForm.rute} required />
                    <input
                        bind:this={assignmentDateInput}
                        type="text"
                        value={assignmentForm.tanggal}
                        readonly
                        autocomplete="off"
                        placeholder="Tanggal"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
                    <input
                        bind:this={assignmentTimeInput}
                        type="text"
                        value={assignmentForm.jam}
                        readonly
                        autocomplete="off"
                        placeholder="Jam"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
                    <Input type="number" min="1" bind:value={assignmentForm.unit} required />
                    <select class="h-9 rounded-md border border-input bg-background px-3 text-sm" bind:value={assignmentForm.driver_id} required>
                        <option value={0}>Pilih Driver</option>{#each drivers as driver (driver.id)}<option value={driver.id}>{driver.nama}</option>{/each}
                    </select>
                    <div class="flex gap-2">
                        <Button type="button" variant="outline" onclick={() => void checkAssignmentConflicts()} disabled={assignmentConflictBusy}>Check Conflict</Button>
                        <LoadingButton type="submit" loading={savingAssignment} loadingText={assignmentForm.id ? 'Menyimpan...' : 'Assigning...'}>{assignmentForm.id ? 'Update' : 'Assign'}</LoadingButton>
                    </div>
                </form>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" bind:checked={assignmentAllowConflict} />
                    Override conflict (paksa simpan)
                </label>
                {#if assignmentConflicts.length > 0}
                    <div class="rounded-md border border-amber-500/40 bg-amber-50/50 p-3 text-sm">
                        <p class="font-medium text-amber-700">Ditemukan konflik assignment:</p>
                        <ul class="mt-2 list-disc pl-5">
                            {#each assignmentConflicts as c (`${c.type}-${c.rute}-${c.tanggal}-${c.jam}-${c.unit}-${c.driver_id}`)}
                                <li>{c.type} - {c.rute} ({c.tanggal} {c.jam}) unit {c.unit} driver {c.driver_name ?? c.driver_id}</li>
                            {/each}
                        </ul>
                    </div>
                {/if}
                <div class="overflow-x-auto rounded-md border">
                    <table class="min-w-full text-sm">
                        <thead class="bg-muted/50"><tr><th class="px-3 py-2 text-left"><input type="checkbox" checked={selectedAssignmentIds.length > 0 && selectedAssignmentIds.length === assignments.length} onchange={selectAllAssignments} /></th><th class="px-3 py-2 text-left">Trip</th><th class="px-3 py-2 text-left">Waktu</th><th class="px-3 py-2 text-left">Unit</th><th class="px-3 py-2 text-left">Driver</th><th class="px-3 py-2 text-left">Aksi</th></tr></thead>
                        <tbody>
                            {#each assignments as row (row.id)}
                                <tr class="border-t">
                                    <td class="px-3 py-2"><input type="checkbox" checked={selectedAssignmentIds.includes(row.id)} onchange={() => (selectedAssignmentIds = toggleSelect(selectedAssignmentIds, row.id))} /></td>
                                    <td class="px-3 py-2">{row.rute}</td><td class="px-3 py-2">{row.tanggal} {String(row.jam).slice(0, 5)}</td><td class="px-3 py-2">{row.unit}</td><td class="px-3 py-2">{row.nama ?? '-'}</td>
                                    <td class="px-3 py-2 space-x-2">
                                        <Button type="button" size="sm" variant="outline" onclick={() => {
 assignmentForm = { id: row.id, rute: row.rute, tanggal: row.tanggal, jam: String(row.jam).slice(0,5), unit: row.unit, driver_id: row.driver_id }; assignmentConflicts = []; assignmentAllowConflict = false; 
}}>Edit</Button>
                                        <Button type="button" size="sm" variant="outline" onclick={() => void removeItem(`/api/admin/assignments/${row.id}`, 'Assignment deleted.')}>Delete</Button>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">Total: {assignmentMeta.total}</p>
                    <div class="flex gap-2">
                        <Button type="button" variant="outline" disabled={assignmentMeta.page <= 1} onclick={() => void jumpPage(assignmentMeta.page - 1, 'assignment')}>Prev</Button>
                        <span class="px-2 py-1 text-sm">{assignmentMeta.page} / {assignmentMeta.last_page}</span>
                        <Button type="button" variant="outline" disabled={assignmentMeta.page >= assignmentMeta.last_page} onclick={() => void jumpPage(assignmentMeta.page + 1, 'assignment')}>Next</Button>
                    </div>
                </div>
            {/if}

            {#if activeTab === 'export' && !busy}
                <div class={mobileFiltersExpanded
                    ? 'grid gap-3 md:grid-cols-4'
                    : 'hidden md:grid md:grid-cols-4'}>
                    <input
                        bind:this={exportFromDateInput}
                        type="text"
                        value={filterFrom}
                        readonly
                        autocomplete="off"
                        placeholder="Tanggal mulai"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
                    <input
                        bind:this={exportToDateInput}
                        type="text"
                        value={filterTo}
                        readonly
                        autocomplete="off"
                        placeholder="Tanggal akhir"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
                    <select class="h-9 rounded-md border border-input bg-background px-3 text-sm" bind:value={exportType}>
                        <option value="reguler">Reguler</option>
                        <option value="bagasi">Bagasi</option>
                        <option value="charter">Charter</option>
                    </select>
                    <a href={exportCsvUrl()} target="_blank" rel="noopener noreferrer">
                        <Button type="button">Download Bookings CSV</Button>
                    </a>
                    <a href={exportRevenueCsvUrl()} target="_blank" rel="noopener noreferrer">
                        <Button type="button" variant="outline">Download Revenue CSV</Button>
                    </a>
                </div>
            {/if}
        </CardContent>
    </Card>
</div>
