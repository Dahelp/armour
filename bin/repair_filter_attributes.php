<?php
declare(strict_types=1);

// Accept only an explicitly reviewed, value-preserving plan built from an audit.
try {
    $plan = $filterRepairPlan ?? json_decode(file_get_contents($argv[1] ?? ''), true, 512, JSON_THROW_ON_ERROR);
    $apply = $filterRepairApply ?? in_array('--apply', $argv, true);
    if (($plan['version'] ?? null) !== 1 || $plan['conflicts'] !== [] || $plan['products'] === []) {
        throw new RuntimeException('Repair plan is empty, conflicted or unsupported.');
    }
    if (!isset($filterRepairConnection)) {
        $config = require dirname(__DIR__).'/config/config_db.php';
        $filterRepairConnection = new PDO($config['dsn'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    }
    $db = $filterRepairConnection;
    if ($apply) {
        foreach (['attribute', 'product_attribute'] as $table) {
            $engine = $db->query("SHOW TABLE STATUS WHERE Name = '$table'")->fetch(PDO::FETCH_ASSOC)['Engine'] ?? '';
            if ($engine !== 'InnoDB') throw new RuntimeException('Transactional tables are required.');
            $db->exec("CREATE TABLE IF NOT EXISTS {$table}_backup_filter_groups_20261105 LIKE $table");
        }
    }
    $db->beginTransaction();
    $readProduct = $db->prepare('SELECT article FROM product WHERE id = ?');
    $readRows = $db->prepare('SELECT * FROM product_attribute WHERE product_id = ? ORDER BY id FOR UPDATE');
    $readDefinition = $db->prepare('SELECT * FROM attribute WHERE id = ? FOR UPDATE');
    $write = $db->prepare('UPDATE product_attribute SET attribute_id = ?, attribute_group_id = ? WHERE id = ? AND product_id = ? AND attribute_id = ? AND attribute_group_id = ? AND attribute_text = ?');
    $backup = $apply ? $db->prepare('INSERT IGNORE INTO product_attribute_backup_filter_groups_20261105 SELECT * FROM product_attribute WHERE id = ?') : null;
    $operations = [];
    $snapshots = [];
    foreach ($plan['products'] as $product) {
        $readProduct->execute([$product['id']]);
        if ((string)$readProduct->fetchColumn() !== (string)$product['article']) throw new RuntimeException('Product identity changed: '.$product['article']);
        $readRows->execute([$product['id']]);
        $rows = array_column($readRows->fetchAll(PDO::FETCH_ASSOC), null, 'id');
        $snapshots[$product['id']] = array_map(static fn(array $row): array => [$row['id'], $row['product_id'], $row['attribute_text']], $rows);
        foreach ($product['changes'] as $change) {
            $row = $rows[$change['row_id']] ?? null;
            if (!$row || (string)$row['attribute_text'] !== $change['text']) throw new RuntimeException('Dimension value changed: '.$product['article']);
            $already = (int)$row['attribute_id'] === $change['to_attribute'] && (int)$row['attribute_group_id'] === $change['to_group'];
            if (!$already && ((int)$row['attribute_id'] !== $change['from_attribute'] || (int)$row['attribute_group_id'] !== $change['from_group'])) throw new RuntimeException('Dimension grouping changed: '.$product['article']);
            $readDefinition->execute([$change['to_attribute']]);
            $definition = $readDefinition->fetch(PDO::FETCH_ASSOC);
            $definitionGroup = (int)($definition['attribute_group_id'] ?? 0);
            foreach ($plan['definition_changes'] as $fix) {
                if ($fix['id'] === $change['to_attribute'] && $definitionGroup === $fix['from']) $definitionGroup = $fix['to'];
            }
            if (!$definition || mb_strtolower($definition['attribute_name']) !== mb_strtolower($change['name']) || $definitionGroup !== $change['to_group']) throw new RuntimeException('Target definition changed.');
            if (!$already) $operations[] = $change;
        }
    }
    $definitionChanges = 0;
    foreach ($plan['definition_changes'] as $fix) {
        $readDefinition->execute([$fix['id']]);
        $row = $readDefinition->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['attribute_name'] !== $fix['name'] || !in_array((int)$row['attribute_group_id'], [$fix['from'], $fix['to']], true)) throw new RuntimeException('Definition precondition failed.');
        if ((int)$row['attribute_group_id'] !== $fix['to']) {
            ++$definitionChanges;
            if ($apply) {
                $db->exec('INSERT IGNORE INTO attribute_backup_filter_groups_20261105 SELECT * FROM attribute WHERE id = '.(int)$fix['id']);
                $statement = $db->prepare('UPDATE attribute SET attribute_group_id = ? WHERE id = ? AND attribute_group_id = ?');
                $statement->execute([$fix['to'], $fix['id'], $fix['from']]);
                if ($statement->rowCount() !== 1) throw new RuntimeException('Definition update failed.');
            }
        }
    }
    if ($apply) {
        foreach ($operations as $change) {
            $backup->execute([$change['row_id']]);
            $write->execute([$change['to_attribute'], $change['to_group'], $change['row_id'], $change['product_id'], $change['from_attribute'], $change['from_group'], $change['text']]);
            if ($write->rowCount() !== 1) throw new RuntimeException('Concurrent dimension update detected.');
        }
        foreach ($snapshots as $productId => $before) {
            $readRows->execute([$productId]);
            $after = array_column($readRows->fetchAll(PDO::FETCH_ASSOC), null, 'id');
            $values = array_map(static fn(array $row): array => [$row['id'], $row['product_id'], $row['attribute_text']], $after);
            if ($before !== $values) throw new RuntimeException('Attribute values or row counts changed.');
        }
        $db->commit();
    } else {
        $db->rollBack();
    }
    echo json_encode(['ok'=>true, 'applied'=>$apply, 'products'=>count($plan['products']), 'changed_rows'=>count($operations), 'definition_changes'=>$definitionChanges, 'values_preserved'=>true, 'without_original_dimensions'=>$plan['without_original_dimensions'], 'absent_products'=>$plan['absent_products']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    if (PHP_SAPI !== 'cli') http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>$error instanceof PDOException ? 'Database operation failed; changes rolled back.' : $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    if (PHP_SAPI === 'cli') exit(1);
}
