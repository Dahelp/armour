<?php
declare(strict_types=1);
// CLI only: never publish an HTTP database migration endpoint.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/config/init.php';
foreach (['TireSizeSeoMigration', 'StorefrontSeo', 'TireSizeNotation'] as $class) require_once ROOT . '/app/services/' . $class . '.php';
use app\services\TireSizeSeoMigration as Migration;

$options = getopt('', ['source-schema:', 'source-dump:', 'value-column:', 'apply']);
$config = require CONF . '/config_db.php';
$db = new PDO($config['dsn'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$sourceSchema = $options['source-schema'] ?? 'cl347323_armour';
if (!preg_match('/^[a-zA-Z0-9_]+$/D', $sourceSchema)) throw new RuntimeException('Invalid source schema.');
$rows = isset($options['source-dump'])
    ? (new \app\services\SqlDumpTableReader())->readTable($options['source-dump'], 'tiposize')
    : $db->query('SELECT * FROM `' . $sourceSchema . '`.`tiposize`')->fetchAll();
if (!$rows) throw new RuntimeException('No source tiposize records; nothing was changed.');
if (array_key_exists('size', $rows[0]) && array_key_exists('text', $rows[0])) {
    $rows = array_map(static fn(array $row): array => ['value' => $row['size'], 'content' => $row['text'], 'title' => $row['title_size'], 'description' => $row['description_size'], 'keywords' => $row['keywords_size']], $rows);
}
foreach (Migration::FIELDS as $field) if (!array_key_exists($field, $rows[0])) throw new RuntimeException('Inspect the legacy schema: missing source column ' . $field);
$groups = $db->query("SELECT id FROM attribute_group WHERE LOWER(url_params) = 'tiposize'")->fetchAll();
if (count($groups) !== 1) throw new RuntimeException('Target size group is missing or ambiguous.');
$groupId = (int)$groups[0]['id'];
$statement = $db->prepare('SELECT * FROM attribute_value WHERE attr_group_id = ?'); $statement->execute([$groupId]);
$plan = Migration::plan($rows, $statement->fetchAll(), $options['value-column'] ?? 'value');
$statement = $db->prepare("SELECT * FROM plagins_inseo WHERE tip = 'attribute_group' AND category_id = ?"); $statement->execute([$groupId]);
$rules = $statement->fetchAll();
if (count($rules) > 1) throw new RuntimeException('Duplicate INSEO rules require review.');
$backup = ['sourceSchema' => $sourceSchema, 'sourceTable' => 'tiposize', 'sourceHash' => hash('sha256', json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)), 'createdAt' => gmdate('c'), 'plan' => $plan, 'inseoBefore' => $rules];
$directory = ROOT . '/tmp/seo-migration';
if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new RuntimeException('Unable to create the private backup directory.');
$backupPath = $directory . '/tiposize-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.json';
if (file_put_contents($backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX) === false) throw new RuntimeException('Unable to save the backup; nothing was changed.');
$report = ['sourceRows' => $plan['sourceRows'], 'targetRows' => $plan['targetRows'], 'plannedChanges' => count($plan['changes']), 'unmatched' => $plan['unmatched'], 'conflicts' => $plan['conflicts'], 'backup' => $backupPath, 'applied' => false];
if (isset($options['apply'])) {
    if ($plan['conflicts']) throw new RuntimeException('Ambiguous records: inspect the saved plan before applying.');
    $engines = $db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('attribute_value','plagins_inseo')")->fetchAll(PDO::FETCH_COLUMN);
    if (count($engines) !== 2 || array_filter($engines, static fn($engine) => strtolower($engine) !== 'innodb')) throw new RuntimeException('Both target tables must support transactions.');
    $db->beginTransaction();
    try {
        $report['updated'] = Migration::apply($db, $plan);
        $fields = [...Migration::INSEO, 'hide' => 'show'];
        if ($rules) {
            $set = implode(', ', array_map(static fn($field) => '`' . $field . '` = ?', array_keys($fields)));
            $db->prepare('UPDATE plagins_inseo SET ' . $set . ' WHERE id = ?')->execute([...array_values($fields), $rules[0]['id']]);
        } else {
            $fields = ['tip' => 'attribute_group', 'category_id' => $groupId, 'content' => '', ...$fields];
            $db->prepare('INSERT INTO plagins_inseo (`' . implode('`,`', array_keys($fields)) . '`) VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')')->execute(array_values($fields));
        }
        $db->commit(); $report['applied'] = true;
    } catch (Throwable $error) { if ($db->inTransaction()) $db->rollBack(); throw $error; }
}
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), PHP_EOL;
