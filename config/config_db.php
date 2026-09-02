<?php

$host = trim((string)config_env('DB_HOST', '127.0.0.1'));
$port = (int)config_env('DB_PORT', 3306);
if (preg_match('/^(.+):(\d+)$/', $host, $matches) && !str_starts_with($host, '[')) {
    $host = $matches[1];
    $port = (int)$matches[2];
}

return [
    'dsn' => sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        config_env('DB_DATABASE', '')
    ),
    'user' => config_env('DB_USERNAME', ''),
    'pass' => config_env('DB_PASSWORD', ''),
];
