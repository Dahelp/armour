<?php

declare(strict_types=1);

namespace app\services;

final class SitemapGenerator
{
    private const MAX_URLS = 50000;

    public function generate(string $outputPath = WWW . '/sitemap.xml'): int
    {
        $urls = $this->collectUrls();
        if (count($urls) > self::MAX_URLS) {
            throw new \RuntimeException('Sitemap exceeds the 50,000 URL protocol limit.');
        }

        $directory = dirname($outputPath);
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \RuntimeException('Sitemap output directory is not writable.');
        }

        $xml = $this->buildXml($urls);
        if (file_put_contents($outputPath, $xml, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write sitemap.xml.');
        }

        return count($urls);
    }

    /** @return list<string> */
    public function collectUrls(): array
    {
        $paths = [''];

        foreach (\R::getAll(
            "SELECT ua.sef
             FROM product p
             INNER JOIN url_alias ua ON ua.urlid = p.id AND ua.view = 'Product'
             WHERE p.hide = 'show' AND ua.sef != ''"
        ) as $row) {
            $paths[] = (string)$row['sef'];
        }

        foreach (\R::getAll(
            "SELECT ua.sef
             FROM category c
             INNER JOIN url_alias ua ON ua.urlid = c.id AND ua.view = 'Category'
             WHERE c.hide = 'show' AND ua.sef != ''"
        ) as $row) {
            $paths[] = (string)$row['sef'];
        }

        $contents = \R::getAll(
            "SELECT ua.sef
             FROM contents c
             INNER JOIN content_type ct ON ct.id = c.type_id
             INNER JOIN url_alias ua ON ua.urlid = c.id AND ua.view = ct.param_url
             WHERE c.hide = 'show' AND ct.hide = 'show' AND ua.sef != ''"
        );
        foreach ($contents as $row) {
            $paths[] = (string)$row['sef'];
        }

        $attributeUrls = \R::getAll(
            "SELECT ag.url_params, av.value
             FROM attribute_group ag
             INNER JOIN attribute_value av ON av.attr_group_id = ag.id
             WHERE ag.url_params != '' AND av.hide = 'show' AND av.value != ''"
        );
        foreach ($attributeUrls as $row) {
            $paths[] = trim((string)$row['url_params'], '/') . '/' . trim((string)$row['value'], '/');
        }

        $baseUrl = rtrim((string)config_env('APP_URL', PATH), '/');
        $urls = [];
        foreach ($paths as $path) {
            $normalisedPath = $this->normalisePublicPath($path);
            if ($normalisedPath === null) {
                continue;
            }
            $urls[] = $normalisedPath === '' ? $baseUrl . '/' : $baseUrl . '/' . $normalisedPath;
        }

        $urls = array_values(array_unique($urls));
        sort($urls, SORT_STRING);

        return $urls;
    }

    /** @param list<string> $urls */
    public function buildXml(array $urls): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($urls as $url) {
            $location = htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $lines[] = '  <url><loc>' . $location . '</loc></url>';
        }
        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }

    public function normalisePublicPath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        if ($path === '') {
            return '';
        }
        if (str_contains($path, '..') || preg_match('#[^a-z0-9._~!$&\'()*+,;=:@%/-]#i', $path)) {
            return null;
        }

        return $path;
    }
}
