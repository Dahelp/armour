<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/services/PersonalDataConsent.php';

use app\services\PersonalDataConsent;

function consentAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

consentAssert(PersonalDataConsent::accepted(['privacy_accept' => '1']), 'Valid consent was rejected.');
consentAssert(PersonalDataConsent::accepted(['privacy_accept' => 1]), 'Integer consent was rejected.');
consentAssert(!PersonalDataConsent::accepted([]), 'Missing consent was accepted.');
consentAssert(!PersonalDataConsent::accepted(['privacy_accept' => '0']), 'Negative consent was accepted.');

$root = dirname(__DIR__);
$controllers = [
    'CallbackController.php',
    'SendmailController.php',
    'OneclickController.php',
    'ZchetController.php',
    'CartController.php',
    'UserController.php',
];

foreach ($controllers as $controller) {
    $content = (string)file_get_contents($root . '/app/controllers/' . $controller);
    consentAssert(str_contains($content, 'PersonalDataConsent::accepted'), 'Server-side consent check is missing in ' . $controller);
}

$layout = (string)file_get_contents($root . '/app/views/armour/layouts/watches.php');
consentAssert(substr_count($layout, 'name="privacy_accept"') === 4, 'All four public modal forms must use the unified consent field.');
consentAssert(str_contains($layout, 'data-cookie-acknowledge'), 'Cookie notice acknowledgement is missing.');
consentAssert(str_contains($layout, 'Аналитические и рекламные cookie сейчас не подключены'), 'Cookie notice must describe the actual cookie usage.');
consentAssert(!str_contains($layout, 'data-cookie-preference'), 'Misleading cookie preference controls remain in the layout.');
consentAssert(str_contains($layout, 'data-cookie-settings'), 'Cookie settings control is missing.');

foreach (['Cart/view.php', 'User/signup.php', 'User/edit.php', 'User/company.php'] as $view) {
    $content = (string)file_get_contents($root . '/app/views/armour/' . $view);
    consentAssert(str_contains($content, 'partials/privacy-consent.php'), 'Consent partial is missing in ' . $view);
}

echo "Personal data consent checks passed.\n";
