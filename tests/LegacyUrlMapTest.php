<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function legacyMapAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$validator = new \app\services\LegacyUrlMapValidator();
$valid = $validator->validateRows([
    ['line' => 2, 'source_path' => 'https://armour-shina.ru/OLD-TIRE.HTML', 'target_path' => 'https://techtires.ru/200-50-10-celnolitaya-belaya-nm-ist'],
    ['line' => 3, 'source_path' => '/catalog-vil.html', 'target_path' => '/catalog-vil', 'status_code' => 301, 'is_active' => 1],
]);
legacyMapAssert($valid['errors'] === [], 'Valid legacy URLs were rejected.');
legacyMapAssert(count($valid['rows']) === 2, 'Valid rows were not retained.');
legacyMapAssert($valid['rows'][0]['source_path'] === 'old-tire.html', 'Source URL was not normalised.');
legacyMapAssert($valid['rows'][0]['target_path'] === '200-50-10-celnolitaya-belaya-nm-ist', 'Target URL was not normalised.');

$external = $validator->validateRows([
    ['source_path' => '/old.html', 'target_path' => 'https://evil.example/product'],
]);
legacyMapAssert($external['rows'] === [] && $external['errors'] !== [], 'External redirect target was accepted.');

$duplicate = $validator->validateRows([
    ['line' => 2, 'source_path' => '/old.html', 'target_path' => '/new'],
    ['line' => 3, 'source_path' => '/old.html', 'target_path' => '/other'],
]);
legacyMapAssert(str_contains(implode(' ', $duplicate['errors']), 'уже указан'), 'Duplicate source was not detected.');

$chain = $validator->validateRows([
    ['source_path' => '/one.html', 'target_path' => '/two.html'],
    ['source_path' => '/two.html', 'target_path' => '/final'],
]);
legacyMapAssert(str_contains(implode(' ', $chain['errors']), 'Цепочка'), 'Redirect chain was not detected.');

legacyMapAssert(\app\services\LegacyUrlRedirector::normalisePath("safe%0d%0aLocation:%20https://evil.example") === '', 'Header injection path was accepted.');

$importer = (string)file_get_contents(dirname(__DIR__) . '/bin/import_legacy_redirects.php');
legacyMapAssert(str_contains($importer, "in_array('--apply'"), 'Importer does not default to dry-run.');
legacyMapAssert(str_contains($importer, 'LegacyUrlRedirectRepository'), 'Importer cannot persist verified redirects.');

echo "Legacy URL map checks passed.\n";
