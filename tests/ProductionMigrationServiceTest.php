<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$source = (string)file_get_contents(dirname(__DIR__) . '/app/services/ProductionMigrationService.php');
if (!str_contains($source, 'CREATE TABLE IF NOT EXISTS legacy_url_redirect')) {
    throw new RuntimeException('Redirect migration is missing.');
}
if (!str_contains($source, 'information_schema.statistics')) {
    throw new RuntimeException('Idempotent index checks are missing.');
}
if (!str_contains($source, 'LegacyUrlMapValidator') || !str_contains($source, 'LegacyUrlRedirectRepository')) {
    throw new RuntimeException('Validated redirect import is missing.');
}

echo "Production migration service checks passed.\n";
