<?php

use App\Support\AccessControl;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AccessControl::syncDefaults();
    }

    public function down(): void
    {
        //
    }
};
