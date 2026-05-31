<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            Schema::create('pools', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('code', 40)->nullable()->unique();
                $table->decimal('target_revenue', 15, 2)->default(0);
                $table->string('status', 20)->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pool_route')) {
            Schema::create('pool_route', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pool_id');
                $table->unsignedBigInteger('route_id');
                $table->timestamps();
                $table->unique('route_id', 'uniq_pool_route_route_id');
                $table->unique(['pool_id', 'route_id'], 'uniq_pool_route_pair');
                $table->index('pool_id', 'idx_pool_route_pool_id');
            });
        }

        if (! Schema::hasTable('pool_user')) {
            Schema::create('pool_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pool_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->unique(['pool_id', 'user_id'], 'uniq_pool_user_pair');
                $table->index('user_id', 'idx_pool_user_user_id');
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false);
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_super_admin')) {
            $hasSuperAdmin = DB::table('users')->where('is_super_admin', true)->exists();

            if (! $hasSuperAdmin) {
                $firstUserId = DB::table('users')->orderBy('id')->value('id');

                if ($firstUserId) {
                    DB::table('users')->where('id', $firstUserId)->update(['is_super_admin' => true]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_user');
        Schema::dropIfExists('pool_route');
        Schema::dropIfExists('pools');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_super_admin');
            });
        }
    }
};
