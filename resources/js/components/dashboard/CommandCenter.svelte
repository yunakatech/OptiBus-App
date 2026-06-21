<script lang="ts">
    import { ArrowRight } from 'lucide-svelte';

    type DashboardStats = {
        total_bookings: number;
        revenue_total_today: number;
        revenue_total_month: number;
        // ... include necessary fields from Dashboard.svelte or keep it any for now
    };

    type SummaryScopeStats = {
        total_bookings: number;
        revenue_booking: number;
        revenue_charter: number;
        revenue_luggage: number;
        bop_booking: number;
        bop_charter: number;
        margin_booking: number;
        margin_charter: number;
        target_revenue: number;
        achievement_percent: number;
    };

    type SummaryPeriodMeta = {
        current_label: string;
        previous_label: string;
        subtitle_label: string;
    };

    let {
        stats,
        summaryStatsByScope,
        summaryPeriodByScope,
        toCurrency,
    }: {
        stats: any;
        summaryStatsByScope: Record<'day' | 'month' | 'year', SummaryScopeStats>;
        summaryPeriodByScope: Record<
            'day' | 'month' | 'year',
            SummaryPeriodMeta
        >;
        toCurrency: (value: number) => string;
    } = $props();

    type Scope = 'day' | 'month' | 'year';
    let activeScope = $state<Scope>('day');

    const activeSummaryStats = $derived(summaryStatsByScope[activeScope]);
    const activeSummaryPeriod = $derived(summaryPeriodByScope[activeScope]);
    const activeRevenueLabel = $derived(
        `${activeSummaryPeriod.current_label} • total channel`,
    );

    const activeTotalRevenue = $derived(
        Number(activeSummaryStats.revenue_booking || 0) +
        Number(activeSummaryStats.revenue_charter || 0) +
        Number(activeSummaryStats.revenue_luggage || 0)
    );

    const activeAchievementWidth = $derived(
        Math.min(
            Math.max(Number(activeSummaryStats.achievement_percent || 0), 0),
            100
        )
    );

    const revenueChannels = $derived([
        {
            key: 'booking',
            label: 'Booking',
            value: activeSummaryStats.revenue_booking,
            href: '/bookings',
        },
        {
            key: 'carter',
            label: 'Carter',
            value: activeSummaryStats.revenue_charter,
            href: '/charters',
        },
        {
            key: 'bagasi',
            label: 'Bagasi',
            value: activeSummaryStats.revenue_luggage,
            href: '/luggages',
        },
    ]);
</script>

<div
    class="flex flex-col rounded-3xl border border-gray-200 bg-[#FFFFFF] p-4 shadow-[0_2px_10px_rgba(0,0,0,0.02)] transition-all duration-300 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)]"
>
    <!-- Header & Toggle -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-[13px] font-semibold text-slate-700">
                Ringkasan Revenue
                <span class="font-bold text-slate-900">{activeSummaryPeriod.current_label}</span>
            </p>
            <p class="mt-0.5 text-[11px] text-slate-500">
                Booking + Carter + Bagasi untuk periode aktif
            </p>
        </div>

        <!-- Pill Toggle -->
        <div class="flex items-center rounded-full border border-gray-200 bg-gray-50/80 p-0.5 shadow-[inset_0_1px_2px_rgba(0,0,0,0.02)]">
            <button
                class="relative rounded-full px-3 py-1 text-[11px] font-semibold transition-colors duration-200 {activeScope === 'day' ? 'text-slate-800 shadow-sm ring-1 ring-black/5 bg-white' : 'text-slate-500 hover:text-slate-700'}"
                onclick={() => (activeScope = 'day')}
            >
                Hari Ini
            </button>
            <button
                class="relative rounded-full px-3 py-1 text-[11px] font-semibold transition-colors duration-200 {activeScope === 'month' ? 'text-slate-800 shadow-sm ring-1 ring-black/5 bg-white' : 'text-slate-500 hover:text-slate-700'}"
                onclick={() => (activeScope = 'month')}
            >
                Bulan Ini
            </button>
            <button
                class="relative rounded-full px-3 py-1 text-[11px] font-semibold transition-colors duration-200 {activeScope === 'year' ? 'text-slate-800 shadow-sm ring-1 ring-black/5 bg-white' : 'text-slate-500 hover:text-slate-700'}"
                onclick={() => (activeScope = 'year')}
            >
                Tahun Ini
            </button>
        </div>
    </div>

    <!-- Main Value -->
    <div class="mt-4 flex items-start justify-between">
        <div>
            <h3 class="text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl">
                {toCurrency(activeScope === 'day' ? stats.revenue_total_today : activeScope === 'month' ? stats.revenue_total_month : stats.revenue_total_year)}
            </h3>
            <p class="mt-0.5 text-[11px] font-medium text-slate-500 uppercase tracking-wide">
                {activeRevenueLabel}
            </p>
        </div>
        <a
            href="/reports"
            class="group inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-slate-400 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-600"
            aria-label="Buka laporan revenue"
        >
            <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
        </a>
    </div>

    <!-- Achievement Progress -->
    <div class="mt-5 space-y-2">
        <div class="flex items-end justify-between">
            <div>
                <p class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Target {activeSummaryPeriod.current_label}</p>
                <p class="mt-0.5 text-sm font-bold text-slate-800">
                    {toCurrency(activeSummaryStats.target_revenue)}
                </p>
            </div>
            <div class="text-right">
                <p class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Terikumpul</p>
                <p class="mt-0.5 text-sm font-bold text-emerald-600">
                    {activeAchievementWidth}%
                </p>
            </div>
        </div>
        
        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100 shadow-[inset_0_1px_2px_rgba(0,0,0,0.05)]">
            <div
                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 transition-all duration-700 ease-out"
                style={`width:${activeAchievementWidth}%`}
            ></div>
        </div>
    </div>

    <!-- Channel Breakdown -->
    <div class="mt-auto pt-5">
        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">
            Detail Channel
        </p>
        <div class="space-y-2">
            {#each revenueChannels as channel (channel.key)}
                {@const width =
                    activeTotalRevenue > 0
                        ? Math.max(
                              4,
                              Math.round(
                                  (Number(channel.value || 0) /
                                      activeTotalRevenue) *
                                      100,
                              ),
                          )
                        : 4}
                <a
                    href={channel.href}
                    class="group block rounded-2xl border border-gray-100 bg-gray-50/50 p-3 transition hover:border-gray-200 hover:bg-white hover:shadow-sm"
                >
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="font-medium text-slate-600 group-hover:text-slate-800">{channel.label}</span>
                        <span class="font-bold text-slate-900">{toCurrency(channel.value)}</span>
                    </div>
                    <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-gray-200/60">
                        <div
                            class="h-full rounded-full bg-blue-500 transition-all duration-500 ease-out group-hover:bg-blue-600"
                            style={`width:${width}%`}
                        ></div>
                    </div>
                </a>
            {/each}
        </div>
    </div>
</div>
