<?php

declare(strict_types=1);

function breadcrumbAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$modelPath = $root . '/app/models/Breadcrumbs.php';
$modelSource = (string) file_get_contents($modelPath);
breadcrumbAssert(!str_contains($modelSource, '/catalog'), 'Breadcrumb generator must not link to the missing /catalog page.');

$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/views/armour'));
foreach ($views as $view) {
    if (!$view->isFile() || $view->getExtension() !== 'php') {
        continue;
    }

    $path = str_replace('\\', '/', $view->getPathname());
    if (str_contains($path, '/admin/') || str_contains($path, '/mail/') || str_contains($path, '/Cron/')) {
        continue;
    }

    $source = (string) file_get_contents($path);
    breadcrumbAssert(
        !preg_match('~<(?:nav|ol)[^>]*class=[\'\"][^\'\"]*breadcrumb~i', $source),
        $path . ' contains hand-written breadcrumb markup; use Breadcrumbs::render().'
    );
    breadcrumbAssert(
        !preg_match('~href=[\'\"](?:<\?=\s*PATH\s*\?>)?/catalog[\'\"]~', $source),
        $path . ' links to the missing /catalog page.'
    );
}

echo "Breadcrumb link checks passed.\n";
