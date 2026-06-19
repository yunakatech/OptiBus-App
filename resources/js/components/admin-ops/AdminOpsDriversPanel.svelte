<script lang="ts">
    import {
        ChevronLeft,
        ChevronRight,
        Download,
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
    import TerminalFilter from '@/components/terminal/TerminalFilter.svelte';

    type Pagination = {
        page: number;
        per_page: number;
        total: number;
        last_page: number;
    };

    type PoolOption = {
        id: number;
        name: string;
    };

    type DriverRow = {
        id: number;
        nama: string;
        phone: string | null;
        nopol: string | null;
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

    let {
        drivers = [],
        driverMeta = {
            page: 1,
            per_page: 20,
            total: 0,
            last_page: 1,
        },
        driverSearch = $bindable(''),
        driverPoolId = $bindable(0),
        driverPeriod = $bindable(
            `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}`,
        ),
        poolOptions = [],
        formatCurrency,
        driverGrossMargin,
        driverNetMargin,
        driverAchievement,
        driverStatus,
        loadDrivers,
        editDriver,
        removeDriver,
        canManage = true,
        canExport = false,
    }: {
        drivers?: DriverRow[];
        driverMeta?: Pagination;
        driverSearch?: string;
        driverPoolId?: number;
        driverPeriod?: string;
        poolOptions?: PoolOption[];
        formatCurrency: (value: number) => string;
        driverGrossMargin: (row: DriverRow) => number;
        driverNetMargin: (row: DriverRow) => number;
        driverAchievement: (row: DriverRow) => number;
        driverStatus: (row: DriverRow) => string;
        loadDrivers: (page?: number) => void | Promise<void>;
        editDriver: (row: DriverRow) => void;
        removeDriver: (id: number) => void | Promise<void>;
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

    const exportHref = $derived.by(() => {
        const params = new URLSearchParams();
        const query = driverSearch.trim();

        if (query !== '') {
            params.set('q', query);
        }

        params.set('pool_id', String(Number(driverPoolId || 0)));

        if (driverPeriod.trim() !== '') {
            params.set('period', driverPeriod.trim());
        }

        const suffix = params.toString();

        return `/api/admin/drivers/export${suffix !== '' ? `?${suffix}` : ''}`;
    });

    const filterPoolOptions = $derived([
        { id: 0, name: 'Semua Pool/Wilayah' },
        ...poolOptions,
    ]);

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
</script>

<div class="space-y-4 rounded-lg border border-border/70 bg-background/95 p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">Ringkasan Driver</p>
            <h3 class="text-lg font-bold tracking-tight md:text-xl">Produktivitas Driver</h3>
            <p class="max-w-3xl text-sm text-muted-foreground">
                Kartu driver ini menekankan pencapaian target dan ritase, agar manajemen bisa membaca performa dalam hitungan detik tanpa scroll horizontal.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <Badge variant="secondary" class="rounded-full px-3 py-1 text-[11px] uppercase tracking-wide">
                {driverMeta.total} driver
            </Badge>
            <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] uppercase tracking-wide">
                {selectedPeriodLabel()}
            </Badge>
            <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] uppercase tracking-wide">
                {pageSummary()}
            </Badge>
        </div>
    </div>

    <div class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_minmax(180px,220px)_minmax(180px,220px)_auto]">
        <div class="min-w-0">
            <TerminalFilter
                bind:query={driverSearch}
                placeholder="Cari nama, kontak, atau nopol"
                class="min-w-0"
                on:search={() => void loadDrivers(1)}
            />
        </div>

        <select
            class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            bind:value={driverPoolId}
            onchange={() => void loadDrivers(1)}
        >
            {#each filterPoolOptions as pool (pool.id)}
                <option value={pool.id}>{pool.name}</option>
            {/each}
        </select>

        <select
            class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
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
                class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.98] xl:w-auto"
            >
                <Download class="h-4 w-4" />
                Export Data Driver (Excel)
            </a>
        {/if}
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        {#if drivers.length === 0}
            <div class="col-span-full rounded-lg border border-dashed border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                Belum ada driver untuk filter yang dipilih.
            </div>
        {/if}

        {#each drivers as row (row.id)}
            {@const gross = driverGrossMargin(row)}
            {@const net = driverNetMargin(row)}
            {@const achievement = driverAchievement(row)}
            {@const status = driverStatus(row)}
            {@const tone = achievementTone(achievement)}
            <article class="group flex h-full flex-col rounded-lg border border-border/70 bg-card p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-300/70 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-lg font-bold tracking-tight text-foreground">{row.nama}</p>
                        <p class="mt-1 truncate text-xs text-muted-foreground">
                            {row.phone ?? '-'} | {row.nopol ?? '-'}
                        </p>
                        <Badge variant="secondary" class="mt-2 w-fit rounded-full px-2.5 py-0.5 text-[10px] uppercase tracking-wide">
                            {row.pool_name ?? 'Tanpa Pool'}
                        </Badge>
                    </div>

                    {#if canManage}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 rounded-full border border-border/70">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Aksi driver</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-44">
                                <DropdownMenuItem onclick={() => editDriver(row)}>
                                    <Pencil class="mr-2 h-3.5 w-3.5" />
                                    Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem onclick={() => void removeDriver(row.id)}>
                                    <Trash2 class="mr-2 h-3.5 w-3.5" />
                                    Hapus
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    {/if}
                </div>

                <div class="mt-4 rounded-lg border border-border/70 bg-muted/30 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">Net Margin</p>
                            <p class={`mt-2 text-2xl font-bold tabular-nums ${net >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'}`}>
                                {formatCurrency(net)}
                            </p>
                        </div>
                        <Badge variant="outline" class="rounded-full px-2.5 py-0.5 text-[10px] uppercase tracking-wide">
                            {Number(row.departure_count || 0)} Rit
                        </Badge>
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Gross: {formatCurrency(gross)} | BOP: {formatCurrency(Number(row.bop || 0))}
                    </p>
                </div>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-foreground">Achievement Target</span>
                        <span class={`font-semibold tabular-nums ${tone.text}`}>
                            {achievement.toFixed(1)}%
                        </span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-muted/70">
                        <div
                            class={`h-2 rounded-full ${tone.bar}`}
                            style={`width: ${Math.max(0, Math.min(100, achievement))}%`}
                        ></div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[11px] text-muted-foreground">{tone.label}</p>
                        <span class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${
                            status === 'Tercapai'
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
                                : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300'
                        }`}>
                            {status}
                        </span>
                    </div>
                </div>
            </article>
        {/each}
    </div>

    {#if driverMeta.last_page > 1}
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border/70 pt-3">
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
</div>
