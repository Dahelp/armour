<section class="home-equipment" id="home-equipment" aria-labelledby="home-equipment-title">
    <div class="home-section-heading">
        <div><h2 id="home-equipment-title">Подберите шины по технике</h2><p>Выберите категорию, затем уточните размер и характеристики.</p></div>
        <a href="/industrialnye-shiny">Все индустриальные шины <span aria-hidden="true">→</span></a>
    </div>
    <ul class="home-equipment__list">
        <?php foreach ([
            ['/catalog-vil', 'Вилочные погрузчики', 'Пневматические и цельнолитые шины'],
            ['/catalog-front', 'Фронтальные погрузчики', 'Диагональные, радиальные и цельнолитые'],
            ['/catalog-mini', 'Минипогрузчики', 'Шины для работы в ограниченном пространстве'],
            ['/shiny-dlya-ekskavatora-pogruzchika', 'Экскаваторы-погрузчики', 'Подбор передних и задних шин'],
            ['/shiny-dlya-ekskavatorov', 'Колёсные экскаваторы', 'Шины для земляных и строительных работ'],
            ['/catalog-kvadrotciklov', 'Квадроциклы, ATV и UTV', 'Шины для дорог и бездорожья'],
        ] as [$url, $label, $description]): ?>
        <li><a href="<?=h($url)?>"><strong><?=h($label)?></strong><span><?=h($description)?></span><span class="home-equipment__arrow" aria-hidden="true">→</span></a></li>
        <?php endforeach; ?>
    </ul>
    <nav class="home-equipment__more" aria-label="Другие виды техники">
        <a href="/catalog-greyder">Грейдеры</a><a href="/catalog-katok">Катки и асфальтоукладчики</a><a href="/catalog-kran">Крановая техника</a><a href="/catalog-shahta">Шахтная техника</a><a href="/shiny-dlya-karernyh-samosvalov">Карьерные самосвалы</a>
    </nav>
</section>
<aside class="home-selection-help" aria-label="Помощь в подборе">
    <p><strong>Не уверены в размере?</strong>Подготовьте маркировку шины и модель техники — поможем проверить совместимость.</p>
    <a href="tel:+79250707707">+7 (925) 070-77-07</a>
</aside>
<section class="home-parts" aria-labelledby="home-parts-title">
    <h2 id="home-parts-title">Комплектующие для вашей техники</h2>
    <div class="home-parts__links">
        <a href="/catalog-disk"><strong>Колёсные диски</strong><span>Сборные и сварные</span></a>
        <div class="home-parts__group"><strong>Камеры и ободные ленты</strong><div><a href="/kamery-dlya-spectehniki">Камеры</a> · <a href="/obodnye-lenty-flipper">Ободные ленты</a></div></div>
        <a href="/filtra"><strong>Фильтры для погрузчиков</strong><span>Воздушные, масляные, топливные и другие</span></a>
    </div>
</section>
