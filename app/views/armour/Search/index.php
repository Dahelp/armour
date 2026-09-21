<div class="prdt">
    <div class="container">
        <?=\app\models\Breadcrumbs::render([['label' => 'Поиск по запросу «' . $query . '»']])?>
        <section class="d-md-flex justify-content-between align-items-center mb-4 pb-2">
            <h1 class="h2 mb-3 mb-md-0 me-3">Поиск по запросу: <strong><?=h($query)?></strong></h1>
        </section>

        <?php if (!empty($products)): ?>
            <?php $curr = \ishop\App::$app->getProperty('currency'); ?>
            <div class="row g-0 mx-n2 product-one product-search-results">
                    <?php foreach ($products as $product): ?>
                        <div class="col-xl-3 col-lg-6 col-md-4 col-sm-6 mb-3">
                            <?php new \app\widgets\product\Product($product, $curr, 'product_tpl.php'); ?>
                        </div>
                    <?php endforeach; ?>
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
