<?php
declare(strict_types=1);

$path=(string)(parse_url((string)($_SERVER['REQUEST_URI']??'/'),PHP_URL_PATH)?:'/');
$file=dirname(__DIR__).'/public'.$path;
if($path!=='/'&&is_file($file)){return false;}
$_SERVER['QUERY_STRING']=trim(rawurldecode($path),'/');
require dirname(__DIR__).'/public/index.php';
