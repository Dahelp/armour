<?php

namespace app\controllers\admin;

use app\models\admin\Product;
use ishop\App;
use app\models\AppModel;
use app\services\RemoteXmlDownloader;
use app\services\UrlAliasRepository;

class ImportController extends AppController {

    public function indexAction(){
		$ftp_server = App::$app->getProperty('ftp_server');
		$ftp_login = App::$app->getProperty('ftp_login');
		$ftp_pass = App::$app->getProperty('ftp_pass');
		$ftp_port = App::$app->getProperty('ftp_port');
		if(!empty($_POST)){
			$conn_id = @ftp_connect(''.$ftp_server.'', ''.$ftp_port.'', 5); // коннектимся к серверу FTP
			if($conn_id) // если соединение с сервером прошло удачно, продолжаем
			{
				$login_result = @ftp_login($conn_id, ''.$ftp_login.'', ''.$ftp_pass.''); // вводим свои логин и пароль для FTP
				if($login_result) // если сервер принял логин пароль, идем дальше
				{
					// теперь нужно поиграть с пассивным режимом, включить его или выключить(TRUE, FALSE)
					// если дальнейшие функции ftp будут работать не правильно, пробуйте менять этот параметр (TRUE или FALE)
                   
					ftp_pasv ($conn_id, TRUE); // в данном случае пассивный режим включен
					
					$path = (new RemoteXmlDownloader())->download((string)($_POST['url_file'] ?? ''), WWW . '/xml');
					$xml = simplexml_load_file($path, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
					@unlink($path);
					if ($xml === false) {
						throw new \RuntimeException('Получен некорректный XML-файл.');
					}
					
					if ( $_POST['url_file'])
					{ 

						foreach ( $xml->shop->offers->offer as $row )  
						{
							$product = new Product();
						
							$product->attributes['article'] = $row['id'];
							$product->attributes['price'] = $row->price;
							$product->attributes['price_rrs'] = '0';
							$product->attributes['category_id'] = $_POST["category_id"];	
							$product->attributes['name'] = $row->name;    
							$product->attributes['model'] = $row->model;
							$brand_id = \R::findOne('brand', 'name = ?', [$row->vendor]);
							$product->attributes['brand_id'] = $brand_id['id'];
							$product->attributes['quantity'] = $row->quantity;
							$product->attributes['hide'] = 'show';
							$product->attributes['hit'] = '0';
							$product->attributes['new_product'] = '0';
							$product->attributes['sale'] = '0';
							$product->attributes['opt_price'] = '0';
							$product->attributes['date'] = date("Y-m-d");
							$product->attributes['content'] = $row->opisanie;
							$product->attributes['title'] = $row->title;
							$product->attributes['description'] = $row->description;
							$product->attributes['keywords'] = $row->keywords;
							if($row->quantity>0) { $product->attributes['stock_status_id'] = "1"; } else { $product->attributes['stock_status_id'] = "0"; }
							if(!$product->checkUniqueArticle()){}
							else{													
								$wmax = App::$app->getProperty('img_width');
								$hmax = App::$app->getProperty('img_height');
								$wmaxmini = App::$app->getProperty('mini_img_width');
								$hmaxmini = App::$app->getProperty('mini_img_height');
								$product->uploadImgXml($row->picture, $row->name, $wmax, $hmax, $wmaxmini, $hmaxmini);
								$product->getImg();
								if($id = $product->save('product')){									
									$alias = strtolower($row->url);
									$p = \R::load('product', $id);
									$p->alias = $alias;
									(new UrlAliasRepository())->save($alias, 'Product', (int)$id);
									\R::store($p);
									$isNonEmptyArray = $product->traverseArray($row->param);
									if($isNonEmptyArray){ 
										$attributeRows = [];
										$attributeBindings = [];
										foreach ( $row->param as $params ) {							

											$att = \R::findOne('attribute', 'attribute_name = ?', [$params["name"]]);
											if ($att) {
												$attributeRows[] = '(?, ?, ?, ?)';
												array_push($attributeBindings, (int)$id, (int)$att->id, (int)$att->attribute_group_id, (string)$params);
											}
											
										}
										if ($attributeRows !== []) {
											\R::exec('INSERT IGNORE INTO product_attribute (product_id, attribute_id, attribute_group_id, attribute_text) VALUES ' . implode(',', $attributeRows), $attributeBindings);
										}
									}
									$_SESSION['success'] = 'Товар добавлен';
								}
							}
						}
					}
				}
				else { $_SESSION['error'] = 'Пароль или логин не подошли!'; }
			}
			else { $_SESSION['error'] = 'Не подключились'; }
			if ($conn_id) {
				ftp_close($conn_id); // и закрываем коннект с FTP
			}
		}
		
        $this->setMeta('Импорт товаров');

    }
	
}
