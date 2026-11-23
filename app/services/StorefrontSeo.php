<?php
declare(strict_types=1);

namespace app\services;

/** Render-time defaults only; authored content and registered URLs stay in the database. */
final class StorefrontSeo
{
    private static function field(mixed $row, string $key): string
    {
        if (is_array($row) || $row instanceof \ArrayAccess) return (string)($row[$key] ?? '');
        return is_object($row) ? (string)($row->$key ?? '') : '';
    }

    public static function text(string $html): string
    {
        return trim((string)preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function first(string ...$values): string
    {
        foreach ($values as $value) if (self::text($value) !== '') return $value;
        return '';
    }

    public static function prepare(array $data, array $route): array
    {
        $controller = (string)($route['controller'] ?? '');
        $isFilter = in_array($controller, ['Tiposize', 'Marka', 'Protect', 'Diameter', 'Disk', 'Ground', 'Rim', 'Tipshiny'], true)
            && isset($data['find'], $data['params']);
        $entity = $data['category'] ?? $data['product'] ?? $data['find'] ?? $data['type'] ?? $data['page'] ?? [];
        $value = TireSizeNotation::display(self::field($entity, 'value'));
        $heading = self::first(self::field($entity, 'name'), self::field($entity, 'page_name'), self::field($entity, 'title'), self::field($entity, 'seo_title'));
        if ($isFilter && self::text(self::field($entity, 'name')) === '') {
            $heading = match ($controller) {
                'Tiposize', 'Marka' => 'Шины ' . $value,
                'Protect' => 'Шины с протектором ' . $value,
                default => self::field($data['params'], 'title') . ': ' . $value,
            };
        }
        $heading = TireSizeNotation::display(self::text($heading));
        if ($heading === '') {
            $heading = ['Main' => 'Промышленные шины и комплектующие', 'Search' => 'Поиск товаров', 'Cart' => 'Корзина', 'News' => 'Новости', 'Articles' => 'Статьи', 'Sitemap' => 'Карта сайта'][$controller]
                ?? self::first(self::field($data['type'] ?? [], 'title'), 'Каталог ТехШина');
        }
        $content = self::first(self::field($entity, 'content'), self::field($entity, 'seo_content'));
        $catalogHtml = $content;
        if ($isFilter) {
            $template = self::field($data['inseo'] ?? [], 'content');
            if (self::text($template) !== '') {
                $ids = [];
                foreach (($data['products'] ?? []) as $product) $ids[] = (int)self::field($product, 'id');
                $ids = array_filter($ids);
                if (str_contains($template, '{')) {
                    $template = $ids ? \ishop\App::seoreplacetiposize($template, implode(',', $ids)) : '';
                }
                $catalogHtml .= $template;
            }
            if (self::text($catalogHtml) === '') {
                $label = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
                $catalogHtml = '<h2>Подбор: ' . $label . '</h2>'
                    . '<p>Сравните характеристики, цены и наличие товаров в подборке «' . $label . '». '
                    . 'Перед заказом проверьте маркировку установленной шины, размер диска и требования производителя техники.</p>'
                    . '<p>При подборе учитывайте конструкцию шины, допустимую нагрузку, рисунок протектора и условия эксплуатации. '
                    . 'Если нужна помощь, сообщите специалисту модель техники и обозначение на боковине шины.</p>';
            }
        }
        $description = self::first(self::field($entity, 'description'), self::field($entity, 'seo_description'), self::text($content));
        if ($description === '') $description = $heading . '. Характеристики, цены и наличие в каталоге ТехШина. Помощь в подборе и оформлении заказа.';
        return [
            'heading' => $heading,
            'title' => $heading . ' — ТехШина',
            'description' => mb_substr(self::text($description), 0, 180, 'UTF-8'),
            'catalogHtml' => $catalogHtml,
        ];
    }

    /** Existing metadata is already escaped by Controller::setMeta. */
    public static function completeMeta(array $meta, array $page): array
    {
        foreach (['title' => 'title', 'desc' => 'description'] as $key => $field) {
            if (self::text((string)($meta[$key] ?? '')) === '') {
                $meta[$key] = htmlspecialchars($page[$field], ENT_QUOTES, 'UTF-8');
            }
        }
        return $meta;
    }
}
