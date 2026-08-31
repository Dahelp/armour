<?php

declare(strict_types=1);

namespace app\services;

final class CsrfProtection
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function validateRequest(): void
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $expected = self::token();
        $provided = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? ''));
        if ($provided !== '' && hash_equals($expected, $provided)) {
            return;
        }

        if (self::hasSameOrigin()) {
            return;
        }

        http_response_code(419);
        throw new \Exception('Срок действия формы истёк. Обновите страницу и повторите отправку.', 419);
    }

    private static function hasSameOrigin(): bool
    {
        $source = (string)($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
        if ($source === '') {
            return false;
        }

        $sourceHost = strtolower((string)parse_url($source, PHP_URL_HOST));
        $sourcePort = parse_url($source, PHP_URL_PORT);
        $configuredHost = strtolower((string)parse_url((string)config_env('APP_URL', ''), PHP_URL_HOST));
        $requestHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        $requestHost = preg_replace('/:\d+$/', '', $requestHost) ?? '';

        if ($sourceHost === '' || ($sourceHost !== $configuredHost && $sourceHost !== $requestHost)) {
            return false;
        }

        $configuredPort = parse_url((string)config_env('APP_URL', ''), PHP_URL_PORT);
        return $sourcePort === null || $configuredPort === null || $sourcePort === $configuredPort;
    }
}
