<section class="home-hero home-hero--selection" aria-labelledby="home-selection-title">
    <div class="home-hero__image" aria-hidden="true">
        <picture>
            <img width="1600" height="500" src="<?=PATH?>/images/banners/hero-industrial-tires-v2.webp" alt="" decoding="async" fetchpriority="high" />
        </picture>
    </div>
    <div class="home-hero__content col-full">
        <div class="home-hero__intro">
            <h1 class="home-hero__title" id="home-selection-title">Шины для вашей техники</h1>
            <p class="home-hero__lead">Для погрузчиков, спецтехники и квадроциклов. Найдите свой размер и сравните цены, характеристики и наличие.</p>
            <a class="home-hero__equipment-link" href="#home-equipment">Выбрать по виду техники</a>
        </div>
        <div class="home-selector">
            <h2>Подбор шин</h2>
            <form action="/search/" method="get" data-home-selection role="search" aria-label="Подбор шин">
                <label for="home-tire-query">Размер шины или артикул</label>
                <div class="home-selector__input">
                    <input id="home-tire-query" type="search" name="s" list="home-tire-sizes" placeholder="Например, 17.5-25 или 9523" autocomplete="off" maxlength="100" required aria-describedby="home-query-help home-query-error">
                    <button type="button" class="home-selector__clear" data-selection-clear hidden aria-label="Очистить размер или артикул">×</button>
                </div>
                <datalist id="home-tire-sizes">
                    <?php foreach ($homeSizes as $homeSize): ?><option value="<?=h($homeSize['size'])?>" data-url="<?=h($homeSize['url'])?>"></option><?php endforeach; ?>
                </datalist>
                <p class="home-selector__help" id="home-query-help">Введите маркировку с боковины шины или артикул товара.</p>
                <p class="home-selector__error" id="home-query-error" data-selection-error role="alert" hidden>Укажите размер шины или артикул.</p>
                <button type="submit" class="home-selector__submit">Подобрать шины</button>
            </form>
            <?php if ($homeSizes): ?>
            <nav class="home-selector__popular" aria-label="Типоразмеры из каталога">
                <span>Быстрый переход по размеру</span>
                <div><?php foreach (array_slice($homeSizes, 0, 5) as $homeSize): ?><a href="<?=h($homeSize['url'])?>"><?=h($homeSize['size'])?></a><?php endforeach; ?></div>
            </nav>
            <?php endif; ?>
            <a class="home-selector__all" href="/tiposize">Все типоразмеры</a>
        </div>
    </div>
</section>
    <div id="content" class="site-content" tabindex="-1">
        <div class="col-full">

<div class="woocommerce"></div>
	<div id="primary" class="content-area-main">
		<main id="main" class="site-main" role="main">

			
<?php require APP . '/views/armour/partials/home-selection.php'; ?>
<?php $curr = \ishop\App::$app->getProperty('currency'); ?>
<?php if($hits){ ?>
<div class="widget_text home-hit">
    <h2 class="hit-caption">Хиты продаж</h2>
    <div class="woocommerce columns-4">
        <ul class="products product-grid">
            <?php foreach($hits as $hit): ?>
				<?php new \app\widgets\product\Product($hit, $curr, $cardAttributes[(int)$hit['id']] ?? [], $cardBrands[(int)($hit['brand_id'] ?? 0)] ?? []); ?>
			<?php endforeach; ?>
        </ul>
	</div>
</div>
<?php } ?>
<?php if($sales){ ?>
<div class="home-sale position-relative">
    <div class="sale-back"></div>
    <h2 class="sale-caption">Акция</h2>
    <div class="woocommerce product-slider">
        <ul class="products product-grid">
			<?php foreach($sales as $sale): ?>
				<?php new \app\widgets\product\Product($sale, $curr, $cardAttributes[(int)$sale['id']] ?? [], $cardBrands[(int)($sale['brand_id'] ?? 0)] ?? []); ?>
			<?php endforeach; ?>
		</ul>		
    </div>
</div>
<?php } ?>
<?php if($new_products){ ?>
    <div class="widget_text home-new-block">
        <h2 class="new-caption">Новинки</h2>
        <div class="woocommerce columns-4">
            <ul class="products product-grid">
                <?php foreach($new_products as $nprod): ?>
					<?php new \app\widgets\product\Product($nprod, $curr, $cardAttributes[(int)$nprod['id']] ?? [], $cardBrands[(int)($nprod['brand_id'] ?? 0)] ?? []); ?>
				<?php endforeach; ?>
			</ul>
        </div>
    </div>
<?php } ?>
		</main><!-- #main -->
	</div><!-- #primary -->

</div><!-- .col-full -->

</div><!-- #content -->

<?php if ($news || $articles): ?>
<div class="home-news">
    <div class="col-full">
        <div class="grid">
			<?php $homeContentWidth = $news && $articles ? 'width-1-2' : 'width-1-1'; ?>
			<?php if ($news): ?>
			<div class="<?=$homeContentWidth?>">
				<div class="home-news__heading"><a href="/news">Новости</a></div>
				<?php foreach ($news as $item): ?>
					<div class="home-news__block">
						<div class="home-news__date"><?=\ishop\App::contdate((string)$item['date_post'])?></div>
						<div class="home-news__caption">
							<a href="/news/<?=rawurlencode((string)$item['alias'])?>" rel="bookmark"><?=h($item['name'])?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ($articles): ?>
			<div class="<?=$homeContentWidth?>">
				<div class="home-news__heading"><a href="/articles">Статьи</a></div>
				<?php foreach ($articles as $item): ?>
					<div class="home-news__block">
						<div class="home-news__date"><?=\ishop\App::contdate((string)$item['date_post'])?></div>
						<div class="home-news__caption">
							<a href="/articles/<?=rawurlencode((string)$item['alias'])?>" rel="bookmark"><?=h($item['name'])?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
    </div>
</div>
<?php endif; ?>
	<div class="col-full home-seo">
    
	<div class="entry-content">
			<section class="home-seo__content">
<h2>Шины для спецтехники и квадроциклов</h2>
<p><strong>Шины и комплектующие для складской, строительной и специальной техники, а также квадроциклов — с доставкой по России.</strong></p>

<p>В каталоге представлены пневматические, цельнолитые, диагональные и радиальные шины для вилочных, фронтальных и мини-погрузчиков, экскаваторов-погрузчиков, грейдеров, катков, крановой и шахтной техники. Подобрать товар можно по типоразмеру, характеристикам и модели машины.</p>

<div class="home-seo__equipment">
<h2>Шины и комплектующие для вашей техники</h2>
<ul>
    <li>цельнолитые и пневматические шины для вилочных погрузчиков;</li>
    <li>шины для мини-погрузчиков, фронтальных погрузчиков и экскаваторов;</li>
    <li>шины для дорожно-строительной, карьерной, портовой и шахтной техники;</li>
    <li>шины для квадроциклов, ATV и UTV;</li>
    <li>сборные и сварные колесные диски;</li>
    <li>камеры, ободные ленты и фильтры для погрузчиков.</li>
</ul>
</div>

<h2>Поможем подобрать подходящий типоразмер</h2>
<p>Если точный артикул неизвестен, сообщите нашим специалистам марку и модель техники, условия эксплуатации или обозначение на установленной шине. Мы проверим совместимость, предложим подходящие варианты и рассчитаем доставку. Поиск по сайту также поддерживает артикулы, аналоги и OEM-номера.</p>

<p>Работаем с организациями и частными покупателями. Заказы отправляем транспортными компаниями в Москву, Санкт-Петербург, Екатеринбург, Новосибирск, Краснодар и другие города России.</p>
			</section>
					</div><!-- .entry-content -->
</div>

