<?php
declare(strict_types=1);

define('PATH','https://techtires.ru');
require dirname(__DIR__).'/vendor/autoload.php';

function crossUrlAssert(bool $condition,string $message): void {if(!$condition)throw new RuntimeException($message);}

crossUrlAssert(\app\services\CrossUrl::legacyAlias('crossing-ZP3118F.html')==='zp3118f','Legacy crossing URL was not parsed.');
crossUrlAssert(\app\services\CrossUrl::legacyAlias('catalog-vil')==='','Non-crossing URL must not match.');
crossUrlAssert(\app\services\CrossUrl::canonicalPath(' ZP3118F ')==='cross/zp3118f','Canonical cross path is incorrect.');
crossUrlAssert(\app\services\CrossUrl::canonicalPath('../admin')==='','Unsafe cross alias must be rejected.');

$controller=(string)file_get_contents(dirname(__DIR__).'/app/controllers/CrossController.php');
crossUrlAssert(str_contains($controller,'c.cross_abbreviated_name=?'),'Cross lookup must use an indexable bound parameter.');
$view=(string)file_get_contents(dirname(__DIR__).'/app/views/armour/Cross/view.php');
crossUrlAssert(str_contains($view,'application/ld+json'),'Cross page Product JSON-LD is missing.');
crossUrlAssert(str_contains($view,'JSON_HEX_TAG'),'Cross JSON-LD must be HTML-safe.');

echo "Cross URL checks passed.\n";
