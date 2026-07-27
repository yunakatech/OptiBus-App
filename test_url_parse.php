<?php
require 'vendor/autoload.php';

$connector = new Illuminate\Database\Connectors\PostgresConnector();
$dsn = (new ReflectionMethod($connector, 'getDsn'))->invoke($connector, [
    'host' => 'aws-0-ap-southeast-1.pooler.supabase.com',
    'port' => 6543,
    'database' => 'postgres',
    'options' => '-c pool_mode=session'
]);
var_dump($dsn);
