<?php
$filterHeading = \app\services\FilterSeo::resolve((string)($find->name ?? ''), (string)($inseo->name ?? ''), static fn(string $text): string => \ishop\App::seoreplacefilter($text, $find->id));
if (\app\services\StorefrontSeo::text((string)$filterHeading) === '') $filterHeading = $pageSeo['heading'];
$curr = \ishop\App::$app->getProperty('currency');
?>
<div class="prdt"><div class="container">
<?=\app\models\Breadcrumbs::render([['label' => $params->title, 'url' => $params->url_params], ['label' => \app\services\TireSizeNotation::display((string)$find->value)]])?>
<main class="site-main catalog-listing">
    <h1><?=h(\app\services\TireSizeNotation::display(\app\services\StorefrontSeo::text((string)$filterHeading)))?></h1>
    <?php if (!empty($products)): ?>
        <?php \app\widgets\product\Product::listing($products, $curr, $cardAttributes ?? [], $cardBrands ?? []); ?>
        <?php if (isset($pagination) && $pagination->countPages > 1): ?><div class="product-pagination"><?=$pagination?></div><?php endif; ?>
    <?php else: ?>
        <div class="woocommerce-info"><?=h((string)($params->notproduct ?: 'В этой подборке товаров пока нет.'))?></div>
    <?php endif; ?>
    <?php require APP . '/views/armour/partials/catalog-seo.php'; ?>
</main>
</div></div>
