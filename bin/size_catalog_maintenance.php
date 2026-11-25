<?php
declare(strict_types=1);
require dirname(__DIR__).'/config/init.php';
$config = require ROOT.'/config/config_db.php';
$db = new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$report = [
    'ok'=>true,
    'groups'=>$db->query('SELECT id,title,url_params FROM attribute_group')->fetchAll(),
    'sizes'=>$db->query("SELECT av.*, (SELECT COUNT(DISTINCT ap.product_id) FROM attribute_product ap JOIN product p ON p.id=ap.product_id WHERE ap.attr_id=av.id AND p.hide='show') AS visible_products, (SELECT COUNT(DISTINCT ap.product_id) FROM attribute_product ap JOIN product p ON p.id=ap.product_id WHERE ap.attr_id=av.id AND p.hide='show' AND p.quantity>0) AS stocked_products FROM attribute_value av JOIN attribute_group ag ON ag.id=av.attr_group_id WHERE LOWER(ag.url_params)='tiposize'")->fetchAll(),
    'routes'=>$db->query("SELECT id,sef,view,urlid FROM url_alias WHERE LOWER(view)='tiposize'")->fetchAll(),
    'products'=>$db->query("SELECT id,article,name,alias,category_id,quantity FROM product WHERE hide='show'")->fetchAll(),
    'attributes'=>$db->query("SELECT pa.product_id,pa.attribute_text,a.attribute_name FROM product_attribute pa JOIN attribute a ON a.id=pa.attribute_id JOIN product p ON p.id=pa.product_id WHERE p.hide='show' AND (LOWER(a.attribute_name) LIKE '%размер%' OR LOWER(a.attribute_name) LIKE '%бренд%' OR LOWER(a.attribute_name) LIKE '%протектор%' OR LOWER(a.attribute_name) LIKE '%диаметр%' OR LOWER(a.attribute_name) LIKE '%тип шин%')")->fetchAll(),
    'bindings'=>$db->query("SELECT ap.product_id,ap.attr_id FROM attribute_product ap JOIN attribute_value av ON av.id=ap.attr_id JOIN attribute_group ag ON ag.id=av.attr_group_id JOIN product p ON p.id=ap.product_id WHERE LOWER(ag.url_params)='tiposize' AND p.hide='show'")->fetchAll(),
];
echo json_encode($report,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
