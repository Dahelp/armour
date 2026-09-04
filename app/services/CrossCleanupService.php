<?php
declare(strict_types=1);

namespace app\services;

final class CrossCleanupService
{
    public function removeEkkaCrosses(array $crossIds): array
    {
        $ids=array_values(array_unique(array_filter(array_map('intval',$crossIds),static fn(int $id): bool=>$id>0)));
        if($ids===[])throw new \RuntimeException('No valid cross IDs supplied.');

        $params=array_merge(['EKKA'],$ids);
        $where='UPPER(TRIM(b.name))=? AND c.cross_id IN ('.\R::genSlots($ids).')';
        $found=array_map('intval',array_column(\R::getAll(
            'SELECT c.cross_id FROM plagins_cross c INNER JOIN product p ON p.id=c.product_id INNER JOIN brand b ON b.id=p.brand_id WHERE '.$where,
            $params
        ),'cross_id'));
        $missing=array_values(array_diff($ids,$found));

        \R::exec('CREATE TABLE IF NOT EXISTS plagins_cross_backup_pre_excel_cleanup LIKE plagins_cross');
        \R::begin();
        try {
            if($found!==[]){
                $foundParams=array_merge(['EKKA'],$found);
                $foundWhere='UPPER(TRIM(b.name))=? AND c.cross_id IN ('.\R::genSlots($found).')';
                \R::exec(
                    'INSERT IGNORE INTO plagins_cross_backup_pre_excel_cleanup SELECT c.* FROM plagins_cross c INNER JOIN product p ON p.id=c.product_id INNER JOIN brand b ON b.id=p.brand_id WHERE '.$foundWhere,
                    $foundParams
                );
                $deleted=\R::exec(
                    'DELETE c FROM plagins_cross c INNER JOIN product p ON p.id=c.product_id INNER JOIN brand b ON b.id=p.brand_id WHERE '.$foundWhere,
                    $foundParams
                );
                $remaining=(int)\R::getCell(
                    'SELECT COUNT(*) FROM plagins_cross c INNER JOIN product p ON p.id=c.product_id INNER JOIN brand b ON b.id=p.brand_id WHERE '.$foundWhere,
                    $foundParams
                );
                if($remaining!==0||$deleted!==count($found))throw new \RuntimeException('Cross cleanup verification failed.');
            }else{$deleted=0;}
            \R::commit();
        }catch(\Throwable $e){\R::rollback();throw $e;}

        return [
            'requested'=>count($ids),
            'matched'=>count($found),
            'deleted'=>$deleted,
            'missing_cross_ids'=>$missing,
            'backup_rows'=>(int)\R::getCell('SELECT COUNT(*) FROM plagins_cross_backup_pre_excel_cleanup'),
        ];
    }
}
