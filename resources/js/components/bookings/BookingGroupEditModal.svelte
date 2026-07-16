<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { formatCurrencyInput, parseCurrencyInput } from '@/lib/currency';

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
        groupEditSeatOptions = [],
        paymentOptions = [],
        loadingGroupEditSeats = false,
        savingGroupRowEdit = false,
        formError = '',
        formatGroupDateLabel,
        formatGroupTimeLabel,
        groupEditSeatLabel,
        groupEditSeatHelpText,
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
        groupEditSeatOptions?: string[];
        paymentOptions?: string[];
        loadingGroupEditSeats?: boolean;
        savingGroupRowEdit?: boolean;
        formError?: string;
        formatGroupDateLabel: (value: string) => string;
        formatGroupTimeLabel: (value: string) => string;
        groupEditSeatLabel: (seat: string) => string;
        groupEditSeatHelpText: () => string;
        closeGroupRowEdit: () => void;
        saveGroupRowEdit: () => void | Promise<void>;
    } = $props();
</script>

<div
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4"
    role="dialog"
    aria-modal="true"
>
    <div
        class="w-full max-w-2xl rounded-lg border border-border/80 bg-background p-4 shadow-lg md:p-5"
    >
        <div class="mb-3 flex items-start justify-between gap-3">
            <div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300"
                >
                    Edit Penumpang
                </p>
                <h3 class="text-base font-semibold">{groupEditName || '-'}</h3>
                <p class="text-xs text-muted-foreground">
                    {groupEditRoute} - {formatGroupDateLabel(groupEditDate)} -
                    {formatGroupTimeLabel(groupEditJam)} - Unit {groupEditUnit}
                </p>
            </div>
            <Button
                type="button"
                variant="outline"
                size="sm"
                class="rounded-full"
                onclick={closeGroupRowEdit}
                disabled={savingGroupRowEdit}
            >
                Tutup
            </Button>
        </div>

        <div class="grid gap-2.5 md:grid-cols-2">
            <div>
                <label
                    for="group-edit-seat"
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                >
                    Seat
                </label>
                <select
                    id="group-edit-seat"
                    class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                    bind:value={groupEditSeat}
                    disabled={loadingGroupEditSeats ||
                        savingGroupRowEdit ||
                        groupEditSeatOptions.length === 0}
                >
                    {#if loadingGroupEditSeats}
                        <option value={groupEditSeat}>
                            Memuat seat tersedia...
                        </option>
                    {:else if groupEditSeatOptions.length === 0}
                        <option value="">Seat tidak tersedia</option>
                    {:else}
                        {#each groupEditSeatOptions as seat (`group-edit-seat-opt-${seat}`)}
                            <option value={seat}>
                                {groupEditSeatLabel(seat)}
                            </option>
                        {/each}
                    {/if}
                </select>
                <p class="mt-1 text-[11px] text-muted-foreground">
                    {groupEditSeatHelpText()}
                </p>
            </div>
            <div>
                <label
                    for="group-edit-payment"
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                >
                    Pembayaran
                </label>
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
                <label
                    for="group-edit-name"
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                >
                    Nama
                </label>
                <Input
                    id="group-edit-name"
                    class="h-9 rounded-xl"
                    bind:value={groupEditName}
                />
            </div>
            <div>
                <label
                    for="group-edit-phone"
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                >
                    Telepon
                </label>
                <Input
                    id="group-edit-phone"
                    class="h-9 rounded-xl"
                    bind:value={groupEditPhone}
                />
            </div>
            <div class="md:col-span-2">
                <label
                    for="group-edit-pickup"
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                >
                    Pickup Point
                </label>
                <Input
                    id="group-edit-pickup"
                    class="h-9 rounded-xl"
                    bind:value={groupEditPickupPoint}
                />
            </div>
            <div>
                <label
                    for="group-edit-discount"
                    class="mb-1 block text-xs font-medium text-muted-foreground"
                >
                    Diskon
                </label>
                <Input
                    id="group-edit-discount"
                    class="h-9 rounded-xl"
                    type="text"
                    inputmode="numeric"
                    placeholder="Rp 0"
                    value={formatCurrencyInput(groupEditDiscount)}
                    oninput={(event) => {
                        groupEditDiscount = parseCurrencyInput(
                            (event.currentTarget as HTMLInputElement).value,
                        );
                    }}
                />
            </div>
            <div
                class="md:col-span-2 rounded-lg border border-border/70 bg-muted/10 px-3 py-2 text-[11px] text-muted-foreground"
            >
                Pilihan seat pada dropdown sudah mengikuti ketersediaan kursi
                untuk keberangkatan ini.
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <Button
                type="button"
                onclick={() => void saveGroupRowEdit()}
                disabled={savingGroupRowEdit ||
                    loadingGroupEditSeats ||
                    groupEditSeatOptions.length === 0}
            >
                {savingGroupRowEdit ? 'Menyimpan...' : 'Simpan Perubahan'}
            </Button>
            <Button
                type="button"
                variant="outline"
                onclick={closeGroupRowEdit}
                disabled={savingGroupRowEdit}
            >
                Batal
            </Button>
        </div>

        {#if formError}
            <p class="mt-2 text-sm text-destructive">{formError}</p>
        {/if}
    </div>
</div>
