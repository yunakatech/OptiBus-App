<?php

namespace App\Http\Controllers;

use App\Support\AccessControl;
use App\Support\ActivityLog;
use App\Support\DeferredInertia;
use App\Support\ManifestLifecycle;
use App\Support\PoolScope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /** @var array<int, string> */
    private const STATUSES = ['unpaid', 'dp', 'paid'];

    /** @var array<int, string> */
    private const SOURCES = ['all', 'booking', 'charter', 'luggage'];

    /** @var array<int, string> */
    private const PAID_BOOKING_STATUSES = ['lunas', 'redbus', 'traveloka', 'qris', 'transfer', 'transfer bju', 'tunai'];

    /** @var array<string, string> */
    private array $poolNameCache = [];

    public function __invoke(Request $request): Response
    {
        $filters = $this->filters($request);
        $usesDeferredInertia = DeferredInertia::opsEnabled();

        return Inertia::render('Payments', [
            'filters' => $filters,
            'paymentData' => $usesDeferredInertia
                ? Inertia::defer(fn (): array => $this->paymentData($request), 'payment-data')
                : $this->paymentData($request),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $rowsQuery = $this->paymentRowsQuery($filters);
        $rowsQuery = $rowsQuery === null
            ? null
            : $rowsQuery
                ->orderByDesc('trx_date')
                ->orderByDesc('trx_time')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        $filename = 'pembayaran-'.$filters['status'].'-'.$filters['source'].'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rowsQuery): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Sumber',
                'Kode',
                'Customer',
                'Nama Tambahan',
                'Kontak',
                'Pool',
                'Rute',
                'Tanggal',
                'Jam',
                'Tagihan',
                'Dibayar',
                'DP',
                'Sisa',
                'Status Pembayaran',
                'Status Data',
                'Dibuat',
            ]);

            if ($rowsQuery === null) {
                fclose($out);

                return;
            }

            foreach ($rowsQuery->cursor() as $rawRow) {
                $row = $this->publicRow($rawRow);
                fputcsv($out, [
                    $row['source_label'] ?? '',
                    $row['code'] ?? '',
                    $row['customer_name'] ?? '',
                    $row['secondary_name'] ?? '',
                    $row['contact'] ?? '',
                    $row['pool_name'] ?? '',
                    $row['route'] ?? '',
                    $row['date'] ?? '',
                    $row['time'] ?? '',
                    (float) ($row['amount'] ?? 0),
                    (float) ($row['paid_amount'] ?? 0),
                    (float) ($row['down_payment'] ?? 0),
                    (float) ($row['remaining_amount'] ?? 0),
                    $row['payment_status'] ?? '',
                    $row['source_status'] ?? '',
                    $row['created_at'] ?? '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function update(Request $request, string $source, int $id): JsonResponse
    {
        $source = strtolower(trim($source));
        if (! in_array($source, ['booking', 'charter', 'luggage'], true)) {
            return $this->error('Sumber data pembayaran tidak dikenali.', 404);
        }

        $data = $request->validate([
            'payment_status' => ['required', 'string', 'max:40'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
        ]);
        $paymentStatus = $this->canonicalPaymentStatus($source, (string) $data['payment_status']);
        $downPayment = array_key_exists('down_payment', $data) ? (float) $data['down_payment'] : null;

        $result = $this->updatePaymentSource($source, $id, $paymentStatus, $downPayment, true);
        if (! ($result['ok'] ?? false)) {
            return $this->error((string) ($result['message'] ?? 'Gagal memperbarui pembayaran.'), (int) ($result['status'] ?? 400));
        }

        return $this->ok([
            'message' => (string) ($result['message'] ?? 'Pembayaran berhasil diperbarui.'),
            'payment_status' => (string) ($result['payment_status'] ?? $paymentStatus),
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.source' => ['required', 'string', 'in:booking,charter,luggage'],
            'items.*.id' => ['required', 'integer', 'min:1'],
            'payment_status' => ['required', 'string', 'max:50'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = collect($payload['items'])
            ->map(static fn ($item): array => [
                'source' => strtolower(trim((string) ($item['source'] ?? ''))),
                'id' => (int) ($item['id'] ?? 0),
            ])
            ->filter(static fn (array $item): bool => in_array($item['source'], ['booking', 'charter', 'luggage'], true) && $item['id'] > 0)
            ->values()
            ->all();

        if ($items === []) {
            return $this->error('Tidak ada transaksi yang valid untuk diproses.', 422);
        }

        $targetStatus = $this->normalizeBulkPaymentStatus((string) $payload['payment_status']);
        $downPayment = array_key_exists('down_payment', $payload)
            ? (float) $payload['down_payment']
            : null;

        if ($targetStatus === 'DP') {
            foreach ($items as $item) {
                if ($item['source'] !== 'charter') {
                    return $this->error('Bulk DP hanya bisa untuk carter.', 422);
                }
            }

            if ($downPayment === null || $downPayment <= 0) {
                return $this->error('Nominal DP wajib diisi untuk bulk carter.', 422);
            }
        }

        $updated = [];
        $skipped = [];

        DB::transaction(function () use ($items, $targetStatus, $downPayment, &$updated, &$skipped): void {
            foreach ($items as $item) {
                $source = (string) $item['source'];
                $paymentStatus = $this->canonicalPaymentStatus($source, $targetStatus);
                $itemDownPayment = $source === 'charter' && $targetStatus === 'DP'
                    ? $downPayment
                    : null;

                $result = $this->updatePaymentSource($source, (int) $item['id'], $paymentStatus, $itemDownPayment, false);
                if (($result['ok'] ?? false) === true) {
                    $updated[] = $result;

                    continue;
                }

                $skipped[] = [
                    'source' => $source,
                    'id' => (int) $item['id'],
                    'reason' => (string) ($result['reason'] ?? 'skipped'),
                    'message' => (string) ($result['message'] ?? 'Dilewati.'),
                ];
            }

            if ($updated !== []) {
                $updatedLabels = array_values(array_filter(array_map(
                    static fn (array $item): string => trim((string) ($item['label'] ?? '')),
                    $updated,
                )));
                $skippedLabels = array_values(array_filter(array_map(
                    static fn (array $item): string => trim((string) ($item['source'] ?? '')).'#'.(int) ($item['id'] ?? 0).'('.(string) ($item['reason'] ?? 'skipped').')',
                    $skipped,
                )));

                ActivityLog::write(
                    'PAYMENT',
                    'Pembayaran serentak diperbarui',
                    'Status: '.$targetStatus
                    .' | Diperbarui: '.count($updated)
                    .' | Dilewati: '.count($skipped)
                    .($updatedLabels !== [] ? ' | Item: '.implode(', ', array_slice($updatedLabels, 0, 10)) : '')
                    .($skippedLabels !== [] ? ' | Skip: '.implode(', ', array_slice($skippedLabels, 0, 10)) : ''),
                    (string) (auth()->user()?->email ?? auth()->user()?->name ?? 'system'),
                    [
                        'payment_status' => $targetStatus,
                        'updated_count' => count($updated),
                        'skipped_count' => count($skipped),
                        'updated_items' => $updated,
                        'skipped_items' => $skipped,
                    ],
                );
            }
        });

        return $this->ok([
            'message' => 'Status pembayaran serentak berhasil diproses.',
            'payment_status' => $targetStatus,
            'updated_count' => count($updated),
            'skipped_count' => count($skipped),
            'skipped_items' => $skipped,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentData(Request $request): array
    {
        $filters = $this->filters($request);
        $page = max(1, (int) $filters['page']);
        $perPage = max(10, min(50, (int) $filters['per_page']));
        $baseQuery = $this->paymentBaseUnionQuery($filters);
        $summary = $this->summary($filters, $baseQuery ? clone $baseQuery : null);
        $total = (int) ($summary['active']['count'] ?? 0);
        $rows = [];
        $rowsQuery = $baseQuery ? $this->paymentRowsQuery($filters, $baseQuery) : null;

        if ($rowsQuery !== null) {
            $rows = $rowsQuery
                ->orderByDesc('trx_date')
                ->orderByDesc('trx_time')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->forPage($page, $perPage)
                ->get()
                ->map(fn (object $row): array => $this->publicRow($row))
                ->values()
                ->all();
        }

        return [
            'filters' => $filters,
            'rows' => $rows,
            'summary' => $summary,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / max(1, $perPage))),
            ],
            'source_access' => [
                'booking' => $this->canViewSource('booking'),
                'charter' => $this->canViewSource('charter'),
                'luggage' => $this->canViewSource('luggage'),
            ],
        ];
    }

    /**
     * @return array{status: string, source: string, q: string, date_from: string, date_to: string, page: int, per_page: int}
     */
    private function filters(Request $request): array
    {
        $status = strtolower(trim((string) $request->query('status', 'unpaid')));
        $source = strtolower(trim((string) $request->query('source', 'all')));
        [$dateFrom, $dateTo] = $this->dateRangeFilters($request);

        return [
            'status' => in_array($status, self::STATUSES, true) ? $status : 'unpaid',
            'source' => in_array($source, self::SOURCES, true) ? $source : 'all',
            'q' => trim((string) $request->query('q', '')),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'page' => max(1, (int) $request->query('page', 1)),
            'per_page' => max(10, min(50, (int) $request->query('per_page', 20))),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function dateRangeFilters(Request $request): array
    {
        $today = CarbonImmutable::today();
        $rawFrom = $this->parseDateFilter($request->query('date_from'));
        $rawTo = $this->parseDateFilter($request->query('date_to'));

        if ($rawFrom === null && $rawTo === null) {
            $from = $today->subMonthsNoOverflow(3);
            $to = $today;
        } elseif ($rawFrom !== null && $rawTo === null) {
            $from = $rawFrom;
            $to = $rawFrom->addMonthsNoOverflow(3);
        } elseif ($rawFrom === null && $rawTo !== null) {
            $to = $rawTo;
            $from = $rawTo->subMonthsNoOverflow(3);
        } else {
            $from = $rawFrom;
            $to = $rawTo;
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $maxTo = $from->addMonthsNoOverflow(3);
        if ($to->gt($maxTo)) {
            $to = $maxTo;
        }

        return [$from->toDateString(), $to->toDateString()];
    }

    private function parseDateFilter(mixed $value): ?CarbonImmutable
    {
        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paymentBaseUnionQuery(array $filters): ?Builder
    {
        $source = (string) $filters['source'];
        $queries = [];

        if (($source === 'all' || $source === 'booking') && $this->canViewSource('booking')) {
            $queries[] = $this->bookingPaymentQuery($filters);
        }

        if (($source === 'all' || $source === 'charter') && $this->canViewSource('charter')) {
            $queries[] = $this->charterPaymentQuery($filters);
        }

        if (($source === 'all' || $source === 'luggage') && $this->canViewSource('luggage')) {
            $queries[] = $this->luggagePaymentQuery($filters);
        }

        $queries = array_values(array_filter($queries));
        if ($queries === []) {
            return null;
        }

        $union = array_shift($queries);
        foreach ($queries as $query) {
            $union->unionAll($query);
        }

        return $union;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paymentRowsQuery(array $filters, ?Builder $baseQuery = null): ?Builder
    {
        $baseQuery = $baseQuery ? clone $baseQuery : $this->paymentBaseUnionQuery($filters);
        if ($baseQuery === null) {
            return null;
        }

        $query = DB::query()
            ->fromSub($baseQuery, 'payment_rows')
            ->whereNotNull('status_bucket');

        $status = (string) ($filters['status'] ?? 'unpaid');
        if (in_array($status, self::STATUSES, true)) {
            $query->where('status_bucket', $status);
        }

        $this->applyDateRange($query, $filters);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyDateRange(Builder $query, array $filters): void
    {
        $dateFrom = (string) ($filters['date_from'] ?? '');
        $dateTo = (string) ($filters['date_to'] ?? '');

        if ($dateFrom === '' || $dateTo === '') {
            return;
        }

        $query->whereBetween('trx_date', [$dateFrom, $dateTo]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function bookingPaymentQuery(array $filters): ?Builder
    {
        if (! Schema::hasTable('bookings')) {
            return null;
        }

        $hasRouteId = Schema::hasColumn('bookings', 'route_id');
        $hasTicketCode = Schema::hasColumn('bookings', 'ticket_code');
        $hasDepartureCode = Schema::hasColumn('bookings', 'departure_code');
        $amountSql = '(COALESCE(b.price, 0) - COALESCE(b.discount, 0))';
        $paymentSql = "LOWER(COALESCE(b.pembayaran, ''))";
        $statusBucketSql = "CASE
            WHEN {$paymentSql} IN ('".implode("','", self::PAID_BOOKING_STATUSES)."') THEN 'paid'
            WHEN {$paymentSql} = 'dp' THEN 'dp'
            WHEN b.pembayaran IS NULL OR {$paymentSql} IN ('', 'belum lunas', 'belum bayar') THEN 'unpaid'
            ELSE NULL
        END";
        $query = DB::table('bookings as b');

        if ($hasRouteId && Schema::hasTable('routes')) {
            $query->leftJoin('routes as r', 'b.route_id', '=', 'r.id');
        }

        $query->select([
            DB::raw("'booking' as source"),
            'b.id',
            $hasTicketCode ? 'b.ticket_code as code' : ($hasDepartureCode ? 'b.departure_code as code' : DB::raw('NULL as code')),
            'b.name as customer_name',
            DB::raw('NULL as secondary_name'),
            'b.phone as contact',
            $hasRouteId && Schema::hasTable('routes') ? DB::raw('COALESCE(r.name, b.rute) as route_label') : 'b.rute as route_label',
            DB::raw('NULL as route_secondary'),
            $hasRouteId ? 'b.route_id' : DB::raw('NULL as route_id'),
            'b.tanggal as trx_date',
            'b.jam as trx_time',
            DB::raw($amountSql.' as amount'),
            DB::raw("CASE WHEN {$paymentSql} IN ('".implode("','", self::PAID_BOOKING_STATUSES)."') THEN {$amountSql} ELSE 0 END as paid_amount"),
            DB::raw('0 as down_payment'),
            DB::raw("CASE WHEN {$paymentSql} IN ('".implode("','", self::PAID_BOOKING_STATUSES)."') THEN 0 ELSE {$amountSql} END as remaining_amount"),
            'b.pembayaran as payment_status',
            'b.status as source_status',
            DB::raw("{$statusBucketSql} as status_bucket"),
            DB::raw('NULL as pool_id'),
            DB::raw('NULL as pool_name'),
            'b.created_at',
            DB::raw(($this->canUpdateSource('booking') ? '1' : '0').' as can_update'),
        ]);

        PoolScope::applyRouteScope($query, $hasRouteId ? 'b.route_id' : '', 'b.rute');
        $this->applyTenantScopeIfExists($query, 'bookings', 'b');
        $query->whereRaw("LOWER(COALESCE(b.status, 'active')) <> 'canceled'");
        $this->applySearch($query, (string) $filters['q'], ['b.name', 'b.phone', 'b.rute', 'b.pembayaran']);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function charterPaymentQuery(array $filters): ?Builder
    {
        if (! Schema::hasTable('charters')) {
            return null;
        }

        $hasPoolId = Schema::hasColumn('charters', 'pool_id');
        $hasStatus = Schema::hasColumn('charters', 'status');
        $query = DB::table('charters as c');

        if ($hasPoolId && Schema::hasTable('pools')) {
            $query->leftJoin('pools as p', 'c.pool_id', '=', 'p.id');
        }

        $paymentSql = "LOWER(COALESCE(c.payment_status, ''))";
        $amountSql = 'COALESCE(c.price, 0)';
        $downPaymentSql = 'COALESCE(c.down_payment, 0)';
        $statusBucketSql = "CASE
            WHEN {$paymentSql} = 'lunas' THEN 'paid'
            WHEN {$paymentSql} = 'dp' OR ({$downPaymentSql} > 0 AND {$paymentSql} <> 'lunas') THEN 'dp'
            WHEN {$paymentSql} NOT IN ('lunas', 'dp', 'canceled') AND {$downPaymentSql} <= 0 THEN 'unpaid'
            ELSE NULL
        END";

        $query->select([
            DB::raw("'charter' as source"),
            'c.id',
            DB::raw('NULL as code'),
            'c.name as customer_name',
            'c.company_name as secondary_name',
            'c.phone as contact',
            'c.pickup_point as route_label',
            'c.drop_point as route_secondary',
            DB::raw('NULL as route_id'),
            'c.start_date as trx_date',
            'c.departure_time as trx_time',
            DB::raw($amountSql.' as amount'),
            DB::raw("CASE WHEN {$paymentSql} = 'lunas' THEN {$amountSql} WHEN {$paymentSql} = 'dp' OR {$downPaymentSql} > 0 THEN {$downPaymentSql} ELSE 0 END as paid_amount"),
            DB::raw($downPaymentSql.' as down_payment'),
            DB::raw("CASE WHEN {$paymentSql} = 'lunas' THEN 0 WHEN ({$amountSql} - {$downPaymentSql}) < 0 THEN 0 ELSE ({$amountSql} - {$downPaymentSql}) END as remaining_amount"),
            'c.payment_status',
            $hasStatus ? 'c.status as source_status' : DB::raw("CASE WHEN {$paymentSql} = 'canceled' THEN 'canceled' ELSE 'active' END as source_status"),
            DB::raw("{$statusBucketSql} as status_bucket"),
            $hasPoolId ? 'c.pool_id' : DB::raw('NULL as pool_id'),
            $hasPoolId && Schema::hasTable('pools') ? 'p.name as pool_name' : DB::raw('NULL as pool_name'),
            'c.created_at',
            DB::raw(($this->canUpdateSource('charter') ? '1' : '0').' as can_update'),
        ]);

        PoolScope::applyCharterScope($query, 'c');
        $this->applyTenantScopeIfExists($query, 'charters', 'c');
        $query
            ->whereRaw("LOWER(COALESCE(c.payment_status, '')) <> 'canceled'")
            ->whereRaw(($hasStatus ? "LOWER(COALESCE(c.status, 'active'))" : "'active'")." <> 'canceled'");
        $this->applySearch($query, (string) $filters['q'], ['c.name', 'c.company_name', 'c.phone', 'c.pickup_point', 'c.drop_point', 'c.payment_status']);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function luggagePaymentQuery(array $filters): ?Builder
    {
        if (! Schema::hasTable('luggages')) {
            return null;
        }

        $hasPoolId = Schema::hasColumn('luggages', 'pool_id');
        $hasRouteId = Schema::hasColumn('luggages', 'rute_id');
        $query = DB::table('luggages as l');

        if ($hasPoolId && Schema::hasTable('pools')) {
            $query->leftJoin('pools as p', 'l.pool_id', '=', 'p.id');
        }

        if ($hasRouteId && Schema::hasTable('routes')) {
            $query->leftJoin('routes as r', 'l.rute_id', '=', 'r.id');
        }

        $paymentSql = "LOWER(COALESCE(l.payment_status, ''))";
        $amountSql = 'COALESCE(l.price, 0)';
        $statusBucketSql = "CASE
            WHEN {$paymentSql} = 'lunas' THEN 'paid'
            WHEN {$paymentSql} = 'dp' THEN 'dp'
            WHEN {$paymentSql} NOT IN ('lunas', 'dp', 'canceled') THEN 'unpaid'
            ELSE NULL
        END";

        $query->select([
            DB::raw("'luggage' as source"),
            'l.id',
            Schema::hasColumn('luggages', 'kode_resi') ? 'l.kode_resi as code' : DB::raw('NULL as code'),
            'l.sender_name as customer_name',
            'l.receiver_name as secondary_name',
            'l.sender_phone as contact',
            $hasRouteId && Schema::hasTable('routes') ? DB::raw('COALESCE(r.name, l.rute) as route_label') : 'l.rute as route_label',
            DB::raw('NULL as route_secondary'),
            $hasRouteId ? 'l.rute_id as route_id' : DB::raw('NULL as route_id'),
            'l.tanggal as trx_date',
            DB::raw('NULL as trx_time'),
            DB::raw($amountSql.' as amount'),
            DB::raw("CASE WHEN {$paymentSql} = 'lunas' THEN {$amountSql} ELSE 0 END as paid_amount"),
            DB::raw('0 as down_payment'),
            DB::raw("CASE WHEN {$paymentSql} = 'lunas' THEN 0 ELSE {$amountSql} END as remaining_amount"),
            'l.payment_status',
            'l.status as source_status',
            DB::raw("{$statusBucketSql} as status_bucket"),
            $hasPoolId ? 'l.pool_id' : DB::raw('NULL as pool_id'),
            $hasPoolId && Schema::hasTable('pools') ? 'p.name as pool_name' : DB::raw('NULL as pool_name'),
            'l.created_at',
            DB::raw(($this->canUpdateSource('luggage') ? '1' : '0').' as can_update'),
        ]);

        PoolScope::applyPoolOrRouteScope(
            $query,
            $hasPoolId ? 'l.pool_id' : '',
            $hasRouteId ? 'l.rute_id' : '',
            'l.rute',
        );
        $this->applyTenantScopeIfExists($query, 'luggages', 'l');
        $query
            ->whereRaw("LOWER(COALESCE(l.payment_status, '')) <> 'canceled'")
            ->whereRaw("LOWER(COALESCE(l.status, '')) <> 'canceled'");
        $this->applySearch($query, (string) $filters['q'], ['l.sender_name', 'l.receiver_name', 'l.sender_phone', 'l.receiver_phone', 'l.kode_resi', 'l.rute', 'l.payment_status']);

        return $query;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function applySearch(Builder $query, string $keyword, array $columns): void
    {
        $keyword = mb_strtolower(trim($keyword));
        if ($keyword === '') {
            return;
        }

        $like = '%'.$keyword.'%';
        $query->where(function (Builder $builder) use ($columns, $like): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $builder->{$method}("LOWER(COALESCE({$column}, '')) LIKE ?", [$like]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function summary(array $filters, ?Builder $baseQuery = null): array
    {
        $byStatus = array_fill_keys(self::STATUSES, [
            'count' => 0,
            'amount' => 0.0,
            'remaining' => 0.0,
        ]);
        $baseQuery = $baseQuery ? clone $baseQuery : $this->paymentBaseUnionQuery($filters);

        if ($baseQuery !== null) {
            $rows = DB::query()
                ->fromSub($baseQuery, 'payment_rows')
                ->whereNotNull('status_bucket');

            $this->applyDateRange($rows, $filters);

            $rows = $rows
                ->select('status_bucket')
                ->selectRaw('COUNT(*) as total, COALESCE(SUM(amount), 0) as amount, COALESCE(SUM(remaining_amount), 0) as remaining')
                ->groupBy('status_bucket')
                ->get();

            foreach ($rows as $row) {
                $status = (string) ($row->status_bucket ?? '');
                if (! in_array($status, self::STATUSES, true)) {
                    continue;
                }

                $byStatus[$status] = [
                    'count' => (int) ($row->total ?? 0),
                    'amount' => (float) ($row->amount ?? 0),
                    'remaining' => (float) ($row->remaining ?? 0),
                ];
            }
        }

        return [
            'by_status' => $byStatus,
            'active' => $byStatus[(string) $filters['status']] ?? ['count' => 0, 'amount' => 0.0, 'remaining' => 0.0],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function publicRow(object $row): array
    {
        $source = (string) ($row->source ?? '');
        $routeLabel = trim((string) ($row->route_label ?? ''));
        $routeSecondary = trim((string) ($row->route_secondary ?? ''));
        $route = $routeSecondary !== '' ? trim($routeLabel.' -> '.$routeSecondary) : $routeLabel;
        $amount = (float) ($row->amount ?? 0);
        $paidAmount = (float) ($row->paid_amount ?? 0);
        $remainingAmount = max(0.0, (float) ($row->remaining_amount ?? ($amount - $paidAmount)));

        return [
            'key' => $source.'-'.(int) ($row->id ?? 0),
            'source' => $source,
            'source_label' => $this->sourceLabel($source),
            'id' => (int) ($row->id ?? 0),
            'code' => trim((string) ($row->code ?? '')) ?: strtoupper(substr($source, 0, 3)).'-'.(int) ($row->id ?? 0),
            'customer_name' => trim((string) ($row->customer_name ?? '')),
            'secondary_name' => trim((string) ($row->secondary_name ?? '')),
            'contact' => trim((string) ($row->contact ?? '')),
            'route' => $route !== '' ? $route : '-',
            'route_id' => (int) ($row->route_id ?? 0),
            'date' => (string) ($row->trx_date ?? ''),
            'time' => substr((string) ($row->trx_time ?? ''), 0, 5),
            'amount' => $amount,
            'paid_amount' => $paidAmount,
            'down_payment' => (float) ($row->down_payment ?? 0),
            'remaining_amount' => $remainingAmount,
            'payment_status' => trim((string) ($row->payment_status ?? '')) ?: $this->statusLabel((string) ($row->status_bucket ?? 'unpaid'), $source),
            'status_bucket' => (string) ($row->status_bucket ?? 'unpaid'),
            'source_status' => trim((string) ($row->source_status ?? '')),
            'pool_id' => (int) ($row->pool_id ?? 0),
            'pool_name' => $this->resolvePoolName((int) ($row->pool_id ?? 0), (int) ($row->route_id ?? 0), $routeLabel, $routeSecondary),
            'can_update' => (bool) ($row->can_update ?? false),
            'created_at' => (string) ($row->created_at ?? ''),
        ];
    }

    private function updateBookingPayment(int $id, string $paymentStatus): JsonResponse
    {
        $result = $this->updatePaymentSource('booking', $id, $paymentStatus, null, true);
        if (! ($result['ok'] ?? false)) {
            return $this->error((string) ($result['message'] ?? 'Gagal memperbarui pembayaran.'), (int) ($result['status'] ?? 400));
        }

        return $this->ok([
            'message' => (string) ($result['message'] ?? 'Pembayaran booking diperbarui.'),
            'payment_status' => (string) ($result['payment_status'] ?? $paymentStatus),
        ]);
    }

    private function updateCharterPayment(int $id, string $paymentStatus, ?float $downPayment): JsonResponse
    {
        $result = $this->updatePaymentSource('charter', $id, $paymentStatus, $downPayment, true);
        if (! ($result['ok'] ?? false)) {
            return $this->error((string) ($result['message'] ?? 'Gagal memperbarui pembayaran.'), (int) ($result['status'] ?? 400));
        }

        return $this->ok([
            'message' => (string) ($result['message'] ?? 'Pembayaran carter diperbarui.'),
            'payment_status' => (string) ($result['payment_status'] ?? $paymentStatus),
        ]);
    }

    private function updateLuggagePayment(int $id, string $paymentStatus): JsonResponse
    {
        $result = $this->updatePaymentSource('luggage', $id, $paymentStatus, null, true);
        if (! ($result['ok'] ?? false)) {
            return $this->error((string) ($result['message'] ?? 'Gagal memperbarui pembayaran.'), (int) ($result['status'] ?? 400));
        }

        return $this->ok([
            'message' => (string) ($result['message'] ?? 'Pembayaran bagasi diperbarui.'),
            'payment_status' => (string) ($result['payment_status'] ?? $paymentStatus),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function updatePaymentSource(string $source, int $id, string $paymentStatus, ?float $downPayment = null, bool $writeLog = true): array
    {
        if (! in_array($source, ['booking', 'charter', 'luggage'], true)) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Sumber data pembayaran tidak dikenali.',
                'reason' => 'invalid_source',
            ];
        }

        if (! $this->canUpdateSource($source)) {
            return [
                'ok' => false,
                'status' => 403,
                'message' => 'Anda tidak memiliki akses untuk mengubah pembayaran ini.',
                'reason' => 'forbidden',
            ];
        }

        return match ($source) {
            'booking' => $this->updateBookingPaymentRecord($id, $paymentStatus, $writeLog),
            'charter' => $this->updateCharterPaymentRecord($id, $paymentStatus, $downPayment, $writeLog),
            'luggage' => $this->updateLuggagePaymentRecord($id, $paymentStatus, $writeLog),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function updateBookingPaymentRecord(int $id, string $paymentStatus, bool $writeLog = true): array
    {
        $hasRouteId = Schema::hasColumn('bookings', 'route_id');
        $query = DB::table('bookings as b')->where('b.id', $id);
        PoolScope::applyRouteScope($query, $hasRouteId ? 'b.route_id' : '', 'b.rute');
        $this->applyTenantScopeIfExists($query, 'bookings', 'b');

        $row = $query->first(['b.id', 'b.name', 'b.rute', 'b.pembayaran', 'b.status', 'b.tanggal', 'b.jam', 'b.unit']);
        if (! $row) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Data booking tidak ditemukan atau di luar akses pool.',
                'reason' => 'not_found',
                'source' => 'booking',
                'id' => $id,
            ];
        }

        $currentStatus = strtolower(trim((string) ($row->status ?? '')));
        if (in_array($currentStatus, ['canceled', 'cancelled'], true)) {
            return [
                'ok' => false,
                'status' => 409,
                'message' => 'Booking sudah dibatalkan. Status pembayaran tidak bisa diubah lagi.',
                'reason' => 'canceled',
                'source' => 'booking',
                'id' => $id,
                'label' => (string) ($row->name ?? '#'.$id),
            ];
        }

        $assignment = $this->findTripAssignmentForPayment(
            (string) ($row->rute ?? ''),
            (string) ($row->tanggal ?? ''),
            (string) ($row->jam ?? ''),
            max(1, (int) ($row->unit ?? 1)),
        );
        if ($this->manifestTripIsLocked($assignment)) {
            return [
                'ok' => false,
                'status' => 409,
                'message' => 'Manifest sudah ditutup. Status pembayaran tidak bisa diubah lagi.',
                'reason' => 'manifest_closed',
                'source' => 'booking',
                'id' => $id,
                'label' => (string) ($row->name ?? '#'.$id),
            ];
        }

        $before = (string) ($row->pembayaran ?? '');
        DB::table('bookings')->where('id', $id)->update([
            'pembayaran' => $paymentStatus,
            'updated_at' => now(),
        ]);

        $label = (string) ($row->name ?? '#'.$id);
        if ($writeLog) {
            $this->writePaymentLog('BOOKING', 'Pembayaran booking '.$label.' diperbarui', $before, $paymentStatus, [
                'booking_id' => $id,
                'route' => (string) ($row->rute ?? ''),
            ]);
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Pembayaran booking diperbarui.',
            'payment_status' => $paymentStatus,
            'source' => 'booking',
            'id' => $id,
            'label' => $label,
            'before' => $before,
            'after' => $paymentStatus,
            'log_tag' => 'BOOKING',
            'log_title' => 'Pembayaran booking '.$label.' diperbarui',
            'log_before' => $before,
            'log_after' => $paymentStatus,
            'log_extra' => [
                'booking_id' => $id,
                'route' => (string) ($row->rute ?? ''),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function updateCharterPaymentRecord(int $id, string $paymentStatus, ?float $downPayment = null, bool $writeLog = true): array
    {
        $query = DB::table('charters as c')->where('c.id', $id);
        PoolScope::applyCharterScope($query, 'c');
        $this->applyTenantScopeIfExists($query, 'charters', 'c');

        $row = $query->first(['c.id', 'c.name', 'c.payment_status', 'c.down_payment']);
        if (! $row) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Data carter tidak ditemukan atau di luar akses pool.',
                'reason' => 'not_found',
                'source' => 'charter',
                'id' => $id,
            ];
        }

        $payload = ['payment_status' => $paymentStatus];
        if ($paymentStatus === 'Belum Lunas') {
            $payload['down_payment'] = 0;
        } elseif ($paymentStatus === 'DP') {
            $payload['down_payment'] = max(0, (float) ($downPayment ?? $row->down_payment ?? 0));
        } elseif ($downPayment !== null) {
            $payload['down_payment'] = max(0, $downPayment);
        }

        DB::table('charters')->where('id', $id)->update($payload);

        $label = (string) ($row->name ?? '#'.$id);
        if ($writeLog) {
            $this->writePaymentLog('CHARTER', 'Pembayaran carter '.$label.' diperbarui', (string) ($row->payment_status ?? ''), $paymentStatus, [
                'charter_id' => $id,
                'down_payment_before' => (float) ($row->down_payment ?? 0),
                'down_payment_after' => (float) ($payload['down_payment'] ?? $row->down_payment ?? 0),
            ]);
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Pembayaran carter diperbarui.',
            'payment_status' => $paymentStatus,
            'source' => 'charter',
            'id' => $id,
            'label' => $label,
            'before' => (string) ($row->payment_status ?? ''),
            'after' => $paymentStatus,
            'down_payment_before' => (float) ($row->down_payment ?? 0),
            'down_payment_after' => (float) ($payload['down_payment'] ?? $row->down_payment ?? 0),
            'log_tag' => 'CHARTER',
            'log_title' => 'Pembayaran carter '.$label.' diperbarui',
            'log_before' => (string) ($row->payment_status ?? ''),
            'log_after' => $paymentStatus,
            'log_extra' => [
                'charter_id' => $id,
                'down_payment_before' => (float) ($row->down_payment ?? 0),
                'down_payment_after' => (float) ($payload['down_payment'] ?? $row->down_payment ?? 0),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function updateLuggagePaymentRecord(int $id, string $paymentStatus, bool $writeLog = true): array
    {
        $hasPoolId = Schema::hasColumn('luggages', 'pool_id');
        $hasRouteId = Schema::hasColumn('luggages', 'rute_id');
        $query = DB::table('luggages as l')->where('l.id', $id);
        PoolScope::applyPoolOrRouteScope(
            $query,
            $hasPoolId ? 'l.pool_id' : '',
            $hasRouteId ? 'l.rute_id' : '',
            'l.rute',
        );
        $this->applyTenantScopeIfExists($query, 'luggages', 'l');

        $row = $query->first(['l.id', 'l.kode_resi', 'l.sender_name', 'l.payment_status']);
        if (! $row) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Data bagasi tidak ditemukan atau di luar akses pool.',
                'reason' => 'not_found',
                'source' => 'luggage',
                'id' => $id,
            ];
        }

        DB::table('luggages')->where('id', $id)->update([
            'payment_status' => $paymentStatus,
        ]);

        $label = trim((string) ($row->kode_resi ?? '')) ?: '#'.$id;
        if ($writeLog) {
            $this->writePaymentLog('BAGASI', 'Pembayaran bagasi '.$label.' diperbarui', (string) ($row->payment_status ?? ''), $paymentStatus, [
                'luggage_id' => $id,
                'sender_name' => (string) ($row->sender_name ?? ''),
            ]);
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Pembayaran bagasi diperbarui.',
            'payment_status' => $paymentStatus,
            'source' => 'luggage',
            'id' => $id,
            'label' => $label,
            'before' => (string) ($row->payment_status ?? ''),
            'after' => $paymentStatus,
            'log_tag' => 'BAGASI',
            'log_title' => 'Pembayaran bagasi '.$label.' diperbarui',
            'log_before' => (string) ($row->payment_status ?? ''),
            'log_after' => $paymentStatus,
            'log_extra' => [
                'luggage_id' => $id,
                'sender_name' => (string) ($row->sender_name ?? ''),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findTripAssignmentForPayment(string $rute, string $tanggal, string $jam, int $unit): ?object
    {
        if (! Schema::hasTable('trip_assignments')) {
            return null;
        }

        $targetRoute = $this->normalizeRouteName($rute);
        $query = DB::table('trip_assignments as t')
            ->where('tanggal', $tanggal)
            ->where('t.jam', $this->normalizeTime($jam))
            ->where('t.unit', $unit);

        PoolScope::applyRouteIdentity(
            $query,
            $rute,
            Schema::hasColumn('trip_assignments', 'route_id') ? 't.route_id' : '',
            't.rute',
        );
        PoolScope::applyRouteScope(
            $query,
            Schema::hasColumn('trip_assignments', 'route_id') ? 't.route_id' : '',
            't.rute',
        );
        $this->applyTenantScopeIfExists($query, 'trip_assignments', 't');

        $rows = $query->get([
            't.id',
            't.rute',
            't.tanggal',
            't.jam',
            't.unit',
            Schema::hasColumn('trip_assignments', 'status') ? 't.status' : DB::raw("'active' as status"),
        ]);

        return $rows->first(fn ($row) => $this->normalizeRouteName((string) ($row->rute ?? '')) === $targetRoute);
    }

    private function manifestTripIsLocked(?object $assignment): bool
    {
        if (! $assignment) {
            return false;
        }

        return in_array(
            ManifestLifecycle::syncTripAssignmentStatus($assignment),
            ['canceled', 'closed'],
            true,
        );
    }

    private function normalizeBulkPaymentStatus(string $status): string
    {
        $normalized = mb_strtolower(trim($status));

        if (in_array($normalized, ['paid', 'lunas'], true)) {
            return 'Lunas';
        }

        if (in_array($normalized, ['dp', 'down payment'], true)) {
            return 'DP';
        }

        if (in_array($normalized, ['belum bayar', 'unpaid'], true)) {
            return 'Belum Bayar';
        }

        return 'Belum Lunas';
    }

    private function normalizeRouteName(string $value): string
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
        $normalized = str_replace([' => ', ' -> ', ' â†’ ', ' â€“ ', ' â€” ', ' - '], ' TO ', $normalized);
        $normalized = str_replace(['=>', '->', 'â†’', 'â€“', 'â€”'], ' TO ', $normalized);
        $normalized = preg_replace('/\s*-\s*/', ' TO ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function normalizeTime(string $time): string
    {
        $value = trim($time);

        if (preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value.':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }

        return substr($value, 0, 8);
    }

    private function canonicalPaymentStatus(string $source, string $status): string
    {
        $normalized = mb_strtolower(trim($status));

        if (in_array($normalized, ['paid', 'lunas'], true)) {
            return 'Lunas';
        }

        if (in_array($normalized, ['dp', 'down payment'], true)) {
            return 'DP';
        }

        return $source === 'luggage' ? 'Belum Bayar' : 'Belum Lunas';
    }

    private function statusLabel(string $status, string $source): string
    {
        return match ($status) {
            'paid' => 'Lunas',
            'dp' => 'DP',
            default => $source === 'luggage' ? 'Belum Bayar' : 'Belum Lunas',
        };
    }

    private function sourceLabel(string $source): string
    {
        return match ($source) {
            'booking' => 'Data Keberangkatan',
            'charter' => 'Carter',
            'luggage' => 'Bagasi',
            default => 'Pembayaran',
        };
    }

    private function canViewSource(string $source): bool
    {
        $userId = (int) (auth()->id() ?? 0);
        $viewPermission = match ($source) {
            'booking' => 'booking.view',
            'charter' => 'charter.view',
            'luggage' => 'luggage.view',
            default => '',
        };

        return AccessControl::can($userId, 'payment.update')
            || AccessControl::can($userId, $viewPermission)
            || $this->canUpdateSource($source);
    }

    private function canUpdateSource(string $source): bool
    {
        $userId = (int) (auth()->id() ?? 0);
        $updatePermission = match ($source) {
            'booking' => 'booking.update',
            'charter' => 'charter.update',
            'luggage' => 'luggage.update',
            default => '',
        };

        return AccessControl::can($userId, 'payment.update') || AccessControl::can($userId, $updatePermission);
    }

    private function resolvePoolName(int $poolId, int $routeId, string $routeLabel, string $routeSecondary = ''): string
    {
        $cacheKey = implode('|', [$poolId, $routeId, $this->normalizeLabel($routeLabel), $this->normalizeLabel($routeSecondary)]);
        if (array_key_exists($cacheKey, $this->poolNameCache)) {
            return $this->poolNameCache[$cacheKey];
        }

        if ($poolId > 0 && Schema::hasTable('pools')) {
            $poolQuery = DB::table('pools')->where('id', $poolId);
            $this->applyTenantScopeIfExists($poolQuery, 'pools');
            $name = trim((string) ($poolQuery->value('name') ?? ''));
            if ($name !== '') {
                return $this->poolNameCache[$cacheKey] = $name;
            }
        }

        if ($routeId > 0 && PoolScope::tablesReady()) {
            $poolRouteQuery = DB::table('pool_route as pr')
                ->join('pools as p', 'pr.pool_id', '=', 'p.id')
                ->where('pr.route_id', $routeId);
            $this->applyTenantScopeIfExists($poolRouteQuery, 'pools', 'p');
            $name = trim((string) ($poolRouteQuery->value('p.name') ?? ''));
            if ($name !== '') {
                return $this->poolNameCache[$cacheKey] = $name;
            }
        }

        $labels = array_filter([
            $this->normalizeLabel($routeLabel),
            $this->normalizeLabel($routeSecondary),
            $this->normalizeLabel($routeLabel.' - '.$routeSecondary),
        ]);

        if ($labels !== [] && PoolScope::tablesReady()) {
            $routes = DB::table('pool_route as pr')
                ->join('routes as r', 'pr.route_id', '=', 'r.id')
                ->join('pools as p', 'pr.pool_id', '=', 'p.id');
            $this->applyTenantScopeIfExists($routes, 'pools', 'p');
            $this->applyTenantScopeIfExists($routes, 'routes', 'r');
            $routes = $routes->get(['p.name', 'r.name as route_name', 'r.origin', 'r.destination']);

            foreach ($routes as $route) {
                $routeLabels = [
                    $this->normalizeLabel((string) ($route->route_name ?? '')),
                    $this->normalizeLabel((string) ($route->origin ?? '')),
                    $this->normalizeLabel((string) ($route->destination ?? '')),
                    $this->normalizeLabel(trim((string) ($route->origin ?? '')).' - '.trim((string) ($route->destination ?? ''))),
                ];

                if (array_intersect($labels, array_filter($routeLabels)) !== []) {
                    return $this->poolNameCache[$cacheKey] = (string) ($route->name ?? 'Pool');
                }
            }
        }

        $scope = PoolScope::forCurrentUser();
        $fallback = (string) ($scope['pool_name'] ?? 'Semua Pool');

        return $this->poolNameCache[$cacheKey] = $fallback;
    }

    private function normalizeLabel(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value))) ?? '';
    }

    private function applyTenantScopeIfExists(Builder $query, string $table, string $alias = ''): void
    {
        if (! Schema::hasColumn($table, 'tenant_id')) {
            return;
        }

        $prefix = $alias !== '' ? $alias.'.' : '';
        PoolScope::applyTenantScope($query, $prefix.'tenant_id');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function writePaymentLog(string $tag, string $title, string $before, string $after, array $extra): void
    {
        ActivityLog::write(
            $tag,
            $title,
            'Pembayaran: '.($before !== '' ? $before : '-').' -> '.$after,
            (string) (auth()->user()?->email ?? auth()->user()?->name ?? 'system'),
            $extra,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ok(array $payload = [], int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, ...$payload], $status);
    }

    private function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['success' => false, 'error' => $message], $status);
    }
}
