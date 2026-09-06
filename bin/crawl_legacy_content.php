<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$sitemap = $argv[1] ?? dirname(__DIR__) . '/tmp/armour-shina-sitemap.xml';
$output = $argv[2] ?? dirname(__DIR__) . '/tmp/reports/legacy-content-source.json';
if (!is_file($sitemap)) {
    fwrite(STDERR, "Карта старого сайта не найдена: {$sitemap}\n");
    exit(2);
}

$xml = (string)file_get_contents($sitemap);
preg_match_all('~https://(?:www\.)?armour-shina\.ru/(?:articles|news)-[^<]+?\.html~iu', $xml, $matches);
$urls = array_values(array_unique($matches[0] ?? []));
sort($urls, SORT_NATURAL);
if ($urls === []) {
    fwrite(STDERR, "В карте не найдены статьи или новости.\n");
    exit(1);
}

$rows = [];
foreach ($urls as $position => $url) {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_USERAGENT => 'TechTires legacy content migration/1.0',
    ]);
    $html = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if (!is_string($html) || $html === '') {
        fwrite(STDERR, "Не удалось получить {$url}: {$error}\n");
        exit(1);
    }

    $document = new DOMDocument();
    $old = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($old);
    $xpath = new DOMXPath($document);
    $headline = $xpath->query('//*[@itemprop="headline"]')->item(0);
    $body = $xpath->query('//*[@itemprop="articleBody"]')->item(0);
    if (!$headline || !$body) {
        $container = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " main_big_text ")]')->item(0);
        $headline = $headline ?: $xpath->query('.//h1', $container)->item(0);
        $body = $container;
    }
    if (!$headline || !$body) {
        fwrite(STDERR, "Не найдено содержимое статьи: {$url}\n");
        exit(1);
    }
    $parts = [];
    foreach ($body->childNodes as $node) {
        if ($node === $headline || ($node instanceof DOMElement && in_array('news_date', preg_split('/\s+/', $node->getAttribute('class')) ?: [], true))) {
            continue;
        }
        $parts[] = $document->saveHTML($node);
    }
    $meta = static function (DOMXPath $xpath, string $name): string {
        $node = $xpath->query('//meta[@name="' . $name . '"]')->item(0);
        return $node instanceof DOMElement ? trim($node->getAttribute('content')) : '';
    };
    $dateNode = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " articles_p ")]/p[1] | //p[contains(concat(" ", normalize-space(@class), " "), " news_date ")]')->item(0);
    $datePost = trim((string)($dateNode?->textContent ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePost)) {
        $datePost = date('Y-m-d');
    }
    $rows[] = [
        'source_url' => $url,
        'status' => $status,
        'h1' => trim($headline->textContent),
        'title' => trim((string)($xpath->query('//title')->item(0)?->textContent ?? '')),
        'description' => $meta($xpath, 'description'),
        'date_post' => $datePost,
        'content_html' => trim(implode('', $parts)),
    ];
    printf("[%d/%d] %s\n", $position + 1, count($urls), $url);
}

$directory = dirname($output);
if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
    throw new RuntimeException("Не удалось создать каталог: {$directory}");
}
file_put_contents($output, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), LOCK_EX);
printf("Сохранено материалов: %d. Файл: %s\n", count($rows), $output);
