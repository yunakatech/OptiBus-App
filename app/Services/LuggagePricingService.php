<?php

namespace App\Services;

use App\Support\SchemaCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LuggagePricingService
{
    public function ready(): bool
    {
        foreach ([
            'id',
            'tenant_id',
            'segment_id',
            'service_id',
            'unit_price',
            'is_active',
            'configured_at',
        ] as $column) {
            if (! SchemaCache::hasColumn('luggage_segment_rates', $column)) {
                return false;
            }
        }

        foreach ([
            'segment_id',
            'luggage_segment_rate_id',
            'unit_price',
            'pricing_source',
            'price_override_reason',
            'price_overridden_by_user_id',
        ] as $column) {
            if (! SchemaCache::hasColumn('luggages', $column)) {
                return false;
            }
        }

        return SchemaCache::hasColumn('luggage_services', 'tenant_id')
            && SchemaCache::hasColumn('luggage_services', 'is_active')
            && SchemaCache::hasColumn('luggage_services', 'name');
    }

    public function assertReady(): void
    {
        if (! $this->ready()) {
            throw new RuntimeException('Tabel tarif bagasi belum tersedia. Jalankan migrasi database terlebih dahulu.');
        }
    }

    public function syncServiceRates(int $tenantId, int $serviceId, ?int $actorUserId = null): void
    {
        $this->assertReady();
        $now = now();
        $rows = DB::table('segments')
            ->where('tenant_id', $tenantId)
            ->pluck('id')
            ->map(fn ($segmentId): array => [
                'tenant_id' => $tenantId,
                'segment_id' => (int) $segmentId,
                'service_id' => $serviceId,
                'unit_price' => 0,
                'is_active' => true,
                'configured_at' => null,
                'created_by_user_id' => $actorUserId,
                'updated_by_user_id' => $actorUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('luggage_segment_rates')->insertOrIgnore($chunk);
        }
    }

    public function syncSegmentRates(int $tenantId, int $segmentId, ?int $actorUserId = null): void
    {
        $this->assertReady();
        $now = now();
        $rows = DB::table('luggage_services')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($serviceId): array => [
                'tenant_id' => $tenantId,
                'segment_id' => $segmentId,
                'service_id' => (int) $serviceId,
                'unit_price' => 0,
                'is_active' => true,
                'configured_at' => null,
                'created_by_user_id' => $actorUserId,
                'updated_by_user_id' => $actorUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('luggage_segment_rates')->insertOrIgnore($chunk);
        }
    }

    /**
     * @return array{route: object, segment: object, rate: object, service: object}
     */
    public function resolve(int $tenantId, int $routeId, int $segmentId, int $serviceId): array
    {
        $this->assertReady();

        $route = DB::table('routes')
            ->where('id', $routeId)
            ->where('tenant_id', $tenantId)
            ->first(['id', 'name']);
        if (! $route) {
            throw new RuntimeException('Rute induk tidak ditemukan untuk tenant aktif.');
        }

        $segment = DB::table('segments')
            ->where('id', $segmentId)
            ->where('route_id', $routeId)
            ->where('tenant_id', $tenantId)
            ->first(['id', 'route_id', 'rute', 'origin', 'destination']);
        if (! $segment) {
            throw new RuntimeException('Segment tidak sesuai dengan rute induk yang dipilih.');
        }

        $service = DB::table('luggage_services')
            ->where('id', $serviceId)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first(['id', 'name', 'description']);
        if (! $service) {
            throw new RuntimeException('Kategori barang tidak tersedia untuk tenant aktif.');
        }

        $rate = DB::table('luggage_segment_rates')
            ->where('tenant_id', $tenantId)
            ->where('segment_id', $segmentId)
            ->where('service_id', $serviceId)
            ->where('is_active', true)
            ->first(['id', 'unit_price', 'configured_at']);
        if (! $rate) {
            throw new RuntimeException('Tarif kategori pada segment ini belum tersedia.');
        }

        return compact('route', 'segment', 'rate', 'service');
    }

    /**
     * @return array{segment_id: int, luggage_segment_rate_id: int, unit_price: float, price: float, pricing_source: string, price_override_reason: ?string, price_overridden_by_user_id: ?int, configured: bool, route_name: string, segment_name: string, service_name: string}
     */
    public function snapshot(
        int $tenantId,
        int $routeId,
        int $segmentId,
        int $serviceId,
        int $quantity,
        mixed $unitPriceOverride,
        ?string $overrideReason,
        int $actorUserId,
        bool $canOverride,
    ): array {
        $resolved = $this->resolve($tenantId, $routeId, $segmentId, $serviceId);
        $baseUnitPrice = (float) $resolved['rate']->unit_price;
        $hasOverride = $unitPriceOverride !== null && $unitPriceOverride !== '';
        $unitPrice = $baseUnitPrice;
        $pricingSource = 'rate';
        $reason = null;
        $overriddenBy = null;

        if ($hasOverride) {
            if (! $canOverride) {
                throw new RuntimeException('Anda tidak memiliki izin untuk mengubah tarif bagasi.');
            }

            $unitPrice = round((float) $unitPriceOverride, 2);
            $reason = trim((string) $overrideReason);
            if ($reason === '') {
                throw new RuntimeException('Alasan perubahan tarif wajib diisi.');
            }

            $pricingSource = 'override';
            $overriddenBy = $actorUserId;
        }

        $quantity = max(1, $quantity);

        return [
            'segment_id' => $segmentId,
            'luggage_segment_rate_id' => (int) $resolved['rate']->id,
            'unit_price' => $unitPrice,
            'price' => round($unitPrice * $quantity, 2),
            'pricing_source' => $pricingSource,
            'price_override_reason' => $reason,
            'price_overridden_by_user_id' => $overriddenBy,
            'configured' => $resolved['rate']->configured_at !== null,
            'route_name' => (string) $resolved['route']->name,
            'segment_name' => (string) $resolved['segment']->rute,
            'service_name' => (string) $resolved['service']->name,
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function ratesForSegment(int $tenantId, int $segmentId): Collection
    {
        $this->assertReady();

        return DB::table('luggage_services as ls')
            ->leftJoin('luggage_segment_rates as lsr', function ($join) use ($tenantId, $segmentId): void {
                $join->on('ls.id', '=', 'lsr.service_id')
                    ->where('lsr.tenant_id', '=', $tenantId)
                    ->where('lsr.segment_id', '=', $segmentId);
            })
            ->where('ls.tenant_id', $tenantId)
            ->orderByDesc('ls.is_active')
            ->orderBy('ls.name')
            ->get([
                'ls.id as service_id',
                'ls.name',
                'ls.description',
                'ls.is_active as category_active',
                'lsr.id as rate_id',
                'lsr.unit_price',
                'lsr.is_active as rate_active',
                'lsr.configured_at',
            ])
            ->map(function (object $row): object {
                $row->service_id = (int) $row->service_id;
                $row->rate_id = $row->rate_id !== null ? (int) $row->rate_id : null;
                $row->unit_price = (float) ($row->unit_price ?? 0);
                $row->is_active = (bool) $row->category_active && ($row->rate_active === null || (bool) $row->rate_active);
                $row->configured = $row->configured_at !== null;
                unset($row->category_active, $row->rate_active);

                return $row;
            });
    }

    /**
     * @param  array<int, array{service_id: int, unit_price: float|int|string, is_active?: bool}>  $rates
     */
    public function saveSegmentRates(int $tenantId, int $segmentId, array $rates, int $actorUserId): void
    {
        $this->assertReady();
        $serviceIds = collect($rates)->pluck('service_id')->map(fn ($id) => (int) $id)->unique()->values();
        $validServiceIds = DB::table('luggage_services')
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $serviceIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($validServiceIds->count() !== $serviceIds->count()) {
            throw new RuntimeException('Ada kategori barang yang bukan milik tenant aktif.');
        }

        DB::transaction(function () use ($tenantId, $segmentId, $rates, $actorUserId): void {
            $now = now();
            foreach ($rates as $rate) {
                $identity = [
                    'tenant_id' => $tenantId,
                    'segment_id' => $segmentId,
                    'service_id' => (int) $rate['service_id'],
                ];
                DB::table('luggage_segment_rates')->insertOrIgnore([array_merge($identity, [
                    'unit_price' => 0,
                    'is_active' => true,
                    'configured_at' => null,
                    'created_by_user_id' => $actorUserId,
                    'updated_by_user_id' => $actorUserId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])]);
                DB::table('luggage_segment_rates')->where($identity)->update([
                    'unit_price' => round((float) $rate['unit_price'], 2),
                    'is_active' => (bool) ($rate['is_active'] ?? true),
                    'configured_at' => $now,
                    'updated_by_user_id' => $actorUserId,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
