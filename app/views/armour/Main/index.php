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
            <a class="home-hero__equipment-link" href="#home-equipment">Выбрать по виду техники <span aria-hidden="true">↓</span></a>
        </div>
        <div class="home-selector">
            <h2>Знаете размер или артикул?</h2>
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
                <button type="submit" class="home-selector__submit">Подобрать шины <span aria-hidden="true">→</span></button>
            </form>
            <?php if ($homeSizes): ?>
            <nav class="home-selector__popular" aria-label="Популярные типоразмеры">
                <span>Часто выбирают</span>
                <div><?php foreach (array_slice($homeSizes, 0, 5) as $homeSize): ?><a href="<?=h($homeSize['url'])?>"><?=h($homeSize['size'])?></a><?php endforeach; ?></div>
            </nav>
            <?php endif; ?>
            <a class="home-selector__all" href="/tiposize">Все типоразмеры <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>
    <div id="content" class="site-content" tabindex="-1">
        <div class="col-full">

<div class="woocommerce"></div>
	<div id="primary" class="content-area-main">
		<main id="main" class="site-main" role="main">

			
<?php require APP . '/views/armour/partials/home-selection.php'; ?>
<details class="home-catalog-directory">
<summary>Все категории шин и комплектующих</summary>
<div id="post-2139" class="post-2139 page type-page status-publish hentry" style="" data-featured-image="">
	<div class="col-full">
		<div class="category-box flex flex-between">
			<div class="category-box__block">
				<div class="menu">
					<div class="menu__title">
						<a href="/industrialnye-shiny" class="menu__title-link">Индустриальные шины</a>
					</div>
					<ul class="menu__list">
						<li class="menu__item">
							<a href="/catalog-vil" class="menu__link">Шины для вилочных погрузчиков</a>
							<a href="/pnevmaticheskie-shiny-dlya-vilochnyh-pogruzchikov" class="menu__sub-link">Пневматические шины</a>
							<a href="/celnolitye-shiny-dlya-vilochnyh-pogruzchikov" class="menu__sub-link">Цельнолитые шины</a>
						</li>
						<li class="menu__item">
							<a href="/catalog-mini" class="menu__link">Шины для минипогрузчиков</a>
							<a href="/pnevmaticheskie-shiny-dlya-mini-pogruzchika" class="menu__sub-link">Пневматические шины</a>
							<a href="/celnolitye-shiny-dlya-minipogruzchika" class="menu__sub-link">Цельнолитые шины</a>
						</li>
						<li class="menu__item">
							<a href="/catalog-front" class="menu__link">Шины для фронтальных погрузчиков</a>
							<a href="/diagonalnye-shiny-dlya-frontalnyh-pogruzchikov" class="menu__sub-link">Диагональные</a>
							<a href="/radialnye-shiny-dlya-frontalnyh-pogruzchikov" class="menu__sub-link">Радиальные</a>
							<a href="/celnolitye-shiny-dlya-frontalnyh-pogruzchikov" class="menu__sub-link">Цельнолитые</a>
						</li>					
						<li class="menu__item menu__item--inline">
							<a href="/shiny-dlya-ekskavatora-pogruzchika" class="menu__link">Шины для экскаваторов-погрузчиков</a>						
							<a href="/shiny-dlya-ekskavatorov" class="menu__link">Шины для экскаваторов</a>						
							<a href="/catalog-katok" class="menu__link">Шины для асфальтоукладчиков и катков</a>						
							<a href="/catalog-greyder" class="menu__link">Шины для грейдеров</a>						
							<a href="/catalog-kran" class="menu__link">Шины для крановой техники</a>						
							<a href="/catalog-shahta" class="menu__link">Шины для шахтной техники</a>						
							<a href="/shiny-dlya-karernyh-samosvalov" class="menu__link">Шины для карьерных самосвалов</a>
						</li>						
					</ul>
				</div>
			</div>
			<div class="category-box__block">
				<div class="menu">
					<div class="menu__title">
						<a href="/filtra" class="menu__title-link">Фильтры для погрузчиков</a>
					</div>
					<ul class="menu__list">
                        <li class="menu__item">
                            <a href="/filtry-vozdushnye" class="menu__link">Фильтры воздушные</a>
                        </li>
                        <li class="menu__item">
                            <a href="/filtry-maslyanye" class="menu__link">Фильтры масляные</a>
                        </li>
						<li class="menu__item">
                            <a href="/filtry-gidravlicheskie" class="menu__link">Фильтры гидравлические</a>
                        </li>
						<li class="menu__item">
                            <a href="/filtry-toplivnye" class="menu__link">Фильтры топливные</a>
                        </li>
						<li class="menu__item">
                            <a href="/filtry-kabiny" class="menu__link">Фильтры кабины</a>
                        </li>
						<li class="menu__item">
                            <a href="/filtry-ohlazhdayuschey-zhidkosti" class="menu__link">Фильтры охлаждающей жидкости</a>
                        </li>
						<li class="menu__item">
                            <a href="/filtry-sapuna" class="menu__link">Фильтры сапуна</a>
                        </li>
						<li class="menu__item">
                            <a href="/filtry-osushiteli" class="menu__link">Фильтры осушители</a>
                        </li>        
					</ul>
				</div>
			</div>
			<div class="category-box__block">
				<div class="menu">
					<div class="menu__title">
						<a href="/catalog-disk" class="menu__title-link">Диски для погрузчиков</a>
					</div>
					<ul class="menu__list">
						<li class="menu__item">
							<a href="/catalog-sbornie" class="menu__link">Сборные диски для вилочных погрузчиков</a>
                            <a href="/svarnye-diski-dlya-vilochnyh-pogruzchikov" class="menu__link">Сварные диски для вилочных погрузчиков</a>
                            <a href="/catalog-svarnie" class="menu__link">Сварные диски для минипогрузчика</a>
                            <a href="/svarnye-diski-na-pogruzchiki-new-holland" class="menu__link">Сварные диски на погрузчик NEW HOLLAND</a>
                            <a href="/svarnye-diski-na-ekskavator-pogruzchik-jcb" class="menu__link">Сварные диски на экскаватор-погрузчик JCB</a>
                            <a href="/svarnye-diski-na-ekskavator-pogruzchik-terex" class="menu__link">Сварные диски на экскаватор-погрузчик Terex</a>
                            <a href="/diski-na-frontalnyj-pogruzchik" class="menu__link">Диски на фронтальный погрузчик</a>                            
                        </li>                           
					</ul>
				</div>
			</div>
			<div class="category-box__block">
				<div class="menu">
					<div class="menu__title">
						<span class="menu__title-link">Камеры и ободные ленты</span>
					</div>
					<ul class="menu__list">
                         <li class="menu__item">
                             <a href="/kamery-dlya-spectehniki" class="menu__link">Камеры для спецшин</a>
                         </li>
                         <li class="menu__item">
                             <a href="/obodnye-lenty-flipper" class="menu__link">Ободные ленты (Флиппер)</a>
                         </li>
                    </ul>
				</div>
			</div>
			<div class="category-box__block">
				<div class="menu">
					<div class="menu__title">
						<span class="menu__title-link">Шины для квадроциклов</span>
					</div>
					<ul class="menu__list">
                         <li class="menu__item">
                             <a href="/catalog-kvadrotciklov" class="menu__link">Шины для квадроциклов</a>
                         </li>                         
                    </ul>
				</div>
			</div>
			<?php // Грузовые шины временно скрыты до добавления ассортимента. ?>
		</div>		
	</div>
</div><!-- #post-## -->
</details>
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

