<?php
declare(strict_types=1);

function informationAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$routes = (string)file_get_contents($root . '/config/routes.php');
$controller = (string)file_get_contents($root . '/app/controllers/PagesController.php');
$layout = (string)file_get_contents($root . '/app/views/armour/layouts/watches.php');
$callback = (string)file_get_contents($root . '/app/controllers/CallbackController.php');
$sendmail = (string)file_get_contents($root . '/app/controllers/SendmailController.php');

foreach (['dostavka', 'comp', 'contacts', 'politika-konfidencialnosti', 'soglasie-na-obrabotku-personalnyh-dannyh', 'politika-fajlov-cookie', 'polzovatelskoe-soglashenie'] as $path) {
    informationAssert(str_contains($routes, "^{$path}"), "Static route is missing: {$path}");
}

foreach (['delivery', 'company', 'contacts', 'privacy', 'consent', 'cookies', 'terms'] as $page) {
    informationAssert(str_contains($controller, "'{$page}' =>"), "Information page metadata is missing: {$page}");
    informationAssert(is_file($root . "/app/views/armour/Information/_{$page}.php"), "Information page template is missing: {$page}");
}

informationAssert(!str_contains($layout, 'href="/actions"'), 'Actions page is still linked from the layout.');
informationAssert(!str_contains($layout, 'href="/info"'), 'Empty information landing page is still linked from the menu.');
informationAssert(str_contains($layout, 'favicon-techtires.svg'), 'New favicon is not connected.');
informationAssert(substr_count($layout, 'name="privacy_accept"') === 4, 'Consent must be present in all public modal forms.');
informationAssert(str_contains($callback, 'PersonalDataConsent::accepted'), 'Callback consent is not validated server-side.');
informationAssert(str_contains($sendmail, 'PersonalDataConsent::accepted'), 'Message consent is not validated server-side.');
informationAssert(!str_contains($layout, 'info@armour-shina.ru'), 'Old Armour email remains in the public layout.');

echo "Information and legal page checks passed.\n";
