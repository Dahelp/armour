<?php
declare(strict_types=1);
namespace app\services;

/** Plans a text-only migration. IDs, aliases, product bindings and size values are immutable. */
final class TireSizeSeoMigration
{
    public const FIELDS = ['title', 'description', 'keywords', 'content'];
    public const INSEO = [
        'name' => 'Шины {tiposize}',
        'title' => 'Шины {tiposize} — купить в ТехШина',
        'description' => 'Шины {tiposize}: характеристики, цены и наличие. Помощь в подборе шин для спецтехники. Доставка по России.',
        'keywords' => 'шины {tiposize}, купить шины {tiposize}',
    ];

    public static function key(string $value): string
    {
        $value = TireSizeNotation::display(mb_strtolower(trim($value), 'UTF-8'));
        $value = (string)preg_replace('/(?<=\d),(?=\d)/u', '.', $value);
        return (string)preg_replace('/\s+/u', '', $value);
    }

    public static function plan(array $source, array $target, string $valueColumn = 'value'): array
    {
        $bySize = []; $conflicts = []; $unmatched = []; $changes = [];
        foreach ($source as $row) {
            if (!array_key_exists($valueColumn, $row) || self::key((string)$row[$valueColumn]) === '') {
                throw new \RuntimeException('Source size column is missing or empty: ' . $valueColumn);
            }
            $key = self::key((string)$row[$valueColumn]);
            if (!isset($bySize[$key])) $bySize[$key] = ['value' => (string)$row[$valueColumn], 'fields' => []];
            foreach (self::FIELDS as $field) {
                $value = (string)($row[$field] ?? '');
                if (StorefrontSeo::text($value) === '') continue;
                $before = $bySize[$key]['fields'][$field] ?? null;
                if ($before !== null && $before !== $value) $conflicts[] = ['size' => $key, 'field' => $field, 'reason' => 'Conflicting legacy records'];
                else $bySize[$key]['fields'][$field] = $value;
            }
        }
        $targets = [];
        foreach ($target as $row) $targets[self::key((string)$row['value'])][] = $row;
        foreach ($bySize as $key => $sourceRow) {
            $matches = $targets[$key] ?? [];
            if (!$matches) { $unmatched[] = $sourceRow['value']; continue; }
            if (count($matches) !== 1) { $conflicts[] = ['size' => $key, 'reason' => 'Ambiguous target size']; continue; }
            $row = $matches[0]; $before = []; $after = [];
            foreach ($sourceRow['fields'] as $field => $value) {
                if ((string)($row[$field] ?? '') !== $value) { $before[$field] = (string)($row[$field] ?? ''); $after[$field] = $value; }
            }
            if ($after) $changes[] = ['id' => (int)$row['id'], 'value' => $row['value'], 'alias' => $row['alias'], 'before' => $before, 'after' => $after];
        }
        return ['sourceRows' => count($source), 'targetRows' => count($target), 'changes' => $changes, 'unmatched' => $unmatched, 'conflicts' => $conflicts];
    }

    public static function apply(\PDO $db, array $plan): int
    {
        if ($plan['conflicts']) throw new \RuntimeException('Resolve ambiguous source/target records before applying the migration.');
        if (!$db->inTransaction()) throw new \RuntimeException('The caller must back up the plan and start a transaction.');
        $changed = 0;
        foreach ($plan['changes'] as $change) {
            $lock = $db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
            $statement = $db->prepare('SELECT * FROM attribute_value WHERE id = ?' . $lock); $statement->execute([$change['id']]);
            $current = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$current || $current['alias'] !== $change['alias'] || $current['value'] !== $change['value']) throw new \RuntimeException('Target identity changed after planning.');
            foreach ($change['before'] as $field => $value) if ((string)$current[$field] !== $value) throw new \RuntimeException('Target text changed after planning.');
            if (array_diff(array_keys($change['after']), self::FIELDS)) throw new \RuntimeException('Unexpected migration field.');
            $set = implode(', ', array_map(static fn($field) => '`' . $field . '` = ?', array_keys($change['after'])));
            $db->prepare('UPDATE attribute_value SET ' . $set . ' WHERE id = ?')->execute([...array_values($change['after']), $change['id']]);
            $changed++;
        }
        return $changed;
    }
}
