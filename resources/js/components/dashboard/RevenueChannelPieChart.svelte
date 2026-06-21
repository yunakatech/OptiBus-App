<script lang="ts">
    import { onDestroy } from 'svelte';
    import { Chart, PieController, ArcElement, Tooltip, Legend } from 'chart.js';

    Chart.register(PieController, ArcElement, Tooltip, Legend);

    type SummaryScopeStats = {
        total_bookings: number;
        revenue_booking: number;
        revenue_charter: number;
        revenue_luggage: number;
    };

    let {
        summaryStatsByScope,
        toCurrency,
    }: {
        summaryStatsByScope: Record<'day' | 'month' | 'year', SummaryScopeStats>;
        toCurrency: (val: number) => string;
    } = $props();

    let selectedScope = $state<'month' | 'year'>('month');
    let summaryStats = $derived(summaryStatsByScope[selectedScope]);

    let chartCanvas: HTMLCanvasElement;
    let chartInstance: Chart | null = null;

    $effect(() => {
        if (!chartCanvas) return;
        
        let dataValues = [
            Number(summaryStats.revenue_booking || 0),
            Number(summaryStats.revenue_charter || 0),
            Number(summaryStats.revenue_luggage || 0)
        ];

        // Validate data presence so the chart draws something gracefully if missing
        const hasData = dataValues.some(v => v > 0);
        if (!hasData) {
            dataValues = [1, 1, 1]; // Provide dummy ratio so a grey circle can be rendered
        }

        if (chartInstance) {
            chartInstance.data.datasets[0].data = dataValues;
            chartInstance.data.datasets[0].backgroundColor = hasData ? [
                'rgba(56, 189, 248, 0.9)', // Sky 400
                'rgba(129, 140, 248, 0.9)', // Indigo 400
                'rgba(52, 211, 153, 0.9)', // Emerald 400
            ] : ['#e2e8f0', '#e2e8f0', '#e2e8f0'];
            chartInstance.update();
        } else {
            chartInstance = new Chart(chartCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Booking', 'Carter', 'Bagasi'],
                    datasets: [
                        {
                            data: dataValues,
                            backgroundColor: hasData ? [
                                'rgba(56, 189, 248, 0.9)',
                                'rgba(129, 140, 248, 0.9)',
                                'rgba(52, 211, 153, 0.9)',
                            ] : ['#e2e8f0', '#e2e8f0', '#e2e8f0'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a', // non-transparent
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: 'rgba(51, 65, 85, 1)', // border-slate-700
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    if (!hasData) return ' Rp 0';
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.raw !== null) {
                                        label += toCurrency(context.raw as number);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
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
        Number(summaryStats.revenue_luggage || 0)
    );
</script>

<div class="flex flex-col rounded-3xl border border-gray-200 bg-white p-4 shadow-[0_2px_10px_rgba(0,0,0,0.02)] transition-all duration-300 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] md:p-5">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[13px] font-semibold text-slate-700">
                Sebaran <span class="font-bold text-slate-900">Revenue</span>
            </p>
            <p class="mt-0.5 text-[11px] text-slate-500">
                Layanan
            </p>
        </div>
        <div class="flex rounded-lg bg-gray-100/80 p-0.5">
            <button class={`px-2 py-1 text-[10px] font-semibold rounded-md ${selectedScope === 'month' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'}`} onclick={() => selectedScope = 'month'}>Bulan Ini</button>
            <button class={`px-2 py-1 text-[10px] font-semibold rounded-md ${selectedScope === 'year' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'}`} onclick={() => selectedScope = 'year'}>Tahun Ini</button>
        </div>
    </div>

    <div class="relative mt-5 h-[160px] w-full">
        <canvas bind:this={chartCanvas}></canvas>
        <div class="absolute inset-0 flex pointer-events-none flex-col items-center justify-center pt-2">
            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Total</span>
            <span class="mt-0.5 text-[15px] font-extrabold tracking-tight text-slate-800">{toCurrency(activeTotalRevenue)}</span>
        </div>
    </div>

    <div class="mt-auto pt-6 grid grid-cols-3 gap-2">
        <div class="text-center">
            <div class="mx-auto mb-1.5 h-2.5 w-2.5 rounded-full shadow-sm bg-sky-400"></div>
            <p class="text-[9px] uppercase tracking-wider font-semibold text-slate-500">Booking</p>
            <p class="mt-0.5 text-[11px] font-bold text-slate-800 break-words">{toCurrency(summaryStats.revenue_booking)}</p>
        </div>
        <div class="text-center relative">
            <div class="absolute -left-1 top-1 bottom-1 w-px bg-slate-100"></div>
            <div class="mx-auto mb-1.5 h-2.5 w-2.5 rounded-full shadow-sm bg-indigo-400"></div>
            <p class="text-[9px] uppercase tracking-wider font-semibold text-slate-500">Carter</p>
            <p class="mt-0.5 text-[11px] font-bold text-slate-800 break-words">{toCurrency(summaryStats.revenue_charter)}</p>
        </div>
        <div class="text-center relative">
            <div class="absolute -left-1 top-1 bottom-1 w-px bg-slate-100"></div>
            <div class="mx-auto mb-1.5 h-2.5 w-2.5 rounded-full shadow-sm bg-emerald-400"></div>
            <p class="text-[9px] uppercase tracking-wider font-semibold text-slate-500">Bagasi</p>
            <p class="mt-0.5 text-[11px] font-bold text-slate-800 break-words">{toCurrency(summaryStats.revenue_luggage)}</p>
        </div>
    </div>
</div>
