<?php

declare(strict_types=1);

function breadcrumbAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$breadcrumbSources = [
    $root . '/app/models/Breadcrumbs.php',
    $root . '/app/views/armour/Cross/view.php',
];

foreach ($breadcrumbSources as $sourcePath) {
    $source = (string) file_get_contents($sourcePath);
    breadcrumbAssert(
        !preg_match('~href=[\'\"](?:<\?=\s*PATH\s*\?>)?/catalog[\'\"]~', $source),
        basename($sourcePath) . ' must not link breadcrumbs to the missing /catalog page.'
    );
}

echo "Breadcrumb link checks passed.\n";
