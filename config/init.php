<?php

require_once __DIR__ . '/environment.php';

define("APP_ENV", config_env('APP_ENV', 'production'));
$debug = filter_var(config_env('APP_DEBUG', '0'), FILTER_VALIDATE_BOOL);
define("DEBUG", $debug);
define("ROOT", dirname(__DIR__));
define("WWW", ROOT . '/public');
define("APP", ROOT . '/app');
define("CORE", ROOT . '/vendor/ishop/core');
define("LIBS", ROOT . '/vendor/ishop/core/libs');
define("CACHE", ROOT . '/tmp/cache');
define("CONF", ROOT . '/config');
define("LAYOUT", 'watches');
define("TEMPLATE", 'armour');
$configuredUrl = trim((string)config_env('APP_URL', ''));
if ($configuredUrl !== '') {
    $app_path = rtrim($configuredUrl, '/');
} else {
    $https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = preg_replace('/[^a-z0-9.\-:\[\]]/i', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $basePath = preg_replace('#/public/index\.php$#', '', $script);
    $basePath = preg_replace('#/index\.php$#', '', (string)$basePath);
    $app_path = $scheme . '://' . $host . rtrim((string)$basePath, '/');
}
define("PATH", $app_path);
define("ADMIN", PATH . '/admin');

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (str_starts_with($app_path, 'https://')) {
    ini_set('session.cookie_secure', '1');
}

require_once ROOT . '/vendor/autoload.php';
