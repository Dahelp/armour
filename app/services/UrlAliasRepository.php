<?php

declare(strict_types=1);

namespace app\services;

final class UrlAliasRepository
{
    public function save(string $sef, string $view, int $urlId): void
    {
        $sef = self::normaliseSef($sef);
        $view = trim($view);
        if ($sef === '' || $view === '' || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $view)) {
            throw new \InvalidArgumentException('Некорректный SEO URL или контроллер');
        }

        $conflict = \R::getRow('SELECT id, view, urlid FROM url_alias WHERE sef = ? LIMIT 1', [$sef]);
        if ($conflict && ((string)$conflict['view'] !== $view || (int)$conflict['urlid'] !== $urlId)) {
            throw new \RuntimeException('Такой SEO URL уже используется другой страницей', 409);
        }

        $aliasId = (int)\R::getCell('SELECT id FROM url_alias WHERE view = ? AND urlid = ? LIMIT 1', [$view, $urlId]);
        if ($aliasId > 0) {
            \R::exec('UPDATE url_alias SET sef = ? WHERE id = ?', [$sef, $aliasId]);
        } elseif (!$conflict) {
            \R::exec('INSERT INTO url_alias (sef, view, urlid) VALUES (?, ?, ?)', [$sef, $view, $urlId]);
        }
    }

    public function remove(string $sef, string $view): void
    {
        $sef = self::normaliseSef($sef);
        $view = trim($view);
        if ($sef === '' || $view === '') {
            return;
        }

        \R::exec('DELETE FROM url_alias WHERE sef = ? AND view = ?', [$sef, $view]);
    }

    public static function normaliseSef(string $sef): string
    {
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', trim($sef))) {
            return '';
        }
        $sef = strtolower(trim(str_replace('\\', '/', $sef), '/'));
        if ($sef === '' || str_contains($sef, '..') || !preg_match('#^[a-z0-9][a-z0-9._~!$&\'()*+,;=:@%/-]*$#', $sef)) {
            return '';
        }

        return preg_replace('#/+#', '/', $sef) ?? '';
    }
}
