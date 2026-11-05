<?php
declare(strict_types=1);
namespace app\controllers\admin;

use app\services\LegacyAdminAccess;

final class LegacyController extends AppController
{
    public function indexAction(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!LegacyAdminAccess::allowed($user)) throw new \Exception('Страница не найдена', 404);
        // Recheck the account in the database, rather than trusting a stale admin session.
        $current = \R::getRow('SELECT id, role FROM user WHERE id = ?', [(int)$user['id']]);
        if (!LegacyAdminAccess::allowed($current ?: null)) throw new \Exception('Страница не найдена', 404);
        $ticket = LegacyAdminAccess::launchTicket($current);
        $nonce = bin2hex(random_bytes(16));
        header('Cache-Control: no-store, private, max-age=0');
        header('Referrer-Policy: no-referrer');
        header('X-Robots-Tag: noindex, nofollow');
        header("Content-Security-Policy: default-src 'none'; form-action https://armour-shina.ru; script-src 'nonce-$nonce'; base-uri 'none'; frame-ancestors 'none'");
        echo '<!doctype html><html lang="ru"><meta charset="utf-8"><title>Переход в старую админку</title><form id="legacy-access" method="post" action="https://armour-shina.ru/__owner_bridge.php"><input type="hidden" name="ticket" value="'.htmlspecialchars($ticket, ENT_QUOTES, 'UTF-8').'"><noscript><button type="submit">Открыть админку armour-shina.ru</button></noscript></form><script nonce="'.$nonce.'">document.getElementById("legacy-access").submit();</script></html>';
        exit;
    }
}
