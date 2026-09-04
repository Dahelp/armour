<?php
declare(strict_types=1);
namespace app\services;

final class CrossImportService
{
    public function importEkka(array $sourceRows): array
    {
        if ($sourceRows === []) throw new \RuntimeException('No EKKA cross numbers were supplied.');
        $rows = [];
        foreach ($sourceRows as $index => $row) {
            $item = [
                'cross_id'=>(int)($row['cross_id']??0), 'article'=>trim((string)($row['article']??'')),
                'vendor_name'=>trim((string)($row['vendor_name']??'')), 'cross_name'=>trim((string)($row['cross_name']??'')),
                'cross_abbreviated_name'=>trim((string)($row['cross_abbreviated_name']??'')),
                'tip_cross'=>(int)($row['tip_cross']??0), 'equipment_vendor'=>(int)($row['equipment_vendor']??0),
            ];
            if ($item['cross_id']<=0 || $item['article']==='' || $item['vendor_name']==='' || $item['cross_name']==='') {
                throw new \RuntimeException('Invalid cross row at position '.($index+1).'.');
            }
            $rows[]=$item;
        }
        $articles=array_values(array_unique(array_column($rows,'article')));
        $products=\R::getAll('SELECT p.id,p.article FROM product p INNER JOIN brand b ON b.id=p.brand_id WHERE UPPER(TRIM(b.name))=? AND p.article IN ('.\R::genSlots($articles).')',array_merge(['EKKA'],$articles));
        $productsByArticle=[];
        foreach($products as $product){$article=trim((string)$product['article']);if(isset($productsByArticle[$article]))throw new \RuntimeException("Duplicate EKKA article: {$article}");$productsByArticle[$article]=(int)$product['id'];}
        $missing=array_values(array_diff($articles,array_keys($productsByArticle)));
        if($missing!==[])throw new \RuntimeException('Destination is missing '.count($missing).' EKKA article(s): '.implode(', ',array_slice($missing,0,20)));

        \R::exec('CREATE TABLE IF NOT EXISTS plagins_cross_backup_pre_armour_import LIKE plagins_cross');
        $backupCount=(int)\R::getCell('SELECT COUNT(*) FROM plagins_cross_backup_pre_armour_import');
        if($backupCount===0)\R::exec('INSERT INTO plagins_cross_backup_pre_armour_import SELECT * FROM plagins_cross');
        $vendors=[];
        foreach(\R::getAll('SELECT id,name FROM plagins_cross_vendor') as $vendor)$vendors[mb_strtolower(trim((string)$vendor['name']),'UTF-8')]=(int)$vendor['id'];
        foreach(array_unique(array_column($rows,'vendor_name')) as $name){$key=mb_strtolower((string)$name,'UTF-8');if(!isset($vendors[$key])){\R::exec('INSERT INTO plagins_cross_vendor (name) VALUES (?)',[$name]);$vendors[$key]=(int)\R::getCell('SELECT LAST_INSERT_ID()');}}
        $productIds=array_values($productsByArticle);
        \R::exec('DELETE FROM plagins_cross WHERE product_id IN ('.\R::genSlots($productIds).')',$productIds);
        foreach($rows as $row){$key=mb_strtolower($row['vendor_name'],'UTF-8');\R::exec('INSERT INTO plagins_cross (cross_id,product_id,vendor_id,cross_name,cross_abbreviated_name,tip_cross,equipment_vendor) VALUES (?,?,?,?,?,?,?)',[$row['cross_id'],$productsByArticle[$row['article']],$vendors[$key],$row['cross_name'],$row['cross_abbreviated_name'],$row['tip_cross'],$row['equipment_vendor']]);}
        $imported=(int)\R::getCell('SELECT COUNT(*) FROM plagins_cross WHERE product_id IN ('.\R::genSlots($productIds).')',$productIds);
        if($imported!==count($rows))throw new \RuntimeException('Import verification failed.');
        return ['source_rows'=>count($rows),'imported_rows'=>$imported,'products'=>count($articles),'vendors'=>count(array_unique(array_column($rows,'vendor_name'))),'backup_rows'=>$backupCount];
    }
}
