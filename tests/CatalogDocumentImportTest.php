<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/config/environment.php';
// Local authoritative Composer maps may predate the newly added classes.
foreach(['FilterUrlCanonicalizer','DocumentProductContent','ProductDocumentFacets','SitemapGenerator'] as $class)require_once dirname(__DIR__).'/app/services/'.$class.'.php';
use app\services\FilterUrlCanonicalizer as Canonical;
use app\services\DocumentProductContent as Content;
use app\services\ProductDocumentFacets as Facets;
function checkCatalog(bool $condition,string $message): void {if(!$condition)throw new RuntimeException($message);}
$source=json_decode(file_get_contents(dirname(__DIR__).'/resources/products/herade-12341.json'),true,512,JSON_THROW_ON_ERROR);
$html=Content::render($source);$dom=new DOMDocument();$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xp=new DOMXPath($dom);
checkCatalog($xp->query('//ul/li')->length===2,'Word bullets must remain two list items.');
foreach([2,3] as $index)checkCatalog($xp->query('//ul/li')->item($index-2)->textContent===$source['paragraphs'][$index],'List text changed.');
checkCatalog(substr_count($html,'<p>')===3,'Only the two list paragraphs may be converted.');
checkCatalog(!str_contains(Content::render(['paragraphs'=>['<script>bad</script>']]),'<script>'),'Document text was not escaped.');
$rows=[['sef'=>'tiposize-11.00-20','alias'=>'tiposize-11.00-20','value'=>'11.00-20','url_params'=>'tiposize'],['sef'=>'marka-ekka','alias'=>'marka-ekka','value'=>'EKKA','url_params'=>'marka'],['sef'=>'protect-protect-l2-g2-147','alias'=>'protect-protect-l2-g2-147','value'=>'L2/G2','url_params'=>'protect'],['sef'=>'tiposize-13/80-20','alias'=>'tiposize-13/80-20','value'=>'13/80-20','url_params'=>'tiposize'],['sef'=>'diameter-16-5','alias'=>'diameter-16-5','value'=>'16.5','url_params'=>'diameter']];
foreach(['tiposize-11.00-20.html','TIPOSIZE-11.00-20.HTML','/tiposize/11.00-20','tiposize/tiposize-11.00-20/','tiposize-11.00-20.html/'] as $path)checkCatalog(Canonical::resolve($path,$rows)==='tiposize-11.00-20','Incorrect size redirect: '.$path);
foreach(['marka-EKKA.html','marka/EKKA','/MARKA-EKKA/'] as $path)checkCatalog(Canonical::resolve($path,$rows)==='marka-ekka','Incorrect brand redirect.');
checkCatalog(Canonical::resolve('protect-L2%2FG2.html',$rows)==='protect-protect-l2-g2-147','Renamed tread alias must resolve in one hop.');
checkCatalog(Canonical::resolve('tiposize-13%2F80-20.HTML',$rows)==='tiposize-13/80-20','Encoded slash variant was lost.');
checkCatalog(Canonical::resolve('diameter/16.5',$rows)==='diameter-16-5','Diameter display value must not become a URL.');
foreach(['marka-ekka','tiposize-unknown.html','cart.html','tiposize-../admin.html','tiposize-%2e%2e/admin.html','marka-%0d%0aLocation:evil.html'] as $path)checkCatalog(Canonical::resolve($path,$rows)===null,'Unsafe, unknown or already canonical path was redirected: '.$path);
checkCatalog(Canonical::resolve('MARKA.html/',$rows)==='marka','Filter root variant was not normalised.');

// SQLite fixture with intentionally different group IDs catches the original migration mistake.
class CatalogFixturePdo extends PDO {
    public function query(string $query,?int $fetchMode=null,mixed ...$fetchModeArgs): PDOStatement|false {
        if($query==='SHOW COLUMNS FROM attribute_value')$query="SELECT name AS Field,CASE WHEN `notnull`=1 THEN 'NO' ELSE 'YES' END AS `Null`,dflt_value AS `Default`,CASE WHEN pk=1 THEN 'auto_increment' ELSE '' END AS Extra FROM pragma_table_info('attribute_value')";
        return $fetchMode===null?parent::query($query):parent::query($query,$fetchMode,...$fetchModeArgs);
    }
}
$db=new CatalogFixturePdo('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$db->exec('CREATE TABLE attribute_group (id INTEGER PRIMARY KEY,title TEXT,url_params TEXT)');
$db->exec('CREATE TABLE attribute_value (id INTEGER PRIMARY KEY AUTOINCREMENT,attr_group_id INTEGER NOT NULL,value TEXT NOT NULL,alias TEXT NOT NULL,hide TEXT NOT NULL,title TEXT NOT NULL,description TEXT NOT NULL,keywords TEXT NOT NULL,content TEXT NOT NULL,img TEXT NOT NULL)');
$db->exec('CREATE TABLE attribute_product (id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER,attr_id INTEGER)');
$db->exec('CREATE TABLE attribute_category (id INTEGER PRIMARY KEY AUTOINCREMENT,category_id INTEGER,group_id INTEGER)');
$db->exec('CREATE TABLE url_alias (id INTEGER PRIMARY KEY AUTOINCREMENT,sef TEXT,view TEXT,urlid INTEGER)');
$db->exec("INSERT INTO attribute_group VALUES (9,'Размер шины','tiposize'),(47,'Производитель','marka'),(3,'Протектор','protect'),(108,'Посадочный диаметр','diameter'),(65,'Тип шин','')");
$db->exec("INSERT INTO attribute_value VALUES (1,65,'Пневматические','pnevmaticheskie','show','','','','',''),(2,47,'HERADE','marka-herade','show','','','','','')");
$writer=new Facets($db);$bound=$writer->bind(773,8,$source['facets']);
checkCatalog(count($bound)===5,'All five required filter groups must be filled.');
$linked=$db->query('SELECT av.value,ag.url_params FROM attribute_product ap JOIN attribute_value av ON av.id=ap.attr_id JOIN attribute_group ag ON ag.id=av.attr_group_id WHERE product_id=773')->fetchAll();
$byGroup=array_column($linked,'value','url_params');
checkCatalog($byGroup['tiposize']==='11.00-20'&&$byGroup['marka']==='HERADE'&&$byGroup['diameter']==='20'&&$byGroup['protect']==='C1'&&$byGroup['']==='Пневматические','Filters were mapped using incorrect group IDs.');
checkCatalog((int)$db->query('SELECT COUNT(*) FROM url_alias')->fetchColumn()===4,'Each public filter must have a registered route.');
checkCatalog((int)$db->query('SELECT COUNT(*) FROM attribute_category')->fetchColumn()===5,'Required category filters were not enabled.');
$count=(int)$db->query('SELECT COUNT(*) FROM attribute_value')->fetchColumn();$writer->bind(773,8,$source['facets']);
checkCatalog((int)$db->query('SELECT COUNT(*) FROM attribute_value')->fetchColumn()===$count,'Repeated import duplicated filter values.');
checkCatalog((int)$db->query('SELECT COUNT(*) FROM attribute_product')->fetchColumn()===5,'Repeated import duplicated product filter links.');
checkCatalog((int)$db->query('SELECT COUNT(*) FROM attribute_category')->fetchColumn()===5,'Repeated import duplicated category links.');
checkCatalog((int)$db->query('SELECT COUNT(*) FROM url_alias')->fetchColumn()===4,'Repeated import duplicated canonical URLs.');

// Exercise the real sitemap SQL against registered canonical aliases, including a stale historical URL.
if(!defined('PATH'))define('PATH','https://techtires.ru');
if(!class_exists('R'))class_alias(\RedBeanPHP\R::class,'R');
R::setup('sqlite::memory:');
foreach(['CREATE TABLE product (id INTEGER,hide TEXT)','CREATE TABLE category (id INTEGER,hide TEXT)','CREATE TABLE contents (id INTEGER,type_id INTEGER,hide TEXT)','CREATE TABLE content_type (id INTEGER,param_url TEXT,hide TEXT)','CREATE TABLE plagins_cross (product_id INTEGER,cross_abbreviated_name TEXT)','CREATE TABLE url_alias (sef TEXT,view TEXT,urlid INTEGER)','CREATE TABLE attribute_group (id INTEGER,url_params TEXT)','CREATE TABLE attribute_value (id INTEGER,attr_group_id INTEGER,value TEXT,alias TEXT,hide TEXT)'] as $sql)R::exec($sql);
R::exec("INSERT INTO attribute_group VALUES (1,'tiposize'),(2,'marka')");
R::exec("INSERT INTO attribute_value VALUES (10,1,'11.00-20','tiposize-11.00-20','show'),(20,2,'EKKA','marka-ekka','show'),(30,2,'HIDDEN','marka-hidden','hide')");
R::exec("INSERT INTO url_alias VALUES ('tiposize-11.00-20','Tiposize',10),('marka-ekka','Marka',20),('marka-EKKA.html','Marka',20),('marka-hidden','Marka',30)");
$urls=(new app\services\SitemapGenerator())->collectUrls();
checkCatalog(in_array('https://techtires.ru/tiposize-11.00-20',$urls,true)&&in_array('https://techtires.ru/marka-ekka',$urls,true),'Canonical filters missing from sitemap.');
foreach($urls as $url)checkCatalog(!str_contains($url,'.html')&&!str_contains($url,'/marka/')&&!str_contains($url,'/tiposize/')&&!str_contains($url,'hidden'),'Sitemap contains a noncanonical or hidden filter URL.');
echo "Catalog document, facet identity/idempotency, canonical redirect and sitemap regression checks passed.\n";