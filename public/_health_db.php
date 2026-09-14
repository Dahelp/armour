<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/config/environment.php';

try {
    $database = require dirname(__DIR__) . '/config/config_db.php';
    new PDO(
        (string)$database['dsn'],
        (string)$database['user'],
        (string)$database['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "database: ok\n";
} catch (Throwable $exception) {
    echo "database: failed\n";
    echo 'error: ' . get_class($exception) . "\n";
    exit;
}

try {
    require dirname(__DIR__) . '/config/init.php';
    echo "bootstrap-init: ok\n";
    require_once LIBS . '/functions.php';
    echo "bootstrap-functions: ok\n";
    require CONF . '/routes.php';
    echo "bootstrap-routes: ok\n";
} catch (Throwable $exception) {
    echo "bootstrap: failed\n";
    echo 'error: ' . get_class($exception) . "\n";
    echo 'message: ' . $exception->getMessage() . "\n";
    echo 'source: ' . basename($exception->getFile()) . ':' . $exception->getLine() . "\n";
}
