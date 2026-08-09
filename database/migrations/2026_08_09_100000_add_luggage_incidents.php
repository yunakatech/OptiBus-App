<?php

use App\Support\AccessControl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('luggages')) {
            Schema::table('luggages', function (Blueprint $table): void {
                if (! Schema::hasColumn('luggages', 'damaged_quantity')) {
                    $table->unsignedInteger('damaged_quantity')->default(0)->after('quantity');
                }

                if (! Schema::hasColumn('luggages', 'lost_quantity')) {
                    $table->unsignedInteger('lost_quantity')->default(0)->after('damaged_quantity');
                }

                if (! Schema::hasColumn('luggages', 'condition_status')) {
                    $table->string('condition_status', 30)->default('normal')->after('lost_quantity');
                }
            });

            DB::table('luggages')
                ->whereNull('condition_status')
                ->update(['condition_status' => 'normal']);

            DB::statement('CREATE INDEX IF NOT EXISTS idx_luggages_condition_status ON luggages (condition_status, created_at)');
        }

        if (! Schema::hasTable('luggage_incidents')) {
            Schema::create('luggage_incidents', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('luggage_id');
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('pool_id')->nullable();
                $table->string('type', 20);
                $table->unsignedInteger('quantity');
                $table->text('description');
                $table->string('status', 20)->default('reported');
                $table->string('claim_status', 20)->default('none');
                $table->decimal('claim_amount', 15, 2)->default(0);
                $table->decimal('approved_amount', 15, 2)->default(0);
                $table->text('resolution_note')->nullable();
                $table->unsignedBigInteger('reported_by_user_id')->nullable();
                $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
                $table->timestamp('reported_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index('luggage_id', 'idx_luggage_incidents_luggage');
                $table->index(['tenant_id', 'pool_id', 'status'], 'idx_luggage_incidents_scope_status');
                $table->index(['tenant_id', 'claim_status'], 'idx_luggage_incidents_claim_scope');
            });
        }

        if (
            Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_permission')
        ) {
            AccessControl::syncDefaults();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('luggage_incidents');

        if (Schema::hasTable('luggages')) {
            Schema::table('luggages', function (Blueprint $table): void {
                foreach (['condition_status', 'lost_quantity', 'damaged_quantity'] as $column) {
                    if (Schema::hasColumn('luggages', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
