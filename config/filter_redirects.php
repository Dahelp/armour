<?php
// Remaining filter sections, with a fixed map so unrelated filter-* URLs stay untouched.
if (preg_match('/^filter-\d+$/D', $urli) && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    $filterUrls = json_decode(file_get_contents(ROOT . '/resources/repairs/remaining-filter-urls.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach ($filterUrls as $filterUrl) {
        if ($urli === $filterUrl['old']) {
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            header('Location: ' . rtrim(PATH, '/') . '/' . $filterUrl['new'] . ($query !== null && $query !== '' ? '?' . $query : ''), true, 301);
            exit;
        }
    }
    unset($filterUrls, $filterUrl);
}
