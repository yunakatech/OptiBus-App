<script lang="ts">
    import { onDestroy } from 'svelte';
    import { Chart, type ChartConfiguration } from 'chart.js/auto';

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
        currentMonthRevenue = 0,
        currentMonthLabel = 'Bulan ini',
        title = 'Tren Revenue 12 Bulan Terakhir',
        subtitle = 'Aktivitas Operasional',
        description = 'Grafik ini menampilkan total revenue per bulan. Kartu Command Center di atas memakai periode aktif yang sama agar angka lebih mudah dibandingkan.',
    }: {
        monthlyTrend: TrendItem[];
        toCurrency: (value: number) => string;
        currentMonthRevenue?: number;
        currentMonthLabel?: string;
        title?: string;
        subtitle?: string;
        description?: string;
    } = $props();

    let chartCanvas: HTMLCanvasElement;
    let chartInstance: Chart | null = null;
    let tooltipData = $state<{
        visible: boolean;
        x: number;
        y: number;
        title: string;
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
        revenue: 0,
        booking_revenue: 0,
        charter_revenue: 0,
        luggage_revenue: 0,
        align: 'center',
    });

    const trendRevenueTotal = $derived(
        monthlyTrend.reduce(
            (total, item) => total + Number(item.revenue || 0),
            0,
        ),
    );
    const trendRevenueAverage = $derived(
        monthlyTrend.length > 0 ? trendRevenueTotal / monthlyTrend.length : 0,
    );
    const trendRevenuePeak = $derived(
        monthlyTrend.reduce<TrendItem | null>(
            (best, item) =>
                !best || Number(item.revenue || 0) > Number(best.revenue || 0)
                    ? item
                    : best,
            monthlyTrend[0] ?? null,
        ),
    );

    $effect(() => {
        if (!chartCanvas) return;

        const labels = monthlyTrend.map(
            (item) => item.label || item.month_key || '',
        );
        const data = monthlyTrend.map((item) => Number(item.revenue || 0));

        if (chartInstance) {
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].data = data;
            chartInstance.update();
        } else {
            // Create Gradient
            const ctx = chartCanvas.getContext('2d');
            let gradientFill: CanvasGradient | string = 'rgba(14, 165, 233, 0.2)';
            if (ctx) {
                gradientFill = ctx.createLinearGradient(0, 0, 0, 300);
                gradientFill.addColorStop(0, 'rgba(14, 165, 233, 0.32)');
                gradientFill.addColorStop(1, 'rgba(14, 165, 233, 0.02)');
            }

            const config: ChartConfiguration = {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Total Revenue',
                            data,
                            borderColor: '#2563eb', // blue-600
                            backgroundColor: gradientFill,
                            borderWidth: 2,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4, // Smooth spline curves
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
                            grid: {
                                display: false,
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    family: 'ui-sans-serif, system-ui, sans-serif',
                                    weight: 500,
                                },
                                color: '#64748b',
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
                                color: '#f1f5f9',
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
                                    return;
                                }

                                if (tooltip.body) {
                                    const dataIndex = tooltip.dataPoints[0].dataIndex;
                                    const item = monthlyTrend[dataIndex];

                                    const left = tooltip.caretX;
                                    const width = chart.width;
                                    let align: 'left' | 'right' | 'center' = 'center';

                                    if (left < width * 0.2) align = 'left';
                                    else if (left > width * 0.8) align = 'right';

                                    tooltipData = {
                                        visible: true,
                                        x: tooltip.caretX,
                                        y: tooltip.caretY,
                                        title: item.name || item.date || item.label || '',
                                        revenue: Number(item.revenue || 0),
                                        booking_revenue: Number(item.booking_revenue || 0),
                                        charter_revenue: Number(item.charter_revenue || 0),
                                        luggage_revenue: Number(item.luggage_revenue || 0),
                                        align,
                                    };
                                }
                            },
                        },
                    },
                },
            };

            chartInstance = new Chart(chartCanvas, config);
        }
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
              : '-translate-x-1/2'
    );

    const tooltipArrowClass = $derived(
        tooltipData.align === 'left'
            ? 'left-5'
            : tooltipData.align === 'right'
              ? 'right-5'
              : 'left-1/2 -px-1 -translate-x-1/2'
    );
</script>

<div
    class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_2px_10px_rgba(0,0,0,0.02)] transition-all duration-300 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)]"
>


    <div class="relative flex-1 min-h-[220px] px-3 pb-4 pt-3 sm:min-h-[260px] sm:px-4">
        <div class="mb-5 px-1 sm:px-2">
            <h3 class="text-sm font-bold text-slate-800">Revenue Harian</h3>
            <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">Melihat pergerakan revenue operasional sistem Anda dalam 30 hari terakhir. Tooltip dapat di-hover untuk melihat rincian.</p>
        </div>

        <div class="mb-2 flex items-center justify-end text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
            <span class="normal-case tracking-normal">
                {monthlyTrend.length} titik
            </span>
        </div>

        {#if tooltipData.visible}
            <div
                class={`pointer-events-none absolute z-10 w-[min(92vw,240px)] max-w-[calc(100vw-1rem)] rounded-xl bg-slate-900/96 px-3 py-2.5 text-white shadow-2xl transition sm:rounded-2xl ${tooltipTranslateClass}`}
                style="left: {tooltipData.x}px; top: {tooltipData.y - 10}px;"
            >
                <p class="text-sm font-semibold text-cyan-400">
                    {tooltipData.title}
                </p>
                <div class="mt-2 space-y-1 text-[12px]">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/68">Booking</span>
                        <span class="font-semibold tabular-nums">
                            {toCurrency(tooltipData.booking_revenue)}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/68">Bagasi</span>
                        <span class="font-semibold tabular-nums">
                            {toCurrency(tooltipData.luggage_revenue)}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/68">Carter</span>
                        <span class="font-semibold tabular-nums">
                            {toCurrency(tooltipData.charter_revenue)}
                        </span>
                    </div>
                    <div class="mt-1 flex items-center justify-between gap-3 border-t border-white/12 pt-2">
                        <span class="font-medium text-white/68">Total</span>
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
            class="h-[180px] overflow-visible rounded-[22px] border border-slate-200/70 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(255,255,255,0.92))] p-2 shadow-inner sm:h-[220px] sm:p-3 md:h-[260px]"
        >
            <canvas bind:this={chartCanvas}></canvas>
        </div>
    </div>
</div>
