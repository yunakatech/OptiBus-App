<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'is_private_pricing')) {
                $table->boolean('is_private_pricing')->default(false)->after('plan_id');
            }

            if (! Schema::hasColumn('subscriptions', 'custom_max_drivers')) {
                $table->integer('custom_max_drivers')->nullable()->after('custom_max_armadas');
            }
        });

        $legacyPrivateFields = [
            'custom_price_monthly',
            'custom_price_yearly',
            'custom_max_pools',
            'custom_max_users',
            'custom_max_armadas',
            'custom_max_routes',
            'custom_max_drivers',
        ];

        $availableLegacyFields = array_values(array_filter(
            $legacyPrivateFields,
            static fn (string $field): bool => Schema::hasColumn('subscriptions', $field),
        ));

        if ($availableLegacyFields !== []) {
            DB::table('subscriptions')
                ->where(function ($query) use ($availableLegacyFields): void {
                    foreach ($availableLegacyFields as $field) {
                        $query->orWhereNotNull($field);
                    }
                })
                ->update(['is_private_pricing' => true]);
        }

        if (! Schema::hasTable('subscription_feature_overrides')) {
            Schema::create('subscription_feature_overrides', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('subscription_id');
                $table->unsignedBigInteger('feature_gate_id');
                $table->integer('max_value')->nullable();
                $table->timestamps();
                $table->unique(
                    ['subscription_id', 'feature_gate_id'],
                    'uniq_subscription_feature_override',
                );
                $table->index('subscription_id', 'idx_subscription_feature_subscription');
                $table->index('feature_gate_id', 'idx_subscription_feature_gate');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_feature_overrides');

        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('subscriptions', 'custom_max_drivers')
                    ? 'custom_max_drivers'
                    : null,
                Schema::hasColumn('subscriptions', 'is_private_pricing')
                    ? 'is_private_pricing'
                    : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
