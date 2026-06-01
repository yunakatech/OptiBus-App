<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';

    type LayoutCellType = 'seat' | 'empty' | 'driver';
    type LayoutPattern = '2-2' | '2-1' | '1-1' | '2-3' | '3-0' | '4-0' | 'sleep' | 'empty';
    type LayoutCell = {
        type: LayoutCellType;
        label: string;
        fixed?: boolean;
        hidden?: boolean;
        seatNumber?: number;
        colspan?: number;
        marker?: 'aisle' | 'slot';
        seatStyle?: 'standard' | 'sleeper';
    };
    type LayoutGrid = LayoutCell[][];
    type UnitRow = {
        id: number;
        nopol: string;
        category: string | null;
        kapasitas: number | null;
        layout: string | null;
    };

    let {
        layoutUnit = null,
        layoutCapacity = 0,
        layoutSeatCount,
        layoutOverCapacity = false,
        layoutRemainingSeats = 0,
        layoutTemplateSearch = $bindable(''),
        layoutTemplateChoice = $bindable(''),
        layoutTemplateOptions = [],
        layoutEditorMessage = '',
        seatLayoutDraft = [],
        layoutEditorBusy = false,
        normalizeUnitCategory,
        unitSeatCount,
        rowPatternLabel,
        rowSeatCount,
        applyPatternToAllRows,
        resetLayoutDraft,
        addLayoutRow,
        removeLayoutRow,
        cloneLayoutFromTemplate,
        replaceLayoutRowPattern,
        duplicateLayoutRow,
        addLayoutItem,
        removeLayoutItem,
        saveUnitLayout,
        goBackToData,
    }: {
        layoutUnit?: UnitRow | null;
        layoutCapacity?: number;
        layoutSeatCount: () => number;
        layoutOverCapacity?: boolean;
        layoutRemainingSeats?: number;
        layoutTemplateSearch?: string;
        layoutTemplateChoice?: string;
        layoutTemplateOptions?: UnitRow[];
        layoutEditorMessage?: string;
        seatLayoutDraft?: LayoutGrid;
        layoutEditorBusy?: boolean;
        normalizeUnitCategory: (value: string | null | undefined) => string;
        unitSeatCount: (layout: string | null) => number;
        rowPatternLabel: (row: LayoutCell[]) => string;
        rowSeatCount: (row: LayoutCell[]) => number;
        applyPatternToAllRows: (pattern: LayoutPattern) => void;
        resetLayoutDraft: () => void;
        addLayoutRow: (pattern: LayoutPattern) => void;
        removeLayoutRow: (rowIdx?: number) => void;
        cloneLayoutFromTemplate: (templateId: number) => void;
        replaceLayoutRowPattern: (rowIdx: number, pattern: LayoutPattern) => void;
        duplicateLayoutRow: (rowIdx: number) => void;
        addLayoutItem: (rowIdx: number, colIdx: number) => void;
        removeLayoutItem: (rowIdx: number, colIdx: number) => void;
        saveUnitLayout: () => void | Promise<void>;
        goBackToData: () => void;
    } = $props();

    let layoutFiltersExpanded = $state(false);
</script>

{#if layoutUnit}
    <div class="space-y-4">
        <div class="rounded-2xl border border-border/70 bg-gradient-to-br from-background via-background to-muted/30 p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">Layout Designer</p>
                    <div>
                        <h3 class="text-xl font-semibold">{layoutUnit.nopol}</h3>
                        <p class="text-sm text-muted-foreground">
                            {normalizeUnitCategory(layoutUnit.category)} · Kapasitas {layoutUnit.kapasitas ?? 0}
                        </p>
                    </div>
                </div>
                <div class="grid gap-2 sm:grid-cols-3 lg:min-w-[360px]">
                    <div class="rounded-xl border border-border/70 bg-background/80 p-3">
                        <p class="text-[11px] uppercase tracking-wide text-muted-foreground">Kapasitas</p>
                        <p class="mt-1 text-lg font-semibold">{layoutCapacity || 0} kursi</p>
                    </div>
                    <div class="rounded-xl border border-border/70 bg-background/80 p-3">
                        <p class="text-[11px] uppercase tracking-wide text-muted-foreground">Kursi Aktif</p>
                        <p class="mt-1 text-lg font-semibold">{layoutSeatCount()} kursi</p>
                    </div>
                    <div class={`rounded-xl border p-3 ${layoutOverCapacity ? 'border-red-300 bg-red-50 text-red-700' : 'border-border/70 bg-background/80'}`}>
                        <p class="text-[11px] uppercase tracking-wide text-muted-foreground">Sisa Slot</p>
                        <p class="mt-1 text-lg font-semibold">{layoutRemainingSeats} kursi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[340px_minmax(0,1fr)]">
            <div class="space-y-4">
                <div class="rounded-2xl border border-border/70 bg-background/90 p-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-semibold">Preset Cepat</h4>
                        <p class="text-xs text-muted-foreground">Gunakan template penuh untuk membentuk karakter bus lebih cepat, lalu detailkan dari preview.</p>
                    </div>
                    <div class="mt-3 grid gap-2">
                        <Button type="button" variant="outline" class="justify-start" onclick={() => applyPatternToAllRows('2-2')}>Template 2-2 · Bus Besar</Button>
                        <Button type="button" variant="outline" class="justify-start" onclick={() => applyPatternToAllRows('2-1')}>Template 2-1 · Executive</Button>
                        <Button type="button" variant="outline" class="justify-start" onclick={() => applyPatternToAllRows('2-3')}>Template 2-3 · High Capacity</Button>
                        <Button type="button" variant="outline" class="justify-start" onclick={() => applyPatternToAllRows('3-0')}>Template 3-0 · Triple Seat</Button>
                        <Button type="button" variant="outline" class="justify-start" onclick={() => applyPatternToAllRows('4-0')}>Template 4-0 · Tanpa Lorong</Button>
                        <Button type="button" variant="outline" class="justify-start" onclick={() => applyPatternToAllRows('1-1')}>Template 1-1 · Shuttle</Button>
                        <Button type="button" variant="outline" class="justify-start" onclick={() => applyPatternToAllRows('sleep')}>Template Sleep Seat</Button>
                        <Button type="button" variant="outline" class="justify-start" onclick={resetLayoutDraft}>Reset ke Default</Button>
                    </div>
                </div>

                <div class="rounded-2xl border border-border/70 bg-background/90 p-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-semibold">Tambah Baris</h4>
                        <p class="text-xs text-muted-foreground">Pilih pola baris yang ingin ditambahkan di bagian belakang layout.</p>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('2-2')}>+ Baris 2-2</Button>
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('2-1')}>+ Baris 2-1</Button>
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('2-3')}>+ Baris 2-3</Button>
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('3-0')}>+ Baris 3-0</Button>
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('4-0')}>+ Baris 4-0</Button>
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('1-1')}>+ Baris 1-1</Button>
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('sleep')}>+ Sleep Seat</Button>
                        <Button type="button" variant="outline" onclick={() => addLayoutRow('empty')}>+ Baris Kosong</Button>
                    </div>
                    <div class="mt-2">
                        <Button type="button" variant="ghost" class="w-full justify-start text-muted-foreground" onclick={() => removeLayoutRow()}>
                            Hapus baris terakhir
                        </Button>
                    </div>
                </div>

                <div class="rounded-2xl border border-border/70 bg-background/90 p-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-semibold">Duplikasi Dari Kategori Lain</h4>
                        <p class="text-xs text-muted-foreground">Pilih kategori/model lain jika ingin menjadikannya template awal tanpa membuka popup terpisah.</p>
                    </div>
                    <div class="mt-3 flex justify-end md:hidden">
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 rounded-lg text-xs"
                            onclick={() =>
                                (layoutFiltersExpanded =
                                    !layoutFiltersExpanded)}
                            aria-expanded={layoutFiltersExpanded}
                        >
                            {layoutFiltersExpanded
                                ? 'Sembunyikan Filter'
                                : 'Tampilkan Filter'}
                        </Button>
                    </div>
                    <div class={layoutFiltersExpanded
                        ? 'mt-3 space-y-2'
                        : 'mt-3 hidden space-y-2 md:block'}>
                        <Input placeholder="Cari kategori/model referensi" bind:value={layoutTemplateSearch} />
                        <select class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm" bind:value={layoutTemplateChoice}>
                            <option value="">Pilih template layout referensi</option>
                            {#each layoutTemplateOptions as unit (unit.id)}
                                <option value={unit.id}>
                                    {unit.nopol} · {normalizeUnitCategory(unit.category)} · {unitSeatCount(unit.layout)} kursi
                                </option>
                            {/each}
                        </select>
                        <Button
                            type="button"
                            variant="outline"
                            class="w-full justify-start"
                            onclick={() => cloneLayoutFromTemplate(Number(layoutTemplateChoice || 0))}
                            disabled={layoutTemplateChoice === ''}
                        >
                            Terapkan Layout Referensi
                        </Button>
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-border/70 bg-muted/20 p-4">
                    <h4 class="text-sm font-semibold">Cara Pakai</h4>
                    <ul class="mt-2 space-y-1.5 text-xs leading-relaxed text-muted-foreground">
                        <li>Klik slot kosong di preview untuk menjadikannya kursi.</li>
                        <li>Klik kursi aktif untuk mengubahnya kembali menjadi kosong.</li>
                        <li>Elemen lorong dibuat tetap agar sirkulasi bus lebih mudah dibaca.</li>
                        <li>Baris driver tetap terkunci di paling depan.</li>
                        <li>Jika kursi melebihi kapasitas, sistem akan menahan proses simpan.</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-border/70 bg-background/90 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h4 class="text-sm font-semibold">Preview Layout</h4>
                            <p class="text-xs text-muted-foreground">Preview ini memakai gaya kabin bus agar lorong, kursi reguler, dan sleep seat langsung terbaca.</p>
                        </div>
                        {#if layoutEditorMessage !== ''}
                            <p class={`text-xs ${layoutOverCapacity ? 'text-red-600' : 'text-muted-foreground'}`}>{layoutEditorMessage}</p>
                        {/if}
                    </div>

                    <div class="mt-4 overflow-hidden rounded-[28px] border border-dashed border-border/70 bg-[radial-gradient(circle_at_top,_rgba(15,23,42,0.04),_transparent_38%),linear-gradient(180deg,rgba(248,250,252,0.96),rgba(241,245,249,0.92))] p-4">
                        <div class="mb-3 flex items-center justify-between rounded-2xl border border-border/60 bg-background/85 px-4 py-2">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground">Depan Bus</span>
                            <span class="text-[11px] text-muted-foreground">Lorong tengah ditandai khusus</span>
                        </div>
                        <div class="space-y-3">
                            {#each seatLayoutDraft as row, rowIdx (`layout-preview-row-${rowIdx}`)}
                                <div class="rounded-2xl border border-border/60 bg-background/75 p-3 shadow-[0_8px_30px_rgba(15,23,42,0.04)]">
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                                {rowIdx === 0 ? 'Baris Driver' : `Baris ${rowIdx}`}
                                            </p>
                                            <p class="text-[11px] text-muted-foreground">
                                                {rowIdx === 0 ? 'Kabin depan' : `Pola ${rowPatternLabel(row)} · ${rowSeatCount(row)} kursi`}
                                            </p>
                                        </div>
                                        {#if rowIdx > 0}
                                            <div class="flex flex-wrap gap-1">
                                                <Button type="button" size="sm" variant="ghost" onclick={() => replaceLayoutRowPattern(rowIdx, '2-2')}>2-2</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => replaceLayoutRowPattern(rowIdx, '2-1')}>2-1</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => replaceLayoutRowPattern(rowIdx, '2-3')}>2-3</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => replaceLayoutRowPattern(rowIdx, '3-0')}>3-0</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => replaceLayoutRowPattern(rowIdx, '4-0')}>4-0</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => replaceLayoutRowPattern(rowIdx, '1-1')}>1-1</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => replaceLayoutRowPattern(rowIdx, 'sleep')}>Sleep</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => duplicateLayoutRow(rowIdx)}>Duplikat</Button>
                                                <Button type="button" size="sm" variant="ghost" onclick={() => removeLayoutRow(rowIdx)}>Hapus</Button>
                                            </div>
                                        {/if}
                                    </div>

                                    <div class="grid gap-2" style={`grid-template-columns: repeat(${row.length}, minmax(0, 1fr));`}>
                                        {#each row as cell, colIdx (`layout-preview-cell-${rowIdx}-${colIdx}-${cell.label}-${cell.type}`)}
                                            {#if !cell.hidden}
                                                {#if cell.type === 'driver'}
                                                    <div class="flex h-12 items-center justify-center rounded-2xl border border-zinc-800 bg-zinc-900 text-xs font-semibold uppercase tracking-wide text-white">Driver</div>
                                                {:else if cell.type === 'empty'}
                                                    {#if cell.marker === 'aisle'}
                                                        <div class="flex h-12 items-center justify-center rounded-2xl border border-dashed border-amber-300/80 bg-amber-50/80 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">
                                                            Lorong
                                                        </div>
                                                    {:else}
                                                        <button
                                                            type="button"
                                                            class={`flex h-12 items-center justify-center rounded-2xl border border-dashed text-xs font-medium transition hover:border-foreground/30 ${
                                                                cell.seatStyle === 'sleeper'
                                                                    ? 'border-sky-200 bg-sky-50/70 text-sky-700 hover:bg-sky-100'
                                                                    : 'border-border text-muted-foreground hover:bg-muted'
                                                            }`}
                                                            onclick={() => addLayoutItem(rowIdx, colIdx)}
                                                        >
                                                            {cell.seatStyle === 'sleeper' ? 'Slot Sleep' : 'Slot Kosong'}
                                                        </button>
                                                    {/if}
                                                {:else}
                                                    <button
                                                        type="button"
                                                        class={`flex items-center justify-center rounded-2xl border text-sm font-semibold transition ${
                                                            cell.seatStyle === 'sleeper'
                                                                ? 'h-16 border-sky-300 bg-sky-100 text-sky-800 hover:bg-sky-200'
                                                                : 'h-12 border-emerald-300 bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                        }`}
                                                        onclick={() => removeLayoutItem(rowIdx, colIdx)}
                                                    >
                                                        {cell.seatStyle === 'sleeper' ? `Sleep ${cell.label}` : `Kursi ${cell.label}`}
                                                    </button>
                                                {/if}
                                            {/if}
                                        {/each}
                                    </div>
                                </div>
                            {/each}
                        </div>
                        <div class="mt-3 flex items-center justify-between rounded-2xl border border-border/60 bg-background/85 px-4 py-2">
                            <span class="text-[11px] text-muted-foreground">Regular seat: hijau · Sleep seat: biru · Lorong: amber</span>
                            <span class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground">Belakang</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <LoadingButton type="button" onclick={() => void saveUnitLayout()} loading={layoutEditorBusy} loadingText="Menyimpan layout...">Simpan Layout</LoadingButton>
                    <Button type="button" variant="outline" onclick={goBackToData}>Kembali ke Data</Button>
                </div>
            </div>
        </div>
    </div>
{:else}
    <div class="rounded-xl border border-dashed border-border/70 bg-muted/20 p-4">
        <p class="text-sm text-muted-foreground">Data kategori armada untuk layout tidak ditemukan.</p>
        <Button type="button" variant="outline" class="mt-3" onclick={goBackToData}>Kembali ke Data</Button>
    </div>
{/if}
