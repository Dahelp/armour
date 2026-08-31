<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$file=$argv[1]??'';$apply=in_array('--apply',$argv,true);
if($file===''||!is_file($file)){fwrite(STDERR,"Использование: php bin/import_legacy_content.php <legacy-content-ready.json> [--apply]\n");exit(2);}
$rows=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
if(!is_array($rows)){fwrite(STDERR,"Некорректный пакет контента.\n");exit(1);}
$seen=[];foreach($rows as $index=>$row){$alias=\app\services\UrlAliasRepository::normaliseSef((string)($row['alias']??''));if($alias===''||isset($seen[$alias])||!in_array((int)($row['type_id']??0),[2,3],true)||trim((string)($row['name']??''))===''||trim((string)($row['content']??''))===''){fwrite(STDERR,"Некорректная строка пакета: ".($index+1)."\n");exit(1);}$seen[$alias]=true;}
printf("Проверено черновиков: %d.\n",count($rows));
if(!$apply){echo "Dry-run завершён: база данных не изменена. Для импорта добавьте --apply.\n";exit(0);}
require dirname(__DIR__).'/config/init.php';\ishop\Db::instance();$count=(new \app\services\LegacyContentRepository())->insertDrafts($rows);echo "Импортировано скрытых черновиков: {$count}.\n";
