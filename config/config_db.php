<?php

return [
    'dsn' => sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        config_env('DB_HOST', '127.0.0.1:3306'),
        config_env('DB_DATABASE', '')
    ),
    'user' => config_env('DB_USERNAME', ''),
    'pass' => config_env('DB_PASSWORD', ''),
];
