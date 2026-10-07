<?php
declare(strict_types=1);

namespace app\controllers;

use app\services\CrossUrl;
use app\services\CatalogListingLoader;
use app\models\Breadcrumbs;
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
        $crossTitle='Аналог фильтра '.$cross['cross_name'].' '.$cross['cross_vendor'];
        $title=$crossTitle.' — купить EKKA';
        $description=$cross['product_name'].' — аналог фильтра '.$cross['cross_vendor'].' '.$cross['cross_name'].'. Характеристики, цена и доставка по России.';
        $image=!empty($cross['img'])?rtrim(PATH,'/').'/images/product/baseimg/'.rawurlencode((string)$cross['img']):rtrim(PATH,'/').'/images/'.ltrim((string)App::$app->getProperty('og_logo'),'/');
        $this->setMeta($title,$description,'',App::$app->getProperty('shop_name'),$image,rtrim(PATH,'/').'/'.$canonicalPath);
        // A cross is a virtual catalogue item: display its own details, while
        // retaining the real product ID for the existing cart handlers.
        $product=\R::findOne('product','id=? AND hide!=?',[(int)$cross['product_id'],'hide']);
        if(!$product)throw new \Exception('Страница не найдена',404);
        $product->name=$crossTitle;
        $product->article=(string)$cross['cross_name'];
        $product->description=$description;
        $product->content='<p><strong>Производитель:</strong> '.h((string)$cross['cross_vendor']).'</p>'
            .'<p><strong>Кросс-номер:</strong> '.h((string)$cross['cross_name']).'</p>'
            .'<p>Данный кросс соответствует фильтру EKKA <a href="/'.h(ltrim((string)$cross['product_alias'],'/')).'">'.h((string)$cross['product_name']).'</a>. При оформлении заказа в корзину будет добавлен совместимый фильтр EKKA.</p>';
        $breadcrumbs=Breadcrumbs::getBreadcrumbs((int)$product->category_id,$crossTitle,(string)$cross['cross_abbreviated_name'],'cross');
        $vendor=\R::findOne('brand','id=?',[(int)$product->brand_id]);
        if($vendor)$vendor->name=(string)$cross['cross_vendor'];
        $gallery=\R::findAll('gallery','product_id=?',[(int)$product->id]);
        $mods=[];
        $review=\R::getAll('SELECT * FROM review_product JOIN review ON review.id=review_product.review_id WHERE review_product.product_id=? ORDER BY review.date_post DESC',[(int)$product->id]);
        $reviewCount=count($review);
        $reviewStats=['review_count'=>$reviewCount,'average_rating'=>$reviewCount>0?array_sum(array_map(static fn(array $row): float=>(float)$row['point'],$review))/$reviewCount:0.0];
        $productFilters=\R::getAll('SELECT ag.title,ag.url_params,av.value,av.alias FROM attribute_group ag INNER JOIN attribute_category ac ON ac.group_id=ag.id INNER JOIN attribute_value av ON av.attr_group_id=ag.id INNER JOIN attribute_product ap ON ap.attr_id=av.id WHERE ap.product_id=? GROUP BY ag.id,ag.title,ag.url_params,av.value,av.alias',[(int)$product->id]);
        $attribute_group=\R::getAll('SELECT * FROM attribute JOIN product_attribute ON product_attribute.attribute_group_id=attribute.id WHERE product_attribute.product_id=? GROUP BY product_attribute.attribute_group_id',[(int)$product->id]);
        $attributeRows=\R::getAll('SELECT pa.attribute_group_id,pa.attribute_id,pa.attribute_text,a.attribute_name FROM product_attribute pa INNER JOIN attribute a ON a.id=pa.attribute_id WHERE pa.product_id=? ORDER BY a.attribute_position',[(int)$product->id]);
        $productAttributesByGroup=[];foreach($attributeRows as $row)$productAttributesByGroup[(int)$row['attribute_group_id']][]=$row;
        $crossRows=\R::getAll('SELECT c.cross_name,c.cross_abbreviated_name,c.equipment_vendor,cv.name AS vendor_name FROM plagins_cross c LEFT JOIN plagins_cross_vendor cv ON cv.id=c.vendor_id WHERE c.product_id=? ORDER BY cv.name,c.cross_name',[(int)$product->id]);
        $oemCrosses=[];$analogCrosses=[];foreach($crossRows as $row){if((int)$row['equipment_vendor']===1)$oemCrosses[]=$row;else $analogCrosses[]=$row;}
        $related=\R::getAll('SELECT DISTINCT p.* FROM related_product rp JOIN product p ON p.id=CASE WHEN rp.product_id=? THEN rp.related_id ELSE rp.product_id END WHERE (rp.product_id=? OR rp.related_id=?) AND p.hide=? ORDER BY p.category_id ASC,p.quantity DESC,p.name ASC',[(int)$product->id,(int)$product->id,(int)$product->id,'show']);
        $similar_all=\R::getAll('SELECT DISTINCT p.* FROM similar_product sp JOIN product p ON p.id=CASE WHEN sp.product_id=? THEN sp.similar_id ELSE sp.product_id END WHERE (sp.product_id=? OR sp.similar_id=?) AND p.hide=? ORDER BY p.category_id ASC,p.quantity DESC,p.name ASC',[(int)$product->id,(int)$product->id,(int)$product->id,'show']);
        $similar_categories=[];foreach($similar_all as $row)$similar_categories[$row['category_id']][]=$row;
        $related_count=count($related);$similar_count=count($similar_all);
        $recommendationProducts=array_merge($related,$similar_all);
        [$recommendationAttributes,$recommendationBrands]=(new CatalogListingLoader())->load($recommendationProducts);
        $recommendationCategories=[];$categoryIds=array_values(array_unique(array_map(static fn(array $row): int=>(int)$row['category_id'],$recommendationProducts)));
        if($categoryIds){foreach(\R::getAll('SELECT id,name,table_alt FROM category WHERE id IN ('.\R::genSlots($categoryIds).')',$categoryIds) as $row)$recommendationCategories[(int)$row['id']]=$row;}
        $inseo=(object)['content'=>''];
        $isCrossPage=true;
        $this->set(compact('cross','otherCrosses','canonicalPath','product','breadcrumbs','vendor','gallery','mods','review','reviewStats','productFilters','attribute_group','productAttributesByGroup','oemCrosses','analogCrosses','related','related_count','similar_all','similar_categories','similar_count','recommendationAttributes','recommendationBrands','recommendationCategories','inseo','isCrossPage'));
    }
}
