<?php

declare(strict_types=1);

namespace app\services;

final class LegacyUrlHttpClient
{
    /** @return array{status: int, headers: array<string, string>, body: string} */
    public function get(string $url): array
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new \RuntimeException('Не удалось инициализировать HTTP-клиент.');
        }

        $headers = [];
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'TechTires-Legacy-URL-Auditor/1.0',
            CURLOPT_ENCODING => '',
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$headers): int {
                $length = strlen($line);
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return $length;
            },
        ]);

        $body = curl_exec($handle);
        $error = curl_error($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if (!is_string($body)) {
            throw new \RuntimeException($error !== '' ? $error : 'HTTP-запрос завершился ошибкой.');
        }
        if (strlen($body) > 5 * 1024 * 1024) {
            throw new \RuntimeException('Ответ превышает допустимые 5 МБ.');
        }

        return ['status' => $status, 'headers' => $headers, 'body' => $body];
    }
}
