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

        $conflict = \R::findOne('url_alias', 'sef = ?', [$sef]);
        if ($conflict && ((string)$conflict->view !== $view || (int)$conflict->urlid !== $urlId)) {
            throw new \RuntimeException('Такой SEO URL уже используется другой страницей', 409);
        }

        $alias = \R::findOne('url_alias', 'view = ? AND urlid = ?', [$view, $urlId]);
        if (!$alias) {
            $alias = \R::dispense('url_alias');
        }
        $alias->sef = $sef;
        $alias->view = $view;
        $alias->urlid = $urlId;
        \R::store($alias);
    }

    public function remove(string $sef, string $view): void
    {
        $sef = self::normaliseSef($sef);
        $view = trim($view);
        if ($sef === '' || $view === '') {
            return;
        }

        $alias = \R::findOne('url_alias', 'sef = ? AND view = ?', [$sef, $view]);
        if ($alias) {
            \R::trash($alias);
        }
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
