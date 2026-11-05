<?php
declare(strict_types=1);

// Runs inside the hosting account through the protected maintenance workflow.
function filterDb(string $suffix): PDO {
    $get = static fn(string $key) => (string)(getenv($key.$suffix) ?: config_env($key.$suffix, ''));
    $db = $get('DB_DATABASE');
    return new PDO('mysql:host='.$get('DB_HOST').';'.($db !== '' ? 'dbname='.$db.';' : '').'charset=utf8mb4', $get('DB_USERNAME'), $get('DB_PASSWORD'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}
$destination = filterDb('');
$source = filterDb('_ARMOUR');
$result = ['destination_attributes'=>$destination->query('SELECT * FROM attribute')->fetchAll(), 'destination_filters'=>$destination->query("SELECT p.id,p.article,p.name,p.alias,pa.id AS row_id,pa.attribute_id,pa.attribute_group_id,pa.attribute_text FROM product p LEFT JOIN product_attribute pa ON pa.product_id=p.id WHERE p.name LIKE '%Фильтр%' OR p.name LIKE '%фильтр%' ORDER BY p.id,pa.id")->fetchAll()];
$schemas = $source->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($schemas as $schema) {
    if (in_array($schema, ['information_schema','mysql','performance_schema','sys'], true)) continue;
    $q = '`'.str_replace('`','``',$schema).'`';
    $tables = $source->query('SHOW TABLES FROM '.$q)->fetchAll(PDO::FETCH_COLUMN);
    $result['source_tables'][$schema] = $tables;
    if (in_array('product_attribute',$tables,true)) {
        $result['source_attributes'] = $source->query('SELECT * FROM '.$q.'.attribute')->fetchAll();
        $result['source_filters'] = $source->query("SELECT p.article,p.name,pa.* FROM $q.product p JOIN $q.product_attribute pa ON pa.product_id=p.id WHERE p.name LIKE '%Фильтр%' OR p.name LIKE '%фильтр%' ORDER BY p.id,pa.id")->fetchAll();
    }
}
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
