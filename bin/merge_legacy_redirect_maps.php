<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$args=array_slice($argv,1);$output=array_shift($args);
if(!$output||count($args)<1){fwrite(STDERR,"Использование: php bin/merge_legacy_redirect_maps.php <output.csv> <map1.csv> [map2.csv ...]\n");exit(2);}
$validator=new \app\services\LegacyUrlMapValidator();$rows=[];
foreach($args as $file){$result=$validator->validateCsv($file);if($result['errors']!==[]){fwrite(STDERR,$file.":\n- ".implode("\n- ",$result['errors'])."\n");exit(1);}$rows=array_merge($rows,$result['rows']);}
$result=$validator->validateRows($rows);if($result['errors']!==[]){fwrite(STDERR,"Объединённая карта:\n- ".implode("\n- ",$result['errors'])."\n");exit(1);}
$directory=dirname($output);if(!is_dir($directory)&&!mkdir($directory,0775,true)&&!is_dir($directory))throw new RuntimeException('Не удалось создать каталог.');
$handle=fopen($output,'wb');fputcsv($handle,['source_path','target_path','status_code','is_active'],';','"','\\');foreach($result['rows'] as $row)fputcsv($handle,array_values($row),';','"','\\');fclose($handle);echo 'Объединено редиректов: '.count($result['rows']).PHP_EOL;
