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
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogTitle,
    } from '@/components/ui/dialog';
    import { externalLink } from '@/lib/webview';

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
    let pending = $state<Record<number, boolean>>({});
    let dialogOpen = $state(false);
    let selected = $state<RequestRow | null>(null);
    let decision = $state<'approve' | 'reject'>('approve');
    let reason = $state('');
    let reasonError = $state('');
    let actionError = $state('');
    let reasonInput = $state<HTMLTextAreaElement | null>(null);
    const hasPending = $derived(Object.values(pending).some(Boolean));
    const dialogPending = $derived(!!selected && !!pending[selected.id]);

    async function readPayload(response: Response, fallback: string) {
        const payload = await response.json().catch(() => null);

        if (!response.ok || !payload?.success) {
            throw new Error(
                typeof payload?.error === 'string'
                    ? payload.error
                    : typeof payload?.message === 'string'
                      ? payload.message
                      : fallback,
            );
        }

        return payload;
    }

    function csrfToken(): string {
        const token =
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '';

        return decodeURIComponent(token);
    }

    async function loadRequests() {
        if (loading || hasPending) {
            return;
        }

        loading = true;
        error = '';

        try {
            const response = await fetch(
                '/api/admin/public-booking-requests?status=pending',
                { headers: { Accept: 'application/json' } },
            );
            const payload = await readPayload(
                response,
                'Inbox gagal dimuat. Coba muat ulang.',
            );

            if (!Array.isArray(payload.requests)) {
                throw new Error('Data inbox tidak valid. Coba muat ulang.');
            }

            requests = payload.requests;
        } catch (cause) {
            error =
                cause instanceof TypeError
                    ? 'Koneksi bermasalah. Coba muat ulang inbox.'
                    : cause instanceof Error
                      ? cause.message
                      : 'Inbox gagal dimuat.';
        } finally {
            loading = false;
        }
    }

    function openDecision(item: RequestRow, next: 'approve' | 'reject') {
        if (loading || pending[item.id] || dialogPending) {
            return;
        }

        selected = item;
        decision = next;
        reason = '';
        reasonError = '';
        actionError = '';
        dialogOpen = true;
    }

    async function submitDecision(event: SubmitEvent) {
        event.preventDefault();
        const item = selected;

        if (!item || loading || pending[item.id]) {
            return;
        }

        const trimmedReason = reason.trim();
        reasonError = '';

        if (
            decision === 'reject' &&
            (!trimmedReason || trimmedReason.length > 1000)
        ) {
            reasonError = !trimmedReason
                ? 'Alasan penolakan wajib diisi.'
                : 'Alasan penolakan maksimal 1.000 karakter.';
            reasonInput?.focus();

            return;
        }

        pending[item.id] = true;
        actionError = '';
        message = '';
        let completed = false;

        try {
            const response = await fetch(
                `/api/admin/public-booking-requests/${item.id}/${decision}`,
                {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify(
                        decision === 'reject' ? { reason: trimmedReason } : {},
                    ),
                },
            );
            const payload = await readPayload(
                response,
                'Aksi belum dapat dikonfirmasi. Tutup dialog dan muat ulang inbox untuk memeriksa status.',
            );
            message =
                payload.result?.status === 'rejected' &&
                payload.result?.rejection_reason
                    ? `Approval ditolak: ${payload.result.rejection_reason}`
                    : decision === 'approve'
                      ? 'Booking resmi dibuat.'
                      : 'Request ditolak.';
            requests = requests.filter((row) => row.id !== item.id);
            completed = true;
        } catch (cause) {
            actionError =
                cause instanceof TypeError
                    ? 'Koneksi bermasalah. Tutup dialog dan muat ulang inbox untuk memeriksa status sebelum mencoba lagi.'
                    : cause instanceof Error
                      ? cause.message
                      : 'Aksi gagal. Coba muat ulang inbox.';
        } finally {
            delete pending[item.id];
        }

        if (completed) {
            dialogOpen = false;
            await loadRequests();
        }
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
            disabled={loading || hasPending}
            class="flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 active:scale-[0.98] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
            >{#if loading}<RefreshCw
                    class="h-4 w-4 animate-spin"
                />{:else}<RefreshCw class="h-4 w-4" />{/if} Refresh</button
        >
    </div>
    {#if message}<div
            role="status"
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200"
        >
            {message}
        </div>{/if}
    {#if error}<div
            role="alert"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-800 dark:bg-red-950/50 dark:text-red-200"
        >
            {error}
            <button
                type="button"
                onclick={loadRequests}
                disabled={loading || hasPending}
                class="mt-2 flex min-h-11 items-center rounded-lg border border-current px-3 font-bold disabled:opacity-50"
                >{loading ? 'Memuat...' : 'Coba muat ulang'}</button
            >
        </div>{/if}
    {#if loading && requests.length === 0}<div
            class="py-14 text-center text-sm text-muted-foreground"
        >
            Memuat request...
        </div>{:else if requests.length === 0 && !error}<div
            class="rounded-3xl border border-dashed border-slate-300 px-5 py-16 text-center"
        >
            <Check class="mx-auto h-8 w-8 text-emerald-600" />
            <p class="mt-3 font-black">Inbox bersih</p>
            <p class="mt-1 text-sm text-muted-foreground">
                Belum ada request booking publik yang menunggu.
            </p>
        </div>{:else}<div class="grid gap-4 lg:grid-cols-2">
            {#each requests as item (item.id)}<article
                    aria-busy={!!pending[item.id]}
                    class="rounded-3xl border border-slate-200 bg-white p-5 text-slate-900 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-black tracking-widest text-emerald-700 dark:text-emerald-300"
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
                            <span class="font-normal text-muted-foreground"
                                >· {item.pool_name}</span
                            >
                        </p>
                        <p class="font-semibold">Kursi: {seatSummary(item)}</p>
                        <p
                            class="flex items-start gap-2 text-slate-600 dark:text-slate-300"
                        >
                            <MapPin
                                class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                            />
                            {item.pickup_address}
                        </p>
                        <p class="text-slate-600 dark:text-slate-300">
                            {item.phone} · {item.payment_method}
                        </p>
                        {#if item.notes}<p
                                class="rounded-xl bg-slate-50 p-3 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {item.notes}
                            </p>{/if}
                    </div>
                    <div
                        class="mt-5 grid grid-cols-2 gap-2 sm:flex sm:flex-row"
                    >
                        <button
                            type="button"
                            onclick={() => openDecision(item, 'approve')}
                            disabled={loading ||
                                !!pending[item.id] ||
                                dialogPending}
                            class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-700 px-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                            ><Check class="h-4 w-4" />
                            {pending[item.id]
                                ? 'Memproses...'
                                : 'Approve'}</button
                        ><button
                            type="button"
                            onclick={() => openDecision(item, 'reject')}
                            disabled={loading ||
                                !!pending[item.id] ||
                                dialogPending}
                            class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 text-sm font-black text-red-700 shadow-sm transition hover:bg-red-100 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900/80 dark:bg-red-950/30 dark:text-red-200 dark:hover:bg-red-950/50"
                            ><X class="h-4 w-4" /> Tolak</button
                        ><a
                            use:externalLink
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

<Dialog
    bind:open={dialogOpen}
    onOpenChange={(open) => {
        if (!open && dialogPending) {
            dialogOpen = true;
        }
    }}
>
    <DialogContent
        class="max-h-[90dvh] w-[calc(100%-2rem)] overflow-y-auto text-foreground"
    >
        <form
            onsubmit={submitDecision}
            novalidate
            class="space-y-4"
            aria-busy={dialogPending}
        >
            <DialogTitle
                >{decision === 'approve'
                    ? 'Setujui request booking?'
                    : 'Tolak request booking?'}</DialogTitle
            >
            <DialogDescription>
                {selected?.request_code} — {selected?.contact_name}.
                {decision === 'approve'
                    ? 'Persetujuan akan membuat booking resmi.'
                    : 'Tuliskan alasan penolakan request ini.'}
            </DialogDescription>
            {#if decision === 'reject'}
                <div class="space-y-2">
                    <label
                        for="booking-rejection-reason"
                        class="block text-sm font-semibold"
                        >Alasan penolakan <span class="text-muted-foreground"
                            >(wajib)</span
                        ></label
                    >
                    <textarea
                        id="booking-rejection-reason"
                        bind:this={reasonInput}
                        bind:value={reason}
                        required
                        maxlength={1000}
                        rows={4}
                        disabled={dialogPending}
                        aria-invalid={!!reasonError}
                        aria-describedby={reasonError
                            ? 'booking-rejection-help booking-rejection-error'
                            : 'booking-rejection-help'}
                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-foreground disabled:opacity-50"
                    ></textarea>
                    <p
                        id="booking-rejection-help"
                        class="text-xs text-muted-foreground"
                    >
                        Maksimal 1.000 karakter.
                    </p>
                    {#if reasonError}<p
                            id="booking-rejection-error"
                            role="alert"
                            class="text-sm text-red-700 dark:text-red-300"
                        >
                            {reasonError}
                        </p>{/if}
                </div>
            {/if}
            {#if actionError}<p
                    role="alert"
                    class="text-sm text-red-700 dark:text-red-300"
                >
                    {actionError}
                </p>{/if}
            <div class="flex flex-wrap justify-end gap-2">
                <button
                    type="button"
                    disabled={dialogPending}
                    onclick={() => (dialogOpen = false)}
                    class="min-h-11 rounded-xl border border-input px-4 text-sm font-semibold disabled:opacity-50"
                    >Batal</button
                >
                <button
                    type="submit"
                    disabled={dialogPending || loading}
                    class="min-h-11 rounded-xl px-4 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50 {decision ===
                    'approve'
                        ? 'bg-emerald-700 hover:bg-emerald-800'
                        : 'bg-red-700 hover:bg-red-800'}"
                    >{dialogPending
                        ? 'Memproses...'
                        : decision === 'approve'
                          ? 'Ya, setujui'
                          : 'Tolak request'}</button
                >
            </div>
        </form>
    </DialogContent>
</Dialog>
