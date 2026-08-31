<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use app\models\Cart;

function assertPhp82(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$method = new ReflectionMethod(Cart::class, 'addToCart');
$parameters = $method->getParameters();
assertPhp82(!$parameters[1]->isOptional(), 'Cart quantity must not be optional before required max quantity.');
assertPhp82(!$parameters[2]->isOptional(), 'Cart max quantity is required.');
assertPhp82($parameters[3]->isOptional(), 'Cart modification must remain optional.');

$root = dirname(__DIR__);
$expectedSnippets = [
    '/app/widgets/menu/menu_tpl/menu.php' => "\$cat['parent_id']",
    '/app/views/armour/User/order.php' => "\$summa[0]['qty']",
    '/app/models/admin/ContentsPages.php' => "['option_size_product']",
    '/app/models/admin/PlaginsComplete.php' => "['option_size_product']",
    '/app/models/admin/PlaginsTechnics.php' => "['option_size_product']",
    '/app/models/admin/Product.php' => "['option_size_product']",
    '/app/models/admin/Review.php' => "['option_size_product']",
];
foreach ($expectedSnippets as $file => $snippet) {
    $source = file_get_contents($root . $file);
    assertPhp82(str_contains($source, $snippet), "PHP 8 string key is missing in $file.");
}

echo "PHP 8.2 compatibility tests passed.\n";
