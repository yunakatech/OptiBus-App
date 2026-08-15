<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_webhook_events')) {
            return;
        }

        Schema::table('payment_webhook_events', function (Blueprint $table): void {
            if (! Schema::hasColumn('payment_webhook_events', 'transaction_id')) {
                $table->string('transaction_id', 160)->nullable()->index();
            }
            if (! Schema::hasColumn('payment_webhook_events', 'attempt_count')) {
                $table->unsignedInteger('attempt_count')->default(0);
            }
            if (! Schema::hasColumn('payment_webhook_events', 'locked_until')) {
                $table->timestamp('locked_until')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_webhook_events')) {
            return;
        }

        Schema::table('payment_webhook_events', function (Blueprint $table): void {
            foreach (['transaction_id', 'attempt_count', 'locked_until'] as $column) {
                if (Schema::hasColumn('payment_webhook_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
