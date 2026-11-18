<?php
declare(strict_types=1);
namespace app\services;

/** Resolve groups by meaning, never by IDs copied from another database. Caller controls locking/rollback. */
final class ProductDocumentFacets
{
    public array $createdValues=[];
    public array $createdAliases=[];
    public array $updatedAliases=[];
    public array $deletedAliases=[];
    public array $createdCategoryLinks=[];
    public function __construct(private \PDO $db) {}
    private function insertValue(array $data): int
    {
        $insert=[];
        foreach($this->db->query('SHOW COLUMNS FROM attribute_value')->fetchAll(\PDO::FETCH_ASSOC) as $column){$key=$column['Field'];if(str_contains($column['Extra'],'auto_increment')||str_contains($column['Extra'],'GENERATED'))continue;if(array_key_exists($key,$data))$insert[$key]=$data[$key];elseif($column['Null']==='NO'&&$column['Default']===null)$insert[$key]=preg_match('/int|decimal|float|double|bit/',$column['Type'])?0:'';}
        $columns=implode(',',array_map(static fn($key)=>'`'.$key.'`',array_keys($insert)));
        $this->db->prepare('INSERT INTO attribute_value ('.$columns.') VALUES ('.implode(',',array_fill(0,count($insert),'?')).')')->execute(array_values($insert));
        return (int)$this->db->lastInsertId();
    }
    private static function normalise(string $value): string {return str_replace(['с','х','×','*'],['c','x','x','x'],mb_strtolower(trim($value),'UTF-8'));}
    /** @return list<array{group:string,value:string,id:int,alias:string}> */
    public function bind(int $productId,int $categoryId,array $facets): array
    {
        foreach(['tiposize','marka','protect','diameter','tire_type'] as $key)if(empty($facets[$key]))throw new \RuntimeException('Missing required product facet: '.$key);
        $result=[];
        foreach($facets as $prefix=>$value) {
            if($prefix==='tire_type'){$s=$this->db->prepare("SELECT id,url_params FROM attribute_group WHERE title=? AND url_params=''");$s->execute(['Тип шин']);}
            else {if(!isset(FilterUrlCanonicalizer::CONTROLLERS[$prefix]))throw new \RuntimeException('Unknown product facet.');$s=$this->db->prepare('SELECT id,url_params FROM attribute_group WHERE url_params=?');$s->execute([$prefix]);}
            $groups=$s->fetchAll(\PDO::FETCH_ASSOC);if(count($groups)!==1)throw new \RuntimeException('Missing or ambiguous filter group: '.$prefix);$group=$groups[0];$groupId=(int)$group['id'];
            $s=$this->db->prepare('SELECT id,value,alias,hide FROM attribute_value WHERE attr_group_id=? ORDER BY id');$s->execute([$groupId]);
            $matches=array_values(array_filter($s->fetchAll(\PDO::FETCH_ASSOC),static fn($row)=>self::normalise($row['value'])===self::normalise($value)));
            if(count($matches)>1)throw new \RuntimeException('Ambiguous filter value: '.$value);
            if($matches){$av=$matches[0];if($av['hide']!=='show')throw new \RuntimeException('Required filter is hidden: '.$value);}
            else {
                if($prefix==='tire_type')throw new \RuntimeException('Missing tire type dictionary value.');
                $slug=self::normalise($value);$alias=$prefix.'-'.$slug;
                if(!preg_match('#^[a-z0-9][a-z0-9*.\/-]*$#D',$alias))throw new \RuntimeException('Invalid new facet alias.');
                $s=$this->db->prepare('SELECT COUNT(*) FROM attribute_value WHERE alias=?');$s->execute([$alias]);if($s->fetchColumn())throw new \RuntimeException('Filter alias is already occupied.');
                $id=$this->insertValue(['attr_group_id'=>$groupId,'value'=>$value,'alias'=>$alias,'hide'=>'show','title'=>$value,'description'=>'','keywords'=>'','content'=>'','img'=>'']);$this->createdValues[]=$id;$av=['id'=>$id,'value'=>$value,'alias'=>$alias];
            }
            $avId=(int)$av['id'];$alias=(string)$av['alias'];
            if($group['url_params']!=='')$this->ensureRoute($avId,$prefix,$alias);
            $s=$this->db->prepare('SELECT COUNT(*) FROM attribute_category WHERE category_id=? AND group_id=?');$s->execute([$categoryId,$groupId]);
            if(!$s->fetchColumn()){$this->db->prepare('INSERT INTO attribute_category (category_id,group_id) VALUES (?,?)')->execute([$categoryId,$groupId]);$this->createdCategoryLinks[]=[$categoryId,$groupId];}
            // Replace only this product's bindings in the explicitly supplied group.
            $s=$this->db->prepare('SELECT id FROM attribute_value WHERE attr_group_id=?');$s->execute([$groupId]);$ids=$s->fetchAll(\PDO::FETCH_COLUMN);
            if($ids)$this->db->prepare('DELETE FROM attribute_product WHERE product_id=? AND attr_id IN ('.implode(',',array_fill(0,count($ids),'?')).')')->execute([$productId,...$ids]);
            $this->db->prepare('INSERT INTO attribute_product (product_id,attr_id) VALUES (?,?)')->execute([$productId,$avId]);
            $result[]=['group'=>$prefix,'value'=>$value,'id'=>$avId,'alias'=>$group['url_params']!==''?$alias:''];
        }
        return $result;
    }
    public function ensureRoute(int $avId,string $prefix,string $alias): void
    {
        if(!isset(FilterUrlCanonicalizer::CONTROLLERS[$prefix]))throw new \RuntimeException('Unknown public filter type.');
        $s=$this->db->prepare('SELECT * FROM url_alias WHERE sef=? ORDER BY id');$s->execute([$alias]);$urls=$s->fetchAll(\PDO::FETCH_ASSOC);
        if(!$urls){$this->db->prepare('INSERT INTO url_alias (sef,view,urlid) VALUES (?,?,?)')->execute([$alias,FilterUrlCanonicalizer::CONTROLLERS[$prefix],$avId]);$this->createdAliases[]=(int)$this->db->lastInsertId();return;}
        foreach($urls as $row)if(strtolower($row['view'])!==$prefix)throw new \RuntimeException('Filter URL conflicts with a different page type: '.$alias);
        $correct=array_values(array_filter($urls,static fn($row)=>(int)$row['urlid']===$avId));$keep=$correct[0]??$urls[0];
        foreach($urls as $row)if((int)$row['id']!==(int)$keep['id']){$this->deletedAliases[]=$row;$this->db->prepare('DELETE FROM url_alias WHERE id=?')->execute([$row['id']]);}
        if((int)$keep['urlid']!==$avId){$this->updatedAliases[]=['id'=>$keep['id'],'urlid'=>$keep['urlid']];$this->db->prepare('UPDATE url_alias SET urlid=? WHERE id=?')->execute([$avId,$keep['id']]);}
    }
    public function rollbackCreated(): void
    {
        foreach(array_reverse($this->updatedAliases) as $row)$this->db->prepare('UPDATE url_alias SET urlid=? WHERE id=?')->execute([$row['urlid'],$row['id']]);
        foreach(array_reverse($this->deletedAliases) as $row){$keys=implode(',',array_map(static fn($key)=>'`'.$key.'`',array_keys($row)));$this->db->prepare('INSERT INTO url_alias ('.$keys.') VALUES ('.implode(',',array_fill(0,count($row),'?')).')')->execute(array_values($row));}
        foreach(array_reverse($this->createdAliases) as $id)$this->db->prepare('DELETE FROM url_alias WHERE id=?')->execute([$id]);
        foreach(array_reverse($this->createdValues) as $id)$this->db->prepare('DELETE FROM attribute_value WHERE id=?')->execute([$id]);
        foreach($this->createdCategoryLinks as [$category,$group])$this->db->prepare('DELETE FROM attribute_category WHERE category_id=? AND group_id=?')->execute([$category,$group]);
    }
}
