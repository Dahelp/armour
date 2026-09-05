<?php
declare(strict_types=1);

namespace app\services;

final class CrossUrl
{
    public static function normaliseAlias(string $alias): string
    {
        $alias=trim(rawurldecode($alias));
        if($alias===''||str_contains($alias,'/')||str_contains($alias,'\\')||preg_match('/[\x00-\x1F\x7F]/',$alias))return '';
        return mb_strtolower($alias,'UTF-8');
    }

    public static function canonicalPath(string $alias): string
    {
        $alias=self::normaliseAlias($alias);
        return self::isRoutableAlias($alias)?'cross/'.rawurlencode($alias):'';
    }

    public static function isRoutableAlias(string $alias): bool
    {
        $alias=self::normaliseAlias($alias);
        return $alias!==''&&preg_match('/^[a-z0-9._~+-]+$/D',$alias)===1;
    }

    public static function legacyAlias(string $path): string
    {
        $path=trim(rawurldecode($path),'/');
        if(!preg_match('/^crossing-(.+)\.html$/iu',$path,$matches))return '';
        return self::normaliseAlias($matches[1]);
    }
}
