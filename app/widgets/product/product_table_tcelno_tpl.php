<?php
	$attr = \R::getAll("SELECT*FROM product_attribute WHERE product_id = ?", [$product["id"]]);
	foreach($attr as $att){
		$attribute[$att['attribute_id']] = $att["attribute_text"];
	}
	$brand = \R::findOne('brand', "id = ?", [$product["brand_id"]]);
	
?>
<tr class="product type-product">
    <td>
        <a class="card-img-top overflow-hidden" href="<?=$product["alias"]?>">							
			<img itemprop="image" src="images/product/mini/<?=$product["img"]?>" alt="<?php																								
					if($inseo_prod["name"]) { 					
						echo $name = \ishop\App::seoreplace($inseo_prod["name"], $product["id"]);
					}
					else { echo $product["name"]; }
				?>" title="<?php																							
					if($inseo_prod["name"]) { 					
						echo $name = \ishop\App::seoreplace($inseo_prod["name"], $product["id"]);
					}
					else { echo $product["name"]; }
				?>" />
		</a>
    </td>
    <td><?=$attribute[4]?><br /><?=$attribute[24]?></td>
	<td><?=$brand['name']?></td>
	<td><?=$product['model']?></td>

	<td class="c_price ">
		<span class="woocommerce-Price-amount amount"><bdi><?=$product['price']?>&nbsp;<span class="woocommerce-Price-currencySymbol">руб.</span></bdi></span>
	</td>
	<td>
		<span class="woocommerce-Price-amount amount"><bdi><?=$product['quantity']?>&nbsp;<span class="woocommerce-Price-currencySymbol">шт.</span></bdi></span>
	</td>
	<td class="btn_price">
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
        
    </td>
	<td class="btn_price"><a class="detail_button btn-blue-back" href="<?=$product["alias"]?>">Подробнее</a></td>
</tr>
        