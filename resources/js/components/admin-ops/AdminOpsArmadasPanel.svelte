<script lang="ts">
    import { Eye, MoreHorizontal, Pencil, Trash2 } from 'lucide-svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import DataTable from '@/components/terminal/DataTable.svelte';
    import TerminalFilter from '@/components/terminal/TerminalFilter.svelte';
    import EntityBadge from '@/components/terminal/EntityBadge.svelte';

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
        canManage = true,
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
        canManage?: boolean;
    } = $props();

    let armadaFiltersExpanded = $state(false);

    const columns = [
        { key: 'nopol', label: 'Nopol', width: 'w-[180px]', sticky: 'left', leftOffset: '0px' },
        { key: 'profil', label: 'Profil Armada', width: 'w-[260px]', sticky: 'left', leftOffset: '180px' },
        { key: 'gps', label: 'GPS & Tracking', width: 'w-[220px]', sticky: 'left', leftOffset: '440px' },
        { key: 'revenue', label: 'Revenue', align: 'right', numeric: true },
        { key: 'bop', label: 'BOP', align: 'right', numeric: true },
        { key: 'net', label: 'Net Margin', align: 'right', numeric: true },
        { key: 'target', label: 'Target', align: 'right', numeric: true },
        { key: 'achievement', label: 'Achievement', align: 'right', numeric: true },
        { key: 'status', label: 'Status', align: 'center' },
    ];
</script>

{#if activeMode === 'view'}
    {#if armadaDetail}
        {@const gross = armadaGrossMargin(armadaDetail)}
        {@const net = armadaNetMargin(armadaDetail)}
        {@const achievement = armadaAchievement(armadaDetail)}
        {@const status = armadaStatus(armadaDetail)}
        <div class="space-y-4 rounded-[28px] border border-border/70 bg-background/95 p-4 shadow-sm">
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
        <TerminalFilter bind:query={armadaSearch} placeholder="Cari merk / nopol / kategori / platform GPS" on:search={() => void loadArmadas()} />
    </div>
    <div class="overflow-hidden rounded-[28px] border border-border/70 bg-background/95 shadow-sm">
        <div class="flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(14,165,233,0.05),rgba(15,23,42,0.035))] px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground">Ringkasan Armada</p>
                <h3 class="mt-1 text-xl font-semibold tracking-tight">Performa unit berdasarkan nopol</h3>
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
                <article class="rounded-[24px] border border-border/80 bg-card/95 p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <EntityBadge code={row.nopol} class="text-sm" />
                            <p class="mt-0.5 truncate text-xs text-muted-foreground">{row.merk ?? 'Armada tanpa merek'}</p>
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
            <DataTable {columns} rows={armadas} class="min-w-full">
                {#snippet row({ row })}
                    {@const gross = armadaGrossMargin(row as ArmadaRow)}
                    {@const net = armadaNetMargin(row as ArmadaRow)}
                    {@const achievement = armadaAchievement(row as ArmadaRow)}
                    {@const status = armadaStatus(row as ArmadaRow)}

                    <td class="py-3 px-4 align-top">
                        <EntityBadge code={row.nopol} class="text-sm" />
                        <div class="mt-1 text-[11px] text-muted-foreground">{row.nomor_rangka ?? 'Nomor rangka belum tersedia'}</div>
                    </td>

                    <td class="py-3 px-4 align-top">
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

                    <td class="py-3 px-4 align-top">
                        <div class="font-medium text-foreground">{row.platform_gps ?? '-'}</div>
                        <div class="mt-1 break-all text-[11px] text-muted-foreground">{row.api_gps ?? 'API GPS belum diatur'}</div>
                    </td>

                    <td class="py-3 px-4 text-right font-semibold tabular-nums">{formatCurrency(Number(row.revenue || 0))}</td>
                    <td class="py-3 px-4 text-right tabular-nums">{formatCurrency(Number(row.bop || 0))}</td>
                    <td class="py-3 px-4 text-right tabular-nums">{formatCurrency(net)}</td>
                    <td class="py-3 px-4 text-right tabular-nums">{formatCurrency(Number(row.target_bulanan || 0))}</td>
                    <td class="py-3 px-4 text-right tabular-nums">{achievement.toFixed(1)}%</td>

                    <td class="py-3 px-4 text-center">
                        <span class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${status === 'Tercapai' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}`}>
                            {status}
                        </span>
                    </td>
                {/snippet}

                {#snippet actions({ row })}
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
                                <DropdownMenuItem onclick={() => openArmadaEditor(row as ArmadaRow)}>
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
                {/snippet}
            </DataTable>
        </div>
    </div>
{/if}
