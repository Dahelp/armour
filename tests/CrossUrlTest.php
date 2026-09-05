<?php
declare(strict_types=1);

define('PATH','https://techtires.ru');
require dirname(__DIR__).'/vendor/autoload.php';

function crossUrlAssert(bool $condition,string $message): void {if(!$condition)throw new RuntimeException($message);}

crossUrlAssert(\app\services\CrossUrl::legacyAlias('crossing-ZP3118F.html')==='zp3118f','Legacy crossing URL was not parsed.');
crossUrlAssert(\app\services\CrossUrl::legacyAlias('catalog-vil')==='','Non-crossing URL must not match.');
crossUrlAssert(\app\services\CrossUrl::canonicalPath(' ZP3118F ')==='cross/zp3118f','Canonical cross path is incorrect.');
crossUrlAssert(\app\services\CrossUrl::canonicalPath('../admin')==='','Unsafe cross alias must be rejected.');
crossUrlAssert(\app\services\CrossUrl::canonicalPath('CA 6684')==='','Whitespace cross alias must not produce an unreachable canonical URL.');
crossUrlAssert(\app\services\CrossUrl::canonicalPath('КТF0098A')==='','Non-ASCII cross alias must not produce an unreachable canonical URL.');
\ishop\Router::reset();
\ishop\Router::add('^cross/(?P<alias>[a-z0-9._~%+-]+)/?$',['controller'=>'Cross','action'=>'view','prefix'=>'']);
crossUrlAssert(\ishop\Router::matchRoute('cross/ZP3118F'),'Cross route did not match a valid alias.');
crossUrlAssert((\ishop\Router::getRoute()['alias']??'')==='ZP3118F','Cross route lost its alias.');

$controller=(string)file_get_contents(dirname(__DIR__).'/app/controllers/CrossController.php');
crossUrlAssert(str_contains($controller,'c.cross_abbreviated_name=?'),'Cross lookup must use an indexable bound parameter.');
$redirector=(string)file_get_contents(dirname(__DIR__).'/app/services/LegacyCrossRedirector.php');
crossUrlAssert(str_contains($redirector,'p.alias AS product_alias'),'Malformed legacy crosses must have a product fallback.');
$view=(string)file_get_contents(dirname(__DIR__).'/app/views/armour/Cross/view.php');
crossUrlAssert(str_contains($view,'application/ld+json'),'Cross page Product JSON-LD is missing.');
crossUrlAssert(str_contains($view,'JSON_HEX_TAG'),'Cross JSON-LD must be HTML-safe.');

echo "Cross URL checks passed.\n";
