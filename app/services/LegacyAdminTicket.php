<?php
namespace app\services;

/** Kept compatible with PHP 5.4 for the original Armour application. */
final class LegacyAdminTicket
{
    public static function equals($known, $provided)
    {
        if (!is_string($known) || !is_string($provided) || strlen($known) !== strlen($provided)) return false;
        if (function_exists('hash_equals')) return hash_equals($known, $provided);
        $difference = 0;
        for ($index = 0, $length = strlen($known); $index < $length; ++$index) {
            $difference |= ord($known[$index]) ^ ord($provided[$index]);
        }
        return $difference === 0;
    }

    public static function sign($claims, $secret)
    {
        if (strlen($secret) < 64) throw new \RuntimeException('Legacy access is not configured.');
        $json = json_encode($claims);
        if ($json === false) throw new \RuntimeException('Invalid access claims.');
        $payload = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        return $payload.'.'.hash_hmac('sha256', $payload, $secret);
    }

    public static function verify($ticket, $secret, $purpose, $ownerId, $now, $maxLifetime)
    {
        if (!is_string($ticket) || strlen($ticket) > 4096 || strlen($secret) < 64) return false;
        $parts = explode('.', $ticket);
        if (count($parts) !== 2 || !preg_match('/^[a-zA-Z0-9_-]+$/D', $parts[0]) || !preg_match('/^[a-f0-9]{64}$/D', $parts[1])) return false;
        if (!self::equals(hash_hmac('sha256', $parts[0], $secret), $parts[1])) return false;
        $decoded = base64_decode(strtr($parts[0], '-_', '+/'), true);
        $claims = $decoded === false ? null : json_decode($decoded, true);
        if (!is_array($claims) || !isset($claims['purpose'], $claims['uid'], $claims['exp'], $claims['nonce'])) return false;
        if ($claims['purpose'] !== $purpose || $claims['uid'] !== (int)$ownerId || !is_int($claims['exp']) || $claims['exp'] <= $now || $claims['exp'] > $now + $maxLifetime) return false;
        if (!is_string($claims['nonce']) || !preg_match('/^[a-f0-9]{64}$/D', $claims['nonce'])) return false;
        return $claims;
    }
}
