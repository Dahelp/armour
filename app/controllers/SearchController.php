<?php

namespace app\controllers;

use ishop\App;
use app\services\TireSizeNotation;
use ishop\libs\Pagination;

class SearchController extends AppController{

    public function typeaheadAction(){
        if($this->isAjax()){
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            $query = $this->normaliseQuery($_GET['query'] ?? '');
            if($query){
                $like = '%' . TireSizeNotation::searchKey($query) . '%';
                $nameSearch = TireSizeNotation::searchExpression("CONCAT_WS(' ', name, article)", $query);
                $attributeSearch = TireSizeNotation::searchExpression('av.value', $query);
                $crossSearch = TireSizeNotation::searchExpression("CONCAT_WS(' ', plagins_cross.cross_name, plagins_cross.cross_abbreviated_name, plagins_cross_vendor.name)", $query);
                $products = \R::getAll(
                    "SELECT id, name, img, price, alias FROM (
                        SELECT id, name, img, price, alias
                        FROM product
                        WHERE hide = 'show' AND (
                            {$nameSearch} LIKE ?
                            OR EXISTS (
                                SELECT 1
                                FROM attribute_product ap
                                INNER JOIN attribute_value av ON av.id = ap.attr_id
                                WHERE ap.product_id = product.id
                                  AND {$attributeSearch} LIKE ?
                            )
                        )
                        UNION
                        SELECT product.id, product.name, product.img, product.price, product.alias
                        FROM product
                        JOIN plagins_cross ON product.id = plagins_cross.product_id
                        LEFT JOIN plagins_cross_vendor ON plagins_cross_vendor.id = plagins_cross.vendor_id
                        WHERE product.hide = 'show'
                          AND {$crossSearch} LIKE ?
                    ) product LIMIT 15",
                    [$like, $like, $like]
                );
                header('Content-Type: application/json; charset=utf-8');
                foreach ($products as &$product) {
                    $product['name'] = TireSizeNotation::display((string)$product['name']);
                }
                unset($product);
                echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            }
        }
        die;
    }

    public function indexAction(){
		
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = App::$app->getProperty('pagination');	
		if(empty($_GET['s'])){
            throw new \Exception('Страница не найдена', 404);
        }
        $query = $this->normaliseQuery($_GET['s']);
        if ($query === '') {
            throw new \Exception('Страница не найдена', 404);
        }
        $like = '%' . TireSizeNotation::searchKey($query) . '%';
        $nameSearch = TireSizeNotation::searchExpression("CONCAT_WS(' ', name, article)", $query);
        $attributeSearch = TireSizeNotation::searchExpression('av.value', $query);
        $crossSearch = TireSizeNotation::searchExpression("CONCAT_WS(' ', plagins_cross.cross_name, plagins_cross.cross_abbreviated_name, plagins_cross_vendor.name)", $query);
		$total = (int)\R::getCell(
            "SELECT COUNT(*) FROM (
                SELECT id FROM product
                WHERE hide = 'show' AND (
                    {$nameSearch} LIKE ?
                    OR EXISTS (
                        SELECT 1
                        FROM attribute_product ap
                        INNER JOIN attribute_value av ON av.id = ap.attr_id
                        WHERE ap.product_id = product.id
                          AND {$attributeSearch} LIKE ?
                    )
                )
                UNION
                SELECT product.id FROM product
                JOIN plagins_cross ON product.id = plagins_cross.product_id
                LEFT JOIN plagins_cross_vendor ON plagins_cross_vendor.id = plagins_cross.vendor_id
                WHERE product.hide = 'show'
                  AND {$crossSearch} LIKE ?
            ) product",
            [$like, $like, $like]
        );
		$pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();
		
        if($query){
			$products = \R::getAll(
                "SELECT * FROM (
                    SELECT id, name, price, alias, hit, new_product, sale, img, category_id, article, quantity, stock_status_id, brand_id
                    FROM product
                    WHERE hide = 'show' AND (
                        {$nameSearch} LIKE ?
                        OR EXISTS (
                            SELECT 1
                            FROM attribute_product ap
                            INNER JOIN attribute_value av ON av.id = ap.attr_id
                            WHERE ap.product_id = product.id
                              AND {$attributeSearch} LIKE ?
                        )
                    )
                    UNION
                    SELECT product.id, product.name, product.price, product.alias, product.hit, product.new_product,
                           product.sale, product.img, product.category_id, product.article, product.quantity, product.stock_status_id, product.brand_id
                    FROM product
                    JOIN plagins_cross ON product.id = plagins_cross.product_id
                    LEFT JOIN plagins_cross_vendor ON plagins_cross_vendor.id = plagins_cross.vendor_id
                    WHERE product.hide = 'show'
                      AND {$crossSearch} LIKE ?
                ) product
                ORDER BY FIELD(`stock_status_id`, 1, 3, 2, 0), name ASC
                LIMIT {$start}, {$perpage}",
                [$like, $like, $like]
            );
        
        }
        $this->setMeta('Поиск по: ' . h($query));
		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["alias"]){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
		$this->setMeta('Поиск по: ' . h($query), '', '', '' . App::$app->getProperty('shop_name') . '', ''.PATH.'/images/' . App::$app->getProperty('og_logo') . '', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
        $this->set(compact('products', 'query', 'pagination', 'total'));
    }

    private function normaliseQuery(string $query): string
    {
        $query = preg_replace('/\s+/u', ' ', trim($query));
        $query = preg_replace('/(?<=\d),(?=\d)/u', '.', (string)$query);
        return mb_substr(TireSizeNotation::display((string)$query), 0, 100, 'UTF-8');
    }

}
