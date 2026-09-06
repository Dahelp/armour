<?php

declare(strict_types=1);

function requiredEnv(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '' && function_exists('config_env')) {
        $value = trim((string) config_env($name, ''));
    }
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
    $databaseKey = 'DB_DATABASE' . $suffix;
    $database = trim((string) getenv($databaseKey));
    if ($database === '' && function_exists('config_env')) {
        $database = trim((string) config_env($databaseKey, ''));
    }
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

function discoverContentDatabase(PDO $connection): array
{
    $configured = trim((string) getenv('DB_DATABASE_ARMOUR'));
    if ($configured !== '') {
        return [$configured, 'auto'];
    }
    $fallbackDatabase = '';
    foreach ($connection->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN) as $database) {
        if (in_array($database, ['information_schema', 'mysql', 'performance_schema', 'sys'], true)) {
            continue;
        }
        $quoted = '`' . str_replace('`', '``', (string) $database) . '`';
        if ($fallbackDatabase === '') {
            $fallbackDatabase = (string) $database;
        }
        try {
            $connection->query("SELECT alias, hide FROM {$quoted}.contents LIMIT 1");
            return [(string) $database, 'contents'];
        } catch (Throwable) {
            try {
                $connection->query("SELECT 1 FROM {$quoted}.product LIMIT 1");
                $fallbackDatabase = (string) $database;
            } catch (Throwable) {
            }
        }
    }
    if ($fallbackDatabase !== '') {
        $quoted = '`' . str_replace('`', '``', $fallbackDatabase) . '`';
        $tables = $connection->query("SHOW TABLES FROM {$quoted}")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('articles', $tables, true) && in_array('news', $tables, true)) {
            return [$fallbackDatabase, 'legacy'];
        }
        throw new RuntimeException('Legacy schema tables: ' . implode(', ', $tables));
    }
    throw new RuntimeException('Unable to discover the legacy database.');
}

try {
    $source = databaseConnection('_ARMOUR', false);
    [$sourceDatabase, $sourceKind] = discoverContentDatabase($source);
    $sourceSchema = '`' . str_replace('`', '``', $sourceDatabase) . '`';
    $destination = databaseConnection('');
    if ($sourceKind === 'contents') {
        $rows = $source->query(
            "SELECT alias, hide FROM {$sourceSchema}.contents WHERE alias LIKE 'articles-%' OR alias LIKE 'news-%'"
        )->fetchAll();
    } else {
        $rows = $source->query(
            "SELECT url_alt AS alias, hide FROM {$sourceSchema}.articles
             UNION ALL
             SELECT url_alt AS alias, hide FROM {$sourceSchema}.news"
        )->fetchAll();
        foreach ($rows as &$row) {
            $row['alias'] = preg_replace('~\.html$~i', '', basename((string) $row['alias']));
        }
        unset($row);
    }
    if ($rows === []) {
        throw new RuntimeException('No legacy articles or news were found.');
    }

    $existing = $destination->query(
        "SELECT alias, hide FROM contents WHERE alias LIKE 'articles-%' OR alias LIKE 'news-%'"
    )->fetchAll();
    $existingByAlias = array_column($existing, 'hide', 'alias');
    $changes = [];
    $sourceStatuses = [];
    foreach ($rows as $row) {
        $alias = (string) $row['alias'];
        if (!array_key_exists($alias, $existingByAlias)) {
            continue;
        }
        $sourceStatus = strtolower(trim((string) $row['hide']));
        $sourceStatuses[$sourceStatus] = ($sourceStatuses[$sourceStatus] ?? 0) + 1;
        $target = in_array($sourceStatus, ['show', '0', 'visible'], true) ? 'show' : 'lock';
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
        'source_statuses' => $sourceStatuses,
        'destination_rows' => count($existing),
        'changed' => count($changes),
        'direct_only' => count(array_filter($changes, static fn(string $value): bool => $value === 'lock')),
        'applied' => in_array('--apply', $argv, true),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $exception) {
    if (isset($destination) && $destination instanceof PDO && $destination->inTransaction()) {
        $destination->rollBack();
    }
    $error = json_encode(['ok' => false, 'error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $error);
    } else {
        http_response_code(500);
        echo $error;
    }
    exit(1);
}
