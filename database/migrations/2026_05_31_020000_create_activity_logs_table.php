<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 40)->default('INFO');
            $table->string('title', 255);
            $table->text('meta')->nullable();
            $table->string('actor', 120)->nullable();
            $table->json('extra')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
