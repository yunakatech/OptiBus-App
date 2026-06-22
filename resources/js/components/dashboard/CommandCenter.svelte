<script lang="ts">
    import { onDestroy } from 'svelte';
    import { Chart, type ChartConfiguration } from 'chart.js/auto';

    let {
        monthlyTrend = [],
        toCurrency,
    }: {
        monthlyTrend: any[];
        toCurrency: (val: number) => string;
    } = $props();

    let chartCanvas: HTMLCanvasElement;
    let chartInstance: Chart | null = null;

    $effect(() => {
        if (!chartCanvas || monthlyTrend.length === 0) return;

        const labels = monthlyTrend.map((t) => t.label || t.month_key);
        const dataTarget = monthlyTrend.map((t) =>
            Number(t.target_revenue || 0),
        );
        const dataRevenue = monthlyTrend.map((t) => Number(t.revenue || 0));

        const ctx = chartCanvas.getContext('2d');
        let gradientTarget: CanvasGradient | string =
            'rgba(203, 213, 225, 0.2)'; // slate-300
        let gradientRevenue: CanvasGradient | string =
            'rgba(14, 165, 233, 0.4)'; // sky-500

        if (ctx) {
            gradientTarget = ctx.createLinearGradient(0, 0, 0, 300);
            gradientTarget.addColorStop(0, 'rgba(203, 213, 225, 0.3)');
            gradientTarget.addColorStop(1, 'rgba(203, 213, 225, 0.0)');

            gradientRevenue = ctx.createLinearGradient(0, 0, 0, 300);
            gradientRevenue.addColorStop(0, 'rgba(14, 165, 233, 0.4)');
            gradientRevenue.addColorStop(1, 'rgba(14, 165, 233, 0.01)');
        }

        if (chartInstance) {
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].data = dataTarget;
            chartInstance.data.datasets[1].data = dataRevenue;
            chartInstance.update();
        } else {
            chartInstance = new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Target Revenue',
                            data: dataTarget,
                            borderColor: '#cbd5e1', // slate-300
                            backgroundColor: gradientTarget,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            fill: true,
                            tension: 0.4,
                            borderDash: [5, 5],
                        },
                        {
                            label: 'Total Revenue',
                            data: dataRevenue,
                            borderColor: '#0ea5e9', // sky-500
                            backgroundColor: gradientRevenue,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#0ea5e9',
                            pointBorderColor: '#ffffff',
                            pointRadius: 0,
                            pointHoverRadius: 6,
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
                                color: '#64748b',
                                maxRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 12,
                            },
                            border: { display: false },
                        },
                        y: {
                            grid: {
                                color: '#f1f5f9',
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
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#e2e8f0',
                            borderColor: '#334155',
                            borderWidth: 1,
                            callbacks: {
                                label: (ctx) => {
                                    return (
                                        ctx.dataset.label +
                                        ': ' +
                                        toCurrency(ctx.raw as number)
                                    );
                                },
                            },
                        },
                    },
                },
            });
        }
    });

    onDestroy(() => {
        if (chartInstance) chartInstance.destroy();
    });
</script>

<div
    class="flex w-full flex-col rounded-3xl border border-gray-200 bg-[#FFFFFF] p-4 shadow-[0_2px_10px_rgba(0,0,0,0.02)] transition-all duration-300 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] sm:p-5"
>
    <!-- Header dan Legend Horisontal -->
    <div
        class="mb-4 flex flex-wrap items-start justify-between gap-y-3 gap-x-4"
    >
        <div class="min-w-0">
            <p class="text-[13px] font-semibold text-slate-700">
                Perbandingan <span class="font-bold text-slate-900"
                    >Target vs Revenue</span
                >
            </p>
            <p class="mt-0.5 text-[11px] text-slate-500">
                Pemantauan target omset bulanan terhadap pencapaian aktual
            </p>
        </div>
        
        <!-- Legend -->
        <div class="flex shrink-0 items-center gap-3 pt-0.5">
            <div class="flex items-center gap-1.5">
                <div
                    class="h-2 w-2 rounded-sm border border-slate-300 bg-slate-100"
                ></div>
                <span
                    class="text-[10px] font-bold uppercase tracking-wider text-slate-500 sm:text-[11px]"
                    >Target</span
                >
            </div>
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-sm bg-sky-500"></div>
                <span
                    class="text-[10px] font-bold uppercase tracking-wider text-slate-500 sm:text-[11px]"
                    >Revenue</span
                >
            </div>
        </div>
    </div>

    <!-- Chart Container yang memiliki scroll horizontal di mobile dan batasan tinggi aspect-->
    <div class="w-full overflow-x-auto overflow-y-hidden pb-1">
        <div class="relative min-w-[500px] w-full" style="height: 250px;">
            <canvas bind:this={chartCanvas}></canvas>
        </div>
    </div>
</div>
