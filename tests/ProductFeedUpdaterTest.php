<?php

declare(strict_types=1);

function feedAssert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = dirname(__DIR__);
$service = (string)file_get_contents($root . '/app/services/ProductFeedUpdater.php');
$controller = (string)file_get_contents($root . '/app/controllers/CronController.php');
$admin = (string)file_get_contents($root . '/app/views/armour/admin/Cron/index.php');

feedAssert(str_contains($service, 'CURLOPT_SSL_VERIFYPEER => true'), 'Product feed TLS verification must be enabled.');
feedAssert(str_contains($service, "str_getcsv(\$line, ';')"), 'Product feed must be parsed without a temporary cron directory.');
feedAssert(!str_contains($service, 'file_put_contents'), 'Product feed must not depend on a writable relative cron directory.');
feedAssert(str_contains($service, '\\R::begin()') && str_contains($service, '\\R::rollback()'), 'Product updates must be transactional.');
feedAssert(str_contains($controller, 'new ProductFeedUpdater()'), 'Refresh endpoint must use the validated updater.');
feedAssert(str_contains($controller, "header('Content-Type: application/json; charset=utf-8')"), 'Refresh endpoint must return structured JSON.');
feedAssert(str_contains($admin, 'xhr.responseJSON.error'), 'Admin runner must display the server error message.');

echo "Product feed updater checks passed.\n";
