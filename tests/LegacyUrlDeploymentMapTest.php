<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function deploymentMapAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$rows = [
    ['source_path' => 'old.html', 'target_path' => 'new', 'status_code' => 301, 'is_active' => 1],
    ['source_path' => 'hidden.html', 'target_path' => 'hidden', 'status_code' => 301, 'is_active' => 1],
];
$audit = [
    ['source_path' => 'old.html', 'expected_target' => 'new', 'result' => 'PASS', 'issues' => ''],
    ['source_path' => 'hidden.html', 'expected_target' => 'hidden', 'result' => 'FAIL', 'issues' => 'final_status_404'],
];
$maps = (new \app\services\LegacyUrlDeploymentMapService())->split($rows, $audit);
deploymentMapAssert(count($maps['ready']) === 1, 'Passing redirect was not selected for production.');
deploymentMapAssert(count($maps['deferred']) === 1, 'Failing redirect was not deferred.');
deploymentMapAssert($maps['deferred'][0]['is_active'] === 0, 'Deferred redirect remained active.');
deploymentMapAssert($maps['deferred'][0]['audit_issues'] === 'final_status_404', 'Audit reason was not preserved.');

echo "Legacy URL deployment map checks passed.\n";
