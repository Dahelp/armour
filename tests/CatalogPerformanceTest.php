<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function performanceAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$loader = new \app\services\CatalogListingLoader();
$attributes = $loader->mapAttributes([
    ['product_id' => 10, 'attribute_id' => 4, 'attribute_text' => '12-16.5'],
    ['product_id' => 10, 'attribute_id' => 5, 'attribute_text' => '14'],
    ['product_id' => 20, 'attribute_id' => 4, 'attribute_text' => '10-16.5'],
]);
$brands = $loader->mapBrands([
    ['id' => 3, 'name' => 'Techking'],
    ['id' => 5, 'name' => 'Advance'],
]);

performanceAssert($attributes[10][4] === '12-16.5', 'Product attributes were grouped incorrectly.');
performanceAssert($attributes[20][4] === '10-16.5', 'Attributes leaked between products.');
performanceAssert($brands[5]['name'] === 'Advance', 'Brands were indexed incorrectly.');

$hotTemplates = array_merge(
    glob(dirname(__DIR__) . '/app/widgets/product/product_table_*_tpl.php') ?: [],
    [
        dirname(__DIR__) . '/app/views/armour/Category/view.php',
        dirname(__DIR__) . '/app/views/armour/Category/filter.php',
        dirname(__DIR__) . '/app/views/armour/Product/view.php',
    ]
);
foreach ($hotTemplates as $template) {
    $source = (string)file_get_contents($template);
    performanceAssert(!str_contains($source, '\\R::'), basename($template) . ' must not query the database while rendering.');
}

$breadcrumbsSource = (string)file_get_contents(dirname(__DIR__) . '/app/models/Breadcrumbs.php');
performanceAssert(!str_contains($breadcrumbsSource, '\\R::'), 'Breadcrumb construction must use the cached category tree.');

echo "Catalog performance checks passed.\n";
