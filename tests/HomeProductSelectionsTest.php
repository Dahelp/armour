<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/services/HomeProductSelections.php';

function selectionCheck(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
function selectionProduct(int $id, array $overrides = []): array { return array_replace(['id'=>$id,'hide'=>'show','price'=>100,'quantity'=>2], $overrides); }
$result = \app\services\HomeProductSelections::distinct([
    'sales'=>[selectionProduct(1),selectionProduct(2),selectionProduct(3),selectionProduct(4),selectionProduct(5)],
    'hits'=>[selectionProduct(1),selectionProduct(6,['quantity'=>0]),selectionProduct(7,['price'=>0]),selectionProduct(8,['hide'=>'hide']),selectionProduct(9),selectionProduct(10),selectionProduct(11),selectionProduct(12)],
    'new_products'=>[selectionProduct(9),selectionProduct(13),selectionProduct(14)],
]);
selectionCheck(array_column($result['sales'],'id') === [1,2,3,4], 'Limit each selection to four products and preserve promotion priority.');
selectionCheck(array_column($result['hits'],'id') === [9,10,11,12], 'Backfill duplicate, hidden, unavailable and unpriced candidates.');
selectionCheck(array_column($result['new_products'],'id') === [13,14], 'New selections must not repeat previously selected products.');
selectionCheck(\app\services\HomeProductSelections::distinct([]) === ['sales'=>[],'hits'=>[],'new_products'=>[]], 'Empty selections must remain empty.');
echo "Homepage available product selections and duplicate backfill checks passed.\n";
