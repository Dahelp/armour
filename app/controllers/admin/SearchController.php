<?php

namespace app\controllers\admin;
use ishop\App;
use ishop\libs\Pagination;

class SearchController extends AppController{

    public function typeaheadAction(){
        if($this->isAjax()){
            $query = preg_replace('/\s+/u', ' ', trim((string)($_GET['query'] ?? '')));
            $query = mb_substr((string)$query, 0, 100, 'UTF-8');
            if($query){
                $like = "%{$query}%";
                $products = \R::getAll(
                    "SELECT id, name FROM (
                        SELECT id, name FROM product WHERE concat(name, article) LIKE ?
                        UNION
                        SELECT product.id, product.name FROM product
                        JOIN plagins_cross ON product.id = plagins_cross.product_id
                        WHERE concat(plagins_cross.cross_name, plagins_cross.cross_abbreviated_name) LIKE ?
                    ) product LIMIT 15",
                    [$like, $like]
                );
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            }
        }
        die;
    }
}
