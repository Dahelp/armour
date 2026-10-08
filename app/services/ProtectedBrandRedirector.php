<?php
declare(strict_types=1);

namespace app\services;

/**
 * Prevents legacy brand landing pages from remaining accessible or indexed.
 * These paths are sent to the generic ATV tyre catalogue, not to a page that
 * uses a third party mark as a category name.
 */
final class ProtectedBrandRedirector
{
    public const TARGET_PATH = 'catalog-kvadrotciklov';

    public static function redirectIfNeeded(string $requestPath): void
    {
        if (!in_array(strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')), ['GET', 'HEAD'], true)) {
            return;
        }

        if (!self::isProtectedBrandPath($requestPath)) {
            return;
        }

        header('Location: ' . rtrim(PATH, '/') . '/' . self::TARGET_PATH, true, 301);
        exit;
    }

    public static function isProtectedBrandPath(string $requestPath): bool
    {
        $path = LegacyUrlRedirector::normalisePath($requestPath);
        if ($path === '') {
            return false;
        }

        return preg_match(
            '~(?:^|[/_.-])(?:cf[_.-]*moto|сф[_.-]*мото|цф[_.-]*мото|cforce)(?=$|[/_.-])~iu',
            $path
        ) === 1;
    }
}
