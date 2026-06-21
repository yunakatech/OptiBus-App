<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
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

    onMount(() => {
        if (!chartCanvas) return;

        const labels = monthlyTrend.map(
            (item) => item.label || item.month_key || '',
        );
        const data = monthlyTrend.map((item) => Number(item.revenue || 0));

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
                layout: {
                    padding: {
                        top: 20,
                        bottom: 10,
                        left: -5,
                        right: 15, // extra padding for rightmost tooltip to fit inside container
                    },
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
                            color: '#64748b', // slate-500
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
                            color: '#f1f5f9', // slate-100
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
                        enabled: false, // disable native tooltip, use custom HTML tooltip
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
    <div class="border-b border-slate-100/80 px-4 py-4 sm:px-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-600">
                    {subtitle}
                </p>
                <h3 class="mt-1 text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
                    {title}
                </h3>
                <p class="mt-1 text-xs leading-relaxed text-slate-500 max-w-xl">
                    {description}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-semibold text-slate-700">
                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                    {currentMonthLabel}: {toCurrency(currentMonthRevenue)}
                </div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {monthlyTrend.length} bulan
                </div>
            </div>
        </div>

        <div class="mt-3 grid gap-2 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 px-3 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                    {currentMonthLabel}
                </p>
                <p class="mt-1 text-sm font-bold text-slate-900">
                    {toCurrency(currentMonthRevenue)}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 px-3 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                    Rata-rata data
                </p>
                <p class="mt-1 text-sm font-bold text-slate-900">
                    {toCurrency(trendRevenueAverage)}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 px-3 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                    Puncak data
                </p>
                <p class="mt-1 text-sm font-bold text-slate-900">
                    {trendRevenuePeak
                        ? `${trendRevenuePeak.label || trendRevenuePeak.name || '-'} • ${toCurrency(trendRevenuePeak.revenue)}`
                        : '-'}
                </p>
            </div>
        </div>
    </div>

    <div class="relative flex-1 min-h-[220px] px-3 pb-4 pt-3 sm:min-h-[260px] sm:px-4">
        <div class="mb-2 flex items-center justify-between gap-2 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
            <span>Revenue harian</span>
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
