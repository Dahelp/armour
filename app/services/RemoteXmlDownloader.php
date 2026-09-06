<?php

declare(strict_types=1);

namespace app\services;

final class RemoteXmlDownloader
{
    public function download(string $url, string $directory, int $maxBytes = 20971520, ?string $referer = null): string
    {
        $url = trim($url);
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Допустим только HTTP(S) адрес XML-файла.');
        }
        $host = strtolower((string)($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost') {
            throw new \InvalidArgumentException('Локальные и служебные адреса запрещены.');
        }

        $resolvedIp = null;
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $resolvedIp = $host;
        } else {
            $addresses = gethostbynamel($host) ?: [];
            $resolvedIp = $addresses[0] ?? null;
        }
        if ($resolvedIp === null || filter_var($resolvedIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new \InvalidArgumentException('Локальные и служебные адреса запрещены.');
        }

        $handle = curl_init($url);
		$options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FAILONERROR => true,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; TechTiresMigration/1.0)',
		];
		if ($referer !== null && $referer !== '') {
			$options[CURLOPT_REFERER] = $referer;
		}
		if (!filter_var($host, FILTER_VALIDATE_IP)) {
			$port = (int)($parts['port'] ?? (strtolower((string)$parts['scheme']) === 'https' ? 443 : 80));
			$options[CURLOPT_RESOLVE] = ["$host:$port:$resolvedIp"];
		}
		curl_setopt_array($handle, $options);
        $contents = curl_exec($handle);
        $error = curl_error($handle);
        curl_close($handle);
        if (!is_string($contents) || $contents === '' || strlen($contents) > $maxBytes) {
            throw new \RuntimeException($error !== '' ? $error : 'XML-файл пуст или превышает допустимый размер.');
        }

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Не удалось создать каталог импорта.');
        }
        $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16)) . '.xml';
        if (file_put_contents($path, $contents, LOCK_EX) === false) {
            throw new \RuntimeException('Не удалось сохранить XML-файл.');
        }

        return $path;
    }
}
