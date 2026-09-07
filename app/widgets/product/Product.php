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
		if (is_string($attribute) && str_ends_with($attribute, '.php') && $brand === '' && $tpl === '') {
			$tpl = $attribute;
			$attribute = [];
		}
		$this->tpl = $tpl ?: 'product_tpl.php';
		if (!str_contains($this->tpl, DIRECTORY_SEPARATOR) && !str_contains($this->tpl, '/')) {
			$this->tpl = __DIR__ . DIRECTORY_SEPARATOR . $this->tpl;
		}
        $this->run($product, $curr, $attribute, $brand);
		
    }
	
	protected function run($product, $curr, $attribute, $brand){

        require $this->tpl;

    }

}
