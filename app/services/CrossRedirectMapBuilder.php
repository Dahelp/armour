<?php
declare(strict_types=1);

namespace app\services;

final class CrossRedirectMapBuilder
{
    /** @return array{ready:list<array<string,int|string>>,deferred:list<array<string,int|string>>,summary:array<string,int>} */
    public function build(string $legacyReviewCsv,string $sitemapXml): array
    {
        $active=$this->activeCrossPaths($sitemapXml);
        $handle=fopen($legacyReviewCsv,'rb');
        if($handle===false)throw new \RuntimeException('Unable to read legacy URL review.');
        $header=fgetcsv($handle,0,';','"','\\');
        if(!is_array($header))throw new \RuntimeException('Legacy URL review has no header.');
        $columns=array_flip($header);$ready=[];$deferred=[];$seen=[];$crossingRows=0;
        while(($values=fgetcsv($handle,0,';','"','\\'))!==false){
            $classification=(string)($values[$columns['classification']??-1]??'');
            if($classification!=='deferred_crossing')continue;
            ++$crossingRows;
            $sourcePath=trim((string)($values[$columns['source_path']??-1]??''),'/');
            if($sourcePath===''||isset($seen[$sourcePath]))continue;
            $seen[$sourcePath]=true;
            $alias=CrossUrl::legacyAlias($sourcePath);
            $target=$alias===''?'':CrossUrl::canonicalPath($alias);
            $row=['source_path'=>$sourcePath,'target_path'=>$target,'status_code'=>301,'is_active'=>0];
            $reason=match(true){
                $alias==='' => 'invalid_legacy_cross_alias',
                preg_match('/\s/u',$sourcePath)===1 => 'legacy_path_requires_web_server_rule',
                !CrossUrl::isRoutableAlias($alias) => 'unsupported_cross_route_alias',
                !isset($active[$target]) => 'cross_not_present_on_techtires',
                default => '',
            };
            if($reason===''){$row['is_active']=1;$ready[]=$row;}
            else{$row['reason']=$reason;$deferred[]=$row;}
        }
        fclose($handle);
        $readyTargets=array_unique(array_column($ready,'target_path'));
        return ['ready'=>$ready,'deferred'=>$deferred,'summary'=>[
            'legacy_crossing_rows'=>$crossingRows,'unique_legacy_crossing_urls'=>count($seen),
            'active_cross_paths'=>count($active),'ready_redirects'=>count($ready),'deferred_urls'=>count($deferred),
            'unique_ready_targets'=>count($readyTargets),'duplicate_target_redirects'=>count($ready)-count($readyTargets),
        ]];
    }

    /** @return array<string,true> */
    private function activeCrossPaths(string $filename): array
    {
        $document=new \DOMDocument();
        if(!$document->load($filename,LIBXML_NONET))throw new \RuntimeException('Unable to read sitemap XML.');
        $xpath=new \DOMXPath($document);$xpath->registerNamespace('s','http://www.sitemaps.org/schemas/sitemap/0.9');$paths=[];
        foreach($xpath->query('//s:url/s:loc')?:[] as $node){
            $path=trim(rawurldecode((string)parse_url(trim($node->textContent),PHP_URL_PATH)),'/');
            if(str_starts_with($path,'cross/')){$canonical=CrossUrl::canonicalPath(substr($path,6));if($canonical!=='')$paths[$canonical]=true;}
        }
        return $paths;
    }
}
