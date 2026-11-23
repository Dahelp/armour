<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';
use app\services\StorefrontSeo as Seo;

function seoFallbackAssert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}
$data = ['find' => ['id' => 1, 'value' => '23.5-25', 'title' => '', 'description' => '', 'content' => ''], 'params' => ['title' => 'Размер шины'], 'products' => []];
$page = Seo::prepare($data, ['controller' => 'Tiposize']);
seoFallbackAssert($page['heading'] === 'Шины 23.5-25', 'Filter values have no name column; H1 must use the size.');
$meta = Seo::completeMeta(['title' => '', 'desc' => '', 'shop_url' => 'https://techtires.ru/tiposize-23.5-25'], $page);
seoFallbackAssert(str_contains($meta['title'], '23.5-25') && str_contains($meta['desc'], '23.5-25'), 'Missing size metadata is not completed.');
seoFallbackAssert($page['catalogHtml'] === '', 'A size page must use database content or INSEO, never invented replacement text.');
seoFallbackAssert($meta['shop_url'] === 'https://techtires.ru/tiposize-23.5-25', 'Canonical URL must be preserved.');
$authored = ['title' => 'Заданный &amp; title', 'desc' => 'Полное описание из документа'];
seoFallbackAssert(Seo::completeMeta($authored, $page) === $authored, 'Authored metadata must never be overwritten or escaped twice.');
$data['find']['content'] = '<h2>Авторский текст</h2><ol><li>Первый пункт</li><li>Второй пункт</li></ol>';
seoFallbackAssert(Seo::prepare($data, ['controller' => 'Tiposize'])['catalogHtml'] === $data['find']['content'], 'Full source HTML and numbered lists must remain unchanged.');
$data['inseo'] = ['content' => '<p>Общий текст группы</p>'];
seoFallbackAssert(Seo::prepare($data, ['controller' => 'Tiposize'])['catalogHtml'] === $data['find']['content'], 'Group content is a fallback and must not append duplicate text to authored content.');
foreach (['category', 'product', 'find', 'type'] as $key) {
    $page = Seo::prepare([$key => ['name' => 'Тестовый раздел', 'description' => 'Описание раздела']], ['controller' => 'Articles']);
    seoFallbackAssert($page['title'] !== '' && $page['description'] === 'Описание раздела', 'Metadata fallback missing for entity ' . $key);
}
define('APP', dirname(__DIR__) . '/app');
define('TEMPLATE', 'armour');
define('PATH', 'https://techtires.ru');
require dirname(__DIR__) . '/vendor/ishop/core/libs/functions.php';
\ishop\App::$app = \ishop\Registry::instance();
\ishop\App::$app->setProperty('currency', ['value' => 1]);
$view = new \ishop\base\View(['controller' => 'Tiposize', 'action' => 'view', 'prefix' => ''], false, 'view', ['title' => '', 'desc' => '']);
$view->render([
    'find' => (object)['id' => 1, 'value' => '23.5-25', 'name' => '', 'content' => ''],
    'params' => (object)['title' => 'Размер шины', 'url_params' => 'tiposize', 'notproduct' => ''],
    'inseo' => (object)['name' => '', 'content' => ''],
    'products' => [],
]);
seoFallbackAssert(str_contains($view->getMeta(), '<title>Шины 23.5-25 — ТехШина</title>'), 'The real View rendering path must complete metadata before layout output.');
echo "Storefront SEO fallback checks passed.\n";
