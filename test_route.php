<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
Illuminate\Support\Facades\DB::table('users')->first();
echo "Query success!\n";
