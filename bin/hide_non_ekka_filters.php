<?php
declare(strict_types=1);

$locked = false;
$restore = [];
try {
    $apply = $filterRepairApply ?? in_array('--apply', $argv, true);
    $config = require dirname(__DIR__).'/config/config_db.php';
    $db = new PDO($config['dsn'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $targets = ['4682'=>'ST508AB','4683'=>'ST600AB','4685'=>'ST613AB','4686'=>'ST614AB','4689'=>'ST618AB','4691'=>'ST626AB'];
    if ($apply) {
        $db->exec('CREATE TABLE IF NOT EXISTS product_backup_non_ekka_filters_20261105 LIKE product');
        $db->exec('LOCK TABLES product WRITE, brand READ, product_backup_non_ekka_filters_20261105 WRITE');
        $locked = true;
    }
    $statement = $db->prepare('SELECT product.id,product.article,product.name,product.alias,product.hide,brand.name AS brand_name FROM product JOIN brand ON brand.id = product.brand_id WHERE product.article IN (?,?,?,?,?,?)');
    $statement->execute(array_keys($targets));
    $rows = $statement->fetchAll();
    if (count($rows) !== 6 || count(array_unique(array_column($rows, 'article'))) !== 6) throw new RuntimeException('Expected exactly six unique filter products.');
    foreach ($rows as $row) {
        if (!str_contains($row['name'], $targets[$row['article']]) || mb_strtoupper(trim($row['brand_name'])) === 'EKKA' || !in_array($row['hide'], ['show','hide'], true)) throw new RuntimeException('Unexpected product identity, brand or visibility.');
    }
    $changed = 0;
    if ($apply) {
        $ids = implode(',', array_map('intval', array_column($rows, 'id')));
        $db->exec('INSERT IGNORE INTO product_backup_non_ekka_filters_20261105 SELECT * FROM product WHERE id IN ('.$ids.')');
        $restore = $rows;
        $changed = $db->exec("UPDATE product SET hide = 'hide' WHERE id IN ($ids) AND hide = 'show'");
        $visible = (int)$db->query("SELECT COUNT(*) FROM product WHERE id IN ($ids) AND hide <> 'hide'")->fetchColumn();
        if ($visible !== 0) throw new RuntimeException('Some target products remain visible.');
        $db->exec('UNLOCK TABLES');
        $locked = false;
    }
    echo json_encode(['ok'=>true,'applied'=>$apply,'hidden'=>$changed,'products'=>$rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $error) {
    if ($locked) {
        $statement = $db->prepare('UPDATE product SET hide = ? WHERE id = ?');
        foreach ($restore as $row) $statement->execute([$row['hide'], $row['id']]);
        $db->exec('UNLOCK TABLES');
    }
    if (PHP_SAPI !== 'cli') http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$error instanceof PDOException ? 'Database operation failed.' : $error->getMessage()], JSON_UNESCAPED_UNICODE).PHP_EOL;
    if (PHP_SAPI === 'cli') exit(1);
}
