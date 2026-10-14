<?php

declare(strict_types=1);

function cronAdminAssert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = dirname(__DIR__);
$view = (string)file_get_contents($root . '/app/views/armour/admin/Cron/index.php');
$sync = (string)file_get_contents($root . '/bin/sync_legacy_cron.php');

cronAdminAssert(str_contains($view, "event.preventDefault()"), 'Manual cron launch must not be interrupted by navigation.');
cronAdminAssert(str_contains($view, "class=\"btn btn-link p-0 ml-1 js-cron-run\""), 'Manual launch button is missing.');
cronAdminAssert(str_contains($view, "Выполняется…") && str_contains($view, "Выполнено"), 'Manual launch status is missing.');
cronAdminAssert(str_contains($view, "CRON-задания пока не настроены."), 'Empty cron configuration is not explained.');
cronAdminAssert(str_contains($sync, "cron_backup_pre_legacy_sync"), 'Legacy cron sync must create a recovery backup.');
cronAdminAssert(str_contains($sync, "WHERE url_params = ?"), 'Legacy cron sync must upsert by system name.');
cronAdminAssert(str_contains($sync, "SHOW DATABASES") && str_contains($sync, "discoverCronSchema"), 'Legacy cron database must be auto-discovered when its name is not configured.');

echo "Cron admin checks passed.\n";
