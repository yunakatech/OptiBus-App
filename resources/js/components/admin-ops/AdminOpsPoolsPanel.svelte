<script lang="ts">
    import { Building2, MapPin, MoreHorizontal, Pencil, Route, Trash2, Users } from 'lucide-svelte';
    import { Button } from '@/components/ui/button';
    import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
    import DataTable from '@/components/terminal/DataTable.svelte';
    import TerminalFilter from '@/components/terminal/TerminalFilter.svelte';
    import RevenueChartTable from './RevenueChartTable.svelte';

    type PoolRow = any;

    let {
        pools = [],
        poolsColumns = [],
        poolSearch = $bindable(''),
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
        formatPoolRoutes,
        loadPools,
        openPoolEditor,
        removeItem,
        canManagePools = true,
    }: {
        pools?: PoolRow[];
        poolsColumns?: any[];
        poolSearch?: string;
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
        formatPoolRoutes: (row: PoolRow) => string;
        loadPools: () => void | Promise<void>;
        openPoolEditor: (row: PoolRow) => void;
        removeItem: (url: string, message: string) => void | Promise<void>;
        canManagePools?: boolean;
    } = $props();
</script>

<RevenueChartTable
    title="Pool"
    subtitle="Daftar pool operasional dengan pencarian dan filter wilayah."
    badges={[{ key: 'count', label: `${pools.length} cabang` }]}
>
    {#snippet controls()}
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1.35fr)_minmax(220px,0.55fr)_minmax(220px,0.55fr)]">
            <div class="min-w-0 lg:col-span-1">
                <TerminalFilter
                    bind:query={poolSearch}
                    placeholder="Cari cabang, kode, alamat, atau catatan"
                    on:search={() => void loadPools()}
                />
            </div>
            <label class="space-y-1.5">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                    Wilayah/Region
                </span>
                <select
                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none transition focus:ring-2 focus:ring-ring/20"
                    bind:value={poolRegionFilter}
                    onchange={() => void loadPools()}
                >
                    <option value="all">Semua Wilayah</option>
                    {#each poolRegionOptions as region (region)}
                        <option value={region}>{region}</option>
                    {/each}
                </select>
            </label>
            <label class="space-y-1.5">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                    Urutan Kinerja
                </span>
                <select
                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm outline-none transition focus:ring-2 focus:ring-ring/20"
                    bind:value={poolSortOrder}
                    onchange={() => void loadPools()}
                >
                    <option value="desc">Kinerja Tertinggi</option>
                    <option value="asc">Kinerja Terendah</option>
                </select>
            </label>
        </div>
    {/snippet}

    {#snippet table()}
        <div class="grid gap-3 p-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 lg:hidden">
            {#each pools as row (row.id)}
                {@const achievement = poolAchievement(row)}
                {@const gross = poolGrossMargin(row)}
                {@const net = poolNetMargin(row)}
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
                                    <MapPin class="h-3 w-3" />
                                    {poolReadyLabel(row)}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-muted/60 px-2 py-0.5">
                                    <Users class="h-3 w-3" />
                                    {row.driver_count || 0} Driver
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-muted/60 px-2 py-0.5">
                                    <Route class="h-3 w-3" />
                                    {row.route_ids?.length || 0} Rute
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
                                Target bulan ini {formatCurrency(poolTargetTotal(row))}{row.monthly_target_source === 'monthly' ? '' : ' · pakai target lama'}
                            </p>
                        </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 shrink-0 rounded-full border border-border/70">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Aksi cabang</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
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
            <DataTable columns={poolsColumns} rows={pools} class="min-w-[1780px]">
                {#snippet row({ row })}
                    {@const gross = poolGrossMargin(row)}
                    {@const net = poolNetMargin(row)}
                    {@const achievement = poolAchievement(row)}
                    {@const healthStatus = achievement >= 100 ? 'Tercapai' : 'Kurang'}

                    <td class="px-3 py-2 align-top">
                        <div class="truncate text-[11px] font-semibold text-foreground">{row.name}</div>
                        <div class="mt-0.5 text-[10px] text-muted-foreground">{row.code || 'Tanpa kode'}</div>
                        <span class={`mt-2 inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${row.status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700'}`}>{row.status === 'active' ? 'Aktif' : 'Nonaktif'}</span>
                    </td>

                    <td class="px-3 py-2 align-top"><div class="line-clamp-2 text-[11px] leading-4 text-foreground">{formatPoolRoutes(row)}</div></td>

                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.charter_revenue || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.departure_revenue || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.luggage_revenue || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(Number(row.revenue || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.charter_bop || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.departure_bop || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(Number(row.bop || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(gross)}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.fixed_cost || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] font-semibold tabular-nums">{formatCurrency(net)}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{formatCurrency(Number(row.target_revenue || 0))}</td>
                    <td class="px-3 py-2 text-right text-[11px] tabular-nums">{achievement.toFixed(1)}%</td>
                    <td class="px-3 py-2 text-center"><span class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${healthStatus === 'Tercapai' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>{healthStatus}</span></td>
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
                            <DropdownMenuItem onclick={() => {
                                openPoolEditor(row);
                            }}>
                                <Pencil class="mr-2 h-3.5 w-3.5" />
                                Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem onclick={() => void removeItem(`/api/admin/pools/${row.id}`, 'Pool deleted.')}>
                                <Trash2 class="mr-2 h-3.5 w-3.5" />
                                Hapus
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                {/snippet}
            </DataTable>
        </div>
    {/snippet}
</RevenueChartTable>
