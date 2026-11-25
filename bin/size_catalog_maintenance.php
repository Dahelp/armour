<?php
declare(strict_types=1);
require dirname(__DIR__).'/config/init.php';
$config = require ROOT.'/config/config_db.php';
$db = new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$applied=[];
if ($sizeApply ?? false) {
    require_once ROOT.'/app/services/ProductDocumentFacets.php';
    $plan=$sizePlan;
    if(count($plan)!==63)throw new RuntimeException('Unexpected repair plan.');
    $facets=new \app\services\ProductDocumentFacets($db);
    $group=$db->query("SELECT id FROM attribute_group WHERE LOWER(url_params)='tiposize'")->fetchAll(PDO::FETCH_COLUMN);
    if(count($group)!==1)throw new RuntimeException('Ambiguous tire size group.');
    foreach(['attribute_product','attribute_category','attribute_value','url_alias'] as $table)$db->exec("CREATE TABLE IF NOT EXISTS {$table}_backup_sizes_20261125 LIKE $table");
    $db->exec('LOCK TABLES product READ, product_attribute READ, attribute READ, attribute_group READ, attribute_value WRITE, attribute_product WRITE, attribute_category WRITE, url_alias WRITE, attribute_product_backup_sizes_20261125 WRITE, attribute_category_backup_sizes_20261125 WRITE, attribute_value_backup_sizes_20261125 WRITE, url_alias_backup_sizes_20261125 WRITE');
    $added=[];
    try {
        $db->exec('INSERT IGNORE INTO attribute_value_backup_sizes_20261125 SELECT * FROM attribute_value WHERE attr_group_id='.(int)$group[0]);
        $db->exec("INSERT IGNORE INTO url_alias_backup_sizes_20261125 SELECT * FROM url_alias WHERE LOWER(view)='tiposize'");
        $db->exec('INSERT IGNORE INTO attribute_category_backup_sizes_20261125 SELECT * FROM attribute_category WHERE group_id='.(int)$group[0]);
        foreach($plan as $item) {
            $p=$db->prepare('SELECT id,article,category_id,hide FROM product WHERE id=?');$p->execute([$item['id']]);$product=$p->fetch();
            if(!$product||$product['article']!==$item['article']||(int)$product['category_id']!==$item['category_id']||$product['hide']!=='show')throw new RuntimeException('Product identity changed.');
            $s=$db->prepare('SELECT attribute_text FROM product_attribute WHERE product_id=? AND attribute_id IN (SELECT id FROM attribute WHERE attribute_name=?)');$s->execute([$item['id'],'Размер шины']);
            if(!in_array($item['size'],$s->fetchAll(PDO::FETCH_COLUMN),true))throw new RuntimeException('Product size evidence changed.');
            $s=$db->prepare('SELECT attr_id FROM attribute_product WHERE product_id=? AND attr_id IN (SELECT id FROM attribute_value WHERE attr_group_id=?)');$s->execute([$item['id'],$group[0]]);
            if($s->fetchColumn()) { $applied[]=['id'=>$item['id'],'already_bound'=>true]; continue; }
            $db->prepare('INSERT IGNORE INTO attribute_product_backup_sizes_20261125 SELECT * FROM attribute_product WHERE product_id=?')->execute([$item['id']]);
            // Every size was reviewed against the live dictionary; avoid inventing values.
            $values=$db->prepare('SELECT value FROM attribute_value WHERE attr_group_id=?');$values->execute([$group[0]]);
            $key=static fn($v)=>str_replace(['*','х','×'],['x','x','x'],mb_strtolower(trim($v),'UTF-8'));
            if(count(array_filter($values->fetchAll(PDO::FETCH_COLUMN),static fn($v)=>$key($v)===$key($item['size'])))!==1)throw new RuntimeException('Missing or ambiguous reviewed size.');
            $binding=$facets->bindSize($item['id'],$item['category_id'],$item['size'])[0];
            $added[]=[$item['id'],$binding['id']];$applied[]=['id'=>$item['id'],'size'=>$item['size'],'alias'=>$binding['alias']];
        }
    } catch(Throwable $error) {
        foreach($added as [$productId,$attrId])$db->prepare('DELETE FROM attribute_product WHERE product_id=? AND attr_id=?')->execute([$productId,$attrId]);
        $facets->rollbackCreated();throw $error;
    } finally { $db->exec('UNLOCK TABLES'); }
    if(!class_exists('R'))class_alias(\RedBeanPHP\R::class,'R');
    \R::setup($config['dsn'],$config['user'],$config['pass']);
    $sitemapCount=(new \app\services\SitemapGenerator())->generate();
}
$report = [
    'ok'=>true,
    'applied'=>$applied,
    'sitemap_urls'=>$sitemapCount??null,
    'groups'=>$db->query('SELECT id,title,url_params FROM attribute_group')->fetchAll(),
    'sizes'=>$db->query("SELECT av.*, (SELECT COUNT(DISTINCT ap.product_id) FROM attribute_product ap JOIN product p ON p.id=ap.product_id WHERE ap.attr_id=av.id AND p.hide='show') AS visible_products, (SELECT COUNT(DISTINCT ap.product_id) FROM attribute_product ap JOIN product p ON p.id=ap.product_id WHERE ap.attr_id=av.id AND p.hide='show' AND p.quantity>0) AS stocked_products FROM attribute_value av JOIN attribute_group ag ON ag.id=av.attr_group_id WHERE LOWER(ag.url_params)='tiposize'")->fetchAll(),
    'routes'=>$db->query("SELECT id,sef,view,urlid FROM url_alias WHERE LOWER(view)='tiposize'")->fetchAll(),
    'products'=>$db->query("SELECT id,article,name,alias,category_id,quantity FROM product WHERE hide='show'")->fetchAll(),
    'attributes'=>$db->query("SELECT pa.product_id,pa.attribute_text,a.attribute_name FROM product_attribute pa JOIN attribute a ON a.id=pa.attribute_id JOIN product p ON p.id=pa.product_id WHERE p.hide='show' AND (LOWER(a.attribute_name) LIKE '%размер%' OR LOWER(a.attribute_name) LIKE '%бренд%' OR LOWER(a.attribute_name) LIKE '%протектор%' OR LOWER(a.attribute_name) LIKE '%диаметр%' OR LOWER(a.attribute_name) LIKE '%тип шин%')")->fetchAll(),
    'bindings'=>$db->query("SELECT ap.product_id,ap.attr_id FROM attribute_product ap JOIN attribute_value av ON av.id=ap.attr_id JOIN attribute_group ag ON ag.id=av.attr_group_id JOIN product p ON p.id=ap.product_id WHERE LOWER(ag.url_params)='tiposize' AND p.hide='show'")->fetchAll(),
];
echo json_encode($report,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
