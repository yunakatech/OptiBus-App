<script module lang="ts">
    export const layout = {
        breadcrumbs: [{ title: 'Inbox Booking', href: '/booking-requests' }],
    };
</script>

<script lang="ts">
    import {
        Check,
        Clock3,
        MapPin,
        MessageCircle,
        RefreshCw,
        X,
    } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';

    type RequestRow = {
        id: number;
        request_code: string;
        route_name: string;
        pool_name: string;
        segment_id: number;
        segment_name: string;
        segment_pickup_times: string[];
        pickup_time: string;
        price: number;
        tanggal: string;
        jam: string;
        unit: number;
        contact_name: string;
        phone: string;
        pickup_address: string;
        payment_method: string;
        notes: string;
        status: string;
        hold_expires_at: string;
        seats: { seat: string; passenger_name: string }[];
    };
    let requests = $state<RequestRow[]>([]);
    let loading = $state(false);
    let error = $state('');
    let message = $state('');

    function csrfToken(): string {
        const token =
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '';

        return decodeURIComponent(token);
    }

    async function loadRequests() {
        loading = true;
        error = '';

        try {
            const response = await fetch(
                '/api/admin/public-booking-requests?status=pending',
                { headers: { Accept: 'application/json' } },
            );
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.error ?? 'Inbox gagal dimuat.');
            }

            requests = payload.requests ?? [];
        } catch (cause) {
            error =
                cause instanceof Error ? cause.message : 'Inbox gagal dimuat.';
        } finally {
            loading = false;
        }
    }

    async function approve(item: RequestRow) {
        if (
            !confirm(
                `Setujui request ${item.request_code} menjadi booking resmi?`,
            )
        ) {
            return;
        }

        await action(
            `/api/admin/public-booking-requests/${item.id}/approve`,
            {},
            'Booking resmi dibuat.',
        );
    }

    async function reject(item: RequestRow) {
        const reason = prompt('Alasan penolakan:');

        if (!reason?.trim()) {
            return;
        }

        await action(
            `/api/admin/public-booking-requests/${item.id}/reject`,
            { reason },
            'Request ditolak.',
        );
    }

    async function action(
        url: string,
        body: Record<string, string>,
        successMessage: string,
    ) {
        error = '';
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(body),
        });
        const payload = await response.json();

        if (!response.ok || !payload.success) {
            error = payload.error ?? 'Aksi gagal.';

            return;
        }

        message =
            payload.result?.status === 'rejected' &&
            payload.result?.rejection_reason
                ? `Approval ditolak: ${payload.result.rejection_reason}`
                : successMessage;
        await loadRequests();
    }

    function formatDate(value: string) {
        return new Date(`${value}T00:00:00`).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    }
    function seatSummary(item: RequestRow): string {
        return item.seats
            .map((seat) => `${seat.seat} (${seat.passenger_name})`)
            .join(', ');
    }
    function contactLabel(item: RequestRow): string {
        return `Hubungi ${item.contact_name} via WhatsApp`;
    }
    function formatRupiah(value: number): string {
        return `Rp ${Math.max(0, Number(value || 0)).toLocaleString('id-ID')}`;
    }
    function whatsapp(phone: string, code: string) {
        const target = phone.replace(/\D/g, '').replace(/^0/, '62');

        return `https://wa.me/${target}?text=${encodeURIComponent(`Halo, terkait booking ${code}, kami membutuhkan konfirmasi tambahan.`)}`;
    }
    onMount(loadRequests);
</script>

<AppHead title="Inbox Booking" />
<div class="mx-auto max-w-5xl space-y-5 p-4 sm:p-6">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <p
                class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600"
            >
                Persetujuan pool
            </p>
            <h1 class="mt-1 text-2xl font-black tracking-tight">
                Inbox Booking Publik
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Request baru ditahan 15 menit sebelum kursi kembali tersedia.
            </p>
        </div>
        <button
            type="button"
            onclick={loadRequests}
            disabled={loading}
            class="flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 active:scale-[0.98] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
            >{#if loading}<RefreshCw
                    class="h-4 w-4 animate-spin"
                />{:else}<RefreshCw class="h-4 w-4" />{/if} Refresh</button
        >
    </div>
    {#if message}<div
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"
        >
            {message}
        </div>{/if}
    {#if error}<div
            role="alert"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
        >
            {error}
        </div>{/if}
    {#if loading && requests.length === 0}<div
            class="py-14 text-center text-sm text-muted-foreground"
        >
            Memuat request...
        </div>{:else if requests.length === 0}<div
            class="rounded-3xl border border-dashed border-slate-300 px-5 py-16 text-center"
        >
            <Check class="mx-auto h-8 w-8 text-emerald-600" />
            <p class="mt-3 font-black">Inbox bersih</p>
            <p class="mt-1 text-sm text-muted-foreground">
                Belum ada request booking publik yang menunggu.
            </p>
        </div>{:else}<div class="grid gap-4 lg:grid-cols-2">
            {#each requests as item (item.id)}<article
                    class="rounded-3xl border border-slate-200 bg-white p-5 text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-black tracking-widest text-emerald-700"
                            >
                                {item.request_code}
                            </p>
                            <h2 class="mt-1 text-lg font-black">
                                {item.contact_name}
                            </h2>
                        </div>
                        <span
                            class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700"
                            >Menunggu</span
                        >
                    </div>
                    <div class="mt-4 grid gap-2 text-sm">
                        <p class="flex items-center gap-2 font-semibold">
                            <Clock3 class="h-4 w-4 text-emerald-600" />
                            {formatDate(item.tanggal)} · {item.jam} · Unit {item.unit}
                        </p>
                        {#if item.segment_id}
                            <div
                                class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/50"
                            >
                                <p
                                    class="font-bold text-emerald-900 dark:text-emerald-100"
                                >
                                    {item.segment_name}
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-emerald-700 dark:text-emerald-300"
                                >
                                    Pickup {item.pickup_time ||
                                        item.segment_pickup_times.join(', ') ||
                                        '-'}
                                    Â· {formatRupiah(item.price)}
                                </p>
                                <p
                                    class="mt-1 text-xs text-emerald-800/80 dark:text-emerald-200/80"
                                >
                                    Rute induk: {item.route_name}
                                </p>
                            </div>
                        {/if}
                        <p class="font-bold">
                            {item.route_name}
                            <span class="font-normal text-slate-500"
                                >· {item.pool_name}</span
                            >
                        </p>
                        <p class="font-semibold">Kursi: {seatSummary(item)}</p>
                        <p class="flex items-start gap-2 text-slate-600">
                            <MapPin
                                class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                            />
                            {item.pickup_address}
                        </p>
                        <p class="text-slate-600">
                            {item.phone} · {item.payment_method}
                        </p>
                        {#if item.notes}<p
                                class="rounded-xl bg-slate-50 p-3 text-slate-600"
                            >
                                {item.notes}
                            </p>{/if}
                    </div>
                    <div
                        class="mt-5 grid grid-cols-2 gap-2 sm:flex sm:flex-row"
                    >
                        <button
                            type="button"
                            onclick={() => approve(item)}
                            class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-800 active:scale-[0.98]"
                            ><Check class="h-4 w-4" /> Approve</button
                        ><button
                            type="button"
                            onclick={() => reject(item)}
                            class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 text-sm font-black text-red-700 shadow-sm transition hover:bg-red-100 active:scale-[0.98] dark:border-red-900/80 dark:bg-red-950/30 dark:text-red-200 dark:hover:bg-red-950/50"
                            ><X class="h-4 w-4" /> Tolak</button
                        ><a
                            href={whatsapp(item.phone, item.request_code)}
                            target="_blank"
                            rel="noreferrer"
                            aria-label={contactLabel(item)}
                            class="col-span-2 flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 active:scale-[0.98] sm:col-span-1 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                            ><MessageCircle class="h-4 w-4" /> WA</a
                        >
                    </div>
                </article>{/each}
        </div>{/if}
</div>
