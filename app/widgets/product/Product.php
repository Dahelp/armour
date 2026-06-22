<?php

namespace app\widgets\product;

use ishop\App;

class Product{
	
	public $product;
	public $tpl;
	public $curr;
	public $attribute;
	public $brand;
	
    public function __construct($product, $curr, $attribute = '', $brand = '', $tpl = ''){

		$this->tpl = $tpl ?: __DIR__ . '/product_tpl.php';
        $this->run($product, $curr, $attribute, $brand);
		
    }
	
	protected function run($product, $curr, $attribute, $brand){

        require $this->tpl;

    }

}