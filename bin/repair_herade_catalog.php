<?php
require dirname(__DIR__).'/config/init.php';
foreach(['FilterUrlCanonicalizer','DocumentProductContent','ProductDocumentFacets','SitemapGenerator'] as $class)require_once ROOT.'/app/services/'.$class.'.php';
if(!class_exists('R'))class_alias(\RedBeanPHP\R::class,'R');
$config=require dirname(__DIR__).'/config/config_db.php';
$db=new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$apply=$heradeApply??false;
$runtime=[];
if(!$apply){
    $runtime=['sapi'=>PHP_SAPI,'server'=>$_SERVER['SERVER_SOFTWARE']??'','apache'=>function_exists('apache_get_version')?apache_get_version():null];
    foreach(['https://techtires.ru/','https://techtires.ru/tiposize-13/80-20'] as $probe){$curl=curl_init($probe);curl_setopt_array($curl,[CURLOPT_NOBODY=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>12]);curl_exec($curl);$runtime['self_probes'][]=['url'=>$probe,'status'=>curl_getinfo($curl,CURLINFO_HTTP_CODE),'errno'=>curl_errno($curl),'error'=>curl_error($curl)];curl_close($curl);}
}
$sources=$catalogSources??[
    json_decode(file_get_contents(ROOT.'/resources/products/herade-12341.json'),true,512,JSON_THROW_ON_ERROR),
    json_decode(file_get_contents(ROOT.'/resources/products/vulcan-10302.json'),true,512,JSON_THROW_ON_ERROR)
];
$facets=new \app\services\ProductDocumentFacets($db);$locked=false;$snapshots=[];$newRoutes=[];$renamed=[];$correctedRoutes=[];$stage='preflight';$products=[];
try {
    if($apply){
        $db->exec('CREATE TABLE IF NOT EXISTS product_backup_catalog_20261118 LIKE product');
        $db->exec('CREATE TABLE IF NOT EXISTS attribute_value_backup_catalog_20261118 LIKE attribute_value');
        $db->exec('CREATE TABLE IF NOT EXISTS url_alias_backup_catalog_20261118 LIKE url_alias');
        $db->exec('CREATE TABLE IF NOT EXISTS attribute_product_backup_catalog_20261118 LIKE attribute_product');
        $db->exec('LOCK TABLES product WRITE, product_attribute READ, attribute READ, attribute_product WRITE, attribute_value WRITE, attribute_group READ, attribute_category WRITE, url_alias WRITE, product_backup_catalog_20261118 WRITE, attribute_product_backup_catalog_20261118 WRITE, attribute_value_backup_catalog_20261118 WRITE, url_alias_backup_catalog_20261118 WRITE');$locked=true;
        $db->exec("INSERT IGNORE INTO url_alias_backup_catalog_20261118 SELECT * FROM url_alias WHERE LOWER(view) IN ('tiposize','marka','protect','diameter','disk')");
    }
    foreach($sources as $source){
        $stage='verify source '.$source['article'];
        $s=$db->prepare('SELECT * FROM product WHERE article=? AND alias=?');$s->execute([$source['article'],$source['alias']]);$rows=$s->fetchAll();if(count($rows)!==1)throw new RuntimeException('Unexpected product identity.');$product=$rows[0];$id=(int)$product['id'];
        $definitions=array_column($db->query('SELECT id,attribute_name FROM attribute')->fetchAll(),'attribute_name','id');
        $s=$db->prepare('SELECT attribute_id,attribute_text FROM product_attribute WHERE product_id=?');$s->execute([$id]);$actual=[];foreach($s->fetchAll() as $row)$actual[mb_strtolower($definitions[$row['attribute_id']])]=(string)$row['attribute_text'];
        if(count($actual)!==count($source['attributes']))throw new RuntimeException('Technical attribute count differs from the source.');
        foreach($source['attributes'] as $a)if(($actual[mb_strtolower($a['name'])]??null)!==$a['value'])throw new RuntimeException('Technical attribute differs: '.$a['name']);
        if(!$apply){$products[]=['article'=>$source['article'],'technical_attributes'=>count($actual),'required_filters'=>$source['facets']];continue;}
        $s=$db->prepare('SELECT * FROM attribute_product WHERE product_id=?');$s->execute([$id]);$oldBindings=$s->fetchAll();$snapshots[]=['product'=>$product,'bindings'=>$oldBindings];
        $s=$db->prepare('SELECT COUNT(*) FROM product_backup_catalog_20261118 WHERE id=?');$s->execute([$id]);if(!$s->fetchColumn()){$db->prepare('INSERT INTO product_backup_catalog_20261118 SELECT * FROM product WHERE id=?')->execute([$id]);$db->prepare('INSERT INTO attribute_product_backup_catalog_20261118 SELECT * FROM attribute_product WHERE product_id=?')->execute([$id]);}
        $stage='bind filters '.$source['article'];$bound=$facets->bind($id,(int)$product['category_id'],$source['facets']);
        $content=\app\services\DocumentProductContent::render($source);
        $db->prepare('UPDATE product SET name=?,title=?,description=?,content=? WHERE id=? AND article=?')->execute([$source['name'],$source['title'],$source['description'],$content,$id,$source['article']]);
        $products[]=['article'=>$source['article'],'alias'=>$source['alias'],'technical_attributes'=>count($actual),'filters'=>$bound,'list_items'=>count($source['list_items']??[])];
    }
    // Repair missing canonical registrations for visible dictionary values, without changing their aliases.
    if($apply){
        $stage='register missing public filter routes';
        $groups=array_column($db->query('SELECT id,url_params FROM attribute_group')->fetchAll(),'url_params','id');
        foreach($db->query("SELECT id,attr_group_id,value,alias FROM attribute_value WHERE hide='show'")->fetchAll() as $value){
            $prefix=$groups[$value['attr_group_id']]??'';$alias=$value['alias'];
            if(!isset(\app\services\FilterUrlCanonicalizer::CONTROLLERS[$prefix]))continue;
            if(!preg_match('#^'.preg_quote($prefix,'#').'\-[a-z0-9][a-z0-9*.\/-]*$#D',$alias)||str_ends_with($alias,'.html')){
                $oldAlias=$alias;$alias=trim(preg_replace('#[^a-z0-9*.\/-]+#u','-',mb_strtolower(preg_replace('/\.html$/i','',$alias))),'-');
                if(!preg_match('#^'.preg_quote($prefix,'#').'\-[a-z0-9][a-z0-9*.\/-]*$#D',$alias)||str_contains($alias,'..'))throw new RuntimeException('Unsafe filter alias needs review.');
                $s=$db->prepare('SELECT COUNT(*) FROM attribute_value WHERE alias=? AND id<>?');$s->execute([$alias,$value['id']]);if($s->fetchColumn())throw new RuntimeException('Normalised filter alias conflicts with another value.');
                $s=$db->prepare('SELECT id FROM url_alias WHERE sef=?');$s->execute([$oldAlias]);$oldRouteIds=$s->fetchAll(PDO::FETCH_COLUMN);
                $renamed[]=['id'=>$value['id'],'old'=>$oldAlias,'new'=>$alias,'route_ids'=>$oldRouteIds];
                $db->prepare('INSERT IGNORE INTO attribute_value_backup_catalog_20261118 SELECT * FROM attribute_value WHERE id=?')->execute([$value['id']]);
                $db->prepare('INSERT IGNORE INTO url_alias_backup_catalog_20261118 SELECT * FROM url_alias WHERE sef=?')->execute([$oldAlias]);
                $db->prepare('UPDATE attribute_value SET alias=? WHERE id=?')->execute([$alias,$value['id']]);
                $db->prepare('UPDATE url_alias SET sef=? WHERE sef=? AND urlid=? AND LOWER(view)=?')->execute([$alias,$oldAlias,$value['id'],$prefix]);
            }
            $facets->ensureRoute((int)$value['id'],$prefix,$alias);
        }
    }
} catch(Throwable $error){
    foreach(array_reverse($snapshots) as $snapshot){$p=$snapshot['product'];$db->prepare('UPDATE product SET name=?,title=?,description=?,content=? WHERE id=?')->execute([$p['name'],$p['title'],$p['description'],$p['content'],$p['id']]);$db->prepare('DELETE FROM attribute_product WHERE product_id=?')->execute([$p['id']]);foreach($snapshot['bindings'] as $row){$columns=implode(',',array_map(static fn($k)=>'`'.$k.'`',array_keys($row)));$db->prepare('INSERT INTO attribute_product ('.$columns.') VALUES ('.implode(',',array_fill(0,count($row),'?')).')')->execute(array_values($row));}}
    $facets->rollbackCreated();
    foreach(array_reverse($correctedRoutes) as $row)$db->prepare('UPDATE url_alias SET urlid=? WHERE id=?')->execute([$row['urlid'],$row['id']]);
    foreach(array_reverse($renamed) as $change){$db->prepare('UPDATE attribute_value SET alias=? WHERE id=?')->execute([$change['old'],$change['id']]);foreach($change['route_ids'] as $routeId)$db->prepare('UPDATE url_alias SET sef=? WHERE id=?')->execute([$change['old'],$routeId]);}
    foreach($newRoutes as $id)$db->prepare('DELETE FROM url_alias WHERE id=?')->execute([$id]);
    $conflict=$db->query("SELECT id,sef,view,urlid FROM url_alias WHERE sef='marka-herade'")->fetchAll();
    echo json_encode(['ok'=>false,'route_conflict'=>$conflict,'stage'=>$stage,'error'=>$error instanceof PDOException?($error->errorInfo[2]??'Database operation failed.'):$error->getMessage()],JSON_UNESCAPED_UNICODE);exit;
}finally{if($locked)$db->exec('UNLOCK TABLES');}
if($apply){if(!is_file(ROOT.'/tmp/sitemap.before-catalog-20261118.xml'))copy(WWW.'/sitemap.xml',ROOT.'/tmp/sitemap.before-catalog-20261118.xml');\R::setup($config['dsn'],$config['user'],$config['pass']);$count=(new \app\services\SitemapGenerator())->generate();}
$publicFilters=$db->query("SELECT ua.sef,av.alias,av.value,ag.url_params FROM attribute_value av JOIN attribute_group ag ON ag.id=av.attr_group_id JOIN url_alias ua ON ua.urlid=av.id AND LOWER(ua.view)=LOWER(ag.url_params) AND ua.sef=av.alias WHERE av.hide='show' AND ag.url_params IN ('tiposize','marka','protect','diameter','disk') ORDER BY ua.sef")->fetchAll();
$conflict=$db->query("SELECT id,sef,view,urlid FROM url_alias WHERE sef='marka-herade'")->fetchAll();
echo json_encode(['ok'=>true,'applied'=>$apply,'runtime'=>$runtime,'route_conflict'=>$conflict,'products'=>$products,'sitemap_urls'=>$count??null,'deduplicated_routes'=>count($facets->deletedAliases),'corrected_route_ids'=>count($correctedRoutes)+count($facets->updatedAliases),'renamed_filter_aliases'=>$renamed,'filter_pages'=>$publicFilters],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
