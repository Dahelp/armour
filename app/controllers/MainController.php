<?php

namespace app\controllers;

use ishop\Cache;
use ishop\App;


class MainController extends AppController {

    public function indexAction(){
        $brands = \R::find('brand', 'LIMIT 3');		
        $hits = \R::find('product', "hit = '1' AND hide = 'show' LIMIT 4");
		$sales = \R::find('product', "sale = '1' AND hide = 'show' LIMIT 4");
		$new_products = \R::find('product', "new_product = '1' AND hide = 'show' LIMIT 4");
		$articles = \R::getAll(
			"SELECT c.* FROM contents c INNER JOIN content_type ct ON ct.id = c.type_id WHERE LOWER(ct.param_url) = ? AND c.hide = 'show' ORDER BY c.date_post DESC, c.id DESC LIMIT 4",
			['articles']
		);
		$news = \R::getAll(
			"SELECT c.* FROM contents c INNER JOIN content_type ct ON ct.id = c.type_id WHERE LOWER(ct.param_url) = ? AND c.hide = 'show' ORDER BY c.date_post DESC, c.id DESC LIMIT 4",
			['news']
		);
		$main_title_value = 'Промышленные шины для погрузчиков и спецтехники — ТехШина';
		$main_desc_value = 'Промышленные шины для вилочных, фронтальных и мини-погрузчиков, экскаваторов и спецтехники. Подбор по размеру и модели техники, доставка по России.';
		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["controller"] == "Main"){ $path_controller = ""; }
		if(isset($this->route["alias"])){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
        $this->setMeta($main_title_value, $main_desc_value, '', 'ТехШина — промышленные шины и комплектующие', ''.PATH.'/images/' . App::$app->getProperty('og_logo') . '', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
        $this->set(compact('brands', 'hits', 'sales', 'new_products', 'articles', 'news'));
    }	
}
