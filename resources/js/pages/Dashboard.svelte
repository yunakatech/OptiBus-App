<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Deferred, router } from '@inertiajs/svelte';
    import { ArrowRight, Banknote, Building2, BusFront, Copy, Package, Ticket, TrendingDown, TrendingUp, Wallet } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

    type DashboardStats = {
        total_bookings: number;
        pending: number;
        confirmed: number;
        canceled: number;
        live_fleet: number;
        revenue_today: number;
        revenue_booking_month: number;
        revenue_charter_month: number;
        revenue_luggage_month: number;
        revenue_total_today: number;
        revenue_total_month: number;
        revenue_total_year: number;
        bop_booking_month: number;
        bop_charter_month: number;
        margin_booking_month: number;
        margin_charter_month: number;
        margin_total_month: number;
        target_revenue_month: number;
        achievement_percent: number;
        top_route: string;
        top_route_count: number;
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

    type TrendItem = {
        label: string;
        date?: string;
        name?: string;
        revenue: number;
        transaction_count?: number;
        booking_revenue?: number;
        charter_revenue?: number;
        luggage_revenue?: number;
        booking_bop?: number;
        charter_bop?: number;
        margin?: number;
    };

    type HeatmapItem = TrendItem & {
        date: string;
        month_label?: string;
        month_short?: string;
        day_number?: number;
        day_of_week?: number;
        is_future?: boolean;
    };

    type HeatmapMonthGroup = {
        key: string;
        label: string;
        shortLabel: string;
        totalRevenue: number;
        totalTransactions: number;
        cells: Array<{
            key: string;
            item: HeatmapItem | null;
        }>;
    };

    type HeatmapContributionCell = {
        key: string;
        item: HeatmapItem | null;
        columnIndex: number;
        rowIndex: number;
        left: number;
    };

    type HeatmapMonthMarker = {
        key: string;
        label: string;
        left: number;
    };

    type ChartTooltipAlign = 'start' | 'center' | 'end';

    type ActivityItem = {
        title: string;
        meta: string;
        tag: string;
        time: string;
    };

    type DepartureItem = {
        rute: string;
        tanggal: string;
        jam: string;
        unit: number;
        total_bookings: number;
        driver_name: string;
        bookings: Array<{
            seat: string;
            name: string;
            phone: string;
            pickup_point: string;
            gmaps: string;
            status: string;
            pembayaran: string;
        }>;
    };

    type UpcomingCharterItem = {
        id: number;
        name: string;
        company_name: string | null;
        phone: string | null;
        start_date: string;
        end_date: string;
        departure_time: string | null;
        pickup_point: string;
        drop_point: string;
        driver_name: string | null;
        payment_status: string | null;
        layanan: string | null;
        status: string | null;
        day_diff: number;
        date_label: string;
    };

    type UpcomingCharterReminder = {
        total: number;
        visible_count: number;
        items: UpcomingCharterItem[];
    };

    type PoolOption = {
        id: number;
        name: string;
        code: string | null;
    };

    let {
        stats,
        pools = [] as PoolOption[],
        selectedPoolId = 0,
        selectedPoolName = 'Semua Pool',
        dailyTrend = [],
        yearlyHeatmap = [],
        recentActivity = [],
        departuresToday = [],
        upcomingCharterReminder = { total: 0, visible_count: 0, items: [] },
        recentActivityTotal = 0,
        recentActivityVisibleCount = 0,
        summaryStatsByScope = {
            day: { total_bookings: 0, revenue_booking: 0, revenue_charter: 0, revenue_luggage: 0, bop_booking: 0, bop_charter: 0, margin_booking: 0, margin_charter: 0, target_revenue: 0, achievement_percent: 0 },
            month: { total_bookings: 0, revenue_booking: 0, revenue_charter: 0, revenue_luggage: 0, bop_booking: 0, bop_charter: 0, margin_booking: 0, margin_charter: 0, target_revenue: 0, achievement_percent: 0 },
            year: { total_bookings: 0, revenue_booking: 0, revenue_charter: 0, revenue_luggage: 0, bop_booking: 0, bop_charter: 0, margin_booking: 0, margin_charter: 0, target_revenue: 0, achievement_percent: 0 },
        },
        summaryComparisonByScope = {
            day: { total_bookings: 0, revenue_booking: 0, revenue_charter: 0, revenue_luggage: 0, bop_booking: 0, bop_charter: 0, margin_booking: 0, margin_charter: 0, target_revenue: 0, achievement_percent: 0 },
            month: { total_bookings: 0, revenue_booking: 0, revenue_charter: 0, revenue_luggage: 0, bop_booking: 0, bop_charter: 0, margin_booking: 0, margin_charter: 0, target_revenue: 0, achievement_percent: 0 },
            year: { total_bookings: 0, revenue_booking: 0, revenue_charter: 0, revenue_luggage: 0, bop_booking: 0, bop_charter: 0, margin_booking: 0, margin_charter: 0, target_revenue: 0, achievement_percent: 0 },
        },
        summaryPeriodByScope = {
            day: { current_label: 'Hari Ini', previous_label: 'Kemarin', subtitle_label: 'hari ini' },
            month: { current_label: 'Bulan Ini', previous_label: 'Bulan Lalu', subtitle_label: 'bulan ini' },
            year: { current_label: 'Tahun Ini', previous_label: 'Tahun Lalu', subtitle_label: 'tahun ini' },
        },
    }: {
        stats: DashboardStats;
        pools?: PoolOption[];
        selectedPoolId?: number;
        selectedPoolName?: string;
        dailyTrend?: TrendItem[];
        yearlyHeatmap?: HeatmapItem[];
        recentActivity?: ActivityItem[];
        departuresToday?: DepartureItem[];
        upcomingCharterReminder?: UpcomingCharterReminder;
        recentActivityTotal?: number;
        recentActivityVisibleCount?: number;
        summaryStatsByScope?: Record<'day' | 'month' | 'year', SummaryScopeStats>;
        summaryComparisonByScope?: Record<'day' | 'month' | 'year', SummaryScopeStats>;
        summaryPeriodByScope?: Record<'day' | 'month' | 'year', SummaryPeriodMeta>;
    } = $props();

    const maxDailyRevenue = $derived(Math.max(0, ...dailyTrend.map((item) => Number(item.revenue || 0))));
    const lineScaleMax = $derived(maxDailyRevenue > 0 ? maxDailyRevenue : 1);
    const visibleHeatmapDays = $derived(yearlyHeatmap.filter((item) => !item.is_future));
    const maxHeatmapRevenue = $derived(
        Math.max(
            1,
            ...visibleHeatmapDays.map((item) => Number(item.revenue || 0)),
        ),
    );
    const upcomingCharterOverflow = $derived(
        Math.max(Number(upcomingCharterReminder.total || 0) - Number(upcomingCharterReminder.visible_count || 0), 0),
    );
    const recentActivityOverflow = $derived(Math.max(Number(recentActivityTotal || 0) - Number(recentActivityVisibleCount || 0), 0));
    const recent30DayRevenueTotal = $derived(
        dailyTrend.reduce((total, item) => total + Number(item.revenue || 0), 0),
    );
    const recent30DayTransactionsTotal = $derived(
        dailyTrend.reduce((total, item) => total + Number(item.transaction_count || 0), 0),
    );
    const yearlyRevenueTotal = $derived(
        visibleHeatmapDays.reduce((total, item) => total + Number(item.revenue || 0), 0),
    );
    const yearlyTransactionsTotal = $derived(
        visibleHeatmapDays.reduce((total, item) => total + Number(item.transaction_count || 0), 0),
    );

    const dailyKey = (row: TrendItem) => `daily-${row.label}-${row.date ?? ''}`;
    const heatmapKey = (row: HeatmapItem) => `heatmap-${row.date}`;

    let hoveredDailyKey = $state<string>('');
    let pinnedDailyKey = $state<string>('');
    let hoveredHeatmapKey = $state<string>('');
    let pinnedHeatmapKey = $state<string>('');
    let selectedSummaryScope = $state<'day' | 'month' | 'year'>('month');

    const activeDailyTooltipKey = $derived(pinnedDailyKey || hoveredDailyKey);
    const activeHeatmapTooltipKey = $derived(pinnedHeatmapKey || hoveredHeatmapKey);
    const heatmapYearLabel = $derived(
        visibleHeatmapDays[0]?.date ? new Date(`${visibleHeatmapDays[0].date}T00:00:00`).getFullYear() : new Date().getFullYear(),
    );
    const activeSummaryStats = $derived(summaryStatsByScope[selectedSummaryScope] ?? summaryStatsByScope.month);
    const activeSummaryComparison = $derived(
        summaryComparisonByScope[selectedSummaryScope] ?? summaryComparisonByScope.month,
    );
    const activeSummaryPeriod = $derived(summaryPeriodByScope[selectedSummaryScope] ?? summaryPeriodByScope.month);
    const activeTotalRevenue = $derived(
        Number(activeSummaryStats.revenue_booking || 0)
            + Number(activeSummaryStats.revenue_charter || 0)
            + Number(activeSummaryStats.revenue_luggage || 0),
    );
    const activeTotalBop = $derived(Number(activeSummaryStats.bop_booking || 0) + Number(activeSummaryStats.bop_charter || 0));
    const activeTotalMargin = $derived(activeTotalRevenue - activeTotalBop);
    const activeAchievementWidth = $derived(Math.min(Math.max(Number(activeSummaryStats.achievement_percent || 0), 0), 100));
    const monthlyTotalRevenueComparison = $derived(
        Number(summaryComparisonByScope.month?.revenue_booking || 0)
            + Number(summaryComparisonByScope.month?.revenue_charter || 0)
            + Number(summaryComparisonByScope.month?.revenue_luggage || 0),
    );
    const lineChartPoints = $derived.by(() =>
        dailyTrend.map((row, index) => {
            const x = dailyTrend.length <= 1 ? 50 : (index / (dailyTrend.length - 1)) * 100;
            const y = 100 - Math.min(100, Math.max(0, (Number(row.revenue || 0) / lineScaleMax) * 100));

            return {
                key: dailyKey(row),
                row,
                index,
                x,
                y,
            };
        }),
    );
    const linePath = $derived.by(() =>
        lineChartPoints.length
            ? lineChartPoints
                  .map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`)
                  .join(' ')
            : '',
    );
    const lineAreaPath = $derived.by(() =>
        lineChartPoints.length
            ? `M ${lineChartPoints[0].x} 100 ${lineChartPoints
                  .map((point) => `L ${point.x} ${point.y}`)
                  .join(' ')} L ${lineChartPoints[lineChartPoints.length - 1].x} 100 Z`
            : '',
    );
    const lineChartTicks = $derived.by(() =>
        [1, 0.66, 0.33, 0].map((factor) => ({
            y: (1 - factor) * 100,
            value: maxDailyRevenue * factor,
        })),
    );
    const lineAxisIndexes = $derived.by(() => {
        if (lineChartPoints.length === 0) {
            return new Set<number>();
        }

        const indexes = new Set<number>([0, lineChartPoints.length - 1]);
        const targetCount = Math.min(5, lineChartPoints.length);

        for (let step = 1; step < targetCount - 1; step += 1) {
            indexes.add(
                Math.round((step / (targetCount - 1)) * (lineChartPoints.length - 1)),
            );
        }

        return indexes;
    });
    const heatmapContributionColumns = $derived.by(() => {
        const columns: Array<{
            key: string;
            cells: Array<HeatmapItem | null>;
        }> = [];
        let weekCells = Array<HeatmapItem | null>(7).fill(null);

        yearlyHeatmap.forEach((row, index) => {
            const dayOfWeek = Number(row.day_of_week ?? 0);

            if (index > 0 && dayOfWeek === 0) {
                columns.push({
                    key: `heatmap-week-${columns.length}`,
                    cells: weekCells,
                });
                weekCells = Array<HeatmapItem | null>(7).fill(null);
            }

            weekCells[dayOfWeek] = row;
        });

        if (weekCells.some((cell) => cell !== null)) {
            columns.push({
                key: `heatmap-week-${columns.length}`,
                cells: weekCells,
            });
        }

        return columns;
    });
    const heatmapContributionCells = $derived.by<HeatmapContributionCell[]>(() => {
        const totalColumns = Math.max(heatmapContributionColumns.length, 1);

        return heatmapContributionColumns.flatMap((column, columnIndex) =>
            column.cells.map((item, rowIndex) => ({
                key: item ? heatmapKey(item) : `${column.key}-empty-${rowIndex}`,
                item,
                columnIndex,
                rowIndex,
                left: ((columnIndex + 0.5) / totalColumns) * 100,
            })),
        );
    });
    const heatmapMonthMarkers = $derived.by<HeatmapMonthMarker[]>(() =>
        heatmapContributionColumns.flatMap((column, columnIndex) => {
            const monthStart = column.cells.find((item) => item?.day_number === 1);

            if (!monthStart) {
                return [];
            }

            return [
                {
                    key: `${monthStart.date}-marker`,
                    label: monthStart.month_short ?? monthStart.month_label ?? '',
                    left: ((columnIndex + 0.5) / Math.max(heatmapContributionColumns.length, 1)) * 100,
                },
            ];
        }),
    );

    const toCurrency = (value: number) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
    const toCompactCurrency = (value: number) => {
        const amount = Number(value || 0);
        const sign = amount < 0 ? '-' : '';
        const absolute = Math.abs(amount);

        if (absolute >= 1_000_000_000) {
            return `${sign}Rp ${(absolute / 1_000_000_000).toFixed(1).replace('.0', '')}B`;
        }

        if (absolute >= 1_000_000) {
            return `${sign}Rp ${(absolute / 1_000_000).toFixed(1).replace('.0', '')}M`;
        }

        if (absolute >= 1_000) {
            return `${sign}Rp ${(absolute / 1_000).toFixed(0)}K`;
        }

        return toCurrency(amount);
    };
    const normalizeJam = (value: string) => String(value || '').trim();
    const isCanceledBooking = (status: string | null | undefined) =>
        String(status || '').trim().toLowerCase() === 'canceled';
    const formatDepartDateTime = (tanggal: string, jam: string) => {
        const date = new Date(`${tanggal}T00:00:00`);
        const dateLabel = Number.isNaN(date.getTime())
            ? tanggal
            : date.toLocaleDateString('en-US', {
                  month: 'long',
                  day: 'numeric',
                  year: 'numeric',
              });
        const jamLabel = normalizeJam(jam).replace(':', '.');

        return `${dateLabel} - ${jamLabel}`;
    };

    const charterReminderTag = (item: UpcomingCharterItem) => {
        if (Number(item.day_diff) <= 0) {
            return 'Hari Ini';
        }

        if (Number(item.day_diff) === 1) {
            return 'Besok';
        }

        return `H-${item.day_diff}`;
    };

    const formatCompactNumber = (value: number) => Number(value || 0).toLocaleString('id-ID');

    const metricTrend = (current: number, previous: number, formatter: (value: number) => string, previousLabel = activeSummaryPeriod.previous_label) => {
        const delta = Number(current || 0) - Number(previous || 0);
        const direction = delta > 0 ? 'up' : delta < 0 ? 'down' : 'flat';
        const percent = previous > 0 ? Math.abs((delta / previous) * 100) : null;
        const percentLabel = percent === null ? (current > 0 ? 'Baru' : '0%') : `${percent.toFixed(1).replace('.0', '')}%`;

        return {
            delta,
            direction,
            label:
                direction === 'flat'
                    ? `Stabil vs ${previousLabel}`
                    : `${direction === 'up' ? 'Naik' : 'Turun'} ${percentLabel} vs ${previousLabel}`,
            detail:
                direction === 'flat'
                    ? formatter(previous)
                    : `${delta > 0 ? '+' : '-'}${formatter(Math.abs(delta))}`,
        };
    };

    const metricBars = (current: number, previous: number) => {
        const max = Math.max(Number(current || 0), Number(previous || 0), 1);

        return {
            previous: Math.max(previous > 0 ? Math.round((previous / max) * 100) : 10, 10),
            current: Math.max(current > 0 ? Math.round((current / max) * 100) : 10, 10),
        };
    };

    const isMobileViewport = () => typeof window !== 'undefined' && window.innerWidth < 768;
    const clampPercent = (value: number, min: number, max: number) =>
        Math.min(Math.max(value, min), max);
    const tooltipAlignFor = (left: number): ChartTooltipAlign => {
        if (left <= 18) {
            return 'start';
        }

        if (left >= 82) {
            return 'end';
        }

        return 'center';
    };
    const tooltipTranslateClass = (align: ChartTooltipAlign) =>
        align === 'start'
            ? 'translate-x-0'
            : align === 'end'
              ? '-translate-x-full'
              : '-translate-x-1/2';
    const tooltipArrowClass = (align: ChartTooltipAlign) =>
        align === 'start'
            ? 'left-5'
            : align === 'end'
              ? 'right-5'
              : 'left-1/2 -translate-x-1/2';
    const hoverDailyPoint = (row: TrendItem | null) => {
        hoveredDailyKey = row ? dailyKey(row) : '';
    };
    const togglePinnedDailyPoint = (row: TrendItem) => {
        const key = dailyKey(row);

        pinnedDailyKey = pinnedDailyKey === key ? '' : key;
        hoveredDailyKey = key;
    };
    const hoverHeatmapDay = (row: HeatmapItem | null) => {
        hoveredHeatmapKey = row && !row.is_future ? heatmapKey(row) : '';
    };
    const togglePinnedHeatmapDay = (row: HeatmapItem) => {
        if (row.is_future) {
            return;
        }

        const key = heatmapKey(row);
        pinnedHeatmapKey = pinnedHeatmapKey === key ? '' : key;
        hoveredHeatmapKey = key;
    };
    const updateSummaryScope = (scope: 'day' | 'month' | 'year') => {
        selectedSummaryScope = scope;
    };

    const activeDailyTooltip = $derived.by(() => {
        if (!activeDailyTooltipKey) {
            return null;
        }

        const point = lineChartPoints.find((item) => item.key === activeDailyTooltipKey);

        if (!point) {
            return null;
        }

        const left = clampPercent(point.x, 8, 92);

        return {
            row: point.row,
            left,
            top: clampPercent(point.y - 12, 10, 78),
            align: tooltipAlignFor(left),
        };
    });
    const activeHeatmapTooltip = $derived.by(() => {
        if (!activeHeatmapTooltipKey) {
            return null;
        }

        const cell = heatmapContributionCells.find(
            (item) => item.key === activeHeatmapTooltipKey && item.item,
        );

        if (!cell?.item) {
            return null;
        }

        const left = clampPercent(cell.left, 8, 92);

        return {
            row: cell.item,
            left,
            align: tooltipAlignFor(left),
        };
    });
    const dailySelectedClass = (row: TrendItem) =>
        activeDailyTooltipKey === dailyKey(row) ? 'ring-4 ring-sky-500/20' : '';
    const heatmapSelectedClass = (row: HeatmapItem) =>
        activeHeatmapTooltipKey === heatmapKey(row)
            ? 'ring-2 ring-sky-500/60 ring-offset-2 ring-offset-white dark:ring-offset-slate-950'
            : 'ring-1 ring-slate-200/70 dark:ring-slate-800';
    const heatmapToneClass = (row: HeatmapItem) => {
        if (row.is_future) {
            return 'bg-slate-100/80 text-slate-300 dark:bg-slate-900 dark:text-slate-700';
        }

        const revenue = Number(row.revenue || 0);

        if (revenue <= 0) {
            return 'bg-slate-100 text-slate-400 dark:bg-slate-800/90 dark:text-slate-500';
        }

        const ratio = revenue / maxHeatmapRevenue;

        if (ratio >= 0.75) {
            return 'bg-sky-600 text-white shadow-[0_6px_18px_-10px_rgba(2,132,199,0.9)]';
        }

        if (ratio >= 0.45) {
            return 'bg-sky-400 text-sky-950 dark:bg-sky-500 dark:text-white';
        }

        if (ratio >= 0.2) {
            return 'bg-sky-200 text-sky-950 dark:bg-sky-500/30 dark:text-sky-100';
        }

        return 'bg-cyan-100 text-cyan-900 dark:bg-cyan-500/20 dark:text-cyan-100';
    };
    const showLineAxisLabel = (index: number) => lineAxisIndexes.has(index);

    const dashboardMetricCards = () => [
        {
            key: 'monthly-total-revenue',
            title: 'Total Revenue Bulanan',
            subtitle: 'Akumulasi booking, carter, dan bagasi bulan ini',
            value: toCurrency(stats.revenue_total_month),
            href: '/reports',
            cta: 'Buka Laporan Revenue',
            periodLabel: summaryPeriodByScope.month.current_label,
            trend: metricTrend(stats.revenue_total_month, monthlyTotalRevenueComparison, toCurrency, summaryPeriodByScope.month.previous_label),
            compareBars: metricBars(stats.revenue_total_month, monthlyTotalRevenueComparison),
            icon: Banknote,
            spanClass: 'md:col-span-2 xl:col-span-2',
            shellClass: 'border-indigo-200/80 bg-[linear-gradient(135deg,rgba(238,242,255,0.98),rgba(219,234,254,0.92))] dark:border-indigo-400/25 dark:bg-[linear-gradient(135deg,rgba(30,27,75,0.74),rgba(15,23,42,0.94))]',
            iconClass: 'border-indigo-200/70 bg-indigo-500/12 text-indigo-700 dark:border-indigo-300/25 dark:bg-indigo-300/10 dark:text-indigo-100',
            valueClass: 'text-indigo-950 dark:text-indigo-50',
            barClass: 'bg-indigo-500/75',
            noteClass: 'text-indigo-700/80 dark:text-indigo-100/90',
        },
        {
            key: 'bookings',
            title: 'Booking Active',
            subtitle: `Keberangkatan ${activeSummaryPeriod.subtitle_label}`,
            value: `${formatCompactNumber(activeSummaryStats.total_bookings)}`,
            href: '/bookings',
            cta: 'Buka Data Keberangkatan',
            periodLabel: activeSummaryPeriod.current_label,
            trend: metricTrend(activeSummaryStats.total_bookings, activeSummaryComparison.total_bookings, formatCompactNumber),
            compareBars: metricBars(activeSummaryStats.total_bookings, activeSummaryComparison.total_bookings),
            icon: BusFront,
            spanClass: '',
            shellClass: 'border-cyan-200/80 bg-[linear-gradient(135deg,rgba(236,254,255,0.98),rgba(224,242,254,0.92))] dark:border-cyan-400/25 dark:bg-[linear-gradient(135deg,rgba(8,47,73,0.74),rgba(15,23,42,0.94))]',
            iconClass: 'border-cyan-200/70 bg-cyan-500/12 text-cyan-700 dark:border-cyan-300/25 dark:bg-cyan-300/10 dark:text-cyan-100',
            valueClass: 'text-cyan-950 dark:text-cyan-50',
            barClass: 'bg-cyan-500/75',
            noteClass: 'text-cyan-700/80 dark:text-cyan-100/90',
        },
        {
            key: 'booking-revenue',
            title: 'Revenue Booking',
            subtitle: `Pendapatan keberangkatan ${activeSummaryPeriod.subtitle_label}`,
            value: toCurrency(activeSummaryStats.revenue_booking),
            href: '/bookings',
            cta: 'Buka Menu Booking',
            periodLabel: activeSummaryPeriod.current_label,
            trend: metricTrend(activeSummaryStats.revenue_booking, activeSummaryComparison.revenue_booking, toCurrency),
            compareBars: metricBars(activeSummaryStats.revenue_booking, activeSummaryComparison.revenue_booking),
            icon: Ticket,
            spanClass: '',
            shellClass: 'border-emerald-200/80 bg-[linear-gradient(135deg,rgba(240,253,244,0.98),rgba(220,252,231,0.92))] dark:border-emerald-400/25 dark:bg-[linear-gradient(135deg,rgba(6,78,59,0.72),rgba(15,23,42,0.94))]',
            iconClass: 'border-emerald-200/70 bg-emerald-500/12 text-emerald-700 dark:border-emerald-300/25 dark:bg-emerald-300/10 dark:text-emerald-100',
            valueClass: 'text-emerald-950 dark:text-emerald-50',
            barClass: 'bg-emerald-500/75',
            noteClass: 'text-emerald-700/80 dark:text-emerald-100/90',
        },
        {
            key: 'charter-revenue',
            title: 'Revenue Carter',
            subtitle: `Pendapatan carter ${activeSummaryPeriod.subtitle_label}`,
            value: toCurrency(activeSummaryStats.revenue_charter),
            href: '/charters',
            cta: 'Buka Menu Carter',
            periodLabel: activeSummaryPeriod.current_label,
            trend: metricTrend(activeSummaryStats.revenue_charter, activeSummaryComparison.revenue_charter, toCurrency),
            compareBars: metricBars(activeSummaryStats.revenue_charter, activeSummaryComparison.revenue_charter),
            icon: Wallet,
            spanClass: '',
            shellClass: 'border-amber-200/80 bg-[linear-gradient(135deg,rgba(255,251,235,0.98),rgba(254,243,199,0.92))] dark:border-amber-400/25 dark:bg-[linear-gradient(135deg,rgba(120,53,15,0.62),rgba(15,23,42,0.94))]',
            iconClass: 'border-amber-200/70 bg-amber-500/12 text-amber-700 dark:border-amber-300/25 dark:bg-amber-300/10 dark:text-amber-100',
            valueClass: 'text-amber-950 dark:text-amber-50',
            barClass: 'bg-amber-500/75',
            noteClass: 'text-amber-700/80 dark:text-amber-100/90',
        },
        {
            key: 'luggage-revenue',
            title: 'Revenue Bagasi',
            subtitle: `Pendapatan bagasi ${activeSummaryPeriod.subtitle_label}`,
            value: toCurrency(activeSummaryStats.revenue_luggage),
            href: '/luggages',
            cta: 'Buka Menu Bagasi',
            periodLabel: activeSummaryPeriod.current_label,
            trend: metricTrend(activeSummaryStats.revenue_luggage, activeSummaryComparison.revenue_luggage, toCurrency),
            compareBars: metricBars(activeSummaryStats.revenue_luggage, activeSummaryComparison.revenue_luggage),
            icon: Package,
            spanClass: '',
            shellClass: 'border-sky-200/80 bg-[linear-gradient(135deg,rgba(240,249,255,0.98),rgba(224,242,254,0.92))] dark:border-sky-400/25 dark:bg-[linear-gradient(135deg,rgba(12,74,110,0.68),rgba(15,23,42,0.94))]',
            iconClass: 'border-sky-200/70 bg-sky-500/12 text-sky-700 dark:border-sky-300/25 dark:bg-sky-300/10 dark:text-sky-100',
            valueClass: 'text-sky-950 dark:text-sky-50',
            barClass: 'bg-sky-500/75',
            noteClass: 'text-sky-700/80 dark:text-sky-100/90',
        },
    ];

    const commandMetrics = () => [
        {
            key: 'booking-active',
            label: 'Booking Active',
            value: formatCompactNumber(activeSummaryStats.total_bookings),
            meta: metricTrend(activeSummaryStats.total_bookings, activeSummaryComparison.total_bookings, formatCompactNumber).label,
            href: '/bookings',
        },
        {
            key: 'bop-booking',
            label: 'BOP Booking',
            value: toCurrency(activeSummaryStats.bop_booking),
            meta: 'Unique keberangkatan',
            href: '/reports',
        },
        {
            key: 'bop-carter',
            label: 'BOP Carter',
            value: toCurrency(activeSummaryStats.bop_charter),
            meta: 'Operasional carter',
            href: '/charters',
        },
        {
            key: 'margin-total',
            label: 'Margin Total',
            value: toCurrency(activeTotalMargin),
            meta: `${activeTotalMargin >= 0 ? 'Surplus' : 'Defisit'} ${activeSummaryPeriod.subtitle_label}`,
            href: '/reports',
        },
    ];

    const revenueChannels = () => [
        { key: 'booking', label: 'Booking', value: activeSummaryStats.revenue_booking, href: '/bookings' },
        { key: 'carter', label: 'Carter', value: activeSummaryStats.revenue_charter, href: '/charters' },
        { key: 'bagasi', label: 'Bagasi', value: activeSummaryStats.revenue_luggage, href: '/luggages' },
    ];

    let copyMessage = $state('');
    let copyError = $state('');
    let copyingDepartureKey = $state('');

    const copyText = async (text: string) => {
        if (
            typeof navigator !== 'undefined' &&
            navigator.clipboard &&
            typeof navigator.clipboard.writeText === 'function' &&
            window.isSecureContext
        ) {
            await navigator.clipboard.writeText(text);

            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', 'true');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.pointerEvents = 'none';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        const copied = document.execCommand('copy');
        document.body.removeChild(textarea);

        if (!copied) {
            throw new Error('copy_failed');
        }
    };

    const departureCopyKey = (item: DepartureItem) => `${item.rute}-${item.jam}-${item.unit}`;

    const departureCopyBlock = (item: DepartureItem) => {
        const copyableBookings = item.bookings.filter(
            (row) => !isCanceledBooking(row.status),
        );
        const lines = copyableBookings.map(
            (row) =>
                `- Kursi: ${row.seat || '-'}\n` +
                `Nama: ${row.name || '-'}\n` +
                `No. HP: ${row.phone || '-'}\n` +
                `Titik Jemput: ${row.pickup_point || '-'}\n` +
                `Gmaps: ${row.gmaps || ''}\n` +
                `Pembayaran: ${row.pembayaran || '-'}`,
        );

        return (
        `Info Pemberangkatan\n` +
        `Tanggal & Jam: ${formatDepartDateTime(item.tanggal, item.jam)}\n` +
        `Rute: ${item.rute}\n` +
        `Total Penumpang: ${copyableBookings.length}\n` +
        `Driver: ${item.driver_name || '-'}\n\n` +
        lines.join('\n\n')
        );
    };

    const copyDepartureData = async (item: DepartureItem) => {
        const key = departureCopyKey(item);
        copyingDepartureKey = key;
        copyMessage = '';
        copyError = '';

        try {
            await copyText(departureCopyBlock(item));
            copyMessage = 'Data jadwal berhasil disalin.';
        } catch {
            copyError = 'Gagal menyalin data keberangkatan.';
        } finally {
            copyingDepartureKey = '';
        }
    };
</script>

<AppHead title="Dashboard" />

<div class="flex h-full flex-1 flex-col gap-2 overflow-x-hidden rounded-xl p-2.5 md:gap-3 md:p-4">
    <div class="space-y-2">
        <div class="flex flex-col gap-2 rounded-2xl border border-border/70 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(248,250,252,0.92))] px-3 py-2.5 shadow-sm dark:border-slate-700/70 dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.96),rgba(30,41,59,0.9))] md:flex-row md:items-end md:justify-between md:rounded-3xl md:px-4 md:py-3">
            <div class="space-y-0.5 md:space-y-1">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground md:text-[11px]">Ringkasan Dashboard</p>
                <h2 class="text-base font-semibold tracking-tight text-foreground md:text-lg">Performa Booking dan Pendapatan</h2>
                {#if selectedPoolId > 0}
                    <p class="text-[11px] text-muted-foreground">
                        <Building2 class="inline size-3 -mt-0.5 mr-1" />
                        Pool aktif: <span class="font-medium text-primary">{selectedPoolName}</span>
                    </p>
                {/if}
            </div>
            <div class="flex flex-col gap-2 md:items-end">
                <div class="hidden items-center gap-2 self-start rounded-full border border-border/70 bg-white/80 px-3 py-1.5 text-[11px] font-medium text-muted-foreground dark:border-slate-700/70 dark:bg-slate-900/70 sm:flex md:self-auto">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Perbandingan vs {activeSummaryPeriod.previous_label}
                </div>
                <div class="inline-flex rounded-2xl border border-border/70 bg-white/80 p-0.5 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/70 md:p-1">
                    {#each [
                        { key: 'day', label: 'Hari Ini' },
                        { key: 'month', label: 'Bulan Ini' },
                        { key: 'year', label: 'Tahun Ini' },
                    ] as option (`summary-scope-${option.key}`)}
                        <button
                            type="button"
                            class={`rounded-xl px-2.5 py-1.5 text-[10px] font-medium transition md:px-3 md:text-[11px] ${
                                selectedSummaryScope === option.key
                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted/60'
                            }`}
                            onclick={() => updateSummaryScope(option.key as 'day' | 'month' | 'year')}
                        >
                            {option.label}
                        </button>
                    {/each}
                </div>
            </div>
        </div>
        <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-700/70 dark:bg-slate-950">
            <div class="grid gap-0 lg:grid-cols-[1.05fr_1.35fr]">
                <div class="border-b border-slate-200/80 bg-slate-900 p-4 text-slate-50 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] dark:border-slate-800 lg:border-b-0 lg:border-r">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-200/80">Command Center</p>
                            <h3 class="mt-1 text-2xl font-semibold leading-tight text-white md:text-3xl">{toCurrency(activeTotalRevenue)}</h3>
                            <p class="mt-1 text-xs text-slate-200/80">Total revenue {activeSummaryPeriod.subtitle_label}</p>
                        </div>
                        <a href="/reports" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-white/10 text-white transition hover:bg-white/20" aria-label="Buka laporan revenue">
                            <ArrowRight class="h-4 w-4" />
                        </a>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-[2px]">
                            <p class="text-[11px] text-slate-200/80">Target</p>
                            <p class="mt-1 text-sm font-semibold text-white">{toCurrency(activeSummaryStats.target_revenue || stats.target_revenue_month)}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur-[2px]">
                            <p class="text-[11px] text-slate-200/80">Achievement</p>
                            <p class="mt-1 text-sm font-semibold text-white">{activeSummaryStats.achievement_percent || stats.achievement_percent}%</p>
                        </div>
                    </div>

                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/15">
                        <div class="h-full rounded-full bg-emerald-400 transition-all" style={`width:${activeAchievementWidth}%`}></div>
                    </div>
                </div>

                <div class="p-3 md:p-4">
                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        {#each commandMetrics() as metric (metric.key)}
                            <a href={metric.href} class="group rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-sm transition hover:border-primary/35 hover:bg-primary/5 dark:border-slate-800 dark:bg-slate-900/80">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">{metric.label}</p>
                                    <ArrowRight class="h-3.5 w-3.5 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-primary dark:text-slate-500" />
                                </div>
                                <p class="mt-2 break-words text-lg font-semibold text-slate-900 dark:text-slate-50">{metric.value}</p>
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{metric.meta}</p>
                            </a>
                        {/each}
                    </div>

                    <div class="mt-3 grid gap-3 lg:grid-cols-[1fr_0.85fr]">
                        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-slate-900 dark:text-slate-50">Revenue Channel</p>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">{activeSummaryPeriod.current_label}</span>
                            </div>
                            <div class="space-y-2">
                                {#each revenueChannels() as channel (channel.key)}
                                    {@const width = activeTotalRevenue > 0 ? Math.max(8, Math.round((Number(channel.value || 0) / activeTotalRevenue) * 100)) : 8}
                                    <a href={channel.href} class="block rounded-xl border border-transparent px-2 py-1.5 transition hover:border-slate-200 hover:bg-slate-50 dark:hover:border-slate-800 dark:hover:bg-slate-900">
                                        <div class="flex items-center justify-between gap-3 text-xs">
                                            <span class="font-medium text-slate-700 dark:text-slate-200">{channel.label}</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-50">{toCurrency(channel.value)}</span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full bg-primary/75" style={`width:${width}%`}></div>
                                        </div>
                                    </a>
                                {/each}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                            <p class="text-xs font-semibold text-slate-900 dark:text-slate-50">Unit Economics</p>
                            <div class="mt-3 space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500 dark:text-slate-400">Revenue</span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-50">{toCurrency(activeTotalRevenue)}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500 dark:text-slate-400">BOP Booking</span>
                                    <span class="font-semibold text-rose-600 dark:text-rose-300">{toCurrency(activeSummaryStats.bop_booking)}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500 dark:text-slate-400">BOP Carter</span>
                                    <span class="font-semibold text-rose-600 dark:text-rose-300">{toCurrency(activeSummaryStats.bop_charter)}</span>
                                </div>
                                <div class="border-t border-slate-200 pt-2 dark:border-slate-800">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-medium text-slate-900 dark:text-slate-50">Margin Total</span>
                                        <span class={`font-semibold ${activeTotalMargin >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300'}`}>{toCurrency(activeTotalMargin)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {#if false && (stats.target_revenue_month > 0 || activeSummaryStats.margin_booking || activeSummaryStats.margin_charter || activeSummaryStats.revenue_luggage)}
            <div class="grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-5 md:gap-2.5">
                <!-- Target & Achievement Card -->
                {#if stats.target_revenue_month > 0}
                    <Card class="overflow-hidden border border-purple-200/80 bg-[linear-gradient(135deg,rgba(250,245,255,0.98),rgba(243,232,255,0.92))] shadow-sm dark:border-purple-400/25 dark:bg-[linear-gradient(135deg,rgba(59,7,100,0.72),rgba(15,23,42,0.94))]">
                        <CardContent class="space-y-2 p-3 md:p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-purple-700/80 dark:text-purple-200/80">Target Revenue</p>
                            <p class="text-lg font-semibold tracking-tight text-purple-950 dark:text-purple-50">{toCurrency(stats.target_revenue_month)}</p>
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[10px] font-medium">
                                    <span class="text-purple-700/70 dark:text-purple-200/70">Pencapaian {activeSummaryPeriod.subtitle_label}</span>
                                    <span class="text-purple-800 dark:text-purple-200">{stats.achievement_percent}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-purple-200/70 dark:bg-purple-800/60">
                                    <div class="h-2 rounded-full bg-purple-500/80 transition-all" style={`width:${Math.min(stats.achievement_percent, 100)}%`}></div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                {/if}

                <!-- Margin Booking Card -->
                <Card class="overflow-hidden border border-green-200/80 bg-[linear-gradient(135deg,rgba(240,253,244,0.98),rgba(220,252,231,0.92))] shadow-sm dark:border-green-400/25 dark:bg-[linear-gradient(135deg,rgba(6,78,59,0.72),rgba(15,23,42,0.94))]">
                    <CardContent class="space-y-2 p-3 md:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-green-700/80 dark:text-green-200/80">Margin Booking</p>
                        <p class={`text-lg font-semibold tracking-tight ${(activeSummaryStats.margin_booking || 0) >= 0 ? 'text-green-950 dark:text-green-50' : 'text-rose-700 dark:text-rose-300'}`}>{toCurrency(activeSummaryStats.margin_booking)}</p>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-green-700/70 dark:text-green-200/70">
                            <span>Rev {toCurrency(activeSummaryStats.revenue_booking)}</span>
                            <span>− BOP {toCurrency(activeSummaryStats.bop_booking)}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Margin Carter Card -->
                <Card class="overflow-hidden border border-teal-200/80 bg-[linear-gradient(135deg,rgba(240,253,250,0.98),rgba(204,251,241,0.92))] shadow-sm dark:border-teal-400/25 dark:bg-[linear-gradient(135deg,rgba(19,78,74,0.72),rgba(15,23,42,0.94))]">
                    <CardContent class="space-y-2 p-3 md:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-teal-700/80 dark:text-teal-200/80">Margin Carter</p>
                        <p class={`text-lg font-semibold tracking-tight ${(activeSummaryStats.margin_charter || 0) >= 0 ? 'text-teal-950 dark:text-teal-50' : 'text-rose-700 dark:text-rose-300'}`}>{toCurrency(activeSummaryStats.margin_charter)}</p>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-teal-700/70 dark:text-teal-200/70">
                            <span>Rev {toCurrency(activeSummaryStats.revenue_charter)}</span>
                            <span>− BOP {toCurrency(activeSummaryStats.bop_charter)}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Margin Bagasi Card -->
                <Card class="overflow-hidden border border-amber-200/80 bg-[linear-gradient(135deg,rgba(255,251,235,0.98),rgba(254,243,199,0.92))] shadow-sm dark:border-amber-400/25 dark:bg-[linear-gradient(135deg,rgba(120,53,15,0.62),rgba(15,23,42,0.94))]">
                    <CardContent class="space-y-2 p-3 md:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-amber-700/80 dark:text-amber-200/80">Margin Bagasi</p>
                        <p class="text-lg font-semibold tracking-tight text-amber-950 dark:text-amber-50">{toCurrency(activeSummaryStats.revenue_luggage)}</p>
                        <div class="text-[10px] text-amber-700/70 dark:text-amber-200/70">
                            <span>Rev {toCurrency(activeSummaryStats.revenue_luggage)} — tanpa BOP</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Margin Card -->
                <Card class="overflow-hidden border border-blue-200/80 bg-[linear-gradient(135deg,rgba(239,246,255,0.98),rgba(219,234,254,0.92))] shadow-sm dark:border-blue-400/25 dark:bg-[linear-gradient(135deg,rgba(23,37,84,0.74),rgba(15,23,42,0.94))]">
                    <CardContent class="space-y-2 p-3 md:p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-blue-700/80 dark:text-blue-200/80">Margin Total</p>
                        <p class={`text-lg font-semibold tracking-tight ${((activeSummaryStats.margin_booking || 0) + (activeSummaryStats.margin_charter || 0) + (activeSummaryStats.revenue_luggage || 0)) >= 0 ? 'text-blue-950 dark:text-blue-50' : 'text-rose-700 dark:text-rose-300'}`}>{toCurrency((activeSummaryStats.margin_booking || 0) + (activeSummaryStats.margin_charter || 0) + (activeSummaryStats.revenue_luggage || 0))}</p>
                        <p class="text-[10px] text-blue-700/70 dark:text-blue-200/70">
                            Rev {toCurrency(activeSummaryStats.revenue_booking + activeSummaryStats.revenue_charter + activeSummaryStats.revenue_luggage)} − BOP {toCurrency(activeSummaryStats.bop_booking + activeSummaryStats.bop_charter)}
                        </p>
                    </CardContent>
                </Card>
            </div>
        {/if}
    </div>

    <Deferred
        data={[
            'dailyTrend',
            'yearlyHeatmap',
            'recentActivity',
            'recentActivityTotal',
            'recentActivityVisibleCount',
            'departuresToday',
            'upcomingCharterReminder',
        ]}
    >
        {#snippet fallback()}
            <div class="grid gap-2.5 xl:grid-cols-3 xl:items-start" aria-label="Memuat data dashboard">
                <div class="space-y-2.5 xl:col-span-2">
                    {#each Array.from({ length: 2 }) as _, index (`dashboard-main-skeleton-${index}`)}
                        <Card class="overflow-hidden">
                            <CardContent class="space-y-3 p-4">
                                <div class="h-4 w-40 animate-pulse rounded-full bg-muted"></div>
                                <div class="h-3 w-28 animate-pulse rounded-full bg-muted/70"></div>
                                <div class="grid grid-cols-7 items-end gap-2 pt-3">
                                    {#each Array.from({ length: 7 }) as _, barIndex (`dashboard-chart-skeleton-${index}-${barIndex}`)}
                                        <div
                                            class="animate-pulse rounded-md bg-muted"
                                            style={`height:${24 + ((barIndex + index) % 4) * 10}px`}
                                        ></div>
                                    {/each}
                                </div>
                            </CardContent>
                        </Card>
                    {/each}
                </div>
                <div class="space-y-2.5">
                    {#each Array.from({ length: 3 }) as _, index (`dashboard-side-skeleton-${index}`)}
                        <Card>
                            <CardContent class="space-y-3 p-4">
                                <div class="h-4 w-36 animate-pulse rounded-full bg-muted"></div>
                                {#each Array.from({ length: index === 1 ? 3 : 2 }) as _, rowIndex (`dashboard-side-row-${index}-${rowIndex}`)}
                                    <div class="space-y-2 rounded-xl border border-border/60 p-3">
                                        <div class="h-3 w-2/3 animate-pulse rounded-full bg-muted"></div>
                                        <div class="h-3 w-full animate-pulse rounded-full bg-muted/70"></div>
                                    </div>
                                {/each}
                            </CardContent>
                        </Card>
                    {/each}
                </div>
            </div>
        {/snippet}

        <div class="grid gap-2.5 xl:grid-cols-3 xl:items-start">
        <div class="space-y-2.5 xl:col-span-2">
            <Card class="h-fit overflow-hidden border border-slate-200/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(248,250,252,0.96))] shadow-sm dark:border-slate-700/70 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.98),rgba(15,23,42,0.96))]">
                <CardHeader class="space-y-1 pb-1">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Last 30 days</p>
                            <CardTitle class="mt-1 text-[1.65rem] font-semibold tracking-tight text-slate-950 dark:text-slate-50">Revenue</CardTitle>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge variant="secondary" class="rounded-full px-3 py-1 text-[11px] font-semibold">{toCompactCurrency(recent30DayRevenueTotal)}</Badge>
                            <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] font-semibold">{formatCompactNumber(recent30DayTransactionsTotal)} TRX</Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="pt-0 pb-3">
                    <div class="rounded-[24px] border border-slate-200/80 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(255,255,255,0.92))] p-3 shadow-inner dark:border-slate-800 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.84),rgba(15,23,42,0.7))] md:p-4">
                        <div class="mb-2 flex items-center justify-between gap-2 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            <span>Revenue trajectory</span>
                            <span class="normal-case tracking-normal">Hover / tap titik</span>
                        </div>

                        <div class="relative h-[220px] md:h-[236px]" role="presentation" onmouseleave={() => hoverDailyPoint(null)}>
                            {#if activeDailyTooltip}
                                <div
                                    class={`pointer-events-none absolute z-10 w-[188px] rounded-2xl bg-slate-900/96 px-3 py-2 text-white shadow-2xl transition ${tooltipTranslateClass(activeDailyTooltip.align)}`}
                                    style={`left:${activeDailyTooltip.left}%; top:${activeDailyTooltip.top}%`}
                                >
                                    <p class="text-sm font-semibold">{activeDailyTooltip.row.date ?? activeDailyTooltip.row.label}</p>
                                    <p class="mt-2 text-[11px] uppercase tracking-[0.14em] text-white/65">Revenue</p>
                                    <p class="mt-0.5 text-base font-semibold">{toCurrency(activeDailyTooltip.row.revenue)}</p>
                                    <p class="mt-1 text-[12px] text-white/78">Transactions: {formatCompactNumber(activeDailyTooltip.row.transaction_count || 0)}</p>
                                    <p class="text-[11px] text-white/58">Margin: {toCompactCurrency(activeDailyTooltip.row.margin || 0)}</p>
                                    <span class={`absolute top-full h-3 w-3 -translate-y-1/2 rotate-45 bg-slate-900/96 ${tooltipArrowClass(activeDailyTooltip.align)}`}></span>
                                </div>
                            {/if}

                            <div class="absolute inset-y-3 left-0 w-10">
                                {#each lineChartTicks as tick (`daily-tick-${tick.y}`)}
                                    <div class="absolute left-0 -translate-y-1/2 text-[10px] font-medium text-slate-400 dark:text-slate-500" style={`top:${tick.y}%`}>
                                        {toCompactCurrency(tick.value)}
                                    </div>
                                {/each}
                            </div>

                            <div class="absolute inset-y-3 left-11 right-1 overflow-visible">
                                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                                    {#each lineChartTicks as tick (`daily-grid-${tick.y}`)}
                                        <line x1="0" y1={tick.y} x2="100" y2={tick.y} stroke="currentColor" class="text-slate-200/90 dark:text-slate-800" stroke-dasharray="2 3"></line>
                                    {/each}
                                    {#if lineAreaPath}
                                        <path d={lineAreaPath} fill="url(#dailyRevenueAreaCompact)"></path>
                                        <defs>
                                            <linearGradient id="dailyRevenueAreaCompact" x1="0" x2="0" y1="0" y2="1">
                                                <stop offset="0%" stop-color="rgb(14 165 233 / 0.32)"></stop>
                                                <stop offset="100%" stop-color="rgb(14 165 233 / 0.02)"></stop>
                                            </linearGradient>
                                        </defs>
                                    {/if}
                                    {#if linePath}
                                        <path d={linePath} fill="none" stroke="rgb(37 99 235)" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"></path>
                                    {/if}
                                </svg>

                                {#each lineChartPoints as point, index (point.key)}
                                    <button
                                        type="button"
                                        class={`absolute h-3.5 w-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-blue-500 shadow-sm outline-hidden transition hover:scale-110 focus-visible:ring-2 focus-visible:ring-blue-500/45 dark:border-slate-950 ${dailySelectedClass(point.row)}`}
                                        style={`left:${point.x}%; top:${point.y}%`}
                                        onclick={() => togglePinnedDailyPoint(point.row)}
                                        onmouseenter={() => hoverDailyPoint(point.row)}
                                        onfocus={() => hoverDailyPoint(point.row)}
                                        onmouseleave={() => hoverDailyPoint(null)}
                                        onblur={() => hoverDailyPoint(null)}
                                        aria-label={`${point.row.date ?? point.row.label} ${toCurrency(point.row.revenue)}`}
                                    >
                                        <span class="sr-only">{point.row.date ?? point.row.label}</span>
                                    </button>

                                    {#if showLineAxisLabel(index)}
                                        <div class="absolute top-[calc(100%+0.35rem)] -translate-x-1/2 text-[10px] font-medium text-slate-500 dark:text-slate-400" style={`left:${point.x}%`}>
                                            {point.row.date ?? point.row.label}
                                        </div>
                                    {/if}
                                {/each}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="h-fit overflow-hidden border border-slate-200/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(248,250,252,0.96))] shadow-sm dark:border-slate-700/70 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.98),rgba(15,23,42,0.96))]">
                <CardHeader class="space-y-1 pb-1">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">{heatmapYearLabel}</p>
                            <CardTitle class="mt-1 text-[1.65rem] font-semibold tracking-tight text-slate-950 dark:text-slate-50">Transactions & Revenue</CardTitle>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge variant="secondary" class="rounded-full px-3 py-1 text-[11px] font-semibold">{toCompactCurrency(yearlyRevenueTotal)}</Badge>
                            <Badge variant="outline" class="rounded-full px-3 py-1 text-[11px] font-semibold">{formatCompactNumber(yearlyTransactionsTotal)} TRX</Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="pt-0 pb-3">
                    <div class="rounded-[24px] border border-slate-200/80 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(255,255,255,0.92))] p-3 shadow-inner dark:border-slate-800 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.84),rgba(15,23,42,0.7))] md:p-4">
                        <div class="mb-2 flex items-center justify-between gap-2 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            <span>Contribution graph</span>
                            <div class="hidden items-center gap-1 md:flex">
                                <span>Low</span>
                                {#each [
                                    'bg-slate-100 dark:bg-slate-800/90',
                                    'bg-cyan-100 dark:bg-cyan-500/20',
                                    'bg-sky-200 dark:bg-sky-500/30',
                                    'bg-sky-400 dark:bg-sky-500',
                                    'bg-sky-600',
                                ] as tone (`heatmap-legend-compact-${tone}`)}
                                    <span class={`h-3 w-3 rounded-[4px] ${tone}`}></span>
                                {/each}
                                <span>High</span>
                            </div>
                        </div>

                        <div class="overflow-x-auto pb-1">
                            <div class="relative min-w-[760px] pt-11" role="presentation" onmouseleave={() => hoverHeatmapDay(null)}>
                                {#if activeHeatmapTooltip}
                                    <div
                                        class={`pointer-events-none absolute top-0 z-10 w-[210px] rounded-2xl bg-slate-900/96 px-3 py-2 text-white shadow-2xl transition ${tooltipTranslateClass(activeHeatmapTooltip.align)}`}
                                        style={`left:${activeHeatmapTooltip.left}%`}
                                    >
                                        <p class="text-sm font-semibold">{activeHeatmapTooltip.row.name ?? activeHeatmapTooltip.row.date}</p>
                                        <div class="mt-2 space-y-1 text-[12px]">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-white/68">TPV</span>
                                                <span class="font-semibold">{toCurrency(activeHeatmapTooltip.row.revenue)}</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-white/68">TRX</span>
                                                <span class="font-semibold">{formatCompactNumber(activeHeatmapTooltip.row.transaction_count || 0)}</span>
                                            </div>
                                        </div>
                                        <span class={`absolute top-full h-3 w-3 -translate-y-1/2 rotate-45 bg-slate-900/96 ${tooltipArrowClass(activeHeatmapTooltip.align)}`}></span>
                                    </div>
                                {/if}

                                <div class="absolute inset-x-0 top-0 h-5">
                                    {#each heatmapMonthMarkers as marker (marker.key)}
                                        <span
                                            class={`absolute top-0 text-[11px] font-medium text-slate-500 dark:text-slate-400 ${tooltipTranslateClass(tooltipAlignFor(marker.left))}`}
                                            style={`left:${marker.left}%`}
                                        >
                                            {marker.label}
                                        </span>
                                    {/each}
                                </div>

                                <div class="grid grid-flow-col auto-cols-[12px] grid-rows-7 gap-[3px] pt-2 md:auto-cols-[14px]">
                                    {#each heatmapContributionCells as cell (cell.key)}
                                        {#if cell.item}
                                            {@const day = cell.item}
                                            <button
                                                type="button"
                                                class={`h-3 w-3 rounded-[4px] outline-hidden transition hover:scale-110 focus-visible:ring-2 focus-visible:ring-sky-500/45 md:h-3.5 md:w-3.5 ${heatmapToneClass(day)} ${heatmapSelectedClass(day)}`}
                                                onclick={() => togglePinnedHeatmapDay(day)}
                                                onmouseenter={() => hoverHeatmapDay(day)}
                                                onfocus={() => hoverHeatmapDay(day)}
                                                onmouseleave={() => hoverHeatmapDay(null)}
                                                onblur={() => hoverHeatmapDay(null)}
                                                aria-label={`${day.name ?? day.date} ${toCurrency(day.revenue)}`}
                                                disabled={day.is_future}
                                            ></button>
                                        {:else}
                                            <div class="h-3 w-3 rounded-[4px] bg-transparent md:h-3.5 md:w-3.5"></div>
                                        {/if}
                                    {/each}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="hidden h-fit xl:block">
                <CardHeader class="space-y-1 pb-2">
                    <CardTitle class="text-sm md:text-base">Insight Cepat</CardTitle>
                    <CardDescription class="text-xs">Snapshot operasional & finansial</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-2.5 pt-0 text-sm xl:grid-cols-2">
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Rute Teratas</p>
                        <p class="text-sm font-semibold">{stats.top_route}</p>
                        <p class="text-xs text-muted-foreground">{stats.top_route_count} booking</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Armada Aktif Hari Ini</p>
                        <p class="text-sm font-semibold">{stats.live_fleet} unit</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Pendapatan Hari Ini</p>
                        <p class="text-sm font-semibold">{toCurrency(stats.revenue_total_today)}</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Pendapatan Bulan Ini</p>
                        <p class="text-sm font-semibold">{toCurrency(stats.revenue_total_month)}</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Margin Bulan Ini</p>
                        <p class="text-sm font-semibold text-green-700 dark:text-green-300">{toCurrency(stats.margin_total_month)}</p>
                    </div>
                    {#if stats.target_revenue_month > 0}
                        <div class="rounded-md border p-2.5">
                            <p class="text-xs text-muted-foreground">Target & Pencapaian</p>
                            <p class="text-sm font-semibold">{toCurrency(stats.target_revenue_month)}</p>
                            <div class="mt-1.5 h-1.5 rounded-full bg-muted/80">
                                <div class="h-1.5 rounded-full bg-purple-500/70" style={`width:${Math.min(stats.achievement_percent, 100)}%`}></div>
                            </div>
                            <p class="mt-0.5 text-[10px] text-muted-foreground">{stats.achievement_percent}% tercapai</p>
                        </div>
                    {/if}
                    <div class="rounded-md border p-2.5 xl:col-span-2">
                        <p class="text-xs text-muted-foreground">Pendapatan Tahun Ini</p>
                        <p class="text-sm font-semibold">{toCurrency(stats.revenue_total_year)}</p>
                    </div>
                </CardContent>
            </Card>

        </div>

        <div class="space-y-2.5 xl:col-span-1">
            <Card class="overflow-hidden">
                <CardHeader class="space-y-1 pb-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <CardTitle class="text-sm md:text-base">Info Carter 7 Hari Kedepan</CardTitle>
                            <CardDescription class="text-xs">Reminder charter terdekat, diurutkan dari tanggal paling dekat</CardDescription>
                        </div>
                        {#if upcomingCharterReminder.total > 0}
                            <Badge variant="secondary">{upcomingCharterReminder.total} data</Badge>
                        {/if}
                    </div>
                </CardHeader>
                <CardContent class="space-y-2.5 pt-0">
                    {#if upcomingCharterReminder.items.length === 0}
                        <div class="rounded-md border border-dashed p-3 text-xs text-muted-foreground">
                            Belum ada data carter untuk 7 hari ke depan.
                        </div>
                    {:else}
                        {#each upcomingCharterReminder.items as item (`upcoming-charter-${item.id}`)}
                            <a
                                href={`/charters/view/${item.id}`}
                                class="block rounded-xl border border-border/70 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(236,254,255,0.82))] p-3 transition hover:border-cyan-300/70 hover:shadow-sm dark:bg-[linear-gradient(180deg,rgba(15,23,42,0.95),rgba(8,47,73,0.65))]"
                            >
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-foreground">{item.name}</p>
                                        <p class="truncate text-[11px] text-muted-foreground">
                                            {item.company_name || item.phone || 'Customer charter'}
                                        </p>
                                    </div>
                                    <span class="rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-semibold text-cyan-700">
                                        {charterReminderTag(item)}
                                    </span>
                                </div>
                                <div class="space-y-1 text-[11px] text-muted-foreground">
                                    <p class="font-medium text-foreground">{item.date_label}</p>
                                    <p>{item.departure_time || '--:--'} • {item.layanan || '-'}</p>
                                    <p class="truncate">Rute: {item.pickup_point || '-'} → {item.drop_point || '-'}</p>
                                    <p class="truncate">Driver: {item.driver_name || '-'}</p>
                                </div>
                            </a>
                        {/each}
                        {#if upcomingCharterOverflow > 0}
                            <a
                                href="/charters"
                                class="flex items-center justify-between rounded-xl border border-dashed border-cyan-300/70 bg-cyan-50/70 px-3 py-2 text-xs font-medium text-cyan-800 transition hover:bg-cyan-100/80"
                            >
                                <span>Lihat {upcomingCharterOverflow} reminder lainnya</span>
                                <span>Ke menu Carter</span>
                            </a>
                        {/if}
                    {/if}
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="space-y-1 pb-2">
                    <CardTitle class="text-sm md:text-base">Keberangkatan Hari Ini</CardTitle>
                    <CardDescription class="text-xs">Data jadwal dari booking aktif hari ini</CardDescription>
                </CardHeader>
                <CardContent class="space-y-2.5 pt-0">
                    {#if copyMessage}
                        <p class="text-xs text-emerald-600">{copyMessage}</p>
                    {/if}
                    {#if copyError}
                        <p class="text-xs text-destructive">{copyError}</p>
                    {/if}
                    {#if departuresToday.length === 0}
                        <p class="text-xs text-muted-foreground">Belum ada data keberangkatan hari ini.</p>
                    {:else}
                        {#each departuresToday as item, idx (`departure-${idx}-${item.rute}-${item.jam}-${item.unit}`)}
                            <div class="rounded-md border p-2.5">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <p class="text-xs font-semibold md:text-sm">{item.jam} - Unit {item.unit}</p>
                                    <Badge variant="secondary">{item.total_bookings} booking</Badge>
                                </div>
                                <div class="flex items-end justify-between gap-2">
                                    <div class="min-w-0 space-y-0.5">
                                        <p class="truncate text-xs text-muted-foreground">{item.rute}</p>
                                        <p class="truncate text-[11px] text-muted-foreground">
                                            Driver: {item.driver_name || '-'}
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        class="h-7 rounded-md px-2 text-[11px]"
                                        onclick={() => void copyDepartureData(item)}
                                        disabled={copyingDepartureKey === departureCopyKey(item)}
                                    >
                                        <Copy class="mr-1 h-3 w-3" />
                                        {copyingDepartureKey === departureCopyKey(item)
                                            ? 'Menyalin...'
                                            : 'Copy Data'}
                                    </Button>
                                </div>
                            </div>
                        {/each}
                    {/if}
                </CardContent>
            </Card>

            <Card class="xl:hidden">
                <CardHeader class="space-y-1 pb-2">
                    <CardTitle class="text-sm md:text-base">Insight Cepat</CardTitle>
                    <CardDescription class="text-xs">Snapshot operasional & finansial</CardDescription>
                </CardHeader>
                <CardContent class="space-y-2.5 pt-0 text-sm">
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Rute Teratas</p>
                        <p class="text-sm font-semibold">{stats.top_route}</p>
                        <p class="text-xs text-muted-foreground">{stats.top_route_count} booking</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Armada Aktif Hari Ini</p>
                        <p class="text-sm font-semibold">{stats.live_fleet} unit</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Pendapatan Hari Ini</p>
                        <p class="text-sm font-semibold">{toCurrency(stats.revenue_total_today)}</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Pendapatan Bulan Ini</p>
                        <p class="text-sm font-semibold">{toCurrency(stats.revenue_total_month)}</p>
                    </div>
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Margin Bulan Ini</p>
                        <p class="text-sm font-semibold text-green-700 dark:text-green-300">{toCurrency(stats.margin_total_month)}</p>
                    </div>
                    {#if stats.target_revenue_month > 0}
                        <div class="rounded-md border p-2.5">
                            <p class="text-xs text-muted-foreground">Target & Pencapaian</p>
                            <p class="text-sm font-semibold">{toCurrency(stats.target_revenue_month)}</p>
                            <div class="mt-1.5 h-1.5 rounded-full bg-muted/80">
                                <div class="h-1.5 rounded-full bg-purple-500/70" style={`width:${Math.min(stats.achievement_percent, 100)}%`}></div>
                            </div>
                            <p class="mt-0.5 text-[10px] text-muted-foreground">{stats.achievement_percent}% tercapai</p>
                        </div>
                    {/if}
                    <div class="rounded-md border p-2.5">
                        <p class="text-xs text-muted-foreground">Pendapatan Tahun Ini</p>
                        <p class="text-sm font-semibold">{toCurrency(stats.revenue_total_year)}</p>
                    </div>
                </CardContent>
            </Card>

            <Card class="hidden h-fit xl:block">
                <CardHeader class="space-y-1 pb-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <CardTitle class="text-sm md:text-base">Aktivitas Terbaru</CardTitle>
                            <CardDescription class="text-xs">Update terbaru dari sistem</CardDescription>
                        </div>
                        {#if recentActivityTotal > 0}
                            <Badge variant="secondary">{recentActivityTotal} log</Badge>
                        {/if}
                    </div>
                </CardHeader>
                <CardContent class="pt-0">
                    {#if recentActivity.length === 0}
                        <p class="text-xs text-muted-foreground">Belum ada aktivitas.</p>
                    {:else}
                        <div class="divide-y divide-border rounded-xl border border-border/70 bg-background/70">
                            {#each recentActivity as item, idx (`activity-desktop-${idx}-${item.tag}`)}
                                <div class="flex items-start justify-between gap-3 px-3 py-2.5">
                                    <div class="min-w-0 flex-1">
                                        <div class="mb-1 flex items-center gap-2">
                                            <Badge variant="secondary">{item.tag}</Badge>
                                        </div>
                                        <p class="truncate text-sm font-semibold leading-snug text-foreground">{item.title}</p>
                                        <p class="truncate text-xs leading-relaxed text-muted-foreground">{item.meta}</p>
                                    </div>
                                    <span class="shrink-0 text-[11px] text-muted-foreground">{item.time}</span>
                                </div>
                            {/each}
                        </div>
                        {#if recentActivityOverflow > 0}
                            <a
                                href="/admin-ops/cancellations"
                                class="mt-2.5 flex items-center justify-between rounded-xl border border-dashed border-border/70 bg-muted/25 px-3 py-2 text-xs font-medium text-foreground transition hover:bg-muted/40"
                            >
                                <span>Lihat {recentActivityOverflow} aktivitas lainnya</span>
                                <span>Ke menu Log</span>
                            </a>
                        {/if}
                    {/if}
                </CardContent>
            </Card>
        </div>

        <Card class="order-last h-fit xl:hidden">
            <CardHeader class="space-y-1 pb-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <CardTitle class="text-sm md:text-base">Aktivitas Terbaru</CardTitle>
                        <CardDescription class="text-xs">Update terbaru dari sistem</CardDescription>
                    </div>
                    {#if recentActivityTotal > 0}
                        <Badge variant="secondary">{recentActivityTotal} log</Badge>
                    {/if}
                </div>
            </CardHeader>
            <CardContent class="space-y-2.5 pt-0">
                {#if recentActivity.length === 0}
                    <p class="text-xs text-muted-foreground">Belum ada aktivitas.</p>
                {:else}
                    {#each recentActivity as item, idx (`activity-mobile-${idx}-${item.tag}`)}
                        <div class="rounded-md border p-2.5">
                            <div class="mb-1 flex items-center justify-between gap-2">
                                <Badge variant="secondary">{item.tag}</Badge>
                                <span class="text-xs text-muted-foreground">{item.time}</span>
                            </div>
                            <p class="text-xs font-semibold md:text-sm">{item.title}</p>
                            <p class="text-xs text-muted-foreground">{item.meta}</p>
                        </div>
                    {/each}
                    {#if recentActivityOverflow > 0}
                        <a
                            href="/admin-ops/cancellations"
                            class="flex items-center justify-between rounded-xl border border-dashed border-border/70 bg-muted/25 px-3 py-2 text-xs font-medium text-foreground transition hover:bg-muted/40"
                        >
                            <span>Lihat {recentActivityOverflow} aktivitas lainnya</span>
                            <span>Ke menu Log</span>
                        </a>
                    {/if}
                {/if}
            </CardContent>
        </Card>

        </div>
    </Deferred>
</div>
