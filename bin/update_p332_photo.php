<?php
require dirname(__DIR__).'/config/environment.php';
$config=require dirname(__DIR__).'/config/config_db.php';
$db=new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$rows=$db->query("SELECT DISTINCT p.id,p.name,p.alias,p.img FROM product p JOIN attribute_product ap ON ap.product_id=p.id JOIN attribute_value av ON av.id=ap.attr_id JOIN attribute_group ag ON ag.id=av.attr_group_id WHERE UPPER(TRIM(av.value))='P332' AND ag.url_params='protect' ORDER BY p.id")->fetchAll();
if(!$rows) throw new RuntimeException('No P332 tread products found');
$image='p332-pale-blue-600-20261112.jpg';
foreach(['baseimg','mini'] as $dir) {
    $path=dirname(__DIR__).'/public/images/product/'.$dir.'/'.$image;
    if(!is_file($path) || getimagesize($path)[0]!==600 || getimagesize($path)[1]!==600) throw new RuntimeException('Missing 600x600 asset');
}
$db->exec('CREATE TABLE IF NOT EXISTS product_photo_backup_p332_20261112 (product_id INT PRIMARY KEY, img TEXT NULL)');
$db->exec('LOCK TABLES product WRITE, product_photo_backup_p332_20261112 WRITE');
try {
    $backup=$db->prepare('INSERT IGNORE INTO product_photo_backup_p332_20261112 SELECT id,img FROM product WHERE id=?');
    $update=$db->prepare('UPDATE product SET img=? WHERE id=?');
    foreach($rows as $row) {$backup->execute([$row['id']]);$update->execute([$image,$row['id']]);}
    $verify=$db->prepare('SELECT img FROM product WHERE id=?');
    foreach($rows as $row) {$verify->execute([$row['id']]);if($verify->fetchColumn()!==$image) throw new RuntimeException('Photo verification failed');}
} catch(Throwable $error) {
    $db->exec('UPDATE product JOIN product_photo_backup_p332_20261112 b ON b.product_id=product.id SET product.img=b.img');
    throw $error;
} finally {$db->exec('UNLOCK TABLES');}
echo json_encode(['ok'=>true,'count'=>count($rows),'image'=>$image,'products'=>$rows],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
