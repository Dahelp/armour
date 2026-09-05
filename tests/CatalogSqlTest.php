<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

function sqlAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$filterReflection = new ReflectionClass(\app\widgets\filter\Filter::class);
$normaliseIds = $filterReflection->getMethod('normaliseIds');
$normalised = $normaliseIds->invoke(null, '7,2,7,invalid,-4,0,11');
sqlAssert($normalised === [2, 7, 11], 'Category IDs must be unique positive integers in stable order.');

$categorySource = (string)file_get_contents(dirname(__DIR__) . '/app/controllers/CategoryController.php');
sqlAssert(!str_contains($categorySource, 'attr_id IN ($filter)'), 'Filter IDs must not be interpolated into SQL.');
sqlAssert(!str_contains($categorySource, 'category_id IN ($ids)'), 'Category IDs must not be interpolated into SQL.');
sqlAssert(str_contains($categorySource, '\\R::genSlots($filterIds)'), 'Filter placeholders are missing.');
sqlAssert(str_contains($categorySource, 'COUNT(DISTINCT av.attr_group_id)'), 'Multiple values in one group must use OR semantics.');

$filterSource = (string)file_get_contents(dirname(__DIR__) . '/app/widgets/filter/Filter.php');
sqlAssert(!str_contains($filterSource, '.$ids.'), 'Filter widget IDs must not be interpolated into SQL.');

\ishop\App::$app = \ishop\Registry::instance();
\ishop\App::$app->setProperty('cats', [
    1 => ['parent_id' => 0],
    2 => ['parent_id' => 1],
    3 => ['parent_id' => 2],
    4 => ['parent_id' => 1],
]);
$categoryModel = (new ReflectionClass(\app\models\Category::class))->newInstanceWithoutConstructor();
$treeIds = $categoryModel->getIdList(1);
sort($treeIds, SORT_NUMERIC);
sqlAssert($treeIds === [1, 2, 3, 4], 'Category descendants were collected incorrectly.');

$migration = (string)file_get_contents(dirname(__DIR__) . '/database/migrations/20260828_003_add_catalog_performance_indexes.sql');
foreach (['product', 'attribute_product', 'product_attribute', 'related_product', 'similar_product', 'review_product'] as $table) {
    sqlAssert(str_contains($migration, "'$table'"), "Missing performance index for $table.");
}

echo "Catalog SQL checks passed.\n";
