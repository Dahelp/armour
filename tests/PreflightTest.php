<?php
declare(strict_types=1);
$root=dirname(__DIR__);$source=(string)file_get_contents($root.'/bin/preflight.php');
if(!str_contains($source,'PHP_VERSION_ID<80200'))throw new RuntimeException('PHP 8.2 preflight check is missing.');
foreach(['pdo_mysql','--production','--with-db','APP_DEBUG','20260828_001_create_legacy_url_redirect.sql'] as $needle)if(!str_contains($source,$needle))throw new RuntimeException('Preflight check is incomplete: '.$needle);
require $root.'/config/environment.php';putenv('DB_HOST=db.example.test:3307');putenv('DB_PORT=3306');putenv('DB_DATABASE=test');putenv('DB_USERNAME=test');putenv('DB_PASSWORD=test');$db=require $root.'/config/config_db.php';
if(!str_contains($db['dsn'],'host=db.example.test;port=3307;'))throw new RuntimeException('DB_HOST host:port is not parsed correctly.');
echo "Production preflight checks passed.\n";
