<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingScheduleBackfill
{
    private ?bool $schedulesHasRouteId = null;
    private ?bool $schedulesHasSeatsColumn = null;

    private ?bool $scheduleUnitsTableExists = null;

    /**
     * @return array<string, mixed>
     */
    public function run(bool $write = false, ?string $routeFilter = null, ?string $jamFilter = null): array
    {
        if (! Schema::hasTable('bookings') || ! Schema::hasTable('schedules') || ! Schema::hasTable('units')) {
            return [
                'write' => $write,
                'created' => 0,
                'skipped' => 0,
                'unresolved' => 0,
                'items' => [],
                'message' => 'Tabel inti booking/schedules/units belum lengkap.',
            ];
        }

        $templates = $this->loadUnitTemplates();
        $bookings = $this->loadBookingRows($routeFilter, $jamFilter);
        $scheduleContext = $this->loadScheduleContext();
        $assignmentContext = $this->loadAssignmentContext($routeFilter, $jamFilter);

        $items = [];
        $created = 0;
        $skipped = 0;
        $unresolved = 0;

        foreach ($bookings['groups'] as $groupKey => $group) {
            $routeNorm = (string) ($group['route_norm'] ?? '');
            $routeName = $this->canonicalRouteName($routeNorm, (array) ($group['route_variants'] ?? []), (array) ($scheduleContext['route_names'] ?? []));
            $dow = (int) ($group['dow'] ?? 0);
            $jam = (string) ($group['jam'] ?? '');
            $scheduleKey = $this->scheduleKey($routeNorm, $dow, $jam);

            if (isset($scheduleContext['existing'][$scheduleKey])) {
                $skipped += 1;
                $items[] = [
                    'status' => 'exists',
                    'route' => $routeName,
                    'dow' => $dow,
                    'jam' => $jam,
                    'units' => (int) ($group['max_unit'] ?? 1),
                    'message' => 'Jadwal sudah ada.',
                ];
                continue;
            }

            $unitsCount = max(1, (int) ($group['max_unit'] ?? 1));
            $sameTimeTemplate = $scheduleContext['same_time'][$this->sameTimeKey($routeNorm, $jam)] ?? null;
            $resolvedUnits = [];
            $warnings = [];

            for ($unitNo = 1; $unitNo <= $unitsCount; $unitNo += 1) {
                $resolved = $this->resolveTemplateForUnit(
                    $routeNorm,
                    $jam,
                    $unitNo,
                    $group,
                    $sameTimeTemplate,
                    $templates,
                    $assignmentContext,
                    $resolvedUnits[1] ?? null,
                );

                $resolvedUnits[$unitNo] = $resolved;
                if (($resolved['warning'] ?? '') !== '') {
                    $warnings[] = $resolved['warning'];
                }
            }

            if (empty($resolvedUnits[1]['unit_id'])) {
                $unresolved += 1;
                $items[] = [
                    'status' => 'unresolved',
                    'route' => $routeName,
                    'dow' => $dow,
                    'jam' => $jam,
                    'units' => $unitsCount,
                    'message' => implode(' ', array_filter(array_unique($warnings))) ?: 'Template unit pertama belum bisa diinfer otomatis.',
                ];
                continue;
            }

            $scheduleId = null;
            if ($write) {
                $scheduleId = DB::transaction(function () use ($routeNorm, $routeName, $dow, $jam, $unitsCount, $resolvedUnits, $scheduleContext) {
                    $routeId = $this->resolveRouteId($routeNorm, (array) ($scheduleContext['route_ids'] ?? []));
                    $primary = $resolvedUnits[1];

                    $payload = [
                        'rute' => $routeName,
                        'dow' => $dow,
                        'jam' => $jam.':00',
                        'units' => $unitsCount,
                        'unit_label' => $this->nullableString((string) ($primary['label'] ?? "Unit 1")),
                        'unit_id' => (int) ($primary['unit_id'] ?? 0) > 0 ? (int) $primary['unit_id'] : null,
                        'layout' => $this->nullableString((string) ($primary['layout_json'] ?? '')),
                        'created_at' => now(),
                    ];
                    if ($this->hasSchedulesSeatsColumn()) {
                        $payload['seats'] = (int) ($primary['seats'] ?? 0);
                    }

                    if ($this->hasSchedulesRouteId()) {
                        $payload['route_id'] = $routeId > 0 ? $routeId : null;
                    }

                    $newScheduleId = (int) DB::table('schedules')->insertGetId($payload);

                    if ($this->hasScheduleUnitsTable()) {
                        $rows = [];
                        foreach ($resolvedUnits as $unitNo => $resolved) {
                            $rows[] = [
                                'schedule_id' => $newScheduleId,
                                'unit_no' => (int) $unitNo,
                                'label' => $this->nullableString((string) ($resolved['label'] ?? "Unit {$unitNo}")) ?? "Unit {$unitNo}",
                                'unit_id' => (int) ($resolved['unit_id'] ?? 0) > 0 ? (int) $resolved['unit_id'] : null,
                                'created_at' => now(),
                            ];
                        }

                        if (! empty($rows)) {
                            DB::table('schedule_units')->insert($rows);
                        }
                    }

                    return $newScheduleId;
                });
            }

            $created += 1;
            $items[] = [
                'status' => $write ? 'created' : 'planned',
                'route' => $routeName,
                'dow' => $dow,
                'jam' => $jam,
                'units' => $unitsCount,
                'schedule_id' => $scheduleId,
                'unit_ids' => array_map(static fn ($item) => $item['unit_id'] ?? null, $resolvedUnits),
                'message' => implode(' ', array_filter(array_unique($warnings))) ?: 'Jadwal berhasil diinfer dari data lama.',
            ];
        }

        return [
            'write' => $write,
            'created' => $created,
            'skipped' => $skipped,
            'unresolved' => $unresolved,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadBookingRows(?string $routeFilter = null, ?string $jamFilter = null): array
    {
        $query = DB::table('bookings')
            ->where('status', '!=', 'canceled')
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->orderBy('unit')
            ->orderBy('seat');

        if ($routeFilter !== null && trim($routeFilter) !== '') {
            $query->whereRaw('UPPER(rute) = ?', [strtoupper(trim($routeFilter))]);
        }

        if ($jamFilter !== null && trim($jamFilter) !== '') {
            $jamValue = trim($jamFilter);
            if (preg_match('/^\d{2}:\d{2}$/', $jamValue)) {
                $jamValue .= ':00';
            }
            $query->where('jam', $jamValue);
        }

        $rows = $query->get(['rute', 'tanggal', 'jam', 'unit', 'seat']);

        $groups = [];
        foreach ($rows as $row) {
            $route = trim((string) ($row->rute ?? ''));
            $routeNorm = $this->routeKey($route);
            $jam = substr((string) ($row->jam ?? ''), 0, 5);
            $unit = max(1, (int) ($row->unit ?? 1));
            $seat = strtoupper(trim((string) ($row->seat ?? '')));
            $tanggal = (string) ($row->tanggal ?? '');
            if ($routeNorm === '' || $jam === '' || $tanggal === '') {
                continue;
            }

            $dow = Carbon::createFromFormat('Y-m-d', $tanggal)->dayOfWeek;
            $groupKey = $this->scheduleKey($routeNorm, $dow, $jam);

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'route_norm' => $routeNorm,
                    'route_variants' => [],
                    'dow' => $dow,
                    'jam' => $jam,
                    'max_unit' => 1,
                    'unit_stats' => [],
                ];
            }

            if (! in_array($route, $groups[$groupKey]['route_variants'], true)) {
                $groups[$groupKey]['route_variants'][] = $route;
            }

            $groups[$groupKey]['max_unit'] = max((int) $groups[$groupKey]['max_unit'], $unit);

            if (! isset($groups[$groupKey]['unit_stats'][$unit])) {
                $groups[$groupKey]['unit_stats'][$unit] = [
                    'seat_tokens' => [],
                    'max_numeric_seat' => 0,
                ];
            }

            if ($seat !== '') {
                $groups[$groupKey]['unit_stats'][$unit]['seat_tokens'][] = $seat;
                if (ctype_digit($seat)) {
                    $groups[$groupKey]['unit_stats'][$unit]['max_numeric_seat'] = max(
                        (int) $groups[$groupKey]['unit_stats'][$unit]['max_numeric_seat'],
                        (int) $seat,
                    );
                }
            }
        }

        return ['groups' => $groups];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadScheduleContext(): array
    {
        $rows = DB::table('schedules')
            ->orderBy('rute')
            ->orderBy('dow')
            ->orderBy('jam')
            ->get(['id', 'rute', 'dow', 'jam', 'units', 'unit_label', 'unit_id']);

        $optionsBySchedule = [];
        if ($this->hasScheduleUnitsTable()) {
            $scheduleIds = $rows->pluck('id')->map(static fn ($id) => (int) $id)->filter(static fn ($id) => $id > 0)->values()->all();
            if (! empty($scheduleIds)) {
                $optionRows = DB::table('schedule_units')
                    ->whereIn('schedule_id', $scheduleIds)
                    ->orderBy('schedule_id')
                    ->orderBy('unit_no')
                    ->get(['schedule_id', 'unit_no', 'label', 'unit_id']);

                foreach ($optionRows as $item) {
                    $scheduleId = (int) ($item->schedule_id ?? 0);
                    if ($scheduleId <= 0) {
                        continue;
                    }

                    if (! isset($optionsBySchedule[$scheduleId])) {
                        $optionsBySchedule[$scheduleId] = [];
                    }

                    $optionsBySchedule[$scheduleId][] = [
                        'unit_no' => (int) ($item->unit_no ?? 0),
                        'label' => (string) ($item->label ?? ''),
                        'unit_id' => $item->unit_id !== null ? (int) $item->unit_id : null,
                    ];
                }
            }
        }

        $existing = [];
        $sameTime = [];
        $routeNames = [];
        $routeIds = [];

        foreach ($rows as $row) {
            $routeName = trim((string) ($row->rute ?? ''));
            $routeNorm = $this->routeKey($routeName);
            $jam = substr((string) ($row->jam ?? ''), 0, 5);
            $dow = (int) ($row->dow ?? 0);
            $scheduleId = (int) ($row->id ?? 0);
            $options = $optionsBySchedule[$scheduleId] ?? [];

            $existing[$this->scheduleKey($routeNorm, $dow, $jam)] = true;
            if (! isset($routeNames[$routeNorm])) {
                $routeNames[$routeNorm] = $routeName;
            }

            $sameTimeKey = $this->sameTimeKey($routeNorm, $jam);
            if (! isset($sameTime[$sameTimeKey])) {
                $sameTime[$sameTimeKey] = [
                    'unit_id' => (int) ($row->unit_id ?? 0),
                    'unit_label' => (string) ($row->unit_label ?? ''),
                    'unit_options' => $options,
                    'units' => (int) ($row->units ?? 1),
                ];
            }
        }

        if ($this->hasSchedulesRouteId() && Schema::hasTable('routes')) {
            $routeRows = DB::table('routes')->get(['id', 'name']);
            foreach ($routeRows as $row) {
                $routeNorm = $this->routeKey((string) ($row->name ?? ''));
                if ($routeNorm === '') {
                    continue;
                }
                $routeIds[$routeNorm] = (int) ($row->id ?? 0);
                $routeNames[$routeNorm] = (string) ($row->name ?? '');
            }
        }

        return [
            'existing' => $existing,
            'same_time' => $sameTime,
            'route_names' => $routeNames,
            'route_ids' => $routeIds,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadAssignmentContext(?string $routeFilter = null, ?string $jamFilter = null): array
    {
        if (! Schema::hasTable('trip_assignments') || ! Schema::hasTable('armadas')) {
            return ['categories' => []];
        }

        $query = DB::table('trip_assignments as t')
            ->leftJoin('armadas as a', 't.armada_id', '=', 'a.id')
            ->select(['t.rute', 't.jam', 't.unit', DB::raw('a.kategori as armada_kategori')]);

        if ($routeFilter !== null && trim($routeFilter) !== '') {
            $query->whereRaw('UPPER(t.rute) = ?', [strtoupper(trim($routeFilter))]);
        }

        if ($jamFilter !== null && trim($jamFilter) !== '') {
            $jamValue = trim($jamFilter);
            if (preg_match('/^\d{2}:\d{2}$/', $jamValue)) {
                $jamValue .= ':00';
            }
            $query->where('t.jam', $jamValue);
        }

        $rows = $query->get();
        $categories = [];
        foreach ($rows as $row) {
            $routeNorm = $this->routeKey((string) ($row->rute ?? ''));
            $jam = substr((string) ($row->jam ?? ''), 0, 5);
            $unit = max(1, (int) ($row->unit ?? 1));
            $category = trim((string) ($row->armada_kategori ?? ''));
            if ($routeNorm === '' || $jam === '' || $category === '') {
                continue;
            }

            $key = $this->sameTimeUnitKey($routeNorm, $jam, $unit);
            if (! isset($categories[$key])) {
                $categories[$key] = [];
            }

            if (! in_array($category, $categories[$key], true)) {
                $categories[$key][] = $category;
            }
        }

        return ['categories' => $categories];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadUnitTemplates(): array
    {
        return DB::table('units')
            ->orderBy('id')
            ->get(['id', 'nopol', 'category', 'kapasitas', 'layout'])
            ->map(function ($row) {
                $layoutJson = trim((string) ($row->layout ?? ''));
                $decoded = $layoutJson !== '' ? json_decode($layoutJson, true) : [];
                $seatCount = is_array($decoded) ? $this->countSeatsFromLayout($decoded) : 0;
                if ($seatCount <= 0) {
                    $seatCount = max(0, (int) ($row->kapasitas ?? 0));
                }

                return [
                    'id' => (int) ($row->id ?? 0),
                    'nopol' => (string) ($row->nopol ?? ''),
                    'category' => trim((string) ($row->category ?? '')),
                    'kapasitas' => (int) ($row->kapasitas ?? 0),
                    'seats' => $seatCount,
                    'layout_json' => $layoutJson,
                ];
            })
            ->filter(static fn (array $row) => (int) ($row['id'] ?? 0) > 0)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<string, mixed>|null  $sameTimeTemplate
     * @param  array<int, array<string, mixed>>  $templates
     * @param  array<string, mixed>  $assignmentContext
     * @param  array<string, mixed>|null  $primaryResolved
     * @return array<string, mixed>
     */
    private function resolveTemplateForUnit(
        string $routeNorm,
        string $jam,
        int $unitNo,
        array $group,
        ?array $sameTimeTemplate,
        array $templates,
        array $assignmentContext,
        ?array $primaryResolved = null,
    ): array {
        if ($sameTimeTemplate !== null) {
            $options = is_array($sameTimeTemplate['unit_options'] ?? null) ? $sameTimeTemplate['unit_options'] : [];
            foreach ($options as $option) {
                if ((int) ($option['unit_no'] ?? 0) !== $unitNo) {
                    continue;
                }

                $template = $this->findTemplateById($templates, (int) ($option['unit_id'] ?? 0));
                if ($template !== null) {
                    return [
                        'unit_id' => $template['id'],
                        'label' => trim((string) ($option['label'] ?? '')) ?: "Unit {$unitNo}",
                        'layout_json' => (string) ($template['layout_json'] ?? ''),
                        'seats' => (int) ($template['seats'] ?? 0),
                        'warning' => 'Template mengikuti jadwal lain di jam yang sama.',
                    ];
                }
            }

            if ($unitNo === 1) {
                $template = $this->findTemplateById($templates, (int) ($sameTimeTemplate['unit_id'] ?? 0));
                if ($template !== null) {
                    return [
                        'unit_id' => $template['id'],
                        'label' => trim((string) ($sameTimeTemplate['unit_label'] ?? '')) ?: 'Unit 1',
                        'layout_json' => (string) ($template['layout_json'] ?? ''),
                        'seats' => (int) ($template['seats'] ?? 0),
                        'warning' => 'Template mengikuti jadwal lain di jam yang sama.',
                    ];
                }
            }
        }

        $unitStats = (array) (($group['unit_stats'] ?? [])[$unitNo] ?? []);
        $maxSeat = (int) ($unitStats['max_numeric_seat'] ?? 0);

        if ($maxSeat > 0) {
            $exactCapacity = array_values(array_filter($templates, static function (array $template) use ($maxSeat) {
                return (int) ($template['seats'] ?? 0) === $maxSeat || (int) ($template['kapasitas'] ?? 0) === $maxSeat;
            }));

            if (count($exactCapacity) === 1) {
                $template = $exactCapacity[0];

                return [
                    'unit_id' => $template['id'],
                    'label' => "Unit {$unitNo}",
                    'layout_json' => (string) ($template['layout_json'] ?? ''),
                    'seats' => (int) ($template['seats'] ?? 0),
                    'warning' => 'Template diinfer dari kapasitas kursi tertinggi pada booking lama.',
                ];
            }
        }

        $assignmentCategories = (array) ($assignmentContext['categories'][$this->sameTimeUnitKey($routeNorm, $jam, $unitNo)] ?? []);
        if (count($assignmentCategories) === 1) {
            $category = trim((string) $assignmentCategories[0]);
            $categoryTemplates = array_values(array_filter($templates, static function (array $template) use ($category) {
                return strtoupper(trim((string) ($template['category'] ?? ''))) === strtoupper($category);
            }));

            if ($maxSeat > 0) {
                $exactCategoryCapacity = array_values(array_filter($categoryTemplates, static function (array $template) use ($maxSeat) {
                    return (int) ($template['seats'] ?? 0) === $maxSeat || (int) ($template['kapasitas'] ?? 0) === $maxSeat;
                }));

                if (count($exactCategoryCapacity) === 1) {
                    $template = $exactCategoryCapacity[0];

                    return [
                        'unit_id' => $template['id'],
                        'label' => "Unit {$unitNo}",
                        'layout_json' => (string) ($template['layout_json'] ?? ''),
                        'seats' => (int) ($template['seats'] ?? 0),
                        'warning' => 'Template diinfer dari kategori armada dan kapasitas booking lama.',
                    ];
                }
            }

            if (count($categoryTemplates) === 1) {
                $template = $categoryTemplates[0];

                return [
                    'unit_id' => $template['id'],
                    'label' => "Unit {$unitNo}",
                    'layout_json' => (string) ($template['layout_json'] ?? ''),
                    'seats' => (int) ($template['seats'] ?? 0),
                    'warning' => 'Template diinfer dari kategori armada yang konsisten.',
                ];
            }
        }

        if ($unitNo > 1 && $primaryResolved !== null && ! empty($primaryResolved['unit_id'])) {
            return [
                'unit_id' => (int) ($primaryResolved['unit_id'] ?? 0),
                'label' => "Unit {$unitNo}",
                'layout_json' => (string) ($primaryResolved['layout_json'] ?? ''),
                'seats' => (int) ($primaryResolved['seats'] ?? 0),
                'warning' => 'Unit tambahan mengikuti template unit pertama.',
            ];
        }

        if (count($templates) === 1) {
            $template = $templates[0];

            return [
                'unit_id' => $template['id'],
                'label' => "Unit {$unitNo}",
                'layout_json' => (string) ($template['layout_json'] ?? ''),
                'seats' => (int) ($template['seats'] ?? 0),
                'warning' => 'Template tunggal dipakai sebagai default.',
            ];
        }

        return [
            'unit_id' => null,
            'label' => "Unit {$unitNo}",
            'layout_json' => '',
            'seats' => 0,
            'warning' => "Unit {$unitNo} masih ambigu dan perlu dicek manual di menu Jadwal.",
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $templates
     * @return array<string, mixed>|null
     */
    private function findTemplateById(array $templates, int $id): ?array
    {
        foreach ($templates as $template) {
            if ((int) ($template['id'] ?? 0) === $id) {
                return $template;
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $routeNames
     */
    private function canonicalRouteName(string $routeNorm, array $variants, array $routeNames): string
    {
        if (isset($routeNames[$routeNorm]) && trim((string) $routeNames[$routeNorm]) !== '') {
            return trim((string) $routeNames[$routeNorm]);
        }

        foreach ($variants as $variant) {
            $value = trim((string) $variant);
            if ($value !== '') {
                return $value;
            }
        }

        return $routeNorm;
    }

    /**
     * @param  array<string, int>  $routeIds
     */
    private function resolveRouteId(string $routeNorm, array $routeIds): int
    {
        return (int) ($routeIds[$routeNorm] ?? 0);
    }

    private function routeKey(string $value): string
    {
        return strtoupper(trim($value));
    }

    private function scheduleKey(string $routeNorm, int $dow, string $jam): string
    {
        return implode('|', [$routeNorm, (string) $dow, substr($jam, 0, 5)]);
    }

    private function sameTimeKey(string $routeNorm, string $jam): string
    {
        return implode('|', [$routeNorm, substr($jam, 0, 5)]);
    }

    private function sameTimeUnitKey(string $routeNorm, string $jam, int $unitNo): string
    {
        return implode('|', [$routeNorm, substr($jam, 0, 5), (string) max(1, $unitNo)]);
    }

    private function nullableString(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function countSeatsFromLayout(array $layout): int
    {
        $count = 0;

        foreach ($layout as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row as $cell) {
                if (is_array($cell)) {
                    $type = strtolower(trim((string) ($cell['type'] ?? $cell['kind'] ?? '')));
                    if ($type === 'seat') {
                        $count += 1;
                    }
                    continue;
                }

                if (! is_string($cell) && ! is_numeric($cell)) {
                    continue;
                }

                $token = strtoupper(trim((string) $cell));
                if ($token === '' || in_array($token, ['DRIVER', 'AISLE', '-', '_'], true)) {
                    continue;
                }

                $count += 1;
            }
        }

        return $count;
    }

    private function hasSchedulesRouteId(): bool
    {
        if ($this->schedulesHasRouteId === null) {
            $this->schedulesHasRouteId = Schema::hasColumn('schedules', 'route_id');
        }

        return $this->schedulesHasRouteId;
    }

    private function hasSchedulesSeatsColumn(): bool
    {
        if ($this->schedulesHasSeatsColumn === null) {
            $this->schedulesHasSeatsColumn = Schema::hasColumn('schedules', 'seats');
        }

        return $this->schedulesHasSeatsColumn;
    }

    private function hasScheduleUnitsTable(): bool
    {
        if ($this->scheduleUnitsTableExists === null) {
            $this->scheduleUnitsTableExists = Schema::hasTable('schedule_units');
        }

        return $this->scheduleUnitsTableExists;
    }
}
