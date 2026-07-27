<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules') || ! Schema::hasColumn('schedules', 'tenant_id')) {
            return;
        }

        $this->dropUniqueIfExists('schedules', 'uniq_schedules_rute_dow_jam');
        $this->dropUniqueIfExists('schedules', 'uniq_schedules_tenant_rute_dow_jam');
        $this->addUniqueIfMissing('schedules', ['tenant_id', 'rute', 'dow', 'jam'], 'uniq_schedules_tenant_rute_dow_jam');
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedules') || ! Schema::hasColumn('schedules', 'tenant_id')) {
            return;
        }

        $this->dropUniqueIfExists('schedules', 'uniq_schedules_tenant_rute_dow_jam');
        $this->addUniqueIfMissing('schedules', ['rute', 'dow', 'jam'], 'uniq_schedules_rute_dow_jam');
    }

    private function dropUniqueIfExists(string $table, string $index): void
    {
        if (! Schema::hasIndex($table, $index, 'unique')) {
            return;
        }

        Schema::table($table, function (Blueprint $schema) use ($index): void {
            $schema->dropUnique($index);
        });
    }

    private function addUniqueIfMissing(string $table, array $columns, string $index): void
    {
        if (Schema::hasIndex($table, $index, 'unique')) {
            return;
        }

        Schema::table($table, function (Blueprint $schema) use ($columns, $index): void {
            $schema->unique($columns, $index);
        });
    }
};
