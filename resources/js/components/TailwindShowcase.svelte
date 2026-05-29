<script lang="ts">
    import BellRing from 'lucide-svelte/icons/bell-ring';
    import ChartNoAxesCombined from 'lucide-svelte/icons/chart-no-axes-combined';
    import Zap from 'lucide-svelte/icons/zap';

    const stats = [
        {
            label: 'Throughput',
            value: '1.8k',
            hint: 'booking / jam',
            icon: Zap,
        },
        {
            label: 'Live Alerts',
            value: '24',
            hint: 'anomali hari ini',
            icon: BellRing,
        },
        {
            label: 'Conversion',
            value: '92%',
            hint: 'checkout berhasil',
            icon: ChartNoAxesCombined,
        },
    ];
</script>

<section
    class="relative mb-8 overflow-hidden rounded-2xl border border-cyan-200/50 bg-linear-to-br from-cyan-500 via-sky-500 to-blue-600 p-5 text-white shadow-xl shadow-sky-900/15 sm:p-7"
>
    <div class="absolute -top-20 -right-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>
    <div class="absolute -bottom-24 -left-10 h-56 w-56 rounded-full bg-blue-900/20 blur-3xl"></div>

    <div class="relative space-y-4">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/80">Tailwind Frontend</p>
            <h2 class="text-xl font-semibold tracking-tight sm:text-2xl">
                UI modern untuk operasi booking yang cepat dan rapi
            </h2>
            <p class="max-w-2xl text-sm text-white/90">
                Section ini dibangun full utility class Tailwind: gradient, responsive grid, glow background, dan
                card statistik siap pakai.
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            {#each stats as item (item.label)}
                <article class="rounded-xl border border-white/30 bg-white/15 p-4 backdrop-blur-sm">
                    <div class="mb-3 inline-flex rounded-md bg-white/20 p-2">
                        <item.icon class="size-4" />
                    </div>
                    <p class="text-xs uppercase tracking-wide text-white/80">{item.label}</p>
                    <p class="text-2xl font-semibold">{item.value}</p>
                    <p class="text-xs text-white/80">{item.hint}</p>
                </article>
            {/each}
        </div>
    </div>
</section>
