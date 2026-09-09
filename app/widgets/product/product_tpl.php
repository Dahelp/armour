<?php
$productId = (int)($product['id'] ?? 0);
$alias = '/' . ltrim((string)($product['alias'] ?? ''), '/');
$name = (string)($product['name'] ?? 'Товар');
$article = trim((string)($product['article'] ?? ''));
$quantity = max(0, (int)($product['quantity'] ?? 0));
$cartQty = max(0, (int)($_SESSION['cart'][$productId]['qty'] ?? 0));
$price = (float)($product['price'] ?? 0) * (float)($curr['value'] ?? 1);
$image = basename((string)($product['img'] ?? ''));
$imageSrc = '/images/product/baseimg/' . rawurlencode($image);
$hasCartItem = $cartQty > 0;
?>
<li class="i-product product product-card-unified type-product instock purchasable product-type-simple"
    itemscope itemtype="https://schema.org/Product" data-id="<?=$productId?>">
    <div class="product-card-unified__media">
        <a href="<?=h($alias)?>" class="product-card-unified__image-link" aria-label="<?=h($name)?>">
            <img width="500" height="500" src="<?=h($imageSrc)?>" alt="<?=h($name)?>" loading="lazy" decoding="async">
        </a>
        <div class="product-card-unified__badges" aria-label="Метки товара">
            <?php if (!empty($product['hit'])): ?><span class="product-card-unified__badge product-card-unified__badge--hit">Хит</span><?php endif; ?>
            <?php if (!empty($product['new_product'])): ?><span class="product-card-unified__badge product-card-unified__badge--new">Новинка</span><?php endif; ?>
            <?php if (!empty($product['sale'])): ?><span class="product-card-unified__badge product-card-unified__badge--sale">Акция</span><?php endif; ?>
        </div>
    </div>
    <div class="product-card-unified__body">
        <p class="woocommerce-loop-product__title product-card-unified__title">
            <a itemprop="url" href="<?=h($alias)?>"><span itemprop="name"><?=h($name)?></span></a>
        </p>
        <?php if ($article !== ''): ?><p class="product-sku product-card-unified__sku" itemprop="sku" content="<?=h($article)?>">Артикул: <?=h($article)?></p><?php endif; ?>
        <div class="stock product-card-unified__stock <?=$quantity > 0 ? 'is-available' : 'is-unavailable'?>">
            <i class="fas <?=$quantity > 0 ? 'fa-check-circle' : 'fa-times-circle'?>" aria-hidden="true"></i>
            <?php if ($quantity === 0): ?>Нет в наличии<?php else: ?>В наличии: <?=$quantity?> шт.<?php endif; ?>
        </div>
        <div class="box-price-btn product-card-unified__footer">
            <div class="price product-card-unified__price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <meta itemprop="priceCurrency" content="RUB"><meta itemprop="price" content="<?=number_format($price, 2, '.', '')?>">
                <span class="price_prefix">Цена</span>
                <span class="price_html"><span class="woocommerce-Price-amount amount"><bdi><?=number_format($price, 0, '.', ' ')?> <span class="woocommerce-Price-currencySymbol">₽</span></bdi></span></span>
            </div>
            <div class="block-btn product-card-unified__buy">
                <?php if ($quantity > 0): ?>
                    <div class="quantity-block my_quant-<?=$productId?>" style="display:<?=$hasCartItem ? 'inline-flex' : 'none'?>">
                        <button type="button" data-id="<?=$productId?>" data-qty="<?=$cartQty ?: 1?>" class="my-minus-<?=$productId?> my-minus quantity-arrow-minus" aria-label="Уменьшить количество">−</button>
                        <span class="qty-item"><input data-id="<?=$productId?>" type="text" class="text-center input-number qty-item-<?=$productId?> input-text qty text" value="<?=$cartQty ?: 1?>" min="1" max="<?=$quantity?>" maxlength="4" inputmode="numeric" aria-label="Количество"></span>
                        <button type="button" data-id="<?=$productId?>" data-qty="<?=$cartQty ?: 1?>" class="my-plus-<?=$productId?> my-plus quantity-arrow-plus" aria-label="Увеличить количество">+</button>
                    </div>
                    <div class="my_btn my_btn-<?=$productId?>">
                        <a data-id="<?=$productId?>" href="/cart/add?id=<?=$productId?>" class="add-to-cart-link button btn-green-back korzina-<?=$productId?> clear-korzina" data-max="<?=$quantity?>"<?=$hasCartItem ? ' style="display:none"' : ''?>>В корзину</a>
                        <a href="/cart" class="button btn-green-back vkorzine-<?=$productId?> clear-vkorzine"<?=$hasCartItem ? '' : ' style="display:none"'?>>В корзине</a>
                    </div>
                <?php else: ?><a href="<?=h($alias)?>" class="button product-card-unified__details">Подробнее</a><?php endif; ?>
            </div>
        </div>
    </div>
</li>
