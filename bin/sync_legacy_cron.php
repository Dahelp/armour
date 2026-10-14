<?php

declare(strict_types=1);

function cronEnv(string $name): string
{
    $value = trim((string)getenv($name));
    if ($value === '' && function_exists('config_env')) {
        $value = trim((string)config_env($name, ''));
    }
    if ($value === '') {
        throw new RuntimeException("Missing environment variable: {$name}");
    }
    return $value;
}

function cronPdo(string $suffix, bool $databaseRequired = true): PDO
{
    $host = cronEnv('DB_HOST' . $suffix);
    $port = 3306;
    if (preg_match('/^(.+):(\d+)$/', $host, $matches)) {
        $host = $matches[1];
        $port = (int)$matches[2];
    }
    $database = trim((string)getenv('DB_DATABASE' . $suffix));
    if ($database === '' && function_exists('config_env')) {
        $database = trim((string)config_env('DB_DATABASE' . $suffix, ''));
    }
    if ($databaseRequired && $database === '') {
        throw new RuntimeException('Missing environment variable: DB_DATABASE' . $suffix);
    }
    return new PDO(
        sprintf('mysql:host=%s;port=%d;%scharset=utf8mb4', $host, $port, $database !== '' ? 'dbname=' . $database . ';' : ''),
        cronEnv('DB_USERNAME' . $suffix),
        cronEnv('DB_PASSWORD' . $suffix),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

function discoverCronSchema(PDO $connection): string
{
    $configured = trim((string)getenv('DB_DATABASE_ARMOUR'));
    if ($configured !== '') {
        return '`' . str_replace('`', '``', $configured) . '`';
    }
    foreach ($connection->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN) as $database) {
        if (in_array($database, ['information_schema', 'mysql', 'performance_schema', 'sys'], true)) {
            continue;
        }
        $schema = '`' . str_replace('`', '``', (string)$database) . '`';
        try {
            $count = (int)$connection->query("SELECT COUNT(*) FROM {$schema}.cron WHERE TRIM(url_params) <> ''")->fetchColumn();
            if ($count > 0) {
                return $schema;
            }
        } catch (Throwable) {
        }
    }
    throw new RuntimeException('Legacy database with configured cron jobs was not found.');
}

$apply = in_array('--apply', $argv ?? [], true);
$source = cronPdo('_ARMOUR', false);
$sourceSchema = discoverCronSchema($source);
$destination = cronPdo('');
$rows = $source->query(
    "SELECT name, alias, url_params, url_download, hide, date_update
     FROM {$sourceSchema}.cron WHERE TRIM(url_params) <> '' ORDER BY id"
)->fetchAll();

if (!$apply) {
    echo json_encode(['found' => count($rows), 'apply' => false], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return;
}

$destination->beginTransaction();
try {
    $destination->exec('CREATE TABLE IF NOT EXISTS cron_backup_pre_legacy_sync LIKE cron');
    if ((int)$destination->query('SELECT COUNT(*) FROM cron_backup_pre_legacy_sync')->fetchColumn() === 0) {
        $destination->exec('INSERT INTO cron_backup_pre_legacy_sync SELECT * FROM cron');
    }
    $find = $destination->prepare('SELECT id FROM cron WHERE url_params = ? LIMIT 1');
    $insert = $destination->prepare(
        'INSERT INTO cron (name, alias, url_params, url_download, hide, date_update) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $update = $destination->prepare(
        'UPDATE cron SET name = ?, alias = ?, url_download = ?, hide = ?, date_update = ? WHERE id = ?'
    );
    $imported = 0;
    foreach ($rows as $row) {
        $find->execute([$row['url_params']]);
        $id = $find->fetchColumn();
        if ($id) {
            $update->execute([$row['name'], $row['alias'], $row['url_download'], $row['hide'], $row['date_update'], $id]);
        } else {
            $insert->execute([$row['name'], $row['alias'], $row['url_params'], $row['url_download'], $row['hide'], $row['date_update']]);
        }
        $imported++;
    }
    $destination->commit();
    echo json_encode(['found' => count($rows), 'imported' => $imported, 'apply' => true], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $exception) {
    $destination->rollBack();
    throw $exception;
}
