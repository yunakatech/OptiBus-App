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
    }: {
        monthlyTrend: TrendItem[];
        toCurrency: (value: number) => string;
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
    class="flex flex-col h-full rounded-3xl border border-gray-200 bg-[#FFFFFF] shadow-[0_2px_10px_rgba(0,0,0,0.02)] transition-all duration-300 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)]"
>
    <!-- Header -->
    <div class="px-5 pt-5 pb-2 border-b border-gray-50/80">
        <div class="flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-600">
                    Aktivitas Operasional
                </p>
                <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900">
                    Tren Revenue 12 Bulan Terakhir
                </h3>
            </div>
            <!-- Interactive Badge / Status Indicator -->
            <div class="hidden md:flex items-center gap-2 rounded-full border border-sky-100 bg-sky-50/50 px-3 py-1.5 shadow-[inset_0_1px_2px_rgba(0,0,0,0.02)]">
                <span class="relative flex h-2.5 w-2.5">
                  <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"></span>
                  <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                </span>
                <span class="text-[11px] font-semibold tracking-wide text-sky-700">LIVE DATA</span>
            </div>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="relative flex-1 min-h-[260px] p-4 h-[280px]">
        {#if tooltipData.visible}
            <div
                class={`pointer-events-none absolute z-10 w-[240px] max-w-[calc(100vw-3rem)] rounded-xl bg-slate-900/95 backdrop-blur-md px-3.5 py-3 text-white shadow-2xl transition sm:rounded-2xl ${tooltipTranslateClass}`}
                style="left: {tooltipData.x}px; top: {tooltipData.y - 10}px;"
            >
                <p class="text-sm font-bold text-cyan-400 tracking-wide">
                    {tooltipData.title}
                </p>
                <div class="mt-2.5 space-y-1.5 text-[12px]">
                    <div class="flex items-center justify-between gap-3 group">
                        <span class="text-white/70 transition-colors group-hover:text-white/90">Booking</span>
                        <span class="font-semibold tabular-nums">{toCurrency(tooltipData.booking_revenue)}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 group">
                        <span class="text-white/70 transition-colors group-hover:text-white/90">Bagasi</span>
                        <span class="font-semibold tabular-nums">{toCurrency(tooltipData.luggage_revenue)}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 group">
                        <span class="text-white/70 transition-colors group-hover:text-white/90">Carter</span>
                        <span class="font-semibold tabular-nums">{toCurrency(tooltipData.charter_revenue)}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-white/15 pt-2 mt-2">
                        <span class="text-white/70 font-medium">Total</span>
                        <span class="font-bold text-emerald-400 tabular-nums">{toCurrency(tooltipData.revenue)}</span>
                    </div>
                </div>
                <!-- Tooltip Arrow -->
                <span
                    class={`absolute top-full h-3.5 w-3.5 -translate-y-1/2 rotate-45 bg-slate-900/95 border-r border-b border-slate-700/50 ${tooltipArrowClass}`}
                ></span>
            </div>
        {/if}

        <canvas bind:this={chartCanvas}></canvas>
    </div>
</div>
