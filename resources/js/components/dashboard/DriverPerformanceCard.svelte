<script lang="ts">
    type DriverItem = {
        rank: number;
        name: string;
        trip_count: number;
        revenue: number;
        route?: string | null;
    };

    let {
        drivers = [],
        toCurrency,
        period = 'Bulan Ini',
    }: {
        drivers: DriverItem[];
        toCurrency: (val: number) => string;
        period?: string;
    } = $props();

    const maxRevenue = $derived(
        drivers.length > 0 ? Math.max(...drivers.map((d) => d.revenue)) : 1,
    );

    const medalColors = ['#f59e0b', '#94a3b8', '#b45309']; // gold, silver, bronze
    const medalLabel = ['🥇', '🥈', '🥉'];
</script>

<div
    class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition-all duration-300 hover:shadow"
>
    <!-- Header -->
    <div class="mb-3 flex items-start justify-between gap-3">
        <div>
            <p class="text-[13px] font-semibold text-slate-700">
                Peringkat <span class="font-bold text-slate-900"
                    >Performa Driver</span
                >
            </p>
            <p class="mt-0.5 text-[11px] text-slate-500">
                Berdasarkan jumlah trip & revenue — {period}
            </p>
        </div>
        <a
            href="/admin-ops/drivers"
            class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-[10px] font-semibold text-slate-500 transition hover:border-gray-300 hover:bg-white hover:text-slate-700"
        >
            Lihat Detail →
        </a>
    </div>

    {#if drivers.length === 0}
        <div
            class="flex h-20 items-center justify-center rounded-xl border border-dashed border-gray-200 text-[11px] text-slate-400"
        >
            Belum ada data performa driver
        </div>
    {:else}
        <div class="space-y-2">
            {#each drivers as driver, i (driver.rank)}
                {@const barWidth =
                    maxRevenue > 0
                        ? Math.max(6, Math.round((driver.revenue / maxRevenue) * 100))
                        : 6}
                {@const isMedal = i < 3}
                <div
                    class={`group relative rounded-xl border p-2 transition ${i === 0 ? 'border-amber-200 bg-amber-50/60 hover:border-amber-300 hover:bg-amber-50' : 'border-gray-100 bg-gray-50/50 hover:border-gray-200 hover:bg-white hover:shadow-sm'}`}
                >
                    <!-- Top row: rank + name + trips -->
                    <div class="mb-1.5 flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-1.5">
                            <span
                                class="shrink-0 text-[13px] leading-none"
                                aria-hidden="true"
                            >
                                {#if isMedal}
                                    {medalLabel[i]}
                                {:else}
                            <span
                                        class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-slate-100 text-[9px] font-bold text-slate-500"
                                        >{driver.rank}</span
                                    >
                                {/if}
                            </span>
                            <p
                                class="truncate text-[12px] font-semibold text-slate-800"
                            >
                                {driver.name}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-600"
                        >
                            {driver.trip_count} trip
                        </span>
                    </div>

                    <!-- Revenue bar -->
                    <div
                        class="h-1 overflow-hidden rounded-full bg-gray-200/70"
                    >
                        <div
                            class={`h-full rounded-full transition-all duration-700 ease-out ${i === 0 ? 'bg-gradient-to-r from-amber-400 to-yellow-500' : i === 1 ? 'bg-slate-400' : i === 2 ? 'bg-amber-700/70' : 'bg-blue-400'}`}
                            style={`width:${barWidth}%`}
                        ></div>
                    </div>

                    <!-- Revenue amount -->
                    <p class="mt-1 text-[10px] font-bold text-slate-700">
                        {toCurrency(driver.revenue)}
                    </p>

                    {#if driver.route}
                        <p class="mt-0.5 truncate text-[9px] text-slate-400">
                            Rute: {driver.route}
                        </p>
                    {/if}
                </div>
            {/each}
        </div>
    {/if}
</div>
