<?php

declare(strict_types=1);

define('WWW', dirname(__DIR__) . '/public');
define('PATH', 'https://techtires.ru');
define('LAYOUT', 'watches');

require dirname(__DIR__) . '/config/environment.php';
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/ishop/core/libs/functions.php';

function seoAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$robots = file_get_contents(WWW . '/robots.txt');
seoAssert($robots !== false, 'robots.txt is missing.');
seoAssert(!preg_match('/^Disallow:\s*\/$/m', $robots), 'robots.txt must not block the whole site.');
seoAssert(str_contains($robots, 'Disallow: /admin/'), 'The admin area must be blocked.');
seoAssert(str_contains($robots, 'Sitemap: https://techtires.ru/sitemap.xml'), 'The sitemap declaration is missing.');

$publishedSitemap = file_get_contents(WWW . '/sitemap.xml');
seoAssert($publishedSitemap !== false, 'A deployable sitemap.xml is missing.');
$publishedDocument = new DOMDocument();
seoAssert($publishedDocument->loadXML($publishedSitemap), 'The published sitemap.xml must be valid.');
seoAssert(!str_contains($publishedSitemap, '/product/'), 'Technical product URLs must not appear in sitemap.xml.');
seoAssert(!str_contains($publishedSitemap, '/category/'), 'Technical category URLs must not appear in sitemap.xml.');

$generator = new \app\services\SitemapGenerator();
seoAssert($generator->normalisePublicPath('/product.html/') === 'product.html', 'Legacy extensions must be retained.');
seoAssert($generator->normalisePublicPath('../admin') === null, 'Traversal paths must be rejected.');
seoAssert($generator->normalisePublicPath('товар') === null, 'Unencoded non-ASCII paths must be rejected.');

$xml = $generator->buildXml([
    'https://techtires.ru/',
    'https://techtires.ru/product?a=1&b=2',
]);
$document = new DOMDocument();
seoAssert($document->loadXML($xml), 'Generated sitemap XML must be valid.');
seoAssert(str_contains($xml, 'a=1&amp;b=2'), 'Sitemap URLs must be XML-escaped.');

$view = new \ishop\base\View(
    ['controller' => 'Product', 'prefix' => ''],
    false,
    'view',
    [
        'title' => 'Шина',
        'desc' => 'Описание',
        'keywords' => '',
        'shop_name' => 'TechTires',
        'shop_img' => 'https://techtires.ru/image.webp',
        'shop_url' => 'https://techtires.ru/product-slug',
    ]
);
$meta = $view->getMeta();
seoAssert(substr_count($meta, 'rel="canonical"') === 1, 'Exactly one canonical link is required.');
seoAssert(str_contains($meta, 'https://techtires.ru/product-slug'), 'Canonical URL is incorrect.');

$_SERVER['REQUEST_URI'] = '/catalog?page=2';
$controllerReflection = new ReflectionClass(\app\controllers\CatalogController::class);
$controller = $controllerReflection->newInstanceWithoutConstructor();
$baseConstructor = new ReflectionMethod(\ishop\base\Controller::class, '__construct');
$baseConstructor->invoke($controller, ['controller' => 'Catalog', 'action' => 'index', 'prefix' => '']);
$controller->setMeta('Каталог');
seoAssert($controller->meta['shop_url'] === 'https://techtires.ru/catalog', 'Fallback canonical must exclude query parameters.');
seoAssert(str_starts_with($controller->meta['robots'], 'index, follow'), 'Catalog pages must be indexable.');

$searchController = (new ReflectionClass(\app\controllers\SearchController::class))->newInstanceWithoutConstructor();
$baseConstructor->invoke($searchController, ['controller' => 'Search', 'action' => 'index', 'prefix' => '']);
$searchController->setMeta('Поиск');
seoAssert($searchController->meta['robots'] === 'noindex, nofollow', 'Search pages must not be indexed.');
seoAssert(($searchController->meta['shop_url'] ?? '') === '', 'Noindex search pages must not get a fallback canonical.');

echo "SEO infrastructure checks passed.\n";
