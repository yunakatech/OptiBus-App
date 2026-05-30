<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addColumnIfMissing(string $table, string $column, callable $callback): void
    {
        if (! Schema::hasColumn($table, $column)) {
            Schema::table($table, $callback);
        }
    }

    public function up(): void
    {
        if (! Schema::hasTable('routes')) {
            Schema::create('routes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('origin')->nullable();
                $table->string('destination')->nullable();
                $table->decimal('distance_km', 10, 2)->nullable();
                $table->unsignedInteger('duration_minutes')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        } else {
            $this->addColumnIfMissing('routes', 'origin', fn (Blueprint $table) => $table->string('origin')->nullable());
            $this->addColumnIfMissing('routes', 'destination', fn (Blueprint $table) => $table->string('destination')->nullable());
            $this->addColumnIfMissing('routes', 'distance_km', fn (Blueprint $table) => $table->decimal('distance_km', 10, 2)->nullable());
            $this->addColumnIfMissing('routes', 'duration_minutes', fn (Blueprint $table) => $table->unsignedInteger('duration_minutes')->nullable());
            $this->addColumnIfMissing('routes', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('nopol')->unique();
                $table->string('merek')->nullable();
                $table->string('type')->nullable();
                $table->string('category')->default('Big Bus');
                $table->unsignedInteger('tahun')->default(0);
                $table->string('warna')->nullable();
                $table->unsignedInteger('kapasitas')->default(0);
                $table->string('status', 20)->default('Aktif');
                $table->text('layout')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        } else {
            $this->addColumnIfMissing('units', 'category', fn (Blueprint $table) => $table->string('category')->default('Big Bus'));
            $this->addColumnIfMissing('units', 'tahun', fn (Blueprint $table) => $table->unsignedInteger('tahun')->default(0));
            $this->addColumnIfMissing('units', 'warna', fn (Blueprint $table) => $table->string('warna')->nullable());
            $this->addColumnIfMissing('units', 'layout', fn (Blueprint $table) => $table->text('layout')->nullable());
            $this->addColumnIfMissing('units', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        if (! Schema::hasTable('schedules')) {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id();
                $table->string('rute', 100);
                $table->unsignedTinyInteger('dow');
                $table->time('jam');
                $table->unsignedInteger('units')->default(1);
                $table->unsignedInteger('seats')->default(8);
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->text('layout')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['rute', 'dow', 'jam'], 'uniq_schedules_rute_dow_jam');
            });
        } else {
            $this->addColumnIfMissing('schedules', 'unit_id', fn (Blueprint $table) => $table->unsignedBigInteger('unit_id')->nullable());
            $this->addColumnIfMissing('schedules', 'layout', fn (Blueprint $table) => $table->text('layout')->nullable());
            $this->addColumnIfMissing('schedules', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone', 50)->unique();
                $table->text('gmaps')->nullable();
                $table->string('pickup_point')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        } else {
            $this->addColumnIfMissing('customers', 'gmaps', fn (Blueprint $table) => $table->text('gmaps')->nullable());
            $this->addColumnIfMissing('customers', 'pickup_point', fn (Blueprint $table) => $table->string('pickup_point')->nullable());
            $this->addColumnIfMissing('customers', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        if (! Schema::hasTable('segments')) {
            Schema::create('segments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('route_id')->default(0);
                $table->string('rute', 100);
                $table->string('origin')->nullable();
                $table->string('destination')->nullable();
                $table->string('pickup_time', 5)->nullable();
                $table->decimal('harga', 15, 2)->default(0);
                $table->timestamp('created_at')->useCurrent();
            });
        } else {
            $this->addColumnIfMissing('segments', 'route_id', fn (Blueprint $table) => $table->unsignedBigInteger('route_id')->default(0));
            $this->addColumnIfMissing('segments', 'origin', fn (Blueprint $table) => $table->string('origin')->nullable());
            $this->addColumnIfMissing('segments', 'destination', fn (Blueprint $table) => $table->string('destination')->nullable());
            $this->addColumnIfMissing('segments', 'pickup_time', fn (Blueprint $table) => $table->string('pickup_time', 5)->nullable());
            $this->addColumnIfMissing('segments', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->string('rute', 100);
                $table->date('tanggal');
                $table->time('jam');
                $table->unsignedInteger('unit')->default(1);
                $table->string('seat', 20);
                $table->string('name');
                $table->string('phone', 50);
                $table->string('pickup_point')->nullable();
                $table->string('pembayaran', 50)->default('Belum Lunas');
                $table->string('status', 20)->default('active');
                $table->unsignedBigInteger('segment_id')->nullable();
                $table->decimal('price', 15, 2)->default(0);
                $table->decimal('discount', 15, 2)->default(0);
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->string('created_by_username')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        } else {
            $this->addColumnIfMissing('bookings', 'segment_id', fn (Blueprint $table) => $table->unsignedBigInteger('segment_id')->nullable());
            $this->addColumnIfMissing('bookings', 'price', fn (Blueprint $table) => $table->decimal('price', 15, 2)->default(0));
            $this->addColumnIfMissing('bookings', 'discount', fn (Blueprint $table) => $table->decimal('discount', 15, 2)->default(0));
            $this->addColumnIfMissing('bookings', 'created_by_user_id', fn (Blueprint $table) => $table->unsignedBigInteger('created_by_user_id')->nullable());
            $this->addColumnIfMissing('bookings', 'created_by_username', fn (Blueprint $table) => $table->string('created_by_username')->nullable());
            $this->addColumnIfMissing('bookings', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        DB::statement('CREATE INDEX IF NOT EXISTS idx_schedules_rute_dow_jam ON schedules (rute, dow, jam)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_bookings_trip_lookup ON bookings (rute, tanggal, jam, unit, seat)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_bookings_trip_date ON bookings (tanggal, jam, rute, unit)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_customers_phone_name ON customers (phone, name)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_segments_route_price ON segments (route_id, harga)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_units_status_nopol ON units (status, nopol)');

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_bookings_active_trip ON bookings (rute, tanggal, jam, unit, seat) WHERE status <> 'canceled'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('segments');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('units');
        Schema::dropIfExists('routes');
    }
};
