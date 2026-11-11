<?php
// Explicit oil-filter URL map; other filter sections are intentionally separate.
$oilFilterUrls = json_decode(file_get_contents(ROOT . '/resources/repairs/oil-filter-urls.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($oilFilterUrls as $oilFilterUrl) {
    if ($urli === $oilFilterUrl['old'] && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        header('Location: ' . rtrim(PATH, '/') . '/' . $oilFilterUrl['new'] . ($query !== null && $query !== '' ? '?' . $query : ''), true, 301);
        exit;
    }
}
unset($oilFilterUrls, $oilFilterUrl);
