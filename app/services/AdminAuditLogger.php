<?php

declare(strict_types=1);

namespace app\services;

final class AdminAuditLogger
{
    public static function log(int $groupId, int $actionId, string $table, int $recordId): void
    {
        if ($groupId < 1 || $actionId < 1 || $recordId < 1 || !preg_match('/^[a-z_][a-z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException('Некорректные параметры журнала администратора.');
        }

        $administratorId = (int)($_SESSION['user']['id'] ?? 0);
        if ($administratorId < 1) {
            throw new \RuntimeException('Не удалось определить администратора для журнала действий.');
        }

        \R::exec(
            'INSERT INTO admin_last_history (gh_id, ah_id, name_tbl, id_tbl, date_modified, customer_id) VALUES (?, ?, ?, ?, ?, ?)',
            [$groupId, $actionId, $table, $recordId, date('Y-m-d H:i:s'), $administratorId]
        );
    }
}
