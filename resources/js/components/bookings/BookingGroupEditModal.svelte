<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        formatCurrencyInput,
        parseCurrencyInput,
    } from '@/lib/currency';

    type LayoutCell = {
        kind: 'seat' | 'driver' | 'empty';
        seat?: string;
    };

    let {
        groupEditSeat = $bindable(''),
        groupEditPayment = $bindable('Belum Lunas'),
        groupEditName = $bindable(''),
        groupEditPhone = $bindable(''),
        groupEditPickupPoint = $bindable(''),
        groupEditDiscount = $bindable<string | number>(''),
        groupEditRoute = '',
        groupEditDate = '',
        groupEditJam = '',
        groupEditUnit = 1,
        groupEditCurrentSeat = '',
        groupEditSeatOptions = [],
        groupEditBookedSeatTokens = [],
        groupEditLayoutRows = [],
        paymentOptions = [],
        loadingGroupEditSeats = false,
        savingGroupRowEdit = false,
        formError = '',
        formatGroupDateLabel,
        formatGroupTimeLabel,
        groupEditSeatLabel,
        groupEditSeatHelpText,
        groupEditSeatStatus,
        groupEditSeatButtonClass,
        selectGroupEditSeat,
        normalizeSeatToken,
        closeGroupRowEdit,
        saveGroupRowEdit,
    }: {
        groupEditSeat?: string;
        groupEditPayment?: string;
        groupEditName?: string;
        groupEditPhone?: string;
        groupEditPickupPoint?: string;
        groupEditDiscount?: string | number;
        groupEditRoute?: string;
        groupEditDate?: string;
        groupEditJam?: string;
        groupEditUnit?: number;
        groupEditCurrentSeat?: string;
        groupEditSeatOptions?: string[];
        groupEditBookedSeatTokens?: string[];
        groupEditLayoutRows?: LayoutCell[][];
        paymentOptions?: string[];
        loadingGroupEditSeats?: boolean;
        savingGroupRowEdit?: boolean;
        formError?: string;
        formatGroupDateLabel: (value: string) => string;
        formatGroupTimeLabel: (value: string) => string;
        groupEditSeatLabel: (seat: string) => string;
        groupEditSeatHelpText: () => string;
        groupEditSeatStatus: (seat: string) => string;
        groupEditSeatButtonClass: (seat: string) => string;
        selectGroupEditSeat: (seat: string) => void;
        normalizeSeatToken: (seat: string) => string;
        closeGroupRowEdit: () => void;
        saveGroupRowEdit: () => void | Promise<void>;
    } = $props();
</script>

<div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true">
    <div class="w-full max-w-4xl rounded-2xl border border-border/80 bg-background p-4 shadow-2xl md:p-5">
        <div class="mb-3 flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-semibold tracking-wide text-cyan-700 uppercase dark:text-cyan-300">Edit Penumpang</p>
                <h3 class="text-base font-semibold">{groupEditName || '-'}</h3>
                <p class="text-xs text-muted-foreground">
                    {groupEditRoute} • {formatGroupDateLabel(groupEditDate)} • {formatGroupTimeLabel(groupEditJam)} • Unit {groupEditUnit}
                </p>
            </div>
            <Button type="button" variant="outline" size="sm" class="rounded-full" onclick={closeGroupRowEdit} disabled={savingGroupRowEdit}>
                Tutup
            </Button>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.1fr)_minmax(300px,0.9fr)]">
            <div class="grid gap-2.5 md:grid-cols-2">
                <div>
                    <label for="group-edit-seat" class="mb-1 block text-xs font-medium text-muted-foreground">Seat</label>
                    <select
                        id="group-edit-seat"
                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                        bind:value={groupEditSeat}
                        disabled={loadingGroupEditSeats || savingGroupRowEdit || groupEditSeatOptions.length === 0}
                    >
                        {#if loadingGroupEditSeats}
                            <option value={groupEditSeat}>Memuat seat tersedia...</option>
                        {:else if groupEditSeatOptions.length === 0}
                            <option value="">Seat tidak tersedia</option>
                        {:else}
                            {#each groupEditSeatOptions as seat (`group-edit-seat-opt-${seat}`)}
                                <option value={seat}>{groupEditSeatLabel(seat)}</option>
                            {/each}
                        {/if}
                    </select>
                    <p class="mt-1 text-[11px] text-muted-foreground">{groupEditSeatHelpText()}</p>
                </div>
                <div>
                    <label for="group-edit-payment" class="mb-1 block text-xs font-medium text-muted-foreground">Pembayaran</label>
                    <select
                        id="group-edit-payment"
                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                        bind:value={groupEditPayment}
                    >
                        {#each paymentOptions as option (option)}
                            <option value={option}>{option}</option>
                        {/each}
                    </select>
                </div>
                <div>
                    <label for="group-edit-name" class="mb-1 block text-xs font-medium text-muted-foreground">Nama</label>
                    <Input id="group-edit-name" class="h-9 rounded-xl" bind:value={groupEditName} />
                </div>
                <div>
                    <label for="group-edit-phone" class="mb-1 block text-xs font-medium text-muted-foreground">Telepon</label>
                    <Input id="group-edit-phone" class="h-9 rounded-xl" bind:value={groupEditPhone} />
                </div>
                <div class="md:col-span-2">
                    <label for="group-edit-pickup" class="mb-1 block text-xs font-medium text-muted-foreground">Pickup Point</label>
                    <Input id="group-edit-pickup" class="h-9 rounded-xl" bind:value={groupEditPickupPoint} />
                </div>
                <div>
                    <label for="group-edit-discount" class="mb-1 block text-xs font-medium text-muted-foreground">Diskon</label>
                    <Input
                        id="group-edit-discount"
                        class="h-9 rounded-xl"
                        type="text"
                        inputmode="numeric"
                        placeholder="Rp 0"
                        value={formatCurrencyInput(groupEditDiscount)}
                        oninput={(event) => {
                            groupEditDiscount = parseCurrencyInput((event.currentTarget as HTMLInputElement).value);
                        }}
                    />
                </div>
            </div>

            <div class="rounded-2xl border border-cyan-200/60 bg-linear-to-br from-cyan-50/70 via-background to-background p-3 dark:border-cyan-500/20 dark:from-cyan-950/20">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700 dark:text-cyan-300">Preview Layout</p>
                        <p class="mt-1 text-[11px] text-muted-foreground">Klik seat kosong untuk memilih kursi langsung dari layout.</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[10px]">
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-2 py-0.5 text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-950/30 dark:text-cyan-200">Saat ini</span>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/30 dark:text-emerald-200">Kosong</span>
                        <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-rose-700 dark:border-rose-500/30 dark:bg-rose-950/30 dark:text-rose-200">Terisi</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2 text-[11px]">
                    <div class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Dipilih</p>
                        <p class="mt-1 font-semibold text-foreground">{groupEditSeat || '-'}</p>
                    </div>
                    <div class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Kosong</p>
                        <p class="mt-1 font-semibold text-foreground">{groupEditSeatOptions.filter((seat) => normalizeSeatToken(seat) !== normalizeSeatToken(groupEditCurrentSeat)).length}</p>
                    </div>
                    <div class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Terisi</p>
                        <p class="mt-1 font-semibold text-foreground">{groupEditBookedSeatTokens.length}</p>
                    </div>
                </div>

                <div class="mt-3 rounded-2xl border border-border/70 bg-background/85 p-3">
                    {#if groupEditLayoutRows.length === 0}
                        <div class="flex h-36 items-center justify-center rounded-xl border border-dashed border-border/70 bg-muted/10 px-4 text-center text-[11px] text-muted-foreground">
                            Preview layout belum tersedia.
                        </div>
                    {:else}
                        <div class="space-y-2">
                            {#each groupEditLayoutRows as layoutRow, rowIndex (`group-edit-layout-row-${rowIndex}`)}
                                <div class={`grid gap-2 ${layoutRow.length >= 4 ? 'grid-cols-4' : 'grid-cols-3'}`}>
                                    {#each layoutRow as cell, colIndex (`group-edit-layout-cell-${rowIndex}-${colIndex}`)}
                                        {#if cell.kind === 'seat' && cell.seat}
                                            <button
                                                type="button"
                                                class={`h-14 rounded-xl border text-center transition-all ${groupEditSeatButtonClass(cell.seat)}`}
                                                onclick={() => selectGroupEditSeat(cell.seat ?? '')}
                                                disabled={groupEditSeatStatus(cell.seat) === 'taken' || savingGroupRowEdit || loadingGroupEditSeats}
                                            >
                                                <span class="block text-sm font-semibold leading-none">{cell.seat}</span>
                                                <span class="mt-1 block text-[9px] uppercase tracking-[0.14em] opacity-80">
                                                    {groupEditSeatStatus(cell.seat) === 'taken'
                                                        ? 'Terisi'
                                                        : groupEditSeatStatus(cell.seat) === 'current'
                                                            ? 'Saat ini'
                                                            : normalizeSeatToken(groupEditSeat) === normalizeSeatToken(cell.seat)
                                                                ? 'Dipilih'
                                                                : 'Kosong'}
                                                </span>
                                            </button>
                                        {:else if cell.kind === 'driver'}
                                            <div class="flex h-14 items-center justify-center rounded-xl border border-dashed border-border/70 bg-muted/15 text-[10px] font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                                                Driver
                                            </div>
                                        {:else}
                                            <div class="h-14 rounded-xl border border-dashed border-border/50 bg-muted/10"></div>
                                        {/if}
                                    {/each}
                                </div>
                            {/each}
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <Button
                type="button"
                onclick={() => void saveGroupRowEdit()}
                disabled={savingGroupRowEdit || loadingGroupEditSeats || groupEditSeatOptions.length === 0}
            >
                {savingGroupRowEdit ? 'Menyimpan...' : 'Simpan Perubahan'}
            </Button>
            <Button type="button" variant="outline" onclick={closeGroupRowEdit} disabled={savingGroupRowEdit}>
                Batal
            </Button>
        </div>

        {#if formError}
            <p class="mt-2 text-sm text-destructive">{formError}</p>
        {/if}
    </div>
</div>
