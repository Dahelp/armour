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
}
