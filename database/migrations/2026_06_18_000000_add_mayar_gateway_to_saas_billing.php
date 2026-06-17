<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_subscriptions')) {
            Schema::table('invoice_subscriptions', function (Blueprint $table): void {
                if (! Schema::hasColumn('invoice_subscriptions', 'payment_gateway')) {
                    $table->string('payment_gateway', 40)->nullable()->after('payment_method');
                }

                if (! Schema::hasColumn('invoice_subscriptions', 'gateway_reference')) {
                    $table->string('gateway_reference', 120)->nullable()->after('payment_gateway')->index();
                }

                if (! Schema::hasColumn('invoice_subscriptions', 'gateway_checkout_url')) {
                    $table->text('gateway_checkout_url')->nullable()->after('gateway_reference');
                }

                if (! Schema::hasColumn('invoice_subscriptions', 'gateway_status')) {
                    $table->string('gateway_status', 60)->nullable()->after('gateway_checkout_url')->index();
                }

                if (! Schema::hasColumn('invoice_subscriptions', 'gateway_payload')) {
                    $table->json('gateway_payload')->nullable()->after('gateway_status');
                }

                if (! Schema::hasColumn('invoice_subscriptions', 'gateway_paid_at')) {
                    $table->timestamp('gateway_paid_at')->nullable()->after('gateway_payload');
                }
            });
        }

        if (Schema::hasTable('tenants') && ! Schema::hasColumn('tenants', 'mayar_customer_id')) {
            Schema::table('tenants', function (Blueprint $table): void {
                $table->string('mayar_customer_id', 120)->nullable()->after('status')->index();
            });
        }

        if (! Schema::hasTable('payment_webhook_events')) {
            Schema::create('payment_webhook_events', function (Blueprint $table): void {
                $table->id();
                $table->string('gateway', 40);
                $table->string('event_id', 160)->nullable();
                $table->string('reference', 160)->nullable();
                $table->string('event_type', 120)->nullable();
                $table->json('payload')->nullable();
                $table->string('status', 60)->default('received');
                $table->text('error_message')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->unique(['gateway', 'event_id']);
                $table->unique(['gateway', 'reference', 'event_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');

        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'mayar_customer_id')) {
            Schema::table('tenants', function (Blueprint $table): void {
                $table->dropIndex(['mayar_customer_id']);
                $table->dropColumn('mayar_customer_id');
            });
        }

        if (Schema::hasTable('invoice_subscriptions')) {
            Schema::table('invoice_subscriptions', function (Blueprint $table): void {
                if (Schema::hasColumn('invoice_subscriptions', 'gateway_reference')) {
                    $table->dropIndex(['gateway_reference']);
                }

                if (Schema::hasColumn('invoice_subscriptions', 'gateway_status')) {
                    $table->dropIndex(['gateway_status']);
                }

                $columns = [
                    'payment_gateway',
                    'gateway_reference',
                    'gateway_checkout_url',
                    'gateway_status',
                    'gateway_payload',
                    'gateway_paid_at',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('invoice_subscriptions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
