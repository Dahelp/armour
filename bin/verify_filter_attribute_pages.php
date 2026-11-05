<?php
declare(strict_types=1);

$plan = json_decode(file_get_contents($argv[1] ?? ''), true, 512, JSON_THROW_ON_ERROR);
$normalise = static fn(string $text): string => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)), 'UTF-8');
$results = [];
foreach (array_chunk($plan['products'], 4) as $batch) {
    $multi = curl_multi_init();
    $handles = [];
    foreach ($batch as $product) {
        $handle = curl_init('https://techtires.ru/'.rawurlencode($product['alias']));
        curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>30, CURLOPT_CONNECTTIMEOUT=>10, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_USERAGENT=>'Techtires filter repair verification']);
        $handles[] = [$handle, $product];
        curl_multi_add_handle($multi, $handle);
    }
    do {
        $status = curl_multi_exec($multi, $active);
        if ($active) curl_multi_select($multi, 1);
    } while ($active && $status === CURLM_OK);
    foreach ($handles as [$handle, $product]) {
        $html = curl_multi_getcontent($handle);
        if (curl_getinfo($handle, CURLINFO_RESPONSE_CODE) !== 200) throw new RuntimeException('Page failed: '.$product['alias']);
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xpath = new DOMXPath($document);
        $groups = [];
        foreach ($xpath->query('//th[contains(@class,"attribute_group_name")]') as $heading) {
            $name = trim($heading->textContent);
            $row = $heading->parentNode->nextSibling;
            while ($row && !($row instanceof DOMElement)) $row = $row->nextSibling;
            $groups[$name] = [];
            foreach ($xpath->query('.//tr[th and td]', $row) as $attribute) {
                $groups[$name][] = [$normalise($xpath->query('./th', $attribute)[0]->textContent), trim($xpath->query('./td', $attribute)[0]->textContent)];
            }
        }
        if (count($groups['Внешний'] ?? []) + count($groups['Внутренний'] ?? []) !== $product['source_dimensions']) throw new RuntimeException('Grouped dimension count differs: '.$product['alias']);
        foreach ($product['changes'] as $change) {
            $group = $change['to_group'] === 2 ? 'Внешний' : 'Внутренний';
            if (!in_array([$normalise($change['name']),$change['text']], $groups[$group] ?? [], true)) throw new RuntimeException('Dimension not rendered in '.$group.': '.$product['alias']);
        }
        $results[] = ['article'=>$product['article'], 'url'=>'https://techtires.ru/'.$product['alias'], 'outer'=>count($groups['Внешний'] ?? []), 'inner'=>count($groups['Внутренний'] ?? []), 'ok'=>true];
        curl_multi_remove_handle($multi, $handle);
    }
    curl_multi_close($multi);
}
echo json_encode(['ok'=>true,'checked'=>count($results),'products'=>$results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
