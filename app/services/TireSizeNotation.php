<?php
declare(strict_types=1);

namespace app\services;

/** Presentation/search only: never use this to rewrite a registered URL or source data. */
final class TireSizeNotation
{
    public static function display(string $text): string
    {
        return (string)preg_replace('/(?<=\d)\h*[*xXхХ×]\h*(?=\d)/u', 'x', $text);
    }

    public static function containsSize(string $query): bool
    {
        return preg_match('/\d\h*[*xXхХ×.,\/\-Rr]\h*\d/u', $query) === 1;
    }

    public static function searchKey(string $query): string
    {
        if (!self::containsSize($query)) {
            return $query;
        }
        $query = strtr($query, ['*' => 'x', 'X' => 'x', 'х' => 'x', 'Х' => 'x', '×' => 'x', ',' => '.']);
        return (string)preg_replace('/\s+/u', '', $query);
    }

    public static function searchPattern(string $query): string
    {
        if (!self::containsSize($query)) {
            return '%' . $query . '%';
        }
        $parts = [];
        foreach (preg_split('//u', self::searchKey($query), -1, PREG_SPLIT_NO_EMPTY) as $character) {
            $parts[] = match ($character) {
                'x' => '(x|X|х|Х|[*]|×)',
                '.' => '[.,]',
                default => preg_quote($character, '~'),
            };
        }
        return implode("[[:space:]\u{00A0}\u{202F}]*", $parts);
    }

    /** Avoid deeply nested SQL functions on the production MySQL version. */
    public static function searchExpression(string $expression, string $query): string
    {
        return $expression . (self::containsSize($query) ? ' REGEXP ?' : ' LIKE ?');
    }
}
