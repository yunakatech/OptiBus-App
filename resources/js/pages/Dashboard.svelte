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
    import { Copy } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import CommandCenter from '@/components/dashboard/CommandCenter.svelte';
    import RevenueActivityChart from '@/components/dashboard/RevenueActivityChart.svelte';
    import RevenueChannelPieChart from '@/components/dashboard/RevenueChannelPieChart.svelte';
    import DriverPerformanceCard from '@/components/dashboard/DriverPerformanceCard.svelte';
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
        total_passengers: number;
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
        recentActivity = [],
        departuresToday = [],
        upcomingCharterReminder = { total: 0, visible_count: 0, items: [] },
        recentActivityTotal = 0,
        recentActivityVisibleCount = 0,
        topDrivers = { 'Minibus': [], 'Mediumbus': [], 'Bigbus': [] } as Record<string, any[]>,
        summaryStatsByScope = {
            day: {
                total_bookings: 0,
                total_passengers: 0,
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
                total_passengers: 0,
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
                total_passengers: 0,
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
                total_passengers: 0,
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
                total_passengers: 0,
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
                total_passengers: 0,
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
        recentActivity?: ActivityItem[];
        departuresToday?: DepartureItem[];
        upcomingCharterReminder?: UpcomingCharterReminder;
        recentActivityTotal?: number;
        recentActivityVisibleCount?: number;
        topDrivers?: Record<string, any[]>;
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

    const activeTrendRows = $derived(dailyTrend);
    const operationalTrendSourceRows = $derived(monthlyTrend);
    const maxTrendRevenue = $derived(
        Math.max(
            0,
            ...activeTrendRows.map((item) => Number(item.revenue || 0)),
        ),
    );
    const lineScaleMax = $derived(maxTrendRevenue > 0 ? maxTrendRevenue : 1);
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
        activeTrendRows.reduce(
            (total, item) => total + Number(item.revenue || 0),
            0,
        ),
    );
    const departuresTodayTotalBookings = $derived(
        departuresToday.reduce(
            (total, item) => total + Number(item.total_bookings || 0),
            0,
        ),
    );
    const nextCharter = $derived(upcomingCharterReminder.items[0] ?? null);
    const hasTrendTransactionCounts = $derived(
        operationalTrendSourceRows.some(
            (item) => Number(item.transaction_count || 0) > 0,
        ),
    );
    const operationalTrendRows = $derived(
        operationalTrendSourceRows.map((item, index) => ({
            key: `ops-${item.date ?? item.label}-${index}`,
            label: item.label,
            name: item.name ?? item.month_key ?? item.label,
            value: hasTrendTransactionCounts
                ? Number(item.transaction_count || 0)
                : index === operationalTrendSourceRows.length - 1
                  ? Math.max(
                        departuresToday.length,
                        departuresTodayTotalBookings,
                    )
                  : 0,
        })),
    );
    const maxOperationalActivity = $derived(
        Math.max(1, ...operationalTrendRows.map((item) => item.value)),
    );

    const trendKey = (row: TrendItem) =>
        `trend-${row.month_key ?? row.label}-${row.name ?? row.date ?? ''}`;

    let hoveredTrendKey = $state<string>('');
    let pinnedTrendKey = $state<string>('');

    const activeTrendTooltipKey = $derived(hoveredTrendKey || pinnedTrendKey);
    const activeTrendDataSignature = $derived(
        `${selectedPoolId}|daily|${activeTrendRows
            .map(
                (row) =>
                    `${trendKey(row)}:${Number(row.revenue || 0)}:${Number(row.booking_revenue || 0)}:${Number(row.luggage_revenue || 0)}:${Number(row.charter_revenue || 0)}`,
            )
            .join('|')}`,
    );
    const activeSummaryStats = $derived(summaryStatsByScope.month);
    const activeSummaryPeriod = $derived(
        summaryPeriodByScope.month,
    );
    const activeTotalRevenue = $derived(
        Number(activeSummaryStats.revenue_booking || 0) +
            Number(activeSummaryStats.revenue_charter || 0) +
            Number(activeSummaryStats.revenue_luggage || 0),
    );
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
    $effect(() => {
        activeTrendDataSignature;
        hoveredTrendKey = '';
        pinnedTrendKey = '';
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
    const trendSelectedClass = (row: TrendItem) =>
        activeTrendTooltipKey === trendKey(row) ? 'ring-4 ring-sky-500/20' : '';
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

        <!-- Command Center & Prioritas Grid -->
        <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
            <!-- Revenue Activity Chart -->
            <RevenueActivityChart
                monthlyTrend={dailyTrend}
                {toCurrency}
                title="Revenue 30 Hari Terakhir"
                subtitle="Aktivitas Harian"
                description="Grafik spline ini menampilkan pergerakan revenue operasional sistem Anda dalam 30 hari ke belakang secara presisi."
                currentMonthLabel="Total 30 Hari"
                currentMonthRevenue={activeTrendRevenueTotal}
            />

            <!-- Prioritas Hari Ini replaced with RevenueChannelPieChart -->
            <RevenueChannelPieChart summaryStatsByScope={summaryStatsByScope} {toCurrency} />
        </div>
    </div>

    <Deferred
        data={[
            'dailyTrend',
            'monthlyTrend',
            'recentActivity',
            'recentActivityTotal',
            'recentActivityVisibleCount',
            'departuresToday',
            'upcomingCharterReminder',
            'topDrivers',
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
                <!-- Command Center -->
                <CommandCenter
                    monthlyTrend={monthlyTrend}
                    {toCurrency}
                />

                <!-- Driver Performance Card -->
                <DriverPerformanceCard
                    categories={topDrivers}
                    {toCurrency}
                    period="Bulan Ini"
                />
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
                                            {item.departure_time || '--:--'} • {item.layanan ||
                                                '-'}
                                        </p>
                                        <p class="truncate">
                                            Rute: {item.pickup_point || '-'} → {item.drop_point ||
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
