<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('charters') || Schema::hasColumn('charters', 'status')) {
            return;
        }

        Schema::table('charters', function (Blueprint $table): void {
            $table->string('status', 20)->default('active')->after('payment_status');
        });

        DB::table('charters')->update(['status' => 'active']);
        DB::statement('CREATE INDEX IF NOT EXISTS idx_charters_status ON charters (status)');
    }

    public function down(): void
    {
        if (!Schema::hasTable('charters') || !Schema::hasColumn('charters', 'status')) {
            return;
        }

        Schema::table('charters', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
