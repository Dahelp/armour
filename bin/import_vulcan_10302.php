<?php
declare(strict_types=1);
$config = require dirname(__DIR__).'/config/config_db.php';
$db = new PDO($config['dsn'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$rows=$db->query("SELECT id,article,name,alias,hide,price,quantity FROM product WHERE article='10302'")->fetchAll();
echo json_encode(['ok'=>true,'products'=>$rows],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);