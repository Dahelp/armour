<?php

namespace app\controllers\admin;

use app\services\AdminAuditLogger;
use app\services\ImageFileStorage;
use app\models\AppModel;
use app\models\admin\SSP;
use ishop\App;
use ishop\libs\Pagination;
use app\models\admin\PlaginsComplete;
use app\models\admin\PlaginsCross;
use app\models\admin\PlaginsCrossVendor;
use app\models\admin\PlaginsInseo;
use app\models\admin\PlaginsTechnics;
use app\models\admin\PlaginsTechnicsType;
use app\models\admin\PlaginsTechnicsManufacturer;
use app\models\admin\PlaginsIndexnow;
use app\models\admin\PlaginsPromocode;
use app\models\admin\PlaginsYandexTovars;
use app\services\PluginStoragePolicy;
use app\services\RelationWriter;
use app\services\RemoteXmlDownloader;

class PlaginsController extends AppController {

    public function indexAction(){
		
		$plagins = \R::getAll("SELECT * FROM plagins ORDER BY name");
        $this->setMeta('Список установленых дополнений');
        $this->set(compact('plagins'));
		
	}
	
/*YANDEX PRODUCTS*/	
	public function yandexTovarsAction(){
		$yatovars = new PlaginsYandexTovars();
		$parsed = $yatovars->yandexTovars();
		//$yandex_tovars = \R::getAll("SELECT * FROM plagins_yandex_tovars ORDER BY id");
        $this->setMeta('Настройка Яндекс товаров');
        $this->set(compact('parsed'));
		
	}
/*YANDEX PRODUCTS*/	
	
/*PROMOCODE*/
	
	public function promocodeAction(){
		
		$promocode = \R::getAll("SELECT * FROM plagins_promocode ORDER BY id");
        $this->setMeta('Список промокодов');
        $this->set(compact('promocode'));		
	}
	
	public function promocodeAddAction(){
		if(!empty($_POST)){
            $promocode = new PlaginsPromocode();
            $data = $_POST;
            $promocode->load($data);
            if(!$promocode->validate($data) || !$promocode->checkUnique()){
                $promocode->getErrors();
                redirect();
            }
            if($id = $promocode->save('plagins_promocode', false)){
				(new RelationWriter())->replace('plagins_promocode_category', 'promocode_id', (int)$id, 'category_id', (array)($_POST['category_id'] ?? []));
				AdminAuditLogger::log(2, 28, 'plagins_promocode', (int)$id);
                
                $_SESSION['success'] = 'Промокод добавлен';
                redirect();
            }
        }
		$this->setMeta('Добавить промокод');
		$category = \R::findAll('category');
		$this->set(compact('category'));
	}
	
	public function promocodeEditAction(){
		if(!empty($_POST)){
			$id = $this->getRequestID(false);
            $promocode = new PlaginsPromocode();
            $data = $_POST;
            $promocode->load($data);
			
			if(!$promocode->validate($data)){
                $promocode->getErrors();
                redirect();
            }
			if($promocode->update('plagins_promocode', $id)){
				
				(new RelationWriter())->replace('plagins_promocode_category', 'promocode_id', (int)$id, 'category_id', (array)($_POST['category_id'] ?? []));
				AdminAuditLogger::log(2, 29, 'plagins_promocode', (int)$id);
                
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }

        }
		$id = $this->getRequestID();
        $promocode = \R::load('plagins_promocode', $id);
		$category = \R::findAll('category');
        $this->setMeta('Редактировать промокод');
        $this->set(compact('promocode', 'category'));
	}
	
	public function deletePromocodeAction(){
        $id = $this->getRequestID();        
        $promocode = \R::load('plagins_promocode', $id);
        \R::trash($promocode);
		$category = \R::load('plagins_promocode_category', $promocode->$id);
        \R::trash($category);
        $_SESSION['success'] = 'Промокод удален';
        redirect();
    }
	
/*PROMOCODE*/	
	
/*INDEXNOW*/
	 public function indexnowAction(){
		
		$indexnow = \R::getAll("SELECT * FROM plagins_indexnow ORDER BY id");
        $this->setMeta('Настройка IndexNow');
        $this->set(compact('indexnow'));		
	}
	
	public function indexnowAddAction(){
		if(!empty($_POST)){
            $indexnow = new PlaginsIndexnow();
            $data = $_POST;
            $indexnow->load($data);
            if(!$indexnow->validate($data) || !$indexnow->checkUnique()){
                $indexnow->getErrors();
                redirect();
            }
            if($indexnow->save('plagins_indexnow', false)){
                $_SESSION['success'] = 'Поисковая система добавлена';
                redirect();
            }
        }
		$this->setMeta('Добавить поисковую систему');
	}
	
	public function indexnowEditAction(){
		if(!empty($_POST)){
			$id = $this->getRequestID(false);
            $indexnow = new PlaginsIndexnow();
            $data = $_POST;
            $indexnow->load($data);
			
			if(!$indexnow->validate($data)){
                $indexnow->getErrors();
                redirect();
            }
			if($indexnow->update('plagins_indexnow', $id)){
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }

        }
		$id = $this->getRequestID();
        $indexnow = \R::load('plagins_indexnow', $id);
        $this->setMeta('Редактировать поисковую систему');
        $this->set(compact('indexnow'));
	}
	
	public function deleteIndexnowAction(){
        $id = $this->getRequestID();        
        $indexnow = \R::load('plagins_indexnow', $id);
        \R::trash($indexnow);
        $_SESSION['success'] = 'Поисковая система удалена';
        redirect();
    }
/*INDEXNOW*/

/* INSEO */	
	public function inseoAction(){
		
		$inseo = \R::getAll("SELECT id, tip, category_id FROM plagins_inseo ORDER BY tip");
        $this->setMeta('INSEO - Список правил');
        $this->set(compact('inseo'));
		
	}
	
	public function inseoAddAction(){
		if(!empty($_POST)){
            $inseo = new PlaginsInseo();
            $data = $_POST;
            $inseo->load($data);
            if(!$inseo->validate($data) || !$inseo->checkUnique()){
                $inseo->getErrors();
                redirect();
            }
            if($inseo->save('plagins_inseo', false)){
                $_SESSION['success'] = 'Правило добавлено';
                redirect();
            }
        }
		$this->setMeta('Добавить правило');
	}
	
	public function inseoEditAction(){
		if(!empty($_POST)){
			$id = $this->getRequestID(false);
            $inseo = new PlaginsInseo();
            $data = $_POST;
            $inseo->load($data);
			if($inseo->attributes['tip'] =="attribute_group") { $inseo->attributes['category_id'] =  $_POST['group_id']; }
			if(!$inseo->validate($data)){
                $inseo->getErrors();
                redirect();
            }
			if($inseo->update('plagins_inseo', $id)){
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }

        }
		$id = $this->getRequestID();
        $inseo = \R::load('plagins_inseo', $id);
		App::$app->setProperty('parent_id', $inseo->category_id);
        $this->setMeta('Редактировать правило');
        $this->set(compact('inseo'));
		
	}
	
	public function deleteInseoAction(){
        $id = $this->getRequestID();        
        $inseo = \R::load('plagins_inseo', $id);
        \R::trash($inseo);
        $_SESSION['success'] = 'Правило удалено';
        redirect();
    }
/* INSEO */

/* CROSS-NUMBER */	
	public function crossAction(){
		$crossing = \R::getAll("SELECT plagins_cross.*, plagins_cross_vendor.name AS vendor, product.name AS product_name FROM plagins_cross, plagins_cross_vendor, product WHERE plagins_cross_vendor.id = plagins_cross.vendor_id AND plagins_cross.product_id = product.id");
        $this->setMeta('Кросс-номера');
        $this->set(compact('crossing'));		
	}
	
	public function crossAddAction(){
		if(!empty($_POST)){
            $cross = new PlaginsCross();
            $data = $_POST;
            $cross->load($data);
            if(!$cross->validate($data) || !$cross->checkUnique()){
                $cross->getErrors();
                redirect();
            }
			$product = \R::findOne('product', 'article = ?', [$data['product_id']]);
			$cross->attributes['product_id'] = $product['id'];
            if($id = $cross->save('plagins_cross')){
				
				$p = \R::load('plagins_cross', $id);
				
				// API IndexNow
				$indexnow = new PlaginsIndexnow();
				$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
				foreach($inw as $in){					
					$search_engine .= $indexnow->indexNowEngine($in->url, 'cross', $p["cross_abbreviated_name"], $in->verification);
				}
				
                $_SESSION['success'] = 'Кросс-номер добавлен.'.$search_engine.'';
                redirect();
            }
        }
        $vendors = \R::findAll('plagins_cross_vendor');
        $this->setMeta('Добавить кросс-номер');		
        $this->set(compact('vendors'));		
	}
		
	public function crossEditAction(){
		if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $cross = new PlaginsCross();
            $data = $_POST;
            $cross->load($data);
			$product = \R::findOne('product', 'article = ?', [$data['product_id']]);
			$cross->attributes['product_id'] = $product['id'];
            if(!$cross->validate($data)){
                $cross->getErrors();
                redirect();
            }
            if($cross->update('plagins_cross', $id)){
				
				$p = \R::load('plagins_cross', $id);
				
				// API IndexNow
				$indexnow = new PlaginsIndexnow();
				$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
				foreach($inw as $in){					
					$search_engine .= $indexnow->indexNowEngine($in->url, 'cross', $p["cross_abbreviated_name"], $in->verification);
				}
				
                $_SESSION['success'] = 'Изменения сохранены.'.$search_engine.'';
                redirect();
            }
        }
        $id = $this->getRequestID();
        $cross = \R::load('plagins_cross', $id);
        $vendors = \R::findAll('plagins_cross_vendor');
        $this->setMeta('Редактирование кросс-номера');
        $this->set(compact('cross', 'vendors'));
		
	}
	
	public function crossExportAction(){
		if(!empty($_POST)){
			$cross = new PlaginsCross();
			if($_POST["format"] == 1) {
				if($_POST["dumps"] == 1) {
					$cross->exportCrossXml();
				}
				if($_POST["dumps"] == 2) {
					$product_id = $_POST["product_id"];
					$cross->exportCrossXml($product_id);
				}
			}
			if($_POST["format"] == 2) {
				if($_POST["dumps"] == 1) {
					$cross->exportCrossCsv();
				}
				if($_POST["dumps"] == 2) {
					$product_id = $_POST["product_id"];
					$cross->exportCrossCsv($product_id);
				}
			}
		}
		$this->setMeta('Экспорт кросс-номера');
	}
	
	public function crossImportAddAction(){
		if(!empty($_POST)){
			
			if($_POST["format"] == 1) {
				$path = (new RemoteXmlDownloader())->download((string)($_POST['url_file'] ?? ''), WWW . '/xml');
				$xml = simplexml_load_file($path, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
				@unlink($path);
				if ($xml === false) {
					throw new \RuntimeException('Получен некорректный XML-файл.');
				}
				if ( $_POST['url_file'])
				{ 
					$cross = array();
					$i=1;
					foreach ( $xml->shop->offers->offer as $row )  
					{

						$goods_cross_id = $row['id'];
						$article = $row->goods;
						$manufacturer_name = $row->manufacturer;
						$cross_name = $row->name;
						$cross_abbreviated_name = $row->abbreviated;
						$tip_cross = $row->tip;
						$equipment_manufacturer = $row->equipment;
						
						if($tip_cross=="Внешняя часть") {$tip_cross = "1";}
						if($tip_cross=="Внутренняя часть") {$tip_cross = "2";}
						if($tip_cross=="Не определено") {$tip_cross = "3";}
						if($tip_cross=="Комплект из 2х частей") {$tip_cross = "4";}
						$equipment_manufacturer= trim($equipment_manufacturer);

						if($equipment_manufacturer=="Да") {$equipment_manufacturer = "1";}
						elseif($equipment_manufacturer=="Нет") {$equipment_manufacturer = "2";}
						
						$cross[$i]['cross_id'] = $goods_cross_id;
						$product = \R::findOne('product', 'article = ?', [$article]);
						$cross[$i]['product_id'] = $product['id'];
						$cross[$i]['cross_name'] = $cross_name;
						$cross[$i]['cross_abbreviated_name'] = $cross_abbreviated_name;
						$vendor = \R::findOne('plagins_cross_vendor', 'name = ?', [$manufacturer_name]);
						$cross[$i]['vendor_id'] = $vendor['id'];
						$cross[$i]['manufacturer_name'] = $manufacturer_name;
						$cross[$i]['tip_cross'] = $tip_cross;  
						$cross[$i]['equipment_vendor'] = $equipment_manufacturer;						
						
					$i++;
					}					
				}
			}
			if($_POST["format"] == 2) {
				$file_type = substr($_FILES['fileprod']['name'], -3);
				$_FILES['fileprod']['name'] = mt_rand(1,100).rand(100,1054).mt_rand(10,150).".".$file_type;
				  
				if($_FILES['fileprod']['size'] > 1024*1*1024)
				{
					$_SESSION['success'] = "Ошибка размера файла!";
				}
				else if( !copy($_FILES['fileprod']['tmp_name'], "adminlte/xls/".$_FILES['fileprod']['name']) )
				{
					$_SESSION['success'] = "Ошибка загрузки файла!";
				}
				else
				{
					$filecsv = $_FILES['fileprod']['name'];
					$data = File("adminlte/xls/$filecsv");
					$cross = array();
					for ($i=1;$i<count($data);$i++) {
 
						list($a, $b, $c, $d, $e, $f, $k) = explode(";", $data[$i]);
					    $c = iconv("CP1251", "UTF-8", $c);
						$d = iconv("CP1251", "UTF-8", $d);
						$e = iconv("CP1251", "UTF-8", $e);
						$f = iconv("CP1251", "UTF-8", $f);
						$k = iconv("CP1251", "UTF-8", $k);					 
					  
						if($f=="Внешняя часть") {$f = "1";}
						if($f=="Внутренняя часть") {$f = "2";}
						if($f=="Не определено") {$f = "3";}
						if($f=="Комплект из 2х частей") {$f = "4";}
						$k= trim($k);

						if($k=="Да") {$k = "1";}
						elseif($k=="Нет") {$k = "2";}
						
						$cross[$i]['cross_id'] = $a;
						$product = \R::findOne('product', 'article = ?', [$b]);
						$cross[$i]['product_id'] = $product['id'];
						$cross[$i]['cross_name'] = $c;
						$cross[$i]['cross_abbreviated_name'] = $d;
						$vendor = \R::findOne('plagins_cross_vendor', 'name = ?', [$e]);
						$cross[$i]['vendor_id'] = $vendor['id'];
						$cross[$i]['manufacturer_name'] = $e;
						$cross[$i]['tip_cross'] = $f;  
						$cross[$i]['equipment_vendor'] = $k;										
											
					}				
						
					@unlink("../public/adminlte/xls/".$filecsv."");
					
				}
				
			}
			
		}
		
        $this->setMeta('Подтверждение импорта CSV кросс-номеров');
		$this->set(compact('cross', 'crossid'));
	}
	
	public function crossImportConfirmationAction(){
		if(!empty($_POST)){
			$insertRows = [];
			$insertBindings = [];
			$search_engine = '';
			$crossIds = (array)($_POST['cross_id'] ?? []);
			for($i = 0; $i < count($crossIds); $i++)
			{
				$cross_id = (int)($crossIds[$i] ?? 0);
				$product_id = (int)($_POST['product_id'][$i] ?? 0);
				$cross_name = (string)($_POST['cross_name'][$i] ?? '');
				$cross_abbreviated_name = trim((string)($_POST['cross_abbreviated_name'][$i] ?? ''));
				$vendor_id = (int)($_POST['vendor_id'][$i] ?? 0);
				$tip_cross = (int)($_POST['tip_cross'][$i] ?? 0);
				$equipment_vendor = (int)($_POST['equipment_vendor'][$i] ?? 0);
				if ($cross_abbreviated_name === '') {
					continue;
				}
				$crossname = \R::findOne('plagins_cross', 'cross_abbreviated_name = ?', [$cross_abbreviated_name]);
				
				if($crossname){						
					\R::exec('UPDATE plagins_cross SET cross_id = ?, product_id = ?, vendor_id = ?, cross_name = ?, cross_abbreviated_name = ?, tip_cross = ?, equipment_vendor = ? WHERE id = ?', [$cross_id, $product_id, $vendor_id, $cross_name, $cross_abbreviated_name, $tip_cross, $equipment_vendor, (int)$crossname->id]);
				}else{
					$insertRows[] = '(?, ?, ?, ?, ?, ?, ?)';
					array_push($insertBindings, $cross_id, $product_id, $vendor_id, $cross_name, $cross_abbreviated_name, $tip_cross, $equipment_vendor);
				}
				
				// API IndexNow
				$indexnow = new PlaginsIndexnow();
				$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
				foreach($inw as $in){					
					$search_engine .= $indexnow->indexNowEngine($in->url, 'cross', $cross_abbreviated_name, $in->verification);
				}
			}
			
			if($insertRows !== []){
				\R::exec('INSERT IGNORE INTO plagins_cross (cross_id, product_id, vendor_id, cross_name, cross_abbreviated_name, tip_cross, equipment_vendor) VALUES ' . implode(',', $insertRows), $insertBindings);
			}				
						
			$_SESSION['success'] = 'Кросс-номера импортированы';
			redirect(''.ADMIN.'/plagins/cross');					
		
			$this->setMeta('Импорт подтверждён');
		}
	}
	
	public function crossImportAction(){
		$this->setMeta('Импорт CSV кросс-номеров');
	}
	
	public function crossVendorAction(){
		
		$vendors = \R::getAll("SELECT * FROM `plagins_cross_vendor`");
        $this->setMeta('Производители кросс-номеров');
        $this->set(compact('vendors', 'count', 'pagination'));
		
	}
	
	public function crossAddVendorAction(){
		if(!empty($_POST)){
            $vendor = new PlaginsCrossVendor();
            $data = $_POST;
            $vendor->load($data);
            if(!$vendor->validate($data) || !$vendor->checkUnique()){
                $vendor->getErrors();
                redirect();
            }
            if($vendor->save('plagins_cross_vendor', false)){
                $_SESSION['success'] = 'Производитель добавлен';
                redirect();
            }
        }
        $this->setMeta('Добавить производителя кросс-номер');
	}
		
	public function crossEditVendorAction(){
		if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $vendor = new PlaginsCrossVendor();
            $data = $_POST;
            $vendor->load($data);
            if(!$vendor->validate($data)){
                $vendor->getErrors();
                redirect();
            }
            if($vendor->update('plagins_cross_vendor', $id)){
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }
        $id = $this->getRequestID();
        $vendor = \R::load('plagins_cross_vendor', $id);
        $this->setMeta('Редактирование производителя кросс-номера');
        $this->set(compact('vendor'));
		
	}
	
	public function deleteCrossAction(){
        $id = $this->getRequestID();        
        $cross = \R::load('plagins_cross', $id);
        \R::trash($cross);
		
		// API IndexNow
		$indexnow = new PlaginsIndexnow();
		$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
		foreach($inw as $in){					
			$search_engine .= $indexnow->indexNowEngine($in->url, 'cross', $cross["cross_abbreviated_name"], $in->verification);
		}
		
        $_SESSION['success'] = 'Кросс-номер '.$cross["cross_name"].' удален.'.$search_engine.'';
        redirect();
    }
	
	public function deleteCrossVendorAction(){
        $id = $this->getRequestID();        
        $vendor = \R::load('plagins_cross_vendor', $id);
        \R::trash($vendor);
        $_SESSION['success'] = 'Производитель '.$vendor["name"].' удален';
        redirect();
    }
	
/* AND CROSS-NUMBER */

/* TECHNICS */
	public function technicsAction(){
		
		$category = $this->getTechnics();
		$manufacturers = \R::getAll('SELECT * FROM `technics_manufacturer` ORDER BY name');
        $technics = \R::getAll("SELECT technics.*, technics_type.name AS cat, technics_manufacturer.name AS manufacturer_name FROM technics JOIN technics_type JOIN technics_manufacturer ON technics_type.id = technics.type_id AND technics_manufacturer.id = technics.manufacturer_id ORDER BY technics.model");
        $this->setMeta('Техника');
        $this->set(compact('technics', 'category', 'manufacturers'));		
	}
	
	public function serverProcessingTechnicsAction(){		
		//datatables server-side
		$id = $_GET["id"];
		if($_GET["id"]){ $where = " WHERE a.type_id = '".$id."'"; }
		$table = <<<EOT
		 (
			SELECT a.id, a.img, b.name, a.manufacturer_id, a.model FROM technics a LEFT JOIN technics_type b ON a.type_id = b.id$where 
		 ) temp
		EOT;
		$primaryKey = 'id';
		
		$columns = array(
			array( 'db' => 'id', 'dt' => 0 ),
			array( 'db' => 'img',  'dt' => 1,
				   'formatter' => function( $d, $row ) {
						return '<img src="/images/technics/mini/'.$d.'" alt="" style="max-height: 70px">';
					} ),
			array( 'db' => 'name',  'dt' => 2	),
			array( 'db' => 'manufacturer_id', 'dt' => 3,
					'formatter' => function( $d, $row ) {
						$manufacturer = \R::findOne('technics_manufacturer', 'id = ?', [$d]);
						return ''.$manufacturer['name'].'';
					}
			),
			array( 'db' => 'model',   'dt' => 4 ),			
			array( 'db' => 'id',   'dt' => 5,
					'formatter' => function( $d, $row ) {
						$tiposize = \R::count('technics_tiposize', 'technics_id = ?', [$d]);
						return ''.$tiposize.'';
					}
			),
			array( 'db' => 'id',   'dt' => 6, 
				'formatter' => function( $d, $row ) {
					$technics = \R::findOne('technics', 'id=?', [$d]);
					if($technics['title'] !="") { $s1 = "20"; }else{ $s1 = 0; }
					if($technics['description'] !="") { $s2 = "20"; }else{ $s2 = 0; }
					if($technics['keywords'] !="") { $s3 = "20"; }else{ $s3 = 0; }
					if($technics['content'] !="") { $s4 = "20"; }else{ $s4 = 0; }
					if($technics['img'] !="") { $s5 = "20"; }else{ $s5 = 0; }
					$seo = $s1+$s2+$s3+$s4+$s5; 
					if($seo == 20) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-danger progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
					if($seo == 40) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-danger progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
					if($seo == 60) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-warning progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
					if($seo == 80) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-warning progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
					if($seo == 100) { $itog_seo = "SEO $seo% <div class='progress progress-xs'><div class='progress-bar bg-success progress-bar-striped' role='progressbar' aria-valuenow='".$seo."' aria-valuemin='0' aria-valuemax='100' style='width: ".$seo."%'><span class='sr-only'>".$seo."% Complete (warning)</span></div></div>"; }
										
					return ''.$itog_seo.''; 
				}
			),
			array( 'db' => 'id',   'dt' => 7, 
					'formatter' => function( $d, $row ) {
						$technics = \R::findOne('technics', 'id=?', [$d]);
						return '<a href="'.ADMIN.'/plagins/technics-edit?id='.$d.'"><i class="fas fa-pencil-alt"></i></a> <a class="delete" href="'.ADMIN.'/plagins/technics-delete?id='.$d.'"><i class="fas fa-times-circle text-danger"></i></a> <a target="_blank" href="/technics/'.$technics['alias'].'"><i class="fas fa-eye"></i></a> <a target="_blank" href="'.ADMIN.'/plagins/technics-copy?id='.$d.'"><i class="fas fa-copy"></i></a>'; 
					}
			)
		);
		 
		// SQL server connection information
		$sql_details = array(
			'user' => App::$app->getProperty('sql_user'),
			'pass' => App::$app->getProperty('sql_pass'),
			'db'   => App::$app->getProperty('sql_db'),
			'host' => App::$app->getProperty('sql_host')
		);
		$spp = new SSP();
		echo json_encode(
			$spp::simple( $_GET, $sql_details, $table, $primaryKey, $columns, null, "" )
		);
		die;
		
	}
	
	public function technicsTypeAction(){
		$id = $this->getRequestID();
		$category = \R::findOne('technics_type', 'id = ?', [$id]);
        $technics = \R::getAll("SELECT technics.*, technics_type.name AS cat, technics_manufacturer.name AS manufacturer_name FROM technics JOIN technics_type JOIN technics_manufacturer ON technics_type.id = technics.type_id AND technics_manufacturer.id = technics.manufacturer_id AND technics.type_id = '".$id."' ORDER BY technics.model");		
        $this->setMeta('Список категорий');
        $this->set(compact('technics', 'category'));
    }
	
	/* ====Каталог - получение массива=== */
    public function getTechnics(){
		$query = \R::getAll('SELECT * FROM `technics_type`');
		$category = [];
		foreach($query as $k => $row){
			if(!$row['parent_id']){
				$category[$row['id']][] = $row['name'];
			}else{
				$category[$row['parent_id']]['sub'][$row['id']] = $row['name'];
			}
		}
		return $category;
	}
	
	public function technicsAddImageAction(){
        if(isset($_GET['upload'])){
            if($_POST['name'] == 'single'){
                $wmax = App::$app->getProperty('img_width');
                $hmax = App::$app->getProperty('img_height');
				$wmaxmini = App::$app->getProperty('mini_img_width');
                $hmaxmini = App::$app->getProperty('mini_img_height');
            }else{
                $wmax = App::$app->getProperty('gallery_width');
                $hmax = App::$app->getProperty('gallery_height');
				$wmaxmini = App::$app->getProperty('mini_gallery_width');
                $hmaxmini = App::$app->getProperty('mini_gallery_height');
            }
            $name = $_POST['name'];
            $technics = new PlaginsTechnics();
            $technics->uploadImg($name, $wmax, $hmax, $wmaxmini, $hmaxmini);
        }
    }	
	public function technicsAddAction(){
        if(!empty($_POST)){
            $technics = new PlaginsTechnics();
            $data = $_POST;
            $technics->load($data);
            $technics->getImg();

            if(!$technics->validate($data) || !$technics->checkUnique()){
                $technics->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $technics->save('technics')){                
                $types = \R::findOne('technics_type', 'id = ?', [$data["type_id"]]);
				$manufacturers = \R::findOne('technics_manufacturer', 'id = ?', [$data["manufacturer_id"]]);
				$name = "".$types->name.", ".$manufacturers->name.", ".$data["model"]."";
                $alias = AppModel::createAlias('technics', 'alias', $name, $id);
                $p = \R::load('technics', $id);
                $p->alias = $alias;
                \R::store($p);
				$technics->editSizeTechnics($id, $data);
				$technics->editSizeBackTechnics($id, $data);
				$technics->editSizeAltTechnics($id, $data);
				$technics->editSizeAltBackTechnics($id, $data);
				AdminAuditLogger::log(2, 13, 'technics', (int)$id);
                
				// API IndexNow
				$indexnow = new PlaginsIndexnow();
				$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
				foreach($inw as $in){					
					$search_engine .= $indexnow->indexNowEngine($in->url, 'technics', $p->alias, $in->verification);
				}
				
				$_SESSION['success'] = 'Техника добавлена.'.$search_engine.'';
            }
			
            redirect();
        }
		
		$types = \R::getAll('SELECT * FROM `technics_type`');
        $manufacturers = \R::getAll('SELECT * FROM `technics_manufacturer` ORDER BY name');
        $this->setMeta('Добавление техники');
		$this->set(compact('types', 'manufacturers'));
    }
	
	public function technicsEditAction(){	
		
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $technics = new PlaginsTechnics();
            $data = $_POST;
            $technics->load($data);
            $technics->getImg();
            if(!$technics->validate($data)){
                $technics->getErrors();
                redirect();
            }
			
            if($technics->update('technics', $id)){
				
				$types = \R::findOne('technics_type', 'id = ?', [$data["type_id"]]);
				$manufacturers = \R::findOne('technics_manufacturer', 'id = ?', [$data["manufacturer_id"]]);
				$name = "".$types->name.", ".$manufacturers->name.", ".$data["model"]."";
                $alias = AppModel::createAlias('technics', 'alias', $name, $id);
				$technics->editSizeTechnics($id, $data);
				$technics->editSizeBackTechnics($id, $data);
				$technics->editSizeAltTechnics($id, $data);
				$technics->editSizeAltBackTechnics($id, $data);
                $technics = \R::load('technics', $id);
				if($data['alias']!=""){ $technics->alias = $data['alias'];}
				else{$technics->alias = $alias;}
				AdminAuditLogger::log(2, 14, 'technics', (int)$id);
                \R::store($technics);
				
				// API IndexNow
				$indexnow = new PlaginsIndexnow();
				$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
				foreach($inw as $in){					
					$search_engine .= $indexnow->indexNowEngine($in->url, 'technics', $technics->alias, $in->verification);
				}
				
                $_SESSION['success'] = 'Изменения сохранены.'.$search_engine.'';
                redirect();
            }
        }

		$id = $this->getRequestID();
        $technics = \R::load('technics', $id);

		$types = \R::findOne('technics_type', 'id = ?', [$technics->type_id]);
        $manufacturers = \R::findOne('technics_manufacturer', 'id = ?', [$technics->manufacturer_id]);
		$tipo_size = \R::getAll('SELECT attribute_value.id, attribute_value.value FROM attribute_value JOIN technics_tiposize ON technics_tiposize.value_id = attribute_value.id WHERE technics_tiposize.technics_id = ? AND technics_tiposize.tip_size = ?', [$id, 1]);
		$tipo_sizeback = \R::getAll('SELECT attribute_value.id, attribute_value.value FROM attribute_value JOIN technics_tiposize ON technics_tiposize.value_id = attribute_value.id WHERE technics_tiposize.technics_id = ? AND technics_tiposize.tip_size = ?', [$id, 2]);
		$tipo_sizealt = \R::getAll('SELECT attribute_value.id, attribute_value.value FROM attribute_value JOIN technics_tiposize ON technics_tiposize.value_id = attribute_value.id WHERE technics_tiposize.technics_id = ? AND technics_tiposize.tip_size = ?', [$id, 3]);
		$tipo_sizealtback = \R::getAll('SELECT attribute_value.id, attribute_value.value FROM attribute_value JOIN technics_tiposize ON technics_tiposize.value_id = attribute_value.id WHERE technics_tiposize.technics_id = ? AND technics_tiposize.tip_size = ?', [$id, 4]);
		
        $this->setMeta("Редактирование техники {$types->name} {$manufacturers->name} {$technics->model}");
        $this->set(compact('technics', 'types', 'manufacturers', 'tipo_size', 'tipo_sizeback', 'tipo_sizealt', 'tipo_sizealtback'));
    }
	
	public function tiposizeTechnicsAction(){
        $q = isset($_GET['q']) ? $_GET['q'] : '';
        $data['items'] = [];
        $tiposize = \R::getAssoc('SELECT id, value FROM attribute_value WHERE attr_group_id = ? AND value LIKE ? LIMIT 15', [2, "%{$q}%"]);
        if($tiposize){
            $i = 0;
            foreach($tiposize as $id => $value){
                $data['items'][$i]['id'] = $id;
                $data['items'][$i]['text'] = $value;
                $i++;
            }
        }
        echo json_encode($data);
        die;
    }
	
	public function technicsDeleteAction(){
        $id = $this->getRequestID();        
        $technics = \R::load('technics', $id);
		@unlink(WWW . "/images/technics/baseimg/".$technics["img"]."");
		@unlink(WWW . "/images/technics/mini/".$technics["img"]."");
		\R::trash($technics);
		
		// Удаление из всех таблиц по технике (tiposize)
		$related = \R::findAll('technics_tiposize', 'technics_id = ?', [$id]);
		foreach($related as $rel) {
			\R::exec("DELETE FROM technics_tiposize WHERE id = ?", [$rel->id]);
		}
		
		//Запись в историю
		AdminAuditLogger::log(2, 15, 'technics', (int)$id);
        
        // API IndexNow
		$indexnow = new PlaginsIndexnow();
		$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
		foreach($inw as $in){					
			$search_engine .= $indexnow->indexNowEngine($in->url, 'technics', $technics->alias, $in->verification);
		}
		
        $_SESSION['success'] = 'Техника ID '.$id.' удалена.'.$search_engine.'';
        redirect();
    }
		
	public function technicsAddTypeAction(){
        if(!empty($_POST)){
            $types = new PlaginsTechnicsType();
            $data = $_POST;
            $types->load($data);
            $types->getImg();

            if(!$types->validate($data) || !$types->checkUnique()){
                $types->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $types->save('technics_type')){                
                $alias = AppModel::createAlias('technics_type', 'alias', $data["name"], $id);
                $p = \R::load('technics_type', $id);
                $p->alias = $alias;
                \R::store($p);
				AdminAuditLogger::log(2, 22, 'technics_type', (int)$id);
                $_SESSION['success'] = 'Категория техники добавлена';
            }
            redirect();
        }
		
        $this->setMeta('Добавление категории техники');

    }
	
	public function technicsEditTypeAction(){	
		
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $types = new PlaginsTechnicsType();
            $data = $_POST;
            $types->load($data);
            $types->getImg();
            if(!$types->validate($data)){
                $types->getErrors();
                redirect();
            }
			
            if($types->update('technics_type', $id)){				
				$alias = AppModel::createAlias('technics_type', 'alias', $data["name"], $id);				
                $types = \R::load('technics_type', $id);
				if($data['alias']!=""){ $types->alias = $data['alias'];}
				else{$types->alias = $alias;}
				AdminAuditLogger::log(2, 23, 'technics_type', (int)$id);
                \R::store($types);
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }

		$id = $this->getRequestID();
        $types = \R::load('technics_type', $id);
		
		$this->setMeta("Редактирование категории техники {$types->name}");
        $this->set(compact('types'));
    }
	
	public function technicsTypeAddImageAction(){
        if(isset($_GET['upload'])){
            if($_POST['name'] == 'single'){
                $wmax = App::$app->getProperty('img_width');
                $hmax = App::$app->getProperty('img_height');
				$wmaxmini = App::$app->getProperty('mini_img_width');
                $hmaxmini = App::$app->getProperty('mini_img_height');
            }else{
                $wmax = App::$app->getProperty('gallery_width');
                $hmax = App::$app->getProperty('gallery_height');
				$wmaxmini = App::$app->getProperty('mini_gallery_width');
                $hmaxmini = App::$app->getProperty('mini_gallery_height');
            }
            $name = $_POST['name'];
            $types = new PlaginsTechnicsType();
            $types->uploadImg($name, $wmax, $hmax, $wmaxmini, $hmaxmini);
        }
    }
	
	public function technicsTypeDeleteAction(){
        $id = $this->getRequestID();        
        $types = \R::load('technics_type', $id);
		@unlink(WWW . "/images/technics_type/baseimg/".$types["img"]."");
		$related = \R::findAll('technics', 'type_id = ?', [$types["id"]]);
		foreach($related as $rel) {
			\R::exec("DELETE FROM technics WHERE id = ?", [$rel->id]);
			
			// Удаление из всех таблиц по технике (tiposize)
			$related_size = \R::findAll('technics_tiposize', 'technics_id = ?', [$rel->id]);
			foreach($related_size as $rel_size) {
				\R::exec("DELETE FROM technics_tiposize WHERE id = ?", [$rel_size->id]);
			}
			AdminAuditLogger::log(2, 15, 'technics', (int)$rel->id);
		}
		AdminAuditLogger::log(2, 24, 'technics_type', (int)$id);
        \R::trash($types);		
		
        $_SESSION['success'] = 'Категория '.$types["name"].' удалена';
        redirect();
    }
	
	public function technicsManufacturerAction(){
		
        $manufacturer = \R::getAll("SELECT * FROM technics_manufacturer ORDER BY name");

        $this->setMeta('Производители техники');
        $this->set(compact('manufacturer'));		
	}
	
	public function technicsAddManufacturerAction(){
        if(!empty($_POST)){
            $manufacturer = new PlaginsTechnicsManufacturer();
            $data = $_POST;
            $manufacturer->load($data);
            $manufacturer->getImg();

            if(!$manufacturer->validate($data) || !$manufacturer->checkUnique()){
                $manufacturer->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $manufacturer->save('technics_manufacturer')){                
                $alias = AppModel::createAlias('technics_manufacturer', 'alias', $data["name"], $id);
                $p = \R::load('technics_manufacturer', $id);
                $p->alias = $alias;
                \R::store($p);
				AdminAuditLogger::log(2, 25, 'technics_manufacturer', (int)$id);
                $_SESSION['success'] = 'Производитель техники добавлен';
            }
            redirect();
        }
		
        $this->setMeta('Добавление производителя техники');

    }
	
	public function technicsEditManufacturerAction(){	
		
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $manufacturer = new PlaginsTechnicsType();
            $data = $_POST;
            $manufacturer->load($data);
            $manufacturer->getImg();
            if(!$manufacturer->validate($data)){
                $manufacturer->getErrors();
                redirect();
            }
			
            if($manufacturer->update('technics_manufacturer', $id)){				
				$alias = AppModel::createAlias('technics_manufacturer', 'alias', $data["name"], $id);				
                $manufacturer = \R::load('technics_manufacturer', $id);
				if($data['alias']!=""){ $manufacturer->alias = $data['alias'];}
				else{$manufacturer->alias = $alias;}
				AdminAuditLogger::log(2, 26, 'technics_manufacturer', (int)$id);
                \R::store($manufacturer);
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }

		$id = $this->getRequestID();
        $manufacturer = \R::load('technics_manufacturer', $id);
		
		$this->setMeta("Редактирование производителя техники {$manufacturer->name}");
        $this->set(compact('manufacturer'));
    }
	
	public function technicsManufacturerAddImageAction(){
        if(isset($_GET['upload'])){
            if($_POST['name'] == 'single'){
                $wmax = App::$app->getProperty('img_width');
                $hmax = App::$app->getProperty('img_height');
				$wmaxmini = App::$app->getProperty('mini_img_width');
                $hmaxmini = App::$app->getProperty('mini_img_height');
            }else{
                $wmax = App::$app->getProperty('gallery_width');
                $hmax = App::$app->getProperty('gallery_height');
				$wmaxmini = App::$app->getProperty('mini_gallery_width');
                $hmaxmini = App::$app->getProperty('mini_gallery_height');
            }
            $name = $_POST['name'];
            $manufacturer = new PlaginsTechnicsManufacturer();
            $manufacturer->uploadImg($name, $wmax, $hmax, $wmaxmini, $hmaxmini);
        }
    }
	
	public function technicsManufacturerDeleteAction(){
        $id = $this->getRequestID();        
        $manufacturer = \R::load('technics_manufacturer', $id);
		@unlink(WWW . "/images/technics_manufacturer/baseimg/".$manufacturer["img"]."");
		
		AdminAuditLogger::log(2, 27, 'technics_manufacturer', (int)$id);
        \R::trash($manufacturer);		
		
        $_SESSION['success'] = 'Производитель '.$manufacturer["name"].' удален';
        redirect();
    }
	
	public function technicsExportxmlAction(){
		$id = $this->getRequestID();
		
		$technics = new PlaginsTechnics();
		$technics->exportTechnicsXml($id);		
	}	
	
	public function technicsExportAction(){
		if(!empty($_POST)){
			$technics = new PlaginsTechnics();
			$technics->exportTechnicsXml();				
		}
		$this->setMeta('Экспорт техники');
	}
	
	public function technicsImportAction(){
		if(!empty($_POST)){	
			$path = (new RemoteXmlDownloader())->download((string)($_POST['url_file'] ?? ''), WWW . '/xml');
			$xml = simplexml_load_file($path, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
			@unlink($path);
			if ($xml === false) {
				throw new \RuntimeException('Получен некорректный XML-файл.');
			}

				foreach ( $xml->shop->offers->offer as $row )  
				{
					$technics = new PlaginsTechnics();
					
					$technic_id = $row['id'];
					$type_name = (string)$row->tip;
					$manufacturer_name = (string)$row->name;
					$model = (string)$row->model;
					$array_size = explode(',', $row->size);
					$array_size_back = explode(',', $row->size_back);
					$array_size_alt = explode(',', $row->size_alt);
					$array_size_alt_back = explode(',', $row->size_alt_back);
					$data = Array('size' => $array_size, 'size_back' => $array_size_back, 'size_alt' => $array_size_alt, 'size_alt_back' => $array_size_alt_back);
					$type = \R::findOne('technics_type', 'name = ?', [$type_name]);
					if(!$type){
						$type = \R::dispense('technics_type');
						$type->name = (string)$row->tip;
						$type->alias = '';
						$type->hide = 'show';
						$type->title = '';
						$type->description = '';
						$type->keywords = '';
						$type->content = '';
						$type->img = '';
						$type->seoname_1 = '';
						$type->seoname_2 = '';
						$type->seoname_3 = '';
						$typeId = (int)\R::store($type);
						$type->alias = AppModel::createAlias('technics_type', 'alias', (string)$row->tip, $typeId);
						\R::store($type);
						AdminAuditLogger::log(2, 22, 'technics_type', (int)$typeId);
					}
					$technics->attributes['type_id'] = (int)$type->id;
					$manufacturer = \R::findOne('technics_manufacturer', 'name = ?', [$manufacturer_name]);
					if(!$manufacturer){
						$manufacturer = \R::dispense('technics_manufacturer');
						$manufacturer->name = (string)$row->name;
						$manufacturer->content = '';
						$manufacturer->alias = '';
						$manufacturer->title = '';
						$manufacturer->description = '';
						$manufacturer->keywords = '';
						$manufacturer->hide = 'show';
						$manufacturer->img = '';
						$manufacturerId = (int)\R::store($manufacturer);
						$manufacturer->alias = AppModel::createAlias('technics_manufacturer', 'alias', (string)$row->name, $manufacturerId);
						\R::store($manufacturer);
						AdminAuditLogger::log(2, 25, 'technics_manufacturer', (int)$manufacturerId);
					}
					$technics->attributes['manufacturer_id'] = (int)$manufacturer->id;
					$technics->attributes['model'] = $model;	
					$technics->attributes['position'] = '0';
					$technics->attributes['hide'] = 'show';
					
					if(!$technics->checkUnique()){
						$technics->getErrors();
						$_SESSION['form_data'] = $data;
						redirect();
					}
					else{													
						$wmax = App::$app->getProperty('img_width');
						$hmax = App::$app->getProperty('img_height');
						$wmaxmini = App::$app->getProperty('mini_img_width');
						$hmaxmini = App::$app->getProperty('mini_img_height');
						$img_name = "".$row->name." ".$row->model."";
						$technics->uploadImgXml($row->picture, $img_name, $wmax, $hmax, $wmaxmini, $hmaxmini);
						$technics->getImg();
						if($id = $technics->save('technics')){
							$name = "".$type->name." ".$manufacturer->name." ".$model."";
							$alias = AppModel::createAlias('technics', 'alias', ''.$name.'', $id);
							$p = \R::load('technics', $id);
							$p->alias = $alias;	
							\R::store($p);
							if($row->size !=""){ $technics->editSizeTechnicsImport($id, $data); }
							if($row->size_back !=""){ $technics->editSizeBackTechnicsImport($id, $data); }
							if($row->size_alt !=""){ $technics->editSizeAltTechnicsImport($id, $data); }
							if($row->size_alt_back !=""){ $technics->editSizeAltBackTechnicsImport($id, $data); }
							AdminAuditLogger::log(2, 13, 'technics', (int)$id);
						}
					}
					
					// API IndexNow
					$indexnow = new PlaginsIndexnow();
					$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
					foreach($inw as $in){					
						$search_engine .= $indexnow->indexNowEngine($in->url, 'technics', $p->alias, $in->verification);
					}
				}
			$_SESSION['success'] = 'Техника добавлена';
			redirect();				
		}		
        $this->setMeta('Импорт техники');		
	}

/* AND TECHNICS */

/* COMPLETE */
	
	public function completeAction(){
		
        $complete = \R::getAll("SELECT * FROM plagins_complete");

        $this->setMeta('Комплекты из товаров');
        $this->set(compact('complete'));		
	}
	
	public function CompleteAddImageAction(){
        if(isset($_GET['upload'])){
            if($_POST['name'] == 'single'){
                $wmax = App::$app->getProperty('img_width');
                $hmax = App::$app->getProperty('img_height');
				$wmaxmini = App::$app->getProperty('mini_img_width');
                $hmaxmini = App::$app->getProperty('mini_img_height');
            }else{
                $wmax = App::$app->getProperty('gallery_width');
                $hmax = App::$app->getProperty('gallery_height');
				$wmaxmini = App::$app->getProperty('mini_gallery_width');
                $hmaxmini = App::$app->getProperty('mini_gallery_height');
            }
            $name = $_POST['name'];
            $complete = new PlaginsComplete();
            $complete->uploadImg($name, $wmax, $hmax, $wmaxmini, $hmaxmini);
        }
    }
	
	public function deleteCompleteAction(){
        $id = $this->getRequestID();        
        $complete = \R::load('plagins_complete', $id);
		@unlink(WWW . "/images/complete/baseimg/".$complete["img"]."");
		@unlink(WWW . "/images/complete/mini/".$complete["img"]."");
		\R::trash($complete);
		$gallery = \R::findOne('plagins_complete_gallery', 'complete_id = ?', [$id]);		
		@unlink(WWW . "/images/complete/gallery/".$gallery["img"]."");
        $del_gallery = \R::load('gallery', $gallery["id"]);
		\R::trash($del_gallery);
		
		
		$find_complete = \R::findAll('plagins_complete_product', 'product_id = ?', [$id]);
		foreach($find_complete as $compl) {
			$delete_complete = \R::load('plagins_complete_product', $compl->id);
			\R::trash($delete_complete);
		}
		
		// Yandex API IndexNow
		$verification_yandex = \ishop\App::options('option_verification_yandex');
		$client_yandex = new \GuzzleHttp\Client();
		$response_yandex = $client_yandex->request('GET', 'https://yandex.com/indexnow?url='.PATH.'/complete/'.$complete["alias"].'&key='.$verification_yandex.'');
		if($response_yandex->getStatusCode() == "200") { $status_code_yandex = "OK"; }else{ $status_code_yandex = $response_yandex->getBody(); }
		$yandex = "<br>Yandex IndexNow: ".$status_code_yandex."";
		
		// Bing API IndexNow
		$verification_bing = \ishop\App::options('option_verification_bing');
		$client_bing = new \GuzzleHttp\Client();
		$response_bing = $client_bing->request('GET', 'https://www.bing.com/indexnow?url='.PATH.'/complete/'.$complete["alias"].'&key='.$verification_bing.'');
		if($response_bing->getStatusCode() == "200") { $status_code_bing = "OK"; }else{ $status_code_bing = $response_bing->getBody(); }
		$bing = "<br>Bing IndexNow: ".$status_code_bing."";
		
		// API IndexNow
		$indexnow = new PlaginsIndexnow();
		$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
		foreach($inw as $in){					
			$search_engine .= $indexnow->indexNowEngine($in->url, 'complete', $complete["alias"], $in->verification);
		}
		
        $_SESSION['success'] = 'Комплект ID '.$id.' удален.'.$search_engine.'';
        redirect();
    }
	
	public function completeAddAction(){
        if(!empty($_POST)){
            $complete = new PlaginsComplete();
            $data = $_POST;
            $complete->load($data);
            $complete->getImg();

            if(!$complete->validate($data) || !$complete->checkUnique()){
                $complete->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $complete->save('plagins_complete')){
				$complete->saveGallery($id);
                $alias = AppModel::createAlias('plagins_complete', 'alias', $data["name"], $id);
                $p = \R::load('plagins_complete', $id);
                $p->alias = $alias;
                \R::store($p);
				$complete->editProductComplete($id, $data);
				
				// API IndexNow
				$indexnow = new PlaginsIndexnow();
				$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
				foreach($inw as $in){					
					$search_engine .= $indexnow->indexNowEngine($in->url, 'complete', $p["alias"], $in->verification);
				}
				
                $_SESSION['success'] = 'Комплект добавлен.'.$search_engine.'';
            }
            redirect();
        }

        $this->setMeta('Добавление комплекта');

    }
	
	public function completeEditAction(){	
		
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $complete = new PlaginsComplete();
            $data = $_POST;
            $complete->load($data);
            $complete->getImg();
            if(!$complete->validate($data)){
                $complete->getErrors();
                redirect();
            }
			
            if($complete->update('plagins_complete', $id)){
				
                $alias = AppModel::createAlias('plagins_complete', 'alias', $data["name"], $id);
				$complete->editProductComplete($id, $data);
                $complete = \R::load('plagins_complete', $id);
				if($data['alias']!=""){ $complete->alias = $data['alias'];}
				else{$complete->alias = $alias;}
                \R::store($complete);
				
				// API IndexNow
				$indexnow = new PlaginsIndexnow();
				$inw = \R::findAll('plagins_indexnow', 'hide = ?', ['show']);
				foreach($inw as $in){					
					$search_engine .= $indexnow->indexNowEngine($in->url, 'complete', $complete["alias"], $in->verification);
				}
				
                $_SESSION['success'] = 'Изменения сохранены.'.$search_engine.'';
                redirect();
            }
        }

		$id = $this->getRequestID();
        $complete = \R::load('plagins_complete', $id);
		App::$app->setProperty('parent_id', $complete->category_id);
		$gallery = \R::getCol('SELECT img FROM plagins_complete_gallery WHERE complete_id = ?', [$id]);
		$complete_product = \R::getAll('SELECT * FROM plagins_complete_product JOIN product ON product.id = plagins_complete_product.product_id WHERE plagins_complete_product.complete_id = ?', [$id]);
		
		$this->setMeta("Редактирование {$complete->name}");
        $this->set(compact('complete', 'gallery', 'complete_product'));
    }
	
		public function searchproductAction(){
		$request = 1;
		$code = $_GET['code'];
		
		if(isset($_POST['request'])){
			$request = $_POST['request'];
		}
		
		// Select2 data
		if($request == 1){
			$q = isset($_GET['q']) ? $_GET['q'] : '';
			$data['items'] = [];
			$searchproduct = \R::getAssoc('SELECT id, name FROM product WHERE name LIKE ? LIMIT 10', ["%{$q}%"]);
			if($searchproduct){
				$i = 0;
				foreach($searchproduct as $id => $name){
					$data['items'][$i]['id'] = $id;
					$data['items'][$i]['text'] = $name;					
					$i++;
				}
			}
			echo json_encode($data);
			
			die;
		}

		// Add element
		if($request == 2){
			
		   $html = "<div style=\"width: 100%;display:flex\">
						<div style=\"width:50%;padding: 0 0 0 10px;\"><select name=\"order_zakaz[".$code."][product_id]\" class=\"form-control searchproduct_".$code."\" data-id=\"".$code."\" data-placeholder=\"Выберите товар\"></select></div><div style=\"width:15%;padding: 0 0 0 10px;\" class=\"artic_".$code."\"></div><div style=\"width:15%;padding: 0 0 0 10px;\" class=\"price_".$code."\"></div><div style=\"width:10%;padding: 0 0 0 10px;\"><input name=\"order_zakaz[".$code."][quantity]\" type=\"text\" value=\"\" class=\"form-control orderquantity_".$code."\" placeholder=\"введите количество\" /></div><div style=\"width:10%;padding: 0 0 0 10px;\" class=\"itogpriceproduct_".$code."\"><input name=\"order_zakaz[".$code."]itog\" type=\"text\" value=\"\" class=\"form-control itog_".$code."\" placeholder=\"0\" disabled /></div>
					</div>";
		   echo $html;
		   exit;

		}
    }
	
	public function productpriceAction(){
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		if($id) {
			$product = \R::findOne('product', 'id = ?', [$id]);
		}else{
			$product->article = "";
		}
		echo json_encode(array('result1'=>''.$product->article.'', 'result2'=>''.$product->price.'', 'result3'=>''.$product->quantity.''));		
		die;
    }

/* AND COMPLETE*/

/* IMAGES PLAGINS RAZDEL */
	public function deleteGalleryAction(){
		$id = (int)($_POST['id'] ?? 0);
		$src = ImageFileStorage::safeName(PluginStoragePolicy::imageName((string)($_POST['src'] ?? '')));
		$plagins = strtolower((string)($_POST['plagins'] ?? ''));
        if(!$id || !$src){
            return;
        }
		$table = PluginStoragePolicy::table($plagins, '_gallery');
		if(\R::exec("DELETE FROM `$table` WHERE complete_id = ? AND img = ?", [$id, $src])){
			ImageFileStorage::delete(WWW . "/images/$plagins/gallery", $src);
            exit('1');
        }
        return;
    }
	
	public function DeleteBaseimgAction(){
		$id = (int)($_POST['id'] ?? 0);
		$src = ImageFileStorage::safeName(PluginStoragePolicy::imageName((string)($_POST['src'] ?? '')));
		$plagins = strtolower((string)($_POST['plagins'] ?? ''));
        if(!$id || !$src){
            return;
        }
		$table = PluginStoragePolicy::table($plagins);
		if(\R::exec("UPDATE `$table` SET img = '' WHERE id = ? AND img = ?", [$id, $src])){
			ImageFileStorage::delete(WWW . "/images/$plagins/baseimg", $src);
			ImageFileStorage::delete(WWW . "/images/$plagins/mini", $src);
			exit('1');
		}
        return;
    }
/* AND IMAGES PLAGINS RAZDEL */	

}
