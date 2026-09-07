<?php
declare(strict_types=1);

function legalIdentityAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$files = [
    $root . '/app/views/armour/layouts/watches.php',
    $root . '/app/views/armour/Information/_company.php',
    $root . '/app/views/armour/Information/_contacts.php',
    $root . '/app/views/armour/Information/_privacy.php',
    $root . '/app/views/armour/Information/_consent.php',
    $root . '/app/views/armour/User/pricelist.php',
];

foreach ($files as $file) {
    $content = (string)file_get_contents($file);
    legalIdentityAssert(!preg_match('/ИТС[- ]?Центр|1105074000096|5036103305|503601001|40702810901080002314/u', $content), 'Obsolete legal identity remains in ' . $file);
}

$legalPages = implode("\n", array_map(static fn(string $file): string => (string)file_get_contents($file), array_slice($files, 0, 5)));
legalIdentityAssert(str_contains($legalPages, 'ООО «Еккатрейд»'), 'New company name is missing.');
legalIdentityAssert(str_contains($legalPages, '5074079702'), 'New INN is missing.');
legalIdentityAssert(str_contains($legalPages, '507401001'), 'New KPP is missing.');
legalIdentityAssert(str_contains($legalPages, '1235000003259'), 'New OGRN is missing.');
legalIdentityAssert(str_contains($legalPages, 'Фабричный проезд'), 'New legal address is missing.');
legalIdentityAssert(str_contains((string)file_get_contents($root . '/app/views/armour/Information/_delivery.php'), 'Коммунальная, 26, стр. 2'), 'Pickup address was changed or removed.');

echo "Legal identity checks passed.\n";
