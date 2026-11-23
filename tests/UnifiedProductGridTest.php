<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/ishop/core/libs/functions.php';
function gridAssert(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$products = [
    ['id' => 1, 'name' => 'Шина 18*7-8', 'alias' => 'existing-tire-alias', 'price' => 1500, 'quantity' => 12],
    ['id' => 2, 'name' => 'Шина 23.5-25', 'alias' => 'another-tire', 'price' => 0, 'quantity' => 0],
];
ob_start();
\app\widgets\product\Product::listing($products, ['value' => 1]);
$html = ob_get_clean();
$dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="UTF-8">' . $html); $xp = new DOMXPath($dom);
gridAssert($xp->query('//ul/li')->length === 2, 'Cards must be direct list items, not orphan li inside divs.');
gridAssert($xp->query('//li//ul')->length === 0, 'Product lists must not be nested inside cards.');
gridAssert(str_contains($html, '18x7-8') && str_contains($html, 'href="/existing-tire-alias"'), 'Names or existing links changed.');
gridAssert(str_contains($html, 'В наличии: 12 шт.') && str_contains($html, 'Цена по запросу'), 'Stock or unconfirmed-price state is missing.');
gridAssert($xp->query('//*[@itemprop="offers"]')->length === 1, 'Zero prices must not be advertised as an Offer.');
foreach (['Tiposize', 'Marka', 'Protect', 'Diameter', 'Disk', 'Ground', 'Rim', 'Tipshiny'] as $controller) {
    gridAssert(str_contains(file_get_contents(dirname(__DIR__).'/app/views/armour/'.$controller.'/view.php'), 'partials/filter-listing.php'), 'Filter page does not share the listing renderer: '.$controller);
}
echo "Unified product grid checks passed.\n";
