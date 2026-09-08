<script lang="ts">
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import { onMount } from 'svelte';
    import { loadFlatpickr } from '@/lib/flatpickr';
    import type { FlatpickrInstance } from '@/lib/flatpickr';

    let {
        label,
        value = $bindable(''),
        minDate,
        maxDate,
        placeholder = 'Pilih tanggal',
        disabled = false,
    }: {
        label: string;
        value?: string;
        minDate?: string;
        maxDate?: string;
        placeholder?: string;
        disabled?: boolean;
    } = $props();

    let input = $state<HTMLInputElement | null>(null);
    let picker: FlatpickrInstance | null = null;

    onMount(() => {
        let disposed = false;

        void loadFlatpickr()
            .then((flatpickr) => {
                if (disposed || !input) {
                    return;
                }

                picker = flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'D, d M Y',
                    ariaDateFormat: 'l, j F Y',
                    altInputClass:
                        'h-12 w-full cursor-pointer rounded-xl border border-input bg-background py-1 pr-10 pl-3 text-base text-foreground shadow-sm outline-none transition placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/35 disabled:cursor-not-allowed disabled:bg-muted/60 disabled:opacity-70',
                    defaultDate: value || undefined,
                    minDate,
                    maxDate,
                    disableMobile: true,
                    onChange: (_selectedDates, dateString) => {
                        value = dateString || '';
                    },
                });
                picker.calendarContainer.classList.add('filter-date-calendar');
                picker.altInput?.setAttribute('placeholder', placeholder);
            })
            .catch(() => {
                // Input bawaan tetap dapat dipakai bila chunk Flatpickr gagal dimuat.
            });

        return () => {
            disposed = true;
            picker?.destroy();
            picker = null;
        };
    });

    $effect(() => {
        const nextValue = value;

        if (!picker || picker.input.value === nextValue) {
            return;
        }

        if (nextValue) {
            picker.setDate(nextValue, false, 'Y-m-d');
        } else {
            picker.clear(false);
        }
    });
</script>

<label class="grid gap-1.5 text-sm font-medium text-foreground">
    <span>{label}</span>
    <span class="relative block">
        <input
            bind:this={input}
            type="date"
            {value}
            min={minDate}
            max={maxDate}
            {disabled}
            aria-label={label}
            {placeholder}
            class="h-12 w-full rounded-xl border border-input bg-background px-3 text-base text-foreground shadow-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/35 disabled:cursor-not-allowed disabled:bg-muted/60 disabled:opacity-70"
            onchange={(event) => {
                value = event.currentTarget.value;
            }}
        />
        <CalendarDays
            class="pointer-events-none absolute top-1/2 right-3 z-10 h-4 w-4 -translate-y-1/2 text-muted-foreground"
        />
    </span>
</label>

<style>
    :global(.flatpickr-calendar.filter-date-calendar) {
        z-index: 80;
        border-color: color-mix(in srgb, var(--border) 82%, transparent);
        border-radius: 1rem;
        box-shadow: 0 18px 42px rgb(15 23 42 / 0.2);
    }

    :global(.flatpickr-calendar.filter-date-calendar .flatpickr-day.selected),
    :global(.flatpickr-calendar.filter-date-calendar .flatpickr-day.startRange),
    :global(.flatpickr-calendar.filter-date-calendar .flatpickr-day.endRange) {
        border-color: var(--primary);
        background: var(--primary);
    }
</style>
