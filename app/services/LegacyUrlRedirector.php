<?php

namespace app\services;

/**
 * Optional redirect layer for URLs imported from armour-shina.ru.
 * It stays disabled until the migration table and URL map are ready.
 */
final class LegacyUrlRedirector
{
    public static function redirectIfNeeded(string $requestPath): void
    {
        if (!filter_var(getenv('LEGACY_REDIRECTS_ENABLED') ?: '0', FILTER_VALIDATE_BOOL)) {
            return;
        }

        $requestPath = self::normalisePath($requestPath);
        if ($requestPath === '') {
            return;
        }

        $redirect = \R::findOne(
            'legacy_url_redirect',
            'source_path = ? AND is_active = 1',
            [$requestPath]
        );

        if (!$redirect) {
            return;
        }

        $targetPath = self::normalisePath((string)$redirect->target_path);
        if ($targetPath === '' || $targetPath === $requestPath) {
            return;
        }

        $statusCode = (int)$redirect->status_code;
        if (!in_array($statusCode, [301, 308], true)) {
            $statusCode = 301;
        }

        $baseUrl = rtrim((string)(getenv('LEGACY_REDIRECT_BASE_URL') ?: PATH), '/');
        header('Location: ' . $baseUrl . '/' . $targetPath, true, $statusCode);
        exit;
    }

    private static function normalisePath(string $path): string
    {
        $path = rawurldecode($path);
        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '' || strpos($path, '..') !== false) {
            return '';
        }

        return mb_strtolower($path, 'UTF-8');
    }
}
