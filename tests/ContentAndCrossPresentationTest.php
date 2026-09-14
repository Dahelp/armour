<?php
declare(strict_types=1);

function presentationAssert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root = dirname(__DIR__);
$mainController = (string)file_get_contents($root.'/app/controllers/MainController.php');
$mainView = (string)file_get_contents($root.'/app/views/armour/Main/index.php');
$productController = (string)file_get_contents($root.'/app/controllers/ProductController.php');
$productView = (string)file_get_contents($root.'/app/views/armour/Product/view.php');
$searchController = (string)file_get_contents($root.'/app/controllers/SearchController.php');
$layout = (string)file_get_contents($root.'/app/views/armour/layouts/watches.php');
$productTabsCss = (string)file_get_contents($root.'/public/css/armour/product-tabs.css');
$mainJs = (string)file_get_contents($root.'/public/js/main.js');

presentationAssert(str_contains($mainController, "'articles'") && str_contains($mainController, "'news'"), 'Homepage content is not loaded by public content type.');
presentationAssert(str_contains($mainController, "\$homepageContentDate = '2024-01-01 00:00:00'"), 'Homepage does not exclude stale legacy content.');
presentationAssert(!str_contains($mainView, 'advanta-ekb.ru'), 'Homepage retains links to the obsolete template site.');
presentationAssert(!str_contains($mainView, 'home-videoobzory'), 'Undeveloped video section is still rendered.');
presentationAssert(str_contains($mainView, 'href="/articles"') && str_contains($mainView, 'href="/news"'), 'Homepage content links are not local.');
presentationAssert(str_contains($mainView, '<?php if ($news || $articles): ?>'), 'Homepage renders an empty stale-content block.');
presentationAssert(str_contains($productController, 'equipment_vendor') && str_contains($productController, 'oemCrosses'), 'Product crosses are not classified.');
presentationAssert(str_contains($productView, '<?php if ($analogCrosses): ?>') && str_contains($productView, 'id="tab-analogs"'), 'Analog tab is not conditional.');
presentationAssert(str_contains($productView, '<?php if ($oemCrosses): ?>') && str_contains($productView, 'id="tab-oem"'), 'OEM tab is not conditional.');
presentationAssert(!str_contains($productView, 'Для этого товара аналоги пока не указаны.') && !str_contains($productView, 'Для этого товара OEM номера пока не указаны.'), 'Empty cross tabs are still rendered.');
presentationAssert(str_contains($layout, "/css/armour/product-tabs.css?v=<?=filemtime(WWW.'/css/armour/product-tabs.css')?>"), 'Product tab counter styles are not loaded with cache busting.');
presentationAssert(str_contains($productTabsCss, 'position: static;') && str_contains($productTabsCss, 'margin-left: 6px;'), 'Product tab counters can overlap their labels.');
presentationAssert(str_contains($productTabsCss, 'background-color: #eef7ff;') && str_contains($productTabsCss, 'border-radius: 10px;'), 'Product tab counters are not visually distinguished.');
presentationAssert(str_contains($productView, 'class="black modal"') && str_contains($productView, 'class="a_close_box" rel="form-reviews"'), 'Review form does not use the standard closable modal structure.');
presentationAssert(str_contains($productView, "\$consentId = 'review-privacy-accept'") && str_contains($productView, "\$consentClass = 'check-val'"), 'Review form consent is missing.');
presentationAssert(str_contains($mainJs, "document.querySelectorAll('.big_box_close')") && str_contains($mainJs, "event.key !== 'Escape'"), 'Standard modal dismissal controls are incomplete.');
presentationAssert(str_contains($searchController, 'plagins_cross_vendor.name'), 'Cross-number search does not include vendor and number fields.');
presentationAssert(str_contains($layout, '<svg class="custom-logo"'), 'TechTires logo is not embedded in the header.');
presentationAssert(str_contains($layout, 'techtires-logo-title'), 'Embedded TechTires logo has no accessible title.');
presentationAssert(str_contains($layout, 'class="footer-brand__logo"'), 'Embedded TechTires logo is missing from the footer.');

echo "Content and cross presentation checks passed.\n";
