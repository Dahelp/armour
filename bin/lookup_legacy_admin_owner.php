<?php
declare(strict_types=1);

try {
    $email = trim((string)getenv('LEGACY_OWNER_EMAIL'));
    $legacyLogin = trim((string)getenv('LEGACY_OWNER_LOGIN')) ?: $email;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Owner email is required.');
    $config = require dirname(__DIR__).'/config/config_db.php';
    $destination = new PDO($config['dsn'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $statement = $destination->prepare("SELECT id, name FROM user WHERE LOWER(email) = LOWER(?) AND role = 'admin'");
    $statement->execute([$email]);
    $owners = $statement->fetchAll();
    if (count($owners) !== 1) throw new RuntimeException('The specified TechTires administrator was not found uniquely.');
    $source = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', (string)getenv('DB_USERNAME_ARMOUR'), (string)getenv('DB_PASSWORD_ARMOUR'), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $accounts = [];
    $legacySchemas = [];
    foreach ($source->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN) as $schema) {
        if (in_array($schema, ['mysql','information_schema','performance_schema','sys'], true)) continue;
        $quoted = '`'.str_replace('`','``',$schema).'`';
        if (!in_array('customers', $source->query('SHOW TABLES FROM '.$quoted)->fetchAll(PDO::FETCH_COLUMN), true)) continue;
        $legacySchemas[] = $quoted;
        $statement = $source->prepare('SELECT customer_id, name, id_role FROM '.$quoted.'.customers WHERE (LOWER(email) = LOWER(?) OR LOWER(login) = LOWER(?)) AND id_role <> 1');
        $statement->execute([$email, $legacyLogin]);
        $accounts = array_merge($accounts, $statement->fetchAll());
    }
    $created = false;
    if ($accounts === [] && count($legacySchemas) === 1 && getenv('LEGACY_CREATE_OWNER') === 'true') {
        $schema = $legacySchemas[0];
        $role = $source->query('SELECT name_role FROM '.$schema.'.roles WHERE id_role = 2')->fetchColumn();
        if (mb_strtolower(trim((string)$role)) !== 'администратор') throw new RuntimeException('The standard legacy administrator role could not be verified.');
        $login = 'tt-owner-'.(int)$owners[0]['id'];
        $statement = $source->prepare('SELECT customer_id,name,id_role,email FROM '.$schema.'.customers WHERE login = ?');
        $statement->execute([$login]);
        $existing = $statement->fetchAll();
        if ($existing !== []) throw new RuntimeException('Private owner login already exists but does not match the specified email.');
        // A NULL password prevents ordinary password login; only the signed owner bridge can use this account.
        $statement = $source->prepare('INSERT INTO '.$schema.'.customers (name,email,login,password,telefon,hide,id_role,subscription,avatar,uxeh) VALUES (?,?,?,NULL,?, ?,2,?,?,?)');
        $statement->execute([$owners[0]['name'].' (TechTires)', $email, $login, '', 'hide', 'hide', '', '']);
        $accounts = [['customer_id'=>(int)$source->lastInsertId(), 'name'=>$owners[0]['name'].' (TechTires)', 'id_role'=>2]];
        $created = true;
    }
    if (count($accounts) !== 1) {
        $metadata = [];
        foreach ($legacySchemas as $schema) {
            $metadata[] = ['roles'=>$source->query('SELECT id_role,name_role FROM '.$schema.'.roles WHERE id_role <> 1')->fetchAll(), 'columns'=>$source->query('SHOW COLUMNS FROM '.$schema.'.customers')->fetchAll()];
        }
        http_response_code(500);
        echo json_encode(['ok'=>false,'error'=>'The matching original Armour administrator was not found uniquely.','account_schema'=>$metadata], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
    echo json_encode(['ok'=>true, 'owner_id'=>(int)$owners[0]['id'], 'legacy_id'=>(int)$accounts[0]['customer_id'], 'legacy_name'=>$accounts[0]['name'], 'legacy_role'=>(int)$accounts[0]['id_role'], 'created_private_account'=>$created], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$error instanceof PDOException ? 'Account lookup database error.' : $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
