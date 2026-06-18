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
                    Operasional Hari Ini
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
                    Fokus: jadwal, armada, carter, dan revenue harian
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
                                {toCurrency(stats.revenue_total_today)}
                            </h3>
                            <p class="mt-1 text-xs text-slate-200/80">
                                Revenue hari ini
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
                                >{summaryPeriodByScope.month.current_label}</span
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
                                Prioritas Hari Ini
                            </p>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Hal yang perlu dipantau sebelum membuka menu detail.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <a
                                href="/bookings"
                                class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Keberangkatan
                                </p>
                                <p
                                    class="mt-1 break-words text-sm font-semibold text-slate-900 dark:text-slate-50"
                                >
                                    {departuresToday.length} jadwal
                                </p>
                                <p
                                    class="mt-1 text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    {departuresTodayTotalBookings} booking aktif
                                </p>
                            </a>
                            <a
                                href="/admin-ops/armadas"
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
                            </a>
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
                                class="flex items-start justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400"
                                    >
                                        Carter Terdekat
                                    </p>
                                    <p
                                        class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-slate-50"
                                    >
                                        {nextCharter?.name ?? 'Belum ada carter'}
                                    </p>
                                    <p
                                        class="mt-1 truncate text-[11px] text-slate-500 dark:text-slate-400"
                                    >
                                        {nextCharter?.date_label ??
                                            '7 hari ke depan kosong'}
                                    </p>
                                </div>
                                {#if upcomingCharterReminder.total > 0}
                                    <span
                                        class="shrink-0 rounded-full bg-cyan-100 px-2 py-1 text-[11px] font-semibold text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-200"
                                    >
                                        {upcomingCharterReminder.total} data
                                    </span>
                                {/if}
                            </div>
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
                                    30 Hari Terakhir
                                </p>
                                <CardTitle
                                    class="mt-1 text-xl font-semibold tracking-tight text-slate-950 dark:text-slate-50 sm:text-[1.65rem]"
                                    >Revenue 30 Hari Terakhir</CardTitle
                                >
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Tren pendapatan harian tanpa rekap tahunan.
                                </p>
                            </div>
                            <div
                                class="flex w-full flex-wrap items-center gap-2 sm:items-center lg:w-auto lg:justify-end"
                            >
                                <div
                                    class="min-w-0 flex-1 rounded-2xl border border-slate-200/80 bg-background/82 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/60 sm:flex-none"
                                >
                                    <p
                                        class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                                    >
                                        Total 30 Hari
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
                                    >Revenue harian</span
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
                    class="h-fit overflow-hidden border border-slate-200/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(248,250,252,0.96))] shadow-sm dark:border-slate-700/70 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.98),rgba(15,23,42,0.96))]"
                >
                    <CardHeader class="space-y-1 pb-2 sm:pb-1">
                        <div
                            class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
                        >
                            <div class="min-w-0">
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400"
                                >
                                    Tahun Berjalan
                                </p>
                                <CardTitle
                                    class="mt-1 text-xl font-semibold tracking-tight text-slate-950 dark:text-slate-50 sm:text-[1.65rem]"
                                    >Aktivitas Operasional</CardTitle
                                >
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Volume aktivitas bulanan selama tahun ini.
                                </p>
                            </div>
                            <div
                                class="min-w-0 rounded-2xl border border-slate-200/80 bg-background/82 px-3 py-2 dark:border-slate-800 dark:bg-slate-950/60"
                            >
                                <p
                                    class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                                >
                                    Hari Ini
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-slate-950 dark:text-slate-50"
                                >
                                    {departuresToday.length} jadwal / {departuresTodayTotalBookings}
                                    booking
                                </p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-0 pb-3">
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-[linear-gradient(180deg,rgba(248,250,252,0.96),rgba(255,255,255,0.92))] p-3 dark:border-slate-800 dark:bg-[linear-gradient(180deg,rgba(2,6,23,0.84),rgba(15,23,42,0.7))] md:p-4"
                        >
                            <div
                                class="mb-3 flex items-center justify-between gap-2 text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400"
                            >
                                <span>Aktivitas bulanan</span>
                                <span class="normal-case tracking-normal"
                                    >{operationalTrendRows.length} bulan</span
                                >
                            </div>

                            <div
                                class="grid h-[190px] grid-flow-col auto-cols-fr items-end gap-1.5 md:h-[220px] md:gap-2"
                                role="img"
                                aria-label="Grafik aktivitas operasional tahun berjalan"
                            >
                                {#each operationalTrendRows as row, index (row.key)}
                                    {@const height = Math.max(
                                        row.value > 0
                                            ? Math.round(
                                                  (row.value /
                                                      maxOperationalActivity) *
                                                      100,
                                              )
                                            : 4,
                                        4,
                                    )}
                                    <div
                                        class="flex h-full min-w-0 flex-col justify-end gap-1"
                                    >
                                        <div
                                            class="group relative flex flex-1 items-end"
                                            title={`${row.name}: ${row.value} aktivitas`}
                                        >
                                            <div
                                                class={`w-full rounded-t-lg transition group-hover:bg-cyan-500 ${
                                                    row.value > 0
                                                        ? 'bg-cyan-400 dark:bg-cyan-500'
                                                        : 'bg-slate-200 dark:bg-slate-800'
                                                }`}
                                                style={`height:${height}%`}
                                            ></div>
                                        </div>
                                        {#if index === 0 ||
                                            index ===
                                                operationalTrendRows.length -
                                                    1 ||
                                            index % 2 === 0}
                                            <span
                                                class="truncate text-center text-[9px] font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                {row.label}
                                            </span>
                                        {:else}
                                            <span class="h-[13px]"></span>
                                        {/if}
                                    </div>
                                {/each}
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
