<?php

// Enable error reporting to diagnose HTTP 500 error on Vercel
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    // Forward Vercel requests to the normal Laravel entry point
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    echo '<h1>Captured Serverless PHP Exception:</h1>';
    echo '<p><b>Message:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><b>File:</b> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
    echo '<h3>Stack Trace:</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    exit(1);
}
