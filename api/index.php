<?php

// Enable error reporting to diagnose HTTP 500 error on Vercel
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

    require __DIR__.'/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    // Swap ExceptionHandler in container so we get the raw original error (using a Closure)
    $app->singleton(
        \Illuminate\Contracts\Debug\ExceptionHandler::class,
        function () {
            return new class implements \Illuminate\Contracts\Debug\ExceptionHandler {
                public function report(\Throwable $e) {}
                public function shouldReport(\Throwable $e) { return false; }
                public function render($request, \Throwable $e) { throw $e; }
                public function renderForConsole($output, \Throwable $e) { throw $e; }
            };
        }
    );

    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    echo '<h1>Captured Serverless PHP Exception:</h1>';
    echo '<p><b>Message:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><b>File:</b> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
    echo '<h3>Stack Trace:</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    exit(1);
}
