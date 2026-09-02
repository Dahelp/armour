<?php
declare(strict_types=1);
namespace app\services;

final class LegacyContentImageMigrator
{
    /** @param list<array<string,mixed>> $items @return array{items:list<array<string,mixed>>,downloaded:int} */
    public function migrate(array $items,string $directory,string $publicPrefix='/images/contents/legacy'):array
    {
        $downloaded=0;$cache=[];
        foreach($items as &$item){
            $fragment=(string)($item['content_html']??'');if(!str_contains($fragment,'<img'))continue;
            $document=new \DOMDocument();$old=libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8"><div id="legacy-images">'.$fragment.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD|LIBXML_NOERROR|LIBXML_NOWARNING);
            libxml_clear_errors();libxml_use_internal_errors($old);$xpath=new \DOMXPath($document);
            foreach($xpath->query('//img[@src]')?:[] as $image){
                $src=trim($image->getAttribute('src'));if(str_starts_with($src,$publicPrefix.'/'))continue;
                $host=strtolower((string)parse_url($src,PHP_URL_HOST));
                if(!in_array($host,['armour-shina.ru','www.armour-shina.ru'],true))throw new \RuntimeException('Запрещённый источник изображения: '.$host);
                if(!isset($cache[$src])){$saved=(new RemoteImageDownloader())->download($src,$directory,10*1024*1024);$cache[$src]=rtrim($publicPrefix,'/').'/'.$saved['name'];$downloaded++;}
                $image->setAttribute('src',$cache[$src]);$image->removeAttribute('srcset');$image->setAttribute('loading','lazy');$image->setAttribute('decoding','async');
            }
            $root=$document->getElementById('legacy-images');$parts=[];if($root)foreach($root->childNodes as $node)$parts[]=$document->saveHTML($node);$item['content_html']=trim(implode('',$parts));
        }
        unset($item);return ['items'=>$items,'downloaded'=>$downloaded];
    }
}
