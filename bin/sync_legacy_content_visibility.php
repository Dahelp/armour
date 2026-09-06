<?php

declare(strict_types=1);

function requiredEnv(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '') {
        throw new RuntimeException("Missing environment variable: {$name}");
    }
    return $value;
}

function databaseConnection(string $suffix, bool $databaseRequired = true): PDO
{
    $host = requiredEnv('DB_HOST' . $suffix);
    $port = 3306;
    if (preg_match('/^(.+):(\d+)$/', $host, $matches)) {
        $host = $matches[1];
        $port = (int) $matches[2];
    }
    $database = trim((string) getenv('DB_DATABASE' . $suffix));
    if ($databaseRequired && $database === '') {
        throw new RuntimeException('Missing environment variable: DB_DATABASE' . $suffix);
    }
    return new PDO(
        sprintf('mysql:host=%s;port=%d;%scharset=utf8mb4', $host, $port, $database !== '' ? 'dbname=' . $database . ';' : ''),
        requiredEnv('DB_USERNAME' . $suffix),
        requiredEnv('DB_PASSWORD' . $suffix),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

function discoverContentDatabase(PDO $connection): string
{
    $configured = trim((string) getenv('DB_DATABASE_ARMOUR'));
    if ($configured !== '') {
        return $configured;
    }
    foreach ($connection->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN) as $database) {
        if (in_array($database, ['information_schema', 'mysql', 'performance_schema', 'sys'], true)) {
            continue;
        }
        $quoted = '`' . str_replace('`', '``', (string) $database) . '`';
        try {
            $connection->query("SELECT alias, hide FROM {$quoted}.contents LIMIT 1");
            return (string) $database;
        } catch (Throwable) {
            continue;
        }
    }
    throw new RuntimeException('Unable to discover the legacy database containing contents.');
}

try {
    $source = databaseConnection('_ARMOUR', false);
    $sourceDatabase = discoverContentDatabase($source);
    $sourceTable = '`' . str_replace('`', '``', $sourceDatabase) . '`.contents';
    $destination = databaseConnection('');
    $rows = $source->query(
        "SELECT alias, hide FROM {$sourceTable} WHERE alias LIKE 'articles-%' OR alias LIKE 'news-%'"
    )->fetchAll();
    if ($rows === []) {
        throw new RuntimeException('No legacy articles or news were found.');
    }

    $existing = $destination->query(
        "SELECT alias, hide FROM contents WHERE alias LIKE 'articles-%' OR alias LIKE 'news-%'"
    )->fetchAll();
    $existingByAlias = array_column($existing, 'hide', 'alias');
    $changes = [];
    foreach ($rows as $row) {
        $alias = (string) $row['alias'];
        if (!array_key_exists($alias, $existingByAlias)) {
            continue;
        }
        $target = strtolower(trim((string) $row['hide'])) === 'show' ? 'show' : 'direct';
        if ((string) $existingByAlias[$alias] !== $target) {
            $changes[$alias] = $target;
        }
    }

    if (in_array('--apply', $argv, true) && $changes !== []) {
        $destination->beginTransaction();
        $destination->exec('CREATE TABLE IF NOT EXISTS contents_backup_pre_visibility_sync LIKE contents');
        if ((int) $destination->query('SELECT COUNT(*) FROM contents_backup_pre_visibility_sync')->fetchColumn() === 0) {
            $destination->exec("INSERT INTO contents_backup_pre_visibility_sync SELECT * FROM contents WHERE alias LIKE 'articles-%' OR alias LIKE 'news-%'");
        }
        $update = $destination->prepare('UPDATE contents SET hide = ?, date_last_modified = NOW() WHERE alias = ?');
        foreach ($changes as $alias => $visibility) {
            $update->execute([$visibility, $alias]);
        }
        $destination->commit();
    }

    echo json_encode([
        'ok' => true,
        'source_rows' => count($rows),
        'destination_rows' => count($existing),
        'changed' => count($changes),
        'direct_only' => count(array_filter($changes, static fn(string $value): bool => $value === 'direct')),
        'applied' => in_array('--apply', $argv, true),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $exception) {
    if (isset($destination) && $destination instanceof PDO && $destination->inTransaction()) {
        $destination->rollBack();
    }
    fwrite(STDERR, json_encode(['ok' => false, 'error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE) . PHP_EOL);
    exit(1);
}
