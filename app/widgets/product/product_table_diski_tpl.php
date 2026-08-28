<?php
	$attribute = is_array($attribute) ? $attribute : [];
	$brand = is_array($brand) ? $brand : [];
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
    <td><?=$attribute[6]?></td>
	<td><?=$attribute[4]?></td>
	
	<td><?=$attribute[42]?></td>
	<td><?=$attribute[43]?></td>
	
	<td><?=$attribute[45]?></td>
	<td class="c_price ">
		<span class="woocommerce-Price-amount amount"><bdi><?=$product['price']?>&nbsp;<span class="woocommerce-Price-currencySymbol">руб.</span></bdi></span>
	</td>
	<td>
		<span class="woocommerce-Price-amount amount"><bdi><?=$product['quantity']?>&nbsp;<span class="woocommerce-Price-currencySymbol">шт.</span></bdi></span>
	</td>
	<td class="btn_price"><a class="detail_button btn-blue-back" href="<?=$product["alias"]?>">Подробнее</a></td>
</tr>
