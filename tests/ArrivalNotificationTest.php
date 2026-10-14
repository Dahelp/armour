<?php

declare(strict_types=1);

function arrivalAssert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = dirname(__DIR__);
$controller = (string)file_get_contents($root . '/app/controllers/ProductController.php');
$model = (string)file_get_contents($root . '/app/models/Product.php');
$view = (string)file_get_contents($root . '/app/views/armour/Product/view.php');
$layout = (string)file_get_contents($root . '/app/views/armour/layouts/watches.php');

arrivalAssert(str_contains($view, 'Сообщить о поступлении') && str_contains($view, 'rel="form6"'), 'Out-of-stock button is missing.');
arrivalAssert(str_contains($view, 'if ((int)$product->quantity > 0)'), 'Invoice button must only be shown for available products.');
arrivalAssert(str_contains($layout, 'name="arrival_name"') && str_contains($layout, 'name="arrival_email"'), 'Arrival form fields are missing.');
arrivalAssert(!str_contains($layout, 'name="arrival_product"'), 'The form must not submit an editable product identity.');
arrivalAssert(str_contains($controller, "\$name_tovar = (string)\$product->name"), 'Product name must come from the resolved route product.');
arrivalAssert(str_contains($controller, 'FILTER_VALIDATE_EMAIL'), 'Arrival email must be validated server-side.');
arrivalAssert(str_contains($controller, 'mailArrivalNotification'), 'Arrival notification must be mailed.');
arrivalAssert(str_contains($model, 'setReplyTo'), 'Admin must be able to reply to the customer.');

echo "Arrival notification checks passed.\n";
