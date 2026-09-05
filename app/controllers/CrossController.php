<?php
declare(strict_types=1);

namespace app\controllers;

use app\services\CrossUrl;
use ishop\App;

final class CrossController extends AppController
{
    public function viewAction(): void
    {
        $alias=CrossUrl::normaliseAlias((string)($this->route['alias']??''));
        if($alias==='')throw new \Exception('Страница не найдена',404);
        $cross=\R::getRow(
            "SELECT c.*,cv.name AS cross_vendor,p.id AS product_id,p.name AS product_name,p.article,p.alias AS product_alias,p.img,p.price,p.quantity,p.description AS product_description,b.name AS brand_name
             FROM plagins_cross c
             LEFT JOIN plagins_cross_vendor cv ON cv.id=c.vendor_id
             INNER JOIN product p ON p.id=c.product_id
             LEFT JOIN brand b ON b.id=p.brand_id
             WHERE c.cross_abbreviated_name=? AND p.hide!='hide'
             ORDER BY c.id LIMIT 1",
            [$alias]
        );
        if(!$cross)throw new \Exception('Страница не найдена',404);

        $canonicalPath=CrossUrl::canonicalPath((string)$cross['cross_abbreviated_name']);
        if($canonicalPath==='')throw new \Exception('Страница не найдена',404);
        $requestedPath='cross/'.rawurlencode($alias);
        if($requestedPath!==$canonicalPath&&in_array(strtoupper((string)($_SERVER['REQUEST_METHOD']??'GET')),['GET','HEAD'],true)){
            header('Location: '.rtrim(PATH,'/').'/'.$canonicalPath,true,301);
            exit;
        }

        $otherCrosses=\R::getAll(
            'SELECT c.cross_name,c.cross_abbreviated_name,c.tip_cross,c.equipment_vendor,cv.name AS cross_vendor FROM plagins_cross c INNER JOIN plagins_cross_vendor cv ON cv.id=c.vendor_id WHERE c.product_id=? AND c.id!=? ORDER BY cv.name,c.cross_name',
            [(int)$cross['product_id'],(int)$cross['id']]
        );
        $title='Аналог фильтра '.$cross['cross_name'].' '.$cross['cross_vendor'].' — купить EKKA';
        $description=$cross['product_name'].' — аналог фильтра '.$cross['cross_vendor'].' '.$cross['cross_name'].'. Характеристики, цена и доставка по России.';
        $image=!empty($cross['img'])?rtrim(PATH,'/').'/images/product/baseimg/'.rawurlencode((string)$cross['img']):rtrim(PATH,'/').'/images/'.ltrim((string)App::$app->getProperty('og_logo'),'/');
        $this->setMeta($title,$description,'',App::$app->getProperty('shop_name'),$image,rtrim(PATH,'/').'/'.$canonicalPath);
        $this->set(compact('cross','otherCrosses','canonicalPath'));
    }
}
