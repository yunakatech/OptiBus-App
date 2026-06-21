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

    const activeAchievementWidth = $derived(
        Math.min(
            Math.max(Number(activeSummaryStats.achievement_percent || 0), 0),
            100
        )
    );
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


</div>
