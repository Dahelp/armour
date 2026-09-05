<?php
declare(strict_types=1);

use app\services\CrossRedirectMapBuilder;

require dirname(__DIR__).'/vendor/autoload.php';

if($argc<4){fwrite(STDERR,"Usage: php bin/build_cross_redirect_map.php <legacy-review.csv> <sitemap.xml> <output-directory>\n");exit(2);}
$result=(new CrossRedirectMapBuilder())->build($argv[1],$argv[2]);
$directory=rtrim($argv[3],'/\\');
if(!is_dir($directory)&&!mkdir($directory,0775,true)&&!is_dir($directory))throw new RuntimeException('Unable to create output directory.');
$writeCsv=static function(string $filename,array $rows,bool $reason): void {
    $handle=fopen($filename,'wb');if($handle===false)throw new RuntimeException('Unable to write '.$filename);
    $columns=['source_path','target_path','status_code','is_active'];if($reason)$columns[]='reason';
    fputcsv($handle,$columns,';','"','\\');foreach($rows as $row)fputcsv($handle,array_map(static fn(string $column): int|string=>$row[$column]??'',$columns),';','"','\\');fclose($handle);
};
$writeCsv($directory.'/cross-redirects-ready.csv',$result['ready'],false);
$writeCsv($directory.'/cross-redirects-deferred.csv',$result['deferred'],true);
$sample=[];$ready=$result['ready'];$step=max(1,(int)floor(count($ready)/30));
for($index=0;$index<count($ready);$index+=$step)$sample[$ready[$index]['source_path']]=$ready[$index];
foreach($ready as $row){
    if(count($sample)>=50)break;
    if(preg_match('/[^a-z0-9._-]/i',(string)$row['source_path']))$sample[$row['source_path']]=$row;
}
$writeCsv($directory.'/cross-redirects-live-sample.csv',array_values($sample),false);
$result['summary']['live_sample_urls']=count($sample);
file_put_contents($directory.'/cross-redirect-summary.json',json_encode($result['summary'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL,LOCK_EX);
printf("Legacy: %d; active paths: %d; ready: %d; deferred: %d.\n",$result['summary']['unique_legacy_crossing_urls'],$result['summary']['active_cross_paths'],$result['summary']['ready_redirects'],$result['summary']['deferred_urls']);
