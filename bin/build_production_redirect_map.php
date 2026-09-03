<?php

declare(strict_types=1);

use app\services\LegacyUrlDeploymentMapService;
use app\services\LegacyUrlMapValidator;

require dirname(__DIR__) . '/vendor/autoload.php';

[$script, $mapFile, $auditFile, $outputDirectory] = array_pad($argv, 4, null);
if ($mapFile === null || $auditFile === null || $outputDirectory === null) {
    fwrite(STDERR, "Использование: php bin/build_production_redirect_map.php <map.csv> <audit.json> <output-directory>\n");
    exit(2);
}

$validation = (new LegacyUrlMapValidator())->validateCsv($mapFile);
if ($validation['errors'] !== []) {
    fwrite(STDERR, "Карта URL не прошла проверку:\n- " . implode("\n- ", $validation['errors']) . "\n");
    exit(1);
}
$json = file_get_contents($auditFile);
if ($json === false) {
    throw new RuntimeException('Не удалось прочитать HTTP-аудит.');
}
$audit = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($audit)) {
    throw new RuntimeException('HTTP-аудит имеет неверный формат.');
}

$service = new LegacyUrlDeploymentMapService();
$maps = $service->split($validation['rows'], $audit);
$readyFile = rtrim($outputDirectory, '/\\') . '/legacy-urls-production.csv';
$deferredFile = rtrim($outputDirectory, '/\\') . '/legacy-urls-deferred.csv';
$service->writeCsv($readyFile, $maps['ready']);
$service->writeCsv($deferredFile, $maps['deferred'], true);

printf(
    "Готово к production: %d; отложено: %d.\nProduction: %s\nОтложенные: %s\n",
    count($maps['ready']),
    count($maps['deferred']),
    $readyFile,
    $deferredFile
);
