<?php
declare(strict_types=1);
namespace app\services;

final class LegacyContentMigrationBuilder
{
    /** @return array{ready:list<array<string,mixed>>,review:list<array<string,mixed>>} */
    public function build(string $metadataFile): array
    {
        $items=json_decode((string)file_get_contents($metadataFile),true,512,JSON_THROW_ON_ERROR);
        $ready=[];$review=[];
        foreach($items as $item){
            $source=LegacyUrlRedirector::normalisePath((string)parse_url((string)$item['source_url'],PHP_URL_PATH));
            $alias=preg_replace('/\.html$/i','',$source)??$source;
            [$html,$external,$images]=$this->sanitize((string)$item['content_html']);
            $text=trim(preg_replace('/\s+/u',' ',strip_tags($html))??'');
            $relevant=preg_match('/(шин|погруз|спецтех|диск|фильтр|колес|трактор|экскават|карьер|сельскох)/ui',(string)$item['h1'].' '.$text)===1;
            $reasons=[];
            if((int)$item['status']!==200)$reasons[]='source_not_200';
            if(mb_strlen($text)<100)$reasons[]='content_too_short';
            if(!$relevant)$reasons[]='topic_review_required';
            if($images>0)$reasons[]='images_require_migration';
            $row=[
                'source_path'=>$source,'alias'=>$alias,
                'type_id'=>str_starts_with($source,'news-')?3:2,
                'name'=>trim((string)$item['h1']),
                'title'=>mb_substr(trim((string)$item['title']),0,255),
                'description'=>mb_substr(trim((string)$item['description']),0,500),
                'content'=>$html,'date_post'=>(string)($item['date_post']??date('Y-m-d')),'hide'=>'hide','external_links_removed'=>$external,
                'review_reasons'=>implode('|',$reasons),
            ];
            if($reasons===[])$ready[]=$row;else$review[]=$row;
        }
        return ['ready'=>$ready,'review'=>$review];
    }

    public function write(array $result,string $directory):array
    {
        if(!is_dir($directory)&&!mkdir($directory,0775,true)&&!is_dir($directory))throw new \RuntimeException('Не удалось создать каталог.');
        $ready=$directory.'/legacy-content-ready.json';$review=$directory.'/legacy-content-review.json';$redirects=$directory.'/legacy-content-redirects.csv';
        file_put_contents($ready,json_encode($result['ready'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),LOCK_EX);
        file_put_contents($review,json_encode($result['review'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),LOCK_EX);
        $handle=fopen($redirects,'wb');if($handle===false)throw new \RuntimeException('Не удалось создать карту контентных URL.');
        fputcsv($handle,['source_path','target_path','status_code','is_active'],';','"','\\');
        foreach($result['ready'] as $row)fputcsv($handle,[$row['source_path'],$row['alias'],301,1],';','"','\\');fclose($handle);
        return ['ready'=>$ready,'review'=>$review,'redirects'=>$redirects];
    }

    /** @return array{string,int,int} */
    private function sanitize(string $fragment):array
    {
        $document=new \DOMDocument();$old=libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="legacy-root">'.$fragment.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD|LIBXML_NOERROR|LIBXML_NOWARNING);
        libxml_clear_errors();libxml_use_internal_errors($old);$xpath=new \DOMXPath($document);
        foreach(iterator_to_array($xpath->query('//script|//style|//iframe|//object|//embed|//form')?:[]) as $node)$node->parentNode?->removeChild($node);
        $external=0;
        foreach(iterator_to_array($xpath->query('//a[@href]')?:[]) as $link){
            $href=trim($link->getAttribute('href'));$host=strtolower((string)parse_url($href,PHP_URL_HOST));
            if($host!==''&&!in_array($host,['armour-shina.ru','www.armour-shina.ru','techtires.ru','www.techtires.ru'],true)){
                $external++;$parent=$link->parentNode;if($parent){while($link->firstChild)$parent->insertBefore($link->firstChild,$link);$parent->removeChild($link);}
            }elseif(in_array($host,['armour-shina.ru','www.armour-shina.ru'],true)){
                $path=(string)parse_url($href,PHP_URL_PATH);$query=(string)parse_url($href,PHP_URL_QUERY);
                $path=preg_replace('/\.html$/i','',$path)??$path;
                $link->setAttribute('href',$path.($query!==''?'?'.$query:''));
            }
        }
        $images=0;foreach($xpath->query('//img[@src]')?:[] as $image){if(!str_starts_with(trim($image->getAttribute('src')),'/images/contents/legacy/'))$images++;}
        $root=$document->getElementById('legacy-root');$parts=[];
        if($root)foreach($root->childNodes as $node)$parts[]=$document->saveHTML($node);
        return [trim(implode('',$parts)),$external,$images];
    }
}
