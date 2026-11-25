<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/app/services/TireSizeDirectory.php';
function directoryAssert(bool $condition,string $message): void { if (!$condition) throw new RuntimeException($message); }
$sizes=\app\services\TireSizeDirectory::prepare([
 ['value'=>'23.5R25','sef'=>'tiposize-23.5r25','total'=>2,'stocked'=>0],
 ['value'=>'18*7-8','sef'=>'tiposize-18*7-8','total'=>3,'stocked'=>2],
 ['value'=>'200/50-10','sef'=>'tiposize-200/50-10','total'=>1,'stocked'=>1],
 ['value'=>'9.00-20','sef'=>'tiposize-9.00-20','total'=>0,'stocked'=>0],
 ['value'=>'10-16.5','sef'=>'','total'=>2,'stocked'=>1],
]);
directoryAssert(count($sizes)===3,'Do not advertise empty sizes or unregistered URLs.');
$bySize=array_column($sizes,null,'size');
directoryAssert($bySize['18x7-8']['url']==='/tiposize-18%2A7-8','Never manufacture URLs from display notation.');
foreach(['18*7','18Х7','18 × 7','18x7'] as $query) directoryAssert(\app\services\TireSizeDirectory::matches($bySize['18x7-8'],$query,false),'Equivalent tire notation must match.');
directoryAssert(!\app\services\TireSizeDirectory::matches($bySize['23.5R25'],'23.5-25',false),'Radial notation must remain distinct.');
directoryAssert(!\app\services\TireSizeDirectory::matches($bySize['23.5R25'],'',true),'Stock filter must exclude sizes without stock.');
directoryAssert(\app\services\TireSizeDirectory::matches($bySize['200/50-10'],'200/50',true),'Slash sizes must remain searchable.');
directoryAssert(\app\services\TireSizeDirectory::productCount(1)==='1 товар' && \app\services\TireSizeDirectory::productCount(12)==='12 товаров','Russian item counts must be readable.');
echo "Tire size directory canonical links, notation, visibility and stock checks passed.\n";
