<?php

declare(strict_types=1);

function productCardAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$template = (string)file_get_contents($root . '/app/widgets/product/product_tpl.php');
$widget = (string)file_get_contents($root . '/app/widgets/product/Product.php');
$homepage = (string)file_get_contents($root . '/app/views/armour/Main/index.php');
$search = (string)file_get_contents($root . '/app/views/armour/Search/index.php');

productCardAssert(str_contains($template, 'product-card-unified'), 'Unified product card class is missing.');
productCardAssert(!str_contains($template, '13164'), 'A hard-coded product id remains in the card.');
productCardAssert(!str_contains($template, 'advanta-ekb.ru'), 'A legacy external comparison URL remains in the card.');
productCardAssert(!str_contains($template, '\\R::'), 'Product cards must not execute per-card database queries.');
productCardAssert(str_contains($template, 'data-id="<?=$productId?>"'), 'Dynamic cart product id is missing.');
productCardAssert(str_contains($widget, "str_ends_with(\$attribute, '.php')"), 'Legacy widget template argument compatibility is missing.');
productCardAssert(str_contains($homepage, 'new \\app\\widgets\\product\\Product'), 'Homepage does not use the shared card widget.');
productCardAssert(str_contains($search, 'new \\app\\widgets\\product\\Product'), 'Search does not use the shared card widget.');

echo "Product card template checks passed.\n";
