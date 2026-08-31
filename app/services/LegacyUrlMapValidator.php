<?php

declare(strict_types=1);

namespace app\services;

final class LegacyUrlMapValidator
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /** @var array<string, true> */
    private array $sourceHosts;

    /** @var array<string, true> */
    private array $targetHosts;

    public function __construct(array $sourceHosts = [], array $targetHosts = [])
    {
        $this->sourceHosts = $this->hostMap($sourceHosts ?: [
            'armour-shina.ru',
            'www.armour-shina.ru',
            'techtires.ru',
            'www.techtires.ru',
        ]);
        $this->targetHosts = $this->hostMap($targetHosts ?: [
            'techtires.ru',
            'www.techtires.ru',
        ]);
    }

    /**
     * @return array{rows: list<array{source_path: string, target_path: string, status_code: int, is_active: int}>, errors: list<string>}
     */
    public function validateCsv(string $filename): array
    {
        if (!is_file($filename) || !is_readable($filename)) {
            return ['rows' => [], 'errors' => ['Файл CSV не найден или недоступен для чтения.']];
        }

        $size = filesize($filename);
        if ($size === false || $size > self::MAX_FILE_SIZE) {
            return ['rows' => [], 'errors' => ['Размер CSV превышает допустимые 10 МБ.']];
        }

        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return ['rows' => [], 'errors' => ['Не удалось открыть CSV.']];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return ['rows' => [], 'errors' => ['CSV пуст.']];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);
        $records = [];
        $line = 0;
        while (($columns = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            ++$line;
            if ($line === 1) {
                $columns[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)($columns[0] ?? '')) ?? '';
                if (strtolower(trim($columns[0])) === 'source_path') {
                    continue;
                }
            }
            if (count($columns) === 1 && trim((string)$columns[0]) === '') {
                continue;
            }
            $records[] = [
                'line' => $line,
                'source_path' => (string)($columns[0] ?? ''),
                'target_path' => (string)($columns[1] ?? ''),
                'status_code' => (string)($columns[2] ?? '301'),
                'is_active' => (string)($columns[3] ?? '1'),
            ];
        }
        fclose($handle);

        return $this->validateRows($records);
    }

    /**
     * @param iterable<array<string, mixed>> $records
     * @return array{rows: list<array{source_path: string, target_path: string, status_code: int, is_active: int}>, errors: list<string>}
     */
    public function validateRows(iterable $records): array
    {
        $rows = [];
        $errors = [];
        $sources = [];

        foreach ($records as $index => $record) {
            $line = (int)($record['line'] ?? ($index + 1));
            $source = $this->normaliseUrl((string)($record['source_path'] ?? ''), $this->sourceHosts);
            $target = $this->normaliseUrl((string)($record['target_path'] ?? ''), $this->targetHosts);
            $status = filter_var($record['status_code'] ?? 301, FILTER_VALIDATE_INT);
            $active = filter_var($record['is_active'] ?? 1, FILTER_VALIDATE_INT);

            if ($source === '') {
                $errors[] = "Строка {$line}: некорректный или внешний source_path.";
                continue;
            }
            if ($target === '') {
                $errors[] = "Строка {$line}: некорректный или внешний target_path.";
                continue;
            }
            if ($source === $target) {
                $errors[] = "Строка {$line}: source_path совпадает с target_path.";
                continue;
            }
            if (!in_array($status, [301, 308], true)) {
                $errors[] = "Строка {$line}: status_code должен быть 301 или 308.";
                continue;
            }
            if (!in_array($active, [0, 1], true)) {
                $errors[] = "Строка {$line}: is_active должен быть 0 или 1.";
                continue;
            }
            if (isset($sources[$source])) {
                $errors[] = "Строка {$line}: source_path '{$source}' уже указан в строке {$sources[$source]}.";
                continue;
            }

            $sources[$source] = $line;
            $rows[] = [
                'source_path' => $source,
                'target_path' => $target,
                'status_code' => $status,
                'is_active' => $active,
            ];
        }

        foreach ($rows as $row) {
            if (isset($sources[$row['target_path']])) {
                $errors[] = "Цепочка редиректов запрещена: '{$row['source_path']}' ведёт на другой source_path '{$row['target_path']}'.";
            }
        }

        return ['rows' => $errors === [] ? $rows : [], 'errors' => array_values(array_unique($errors))];
    }

    /** @param array<string, true> $allowedHosts */
    private function normaliseUrl(string $value, array $allowedHosts): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            $parts = parse_url($value);
            $host = strtolower((string)($parts['host'] ?? ''));
            if (!isset($allowedHosts[$host]) || isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])) {
                return '';
            }
            $value = (string)($parts['path'] ?? '');
        } elseif (str_starts_with($value, '//') || str_contains($value, '?') || str_contains($value, '#')) {
            return '';
        }

        return LegacyUrlRedirector::normalisePath($value);
    }

    /** @return array<string, true> */
    private function hostMap(array $hosts): array
    {
        $map = [];
        foreach ($hosts as $host) {
            $host = strtolower(trim((string)$host));
            if ($host !== '') {
                $map[$host] = true;
            }
        }
        return $map;
    }
}
