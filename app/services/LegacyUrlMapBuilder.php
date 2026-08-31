<?php

declare(strict_types=1);

namespace app\services;

final class LegacyUrlMapBuilder
{
    private const PUBLIC_VIEWS = ['product', 'tiposize', 'protect', 'marka', 'category', 'diameter', 'pages', 'main', 'disk'];

    /**
     * @param list<array<string, mixed>> $aliases
     * @return array{ready: list<array<string, int|string>>, review: list<array<string, string>>}
     */
    public function build(string $sitemapFile, array $aliases): array
    {
        $document = new \DOMDocument();
        if (!$document->load($sitemapFile, LIBXML_NONET)) {
            throw new \RuntimeException('Не удалось прочитать sitemap.xml.');
        }
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $targets = [];
        foreach ($aliases as $alias) {
            $view = strtolower(trim((string)($alias['view'] ?? '')));
            $sef = LegacyUrlRedirector::normalisePath((string)($alias['sef'] ?? ''));
            if ($sef !== '' && in_array($view, self::PUBLIC_VIEWS, true)) {
                $targets[$sef] = true;
            }
        }

        $ready = [];
        $review = [];
        $seen = [];
        foreach ($xpath->query('//s:url/s:loc') ?: [] as $node) {
            $url = trim((string)$node->textContent);
            $path = LegacyUrlRedirector::normalisePath((string)parse_url($url, PHP_URL_PATH));
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $candidate = preg_replace('/\.html$/i', '', $path) ?? $path;
            if ($candidate !== $path && isset($targets[$candidate])) {
                $ready[] = [
                    'source_path' => $path,
                    'target_path' => $candidate,
                    'status_code' => 301,
                    'is_active' => 1,
                ];
                continue;
            }
            $review[] = [
                'source_url' => $url,
                'source_path' => $path,
                'proposed_target' => '',
                'classification' => $this->classify($path),
                'reason' => 'no_exact_canonical_alias',
            ];
        }

        return ['ready' => $ready, 'review' => $review];
    }

    /** @param array{ready: list<array<string, int|string>>, review: list<array<string, string>>} $result */
    public function write(array $result, string $outputDirectory): array
    {
        if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
            throw new \RuntimeException('Не удалось создать каталог карты URL.');
        }
        $readyPath = rtrim($outputDirectory, '/\\') . '/legacy-urls-ready.csv';
        $reviewPath = rtrim($outputDirectory, '/\\') . '/legacy-urls-review.csv';
        $this->writeCsv($readyPath, ['source_path', 'target_path', 'status_code', 'is_active'], $result['ready']);
        $this->writeCsv($reviewPath, ['source_url', 'source_path', 'proposed_target', 'classification', 'reason'], $result['review']);
        return ['ready' => $readyPath, 'review' => $reviewPath];
    }

    private function classify(string $path): string
    {
        $slug = preg_replace('/\.html$/i', '', $path) ?? $path;
        return match (true) {
            str_starts_with($slug, 'crossing-') => 'deferred_crossing',
            preg_match('/^tovar-?\d+/i', $slug) === 1 => 'legacy_product_id',
            str_starts_with($slug, 'articles-'), str_starts_with($slug, 'news-') => 'content',
            str_starts_with($slug, 'catalog-'), str_starts_with($slug, 'fcat-') => 'catalog',
            str_starts_with($slug, 'tovar_disk-'), str_starts_with($slug, 'disk-') => 'disk',
            default => 'unclassified',
        };
    }

    /** @param list<array<string, int|string>> $rows */
    private function writeCsv(string $filename, array $columns, array $rows): void
    {
        $handle = fopen($filename, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Не удалось создать CSV: ' . $filename);
        }
        fputcsv($handle, $columns, ';', '"', '\\');
        foreach ($rows as $row) {
            fputcsv($handle, array_map(static fn(string $column) => $row[$column] ?? '', $columns), ';', '"', '\\');
        }
        fclose($handle);
    }
}
