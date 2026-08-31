<?php

declare(strict_types=1);

namespace app\services;

final class RemoteImageDownloader
{
    public function download(string $url, string $directory, int $maxBytes = 20971520): array
    {
        $temporaryPath = (new RemoteXmlDownloader())->download($url, $directory, $maxBytes);
        $field = '__remote_image_' . bin2hex(random_bytes(6));
        try {
            $_FILES[$field] = [
                'name' => basename((string)(parse_url($url, PHP_URL_PATH) ?: 'image')),
                'type' => '',
                'tmp_name' => $temporaryPath,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($temporaryPath),
            ];
            ImageFileStorage::secureUploadField($field, $maxBytes);
            $name = (string)$_FILES[$field]['name'];
            $finalPath = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $name;
            if (!rename($temporaryPath, $finalPath)) {
                throw new \RuntimeException('Не удалось подготовить удалённое изображение.');
            }

            return [
                'path' => $finalPath,
                'name' => $name,
                'extension' => strtolower((string)pathinfo($name, PATHINFO_EXTENSION)),
            ];
        } finally {
            unset($_FILES[$field]);
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }
}
