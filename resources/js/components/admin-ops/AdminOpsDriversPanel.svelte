<script lang="ts">
    import {
        ChevronLeft,
        ChevronRight,
        Download,
        Eye,
        MoreHorizontal,
        Pencil,
        Trash2,
    } from 'lucide-svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import DataTable from '@/components/terminal/DataTable.svelte';
    import TerminalFilter from '@/components/terminal/TerminalFilter.svelte';
    import RevenueChartTable from './RevenueChartTable.svelte';

    type Pagination = {
        page: number;
        per_page: number;
        total: number;
        last_page: number;
    };

    type DriverRow = {
        id: number;
        nama: string;
        phone: string | null;
        nopol: string | null;
        category: string | null;
        pool_name?: string | null;
        departure_count?: number;
        charter_revenue: number;
        departure_revenue: number;
        luggage_revenue: number;
        revenue: number;
        charter_bop: number;
        departure_bop: number;
        bop: number;
        fixed_cost: number;
        target_revenue_bulanan: number;
    };
    type DriverDetailRow = DriverRow & {
        unit_id?: number | null;
        armada_id?: number | null;
        pool_id?: number | null;
        pool_name?: string | null;
        monthly?: {
            summary: {
                period_label: string;
                charter_count: number;
                departure_count: number;
                luggage_count: number;
                charter_revenue: number;
                departure_revenue: number;
                luggage_revenue: number;
                total_revenue: number;
                charter_bop: number;
                departure_bop: number;
                total_bop: number;
                gross: number;
                fixed_cost: number;
                net_margin: number;
                target_revenue: number;
                achievement: number;
                status: string;
            };
            bookings: Array<{ id: number; tanggal: string; jam: string; departure_date: string; rute: string; unit: number; seat: string; name: string; phone: string; pickup_point: string; pembayaran: string; status: string; total: number; revenue: number; bop: number }>;
            charters: Array<{ id: number; start_date: string; end_date: string; departure_date: string; departure_time: string; name: string; phone: string; pickup_point: string; drop_point: string; layanan: string; payment_status: string; bop_status: string; status: string; armada_nopol: string; driver_name: string; total: number; revenue: number; bop: number }>;
            bagasi: Array<{ id: number; tanggal: string; created_at: string; departure_date: string; kode_resi: string; sender_name: string; receiver_name: string; quantity: number; payment_status: string; status: string; service_name: string; total: number; revenue: number; bop: number; departure_time: string; departure_unit: number }>;
        } | null;
    };

    let {
        activeMode = 'data',
        driverDetail = null,
        drivers = [],
        driverMeta = {
            page: 1,
            per_page: 20,
            total: 0,
            last_page: 1,
        },
        driverSearch = $bindable(''),
        driverPeriod = $bindable(
            `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}`,
        ),
        formatCurrency,
        driverGrossMargin,
        driverNetMargin,
        driverAchievement,
        driverStatus,
        loadDrivers,
        openDriverView,
        driverDetailLoading = false,
        editDriver,
        removeDriver,
        goBackToData,
        canManage = true,
        canExport = false,
    }: {
        activeMode?: 'data' | 'form' | 'view' | 'layout';
        driverDetail?: DriverDetailRow | null;
        drivers?: DriverRow[];
        driverMeta?: Pagination;
        driverSearch?: string;
        driverPeriod?: string;
        formatCurrency: (value: number) => string;
        driverGrossMargin: (row: DriverRow) => number;
        driverNetMargin: (row: DriverRow) => number;
        driverAchievement: (row: DriverRow) => number;
        driverStatus: (row: DriverRow) => string;
        loadDrivers: (page?: number) => void | Promise<void>;
        openDriverView: (row: DriverRow) => void;
        driverDetailLoading?: boolean;
        editDriver: (row: DriverRow) => void;
        removeDriver: (id: number) => void | Promise<void>;
        goBackToData: () => void;
        canManage?: boolean;
        canExport?: boolean;
    } = $props();

    const periodOptions = (() => {
        const formatter = new Intl.DateTimeFormat('id-ID', {
            month: 'long',
            year: 'numeric',
        });
        const now = new Date();
        const items: Array<{ value: string; label: string }> = [];

        for (let index = 0; index < 12; index += 1) {
            const date = new Date(now.getFullYear(), now.getMonth() - index, 1);
            const value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

            items.push({
                value,
                label: formatter.format(date),
            });
        }

        return items;
    })();

    const selectedPeriodLabel = () =>
        periodOptions.find((option) => option.value === driverPeriod)?.label ??
        'Periode sekarang';

    const formatDateLabel = (value: string) => {
        const raw = String(value ?? '').trim();
        if (raw === '') return '-';
        const date = new Date(`${raw}T00:00:00`);
        if (Number.isNaN(date.getTime())) return raw;

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(date);
    };

    const exportHref = $derived.by(() => {
        const params = new URLSearchParams();
        const query = driverSearch.trim();

        if (query !== '') {
            params.set('q', query);
        }

        if (driverPeriod.trim() !== '') {
            params.set('period', driverPeriod.trim());
        }

        const suffix = params.toString();

        return `/api/admin/drivers/export${suffix !== '' ? `?${suffix}` : ''}`;
    });

    const achievementTone = (achievement: number) => {
        if (achievement < 60) {
            return {
                bar: 'bg-rose-500',
                label: 'Performa Rendah',
                text: 'text-rose-700 dark:text-rose-300',
            };
        }

        if (achievement <= 85) {
            return {
                bar: 'bg-amber-400',
                label: 'Aman/Menuju Target',
                text: 'text-amber-700 dark:text-amber-300',
            };
        }

        return {
            bar: 'bg-emerald-500',
            label: 'Sesuai Target / Outstanding',
            text: 'text-emerald-700 dark:text-emerald-300',
        };
    };

    const goToPage = async (page: number) => {
        if (page < 1 || page > Number(driverMeta.last_page || 1)) {
            return;
        }

        await loadDrivers(page);
    };

    const pageSummary = () => {
        const total = Number(driverMeta.total || 0);
        const perPage = Number(driverMeta.per_page || 0);
        const page = Number(driverMeta.page || 1);
        const from = total === 0 ? 0 : (page - 1) * perPage + 1;
        const to = total === 0 ? 0 : Math.min(total, page * perPage);

        return `${from}-${to} dari ${total}`;
    };
    const driverStatusTone = (status: string) => {
        const normalized = String(status ?? '')
            .trim()
            .toLowerCase();

        if (
            normalized === 'tercapai' ||
            normalized === 'aktif' ||
            normalized === 'done' ||
            normalized === 'lunas' ||
            normalized === 'paid'
        ) {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300';
        }

        if (
            normalized === 'aman/menuju target' ||
            normalized === 'pending' ||
            normalized === 'proses' ||
            normalized === 'partial'
        ) {
            return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300';
        }

        return 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/35 dark:text-rose-300';
    };
    const driverDetailCards = (driver: DriverDetailRow) => {
        const gross = driverGrossMargin(driver);
        const net = driverNetMargin(driver);
        const achievement = driverAchievement(driver);
        const status = driverStatus(driver);

        return [
            {
                key: 'revenue',
                label: 'Total Revenue',
                valueText: formatCurrency(Number(driver.revenue || 0)),
                note: 'Gabungan charter, keberangkatan, dan bagasi',
                tone: 'text-emerald-700 dark:text-emerald-300',
            },
            {
                key: 'bop',
                label: 'Total BOP',
                valueText: formatCurrency(Number(driver.bop || 0)),
                note: 'Biaya operasional driver',
                tone: 'text-amber-700 dark:text-amber-300',
            },
            {
                key: 'gross',
                label: 'Gross Margin',
                valueText: formatCurrency(gross),
                note: 'Revenue - BOP',
                tone:
                    gross < 0
                        ? 'text-rose-700 dark:text-rose-300'
                        : 'text-foreground',
            },
            {
                key: 'fixed-cost',
                label: 'Fixed Cost',
                valueText: formatCurrency(Number(driver.fixed_cost || 0)),
                note: 'Biaya tetap driver',
                tone: 'text-slate-700 dark:text-slate-300',
            },
            {
                key: 'net',
                label: 'Net Margin',
                valueText: formatCurrency(net),
                note: 'Gross - Fixed Cost',
                tone:
                    net < 0
                        ? 'text-rose-700 dark:text-rose-300'
                        : 'text-foreground',
            },
            {
                key: 'target',
                label: 'Target Bulanan',
                valueText: formatCurrency(
                    Number(driver.target_revenue_bulanan || 0),
                ),
                note: 'Target yang digunakan untuk achievement',
                tone: 'text-slate-700 dark:text-slate-300',
            },
            {
                key: 'achievement',
                label: 'Achievement',
                valueText: `${achievement.toFixed(1)}%`,
                note: 'Pencapaian terhadap target',
                tone:
                    achievement >= 100
                        ? 'text-emerald-700 dark:text-emerald-300'
                        : achievement >= 80
                          ? 'text-amber-700 dark:text-amber-300'
                          : 'text-rose-700 dark:text-rose-300',
            },
            {
                key: 'rit',
                label: 'Trip / Rit',
                valueText: String(Number(driver.departure_count || 0)),
                note: 'Jumlah keberangkatan yang tercatat',
                tone: 'text-slate-700 dark:text-slate-300',
            },
            {
                key: 'status',
                label: 'Status',
                valueText: status,
                note: 'Status performa driver',
                tone: driverStatusTone(status),
            },
        ];
    };
    const driverTableColumns = [
        { key: 'driver', label: 'Driver', width: 'w-[180px]', sticky: 'left' },
        { key: 'category', label: 'Kategori', width: 'w-[150px]' },
        {
            key: 'target',
            label: 'Target',
            align: 'right',
            numeric: true,
            width: 'w-[120px]',
        },
        {
            key: 'revenue',
            label: 'Revenue',
            align: 'right',
            numeric: true,
            width: 'w-[120px]',
        },
        {
            key: 'bop',
            label: 'BOP',
            align: 'right',
            numeric: true,
            width: 'w-[110px]',
        },
        {
            key: 'gross',
            label: 'Gross',
            align: 'right',
            numeric: true,
            width: 'w-[120px]',
        },
        {
            key: 'fixed_cost',
            label: 'Fixed Cost',
            align: 'right',
            numeric: true,
            width: 'w-[120px]',
        },
        {
            key: 'net',
            label: 'Net Margin',
            align: 'right',
            numeric: true,
            width: 'w-[120px]',
        },
        {
            key: 'achievement',
            label: 'Achievement',
            align: 'right',
            numeric: true,
            width: 'w-[100px]',
        },
        { key: 'status', label: 'Status', align: 'center', width: 'w-[100px]' },
    ];
</script>

{#if activeMode === 'view'}
    {#if driverDetail}
        {@const driver = driverDetail}
        {@const gross = driverGrossMargin(driver)}
        {@const net = driverNetMargin(driver)}
        {@const achievement = driverAchievement(driver)}
        {@const status = driverStatus(driver)}
        {@const detailCards = driverDetailCards(driver)}
        {#if driverDetailLoading}
            <div class="animate-pulse space-y-4 rounded-xl border border-border/70 p-4">
                <div class="h-36 rounded-xl bg-muted"></div>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    {#each Array(8) as _, index (index)}<div class="h-20 rounded-lg bg-muted"></div>{/each}
                </div>
            </div>
        {:else}
        {@const monthlyDetail = driver.monthly ?? null}
        {@const monthly = monthlyDetail?.summary ?? null}
        <div class="space-y-5">
            <section class="relative overflow-hidden rounded-xl border border-slate-800 bg-slate-950 px-4 py-5 text-white shadow-md sm:px-6">
                <div class="pointer-events-none absolute -right-12 -top-16 size-56 rounded-full border-[28px] border-cyan-400/10"></div>
                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge variant="secondary" class="rounded-full border border-white/10 bg-white/10 px-2.5 py-1 text-[10px] uppercase tracking-[0.14em] text-white hover:bg-white/10">{driver.pool_name || 'Semua Pool'}</Badge>
                            <Badge variant="outline" class="rounded-full border border-cyan-300/30 bg-cyan-300/10 px-2.5 py-1 text-[10px] uppercase tracking-[0.14em] text-cyan-100">{driver.category || 'Driver'}</Badge>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-cyan-200/70">Detail Driver</p>
                            <h2 class="mt-1 break-words text-2xl font-bold tracking-tight sm:text-3xl">{driver.nama}</h2>
                            <p class="mt-1 text-sm text-slate-300">{driver.phone || 'Kontak belum dicatat'}{driver.nopol ? ` · ${driver.nopol}` : ''}</p>
                        </div>
                        <div class="grid gap-3 text-xs text-slate-300 sm:grid-cols-3">
                            <div><p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Kategori</p><p class="mt-1 font-medium text-slate-100">{driver.category || '-'}</p></div>
                            <div><p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Trip / Rit</p><p class="mt-1 font-medium text-slate-100">{Number(driver.departure_count || 0)} keberangkatan</p></div>
                            <div><p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Status</p><p class="mt-1 font-medium text-slate-100">{status}</p></div>
                        </div>
                    </div>
                    <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
                        {#if canManage}<Button type="button" variant="outline" class="h-10 border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white" onclick={() => editDriver(driver)}>Edit</Button>{/if}
                        <Button type="button" variant="outline" class="h-10 border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white" onclick={goBackToData}>Kembali ke daftar</Button>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-border/70 bg-background shadow-sm">
                <div class="flex flex-col gap-3 border-b border-border/70 bg-muted/20 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div><p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">Ringkasan bulanan</p><h3 class="mt-1 text-base font-semibold tracking-tight">{monthly?.period_label ?? selectedPeriodLabel()}</h3></div>
                    <div class="flex flex-wrap items-center gap-2"><Badge variant="secondary" class="rounded-full px-2.5 py-1 text-xs font-bold tabular-nums">{(monthly?.achievement ?? achievement).toFixed(1)}% tercapai</Badge><Badge variant="outline" class={`rounded-full px-2.5 py-1 text-[10px] uppercase tracking-wide ${driverStatusTone(monthly?.status ?? status)}`}>{monthly?.status ?? status}</Badge></div>
                </div>
                <div class="grid gap-px bg-border/60 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    {#each detailCards as card (card.key)}<article class="min-w-0 bg-background px-4 py-3.5 sm:px-5"><p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-muted-foreground">{card.label}</p><p class={`mt-1.5 break-words text-base font-bold tabular-nums ${card.tone}`}>{card.valueText}</p><p class="mt-1 text-[11px] leading-4 text-muted-foreground">{card.note}</p></article>{/each}
                </div>
            </section>

            <div class="grid gap-4 2xl:grid-cols-3">
                <section class="min-w-0 overflow-hidden rounded-xl border border-border/70 bg-background shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-border/70 px-4 py-3.5"><div><p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted-foreground">Manifest keberangkatan</p><h4 class="mt-1 text-sm font-semibold">{monthly?.departure_count ?? 0} trip</h4></div><Badge variant="secondary" class="rounded-full px-2 py-1 text-[10px]">{formatCurrency(monthly?.departure_revenue ?? 0)}</Badge></div>
                    <div class="max-h-[26rem] overflow-auto"><table class="w-full min-w-[680px] text-left text-xs"><thead class="sticky top-0 z-10 bg-muted/60 text-[10px] uppercase tracking-wide text-muted-foreground backdrop-blur"><tr><th class="px-3 py-2">Tanggal</th><th class="px-3 py-2">Rute</th><th class="px-3 py-2">Slot</th><th class="px-3 py-2">Pelanggan</th><th class="px-3 py-2 text-right">Revenue</th></tr></thead><tbody class="divide-y divide-border/60">{#if (monthlyDetail?.bookings ?? []).length === 0}<tr><td class="px-4 py-8 text-center text-muted-foreground" colspan="5">Tidak ada manifest pada periode ini.</td></tr>{:else}{#each monthlyDetail?.bookings ?? [] as row (row.id)}<tr class="align-top hover:bg-muted/30"><td class="whitespace-nowrap px-3 py-2.5 text-muted-foreground">{formatDateLabel(row.departure_date || row.tanggal)}<div class="text-[10px]">{row.jam}</div></td><td class="px-3 py-2.5 font-medium">{row.rute}</td><td class="px-3 py-2.5 tabular-nums">{row.unit}</td><td class="px-3 py-2.5"><div class="font-medium">{row.name}</div><div class="text-[10px] text-muted-foreground">{row.seat} · {row.pickup_point}</div></td><td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold tabular-nums">{formatCurrency(row.revenue ?? row.total)}</td></tr>{/each}{/if}</tbody></table></div>
                </section>
                <section class="min-w-0 overflow-hidden rounded-xl border border-border/70 bg-background shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-border/70 px-4 py-3.5"><div><p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted-foreground">Carter</p><h4 class="mt-1 text-sm font-semibold">{monthly?.charter_count ?? 0} data</h4></div><Badge variant="secondary" class="rounded-full px-2 py-1 text-[10px]">{formatCurrency(monthly?.charter_revenue ?? 0)}</Badge></div>
                    <div class="max-h-[26rem] overflow-auto"><table class="w-full min-w-[760px] text-left text-xs"><thead class="sticky top-0 z-10 bg-muted/60 text-[10px] uppercase tracking-wide text-muted-foreground backdrop-blur"><tr><th class="px-3 py-2">Tanggal</th><th class="px-3 py-2">Rute</th><th class="px-3 py-2">Penyewa</th><th class="px-3 py-2">Armada</th><th class="px-3 py-2 text-right">Revenue</th><th class="px-3 py-2 text-right">BOP</th></tr></thead><tbody class="divide-y divide-border/60">{#if (monthlyDetail?.charters ?? []).length === 0}<tr><td class="px-4 py-8 text-center text-muted-foreground" colspan="6">Tidak ada carter pada periode ini.</td></tr>{:else}{#each monthlyDetail?.charters ?? [] as row (row.id)}<tr class="align-top hover:bg-muted/30"><td class="whitespace-nowrap px-3 py-2.5 text-muted-foreground">{formatDateLabel(row.departure_date || row.start_date)}<div class="text-[10px]">{row.departure_time || '-'}</div></td><td class="px-3 py-2.5"><div class="font-medium">{row.pickup_point}</div><div class="text-[10px] text-muted-foreground">{row.drop_point}</div></td><td class="px-3 py-2.5"><div class="font-medium">{row.name}</div><div class="text-[10px] text-muted-foreground">{row.phone}</div></td><td class="px-3 py-2.5"><div class="font-medium">{row.armada_nopol || '-'}</div><div class="text-[10px] text-muted-foreground">{row.layanan}</div></td><td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold tabular-nums">{formatCurrency(row.revenue ?? row.total)}</td><td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold tabular-nums">{formatCurrency(row.bop ?? 0)}</td></tr>{/each}{/if}</tbody></table></div>
                </section>
                <section class="min-w-0 overflow-hidden rounded-xl border border-border/70 bg-background shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-border/70 px-4 py-3.5"><div><p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted-foreground">Bagasi</p><h4 class="mt-1 text-sm font-semibold">{monthly?.luggage_count ?? 0} data</h4></div><Badge variant="secondary" class="rounded-full px-2 py-1 text-[10px]">{formatCurrency(monthly?.luggage_revenue ?? 0)}</Badge></div>
                    <div class="max-h-[26rem] overflow-auto"><table class="w-full min-w-[760px] text-left text-xs"><thead class="sticky top-0 z-10 bg-muted/60 text-[10px] uppercase tracking-wide text-muted-foreground backdrop-blur"><tr><th class="px-3 py-2">Tanggal</th><th class="px-3 py-2">Resi</th><th class="px-3 py-2">Pengirim</th><th class="px-3 py-2">Penerima</th><th class="px-3 py-2 text-right">Revenue</th><th class="px-3 py-2 text-right">BOP</th></tr></thead><tbody class="divide-y divide-border/60">{#if (monthlyDetail?.bagasi ?? []).length === 0}<tr><td class="px-4 py-8 text-center text-muted-foreground" colspan="6">Tidak ada bagasi pada periode ini.</td></tr>{:else}{#each monthlyDetail?.bagasi ?? [] as row (row.id)}<tr class="align-top hover:bg-muted/30"><td class="whitespace-nowrap px-3 py-2.5 text-muted-foreground">{formatDateLabel(row.departure_date || row.tanggal)}<div class="text-[10px]">Slot {row.departure_unit || '-'}</div></td><td class="px-3 py-2.5"><div class="font-medium">{row.kode_resi}</div><div class="text-[10px] text-muted-foreground">{row.service_name}</div></td><td class="px-3 py-2.5"><div class="font-medium">{row.sender_name}</div><div class="text-[10px] text-muted-foreground">Qty {row.quantity}</div></td><td class="px-3 py-2.5"><div class="font-medium">{row.receiver_name}</div><div class="text-[10px] text-muted-foreground">Slot {row.departure_unit}</div></td><td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold tabular-nums">{formatCurrency(row.revenue ?? row.total)}</td><td class="whitespace-nowrap px-3 py-2.5 text-right font-semibold tabular-nums">{formatCurrency(row.bop ?? 0)}</td></tr>{/each}{/if}</tbody></table></div>
                </section>
            </div>
        </div>
        {/if}
    {:else}
        <div
            class="rounded-xl border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground"
        >
            Data driver tidak ditemukan.
        </div>
        <div class="pt-3">
            <Button
                type="button"
                variant="outline"
                class="h-9 rounded-md"
                onclick={goBackToData}
            >
                Kembali
            </Button>
        </div>
    {/if}
{:else}
    <RevenueChartTable
        title="Driver"
        subtitle="Daftar driver dengan pencarian, periode, dan aksi."
        badges={[
            { key: 'total', label: `${driverMeta.total} driver` },
            { key: 'period', label: selectedPeriodLabel() },
            { key: 'page', label: pageSummary() },
        ]}
        density="compact"
        showHeader={false}
    >
        {#snippet controls()}
            <div
                class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_minmax(180px,220px)_auto]"
            >
                <div class="min-w-0">
                    <TerminalFilter
                        bind:query={driverSearch}
                        placeholder="Cari nama, kontak, atau kategori"
                        class="min-w-0"
                        on:search={() => void loadDrivers(1)}
                    />
                </div>

                <select
                    class="h-9 w-full rounded-md border border-input bg-background px-2.5 text-[13px] shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    bind:value={driverPeriod}
                    onchange={() => void loadDrivers(1)}
                >
                    {#each periodOptions as period (period.value)}
                        <option value={period.value}>{period.label}</option>
                    {/each}
                </select>

                {#if canExport}
                    <a
                        href={exportHref}
                        class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 text-[13px] font-semibold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.98] xl:w-auto"
                    >
                        <Download class="h-4 w-4" />
                        Export Data Driver (Excel)
                    </a>
                {/if}
            </div>
        {/snippet}

        {#snippet table()}
            <div class="grid gap-3 md:hidden">
                {#if drivers.length === 0}
                    <div
                        class="col-span-full rounded-lg border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground"
                    >
                        Belum ada driver untuk filter yang dipilih.
                    </div>
                {/if}

                {#each drivers as row (row.id)}
                    {@const gross = driverGrossMargin(row)}
                    {@const net = driverNetMargin(row)}
                    {@const achievement = driverAchievement(row)}
                    {@const status = driverStatus(row)}
                    {@const tone = achievementTone(achievement)}
                    <article
                        class="group flex h-full flex-col rounded-lg border border-border/70 bg-card p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-300/70 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p
                                    class="truncate text-lg font-bold tracking-tight text-foreground"
                                >
                                    {row.nama}
                                </p>
                                <p
                                    class="mt-1 truncate text-xs text-muted-foreground"
                                >
                                    {row.phone ?? '-'} | {row.category ?? '-'}
                                </p>
                            </div>

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
                                    <DropdownMenuItem
                                        onclick={() => openDriverView(row)}
                                    >
                                        <Eye class="mr-2 h-3.5 w-3.5" />
                                        Lihat Detail
                                    </DropdownMenuItem>
                                    {#if canManage}
                                        <DropdownMenuItem
                                            onclick={() => editDriver(row)}
                                        >
                                            <Pencil class="mr-2 h-3.5 w-3.5" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            onclick={() =>
                                                void removeDriver(row.id)}
                                        >
                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                            Hapus
                                        </DropdownMenuItem>
                                    {/if}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div
                            class="mt-4 rounded-lg border border-border/70 bg-muted/30 p-4"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                                    >
                                        Net Margin
                                    </p>
                                    <p
                                        class={`mt-2 text-2xl font-bold tabular-nums ${net >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'}`}
                                    >
                                        {formatCurrency(net)}
                                    </p>
                                </div>
                                <Badge
                                    variant="outline"
                                    class="rounded-full px-2.5 py-0.5 text-[10px] uppercase tracking-wide"
                                >
                                    Target {formatCurrency(
                                        Number(row.target_revenue_bulanan || 0),
                                    )}
                                </Badge>
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground">
                                Gross: {formatCurrency(gross)} | BOP: {formatCurrency(
                                    Number(row.bop || 0),
                                )}
                            </p>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div
                                class="flex items-center justify-between text-xs"
                            >
                                <span class="font-semibold text-foreground"
                                    >Achievement Target</span
                                >
                                <span
                                    class={`font-semibold tabular-nums ${tone.text}`}
                                >
                                    {achievement.toFixed(1)}%
                                </span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted/70">
                                <div
                                    class={`h-2 rounded-full ${tone.bar}`}
                                    style={`width: ${Math.max(0, Math.min(100, achievement))}%`}
                                ></div>
                            </div>
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <p class="text-[11px] text-muted-foreground">
                                    {tone.label}
                                </p>
                                <span
                                    class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${
                                        status === 'Tercapai'
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
                                            : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300'
                                    }`}
                                >
                                    {status}
                                </span>
                            </div>
                        </div>
                    </article>
                {/each}
            </div>

            <div class="hidden md:block">
                <DataTable
                    columns={driverTableColumns}
                    rows={drivers}
                    density="compact"
                >
                    {#snippet row({ row })}
                        {@const driver = row as DriverRow}
                        {@const gross = driverGrossMargin(driver)}
                        {@const net = driverNetMargin(driver)}
                        {@const achievement = driverAchievement(driver)}
                        {@const status = driverStatus(driver)}
                        {@const tone = achievementTone(achievement)}

                        <td class="px-2.5 py-1.5 align-top">
                            <div
                                class="truncate text-[11px] font-semibold leading-4 text-foreground"
                            >
                                {driver.nama}
                            </div>
                            <div
                                class="mt-0.5 truncate text-[10px] text-muted-foreground"
                            >
                                {driver.phone ?? '-'}
                            </div>
                        </td>

                        <td class="px-2.5 py-1.5 align-top">
                            <div class="truncate font-medium text-foreground">
                                {driver.category ?? '-'}
                            </div>
                        </td>

                        <td
                            class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums text-slate-700 dark:text-slate-300"
                            >{formatCurrency(
                                Number(driver.target_revenue_bulanan || 0),
                            )}</td
                        >
                        <td
                            class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums text-emerald-700 dark:text-emerald-300"
                            >{formatCurrency(Number(driver.revenue || 0))}</td
                        >
                        <td
                            class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums text-amber-700 dark:text-amber-300"
                            >{formatCurrency(Number(driver.bop || 0))}</td
                        >
                        <td
                            class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums"
                            >{formatCurrency(gross)}</td
                        >
                        <td
                            class="px-2.5 py-1.5 text-right text-[10px] tabular-nums"
                            >{formatCurrency(
                                Number(driver.fixed_cost || 0),
                            )}</td
                        >
                        <td
                            class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums"
                            >{formatCurrency(net)}</td
                        >
                        <td
                            class="px-2.5 py-1.5 text-right text-[10px] tabular-nums"
                            >{achievement.toFixed(1)}%</td
                        >
                        <td class="px-2.5 py-1.5 text-center">
                            <span
                                class={`inline-flex rounded-full border px-1.5 py-0.5 text-[9px] font-semibold ${
                                    status === 'Tercapai'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
                                        : tone.label === 'Aman/Menuju Target'
                                          ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300'
                                          : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/35 dark:text-rose-300'
                                }`}
                            >
                                {status}
                            </span>
                        </td>
                    {/snippet}

                    {#snippet actions({ row })}
                        {@const driver = row as DriverRow}
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
                                <DropdownMenuItem
                                    onclick={() => openDriverView(driver)}
                                >
                                    <Eye class="mr-2 h-3.5 w-3.5" />
                                    Lihat Detail
                                </DropdownMenuItem>
                                {#if canManage}
                                    <DropdownMenuItem
                                        onclick={() => editDriver(driver)}
                                    >
                                        <Pencil class="mr-2 h-3.5 w-3.5" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        onclick={() =>
                                            void removeDriver(driver.id)}
                                    >
                                        <Trash2 class="mr-2 h-3.5 w-3.5" />
                                        Hapus
                                    </DropdownMenuItem>
                                {/if}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    {/snippet}
                </DataTable>
            </div>

            {#if driverMeta.last_page > 1}
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-border/70 pt-3"
                >
                    <p class="text-xs text-muted-foreground">
                        Halaman {driverMeta.page} dari {driverMeta.last_page}
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-md"
                            disabled={driverMeta.page <= 1}
                            onclick={() => void goToPage(driverMeta.page - 1)}
                        >
                            <ChevronLeft class="mr-1.5 h-4 w-4" />
                            Sebelumnya
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-md"
                            disabled={driverMeta.page >= driverMeta.last_page}
                            onclick={() => void goToPage(driverMeta.page + 1)}
                        >
                            Berikutnya
                            <ChevronRight class="ml-1.5 h-4 w-4" />
                        </Button>
                    </div>
                </div>
            {/if}
        {/snippet}
    </RevenueChartTable>
{/if}
