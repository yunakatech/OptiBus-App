<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    type SummaryCard = {
        key: string;
        label: string;
        valueText: string;
        note?: string;
        formula?: string;
        tone?: string;
    };

    type ChartBar = {
        key: string;
        label: string;
        value: number;
        valueText: string;
        subtitle?: string;
        tone?: string;
    };

    type BadgeItem = {
        key: string;
        label: string;
        tone?: string;
    };

    let {
        title,
        subtitle = '',
        badges = [],
        summaryCards = [],
        chartBars = [],
        density = 'comfortable',
        showHeader = true,
        controls,
        table,
        class: className = '',
    }: {
        title: string;
        subtitle?: string;
        badges?: BadgeItem[];
        summaryCards?: SummaryCard[];
        chartBars?: ChartBar[];
        density?: 'comfortable' | 'compact';
        showHeader?: boolean;
        controls?: Snippet;
        table: Snippet;
        class?: string;
    } = $props();

    const chartItems = $derived(chartBars.slice(0, 6));
    const maxValue = $derived(
        Math.max(1, ...chartItems.map((item) => Number(item.value || 0))),
    );
    const isCompact = $derived(density === 'compact');
</script>

<section
    class={cn(
        isCompact
            ? 'space-y-3 overflow-hidden rounded-lg border border-border/70 bg-background/95 p-3 shadow-sm'
            : 'space-y-4 overflow-hidden rounded-lg border border-border/70 bg-background/95 p-4 shadow-sm',
        className,
    )}
>
    {#if showHeader}
        <div
            class={isCompact
                ? 'flex flex-col gap-2 border-b border-border/70 bg-[linear-gradient(135deg,rgba(8,145,178,0.08),rgba(15,23,42,0.03))] px-1 pb-3 lg:flex-row lg:items-start lg:justify-between'
                : 'flex flex-col gap-3 border-b border-border/70 bg-[linear-gradient(135deg,rgba(8,145,178,0.08),rgba(15,23,42,0.03))] px-1 pb-4 lg:flex-row lg:items-start lg:justify-between'}
        >
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3
                        class={isCompact
                            ? 'text-base font-bold tracking-tight md:text-lg'
                            : 'text-lg font-bold tracking-tight md:text-xl'}
                    >
                        {title}
                    </h3>
                    {#each badges as badge (badge.key)}
                        <span
                            class={cn(
                                isCompact
                                    ? 'inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide'
                                    : 'inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide',
                                badge.tone ??
                                    'border-border/70 bg-muted/30 text-foreground',
                            )}
                        >
                            {badge.label}
                        </span>
                    {/each}
                </div>
                {#if subtitle !== ''}
                    <p
                        class={isCompact
                            ? 'max-w-3xl text-xs text-muted-foreground'
                            : 'max-w-3xl text-sm text-muted-foreground'}
                    >
                        {subtitle}
                    </p>
                {/if}
            </div>
        </div>
    {/if}

    {#if summaryCards.length > 0}
        <div
            class={isCompact
                ? 'grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6'
                : 'grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6'}
        >
            {#each summaryCards as card (card.key)}
                <article
                    class={isCompact
                        ? 'rounded-xl border border-border/70 bg-muted/20 p-2.5 shadow-sm'
                        : 'rounded-xl border border-border/70 bg-muted/20 p-3 shadow-sm'}
                >
                    <p
                        class={isCompact
                            ? 'text-[9px] font-semibold uppercase tracking-[0.16em] text-muted-foreground'
                            : 'text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground'}
                    >
                        {card.label}
                    </p>
                    <p
                        class={cn(
                            isCompact
                                ? 'mt-1.5 text-base font-bold tabular-nums'
                                : 'mt-2 text-lg font-bold tabular-nums',
                            card.tone ?? 'text-foreground',
                        )}
                    >
                        {card.valueText}
                    </p>
                    {#if card.note}
                        <p
                            class={isCompact
                                ? 'mt-1 text-[10px] leading-4 text-muted-foreground'
                                : 'mt-1 text-[11px] leading-4 text-muted-foreground'}
                        >
                            {card.note}
                        </p>
                    {/if}
                    {#if card.formula}
                        <p
                            class={isCompact
                                ? 'mt-1 text-[9px] font-medium uppercase tracking-[0.12em] text-muted-foreground/80'
                                : 'mt-1 text-[10px] font-medium uppercase tracking-[0.14em] text-muted-foreground/80'}
                        >
                            {card.formula}
                        </p>
                    {/if}
                </article>
            {/each}
        </div>
    {/if}

    {#if chartItems.length > 0}
        <div
            class={isCompact
                ? 'rounded-lg border border-border/70 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(241,245,249,0.92))] p-3 dark:bg-[linear-gradient(180deg,rgba(15,23,42,0.94),rgba(15,23,42,0.72))]'
                : 'rounded-lg border border-border/70 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(241,245,249,0.92))] p-4 dark:bg-[linear-gradient(180deg,rgba(15,23,42,0.94),rgba(15,23,42,0.72))]'}
        >
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <p
                        class={isCompact
                            ? 'text-[9px] font-semibold uppercase tracking-[0.16em] text-muted-foreground'
                            : 'text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground'}
                    >
                        Top Revenue Snapshot
                    </p>
                    <h4
                        class={isCompact
                            ? 'mt-1 text-xs font-semibold tracking-tight'
                            : 'mt-1 text-sm font-semibold tracking-tight'}
                    >
                        Bar ringkas dari data teratas
                    </h4>
                </div>
                <p
                    class={isCompact
                        ? 'text-[11px] text-muted-foreground'
                        : 'text-xs text-muted-foreground'}
                >
                    Semi visual, tetap ringan.
                </p>
            </div>

            <div class={isCompact ? 'mt-3 space-y-2.5' : 'mt-4 space-y-3'}>
                {#each chartItems as bar (bar.key)}
                    {@const width = Math.max(
                        4,
                        Math.round((Number(bar.value || 0) / maxValue) * 100),
                    )}
                    <div
                        class={isCompact
                            ? 'grid gap-2 sm:grid-cols-[minmax(0,160px)_minmax(0,1fr)_auto] sm:items-center'
                            : 'grid gap-2 sm:grid-cols-[minmax(0,180px)_minmax(0,1fr)_auto] sm:items-center'}
                    >
                        <div class="min-w-0">
                            <p
                                class={isCompact
                                    ? 'truncate text-[11px] font-semibold text-foreground'
                                    : 'truncate text-xs font-semibold text-foreground'}
                            >
                                {bar.label}
                            </p>
                            {#if bar.subtitle}
                                <p
                                    class={isCompact
                                        ? 'truncate text-[10px] text-muted-foreground'
                                        : 'truncate text-[11px] text-muted-foreground'}
                                >
                                    {bar.subtitle}
                                </p>
                            {/if}
                        </div>
                        <div
                            class={isCompact
                                ? 'h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800'
                                : 'h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800'}
                        >
                            <div
                                class={cn(
                                    'h-full rounded-full transition-[width] duration-500',
                                    bar.tone ?? 'bg-cyan-500',
                                )}
                                style={`width: ${width}%`}
                            ></div>
                        </div>
                        <div class="text-right">
                            <p
                                class={isCompact
                                    ? 'text-xs font-bold tabular-nums text-foreground'
                                    : 'text-sm font-bold tabular-nums text-foreground'}
                            >
                                {bar.valueText}
                            </p>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    {/if}

    {#if controls}
        <div
            class={isCompact
                ? 'rounded-lg border border-dashed border-border/70 bg-muted/10 p-2.5'
                : 'rounded-lg border border-dashed border-border/70 bg-muted/10 p-3'}
        >
            {@render controls()}
        </div>
    {/if}

    {@render table()}
</section>
