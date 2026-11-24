<?php
declare(strict_types=1);
namespace app\services;

/** Homepage choices come only from registered size pages with visible products. */
final class HomeTireSelector
{
    public static function load(): array
    {
        return self::choices(\R::getAll(
            "SELECT av.value, MIN(u.sef) AS sef, COUNT(DISTINCT p.id) AS total
             FROM attribute_value av
             INNER JOIN attribute_group ag ON ag.id = av.attr_group_id AND LOWER(ag.url_params) = 'tiposize'
             INNER JOIN url_alias u ON u.sef = av.alias AND LOWER(u.view) = 'tiposize'
             INNER JOIN attribute_product ap ON ap.attr_id = av.id
             INNER JOIN product p ON p.id = ap.product_id AND p.hide = 'show'
             WHERE u.sef <> '' GROUP BY av.id, av.value ORDER BY total DESC, av.value"
        ));
    }

    public static function choices(array $rows): array
    {
        $result = []; $seen = [];
        foreach ($rows as $row) {
            $size = TireSizeNotation::display(trim((string)$row['value']));
            $alias = trim((string)$row['sef'], '/');
            $key = mb_strtolower(TireSizeNotation::searchKey($size), 'UTF-8');
            if ($key === '' || $alias === '' || (int)$row['total'] < 1 || isset($seen[$key])) continue;
            $seen[$key] = true;
            $result[] = ['size' => $size, 'url' => '/' . implode('/', array_map('rawurlencode', explode('/', $alias)))];
        }
        return $result;
    }
}
