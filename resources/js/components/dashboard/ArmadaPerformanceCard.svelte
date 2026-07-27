<script lang="ts">
    import { themeState } from '@/lib/theme.svelte';

    type ArmadaItem = {
        rank: number;
        nopol: string;
        trip_count: number;
        revenue: number;
        pool_name?: string | null;
        category?: string | null;
    };

    const categoryOptions = ['Minibus', 'Mediumbus', 'Bigbus'] as const;
    type ArmadaCategory = (typeof categoryOptions)[number];

    let {
        categories = {
            Minibus: [],
            Mediumbus: [],
            Bigbus: [],
        },
        toCurrency,
    }: {
        categories: Record<string, ArmadaItem[]>;
        toCurrency: (val: number) => string;
    } = $props();

    const normalizeCategory = (
        value: string | null | undefined,
    ): ArmadaCategory => {
        const normalized = String(value ?? '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '');

        if (normalized === 'mediumbus') {
            return 'Mediumbus';
        }

        if (normalized === 'bigbus' || normalized === 'bigbun') {
            return 'Bigbus';
        }

        return 'Minibus';
    };

    let selectedCategory = $state<ArmadaCategory>('Minibus');
    let currentArmadas = $derived(
        [...(categories[selectedCategory] ?? [])]
            .map((item) => ({
                ...item,
                category: normalizeCategory(item.category),
            }))
            .sort((left, right) => right.revenue - left.revenue),
    );

    const maxRevenue = $derived(
        currentArmadas.length > 0
            ? Math.max(...currentArmadas.map((item) => item.revenue), 1)
            : 1,
    );
    const { resolvedAppearance } = themeState();
    const isDark = $derived(resolvedAppearance() === 'dark');
</script>

<div
    class={`overflow-hidden rounded-lg border p-3 text-card-foreground shadow-xs transition hover:shadow-sm sm:rounded-lg sm:p-5 ${isDark ? 'border-border/80 bg-gradient-to-br from-slate-950 via-slate-950 to-cyan-950/10' : 'border-border/80 bg-gradient-to-br from-background via-card to-cyan-50/20'}`}
>
    <div class="mb-3 flex items-start justify-between gap-3 sm:mb-4">
        <div>
            <p class="text-[13px] font-semibold text-muted-foreground sm:text-sm">
                Peringkat <span class="font-bold text-foreground"
                    >Performa Armada</span
                >
            </p>
            <p class="mt-0.5 text-[10px] text-muted-foreground sm:text-[11px]">
                Diurutkan dari total revenue bulan berjalan
            </p>
        </div>
        <span
            class="shrink-0 rounded-full border border-border/70 bg-muted/40 px-2.5 py-1 text-[10px] font-semibold text-muted-foreground"
        >
            {currentArmadas.length} unit
        </span>
    </div>

    <div class="mb-3 flex rounded-lg bg-muted/70 p-1 dark:bg-slate-900/70">
        {#each categoryOptions as category (category)}
            <button
                class={`flex-1 rounded-md px-3 py-1.5 text-[11px] font-semibold transition ${selectedCategory === category ? 'bg-background text-foreground shadow-sm dark:bg-slate-800' : 'text-muted-foreground hover:text-foreground'}`}
                onclick={() => (selectedCategory = category)}
            >
                {category}
            </button>
        {/each}
    </div>

    {#if currentArmadas.length === 0}
        <div
            class="flex h-20 items-center justify-center rounded-xl border border-dashed border-border/70 text-[11px] text-muted-foreground"
        >
            Belum ada data performa armada
        </div>
    {:else}
        <div class="space-y-2">
            {#each currentArmadas as item, index (item.nopol)}
                {@const barWidth =
                    maxRevenue > 0
                        ? Math.max(
                              6,
                              Math.round((item.revenue / maxRevenue) * 100),
                          )
                        : 6}
                <div
                    class={`group relative rounded-xl border p-2 transition ${index === 0 ? 'border-cyan-400/40 bg-cyan-50/70 hover:border-cyan-300 hover:bg-cyan-50 dark:border-cyan-400/30 dark:bg-cyan-950/20 dark:hover:bg-cyan-950/30' : 'border-border/70 bg-background/85 hover:border-border hover:bg-muted/40 dark:bg-slate-900/65 dark:hover:bg-slate-900/80'}`}
                >
                    <div class="mb-1.5 flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex min-w-0 items-center gap-1.5">
                                <span
                                    class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-muted text-[9px] font-bold text-muted-foreground"
                                >
                                    {index + 1}
                                </span>
                                <p class="truncate text-[12px] font-semibold text-foreground">
                                    {item.nopol}
                                </p>
                            </div>
                            <p class="mt-0.5 truncate text-[9px] text-muted-foreground">
                                {item.pool_name || 'Semua Pool'}
                                {#if item.category}
                                    - {item.category}
                                {/if}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-muted px-1.5 py-0.5 text-[9px] font-bold text-muted-foreground"
                        >
                            {item.trip_count} trip
                        </span>
                    </div>

                    <div class="h-1 overflow-hidden rounded-full bg-muted/70">
                        <div
                            class={`h-full rounded-full transition-all duration-700 ease-out ${index === 0 ? 'bg-gradient-to-r from-cyan-400 to-sky-500' : index === 1 ? 'bg-slate-400' : index === 2 ? 'bg-cyan-700/70' : 'bg-blue-400'}`}
                            style={`width:${barWidth}%`}
                        ></div>
                    </div>

                    <p class="mt-1 text-[10px] font-bold text-foreground">
                        {toCurrency(item.revenue)}
                    </p>
                </div>
            {/each}
        </div>
    {/if}
</div>
