<?php
declare(strict_types=1);

namespace app\services;

final class HomeProductSelections
{
    public static function load(): array
    {
        $candidates = [];
        foreach (['sales' => 'sale', 'hits' => 'hit', 'new_products' => 'new_product'] as $key => $flag) {
            $candidates[$key] = \R::find('product', "$flag = '1' AND hide = 'show' AND quantity > 0 AND price > 0 ORDER BY id DESC LIMIT 12");
        }
        return self::distinct($candidates);
    }

    /** Promotions take priority; a product appears in only one homepage selection. */
    public static function distinct(array $candidates): array
    {
        $selected = [];
        $seen = [];
        foreach (['sales', 'hits', 'new_products'] as $key) {
            $selected[$key] = [];
            foreach ($candidates[$key] ?? [] as $product) {
                $id = (int)($product['id'] ?? 0);
                if ($id <= 0 || isset($seen[$id]) || ($product['hide'] ?? '') !== 'show'
                    || (float)($product['quantity'] ?? 0) <= 0 || (float)($product['price'] ?? 0) <= 0) continue;
                $selected[$key][] = $product;
                $seen[$id] = true;
                if (count($selected[$key]) === 4) break;
            }
        }
        return $selected;
    }
}
