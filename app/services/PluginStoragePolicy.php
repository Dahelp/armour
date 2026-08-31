<?php

declare(strict_types=1);

namespace app\services;

final class PluginStoragePolicy
{
    private const IMAGE_PLUGINS = ['complete', 'technics'];

    public static function table(string $plugin, string $suffix = ''): string
    {
        $plugin = strtolower(trim($plugin));
		$validBaseTable = $suffix === '' && in_array($plugin, self::IMAGE_PLUGINS, true);
		$validGalleryTable = $suffix === '_gallery' && $plugin === 'complete';
		if (!$validBaseTable && !$validGalleryTable) {
            throw new \InvalidArgumentException('Недопустимый раздел плагина.');
        }

        return $plugin === 'technics' ? 'technics' . $suffix : 'plagins_' . $plugin . $suffix;
    }

    public static function imageName(string $name): string
    {
        $name = trim($name);
        if ($name === '' || basename(str_replace('\\', '/', $name)) !== $name || !preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
            throw new \InvalidArgumentException('Недопустимое имя изображения.');
        }

        return $name;
    }
}
