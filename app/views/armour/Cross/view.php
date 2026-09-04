<?php
$productPath='/'.ltrim((string)$cross['product_alias'],'/');
$crossUrl=rtrim(PATH,'/').'/'.$canonicalPath;
$productImage=!empty($cross['img'])?'/images/product/baseimg/'.rawurlencode((string)$cross['img']):'/images/'.ltrim((string)\ishop\App::$app->getProperty('og_logo'),'/');
$schema=[
    '@context'=>'https://schema.org','@type'=>'Product','name'=>(string)$cross['product_name'],
    'sku'=>(string)$cross['article'],'url'=>$crossUrl,'image'=>[rtrim(PATH,'/').$productImage],
    'description'=>trim(strip_tags((string)$cross['product_description'])),
    'brand'=>['@type'=>'Brand','name'=>(string)$cross['brand_name']],
    'isSimilarTo'=>['@type'=>'Product','name'=>(string)$cross['cross_vendor'].' '.(string)$cross['cross_name']],
    'offers'=>['@type'=>'Offer','url'=>$crossUrl,'priceCurrency'=>'RUB','price'=>number_format((float)$cross['price'],2,'.',''),'availability'=>(int)$cross['quantity']>0?'https://schema.org/InStock':'https://schema.org/OutOfStock','itemCondition'=>'https://schema.org/NewCondition'],
];
?>
<script type="application/ld+json"><?=json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR)?></script>
<div class="storefront-breadcrumb"><div class="col-full"><nav class="woocommerce-breadcrumb" aria-label="breadcrumbs"><a href="/">Главная</a> / <a href="/catalog">Каталог</a> / <?=h((string)$cross['cross_vendor'])?> <?=h((string)$cross['cross_name'])?></nav></div></div>
<div id="content" class="site-content single-product" tabindex="-1"><div class="col-full"><main class="site-main">
<article class="i-product product"><div class="grid full-box">
<div class="width-1-3 gallery-width"><figure class="woocommerce-product-gallery__wrapper"><a href="<?=h($productPath)?>"><img src="<?=h($productImage)?>" class="wp-post-image" alt="<?=h((string)$cross['product_name'])?> — аналог <?=h((string)$cross['cross_name'])?>"></a></figure></div>
<div class="width-2-3 plansh-width"><div class="summary entry-summary">
<h1 class="product_title entry-title">Аналог фильтра <?=h((string)$cross['cross_name'])?> <?=h((string)$cross['cross_vendor'])?></h1>
<p><strong>Подходящий фильтр:</strong> <a href="<?=h($productPath)?>"><?=h((string)$cross['product_name'])?></a></p>
<p><strong>Артикул EKKA:</strong> <?=h((string)$cross['article'])?></p><p><strong>Кросс-номер:</strong> <?=h((string)$cross['cross_name'])?></p>
<p><strong>Производитель:</strong> <?=h((string)$cross['cross_vendor'])?></p><p><strong>Наличие:</strong> <?=((int)$cross['quantity']>0)?'в наличии':'уточняйте у менеджера'?></p>
<p><a class="button" href="<?=h($productPath)?>">Перейти к товару и цене</a></p>
</div></div></div>
<section class="woocommerce-tabs wc-tabs-wrapper"><h2>Совместимый фильтр EKKA</h2><p><?=h((string)$cross['product_name'])?> соответствует фильтру <?=h((string)$cross['cross_vendor'])?> с номером <?=h((string)$cross['cross_name'])?>. На странице товара доступны характеристики, актуальная цена и условия доставки.</p></section>
<?php if($otherCrosses): ?><section class="woocommerce-tabs wc-tabs-wrapper"><h2>Другие номера для этого фильтра</h2><table class="shop_attributes"><thead><tr><th>Производитель</th><th>Номер</th></tr></thead><tbody><?php foreach($otherCrosses as $item): $path=\app\services\CrossUrl::canonicalPath((string)$item['cross_abbreviated_name']);if($path==='')continue;?><tr><td><?=h((string)$item['cross_vendor'])?></td><td><a href="/<?=h($path)?>"><?=h((string)$item['cross_name'])?></a></td></tr><?php endforeach;?></tbody></table></section><?php endif;?>
</article></main></div></div>
