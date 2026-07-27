<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('units') && ! Schema::hasTable('category_armada')) {
            Schema::rename('units', 'category_armada');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('category_armada') && ! Schema::hasTable('units')) {
            Schema::rename('category_armada', 'units');
        }
    }
};
