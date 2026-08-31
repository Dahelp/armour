<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/environment.php';
require dirname(__DIR__) . '/vendor/autoload.php';

function csrfAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$_SESSION = [];
$token = \app\services\CsrfProtection::token();
csrfAssert(strlen($token) === 64, 'CSRF token must contain 256 bits of entropy.');
csrfAssert(\app\services\CsrfProtection::token() === $token, 'CSRF token must remain stable during a session.');

$_SERVER = ['REQUEST_METHOD' => 'GET'];
$_POST = [];
\app\services\CsrfProtection::validateRequest();

$_SERVER = ['REQUEST_METHOD' => 'POST', 'HTTP_X_CSRF_TOKEN' => $token];
$_POST = [];
\app\services\CsrfProtection::validateRequest();

$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'HTTP_HOST' => 'techtires.ru',
    'HTTP_ORIGIN' => 'https://techtires.ru',
];
$_POST = [];
\app\services\CsrfProtection::validateRequest();

$_SERVER['HTTP_ORIGIN'] = 'https://attacker.example';
try {
    \app\services\CsrfProtection::validateRequest();
    throw new RuntimeException('A foreign origin was accepted.');
} catch (Throwable $exception) {
    csrfAssert($exception->getCode() === 419, 'A foreign origin must return HTTP 419.');
}

$productSource = (string)file_get_contents(dirname(__DIR__) . '/app/controllers/ProductController.php');
csrfAssert(!str_contains($productSource, "VALUES ('\".\$user_id"), 'Product request inserts must be parameterized.');
csrfAssert(str_contains($productSource, '$product_id = (int)$product->id'), 'Requests must use the product resolved from the route.');
csrfAssert(str_contains($productSource, '$data["product_id"] = (int)$reviewProduct->id'), 'Reviews must use the product resolved from the route.');
csrfAssert(str_contains($productSource, 'INSERT INTO review_product (review_id, product_id) VALUES (?, ?)'), 'Reviews must be linked with a parameterized query.');

$csrfScript = (string)file_get_contents(dirname(__DIR__) . '/public/js/csrf.js');
csrfAssert(str_contains($csrfScript, 'X-CSRF-Token'), 'AJAX requests must receive the CSRF header.');
csrfAssert(str_contains($csrfScript, "input.name = '_csrf'"), 'POST forms must receive the CSRF field.');

echo "CSRF protection checks passed.\n";
