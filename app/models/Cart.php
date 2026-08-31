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
        if($mod){
            $ID = "{$product->id}-{$mod->id}";
            $name = "{$product->name} ({$mod->name_modification})";
            $price = $mod->price;
			$article = $mod->article;
			$unit = $mod->unit;
			$weight = $mod->weight;
			$volume = $mod->volume;
			$category_id = $mod->category_id;
			$model = $mod->model;
        }else{
            $ID = $product->id;
            $name = $product->name;
            $price = $product->price;
			$article = $product->article;
			$unit = $product->unit;
			$weight = $product->weight;
			$volume = $product->volume;
			$category_id = $product->category_id;
			$model = $product->model;
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
                'price' => $price * $_SESSION['cart.currency']['value'],
                'img' => $product->img,
				'category_id' => $product->category_id,
				'model' => $product->model,
            ];
        }
        $_SESSION['cart.qty'] = isset($_SESSION['cart.qty']) ? $_SESSION['cart.qty'] + $qty : $qty;
        $_SESSION['cart.sum'] = isset($_SESSION['cart.sum']) ? $_SESSION['cart.sum'] + $qty * ($price * $_SESSION['cart.currency']['value']) : $qty * ($price * $_SESSION['cart.currency']['value']);
		$_SESSION['cart.weight'] = isset($_SESSION['cart.weight']) ? $_SESSION['cart.weight'] + $qty * $weight : $qty * $weight;
		$_SESSION['cart.volume'] = isset($_SESSION['cart.volume']) ? $_SESSION['cart.volume'] + $qty * $volume : $qty * $volume;
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
        $qtyMinus = $_SESSION['cart'][$id]['qty'];
		$sumWeight = $_SESSION['cart'][$id]['weight'];
		$sumVolume = $_SESSION['cart'][$id]['volume'];
        $sumMinus = $_SESSION['cart'][$id]['qty'] * $_SESSION['cart'][$id]['price'];
        $_SESSION['cart.qty'] -= $qtyMinus;
        $_SESSION['cart.sum'] -= $sumMinus;
		$_SESSION['cart.weight'] -= $sumWeight;
		$_SESSION['cart.volume'] -= $sumVolume;		
        unset($_SESSION['cart'][$id]);
    }
	
	public function pluscartItem($id){
        $qtyPlus = $_SESSION['cart'][$id]['qty'];
        $sumPlus = $_SESSION['cart'][$id]['price'];
		$weightPlus = $_SESSION['cart'][$id]['weight'];
		$volumePlus = $_SESSION['cart'][$id]['volume'];
        $_SESSION['cart.qty'] = $_SESSION['cart.qty'] + 1;
        $_SESSION['cart.sum'] += $sumPlus;
		$_SESSION['cart.weight'] += $weightPlus;
		$_SESSION['cart.volume'] += $volumePlus;
		$_SESSION['cart'][$id]['qty'] = $_SESSION['cart'][$id]['qty'] + 1;
		
    }
	
	public function minuscartItem($id){
        $qtyMinus = $_SESSION['cart'][$id]['qty'];
        $sumMinus = $_SESSION['cart'][$id]['price'];
		$weightMinus = $_SESSION['cart'][$id]['weight'];
		$volumeMinus = $_SESSION['cart'][$id]['volume'];
        $_SESSION['cart.qty'] = $_SESSION['cart.qty'] - 1;
        $_SESSION['cart.sum'] -= $sumMinus;
		$_SESSION['cart.weight'] -= $weightMinus;
		$_SESSION['cart.volume'] -= $volumeMinus;
		$_SESSION['cart'][$id]['qty'] = $_SESSION['cart'][$id]['qty'] - 1;
		if($_SESSION['cart'][$id]['qty'] == 0){  unset($_SESSION['cart'][$id]); }
    }

    public static function recalc($curr){
        if(isset($_SESSION['cart.currency'])){
            if($_SESSION['cart.currency']['base']){
                $_SESSION['cart.sum'] *= $curr->value;
            }else{
                $_SESSION['cart.sum'] = $_SESSION['cart.sum'] / $_SESSION['cart.currency']['value'] * $curr->value;
            }
            foreach($_SESSION['cart'] as $k => $v){
                if($_SESSION['cart.currency']['base']){
                    $_SESSION['cart'][$k]['price'] *= $curr->value;
                }else{
                    $_SESSION['cart'][$k]['price'] = $_SESSION['cart'][$k]['price'] / $_SESSION['cart.currency']['value'] * $curr->value;
                }
            }
            foreach($curr as $k => $v){
                $_SESSION['cart.currency'][$k] = $v;
            }
        }
    }

}
