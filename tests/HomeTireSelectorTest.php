<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/app/services/HomeTireSelector.php';
require dirname(__DIR__).'/vendor/ishop/core/libs/functions.php';
function homeSelectionAssert(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$homeSizes = \app\services\HomeTireSelector::choices([
    ['value'=>'18*7-8','sef'=>'tiposize-18*7-8','total'=>14],
    ['value'=>'18х7-8','sef'=>'duplicate','total'=>3],
    ['value'=>'23.5R25','sef'=>'tiposize-23.5r25','total'=>2],
    ['value'=>'200/50-10','sef'=>'tiposize-200/50-10','total'=>1],
    ['value'=>'unknown','sef'=>'','total'=>1],
    ['value'=>'17.5-25','sef'=>'tiposize-17.5-25','total'=>0],
]);
homeSelectionAssert(count($homeSizes) === 3, 'Remove duplicate notation, unregistered and empty sizes.');
homeSelectionAssert($homeSizes[0] === ['size'=>'18x7-8','url'=>'/tiposize-18%2A7-8'], 'Preserve the registered alias; never manufacture a URL from the size.');
homeSelectionAssert($homeSizes[1]['size'] === '23.5R25' && $homeSizes[2]['url'] === '/tiposize-200/50-10', 'R construction and slash sizes must remain distinct.');
define('APP',dirname(__DIR__).'/app'); define('PATH','http://127.0.0.1:8097');
\ishop\App::$app=\ishop\Registry::instance(); \ishop\App::$app->setProperty('currency',['value'=>1]);
$hits=$sales=$new_products=$news=$articles=[];
ob_start(); require APP.'/views/armour/Main/index.php'; $html=ob_get_clean();
$dom=new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html); $xp=new DOMXPath($dom);
homeSelectionAssert($xp->query('//h1')->length === 1, 'Homepage must have one primary heading.');
homeSelectionAssert($xp->query('//form[@action="/search/" and @method="get"]//input[@name="s"]')->length === 1, 'Search must work as a standard GET form without JS.');
homeSelectionAssert($xp->query('//datalist/option')->length === 3, 'Available canonical sizes must be rendered server-side.');
homeSelectionAssert($xp->query('//*[@class="home-equipment__list"]/li')->length === 6, 'Equipment choices are missing.');
homeSelectionAssert($xp->query('//ul[@class="home-equipment__list"]//img')->length === 6, 'Every equipment section needs its matching generated image.');
foreach ($xp->query('//img[starts-with(@src,"/images/home-selection/")]') as $image) {
    $path = dirname(__DIR__).'/public'.$image->getAttribute('src');
    homeSelectionAssert(is_file($path) && getimagesize($path) !== false, 'Category image is missing or invalid: '.$path);
    homeSelectionAssert($image->getAttribute('loading') === 'lazy', 'Below-fold category media must load lazily.');
}
homeSelectionAssert(!str_contains($html,'→') && !str_contains($html,'↓'), 'Decorative arrows must be removed from homepage selection.');
homeSelectionAssert(str_contains($html,'Квадроциклы, ATV и UTV'), 'ATV category must remain reachable.');
homeSelectionAssert(!str_contains($html,'home-catalog-directory') && !str_contains($html,'Все категории шин и комплектующих'), 'Removed category disclosure must not reappear.');
homeSelectionAssert(!str_contains($html,'/gruzovye-shiny'), 'Empty truck category must stay hidden.');
homeSelectionAssert($xp->query('//section[@id="home-order"]//ol/li')->length === 4, 'Ordering guidance must retain its semantic sequence.');
homeSelectionAssert($xp->query('//section[contains(@class,"home-questions")]//details/summary')->length === 6, 'Purchase questions must work without JavaScript.');
homeSelectionAssert($xp->query('//section[@data-home-products]')->length === 0, 'Empty promotional selections must not render empty sections.');
if (in_array('--preview',$argv,true)) echo $html;
else echo "Homepage canonical selection and no-JS rendering checks passed.\n";
