<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
function contentRuntimeAssert(bool $value,string $message):void{if(!$value)throw new RuntimeException($message);}
foreach(['Articles','News'] as $name){
    $source=(string)file_get_contents(dirname(__DIR__).'/app/controllers/'.$name.'Controller.php');
    contentRuntimeAssert(str_contains($source,"alias = ? AND hide = ?"),$name.' exposes hidden drafts.');
    contentRuntimeAssert(str_contains($source,"hide = ? AND type_id = ? ORDER BY"),$name.' list includes hidden drafts.');
    contentRuntimeAssert(!str_contains($source, 'type_id = \'$type->id\''),$name.' contains interpolated count SQL.');
    $view=(string)file_get_contents(dirname(__DIR__).'/app/views/armour/'.$name.'/view.php');
    contentRuntimeAssert(str_contains($view,'h($find->name)'),$name.' title is not escaped.');
    contentRuntimeAssert(str_contains($view,'loading="lazy"'),$name.' image is not lazy-loaded.');
}
echo "Content runtime security checks passed.\n";
