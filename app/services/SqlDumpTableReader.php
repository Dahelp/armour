<?php

declare(strict_types=1);

namespace app\services;

/** Reads selected INSERT statements without executing an untrusted SQL dump. */
final class SqlDumpTableReader
{
    /** @return list<array<string, mixed>> */
    public function readTable(string $filename, string $table): array
    {
        if (!preg_match('/^[a-z0-9_]+$/i', $table)) {
            throw new \InvalidArgumentException('Некорректное имя таблицы.');
        }
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \RuntimeException('SQL-дамп недоступен для чтения.');
        }
        $size = filesize($filename);
        if ($size === false || $size > 512 * 1024 * 1024) {
            throw new \RuntimeException('SQL-дамп превышает допустимые 512 МБ.');
        }
        $sql = file_get_contents($filename);
        if (!is_string($sql)) {
            throw new \RuntimeException('Не удалось прочитать SQL-дамп.');
        }

        $rows = [];
        $pattern = '/INSERT\s+INTO\s+`' . preg_quote($table, '/') . '`\s*\(([^)]+)\)\s*VALUES\s*/i';
        if (!preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        foreach ($matches[0] as $index => $match) {
            preg_match_all('/`([^`]+)`/', $matches[1][$index][0], $columnMatches);
            $columns = $columnMatches[1];
            $offset = $match[1] + strlen($match[0]);
            foreach ($this->parseTuples($sql, $offset) as $values) {
                if (count($columns) === count($values)) {
                    $rows[] = array_combine($columns, $values);
                }
            }
        }
        return $rows;
    }

    /** @return list<list<mixed>> */
    private function parseTuples(string $sql, int $offset): array
    {
        $rows = [];
        $values = [];
        $token = '';
        $depth = 0;
        $quoted = false;
        $escaped = false;
        $length = strlen($sql);

        for ($position = $offset; $position < $length; ++$position) {
            $character = $sql[$position];
            if ($quoted) {
                $token .= $character;
                if ($escaped) {
                    $escaped = false;
                } elseif ($character === '\\') {
                    $escaped = true;
                } elseif ($character === "'") {
                    if (($sql[$position + 1] ?? '') === "'") {
                        $token .= "'";
                        ++$position;
                    } else {
                        $quoted = false;
                    }
                }
                continue;
            }
            if ($character === "'") {
                $quoted = true;
                $token .= $character;
                continue;
            }
            if ($character === '(') {
                if ($depth++ === 0) {
                    $values = [];
                    $token = '';
                } else {
                    $token .= $character;
                }
                continue;
            }
            if ($character === ')' && $depth > 0) {
                if (--$depth === 0) {
                    $values[] = $this->decodeValue($token);
                    $rows[] = $values;
                    $token = '';
                } else {
                    $token .= $character;
                }
                continue;
            }
            if ($character === ',' && $depth === 1) {
                $values[] = $this->decodeValue($token);
                $token = '';
                continue;
            }
            if ($character === ';' && $depth === 0) {
                break;
            }
            if ($depth > 0) {
                $token .= $character;
            }
        }
        return $rows;
    }

    private function decodeValue(string $token): mixed
    {
        $token = trim($token);
        if (strcasecmp($token, 'NULL') === 0) {
            return null;
        }
        if (strlen($token) >= 2 && $token[0] === "'" && $token[strlen($token) - 1] === "'") {
            $token = substr($token, 1, -1);
            return strtr($token, [
                '\\0' => "\0", '\\n' => "\n", '\\r' => "\r", '\\Z' => "\x1a",
                '\\"' => '"', "\\'" => "'", '\\\\' => '\\', "''" => "'",
            ]);
        }
        if (is_numeric($token)) {
            return str_contains($token, '.') ? (float)$token : (int)$token;
        }
        return $token;
    }
}
