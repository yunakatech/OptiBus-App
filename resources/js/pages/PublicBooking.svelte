<script lang="ts">
    import {
        ArrowLeft,
        ArrowRight,
        CalendarDays,
        Check,
        Clock3,
        MapPin,
        MessageCircle,
        Pencil,
        RefreshCw,
        Ticket,
        UserRound,
    } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import { loadFlatpickr } from '@/lib/flatpickr';
    import type { FlatpickrInstance } from '@/lib/flatpickr';

    type Tenant = {
        name: string;
        slug: string;
        phone: string;
        logo_url: string | null;
    };
    type RouteOption = {
        id: number;
        name: string;
        origin: string;
        destination: string;
        pool_name: string;
    };
    type Seat = { code: string; status: 'available' | 'held' | 'booked' };
    type LayoutCell =
        | string
        | number
        | null
        | {
              type?: string;
              kind?: string;
              label?: string | number;
              seat?: string | number;
              number?: string | number;
              marker?: string;
              hidden?: boolean;
              seatStyle?: string;
          };
    type LayoutRow = LayoutCell[];
    type Schedule = {
        id: number;
        jam: string;
        unit: number;
        unit_label: string;
        seats: Seat[];
        total_seats: number;
        layout: LayoutRow[];
    };
    type RequestResult = {
        request_code: string;
        hold_expires_at: string;
        whatsapp_url: string | null;
    };

    let {
        tenant,
        date_min: dateMin,
        date_max: dateMax,
        payment_methods: paymentMethods,
    }: {
        tenant: Tenant;
        date_min: string;
        date_max: string;
        payment_methods: string[];
    } = $props();

    let dateValue = $state('');
    let dateInput = $state<HTMLInputElement | null>(null);
    let datePicker: FlatpickrInstance | null = null;
    let routes = $state<RouteOption[]>([]);
    let schedules = $state<Schedule[]>([]);
    let routeId = $state(0);
    let scheduleKey = $state('');
    let selectedSeats = $state<string[]>([]);
    let passengerNames = $state<Record<string, string>>({});
    let contactName = $state('');
    let phone = $state('');
    let pickupAddress = $state('');
    let paymentMethod = $state('');
    let notes = $state('');
    let step = $state(1);
    let loading = $state(false);
    let submitting = $state(false);
    let error = $state('');
    let requestResult = $state<RequestResult | null>(null);
    let copied = $state(false);

    const selectedSchedule = $derived(
        schedules.find((item) => `${item.id}-${item.unit}` === scheduleKey) ??
            null,
    );
    const selectedRoute = $derived(
        routes.find((item) => item.id === routeId) ?? null,
    );

    onMount(() => {
        dateValue = dateMin;
        paymentMethod = paymentMethods[0] ?? 'Belum Lunas';
        void initDatePicker();
        void loadAvailability();

        return () => {
            datePicker?.destroy();
            datePicker = null;
        };
    });

    async function initDatePicker() {
        if (typeof window === 'undefined' || !dateInput || datePicker) {
            return;
        }

        const flatpickr = await loadFlatpickr();

        if (!dateInput || datePicker) {
            return;
        }

        datePicker = flatpickr(dateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j F Y',
            altInputClass:
                'h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100',
            ariaDateFormat: 'j F Y',
            defaultDate: dateMin,
            minDate: dateMin,
            maxDate: dateMax,
            disableMobile: true,
            onChange: (_selectedDates, dateStr) => {
                changeDate(dateStr || dateMin);
            },
        });
    }

    function csrfToken(): string {
        const token =
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '';

        return decodeURIComponent(token);
    }

    async function loadAvailability() {
        loading = true;
        error = '';
        schedules = [];
        scheduleKey = '';
        selectedSeats = [];

        try {
            const query = new URLSearchParams({ tanggal: dateValue });

            if (routeId > 0) {
                query.set('route_id', String(routeId));
            }

            const response = await fetch(
                `/api/public/booking/${tenant.slug}/availability?${query.toString()}`,
                { headers: { Accept: 'application/json' } },
            );
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.error ?? 'Jadwal belum dapat dimuat.');
            }

            routes = payload.routes ?? [];
            schedules = payload.schedules ?? [];
        } catch (cause) {
            error =
                cause instanceof Error
                    ? cause.message
                    : 'Jadwal belum dapat dimuat.';
        } finally {
            loading = false;
        }
    }

    function changeDate(value: string) {
        dateValue = value;
        routeId = 0;
        void loadAvailability();
    }

    function changeRoute(value: string) {
        routeId = Number(value);
        void loadAvailability();
    }

    function updatePassengerName(seat: string, value: string) {
        passengerNames = { ...passengerNames, [seat]: value };
    }

    function formatDateLabel(value: string): string {
        const date = new Date(`${value}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    }

    function chooseSchedule(schedule: Schedule) {
        scheduleKey = `${schedule.id}-${schedule.unit}`;
        selectedSeats = [];
        passengerNames = {};
    }

    function chooseScheduleKey(value: string) {
        const schedule = schedules.find(
            (item) => `${item.id}-${item.unit}` === value,
        );

        if (schedule) {
            chooseSchedule(schedule);
        } else {
            scheduleKey = '';
            selectedSeats = [];
        }
    }

    function availableSeatCount(schedule: Schedule): number {
        return schedule.seats.filter((seat) => seat.status === 'available')
            .length;
    }

    function layoutRows(schedule: Schedule): LayoutRow[] {
        return schedule.layout?.length
            ? schedule.layout
            : [schedule.seats.map((seat) => seat.code)];
    }

    function cellType(cell: LayoutCell): string {
        return typeof cell === 'object' && cell !== null
            ? (cell.type ?? cell.kind ?? '').toLowerCase()
            : '';
    }

    function cellCode(cell: LayoutCell): string {
        if (typeof cell === 'object' && cell !== null) {
            return String(cell.label ?? cell.seat ?? cell.number ?? '').trim();
        }

        return String(cell ?? '').trim();
    }

    function isHiddenCell(cell: LayoutCell): boolean {
        return (
            typeof cell === 'object' && cell !== null && cell.hidden === true
        );
    }

    function isAisleCell(cell: LayoutCell): boolean {
        return (
            cellType(cell) === 'driver' ||
            cellType(cell) === 'aisle' ||
            (typeof cell === 'object' &&
                cell?.marker?.toLowerCase() === 'aisle')
        );
    }

    function isSeatCell(cell: LayoutCell): boolean {
        if (isHiddenCell(cell) || isAisleCell(cell)) {
            return false;
        }

        const type = cellType(cell);

        if (type === 'empty' || type === 'blank') {
            return false;
        }

        const code = cellCode(cell).toUpperCase();

        return (
            (type === '' || type === 'seat' || type === 'sleeper') &&
            code !== '' &&
            !['DRIVER', 'AISLE', '-', '_'].includes(code)
        );
    }

    function seatForCell(cell: LayoutCell): Seat | null {
        const code = cellCode(cell).toUpperCase();

        return (
            selectedSchedule?.seats.find(
                (seat) => seat.code.toUpperCase() === code,
            ) ?? null
        );
    }

    function toggleSeat(seat: Seat) {
        if (seat.status === 'booked' || !selectedSchedule) {
            return;
        }

        if (selectedSeats.includes(seat.code)) {
            selectedSeats = selectedSeats.filter((item) => item !== seat.code);
            const next = { ...passengerNames };
            delete next[seat.code];
            passengerNames = next;
        } else {
            selectedSeats = [...selectedSeats, seat.code];
        }
    }

    function continueToDetails() {
        error = '';

        if (!selectedSchedule || selectedSeats.length === 0) {
            error = 'Pilih jadwal dan minimal satu kursi.';

            return;
        }

        step = 3;
    }

    function continueToReview() {
        error = '';

        if (!contactName.trim() || !phone.trim() || !pickupAddress.trim()) {
            error = 'Lengkapi nama, nomor HP, dan alamat penjemputan.';

            return;
        }

        if (selectedSeats.some((seat) => !passengerNames[seat]?.trim())) {
            error = 'Isi nama setiap penumpang.';

            return;
        }

        step = 4;
    }

    async function submitRequest() {
        if (!selectedSchedule || !selectedRoute || submitting) {
            return;
        }

        submitting = true;
        error = '';

        try {
            const response = await fetch(
                `/api/public/booking/${tenant.slug}/requests`,
                {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({
                        route_id: selectedRoute.id,
                        schedule_id: selectedSchedule.id,
                        tanggal: dateValue,
                        unit: selectedSchedule.unit,
                        contact_name: contactName,
                        phone,
                        pickup_address: pickupAddress,
                        payment_method: paymentMethod,
                        notes,
                        passengers: selectedSeats.map((seat) => ({
                            seat,
                            passenger_name: passengerNames[seat],
                        })),
                        website: '',
                    }),
                },
            );
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(
                    payload.error ?? 'Request booking gagal dikirim.',
                );
            }

            requestResult = payload;
            step = 5;
        } catch (cause) {
            error =
                cause instanceof Error
                    ? cause.message
                    : 'Request booking gagal dikirim.';
        } finally {
            submitting = false;
        }
    }

    async function copyCode() {
        if (!requestResult) {
            return;
        }

        await navigator.clipboard?.writeText(requestResult.request_code);
        copied = true;
        setTimeout(() => (copied = false), 1800);
    }

    function formatHold(value: string): string {
        return new Date(value).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
        });
    }
</script>

<svelte:head>
    <title>Booking {tenant.name}</title>
    <meta
        name="description"
        content={`Pesan perjalanan bersama ${tenant.name}`}
    />
</svelte:head>

<main
    class="min-h-screen bg-[#f5f7f2] text-slate-900 selection:bg-emerald-200 dark:bg-slate-950 dark:text-slate-100"
>
    <div
        class="mx-auto min-h-screen w-full max-w-xl bg-[#f5f7f2] px-4 pb-28 pt-4 sm:px-6 sm:pt-6 dark:bg-slate-950"
    >
        {#if step < 5}
            <section
                class="mb-5 rounded-[2rem] bg-slate-950 p-5 text-white shadow-xl shadow-slate-900/10"
            >
                <div
                    class="mb-6 flex items-start justify-between gap-4 border-b border-slate-800 pb-5"
                >
                    <div class="min-w-0">
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300"
                        >
                            Booking resmi
                        </p>
                        {#if tenant.logo_url}
                            <img
                                src={tenant.logo_url}
                                alt={tenant.name}
                                class="mt-2 max-h-14 max-w-[12rem] object-contain object-left"
                            />
                        {:else}
                            <h1
                                class="mt-1 truncate text-xl font-black tracking-tight"
                            >
                                {tenant.name}
                            </h1>
                        {/if}
                    </div>
                    <Ticket
                        class="h-9 w-9 shrink-0 text-emerald-300"
                        strokeWidth={1.5}
                    />
                </div>
                <div>
                    <p class="text-sm text-emerald-300">
                        Pesan perjalanan tanpa antre
                    </p>
                    <h2 class="mt-1 text-2xl font-black leading-tight">
                        Pilih kursi,<br />kami konfirmasi.
                    </h2>
                </div>
                <div
                    class="mt-6 flex items-center gap-2 text-[11px] font-bold text-slate-400"
                >
                    {#each ['Tanggal', 'Kursi', 'Data', 'Review'] as label, index (label)}
                        <div
                            class:flex-1={index < 3}
                            class="flex items-center gap-2"
                        >
                            <span
                                class:!bg-emerald-400={step > index + 1}
                                class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-slate-700 text-white"
                                >{step > index + 1 ? '✓' : index + 1}</span
                            >
                            <span class="hidden sm:inline">{label}</span>
                            {#if index < 3}<span
                                    class="h-px flex-1 bg-slate-700"
                                ></span>{/if}
                        </div>
                    {/each}
                </div>
            </section>
        {/if}

        {#if error}
            <div
                role="alert"
                class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900 dark:bg-red-950/60 dark:text-red-200"
            >
                {error}
            </div>
        {/if}

        {#if step === 1 || step === 2}
            <section class="space-y-4">
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <label
                        for="public-date"
                        class="mb-3 flex items-center gap-2 text-sm font-black"
                    >
                        <CalendarDays class="h-4 w-4 text-emerald-600" />
                        Tanggal perjalanan
                    </label>
                    <div class="relative">
                        <input
                            id="public-date"
                            bind:this={dateInput}
                            value={dateValue}
                            type="text"
                            placeholder="Pilih tanggal"
                            autocomplete="off"
                            readonly
                            aria-label="Tanggal perjalanan"
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400"
                        />
                    </div>
                    <p
                        class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400"
                    >
                        Pilih tanggal keberangkatan yang tersedia.
                    </p>
                </div>
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <label
                        for="public-route"
                        class="mb-3 flex items-center gap-2 text-sm font-black"
                        ><MapPin class="h-4 w-4 text-emerald-600" /> Mau ke mana?</label
                    >
                    <select
                        id="public-route"
                        value={routeId}
                        onchange={(event) =>
                            changeRoute(event.currentTarget.value)}
                        class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option value={0}>Pilih rute</option>
                        {#each routes as route (route.id)}
                            <option value={route.id}
                                >{route.origin && route.destination
                                    ? `${route.origin} → ${route.destination}`
                                    : route.name}</option
                            >
                        {/each}
                    </select>
                </div>
                {#if loading}
                    <div
                        class="flex items-center justify-center gap-2 py-12 text-sm font-semibold text-slate-500 dark:text-slate-400"
                    >
                        <RefreshCw class="h-4 w-4 animate-spin" /> Memuat jadwal...
                    </div>
                {:else if routeId > 0}
                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                    >
                        <label
                            for="public-schedule"
                            class="mb-3 flex items-center gap-2 text-sm font-black"
                        >
                            <Clock3 class="h-4 w-4 text-emerald-600" /> Jam keberangkatan
                        </label>
                        <select
                            id="public-schedule"
                            value={scheduleKey}
                            onchange={(event) =>
                                chooseScheduleKey(event.currentTarget.value)}
                            disabled={schedules.length === 0}
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="">Pilih jam keberangkatan</option>
                            {#each schedules as schedule (`${schedule.id}-${schedule.unit}`)}
                                <option
                                    value={`${schedule.id}-${schedule.unit}`}
                                >
                                    {schedule.jam} · {schedule.unit_label} · {availableSeatCount(
                                        schedule,
                                    )} kursi tersedia
                                </option>
                            {/each}
                        </select>
                        {#if schedules.length === 0}
                            <p
                                class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                Belum ada jadwal untuk tanggal dan rute ini.
                            </p>
                        {/if}
                    </div>

                    {#if selectedSchedule}
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p
                                        class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300"
                                    >
                                        Pilih kursi
                                    </p>
                                    <h3 class="mt-1 text-lg font-black">
                                        {selectedSchedule.jam} · {selectedSchedule.unit_label}
                                    </h3>
                                </div>
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200"
                                >
                                    {availableSeatCount(selectedSchedule)} tersedia
                                </span>
                            </div>

                            <div
                                class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-[11px] font-bold text-slate-500 dark:text-slate-400"
                            >
                                <span class="flex items-center gap-1.5"
                                    ><i
                                        class="h-3 w-3 rounded bg-emerald-100 ring-1 ring-emerald-300"
                                    ></i>Tersedia</span
                                >
                                <span class="flex items-center gap-1.5"
                                    ><i class="h-3 w-3 rounded bg-emerald-600"
                                    ></i>Dipilih</span
                                >
                                <span class="flex items-center gap-1.5"
                                    ><i
                                        class="h-3 w-3 rounded bg-amber-200 ring-1 ring-amber-300 dark:bg-amber-700 dark:ring-amber-600"
                                    ></i>Ditahan</span
                                >
                                <span class="flex items-center gap-1.5"
                                    ><i
                                        class="h-3 w-3 rounded bg-slate-300 dark:bg-slate-600"
                                    ></i>Terisi</span
                                >
                            </div>

                            <div
                                class="mt-5 overflow-x-auto rounded-2xl bg-slate-50 p-3 dark:bg-slate-800"
                            >
                                <div
                                    class="mx-auto min-w-[280px] max-w-md space-y-2"
                                >
                                    {#each layoutRows(selectedSchedule) as row, rowIndex (`layout-row-${rowIndex}`)}
                                        <div
                                            class="grid gap-2"
                                            style={`grid-template-columns: repeat(${Math.max(row.length, 1)}, minmax(0, 1fr));`}
                                        >
                                            {#each row as cell, colIndex (`layout-cell-${rowIndex}-${colIndex}-${cellCode(cell)}`)}
                                                {#if isHiddenCell(cell)}
                                                    <div
                                                        class="h-11"
                                                        aria-hidden="true"
                                                    ></div>
                                                {:else if cellType(cell) === 'driver'}
                                                    <div
                                                        class="flex h-11 items-center justify-center rounded-xl bg-slate-900 px-1 text-[10px] font-black uppercase tracking-wider text-white"
                                                    >
                                                        Driver
                                                    </div>
                                                {:else if isAisleCell(cell)}
                                                    <div
                                                        class="flex h-11 items-center justify-center rounded-xl border border-dashed border-amber-300 bg-amber-50 px-1 text-[9px] font-black uppercase tracking-wider text-amber-700 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-200"
                                                    >
                                                        Lorong
                                                    </div>
                                                {:else if isSeatCell(cell)}
                                                    {@const seat =
                                                        seatForCell(cell)}
                                                    {#if seat}
                                                        <button
                                                            type="button"
                                                            onclick={() =>
                                                                toggleSeat(
                                                                    seat,
                                                                )}
                                                            disabled={seat.status !==
                                                                'available'}
                                                            aria-label={`Kursi ${seat.code} ${seat.status === 'booked' ? 'terisi' : seat.status === 'held' ? 'ditahan' : selectedSeats.includes(seat.code) ? 'dipilih' : 'tersedia'}`}
                                                            class:!bg-slate-300={seat.status ===
                                                                'booked'}
                                                            class:!bg-amber-200={seat.status ===
                                                                'held'}
                                                            class:!bg-emerald-600={selectedSeats.includes(
                                                                seat.code,
                                                            )}
                                                            class:!text-white={selectedSeats.includes(
                                                                seat.code,
                                                            )}
                                                            class="grid h-11 w-full place-items-center rounded-xl bg-emerald-100 text-xs font-black text-emerald-800 ring-1 ring-inset ring-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-200 disabled:cursor-not-allowed disabled:text-slate-500 disabled:hover:translate-y-0 dark:bg-emerald-950 dark:text-emerald-200 dark:ring-emerald-800 dark:hover:bg-emerald-900 dark:disabled:text-slate-500"
                                                        >
                                                            {seat.code}
                                                        </button>
                                                    {:else}
                                                        <div
                                                            class="h-11 rounded-xl bg-slate-100 dark:bg-slate-700"
                                                        ></div>
                                                    {/if}
                                                {:else}
                                                    <div
                                                        class="h-11 rounded-xl bg-slate-100 dark:bg-slate-700"
                                                        aria-hidden="true"
                                                    ></div>
                                                {/if}
                                            {/each}
                                        </div>
                                    {/each}
                                </div>
                            </div>
                            <p
                                class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                Pilih satu atau beberapa kursi. Kursi ditahan
                                setelah request dikirim.
                            </p>
                        </div>
                    {/if}
                {/if}
            </section>
            {#if selectedSchedule}
                <div
                    class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-4 backdrop-blur dark:border-slate-700 dark:bg-slate-900/95"
                >
                    <div
                        class="mx-auto flex max-w-xl items-center justify-between gap-3"
                    >
                        <div>
                            <p
                                class="text-xs font-bold text-slate-500 dark:text-slate-400"
                            >
                                {selectedSchedule.jam} · {selectedSchedule.unit_label}
                            </p>
                            <p class="font-black">
                                {selectedSeats.length} kursi dipilih
                            </p>
                        </div>
                        <button
                            type="button"
                            onclick={continueToDetails}
                            class="flex h-12 items-center gap-2 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20"
                            >Lanjut <ArrowRight class="h-4 w-4" /></button
                        >
                    </div>
                </div>
            {/if}
        {:else if step === 3}
            <section
                class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300"
                        >
                            Langkah 3 dari 4
                        </p>
                        <h2 class="text-2xl font-black">Lengkapi data</h2>
                        <p
                            class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Nama penumpang diisi sesuai kursi yang dipilih.
                        </p>
                    </div>
                    <UserRound class="h-7 w-7 text-emerald-600" />
                </div>
                <div class="space-y-4">
                    {#each selectedSeats as seat, passengerIndex (seat)}
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-700 dark:bg-slate-800/70"
                        >
                            <div
                                class="mb-3 flex items-center justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-black text-slate-900 dark:text-slate-100"
                                    >
                                        {passengerNames[seat]?.trim() ||
                                            `Penumpang ${passengerIndex + 1}`}
                                    </p>
                                    <p
                                        class="text-xs font-bold text-slate-500 dark:text-slate-400"
                                    >
                                        Kursi {seat}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-emerald-200 dark:ring-slate-600"
                                >
                                    Slot {passengerIndex + 1}
                                </span>
                            </div>
                            <label
                                for={`passenger-${seat}`}
                                class="mb-1 block text-xs font-bold text-slate-500 dark:text-slate-400"
                                >Nama penumpang</label
                            ><input
                                id={`passenger-${seat}`}
                                value={passengerNames[seat] ?? ''}
                                oninput={(event) =>
                                    updatePassengerName(
                                        seat,
                                        event.currentTarget.value,
                                    )}
                                placeholder="Nama lengkap"
                                class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400"
                            />
                        </div>
                    {/each}
                    <div>
                        <label
                            for="contact-name"
                            class="mb-1 block text-xs font-bold text-slate-500 dark:text-slate-400"
                            >Nama pemesan</label
                        ><input
                            id="contact-name"
                            bind:value={contactName}
                            placeholder="Nama yang bisa dihubungi"
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400"
                        />
                    </div>
                    <div>
                        <label
                            for="contact-phone"
                            class="mb-1 block text-xs font-bold text-slate-500 dark:text-slate-400"
                            >Nomor HP / WhatsApp</label
                        ><input
                            id="contact-phone"
                            bind:value={phone}
                            inputmode="tel"
                            placeholder="08xxxxxxxxxx"
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400"
                        />
                    </div>
                    <div>
                        <label
                            for="pickup-address"
                            class="mb-1 block text-xs font-bold text-slate-500 dark:text-slate-400"
                            >Alamat penjemputan</label
                        ><textarea
                            id="pickup-address"
                            bind:value={pickupAddress}
                            rows="3"
                            placeholder="Tulis alamat atau patokan"
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400"
                        ></textarea>
                    </div>
                    <div>
                        <label
                            for="payment-method"
                            class="mb-1 block text-xs font-bold text-slate-500 dark:text-slate-400"
                            >Pembayaran</label
                        ><select
                            id="payment-method"
                            bind:value={paymentMethod}
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            >{#each paymentMethods as method (method)}<option
                                    value={method}>{method}</option
                                >{/each}</select
                        >
                    </div>
                    <div>
                        <label
                            for="booking-notes"
                            class="mb-1 block text-xs font-bold text-slate-500 dark:text-slate-400"
                            >Catatan <span class="font-normal">(opsional)</span
                            ></label
                        ><textarea
                            id="booking-notes"
                            bind:value={notes}
                            rows="2"
                            placeholder="Contoh: turun di titik tertentu"
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400"
                        ></textarea>
                    </div>
                </div>
            </section>
            <div
                class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-4 backdrop-blur dark:border-slate-700 dark:bg-slate-900/95"
            >
                <div class="mx-auto flex max-w-xl justify-between gap-3">
                    <button
                        type="button"
                        onclick={() => (step = 2)}
                        class="flex h-12 items-center gap-2 rounded-2xl border border-slate-200 px-4 text-sm font-black dark:border-slate-700"
                        ><ArrowLeft class="h-4 w-4" /> Kembali</button
                    ><button
                        type="button"
                        onclick={continueToReview}
                        class="flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-700 text-sm font-black text-white"
                        >Review booking <ArrowRight class="h-4 w-4" /></button
                    >
                </div>
            </div>
        {:else if step === 4}
            <section
                class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300"
                        >
                            Langkah 4 dari 4
                        </p>
                        <h2 class="mt-1 text-2xl font-black">Review booking</h2>
                        <p
                            class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Pastikan data sudah benar sebelum dikirim.
                        </p>
                    </div>
                    <Ticket class="h-7 w-7 text-emerald-600" />
                </div>

                <div class="space-y-3">
                    <div
                        class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/60"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300"
                        >
                            Perjalanan
                        </p>
                        <p
                            class="mt-2 text-base font-black text-emerald-950 dark:text-emerald-100"
                        >
                            {selectedRoute?.origin} → {selectedRoute?.destination}
                        </p>
                        <p
                            class="mt-1 text-sm font-semibold text-emerald-800 dark:text-emerald-200"
                        >
                            {formatDateLabel(dateValue)} ·
                            {selectedSchedule?.jam} ·
                            {selectedSchedule?.unit_label}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                        >
                            Penumpang & kursi
                        </p>
                        <div class="mt-3 space-y-2">
                            {#each selectedSeats as seat, passengerIndex (seat)}
                                <div
                                    class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5 dark:bg-slate-800"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-black text-slate-900 dark:text-slate-100"
                                        >
                                            {passengerNames[seat] ||
                                                `Penumpang ${passengerIndex + 1}`}
                                        </p>
                                        <p
                                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            Slot {passengerIndex + 1}
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-lg bg-white px-2.5 py-1 text-xs font-black text-emerald-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-emerald-200 dark:ring-slate-600"
                                        >Kursi {seat}</span
                                    >
                                </div>
                            {/each}
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                        >
                            Data pemesan
                        </p>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt
                                    class="font-semibold text-slate-500 dark:text-slate-400"
                                >
                                    Nama
                                </dt>
                                <dd
                                    class="text-right font-black text-slate-900 dark:text-slate-100"
                                >
                                    {contactName}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt
                                    class="font-semibold text-slate-500 dark:text-slate-400"
                                >
                                    Nomor HP
                                </dt>
                                <dd
                                    class="text-right font-black text-slate-900 dark:text-slate-100"
                                >
                                    {phone}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt
                                    class="font-semibold text-slate-500 dark:text-slate-400"
                                >
                                    Pembayaran
                                </dt>
                                <dd
                                    class="text-right font-black text-slate-900 dark:text-slate-100"
                                >
                                    {paymentMethod}
                                </dd>
                            </div>
                            <div
                                class="border-t border-slate-100 pt-2 dark:border-slate-700"
                            >
                                <dt
                                    class="font-semibold text-slate-500 dark:text-slate-400"
                                >
                                    Alamat penjemputan
                                </dt>
                                <dd
                                    class="mt-1 font-bold leading-5 text-slate-900 dark:text-slate-100"
                                >
                                    {pickupAddress}
                                </dd>
                            </div>
                            {#if notes.trim()}
                                <div
                                    class="border-t border-slate-100 pt-2 dark:border-slate-700"
                                >
                                    <dt
                                        class="font-semibold text-slate-500 dark:text-slate-400"
                                    >
                                        Catatan
                                    </dt>
                                    <dd
                                        class="mt-1 font-bold leading-5 text-slate-900 dark:text-slate-100"
                                    >
                                        {notes}
                                    </dd>
                                </div>
                            {/if}
                        </dl>
                    </div>
                </div>

                <div
                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs font-semibold leading-5 text-amber-800 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-200"
                >
                    Setelah dikirim, kursi ditahan selama 15 menit sambil
                    menunggu konfirmasi admin pool.
                </div>
            </section>
            <div
                class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-4 backdrop-blur dark:border-slate-700 dark:bg-slate-900/95"
            >
                <div class="mx-auto flex max-w-xl justify-between gap-3">
                    <button
                        type="button"
                        onclick={() => (step = 3)}
                        class="flex h-12 items-center gap-2 rounded-2xl border border-slate-200 px-4 text-sm font-black dark:border-slate-700"
                        ><Pencil class="h-4 w-4" /> Edit data</button
                    >
                    <button
                        type="button"
                        onclick={submitRequest}
                        disabled={submitting}
                        class="flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-700 text-sm font-black text-white disabled:opacity-50"
                        >{submitting ? 'Mengirim...' : 'Kirim request'}</button
                    >
                </div>
            </div>
        {:else}
            <section
                class="rounded-[2rem] bg-slate-950 p-6 text-center text-white shadow-xl"
            >
                <div
                    class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-400 text-slate-950"
                >
                    <Check class="h-8 w-8" strokeWidth={3} />
                </div>
                <p class="mt-5 text-sm font-bold text-emerald-300">
                    Request sudah masuk
                </p>
                <h2 class="mt-1 text-3xl font-black">
                    {requestResult?.request_code}
                </h2>
                <p
                    class="mx-auto mt-3 max-w-xs text-sm leading-6 text-slate-300"
                >
                    Admin pool akan memeriksa dan menyetujui booking Anda. Kursi
                    ditahan sampai pukul {requestResult
                        ? formatHold(requestResult.hold_expires_at)
                        : '-'}.
                </p>
                <div class="mt-6 grid gap-3">
                    {#if requestResult?.whatsapp_url}<a
                            href={requestResult.whatsapp_url}
                            target="_blank"
                            rel="noreferrer"
                            class="flex h-12 items-center justify-center gap-2 rounded-2xl bg-[#25D366] text-sm font-black text-slate-950"
                            ><MessageCircle class="h-5 w-5" /> Kirim ke WhatsApp</a
                        >{/if}<button
                        type="button"
                        onclick={copyCode}
                        class="h-12 rounded-2xl border border-slate-700 text-sm font-black"
                        >{copied
                            ? 'Kode tersalin'
                            : 'Salin kode request'}</button
                    >
                </div>
            </section>
        {/if}
    </div>
</main>
