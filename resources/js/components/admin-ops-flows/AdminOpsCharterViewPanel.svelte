<script lang="ts">
    import { Printer } from 'lucide-svelte';
    import { Button } from '@/components/ui/button';

    type Charter = {
        id: number;
        name: string;
        company_name: string | null;
        phone: string | null;
        start_date: string;
        end_date: string;
        departure_time: string | null;
        pickup_point: string | null;
        drop_point: string | null;
        unit_nopol: string | null;
        unit_category: string | null;
        armada_nopol: string | null;
        driver_name: string | null;
        price: number;
        layanan: string | null;
        bop_price: number;
        bop_status: string | null;
        down_payment: number;
        payment_status: string | null;
        status: string | null;
    };

    let {
        charterViewBusy = false,
        charterViewData = null,
        defaultCharterService = 'DROPOFF',
        closeCharterView,
        charterStatusClass,
        charterStatusLabel,
        charterPaymentClass,
        openCharterInvoice,
        charterCanMarkDone,
        markCharterAsDone,
        charterCanEdit,
        openCharterEditor,
        charterMarkDoneHint,
        formatCurrencyId,
        charterPaymentRemaining,
    }: {
        charterViewBusy?: boolean;
        charterViewData?: Charter | null;
        defaultCharterService?: string;
        closeCharterView: () => void;
        charterStatusClass: (status: string | null | undefined) => string;
        charterStatusLabel: (status: string | null | undefined) => string;
        charterPaymentClass: (status: string | null | undefined) => string;
        openCharterInvoice: (id: number) => void;
        charterCanMarkDone: (row: Charter | null | undefined) => boolean;
        markCharterAsDone: (row: Charter) => void | Promise<void>;
        charterCanEdit: (row: Charter | null | undefined) => boolean;
        openCharterEditor: (row: Charter) => void | Promise<void>;
        charterMarkDoneHint: (row: Charter | null | undefined) => string;
        formatCurrencyId: (value: number | string | null | undefined) => string;
        charterPaymentRemaining: (row: Charter | null | undefined) => number;
    } = $props();
</script>

<div class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2">
    <p class="text-xs font-medium text-muted-foreground">Halaman Detail Carter</p>
    <Button type="button" size="sm" variant="outline" class="h-8 rounded-lg text-xs" onclick={closeCharterView}>
        Kembali ke Data Carter
    </Button>
</div>

{#if charterViewBusy}
    <div class="space-y-2 rounded-2xl border border-border/70 bg-card p-4 shadow-sm">
        <div class="h-7 w-full animate-pulse rounded bg-muted"></div>
        <div class="h-7 w-full animate-pulse rounded bg-muted"></div>
        <div class="h-24 w-full animate-pulse rounded bg-muted"></div>
    </div>
{:else if charterViewData}
    <div class="overflow-hidden rounded-[28px] border border-border/70 bg-card shadow-sm">
        <div class="relative border-b border-border/70 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.18),_transparent_42%),linear-gradient(135deg,_rgba(15,23,42,0.98),_rgba(8,47,73,0.92))] px-5 py-5 text-white">
            <div class="absolute inset-y-0 right-0 hidden w-64 bg-[radial-gradient(circle_at_center,_rgba(255,255,255,0.14),_transparent_62%)] lg:block"></div>
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class={`rounded-full px-3 py-1 text-[11px] font-semibold ${charterStatusClass(charterViewData.status)}`}>
                            {charterStatusLabel(charterViewData.status)}
                        </span>
                        <span class={`rounded-full px-3 py-1 text-[11px] font-semibold ${charterPaymentClass(charterViewData.payment_status)}`}>
                            {charterViewData.payment_status ?? '-'}
                        </span>
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold text-slate-100">
                            {charterViewData.layanan ?? defaultCharterService}
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.18em] text-cyan-100/80">Detail Carter</p>
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight">{charterViewData.name}</h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-200">
                            {[charterViewData.company_name, charterViewData.phone].filter(Boolean).join(' / ') || 'Kontak charter belum tersedia'}
                        </p>
                    </div>
                </div>

                <div class="relative z-10 flex flex-wrap gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="border-white/20 bg-white/10 text-white hover:bg-white/15 hover:text-white"
                        onclick={() => {
                            if (charterViewData) {
                                openCharterInvoice(charterViewData.id);
                            }
                        }}
                    >
                        <Printer class="mr-2 h-4 w-4" />
                        Cetak Invoice
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="border-white/20 bg-white/10 text-white hover:bg-white/15 hover:text-white"
                        disabled={!charterCanMarkDone(charterViewData)}
                        onclick={() => {
                            if (charterViewData) {
                                void markCharterAsDone(charterViewData);
                            }
                        }}
                    >
                        Selesaikan Perjalanan
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="border-white/20 bg-white/10 text-white hover:bg-white/15 hover:text-white"
                        disabled={!charterCanEdit(charterViewData)}
                        onclick={() => {
                            if (charterViewData) {
                                void openCharterEditor(charterViewData);
                            }
                        }}
                    >
                        {charterCanEdit(charterViewData) ? 'Edit Carter' : 'Charter Selesai'}
                    </Button>
                </div>
            </div>
            <p class="relative z-10 mt-3 max-w-2xl text-xs leading-relaxed text-slate-200/85">
                {charterMarkDoneHint(charterViewData)}
            </p>
        </div>

        <div class="space-y-5 p-5">
            <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-4">
                <div class="rounded-2xl border border-border/70 bg-muted/20 p-4">
                    <p class="text-[11px] uppercase tracking-[0.08em] text-muted-foreground">Jadwal</p>
                    <p class="mt-2 text-base font-semibold text-foreground">{charterViewData.start_date}</p>
                    <p class="text-sm text-muted-foreground">{charterViewData.end_date}</p>
                    <p class="mt-2 text-sm font-medium text-foreground">{charterViewData.departure_time ? String(charterViewData.departure_time).slice(0, 5) : '--:--'}</p>
                </div>
                <div class="rounded-2xl border border-border/70 bg-muted/20 p-4">
                    <p class="text-[11px] uppercase tracking-[0.08em] text-muted-foreground">Armada & Driver</p>
                    <p class="mt-2 text-base font-semibold text-foreground">{charterViewData.driver_name ?? '-'}</p>
                    <p class="mt-1 text-sm text-muted-foreground">{charterViewData.unit_category ?? 'Kategori armada belum dipilih'}</p>
                    <p class="text-sm text-muted-foreground">{charterViewData.armada_nopol ?? charterViewData.unit_nopol ?? '-'}</p>
                </div>
                <div class="rounded-2xl border border-border/70 bg-muted/20 p-4">
                    <p class="text-[11px] uppercase tracking-[0.08em] text-muted-foreground">Total Biaya</p>
                    <p class="mt-2 text-base font-semibold text-foreground">{formatCurrencyId(charterViewData.price ?? 0)}</p>
                    <p class="mt-1 text-sm text-muted-foreground">BOP {formatCurrencyId(charterViewData.bop_price ?? 0)}</p>
                    <p class="text-sm text-muted-foreground">Sisa {formatCurrencyId(charterPaymentRemaining(charterViewData))}</p>
                </div>
                <div class="rounded-2xl border border-border/70 bg-muted/20 p-4">
                    <p class="text-[11px] uppercase tracking-[0.08em] text-muted-foreground">Pembayaran</p>
                    <p class="mt-2 text-base font-semibold text-foreground">{charterViewData.payment_status ?? '-'}</p>
                    <p class="mt-1 text-sm text-muted-foreground">DP {formatCurrencyId(charterViewData.down_payment ?? 0)}</p>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[1.35fr_0.9fr]">
                <div class="rounded-[24px] border border-border/70 bg-background p-4">
                    <div class="flex items-center justify-between gap-3 border-b border-border/70 pb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">Rute Perjalanan</p>
                            <h3 class="mt-1 text-lg font-semibold tracking-tight text-foreground">Pickup ke titik antar</h3>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/70 p-4">
                            <p class="text-[11px] uppercase tracking-[0.08em] text-emerald-700">Titik Jemput</p>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-emerald-950">{charterViewData.pickup_point ?? '-'}</p>
                        </div>
                        <div class="rounded-2xl border border-cyan-200/70 bg-cyan-50/70 p-4">
                            <p class="text-[11px] uppercase tracking-[0.08em] text-cyan-700">Titik Antar</p>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-cyan-950">{charterViewData.drop_point ?? '-'}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[24px] border border-border/70 bg-muted/15 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">Snapshot Invoice</p>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-muted-foreground">Nama Charter</span>
                            <span class="text-right font-semibold text-foreground">{charterViewData.name}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-muted-foreground">Layanan</span>
                            <span class="text-right font-semibold text-foreground">{charterViewData.layanan ?? '-'}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-muted-foreground">Armada</span>
                            <span class="text-right font-semibold text-foreground">{charterViewData.armada_nopol ?? charterViewData.unit_nopol ?? '-'}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-muted-foreground">Status Bayar</span>
                            <span class="text-right font-semibold text-foreground">{charterViewData.payment_status ?? '-'}</span>
                        </div>
                    </div>
                    <div class="mt-4 rounded-2xl border border-border/70 bg-card p-4">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-muted-foreground">Tagihan Saat Ini</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-foreground">{formatCurrencyId(charterPaymentRemaining(charterViewData))}</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Total {formatCurrencyId(charterViewData.price ?? 0)} | DP {formatCurrencyId(charterViewData.down_payment ?? 0)}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
{:else}
    <p class="text-sm text-muted-foreground">Data charter tidak ditemukan.</p>
{/if}
