<?php

/**
 * Read configuration from the process environment or an ignored local .env file.
 * Production should prefer real server environment variables.
 */
function config_env(string $key, $default = null)
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    static $local = null;
    if ($local === null) {
        $local = [];
        $file = dirname(__DIR__) . '/.env';
        if (is_readable($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') {
                    continue;
                }

                [$name, $item] = array_pad(explode('=', $line, 2), 2, '');
                $name = trim($name);
                if ($name === '') {
                    continue;
                }

                $item = trim($item);
                if (strlen($item) >= 2) {
                    $first = $item[0];
                    $last = $item[strlen($item) - 1];
                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $item = substr($item, 1, -1);
                    }
                }
                $local[$name] = $item;
            }
        }
    }

    return array_key_exists($key, $local) ? $local[$key] : $default;
}
