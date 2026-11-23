<?php
declare(strict_types=1);
namespace app\services;

final class FilterSeo
{
    /** Authored values take precedence; INSEO is a fallback, never an override. */
    public static function resolve(string $individual, string $template, callable $expand): string
    {
        foreach ([$individual, $template] as $candidate) {
            if (StorefrontSeo::text($candidate) === '') continue;
            $result = str_contains($candidate, '{') ? (string)$expand($candidate) : $candidate;
            if (StorefrontSeo::text($result) !== '') return $result;
        }
        return '';
    }
}
