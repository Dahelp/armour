<?php
require dirname(__DIR__).'/config/environment.php';
$config=require dirname(__DIR__).'/config/config_db.php';
$db=new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$image='9522-17-5-25-20261123.jpg';
foreach(['baseimg','mini'] as $folder){$path=dirname(__DIR__).'/public/images/product/'.$folder.'/'.$image;$size=is_file($path)?getimagesize($path):false;if(!$size||$size[0]!==600||$size[1]!==600||$size[2]!==IMAGETYPE_JPEG||hash_file('sha256',$path)!=='9dc4f109a304f1845c97f1c943ccd70fc459882a4099e8c4128b735ac8ce666c')throw new RuntimeException('Uploaded image differs from the supplied photo.');}
$db->exec('CREATE TABLE IF NOT EXISTS product_photo_backup_9522_20261123 (product_id INT PRIMARY KEY,img TEXT NULL)');
$db->exec('LOCK TABLES product WRITE, product_photo_backup_9522_20261123 WRITE');
$original=null;
try {
    $read=$db->prepare('SELECT id,article,alias,img,name,price,quantity,category_id,brand_id,title,description,content FROM product WHERE article=?');$read->execute(['9522']);$rows=$read->fetchAll();
    if(count($rows)!==1||(int)$rows[0]['id']!==247||$rows[0]['alias']!=='shiny-17-5-25-20pr-g2-l2-qh808-tl-superguider')throw new RuntimeException('Product 9522 identity changed; no update performed.');
    $original=$rows[0];
    $db->prepare('INSERT IGNORE INTO product_photo_backup_9522_20261123 SELECT id,img FROM product WHERE id=? AND article=?')->execute([247,'9522']);
    $db->prepare('UPDATE product SET img=? WHERE id=? AND article=?')->execute([$image,247,'9522']);
    $read->execute(['9522']);$after=$read->fetch();if($after['img']!==$image)throw new RuntimeException('Photo update verification failed.');
    $before=$original;unset($before['img'],$after['img']);if($before!==$after)throw new RuntimeException('Other product fields changed unexpectedly.');
} catch(Throwable $error){if($original)$db->prepare('UPDATE product SET img=? WHERE id=? AND article=?')->execute([$original['img'],247,'9522']);throw $error;}finally{$db->exec('UNLOCK TABLES');}
echo json_encode(['ok'=>true,'article'=>'9522','count'=>1,'image'=>$image,'products'=>[['id'=>247,'alias'=>$original['alias']]]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);