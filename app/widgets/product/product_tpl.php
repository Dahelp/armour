<?php
	$attr = \R::getAll("SELECT*FROM product_attribute WHERE product_id = ?", [$product["id"]]);
	foreach($attr as $att){
		$attribute[$att['attribute_id']] = $att["attribute_text"];
	}
	$brand = \R::findOne('brand', "id = ?", [$product["brand_id"]]);
	
?>
<li class="i-product product type-product post-13164 status-publish instock product_cat-kolesa-i-kolesnye-opory product_cat-bolshegruznye-obrezinennye product_tag-hit-prodazh has-post-thumbnail purchasable product-type-simple" itemscope="" itemtype="https://schema.org/Product" data-id="13164">
	<a href="/<?=$product["alias"];?>" class="">
		<picture>
			<img width="500" height="500" src="images/product/baseimg/<?=$product["img"];?>" class="attachment-full size-full" alt="" loading="lazy" decoding="async">
		</picture>
	</a>
	<p class="woocommerce-loop-product__title">
		<a itemprop="url" href="/<?=$product["alias"];?>">
			<span itemprop="name"><?=$product["name"];?></span>
		</a>
	</p>
	<a class="add_to_cart_button button br_compare_button br_product_13164 br_compare_button_inited" data-id="13164" href="https://advanta-ekb.ru/compare">
            <i class="fa fa-square-o"></i>
            <i class="fa fa-check-square-o"></i>
            <span class="br_compare_button_text" data-added="В сравнении" data-not_added="В сравнение">В сравнение</span>
    </a>
	<p class="product-sku" itemprop="sku" content="D63">Артикул: <?=$product["article"];?></p>
	<div class="block-rating-stock">
		<div class="rating-card">			
			<?php $review_prod = \R::getAll("SELECT SUM(review.point) as bal FROM review_product JOIN review ON review.id = review_product.review_id WHERE review_product.product_id = ?", [$product["id"]]); ?>
			<?php $rwcount = \R::count('review_product', "product_id = ?", [$product["id"]]); ?>
			<?php if($rwcount>0) { $srew = $review_prod[0]['bal']/$rwcount; }else{ $srew = 0; } ?>
			<?php for ($i = 1; $i <= 5; $i++) { ?>
				<?php if ($srew < $i) { ?>
					<span class="fa fa-stack"><i class="far fa-star fa-stack-2x"></i></span>
				<?php } else { ?>
					<span class="fa fa-stack"><i class="fas fa-star fa-stack-2x"></i><i class="fa fa-star-o fa-stack-2x"></i></span>
				<?php } ?>
			<?php } ?>							
		</div>
		<div class="rating-count"><?=$rwcount?> отзывов</div>		
	</div>
	<div class="stock">
		<?php
			if($product["quantity"] == 0) { echo "<span class=\"nalich_no\"><i class=\"fa fa-times-circle fa-tabls\" aria-hidden=\"true\"></i></span> Нет в наличии"; }
			if($product["quantity"] > 0 && $product["quantity"] < 11) {echo "<span class=\"nalich_ml\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></span> В наличии: ".$product["quantity"]." шт";}
			if($product["quantity"] > 10 && $product["quantity"] < 31) {echo "<span class=\"nalich_ok\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></span> В наличии: более 10 шт";}
			if($product["quantity"] > 30 && $product["quantity"] < 51) {echo "<span class=\"nalich_ok\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></span> В наличии: более 30 шт";}
			if($product["quantity"] > 50) {echo "<span class=\"nalich_ok\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></span> В наличии: более 50 шт";}
		?>		
	</div>
	<div class="box-price-btn">		
		<div class="product-badge">
			<div class="product-badge__col"></div>
			<div class="product-badge__col"></div>
		</div>
		<div class="price">
			<div class="price_prefix">Цена:</div><div class="price_html "><span class="woocommerce-Price-amount amount"><bdi><?=$product["price"] * $curr['value'];?> <span class="woocommerce-Price-currencySymbol">руб.</span></bdi></span></div>			
		</div>
		<div class="block-btn">
		<?php if($product["quantity"] > 0) { ?>	
			<?php if($_SESSION['cart'][$product["id"]]) { ?>
				<div class="quantity-block my_quant-<?=$product["id"];?>" style="display:inline-flex">
					<button type="button" data-id="<?=$product["id"]?>" data-qty="<?php if($_SESSION['cart'][$product["id"]][qty]){ echo $_SESSION['cart'][$product["id"]][qty]; }else{ echo "1"; }?>" class="my-minus-<?=$product["id"]?> my-minus quantity-arrow-minus"> -</button>
					<span class="qty-item"><input data-id="<?=$product["id"]?>" placeholder="1" type="text" class="text-center input-number qty-item-<?=$product["id"]?> input-text qty text" step="1" maxlength="4" name="quantity" value="<?php if($_SESSION['cart'][$product["id"]][qty]){ echo $_SESSION['cart'][$product["id"]][qty]; }else{ echo "1"; }?>" min="1" max="<?=$product["quantity"]?>" title="Кол-во" size="4" inputmode="numeric"></span>
					<button type="button" data-id="<?=$product["id"]?>" data-qty="<?php if($_SESSION['cart'][$product["id"]][qty]){ echo $_SESSION['cart'][$product["id"]][qty]; }else{ echo "1"; }?>" class="my-plus-<?=$product["id"]?> my-plus quantity-arrow-plus"> +</button>
				</div>
				<div class="my_btn my_btn-<?=$product["id"];?>" style="display:inline-flex">
					<a data-id="<?=$product["id"]?>" href="cart/add?id=<?=$product["id"]?>" class="add-to-cart-link button btn-green-back korzina-<?=$product["id"]?> clear-korzina" data-max="<?=$product["quantity"]?>" style="display:none;">В корзину</a>
					<a href="/cart" class="button btn-green-back vkorzine-<?=$product["id"]?> clear-vkorzine">Добавлено</a>
				</div>
			<?php }else{ ?>
				<div class="quantity-block my_quant-<?=$product["id"];?>" style="display:none">
					<button type="button" data-id="<?=$product["id"]?>" data-qty="<?php if($_SESSION['cart'][$product["id"]][qty]){ echo $_SESSION['cart'][$product["id"]][qty]; }else{ echo "1"; }?>" class="my-minus-<?=$product["id"]?> my-minus quantity-arrow-minus"> -</button>
					<span class="qty-item"><input data-id="<?=$product["id"]?>" placeholder="1" type="text" class="text-center input-number qty-item-<?=$product["id"]?> input-text qty text" step="1" maxlength="4" name="quantity" value="<?php if($_SESSION['cart'][$product["id"]][qty]){ echo $_SESSION['cart'][$product["id"]][qty]; }else{ echo "1"; }?>" min="1" max="<?=$product["quantity"]?>" title="Кол-во" size="4" inputmode="numeric"></span>
					<button type="button" data-id="<?=$product["id"]?>" data-qty="<?php if($_SESSION['cart'][$product["id"]][qty]){ echo $_SESSION['cart'][$product["id"]][qty]; }else{ echo "1"; }?>" class="my-plus-<?=$product["id"]?> my-plus quantity-arrow-plus"> +</button>
				</div>
				<div class="my_btn my_btn-<?=$product["id"];?>">	
					<a data-id="<?=$product["id"]?>" href="cart/add?id=<?=$product["id"]?>" class="add-to-cart-link button btn-green-back korzina-<?=$product["id"]?> clear-korzina" data-max="<?=$product["quantity"]?>">В корзину</a>
					<a href="/cart" class="button btn-green-back vkorzine-<?=$product["id"]?> clear-vkorzine" style="display:none;">Добавлено</a>
				</div>
			<?php } ?>
		<?php } ?>
		</div>
		<a class="product-card__cw-wish product-wish card-button-wish off hlp-inited" data-tooltip="В избранное" data-tooltip-added="В избранном">
			<span class="icon-wish"></span>
			<div class="wish-tooltip">В избранное</div>
		</a>
	</div>
</li>