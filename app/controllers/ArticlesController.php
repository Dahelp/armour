<?php

namespace app\controllers;

use ishop\App;
use ishop\libs\Pagination;

class ArticlesController extends AppController {

    public function viewAction(){
		
		$alias = $this->route['alias'];
		$find = \R::findOne('contents', 'alias = ? AND hide IN (?, ?)', [$alias, 'show', 'lock']);
		if(!$find){
            throw new \Exception("Страница не найдена", 404);
        }
		$type = \R::findOne('content_type', 'id = ?', [$find->type_id]);
		if (!$type || strtolower((string)$type->param_url) !== 'articles') {
			throw new \Exception("Страница не найдена", 404);
		}

		// связанные товары
        $related = \R::getAll("SELECT * FROM content_related JOIN product ON product.id = content_related.related_id WHERE content_related.content_id = ?", [$find->id]);
		
		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if(isset($this->route["alias"]) && $this->route["alias"] !== ''){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
		if($find->img){$find_img = "".PATH."/images/contents/baseimg/".$find->img.""; }else{ $find_img = "".PATH."/images/".App::$app->getProperty('og_logo').""; }
		$this->setMeta($find->title, $find->description, $find->keywords, '' . App::$app->getProperty('shop_name') . '', ''.$find_img.'', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
		
        $this->set(compact('find', 'type', 'related'));
    }
	public function indexAction(){
		$type = \R::findOne('content_type', 'LOWER(param_url) = ?', ['articles']);
		
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = App::$app->getProperty('pagination');
		
		if (!$type) {
			throw new \Exception("Раздел не найден", 404);
		}
		$total = \R::count('contents', 'hide = ? AND type_id = ?', ['show', (int)$type->id]);
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();
		
		$conts = \R::findAll('contents', 'hide = ? AND type_id = ? ORDER BY date_post DESC LIMIT ?, ?', ['show', (int)$type->id, $start, $perpage]);

		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["alias"]){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
		$this->setMeta($type->title, $type->description, $type->keywords, '' . App::$app->getProperty('shop_name') . '', ''.PATH.'/images/' . App::$app->getProperty('og_logo') . '', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
		
        $this->set(compact('conts', 'type', 'pagination'));
	}

}
