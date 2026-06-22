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
		$articles = \R::find('contents', "type_id = '9' AND hide = 'show' ORDER BY id DESC LIMIT 4");
		$main_title = \R::findOne('options', "tip = 'seo' AND alt_name = 'option_name'" );
		$main_desc = \R::findOne('options', "tip = 'seo' AND alt_name = 'option_description'" );
		$main_keywords = \R::findOne('options', "tip = 'seo' AND alt_name = 'option_keywords'" );
		$main_title_value = $main_title->znachenie ?? App::$app->getProperty('shop_name');
		$main_desc_value = $main_desc->znachenie ?? App::$app->getProperty('shop_description');
		$main_keywords_value = $main_keywords->znachenie ?? '';
		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["controller"] == "Main"){ $path_controller = ""; }
		if(isset($this->route["alias"])){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
        $this->setMeta($main_title_value, $main_desc_value, $main_keywords_value, '' . App::$app->getProperty('shop_name') . '', ''.PATH.'/images/' . App::$app->getProperty('og_logo') . '', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
        $this->set(compact('brands', 'hits', 'sales', 'new_products', 'main_title', 'main_desc', 'main_keywords', 'articles'));
    }	
}
