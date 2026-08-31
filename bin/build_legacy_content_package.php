<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$input=$argv[1]??'';$output=$argv[2]??dirname(__DIR__).'/tmp/reports/legacy-content';
if($input===''){fwrite(STDERR,"Использование: php bin/build_legacy_content_package.php <legacy-content.json> [output-directory]\n");exit(2);}
$builder=new \app\services\LegacyContentMigrationBuilder();$result=$builder->build($input);$files=$builder->write($result,$output);
printf("Черновиков готово: %d; требуют проверки: %d.\nГотово: %s\nПроверка: %s\n",count($result['ready']),count($result['review']),$files['ready'],$files['review']);
