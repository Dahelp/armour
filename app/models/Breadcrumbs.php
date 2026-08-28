<?php

namespace app\models;

use ishop\App;

class Breadcrumbs{

    public static function getBreadcrumbs($category_id, $bname = '', $alias_active = '', $controller = ''){
        $cats = App::$app->getProperty('cats');
        $breadcrumbs_array = self::getParts($cats, $category_id);

        if($breadcrumbs_array){			
			$breadcrumbs = "<a href='" . PATH . "'>Главная</a><span class='breadcrumb-separator'> / </span><a href='" . PATH . "/catalog'>Каталог</a><span class='breadcrumb-separator'> / </span>";
			$categoryTypes = [];
			foreach ($cats as $category) {
				$categoryTypes[(string)$category['alias']] = (int)($category['type_id'] ?? 0);
			}
            $i=2;
			foreach($breadcrumbs_array as $alias => $name){
				$position = $i+1;
				$pos = $i+1;
				if(($categoryTypes[$alias] ?? 0) === 1){
					if($alias_active != $alias){
						$breadcrumbs .= "<a href='" . PATH . "/{$alias}'><span itemprop='name'>{$name}</span></a><span class='breadcrumb-separator'> / </span>";
					}else{
						$breadcrumbs .= "{$name}";
					}
				}else{
					if($alias_active != $alias){
						$breadcrumbs .= "<a href='" . PATH . "/{$alias}'>{$name}</a><span class='breadcrumb-separator'> / </span>";
					}else{
						$breadcrumbs .= "{$name}";
					}
				}
				$i++;
            }
        }else{
			if($bname){
				$breadcrumbs = "<a itemprop='item' class='text-nowrap' href='" . PATH . "'>Главная</a><span class='breadcrumb-separator'> / </span><a itemprop='item' class='text-nowrap' href='" . PATH . "/catalog'>Каталог</a>";
			}else{
				$breadcrumbs = "<a itemprop='item' class='text-nowrap' href='" . PATH . "'>Главная</a><span class='breadcrumb-separator'> / </span>Каталог";
			}
		}
        if($bname){
			$pos = $pos+1;
            $breadcrumbs .= "$bname";
        }
        return $breadcrumbs;
    }

    public static function getParts($cats, $id){
        if(!$id) return false;
        $breadcrumbs = [];
        foreach($cats as $k => $v){
            if(isset($cats[$id])){
                $breadcrumbs[$cats[$id]['alias']] = $cats[$id]['name'];
                $id = $cats[$id]['parent_id'];
            }else break;
        }
        return array_reverse($breadcrumbs, true);
    }

}
