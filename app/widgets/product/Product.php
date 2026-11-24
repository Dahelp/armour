<?php

namespace app\widgets\product;

use ishop\App;

class Product{
	
	public $product;
	public $tpl;
	public $curr;
	public $attribute;
	public $brand;

    public static function listing(iterable $products, array $curr, array $attributes = [], array $brands = [], array $options = []): void
    {
        echo '<ul class="products product-grid" role="list">';
        foreach ($products as $product) {
            new self($product, $curr, $attributes[(int)$product['id']] ?? [], $brands[(int)($product['brand_id'] ?? 0)] ?? [], '', $options);
        }
        echo '</ul>';
    }
	
    public function __construct($product, $curr, $attribute = '', $brand = '', $tpl = '', array $options = []){
		if (is_string($attribute) && str_ends_with($attribute, '.php') && $brand === '' && $tpl === '') {
			$tpl = $attribute;
			$attribute = [];
		}
		$this->tpl = $tpl ?: 'product_tpl.php';
		if (!str_contains($this->tpl, DIRECTORY_SEPARATOR) && !str_contains($this->tpl, '/')) {
			$this->tpl = __DIR__ . DIRECTORY_SEPARATOR . $this->tpl;
		}
        $this->run($product, $curr, $attribute, $brand, $options);
		
    }
	
	protected function run($product, $curr, $attribute, $brand, array $options = []){

        require $this->tpl;

    }

}
