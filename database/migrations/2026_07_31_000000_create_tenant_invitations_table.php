<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_invitations')) {
            return;
        }

        Schema::create('tenant_invitations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('token_hash', 64)->unique();
            $table->json('role_ids')->nullable();
            $table->json('pool_ids')->nullable();
            $table->unsignedBigInteger('invited_by_user_id')->nullable();
            $table->unsignedBigInteger('accepted_by_user_id')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'email'], 'idx_tenant_invitations_tenant_email');
            $table->index(['tenant_id', 'accepted_at', 'revoked_at'], 'idx_tenant_invitations_status');
            $table->index('expires_at', 'idx_tenant_invitations_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invitations');
    }
};
