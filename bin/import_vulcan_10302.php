<?php
declare(strict_types=1);
// One-product import. Schema and reference data stay inside the hosting account.
$config = require dirname(__DIR__).'/config/config_db.php';
$db = new PDO($config['dsn'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$apply = $vulcanApply ?? in_array('--apply', $argv ?? [], true);
$alias='27-11-00-14-6pr-tl-vulcan-ekka';
$locked=false;
try {
    if (!(int)$db->query("SELECT GET_LOCK('import_vulcan_10302',10)")->fetchColumn()) throw new RuntimeException('Import is already running.');
    $locked=true;
    $find=$db->prepare('SELECT id,article,name,alias,hide,price,quantity FROM product WHERE article=?');
    $find->execute(['10302']); $existing=$find->fetchAll();
    if ($existing) {
        if ($apply && count($existing)===1 && $existing[0]['alias']===$alias) {
            $db->prepare("UPDATE product SET img=? WHERE id=? AND article='10302' AND (img='' OR img IS NULL)")->execute(['vulcan-10302-no-photo.svg',$existing[0]['id']]);
        }
        echo json_encode(['ok'=>true,'created'=>false,'products'=>$existing],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); return;
    }
    $one=static function(string $sql,array $args) use($db): array {
        $s=$db->prepare($sql); $s->execute($args); $rows=$s->fetchAll();
        if(count($rows)!==1) throw new RuntimeException('Required catalog reference is missing or ambiguous: '.json_encode($args,JSON_UNESCAPED_UNICODE));
        return $rows[0];
    };
    $category=$one('SELECT c.id FROM category c JOIN url_alias u ON u.urlid=c.id WHERE u.sef=? AND LOWER(u.view)=? AND c.name LIKE ?',['catalog-kvadrotciklov','category','%квадро%']);
    $brand=$one('SELECT id FROM brand WHERE UPPER(TRIM(name))=?',['EKKA']);
    $specs=[
        4=>['Размер шины','27×11.00-14'],5=>['Слойность (PR)','6'],
        6=>['Ширина диска (дюйм)','9.00'],7=>['Посадочный диаметр (дюйм)','14'],
        8=>['Производитель','EKKA'],9=>['Протектор','Vulcan'],10=>['Глубина протектора (мм)','35'],
        12=>['Макс. Давление (PSI)','7'],13=>['Макс. Нагрузка (кг)','185'],
        14=>['Наружный диаметр, мм','685'],15=>['Применение','Квадроцикл'],16=>['Тип','TL'],
        17=>['Ширина протектора (мм)','279'],18=>['Макс. Давление (КПа)','48'],
        19=>['Вес, кг','15'],20=>['Объём, м3','0.13']
    ];
    $normalise=static fn(string $name): string=>preg_replace('/[^a-zа-яё0-9]/u','',mb_strtolower($name));
    $synonyms=[6=>['Размер диска (RIM)','Размер диска'],7=>['Диаметр диска (дюйм)'],
        9=>['Модель протектора'],14=>['Наружный диаметр (мм)'],16=>['Тип (TL)','Тип шины'],
        18=>['Макс. Давление (KPA)'],19=>['Вес (кг)'],20=>['Объем (м3)']];
    $definitions=$db->query('SELECT id,attribute_name FROM attribute WHERE attribute_group_id=1')->fetchAll();
    $resolved=[]; $missing=[];
    foreach($specs as $preferredId=>[$name,$value]) {
        $names=array_map($normalise,array_merge([$name],$synonyms[$preferredId]??[]));
        $matches=array_values(array_filter($definitions,static fn($a)=>in_array($normalise($a['attribute_name']),$names,true)));
        $preferred=array_values(array_filter($matches,static fn($a)=>(int)$a['id']===$preferredId));
        if(count($preferred)===1) $match=$preferred[0];
        elseif(count($matches)===1) $match=$matches[0];
        else { $missing[]=$name; continue; }
        $resolved[(int)$match['id']]=[$name,$value];
    }
    // All supplied dimensions are also present in the description. Reuse only unambiguous definitions.
    $specs=$resolved;
    $filters=[];
    foreach([[1,'14 дюймов'],[2,'27x11-14'],[3,'Vulcan'],[4,'EKKA']] as [$group,$value]) {
        $lookup=$db->prepare('SELECT id FROM attribute_value WHERE attr_group_id=? AND value=?');$lookup->execute([$group,$value]);$matches=$lookup->fetchAll();
        if(count($matches)===1) $filters[]=$matches[0]['id'];
    }
    $s=$db->prepare('SELECT COUNT(*) FROM url_alias WHERE sef=?'); $s->execute([$alias]);
    if($s->fetchColumn()) throw new RuntimeException('Product URL is already in use.');
    $content='<p>EKKA VULCAN 27×11.00-14 6PR TL — бескамерная шина для квадроциклов. Грязевой направленный протектор с глубокими грунтозацепами предназначен для движения по грязи, песку и глине. Рисунок способствует самоочищению протектора на рыхлом грунте.</p><p>Слойность — 6PR. Рекомендуемая ширина диска — 9.00 дюйма. Максимальная нагрузка — 185 кг, максимальное давление — 7 PSI (48 кПа). Наружный диаметр — 685 мм, ширина протектора — 279 мм, глубина протектора — 35 мм. Вес — 15 кг, объём — 0.13 м³.</p>';
    $data=['category_id'=>(int)$category['id'],'brand_id'=>(int)$brand['id'],'article'=>'10302',
        'name'=>'27×11.00-14 6PR TL VULCAN EKKA — шина для квадроцикла','alias'=>$alias,
        'title'=>'Купить 27×11.00-14 6PR TL VULCAN EKKA — шины для квадроциклов',
        'description'=>'Бескамерная шина EKKA 27×11.00-14 6PR TL с грязевым протектором VULCAN для квадроциклов. Цена 13 700 ₽. Характеристики и условия доставки.',
        'content'=>$content,'price'=>13700,'quantity'=>0,'stock_status_id'=>0,'hide'=>'show',
        'model'=>'Vulcan','unit'=>'шт','weight'=>15,'volume'=>0.13,'img'=>'vulcan-10302-no-photo.svg',
        'date'=>date('Y-m-d'),'data_edit_all'=>date('Y-m-d H:i:s'),'data_edit_price'=>date('Y-m-d')];
    // Supply empty/zero values only for mandatory legacy columns lacking defaults.
    $fields=$db->query('SHOW COLUMNS FROM product')->fetchAll(); $insert=[];
    foreach($fields as $field) {
        $name=$field['Field'];
        if(str_contains($field['Extra'],'auto_increment') || str_contains($field['Extra'],'GENERATED')) continue;
        if(array_key_exists($name,$data)) $insert[$name]=$data[$name];
        elseif($field['Null']==='NO' && $field['Default']===null) {
            $insert[$name]=preg_match('/int|decimal|float|double|bit/',$field['Type'])?0:'';
        }
    }
    if(!$apply) { echo json_encode(['ok'=>true,'created'=>false,'ready'=>true,'article'=>'10302','alias'=>$alias]); return; }
    foreach(['product','product_attribute','attribute_product','url_alias'] as $table) {
        $engine=$one('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table]);
        if(strtoupper($engine['ENGINE'])!=='INNODB') throw new RuntimeException('Import requires transactional catalog tables.');
    }
    $db->beginTransaction();
    $columns=implode(',',array_map(static fn($key)=>'`'.$key.'`',array_keys($insert)));
    $db->prepare('INSERT INTO product ('.$columns.') VALUES ('.implode(',',array_fill(0,count($insert),'?')).')')->execute(array_values($insert));
    $id=(int)$db->lastInsertId();
    $s=$db->prepare('INSERT INTO product_attribute (product_id,attribute_id,attribute_group_id,attribute_text) VALUES (?,?,1,?)');
    foreach($specs as $attributeId=>[$name,$value]) $s->execute([$id,$attributeId,$value]);
    $s=$db->prepare('INSERT INTO attribute_product (product_id,attr_id) VALUES (?,?)');
    foreach($filters as $filter) $s->execute([$id,$filter]);
    $db->prepare('INSERT INTO url_alias (sef,view,urlid) VALUES (?,?,?)')->execute([$alias,'product',$id]);
    $db->commit();
    echo json_encode(['ok'=>true,'created'=>true,'id'=>$id,'article'=>'10302','alias'=>$alias,'price'=>13700,'quantity'=>0,'image'=>'vulcan-10302-no-photo.svg'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {
    if($db->inTransaction()) $db->rollBack();
    if(PHP_SAPI!=='cli') http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e instanceof PDOException?'Catalog database operation failed.':$e->getMessage()]);
    if(PHP_SAPI==='cli') exit(1);
} finally {
    if($locked) $db->query("SELECT RELEASE_LOCK('import_vulcan_10302')");
}