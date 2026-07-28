<script lang="ts">
    import { X } from 'lucide-svelte';
    import { onDestroy } from 'svelte';
    import { Chart, type ChartConfiguration } from 'chart.js/auto';
    import { themeState } from '@/lib/theme.svelte';

    let {
        monthlyTrend = [],
        toCurrency,
    }: {
        monthlyTrend: any[];
        toCurrency: (val: number) => string;
    } = $props();

    let chartCanvas: HTMLCanvasElement;
    let chartInstance: Chart | null = null;
    let tooltipData = $state<{
        visible: boolean;
        x: number;
        y: number;
        title: string;
        key: string;
        align: 'left' | 'right' | 'center';
        items: Array<{ label: string; value: number; color: string }>;
    }>({
        visible: false,
        x: 0,
        y: 0,
        title: '',
        key: '',
        align: 'center',
        items: [],
    });
    let dismissedTooltipKey = $state('');
    let activeTooltipSignature = $state('');
    const { resolvedAppearance } = themeState();
    const isDark = $derived(resolvedAppearance() === 'dark');

    const chartTheme = () =>
        isDark
            ? {
                  targetBorder: '#94a3b8',
                  targetFillStart: 'rgba(148, 163, 184, 0.24)',
                  targetFillEnd: 'rgba(148, 163, 184, 0.02)',
                  revenueBorder: '#38bdf8',
                  revenueFillStart: 'rgba(56, 189, 248, 0.36)',
                  revenueFillEnd: 'rgba(56, 189, 248, 0.02)',
                  targetPointFill: '#020617',
                  revenuePointFill: '#38bdf8',
                  targetPointBorder: '#94a3b8',
                  revenuePointBorder: '#020617',
                  tickColor: '#94a3b8',
                  gridColor: 'rgba(148, 163, 184, 0.18)',
                  panelClass:
                      'border-slate-800/80 bg-[linear-gradient(180deg,rgba(2,6,23,0.98),rgba(15,23,42,0.9))] shadow-inner shadow-slate-950/50',
                  titleClass: 'text-foreground',
                  subtitleClass: 'text-muted-foreground',
                  legendTextClass: 'text-slate-400',
                  chartTitleClass: 'text-sky-200',
                  chartBodyClass: 'text-slate-300/80',
                  totalLabelClass: 'text-slate-400',
                  totalValueClass: 'text-foreground',
                  footerLabelClass: 'text-slate-400',
                  footerValueClass: 'text-foreground',
              }
            : {
                  targetBorder: '#cbd5e1',
                  targetFillStart: 'rgba(203, 213, 225, 0.3)',
                  targetFillEnd: 'rgba(203, 213, 225, 0.0)',
                  revenueBorder: '#0ea5e9',
                  revenueFillStart: 'rgba(14, 165, 233, 0.4)',
                  revenueFillEnd: 'rgba(14, 165, 233, 0.01)',
                  targetPointFill: '#ffffff',
                  revenuePointFill: '#0ea5e9',
                  targetPointBorder: '#cbd5e1',
                  revenuePointBorder: '#ffffff',
                  tickColor: '#64748b',
                  gridColor: '#f1f5f9',
                  panelClass:
                      'border-border/80 bg-gradient-to-br from-background via-card to-sky-50/20 shadow-xs',
                  titleClass: 'text-foreground',
                  subtitleClass: 'text-muted-foreground',
                  legendTextClass: 'text-muted-foreground',
                  chartTitleClass: 'text-cyan-400',
                  chartBodyClass: 'text-white/70',
                  totalLabelClass: 'text-muted-foreground',
                  totalValueClass: 'text-foreground',
                  footerLabelClass: 'text-muted-foreground',
                  footerValueClass: 'text-foreground',
              };

    $effect(() => {
        if (!chartCanvas) return;

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        if (monthlyTrend.length === 0) return;

        const labels = monthlyTrend.map((t) => t.label || t.month_key);
        const dataTarget = monthlyTrend.map((t) =>
            Number(t.target_revenue || 0),
        );
        const dataRevenue = monthlyTrend.map((t) => Number(t.revenue || 0));

        const ctx = chartCanvas.getContext('2d');
        const theme = chartTheme();
        let gradientTarget: CanvasGradient | string = theme.targetFillStart;
        let gradientRevenue: CanvasGradient | string = theme.revenueFillStart;

        if (ctx) {
            gradientTarget = ctx.createLinearGradient(0, 0, 0, 300);
            gradientTarget.addColorStop(0, theme.targetFillStart);
            gradientTarget.addColorStop(1, theme.targetFillEnd);

            gradientRevenue = ctx.createLinearGradient(0, 0, 0, 300);
            gradientRevenue.addColorStop(0, theme.revenueFillStart);
            gradientRevenue.addColorStop(1, theme.revenueFillEnd);
        }

        chartInstance = new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Target Revenue',
                        data: dataTarget,
                        borderColor: theme.targetBorder,
                        backgroundColor: gradientTarget,
                        borderWidth: 2,
                        pointBackgroundColor: theme.targetPointFill,
                        pointBorderColor: theme.targetPointBorder,
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointHitRadius: 14,
                        fill: true,
                        tension: 0.4,
                        borderDash: [5, 5],
                    },
                    {
                        label: 'Total Revenue',
                        data: dataRevenue,
                        borderColor: theme.revenueBorder,
                        backgroundColor: gradientRevenue,
                        borderWidth: 2.5,
                        pointBackgroundColor: theme.revenuePointFill,
                        pointBorderColor: theme.revenuePointBorder,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointHitRadius: 14,
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: {
                                size: 10,
                                family: 'ui-sans-serif, system-ui',
                            },
                            color: theme.tickColor,
                            maxRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 12,
                        },
                        border: { display: false },
                    },
                    y: {
                        grid: {
                            color: theme.gridColor,
                            tickBorderDash: [4, 4],
                        },
                        ticks: { display: false },
                        border: { display: false },
                        beginAtZero: true,
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: false,
                        external: (context) => {
                            const { chart, tooltip } = context;

                            if (tooltip.opacity === 0) {
                                tooltipData.visible = false;
                                dismissedTooltipKey = '';
                                activeTooltipSignature = '';
                                return;
                            }

                            if (!tooltip.dataPoints?.length) {
                                tooltipData.visible = false;
                                return;
                            }

                            const dataIndex = tooltip.dataPoints[0].dataIndex;
                            const label = String(labels[dataIndex] ?? '');
                            const key = label || String(dataIndex);

                            if (dismissedTooltipKey === key) {
                                tooltipData.visible = false;
                                return;
                            }

                            const left = tooltip.caretX;
                            const width = chart.width;
                            let align: 'left' | 'right' | 'center' = 'center';

                            if (left < width * 0.2) align = 'left';
                            else if (left > width * 0.8) align = 'right';

                            const nextSignature = [
                                key,
                                align,
                                ...tooltip.dataPoints.flatMap((point) => [
                                    String(point.dataset.label ?? ''),
                                    String(point.dataIndex ?? 0),
                                    Number(point.raw ?? 0).toString(),
                                ]),
                            ].join('|');

                            if (
                                activeTooltipSignature === nextSignature &&
                                tooltipData.visible
                            ) {
                                return;
                            }

                            activeTooltipSignature = nextSignature;

                            tooltipData = {
                                visible: true,
                                x: tooltip.caretX,
                                y: tooltip.caretY,
                                title: label,
                                key,
                                align,
                                items: tooltip.dataPoints.map((point) => ({
                                    label: String(point.dataset.label ?? ''),
                                    value: Number(point.raw ?? 0),
                                    color: String(
                                        point.dataset.borderColor ??
                                            theme.revenueBorder,
                                    ),
                                })),
                            };
                        },
                    },
                },
            },
        });
    });

    onDestroy(() => {
        if (chartInstance) chartInstance.destroy();
    });

    const tooltipTranslateClass = $derived(
        tooltipData.align === 'left'
            ? 'translate-x-0'
            : tooltipData.align === 'right'
              ? '-translate-x-full'
              : '-translate-x-1/2',
    );
    const tooltipArrowClass = $derived(
        tooltipData.align === 'left'
            ? 'left-5'
            : tooltipData.align === 'right'
              ? 'right-5'
              : 'left-1/2 -translate-x-1/2',
    );
    const closeTooltip = () => {
        if (!tooltipData.visible) {
            return;
        }

        dismissedTooltipKey = tooltipData.key;
        activeTooltipSignature = '';
        tooltipData.visible = false;
    };
</script>

<div
    class={`flex w-full flex-col rounded-lg border p-4 text-card-foreground shadow-xs transition-all duration-300 hover:shadow-sm sm:p-5 ${chartTheme().panelClass}`}
>
    <!-- Header dan Legend Horisontal -->
    <div
        class="mb-4 flex flex-wrap items-start justify-between gap-y-3 gap-x-4"
    >
        <div class="min-w-0">
            <p class="text-[13px] font-semibold text-muted-foreground">
                Perbandingan <span class={`font-bold ${chartTheme().titleClass}`}
                    >Target vs Revenue</span
                >
            </p>
            <p class={`mt-0.5 text-[11px] ${chartTheme().subtitleClass}`}>
                Pemantauan target omset bulanan terhadap pencapaian aktual
            </p>
        </div>

        <!-- Legend -->
        <div class="flex shrink-0 items-center gap-3 pt-0.5">
            <div class="flex items-center gap-1.5">
                <div
                    class={`h-2 w-2 rounded-sm border ${isDark ? 'border-slate-500 bg-slate-700' : 'border-slate-300 bg-slate-100'}`}
                ></div>
                <span
                    class={`text-[10px] font-bold uppercase tracking-wider sm:text-[11px] ${chartTheme().legendTextClass}`}
                    >Target</span
                >
            </div>
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-sm bg-sky-500"></div>
                <span
                    class={`text-[10px] font-bold uppercase tracking-wider sm:text-[11px] ${chartTheme().legendTextClass}`}
                    >Revenue</span
                >
            </div>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="w-full pb-1">
        <div class="relative w-full" style="height: 250px;">
            {#if tooltipData.visible}
                <div
                    class={`pointer-events-none absolute z-20 w-[min(92vw,260px)] max-w-[calc(100vw-1rem)] rounded-xl bg-slate-900/96 px-3 py-2.5 text-white shadow-lg transition ${tooltipTranslateClass}`}
                    style="left: {tooltipData.x}px; top: {tooltipData.y -
                        10}px;"
                >
                    <div class="flex items-start justify-between gap-2">
                        <p class={`text-sm font-semibold ${chartTheme().chartTitleClass}`}>
                            {tooltipData.title}
                        </p>
                        <button
                            type="button"
                            class="pointer-events-auto rounded-full p-1 text-white/70 transition hover:bg-white/10 hover:text-white"
                            aria-label="Tutup tooltip"
                            onclick={closeTooltip}
                        >
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <div class="mt-2 space-y-1 text-[12px]">
                        {#each tooltipData.items as item (item.label)}
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span class={`flex items-center gap-1.5 ${chartTheme().chartBodyClass}`}>
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        style={`background-color:${item.color}`}
                                    ></span>
                                    {item.label}
                                </span>
                                <span class="font-semibold tabular-nums">
                                    {toCurrency(item.value)}
                                </span>
                            </div>
                        {/each}
                    </div>
                    <span
                        class={`absolute top-full h-3.5 w-3.5 -translate-y-1/2 rotate-45 bg-slate-900/96 ${tooltipArrowClass}`}
                    ></span>
                </div>
            {/if}
            <canvas bind:this={chartCanvas}></canvas>
        </div>
    </div>
</div>
