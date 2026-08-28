<?php if(!empty($products)): ?>
    <?php $curr = \ishop\App::$app->getProperty('currency'); ?>
	<table>
		<thead>								
			<tr>
				<th>Фото</th>
				<th>Типоразмер</th>
				<th>Марка шин</th>
				<th>Тип протектора</th>
				<th>PR</th>
				<th class="c_price">Цена (с НДС)</th>
				<th>Наличие</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
    <?php foreach($products as $product): ?>
		<?php $inseo_prod = $inseoProd; ?>
		<?php $attribute = $productAttributes[(int)$product['id']] ?? []; ?>
		<?php $brand = $brands[(int)$product['brand_id']] ?? []; ?>
        <tr class="product type-product">
			<td>
				<a class="card-img-top d-block overflow-hidden" href="<?=$product["alias"]?>">							
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
			<td><?=$attribute[4]?></td>
			<td><?=$brand['name']?></td>
			<td><?=$product['model']?></td>
			<td><?=$attribute[5]?></td>
			<td>
				<span class="woocommerce-Price-amount amount"><bdi><?=$product['price']?>&nbsp;<span class="woocommerce-Price-currencySymbol">руб.</span></bdi></span>
			</td>
			<td>
				<span class="woocommerce-Price-amount amount"><bdi><?=$product['quantity']?>&nbsp;<span class="woocommerce-Price-currencySymbol">шт.</span></bdi></span>
			</td>
			<td>
				<form class="cart" method="post" enctype="multipart/form-data">
					<div class="quantity-block">
						<button type="button" class="quantity-arrow-minus"> -</button>
						<input type="text" class="input-text qty text" step="1" min="1" max="9999" maxlength="4" name="quantity" value="1" title="Кол-во" size="4" inputmode="numeric">
						<button type="button" class="quantity-arrow-plus"> +</button>
					</div>
					<button type="submit" name="add-to-cart" value="8632" class="single_add_to_cart_button button btn-green-back">В корзину</button>
				</form>
			</td>
			<td><a class="detail_button btn-blue-back" href="<?=$product["alias"]?>">Подробнее</a></td>
		</tr>
    <?php endforeach; ?>
	</tbody>
	</table>
    <div class="clearfix"></div>
    <div class="text-center">
        <p>(<?=count($products)?> товара(ов) из <?=$total;?>)</p>
        <?php if($pagination->countPages > 1): ?>
            <?=$pagination;?>
        <?php endif; ?>
    </div>
<?php else: ?>
    <h3>Товаров не найдено...</h3>
<?php endif; ?>
