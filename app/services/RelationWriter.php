<?php

declare(strict_types=1);

namespace app\services;

final class RelationWriter
{
    public function replace(
        string $table,
        string $ownerColumn,
        int $ownerId,
        string $relatedColumn,
        array $relatedIds
    ): void {
        foreach ([$table, $ownerColumn, $relatedColumn] as $identifier) {
            if (!preg_match('/^[a-z_][a-z0-9_]*$/', $identifier)) {
                throw new \InvalidArgumentException('Invalid relation identifier.');
            }
        }

        $relatedIds = array_values(array_unique(array_filter(
            array_map('intval', $relatedIds),
            static fn(int $id): bool => $id > 0
        )));

        \R::begin();
        try {
            \R::exec("DELETE FROM `$table` WHERE `$ownerColumn` = ?", [$ownerId]);
            if ($relatedIds !== []) {
                $slots = implode(',', array_fill(0, count($relatedIds), '(?, ?)'));
                $bindings = [];
                foreach ($relatedIds as $relatedId) {
                    $bindings[] = $ownerId;
                    $bindings[] = $relatedId;
                }
                \R::exec(
                    "INSERT INTO `$table` (`$ownerColumn`, `$relatedColumn`) VALUES $slots",
                    $bindings
                );
            }
            \R::commit();
        } catch (\Throwable $exception) {
            \R::rollback();
            throw $exception;
        }
    }
}
