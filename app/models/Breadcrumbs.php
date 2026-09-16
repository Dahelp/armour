<?php

namespace app\models;

use ishop\App;

class Breadcrumbs
{
    /**
     * Render the only supported storefront breadcrumb markup.
     *
     * Each item is ['label' => string, 'url' => ?string]. The home item is
     * added automatically; the last item is always rendered as plain text.
     */
    public static function render(array $items = []): string
    {
        $normalised = [];
        foreach ($items as $item) {
            $label = trim((string)($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $normalised[] = [
                'label' => $label,
                'url' => isset($item['url']) && $item['url'] !== '' ? (string)$item['url'] : null,
            ];
        }

        $trail = '<a href="' . self::escape((string)PATH) . '">Главная</a>';
        $lastIndex = count($normalised) - 1;
        foreach ($normalised as $index => $item) {
            $trail .= '<span class="breadcrumb-separator"> / </span>';
            if ($index !== $lastIndex && $item['url'] !== null) {
                $trail .= '<a href="' . self::escape($item['url']) . '">' . self::escape($item['label']) . '</a>';
            } else {
                $trail .= self::escape($item['label']);
            }
        }

        return '<div class="storefront-breadcrumb"><div class="col-full">'
            . '<nav class="woocommerce-breadcrumb" aria-label="Хлебные крошки">'
            . $trail
            . '</nav></div></div>';
    }

    public static function getBreadcrumbs($category_id, $bname = '', $alias_active = '', $controller = ''): string
    {
        $cats = App::$app->getProperty('cats');
        $parts = self::getParts($cats, $category_id);
        $items = [];

        foreach ($parts as $alias => $name) {
            $items[] = [
                'label' => (string)$name,
                'url' => $alias === $alias_active ? null : rtrim((string)PATH, '/') . '/' . ltrim((string)$alias, '/'),
            ];
        }

        if ($bname !== '') {
            $items[] = ['label' => (string)$bname];
        }

        return self::render($items);
    }

    public static function getParts($cats, $id): array
    {
        if (!$id) {
            return [];
        }

        $breadcrumbs = [];
        foreach ($cats as $category) {
            if (isset($cats[$id])) {
                $breadcrumbs[$cats[$id]['alias']] = $cats[$id]['name'];
                $id = $cats[$id]['parent_id'];
            } else {
                break;
            }
        }

        return array_reverse($breadcrumbs, true);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
