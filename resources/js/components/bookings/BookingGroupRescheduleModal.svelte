<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';

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
        loadingGroupRescheduleSchedules = false,
        loadingGroupRescheduleSeats = false,
        savingGroupReschedule = false,
        formError = '',
        formatGroupDateLabel,
        formatGroupTimeLabel,
        groupRescheduleUnitOptions,
        groupRescheduleSeatLabel,
        groupRescheduleSeatHelpText,
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
        loadingGroupRescheduleSchedules?: boolean;
        loadingGroupRescheduleSeats?: boolean;
        savingGroupReschedule?: boolean;
        formError?: string;
        formatGroupDateLabel: (value: string) => string;
        formatGroupTimeLabel: (value: string) => string;
        groupRescheduleUnitOptions: () => UnitOption[];
        groupRescheduleSeatLabel: (seat: string) => string;
        groupRescheduleSeatHelpText: () => string;
        onGroupRescheduleDateChange: () => void | Promise<void>;
        onGroupRescheduleScheduleChange: () => void | Promise<void>;
        loadGroupRescheduleSeatOptions: () => void | Promise<void>;
        closeGroupRescheduleModal: () => void;
        saveGroupReschedule: () => void | Promise<void>;
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
                    Reschedule Penumpang
                </p>
                <h3 class="text-base font-semibold">
                    {groupRescheduleBookingName || '-'}
                </h3>
                <p class="text-xs text-muted-foreground">
                    {groupRescheduleRoute}
                </p>
            </div>
            <Button
                type="button"
                variant="outline"
                size="sm"
                class="rounded-full"
                onclick={closeGroupRescheduleModal}
                disabled={savingGroupReschedule}
            >
                Tutup
            </Button>
        </div>

        <div class="space-y-4">
            <div
                class="grid gap-3 rounded-lg border border-border/70 bg-background/80 p-3 md:grid-cols-2"
            >
                <div
                    class="rounded-xl border border-cyan-200/60 bg-cyan-50/70 px-3 py-2 dark:border-cyan-500/20 dark:bg-cyan-950/20"
                >
                    <p
                        class="text-[10px] uppercase tracking-[0.12em] text-cyan-700 dark:text-cyan-300"
                    >
                        Keberangkatan Saat Ini
                    </p>
                    <p class="mt-1 text-sm font-semibold text-foreground">
                        {formatGroupDateLabel(groupRescheduleCurrentDate)} -
                        {formatGroupTimeLabel(groupRescheduleCurrentJam)}
                    </p>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Unit {groupRescheduleCurrentUnit} - Seat
                        {groupRescheduleCurrentSeat || '-'}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-emerald-200/60 bg-emerald-50/70 px-3 py-2 dark:border-emerald-500/20 dark:bg-emerald-950/20"
                >
                    <p
                        class="text-[10px] uppercase tracking-[0.12em] text-emerald-700 dark:text-emerald-300"
                    >
                        Tujuan Reschedule
                    </p>
                    <p class="mt-1 text-sm font-semibold text-foreground">
                        {groupRescheduleDate
                            ? formatGroupDateLabel(groupRescheduleDate)
                            : '-'}
                        -
                        {groupRescheduleJam
                            ? formatGroupTimeLabel(groupRescheduleJam)
                            : '-'}
                    </p>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Unit {groupRescheduleUnit} - Seat
                        {groupRescheduleSeat || '-'}
                    </p>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label
                        for="group-reschedule-date"
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                    >
                        Tanggal Tujuan
                    </label>
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
                    <label
                        for="group-reschedule-jam"
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                    >
                        Jam Keberangkatan
                    </label>
                    <select
                        id="group-reschedule-jam"
                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                        bind:value={groupRescheduleJam}
                        onchange={() => void onGroupRescheduleScheduleChange()}
                        disabled={savingGroupReschedule ||
                            loadingGroupRescheduleSchedules ||
                            groupRescheduleSchedules.length === 0}
                    >
                        {#if loadingGroupRescheduleSchedules}
                            <option value="">Memuat jadwal...</option>
                        {:else if groupRescheduleSchedules.length === 0}
                            <option value="">Jadwal tidak tersedia</option>
                        {:else}
                            {#each groupRescheduleSchedules as schedule (`group-reschedule-jam-${schedule.jam}`)}
                                <option value={schedule.jam}>
                                    {schedule.jam}
                                </option>
                            {/each}
                        {/if}
                    </select>
                </div>
                <div>
                    <label
                        for="group-reschedule-unit"
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                    >
                        Unit Tujuan
                    </label>
                    <select
                        id="group-reschedule-unit"
                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                        bind:value={groupRescheduleUnit}
                        onchange={() => void loadGroupRescheduleSeatOptions()}
                        disabled={savingGroupReschedule ||
                            loadingGroupRescheduleSchedules ||
                            groupRescheduleUnitOptions().length === 0}
                    >
                        {#each groupRescheduleUnitOptions() as option (`group-reschedule-unit-opt-${option.value}`)}
                            <option value={option.value}>
                                {option.label}
                            </option>
                        {/each}
                    </select>
                </div>
                <div>
                    <label
                        for="group-reschedule-seat"
                        class="mb-1 block text-xs font-medium text-muted-foreground"
                    >
                        Seat Tersedia
                    </label>
                    <select
                        id="group-reschedule-seat"
                        class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm"
                        bind:value={groupRescheduleSeat}
                        disabled={savingGroupReschedule ||
                            loadingGroupRescheduleSeats ||
                            groupRescheduleSeatOptions.length === 0}
                    >
                        {#if loadingGroupRescheduleSeats}
                            <option value={groupRescheduleSeat}>
                                Memuat seat tersedia...
                            </option>
                        {:else if groupRescheduleSeatOptions.length === 0}
                            <option value="">Seat tidak tersedia</option>
                        {:else}
                            {#each groupRescheduleSeatOptions as seat (`group-reschedule-seat-opt-${seat}`)}
                                <option value={seat}>
                                    {groupRescheduleSeatLabel(seat)}
                                </option>
                            {/each}
                        {/if}
                    </select>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        {groupRescheduleSeatHelpText()}
                    </p>
                </div>
                <div
                    class="md:col-span-2 rounded-lg border border-border/70 bg-muted/10 px-3 py-2 text-[11px] text-muted-foreground"
                >
                    Dropdown seat sudah mengikuti ketersediaan kursi pada jadwal
                    dan unit tujuan.
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <Button
                type="button"
                onclick={() => void saveGroupReschedule()}
                disabled={savingGroupReschedule ||
                    loadingGroupRescheduleSchedules ||
                    loadingGroupRescheduleSeats ||
                    groupRescheduleSeatOptions.length === 0}
            >
                {savingGroupReschedule ? 'Memindahkan...' : 'Simpan Reschedule'}
            </Button>
            <Button
                type="button"
                variant="outline"
                onclick={closeGroupRescheduleModal}
                disabled={savingGroupReschedule}
            >
                Batal
            </Button>
        </div>

        {#if formError}
            <p class="mt-2 text-sm text-destructive">{formError}</p>
        {/if}
    </div>
</div>
