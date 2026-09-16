<?php

namespace app\models;

use ishop\App;

class Cart extends AppModel {
	
	public $rules = [
        'required' => [            
            ['name'],
            ['email'],
			['telefon'],
        ],
        'email' => [
            ['email'],
        ],
		'telefon' => [
            ['telefon'],
        ]
    ];

    public function addToCart($product, $qty, $max, $mod = null){
        if(!isset($_SESSION['cart.currency'])){
            $_SESSION['cart.currency'] = App::$app->getProperty('currency');
        }
		$qty = max(1, (int)$qty);
		$max = max(0, (int)$max);
		if($max<1)return;
		$currencyValue = (float)($_SESSION['cart.currency']['value'] ?? 1);
        if($mod){
            $ID = "{$product->id}-{$mod->id}";
            $name = "{$product->name} ({$mod->name_modification})";
            $price = $mod->price;
			$article = $mod->article;
			$unit = $mod->unit;
			$weight = (float)($mod->weight ?? 0);
			$volume = (float)($mod->volume ?? 0);
			$category_id = (int)($mod->category_id ?? $product->category_id ?? 0);
			$model = (string)($mod->model ?? $product->model ?? '');
        }else{
            $ID = $product->id;
            $name = $product->name;
            $price = $product->price;
			$article = $product->article;
			$unit = $product->unit;
			$weight = (float)($product->weight ?? 0);
			$volume = (float)($product->volume ?? 0);
			$category_id = (int)($product->category_id ?? 0);
			$model = (string)($product->model ?? '');
        }
		$currentQuantity = (int)($_SESSION['cart'][$ID]['qty'] ?? 0);
		$qty = min($qty, max(0, $max - $currentQuantity));
		if ($qty < 1) {
			return;
		}
        if(isset($_SESSION['cart'][$ID])){
            $_SESSION['cart'][$ID]['qty'] += $qty;
        }else{
            $_SESSION['cart'][$ID] = [
                'qty' => $qty,
				'unit' => $unit,
				'weight' => $weight,
				'volume' => $volume,
				'max' => $max,
                'name' => $name,
				'article' => $article,
                'alias' => $product->alias,
				'price' => $price * $currencyValue,
                'img' => $product->img,
				'category_id' => $category_id,
				'model' => $model,
            ];
        }
        self::recalculateTotals();
    }
	
/*	public function addToCartInput($product, $qty, $max,  $mod = null){
        if(!isset($_SESSION['cart.currency'])){
            $_SESSION['cart.currency'] = App::$app->getProperty('currency');
        }
        if($mod){
            $ID = "{$product->id}-{$mod->id}";
            $name = "{$product->name} ({$mod->name_modification})";
            $price = $mod->price;
			$article = $mod->article;
			$unit = $mod->unit;
			$weight = $mod->weight;
			$volume = $mod->volume;
        }else{
            $ID = $product->id;
            $name = $product->name;
            $price = $product->price;
			$article = $product->article;
			$unit = $product->unit;
			$weight = $product->weight;
			$volume = $product->volume;
        }
        if(isset($_SESSION['cart'][$ID])){			
			$_SESSION['cart'][$ID]['qty'] = $qty;
			
        }else{
            $_SESSION['cart'][$ID] = [
                'qty' => $qty,
				'unit' => $unit,
				'weight' => $weight,
				'volume' => $volume,
				'max' => $max,
                'name' => $name,
				'article' => $article,
                'alias' => $product->alias,
                'price' => $price * $_SESSION['cart.currency']['value'],
                'img' => $product->img,
            ];
			
			
        }
        $_SESSION['cart.qty'] = $_SESSION['cart.qty'] - $_SESSION['cart'][$ID]['qty'];            
		$_SESSION['cart.qty'] = $_SESSION['cart.qty'] + $qty;
		
        $_SESSION['cart.sum'] = isset($_SESSION['cart.sum']) ? $_SESSION['cart.sum'] : $qty * ($price * $_SESSION['cart.currency']['value']);
		$_SESSION['cart.weight'] = isset($_SESSION['cart.weight']) ? $_SESSION['cart.weight'] : $qty * $weight;
		$_SESSION['cart.volume'] = isset($_SESSION['cart.volume']) ? $_SESSION['cart.volume'] : $qty * $volume;
    }*/

    public function deleteItem($id){
        unset($_SESSION['cart'][$id]);
        self::recalculateTotals();
    }
	
	public function pluscartItem($id){
        $qtyPlus = $_SESSION['cart'][$id]['qty'];
		$max = (int)($_SESSION['cart'][$id]['max'] ?? 0);
		if ($max > 0 && $qtyPlus >= $max) {
			return;
		}
		$_SESSION['cart'][$id]['qty'] = $_SESSION['cart'][$id]['qty'] + 1;
        self::recalculateTotals();
    }
	
	public function minuscartItem($id){
		$_SESSION['cart'][$id]['qty'] = $_SESSION['cart'][$id]['qty'] - 1;
		if($_SESSION['cart'][$id]['qty'] <= 0){
			unset($_SESSION['cart'][$id]);
		}
        self::recalculateTotals();
    }

    /** Keep all derived values authoritative even after a partial/legacy session. */
    public static function recalculateTotals(): void {
        $qty = 0;
        $sum = 0.0;
        $weight = 0.0;
        $volume = 0.0;
        foreach ((array)($_SESSION['cart'] ?? []) as $item) {
            $itemQty = max(0, (int)($item['qty'] ?? 0));
            $qty += $itemQty;
            $sum += $itemQty * (float)($item['price'] ?? 0);
            $weight += $itemQty * (float)($item['weight'] ?? 0);
            $volume += $itemQty * (float)($item['volume'] ?? 0);
        }
        $_SESSION['cart.qty'] = $qty;
        $_SESSION['cart.sum'] = round($sum, 2);
        $_SESSION['cart.weight'] = round($weight, 3);
        $_SESSION['cart.volume'] = round($volume, 3);
    }

    public static function recalc($curr){
        if(isset($_SESSION['cart.currency'])){
            if($_SESSION['cart.currency']['base']){
                $_SESSION['cart.sum'] *= $curr->value;
            }else{
                $_SESSION['cart.sum'] = $_SESSION['cart.sum'] / $_SESSION['cart.currency']['value'] * $curr->value;
            }
			foreach((array)($_SESSION['cart'] ?? []) as $k => $v){
                if($_SESSION['cart.currency']['base']){
                    $_SESSION['cart'][$k]['price'] *= $curr->value;
                }else{
                    $_SESSION['cart'][$k]['price'] = $_SESSION['cart'][$k]['price'] / $_SESSION['cart.currency']['value'] * $curr->value;
                }
            }
            foreach($curr as $k => $v){
                $_SESSION['cart.currency'][$k] = $v;
            }
            self::recalculateTotals();
        }
    }

}
