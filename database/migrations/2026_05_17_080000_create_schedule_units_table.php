<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        if (! Schema::hasTable('schedule_units')) {
            Schema::create('schedule_units', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schedule_id');
                $table->unsignedInteger('unit_no');
                $table->string('label', 120)->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->unique(['schedule_id', 'unit_no'], 'uniq_schedule_units_slot');
                $table->index(['schedule_id'], 'idx_schedule_units_schedule');
            });
        }

        $schedules = DB::table('schedules')
            ->orderBy('id')
            ->get(['id', 'units', 'unit_label', 'unit_id']);

        foreach ($schedules as $schedule) {
            $scheduleId = (int) ($schedule->id ?? 0);
            if ($scheduleId <= 0) {
                continue;
            }

            $existingCount = (int) DB::table('schedule_units')->where('schedule_id', $scheduleId)->count();
            if ($existingCount > 0) {
                continue;
            }

            $count = max(1, (int) ($schedule->units ?? 1));
            $fallbackLabel = trim((string) ($schedule->unit_label ?? ''));
            $rows = [];
            for ($i = 1; $i <= $count; $i += 1) {
                $rows[] = [
                    'schedule_id' => $scheduleId,
                    'unit_no' => $i,
                    'label' => $fallbackLabel !== '' ? $fallbackLabel : "Unit {$i}",
                    'unit_id' => $schedule->unit_id !== null ? (int) $schedule->unit_id : null,
                    'created_at' => now(),
                ];
            }

            DB::table('schedule_units')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_units');
    }
};
