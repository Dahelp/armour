<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function searchSqlAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$source = (string)file_get_contents(dirname(__DIR__) . '/app/controllers/SearchController.php');
$mainJs = (string)file_get_contents(dirname(__DIR__) . '/public/js/main.js');
$typeaheadJs = (string)file_get_contents(dirname(__DIR__) . '/public/js/typeahead.bundle.js');
$layout = (string)file_get_contents(dirname(__DIR__) . '/app/views/armour/layouts/watches.php');

searchSqlAssert(
    substr_count($source, 'FROM attribute_product ap') === 3,
    'Typeahead, result count, and result listing must all search product attributes.'
);
searchSqlAssert(
    substr_count($source, 'INNER JOIN attribute_value av ON av.id = ap.attr_id') === 3,
    'Attribute values are not joined consistently in every search query.'
);
searchSqlAssert(
    substr_count($source, "REPLACE(av.value, ',', '.') LIKE ?") === 3,
    'Search queries do not match the filter attribute value against the current product.'
);
searchSqlAssert(
    substr_count($source, "WHERE product.hide = 'show'") === 3,
    'Cross-number search must not expose hidden products.'
);
searchSqlAssert(
    !str_contains($source, 'concat(name, article)'),
    'Nullable article fields must not suppress a product-name match.'
);

$controller = (new ReflectionClass(\app\controllers\SearchController::class))->newInstanceWithoutConstructor();
$normaliseQuery = new ReflectionMethod($controller, 'normaliseQuery');
searchSqlAssert(
    $normaliseQuery->invoke($controller, '16,9-28') === '16.9-28',
    'A decimal comma in a tyre size must be equivalent to a decimal point.'
);
searchSqlAssert(
    $normaliseQuery->invoke($controller, '16.9-28') === '16.9-28',
    'A decimal point in a tyre size must remain unchanged.'
);
searchSqlAssert(
    $normaliseQuery->invoke($controller, 'модель, шина') === 'модель, шина',
    'Ordinary punctuation outside a numeric value must remain unchanged.'
);
searchSqlAssert(
    str_contains($source, "header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')"),
    'Dynamic typeahead responses must not be retained by the browser cache.'
);
searchSqlAssert(
    str_contains($mainJs, 'cache: false')
        && str_contains($mainJs, 'source: searchProducts')
        && str_contains($mainJs, 'asyncResults(Array.isArray(items) ? items : [])'),
    'The typeahead client must bypass stale caches and pass the complete API result to the menu.'
);
searchSqlAssert(
    str_contains($typeaheadJs, 'suggestions = suggestions.slice(0, that.limit - rendered);')
        && !str_contains($typeaheadJs, 'that._append(query, suggestions.slice(0, that.limit - rendered));'),
    'Typeahead must calculate the remaining result limit before incrementing the rendered count.'
);
searchSqlAssert(
    str_contains($layout, "typeahead.bundle.js?v=<?=filemtime(WWW.'/js/typeahead.bundle.js')?>"),
    'The corrected typeahead bundle must be cache-busted in the storefront layout.'
);

echo "Search SQL checks passed.\n";
