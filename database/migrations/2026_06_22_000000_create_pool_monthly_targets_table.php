<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pool_monthly_targets')) {
            Schema::create('pool_monthly_targets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pool_id');
                $table->date('target_month');
                $table->decimal('booking_target', 15, 2)->default(0);
                $table->decimal('bagasi_target', 15, 2)->default(0);
                $table->decimal('carter_target', 15, 2)->default(0);
                $table->timestamps();

                $table->unique(['pool_id', 'target_month'], 'uniq_pool_monthly_targets_pool_month');
                $table->index('target_month', 'idx_pool_monthly_targets_month');
                $table->index('pool_id', 'idx_pool_monthly_targets_pool');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_monthly_targets');
    }
};
