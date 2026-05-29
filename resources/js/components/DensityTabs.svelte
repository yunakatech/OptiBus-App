<script lang="ts">
    import MonitorDown from 'lucide-svelte/icons/monitor-down';
    import MonitorUp from 'lucide-svelte/icons/monitor-up';
    import type { Component, SvelteComponent } from 'svelte';
    import { themeState } from '@/lib/theme.svelte';
    import type { Density } from '@/types';

    const { density, updateDensity } = themeState();

    type IconComponent =
        | Component<{ class?: string }>
        | (new (...args: any[]) => SvelteComponent<{ class?: string }>);

    const tabs: { value: Density; Icon: IconComponent; label: string }[] = [
        { value: 'compact', Icon: MonitorDown, label: 'Compact' },
        { value: 'comfortable', Icon: MonitorUp, label: 'Comfortable' },
    ];

    function handleDensityChange(value: Density) {
        updateDensity(value);
    }
</script>

<div
    class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
>
    {#each tabs as { value, Icon, label } (value)}
        <button
            onclick={() => handleDensityChange(value)}
            class="flex items-center rounded-md px-3.5 py-1.5 transition-colors {density.value ===
            value
                ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60'}"
        >
            <Icon class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{label}</span>
        </button>
    {/each}
</div>
