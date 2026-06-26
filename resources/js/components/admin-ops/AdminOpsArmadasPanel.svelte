<script lang="ts">
    import { Download, Eye, MoreHorizontal, Pencil, Trash2 } from 'lucide-svelte';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import DataTable from '@/components/terminal/DataTable.svelte';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import TerminalFilter from '@/components/terminal/TerminalFilter.svelte';
import RevenueChartTable from './RevenueChartTable.svelte';

    type ViewMode = 'data' | 'form' | 'view' | 'layout';
    type ArmadaPoolOption = {
        id: number;
        name: string;
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
        driver_name?: string | null;
    };
    type ArmadaMonthlyBookingRow = {
        id: number;
        tanggal: string;
        jam: string;
        departure_date: string;
        rute: string;
        unit: number;
        seat: string;
        name: string;
        phone: string;
        pickup_point: string;
        pembayaran: string;
        status: string;
        total: number;
        revenue: number;
        bop: number;
    };
    type ArmadaMonthlyCharterRow = {
        id: number;
        start_date: string;
        end_date: string;
        departure_date: string;
        departure_time: string;
        name: string;
        phone: string;
        pickup_point: string;
        drop_point: string;
        layanan: string;
        payment_status: string;
        bop_status: string;
        status: string;
        armada_nopol: string;
        driver_name: string;
        total: number;
        revenue: number;
        bop: number;
    };
    type ArmadaMonthlyLuggageRow = {
        id: number;
        tanggal: string;
        created_at: string;
        departure_date: string;
        kode_resi: string;
        sender_name: string;
        receiver_name: string;
        quantity: number;
        payment_status: string;
        status: string;
        service_name: string;
        total: number;
        revenue: number;
        bop: number;
        departure_time: string;
        departure_unit: number;
    };
    type ArmadaMonthlySummary = {
        period: string;
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
    type ArmadaMonthlyDetail = {
        summary: ArmadaMonthlySummary;
        bookings: ArmadaMonthlyBookingRow[];
        charters: ArmadaMonthlyCharterRow[];
        bagasi: ArmadaMonthlyLuggageRow[];
    };
    type ArmadaDetailRow = ArmadaRow & {
        monthly?: ArmadaMonthlyDetail | null;
    };

    let {
        activeMode = 'data',
        armadaDetail = null,
        armadaDetailLoading = false,
        armadas = [],
        armadaSearch = $bindable(''),
        armadaPoolId = $bindable(0),
        armadaPeriod = $bindable(`${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}`),
        armadaPoolOptions = [],
        formatCurrency,
        armadaGrossMargin,
        armadaNetMargin,
        armadaAchievement,
        armadaStatus,
        categoryTone,
        normalizeUnitCategory,
        loadArmadas,
        openArmadaView,
        openArmadaEditor,
        removeArmada,
        goBackToData,
        canManage = true,
        canExport = false,
    }: {
        activeMode?: ViewMode;
        armadaDetail?: ArmadaDetailRow | null;
        armadaDetailLoading?: boolean;
        armadas?: ArmadaRow[];
        armadaSearch?: string;
        armadaPoolId?: number;
        armadaPeriod?: string;
        armadaPoolOptions?: ArmadaPoolOption[];
        formatCurrency: (value: number) => string;
        armadaGrossMargin: (row: ArmadaRow) => number;
        armadaNetMargin: (row: ArmadaRow) => number;
        armadaAchievement: (row: ArmadaRow) => number;
        armadaStatus: (row: ArmadaRow) => string;
        categoryTone: (category: string | null | undefined) => string;
        normalizeUnitCategory: (value: string | null | undefined) => string;
        loadArmadas: () => void | Promise<void>;
        openArmadaView: (id: number) => void;
        openArmadaEditor: (row: ArmadaRow) => void;
        removeArmada: (id: number) => void | Promise<void>;
        goBackToData: () => void;
        canManage?: boolean;
        canExport?: boolean;
    } = $props();

    const rowPoolName = (row: ArmadaRow) =>
        String(row.pool_name ?? '').trim() || 'Semua Pool';

    const formatDateLabel = (value: string) => {
        const raw = String(value ?? '').trim();

        if (raw === '') {
            return '-';
        }

        const date = new Date(`${raw}T00:00:00`);
        if (Number.isNaN(date.getTime())) {
            return raw;
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(date);
    };

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
        periodOptions.find((option) => option.value === armadaPeriod)?.label ??
        'Periode sekarang';

    const exportHref = $derived.by(() => {
        const params = new URLSearchParams();
        const query = armadaSearch.trim();

        if (query !== '') {
            params.set('q', query);
        }

        params.set('pool_id', String(Number(armadaPoolId || 0)));

        if (armadaPeriod.trim() !== '') {
            params.set('period', armadaPeriod.trim());
        }

        const suffix = params.toString();

        return `/api/admin/armadas/export${suffix !== '' ? `?${suffix}` : ''}`;
    });

    const gpsActive = (row: ArmadaRow) =>
        [row.platform_gps, row.api_gps].some(
            (value) => String(value ?? '').trim() !== '',
        );

    const achievementTone = (achievement: number) => {
        if (achievement < 50) {
            return {
                bar: 'bg-rose-500',
                text: 'text-rose-700 dark:text-rose-300',
                badge: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/35 dark:text-rose-300',
                label: 'Kurang',
            };
        }

        if (achievement <= 80) {
            return {
                bar: 'bg-amber-400',
                text: 'text-amber-700 dark:text-amber-300',
                badge: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300',
                label: 'Mendekati',
            };
        }

        return {
            bar: 'bg-emerald-500',
            text: 'text-emerald-700 dark:text-emerald-300',
            badge: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300',
            label: 'Tercapai',
        };
    };

    const gpsTone = (active: boolean) =>
        active
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
            : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/35 dark:text-rose-300';
    const armadaSummary = $derived.by(() => {
        const totals = armadas.reduce(
            (acc, row) => {
                const revenue = Number(row.revenue || 0);
                const bop = Number(row.bop || 0);
                const fixedCost = Number(row.fixed_cost || 0);
                const target = Number(row.target_bulanan || 0);

                acc.revenue += revenue;
                acc.bop += bop;
                acc.fixedCost += fixedCost;
                acc.target += target;

                return acc;
            },
            {
                revenue: 0,
                bop: 0,
                fixedCost: 0,
                target: 0,
            },
        );

        const gross = totals.revenue - totals.bop;
        const net = gross - totals.fixedCost;
        const achievement = totals.target > 0 ? (totals.revenue / totals.target) * 100 : 0;

        return {
            count: armadas.length,
            revenue: totals.revenue,
            bop: totals.bop,
            gross,
            fixedCost: totals.fixedCost,
            net,
            target: totals.target,
            achievement,
        };
    });

    const summaryTone = (value: number) => {
        if (value < 0) {
            return 'text-rose-700 dark:text-rose-300';
        }

        return 'text-foreground';
    };

    const detailMetricCards = (row: ArmadaDetailRow) => {
        const monthlyDetail = row.monthly ?? null;
        const monthly = monthlyDetail?.summary ?? null;
        const gross = monthly?.gross ?? armadaGrossMargin(row);
        const net = monthly?.net_margin ?? armadaNetMargin(row);
        const achievement = monthly?.achievement ?? armadaAchievement(row);
        const target = monthly?.target_revenue ?? Number(row.target_bulanan || 0);
        const status = monthly?.status ?? armadaStatus(row);

        return [
            {
                key: 'charter',
                label: 'Charter',
                valueText: String(monthly?.charter_count ?? 0),
                note: 'Jumlah transaksi charter',
                tone: 'text-foreground',
            },
            {
                key: 'departure',
                label: 'Keberangkatan',
                valueText: String(monthly?.departure_count ?? 0),
                note: 'Jumlah data keberangkatan',
                tone: 'text-foreground',
            },
            {
                key: 'bagasi',
                label: 'Bagasi',
                valueText: String(monthly?.luggage_count ?? 0),
                note: 'Jumlah data bagasi',
                tone: 'text-foreground',
            },
            {
                key: 'revenue',
                label: 'Total Revenue',
                valueText: formatCurrency(monthly?.total_revenue ?? Number(row.revenue || 0)),
                note: 'Revenue bulanan armada',
                tone: 'text-emerald-700 dark:text-emerald-300',
            },
            {
                key: 'charter-bop',
                label: 'Charter BOP',
                valueText: formatCurrency(monthly?.charter_bop ?? Number(row.charter_bop || 0)),
                note: 'BOP perjalanan charter',
                tone: 'text-amber-700 dark:text-amber-300',
            },
            {
                key: 'departure-bop',
                label: 'Keberangkatan BOP',
                valueText: formatCurrency(monthly?.departure_bop ?? Number(row.departure_bop || 0)),
                note: 'BOP perjalanan reguler',
                tone: 'text-amber-700 dark:text-amber-300',
            },
            {
                key: 'bop',
                label: 'Total BOP',
                valueText: formatCurrency(monthly?.total_bop ?? Number(row.bop || 0)),
                note: 'Akumulasi BOP bulanan',
                tone: 'text-amber-700 dark:text-amber-300',
            },
            {
                key: 'gross',
                label: 'Gross',
                valueText: formatCurrency(gross),
                note: 'Revenue - BOP',
                tone: summaryTone(gross),
            },
            {
                key: 'fixed-cost',
                label: 'Fixed Cost',
                valueText: formatCurrency(monthly?.fixed_cost ?? Number(row.fixed_cost || 0)),
                note: 'Biaya tetap bulanan',
                tone: 'text-slate-700 dark:text-slate-300',
            },
            {
                key: 'net',
                label: 'Net Margin',
                valueText: formatCurrency(net),
                note: 'Gross - Fixed Cost',
                tone: summaryTone(net),
            },
            {
                key: 'target',
                label: 'Target Revenue',
                valueText: formatCurrency(target),
                note: 'Target bulanan',
                tone: 'text-slate-700 dark:text-slate-300',
            },
            {
                key: 'achievement',
                label: 'Achievement',
                valueText: `${achievement.toFixed(1)}%`,
                note: 'Pencapaian terhadap target',
                tone: achievement >= 100
                    ? 'text-emerald-700 dark:text-emerald-300'
                    : achievement >= 80
                      ? 'text-amber-700 dark:text-amber-300'
                      : 'text-rose-700 dark:text-rose-300',
            },
            {
                key: 'status',
                label: 'Status',
                valueText: status,
                note: monthly?.period_label ?? selectedPeriodLabel(),
                tone: achievement >= 100
                    ? 'text-emerald-700 dark:text-emerald-300'
                    : 'text-rose-700 dark:text-rose-300',
            },
        ];
    };

    const statusTone = (value: string) => {
        const normalized = String(value ?? '').trim().toLowerCase();

        if (
            normalized === 'done' ||
            normalized === 'selesai' ||
            normalized === 'lunas' ||
            normalized === 'paid' ||
            normalized === 'active' ||
            normalized === 'aktif' ||
            normalized === 'tercapai'
        ) {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300';
        }

        if (
            normalized === 'pending' ||
            normalized === 'dp' ||
            normalized === 'proses' ||
            normalized === 'partial'
        ) {
            return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300';
        }

        if (
            normalized === 'canceled' ||
            normalized === 'cancelled' ||
            normalized === 'batal' ||
            normalized === 'kurang'
        ) {
            return 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/35 dark:text-rose-300';
        }

        return 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-800 dark:bg-slate-900/35 dark:text-slate-300';
    };

    const summaryCards = $derived([
        {
            key: 'revenue',
            label: 'Total Revenue',
            value: armadaSummary.revenue,
            valueText: formatCurrency(armadaSummary.revenue),
            note: 'Sum Revenue unit aktif',
            formula: 'Total Revenue = Sum Revenue',
            tone: 'text-emerald-700 dark:text-emerald-300',
        },
        {
            key: 'bop',
            label: 'Total BOP',
            value: armadaSummary.bop,
            valueText: formatCurrency(armadaSummary.bop),
            note: 'Biaya operasional perjalanan',
            formula: 'Total BOP = Sum BOP unit',
            tone: 'text-amber-700 dark:text-amber-300',
        },
        {
            key: 'gross',
            label: 'Gross Margin',
            value: armadaSummary.gross,
            valueText: formatCurrency(armadaSummary.gross),
            note: 'Revenue - BOP',
            formula: 'Gross = Revenue - BOP',
            tone: summaryTone(armadaSummary.gross),
        },
        {
            key: 'fixedCost',
            label: 'Total Fixed Cost',
            value: armadaSummary.fixedCost,
            valueText: formatCurrency(armadaSummary.fixedCost),
            note: 'Biaya tetap bulanan',
            formula: 'Fixed Cost = Sum Fixed Cost',
            tone: 'text-slate-700 dark:text-slate-300',
        },
        {
            key: 'net',
            label: 'Net Margin',
            value: armadaSummary.net,
            valueText: formatCurrency(armadaSummary.net),
            note: 'Gross - Fixed Cost',
            formula: 'Net = Gross - Fixed Cost',
            tone: summaryTone(armadaSummary.net),
        },
        {
            key: 'achievement',
            label: 'Achievement Gabungan',
            value: armadaSummary.achievement,
            valueText: `${armadaSummary.achievement.toFixed(1)}%`,
            note: `${armadaSummary.count} armada aktif`,
            formula: 'Achievement = Revenue / Target x 100%',
            tone: armadaSummary.achievement >= 100
                ? 'text-emerald-700 dark:text-emerald-300'
                : armadaSummary.achievement >= 80
                  ? 'text-amber-700 dark:text-amber-300'
                : 'text-rose-700 dark:text-rose-300',
        },
    ]);

    const armadaSummaryCards = $derived([
        {
            key: 'revenue',
            label: 'Total Revenue',
            valueText: formatCurrency(armadaSummary.revenue),
            note: 'Sum Revenue unit aktif',
            formula: 'Total Revenue = Sum Revenue',
            tone: 'text-emerald-700 dark:text-emerald-300',
        },
        {
            key: 'bop',
            label: 'Total BOP',
            valueText: formatCurrency(armadaSummary.bop),
            note: 'Biaya operasional perjalanan',
            formula: 'Total BOP = Sum BOP unit',
            tone: 'text-amber-700 dark:text-amber-300',
        },
        {
            key: 'gross',
            label: 'Gross Margin',
            valueText: formatCurrency(armadaSummary.gross),
            note: 'Revenue - BOP',
            formula: 'Gross = Revenue - BOP',
            tone: summaryTone(armadaSummary.gross),
        },
        {
            key: 'fixedCost',
            label: 'Total Fixed Cost',
            valueText: formatCurrency(armadaSummary.fixedCost),
            note: 'Biaya tetap bulanan',
            formula: 'Fixed Cost = Sum Fixed Cost',
            tone: 'text-slate-700 dark:text-slate-300',
        },
        {
            key: 'net',
            label: 'Net Margin',
            valueText: formatCurrency(armadaSummary.net),
            note: 'Gross - Fixed Cost',
            formula: 'Net = Gross - Fixed Cost',
            tone: summaryTone(armadaSummary.net),
        },
        {
            key: 'achievement',
            label: 'Achievement Gabungan',
            valueText: `${armadaSummary.achievement.toFixed(1)}%`,
            note: `${armadaSummary.count} armada aktif`,
            formula: 'Achievement = Revenue / Target × 100%',
            tone: armadaSummary.achievement >= 100
                ? 'text-emerald-700 dark:text-emerald-300'
                : armadaSummary.achievement >= 80
                  ? 'text-amber-700 dark:text-amber-300'
                  : 'text-rose-700 dark:text-rose-300',
        },
    ]);

    const armadaChartBars = $derived.by(() =>
        [...armadas]
            .sort(
                (left, right) =>
                    Number(right.revenue || 0) - Number(left.revenue || 0),
            )
            .slice(0, 6)
            .map((row) => ({
                key: String(row.id),
                label: row.nopol,
                subtitle: `${row.driver_name ?? 'Driver belum diatur'} · ${row.pool_name ?? 'Semua Pool'}`,
                value: Number(row.revenue || 0),
                valueText: formatCurrency(Number(row.revenue || 0)),
                tone: 'bg-cyan-500',
            })),
    );

    const armadaTableColumns = [
        { key: 'armada', label: 'Armada', width: 'w-[160px]', sticky: 'left' },
        { key: 'driver', label: 'Driver / Pool', width: 'w-[170px]' },
        { key: 'revenue', label: 'Revenue', align: 'right', numeric: true, width: 'w-[120px]' },
        { key: 'bop', label: 'BOP', align: 'right', numeric: true, width: 'w-[110px]' },
        { key: 'gross', label: 'Gross', align: 'right', numeric: true, width: 'w-[120px]' },
        { key: 'fixed_cost', label: 'Fixed Cost', align: 'right', numeric: true, width: 'w-[120px]' },
        { key: 'target', label: 'Target', align: 'right', numeric: true, width: 'w-[120px]' },
        { key: 'achievement', label: 'Achievement', align: 'right', numeric: true, width: 'w-[100px]' },
        { key: 'gps', label: 'GPS', align: 'center', width: 'w-[96px]' },
    ];
</script>

{#if activeMode === 'view'}
    {#if armadaDetailLoading}
        <div class="rounded-lg border border-border/70 bg-background/95 p-4 shadow-sm">
            <div class="animate-pulse space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="space-y-2">
                        <div class="h-5 w-40 rounded bg-muted"></div>
                        <div class="h-3 w-64 rounded bg-muted"></div>
                    </div>
                    <div class="h-9 w-24 rounded bg-muted"></div>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    {#each Array(12) as _, index (index)}
                        <div class="h-20 rounded-lg border border-border/60 bg-muted/30"></div>
                    {/each}
                </div>
                <div class="grid gap-3 xl:grid-cols-3">
                    <div class="h-72 rounded-lg border border-border/60 bg-muted/30"></div>
                    <div class="h-72 rounded-lg border border-border/60 bg-muted/30"></div>
                    <div class="h-72 rounded-lg border border-border/60 bg-muted/30"></div>
                </div>
            </div>
        </div>
    {:else if armadaDetail}
        {@const monthlyDetail = armadaDetail.monthly ?? null}
        {@const monthly = monthlyDetail?.summary ?? null}
        {@const detailCards = detailMetricCards(armadaDetail)}
        <div class="space-y-4 rounded-lg border border-border/70 bg-background/95 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-lg font-bold tracking-tight">{armadaDetail.nopol}</p>
                        <Badge variant="secondary" class="rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {rowPoolName(armadaDetail)}
                        </Badge>
                        <Badge variant="outline" class="rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {armadaDetail.kategori ?? '-'} / {armadaDetail.ac_type}
                        </Badge>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        {armadaDetail.driver_name ?? 'Driver belum diatur'} · No. rangka {armadaDetail.nomor_rangka ?? '-'}
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
                        <span>Platform GPS: {armadaDetail.platform_gps ?? '-'}</span>
                        <span>API GPS: {armadaDetail.api_gps ?? '-'}</span>
                    </div>
                </div>
                <Button type="button" variant="outline" class="h-9 shrink-0" onclick={goBackToData}>Kembali</Button>
            </div>

            <div class="rounded-lg border border-border/70 bg-muted/20 p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                            Ringkasan Bulanan
                        </p>
                        <h4 class="mt-1 text-sm font-semibold tracking-tight">
                            {monthly?.period_label ?? selectedPeriodLabel()}
                        </h4>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge variant="secondary" class="rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {(monthly?.achievement ?? armadaAchievement(armadaDetail)).toFixed(1)}%
                        </Badge>
                        <Badge variant="outline" class={`rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide ${statusTone(monthly?.status ?? armadaStatus(armadaDetail))}`}>
                            {monthly?.status ?? armadaStatus(armadaDetail)}
                        </Badge>
                    </div>
                </div>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    {#each detailCards as card (card.key)}
                        <article class="rounded-md border border-border/70 bg-background px-3 py-2">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                                {card.label}
                            </p>
                            <p class={`mt-1 text-sm font-bold tabular-nums ${card.tone}`}>
                                {card.valueText}
                            </p>
                            <p class="mt-1 text-[11px] leading-4 text-muted-foreground">
                                {card.note}
                            </p>
                        </article>
                    {/each}
                </div>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <section class="rounded-lg border border-border/70 bg-background/95 p-3 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Manifest Keberangkatan
                            </p>
                            <h4 class="mt-1 text-sm font-semibold tracking-tight">
                                {monthly?.departure_count ?? 0} data
                            </h4>
                        </div>
                        <Badge variant="secondary" class="rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {formatCurrency(monthly?.departure_revenue ?? 0)}
                        </Badge>
                    </div>
                    <div class="table-container mt-3 max-h-72 overflow-auto pr-1 scrollbar-thin">
                        <table class="w-full text-left text-[11px]">
                            <thead class="sticky top-0 bg-background/95 text-[10px] uppercase tracking-[0.16em] text-muted-foreground">
                                <tr>
                                    <th class="py-2 pr-2 font-semibold">Manifest</th>
                                    <th class="py-2 pr-2 font-semibold">Rute</th>
                                    <th class="py-2 pr-2 font-semibold">Unit</th>
                                    <th class="py-2 pr-2 font-semibold">Nama</th>
                                    <th class="py-2 pr-2 text-right font-semibold">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                {#if (monthlyDetail?.bookings ?? []).length === 0}
                                    <tr>
                                        <td class="py-3 text-muted-foreground" colspan="5">Tidak ada data manifest keberangkatan pada periode ini.</td>
                                    </tr>
                                {:else}
                                    {#each monthlyDetail?.bookings ?? [] as row (row.id)}
                                        <tr class="align-top transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/30">
                                            <td class="py-2 pr-2 text-muted-foreground">
                                                <div class="font-medium text-foreground">{formatDateLabel(row.departure_date || row.tanggal)}</div>
                                                <div class="text-[10px]">{row.jam}</div>
                                            </td>
                                            <td class="py-2 pr-2 font-medium">{row.rute}</td>
                                            <td class="py-2 pr-2 tabular-nums">{row.unit}</td>
                                            <td class="py-2 pr-2">
                                                <div class="font-medium text-foreground">{row.name}</div>
                                                <div class="text-[10px] text-muted-foreground">{row.seat} · {row.pickup_point}</div>
                                            </td>
                                            <td class="py-2 text-right font-semibold tabular-nums">{formatCurrency(row.revenue ?? row.total)}</td>
                                        </tr>
                                    {/each}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-lg border border-border/70 bg-background/95 p-3 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Carter
                            </p>
                            <h4 class="mt-1 text-sm font-semibold tracking-tight">
                                {monthly?.charter_count ?? 0} data
                            </h4>
                        </div>
                        <Badge variant="secondary" class="rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {formatCurrency(monthly?.charter_revenue ?? 0)}
                        </Badge>
                    </div>
                    <div class="table-container mt-3 max-h-72 overflow-auto pr-1 scrollbar-thin">
                        <table class="w-full text-left text-[11px]">
                            <thead class="sticky top-0 bg-background/95 text-[10px] uppercase tracking-[0.16em] text-muted-foreground">
                                <tr>
                                    <th class="py-2 pr-2 font-semibold">Berangkat</th>
                                    <th class="py-2 pr-2 font-semibold">Rute</th>
                                    <th class="py-2 pr-2 font-semibold">Penyewa</th>
                                    <th class="py-2 pr-2 font-semibold">Armada</th>
                                    <th class="py-2 pr-2 text-right font-semibold">Revenue</th>
                                    <th class="py-2 pr-2 text-right font-semibold">BOP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                {#if (monthlyDetail?.charters ?? []).length === 0}
                                    <tr>
                                        <td class="py-3 text-muted-foreground" colspan="6">Tidak ada data charter pada periode ini.</td>
                                    </tr>
                                {:else}
                                    {#each monthlyDetail?.charters ?? [] as row (row.id)}
                                        <tr class="align-top transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/30">
                                            <td class="py-2 pr-2 text-muted-foreground">
                                                <div class="font-medium text-foreground">{formatDateLabel(row.departure_date || row.start_date)}</div>
                                                <div class="text-[10px]">{row.departure_time || '-'}</div>
                                            </td>
                                            <td class="py-2 pr-2">
                                                <div class="font-medium text-foreground">{row.pickup_point}</div>
                                                <div class="text-[10px] text-muted-foreground">{row.drop_point}</div>
                                            </td>
                                            <td class="py-2 pr-2">
                                                <div class="font-medium text-foreground">{row.name}</div>
                                                <div class="text-[10px] text-muted-foreground">{row.phone}</div>
                                            </td>
                                            <td class="py-2 pr-2">
                                                <div class="font-medium text-foreground">{row.armada_nopol || armadaDetail.nopol}</div>
                                                <div class="text-[10px] text-muted-foreground">{row.layanan}</div>
                                            </td>
                                            <td class="py-2 text-right font-semibold tabular-nums">{formatCurrency(row.revenue ?? row.total)}</td>
                                            <td class="py-2 text-right font-semibold tabular-nums">{formatCurrency(row.bop ?? 0)}</td>
                                        </tr>
                                    {/each}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-lg border border-border/70 bg-background/95 p-3 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Bagasi
                            </p>
                            <h4 class="mt-1 text-sm font-semibold tracking-tight">
                                {monthly?.luggage_count ?? 0} data
                            </h4>
                        </div>
                        <Badge variant="secondary" class="rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {formatCurrency(monthly?.luggage_revenue ?? 0)}
                        </Badge>
                    </div>
                    <div class="table-container mt-3 max-h-72 overflow-auto pr-1 scrollbar-thin">
                        <table class="w-full text-left text-[11px]">
                            <thead class="sticky top-0 bg-background/95 text-[10px] uppercase tracking-[0.16em] text-muted-foreground">
                                <tr>
                                    <th class="py-2 pr-2 font-semibold">Berangkat</th>
                                    <th class="py-2 pr-2 font-semibold">Resi</th>
                                    <th class="py-2 pr-2 font-semibold">Pengirim</th>
                                    <th class="py-2 pr-2 font-semibold">Penerima</th>
                                    <th class="py-2 pr-2 text-right font-semibold">Revenue</th>
                                    <th class="py-2 pr-2 text-right font-semibold">BOP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                {#if (monthlyDetail?.bagasi ?? []).length === 0}
                                    <tr>
                                        <td class="py-3 text-muted-foreground" colspan="6">Tidak ada data bagasi pada periode ini.</td>
                                    </tr>
                                {:else}
                                    {#each monthlyDetail?.bagasi ?? [] as row (row.id)}
                                        <tr class="align-top transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/30">
                                            <td class="py-2 pr-2 text-muted-foreground">
                                                <div class="font-medium text-foreground">{formatDateLabel(row.departure_date || row.tanggal)}</div>
                                                <div class="text-[10px]">Unit {row.departure_unit || '-'}</div>
                                            </td>
                                            <td class="py-2 pr-2">
                                                <div class="font-medium text-foreground">{row.kode_resi}</div>
                                                <div class="text-[10px] text-muted-foreground">{row.service_name}</div>
                                            </td>
                                            <td class="py-2 pr-2">
                                                <div class="font-medium text-foreground">{row.sender_name}</div>
                                                <div class="text-[10px] text-muted-foreground">Qty {row.quantity}</div>
                                            </td>
                                            <td class="py-2 pr-2">
                                                <div class="font-medium text-foreground">{row.receiver_name}</div>
                                                <div class="text-[10px] text-muted-foreground">Unit {row.departure_unit}</div>
                                            </td>
                                            <td class="py-2 text-right font-semibold tabular-nums">{formatCurrency(row.revenue ?? row.total)}</td>
                                            <td class="py-2 text-right font-semibold tabular-nums">{formatCurrency(row.bop ?? 0)}</td>
                                        </tr>
                                    {/each}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    {:else}
        <p class="text-sm text-muted-foreground">Data armada tidak ditemukan.</p>
        <Button type="button" variant="outline" class="h-9" onclick={goBackToData}>Kembali</Button>
    {/if}
{/if}
<RevenueChartTable
    title="Armada"
    subtitle="Daftar armada dengan filter pool dan periode."
    badges={[
        { key: 'total', label: `${armadas.length} unit` },
        { key: 'period', label: selectedPeriodLabel() },
    ]}
    density="compact"
>
    {#snippet controls()}
        <div class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_minmax(180px,220px)_minmax(180px,220px)_auto]">
            <div class="min-w-0">
                <TerminalFilter
                    bind:query={armadaSearch}
                    placeholder="Cari nopol, driver, pool, atau GPS"
                    class="min-w-0"
                    on:search={() => void loadArmadas()}
                />
            </div>

            <label class="space-y-1">
                <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                    Pool
                </span>
                <select
                    class="h-9 w-full rounded-md border border-input bg-background px-2.5 text-[13px] shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    bind:value={armadaPoolId}
                    onchange={() => void loadArmadas()}
                >
                    {#each armadaPoolOptions as pool (pool.id)}
                        <option value={pool.id}>{pool.name}</option>
                    {/each}
                </select>
            </label>

            <label class="space-y-1">
                <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                    Periode
                </span>
                <select
                    class="h-9 w-full rounded-md border border-input bg-background px-2.5 text-[13px] shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    bind:value={armadaPeriod}
                    onchange={() => void loadArmadas()}
                >
                    {#each periodOptions as period (period.value)}
                        <option value={period.value}>{period.label}</option>
                    {/each}
                </select>
            </label>

            {#if canExport}
                <a
                    href={exportHref}
                    class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 text-[13px] font-semibold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.98] xl:w-auto"
                >
                    <Download class="h-4 w-4" />
                    Export ke Excel
                </a>
            {/if}
        </div>
    {/snippet}

    {#snippet table()}
        <div class="grid gap-3 md:hidden">
            {#if armadas.length === 0}
                <div class="col-span-full rounded-lg border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                    Belum ada armada untuk filter yang dipilih.
                </div>
            {/if}

            {#each armadas as row (row.id)}
                {@const gross = armadaGrossMargin(row)}
                {@const net = armadaNetMargin(row)}
                {@const achievement = armadaAchievement(row)}
                {@const activeGps = gpsActive(row)}
                {@const achievementStyle = achievementTone(achievement)}
                <article class="group flex min-w-0 flex-col space-y-2 overflow-hidden rounded-xl border border-border/70 bg-card p-4 shadow-sm transition duration-200 hover:border-blue-400/70 hover:shadow-[0_12px_24px_-16px_rgba(59,130,246,0.5)]">
                    <header class="flex min-w-0 items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="min-w-0 break-words text-base leading-6 font-bold tracking-tight text-foreground">{row.nopol}</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {row.driver_name ?? 'Driver belum diatur'} · {row.pool_name ?? 'Semua Pool'}
                            </p>
                        </div>
                        <span class={`inline-flex shrink-0 items-center rounded-full border px-2.5 py-1 text-xs font-bold tabular-nums ${achievementStyle.badge}`}>
                            {achievement.toFixed(0)}%
                        </span>
                    </header>

                    <div class="flex min-w-0 flex-wrap items-center gap-2" aria-label="Informasi driver dan GPS">
                        <span class={`inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs font-semibold ${gpsTone(activeGps)}`}>
                            <span class={`size-1.5 rounded-full ${activeGps ? 'bg-emerald-500' : 'bg-rose-500'}`}></span>
                            GPS {activeGps ? 'Aktif' : 'Mati'}
                        </span>
                    </div>

                    <div class="flex min-w-0 items-end justify-between gap-3 pt-1">
                        <dl class="min-w-0">
                            <dt class="text-xs font-medium text-muted-foreground">Net Margin</dt>
                            <dd class="mt-0.5 min-w-0">
                                <p class="max-w-full break-words text-left text-base leading-6 font-bold tabular-nums text-emerald-600">
                                    {formatCurrency(net)}
                                </p>
                            </dd>
                        </dl>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="h-11 w-11 shrink-0 rounded-full border border-border/70 bg-muted/30"
                                    aria-label="Aksi armada"
                                >
                                    <MoreHorizontal class="h-5 w-5" />
                                    <span class="sr-only">Aksi armada</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                <DropdownMenuItem onclick={() => openArmadaView(row.id)}>
                                    <Eye class="mr-2 h-3.5 w-3.5" />
                                    Lihat Detail
                                </DropdownMenuItem>
                                {#if canManage}
                                    <DropdownMenuItem onclick={() => openArmadaEditor(row)}>
                                        <Pencil class="mr-2 h-3.5 w-3.5" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem onclick={() => void removeArmada(row.id)}>
                                        <Trash2 class="mr-2 h-3.5 w-3.5" />
                                        Hapus
                                    </DropdownMenuItem>
                                {/if}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="relative h-1.5 w-full rounded-full bg-muted/70" aria-hidden="true">
                        <div class={`absolute inset-y-0 left-0 rounded-full ${achievementStyle.bar}`} style={`width: ${Math.max(0, Math.min(100, achievement))}%`}></div>
                    </div>
                </article>
            {/each}
        </div>

        <div class="hidden md:block">
            <DataTable columns={armadaTableColumns} rows={armadas} density="compact">
                {#snippet row({ row })}
                    {@const armada = row as ArmadaRow}
                    {@const gross = armadaGrossMargin(armada)}
                    {@const net = armadaNetMargin(armada)}
                    {@const achievement = armadaAchievement(armada)}
                    {@const activeGps = gpsActive(armada)}
                    {@const achievementStyle = achievementTone(achievement)}

                    <td class="px-2.5 py-1.5 align-top">
                        <div class="truncate text-[11px] font-semibold leading-4 text-foreground">{armada.nopol}</div>
                        <div class="mt-0.5 text-[10px] text-muted-foreground">{armada.kategori ?? '-'} / {armada.ac_type}</div>
                    </td>

                    <td class="px-2.5 py-1.5 align-top">
                        <div class="truncate font-medium text-foreground">{armada.driver_name ?? 'Driver belum diatur'}</div>
                        <div class="mt-0.5 truncate text-[10px] text-muted-foreground">{armada.pool_name ?? 'Semua Pool'}</div>
                    </td>

                    <td class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">{formatCurrency(Number(armada.revenue || 0))}</td>
                    <td class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums text-amber-700 dark:text-amber-300">{formatCurrency(Number(armada.bop || 0))}</td>
                    <td class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums">{formatCurrency(gross)}</td>
                    <td class="px-2.5 py-1.5 text-right text-[10px] tabular-nums">{formatCurrency(Number(armada.fixed_cost || 0))}</td>
                    <td class="px-2.5 py-1.5 text-right text-[10px] tabular-nums">{formatCurrency(Number(armada.target_bulanan || 0))}</td>
                    <td class="px-2.5 py-1.5 text-right text-[10px] tabular-nums">{achievement.toFixed(1)}%</td>
                    <td class="px-2.5 py-1.5 text-center">
                        <span class={`inline-flex items-center gap-1.5 rounded-full border px-1.5 py-0.5 text-[9px] font-semibold ${gpsTone(activeGps)}`}>
                            <span class={`size-1.5 rounded-full ${activeGps ? 'bg-emerald-500' : 'bg-rose-500'}`}></span>
                            GPS {activeGps ? 'Aktif' : 'Mati'}
                        </span>
                    </td>
                {/snippet}

                {#snippet actions({ row })}
                    {@const armada = row as ArmadaRow}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                <MoreHorizontal class="h-4 w-4" />
                                <span class="sr-only">Aksi armada</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                            <DropdownMenuItem onclick={() => openArmadaView(armada.id)}>
                                <Eye class="mr-2 h-3.5 w-3.5" />
                                Lihat Detail
                            </DropdownMenuItem>
                            {#if canManage}
                                <DropdownMenuItem onclick={() => openArmadaEditor(armada)}>
                                    <Pencil class="mr-2 h-3.5 w-3.5" />
                                    Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem onclick={() => void removeArmada(armada.id)}>
                                    <Trash2 class="mr-2 h-3.5 w-3.5" />
                                    Hapus
                                </DropdownMenuItem>
                            {/if}
                        </DropdownMenuContent>
                    </DropdownMenu>
                {/snippet}
            </DataTable>
        </div>
    {/snippet}
</RevenueChartTable>
