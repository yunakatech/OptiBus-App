<script lang="ts">
    import { Download, Eye, MoreHorizontal, Pencil, Trash2 } from 'lucide-svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
    import TerminalFilter from '@/components/terminal/TerminalFilter.svelte';

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

    let {
        activeMode = 'data',
        armadaDetail = null,
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
        armadaDetail?: ArmadaRow | null;
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
                label: 'Kurang',
            };
        }

        if (achievement <= 80) {
            return {
                bar: 'bg-amber-400',
                text: 'text-amber-700 dark:text-amber-300',
                label: 'Mendekati',
            };
        }

        return {
            bar: 'bg-emerald-500',
            text: 'text-emerald-700 dark:text-emerald-300',
            label: 'Tercapai',
        };
    };

    const gpsTone = (active: boolean) =>
        active
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
            : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950/35 dark:text-rose-300';
</script>

{#if activeMode === 'view'}
    {#if armadaDetail}
        {@const gross = armadaGrossMargin(armadaDetail)}
        {@const net = armadaNetMargin(armadaDetail)}
        {@const achievement = armadaAchievement(armadaDetail)}
        {@const status = armadaStatus(armadaDetail)}
        <div class="space-y-4 rounded-lg border border-border/70 bg-background/95 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-lg font-bold tracking-tight">{armadaDetail.nopol}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <Badge variant="secondary" class="rounded-full px-2 py-0.5 text-[10px] uppercase tracking-wide">
                            {rowPoolName(armadaDetail)}
                        </Badge>
                        <span class="text-xs text-muted-foreground">
                            {armadaDetail.kategori ?? '-'} / {armadaDetail.ac_type}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">
                        No. rangka: {armadaDetail.nomor_rangka ?? '-'}
                    </p>
                </div>
                <Button type="button" variant="outline" class="h-9" onclick={goBackToData}>Kembali</Button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Revenue</p>
                    <p class="mt-2 text-sm font-semibold text-foreground">{formatCurrency(armadaDetail.revenue)}</p>
                </div>
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Gross Margin</p>
                    <p class="mt-2 text-sm font-semibold text-foreground">{formatCurrency(gross)}</p>
                </div>
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Net Margin</p>
                    <p class="mt-2 text-sm font-semibold text-foreground">{formatCurrency(net)}</p>
                </div>
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Achievement</p>
                    <p class="mt-2 text-sm font-semibold text-foreground">{achievement.toFixed(1)}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">{status}</p>
                </div>
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">BOP</p>
                    <p class="mt-2 text-sm font-semibold text-foreground">{formatCurrency(armadaDetail.bop)}</p>
                </div>
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Fixed Cost</p>
                    <p class="mt-2 text-sm font-semibold text-foreground">{formatCurrency(armadaDetail.fixed_cost)}</p>
                </div>
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Platform GPS</p>
                    <p class="mt-2 text-sm font-semibold text-foreground">{armadaDetail.platform_gps ?? '-'}</p>
                </div>
                <div class="rounded-lg border border-border/70 bg-muted/30 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">API GPS</p>
                    <p class="mt-2 break-all text-sm font-semibold text-foreground">{armadaDetail.api_gps ?? '-'}</p>
                </div>
            </div>
        </div>
    {:else}
        <p class="text-sm text-muted-foreground">Data armada tidak ditemukan.</p>
        <Button type="button" variant="outline" class="h-9" onclick={goBackToData}>Kembali</Button>
    {/if}
{:else}
    <div class="space-y-4 rounded-lg border border-border/70 bg-background/95 p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">Ringkasan Armada</p>
                <h3 class="text-lg font-bold tracking-tight md:text-xl">Performa Armada</h3>
                <p class="max-w-3xl text-sm text-muted-foreground">
                    Kartu ini dibuat untuk dibaca cepat oleh manajemen dan direksi, tanpa scroll horizontal, dengan fokus ke performa finansial, GPS, dan achievement per armada.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Badge variant="secondary" class="rounded-full px-3 py-1 text-[11px] uppercase tracking-wide">
                    {armadas.length} unit
                </Badge>
                <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] uppercase tracking-wide">
                    {selectedPeriodLabel()}
                </Badge>
            </div>
        </div>

        <div class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_minmax(180px,220px)_minmax(180px,220px)_auto]">
            <div class="min-w-0">
                <TerminalFilter
                    bind:query={armadaSearch}
                    placeholder="Cari nopol, driver, pool, atau GPS"
                    class="min-w-0"
                    on:search={() => void loadArmadas()}
                />
            </div>

            <select
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                bind:value={armadaPoolId}
                onchange={() => void loadArmadas()}
            >
                {#each armadaPoolOptions as pool (pool.id)}
                    <option value={pool.id}>{pool.name}</option>
                {/each}
            </select>

            <select
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                bind:value={armadaPeriod}
                onchange={() => void loadArmadas()}
            >
                {#each periodOptions as period (period.value)}
                    <option value={period.value}>{period.label}</option>
                {/each}
            </select>

            {#if canExport}
                <a
                    href={exportHref}
                    class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.98] xl:w-auto"
                >
                    <Download class="h-4 w-4" />
                    Export ke Excel
                </a>
            {/if}
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            {#if armadas.length === 0}
                <div class="col-span-full rounded-lg border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                    Belum ada armada untuk filter yang dipilih.
                </div>
            {/if}

            {#each armadas as row (row.id)}
                {@const gross = armadaGrossMargin(row)}
                {@const net = armadaNetMargin(row)}
                {@const achievement = armadaAchievement(row)}
                {@const status = armadaStatus(row)}
                {@const activeGps = gpsActive(row)}
                {@const achievementStyle = achievementTone(achievement)}
                <article class="group flex h-full flex-col rounded-lg border border-border/70 bg-card p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:scale-[1.01] hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-lg font-bold tracking-tight text-foreground">{row.nopol}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class={`inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-semibold ${gpsTone(activeGps)}`}>
                            <span class={`size-2 rounded-full ${activeGps ? 'bg-emerald-500' : 'bg-rose-500'}`}></span>
                            {activeGps ? 'GPS Aktif' : 'GPS Offline'}
                        </span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class={`rounded-full border px-2 py-0.5 text-[10px] font-semibold ${categoryTone(row.kategori)}`}>
                            {normalizeUnitCategory(row.kategori)}
                        </span>
                        <span class="rounded-full border border-border/70 bg-muted/25 px-2 py-0.5 text-[10px] font-semibold text-muted-foreground">
                            {row.ac_type}
                        </span>
                    </div>
                </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                    <MoreHorizontal class="h-4 w-4" />
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

                    <div class="mt-4 rounded-lg border border-border/70 bg-muted/30 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">Net Margin</p>
                        <p class={`mt-2 text-2xl font-bold tabular-nums ${net >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'}`}>
                            {formatCurrency(net)}
                        </p>
                        <p class="mt-2 text-xs text-muted-foreground">
                            Gross: {formatCurrency(gross)} | BOP: {formatCurrency(Number(row.bop || 0))}
                        </p>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-foreground">Achievement</span>
                            <span class={`font-semibold tabular-nums ${achievementStyle.text}`}>
                                {achievement.toFixed(1)}%
                            </span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-muted/70">
                            <div
                                class={`h-2 rounded-full ${achievementStyle.bar}`}
                                style={`width: ${Math.max(0, Math.min(100, achievement))}%`}
                            ></div>
                        </div>
                        <p class="text-[11px] text-muted-foreground">{achievementStyle.label}</p>
                        <p class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${
                            status === 'Tercapai'
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
                                : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300'
                        }`}>
                            {status}
                        </p>
                    </div>
                </article>
            {/each}
        </div>
    </div>
{/if}
