<?php

declare(strict_types=1);

namespace app\services;

final class LegacyUrlAuditService
{
    /** @var callable(string): array{status: int, headers: array<string, string>, body: string} */
    private $fetcher;

    public function __construct(?callable $fetcher = null, private readonly int $maxRedirects = 5)
    {
        $client = new LegacyUrlHttpClient();
        $this->fetcher = $fetcher ?? static fn(string $url): array => $client->get($url);
    }

    /**
     * @param list<array{source_path: string, target_path: string, status_code: int, is_active: int}> $rows
     * @return list<array<string, int|string>>
     */
    public function audit(array $rows, string $baseUrl): array
    {
        $baseUrl = $this->normaliseBaseUrl($baseUrl);
        $allowedHost = strtolower((string)parse_url($baseUrl, PHP_URL_HOST));
        $report = [];

        foreach ($rows as $row) {
            $sourceUrl = $baseUrl . '/' . $row['source_path'];
            $expectedUrl = $baseUrl . '/' . $row['target_path'];
            if ($row['is_active'] !== 1) {
                $report[] = [
                    'source_path' => $row['source_path'],
                    'expected_target' => $row['target_path'],
                    'first_status' => 0,
                    'redirects' => 0,
                    'final_status' => 0,
                    'final_url' => $sourceUrl,
                    'canonical' => '',
                    'result' => 'SKIP',
                    'issues' => 'inactive',
                ];
                continue;
            }
            $currentUrl = $sourceUrl;
            $redirects = 0;
            $issues = [];
            $firstStatus = 0;
            $finalStatus = 0;
            $canonical = '';

            try {
                while (true) {
                    $response = ($this->fetcher)($currentUrl);
                    $status = (int)$response['status'];
                    $firstStatus = $firstStatus ?: $status;
                    $finalStatus = $status;

                    if (!in_array($status, [301, 302, 303, 307, 308], true)) {
                        $canonical = $this->extractCanonical((string)$response['body'], $currentUrl);
                        $robots = strtolower(
                            ((string)($response['headers']['x-robots-tag'] ?? '')) . ' '
                            . $this->extractRobots((string)$response['body'])
                        );
                        if (str_contains($robots, 'noindex')) {
                            $issues[] = 'noindex';
                        }
                        break;
                    }

                    ++$redirects;
                    if ($redirects > $this->maxRedirects) {
                        $issues[] = 'too_many_redirects';
                        break;
                    }
                    $location = trim((string)($response['headers']['location'] ?? ''));
                    if ($location === '') {
                        $issues[] = 'redirect_without_location';
                        break;
                    }
                    $nextUrl = $this->resolveUrl($currentUrl, $location);
                    if ($nextUrl === '' || strtolower((string)parse_url($nextUrl, PHP_URL_HOST)) !== $allowedHost) {
                        $issues[] = 'external_or_invalid_redirect';
                        break;
                    }
                    if ($nextUrl === $currentUrl) {
                        $issues[] = 'redirect_loop';
                        break;
                    }
                    $currentUrl = $nextUrl;
                }

                if (!in_array($firstStatus, [301, 308], true)) {
                    $issues[] = 'source_not_permanent_redirect';
                }
                if ($firstStatus !== $row['status_code']) {
                    $issues[] = 'unexpected_redirect_status';
                }
                if ($redirects !== 1) {
                    $issues[] = 'redirect_chain_length_' . $redirects;
                }
                if ($finalStatus !== 200) {
                    $issues[] = 'final_status_' . $finalStatus;
                }
                if ($this->comparableUrl($currentUrl) !== $this->comparableUrl($expectedUrl)) {
                    $issues[] = 'unexpected_final_url';
                }
                if ($canonical === '') {
                    $issues[] = 'canonical_missing';
                } elseif ($this->comparableUrl($canonical) !== $this->comparableUrl($expectedUrl)) {
                    $issues[] = 'canonical_mismatch';
                }
            } catch (\Throwable $exception) {
                $issues[] = 'request_error:' . preg_replace('/[\r\n]+/', ' ', $exception->getMessage());
            }

            $issues = array_values(array_unique($issues));
            $report[] = [
                'source_path' => $row['source_path'],
                'expected_target' => $row['target_path'],
                'first_status' => $firstStatus,
                'redirects' => $redirects,
                'final_status' => $finalStatus,
                'final_url' => $currentUrl,
                'canonical' => $canonical,
                'result' => $issues === [] ? 'PASS' : 'FAIL',
                'issues' => implode('|', $issues),
            ];
        }

        return $report;
    }

    /** @param list<array<string, int|string>> $report */
    public function writeReports(array $report, string $outputPrefix): array
    {
        $directory = dirname($outputPrefix);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Не удалось создать каталог отчёта.');
        }

        $csvPath = $outputPrefix . '.csv';
        $jsonPath = $outputPrefix . '.json';
        $handle = fopen($csvPath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Не удалось создать CSV-отчёт.');
        }
        $columns = ['source_path', 'expected_target', 'first_status', 'redirects', 'final_status', 'final_url', 'canonical', 'result', 'issues'];
        fputcsv($handle, $columns, ';', '"', '\\');
        foreach ($report as $row) {
            fputcsv($handle, array_map(static fn(string $column) => $row[$column] ?? '', $columns), ';', '"', '\\');
        }
        fclose($handle);

        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (file_put_contents($jsonPath, $json . PHP_EOL, LOCK_EX) === false) {
            throw new \RuntimeException('Не удалось создать JSON-отчёт.');
        }

        return ['csv' => $csvPath, 'json' => $jsonPath];
    }

    private function normaliseBaseUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host'])) {
            throw new \InvalidArgumentException('base-url должен быть абсолютным HTTP(S) URL.');
        }
        return $url;
    }

    private function resolveUrl(string $currentUrl, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }
        if (str_starts_with($location, '//')) {
            return (string)parse_url($currentUrl, PHP_URL_SCHEME) . ':' . $location;
        }
        $origin = (string)parse_url($currentUrl, PHP_URL_SCHEME) . '://' . (string)parse_url($currentUrl, PHP_URL_HOST);
        $port = parse_url($currentUrl, PHP_URL_PORT);
        if (is_int($port)) {
            $origin .= ':' . $port;
        }
        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }
        $path = (string)parse_url($currentUrl, PHP_URL_PATH);
        return $origin . rtrim(str_replace('\\', '/', dirname($path)), '/') . '/' . $location;
    }

    private function extractCanonical(string $html, string $pageUrl): string
    {
        if ($html === '') {
            return '';
        }
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$loaded) {
            return '';
        }
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query('//link[contains(concat(" ", translate(@rel, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " "), " canonical ")]/@href');
        $href = trim((string)($nodes?->item(0)?->nodeValue ?? ''));
        return $href === '' ? '' : $this->resolveUrl($pageUrl, $href);
    }

    private function extractRobots(string $html): string
    {
        if (preg_match('/<meta[^>]+name=["\']robots["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $match)) {
            return $match[1];
        }
        if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']robots["\']/i', $html, $match)) {
            return $match[1];
        }
        return '';
    }

    private function comparableUrl(string $url): string
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return '';
        }
        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        $host = strtolower((string)($parts['host'] ?? ''));
        $path = '/' . trim(rawurldecode((string)($parts['path'] ?? '')), '/');
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        return $scheme . '://' . $host . ($path === '/' ? '/' : rtrim($path, '/')) . $query;
    }
}
