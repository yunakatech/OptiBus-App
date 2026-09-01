<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants') && ! Schema::hasColumn('tenants', 'public_booking_enabled')) {
            Schema::table('tenants', function (Blueprint $table): void {
                $table->boolean('public_booking_enabled')->default(false)->after('logo_url');
            });
        }

        if (Schema::hasTable('bookings') && ! Schema::hasColumn('bookings', 'public_booking_request_id')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->unsignedBigInteger('public_booking_request_id')->nullable()->after('created_by_username');
                $table->index('public_booking_request_id', 'idx_bookings_public_request_id');
            });
        }

        if (! Schema::hasTable('public_booking_requests')) {
            Schema::create('public_booking_requests', function (Blueprint $table): void {
                $table->id();
                $table->string('request_code', 32)->unique();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('pool_id');
                $table->unsignedBigInteger('route_id')->nullable();
                $table->unsignedBigInteger('schedule_id');
                $table->date('tanggal');
                $table->time('jam');
                $table->unsignedInteger('unit')->default(1);
                $table->string('contact_name', 255);
                $table->string('phone', 50);
                $table->string('pickup_address', 255);
                $table->string('payment_method', 50);
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('pending');
                $table->timestamp('hold_expires_at');
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'status', 'hold_expires_at'], 'idx_public_booking_tenant_status_hold');
                $table->index(['pool_id', 'status', 'created_at'], 'idx_public_booking_pool_status_created');
                $table->index(['schedule_id', 'tanggal', 'jam', 'unit'], 'idx_public_booking_trip');
            });
        }

        if (! Schema::hasTable('public_booking_request_seats')) {
            Schema::create('public_booking_request_seats', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('request_id');
                $table->string('seat', 20);
                $table->string('passenger_name', 255);
                $table->timestamps();
                $table->unique(['request_id', 'seat'], 'uniq_public_booking_request_seat');
                $table->index('request_id', 'idx_public_booking_request_seats_request');
            });
        }

        if (Schema::hasTable('permissions') && ! DB::table('permissions')->where('slug', 'booking.public.manage')->exists()) {
            DB::table('permissions')->insert([
                'slug' => 'booking.public.manage',
                'name' => 'Kelola Booking Publik',
                'group' => 'Booking',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('roles') && Schema::hasTable('permissions') && Schema::hasTable('role_permission')) {
            $permissionId = (int) DB::table('permissions')->where('slug', 'booking.public.manage')->value('id');
            foreach (['tenant-owner', 'admin-pusat'] as $roleSlug) {
                $roleId = (int) DB::table('roles')->where('slug', $roleSlug)->value('id');
                if ($permissionId > 0 && $roleId > 0 && ! DB::table('role_permission')->where('role_id', $roleId)->where('permission_id', $permissionId)->exists()) {
                    DB::table('role_permission')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('role_permission') && Schema::hasTable('permissions')) {
            $permissionId = DB::table('permissions')->where('slug', 'booking.public.manage')->value('id');
            if ($permissionId) {
                DB::table('role_permission')->where('permission_id', $permissionId)->delete();
                DB::table('permissions')->where('id', $permissionId)->delete();
            }
        }

        Schema::dropIfExists('public_booking_request_seats');
        Schema::dropIfExists('public_booking_requests');

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'public_booking_request_id')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->dropIndex('idx_bookings_public_request_id');
                $table->dropColumn('public_booking_request_id');
            });
        }

        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'public_booking_enabled')) {
            Schema::table('tenants', fn (Blueprint $table) => $table->dropColumn('public_booking_enabled'));
        }
    }
};
