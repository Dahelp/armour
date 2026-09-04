<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
function productionAssert(bool $value,string $message):void{if(!$value)throw new RuntimeException($message);}
$public=(string)file_get_contents(dirname(__DIR__).'/public/.htaccess');
foreach(['X-Content-Type-Options','X-Frame-Options','Referrer-Policy','Permissions-Policy'] as $header)productionAssert(str_contains($public,$header),$header.' header is missing.');
productionAssert(str_contains($public,'ExpiresDefault "access plus 0 seconds"'),'Dynamic responses inherit a positive cache lifetime.');
$root=(string)file_get_contents(dirname(__DIR__).'/.htaccess');
productionAssert(str_contains($root,'RewriteRule ^public/ - [L]'),'Root rewrite can recurse into public/.');
$upload=(string)file_get_contents(dirname(__DIR__).'/public/upload/.htaccess');
productionAssert(str_contains($upload,'Require all denied'),'Executable uploads are not denied for Apache 2.4.');
$kcfinder=(string)file_get_contents(dirname(__DIR__).'/public/adminlte/bower_components/kcfinder/lib/class_image.php');
productionAssert(!preg_match('/\beach\s*\(/',$kcfinder),'KCFinder still uses removed PHP each().');
$legacyRedirect=(string)file_get_contents(dirname(__DIR__).'/ops/armour-shina-redirect/.htaccess');
productionAssert(str_contains($legacyRedirect,'https://techtires.ru%{REQUEST_URI}'),'Old-domain redirect must preserve the request path.');
productionAssert(str_contains($legacyRedirect,'[R=301,L,NE]'),'Old-domain redirect must be permanent.');
$openRobots=(string)file_get_contents(dirname(__DIR__).'/ops/production/robots.open.txt');
productionAssert(!preg_match('/^Disallow:\s*\/$/m',$openRobots),'Open-indexing robots template blocks the whole site.');
productionAssert(str_contains($openRobots,'Sitemap: https://techtires.ru/sitemap.xml'),'Open-indexing robots template has no sitemap.');
echo "Production configuration checks passed.\n";
