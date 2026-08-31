<?php

declare(strict_types=1);

use app\services\LegacyUrlMapBuilder;
use app\services\LegacyUrlMapValidator;
use app\services\SqlDumpTableReader;

require dirname(__DIR__) . '/vendor/autoload.php';

$sitemap = $argv[1] ?? '';
$dump = $argv[2] ?? '';
$output = $argv[3] ?? (dirname(__DIR__) . '/tmp/reports/legacy-url-map');
if ($sitemap === '' || $dump === '') {
    fwrite(STDERR, "Использование: php bin/build_legacy_url_map.php <sitemap.xml> <database.sql> [output-directory]\n");
    exit(2);
}

$aliases = (new SqlDumpTableReader())->readTable($dump, 'url_alias');
$builder = new LegacyUrlMapBuilder();
$result = $builder->build($sitemap, $aliases);
$validation = (new LegacyUrlMapValidator())->validateRows($result['ready']);
if ($validation['errors'] !== []) {
    fwrite(STDERR, "Готовая карта не прошла контроль:\n- " . implode("\n- ", $validation['errors']) . "\n");
    exit(1);
}
$result['ready'] = $validation['rows'];
$files = $builder->write($result, $output);
printf(
    "Точных сопоставлений: %d; требуют решения: %d.\nГотовая карта: %s\nНа проверку: %s\n",
    count($result['ready']),
    count($result['review']),
    $files['ready'],
    $files['review']
);
