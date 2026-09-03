<?php

declare(strict_types=1);

namespace app\services;

final class LegacyUrlDeploymentMapService
{
    /**
     * @param list<array{source_path: string, target_path: string, status_code: int, is_active: int}> $rows
     * @param list<array<string, int|string>> $audit
     * @return array{ready: list<array<string, int|string>>, deferred: list<array<string, int|string>>}
     */
    public function split(array $rows, array $audit): array
    {
        $results = [];
        foreach ($audit as $item) {
            $source = (string)($item['source_path'] ?? '');
            if ($source === '' || isset($results[$source])) {
                throw new \InvalidArgumentException('Некорректный или повторяющийся source_path в HTTP-аудите.');
            }
            $results[$source] = $item;
        }

        $ready = [];
        $deferred = [];
        foreach ($rows as $row) {
            $source = $row['source_path'];
            if (!isset($results[$source])) {
                throw new \InvalidArgumentException('В HTTP-аудите отсутствует URL: ' . $source);
            }
            $auditRow = $results[$source];
            if (($auditRow['expected_target'] ?? '') !== $row['target_path']) {
                throw new \InvalidArgumentException('Цель URL изменилась после HTTP-аудита: ' . $source);
            }
            if (($auditRow['result'] ?? '') === 'PASS') {
                $ready[] = $row;
                continue;
            }
            $row['is_active'] = 0;
            $row['audit_issues'] = (string)($auditRow['issues'] ?? 'audit_failed');
            $deferred[] = $row;
        }

        return ['ready' => $ready, 'deferred' => $deferred];
    }

    /** @param list<array<string, int|string>> $rows */
    public function writeCsv(string $filename, array $rows, bool $includeIssues = false): void
    {
        $directory = dirname($filename);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Не удалось создать каталог карты URL.');
        }
        $handle = fopen($filename, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Не удалось создать карту URL: ' . $filename);
        }
        $columns = ['source_path', 'target_path', 'status_code', 'is_active'];
        if ($includeIssues) {
            $columns[] = 'audit_issues';
        }
        fputcsv($handle, $columns, ';', '"', '\\');
        foreach ($rows as $row) {
            fputcsv($handle, array_map(static fn(string $column): int|string => $row[$column] ?? '', $columns), ';', '"', '\\');
        }
        fclose($handle);
    }
}
