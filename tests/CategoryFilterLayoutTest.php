<?php

declare(strict_types=1);

function categoryFilterLayoutAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$categoryView = (string) file_get_contents($root . '/app/views/armour/Category/view.php');
$filterTemplate = (string) file_get_contents($root . '/app/widgets/filter/filter_tpl.php');

categoryFilterLayoutAssert(
    str_contains($categoryView, '$filtersHtml = trim((string) ob_get_clean());'),
    'Category filters must be captured before rendering their layout wrapper.'
);
categoryFilterLayoutAssert(
    str_contains($categoryView, "if(\$filtersHtml !== '')"),
    'The filter layout wrapper must be omitted when the widget has no filters.'
);
categoryFilterLayoutAssert(
    str_contains($filterTemplate, "if(!empty(\$this->attrs[\$group_id]))"),
    'Filter groups without available attribute values must not render empty rows.'
);

echo "Category filter layout checks passed.\n";
