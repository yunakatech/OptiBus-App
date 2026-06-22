<?php
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create('/api/bookings/schedules', 'GET', ['rute' => 'PINRANG → MAKASSAR', 'tanggal' => '2026-06-25']);
$response = $kernel->handle($request);
file_put_contents(__DIR__.'/api_test_dump.json', json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT));
echo "API tested\n";
