<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
function contentMigrationAssert(bool $value,string $message):void{if(!$value)throw new RuntimeException($message);}
$file=tempnam(sys_get_temp_dir(),'legacy-content-');
file_put_contents($file,json_encode([['source_url'=>'https://armour-shina.ru/articles-1.html','status'=>200,'h1'=>'Шины для погрузчика','title'=>'Статья','description'=>'Описание','content_html'=>'<p>'.str_repeat('Полезный текст о шинах для погрузчика. ',6).'<a href="https://spam.example">источник</a></p>']],JSON_UNESCAPED_UNICODE));
$result=(new \app\services\LegacyContentMigrationBuilder())->build($file);@unlink($file);
contentMigrationAssert(count($result['ready'])===1,'Relevant article was not prepared.');
contentMigrationAssert($result['ready'][0]['alias']==='articles-1','Legacy alias was not preserved.');
contentMigrationAssert($result['ready'][0]['hide']==='hide','Imported content must default to a hidden draft.');
contentMigrationAssert(!str_contains($result['ready'][0]['content'],'spam.example'),'External link was retained.');
contentMigrationAssert(str_contains($result['ready'][0]['content'],'источник'),'External anchor text was lost.');
echo "Legacy content migration checks passed.\n";
