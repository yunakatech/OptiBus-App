<?php

namespace App\Services;

use App\Support\SchemaCache;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantDeletionService
{
    private const BATCH_SIZE = 75;

    /**
     * @var array<int, string>
     */
    private const RELATION_STEPS = [
        'cancellations',
        'bagasi_logs',
        'luggage_incidents',
        'schedule_segment',
        'schedule_units',
        'pool_monthly_targets',
        'pool_user',
        'user_role',
        'pool_route',
    ];

    /**
     * Tables directly scoped by tenant_id. The order is deliberately child-first.
     *
     * @var array<int, string>
     */
    private const DIRECT_STEPS = [
        'activity_logs',
        'luggage_segment_rates',
        'tenant_invitations',
        'invoice_subscriptions',
        'subscriptions',
        'bookings',
        'trip_assignments',
        'charters',
        'luggages',
        'customer_bagasi',
        'customer_charter',
        'customers',
        'segments',
        'schedules',
        'routes',
        'drivers',
        'armadas',
        'units',
        'master_carter',
        'luggage_services',
        'pools',
        'users',
    ];

    /**
     * @return array<string, mixed>
     */
    public function preview(int $tenantId): array
    {
        $tenant = $this->tenant($tenantId, true);
        if (! $tenant) {
            throw new \RuntimeException('Tenant tidak ditemukan.');
        }

        $counts = [];
        foreach (self::DIRECT_STEPS as $table) {
            $counts[$table] = $this->countDirect($table, $tenantId);
        }

        $counts['schedule_units'] = $this->countRelated('schedule_units', 'schedule_id', 'schedules', $tenantId);
        $counts['schedule_segment'] = $this->countScheduleSegments($tenantId);
        $counts['cancellations'] = $this->countRelated('cancellations', 'booking_id', 'bookings', $tenantId);
        $counts['bagasi_logs'] = $this->countRelated('bagasi_logs', 'kode_resi', 'luggages', $tenantId, 'kode_resi');
        $counts['pool_monthly_targets'] = $this->countRelated('pool_monthly_targets', 'pool_id', 'pools', $tenantId);
        $counts['pool_route'] = $this->countPoolRoutes($tenantId);
        $counts['pool_user'] = $this->countPoolUsers($tenantId);
        $counts['user_role'] = $this->countUserRoles($tenantId);

        $counts['total'] = array_sum($counts);

        return [
            'tenant' => [
                'id' => $tenantId,
                'name' => (string) ($tenant->name ?? ''),
                'slug' => (string) ($tenant->slug ?? ''),
                'status' => (string) ($tenant->status ?? ''),
            ],
            'counts' => $counts,
            'active_job' => $this->activeJob($tenantId),
        ];
    }

    public function archive(int $tenantId, int $actorId): object
    {
        return DB::transaction(function () use ($tenantId, $actorId): object {
            $tenant = $this->tenant($tenantId, true);
            if (! $tenant) {
                throw new \RuntimeException('Tenant tidak ditemukan.');
            }

            if ($this->activeJob($tenantId)) {
                throw new \RuntimeException('Tenant sedang dalam proses penghapusan.');
            }

            DB::table('tenants')->where('id', $tenantId)->update([
                'status' => 'canceled',
                'updated_at' => now(),
            ]);

            if ($this->table('subscriptions')) {
                DB::table('subscriptions')
                    ->where('tenant_id', $tenantId)
                    ->whereIn('status', ['pending_payment', 'trial', 'active', 'past_due'])
                    ->update([
                        'status' => 'canceled',
                        'canceled_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            $this->platformAudit('tenant.archived', "Tenant '{$tenant->name}' diarsipkan.", $actorId, [
                'target_tenant_id' => $tenantId,
                'target_tenant_slug' => (string) ($tenant->slug ?? ''),
            ]);

            return $tenant;
        });
    }

    public function createPurgeJob(int $tenantId, int $actorId): object
    {
        if (! $this->table('tenant_deletion_jobs')) {
            throw new \RuntimeException(
                'Tabel tenant_deletion_jobs belum tersedia. Jalankan php artisan migrate --force pada database production lalu coba lagi.',
            );
        }

        return DB::transaction(function () use ($tenantId, $actorId): object {
            $tenant = $this->tenant($tenantId, true);
            if (! $tenant) {
                throw new \RuntimeException('Tenant tidak ditemukan.');
            }

            if ($this->activeJob($tenantId)) {
                throw new \RuntimeException('Tenant sedang dalam proses penghapusan.');
            }

            $preview = $this->preview($tenantId);
            $jobId = DB::table('tenant_deletion_jobs')->insertGetId([
                'tenant_id' => $tenantId,
                'tenant_name' => (string) ($tenant->name ?? ''),
                'tenant_slug' => (string) ($tenant->slug ?? ''),
                'requested_by_user_id' => $actorId > 0 ? $actorId : null,
                'mode' => 'purge_all',
                'status' => 'queued',
                'current_step' => 'relations.cancellations',
                'progress_percent' => 0,
                'counts' => json_encode($preview['counts'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'cursor' => json_encode(['step' => 'relations.cancellations', 'last_id' => 0], JSON_UNESCAPED_SLASHES),
                'deleted_counts' => json_encode([], JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tenants')->where('id', $tenantId)->update([
                'status' => 'deleting',
                'updated_at' => now(),
            ]);

            $this->platformAudit('tenant.purge_requested', "Purge tenant '{$tenant->name}' diminta.", $actorId, [
                'target_tenant_id' => $tenantId,
                'target_tenant_slug' => (string) ($tenant->slug ?? ''),
                'deletion_job_id' => (int) $jobId,
            ]);

            return DB::table('tenant_deletion_jobs')->where('id', $jobId)->first();
        });
    }

    /**
     * Process one bounded batch. The authenticated admin status poll calls
     * this method repeatedly so purge does not depend on a scheduler.
     *
     * @return array<string, mixed>
     */
    public function processNextBatch(?int $jobId = null): array
    {
        $currentJobId = $jobId;

        try {
            return DB::transaction(function () use ($jobId, &$currentJobId): array {
                $jobQuery = DB::table('tenant_deletion_jobs')
                    ->whereIn('status', ['queued', 'running'])
                    ->orderBy('id');
                if ($jobId !== null && $jobId > 0) {
                    $jobQuery->where('id', $jobId);
                }

                $job = $jobQuery->lockForUpdate()->first();
                if (! $job) {
                    return ['processed' => false, 'message' => 'Tidak ada purge job yang perlu diproses.'];
                }
                $currentJobId = (int) $job->id;

                if (! $this->tenant((int) $job->tenant_id, true)) {
                    DB::table('tenant_deletion_jobs')->where('id', $job->id)->update([
                        'status' => 'completed',
                        'current_step' => 'tenant_missing',
                        'progress_percent' => 100,
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return ['processed' => true, 'status' => 'completed', 'job_id' => (int) $job->id];
                }

                $this->markRunning($job);
                $state = $this->decodeState($job);
                $step = (string) ($state['step'] ?? 'relations');
                $lastId = (int) ($state['last_id'] ?? 0);

                if (str_starts_with($step, 'relations.')) {
                    $relationStep = substr($step, strlen('relations.'));
                    $deleted = $this->deleteRelationStep((int) $job->tenant_id, $relationStep, $lastId);
                    if ($deleted['count'] === 0 && ! $deleted['remaining']) {
                        $nextState = $this->nextRelationState($relationStep);
                        DB::table('tenant_deletion_jobs')->where('id', $job->id)->update([
                            'current_step' => $nextState['step'],
                            'progress_percent' => $this->progressFor((int) $job->tenant_id, $nextState['step']),
                            'cursor' => json_encode($nextState, JSON_UNESCAPED_SLASHES),
                            'updated_at' => now(),
                        ]);

                        return [
                            'processed' => true,
                            'status' => 'running',
                            'job_id' => (int) $job->id,
                            'current_step' => $nextState['step'],
                            'progress_percent' => $this->progressFor((int) $job->tenant_id, $nextState['step']),
                            'deleted_count' => 0,
                        ];
                    }

                    $nextState = [
                        'step' => $step,
                        'last_id' => $deleted['last_id'],
                    ];
                } elseif ($step === 'tenant') {
                    $this->finishTenant((int) $job->tenant_id);
                    DB::table('tenant_deletion_jobs')->where('id', $job->id)->update([
                        'status' => 'completed',
                        'current_step' => 'completed',
                        'progress_percent' => 100,
                        'cursor' => json_encode(['step' => 'completed', 'last_id' => 0]),
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->platformAudit('tenant.purge_completed', "Purge tenant '{$job->tenant_name}' selesai.", (int) $job->requested_by_user_id, [
                        'target_tenant_id' => (int) $job->tenant_id,
                        'target_tenant_slug' => (string) $job->tenant_slug,
                        'deletion_job_id' => (int) $job->id,
                    ]);

                    return ['processed' => true, 'status' => 'completed', 'job_id' => (int) $job->id];
                } else {
                    $deleted = $this->deleteDirectBatch((int) $job->tenant_id, $step, $lastId);
                    $nextState = $deleted['remaining']
                        ? ['step' => $step, 'last_id' => $deleted['last_id']]
                        : $this->nextState($step);
                }

                $progress = $this->progressFor((int) $job->tenant_id, $nextState['step']);
                $deletedCounts = $this->decodeJson($job->deleted_counts);
                $deletedCounts[$deleted['label']] = (int) ($deletedCounts[$deleted['label']] ?? 0) + $deleted['count'];

                DB::table('tenant_deletion_jobs')->where('id', $job->id)->update([
                    'current_step' => $nextState['step'],
                    'progress_percent' => $progress,
                    'cursor' => json_encode($nextState, JSON_UNESCAPED_SLASHES),
                    'deleted_counts' => json_encode($deletedCounts, JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);

                return [
                    'processed' => true,
                    'status' => 'running',
                    'job_id' => (int) $job->id,
                    'current_step' => $nextState['step'],
                    'progress_percent' => $progress,
                    'deleted_count' => $deleted['count'],
                ];
            });
        } catch (\Throwable $e) {
            if ($currentJobId !== null && $currentJobId > 0 && $this->table('tenant_deletion_jobs')) {
                DB::table('tenant_deletion_jobs')->where('id', $currentJobId)->update([
                    'status' => 'failed',
                    'error_message' => Str::limit($e->getMessage(), 4000),
                    'updated_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    public function job(int $jobId): ?object
    {
        return $this->table('tenant_deletion_jobs')
            ? DB::table('tenant_deletion_jobs')->where('id', $jobId)->first()
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function jobPayload(object $job): array
    {
        return [
            'id' => (int) ($job->id ?? 0),
            'tenant_id' => $job->tenant_id !== null ? (int) $job->tenant_id : null,
            'tenant_name' => (string) ($job->tenant_name ?? ''),
            'tenant_slug' => (string) ($job->tenant_slug ?? ''),
            'status' => (string) ($job->status ?? 'queued'),
            'mode' => (string) ($job->mode ?? 'purge_all'),
            'current_step' => (string) ($job->current_step ?? ''),
            'progress_percent' => (int) ($job->progress_percent ?? 0),
            'counts' => $this->decodeJson($job->counts ?? null),
            'deleted_counts' => $this->decodeJson($job->deleted_counts ?? null),
            'error_message' => $job->error_message,
            'started_at' => $job->started_at,
            'completed_at' => $job->completed_at,
            'created_at' => $job->created_at,
        ];
    }

    public function retry(int $jobId): ?object
    {
        if (! $this->job($jobId)) {
            return null;
        }

        DB::table('tenant_deletion_jobs')->where('id', $jobId)->where('status', 'failed')->update([
            'status' => 'queued',
            'error_message' => null,
            'updated_at' => now(),
        ]);

        return $this->job($jobId);
    }

    private function deleteRelationStep(int $tenantId, string $step, int $lastId): array
    {
        $query = null;
        $label = $step;

        if ($step === 'cancellations') {
            $query = $this->relationQuery('cancellations', 'booking_id', 'bookings', $tenantId, $lastId);
        } elseif ($step === 'bagasi_logs') {
            $query = $this->relationQuery('bagasi_logs', 'kode_resi', 'luggages', $tenantId, $lastId, 'kode_resi');
        } elseif ($step === 'luggage_incidents') {
            $query = $this->table('luggage_incidents')
                ? DB::table('luggage_incidents')->where('id', '>', $lastId)->where(function (Builder $q) use ($tenantId): void {
                    $q->where('tenant_id', $tenantId);
                    if ($this->column('luggage_incidents', 'luggage_id')) {
                        $q->orWhereIn('luggage_id', $this->idsQuery('luggages', $tenantId));
                    }
                })
                : null;
        } elseif ($step === 'schedule_segment') {
            $query = $this->table('schedule_segment')
                ? DB::table('schedule_segment')->where('id', '>', $lastId)->where(function (Builder $q) use ($tenantId): void {
                    $q->whereIn('schedule_id', $this->idsQuery('schedules', $tenantId))
                        ->orWhereIn('segment_id', $this->idsQuery('segments', $tenantId));
                })
                : null;
        } elseif ($step === 'schedule_units') {
            $query = $this->relationQuery('schedule_units', 'schedule_id', 'schedules', $tenantId, $lastId);
        } elseif ($step === 'pool_monthly_targets') {
            $query = $this->relationQuery('pool_monthly_targets', 'pool_id', 'pools', $tenantId, $lastId);
        } elseif ($step === 'pool_user') {
            $query = $this->table('pool_user')
                ? DB::table('pool_user')->where('id', '>', $lastId)->where(function (Builder $q) use ($tenantId): void {
                    $q->whereIn('pool_id', $this->idsQuery('pools', $tenantId))
                        ->orWhereIn('user_id', $this->idsQuery('users', $tenantId));
                })
                : null;
        } elseif ($step === 'user_role') {
            $query = $this->relationQuery('user_role', 'user_id', 'users', $tenantId, $lastId);
        } elseif ($step === 'pool_route') {
            $query = $this->table('pool_route')
                ? DB::table('pool_route')->where('id', '>', $lastId)->where(function (Builder $q) use ($tenantId): void {
                    $q->whereIn('pool_id', $this->idsQuery('pools', $tenantId))
                        ->orWhereIn('route_id', $this->idsQuery('routes', $tenantId));
                })
                : null;
        }

        if (! $query) {
            return ['label' => $label, 'count' => 0, 'last_id' => 0, 'remaining' => false];
        }

        return $this->deleteQueryBatch($query, $label);
    }

    private function deleteDirectBatch(int $tenantId, string $step, int $lastId): array
    {
        if ($step === 'completed') {
            return ['label' => $step, 'count' => 0, 'last_id' => 0, 'remaining' => false];
        }

        $query = $this->table($step) && $this->column($step, 'tenant_id')
            ? DB::table($step)->where('tenant_id', $tenantId)->where('id', '>', $lastId)
            : null;

        if (! $query) {
            return ['label' => $step, 'count' => 0, 'last_id' => 0, 'remaining' => false];
        }

        return $this->deleteQueryBatch($query, $step);
    }

    private function deleteQueryBatch(Builder $query, string $label): array
    {
        $ids = $query->orderBy('id')->limit(self::BATCH_SIZE)->pluck('id')->map(fn ($id): int => (int) $id)->all();
        if ($ids === []) {
            return ['label' => $label, 'count' => 0, 'last_id' => 0, 'remaining' => false];
        }

        $table = $query->from;
        $count = DB::table($table)->whereIn('id', $ids)->delete();
        $lastId = (int) end($ids);
        $remainingQuery = (clone $query)->cloneWithout(['orders', 'limit', 'offset']);
        $remaining = $remainingQuery->where('id', '>', $lastId)->exists();

        return [
            'label' => $label,
            'count' => $count,
            'last_id' => $lastId,
            'remaining' => $remaining,
        ];
    }

    private function finishTenant(int $tenantId): void
    {
        foreach (self::DIRECT_STEPS as $table) {
            if ($this->countDirect($table, $tenantId) > 0) {
                throw new \RuntimeException("Data tenant masih tersisa pada tabel {$table}.");
            }
        }

        if ($this->preview($tenantId)['counts']['total'] > 0) {
            throw new \RuntimeException('Relasi tenant masih tersisa. Purge akan dilanjutkan pada batch berikutnya.');
        }

        DB::table('tenants')->where('id', $tenantId)->delete();
    }

    /**
     * @return array{step: string, last_id: int}
     */
    private function nextState(string $step): array
    {
        if ($step === 'relations') {
            return ['step' => 'relations.'.self::RELATION_STEPS[0], 'last_id' => 0];
        }

        $index = array_search($step, self::DIRECT_STEPS, true);
        if ($index === false || ! isset(self::DIRECT_STEPS[$index + 1])) {
            return ['step' => 'tenant', 'last_id' => 0];
        }

        return ['step' => self::DIRECT_STEPS[$index + 1], 'last_id' => 0];
    }

    /**
     * @return array{step: string, last_id: int}
     */
    private function nextRelationState(string $step): array
    {
        $index = array_search($step, self::RELATION_STEPS, true);
        if ($index === false || ! isset(self::RELATION_STEPS[$index + 1])) {
            return ['step' => self::DIRECT_STEPS[0], 'last_id' => 0];
        }

        return ['step' => 'relations.'.self::RELATION_STEPS[$index + 1], 'last_id' => 0];
    }

    private function progressFor(int $tenantId, string $step): int
    {
        if ($step === 'tenant') {
            return 99;
        }

        $total = count(self::DIRECT_STEPS) + 1;
        if ($step === 'relations' || str_starts_with($step, 'relations.')) {
            return 1;
        }

        $index = array_search($step, self::DIRECT_STEPS, true);

        return max(1, min(98, (int) floor((($index + 1) / $total) * 100)));
    }

    private function markRunning(object $job): void
    {
        if ($job->status === 'running') {
            return;
        }

        DB::table('tenant_deletion_jobs')->where('id', $job->id)->update([
            'status' => 'running',
            'started_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function relationQuery(string $table, string $column, string $parentTable, int $tenantId, int $lastId, ?string $parentColumn = null): ?Builder
    {
        if (! $this->table($table) || ! $this->table($parentTable)) {
            return null;
        }

        $parentColumn ??= 'id';
        if (! $this->column($parentTable, 'tenant_id') || ! $this->column($table, $column)) {
            return null;
        }

        return DB::table($table)
            ->where('id', '>', $lastId)
            ->whereIn($column, $this->idsQuery($parentTable, $tenantId, $parentColumn));
    }

    private function idsQuery(string $table, int $tenantId, string $column = 'id'): Builder
    {
        return DB::table($table)->where('tenant_id', $tenantId)->select($column);
    }

    private function countDirect(string $table, int $tenantId): int
    {
        return $this->table($table) && $this->column($table, 'tenant_id')
            ? (int) DB::table($table)->where('tenant_id', $tenantId)->count()
            : 0;
    }

    private function countRelated(string $table, string $column, string $parentTable, int $tenantId, ?string $parentColumn = null): int
    {
        $query = $this->relationQuery($table, $column, $parentTable, $tenantId, 0, $parentColumn);

        return $query ? (int) $query->count() : 0;
    }

    private function countScheduleSegments(int $tenantId): int
    {
        if (! $this->table('schedule_segment')) {
            return 0;
        }

        return (int) DB::table('schedule_segment')->where(function (Builder $q) use ($tenantId): void {
            $q->whereIn('schedule_id', $this->idsQuery('schedules', $tenantId))
                ->orWhereIn('segment_id', $this->idsQuery('segments', $tenantId));
        })->count();
    }

    private function countPoolRoutes(int $tenantId): int
    {
        if (! $this->table('pool_route')) {
            return 0;
        }

        return (int) DB::table('pool_route')->where(function (Builder $q) use ($tenantId): void {
            $q->whereIn('pool_id', $this->idsQuery('pools', $tenantId))
                ->orWhereIn('route_id', $this->idsQuery('routes', $tenantId));
        })->count();
    }

    private function countPoolUsers(int $tenantId): int
    {
        if (! $this->table('pool_user')) {
            return 0;
        }

        return (int) DB::table('pool_user')->where(function (Builder $q) use ($tenantId): void {
            $q->whereIn('pool_id', $this->idsQuery('pools', $tenantId))
                ->orWhereIn('user_id', $this->idsQuery('users', $tenantId));
        })->count();
    }

    private function countUserRoles(int $tenantId): int
    {
        return $this->table('user_role')
            ? (int) DB::table('user_role')->whereIn('user_id', $this->idsQuery('users', $tenantId))->count()
            : 0;
    }

    private function tenant(int $tenantId, bool $withDeleting = false): ?object
    {
        if (! $this->table('tenants')) {
            return null;
        }

        $query = DB::table('tenants')->where('id', $tenantId);
        if (! $withDeleting) {
            $query->where('status', '!=', 'deleting');
        }

        return $query->first();
    }

    private function activeJob(int $tenantId): ?object
    {
        if (! $this->table('tenant_deletion_jobs')) {
            return null;
        }

        return DB::table('tenant_deletion_jobs')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['queued', 'running'])
            ->latest('id')
            ->first();
    }

    private function table(string $table): bool
    {
        return SchemaCache::hasTable($table);
    }

    private function column(string $table, string $column): bool
    {
        return SchemaCache::hasColumn($table, $column);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeState(object $job): array
    {
        $state = $this->decodeJson($job->cursor ?? null);

        return [
            'step' => (string) ($state['step'] ?? 'relations'),
            'last_id' => (int) ($state['last_id'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function platformAudit(string $tag, string $title, int $actorId, array $extra): void
    {
        if (! $this->table('activity_logs')) {
            return;
        }

        $extra['platform_audit'] = true;
        $extra['actor_user_id'] = $actorId > 0 ? $actorId : null;
        DB::table('activity_logs')->insert([
            'tag' => strtoupper($tag),
            'title' => $title,
            'meta' => '',
            'actor' => (string) $actorId,
            'extra' => json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
        ] + ($this->column('activity_logs', 'tenant_id') ? ['tenant_id' => null] : []));
    }
}
