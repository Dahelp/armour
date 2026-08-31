<?php

declare(strict_types=1);

function assertOrderWrite(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$model = file_get_contents($root . '/app/models/admin/Order.php');
$controller = file_get_contents($root . '/app/controllers/admin/OrderController.php');

assertOrderWrite(!str_contains($model, '.seller.'), 'PHP 8 must not evaluate seller as an undefined constant.');
assertOrderWrite(!str_contains($model, '$sql_part'), 'Order rows must not be assembled through SQL string concatenation.');
assertOrderWrite(str_contains($model, "'(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'"), 'Order products must use bound placeholders.');
assertOrderWrite(str_contains($model, 'return (int)\\R::store($user);'), 'New user creation must return its actual inserted id.');
assertOrderWrite(str_contains($model, 'return (int)\\R::store($company);'), 'New company creation must return its actual inserted id.');
assertOrderWrite(str_contains($model, '$id = (int)\\R::store($order);'), 'New order creation must use its actual inserted id.');
assertOrderWrite(!str_contains($controller, "findLast('order')"), 'Order creation must not infer an id from the last row.');
assertOrderWrite(!str_contains($controller, "findLast('user')"), 'User creation must not infer an id from the last row.');
assertOrderWrite(!str_contains($controller, "findLast('company')"), 'Company creation must not infer an id from the last row.');
assertOrderWrite(substr_count($controller, '\\R::begin();') >= 2, 'Order create and edit writes must be transactional.');

echo "Admin order write tests passed.\n";
