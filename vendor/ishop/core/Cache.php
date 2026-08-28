<?php

declare(strict_types=1);

namespace ishop;

final class Cache
{
    use TSingletone;

    public function set(string $key, mixed $data, int $seconds = 3600): bool
    {
        if ($seconds <= 0 || !$this->ensureDirectory()) {
            return false;
        }

        $payload = serialize(['data' => $data, 'end_time' => time() + $seconds]);
        $handle = @fopen($this->path($key), 'c+b');
        if ($handle === false) {
            return false;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return false;
            }
            if (!ftruncate($handle, 0) || rewind($handle) === false) {
                return false;
            }
            $written = fwrite($handle, $payload);
            fflush($handle);
            return $written === strlen($payload);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function get(string $key): mixed
    {
        $handle = @fopen($this->path($key), 'rb');
        if ($handle === false) {
            return false;
        }

        try {
            if (!flock($handle, LOCK_SH)) {
                return false;
            }
            $payload = stream_get_contents($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        if (!is_string($payload) || $payload === '') {
            $this->delete($key);
            return false;
        }

        $content = @unserialize($payload, ['allowed_classes' => false]);
        if (!is_array($content)
            || !array_key_exists('data', $content)
            || !isset($content['end_time'])
            || !is_int($content['end_time'])
        ) {
            $this->delete($key);
            return false;
        }

        if (time() > $content['end_time']) {
            $this->delete($key);
            return false;
        }

        return $content['data'];
    }

    public function delete(string $key): bool
    {
        $file = $this->path($key);
        return !is_file($file) || @unlink($file);
    }

    public function clear(): bool
    {
        if (!is_dir(CACHE)) {
            return true;
        }

        $success = true;
        foreach (glob(CACHE . DIRECTORY_SEPARATOR . '*.cache') ?: [] as $file) {
            if (is_file($file) && !@unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    private function path(string $key): string
    {
        return CACHE . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.cache';
    }

    private function ensureDirectory(): bool
    {
        return is_dir(CACHE) || @mkdir(CACHE, 0775, true) || is_dir(CACHE);
    }
}
