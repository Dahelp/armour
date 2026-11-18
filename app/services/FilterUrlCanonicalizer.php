<?php
declare(strict_types=1);
namespace app\services;

/** Resolve historical filter URLs only to a registered, visible filter page. */
final class FilterUrlCanonicalizer
{
    public const CONTROLLERS=['tiposize'=>'Tiposize','marka'=>'Marka','protect'=>'Protect','diameter'=>'Diameter','disk'=>'Disk'];
    private static function key(string $path): ?string
    {
        $path=rawurldecode($path);
        if(str_contains($path,'..')||str_contains($path,'\\')||preg_match('/[\x00-\x1F\x7f?#:]/u',$path))return null;
        return mb_strtolower(preg_replace('#/+#','/',trim($path,'/')),'UTF-8');
    }
    /** @param list<array{sef:string,alias:string,value:string,url_params:string}> $rows */
    public static function resolve(string $path,array $rows): ?string
    {
        $key=self::key($path);if($key===null)return null;
        $key=preg_replace('/\.html$/i','',$key);
        if(isset(self::CONTROLLERS[$key]))return ltrim($path,'/')===$key?null:$key;
        $targets=[];
        foreach($rows as $row) {
            $prefix=strtolower($row['url_params']);$target=$row['sef'];
            if(!isset(self::CONTROLLERS[$prefix])||!preg_match('#^[a-z0-9][a-z0-9*.\/-]*$#D',$target)||str_ends_with(strtolower($target),'.html')||str_contains($target,'..'))continue;
            foreach([$row['alias'],$target,$prefix.'-'.$row['value'],$prefix.'/'.$row['value'],$prefix.'/'.$row['alias']] as $variant) {
                $variant=self::key($variant);if($variant===null)continue;
                if(array_key_exists($variant,$targets)&&$targets[$variant]!==$target)$targets[$variant]=false;
                else $targets[$variant]=$target;
            }
        }
        $target=$targets[$key]??null;
        return is_string($target)&&ltrim($path,'/')!==$target?$target:null;
    }
    public static function redirectIfNeeded(string $path): void
    {
        if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true))return;
        $key=self::key($path);
        if($key===null||!preg_match('#^(tiposize|marka|protect|diameter|disk)(?:[-/.]|$)#',$key,$match))return;
        $rows=\R::getAll("SELECT ua.sef,av.alias,av.value,ag.url_params FROM attribute_value av JOIN attribute_group ag ON ag.id=av.attr_group_id JOIN url_alias ua ON ua.urlid=av.id AND LOWER(ua.view)=LOWER(ag.url_params) AND ua.sef=av.alias WHERE av.hide='show' AND ag.url_params=?",[$match[1]]);
        $target=self::resolve($path,$rows);if($target===null)return;
        $query=(string)(parse_url((string)($_SERVER['REQUEST_URI']??''),PHP_URL_QUERY)??'');
        header('Location: '.rtrim(PATH,'/').'/'.$target.($query!==''?'?'.$query:''),true,301);exit;
    }
}