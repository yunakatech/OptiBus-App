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
    import { ArrowRight, Building2, Copy } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';

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
        month_key?: string;
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
        monthlyTrend = [],
        yearlyHeatmap = [],
        recentActivity = [],
        departuresToday = [],
        upcomingCharterReminder = { total: 0, visible_count: 0, items: [] },
        recentActivityTotal = 0,
        recentActivityVisibleCount = 0,
        summaryStatsByScope = {
            day: {
                total_bookings: 0,
                revenue_booking: 0,
                revenue_charter: 0,
                revenue_luggage: 0,
                bop_booking: 0,
                bop_charter: 0,
                margin_booking: 0,
                margin_charter: 0,
                target_revenue: 0,
                achievement_percent: 0,
            },
            month: {
                total_bookings: 0,
                revenue_booking: 0,
                revenue_charter: 0,
                revenue_luggage: 0,
                bop_booking: 0,
                bop_charter: 0,
                margin_booking: 0,
                margin_charter: 0,
                target_revenue: 0,
                achievement_percent: 0,
            },
            year: {
                total_bookings: 0,
                revenue_booking: 0,
                revenue_charter: 0,
                revenue_luggage: 0,
                bop_booking: 0,
                bop_charter: 0,
                margin_booking: 0,
                margin_charter: 0,
                target_revenue: 0,
                achievement_percent: 0,
            },
        },
        summaryComparisonByScope = {
            day: {
                total_bookings: 0,
                revenue_booking: 0,
                revenue_charter: 0,
                revenue_luggage: 0,
                bop_booking: 0,
                bop_charter: 0,
                margin_booking: 0,
                margin_charter: 0,
                target_revenue: 0,
                achievement_percent: 0,
            },
            month: {
                total_bookings: 0,
                revenue_booking: 0,
                revenue_charter: 0,
                revenue_luggage: 0,
                bop_booking: 0,
                bop_charter: 0,
                margin_booking: 0,
                margin_charter: 0,
                target_revenue: 0,
                achievement_percent: 0,
            },
            year: {
                total_bookings: 0,
                revenue_booking: 0,
                revenue_charter: 0,
                revenue_luggage: 0,
                bop_booking: 0,
                bop_charter: 0,
                margin_booking: 0,
                margin_charter: 0,
                target_revenue: 0,
                achievement_percent: 0,
            },
        },
        summaryPeriodByScope = {
            day: {
                current_label: 'Hari Ini',
                previous_label: 'Kemarin',
                subtitle_label: 'hari ini',
            },
            month: {
                current_label: 'Bulan Ini',
                previous_label: 'Bulan Lalu',
                subtitle_label: 'bulan ini',
            },
            year: {
                current_label: 'Tahun Ini',
                previous_label: 'Tahun Lalu',
                subtitle_label: 'tahun ini',
            },
        },
    }: {
        stats: DashboardStats;
        pools?: PoolOption[];
        selectedPoolId?: number;
        selectedPoolName?: string;
        dailyTrend?: TrendItem[];
        monthlyTrend?: TrendItem[];
        yearlyHeatmap?: HeatmapItem[];
        recentActivity?: ActivityItem[];
        departuresToday?: DepartureItem[];
        upcomingCharterReminder?: UpcomingCharterReminder;
        recentActivityTotal?: number;
        recentActivityVisibleCount?: number;
        summaryStatsByScope?: Record<
            'day' | 'month' | 'year',
            SummaryScopeStats
        >;
        summaryComparisonByScope?: Record<
            'day' | 'month' | 'year',
            SummaryScopeStats
        >;
        summaryPeriodByScope?: Record<
            'day' | 'month' | 'year',
            SummaryPeriodMeta
        >;
    } = $props();

    let trendMode = $state<'monthly' | 'daily'>('monthly');

    const activeTrendRows = $derived(
        trendMode === 'daily' ? dailyTrend : monthlyTrend,
    );
    const maxTrendRevenue = $derived(
        Math.max(
            0,
            ...activeTrendRows.map((item) => Number(item.revenue || 0)),
        ),
    );
    const lineScaleMax = $derived(maxTrendRevenue > 0 ? maxTrendRevenue : 1);
    const currentMonthKey = $derived.by(() => {
        const date = new Date();
        const month = String(date.getMonth() + 1).padStart(2, '0');

        return `${date.getFullYear()}-${month}`;
    });
    const currentMonthHeatmapDays = $derived(
        yearlyHeatmap.filter(
            (item) => !item.is_future && item.date?.startsWith(currentMonthKey),
        ),
    );
    const yearlyHeatmapDays = $derived(yearlyHeatmap);
    const maxYearlyHeatmapRevenue = $derived(
        Math.max(
            1,
            ...yearlyHeatmapDays.map((item) => Number(item.revenue || 0)),
        ),
    );
    const maxCurrentMonthHeatmapRevenue = $derived(
        Math.max(
            1,
            ...currentMonthHeatmapDays.map((item) => Number(item.revenue || 0)),
        ),
    );
    const upcomingCharterOverflow = $derived(
        Math.max(
            Number(upcomingCharterReminder.total || 0) -
                Number(upcomingCharterReminder.visible_count || 0),
            0,
        ),
    );
    const recentActivityOverflow = $derived(
        Math.max(
            Number(recentActivityTotal || 0) -
                Number(recentActivityVisibleCount || 0),
            0,
        ),
    );
    const activeTrendRevenueTotal = $derived(
        trendMode === 'monthly'
            ? Number(stats.revenue_total_year || 0)
            : activeTrendRows.reduce(
                  (total, item) => total + Number(item.revenue || 0),
                  0,
              ),
    );
    const currentMonthRevenueTotal = $derived(
        currentMonthHeatmapDays.reduce(
            (total, item) => total + Number(item.revenue || 0),
            0,
        ),
    );
    const visibleRevenueDays = $derived(
        currentMonthHeatmapDays.filter((item) => Number(item.revenue || 0) > 0),
    );

    const trendKey = (row: TrendItem) =>
        `trend-${row.month_key ?? row.label}-${row.name ?? row.date ?? ''}`;
    const heatmapKey = (row: HeatmapItem) => `heatmap-${row.date}`;

    let hoveredTrendKey = $state<string>('');
    let pinnedTrendKey = $state<string>('');
    let hoveredHeatmapKey = $state<string>('');
    let pinnedHeatmapKey = $state<string>('');
    let selectedSummaryScope = $state<'day' | 'month' | 'year'>('month');

    const activeTrendTooltipKey = $derived(hoveredTrendKey || pinnedTrendKey);
    const activeHeatmapTooltipKey = $derived(
        hoveredHeatmapKey || pinnedHeatmapKey,
    );
    const heatmapMonthLabel = $derived(
        currentMonthHeatmapDays[0]?.date
            ? new Date(
                  `${currentMonthHeatmapDays[0].date}T00:00:00`,
              ).toLocaleDateString('id-ID', {
                  month: 'long',
                  year: 'numeric',
              })
            : summaryPeriodByScope.month.current_label,
    );
    const activeTrendTitle = $derived(
        trendMode === 'daily' ? 'Daily Revenue' : 'Monthly Revenue',
    );
    const heatmapYearLabel = $derived(`${new Date().getFullYear()}`);
    const activeTrendEyebrow = $derived(
        trendMode === 'daily' ? '30 Hari Terakhir' : 'Tahun Berjalan',
    );
    const activeTrendRangeLabel = $derived(
        trendMode === 'daily' ? '30 hari terakhir' : 'Jan - Des',
    );
    const activeTrendDataSignature = $derived(
        `${selectedPoolId}|${trendMode}|${activeTrendRows
            .map(
                (row) =>
                    `${trendKey(row)}:${Number(row.revenue || 0)}:${Number(row.booking_revenue || 0)}:${Number(row.luggage_revenue || 0)}:${Number(row.charter_revenue || 0)}`,
            )
            .join('|')}`,
    );
    const heatmapDataSignature = $derived(
        `${selectedPoolId}|${yearlyHeatmapDays
            .map(
                (row) =>
                    `${heatmapKey(row)}:${Number(row.revenue || 0)}:${Number(row.booking_revenue || 0)}:${Number(row.luggage_revenue || 0)}:${Number(row.charter_revenue || 0)}`,
            )
            .join('|')}`,
    );
    const activeSummaryStats = $derived(
        summaryStatsByScope[selectedSummaryScope] ?? summaryStatsByScope.month,
    );
    const activeSummaryPeriod = $derived(
        summaryPeriodByScope[selectedSummaryScope] ??
            summaryPeriodByScope.month,
    );
    const activeTotalRevenue = $derived(
        Number(activeSummaryStats.revenue_booking || 0) +
            Number(activeSummaryStats.revenue_charter || 0) +
            Number(activeSummaryStats.revenue_luggage || 0),
    );
    const activeTotalBop = $derived(
        Number(activeSummaryStats.bop_booking || 0) +
            Number(activeSummaryStats.bop_charter || 0),
    );
    const activeTotalMargin = $derived(activeTotalRevenue - activeTotalBop);
    const activeAchievementWidth = $derived(
        Math.min(
            Math.max(Number(activeSummaryStats.achievement_percent || 0), 0),
            100,
        ),
    );
    const lineChartPoints = $derived.by(() =>
        activeTrendRows.map((row, index) => {
            const x =
                activeTrendRows.length <= 1
                    ? 50
                    : (index / (activeTrendRows.length - 1)) * 100;
            const y =
                100 -
                Math.min(
                    100,
                    Math.max(
                        0,
                        (Number(row.revenue || 0) / lineScaleMax) * 100,
                    ),
                );

            return {
                key: trendKey(row),
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
                  .map(
                      (point, index) =>
                          `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`,
                  )
                  .join(' ')
            : '',
    );
    const lineAreaPath = $derived.by(() =>
        lineChartPoints.length
            ? `M ${lineChartPoints[0].x} 100 ${lineChartPoints
                  .map((point) => `L ${point.x} ${point.y}`)
                  .join(
                      ' ',
                  )} L ${lineChartPoints[lineChartPoints.length - 1].x} 100 Z`
            : '',
    );
    const lineChartTicks = $derived.by(() =>
        [1, 0.5, 0].map((factor) => ({
            y: (1 - factor) * 100,
            value: maxTrendRevenue * factor,
        })),
    );
    const lineAxisIndexes = $derived.by(() => {
        const totalPoints = lineChartPoints.length;

        if (totalPoints === 0) {
            return new Set<number>();
        }

        if (totalPoints <= 12) {
            const indexes = new Set<number>();

            for (let index = 0; index < totalPoints; index += 2) {
                indexes.add(index);
            }

            indexes.add(totalPoints - 1);

            return indexes;
        }

        const indexes = new Set<number>([0, totalPoints - 1]);
        const targetCount = Math.min(5, totalPoints);

        for (let step = 1; step < targetCount - 1; step += 1) {
            indexes.add(
                Math.round((step / (targetCount - 1)) * (totalPoints - 1)),
            );
        }

        return indexes;
    });
    const buildHeatmapContributionColumns = (rows: HeatmapItem[]) => {
        const columns: Array<{
            key: string;
            cells: Array<HeatmapItem | null>;
        }> = [];
        let weekCells = Array<HeatmapItem | null>(7).fill(null);

        rows.forEach((row, index) => {
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
    };
    const heatmapContributionColumns = $derived.by(() =>
        buildHeatmapContributionColumns(yearlyHeatmapDays),
    );
    const mobileHeatmapContributionColumns = $derived.by(() =>
        buildHeatmapContributionColumns(currentMonthHeatmapDays),
    );
    const heatmapCellsFromColumns = (
        columns: Array<{
            key: string;
            cells: Array<HeatmapItem | null>;
        }>,
    ): HeatmapContributionCell[] => {
        const totalColumns = Math.max(columns.length, 1);

        return columns.flatMap((column, columnIndex) =>
            column.cells.map((item, rowIndex) => ({
                key: item
                    ? heatmapKey(item)
                    : `${column.key}-empty-${rowIndex}`,
                item,
                columnIndex,
                rowIndex,
                left: ((columnIndex + 0.5) / totalColumns) * 100,
            })),
        );
    };
    const heatmapContributionCells = $derived.by<HeatmapContributionCell[]>(
        () => heatmapCellsFromColumns(heatmapContributionColumns),
    );
    const mobileHeatmapContributionCells = $derived.by<
        HeatmapContributionCell[]
    >(() => heatmapCellsFromColumns(mobileHeatmapContributionColumns));
    const heatmapMonthMarkers = $derived.by<HeatmapMonthMarker[]>(() =>
        heatmapContributionColumns.flatMap((column, columnIndex) => {
            const monthStart = column.cells.find(
                (item) => item?.day_number === 1,
            );

            if (!monthStart) {
                return [];
            }

            return [
                {
                    key: `${monthStart.date}-marker`,
                    label:
                        monthStart.month_short ?? monthStart.month_label ?? '',
                    left:
                        ((columnIndex + 0.5) /
                            Math.max(heatmapContributionColumns.length, 1)) *
                        100,
                },
            ];
        }),
    );

    const toCurrency = (value: number) =>
        `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
    const normalizeJam = (value: string) => String(value || '').trim();
    const isCanceledBooking = (status: string | null | undefined) =>
        String(status || '')
            .trim()
            .toLowerCase() === 'canceled';
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

    const setTrendMode = (mode: 'monthly' | 'daily') => {
        trendMode = mode;
        hoveredTrendKey = '';
        pinnedTrendKey = '';
    };

    const isMobileViewport = () =>
        typeof window !== 'undefined' && window.innerWidth < 768;
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
    const trendLabel = (row: TrendItem) => row.name ?? row.date ?? row.label;
    const hoverTrendPoint = (row: TrendItem | null) => {
        hoveredTrendKey = row ? trendKey(row) : '';
    };
    const togglePinnedTrendPoint = (row: TrendItem) => {
        const key = trendKey(row);

        pinnedTrendKey = pinnedTrendKey === key ? '' : key;
        hoveredTrendKey = key;
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

    $effect(() => {
        activeTrendDataSignature;
        hoveredTrendKey = '';
        pinnedTrendKey = '';
    });

    $effect(() => {
        heatmapDataSignature;
        hoveredHeatmapKey = '';
        pinnedHeatmapKey = '';
    });

    const activeTrendTooltip = $derived.by(() => {
        if (!activeTrendTooltipKey) {
            return null;
        }

        const point = lineChartPoints.find(
            (item) => item.key === activeTrendTooltipKey,
        );

        if (!point) {
            return null;
        }

        const left = clampPercent(point.x, 8, 92);

        return {
            row: point.row,
            left,
            align: tooltipAlignFor(left),
        };
    });
    const activeHeatmapTooltip = $derived.by(() => {
        if (!activeHeatmapTooltipKey) {
            return null;
        }

        const tooltipCells = isMobileViewport()
            ? [...mobileHeatmapContributionCells, ...heatmapContributionCells]
            : [...heatmapContributionCells, ...mobileHeatmapContributionCells];
        const cell = tooltipCells.find(
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
    const trendSelectedClass = (row: TrendItem) =>
        activeTrendTooltipKey === trendKey(row) ? 'ring-4 ring-sky-500/20' : '';
    const heatmapSelectedClass = (row: HeatmapItem) =>
        activeHeatmapTooltipKey === heatmapKey(row)
            ? 'ring-2 ring-sky-500/60 ring-offset-2 ring-offset-white dark:ring-offset-slate-950'
            : 'ring-1 ring-slate-200/70 dark:ring-slate-800';
    const heatmapToneClass = (row: HeatmapItem, maxRevenue: number) => {
        if (row.is_future) {
            return 'bg-slate-100/80 text-slate-300 dark:bg-slate-900 dark:text-slate-700';
        }

        const revenue = Number(row.revenue || 0);

        if (revenue <= 0) {
            return 'bg-slate-100 text-slate-400 dark:bg-slate-800/90 dark:text-slate-500';
        }

        const ratio = revenue / maxRevenue;

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

    const revenueChannels = () => [
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

    const departureCopyKey = (item: DepartureItem) =>
        `${item.rute}-${item.jam}-${item.unit}`;

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

<div
    class="flex h-full flex-1 flex-col gap-2 overflow-x-clip rounded-xl px-2 py-2 md:gap-3 md:p-4"
>
    <div class="space-y-2">
        <div
            class="flex flex-col gap-2 rounded-2xl border border-border/70 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(248,250,252,0.92))] px-3 py-2.5 shadow-sm dark:border-slate-700/70 dark:bg-[linear-gradient(135deg,rgba(15,23,42,0.96),rgba(30,41,59,0.9))] md:flex-row md:items-end md:justify-between md:rounded-3xl md:px-4 md:py-3"
        >
            <div class="space-y-0.5 md:space-y-1">
                <p
                    class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground md:text-[11px]"
                >
                    Ringkasan Dashboard
                </p>
                <h2
                    class="text-base font-semibold tracking-tight text-foreground md:text-lg"
                >
                    Performa Booking dan Pendapatan
                </h2>
                {#if selectedPoolId > 0}
                    <p class="text-[11px] text-muted-foreground">
                        <Building2 class="inline size-3 -mt-0.5 mr-1" />
                        Pool aktif:
                        <span class="font-medium text-primary"
                            >{selectedPoolName}</span
                        >
                    </p>
                {/if}
            </div>
            <div class="flex flex-col gap-2 md:items-end">
                <div
                    class="hidden items-center gap-2 self-start rounded-full border border-border/70 bg-white/80 px-3 py-1.5 text-[11px] font-medium text-muted-foreground dark:border-slate-700/70 dark:bg-slate-900/70 sm:flex md:self-auto"
                >
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Perbandingan vs {activeSummaryPeriod.previous_label}
                </div>
                <div
                    class="grid w-full grid-cols-3 rounded-2xl border border-border/70 bg-white/80 p-0.5 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/70 md:w-auto md:p-1"
                >
                    {#each [{ key: 'day', label: 'Hari Ini' }, { key: 'month', label: 'Bulan Ini' }, { key: 'year', label: 'Tahun Ini' }] as option (`summary-scope-${option.key}`)}
                        <button
                            type="button"
                            class={`rounded-xl px-2 py-1.5 text-center text-[10px] font-medium transition md:px-3 md:text-[11px] ${
                                selectedSummaryScope === option.key
                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted/60'
                            }`}
                            onclick={() =>
                                updateSummaryScope(
                                    option.key as 'day' | 'month' | 'year',
                                )}
                        >
                            {option.label}
                        </button>
                    {/each}
                </div>
            </div>
        </div>
        <section
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-700/70 dark:bg-slate-950 md:rounded-3xl"
        >
            <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                <div
                    class="border-b border-slate-200/80 bg-slate-900 p-3 text-slate-50 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] dark:border-slate-800 md:p-4 lg:border-b-0 lg:border-r"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-200/80"
                            >
                                Command Center
                            </p>
                            <h3
                                class="mt-1 break-words text-xl font-semibold leading-tight text-white md:text-3xl"
                            >
                                {toCurrency(activeTotalRevenue)}
                            </h3>
                            <p class="mt-1 text-xs text-slate-200/80">
                                Total revenue {activeSummaryPeriod.subtitle_label}
                            </p>
                        </div>
                        <a
                            href="/reports"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-white/10 text-white transition hover:bg-white/20"
                            aria-label="Buka laporan revenue"
                        >
                            <ArrowRight class="h-4 w-4" />
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 md:mt-5">
                        <div
                            class="min-w-0 rounded-2xl border border-white/10 bg-white/10 p-2.5 backdrop-blur-[2px] md:p-3"
                        >
                            <p class="text-[11px] text-slate-200/80">Target</p>
                            <p
                                class="mt-1 break-words text-sm font-semibold text-white"
                            >
                                {toCurrency(
                                    activeSummaryStats.target_revenue ||
                                        stats.target_revenue_month,
                                )}
                            </p>
                        </div>
                        <div
                            class="min-w-0 rounded-2xl border border-white/10 bg-white/10 p-2.5 backdrop-blur-[2px] md:p-3"
                        >
                            <p class="text-[11px] text-slate-200/80">
                                Achievement
                            </p>
                            <p class="mt-1 text-sm font-semibold text-white">
                                {activeSummaryStats.achievement_percent ||
                                    stats.achievement_percent}%
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-3 h-2 overflow-hidden rounded-full bg-white/15"
                    >
                        <div
                            class="h-full rounded-full bg-emerald-400 transition-all"
                            style={`width:${activeAchievementWidth}%`}
                        ></div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-200/75"
                            >
                                Revenue Channel
                            </p>
                            <span class="text-[11px] text-slate-300/80"
                                >{activeSummaryPeriod.current_label}</span
                            >
                        </div>
                        {#each revenueChannels() as channel (channel.key)}
                            {@const width =
                                activeTotalRevenue > 0
                                    ? Math.max(
                                          8,
                                          Math.round(
                                              (Number(channel.value || 0) /
                                                  activeTotalRevenue) *
                                                  100,
                                          ),
                                      )
                                    : 8}
                            <a
                                href={channel.href}
                                class="block rounded-2xl border border-white/10 bg-white/8 px-3 py-2 transition hover:bg-white/14"
                            >
                                <div
                                    class="flex items-start justify-between gap-3 text-xs"
                                >
                                    <span
                                        class="min-w-0 font-medium text-slate-100"
                                        >{channel.label}</span
                                    >
                                    <span
                                        class="min-w-0 break-words text-right font-semibold text-white"
                                        >{toCurrency(channel.value)}</span
                                    >
                                </div>
                                <div
                                    class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/12"
                                >
                                    <div
                                        class="h-full rounded-full bg-emerald-400"
                                        style={`width:${width}%`}
                                    ></div>
                                </div>
                            </a>
                        {/each}
                    </div>
                </div>

                <div class="p-3 md:p-4">
                    <div class="space-y-3">
                        <div>
                            <p
                                class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400"
                            >
                                Insight Cepat
                            </p>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Snapshot penting tanpa membuka laporan.
                            </p>
                        </div>
                        <a
                            href="/reports"
                            class="group flex items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-3 transition hover:border-primary/35 hover:bg-primary/5 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div class="min-w-0">
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400"
                                >
                                    Pendapatan Bulan Ini
                                </p>
                                <p
                                    class="mt-1 break-words text-base font-semibold text-slate-900 dark:text-slate-50"
                                >
                                    {toCurrency(stats.revenue_total_month)}
                                </p>
                                <p
                                    class="mt-1 text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Periode berjalan
                                </p>
                            </div>
                            <ArrowRight
                                class="mt-1 h-3.5 w-3.5 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-primary"
                            />
                        </a>
                        <div class="grid grid-cols-2 gap-2">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Hari Ini
                                </p>
                                <p
                                    class="mt-1 break-words text-sm font-semibold text-slate-900 dark:text-slate-50"
                                >
                                    {toCurrency(stats.revenue_total_today)}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Armada Aktif
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-50"
                                >
                                    {stats.live_fleet} unit
                                </p>
                            </div>
                        </div>
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400"
                                    >
                                        Rute Teratas
                                    </p>
                                    <p
                                        class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-slate-50"
                                    >
                                        {stats.top_route}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 rounded-full bg-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    {stats.top_route_count}
                                </span>
                            </div>
                        </div>
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div
                                class="flex items-center justify-between gap-3 text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                <span>Pendapatan Tahun Ini</span>
                                <span
                                    class="font-semibold text-slate-900 dark:text-slate-50"
                                >
                                    {toCurrency(stats.revenue_total_year)}
                                </span>
                            </div>
                            {#if stats.target_revenue_month > 0}
                                <div
                                    class="mt-3 flex items-center justify-between gap-3 text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    <span>Pencapaian Target</span>
                                    <span
                                        class="font-semibold text-slate-900 dark:text-slate-50"
                                    >
                                        {activeSummaryStats.achievement_percent ||
                                            stats.achievement_percent}%
                                    </span>
                                </div>
                                <div
                                    class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-primary transition-all"
                                        style={`width:${activeAchievementWidth}%`}
                                    ></div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <Deferred
        data={[
            'dailyTrend',
            'monthlyTrend',
            'yearlyHeatmap',
            'recentActivity',
            'recentActivityTotal',
            'recentActivityVisibleCount',
            'departuresToday',
            'upcomingCharterReminder',
        ]}
    >
        {#snippet fallback()}
            <div
                class="grid gap-2.5 xl:grid-cols-3 xl:items-start"
                aria-label="Memuat data dashboard"
            >
                <div class="space-y-2.5 xl:col-span-2">
                    {#each Array.from( { length: 2 }, ) as _, index (`dashboard-main-skeleton-${index}`)}
                        <Card class="overflow-hidden">
                            <CardContent class="space-y-3 p-4">
                                <div
                                    class="h-4 w-40 animate-pulse rounded-full bg-muted"
                                ></div>
                                <div
                                    class="h-3 w-28 animate-pulse rounded-full bg-muted/70"
                                ></div>
                                <div
                                    class="grid grid-cols-7 items-end gap-2 pt-3"
                                >
                                    {#each Array.from( { length: 7 }, ) as _, barIndex (`dashboard-chart-skeleton-${index}-${barIndex}`)}
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
                    {#each Array.from( { length: 3 }, ) as _, index (`dashboard-side-skeleton-${index}`)}
                        <Card>
                            <CardContent class="space-y-3 p-4">
                                <div
                                    class="h-4 w-36 animate-pulse rounded-full bg-muted"
                                ></div>
                                {#each Array.from( { length: index === 1 ? 3 : 2 }, ) as _, rowIndex (`dashboard-side-row-${index}-${rowIndex}`)}
                                    <div
                                        class="space-y-2 rounded-xl border border-border/60 p-3"
                                    >
                                        <div
                                            class="h-3 w-2/3 animate-pulse rounded-full bg-muted"
                                        ></div>
                                        <div
                                            class="h-3 w-full animate-pulse rounded-full bg-muted/70"
                                        ></div>
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
                <Card
                    class="h-fit overflow-visible border border-slate-200/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(248,250,252,0.96))] shadow-sm dark:border-slate-700/70 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.98),rgba(15,23,42,0.96))]"
                >
                    <CardHeader class="space-y-1 pb-2 sm:pb-1">
                        <div
                            class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between"
                        >
                            <div class="min-w-0">
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400"
                                >
                                    {activeTrendEyebrow}
                                </p>
                                <CardTitle
                                    class="mt-1 text-xl font-semibold tracking-tight text-slate-950 dark:text-slate-50 sm:text-[1.65rem]"
                                    >{activeTrendTitle}</CardTitle
                                >
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {activeTrendRangeLabel}
                                </p>
                            </div>
                            <div
                                class="flex w-full flex-wrap items-center gap-2 sm:items-center lg:w-auto lg:justify-end"
                            >
                                <div
                                    class="inline-flex w-full rounded-full border border-border/70 bg-background/80 p-1 shadow-sm sm:w-auto"
                                >
                                    <button
                                        type="button"
                                        class={`flex-1 rounded-full px-3 py-1 text-[11px] font-semibold transition sm:flex-none ${trendMode === 'monthly' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-950' : 'text-slate-600 dark:text-slate-300'}`}
                                        onclick={() => setTrendMode('monthly')}
                                    >
                                        Bulanan
                                    </button>
                                    <button
                                        type="button"
                                        class={`flex-1 rounded-full px-3 py-1 text-[11px] font-semibold transition sm:flex-none ${trendMode === 'daily' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-950' : 'text-slate-600 dark:text-slate-300'}`}
                                        onclick={() => setTrendMode('daily')}
                                    >
                                        Harian
                                    </button>
                                </div>
                                <div
                                    class="min-w-0 flex-1 rounded-2xl border border-slate-200/80 bg-background/82 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/60 sm:flex-none"
                                >
                                    <p
                                        class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                                    >
                                        {trendMode === 'daily'
                                            ? 'Total 30 Hari'
                                            : 'Total Tahun Ini'}
                                    </p>
                                    <p
                                        class="mt-1 break-words text-sm font-semibold text-slate-950 dark:text-slate-50"
                                    >
                                        {toCurrency(activeTrendRevenueTotal)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="overflow-visible pt-0 pb-3">
                        <div
                            class="overflow-visible rounded-2xl border border-slate-200/80 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(255,255,255,0.92))] p-2.5 shadow-inner dark:border-slate-800 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.84),rgba(15,23,42,0.7))] sm:rounded-[24px] sm:p-3 md:p-4"
                        >
                            <div
                                class="mb-2 flex items-center justify-between gap-2 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400"
                            >
                                <span
                                    >{trendMode === 'daily'
                                        ? 'Revenue harian'
                                        : 'Revenue bulanan'}</span
                                >
                                <span class="normal-case tracking-normal"
                                    >{lineChartPoints.length} titik</span
                                >
                            </div>

                            <div
                                class="w-full overflow-visible pb-2"
                                role="region"
                                aria-label="Grafik revenue"
                            >
                                <div
                                    class="relative h-[214px] min-w-0 overflow-visible pt-14 md:h-[244px] md:pt-16"
                                    role="presentation"
                                    onmouseleave={() => hoverTrendPoint(null)}
                                >
                                    {#if activeTrendTooltip}
                                        {#key activeTrendTooltipKey}
                                            <div
                                                class={`pointer-events-none absolute z-10 w-[240px] max-w-[calc(100vw-2rem)] rounded-xl bg-slate-900/96 px-3 py-2 text-white shadow-2xl transition sm:rounded-2xl ${tooltipTranslateClass(activeTrendTooltip.align)}`}
                                                style={`left:${activeTrendTooltip.left}%; top:0px`}
                                            >
                                                <p
                                                    class="text-sm font-semibold"
                                                >
                                                    {trendLabel(
                                                        activeTrendTooltip.row,
                                                    )}
                                                </p>
                                                <div
                                                    class="mt-2 space-y-1 text-[12px]"
                                                >
                                                    <div
                                                        class="flex items-center justify-between gap-3"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Keberangkatan</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeTrendTooltip
                                                                    .row
                                                                    .booking_revenue ||
                                                                    0,
                                                            )}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex items-center justify-between gap-3"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Bagasi</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeTrendTooltip
                                                                    .row
                                                                    .luggage_revenue ||
                                                                    0,
                                                            )}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex items-center justify-between gap-3"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Carter</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeTrendTooltip
                                                                    .row
                                                                    .charter_revenue ||
                                                                    0,
                                                            )}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex items-center justify-between gap-3 border-t border-white/12 pt-1"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Total</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeTrendTooltip
                                                                    .row
                                                                    .revenue,
                                                            )}</span
                                                        >
                                                    </div>
                                                </div>
                                                <span
                                                    class={`absolute top-full h-3 w-3 -translate-y-1/2 rotate-45 bg-slate-900/96 ${tooltipArrowClass(activeTrendTooltip.align)}`}
                                                ></span>
                                            </div>
                                        {/key}
                                    {/if}

                                    <div
                                        class="absolute top-14 bottom-6 left-0 w-[58px] md:top-16 md:w-[92px]"
                                        aria-hidden="true"
                                    >
                                        {#each lineChartTicks as tick (`trend-tick-${tick.y}`)}
                                            <div
                                                class="absolute right-0 max-w-[84px] -translate-y-1/2 text-right text-[8px] leading-tight font-medium text-slate-400 dark:text-slate-500 sm:text-[9px]"
                                                style={`top:${tick.y}%`}
                                            >
                                                {toCurrency(tick.value)}
                                            </div>
                                        {/each}
                                    </div>

                                    <div
                                        class="absolute top-14 right-1 bottom-6 left-[62px] overflow-visible md:top-16 md:left-[100px]"
                                    >
                                        <svg
                                            viewBox="0 0 100 100"
                                            preserveAspectRatio="none"
                                            class="h-full w-full overflow-visible"
                                        >
                                            {#each lineChartTicks as tick (`trend-grid-${tick.y}`)}
                                                <line
                                                    x1="0"
                                                    y1={tick.y}
                                                    x2="100"
                                                    y2={tick.y}
                                                    stroke="currentColor"
                                                    class="text-slate-200/90 dark:text-slate-800"
                                                    stroke-dasharray="2 3"
                                                ></line>
                                            {/each}
                                            {#if lineAreaPath}
                                                <path
                                                    d={lineAreaPath}
                                                    fill="url(#dailyRevenueAreaCompact)"
                                                ></path>
                                                <defs>
                                                    <linearGradient
                                                        id="dailyRevenueAreaCompact"
                                                        x1="0"
                                                        x2="0"
                                                        y1="0"
                                                        y2="1"
                                                    >
                                                        <stop
                                                            offset="0%"
                                                            stop-color="rgb(14 165 233 / 0.32)"
                                                        ></stop>
                                                        <stop
                                                            offset="100%"
                                                            stop-color="rgb(14 165 233 / 0.02)"
                                                        ></stop>
                                                    </linearGradient>
                                                </defs>
                                            {/if}
                                            {#if linePath}
                                                <path
                                                    d={linePath}
                                                    fill="none"
                                                    stroke="rgb(37 99 235)"
                                                    stroke-width="2.1"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                ></path>
                                            {/if}
                                        </svg>

                                        {#each lineChartPoints as point, index (point.key)}
                                            <button
                                                type="button"
                                                class="absolute flex h-7 w-7 -translate-x-1/2 -translate-y-1/2 touch-manipulation items-center justify-center rounded-full bg-transparent outline-hidden transition hover:scale-105 focus-visible:ring-2 focus-visible:ring-blue-500/45"
                                                style={`left:${point.x}%; top:${point.y}%`}
                                                onclick={() =>
                                                    togglePinnedTrendPoint(
                                                        point.row,
                                                    )}
                                                onmouseenter={() =>
                                                    hoverTrendPoint(point.row)}
                                                onfocus={() =>
                                                    hoverTrendPoint(point.row)}
                                                onmouseleave={() =>
                                                    hoverTrendPoint(null)}
                                                onblur={() =>
                                                    hoverTrendPoint(null)}
                                                aria-label={`${trendLabel(point.row)} ${toCurrency(point.row.revenue)}`}
                                                aria-pressed={activeTrendTooltipKey ===
                                                    trendKey(point.row)}
                                            >
                                                <span
                                                    class={`block h-3.5 w-3.5 rounded-full border-2 border-white bg-blue-500 shadow-sm dark:border-slate-950 ${trendSelectedClass(point.row)}`}
                                                ></span>
                                                <span class="sr-only"
                                                    >{trendLabel(
                                                        point.row,
                                                    )}</span
                                                >
                                            </button>

                                            {#if showLineAxisLabel(index)}
                                                <div
                                                    class="absolute top-[calc(100%+0.35rem)] max-w-12 -translate-x-1/2 truncate text-center text-[9px] font-medium text-slate-500 dark:text-slate-400 md:max-w-14 md:text-[10px]"
                                                    style={`left:${point.x}%`}
                                                >
                                                    {point.row.label}
                                                </div>
                                            {/if}
                                        {/each}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="h-fit overflow-visible border border-slate-200/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(248,250,252,0.96))] shadow-sm dark:border-slate-700/70 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.98),rgba(15,23,42,0.96))]"
                >
                    <CardHeader class="space-y-1 pb-2 sm:pb-1">
                        <div
                            class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
                        >
                            <div class="min-w-0">
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400"
                                >
                                    Bulan Berjalan
                                </p>
                                <CardTitle
                                    class="mt-1 text-xl font-semibold tracking-tight text-slate-950 dark:text-slate-50 sm:text-[1.65rem]"
                                    >Transaction Revenue</CardTitle
                                >
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    <span class="md:hidden"
                                        >{heatmapMonthLabel}</span
                                    >
                                    <span class="hidden md:inline"
                                        >Tahun {heatmapYearLabel}</span
                                    >
                                </p>
                            </div>
                            <div
                                class="flex w-full flex-wrap items-stretch gap-2 sm:items-center md:w-auto md:justify-end"
                            >
                                <div
                                    class="min-w-0 flex-1 rounded-2xl border border-slate-200/80 bg-background/82 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/60 sm:flex-none"
                                >
                                    <p
                                        class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                                    >
                                        <span class="md:hidden"
                                            >Total Bulan Ini</span
                                        >
                                        <span class="hidden md:inline"
                                            >Total Tahun Ini</span
                                        >
                                    </p>
                                    <p
                                        class="mt-1 break-words text-sm font-semibold text-slate-950 dark:text-slate-50"
                                    >
                                        <span class="md:hidden">
                                            {toCurrency(
                                                currentMonthRevenueTotal,
                                            )}
                                        </span>
                                        <span class="hidden md:inline">
                                            {toCurrency(
                                                stats.revenue_total_year,
                                            )}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="overflow-visible pt-0 pb-3">
                        <div
                            class="overflow-visible rounded-2xl border border-slate-200/80 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(255,255,255,0.92))] p-2.5 shadow-inner dark:border-slate-800 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.84),rgba(15,23,42,0.7))] sm:rounded-[24px] sm:p-3 md:p-4"
                        >
                            <div
                                class="mb-2 flex items-center justify-between gap-2 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400"
                            >
                                <span class="md:hidden">Kalender bulan ini</span
                                >
                                <span class="hidden md:inline"
                                    >Kalender tahun berjalan</span
                                >
                                <div class="hidden items-center gap-1 md:flex">
                                    <span>Low</span>
                                    {#each ['bg-slate-100 dark:bg-slate-800/90', 'bg-cyan-100 dark:bg-cyan-500/20', 'bg-sky-200 dark:bg-sky-500/30', 'bg-sky-400 dark:bg-sky-500', 'bg-sky-600'] as tone (`heatmap-legend-compact-${tone}`)}
                                        <span
                                            class={`h-3 w-3 rounded-[4px] ${tone}`}
                                        ></span>
                                    {/each}
                                    <span>High</span>
                                </div>
                            </div>

                            <div
                                class="w-full overflow-visible pb-2 md:overflow-x-auto md:overscroll-x-contain md:[scrollbar-width:thin]"
                                role="region"
                                aria-label="Grafik transaksi revenue"
                            >
                                <div
                                    class="relative pt-12 md:min-w-[760px] md:pt-14"
                                    role="presentation"
                                    onmouseleave={() => hoverHeatmapDay(null)}
                                >
                                    {#if activeHeatmapTooltip}
                                        {#key activeHeatmapTooltipKey}
                                            <div
                                                class={`pointer-events-none absolute top-0 z-10 w-[240px] max-w-[calc(100vw-2rem)] rounded-xl bg-slate-900/96 px-3 py-2 text-white shadow-2xl transition sm:rounded-2xl ${tooltipTranslateClass(activeHeatmapTooltip.align)}`}
                                                style={`left:${activeHeatmapTooltip.left}%`}
                                            >
                                                <p
                                                    class="text-sm font-semibold"
                                                >
                                                    {activeHeatmapTooltip.row
                                                        .name ??
                                                        activeHeatmapTooltip.row
                                                            .date}
                                                </p>
                                                <div
                                                    class="mt-2 space-y-1 text-[12px]"
                                                >
                                                    <div
                                                        class="flex items-center justify-between gap-3"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Keberangkatan</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeHeatmapTooltip
                                                                    .row
                                                                    .booking_revenue ||
                                                                    0,
                                                            )}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex items-center justify-between gap-3"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Bagasi</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeHeatmapTooltip
                                                                    .row
                                                                    .luggage_revenue ||
                                                                    0,
                                                            )}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex items-center justify-between gap-3"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Carter</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeHeatmapTooltip
                                                                    .row
                                                                    .charter_revenue ||
                                                                    0,
                                                            )}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex items-center justify-between gap-3 border-t border-white/12 pt-1"
                                                    >
                                                        <span
                                                            class="text-white/68"
                                                            >Total</span
                                                        >
                                                        <span
                                                            class="font-semibold"
                                                            >{toCurrency(
                                                                activeHeatmapTooltip
                                                                    .row
                                                                    .revenue,
                                                            )}</span
                                                        >
                                                    </div>
                                                </div>
                                                <span
                                                    class={`absolute top-full h-3 w-3 -translate-y-1/2 rotate-45 bg-slate-900/96 ${tooltipArrowClass(activeHeatmapTooltip.align)}`}
                                                ></span>
                                            </div>
                                        {/key}
                                    {/if}

                                    <div
                                        class="absolute inset-x-0 top-0 hidden h-5 md:block"
                                    >
                                        {#each heatmapMonthMarkers as marker (marker.key)}
                                            <span
                                                class={`absolute top-0 text-[11px] font-medium text-slate-500 dark:text-slate-400 ${tooltipTranslateClass(tooltipAlignFor(marker.left))}`}
                                                style={`left:${marker.left}%`}
                                            >
                                                {marker.label}
                                            </span>
                                        {/each}
                                    </div>

                                    <div
                                        class="grid grid-flow-col auto-cols-fr grid-rows-7 gap-[4px] pt-2 md:hidden"
                                    >
                                        {#each mobileHeatmapContributionCells as cell (cell.key)}
                                            {#if cell.item}
                                                {@const day = cell.item}
                                                <button
                                                    type="button"
                                                    class={`aspect-square min-h-0 w-full touch-manipulation rounded-[4px] outline-hidden transition hover:scale-105 focus-visible:ring-2 focus-visible:ring-sky-500/45 ${heatmapToneClass(day, maxCurrentMonthHeatmapRevenue)} ${heatmapSelectedClass(day)}`}
                                                    onclick={() =>
                                                        togglePinnedHeatmapDay(
                                                            day,
                                                        )}
                                                    onmouseenter={() =>
                                                        hoverHeatmapDay(day)}
                                                    onfocus={() =>
                                                        hoverHeatmapDay(day)}
                                                    onmouseleave={() =>
                                                        hoverHeatmapDay(null)}
                                                    onblur={() =>
                                                        hoverHeatmapDay(null)}
                                                    aria-label={`${day.name ?? day.date} ${toCurrency(day.revenue)}`}
                                                    aria-pressed={activeHeatmapTooltipKey ===
                                                        heatmapKey(day)}
                                                    disabled={day.is_future}
                                                ></button>
                                            {:else}
                                                <div
                                                    class="aspect-square w-full rounded-[4px] bg-transparent"
                                                ></div>
                                            {/if}
                                        {/each}
                                    </div>

                                    <div
                                        class="hidden grid-flow-col auto-cols-[14px] grid-rows-7 gap-[4px] pt-2 md:grid"
                                    >
                                        {#each heatmapContributionCells as cell (cell.key)}
                                            {#if cell.item}
                                                {@const day = cell.item}
                                                <button
                                                    type="button"
                                                    class={`h-3.5 w-3.5 touch-manipulation rounded-[4px] outline-hidden transition hover:scale-110 focus-visible:ring-2 focus-visible:ring-sky-500/45 ${heatmapToneClass(day, maxYearlyHeatmapRevenue)} ${heatmapSelectedClass(day)}`}
                                                    onclick={() =>
                                                        togglePinnedHeatmapDay(
                                                            day,
                                                        )}
                                                    onmouseenter={() =>
                                                        hoverHeatmapDay(day)}
                                                    onfocus={() =>
                                                        hoverHeatmapDay(day)}
                                                    onmouseleave={() =>
                                                        hoverHeatmapDay(null)}
                                                    onblur={() =>
                                                        hoverHeatmapDay(null)}
                                                    aria-label={`${day.name ?? day.date} ${toCurrency(day.revenue)}`}
                                                    aria-pressed={activeHeatmapTooltipKey ===
                                                        heatmapKey(day)}
                                                    disabled={day.is_future}
                                                ></button>
                                            {:else}
                                                <div
                                                    class="h-3.5 w-3.5 rounded-[4px] bg-transparent"
                                                ></div>
                                            {/if}
                                        {/each}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 grid gap-2 md:hidden">
                                {#if visibleRevenueDays.length === 0}
                                    <div
                                        class="rounded-xl border border-dashed border-slate-300 p-3 text-xs text-muted-foreground dark:border-slate-700"
                                    >
                                        Belum ada transaksi bulan berjalan.
                                    </div>
                                {:else}
                                    {#each visibleRevenueDays
                                        .slice(-8)
                                        .reverse() as day (`mobile-revenue-day-${day.date}`)}
                                        <button
                                            type="button"
                                            class={`rounded-xl border p-3 text-left transition ${
                                                activeHeatmapTooltipKey ===
                                                heatmapKey(day)
                                                    ? 'border-sky-400 bg-sky-50 dark:border-sky-500/50 dark:bg-sky-950/30'
                                                    : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950'
                                            }`}
                                            onclick={() =>
                                                togglePinnedHeatmapDay(day)}
                                        >
                                            <div
                                                class="flex items-start justify-between gap-3"
                                            >
                                                <span
                                                    class="text-xs font-semibold text-slate-900 dark:text-slate-50"
                                                    >{day.name ??
                                                        day.date}</span
                                                >
                                                <span
                                                    class="break-words text-right text-xs font-semibold text-slate-900 dark:text-slate-50"
                                                    >{toCurrency(
                                                        day.revenue,
                                                    )}</span
                                                >
                                            </div>
                                            <div
                                                class="mt-2 grid grid-cols-1 gap-1 text-[10px] text-slate-500 dark:text-slate-400 sm:grid-cols-3"
                                            >
                                                <span
                                                    >Booking {toCurrency(
                                                        day.booking_revenue ||
                                                            0,
                                                    )}</span
                                                >
                                                <span
                                                    >Bagasi {toCurrency(
                                                        day.luggage_revenue ||
                                                            0,
                                                    )}</span
                                                >
                                                <span
                                                    >Carter {toCurrency(
                                                        day.charter_revenue ||
                                                            0,
                                                    )}</span
                                                >
                                            </div>
                                        </button>
                                    {/each}
                                {/if}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-2.5 xl:col-span-1">
                <Card class="overflow-hidden">
                    <CardHeader class="space-y-1 pb-2">
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="min-w-0">
                                <CardTitle class="text-sm md:text-base"
                                    >Info Carter 7 Hari Kedepan</CardTitle
                                >
                                <CardDescription class="text-xs"
                                    >Reminder charter terdekat, diurutkan dari
                                    tanggal paling dekat</CardDescription
                                >
                            </div>
                            {#if upcomingCharterReminder.total > 0}
                                <Badge
                                    variant="secondary"
                                    class="w-fit shrink-0"
                                    >{upcomingCharterReminder.total} data</Badge
                                >
                            {/if}
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-2.5 pt-0">
                        {#if upcomingCharterReminder.items.length === 0}
                            <div
                                class="rounded-md border border-dashed p-3 text-xs text-muted-foreground"
                            >
                                Belum ada data carter untuk 7 hari ke depan.
                            </div>
                        {:else}
                            {#each upcomingCharterReminder.items as item (`upcoming-charter-${item.id}`)}
                                <a
                                    href={`/charters/view/${item.id}`}
                                    class="block rounded-xl border border-border/70 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(236,254,255,0.82))] p-3 transition hover:border-cyan-300/70 hover:shadow-sm dark:bg-[linear-gradient(180deg,rgba(15,23,42,0.95),rgba(8,47,73,0.65))]"
                                >
                                    <div
                                        class="mb-2 flex items-start justify-between gap-2"
                                    >
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-foreground"
                                            >
                                                {item.name}
                                            </p>
                                            <p
                                                class="truncate text-[11px] text-muted-foreground"
                                            >
                                                {item.company_name ||
                                                    item.phone ||
                                                    'Customer charter'}
                                            </p>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-semibold text-cyan-700"
                                        >
                                            {charterReminderTag(item)}
                                        </span>
                                    </div>
                                    <div
                                        class="space-y-1 text-[11px] text-muted-foreground"
                                    >
                                        <p class="font-medium text-foreground">
                                            {item.date_label}
                                        </p>
                                        <p>
                                            {item.departure_time || '--:--'} â€¢ {item.layanan ||
                                                '-'}
                                        </p>
                                        <p class="truncate">
                                            Rute: {item.pickup_point || '-'} â†’ {item.drop_point ||
                                                '-'}
                                        </p>
                                        <p class="truncate">
                                            Driver: {item.driver_name || '-'}
                                        </p>
                                    </div>
                                </a>
                            {/each}
                            {#if upcomingCharterOverflow > 0}
                                <a
                                    href="/charters"
                                    class="flex flex-col gap-1 rounded-xl border border-dashed border-cyan-300/70 bg-cyan-50/70 px-3 py-2 text-xs font-medium text-cyan-800 transition hover:bg-cyan-100/80 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <span
                                        >Lihat {upcomingCharterOverflow} reminder
                                        lainnya</span
                                    >
                                    <span>Ke menu Carter</span>
                                </a>
                            {/if}
                        {/if}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="space-y-1 pb-2">
                        <CardTitle class="text-sm md:text-base"
                            >Keberangkatan Hari Ini</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >Data jadwal dari booking aktif hari ini</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-2.5 pt-0">
                        {#if copyMessage}
                            <p class="text-xs text-emerald-600">
                                {copyMessage}
                            </p>
                        {/if}
                        {#if copyError}
                            <p class="text-xs text-destructive">{copyError}</p>
                        {/if}
                        {#if departuresToday.length === 0}
                            <p class="text-xs text-muted-foreground">
                                Belum ada data keberangkatan hari ini.
                            </p>
                        {:else}
                            {#each departuresToday as item, idx (`departure-${idx}-${item.rute}-${item.jam}-${item.unit}`)}
                                <div class="rounded-md border p-2.5">
                                    <div
                                        class="mb-1 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <p
                                            class="text-xs font-semibold md:text-sm"
                                        >
                                            {item.jam} - Unit {item.unit}
                                        </p>
                                        <Badge
                                            variant="secondary"
                                            class="w-fit shrink-0"
                                            >{item.total_bookings} booking</Badge
                                        >
                                    </div>
                                    <div
                                        class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                                    >
                                        <div class="min-w-0 space-y-0.5">
                                            <p
                                                class="truncate text-xs text-muted-foreground"
                                            >
                                                {item.rute}
                                            </p>
                                            <p
                                                class="truncate text-[11px] text-muted-foreground"
                                            >
                                                Driver: {item.driver_name ||
                                                    '-'}
                                            </p>
                                        </div>
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            class="h-8 w-full rounded-md px-2 text-[11px] sm:h-7 sm:w-auto"
                                            onclick={() =>
                                                void copyDepartureData(item)}
                                            disabled={copyingDepartureKey ===
                                                departureCopyKey(item)}
                                        >
                                            <Copy class="mr-1 h-3 w-3" />
                                            {copyingDepartureKey ===
                                            departureCopyKey(item)
                                                ? 'Menyalin...'
                                                : 'Copy Data'}
                                        </Button>
                                    </div>
                                </div>
                            {/each}
                        {/if}
                    </CardContent>
                </Card>

                <Card class="hidden h-fit xl:block">
                    <CardHeader class="space-y-1 pb-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <CardTitle class="text-sm md:text-base"
                                    >Aktivitas Terbaru</CardTitle
                                >
                                <CardDescription class="text-xs"
                                    >Update terbaru dari sistem</CardDescription
                                >
                            </div>
                            {#if recentActivityTotal > 0}
                                <Badge variant="secondary" class="shrink-0"
                                    >{recentActivityTotal} log</Badge
                                >
                            {/if}
                        </div>
                    </CardHeader>
                    <CardContent class="pt-0">
                        {#if recentActivity.length === 0}
                            <p class="text-xs text-muted-foreground">
                                Belum ada aktivitas.
                            </p>
                        {:else}
                            <div
                                class="divide-y divide-border rounded-xl border border-border/70 bg-background/70"
                            >
                                {#each recentActivity as item, idx (`activity-desktop-${idx}-${item.tag}`)}
                                    <div
                                        class="flex items-start justify-between gap-3 px-3 py-2.5"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="mb-1 flex items-center gap-2"
                                            >
                                                <Badge variant="secondary"
                                                    >{item.tag}</Badge
                                                >
                                            </div>
                                            <p
                                                class="truncate text-sm font-semibold leading-snug text-foreground"
                                            >
                                                {item.title}
                                            </p>
                                            <p
                                                class="truncate text-xs leading-relaxed text-muted-foreground"
                                            >
                                                {item.meta}
                                            </p>
                                        </div>
                                        <span
                                            class="shrink-0 text-[11px] text-muted-foreground"
                                            >{item.time}</span
                                        >
                                    </div>
                                {/each}
                            </div>
                            {#if recentActivityOverflow > 0}
                                <a
                                    href="/admin-ops/cancellations"
                                    class="mt-2.5 flex items-center justify-between rounded-xl border border-dashed border-border/70 bg-muted/25 px-3 py-2 text-xs font-medium text-foreground transition hover:bg-muted/40"
                                >
                                    <span
                                        >Lihat {recentActivityOverflow} aktivitas
                                        lainnya</span
                                    >
                                    <span>Ke menu Log</span>
                                </a>
                            {/if}
                        {/if}
                    </CardContent>
                </Card>
            </div>

            <Card class="order-last h-fit xl:hidden">
                <CardHeader class="space-y-1 pb-2">
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="min-w-0">
                            <CardTitle class="text-sm md:text-base"
                                >Aktivitas Terbaru</CardTitle
                            >
                            <CardDescription class="text-xs"
                                >Update terbaru dari sistem</CardDescription
                            >
                        </div>
                        {#if recentActivityTotal > 0}
                            <Badge variant="secondary" class="w-fit shrink-0"
                                >{recentActivityTotal} log</Badge
                            >
                        {/if}
                    </div>
                </CardHeader>
                <CardContent class="space-y-2.5 pt-0">
                    {#if recentActivity.length === 0}
                        <p class="text-xs text-muted-foreground">
                            Belum ada aktivitas.
                        </p>
                    {:else}
                        {#each recentActivity as item, idx (`activity-mobile-${idx}-${item.tag}`)}
                            <div class="rounded-md border p-2.5">
                                <div
                                    class="mb-1 flex items-start justify-between gap-2"
                                >
                                    <Badge
                                        variant="secondary"
                                        class="max-w-[70%] truncate"
                                        >{item.tag}</Badge
                                    >
                                    <span
                                        class="shrink-0 text-xs text-muted-foreground"
                                        >{item.time}</span
                                    >
                                </div>
                                <p
                                    class="break-words text-xs font-semibold md:text-sm"
                                >
                                    {item.title}
                                </p>
                                <p
                                    class="break-words text-xs text-muted-foreground"
                                >
                                    {item.meta}
                                </p>
                            </div>
                        {/each}
                        {#if recentActivityOverflow > 0}
                            <a
                                href="/admin-ops/cancellations"
                                class="flex flex-col gap-1 rounded-xl border border-dashed border-border/70 bg-muted/25 px-3 py-2 text-xs font-medium text-foreground transition hover:bg-muted/40 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <span
                                    >Lihat {recentActivityOverflow} aktivitas lainnya</span
                                >
                                <span>Ke menu Log</span>
                            </a>
                        {/if}
                    {/if}
                </CardContent>
            </Card>
        </div>
    </Deferred>
</div>
