<?php

declare(strict_types=1);

namespace app\services;

final class LegacyUrlRedirectRepository
{
    /** @param list<array{source_path: string, target_path: string, status_code: int, is_active: int}> $rows */
    public function upsert(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        \R::begin();
        try {
            foreach (array_chunk($rows, 250) as $chunk) {
                $values = [];
                $bindings = [];
                foreach ($chunk as $row) {
                    $values[] = '(?, ?, ?, ?)';
                    array_push($bindings, $row['source_path'], $row['target_path'], $row['status_code'], $row['is_active']);
                }
                \R::exec(
                    'INSERT INTO legacy_url_redirect (source_path, target_path, status_code, is_active) VALUES '
                    . implode(', ', $values)
                    . ' ON DUPLICATE KEY UPDATE target_path = VALUES(target_path), status_code = VALUES(status_code), '
                    . 'is_active = VALUES(is_active), updated_at = CURRENT_TIMESTAMP',
                    $bindings
                );
            }
            \R::commit();
        } catch (\Throwable $exception) {
            \R::rollback();
            throw $exception;
        }

        return count($rows);
    }
}
