<script lang="ts">
    type ArmadaItem = {
        rank: number;
        nopol: string;
        trip_count: number;
        revenue: number;
        pool_name?: string | null;
        category?: string | null;
    };

    let {
        items = [],
        toCurrency,
        period = 'Bulan Ini',
    }: {
        items: ArmadaItem[];
        toCurrency: (val: number) => string;
        period?: string;
    } = $props();

    const visibleItems = $derived(items.slice(0, 5));
    const maxRevenue = $derived(
        visibleItems.length > 0 ? Math.max(...visibleItems.map((item) => item.revenue), 1) : 1,
    );
</script>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow sm:rounded-3xl sm:p-5">
    <div class="mb-3 flex items-start justify-between gap-3 sm:mb-4">
        <div>
            <p class="text-[13px] font-semibold text-slate-700 sm:text-sm">
                Peringkat <span class="font-bold text-slate-900">Performa Armada</span>
            </p>
            <p class="mt-0.5 text-[10px] text-slate-500 sm:text-[11px]">
                Berdasarkan revenue & trip - {period}
            </p>
        </div>
        <span class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-[10px] font-semibold text-slate-500">
            {visibleItems.length} unit
        </span>
    </div>

    {#if visibleItems.length === 0}
        <div class="flex h-20 items-center justify-center rounded-xl border border-dashed border-gray-200 text-[11px] text-slate-400">
            Belum ada data performa armada
        </div>
    {:else}
        <div class="space-y-2">
            {#each visibleItems as item, index (item.rank)}
                {@const barWidth = maxRevenue > 0 ? Math.max(6, Math.round((item.revenue / maxRevenue) * 100)) : 6}
                <div class={`group relative rounded-xl border p-2 transition ${index === 0 ? 'border-cyan-200 bg-cyan-50/60 hover:border-cyan-300 hover:bg-cyan-50' : 'border-gray-100 bg-gray-50/50 hover:border-gray-200 hover:bg-white hover:shadow-sm'}`}>
                    <div class="mb-1.5 flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex min-w-0 items-center gap-1.5">
                                <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[9px] font-bold text-slate-500">
                                    {item.rank}
                                </span>
                                <p class="truncate text-[12px] font-semibold text-slate-800">
                                    {item.nopol}
                                </p>
                            </div>
                            <p class="mt-0.5 truncate text-[9px] text-slate-400">
                                {item.pool_name || 'Semua Pool'}
                                {#if item.category}
                                    · {item.category}
                                {/if}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-600">
                            {item.trip_count} trip
                        </span>
                    </div>

                    <div class="h-1 overflow-hidden rounded-full bg-gray-200/70">
                        <div
                            class={`h-full rounded-full transition-all duration-700 ease-out ${index === 0 ? 'bg-gradient-to-r from-cyan-400 to-sky-500' : index === 1 ? 'bg-slate-400' : index === 2 ? 'bg-cyan-700/70' : 'bg-blue-400'}`}
                            style={`width:${barWidth}%`}
                        ></div>
                    </div>

                    <p class="mt-1 text-[10px] font-bold text-slate-700">
                        {toCurrency(item.revenue)}
                    </p>
                </div>
            {/each}
        </div>
    {/if}
</div>
