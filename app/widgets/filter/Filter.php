<?php

namespace app\widgets\filter;

use ishop\Cache;

class Filter{

    public $groups;
    public $attrs;
    public $tpl;
    public $filter;
	public $ids;
	
    public function __construct($ids = null, $filter = null, $tpl = ''){
        $this->filter = $filter;
		$this->ids = $ids;
        $this->tpl = $tpl ?: __DIR__ . '/filter_tpl.php';
		if(!empty($ids)){
			$this->run($ids);
		}else{ $this->run(); }
    }

    protected function run($ids = null){
        $cache = Cache::instance();
		$groupCacheKey = $this->cacheKey('filter_group', $ids);
		$attributeCacheKey = $this->cacheKey('filter_attrs', $ids);
        $this->groups = $cache->get($groupCacheKey);
        if(!$this->groups){
			if(!empty($ids)){
				$this->groups = $this->getGroups($ids);
			}else{
				$this->groups = $this->getGroups();
			}
            $cache->set($groupCacheKey, $this->groups, 3600);
        }
        $this->attrs = $cache->get($attributeCacheKey);
        if(!$this->attrs){
			if(!empty($ids)){
				$this->attrs = self::getAttrs($ids);
			}else{
				$this->attrs = self::getAttrs();
			}
            $cache->set($attributeCacheKey, $this->attrs, 3600);
        }
        $filters = $this->getHtml();
        echo $filters;

    }

    private function cacheKey(string $prefix, mixed $ids = null): string
    {
		$normalisedIds = self::normaliseIds($ids);
		$scope = $normalisedIds === [] ? 'all' : hash('sha256', implode(',', $normalisedIds));
		return $prefix . ':' . $scope;
    }

    protected function getHtml(){
        ob_start();
        $filter = self::getFilter();
        if(!empty($filter)){
            $filter = explode(',', $filter);
        }
        require $this->tpl;
        return ob_get_clean();
    }

	public function getGroups($ids = null){
		$categoryIds = self::normaliseIds($ids);
		if($categoryIds !== []){
			return \R::getAssoc('SELECT attribute_group.id, attribute_group.title, attribute_category.group_id FROM attribute_group, attribute_category WHERE attribute_group.id = attribute_category.group_id AND attribute_category.category_id IN (' . \R::genSlots($categoryIds) . ') ORDER BY attribute_group.position', $categoryIds);
		}else{
			return \R::getAssoc('SELECT id, title FROM attribute_group ORDER BY attribute_group.position');
		}
    }

    protected static function getAttrs($ids = null){
		$categoryIds = self::normaliseIds($ids);
		if($categoryIds !== []){
			$data = \R::getAssoc('SELECT attribute_value.id, attribute_value.value, attribute_value.attr_group_id FROM attribute_value, attribute_product, product WHERE attribute_value.id = attribute_product.attr_id AND product.id = attribute_product.product_id AND product.category_id IN (' . \R::genSlots($categoryIds) . ') GROUP BY attribute_value.id, attribute_value.value, attribute_value.attr_group_id ORDER BY attribute_value.value', $categoryIds);
        }else{
			$data = \R::getAssoc('SELECT attribute_value.id, attribute_value.value, attribute_value.attr_group_id FROM attribute_value, attribute_product WHERE attribute_value.id = attribute_product.attr_id GROUP BY attribute_value.id, attribute_value.value, attribute_value.attr_group_id ORDER BY attribute_value.value');
        }
		$attrs = [];
        foreach($data as $k => $v){
            $attrs[$v['attr_group_id']][$k] = $v['value'];
        }
        return $attrs;
    }

    /** @return list<int> */
    private static function normaliseIds(mixed $ids): array
    {
		$values = is_array($ids) ? $ids : explode(',', (string)$ids);
		$values = array_values(array_unique(array_filter(
			array_map('intval', $values),
			static fn(int $value): bool => $value > 0
		)));
		sort($values, SORT_NUMERIC);
		return $values;
    }

    public static function getFilter(){
        $filter = null;
        if(!empty($_GET['filter'])){
            $filter = preg_replace("#[^\d,]+#", '', $_GET['filter']);
			//$filter=str_replace("%2c",",",$_GET['filter']);
			//$filter = rawurldecode($filter);
            $filter = trim($filter, ',');
        }
        return $filter;
    }

    public static function getCountGroups($filter){
        $filters = explode(',', $filter);
        $cache = Cache::instance();
        $attrs = $cache->get('filter_attrs:all');
        if(!$attrs){
            $attrs = self::getAttrs();
			$cache->set('filter_attrs:all', $attrs, 3600);
        }
        $data = [];
        foreach($attrs as $key => $item){
            foreach($item as $k => $v){
                if(in_array($k, $filters)){
                    $data[] = $key;
                    break;
                }
            }
        }
        return count($data);
    }

}
