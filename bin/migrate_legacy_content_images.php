<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$input=$argv[1]??'';$output=$argv[2]??'';
if($input===''||$output===''){fwrite(STDERR,"Использование: php bin/migrate_legacy_content_images.php <input.json> <output.json>\n");exit(2);}
$items=json_decode((string)file_get_contents($input),true,512,JSON_THROW_ON_ERROR);
$result=(new \app\services\LegacyContentImageMigrator())->migrate($items,dirname(__DIR__).'/public/images/contents/legacy');
file_put_contents($output,json_encode($result['items'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),LOCK_EX);
printf("Скачано изображений: %d; материалов: %d.\n",$result['downloaded'],count($result['items']));
