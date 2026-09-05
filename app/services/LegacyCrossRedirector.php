<?php
declare(strict_types=1);

namespace app\services;

final class LegacyCrossRedirector
{
    public static function redirectIfNeeded(string $requestPath): void
    {
        if(!in_array(strtoupper((string)($_SERVER['REQUEST_METHOD']??'GET')),['GET','HEAD'],true))return;
        $alias=CrossUrl::legacyAlias($requestPath);
        if($alias==='')return;
        $row=\R::getRow(
            "SELECT c.cross_abbreviated_name,p.alias AS product_alias FROM plagins_cross c INNER JOIN product p ON p.id=c.product_id WHERE c.cross_abbreviated_name=? AND p.hide!='hide' ORDER BY c.id LIMIT 1",
            [$alias]
        );
        if(!$row)return;
        $target=CrossUrl::canonicalPath((string)$row['cross_abbreviated_name']);
        if($target==='')$target=LegacyUrlRedirector::normalisePath((string)$row['product_alias']);
        if($target==='')return;
        header('Location: '.rtrim(PATH,'/').'/'.$target,true,301);
        exit;
    }
}
