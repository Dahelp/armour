<?php
declare(strict_types=1);

try {
    $email = trim((string)getenv('LEGACY_OWNER_EMAIL'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Owner email is required.');
    $config = require dirname(__DIR__).'/config/config_db.php';
    $destination = new PDO($config['dsn'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $statement = $destination->prepare("SELECT id, name FROM user WHERE LOWER(email) = LOWER(?) AND role = 'admin'");
    $statement->execute([$email]);
    $owners = $statement->fetchAll();
    if (count($owners) !== 1) throw new RuntimeException('The specified TechTires administrator was not found uniquely.');
    $source = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', (string)getenv('DB_USERNAME_ARMOUR'), (string)getenv('DB_PASSWORD_ARMOUR'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $accounts = [];
    foreach ($source->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN) as $schema) {
        if (in_array($schema, ['mysql','information_schema','performance_schema','sys'], true)) continue;
        $quoted = '`'.str_replace('`','``',$schema).'`';
        if (!in_array('customers', $source->query('SHOW TABLES FROM '.$quoted)->fetchAll(PDO::FETCH_COLUMN), true)) continue;
        $statement = $source->prepare('SELECT customer_id, name, id_role FROM '.$quoted.'.customers WHERE (LOWER(email) = LOWER(?) OR LOWER(login) = LOWER(?)) AND id_role <> 1');
        $statement->execute([$email, $email]);
        $accounts = array_merge($accounts, $statement->fetchAll());
    }
    if (count($accounts) !== 1) throw new RuntimeException('The matching original Armour administrator was not found uniquely.');
    echo json_encode(['ok'=>true, 'owner_id'=>(int)$owners[0]['id'], 'legacy_id'=>(int)$accounts[0]['customer_id'], 'legacy_name'=>$accounts[0]['name'], 'legacy_role'=>(int)$accounts[0]['id_role']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$error instanceof PDOException ? 'Account lookup database error.' : $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
