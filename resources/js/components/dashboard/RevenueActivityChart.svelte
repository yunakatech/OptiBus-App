<script lang="ts">
    import { X } from 'lucide-svelte';
    import { onDestroy } from 'svelte';
    import { Chart, type ChartConfiguration } from 'chart.js/auto';
    import { themeState } from '@/lib/theme.svelte';

    type TrendItem = {
        label: string;
        month_key?: string;
        date?: string;
        name?: string;
        revenue: number;
        transaction_count?: number;
        booking_revenue?: number;
        charter_revenue?: number;
        luggage_revenue?: number;
    };

    let {
        monthlyTrend = [],
        toCurrency,
        title = 'Tren Revenue 12 Bulan Terakhir',
        subtitle = 'Aktivitas Operasional',
    }: {
        monthlyTrend: TrendItem[];
        toCurrency: (value: number) => string;
        title?: string;
        subtitle?: string;
    } = $props();

    let chartCanvas: HTMLCanvasElement;
    let chartInstance: Chart | null = null;
    let tooltipData = $state<{
        visible: boolean;
        x: number;
        y: number;
        title: string;
        key: string;
        revenue: number;
        booking_revenue: number;
        charter_revenue: number;
        luggage_revenue: number;
        align: 'left' | 'right' | 'center';
    }>({
        visible: false,
        x: 0,
        y: 0,
        title: '',
        key: '',
        revenue: 0,
        booking_revenue: 0,
        charter_revenue: 0,
        luggage_revenue: 0,
        align: 'center',
    });
    let dismissedTooltipKey = $state('');
    let activeTooltipSignature = $state('');
    const { resolvedAppearance } = themeState();
    const isDark = $derived(resolvedAppearance() === 'dark');

    const trendPointKey = (item: TrendItem, index: number) =>
        `${item.month_key ?? item.date ?? item.label ?? index}`;

    const chartTheme = () =>
        isDark
            ? {
                  borderColor: '#38bdf8',
                  pointBorderColor: '#020617',
                  pointFillColor: '#38bdf8',
                  gradientStart: 'rgba(56, 189, 248, 0.3)',
                  gradientEnd: 'rgba(56, 189, 248, 0.02)',
                  tickColor: '#94a3b8',
                  gridColor: 'rgba(148, 163, 184, 0.18)',
                  panelClass:
                      'border-border/80 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900 shadow-inner shadow-slate-950/40',
                  titleClass: 'text-foreground',
                  subtitleClass:
                      'border-sky-400/20 bg-sky-950/30 text-sky-200',
                  countClass: 'text-muted-foreground',
                  chartTitleClass: 'text-sky-300',
                  chartBodyClass: 'text-slate-200/80',
              }
            : {
                  borderColor: '#2563eb',
                  pointBorderColor: '#ffffff',
                  pointFillColor: '#2563eb',
                  gradientStart: 'rgba(14, 165, 233, 0.32)',
                  gradientEnd: 'rgba(14, 165, 233, 0.02)',
                  tickColor: '#64748b',
                  gridColor: '#f1f5f9',
                  panelClass:
                      'border-border/80 bg-gradient-to-br from-background via-card to-sky-50/20 shadow-xs',
                  titleClass: 'text-foreground',
                  subtitleClass: 'border-sky-200 bg-sky-50 text-sky-700',
                  countClass: 'text-muted-foreground',
                  chartTitleClass: 'text-cyan-400',
                  chartBodyClass: 'text-white/70',
              };

    $effect(() => {
        if (!chartCanvas) return;
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        if (monthlyTrend.length === 0) {
            return;
        }

        const labels = monthlyTrend.map(
            (item) => item.label || item.month_key || '',
        );
        const data = monthlyTrend.map((item) => Number(item.revenue || 0));
        const theme = chartTheme();
        const ctx = chartCanvas.getContext('2d');
        let gradientFill: CanvasGradient | string = theme.gradientStart;

        if (ctx) {
            gradientFill = ctx.createLinearGradient(0, 0, 0, 300);
            gradientFill.addColorStop(0, theme.gradientStart);
            gradientFill.addColorStop(1, theme.gradientEnd);
        }

        const config: ChartConfiguration = {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Revenue',
                        data,
                        borderColor: theme.borderColor,
                        backgroundColor: gradientFill,
                        borderWidth: 2.5,
                        pointBackgroundColor: theme.pointFillColor,
                        pointBorderColor: theme.pointBorderColor,
                        pointBorderWidth: 2,
                        pointRadius: 3.25,
                        pointHoverRadius: 7,
                        pointHitRadius: 18,
                        fill: false,
                        tension: 0.32,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (event, elements, chart) => {
                    const nativeEvent = event.native as MouseEvent | undefined;
                    const hitElements = nativeEvent
                        ? chart.getElementsAtEventForMode(
                              nativeEvent,
                              'nearest',
                              { intersect: true },
                              true,
                          )
                        : elements;
                    const [firstElement] = hitElements;

                    if (!firstElement) {
                        return;
                    }

                    const x = nativeEvent?.offsetX ?? firstElement.element.x;
                    const y = nativeEvent?.offsetY ?? firstElement.element.y;

                    dismissedTooltipKey = '';
                    tooltipData.visible = false;
                    chart.tooltip?.setActiveElements(
                        [
                            {
                                datasetIndex: firstElement.datasetIndex,
                                index: firstElement.index,
                            },
                        ],
                        { x, y },
                    );
                    chart.setActiveElements([
                        {
                            datasetIndex: firstElement.datasetIndex,
                            index: firstElement.index,
                        },
                    ]);
                    chart.update();
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                                family: 'ui-sans-serif, system-ui, sans-serif',
                                weight: 500,
                            },
                            color: theme.tickColor,
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 6,
                        },
                        border: {
                            display: false,
                        },
                    },
                    y: {
                        grid: {
                            color: theme.gridColor,
                            tickBorderDash: [4, 4],
                        },
                        ticks: {
                            display: false,
                        },
                        border: {
                            display: false,
                        },
                        beginAtZero: true,
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
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
                            const item = monthlyTrend[dataIndex];
                            if (!item) {
                                tooltipData.visible = false;
                                return;
                            }

                            const key = trendPointKey(item, dataIndex);

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
                                Number(item.revenue || 0),
                                Number(item.booking_revenue || 0),
                                Number(item.charter_revenue || 0),
                                Number(item.luggage_revenue || 0),
                                item.name || '',
                                item.date || '',
                                item.label || '',
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
                                title:
                                    item.name || item.date || item.label || '',
                                key,
                                revenue: Number(item.revenue || 0),
                                booking_revenue: Number(
                                    item.booking_revenue || 0,
                                ),
                                charter_revenue: Number(
                                    item.charter_revenue || 0,
                                ),
                                luggage_revenue: Number(
                                    item.luggage_revenue || 0,
                                ),
                                align,
                            };
                        },
                    },
                },
            },
        };

        chartInstance = new Chart(chartCanvas, config);
    });

    onDestroy(() => {
        if (chartInstance) {
            chartInstance.destroy();
        }
    });

    // Custom Tooltip Classes
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
    class={`flex h-full flex-col overflow-hidden rounded-lg border text-card-foreground shadow-xs transition-all duration-300 hover:shadow-sm ${chartTheme().panelClass}`}
>
    <div
        class="relative flex-1 min-h-[220px] px-3 pb-4 pt-3 sm:min-h-[260px] sm:px-4"
    >
        <div class="mb-4 px-1 sm:px-2">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class={`text-sm font-bold ${chartTheme().titleClass}`}>
                    {title}
                </h3>
                <span
                    class={`rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] ${chartTheme().subtitleClass}`}
                >
                    {subtitle}
                </span>
            </div>
        </div>

        <div
            class={`mb-2 flex items-center justify-end text-[10px] font-medium uppercase tracking-[0.18em] ${chartTheme().countClass}`}
        >
            <span class="normal-case tracking-normal">
                {monthlyTrend.length} titik
            </span>
        </div>

        {#if tooltipData.visible}
            <div
                class={`pointer-events-none absolute z-20 w-[min(92vw,240px)] max-w-[calc(100vw-1rem)] rounded-xl bg-slate-900/96 px-3 py-2.5 text-white shadow-lg transition sm:rounded-lg ${tooltipTranslateClass}`}
                style="left: {tooltipData.x}px; top: {tooltipData.y - 10}px;"
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
                    <div class="flex items-center justify-between gap-3">
                        <span class={chartTheme().chartBodyClass}>Booking</span>
                        <span class="font-semibold tabular-nums">
                            {toCurrency(tooltipData.booking_revenue)}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class={chartTheme().chartBodyClass}>Bagasi</span>
                        <span class="font-semibold tabular-nums">
                            {toCurrency(tooltipData.luggage_revenue)}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class={chartTheme().chartBodyClass}>Carter</span>
                        <span class="font-semibold tabular-nums">
                            {toCurrency(tooltipData.charter_revenue)}
                        </span>
                    </div>
                    <div
                        class="mt-1 flex items-center justify-between gap-3 border-t border-white/12 pt-2"
                    >
                        <span class={`font-medium ${chartTheme().chartBodyClass}`}>
                            Total
                        </span>
                        <span class="font-bold text-emerald-400 tabular-nums">
                            {toCurrency(tooltipData.revenue)}
                        </span>
                    </div>
                </div>
                <span
                    class={`absolute top-full h-3.5 w-3.5 -translate-y-1/2 rotate-45 bg-slate-900/96 ${tooltipArrowClass}`}
                ></span>
            </div>
        {/if}

        <div
            class={`h-[180px] overflow-visible rounded-lg border p-2 shadow-inner sm:h-[220px] sm:p-3 md:h-[260px] ${isDark ? 'border-border/80 bg-[linear-gradient(180deg,rgba(15,23,42,0.94),rgba(15,23,42,0.82))] shadow-slate-950/40' : 'border-border/70 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(255,255,255,0.92))]'}`}
        >
            <canvas bind:this={chartCanvas} class="relative z-10 cursor-pointer"
            ></canvas>
        </div>
    </div>
</div>
