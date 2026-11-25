<?php
declare(strict_types=1);
namespace app\services;

final class TireSizeDirectory
{
    public static function load(): array
    {
        return self::prepare(\R::getAll("SELECT av.id,av.value,u.sef,COUNT(DISTINCT p.id) AS total,
            COUNT(DISTINCT CASE WHEN p.quantity>0 THEN p.id END) AS stocked
            FROM attribute_value av
            JOIN attribute_group ag ON ag.id=av.attr_group_id AND LOWER(ag.url_params)='tiposize'
            JOIN url_alias u ON u.urlid=av.id AND LOWER(u.view)='tiposize' AND u.sef=av.alias
            JOIN attribute_product ap ON ap.attr_id=av.id
            JOIN product p ON p.id=ap.product_id AND p.hide='show'
            WHERE av.hide='show' AND av.value<>'' AND u.sef<>'' GROUP BY av.id,av.value,u.sef"));
    }

    public static function key(string $text): string
    {
        return preg_replace('/\s+/u', '', strtr(mb_strtolower(trim($text), 'UTF-8'), ['*'=>'x','х'=>'x','×'=>'x',','=>'.']));
    }

    public static function prepare(array $rows): array
    {
        $sizes=[];
        foreach ($rows as $row) {
            if ((int)$row['total']<1 || trim((string)$row['sef'],'/')==='') continue;
            $size=TireSizeNotation::display(trim((string)$row['value']));
            $sizes[]=['size'=>$size,'key'=>self::key($size),'url'=>'/'.implode('/',array_map('rawurlencode',explode('/',trim($row['sef'],'/')))),
                'total'=>(int)$row['total'],'stocked'=>(int)$row['stocked']];
        }
        usort($sizes,static fn($a,$b)=>strnatcasecmp($a['key'],$b['key']));
        return $sizes;
    }

    public static function matches(array $size,string $query,bool $stockOnly): bool
    {
        return (!$stockOnly || $size['stocked']>0) && str_contains($size['key'],self::key($query));
    }

    public static function productCount(int $count): string
    {
        $ending=$count%100;
        $word=$ending>=11 && $ending<=14?'товаров':match($count%10){1=>'товар',2,3,4=>'товара',default=>'товаров'};
        return $count.' '.$word;
    }
}
