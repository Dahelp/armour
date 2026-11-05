<?php
declare(strict_types=1);

// Build a value-preserving repair from the read-only production audit.
$audit = json_decode(file_get_contents($argv[1] ?? ''), true, 512, JSON_THROW_ON_ERROR);
$normalize = static fn(string $value): string => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value)), 'UTF-8');
$definitions = array_column($audit['destination_attributes'], null, 'id');
$sourceDefinitions = array_column($audit['source_attributes'], null, 'attribute_id');
$goods = array_column($audit['source_goods'], null, 'goods_id');
$sourceByProduct = [];
foreach ($audit['source_goods_attributes'] as $row) {
    if (in_array((int)$row['attribute_group_id'], [8, 9], true)) {
        $sourceByProduct[$row['goods_id']][] = $row;
    }
}
$destinationByArticle = [];
foreach ($audit['destination_filters'] as $row) {
    $destinationByArticle[(string)$row['article']][$row['id']][] = $row;
}
$plan = ['version'=>1, 'definition_changes'=>[], 'products'=>[], 'conflicts'=>[], 'absent_products'=>[], 'without_original_dimensions'=>[]];
// A pre-existing typo assigns an inner diameter to another attribute, not a group.
if (($definitions[77]['attribute_group_id'] ?? null) === 73) {
    $plan['definition_changes'][] = ['id'=>77, 'from'=>73, 'to'=>3, 'name'=>$definitions[77]['attribute_name']];
    $definitions[77]['attribute_group_id'] = 3;
}
foreach ($sourceByProduct as $goodsId => $rows) {
    if (count(array_unique(array_column($rows, 'attribute_group_id'))) < 2) {
        continue; // A lone misclassified dimension does not establish a two-part filter.
    }
    $product = $goods[$goodsId];
    $article = (string)$product['article'];
    $matches = $destinationByArticle[$article] ?? [];
    if ($matches === []) {
        $plan['absent_products'][] = $article;
        continue;
    }
    if (count($matches) !== 1) {
        $plan['conflicts'][] = ['article'=>$article, 'reason'=>'Missing or ambiguous destination product'];
        continue;
    }
    $current = array_values($matches)[0];
    $available = array_column($current, null, 'row_id');
    $changes = [];
    $problems = [];
    foreach ($rows as $source) {
        $name = $sourceDefinitions[$source['attribute_id']]['attribute_name'];
        $group = (int)$source['attribute_group_id'] === 8 ? 2 : 3;
        $candidates = array_filter($definitions, static fn(array $definition): bool => (int)$definition['attribute_group_id'] === $group && $normalize($definition['attribute_name']) === $normalize($name));
        if (count($candidates) !== 1) {
            $problems[] = 'Missing or ambiguous grouped definition: '.$name;
            continue;
        }
        $desired = array_values($candidates)[0];
        $matching = array_filter($available, static function(array $row) use ($source, $name, $definitions, $normalize): bool {
            return isset($definitions[$row['attribute_id']]) && $normalize($definitions[$row['attribute_id']]['attribute_name']) === $normalize($name) && (string)$row['attribute_text'] === (string)$source['attribute_text'];
        });
        // Prefer an already-correct occurrence when two elements have the same value.
        uasort($matching, static fn(array $a, array $b): int => ((int)$b['attribute_id'] === (int)$desired['id']) <=> ((int)$a['attribute_id'] === (int)$desired['id']));
        if ($matching === []) {
            $problems[] = 'Original dimension has no exact destination value: '.$name.'='.$source['attribute_text'];
            continue;
        }
        $before = reset($matching);
        unset($available[$before['row_id']]);
        if ((int)$before['attribute_id'] !== (int)$desired['id'] || (int)$before['attribute_group_id'] !== $group) {
            $changes[] = ['row_id'=>(int)$before['row_id'], 'product_id'=>(int)$before['id'], 'text'=>(string)$before['attribute_text'], 'from_attribute'=>(int)$before['attribute_id'], 'from_group'=>(int)$before['attribute_group_id'], 'to_attribute'=>(int)$desired['id'], 'to_group'=>$group, 'name'=>$name];
        }
    }
    if ($problems !== []) {
        $plan['conflicts'][] = ['article'=>$article, 'reason'=>$problems];
    } else {
        $plan['products'][] = ['id'=>(int)$current[0]['id'], 'article'=>$article, 'name'=>$current[0]['name'], 'alias'=>$current[0]['alias'], 'source_dimensions'=>count($rows), 'changes'=>$changes];
    }
}
foreach ($goods as $goodsId => $product) {
    if (preg_match('/(?:AB|АВ)\b/ui', $product['model']) && !isset($sourceByProduct[$goodsId]) && isset($destinationByArticle[(string)$product['article']])) {
        $plan['without_original_dimensions'][] = ['article'=>(string)$product['article'], 'name'=>$product['name']];
    }
}
echo json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
