<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'custom_price_monthly')) {
                $table->decimal('custom_price_monthly', 15, 2)->nullable()->after('plan_id');
            }

            if (! Schema::hasColumn('subscriptions', 'custom_price_yearly')) {
                $table->decimal('custom_price_yearly', 15, 2)->nullable()->after('custom_price_monthly');
            }

            if (! Schema::hasColumn('subscriptions', 'custom_max_pools')) {
                $table->integer('custom_max_pools')->nullable()->after('custom_price_yearly');
            }

            if (! Schema::hasColumn('subscriptions', 'custom_max_users')) {
                $table->integer('custom_max_users')->nullable()->after('custom_max_pools');
            }

            if (! Schema::hasColumn('subscriptions', 'custom_max_armadas')) {
                $table->integer('custom_max_armadas')->nullable()->after('custom_max_users');
            }

            if (! Schema::hasColumn('subscriptions', 'custom_max_routes')) {
                $table->integer('custom_max_routes')->nullable()->after('custom_max_armadas');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('subscriptions', 'custom_max_routes') ? 'custom_max_routes' : null,
                Schema::hasColumn('subscriptions', 'custom_max_armadas') ? 'custom_max_armadas' : null,
                Schema::hasColumn('subscriptions', 'custom_max_users') ? 'custom_max_users' : null,
                Schema::hasColumn('subscriptions', 'custom_max_pools') ? 'custom_max_pools' : null,
                Schema::hasColumn('subscriptions', 'custom_price_yearly') ? 'custom_price_yearly' : null,
                Schema::hasColumn('subscriptions', 'custom_price_monthly') ? 'custom_price_monthly' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
