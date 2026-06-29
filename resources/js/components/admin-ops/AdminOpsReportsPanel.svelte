<script lang="ts">
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';

    type ReportKind = 'booking' | 'charter' | 'bagasi';
    type ReportSummary = {
        from: string;
        to: string;
        type: ReportKind;
        total_rows: number;
        revenue_total: number;
        bop_total?: number;
        margin_total?: number;
        achievement_percent?: number;
        pool_id?: number;
        pool_name?: string;
        target_revenue?: number;
    };
    type PoolOption = {
        id: number;
        name: string;
        code: string;
        target_revenue: number;
        status: string;
    };
    type Pagination = {
        page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
    type BookingReportRow = {
        id: number;
        tanggal: string;
        jam: string;
        name: string;
        phone: string;
        rute: string;
        pickup_point: string;
        unit: string;
        seat: string;
        pembayaran: string;
        status: string;
        discount: number;
        total: number;
    };
    type CharterReportRow = {
        id: number;
        start_date: string;
        end_date: string;
        departure_time: string;
        name: string;
        phone: string;
        pickup_point: string;
        drop_point: string;
        driver_name: string;
        layanan: string;
        payment_status: string;
        bop_status: string;
        status: string;
        unit_nopol: string;
        armada_nopol: string;
        total: number;
    };
    type LuggageReportRow = {
        id: number;
        tanggal: string;
        created_at: string;
        kode_resi: string;
        sender_name: string;
        sender_phone: string;
        receiver_name: string;
        receiver_phone: string;
        quantity: number;
        payment_status: string;
        status: string;
        service_name: string;
        total: number;
    };
    type ReportRow = BookingReportRow | CharterReportRow | LuggageReportRow;

    const reportTypeMeta: Record<
        ReportKind,
        {
            label: string;
            dataLabel: string;
            itemLabel: string;
            intro: string;
            tone: string;
            subtleTone: string;
        }
    > = {
        booking: {
            label: 'Booking',
            dataLabel: 'transaksi',
            itemLabel: 'transaksi',
            intro: 'Pantau tiket reguler, titik jemput, dan nilai bersih setiap penumpang dalam satu alur yang lebih rapi.',
            tone: 'border-sky-200/80 bg-sky-50/80 text-sky-700',
            subtleTone:
                'border-sky-200/70 bg-[linear-gradient(135deg,rgba(14,165,233,0.10),rgba(15,23,42,0.02))]',
        },
        charter: {
            label: 'Carter',
            dataLabel: 'perjalanan',
            itemLabel: 'perjalanan',
            intro: 'Lihat performa perjalanan carter, armada, dan status pembayaran tanpa perlu pindah ke panel lain.',
            tone: 'border-emerald-200/80 bg-emerald-50/80 text-emerald-700',
            subtleTone:
                'border-emerald-200/70 bg-[linear-gradient(135deg,rgba(16,185,129,0.10),rgba(15,23,42,0.02))]',
        },
        bagasi: {
            label: 'Bagasi',
            dataLabel: 'resi',
            itemLabel: 'resi',
            intro: 'Ringkas pemasukan bagasi beserta pengirim, penerima, dan progres pengiriman dalam tampilan yang cepat dibaca.',
            tone: 'border-amber-200/80 bg-amber-50/85 text-amber-700',
            subtleTone:
                'border-amber-200/70 bg-[linear-gradient(135deg,rgba(245,158,11,0.11),rgba(15,23,42,0.02))]',
        },
    };

    const asBookingRows = (rows: ReportRow[]) => rows as BookingReportRow[];
    const asCharterRows = (rows: ReportRow[]) => rows as CharterReportRow[];
    const asLuggageRows = (rows: ReportRow[]) => rows as LuggageReportRow[];

    const shortTime = (value: string) => {
        const normalized = String(value ?? '').trim();

        return normalized === '' ? '-' : normalized.slice(0, 5);
    };

    const routeLabel = (pickupPoint: string, dropPoint: string) => {
        const pickup = String(pickupPoint ?? '').trim();
        const drop = String(dropPoint ?? '').trim();

        if (pickup === '' && drop === '') {
            return '-';
        }

        if (pickup === '') {
            return drop;
        }

        if (drop === '') {
            return pickup;
        }

        return `${pickup} - ${drop}`;
    };

    const charterVehicle = (row: CharterReportRow) => {
        const armada = String(row.armada_nopol ?? '').trim();
        const unit = String(row.unit_nopol ?? '').trim();

        return armada !== '' ? armada : unit !== '' ? unit : '-';
    };

    const badgeClass = (value: string) => {
        const normalized = String(value ?? '')
            .trim()
            .toLowerCase();

        if (normalized.includes('cancel')) {
            return 'border-rose-200 bg-rose-50 text-rose-700';
        }

        if (
            normalized.includes('lunas') ||
            normalized.includes('done') ||
            normalized.includes('arrived') ||
            normalized.includes('delivered') ||
            normalized.includes('tiba')
        ) {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700';
        }

        if (
            normalized.includes('pending') ||
            normalized.includes('belum') ||
            normalized.includes('received') ||
            normalized.includes('pickup') ||
            normalized.includes('diterima')
        ) {
            return 'border-amber-200 bg-amber-50 text-amber-700';
        }

        return 'border-slate-200 bg-slate-50 text-slate-700';
    };

    const exportHref = (
        type: ReportKind,
        from: string,
        to: string,
        poolId: number,
        routeId: number,
    ) => {
        const encodedFrom = encodeURIComponent(from);
        const encodedTo = encodeURIComponent(to);
        const poolSuffix =
            Number(poolId || 0) > 0
                ? `&pool_id=${encodeURIComponent(String(poolId))}`
                : '';
        const routeSuffix =
            Number(routeId || 0) > 0
                ? `&route_id=${encodeURIComponent(String(routeId))}`
                : '';

        if (type === 'booking') {
            return `/api/admin/reports/bookings-csv?from=${encodedFrom}&to=${encodedTo}${poolSuffix}${routeSuffix}`;
        }

        const revenueType = type === 'bagasi' ? 'bagasi' : 'charter';

        return `/api/admin/reports/revenue-csv?from=${encodedFrom}&to=${encodedTo}&type=${revenueType}${poolSuffix}${routeSuffix}`;
    };

    const exportLabel = (type: ReportKind) =>
        type === 'booking' ? 'Export Booking CSV' : 'Export Revenue CSV';

    const resolvedReportType = (
        summary: ReportSummary | null,
        fallback: ReportKind,
    ) => summary?.type ?? fallback;

    const resolvedMeta = (
        summary: ReportSummary | null,
        fallback: ReportKind,
    ) => reportTypeMeta[resolvedReportType(summary, fallback)];

    let {
        reportFrom = '',
        reportTo = '',
        reportType = $bindable<ReportKind>('booking'),
        reportSummary = null,
        reportRows = [],
        reportMeta = { page: 1, per_page: 50, total: 0, last_page: 1 },
        reportLoading = false,
        pools = [],
        reportPoolId = $bindable(0),
        reportRouteId = $bindable(0),
        routes = [] as Array<{
            id: number;
            name: string;
            origin: string | null;
            destination: string | null;
        }>,
        reportFromInput = $bindable<HTMLInputElement | null>(null),
        reportToInput = $bindable<HTMLInputElement | null>(null),
        formatCurrency,
        loadReport,
        jumpReportPage,
    }: {
        reportFrom?: string;
        reportTo?: string;
        reportType?: ReportKind;
        reportSummary?: ReportSummary | null;
        reportRows?: ReportRow[];
        reportMeta?: Pagination;
        reportLoading?: boolean;
        pools?: PoolOption[];
        reportPoolId?: number;
        reportRouteId?: number;
        routes?: Array<{
            id: number;
            name: string;
            origin: string | null;
            destination: string | null;
        }>;
        reportFromInput?: HTMLInputElement | null;
        reportToInput?: HTMLInputElement | null;
        formatCurrency: (value: number) => string;
        loadReport: (page?: number) => void | Promise<void>;
        jumpReportPage: (page: number) => void | Promise<void>;
    } = $props();

    let reportFiltersExpanded = $state(false);
</script>

<div
    class="overflow-hidden rounded-[28px] border border-border/70 bg-background/95 shadow-[0_18px_60px_-28px_rgba(15,23,42,0.28)]"
>
    <div class="relative border-b border-border/70">
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.92),transparent_34%),radial-gradient(circle_at_bottom_right,rgba(148,163,184,0.13),transparent_38%)]"
        ></div>
        <div class="relative space-y-5 px-5 py-5 md:px-6">
            <div
                class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between"
            >
                <div class="max-w-4xl space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            variant="secondary"
                            class="rounded-full px-3 py-1 text-[11px] uppercase tracking-[0.18em]"
                        >
                            Laporan Operasional
                        </Badge>
                        <span
                            class={`inline-flex rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] ${resolvedMeta(reportSummary, reportType).tone}`}
                        >
                            {resolvedMeta(reportSummary, reportType).label}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-xl font-semibold tracking-tight">
                            Pendapatan
                            {resolvedMeta(
                                reportSummary,
                                reportType,
                            ).label.toLowerCase()}
                            dalam satu panel yang lebih mudah dipantau
                        </h3>
                        <p class="text-sm leading-6 text-muted-foreground">
                            {resolvedMeta(reportSummary, reportType).intro}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a
                        href={exportHref(
                            resolvedReportType(reportSummary, reportType),
                            reportFrom,
                            reportTo,
                            reportPoolId,
                            reportRouteId,
                        )}
                        class="inline-flex h-11 items-center justify-center rounded-2xl border border-border/70 bg-background px-4 text-sm font-medium shadow-sm transition hover:bg-muted/25"
                    >
                        {exportLabel(
                            resolvedReportType(reportSummary, reportType),
                        )}
                    </a>
                </div>
            </div>

            <div class="flex justify-end md:hidden">
                <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    class="h-8 rounded-lg text-xs"
                    onclick={() =>
                        (reportFiltersExpanded = !reportFiltersExpanded)}
                    aria-expanded={reportFiltersExpanded}
                >
                    {reportFiltersExpanded
                        ? 'Sembunyikan Filter'
                        : 'Tampilkan Filter'}
                </Button>
            </div>
            <div
                class={reportFiltersExpanded
                    ? `grid gap-3 rounded-[24px] border p-3 md:grid-cols-2 xl:grid-cols-3 ${resolvedMeta(reportSummary, reportType).subtleTone}`
                    : `hidden rounded-[24px] border p-3 md:grid md:grid-cols-2 xl:grid-cols-3 ${resolvedMeta(reportSummary, reportType).subtleTone}`}
            >
                <label class="flex min-w-0 flex-col gap-1.5">
                    <span
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Kategori laporan
                    </span>
                    <select
                        class="h-10 rounded-2xl border border-border/70 bg-background/90 px-3 text-sm shadow-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        bind:value={reportType}
                        onchange={() => void loadReport(1)}
                    >
                        <option value="booking">Booking</option>
                        <option value="charter">Carter</option>
                        <option value="bagasi">Bagasi</option>
                    </select>
                </label>

                <label class="flex min-w-0 flex-col gap-1.5">
                    <span
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Pool
                    </span>
                    <select
                        class="h-10 rounded-2xl border border-border/70 bg-background/90 px-3 text-sm shadow-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        bind:value={reportPoolId}
                        onchange={() => void loadReport(1)}
                    >
                        <option value={0}>Semua Pool</option>
                        {#each pools as pool (pool.id)}
                            <option value={pool.id}>
                                {pool.name}{pool.code ? ` (${pool.code})` : ''}
                            </option>
                        {/each}
                    </select>
                </label>

                <label class="flex min-w-0 flex-col gap-1.5">
                    <span
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Rute Induk
                    </span>
                    <select
                        class="h-10 rounded-2xl border border-border/70 bg-background/90 px-3 text-sm shadow-sm outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                        bind:value={reportRouteId}
                        onchange={() => void loadReport(1)}
                    >
                        <option value={0}>Semua Rute</option>
                        {#each routes as route (route.id)}
                            <option value={route.id}>
                                {route.name}{route.origin
                                    ? ` (${route.origin}→${route.destination ?? '-'})`
                                    : ''}
                            </option>
                        {/each}
                    </select>
                </label>

                <label class="flex min-w-0 flex-col gap-1.5">
                    <span
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Dari tanggal
                    </span>
                    <input
                        bind:this={reportFromInput}
                        type="text"
                        value={reportFrom}
                        readonly
                        autocomplete="off"
                        placeholder="Tanggal mulai"
                        class="flex h-10 w-full rounded-2xl border border-border/70 bg-background/90 px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-xs placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring/20 focus-visible:outline-none"
                    />
                </label>

                <label class="flex min-w-0 flex-col gap-1.5">
                    <span
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Sampai tanggal
                    </span>
                    <input
                        bind:this={reportToInput}
                        type="text"
                        value={reportTo}
                        readonly
                        autocomplete="off"
                        placeholder="Tanggal akhir"
                        class="flex h-10 w-full rounded-2xl border border-border/70 bg-background/90 px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-xs placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring/20 focus-visible:outline-none"
                    />
                </label>

                <div class="flex items-end md:col-span-2 xl:col-span-1">
                    <Button
                        type="button"
                        class="h-10 w-full rounded-2xl px-5"
                        onclick={() => void loadReport(1)}
                        disabled={reportLoading}
                    >
                        {reportLoading
                            ? 'Memuat laporan...'
                            : 'Tampilkan Laporan'}
                    </Button>
                </div>
            </div>

            {#if reportSummary}
                <div
                    class="grid gap-3 rounded-[24px] border border-border/70 bg-background/80 p-3 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="rounded-2xl bg-muted/20 px-4 py-3">
                        <p
                            class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                        >
                            Total Pendapatan
                        </p>
                        <p
                            class="mt-2 text-2xl font-semibold tracking-tight tabular-nums"
                        >
                            {formatCurrency(reportSummary.revenue_total)}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Akumulasi
                            {resolvedMeta(
                                reportSummary,
                                reportType,
                            ).label.toLowerCase()}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-muted/20 px-4 py-3">
                        <p
                            class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                        >
                            Total BOP
                        </p>
                        <p
                            class="mt-2 text-2xl font-semibold tracking-tight tabular-nums text-rose-700 dark:text-rose-300"
                        >
                            {formatCurrency(reportSummary.bop_total ?? 0)}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Biaya operasional periode ini
                        </p>
                    </div>

                    <div class="rounded-2xl bg-muted/20 px-4 py-3">
                        <p
                            class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                        >
                            Margin
                        </p>
                        <p
                            class={`mt-2 text-2xl font-semibold tracking-tight tabular-nums ${(reportSummary.margin_total ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'}`}
                        >
                            {formatCurrency(reportSummary.margin_total ?? 0)}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Pendapatan − BOP
                        </p>
                    </div>

                    <div class="rounded-2xl bg-muted/20 px-4 py-3">
                        <p
                            class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                        >
                            Volume Data
                        </p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight">
                            {reportSummary.total_rows}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {resolvedMeta(reportSummary, reportType).dataLabel}
                        </p>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    {#if reportLoading}
        <div class="px-6 py-10 text-sm text-muted-foreground">
            Memuat data laporan...
        </div>
    {:else if reportSummary}
        <div class="space-y-0">
            <div
                class="flex flex-col gap-2 border-b border-border/70 px-5 py-4 md:flex-row md:items-center md:justify-between md:px-6"
            >
                <div>
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Detail Laporan
                    </p>
                    <h4 class="mt-1 text-sm font-semibold">
                        Semua data
                        {resolvedMeta(
                            reportSummary,
                            reportType,
                        ).label.toLowerCase()}
                        pada periode terpilih
                    </h4>
                </div>
                <div class="text-xs text-muted-foreground">
                    {reportSummary.total_rows}
                    {resolvedMeta(reportSummary, reportType).dataLabel} • periode
                    {reportSummary.from} sampai {reportSummary.to}
                </div>
            </div>

            {#if reportRows.length > 0}
                <div class="table-container max-h-[70vh]">
                    {#if reportSummary.type === 'booking'}
                        <div class="grid gap-3 p-3 md:hidden">
                            {#each asBookingRows(reportRows) as row (row.id)}
                                <article
                                    class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm"
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-foreground"
                                            >
                                                {row.name || '-'}
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs text-muted-foreground"
                                            >
                                                {row.tanggal || '-'} / {shortTime(
                                                    row.jam,
                                                )}
                                            </p>
                                        </div>
                                        <p
                                            class="shrink-0 text-right text-sm font-semibold tabular-nums text-foreground"
                                        >
                                            {formatCurrency(row.total)}
                                        </p>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <span
                                            class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.pembayaran)}`}
                                        >
                                            {row.pembayaran || '-'}
                                        </span>
                                        <span
                                            class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.status)}`}
                                        >
                                            {row.status || '-'}
                                        </span>
                                    </div>
                                    <details
                                        class="mt-3 overflow-hidden rounded-xl border border-border/70 bg-muted/20 text-xs"
                                    >
                                        <summary
                                            class="cursor-pointer list-none px-3 py-2 font-semibold text-foreground"
                                        >
                                            Detail
                                        </summary>
                                        <div
                                            class="grid gap-2 border-t border-border/70 p-3"
                                        >
                                            <div
                                                class="rounded-xl bg-background/80 px-3 py-2"
                                            >
                                                <p
                                                    class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                >
                                                    Rute
                                                </p>
                                                <p
                                                    class="mt-1 font-medium text-foreground"
                                                >
                                                    {row.rute || '-'}
                                                </p>
                                                <p
                                                    class="mt-1 text-[11px] text-muted-foreground"
                                                >
                                                    {row.pickup_point || '-'}
                                                </p>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div
                                                    class="rounded-xl bg-background/80 px-3 py-2"
                                                >
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                    >
                                                        Unit / Seat
                                                    </p>
                                                    <p
                                                        class="mt-1 font-medium text-foreground"
                                                    >
                                                        {row.unit || '-'} / {row.seat ||
                                                            '-'}
                                                    </p>
                                                </div>
                                                <div
                                                    class="rounded-xl bg-background/80 px-3 py-2"
                                                >
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                    >
                                                        Potongan
                                                    </p>
                                                    <p
                                                        class="mt-1 font-medium text-foreground"
                                                    >
                                                        {formatCurrency(
                                                            row.discount,
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </article>
                            {/each}
                        </div>
                        <table class="hidden w-full text-sm md:table">
                            <thead
                                class="sticky top-0 z-10 bg-background/95 backdrop-blur"
                            >
                                <tr
                                    class="border-b border-border/70 text-[11px] uppercase tracking-[0.12em] text-muted-foreground"
                                >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Tanggal</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Pelanggan</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Rute</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Seat / Unit</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Pembayaran</th
                                    >
                                    <th
                                        class="px-4 py-3 text-right font-semibold tabular-nums"
                                        >Potongan</th
                                    >
                                    <th
                                        class="px-4 py-3 text-right font-semibold tabular-nums"
                                        >Total</th
                                    >
                                </tr>
                            </thead>
                            <tbody class="text-[13px]">
                                {#each asBookingRows(reportRows) as row (row.id)}
                                    <tr
                                        class="border-b border-border/60 align-top transition hover:bg-muted/15"
                                    >
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.tanggal || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {shortTime(row.jam)}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.name || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.phone || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.rute || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.pickup_point || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                Seat {row.seat || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.unit || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span
                                                class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.pembayaran)}`}
                                            >
                                                {row.pembayaran || '-'}
                                            </span>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.status || '-'}
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3.5 text-right tabular-nums text-muted-foreground"
                                        >
                                            {formatCurrency(row.discount)}
                                        </td>
                                        <td
                                            class="px-4 py-3.5 text-right font-semibold tabular-nums text-foreground"
                                        >
                                            {formatCurrency(row.total)}
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {:else if reportSummary.type === 'charter'}
                        <div class="grid gap-3 p-3 md:hidden">
                            {#each asCharterRows(reportRows) as row (row.id)}
                                <article
                                    class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm"
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-foreground"
                                            >
                                                {row.name || '-'}
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs text-muted-foreground"
                                            >
                                                {row.start_date || '-'} / {shortTime(
                                                    row.departure_time,
                                                )}
                                            </p>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <p
                                                class="text-sm font-semibold tabular-nums text-foreground"
                                            >
                                                {formatCurrency(row.total)}
                                            </p>
                                            {#if row.bop_status}
                                                <span
                                                    class={`inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold ${badgeClass(row.bop_status)}`}
                                                >
                                                    {row.bop_status}
                                                </span>
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <span
                                            class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.payment_status)}`}
                                        >
                                            {row.payment_status || '-'}
                                        </span>
                                        <span
                                            class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.status || row.bop_status)}`}
                                        >
                                            {row.status ||
                                                row.bop_status ||
                                                '-'}
                                        </span>
                                    </div>
                                    <details
                                        class="mt-3 overflow-hidden rounded-xl border border-border/70 bg-muted/20 text-xs"
                                    >
                                        <summary
                                            class="cursor-pointer list-none px-3 py-2 font-semibold text-foreground"
                                        >
                                            Detail
                                        </summary>
                                        <div
                                            class="grid gap-2 border-t border-border/70 p-3"
                                        >
                                            <div
                                                class="rounded-xl bg-background/80 px-3 py-2"
                                            >
                                                <p
                                                    class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                >
                                                    Rute Carter
                                                </p>
                                                <p
                                                    class="mt-1 font-medium text-foreground"
                                                >
                                                    {routeLabel(
                                                        row.pickup_point,
                                                        row.drop_point,
                                                    )}
                                                </p>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div
                                                    class="rounded-xl bg-background/80 px-3 py-2"
                                                >
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                    >
                                                        Driver
                                                    </p>
                                                    <p
                                                        class="mt-1 font-medium text-foreground"
                                                    >
                                                        {row.driver_name || '-'}
                                                    </p>
                                                </div>
                                                <div
                                                    class="rounded-xl bg-background/80 px-3 py-2"
                                                >
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                    >
                                                        Armada
                                                    </p>
                                                    <p
                                                        class="mt-1 font-medium text-foreground"
                                                    >
                                                        {charterVehicle(row)}
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="rounded-xl bg-background/80 px-3 py-2"
                                            >
                                                <p
                                                    class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                >
                                                    Layanan
                                                </p>
                                                <p
                                                    class="mt-1 font-medium text-foreground"
                                                >
                                                    {row.layanan || '-'}
                                                </p>
                                            </div>
                                        </div>
                                    </details>
                                </article>
                            {/each}
                        </div>
                        <table class="hidden w-full text-sm md:table">
                            <thead
                                class="sticky top-0 z-10 bg-background/95 backdrop-blur"
                            >
                                <tr
                                    class="border-b border-border/70 text-[11px] uppercase tracking-[0.12em] text-muted-foreground"
                                >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Perjalanan</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Penyewa</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Rute</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Layanan</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Armada</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Status</th
                                    >
                                    <th
                                        class="px-4 py-3 text-right font-semibold tabular-nums"
                                        >Total</th
                                    >
                                </tr>
                            </thead>
                            <tbody class="text-[13px]">
                                {#each asCharterRows(reportRows) as row (row.id)}
                                    <tr
                                        class="border-b border-border/60 align-top transition hover:bg-muted/15"
                                    >
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.start_date || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.end_date &&
                                                row.end_date !== row.start_date
                                                    ? `${row.end_date} • `
                                                    : ''}{shortTime(
                                                    row.departure_time,
                                                )}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.name || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.phone || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {routeLabel(
                                                    row.pickup_point,
                                                    row.drop_point,
                                                )}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                Driver {row.driver_name || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.layanan || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {charterVehicle(row)}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span
                                                class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.payment_status)}`}
                                            >
                                                {row.payment_status || '-'}
                                            </span>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.status ||
                                                    row.bop_status ||
                                                    '-'}
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3.5 text-right font-semibold tabular-nums text-foreground"
                                        >
                                            {formatCurrency(row.total)}
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {:else}
                        <div class="grid gap-3 p-3 md:hidden">
                            {#each asLuggageRows(reportRows) as row (row.id)}
                                <article
                                    class="rounded-2xl border border-border/80 bg-card/95 p-3 shadow-sm"
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-foreground"
                                            >
                                                {row.kode_resi || '-'}
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs text-muted-foreground"
                                            >
                                                {row.tanggal ||
                                                    row.created_at ||
                                                    '-'}
                                            </p>
                                        </div>
                                        <p
                                            class="shrink-0 text-right text-sm font-semibold tabular-nums text-foreground"
                                        >
                                            {formatCurrency(row.total)}
                                        </p>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <span
                                            class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.status)}`}
                                        >
                                            {row.status || '-'}
                                        </span>
                                        <span
                                            class={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.payment_status)}`}
                                        >
                                            {row.payment_status || '-'}
                                        </span>
                                    </div>
                                    <details
                                        class="mt-3 overflow-hidden rounded-xl border border-border/70 bg-muted/20 text-xs"
                                    >
                                        <summary
                                            class="cursor-pointer list-none px-3 py-2 font-semibold text-foreground"
                                        >
                                            Detail
                                        </summary>
                                        <div
                                            class="grid gap-2 border-t border-border/70 p-3"
                                        >
                                            <div class="grid grid-cols-2 gap-2">
                                                <div
                                                    class="rounded-xl bg-background/80 px-3 py-2"
                                                >
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                    >
                                                        Pengirim
                                                    </p>
                                                    <p
                                                        class="mt-1 font-medium text-foreground"
                                                    >
                                                        {row.sender_name || '-'}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-[11px] text-muted-foreground"
                                                    >
                                                        {row.sender_phone ||
                                                            '-'}
                                                    </p>
                                                </div>
                                                <div
                                                    class="rounded-xl bg-background/80 px-3 py-2"
                                                >
                                                    <p
                                                        class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                    >
                                                        Penerima
                                                    </p>
                                                    <p
                                                        class="mt-1 font-medium text-foreground"
                                                    >
                                                        {row.receiver_name ||
                                                            '-'}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-[11px] text-muted-foreground"
                                                    >
                                                        {row.receiver_phone ||
                                                            '-'}
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="rounded-xl bg-background/80 px-3 py-2"
                                            >
                                                <p
                                                    class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                                >
                                                    Layanan
                                                </p>
                                                <p
                                                    class="mt-1 font-medium text-foreground"
                                                >
                                                    {row.service_name || '-'} / {row.quantity}
                                                    item
                                                </p>
                                            </div>
                                        </div>
                                    </details>
                                </article>
                            {/each}
                        </div>
                        <table class="hidden w-full text-sm md:table">
                            <thead
                                class="sticky top-0 z-10 bg-background/95 backdrop-blur"
                            >
                                <tr
                                    class="border-b border-border/70 text-[11px] uppercase tracking-[0.12em] text-muted-foreground"
                                >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Tanggal</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Resi</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Pengirim</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Penerima</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Layanan</th
                                    >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Status</th
                                    >
                                    <th
                                        class="px-4 py-3 text-right font-semibold tabular-nums"
                                        >Total</th
                                    >
                                </tr>
                            </thead>
                            <tbody class="text-[13px]">
                                {#each asLuggageRows(reportRows) as row (row.id)}
                                    <tr
                                        class="border-b border-border/60 align-top transition hover:bg-muted/15"
                                    >
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.tanggal || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.created_at || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.kode_resi || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.quantity} item
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.sender_name || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.sender_phone || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.receiver_name || '-'}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.receiver_phone || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {row.service_name || '-'}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span
                                                class={`inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold ${badgeClass(row.status)}`}
                                            >
                                                {row.status || '-'}
                                            </span>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {row.payment_status || '-'}
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3.5 text-right font-semibold tabular-nums text-foreground"
                                        >
                                            {formatCurrency(row.total)}
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {/if}
                </div>
                <div
                    class="flex flex-col gap-3 border-t border-border/70 px-5 py-4 text-xs text-muted-foreground md:flex-row md:items-center md:justify-between md:px-6"
                >
                    <span>
                        Menampilkan halaman {reportMeta.page} dari
                        {reportMeta.last_page} ({reportMeta.total}
                        {resolvedMeta(reportSummary, reportType).dataLabel})
                    </span>
                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-xl px-3 text-xs"
                            disabled={reportLoading || reportMeta.page <= 1}
                            onclick={() =>
                                void jumpReportPage(reportMeta.page - 1)}
                        >
                            Prev
                        </Button>
                        <span
                            class="rounded-full border border-border/70 bg-background px-3 py-1 text-xs font-medium text-foreground"
                        >
                            {reportMeta.page} / {reportMeta.last_page}
                        </span>
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-xl px-3 text-xs"
                            disabled={reportLoading ||
                                reportMeta.page >= reportMeta.last_page}
                            onclick={() =>
                                void jumpReportPage(reportMeta.page + 1)}
                        >
                            Next
                        </Button>
                    </div>
                </div>
            {:else}
                <div class="px-5 py-8 md:px-6">
                    <div
                        class={`rounded-[24px] border border-dashed px-5 py-6 text-sm ${resolvedMeta(reportSummary, reportType).tone}`}
                    >
                        Tidak ada data
                        {resolvedMeta(
                            reportSummary,
                            reportType,
                        ).label.toLowerCase()}
                        pada periode yang dipilih. Ubah kategori atau tanggal untuk
                        melihat periode lain.
                    </div>
                </div>
            {/if}
        </div>
    {:else}
        <div class="px-5 py-8 md:px-6">
            <div
                class="rounded-[24px] border border-dashed border-border/80 bg-muted/10 px-5 py-6 text-sm text-muted-foreground"
            >
                Pilih kategori dan rentang tanggal, lalu klik `Tampilkan
                Laporan`.
            </div>
        </div>
    {/if}
</div>
