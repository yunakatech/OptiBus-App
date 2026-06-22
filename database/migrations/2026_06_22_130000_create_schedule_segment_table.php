<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedule_segment')) {
            Schema::create('schedule_segment', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
                $table->foreignId('segment_id')->constrained('segments')->onDelete('cascade');
                $table->char('jam_pickup', 5)->default('00:00');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_segment');
    }
};
