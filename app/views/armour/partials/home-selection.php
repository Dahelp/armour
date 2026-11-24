<section class="home-equipment" id="home-equipment" aria-labelledby="home-equipment-title">
    <div class="home-section-heading">
        <div><h2 id="home-equipment-title">Подберите шины по технике</h2><p>Выберите категорию, затем уточните размер и характеристики.</p></div>
        <a class="home-section-heading__catalog" href="/industrialnye-shiny">Все индустриальные шины</a>
    </div>
    <ul class="home-equipment__list">
        <?php foreach ([
            ['/catalog-vil', 'Вилочные погрузчики', 'Пневматические и цельнолитые шины', 'forklift'],
            ['/catalog-front', 'Фронтальные погрузчики', 'Диагональные, радиальные и цельнолитые', 'wheel-loader'],
            ['/catalog-mini', 'Минипогрузчики', 'Шины для работы в ограниченном пространстве', 'skid-steer'],
            ['/shiny-dlya-ekskavatora-pogruzchika', 'Экскаваторы-погрузчики', 'Подбор передних и задних шин', 'backhoe-loader'],
            ['/shiny-dlya-ekskavatorov', 'Колёсные экскаваторы', 'Шины для земляных и строительных работ', 'wheeled-excavator'],
            ['/catalog-kvadrotciklov', 'Квадроциклы, ATV и UTV', 'Шины для дорог и бездорожья', 'atv'],
        ] as [$url, $label, $description, $image]): ?>
        <li><a href="<?=h($url)?>"><img class="home-equipment__image" src="/images/home-selection/<?=h($image)?>.webp" width="480" height="320" alt="" loading="lazy" decoding="async"><span class="home-equipment__copy"><strong><?=h($label)?></strong><span><?=h($description)?></span></span></a></li>
        <?php endforeach; ?>
    </ul>
    <nav class="home-equipment__more" aria-label="Другие виды техники">
        <a href="/catalog-greyder">Грейдеры</a><a href="/catalog-katok">Катки и асфальтоукладчики</a><a href="/catalog-kran">Крановая техника</a><a href="/catalog-shahta">Шахтная техника</a><a href="/shiny-dlya-karernyh-samosvalov">Карьерные самосвалы</a>
    </nav>
</section>
<aside class="home-selection-help" aria-label="Помощь в подборе">
    <div><h2>Не уверены в размере?</h2><p>Подготовьте маркировку шины и модель техники — поможем проверить совместимость.</p></div>
    <div class="home-selection-help__contact"><span>Помощь специалиста</span><a href="tel:+79250707707">+7 (925) 070-77-07</a></div>
</aside>
<section class="home-parts" aria-labelledby="home-parts-title">
    <h2 id="home-parts-title">Комплектующие для вашей техники</h2>
    <div class="home-parts__links">
        <a class="home-parts__card" href="/catalog-disk"><img src="/images/home-selection/wheel-rim.webp" width="240" height="160" alt="" loading="lazy" decoding="async"><span class="home-parts__copy"><strong>Колёсные диски</strong><span>Сборные и сварные</span></span></a>
        <div class="home-parts__card home-parts__group"><img src="/images/home-selection/tire-tube.webp" width="240" height="160" alt="" loading="lazy" decoding="async"><div class="home-parts__copy"><strong>Камеры и ободные ленты</strong><div><a href="/kamery-dlya-spectehniki">Камеры</a><a href="/obodnye-lenty-flipper">Ободные ленты</a></div></div></div>
        <a class="home-parts__card" href="/filtra"><img src="/images/home-selection/filters.webp" width="240" height="160" alt="" loading="lazy" decoding="async"><span class="home-parts__copy"><strong>Фильтры для погрузчиков</strong><span>Воздушные, масляные, топливные и другие</span></span></a>
    </div>
</section>
