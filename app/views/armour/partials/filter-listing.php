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
        <?php \app\widgets\product\Product::listing($products, $curr, $cardAttributes ?? [], $cardBrands ?? [], ['hideSize' => (string)$params->url_params === 'tiposize']); ?>
        <?php if (isset($pagination) && $pagination->countPages > 1): ?><div class="product-pagination"><?=$pagination?></div><?php endif; ?>
    <?php else: ?>
        <?php if (strtolower((string)$params->url_params) === 'tiposize'): ?>
        <section class="size-directory__empty" aria-labelledby="empty-size-title"><h2 id="empty-size-title">В этом типоразмере пока нет товаров</h2><p>Сейчас в каталоге нет опубликованных товаров размера <?=h(\app\services\TireSizeNotation::display((string)$find->value))?>. Выберите другой типоразмер или уточните возможность подбора у специалиста.</p><a href="/tiposize">Все типоразмеры шин</a><a href="tel:+79250707707">Уточнить подбор: +7 (925) 070-77-07</a></section>
        <?php else: ?><div class="woocommerce-info"><?=h((string)($params->notproduct ?: 'В этой подборке товаров пока нет.'))?></div><?php endif; ?>
    <?php endif; ?>
    <?php require APP . '/views/armour/partials/catalog-seo.php'; ?>
</main>
</div></div>
