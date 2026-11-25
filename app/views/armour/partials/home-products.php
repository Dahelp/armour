<?php $curr = \ishop\App::$app->getProperty('currency'); ?>
<?php foreach ([
    ['hits', 'Хиты продаж', $hits],
    ['sales', 'Товары по акции', $sales],
    ['new', 'Новинки каталога', $new_products],
] as [$selectionKey, $selectionTitle, $selectionProducts]): ?>
<?php if (!$selectionProducts) continue; ?>
<section class="home-products" data-home-products="<?=h($selectionKey)?>" aria-labelledby="home-products-<?=h($selectionKey)?>">
    <div class="home-section-heading"><h2 id="home-products-<?=h($selectionKey)?>"><?=h($selectionTitle)?></h2></div>
    <div class="woocommerce"><?php \app\widgets\product\Product::listing($selectionProducts, $curr, $cardAttributes ?? [], $cardBrands ?? []); ?></div>
</section>
<?php endforeach; ?>
