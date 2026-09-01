<script lang="ts">
    import {
        ArrowLeft,
        ArrowRight,
        Check,
        Clock3,
        MapPin,
        MessageCircle,
        RefreshCw,
        Ticket,
        UserRound,
    } from 'lucide-svelte';
    import { onMount } from 'svelte';

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
    type Schedule = {
        id: number;
        jam: string;
        unit: number;
        unit_label: string;
        seats: Seat[];
        total_seats: number;
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
        void loadAvailability();
    });

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

    function chooseSchedule(schedule: Schedule) {
        scheduleKey = `${schedule.id}-${schedule.unit}`;
        selectedSeats = [];
        passengerNames = {};
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

<main class="min-h-screen bg-[#f5f7f2] text-slate-900 selection:bg-emerald-200">
    <div
        class="mx-auto min-h-screen w-full max-w-xl bg-[#f5f7f2] px-4 pb-28 sm:px-6"
    >
        <header class="flex items-center gap-3 py-5">
            {#if tenant.logo_url}
                <img
                    src={tenant.logo_url}
                    alt={tenant.name}
                    class="h-11 w-11 rounded-2xl object-cover shadow-sm"
                />
            {:else}
                <div
                    class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-700 text-lg font-black text-white"
                >
                    {tenant.name.slice(0, 1).toUpperCase()}
                </div>
            {/if}
            <div class="min-w-0">
                <p
                    class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-700"
                >
                    Booking resmi
                </p>
                <h1 class="truncate text-xl font-black tracking-tight">
                    {tenant.name}
                </h1>
            </div>
        </header>

        {#if step < 5}
            <section
                class="mb-5 rounded-[2rem] bg-slate-950 p-5 text-white shadow-xl shadow-slate-900/10"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-emerald-300">
                            Pesan perjalanan tanpa antre
                        </p>
                        <h2 class="mt-1 text-2xl font-black leading-tight">
                            Pilih kursi,<br />kami konfirmasi.
                        </h2>
                    </div>
                    <Ticket
                        class="h-9 w-9 text-emerald-300"
                        strokeWidth={1.5}
                    />
                </div>
                <div
                    class="mt-6 flex items-center gap-2 text-[11px] font-bold text-slate-400"
                >
                    {#each ['Tanggal', 'Kursi', 'Data', 'Kirim'] as label, index (label)}
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
                class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
            >
                {error}
            </div>
        {/if}

        {#if step === 1 || step === 2}
            <section class="space-y-4">
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div
                        class="mb-3 flex items-center gap-2 text-sm font-black"
                    >
                        <Clock3 class="h-4 w-4 text-emerald-600" /> Kapan berangkat?
                    </div>
                    <input
                        type="date"
                        min={dateMin}
                        max={dateMax}
                        bind:value={dateValue}
                        onchange={(event) =>
                            changeDate(event.currentTarget.value)}
                        class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </div>
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm"
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
                        class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500"
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
                        class="flex items-center justify-center gap-2 py-12 text-sm font-semibold text-slate-500"
                    >
                        <RefreshCw class="h-4 w-4 animate-spin" /> Memuat jadwal...
                    </div>
                {:else if routeId > 0}
                    <div class="space-y-3">
                        <p
                            class="px-1 text-xs font-black uppercase tracking-[0.18em] text-slate-500"
                        >
                            Pilih jam dan kursi
                        </p>
                        {#each schedules as schedule (`${schedule.id}-${schedule.unit}`)}
                            <div
                                role="button"
                                tabindex="0"
                                onclick={() => chooseSchedule(schedule)}
                                onkeydown={(event) => {
                                    if (
                                        event.key === 'Enter' ||
                                        event.key === ' '
                                    ) {
                                        chooseSchedule(schedule);
                                    }
                                }}
                                class:!border-emerald-500={scheduleKey ===
                                    `${schedule.id}-${schedule.unit}`}
                                class="w-full cursor-pointer rounded-3xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:border-emerald-300"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-xl font-black"
                                            >{schedule.jam}</span
                                        ><span
                                            class="ml-2 text-xs font-semibold text-slate-500"
                                            >{schedule.unit_label}</span
                                        >
                                    </div>
                                    <span
                                        class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700"
                                        >{schedule.seats.filter(
                                            (seat) =>
                                                seat.status === 'available',
                                        ).length} kursi</span
                                    >
                                </div>
                                <div
                                    class="mt-4 grid grid-cols-5 gap-2 sm:grid-cols-8"
                                >
                                    {#each schedule.seats as seat (seat.code)}
                                        <button
                                            type="button"
                                            onclick={(event) => {
                                                event.stopPropagation();

                                                if (
                                                    scheduleKey !==
                                                    `${schedule.id}-${schedule.unit}`
                                                ) {
                                                    chooseSchedule(schedule);
                                                }

                                                toggleSeat(seat);
                                            }}
                                            disabled={seat.status !==
                                                'available'}
                                            aria-label={`Kursi ${seat.code} ${seat.status === 'booked' ? 'terisi' : seat.status === 'held' ? 'ditahan' : 'tersedia'}`}
                                            class:!bg-slate-300={seat.status ===
                                                'booked'}
                                            class:!bg-amber-200={seat.status ===
                                                'held'}
                                            class:!bg-emerald-600={scheduleKey ===
                                                `${schedule.id}-${schedule.unit}` &&
                                                selectedSeats.includes(
                                                    seat.code,
                                                )}
                                            class:!text-white={scheduleKey ===
                                                `${schedule.id}-${schedule.unit}` &&
                                                selectedSeats.includes(
                                                    seat.code,
                                                )}
                                            class="grid h-10 w-full place-items-center rounded-xl bg-emerald-50 text-xs font-black text-emerald-700 disabled:cursor-not-allowed disabled:text-slate-500"
                                            >{seat.code}</button
                                        >
                                    {/each}
                                </div>
                            </div>
                        {:else}
                            <div
                                class="rounded-3xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm font-semibold text-slate-500"
                            >
                                Belum ada jadwal untuk tanggal ini.
                            </div>
                        {/each}
                    </div>
                {/if}
            </section>
            {#if selectedSchedule}
                <div
                    class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-4 backdrop-blur"
                >
                    <div
                        class="mx-auto flex max-w-xl items-center justify-between gap-3"
                    >
                        <div>
                            <p class="text-xs font-bold text-slate-500">
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
        {:else if step === 3 || step === 4}
            <section
                class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-widest text-emerald-700"
                        >
                            Data pemesan
                        </p>
                        <h2 class="text-2xl font-black">
                            Siapa yang berangkat?
                        </h2>
                    </div>
                    <UserRound class="h-7 w-7 text-emerald-600" />
                </div>
                <div class="space-y-4">
                    {#each selectedSeats as seat (seat)}
                        <div>
                            <label
                                for={`passenger-${seat}`}
                                class="mb-1 block text-xs font-bold text-slate-500"
                                >Nama penumpang · Kursi {seat}</label
                            ><input
                                id={`passenger-${seat}`}
                                value={passengerNames[seat] ?? ''}
                                oninput={(event) =>
                                    (passengerNames[seat] =
                                        event.currentTarget.value)}
                                placeholder="Nama lengkap"
                                class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                    {/each}
                    <div>
                        <label
                            for="contact-name"
                            class="mb-1 block text-xs font-bold text-slate-500"
                            >Nama pemesan</label
                        ><input
                            id="contact-name"
                            bind:value={contactName}
                            placeholder="Nama yang bisa dihubungi"
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label
                            for="contact-phone"
                            class="mb-1 block text-xs font-bold text-slate-500"
                            >Nomor HP / WhatsApp</label
                        ><input
                            id="contact-phone"
                            bind:value={phone}
                            inputmode="tel"
                            placeholder="08xxxxxxxxxx"
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label
                            for="pickup-address"
                            class="mb-1 block text-xs font-bold text-slate-500"
                            >Alamat penjemputan</label
                        ><textarea
                            id="pickup-address"
                            bind:value={pickupAddress}
                            rows="3"
                            placeholder="Tulis alamat atau patokan"
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                    </div>
                    <div>
                        <label
                            for="payment-method"
                            class="mb-1 block text-xs font-bold text-slate-500"
                            >Pembayaran</label
                        ><select
                            id="payment-method"
                            bind:value={paymentMethod}
                            class="h-12 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >{#each paymentMethods as method (method)}<option
                                    value={method}>{method}</option
                                >{/each}</select
                        >
                    </div>
                    <div>
                        <label
                            for="booking-notes"
                            class="mb-1 block text-xs font-bold text-slate-500"
                            >Catatan <span class="font-normal">(opsional)</span
                            ></label
                        ><textarea
                            id="booking-notes"
                            bind:value={notes}
                            rows="2"
                            placeholder="Contoh: turun di titik tertentu"
                            class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                    </div>
                </div>
            </section>
            <div
                class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-4 backdrop-blur"
            >
                <div class="mx-auto flex max-w-xl justify-between gap-3">
                    <button
                        type="button"
                        onclick={() => (step = 2)}
                        class="flex h-12 items-center gap-2 rounded-2xl border border-slate-200 px-4 text-sm font-black"
                        ><ArrowLeft class="h-4 w-4" /> Kembali</button
                    >{#if step === 3}<button
                            type="button"
                            onclick={continueToReview}
                            class="flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-700 text-sm font-black text-white"
                            >Review <ArrowRight class="h-4 w-4" /></button
                        >{:else}<button
                            type="button"
                            onclick={submitRequest}
                            disabled={submitting}
                            class="flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-700 text-sm font-black text-white disabled:opacity-50"
                            >{submitting
                                ? 'Mengirim...'
                                : 'Kirim request'}</button
                        >{/if}
                </div>
            </div>
            {#if step === 4}<div
                    class="mt-4 rounded-3xl border border-emerald-100 bg-emerald-50 p-4 text-sm"
                >
                    <p class="font-black text-emerald-900">
                        Periksa sebelum dikirim
                    </p>
                    <p class="mt-1 text-emerald-800">
                        {selectedRoute?.name} · {dateValue} · {selectedSchedule?.jam}
                        · Kursi {selectedSeats.join(', ')}
                    </p>
                    <p class="mt-1 text-emerald-800">
                        Request akan ditahan 15 menit sambil menunggu konfirmasi
                        admin.
                    </p>
                </div>{/if}
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
