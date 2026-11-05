<?php
declare(strict_types=1);
namespace app\services;

final class LegacyAdminAccess
{
    public static function configuration(): array
    {
        $file = dirname(__DIR__, 2).'/config/legacy-admin.php';
        if (!is_file($file)) return [];
        $config = require $file;
        return is_array($config) ? $config : [];
    }

    public static function allowed(?array $user): bool
    {
        $config = self::configuration();
        return $user !== null && ($user['role'] ?? '') === 'admin'
            && (int)($config['owner_id'] ?? 0) > 0
            && (int)($user['id'] ?? 0) === (int)$config['owner_id']
            && strlen((string)($config['secret'] ?? '')) >= 64;
    }

    public static function launchTicket(array $user): string
    {
        if (!self::allowed($user)) throw new \RuntimeException('Страница не найдена', 404);
        $config = self::configuration();
        return LegacyAdminTicket::sign(['purpose'=>'legacy-admin-launch', 'uid'=>(int)$user['id'], 'exp'=>time()+45, 'nonce'=>bin2hex(random_bytes(32))], $config['secret']);
    }
}
