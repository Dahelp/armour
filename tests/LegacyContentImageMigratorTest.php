<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$source=(string)file_get_contents(dirname(__DIR__).'/app/services/LegacyContentImageMigrator.php');
if(!str_contains($source,"['armour-shina.ru','www.armour-shina.ru']"))throw new RuntimeException('Image source allowlist is missing.');
if(!str_contains($source,"setAttribute('loading','lazy')"))throw new RuntimeException('Migrated images are not lazy-loaded.');
if(!str_contains($source,'RemoteImageDownloader'))throw new RuntimeException('Secure image downloader is not used.');
echo "Legacy content image migration checks passed.\n";
