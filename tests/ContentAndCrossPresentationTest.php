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

presentationAssert(str_contains($mainController, "['articles']") && str_contains($mainController, "['news']"), 'Homepage content is not loaded by public content type.');
presentationAssert(!str_contains($mainView, 'advanta-ekb.ru'), 'Homepage retains links to the obsolete template site.');
presentationAssert(!str_contains($mainView, 'home-videoobzory'), 'Undeveloped video section is still rendered.');
presentationAssert(str_contains($mainView, 'href="/articles"') && str_contains($mainView, 'href="/news"'), 'Homepage content links are not local.');
presentationAssert(str_contains($productController, 'equipment_vendor') && str_contains($productController, 'oemCrosses'), 'Product crosses are not classified.');
presentationAssert(str_contains($productView, 'id="tab-analogs"') && str_contains($productView, 'id="tab-oem"'), 'Product cross tabs are absent.');
presentationAssert(str_contains($searchController, 'plagins_cross_vendor.name'), 'Cross-number search does not include vendor and number fields.');
presentationAssert(str_contains($layout, '<svg class="custom-logo"'), 'TechTires logo is not embedded in the header.');
presentationAssert(str_contains($layout, 'techtires-logo-title'), 'Embedded TechTires logo has no accessible title.');

echo "Content and cross presentation checks passed.\n";
