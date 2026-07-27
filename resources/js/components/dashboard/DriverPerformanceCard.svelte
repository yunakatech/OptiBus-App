<script lang="ts">
    import { themeState } from '@/lib/theme.svelte';

    type DriverItem = {
        rank: number;
        name: string;
        trip_count: number;
        revenue: number;
        route?: string | null;
    };

    let {
        categories = {
            Minibus: [],
            Mediumbus: [],
            Bigbus: [],
        },
        toCurrency,
    }: {
        categories: Record<string, DriverItem[]>;
        toCurrency: (val: number) => string;
    } = $props();

    let selectedCategory = $state('Minibus');
    let currentDrivers = $derived(
        [...(categories[selectedCategory] || [])].sort(
            (left, right) => right.revenue - left.revenue,
        ),
    );

    const maxRevenue = $derived(
        currentDrivers.length > 0
            ? Math.max(...currentDrivers.map((d) => d.revenue))
            : 1,
    );
    const medalLabel = ['\u{1F947}', '\u{1F948}', '\u{1F949}'];
    const { resolvedAppearance } = themeState();
    const isDark = $derived(resolvedAppearance() === 'dark');
</script>

<div
    class={`rounded-lg border p-3 text-card-foreground shadow-xs transition-all duration-300 hover:shadow-sm sm:rounded-lg sm:p-5 ${isDark ? 'border-border/80 bg-gradient-to-br from-slate-950 via-slate-950 to-amber-950/10' : 'border-border/80 bg-gradient-to-br from-background via-card to-amber-50/20'}`}
>
    <div class="mb-3 flex items-start justify-between gap-3 sm:mb-4">
        <div>
            <p class="text-[13px] font-semibold text-muted-foreground sm:text-sm">
                Peringkat <span class="font-bold text-foreground"
                    >Performa Driver</span
                >
            </p>
            <p class="mt-0.5 text-[10px] text-muted-foreground sm:text-[11px]">
                Diurutkan dari total revenue bulan berjalan
            </p>
        </div>
        <a
            href="/admin-ops/drivers"
            class="shrink-0 rounded-full border border-border/70 bg-muted/40 px-2.5 py-1 text-[10px] font-semibold text-muted-foreground transition hover:border-border hover:bg-background hover:text-foreground dark:hover:bg-slate-900/60"
        >
            Lihat Detail →
        </a>
    </div>

    <div class="mb-3 flex rounded-lg bg-muted/70 p-1 dark:bg-slate-900/70">
        {#each Object.keys(categories) as cat}
            <button
                class={`flex-1 rounded-md px-3 py-1.5 text-[11px] font-semibold transition ${selectedCategory === cat ? 'bg-background text-foreground shadow-sm dark:bg-slate-800' : 'text-muted-foreground hover:text-foreground'}`}
                onclick={() => (selectedCategory = cat)}
            >
                {cat}
            </button>
        {/each}
    </div>

    {#if currentDrivers.length === 0}
        <div
            class="flex h-20 items-center justify-center rounded-xl border border-dashed border-border/70 text-[11px] text-muted-foreground"
        >
            Belum ada data performa driver
        </div>
    {:else}
        <div class="space-y-2">
            {#each currentDrivers as driver, i (`${driver.name}-${driver.route ?? 'route'}-${i}`)}
                {@const barWidth =
                    maxRevenue > 0
                        ? Math.max(
                              6,
                              Math.round((driver.revenue / maxRevenue) * 100),
                          )
                        : 6}
                {@const isMedal = i < 3}
                <div
                    class={`group relative rounded-xl border p-2 transition ${i === 0 ? 'border-amber-400/40 bg-amber-50/70 hover:border-amber-300 hover:bg-amber-50 dark:border-amber-400/30 dark:bg-amber-950/20 dark:hover:bg-amber-950/30' : 'border-border/70 bg-background/85 hover:border-border hover:bg-muted/40 dark:bg-slate-900/65 dark:hover:bg-slate-900/80'}`}
                >
                    <div class="mb-1.5 flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-1.5">
                            <span
                                class="shrink-0 text-[13px] leading-none"
                                aria-hidden="true"
                            >
                                {#if isMedal}
                                    {medalLabel[i]}
                                {:else}
                                    <span
                                        class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-muted text-[9px] font-bold text-muted-foreground"
                                    >
                                        {i + 1}
                                    </span>
                                {/if}
                            </span>
                            <p class="truncate text-[12px] font-semibold text-foreground">
                                {driver.name}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-muted px-1.5 py-0.5 text-[9px] font-bold text-muted-foreground"
                        >
                            {driver.trip_count} trip
                        </span>
                    </div>

                    <div class="h-1 overflow-hidden rounded-full bg-muted/70">
                        <div
                            class={`h-full rounded-full transition-all duration-700 ease-out ${i === 0 ? 'bg-gradient-to-r from-amber-400 to-yellow-500' : i === 1 ? 'bg-slate-400' : i === 2 ? 'bg-amber-700/70' : 'bg-blue-400'}`}
                            style={`width:${barWidth}%`}
                        ></div>
                    </div>

                    <p class="mt-1 text-[10px] font-bold text-foreground">
                        {toCurrency(driver.revenue)}
                    </p>

                    {#if driver.route}
                        <p class="mt-0.5 truncate text-[9px] text-muted-foreground">
                            Rute: {driver.route}
                        </p>
                    {/if}
                </div>
            {/each}
        </div>
    {/if}
</div>
