<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mayar_fulfillments')) {
            return;
        }

        Schema::create('mayar_fulfillments', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_id', 160)->unique();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->string('status', 30)->default('processing')->index();
            $table->unsignedInteger('attempt_count')->default(1);
            $table->text('last_error')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mayar_fulfillments');
    }
};
