<script lang="ts">
    import { X } from 'lucide-svelte';
    import { onDestroy } from 'svelte';
    import {
        Chart,
        PieController,
        ArcElement,
        Tooltip,
        Legend,
    } from 'chart.js';

    Chart.register(PieController, ArcElement, Tooltip, Legend);

    type SummaryScopeStats = {
        total_bookings: number;
        total_passengers: number;
        revenue_booking: number;
        revenue_charter: number;
        revenue_luggage: number;
    };

    let {
        summaryStatsByScope,
        toCurrency,
    }: {
        summaryStatsByScope: Record<
            'day' | 'month' | 'year',
            SummaryScopeStats
        >;
        toCurrency: (val: number) => string;
    } = $props();

    let selectedScope = $state<'month' | 'year'>('month');
    let summaryStats = $derived(summaryStatsByScope[selectedScope]);

    let chartCanvas: HTMLCanvasElement;
    let chartInstance: Chart | null = null;
    let tooltipData = $state<{
        visible: boolean;
        x: number;
        y: number;
        key: string;
        label: string;
        value: number;
        align: 'left' | 'right' | 'center';
    }>({
        visible: false,
        x: 0,
        y: 0,
        key: '',
        label: '',
        value: 0,
        align: 'center',
    });
    let dismissedTooltipKey = $state('');
    let activeTooltipSignature = $state('');

    $effect(() => {
        if (!chartCanvas) return;

        let dataValues = [
            Number(summaryStats.revenue_booking || 0),
            Number(summaryStats.revenue_charter || 0),
            Number(summaryStats.revenue_luggage || 0),
        ];

        // Validate data presence so the chart draws something gracefully if missing
        const hasData = dataValues.some((v) => v > 0);
        if (!hasData) {
            dataValues = [1, 1, 1]; // Provide dummy ratio so a grey circle can be rendered
        }

        if (chartInstance) {
            chartInstance.data.datasets[0].data = dataValues;
            chartInstance.data.datasets[0].backgroundColor = hasData
                ? [
                      'rgba(56, 189, 248, 0.9)', // Sky 400
                      'rgba(129, 140, 248, 0.9)', // Indigo 400
                      'rgba(52, 211, 153, 0.9)', // Emerald 400
                  ]
                : ['#e2e8f0', '#e2e8f0', '#e2e8f0'];
            chartInstance.update();
        } else {
            chartInstance = new Chart(chartCanvas, {
                type: 'pie',
                data: {
                    labels: ['Booking', 'Carter', 'Bagasi'],
                    datasets: [
                        {
                            data: dataValues,
                            backgroundColor: hasData
                                ? [
                                      'rgba(56, 189, 248, 0.9)',
                                      'rgba(129, 140, 248, 0.9)',
                                      'rgba(52, 211, 153, 0.9)',
                                  ]
                                : ['#e2e8f0', '#e2e8f0', '#e2e8f0'],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 12,
                            offset: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'nearest',
                        intersect: false,
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

                                const point = tooltip.dataPoints[0];
                                const label = String(point.label || '');
                                const key = label || String(point.dataIndex);

                                if (dismissedTooltipKey === key) {
                                    tooltipData.visible = false;
                                    return;
                                }

                                const left = tooltip.caretX;
                                const width = chart.width;
                                let align: 'left' | 'right' | 'center' =
                                    'center';

                                if (left < width * 0.2) align = 'left';
                                else if (left > width * 0.8) align = 'right';

                                const nextSignature = [
                                    key,
                                    align,
                                    Number(point.raw ?? 0),
                                    label,
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
                                    key,
                                    label,
                                    value: hasData ? Number(point.raw ?? 0) : 0,
                                    align,
                                };
                            },
                        },
                    },
                },
            });
        }
    });

    onDestroy(() => {
        if (chartInstance) {
            chartInstance.destroy();
        }
    });

    const activeTotalRevenue = $derived(
        Number(summaryStats.revenue_booking || 0) +
            Number(summaryStats.revenue_charter || 0) +
            Number(summaryStats.revenue_luggage || 0),
    );

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
    class="flex flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-[0_2px_10px_rgba(0,0,0,0.02)] transition-all duration-300 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] md:p-5"
>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[13px] font-semibold text-slate-700">
                Sebaran <span class="font-bold text-slate-900">Revenue</span>
            </p>
            <p class="mt-0.5 text-[11px] text-slate-500">Layanan</p>
        </div>
        <div class="flex rounded-lg bg-gray-100/80 p-0.5">
            <button
                class={`px-2 py-1 text-[10px] font-semibold rounded-md ${selectedScope === 'month' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'}`}
                onclick={() => (selectedScope = 'month')}>Bulan Ini</button
            >
            <button
                class={`px-2 py-1 text-[10px] font-semibold rounded-md ${selectedScope === 'year' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'}`}
                onclick={() => (selectedScope = 'year')}>Tahun Ini</button
            >
        </div>
    </div>

    <div class="relative mt-5 h-[160px] w-full">
        {#if tooltipData.visible}
            <div
                class={`pointer-events-none absolute z-20 w-[min(92vw,220px)] max-w-[calc(100vw-1rem)] rounded-xl bg-slate-900/96 px-3 py-2.5 text-white shadow-lg transition ${tooltipTranslateClass}`}
                style="left: {tooltipData.x}px; top: {tooltipData.y - 10}px;"
            >
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-cyan-400">
                        {tooltipData.label}
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
                <div class="mt-2 text-[12px]">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/68">Total Revenue</span>
                        <span class="font-semibold tabular-nums">
                            {toCurrency(tooltipData.value)}
                        </span>
                    </div>
                </div>
                <span
                    class={`absolute top-full h-3.5 w-3.5 -translate-y-1/2 rotate-45 bg-slate-900/96 ${tooltipArrowClass}`}
                ></span>
            </div>
        {/if}
        <canvas bind:this={chartCanvas} class="relative z-10"></canvas>
        <div
            class="absolute inset-0 z-0 flex pointer-events-none flex-col items-center justify-center pt-2"
        >
            <span
                class="text-[9px] font-bold uppercase tracking-widest text-slate-400"
                >Total</span
            >
            <span
                class="mt-0.5 text-[15px] font-extrabold tracking-tight text-slate-800"
                >{toCurrency(activeTotalRevenue)}</span
            >
        </div>
    </div>

    <div class="mt-auto pt-6 grid grid-cols-3 gap-2">
        <div class="text-center">
            <div
                class="mx-auto mb-1.5 h-2.5 w-2.5 rounded-full shadow-sm bg-sky-400"
            ></div>
            <p
                class="text-[9px] uppercase tracking-wider font-semibold text-slate-500"
            >
                Booking
            </p>
            <p class="mt-0.5 text-[11px] font-bold text-slate-800 break-words">
                {toCurrency(summaryStats.revenue_booking)}
            </p>
        </div>
        <div class="text-center relative">
            <div
                class="absolute -left-1 top-1 bottom-1 w-px bg-slate-100"
            ></div>
            <div
                class="mx-auto mb-1.5 h-2.5 w-2.5 rounded-full shadow-sm bg-indigo-400"
            ></div>
            <p
                class="text-[9px] uppercase tracking-wider font-semibold text-slate-500"
            >
                Carter
            </p>
            <p class="mt-0.5 text-[11px] font-bold text-slate-800 break-words">
                {toCurrency(summaryStats.revenue_charter)}
            </p>
        </div>
        <div class="text-center relative">
            <div
                class="absolute -left-1 top-1 bottom-1 w-px bg-slate-100"
            ></div>
            <div
                class="mx-auto mb-1.5 h-2.5 w-2.5 rounded-full shadow-sm bg-emerald-400"
            ></div>
            <p
                class="text-[9px] uppercase tracking-wider font-semibold text-slate-500"
            >
                Bagasi
            </p>
            <p class="mt-0.5 text-[11px] font-bold text-slate-800 break-words">
                {toCurrency(summaryStats.revenue_luggage)}
            </p>
        </div>
    </div>
</div>
