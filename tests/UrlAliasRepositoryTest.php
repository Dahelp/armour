<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function aliasAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

use app\services\UrlAliasRepository;

$repositorySource = (string)file_get_contents(dirname(__DIR__) . '/app/services/UrlAliasRepository.php');
aliasAssert(!str_contains($repositorySource, "dispense('url_alias')"), 'RedBean cannot dispense underscore table names.');
aliasAssert(str_contains($repositorySource, 'INSERT INTO url_alias'), 'URL aliases cannot be inserted.');

aliasAssert(UrlAliasRepository::normaliseSef('/Catalog-VIL/') === 'catalog-vil', 'Slug case or slashes were not normalised.');
aliasAssert(UrlAliasRepository::normaliseSef('/old-product.html') === 'old-product.html', 'Legacy .html slug was rejected.');
aliasAssert(UrlAliasRepository::normaliseSef('catalog//vil') === 'catalog/vil', 'Duplicate slashes were not collapsed.');
aliasAssert(UrlAliasRepository::normaliseSef('../admin') === '', 'Traversal slug was accepted.');
aliasAssert(UrlAliasRepository::normaliseSef('https://evil.example') === '', 'Absolute external URL was accepted.');

$controllers = [
    'CategoryController.php',
    'BrandController.php',
    'ContentsController.php',
    'FiltrsController.php',
];
foreach ($controllers as $controller) {
    $source = (string)file_get_contents(dirname(__DIR__) . '/app/controllers/admin/' . $controller);
    aliasAssert(!preg_match('/url_alias.*(?:VALUES|SET sef).*\$/', $source), $controller . ' still interpolates URL aliases into SQL.');
}

echo "URL alias repository checks passed.\n";
