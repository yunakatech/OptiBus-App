<script lang="ts">
    import { Eye, MoreHorizontal, Pencil, Trash2 } from 'lucide-svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';

    type ViewMode = 'data' | 'form' | 'view' | 'layout';
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
    };

    let {
        activeMode = 'data',
        armadaDetail = null,
        armadas = [],
        armadaSearch = $bindable(''),
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
    }: {
        activeMode?: ViewMode;
        armadaDetail?: ArmadaRow | null;
        armadas?: ArmadaRow[];
        armadaSearch?: string;
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
    } = $props();

    let armadaFiltersExpanded = $state(false);
</script>

{#if activeMode === 'view'}
    {#if armadaDetail}
        {@const gross = armadaGrossMargin(armadaDetail)}
        {@const net = armadaNetMargin(armadaDetail)}
        {@const achievement = armadaAchievement(armadaDetail)}
        {@const status = armadaStatus(armadaDetail)}
        <div class="space-y-4 rounded-md border p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-base font-semibold">{armadaDetail.nopol}</p>
                    <p class="text-[11px] text-muted-foreground">No. rangka: {armadaDetail.nomor_rangka ?? '-'}</p>
                </div>
                <Button type="button" variant="outline" onclick={goBackToData}>Kembali</Button>
            </div>
            <div class="grid gap-3 text-sm md:grid-cols-2 xl:grid-cols-4">
                <div><p class="text-xs text-muted-foreground">Merk</p><p>{armadaDetail.merk ?? '-'}</p></div>
                <div><p class="text-xs text-muted-foreground">Kategori / AC</p><p>{armadaDetail.kategori ?? '-'} / {armadaDetail.ac_type}</p></div>
                <div><p class="text-xs text-muted-foreground">Revenue Charter</p><p>{formatCurrency(armadaDetail.charter_revenue)}</p></div>
                <div><p class="text-xs text-muted-foreground">Revenue Keberangkatan</p><p>{formatCurrency(armadaDetail.departure_revenue)}</p></div>
                <div><p class="text-xs text-muted-foreground">Revenue Bagasi</p><p>{formatCurrency(armadaDetail.luggage_revenue)}</p></div>
                <div><p class="text-xs text-muted-foreground">Total Revenue</p><p>{formatCurrency(armadaDetail.revenue)}</p></div>
                <div><p class="text-xs text-muted-foreground">Target Revenue</p><p>{formatCurrency(armadaDetail.target_bulanan)}</p></div>
                <div><p class="text-xs text-muted-foreground">BOP Charter</p><p>{formatCurrency(armadaDetail.charter_bop)}</p></div>
                <div><p class="text-xs text-muted-foreground">BOP Keberangkatan</p><p>{formatCurrency(armadaDetail.departure_bop)}</p></div>
                <div><p class="text-xs text-muted-foreground">Total BOP</p><p>{formatCurrency(armadaDetail.bop)}</p></div>
                <div><p class="text-xs text-muted-foreground">Gross Margin</p><p>{formatCurrency(gross)}</p></div>
                <div><p class="text-xs text-muted-foreground">Fixed Cost</p><p>{formatCurrency(armadaDetail.fixed_cost)}</p></div>
                <div><p class="text-xs text-muted-foreground">Net Margin</p><p>{formatCurrency(net)}</p></div>
                <div><p class="text-xs text-muted-foreground">Achievement</p><p>{achievement.toFixed(1)}%</p></div>
                <div><p class="text-xs text-muted-foreground">Status</p><p class={status === 'Tercapai' ? 'font-semibold text-emerald-600' : 'font-semibold text-amber-600'}>{status}</p></div>
                <div><p class="text-xs text-muted-foreground">Platform GPS</p><p>{armadaDetail.platform_gps ?? '-'}</p></div>
                <div class="md:col-span-2"><p class="text-xs text-muted-foreground">API GPS</p><p class="break-all">{armadaDetail.api_gps ?? '-'}</p></div>
            </div>
        </div>
    {:else}
        <p class="text-sm text-muted-foreground">Data armada tidak ditemukan.</p>
        <Button type="button" variant="outline" onclick={goBackToData}>Kembali</Button>
    {/if}
{:else}
    <div class="flex justify-end md:hidden">
        <Button
            type="button"
            size="sm"
            variant="outline"
            class="h-8 rounded-lg text-xs"
            onclick={() => (armadaFiltersExpanded = !armadaFiltersExpanded)}
            aria-expanded={armadaFiltersExpanded}
        >
            {armadaFiltersExpanded ? 'Sembunyikan Filter' : 'Tampilkan Filter'}
        </Button>
    </div>
    <div class={armadaFiltersExpanded ? 'flex flex-col gap-2 md:flex-row' : 'hidden md:flex md:gap-2'}>
        <Input placeholder="Cari merk / nopol / kategori / platform GPS" bind:value={armadaSearch} />
        <Button type="button" onclick={() => void loadArmadas()}>Search</Button>
    </div>
    <div class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm">
        <div class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(14,165,233,0.05),rgba(15,23,42,0.035))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground">Ringkasan Armada</p>
                <h3 class="mt-1 text-lg font-semibold">Performa unit berdasarkan nopol</h3>
                <p class="mt-1 max-w-3xl text-sm text-muted-foreground">
                    Tabel ini menekankan nopol, profil armada, dan hasil finansial bulanan agar unit mana yang sehat dan mana yang perlu perhatian bisa langsung terlihat.
                </p>
            </div>
            <Badge variant="secondary" class="w-fit rounded-full px-3 py-1 text-[11px] uppercase tracking-wide">
                {armadas.length} unit tercatat
            </Badge>
        </div>

        <div class="grid gap-3 p-3 md:hidden">
            {#if armadas.length === 0}
                <div class="rounded-2xl border border-dashed border-border/80 bg-muted/20 p-4 text-sm text-muted-foreground">
                    Belum ada armada tercatat.
                </div>
            {/if}
            {#each armadas as row (row.id)}
                {@const gross = armadaGrossMargin(row)}
                {@const net = armadaNetMargin(row)}
                {@const achievement = armadaAchievement(row)}
                {@const status = armadaStatus(row)}
                <article class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-foreground">{row.nopol}</p>
                            <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                {row.merk ?? 'Armada tanpa merek'}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <span
                                class={`rounded-full border px-2 py-0.5 text-[10px] font-semibold ${
                                    status === 'Tercapai'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300'
                                        : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/35 dark:text-amber-300'
                                }`}
                            >
                                {status}
                            </span>
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
                                    <DropdownMenuItem onclick={() => openArmadaEditor(row)}>
                                        <Pencil class="mr-2 h-3.5 w-3.5" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem onclick={() => void removeArmada(row.id)}>
                                        <Trash2 class="mr-2 h-3.5 w-3.5" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${categoryTone(row.kategori)}`}>
                            {normalizeUnitCategory(row.kategori)}
                        </span>
                        <span class="rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground">
                            {row.ac_type}
                        </span>
                        <span class="rounded-full border border-border/70 bg-muted/30 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground">
                            {row.warna ?? '-'} / {row.tahun ?? '-'}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-emerald-50/70 px-3 py-2 dark:bg-emerald-950/25">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Revenue</p>
                            <p class="mt-1 font-semibold text-emerald-800 dark:text-emerald-200">{formatCurrency(Number(row.revenue || 0))}</p>
                        </div>
                        <div class="rounded-xl bg-amber-50/80 px-3 py-2 dark:bg-amber-950/25">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">BOP</p>
                            <p class="mt-1 font-semibold text-amber-800 dark:text-amber-200">{formatCurrency(Number(row.bop || 0))}</p>
                        </div>
                        <div class="rounded-xl bg-sky-50/80 px-3 py-2 dark:bg-sky-950/25">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Fixed Cost</p>
                            <p class="mt-1 font-semibold text-sky-800 dark:text-sky-200">{formatCurrency(Number(row.fixed_cost || 0))}</p>
                        </div>
                        <div class="rounded-xl bg-muted/40 px-3 py-2">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Achievement</p>
                            <p class="mt-1 font-semibold text-foreground">{achievement.toFixed(1)}%</p>
                        </div>
                    </div>

                    <details class="mt-3 border-t border-border/70 pt-2 text-xs">
                        <summary class="cursor-pointer select-none py-1 font-semibold text-muted-foreground">
                            Detail teknis dan margin
                        </summary>
                        <div class="mt-2 grid gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Net Margin</p>
                                    <p class={`mt-1 font-semibold ${net >= 0 ? 'text-sky-800 dark:text-sky-200' : 'text-rose-700 dark:text-rose-300'}`}>{formatCurrency(net)}</p>
                                </div>
                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Gross</p>
                                    <p class="mt-1 font-semibold">{formatCurrency(gross)}</p>
                                </div>
                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">Target</p>
                                    <p class="mt-1 font-semibold">{formatCurrency(Number(row.target_bulanan || 0))}</p>
                                </div>
                                <div class="rounded-xl bg-muted/30 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">No. Rangka</p>
                                    <p class="mt-1 break-words font-semibold">{row.nomor_rangka ?? '-'}</p>
                                </div>
                            </div>
                            <div class="rounded-xl bg-muted/30 px-3 py-2">
                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">GPS</p>
                                <p class="mt-1 break-words font-semibold">{row.platform_gps ?? '-'}</p>
                                <p class="mt-1 break-all text-[11px] text-muted-foreground">{row.api_gps ?? 'API GPS belum diatur'}</p>
                            </div>
                        </div>
                    </details>
                </article>
            {/each}
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-[1980px] w-full border-separate border-spacing-0 text-sm">
                <thead>
                    <tr class="text-[10px] uppercase tracking-[0.24em] text-muted-foreground">
                        <th rowspan="2" class="sticky left-0 z-30 w-[180px] border-b border-r border-border/70 bg-background px-4 py-4 text-left font-semibold">Nopol</th>
                        <th rowspan="2" class="sticky left-[180px] z-30 w-[260px] border-b border-r border-border/70 bg-background px-4 py-4 text-left font-semibold">Profil Armada</th>
                        <th rowspan="2" class="sticky left-[440px] z-30 w-[220px] border-b border-r border-border/70 bg-background px-4 py-4 text-left font-semibold">GPS & Tracking</th>
                        <th colspan="4" class="border-b border-r border-border/70 bg-emerald-50/70 px-3 py-3 text-center font-semibold text-emerald-800">Revenue</th>
                        <th colspan="3" class="border-b border-r border-border/70 bg-amber-50/80 px-3 py-3 text-center font-semibold text-amber-800">BOP</th>
                        <th colspan="3" class="border-b border-r border-border/70 bg-sky-50/80 px-3 py-3 text-center font-semibold text-sky-800">Margin</th>
                        <th colspan="2" class="border-b border-r border-border/70 bg-slate-100/90 px-3 py-3 text-center font-semibold text-slate-700">Target</th>
                        <th rowspan="2" class="border-b border-r border-border/70 bg-slate-100/90 px-3 py-4 text-center font-semibold text-slate-700">Status</th>
                        <th rowspan="2" class="sticky right-0 z-30 w-[96px] border-b border-l border-border/70 bg-background px-3 py-4 text-center font-semibold">Aksi</th>
                    </tr>
                    <tr class="bg-muted/20 text-[11px] font-semibold text-foreground/80">
                        <th class="border-b border-border/70 px-3 py-3 text-right">Charter</th>
                        <th class="border-b border-border/70 px-3 py-3 text-right">Keberangkatan</th>
                        <th class="border-b border-border/70 px-3 py-3 text-right">Bagasi</th>
                        <th class="border-b border-r border-border/70 px-3 py-3 text-right">Total Revenue</th>
                        <th class="border-b border-border/70 px-3 py-3 text-right">Charter</th>
                        <th class="border-b border-border/70 px-3 py-3 text-right">Keberangkatan</th>
                        <th class="border-b border-r border-border/70 px-3 py-3 text-right">Total BOP</th>
                        <th class="border-b border-border/70 px-3 py-3 text-right">Gross</th>
                        <th class="border-b border-border/70 px-3 py-3 text-right">Fixed Cost</th>
                        <th class="border-b border-r border-border/70 px-3 py-3 text-right">Net Margin</th>
                        <th class="border-b border-border/70 px-3 py-3 text-right">Target Revenue</th>
                        <th class="border-b border-r border-border/70 px-3 py-3 text-right">Achievement</th>
                    </tr>
                </thead>
                <tbody>
                    {#each armadas as row (row.id)}
                        {@const gross = armadaGrossMargin(row)}
                        {@const net = armadaNetMargin(row)}
                        {@const achievement = armadaAchievement(row)}
                        {@const status = armadaStatus(row)}
                        <tr class="group transition hover:bg-muted/15">
                            <td class="sticky left-0 z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15">
                                <div class="font-semibold text-foreground">{row.nopol}</div>
                                <div class="mt-1 text-[11px] text-muted-foreground">{row.nomor_rangka ?? 'Nomor rangka belum tersedia'}</div>
                            </td>
                            <td class="sticky left-[180px] z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15">
                                <div class="font-medium text-foreground">{row.merk ?? 'Armada tanpa merek'}</div>
                                <div class="mt-1 text-xs text-muted-foreground">{row.warna ?? '-'} · {row.tahun ?? 0}</div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${categoryTone(row.kategori)}`}>
                                        {normalizeUnitCategory(row.kategori)}
                                    </span>
                                    <span class="rounded-full border border-border/70 bg-muted/25 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground">
                                        {row.ac_type}
                                    </span>
                                </div>
                            </td>
                            <td class="sticky left-[440px] z-20 border-b border-r border-border/60 bg-background px-4 py-4 align-top group-hover:bg-muted/15">
                                <div class="font-medium text-foreground">{row.platform_gps ?? '-'}</div>
                                <div class="mt-1 break-all text-[11px] text-muted-foreground">{row.api_gps ?? 'API GPS belum diatur'}</div>
                            </td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.charter_revenue || 0))}</td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.departure_revenue || 0))}</td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.luggage_revenue || 0))}</td>
                            <td class="border-b border-r border-border/60 bg-emerald-50/45 px-3 py-4 text-right">
                                <div class="text-sm font-semibold text-emerald-800 tabular-nums">{formatCurrency(Number(row.revenue || 0))}</div>
                                <div class="mt-1 text-[10px] uppercase tracking-wide text-emerald-700/80">Total</div>
                            </td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.charter_bop || 0))}</td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.departure_bop || 0))}</td>
                            <td class="border-b border-r border-border/60 bg-amber-50/50 px-3 py-4 text-right">
                                <div class="text-sm font-semibold text-amber-800 tabular-nums">{formatCurrency(Number(row.bop || 0))}</div>
                                <div class="mt-1 text-[10px] uppercase tracking-wide text-amber-700/80">Total</div>
                            </td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(gross)}</td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.fixed_cost || 0))}</td>
                            <td class="border-b border-r border-border/60 px-3 py-4 text-right">
                                <div class={`text-sm font-semibold tabular-nums ${net >= 0 ? 'text-sky-800' : 'text-rose-700'}`}>{formatCurrency(net)}</div>
                                <div class={`mt-1 text-[10px] uppercase tracking-wide ${net >= 0 ? 'text-sky-700/80' : 'text-rose-600/80'}`}>
                                    {net >= 0 ? 'Positif' : 'Minus'}
                                </div>
                            </td>
                            <td class="border-b border-border/60 px-3 py-4 text-right text-xs tabular-nums">{formatCurrency(Number(row.target_bulanan || 0))}</td>
                            <td class="border-b border-r border-border/60 px-3 py-4 text-right">
                                <div class="text-sm font-semibold tabular-nums">{achievement.toFixed(1)}%</div>
                                <div class="mt-1 text-[10px] uppercase tracking-wide text-muted-foreground">Pencapaian</div>
                            </td>
                            <td class="border-b border-r border-border/60 px-3 py-4 text-center">
                                <span
                                    class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${
                                        status === 'Tercapai'
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-amber-200 bg-amber-50 text-amber-700'
                                    }`}
                                >
                                    {status}
                                </span>
                            </td>
                            <td class="sticky right-0 z-20 border-b border-l border-border/60 bg-background px-3 py-4 text-center group-hover:bg-muted/15">
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
                                        <DropdownMenuItem onclick={() => openArmadaEditor(row)}>
                                            <Pencil class="mr-2 h-3.5 w-3.5" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem onclick={() => void removeArmada(row.id)}>
                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                            Hapus
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    </div>
{/if}
