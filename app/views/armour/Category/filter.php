<?php if (!empty($products)): ?>
    <?php $curr = \ishop\App::$app->getProperty('currency'); ?>
    <?php \app\widgets\product\Product::listing($products, $curr, $productAttributes ?? [], $brands ?? []); ?>
    <div class="text-center product-results-count">
        <p>Показано: <?=count($products)?> из <?=$total?></p>
        <?php if ($pagination->countPages > 1): ?><?=$pagination?><?php endif; ?>
    </div>
<?php else: ?><div class="woocommerce-info">Товары не найдены.</div><?php endif; ?>
