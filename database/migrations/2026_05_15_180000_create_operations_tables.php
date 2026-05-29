<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 120);
                $table->string('phone', 30)->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('trip_assignments')) {
            Schema::create('trip_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('rute', 120);
                $table->date('tanggal');
                $table->time('jam');
                $table->unsignedInteger('unit')->default(1);
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('cancellations')) {
            Schema::create('cancellations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->string('admin_user', 120)->nullable();
                $table->text('reason')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 120)->unique();
                $table->text('value')->nullable();
            });
        }

        if (! Schema::hasTable('luggage_services')) {
            Schema::create('luggage_services', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->decimal('price', 15, 2)->default(0);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('master_carter')) {
            Schema::create('master_carter', function (Blueprint $table) {
                $table->id();
                $table->string('name', 180);
                $table->string('origin', 120);
                $table->string('destination', 120);
                $table->string('duration', 50)->default('Regular');
                $table->decimal('rental_price', 15, 2)->default(0);
                $table->decimal('bop_price', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('charters')) {
            Schema::create('charters', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('company_name', 180)->nullable();
                $table->string('phone', 30)->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->time('departure_time')->nullable();
                $table->string('pickup_point', 180)->nullable();
                $table->string('drop_point', 180)->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('driver_name', 120)->nullable();
                $table->decimal('price', 15, 2)->default(0);
                $table->string('layanan', 120)->default('Regular');
                $table->decimal('bop_price', 15, 2)->default(0);
                $table->string('bop_status', 20)->default('pending');
                $table->decimal('down_payment', 15, 2)->default(0);
                $table->string('payment_status', 30)->default('Belum Bayar');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('luggages')) {
            Schema::create('luggages', function (Blueprint $table) {
                $table->id();
                $table->string('sender_name', 120);
                $table->string('sender_phone', 30);
                $table->text('sender_address')->nullable();
                $table->string('receiver_name', 120);
                $table->string('receiver_phone', 30);
                $table->text('receiver_address')->nullable();
                $table->unsignedBigInteger('service_id')->nullable();
                $table->unsignedInteger('quantity')->default(1);
                $table->text('notes')->nullable();
                $table->decimal('price', 15, 2)->default(0);
                $table->string('status', 20)->default('pending');
                $table->string('payment_status', 30)->default('Belum Bayar');
                $table->string('rute', 120)->nullable();
                $table->date('tanggal')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('kode_resi', 50)->nullable();
                $table->unsignedBigInteger('pengirim_id')->nullable();
                $table->unsignedBigInteger('penerima_id')->nullable();
                $table->unsignedBigInteger('rute_id')->nullable();
                $table->unsignedBigInteger('layanan_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('customer_bagasi')) {
            Schema::create('customer_bagasi', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 120);
                $table->string('no_hp', 30)->unique();
                $table->text('alamat')->nullable();
                $table->string('tipe', 20)->default('pengirim');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('customer_charter')) {
            Schema::create('customer_charter', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 120);
                $table->string('no_hp', 30)->unique();
                $table->text('alamat')->nullable();
                $table->string('company', 180)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS idx_drivers_nama ON drivers (nama)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_trip_assignments_lookup ON trip_assignments (rute, tanggal, jam, unit)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_charters_start_date ON charters (start_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_luggages_status_created ON luggages (status, created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_charter');
        Schema::dropIfExists('customer_bagasi');
        Schema::dropIfExists('luggages');
        Schema::dropIfExists('charters');
        Schema::dropIfExists('master_carter');
        Schema::dropIfExists('luggage_services');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('cancellations');
        Schema::dropIfExists('trip_assignments');
        Schema::dropIfExists('drivers');
    }
};

