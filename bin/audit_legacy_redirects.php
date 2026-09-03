<?php

declare(strict_types=1);

use app\services\LegacyUrlAuditService;
use app\services\LegacyUrlMapValidator;

require dirname(__DIR__) . '/vendor/autoload.php';

$filename = null;
$baseUrl = 'https://techtires.ru';
$canonicalBaseUrl = null;
$output = dirname(__DIR__) . '/tmp/reports/legacy-url-audit-' . date('Ymd-His');
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--base-url=')) {
        $baseUrl = substr($argument, strlen('--base-url='));
    } elseif (str_starts_with($argument, '--canonical-base-url=')) {
        $canonicalBaseUrl = substr($argument, strlen('--canonical-base-url='));
    } elseif (str_starts_with($argument, '--output=')) {
        $output = substr($argument, strlen('--output='));
    } elseif (!str_starts_with($argument, '--')) {
        $filename = $argument;
    }
}

if ($filename === null) {
    fwrite(STDERR, "Использование: php bin/audit_legacy_redirects.php <map.csv> [--base-url=https://techtires.ru] [--canonical-base-url=https://techtires.ru] [--output=tmp/reports/name]\n");
    exit(2);
}

$validation = (new LegacyUrlMapValidator())->validateCsv($filename);
if ($validation['errors'] !== []) {
    fwrite(STDERR, "Карта URL не прошла проверку:\n- " . implode("\n- ", $validation['errors']) . "\n");
    exit(1);
}

$auditor = new LegacyUrlAuditService();
$report = $auditor->audit($validation['rows'], $baseUrl, $canonicalBaseUrl);
$files = $auditor->writeReports($report, $output);
$passed = count(array_filter($report, static fn(array $row): bool => $row['result'] === 'PASS'));
$failed = count(array_filter($report, static fn(array $row): bool => $row['result'] === 'FAIL'));
$skipped = count($report) - $passed - $failed;
printf("Проверено: %d; успешно: %d; пропущено: %d; ошибок: %d.\nCSV: %s\nJSON: %s\n", count($report), $passed, $skipped, $failed, $files['csv'], $files['json']);
exit($failed === 0 ? 0 : 1);
