<?php

declare(strict_types=1);

namespace app\services;

final class ImageFileStorage
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public static function secureUploadField(string $field, int $maxBytes = 20971520): void
    {
        $upload = $_FILES[$field] ?? null;
        if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Файл изображения не был загружен.', 422);
        }
        $tmpName = (string)($upload['tmp_name'] ?? '');
        $size = is_file($tmpName) ? filesize($tmpName) : false;
		if ($tmpName === '' || (PHP_SAPI !== 'cli' && !is_uploaded_file($tmpName)) || $size === false || $size < 1 || $size > $maxBytes) {
            throw new \RuntimeException('Некорректный размер изображения.', 422);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpName);
        $dimensions = @getimagesize($tmpName);
		$width = (int)($dimensions[0] ?? 0);
		$height = (int)($dimensions[1] ?? 0);
		if (!is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime]) || $dimensions === false || $width < 1 || $height < 1 || $width > 12000 || $height > 12000 || $width * $height > 40000000) {
            throw new \RuntimeException('Файл не является допустимым изображением.', 422);
        }

        $_FILES[$field]['name'] = self::randomName(self::MIME_EXTENSIONS[$mime]);
        $_FILES[$field]['type'] = $mime;
        $_FILES[$field]['size'] = $size;
    }

    public static function randomName(string $extension): string
    {
        $extension = strtolower(ltrim($extension, '.'));
        if (!in_array($extension, array_values(self::MIME_EXTENSIONS), true)) {
            throw new \InvalidArgumentException('Недопустимое расширение изображения.');
        }

        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    public static function safeName(string $name): string
    {
        $name = trim($name);
        if ($name === '' || basename(str_replace('\\', '/', $name)) !== $name || !preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
            throw new \InvalidArgumentException('Недопустимое имя файла.');
        }

        return $name;
    }

    public static function delete(string $directory, string $name): bool
    {
        $name = self::safeName($name);
        $base = realpath($directory);
        if ($base === false) {
            return false;
        }
        $path = $base . DIRECTORY_SEPARATOR . $name;
        if (!is_file($path)) {
            return false;
        }

        return unlink($path);
    }
}
