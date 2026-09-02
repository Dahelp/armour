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
echo "Production configuration checks passed.\n";
