<?php if (\app\services\StorefrontSeo::text((string)($pageSeo['catalogHtml'] ?? '')) !== ''): ?>
<section class="catalog_text catalog-seo" aria-label="Информация о подборе">
    <?=$pageSeo['catalogHtml']?>
</section>
<?php endif; ?>
