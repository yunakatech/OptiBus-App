<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('luggage_services') || ! Schema::hasTable('segments')) {
            return;
        }

        Schema::table('luggage_services', function (Blueprint $table): void {
            if (! Schema::hasColumn('luggage_services', 'description')) {
                $table->text('description')->nullable();
            }
            if (! Schema::hasColumn('luggage_services', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
            if (! Schema::hasColumn('luggage_services', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (Schema::hasTable('luggages')) {
            Schema::table('luggages', function (Blueprint $table): void {
                if (! Schema::hasColumn('luggages', 'segment_id')) {
                    $table->unsignedBigInteger('segment_id')->nullable()->index();
                }
                if (! Schema::hasColumn('luggages', 'luggage_segment_rate_id')) {
                    $table->unsignedBigInteger('luggage_segment_rate_id')->nullable()->index();
                }
                if (! Schema::hasColumn('luggages', 'unit_price')) {
                    $table->decimal('unit_price', 15, 2)->nullable();
                }
                if (! Schema::hasColumn('luggages', 'pricing_source')) {
                    $table->string('pricing_source', 20)->default('legacy')->index();
                }
                if (! Schema::hasColumn('luggages', 'price_override_reason')) {
                    $table->text('price_override_reason')->nullable();
                }
                if (! Schema::hasColumn('luggages', 'price_overridden_by_user_id')) {
                    $table->unsignedBigInteger('price_overridden_by_user_id')->nullable()->index();
                }
            });
        }

        if (! Schema::hasTable('luggage_segment_rates')) {
            Schema::create('luggage_segment_rates', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('segment_id');
                $table->unsignedBigInteger('service_id');
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('configured_at')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();

                $table->unique(
                    ['tenant_id', 'segment_id', 'service_id'],
                    'uniq_luggage_rate_tenant_segment_service',
                );
                $table->index(
                    ['tenant_id', 'segment_id', 'is_active'],
                    'idx_luggage_rates_segment_active',
                );
                $table->index('service_id', 'idx_luggage_rates_service');
            });

            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE luggage_segment_rates ENABLE ROW LEVEL SECURITY');
            }
        }

        $now = now();
        DB::table('segments')
            ->whereNotNull('tenant_id')
            ->orderBy('id')
            ->chunkById(100, function ($segments) use ($now): void {
                $tenantIds = $segments->pluck('tenant_id')->map(fn ($id) => (int) $id)->unique()->all();
                $services = DB::table('luggage_services')
                    ->whereIn('tenant_id', $tenantIds)
                    ->get(['id', 'tenant_id']);
                $servicesByTenant = $services->groupBy(fn (object $service) => (int) $service->tenant_id);
                $rows = [];

                foreach ($segments as $segment) {
                    foreach ($servicesByTenant->get((int) $segment->tenant_id, collect()) as $service) {
                        $rows[] = [
                            'tenant_id' => (int) $segment->tenant_id,
                            'segment_id' => (int) $segment->id,
                            'service_id' => (int) $service->id,
                            'unit_price' => 0,
                            'is_active' => true,
                            'configured_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('luggage_segment_rates')->insertOrIgnore($chunk);
                }
            }, 'id');
    }

    public function down(): void
    {
        // Keep repair migration non-destructive; original migration owns rollback.
    }
};
