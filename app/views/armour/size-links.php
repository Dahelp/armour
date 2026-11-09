<?php
// Use registered canonical URLs and sizes belonging to visible products only.
$sizeBindings = [];
$sizeScope = '';
if (isset($sizeProductId)) {
    $sizeScope = ' AND p.id = ?';
    $sizeBindings[] = (int)$sizeProductId;
} elseif (isset($sizeCategoryIds)) {
    $sizeScope = ' AND p.category_id IN (' . \R::genSlots($sizeCategoryIds) . ')';
    $sizeBindings = $sizeCategoryIds;
}
$sizeLinks = \R::getAll(
    "SELECT av.value, MIN(u.sef) AS sef, COUNT(DISTINCT p.id) AS total
     FROM attribute_value av
     INNER JOIN url_alias u ON u.sef = av.alias AND LOWER(u.view) = 'tiposize'
     INNER JOIN attribute_product ap ON ap.attr_id = av.id
     INNER JOIN product p ON p.id = ap.product_id
     WHERE av.attr_group_id = 2 AND p.hide = 'show' AND u.sef <> '' $sizeScope
     GROUP BY av.id, av.value ORDER BY total DESC, av.value",
    $sizeBindings
);
if ($sizeLinks): ?>
<nav class="size-navigation" aria-label="Подбор по типоразмеру" style="margin:24px 0">
    <h2><?=isset($sizeProductId) ? 'Все товары этого размера' : 'Подбор по типоразмеру'?></h2>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach (array_slice($sizeLinks, 0, 18) as $sizeLink): ?>
            <a href="/<?=h(implode('/', array_map('rawurlencode', explode('/', ltrim($sizeLink['sef'], '/')))))?>" style="display:inline-block;padding:8px 12px;border:1px solid #ddd;border-radius:4px"><?=h($sizeLink['value'])?></a>
        <?php endforeach; ?>
    </div>
    <?php if (count($sizeLinks) > 18): ?>
        <details style="margin-top:12px">
            <summary>Все типоразмеры (<?=count($sizeLinks)?>)</summary>
            <ul style="columns:3;column-width:150px;margin-top:12px">
                <?php foreach (array_slice($sizeLinks, 18) as $sizeLink): ?>
                    <li><a href="/<?=h(implode('/', array_map('rawurlencode', explode('/', ltrim($sizeLink['sef'], '/')))))?>"><?=h($sizeLink['value'])?></a></li>
                <?php endforeach; ?>
            </ul>
        </details>
    <?php endif; ?>
</nav>
<?php endif;
unset($sizeProductId, $sizeCategoryIds, $sizeScope, $sizeBindings, $sizeLinks, $sizeLink);
