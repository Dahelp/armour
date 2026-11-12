<?php
declare(strict_types=1);

// Correct only the previously imported article, using the attached document.
function updateVulcanFromDocument(PDO $db, array $source, bool $apply): array
{
    if (($source['version'] ?? 0) !== 1 || ($source['product_id'] ?? 0) !== 772 || ($source['article'] ?? '') !== '10302'
        || count($source['attributes'] ?? []) !== 13 || count($source['paragraphs'] ?? []) !== 2) {
        throw new RuntimeException('Unexpected product source.');
    }
    $id=772;
    $content=implode('',array_map(static fn(string $p): string=>'<p>'.htmlspecialchars($p,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'</p>',$source['paragraphs']));
    $fields=['name'=>$source['name'],'title'=>$source['title'],'description'=>$source['description'],'content'=>$content,
        'price'=>$source['price'],'quantity'=>$source['quantity'],'stock_status_id'=>0,'weight'=>$source['weight'],'volume'=>$source['volume'],'model'=>'Vulcan'];
    $productColumns=array_flip(array_column($db->query('SHOW COLUMNS FROM product')->fetchAll(),'Field'));
    $fields=array_intersect_key($fields,$productColumns);
    $timestamp=isset($productColumns['data_edit_all'])?['data_edit_all'=>date('Y-m-d H:i:s')]:[];
    $normalise=static fn(string $text): string=>mb_strtolower(trim($text),'UTF-8');
    $expected=[];
    foreach($source['attributes'] as $a) {
        $key=$normalise($a['name']);
        if(isset($expected[$key])) throw new RuntimeException('Duplicate source attribute.');
        $expected[$key]=(string)$a['value'];
    }
    ksort($expected);
    $writeProduct=static function(array $values) use($db,$id): void {
        $set=implode(',',array_map(static fn(string $key): string=>'`'.$key.'`=?',array_keys($values)));
        $db->prepare('UPDATE product SET '.$set.' WHERE id=? AND article=?')->execute([...array_values($values),$id,'10302']);
    };
    $readProduct=static function() use($db,$id): array {
        $s=$db->prepare('SELECT * FROM product WHERE id=? AND article=?');$s->execute([$id,'10302']);
        $p=$s->fetch();
        if(!$p) throw new RuntimeException('Expected product 772 / article 10302 was not found.');
        return $p;
    };
    $readAttributes=static function() use($db,$id): array {
        $s=$db->prepare('SELECT * FROM product_attribute WHERE product_id=? ORDER BY id');$s->execute([$id]);return $s->fetchAll();
    };
    $definitions=static function() use($db): array {
        return $db->query('SELECT id,attribute_name FROM attribute WHERE attribute_group_id=1 ORDER BY id')->fetchAll();
    };
    $attributesMatch=static function(array $rows,array $defs) use($expected,$normalise): bool {
        $names=array_column($defs,'attribute_name','id');$actual=[];
        foreach($rows as $row) {
            if((int)$row['attribute_group_id']!==1 || !isset($names[$row['attribute_id']])) return false;
            $key=$normalise($names[$row['attribute_id']]);
            if(isset($actual[$key])) return false;
            $actual[$key]=(string)$row['attribute_text'];
        }
        ksort($actual);return $actual===$expected;
    };
    $fieldsMatch=static function(array $p) use($fields): bool {
        foreach($fields as $key=>$value) {
            if(in_array($key,['price','weight','volume'],true)) {if((float)$p[$key] !== (float)$value) return false;}
            elseif((string)$p[$key] !== (string)$value) return false;
        }
        return true;
    };
    $stage='identity check';$locked=false;$namedLock=false;$original=null;$originalRows=[];$created=[];$mutated=false;
    try {
        $namedLock=(int)$db->query("SELECT GET_LOCK('import_vulcan_10302',10)")->fetchColumn()===1;
        if(!$namedLock) throw new RuntimeException('Product maintenance is already running.');
        $product=$readProduct();
        if($product['alias']!==$source['alias']) throw new RuntimeException('Product URL identity changed.');
        $rows=$readAttributes();$defs=$definitions();
        if($fieldsMatch($product) && $attributesMatch($rows,$defs)) return ['ok'=>true,'changed'=>false,'id'=>$id,'article'=>'10302','attribute_count'=>13];
        if(!$apply) return ['ok'=>true,'ready'=>true,'id'=>$id,'article'=>'10302','attribute_count'=>13,'paragraph_count'=>2];
        // These tables preserve the complete original card and attribute rows once.
        $stage='create backup';
        $db->exec('CREATE TABLE IF NOT EXISTS product_backup_vulcan_10302_20261112 LIKE product');
        $db->exec('CREATE TABLE IF NOT EXISTS product_attribute_backup_vulcan_10302_20261112 LIKE product_attribute');
        // The shared attribute dictionary uses MyISAM on this host. Lock and restore explicitly.
        $stage='lock product tables';
        $db->exec('LOCK TABLES product WRITE, product_attribute WRITE, attribute WRITE, product_backup_vulcan_10302_20261112 WRITE, product_attribute_backup_vulcan_10302_20261112 WRITE');
        $locked=true;
        $stage='read locked snapshot';
        $original=$readProduct();$originalRows=$readAttributes();
        if($original['alias']!==$source['alias']) throw new RuntimeException('Product URL identity changed.');
        $s=$db->prepare('SELECT COUNT(*) FROM product_backup_vulcan_10302_20261112 WHERE id=?');$s->execute([$id]);
        if(!(int)$s->fetchColumn()) {
            $db->prepare('INSERT INTO product_backup_vulcan_10302_20261112 SELECT * FROM product WHERE id=?')->execute([$id]);
            $db->prepare('INSERT INTO product_attribute_backup_vulcan_10302_20261112 SELECT * FROM product_attribute WHERE product_id=?')->execute([$id]);
        }
        $stage='resolve attribute group';
        $group=$db->query('SELECT id FROM attribute WHERE id=1 AND attribute_group_id=0')->fetchColumn();
        if(!$group) throw new RuntimeException('Main attribute group is missing.');
        $defs=$definitions();$resolved=[];
        $stage='read attribute fields';
        $columns=$db->query('SHOW COLUMNS FROM attribute')->fetchAll();
        foreach($source['attributes'] as $position=>$a) {
            $stage='resolve attribute: '.$a['name'];
            $matches=array_values(array_filter($defs,static fn(array $d): bool=>$normalise($d['attribute_name'])===$normalise($a['name'])));
            if($matches) $attributeId=(int)$matches[0]['id'];
            else {
                $new=['attribute_group_id'=>1,'attribute_name'=>$a['name'],'attribute_position'=>$position+1,'attribute_hide'=>'show','hide_product'=>'show','url_params'=>''];
                $insert=[];
                foreach($columns as $column) {
                    $key=$column['Field'];
                    if(str_contains($column['Extra'],'auto_increment') || str_contains($column['Extra'],'GENERATED')) continue;
                    if(array_key_exists($key,$new)) $insert[$key]=$new[$key];
                    elseif($column['Null']==='NO' && $column['Default']===null) $insert[$key]=preg_match('/int|decimal|float|double|bit/',$column['Type'])?0:'';
                }
                $names=implode(',',array_map(static fn(string $key): string=>'`'.$key.'`',array_keys($insert)));
                $db->prepare('INSERT INTO attribute ('.$names.') VALUES ('.implode(',',array_fill(0,count($insert),'?')).')')->execute(array_values($insert));
                $attributeId=(int)$db->lastInsertId();$created[]=$attributeId;
            }
            $resolved[]=[$id,$attributeId,1,(string)$a['value']];
        }
        $stage='write product';
        $mutated=true;
        $writeProduct($fields+$timestamp);
        $db->prepare('DELETE FROM product_attribute WHERE product_id=?')->execute([$id]);
        $insert=$db->prepare('INSERT INTO product_attribute (product_id,attribute_id,attribute_group_id,attribute_text) VALUES (?,?,?,?)');
        $stage='write product attributes';
        foreach($resolved as $row) $insert->execute($row);
        $stage='verify product';
        if(!$fieldsMatch($readProduct()) || !$attributesMatch($readAttributes(),$definitions())) throw new RuntimeException('Saved product differs from the source document.');
        return ['ok'=>true,'changed'=>true,'id'=>$id,'article'=>'10302','attribute_count'=>13,'paragraph_count'=>2,'source_sha256'=>hash('sha256',json_encode($source,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))];
    } catch(Throwable $error) {
        if($locked && $mutated && $original) {
            $restore=array_intersect_key($original,$fields+['data_edit_all'=>null]);$writeProduct($restore);
            $db->prepare('DELETE FROM product_attribute WHERE product_id=?')->execute([$id]);
            foreach($originalRows as $row) {
                $names=implode(',',array_map(static fn(string $key): string=>'`'.$key.'`',array_keys($row)));
                $db->prepare('INSERT INTO product_attribute ('.$names.') VALUES ('.implode(',',array_fill(0,count($row),'?')).')')->execute(array_values($row));
            }
        }
        foreach($created as $attributeId) $db->prepare('DELETE FROM attribute WHERE id=?')->execute([$attributeId]);
        if($error instanceof PDOException) throw new RuntimeException('Update failed at '.$stage.': '.($error->errorInfo[2] ?? $error->getCode()));
        throw $error;
    } finally {
        if($locked) $db->exec('UNLOCK TABLES');
        if($namedLock) $db->query("SELECT RELEASE_LOCK('import_vulcan_10302')");
    }
}

if (!defined('VULCAN_UPDATE_LIBRARY_ONLY')) {
    try {
        $source=$vulcanSource ?? json_decode(file_get_contents(dirname(__DIR__).'/resources/products/vulcan-10302.json'),true,512,JSON_THROW_ON_ERROR);
        $config=require dirname(__DIR__).'/config/config_db.php';
        $db=new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        $result=updateVulcanFromDocument($db,$source,$vulcanApply ?? in_array('--apply',$argv ?? [],true));
        echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    } catch(Throwable $error) {
        if(PHP_SAPI!=='cli') http_response_code(500);
        echo json_encode(['ok'=>false,'error'=>$error instanceof PDOException?'Product database update failed.':$error->getMessage()]);
        if(PHP_SAPI==='cli') exit(1);
    }
}