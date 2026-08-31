<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use app\models\Cart;

function assertCartRuntime(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$_SESSION = [
    'cart.currency' => ['value' => 1.0, 'base' => 1, 'code' => 'RUB'],
];
$product = (object)[
    'id' => 42,
    'name' => 'Test product',
    'price' => 100,
    'article' => 'A-42',
    'unit' => 'шт',
    'weight' => 2,
    'volume' => 0.5,
    'category_id' => 7,
    'model' => 'M42',
    'alias' => 'test-product',
    'img' => 'test.webp',
];

$cart = (new ReflectionClass(Cart::class))->newInstanceWithoutConstructor();
$cart->addToCart($product, 5, 3);
assertCartRuntime($_SESSION['cart'][42]['qty'] === 3, 'Cart quantity must be capped by available stock.');
assertCartRuntime($_SESSION['cart.qty'] === 3, 'Cart total quantity is inconsistent.');
assertCartRuntime($_SESSION['cart.weight'] === 6.0, 'Cart total weight is inconsistent.');

$cart->pluscartItem(42);
assertCartRuntime($_SESSION['cart'][42]['qty'] === 3, 'Plus action must respect available stock.');
$cart->minuscartItem(42);
assertCartRuntime($_SESSION['cart'][42]['qty'] === 2, 'Minus action did not update quantity.');
$cart->deleteItem(42);
assertCartRuntime(!isset($_SESSION['cart'][42]), 'Deleted item remains in cart.');
assertCartRuntime($_SESSION['cart.qty'] === 0, 'Delete action did not subtract the full quantity.');
assertCartRuntime($_SESSION['cart.weight'] === 0.0, 'Delete action did not subtract quantity-adjusted weight.');

$root = dirname(__DIR__);
$controller = file_get_contents($root . '/app/controllers/CartController.php');
$order = file_get_contents($root . '/app/models/Order.php');
$managerMail = file_get_contents($root . '/app/views/armour/mail/mail_manager.php');

assertCartRuntime(str_contains($controller, '$mod = (object)['), 'Unified modification must be initialized before property assignment.');
assertCartRuntime(str_contains($controller, "\$_SESSION['cart'][\$id]['qty'] ?? 0"), 'Removed cart items must have a safe JSON response.');
assertCartRuntime(str_contains($order, "(array)(\$_SESSION['cart'] ?? [])"), 'Order creation must tolerate an absent cart session.');
assertCartRuntime(str_contains($managerMail, '$itog_qty = 0; $sum = 0;'), 'Mail totals must be initialized under PHP 8.2.');

echo "Cart runtime compatibility tests passed.\n";
