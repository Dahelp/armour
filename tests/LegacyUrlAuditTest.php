<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function legacyAuditAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$responses = [
    'https://techtires.ru/old-product.html' => [
        'status' => 301,
        'headers' => ['location' => '/new-product'],
        'body' => '',
    ],
    'https://techtires.ru/new-product' => [
        'status' => 200,
        'headers' => ['content-type' => 'text/html; charset=UTF-8'],
        'body' => '<html><head><link rel="canonical" href="https://techtires.ru/new-product"><meta name="robots" content="index, follow"></head></html>',
    ],
];
$fetcher = static function (string $url) use (&$responses): array {
    if (!isset($responses[$url])) {
        throw new RuntimeException('Unexpected URL: ' . $url);
    }
    return $responses[$url];
};
$rows = [['source_path' => 'old-product.html', 'target_path' => 'new-product', 'status_code' => 301, 'is_active' => 1]];
$report = (new \app\services\LegacyUrlAuditService($fetcher))->audit($rows, 'https://techtires.ru');
legacyAuditAssert($report[0]['result'] === 'PASS', 'A correct one-hop redirect failed the audit: ' . $report[0]['issues']);
legacyAuditAssert($report[0]['redirects'] === 1, 'Redirect count is incorrect.');
legacyAuditAssert($report[0]['canonical'] === 'https://techtires.ru/new-product', 'Canonical URL was not extracted.');

$encodedRequests = [];
$encodedFetcher = static function (string $url) use (&$encodedRequests): array {
    $encodedRequests[] = $url;
    if (count($encodedRequests) === 1) {
        return ['status' => 301, 'headers' => ['location' => '/tiposize-20.5%2070-16'], 'body' => ''];
    }
    return [
        'status' => 200,
        'headers' => [],
        'body' => '<link rel="canonical" href="/tiposize-20.5%2070-16">',
    ];
};
$encodedRows = [['source_path' => 'tiposize-20.5 70-16.html', 'target_path' => 'tiposize-20.5 70-16', 'status_code' => 301, 'is_active' => 1]];
$encodedReport = (new \app\services\LegacyUrlAuditService($encodedFetcher))->audit($encodedRows, 'https://techtires.ru');
legacyAuditAssert($encodedRequests[0] === 'https://techtires.ru/tiposize-20.5%2070-16.html', 'A legacy path containing spaces was not URL-encoded.');
legacyAuditAssert($encodedReport[0]['result'] === 'PASS', 'An encoded legacy URL failed the audit: ' . $encodedReport[0]['issues']);

$stagingReport = (new \app\services\LegacyUrlAuditService($fetcher))->audit(
    $rows,
    'http://127.0.0.1:8082',
    'https://techtires.ru'
);
legacyAuditAssert(str_contains((string)$stagingReport[0]['issues'], 'request_error:Unexpected URL'), 'Staging request base was not used independently.');

$responses['https://techtires.ru/new-product'] = [
    'status' => 302,
    'headers' => ['location' => '/final-product'],
    'body' => '',
];
$responses['https://techtires.ru/final-product'] = [
    'status' => 200,
    'headers' => ['x-robots-tag' => 'noindex'],
    'body' => '<link rel="canonical" href="/final-product">',
];
$report = (new \app\services\LegacyUrlAuditService($fetcher))->audit($rows, 'https://techtires.ru');
legacyAuditAssert($report[0]['result'] === 'FAIL', 'A redirect chain was accepted.');
legacyAuditAssert(str_contains((string)$report[0]['issues'], 'redirect_chain_length_2'), 'Redirect chain was not reported.');
legacyAuditAssert(str_contains((string)$report[0]['issues'], 'noindex'), 'Noindex was not reported.');
legacyAuditAssert(str_contains((string)$report[0]['issues'], 'unexpected_final_url'), 'Unexpected final URL was not reported.');

$source = (string)file_get_contents(dirname(__DIR__) . '/app/services/LegacyUrlHttpClient.php');
legacyAuditAssert(str_contains($source, 'CURLOPT_SSL_VERIFYPEER => true'), 'TLS certificate verification is disabled.');
legacyAuditAssert(str_contains($source, 'CURLOPT_FOLLOWLOCATION => false'), 'HTTP client follows redirects outside the auditor.');

echo "Legacy URL HTTP audit checks passed.\n";
