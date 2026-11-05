<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use app\services\ProductAttributeXmlMapper;

$definitions = [
    ['id'=>1,'attribute_group_id'=>0,'attribute_name'=>'Характеристики'],
    ['id'=>2,'attribute_group_id'=>0,'attribute_name'=>'Внешний'],
    ['id'=>3,'attribute_group_id'=>0,'attribute_name'=>'Внутренний'],
    ['id'=>52,'attribute_group_id'=>1,'attribute_name'=>'Высота, мм'],
    ['id'=>70,'attribute_group_id'=>2,'attribute_name'=>'Высота, мм'],
    ['id'=>79,'attribute_group_id'=>3,'attribute_name'=>'Высота, мм'],
];
$mapper = new ProductAttributeXmlMapper();
$xml = new SimpleXMLElement('<offer><param name="Высота, мм" group="Внешний">442</param><param name="Высота, мм" group="Внутренний">402</param></offer>');
$rows = $mapper->map($xml, $definitions);
if ($rows !== [['attribute_id'=>70,'attribute_group_id'=>2,'text'=>'442'],['attribute_id'=>79,'attribute_group_id'=>3,'text'=>'402']]) throw new RuntimeException('Two-part grouping lost.');
$single = $mapper->map(new SimpleXMLElement('<offer><param name="Высота, мм">442</param></offer>'), $definitions);
if ($single[0]['attribute_id'] !== 52) throw new RuntimeException('Single filter compatibility lost.');
foreach ([
    '<offer><param name="Высота, мм">442</param><param name="Высота, мм">402</param></offer>',
    '<offer><param name="Высота, мм" group="Missing">442</param></offer>',
    '<offer><param name="Высота, мм" group="Внутренний">442</param><param name="Высота, мм" group="Внутренний">402</param></offer>',
] as $invalid) {
    $rejected = false;
    try { $mapper->map(new SimpleXMLElement($invalid), $definitions); } catch (RuntimeException) { $rejected = true; }
    if (!$rejected) throw new RuntimeException('Ambiguous XML was accepted.');
}
echo "Grouped filter XML tests passed.\n";
