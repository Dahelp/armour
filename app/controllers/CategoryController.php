<?php

namespace app\controllers;

use app\models\Breadcrumbs;
use app\models\Category;
use app\services\CatalogListingLoader;
use app\widgets\filter\Filter;
use ishop\App;
use ishop\libs\Pagination;

class CategoryController extends AppController {

    public function viewAction(){
        $alias = $this->route['alias'];
		//$up_registr = App::upRegistrLetter($alias);
        $category = \R::findOne('category', 'alias = ?', [$alias]);
        if(!$category){
            throw new \Exception('Страница не найдена', 404);
        }

        $breadcrumbs = Breadcrumbs::getBreadcrumbs($category->id, NULL, $alias, mb_strtolower($this->route["controller"]));

        $cat_model = new Category();
        $categoryIds = $cat_model->getIdList((int)$category->id);
        $ids = implode(',', $categoryIds);
        $page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $perpage = max(1, (int)App::$app->getProperty('pagination'));

        $sql_part = '';
		$queryBindings = $categoryIds;
		
        if(!empty($_GET['filter'])){
            /*
            SELECT `product`.*  FROM `product`  WHERE category_id IN (6) AND id IN
            (
            SELECT product_id FROM attribute_product WHERE attr_id IN (1,5) GROUP BY product_id HAVING COUNT(product_id) = 2
            )
            */
            $filter = Filter::getFilter();
			
            if($filter){               
				$filterIds = array_values(array_unique(array_filter(array_map('intval', explode(',', $filter)))));
				$cnt = Filter::getCountGroups($filter); //без перезагрузки
                if ($filterIds !== [] && $cnt > 0) {
					$sql_part = 'AND product.id IN (SELECT product_id FROM attribute_product WHERE attr_id IN ('
						. \R::genSlots($filterIds)
						. ') GROUP BY product_id HAVING COUNT(DISTINCT attr_id) = ' . (int)$cnt . ')';
					$queryBindings = array_merge($queryBindings, $filterIds);
				}
            }
			
        }

		$sqlSortOptions = [
			'price' => 'ORDER BY price ASC',
			'nal' => 'ORDER BY stock_status_id DESC',
			'rate' => 'ORDER BY hit DESC',
		];
		$sql_sort = $sqlSortOptions[(string)($_GET['sort'] ?? '')]
			?? 'ORDER BY FIELD(`stock_status_id`, 1,3,2,0), name ASC';

        $categorySlots = \R::genSlots($categoryIds);
        $condition = "hide = 'show' AND category_id IN ($categorySlots) $sql_part";
        $total = \R::count('product', $condition, $queryBindings);
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();

        $products = \R::find('product', "$condition $sql_sort LIMIT $start, $perpage", $queryBindings);
		[$productAttributes, $brands] = (new CatalogListingLoader())->load($products);
		$subcategories = \R::getAll('SELECT id, alias, img, name FROM category WHERE parent_id = ?', [$category->id]);
		$inseoProd = \R::findOne('plagins_inseo', "tip = ? AND category_id = ? AND hide = 'show'", ['product', $category->id]);
		
        //InSEO
		$inseo = \R::findOne('plagins_inseo', "tip = ? AND category_id = ? AND hide = 'show'", ['category', $category->id]);
		if(!empty($inseo->title)) {
			$title = \ishop\App::seoreplace($inseo->title, $category->id);
		}else{ $title = $category->title; }
		if(!empty($inseo->description)) {
			$description = \ishop\App::seoreplace($inseo->description, $category->id);
		}else{ $description = $category->description; }
		if(!empty($inseo->keywords)) {
			$keywords = \ishop\App::seoreplace($inseo->keywords, $category->id);
		}else{ $keywords = $category->keywords; }
		/*SEO*/
		
        if($this->isAjax()){
            $this->loadView('filter', compact('products', 'total', 'pagination', 'ids', 'inseo', 'inseoProd', 'filter', 'attr_id', 'cnt', 'alias', 'category', 'productAttributes', 'brands'));
        }
		
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["alias"]){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }		
		$this->setMeta($title, $description, $keywords, '' . App::$app->getProperty('shop_name') . '', ''.PATH.'/images/' . App::$app->getProperty('og_logo') . '', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
        $this->set(compact('filter', 'products', 'breadcrumbs', 'pagination', 'total', 'category', 'ids', 'inseo', 'inseoProd', 'alias', 'subcategories', 'productAttributes', 'brands'));
    }

}
