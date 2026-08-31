<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function mapBuilderAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$sitemap = tempnam(sys_get_temp_dir(), 'sitemap-');
if ($sitemap === false) {
    throw new RuntimeException('Unable to create fixture.');
}
file_put_contents($sitemap, '<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
    . '<url><loc>https://armour-shina.ru/product-one.html</loc></url>'
    . '<url><loc>https://armour-shina.ru/crossing-123.html</loc></url></urlset>');
$aliases = [
    ['sef' => 'product-one', 'view' => 'Product'],
    ['sef' => 'crossing-123', 'view' => 'Cart'],
];
$result = (new \app\services\LegacyUrlMapBuilder())->build($sitemap, $aliases);
@unlink($sitemap);

mapBuilderAssert(count($result['ready']) === 1, 'Exact public alias was not mapped.');
mapBuilderAssert($result['ready'][0]['target_path'] === 'product-one', 'Canonical target is incorrect.');
mapBuilderAssert(count($result['review']) === 1, 'Unsafe alias was not sent for review.');
mapBuilderAssert($result['review'][0]['classification'] === 'deferred_crossing', 'Deferred crossing URL was not classified.');

echo "Legacy URL map builder checks passed.\n";
