<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/vendor/autoload.php';
foreach(['FilterUrlCanonicalizer','DocumentProductContent','ProductDocumentFacets'] as $class)require_once dirname(__DIR__).'/app/services/'.$class.'.php';
function importHerade12341(PDO $db,array $source,bool $apply): array
{
    if(($source['article']??'')!=='12341' || count($source['attributes']??[])!==11 || count($source['paragraphs']??[])!==5) throw new RuntimeException('Unexpected document source.');
    $one=static function(string $sql,array $args,string $label) use($db): array {$s=$db->prepare($sql);$s->execute($args);$r=$s->fetchAll();if(count($r)!==1)throw new RuntimeException('Missing or ambiguous '.$label);return $r[0];};
    $insert=static function(string $table,array $data) use($db): int {
        if(!in_array($table,['product','brand','attribute'],true))throw new RuntimeException('Unexpected table.');
        $values=[];
        foreach($db->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll() as $column) {
            $key=$column['Field'];if(str_contains($column['Extra'],'auto_increment')||str_contains($column['Extra'],'GENERATED'))continue;
            if(array_key_exists($key,$data))$values[$key]=$data[$key];
            elseif($column['Null']==='NO' && $column['Default']===null)$values[$key]=preg_match('/int|decimal|float|double|bit/',$column['Type'])?0:'';
        }
        $columns=implode(',',array_map(static fn(string $key):string=>'`'.$key.'`',array_keys($values)));
        $db->prepare('INSERT INTO `'.$table.'` ('.$columns.') VALUES ('.implode(',',array_fill(0,count($values),'?')).')')->execute(array_values($values));
        return (int)$db->lastInsertId();
    };
    $content=\app\services\DocumentProductContent::render($source);
    $locked=false;$named=false;$id=0;$newDefinitions=[];$newBrand=0;$stage='identity';$originalStock=null;$originalBindings=null;$facetWriter=new \app\services\ProductDocumentFacets($db);
    try {
        $named=(int)$db->query("SELECT GET_LOCK('import_herade_12341',10)")->fetchColumn()===1;if(!$named)throw new RuntimeException('Import is already running.');
        if($apply){$db->exec('CREATE TABLE IF NOT EXISTS product_stock_backup_herade_12341_20261117 LIKE product');$db->exec('LOCK TABLES product WRITE, brand WRITE, category READ, url_alias WRITE, attribute WRITE, product_attribute WRITE, attribute_product WRITE, attribute_value WRITE, attribute_group READ, attribute_category WRITE, product_stock_backup_herade_12341_20261117 WRITE');$locked=true;}
        $s=$db->prepare('SELECT id,article,alias,title,description,content,img,category_id,price,quantity,stock_status_id FROM product WHERE article=? OR alias=?');$s->execute([$source['article'],$source['alias']]);$existing=$s->fetchAll();
        $donor=$one('SELECT id,name,img,category_id FROM product WHERE article=? AND alias=?',[$source['donor_article'],$source['donor_alias']],'photo donor');
        if($donor['name']!==$source['donor_name'] || $donor['img']!==$source['image'])throw new RuntimeException('Photo donor identity changed.');
        if(!is_file(dirname(__DIR__).'/public/images/product/baseimg/'.$donor['img']))throw new RuntimeException('Donor photo file is missing.');
        $category=$one('SELECT urlid FROM url_alias WHERE sef=? AND LOWER(view)=?',[$source['category_alias'],'category'],'asphalt category URL');
        $one('SELECT id FROM category WHERE id=? AND name LIKE ?',[$category['urlid'],'%асфальто%'],'asphalt category');
        if((int)$category['urlid']!==(int)$donor['category_id'])throw new RuntimeException('Donor is not in the requested category.');
        if($existing) {
            if(count($existing)!==1 || $existing[0]['article']!==$source['article'] || $existing[0]['alias']!==$source['alias'])throw new RuntimeException('Product identity conflict.');
            $p=$existing[0];
            if($p['title']!==$source['title']||$p['description']!==$source['description']||$p['content']!==$content||$p['img']!==$donor['img']||(int)$p['category_id']!==(int)$category['urlid'])throw new RuntimeException('Article already exists with different data; no overwrite performed.');
            $s=$db->prepare('SELECT attribute_id,attribute_text FROM product_attribute WHERE product_id=?');$s->execute([$p['id']]);$saved=$s->fetchAll();
            if(count($saved)!==11)throw new RuntimeException('Existing article has incomplete characteristics.');
            if($apply && ((float)$p['price']!==(float)$source['price'] || (int)$p['quantity']!==(int)$source['quantity'] || (int)$p['stock_status_id']!==($source['quantity']>0?1:0))) {
                $stage='update requested price and quantity';
                $db->prepare('INSERT IGNORE INTO product_stock_backup_herade_12341_20261117 SELECT * FROM product WHERE id=?')->execute([$p['id']]);
                $originalStock=$p;
                $db->prepare('UPDATE product SET price=?,quantity=?,stock_status_id=? WHERE id=? AND article=?')->execute([$source['price'],$source['quantity'],$source['quantity']>0?1:0,$p['id'],'12341']);
                $verified=$one('SELECT price,quantity,stock_status_id FROM product WHERE id=? AND article=?',[$p['id'],'12341'],'updated stock');
                if((float)$verified['price']!==(float)$source['price'] || (int)$verified['quantity']!==(int)$source['quantity'] || (int)$verified['stock_status_id']!==($source['quantity']>0?1:0))throw new RuntimeException('Requested price or stock was not saved.');
            }
            if($apply){$s=$db->prepare('SELECT * FROM attribute_product WHERE product_id=?');$s->execute([$p['id']]);$originalBindings=['id'=>$p['id'],'rows'=>$s->fetchAll()];$facetWriter->bind((int)$p['id'],(int)$p['category_id'],$source['facets']);}
            return ['ok'=>true,'created'=>false,'id'=>(int)$p['id'],'alias'=>$source['alias'],'attribute_count'=>11,'image'=>$donor['img'],'price'=>$source['price'],'quantity'=>$source['quantity']];
        }
        $s=$db->prepare('SELECT id FROM brand WHERE UPPER(TRIM(name))=?');$s->execute([$source['brand']]);$brands=$s->fetchAll();if(count($brands)>1)throw new RuntimeException('Ambiguous manufacturer.');
        $one('SELECT id FROM attribute WHERE id=1 AND attribute_group_id=0',[],'main attribute group');
        if(!$apply)return ['ok'=>true,'ready'=>true,'article'=>'12341','attribute_count'=>11,'paragraph_count'=>5,'image'=>$donor['img'],'category_alias'=>$source['category_alias']];
        $stage='manufacturer';
        $brandId=$brands?(int)$brands[0]['id']:($newBrand=$insert('brand',['name'=>$source['brand'],'alias'=>'herade','title'=>'HERADE','hide'=>'show']));
        $definitions=$db->query('SELECT id,attribute_name FROM attribute WHERE attribute_group_id=1 ORDER BY id')->fetchAll();$resolved=[];
        foreach($source['attributes'] as $position=>$a) {
            $stage='attribute '.$a['name'];
            $matches=array_values(array_filter($definitions,static fn(array $d):bool=>mb_strtolower(trim($d['attribute_name']))===mb_strtolower($a['name'])));
            if($matches)$attributeId=(int)$matches[0]['id'];
            else {$attributeId=$insert('attribute',['attribute_group_id'=>1,'attribute_name'=>$a['name'],'attribute_position'=>$position+1,'attribute_hide'=>'show','hide_product'=>'show','url_params'=>'']);$newDefinitions[]=$attributeId;}
            $resolved[]=[$attributeId,(string)$a['value']];
        }
        $stage='new product';
        $id=$insert('product',['category_id'=>(int)$category['urlid'],'brand_id'=>$brandId,'article'=>$source['article'],'name'=>$source['name'],'alias'=>$source['alias'],'title'=>$source['title'],'description'=>$source['description'],'content'=>$content,'price'=>$source['price'],'quantity'=>$source['quantity'],'stock_status_id'=>$source['quantity']>0?1:0,'model'=>$source['model'],'weight'=>$source['weight'],'volume'=>$source['volume'],'img'=>$donor['img'],'unit'=>'шт','hide'=>'show','date'=>date('Y-m-d'),'data_edit_all'=>date('Y-m-d H:i:s')]);
        $stage='product attributes';$s=$db->prepare('INSERT INTO product_attribute (product_id,attribute_id,attribute_group_id,attribute_text) VALUES (?,?,1,?)');foreach($resolved as [$attributeId,$text])$s->execute([$id,$attributeId,$text]);
        $facetWriter->bind($id,(int)$category['urlid'],$source['facets']);
        $stage='canonical URL';$db->prepare('INSERT INTO url_alias (sef,view,urlid) VALUES (?,?,?)')->execute([$source['alias'],'product',$id]);
        $stage='verification';$p=$one('SELECT title,description,content,img,category_id FROM product WHERE id=?',[$id],'created product');
        if($p['title']!==$source['title']||$p['description']!==$source['description']||$p['content']!==$content||$p['img']!==$donor['img']||(int)$p['category_id']!==(int)$category['urlid'])throw new RuntimeException('Saved product does not match the document.');
        $s=$db->prepare('SELECT attribute_id,attribute_text FROM product_attribute WHERE product_id=? ORDER BY id');$s->execute([$id]);$saved=$s->fetchAll();
        if(count($saved)!==11)throw new RuntimeException('Not all source characteristics were saved.');
        foreach($saved as $index=>$row)if((int)$row['attribute_id']!==$resolved[$index][0]||$row['attribute_text']!==$resolved[$index][1])throw new RuntimeException('Characteristic verification failed.');
        return ['ok'=>true,'created'=>true,'id'=>$id,'article'=>'12341','alias'=>$source['alias'],'attribute_count'=>11,'paragraph_count'=>5,'image'=>$donor['img'],'category_alias'=>$source['category_alias']];
    } catch(Throwable $error) {
        if($originalBindings){$db->prepare('DELETE FROM attribute_product WHERE product_id=?')->execute([$originalBindings['id']]);foreach($originalBindings['rows'] as $row){$keys=implode(',',array_map(static fn($key)=>'`'.$key.'`',array_keys($row)));$db->prepare('INSERT INTO attribute_product ('.$keys.') VALUES ('.implode(',',array_fill(0,count($row),'?')).')')->execute(array_values($row));}}
        if($originalStock)$db->prepare('UPDATE product SET price=?,quantity=?,stock_status_id=? WHERE id=? AND article=?')->execute([$originalStock['price'],$originalStock['quantity'],$originalStock['stock_status_id'],$originalStock['id'],'12341']);
        if($id){foreach(['url_alias','attribute_product','product_attribute'] as $table){$key=$table==='url_alias'?'urlid':'product_id';$db->prepare('DELETE FROM '.$table.' WHERE '.$key.'=?'.($table==='url_alias'?" AND view='product'":''))->execute([$id]);}$db->prepare('DELETE FROM product WHERE id=? AND article=?')->execute([$id,'12341']);}
        $facetWriter->rollbackCreated();
        foreach($newDefinitions as $attributeId)$db->prepare('DELETE FROM attribute WHERE id=?')->execute([$attributeId]);
        if($newBrand)$db->prepare('DELETE FROM brand WHERE id=?')->execute([$newBrand]);
        if($error instanceof PDOException)throw new RuntimeException('Import failed at '.$stage.': '.($error->errorInfo[2]??$error->getCode()));throw $error;
    } finally {if($locked)$db->exec('UNLOCK TABLES');if($named)$db->query("SELECT RELEASE_LOCK('import_herade_12341')");}
}
if(!defined('HERADE_IMPORT_LIBRARY_ONLY')) {
    try {
        require_once dirname(__DIR__).'/config/environment.php';$config=require dirname(__DIR__).'/config/config_db.php';
        $db=new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        $source=$heradeSource??json_decode(file_get_contents(dirname(__DIR__).'/resources/products/herade-12341.json'),true,512,JSON_THROW_ON_ERROR);
        echo json_encode(importHerade12341($db,$source,$heradeApply??in_array('--apply',$argv??[],true)),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    }catch(Throwable $error){if(PHP_SAPI!=='cli')http_response_code(500);echo json_encode(['ok'=>false,'error'=>$error instanceof PDOException?'Database connection failed.':$error->getMessage()],JSON_UNESCAPED_UNICODE);if(PHP_SAPI==='cli')exit(1);}
}