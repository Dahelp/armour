<?php
declare(strict_types=1);
require dirname(__DIR__).'/config/init.php';

$production=in_array('--production',$argv,true);$withDb=in_array('--with-db',$argv,true);$errors=[];$warnings=[];
if(PHP_VERSION_ID<80200)$errors[]='Требуется PHP 8.2 или новее; обнаружен '.PHP_VERSION;
foreach(['curl','dom','fileinfo','gd','intl','mbstring','openssl','pdo_mysql'] as $extension)if(!extension_loaded($extension))$errors[]='Не установлено расширение PHP: '.$extension;
foreach(['DB_HOST','DB_DATABASE','DB_USERNAME','DB_PASSWORD'] as $key){$value=trim((string)config_env($key,''));if($value===''||str_contains($value,'change-me')||str_contains($value,'адрес_'))$errors[]='Не настроено окружение: '.$key;}
foreach([ROOT.'/tmp',ROOT.'/tmp/cache',WWW.'/images'] as $directory){if(!is_dir($directory))$errors[]='Отсутствует каталог: '.$directory;elseif(!is_writable($directory))$errors[]='Каталог недоступен для записи: '.$directory;}
$migrations=['20260828_001_create_legacy_url_redirect.sql','20260828_002_add_url_alias_sef_index.sql','20260828_003_add_catalog_performance_indexes.sql','20260831_004_fix_attribute_group_url_alias_ids.sql'];
foreach($migrations as $migration)if(!is_file(ROOT.'/database/migrations/'.$migration))$errors[]='Отсутствует миграция: '.$migration;
if($production){
    if(APP_ENV!=='production')$errors[]='APP_ENV должен быть production.';
    if(DEBUG)$errors[]='APP_DEBUG должен быть 0.';
    if(!str_starts_with(PATH,'https://'))$errors[]='APP_URL должен использовать HTTPS.';
}elseif(APP_ENV!=='production')$warnings[]='Проверка запущена не в production-режиме; перед выкладкой добавьте --production.';
if($withDb){try{\ishop\Db::instance();$required=['product','category','url_alias','contents','content_type'];foreach($required as $table)if(!\R::inspect($table))$errors[]='В БД отсутствует таблица: '.$table;}catch(Throwable $exception){$errors[]='Подключение к БД не установлено.';}}
foreach($warnings as $message)echo '[WARN] '.$message.PHP_EOL;foreach($errors as $message)fwrite(STDERR,'[FAIL] '.$message.PHP_EOL);
if($errors!==[])exit(1);echo '[OK] PHP '.PHP_VERSION.', окружение, каталоги и миграции проверены.'.PHP_EOL;
