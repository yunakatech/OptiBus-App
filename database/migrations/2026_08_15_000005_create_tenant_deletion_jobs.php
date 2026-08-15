<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_deletion_jobs')) {
            return;
        }

        Schema::create('tenant_deletion_jobs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('tenant_name', 200)->nullable();
            $table->string('tenant_slug', 100)->nullable();
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->string('mode', 30)->default('purge_all');
            $table->string('status', 30)->default('queued');
            $table->string('current_step', 80)->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->json('counts')->nullable();
            $table->json('cursor')->nullable();
            $table->json('deleted_counts')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'updated_at'], 'idx_tenant_deletion_jobs_status');
            $table->index('tenant_id', 'idx_tenant_deletion_jobs_tenant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_deletion_jobs');
    }
};
