<div class="col-full size-directory">
<?=\app\models\Breadcrumbs::render([['label' => 'Типоразмеры шин']])?>
<main aria-labelledby="size-directory-title">
    <header class="size-directory__intro"><p class="size-directory__label">Подбор по маркировке</p><h1 id="size-directory-title">Типоразмеры шин</h1><p>Найдите обозначение с боковины шины. В списке — размеры, для которых в каталоге есть товары.</p></header>
    <form action="/tiposize" method="get" class="size-directory__search" data-size-directory role="search" aria-label="Поиск типоразмера">
        <div class="size-directory__field"><label for="size-query">Размер шины</label><div class="size-directory__input"><input id="size-query" type="search" name="q" value="<?=h($query)?>" placeholder="Например, 17.5-25, 18x7-8 или 23.5R25" maxlength="100" aria-describedby="size-query-help"><button type="button" class="size-directory__clear" data-size-clear hidden aria-label="Очистить размер">×</button></div></div>
        <button type="submit">Найти размер</button>
        <label class="size-directory__stock"><input type="checkbox" name="stock" value="1"<?=$stockOnly?' checked':''?>> Есть товары в наличии</label>
        <a class="size-directory__reset" href="/tiposize">Сбросить</a>
        <p id="size-query-help">Можно ввести часть размера. Обозначения x, х, × и * распознаются одинаково; букву R и дроби сохраняйте.</p>
    </form>
    <p class="size-directory__status" role="status" aria-live="polite" aria-atomic="true">Найдено размеров: <strong data-size-count><?=$matched?></strong></p>
    <ul class="size-directory__list" role="list">
    <?php foreach ($sizes as $size): ?>
        <li data-size-key="<?=h($size['key'])?>" data-size-stock="<?=$size['stocked']?>"<?=\app\services\TireSizeDirectory::matches($size,$query,$stockOnly)?'':' hidden'?>><a href="<?=h($size['url'])?>"><strong><?=h($size['size'])?></strong><span><?=h(\app\services\TireSizeDirectory::productCount($size['total']))?></span><span class="size-directory__stock-count"><?=$size['stocked']>0?'В наличии: '.\app\services\TireSizeDirectory::productCount($size['stocked']):'Нет в наличии'?></span></a></li>
    <?php endforeach; ?>
    </ul>
    <div class="size-directory__empty" data-size-empty<?=$matched>0?' hidden':''?>><h2>Подходящих размеров не найдено</h2><p>Попробуйте ввести часть маркировки или отключить условие наличия. Если размер не указан, поможем подобрать шины по модели техники.</p><a href="/tiposize">Показать все типоразмеры</a></div>
    <aside class="size-directory__help"><div><h2>Не нашли свою маркировку?</h2><p>Сообщите размер и модель техники — проверим подходящие варианты.</p></div><a href="tel:+79250707707">+7 (925) 070-77-07</a></aside>
    <?php if (trim((string)$type->seo_content)!==''): ?><section class="catalog_text catalog-seo" aria-label="О типоразмерах шин"><?=$type->seo_content?></section><?php endif; ?>
</main>
</div>
