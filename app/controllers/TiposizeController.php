<?php

namespace app\controllers;

use app\models\Breadcrumbs;
use ishop\App;
use ishop\libs\Pagination;

class TiposizeController extends AppController {

    public function viewAction(){
        $alias = $this->route['alias'];
        $find = \R::findOne('attribute_value', "alias = ? AND hide = 'show' AND attr_group_id IN (SELECT id FROM attribute_group WHERE LOWER(url_params) = 'tiposize')", [$alias]);
        if (!$find) throw new \Exception('Страница не найдена', 404);
        $breadcrumbs = Breadcrumbs::getBreadcrumbs($find->id);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perpage = max(1, (int)App::$app->getProperty('pagination'));
        $sql_sort = match ((string)($_GET['sort'] ?? '')) {
            'price' => 'ORDER BY p.price ASC, p.id ASC',
            'nal' => 'ORDER BY p.quantity DESC, p.id ASC',
            'rate' => 'ORDER BY p.hit DESC, p.id ASC',
            default => "ORDER BY FIELD(p.stock_status_id, 1,3,2,0), p.name ASC, p.id ASC",
        };
        $predicate = "p.hide = 'show' AND EXISTS (SELECT 1 FROM attribute_product ap WHERE ap.product_id = p.id AND ap.attr_id = ?)";
        $total = (int)\R::getCell('SELECT COUNT(*) FROM product p WHERE '.$predicate, [$find->id]);
        $products = [];
        $ids = [];
        $pagination = new Pagination($page, $perpage, $total);
        if ($total > 0) {
            $start = $pagination->getStart();
            $products = \R::getAll("SELECT p.* FROM product p WHERE $predicate $sql_sort LIMIT $start, $perpage", [$find->id]);
        }
        if ($total === 0) $this->meta['robots'] = 'noindex, follow';
        //InSEO
		$params = \R::findOne('attribute_group', "id = ?", [$find["attr_group_id"]]);
		$inseo = \R::findOne('plagins_inseo', "tip = ? AND category_id = ? AND hide = 'show'", ['attribute_group', $find["attr_group_id"]]);
        $expandSeo = static fn(string $text): string => \ishop\App::seoreplacefilter($text, $find->id);
        $title = \app\services\FilterSeo::resolve((string)$find->title, (string)($inseo->title ?? ''), $expandSeo);
        $description = \app\services\FilterSeo::resolve((string)$find->description, (string)($inseo->description ?? ''), $expandSeo);
        $keywords = \app\services\FilterSeo::resolve((string)$find->keywords, (string)($inseo->keywords ?? ''), $expandSeo);

		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["alias"]){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
		$this->setMeta($title, $description, $keywords, '' . App::$app->getProperty('shop_name') . '', ''.PATH.'/images/' . App::$app->getProperty('og_logo') . '', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
				

        $this->set(compact('find', 'products', 'breadcrumbs', 'pagination', 'total', 'ids', 'params', 'inseo'));

    }
	public function indexAction(){
        $type = \R::findOne('attribute_group', "LOWER(url_params) = 'tiposize'");
        if (!$type) throw new \Exception('Страница не найдена', 404);
        $sizes = \app\services\TireSizeDirectory::load();
        $query = mb_substr(is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '', 0, 100);
        $stockOnly = (string)($_GET['stock'] ?? '') === '1';
        $matched = count(array_filter($sizes, static fn($size) => \app\services\TireSizeDirectory::matches($size, $query, $stockOnly)));
        if ($query !== '' || $stockOnly) $this->meta['robots'] = 'noindex, follow';
        $this->setMeta($type->seo_title, $type->seo_description, $type->seo_keywords, App::$app->getProperty('shop_name'), PATH.'/images/'.App::$app->getProperty('og_logo'), PATH.'/tiposize');
        $this->set(compact('sizes', 'type', 'query', 'stockOnly', 'matched'));
    }
}