<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';

    type LayoutCell = {
        kind: 'seat' | 'driver' | 'empty';
        seat?: string;
    };

    type ScheduleItem = {
        jam: string;
    };

    type UnitOption = {
        value: number;
        label: string;
    };

    let {
        groupRescheduleDate = $bindable(''),
        groupRescheduleJam = $bindable(''),
        groupRescheduleUnit = $bindable(1),
        groupRescheduleSeat = $bindable(''),
        groupRescheduleBookingName = '',
        groupRescheduleRoute = '',
        groupRescheduleCurrentDate = '',
        groupRescheduleCurrentJam = '',
        groupRescheduleCurrentUnit = 1,
        groupRescheduleCurrentSeat = '',
        groupRescheduleSchedules = [],
        groupRescheduleSeatOptions = [],
        groupRescheduleBookedSeatTokens = [],
        groupRescheduleLayoutRows = [],
        loadingGroupRescheduleSchedules = false,
        loadingGroupRescheduleSeats = false,
        savingGroupReschedule = false,
        formError = '',
        formatGroupDateLabel,
        formatGroupTimeLabel,
        groupRescheduleUnitOptions,
        groupRescheduleSeatLabel,
        groupRescheduleSeatHelpText,
        groupRescheduleSeatStatus,
        groupRescheduleSeatButtonClass,
        selectGroupRescheduleSeat,
        normalizeSeatToken,
        onGroupRescheduleDateChange,
        onGroupRescheduleScheduleChange,
        loadGroupRescheduleSeatOptions,
        closeGroupRescheduleModal,
        saveGroupReschedule,
    }: {
        groupRescheduleDate?: string;
        groupRescheduleJam?: string;
        groupRescheduleUnit?: number;
        groupRescheduleSeat?: string;
        groupRescheduleBookingName?: string;
        groupRescheduleRoute?: string;
        groupRescheduleCurrentDate?: string;
        groupRescheduleCurrentJam?: string;
        groupRescheduleCurrentUnit?: number;
        groupRescheduleCurrentSeat?: string;
        groupRescheduleSchedules?: ScheduleItem[];
        groupRescheduleSeatOptions?: string[];
        groupRescheduleBookedSeatTokens?: string[];
        groupRescheduleLayoutRows?: LayoutCell[][];
        loadingGroupRescheduleSchedules?: boolean;
        loadingGroupRescheduleSeats?: boolean;
        savingGroupReschedule?: boolean;
        formError?: string;
        formatGroupDateLabel: (value: string) => string;
        formatGroupTimeLabel: (value: string) => string;
        groupRescheduleUnitOptions: () => UnitOption[];
        groupRescheduleSeatLabel: (seat: string) => string;
        groupRescheduleSeatHelpText: () => string;
        groupRescheduleSeatStatus: (seat: string) => string;
        groupRescheduleSeatButtonClass: (seat: string) => string;
        selectGroupRescheduleSeat: (seat: string) => void;
        normalizeSeatToken: (seat: string) => string;
        onGroupRescheduleDateChange: () => void | Promise<void>;
        onGroupRescheduleScheduleChange: () => void | Promise<void>;
        loadGroupRescheduleSeatOptions: () => void | Promise<void>;
        closeGroupRescheduleModal: () => void;
        saveGroupReschedule: () => void | Promise<void>;
    } = $props();
</script>

<div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true">
    <div class="w-full max-w-4xl rounded-2xl border border-border/80 bg-background p-4 shadow-2xl md:p-5">
        <div class="mb-3 flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-semibold tracking-wide text-cyan-700 uppercase dark:text-cyan-300">Reschedule Penumpang</p>
                <h3 class="text-base font-semibold">{groupRescheduleBookingName || '-'}</h3>
                <p class="text-xs text-muted-foreground">{groupRescheduleRoute} • Seat {groupRescheduleCurrentSeat || '-'}</p>
            </div>
            <Button type="button" variant="outline" size="sm" class="rounded-full" onclick={closeGroupRescheduleModal} disabled={savingGroupReschedule}>
                Tutup
            </Button>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.9fr)]">
            <div class="space-y-4">
                <div class="grid gap-3 rounded-2xl border border-border/70 bg-background/80 p-3 md:grid-cols-2">
                    <div class="rounded-xl border border-cyan-200/60 bg-cyan-50/70 px-3 py-2 dark:border-cyan-500/20 dark:bg-cyan-950/20">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-cyan-700 dark:text-cyan-300">Keberangkatan Saat Ini</p>
                        <p class="mt-1 text-sm font-semibold text-foreground">{formatGroupDateLabel(groupRescheduleCurrentDate)} • {formatGroupTimeLabel(groupRescheduleCurrentJam)}</p>
                        <p class="mt-1 text-[11px] text-muted-foreground">Unit {groupRescheduleCurrentUnit} • Seat {groupRescheduleCurrentSeat || '-'}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200/60 bg-emerald-50/70 px-3 py-2 dark:border-emerald-500/20 dark:bg-emerald-950/20">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-emerald-700 dark:text-emerald-300">Tujuan Reschedule</p>
                        <p class="mt-1 text-sm font-semibold text-foreground">
                            {groupRescheduleDate ? formatGroupDateLabel(groupRescheduleDate) : '-'} • {groupRescheduleJam ? formatGroupTimeLabel(groupRescheduleJam) : '-'}
                        </p>
                        <p class="mt-1 text-[11px] text-muted-foreground">Unit {groupRescheduleUnit} • Seat {groupRescheduleSeat || '-'}</p>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label for="group-reschedule-date" class="mb-1 block text-xs font-medium text-muted-foreground">Tanggal Tujuan</label>
                        <Input
                            id="group-reschedule-date"
                            type="date"
                            class="h-9 rounded-xl"
                            bind:value={groupRescheduleDate}
                            onchange={() => void onGroupRescheduleDateChange()}
                            disabled={savingGroupReschedule}
                        />
                    </div>
                    <div>
                        <label for="group-reschedule-jam" class="mb-1 block text-xs font-medium text-muted-foreground">Jam Keberangkatan</label>
                        <select
                            id="group-reschedule-jam"
                            class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                            bind:value={groupRescheduleJam}
                            onchange={() => void onGroupRescheduleScheduleChange()}
                            disabled={savingGroupReschedule || loadingGroupRescheduleSchedules || groupRescheduleSchedules.length === 0}
                        >
                            {#if loadingGroupRescheduleSchedules}
                                <option value="">Memuat jadwal...</option>
                            {:else if groupRescheduleSchedules.length === 0}
                                <option value="">Jadwal tidak tersedia</option>
                            {:else}
                                {#each groupRescheduleSchedules as schedule (`group-reschedule-jam-${schedule.jam}`)}
                                    <option value={schedule.jam}>{schedule.jam}</option>
                                {/each}
                            {/if}
                        </select>
                    </div>
                    <div>
                        <label for="group-reschedule-unit" class="mb-1 block text-xs font-medium text-muted-foreground">Unit Tujuan</label>
                        <select
                            id="group-reschedule-unit"
                            class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                            bind:value={groupRescheduleUnit}
                            onchange={() => void loadGroupRescheduleSeatOptions()}
                            disabled={savingGroupReschedule || loadingGroupRescheduleSchedules || groupRescheduleUnitOptions().length === 0}
                        >
                            {#each groupRescheduleUnitOptions() as option (`group-reschedule-unit-opt-${option.value}`)}
                                <option value={option.value}>{option.label}</option>
                            {/each}
                        </select>
                    </div>
                    <div>
                        <label for="group-reschedule-seat" class="mb-1 block text-xs font-medium text-muted-foreground">Seat Tersedia</label>
                        <select
                            id="group-reschedule-seat"
                            class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                            bind:value={groupRescheduleSeat}
                            disabled={savingGroupReschedule || loadingGroupRescheduleSeats || groupRescheduleSeatOptions.length === 0}
                        >
                            {#if loadingGroupRescheduleSeats}
                                <option value={groupRescheduleSeat}>Memuat seat tersedia...</option>
                            {:else if groupRescheduleSeatOptions.length === 0}
                                <option value="">Seat tidak tersedia</option>
                            {:else}
                                {#each groupRescheduleSeatOptions as seat (`group-reschedule-seat-opt-${seat}`)}
                                    <option value={seat}>{groupRescheduleSeatLabel(seat)}</option>
                                {/each}
                            {/if}
                        </select>
                        <p class="mt-1 text-[11px] text-muted-foreground">{groupRescheduleSeatHelpText()}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-cyan-200/60 bg-linear-to-br from-cyan-50/70 via-background to-background p-3 dark:border-cyan-500/20 dark:from-cyan-950/20">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700 dark:text-cyan-300">Preview Seat Tujuan</p>
                        <p class="mt-1 text-[11px] text-muted-foreground">Pilih seat kosong langsung dari layout keberangkatan tujuan.</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[10px]">
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-2 py-0.5 text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-950/30 dark:text-cyan-200">Seat Saat Ini</span>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/30 dark:text-emerald-200">Tersedia</span>
                        <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-rose-700 dark:border-rose-500/30 dark:bg-rose-950/30 dark:text-rose-200">Terisi</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2 text-[11px]">
                    <div class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Dipilih</p>
                        <p class="mt-1 font-semibold text-foreground">{groupRescheduleSeat || '-'}</p>
                    </div>
                    <div class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Tersedia</p>
                        <p class="mt-1 font-semibold text-foreground">{groupRescheduleSeatOptions.filter((seat) => normalizeSeatToken(seat) !== normalizeSeatToken(groupRescheduleCurrentSeat)).length}</p>
                    </div>
                    <div class="rounded-xl border border-border/70 bg-background/85 px-2.5 py-2">
                        <p class="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Terisi</p>
                        <p class="mt-1 font-semibold text-foreground">{groupRescheduleBookedSeatTokens.length}</p>
                    </div>
                </div>

                <div class="mt-3 rounded-2xl border border-border/70 bg-background/85 p-3">
                    {#if groupRescheduleLayoutRows.length === 0}
                        <div class="flex h-36 items-center justify-center rounded-xl border border-dashed border-border/70 bg-muted/10 px-4 text-center text-[11px] text-muted-foreground">
                            Preview layout keberangkatan tujuan belum tersedia.
                        </div>
                    {:else}
                        <div class="space-y-2">
                            {#each groupRescheduleLayoutRows as layoutRow, rowIndex (`group-reschedule-layout-row-${rowIndex}`)}
                                <div class={`grid gap-2 ${layoutRow.length >= 4 ? 'grid-cols-4' : 'grid-cols-3'}`}>
                                    {#each layoutRow as cell, colIndex (`group-reschedule-layout-cell-${rowIndex}-${colIndex}`)}
                                        {#if cell.kind === 'seat' && cell.seat}
                                            <button
                                                type="button"
                                                class={`h-14 rounded-xl border text-center transition-all ${groupRescheduleSeatButtonClass(cell.seat)}`}
                                                onclick={() => selectGroupRescheduleSeat(cell.seat ?? '')}
                                                disabled={groupRescheduleSeatStatus(cell.seat) === 'taken' || savingGroupReschedule || loadingGroupRescheduleSeats}
                                            >
                                                <span class="block text-sm font-semibold leading-none">{cell.seat}</span>
                                                <span class="mt-1 block text-[9px] uppercase tracking-[0.14em] opacity-80">
                                                    {groupRescheduleSeatStatus(cell.seat) === 'taken'
                                                        ? 'Terisi'
                                                        : groupRescheduleSeatStatus(cell.seat) === 'current'
                                                            ? 'Saat ini'
                                                            : normalizeSeatToken(groupRescheduleSeat) === normalizeSeatToken(cell.seat)
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
                onclick={() => void saveGroupReschedule()}
                disabled={savingGroupReschedule || loadingGroupRescheduleSchedules || loadingGroupRescheduleSeats || groupRescheduleSeatOptions.length === 0}
            >
                {savingGroupReschedule ? 'Memindahkan...' : 'Simpan Reschedule'}
            </Button>
            <Button type="button" variant="outline" onclick={closeGroupRescheduleModal} disabled={savingGroupReschedule}>
                Batal
            </Button>
        </div>

        {#if formError}
            <p class="mt-2 text-sm text-destructive">{formError}</p>
        {/if}
    </div>
</div>
