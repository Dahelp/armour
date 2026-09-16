<div class="prdt">
    <div class="container">
        <?=\app\models\Breadcrumbs::render([['label' => 'Поиск по запросу «' . $query . '»']])?>
        <section class="d-md-flex justify-content-between align-items-center mb-4 pb-2">
            <h1 class="h2 mb-3 mb-md-0 me-3">Поиск по запросу: <strong><?=h($query)?></strong></h1>
        </section>

        <?php if (!empty($products)): ?>
            <?php $curr = \ishop\App::$app->getProperty('currency'); ?>
            <div class="woocommerce columns-4 product-search-results">
                <ul class="products columns-4">
                    <?php foreach ($products as $product): ?>
                        <?php new \app\widgets\product\Product($product, $curr); ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="text-center product-results-count">
                <p>Показано: <?=count($products)?> из <?=$total?></p>
                <?php if ($pagination->countPages > 1): ?><?=$pagination?><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="woocommerce-info">По вашему запросу товары не найдены.</div>
        <?php endif; ?>
    </div>
</div>
