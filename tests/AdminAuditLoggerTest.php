<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use app\services\AdminAuditLogger;

function assertAdminAudit(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

foreach ([[0, 1, 'product', 1], [2, 0, 'product', 1], [2, 1, 'product', 0], [2, 1, 'product; DROP TABLE product', 1]] as $arguments) {
    $rejected = false;
    try {
        AdminAuditLogger::log(...$arguments);
    } catch (InvalidArgumentException) {
        $rejected = true;
    }
    assertAdminAudit($rejected, 'Invalid audit parameters must be rejected before a database write.');
}

$root = dirname(__DIR__);
$controllerDirectory = $root . '/app/controllers/admin';
$rawAuditWrites = [];
$centralizedCalls = 0;
foreach (glob($controllerDirectory . '/*Controller.php') ?: [] as $file) {
    $source = file_get_contents($file);
    if (str_contains($source, 'INSERT INTO `admin_last_history`') || str_contains($source, 'INSERT INTO admin_last_history')) {
        $rawAuditWrites[] = basename($file);
    }
    $centralizedCalls += substr_count($source, 'AdminAuditLogger::log(');
}

assertAdminAudit($rawAuditWrites === [], 'Controllers still contain direct audit SQL: ' . implode(', ', $rawAuditWrites));
assertAdminAudit($centralizedCalls >= 50, 'Expected administrative actions were not migrated to the shared logger.');

$logger = file_get_contents($root . '/app/services/AdminAuditLogger.php');
assertAdminAudit(str_contains($logger, 'VALUES (?, ?, ?, ?, ?, ?)'), 'Audit logger must use bound parameters.');
assertAdminAudit(str_contains($logger, "\$_SESSION['user']['id'] ?? 0"), 'Audit logger must identify the current administrator safely.');

echo "Admin audit logger tests passed.\n";
