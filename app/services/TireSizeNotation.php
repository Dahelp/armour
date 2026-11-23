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

    /** $expression is a trusted SQL expression supplied by the caller, never user input. */
    public static function searchExpression(string $expression, string $query): string
    {
        if (!self::containsSize($query)) {
            return $expression;
        }
        $expression = "REPLACE($expression, ',', '.')";
        foreach (['*' => 'x', 'X' => 'x', 'х' => 'x', 'Х' => 'x', '×' => 'x', ' ' => '', "\t" => '', "\n" => '', "\r" => '', "\u{00A0}" => '', "\u{202F}" => ''] as $from => $to) {
            $expression = "REPLACE($expression, '$from', '$to')";
        }
        return $expression;
    }
}
