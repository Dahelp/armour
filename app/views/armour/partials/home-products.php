<?php $curr = \ishop\App::$app->getProperty('currency'); ?>
<?php foreach ([
    ['hits', 'Хиты продаж', 'Товары с отметкой «Хит», доступные для заказа.', $hits],
    ['sales', 'Товары по акции', 'Позиции с отметкой «Акция», которые есть в наличии.', $sales],
    ['new', 'Новинки каталога', 'Недавно добавленные товары с ценой и остатком.', $new_products],
] as [$selectionKey, $selectionTitle, $selectionDescription, $selectionProducts]): ?>
<?php if (!$selectionProducts) continue; ?>
<section class="home-products" data-home-products="<?=h($selectionKey)?>" aria-labelledby="home-products-<?=h($selectionKey)?>">
    <div class="home-section-heading"><div><h2 id="home-products-<?=h($selectionKey)?>"><?=h($selectionTitle)?></h2><p><?=h($selectionDescription)?></p></div><span class="home-products__availability">В наличии</span></div>
    <div class="woocommerce"><?php \app\widgets\product\Product::listing($selectionProducts, $curr, $cardAttributes ?? [], $cardBrands ?? []); ?></div>
</section>
<?php endforeach; ?>
