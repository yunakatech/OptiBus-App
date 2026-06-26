<script lang="ts">
    import {
        Building2,
        Eye,
        MapPin,
        MoreHorizontal,
        Pencil,
        Route,
        Trash2,
        Users,
    } from 'lucide-svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import DataTable from '@/components/terminal/DataTable.svelte';
    import RevenueChartTable from './RevenueChartTable.svelte';

    type PoolMonthlyTargetRow = {
        target_month: string;
        booking_target: number;
        bagasi_target: number;
        carter_target: number;
    };

    type PoolRow = any;
    type ViewMode = 'data' | 'form' | 'view' | 'layout';

    let {
        activeMode = 'data',
        poolDetail = null,
        pools = [],
        poolsColumns = [],
        poolSearch = $bindable(''),
        poolPerformanceFilter = $bindable('all'),
        poolRegionFilter = $bindable('all'),
        poolSortOrder = $bindable('desc'),
        poolRegionOptions = [],
        formatCurrency,
        poolAchievement,
        poolGrossMargin,
        poolNetMargin,
        poolProgressClass,
        poolProgressBadgeClass,
        poolReadyLabel,
        poolTargetTotal,
        poolGap,
        formatPoolRoutes,
        loadPools,
        openPoolEditor,
        openPoolView,
        removeItem,
        goBackToData,
        canManagePools = true,
    }: {
        activeMode?: ViewMode;
        poolDetail?: PoolRow | null;
        pools?: PoolRow[];
        poolsColumns?: any[];
        poolSearch?: string;
        poolPerformanceFilter?: string;
        poolRegionFilter?: string;
        poolSortOrder?: string;
        poolRegionOptions?: string[];
        formatCurrency: (value: number) => string;
        poolAchievement: (row: PoolRow) => number;
        poolGrossMargin: (row: PoolRow) => number;
        poolNetMargin: (row: PoolRow) => number;
        poolProgressClass: (achievement: number) => string;
        poolProgressBadgeClass: (achievement: number) => string;
        poolReadyLabel: (row: PoolRow) => string;
        poolTargetTotal: (row: PoolRow) => number;
        poolGap: (row: PoolRow) => number;
        formatPoolRoutes: (row: PoolRow) => string[];
        loadPools: () => void | Promise<void>;
        openPoolEditor: (row: PoolRow) => void;
        openPoolView: (row: PoolRow) => void;
        removeItem: (url: string, message: string) => void | Promise<void>;
        goBackToData: () => void;
        canManagePools?: boolean;
    } = $props();

    const poolStatusLabel = (value: string | null | undefined) =>
        String(value ?? '').trim().toLowerCase() === 'active'
            ? 'Aktif'
            : 'Nonaktif';

    const formatMonthLabel = (value: string | null | undefined) => {
        const raw = String(value ?? '').trim();

        if (raw === '') {
            return '-';
        }

        const candidate = raw.length === 7 ? `${raw}-01` : raw;
        const date = new Date(`${candidate}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
            return raw;
        }

        return new Intl.DateTimeFormat('id-ID', {
            month: 'long',
            year: 'numeric',
        }).format(date);
    };

    const monthlyTargetRows = (pool: PoolRow): PoolMonthlyTargetRow[] =>
        Array.isArray(pool?.monthly_targets) ? pool.monthly_targets : [];

    const poolDetailCards = (pool: PoolRow) => {
        const achievement = poolAchievement(pool);
        const gross = poolGrossMargin(pool);
        const net = poolNetMargin(pool);
        const gap = poolGap(pool);

        return [
            {
                key: 'revenue',
                label: 'Revenue',
                valueText: formatCurrency(Number(pool.revenue || 0)),
                note: 'Total revenue cabang',
                tone: 'text-emerald-700 dark:text-emerald-300',
            },
            {
                key: 'bop',
                label: 'Total BOP',
                valueText: formatCurrency(Number(pool.bop || 0)),
                note: 'Biaya operasional cabang',
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
                valueText: formatCurrency(Number(pool.fixed_cost || 0)),
                note: 'Biaya tetap cabang',
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
                label: 'Target Revenue',
                valueText: formatCurrency(poolTargetTotal(pool)),
                note: 'Target bulanan aktif',
                tone: 'text-slate-700 dark:text-slate-300',
            },
            {
                key: 'gap',
                label: 'Gap Revenue',
                valueText: formatCurrency(gap),
                note: 'Revenue - target aktif',
                tone:
                    gap >= 0
                        ? 'text-emerald-700 dark:text-emerald-300'
                        : 'text-rose-700 dark:text-rose-300',
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
                key: 'counts',
                label: 'Ketersediaan Armada',
                valueText: `${Number(pool.armada_ready_count || 0)}/${Number(pool.armada_count || 0)}`,
                note: 'Ready / total armada',
                tone: 'text-slate-700 dark:text-slate-300',
            },
        ];
    };
</script>

{#if activeMode === 'view'}
    {#if poolDetail}
        {@const pool = poolDetail}
        {@const achievement = poolAchievement(pool)}
        {@const gross = poolGrossMargin(pool)}
        {@const net = poolNetMargin(pool)}
        {@const gap = poolGap(pool)}
        {@const detailCards = poolDetailCards(pool)}
        {@const routes = Array.isArray(pool.route_names) ? pool.route_names : []}
        {@const targets = monthlyTargetRows(pool)}

        <div class="space-y-4 rounded-2xl border border-border/70 bg-background/95 p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="truncate text-lg font-bold tracking-tight text-foreground">
                            {pool.name}
                        </h3>
                        <Badge
                            variant="secondary"
                            class="rounded-full px-2.5 py-1 text-[10px] uppercase tracking-wide"
                        >
                            {poolStatusLabel(pool.status)}
                        </Badge>
                        <Badge
                            variant="outline"
                            class="rounded-full px-2.5 py-1 text-[10px] uppercase tracking-wide"
                        >
                            {pool.code || 'Tanpa kode'}
                        </Badge>
                        <Badge
                            variant="outline"
                            class={`rounded-full px-2.5 py-1 text-[10px] uppercase tracking-wide ${poolProgressBadgeClass(achievement)}`}
                        >
                            {achievement.toFixed(1)}%
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {pool.region || 'Lainnya'} · {poolReadyLabel(pool)} · {Number(pool.driver_count || 0)} Driver · {Number(pool.armada_count || 0)} Armada
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {pool.phone || '-'} · {pool.address || 'Alamat belum diisi'}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    {#if canManagePools}
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-md"
                            onclick={() => openPoolEditor(pool)}
                        >
                            Edit
                        </Button>
                    {/if}
                    <Button
                        type="button"
                        variant="outline"
                        class="h-9 rounded-md"
                        onclick={goBackToData}
                    >
                        Kembali
                    </Button>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {#each detailCards as card (card.key)}
                    <article class="rounded-xl border border-border/70 bg-card/95 p-3 shadow-sm">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                            {card.label}
                        </p>
                        <p class={`mt-2 text-xl font-bold tabular-nums ${card.tone}`}>
                            {card.valueText}
                        </p>
                        <p class="mt-1 text-[11px] text-muted-foreground">
                            {card.note}
                        </p>
                    </article>
                {/each}
            </div>

            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                <section class="rounded-xl border border-border/70 bg-card/95 p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Informasi Cabang
                            </p>
                            <h4 class="mt-1 text-sm font-semibold text-foreground">
                                Detail identitas dan target
                            </h4>
                        </div>
                        <Badge
                            variant="secondary"
                            class="rounded-full px-2.5 py-1 text-[10px] uppercase tracking-wide"
                        >
                            Status {poolStatusLabel(pool.status)}
                        </Badge>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-border/60 bg-muted/20 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                Region
                            </p>
                            <p class="mt-1 text-sm font-semibold text-foreground">
                                {pool.region || '-'}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/60 bg-muted/20 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                Kode
                            </p>
                            <p class="mt-1 text-sm font-semibold text-foreground">
                                {pool.code || '-'}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/60 bg-muted/20 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                Kontak
                            </p>
                            <p class="mt-1 text-sm font-semibold text-foreground">
                                {pool.phone || '-'}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/60 bg-muted/20 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                Target Aktif
                            </p>
                            <p class="mt-1 text-sm font-semibold text-foreground">
                                {formatMonthLabel(pool.monthly_target_month)}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/60 bg-muted/20 p-3 sm:col-span-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                Alamat
                            </p>
                            <p class="mt-1 text-sm leading-6 text-foreground">
                                {pool.address || 'Alamat belum diisi'}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/60 bg-muted/20 p-3 sm:col-span-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                Catatan
                            </p>
                            <p class="mt-1 text-sm leading-6 text-foreground">
                                {pool.notes || 'Belum ada catatan'}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-border/70 bg-card/95 p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Rute Terkait
                            </p>
                            <h4 class="mt-1 text-sm font-semibold text-foreground">
                                {routes.length} rute aktif
                            </h4>
                        </div>
                        <Badge
                            variant="secondary"
                            class="rounded-full px-2.5 py-1 text-[10px] uppercase tracking-wide"
                        >
                            {poolReadyLabel(pool)}
                        </Badge>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        {#if routes.length === 0}
                            <span class="text-sm text-muted-foreground">
                                Belum ada rute terhubung.
                            </span>
                        {:else}
                            {#each routes as routeName (routeName)}
                                <Badge
                                    variant="outline"
                                    class="max-w-full truncate rounded-full px-2.5 py-1 text-[11px]"
                                >
                                    {routeName}
                                </Badge>
                            {/each}
                        {/if}
                    </div>

                    <div class="mt-5 rounded-xl border border-border/60 bg-muted/20 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                    Kinerja Finansial
                                </p>
                                <p class="mt-1 text-sm font-semibold text-foreground">
                                    Revenue {formatCurrency(Number(pool.revenue || 0))}
                                </p>
                            </div>
                            <span class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${poolProgressBadgeClass(achievement)}`}>
                                {achievement.toFixed(1)}%
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            Gross {formatCurrency(gross)} · Net {formatCurrency(net)} · Gap {formatCurrency(gap)} · Fixed Cost {formatCurrency(Number(pool.fixed_cost || 0))}
                        </p>
                    </div>
                </section>
            </div>

            <section class="rounded-xl border border-border/70 bg-card/95 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                            Target Bulanan
                        </p>
                        <h4 class="mt-1 text-sm font-semibold text-foreground">
                            {targets.length} bulan tersimpan
                        </h4>
                    </div>
                    <Badge
                        variant="secondary"
                        class="rounded-full px-2.5 py-1 text-[10px] uppercase tracking-wide"
                    >
                        {formatCurrency(poolTargetTotal(pool))}
                    </Badge>
                </div>

                <div class="table-container mt-4 max-h-80 overflow-auto pr-1 scrollbar-thin">
                    <table class="w-full text-left text-[11px]">
                        <thead class="sticky top-0 bg-background/95 text-[10px] uppercase tracking-[0.16em] text-muted-foreground">
                            <tr>
                                <th class="py-2 pr-2 font-semibold">Bulan</th>
                                <th class="py-2 pr-2 text-right font-semibold">Booking</th>
                                <th class="py-2 pr-2 text-right font-semibold">Bagasi</th>
                                <th class="py-2 pr-2 text-right font-semibold">Carter</th>
                                <th class="py-2 text-right font-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            {#if targets.length === 0}
                                <tr>
                                    <td colspan="5" class="py-3 text-muted-foreground">
                                        Belum ada target bulanan.
                                    </td>
                                </tr>
                            {:else}
                                {#each targets as target (target.target_month)}
                                    {@const total =
                                        Number(target.booking_target || 0) +
                                        Number(target.bagasi_target || 0) +
                                        Number(target.carter_target || 0)}
                                    <tr class="align-top transition-colors hover:bg-slate-50 dark:hover:bg-slate-900/30">
                                        <td class="py-2 pr-2">
                                            <div class="font-medium text-foreground">
                                                {formatMonthLabel(target.target_month)}
                                            </div>
                                            <div class="text-[10px] text-muted-foreground">
                                                {target.target_month}
                                            </div>
                                        </td>
                                        <td class="py-2 pr-2 text-right tabular-nums">
                                            {formatCurrency(Number(target.booking_target || 0))}
                                        </td>
                                        <td class="py-2 pr-2 text-right tabular-nums">
                                            {formatCurrency(Number(target.bagasi_target || 0))}
                                        </td>
                                        <td class="py-2 pr-2 text-right tabular-nums">
                                            {formatCurrency(Number(target.carter_target || 0))}
                                        </td>
                                        <td class="py-2 text-right font-semibold tabular-nums">
                                            {formatCurrency(total)}
                                        </td>
                                    </tr>
                                {/each}
                            {/if}
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    {:else}
        <div class="rounded-xl border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
            Data pool tidak ditemukan.
        </div>
        <div class="pt-3">
            <Button type="button" variant="outline" class="h-9 rounded-md" onclick={goBackToData}>
                Kembali
            </Button>
        </div>
    {/if}
{:else}
    <RevenueChartTable
        title="Pool"
        subtitle="Daftar pool operasional dengan ringkasan singkat dan detail lengkap di halaman detail."
        badges={[{ key: 'count', label: `${pools.length} cabang` }]}
        density="compact"
    >
        {#snippet controls()}
            <div class="grid gap-3 rounded-2xl border border-border/70 bg-background/95 p-3 shadow-sm xl:grid-cols-[minmax(0,1.2fr)_minmax(180px,0.62fr)_minmax(180px,0.62fr)_minmax(180px,0.62fr)_auto] xl:items-end">
                <div class="min-w-0">
                    <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                        Cari Pool
                    </span>
                    <Input
                        bind:value={poolSearch}
                        placeholder="Cari cabang, kode, alamat, atau catatan"
                        class="h-10"
                        on:keydown={(event) => {
                            if (event.key === 'Enter') {
                                void loadPools();
                            }
                        }}
                    />
                </div>

                <label class="space-y-1.5">
                    <span class="block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                        Tipe Filter
                    </span>
                    <select
                        class="h-10 w-full rounded-md border border-input bg-background px-2.5 text-[13px] outline-none transition focus:ring-2 focus:ring-ring/20"
                        bind:value={poolPerformanceFilter}
                    >
                        <option value="all">Semua</option>
                        <option value="tercapai">Tercapai</option>
                        <option value="kurang">Belum Tercapai</option>
                    </select>
                </label>

                <label class="space-y-1.5">
                    <span class="block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                        Wilayah/Region
                    </span>
                    <select
                        class="h-10 w-full rounded-md border border-input bg-background px-2.5 text-[13px] outline-none transition focus:ring-2 focus:ring-ring/20"
                        bind:value={poolRegionFilter}
                    >
                        <option value="all">Semua Wilayah</option>
                        {#each poolRegionOptions as region (region)}
                            <option value={region}>{region}</option>
                        {/each}
                    </select>
                </label>

                <label class="space-y-1.5">
                    <span class="block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                        Urutan Kinerja
                    </span>
                    <select
                        class="h-10 w-full rounded-md border border-input bg-background px-2.5 text-[13px] outline-none transition focus:ring-2 focus:ring-ring/20"
                        bind:value={poolSortOrder}
                    >
                        <option value="desc">Kinerja Tertinggi</option>
                        <option value="asc">Kinerja Terendah</option>
                    </select>
                </label>

                <div class="flex items-end">
                    <Button
                        type="button"
                        class="h-10 w-full rounded-md px-5 text-sm font-semibold"
                        onclick={() => void loadPools()}
                    >
                        Terapkan
                    </Button>
                </div>
            </div>
        {/snippet}

        {#snippet table()}
            <div class="grid gap-3 p-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 lg:hidden">
                {#each pools as row (row.id)}
                    {@const achievement = poolAchievement(row)}
                    {@const gross = poolGrossMargin(row)}
                    {@const net = poolNetMargin(row)}
                    {@const gap = poolGap(row)}
                    {@const routes = formatPoolRoutes(row)}
                    {@const barClass = poolProgressClass(achievement)}
                    {@const badgeClass = poolProgressBadgeClass(achievement)}
                    <article
                        class="group relative overflow-hidden rounded-2xl border border-border/70 bg-card/95 p-3 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-cyan-300/70 hover:shadow-md"
                        title={`Alamat: ${row.address || 'Belum diisi'} | Kontak: ${row.phone || 'Belum diisi'}`}
                    >
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <Building2 class="h-3.5 w-3.5 shrink-0 text-cyan-600" />
                                            <p class="truncate text-sm font-semibold text-foreground">
                                                {row.name}
                                            </p>
                                        </div>
                                        <p class="mt-0.5 truncate text-[11px] text-muted-foreground">
                                            {row.region || 'Lainnya'} · {row.code || 'Tanpa kode'}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 flex-col items-end gap-1">
                                        <span class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${row.status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700'}`}>
                                            {row.status === 'active' ? 'Aktif' : 'Nonaktif'}
                                        </span>
                                        <span class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${badgeClass}`}>
                                            {achievement.toFixed(0)}%
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-muted-foreground">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-muted/60 px-2 py-0.5">
                                        <Users class="h-3 w-3" />
                                        {row.driver_count || 0} Driver
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-muted/60 px-2 py-0.5">
                                        <MapPin class="h-3 w-3" />
                                        {row.armada_count || 0} Armada
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-muted/60 px-2 py-0.5">
                                        <Route class="h-3 w-3" />
                                        {row.route_ids?.length || 0} Rute
                                    </span>
                                    <span class={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 ${gap >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}>
                                        Gap {formatCurrency(gap)}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                            Revenue Cabang
                                        </p>
                                        <p class="truncate text-sm font-bold text-emerald-600">
                                            {formatCurrency(Number(row.revenue || 0))}
                                        </p>
                                        <p class="mt-0.5 truncate text-[11px] text-muted-foreground">
                                            Gross {formatCurrency(gross)} · BOP {formatCurrency(Number(row.bop || 0))} · Net {formatCurrency(net)}
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            Achievement
                                        </p>
                                        <p class="text-sm font-semibold text-foreground">
                                            {achievement.toFixed(1)}%
                                        </p>
                                    </div>
                                </div>
                                <p class="mt-2 text-[11px] text-muted-foreground">
                                    Target bulan ini {formatCurrency(poolTargetTotal(row))}
                                </p>
                                <div class="mt-2 space-y-1.5">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                        Rute
                                    </p>
                                    <div class="flex flex-wrap gap-1.5">
                                        {#each routes as routeName (routeName)}
                                            <span class="inline-flex max-w-full rounded-full border border-border/70 bg-background px-2 py-0.5 text-[10px] font-medium text-foreground">
                                                <span class="truncate">{routeName}</span>
                                            </span>
                                        {/each}
                                    </div>
                                </div>
                            </div>

                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button type="button" variant="ghost" size="icon" class="h-8 w-8 shrink-0 rounded-full border border-border/70">
                                        <MoreHorizontal class="h-4 w-4" />
                                        <span class="sr-only">Aksi cabang</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                    <DropdownMenuItem onclick={() => openPoolView(row)}>
                                        <Eye class="mr-2 h-3.5 w-3.5" />
                                        Lihat Detail
                                    </DropdownMenuItem>
                                    {#if canManagePools}
                                        <DropdownMenuItem
                                            onclick={() => {
                                                openPoolEditor(row);
                                            }}
                                        >
                                            <Pencil class="mr-2 h-3.5 w-3.5" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem onclick={() => void removeItem(`/api/admin/pools/${row.id}`, 'Pool deleted.')}>
                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                            Hapus
                                        </DropdownMenuItem>
                                    {/if}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class={`h-full rounded-full transition-[width] duration-300 ${barClass}`}
                                style={`width: ${Math.max(0, Math.min(100, achievement))}%`}
                            ></div>
                        </div>
                    </article>
                {/each}
            </div>

            <div class="hidden lg:block">
                <DataTable columns={poolsColumns} rows={pools} density="compact">
                    {#snippet row({ row })}
                        {@const gross = poolGrossMargin(row)}
                        {@const net = poolNetMargin(row)}
                        {@const achievement = poolAchievement(row)}
                        {@const gap = poolGap(row)}
                        {@const routes = formatPoolRoutes(row)}
                        {@const healthStatus = achievement >= 100 ? 'Tercapai' : 'Kurang'}

                        <td class="px-2.5 py-1.5 align-top">
                            <div class="truncate text-[11px] font-semibold leading-4 text-foreground">{row.name}</div>
                            <div class="mt-0.5 text-[10px] text-muted-foreground">{row.code || 'Tanpa kode'}</div>
                            <span class={`mt-1.5 inline-flex rounded-full border px-1.5 py-0.5 text-[9px] font-semibold ${row.status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700'}`}>{row.status === 'active' ? 'Aktif' : 'Nonaktif'}</span>
                        </td>

                        <td class="px-2.5 py-1.5 align-top">
                            <div class="flex max-w-[220px] flex-wrap gap-1.5">
                                {#each routes as routeName (routeName)}
                                    <span class="inline-flex max-w-full rounded-full border border-border/70 bg-muted/35 px-2 py-0.5 text-[10px] font-medium leading-4 text-foreground">
                                        <span class="truncate">{routeName}</span>
                                    </span>
                                {/each}
                            </div>
                        </td>

                        <td class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">{formatCurrency(Number(row.revenue || 0))}</td>
                        <td class={`px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums ${gap < 0 ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300'}`}>{formatCurrency(gap)}</td>
                        <td class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums text-amber-700 dark:text-amber-300">{formatCurrency(Number(row.bop || 0))}</td>
                        <td class="px-2.5 py-1.5 text-right text-[10px] font-semibold tabular-nums">{formatCurrency(gross)}</td>
                        <td class="px-2.5 py-1.5 text-right text-[10px] tabular-nums">{formatCurrency(Number(row.fixed_cost || 0))}</td>
                        <td class="px-2.5 py-1.5 text-right text-[10px] tabular-nums">{formatCurrency(Number(row.target_revenue || 0))}</td>
                        <td class="px-2.5 py-1.5 text-right text-[10px] tabular-nums">{achievement.toFixed(1)}%</td>
                        <td class="px-2.5 py-1.5 text-center"><span class={`inline-flex rounded-full border px-1.5 py-0.5 text-[9px] font-semibold ${healthStatus === 'Tercapai' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>{healthStatus}</span></td>
                    {/snippet}

                    {#snippet actions({ row })}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Aksi pool</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                <DropdownMenuItem onclick={() => openPoolView(row)}>
                                    <Eye class="mr-2 h-3.5 w-3.5" />
                                    Lihat Detail
                                </DropdownMenuItem>
                                {#if canManagePools}
                                    <DropdownMenuItem
                                        onclick={() => {
                                            openPoolEditor(row);
                                        }}
                                    >
                                        <Pencil class="mr-2 h-3.5 w-3.5" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem onclick={() => void removeItem(`/api/admin/pools/${row.id}`, 'Pool deleted.')}>
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
{/if}
