<?php

declare(strict_types=1);

function envRequired(string $name): string
{
    $value = trim((string)getenv($name));
    if ($value === '') {
        throw new RuntimeException("Missing environment variable: {$name}");
    }
    return $value;
}

function splitHost(string $host, int $defaultPort = 3306): array
{
    if (preg_match('/^(.+):(\d+)$/', $host, $matches) && !str_starts_with($host, '[')) {
        return [$matches[1], (int)$matches[2]];
    }
    return [$host, $defaultPort];
}

function connectMysql(string $hostKey, string $userKey, string $passwordKey, ?string $databaseKey = null): PDO
{
    [$host, $port] = splitHost(envRequired($hostKey), (int)(getenv($hostKey . '_PORT') ?: 3306));
    $database = $databaseKey === null ? '' : trim((string)getenv($databaseKey));
    $dsn = sprintf('mysql:host=%s;port=%d;%scharset=utf8mb4', $host, $port, $database !== '' ? 'dbname=' . $database . ';' : '');
    return new PDO($dsn, envRequired($userKey), envRequired($passwordKey), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function sourceDatabase(PDO $source): string
{
    $configured = trim((string)getenv('DB_DATABASE_ARMOUR'));
    if ($configured !== '') {
        return $configured;
    }

    foreach ($source->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN) as $database) {
        if (in_array($database, ['information_schema', 'mysql', 'performance_schema', 'sys'], true)) {
            continue;
        }
        $quoted = '`' . str_replace('`', '``', (string)$database) . '`';
        try {
            $source->query("SELECT 1 FROM {$quoted}.plagins_cross LIMIT 1");
            $source->query("SELECT 1 FROM {$quoted}.product LIMIT 1");
            $source->query("SELECT 1 FROM {$quoted}.brand LIMIT 1");
            return (string)$database;
        } catch (Throwable) {
            continue;
        }
    }

    throw new RuntimeException('Unable to discover the armour-shina database.');
}

function placeholders(int $count): string
{
    return implode(',', array_fill(0, $count, '?'));
}

try {
    $source = connectMysql('DB_HOST_ARMOUR', 'DB_USERNAME_ARMOUR', 'DB_PASSWORD_ARMOUR', 'DB_DATABASE_ARMOUR');
    $destination = connectMysql('DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE');
    $sourceDatabase = sourceDatabase($source);
    $sourceSchema = '`' . str_replace('`', '``', $sourceDatabase) . '`';

    $sourceRows = $source->query(
        "SELECT c.cross_id, p.article, cv.name AS vendor_name, c.cross_name,
                c.cross_abbreviated_name, c.tip_cross, c.equipment_vendor
         FROM {$sourceSchema}.plagins_cross c
         INNER JOIN {$sourceSchema}.product p ON p.id = c.product_id
         INNER JOIN {$sourceSchema}.brand b ON b.id = p.brand_id
         INNER JOIN {$sourceSchema}.plagins_cross_vendor cv ON cv.id = c.vendor_id
         WHERE UPPER(TRIM(b.name)) = 'EKKA'
         ORDER BY c.id"
    )->fetchAll();

    if ($sourceRows === []) {
        throw new RuntimeException('No EKKA cross numbers were found in the old database.');
    }

    $articles = array_values(array_unique(array_map(static fn(array $row): string => trim((string)$row['article']), $sourceRows)));
    $productStatement = $destination->prepare(
        'SELECT p.id, p.article
         FROM product p
         INNER JOIN brand b ON b.id = p.brand_id
         WHERE UPPER(TRIM(b.name)) = ? AND p.article IN (' . placeholders(count($articles)) . ')'
    );
    $productStatement->execute(array_merge(['EKKA'], $articles));
    $productsByArticle = [];
    foreach ($productStatement->fetchAll() as $product) {
        $article = trim((string)$product['article']);
        if (isset($productsByArticle[$article])) {
            throw new RuntimeException("Duplicate EKKA article in destination: {$article}");
        }
        $productsByArticle[$article] = (int)$product['id'];
    }

    $missingArticles = array_values(array_diff($articles, array_keys($productsByArticle)));
    if ($missingArticles !== []) {
        throw new RuntimeException(
            'Destination is missing ' . count($missingArticles) . ' EKKA article(s): '
            . implode(', ', array_slice($missingArticles, 0, 20))
        );
    }

    $dryRun = in_array('--dry-run', $argv, true);
    if ($dryRun) {
        echo json_encode([
            'ok' => true,
            'dry_run' => true,
            'source_rows' => count($sourceRows),
            'products' => count($articles),
            'vendors' => count(array_unique(array_column($sourceRows, 'vendor_name'))),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
        exit(0);
    }

    $destination->exec('CREATE TABLE IF NOT EXISTS plagins_cross_backup_pre_armour_import LIKE plagins_cross');
    $backupCount = (int)$destination->query('SELECT COUNT(*) FROM plagins_cross_backup_pre_armour_import')->fetchColumn();
    if ($backupCount === 0) {
        $destination->exec('INSERT INTO plagins_cross_backup_pre_armour_import SELECT * FROM plagins_cross');
    }

    $vendorRows = $destination->query('SELECT id, name FROM plagins_cross_vendor')->fetchAll();
    $vendorsByName = [];
    foreach ($vendorRows as $vendor) {
        $vendorsByName[mb_strtolower(trim((string)$vendor['name']), 'UTF-8')] = (int)$vendor['id'];
    }
    $insertVendor = $destination->prepare('INSERT INTO plagins_cross_vendor (name) VALUES (?)');
    foreach (array_unique(array_column($sourceRows, 'vendor_name')) as $vendorName) {
        $vendorName = trim((string)$vendorName);
        $key = mb_strtolower($vendorName, 'UTF-8');
        if (!isset($vendorsByName[$key])) {
            $insertVendor->execute([$vendorName]);
            $vendorsByName[$key] = (int)$destination->lastInsertId();
        }
    }

    $productIds = array_values($productsByArticle);
    $delete = $destination->prepare('DELETE FROM plagins_cross WHERE product_id IN (' . placeholders(count($productIds)) . ')');
    $delete->execute($productIds);

    $insert = $destination->prepare(
        'INSERT INTO plagins_cross
         (cross_id, product_id, vendor_id, cross_name, cross_abbreviated_name, tip_cross, equipment_vendor)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($sourceRows as $row) {
        $vendorKey = mb_strtolower(trim((string)$row['vendor_name']), 'UTF-8');
        $insert->execute([
            (int)$row['cross_id'],
            $productsByArticle[trim((string)$row['article'])],
            $vendorsByName[$vendorKey],
            (string)$row['cross_name'],
            (string)$row['cross_abbreviated_name'],
            (int)$row['tip_cross'],
            (int)$row['equipment_vendor'],
        ]);
    }

    $verify = $destination->prepare('SELECT COUNT(*) FROM plagins_cross WHERE product_id IN (' . placeholders(count($productIds)) . ')');
    $verify->execute($productIds);
    $imported = (int)$verify->fetchColumn();
    if ($imported !== count($sourceRows)) {
        throw new RuntimeException("Import verification failed: expected " . count($sourceRows) . ", got {$imported}.");
    }

    echo json_encode([
        'ok' => true,
        'dry_run' => false,
        'source_rows' => count($sourceRows),
        'imported_rows' => $imported,
        'products' => count($articles),
        'vendors' => count(array_unique(array_column($sourceRows, 'vendor_name'))),
        'backup_rows' => $backupCount,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, json_encode([
        'ok' => false,
        'error' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
    exit(1);
}
