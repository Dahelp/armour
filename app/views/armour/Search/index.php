<!--prdt-starts-->
<div class="prdt">
    <div class="container">
		<!--start-breadcrumbs-->
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a class="text-nowrap" href="<?=PATH?>"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item text-nowrap active">Поиск по запросу "<strong><?=h($query);?></strong>"</li>
            </ol>
		</nav>
		<!--end-breadcrumbs-->
		<section class="d-md-flex justify-content-between align-items-center mb-4 pb-2">
            <h1 class="h2 mb-3 mb-md-0 me-3">Поиск по запросу: <strong><?=h($query);?></strong></h1>
        </section>
<!--start-single-->
<div class="single contact">
    <div class="container">
        <?php if(!empty($products)): ?>
                <div class="row g-0 mx-n2 product-one">
                    <?php $curr = \ishop\App::$app->getProperty('currency'); ?>
                    <?php foreach($products as $product): ?>
                    <div class="col-xl-3 col-lg-6 col-md-4 col-sm-6 mb-3">
					            <div class="card product-card card-static pb-3">
									<div class="znachki">
									<?php if($product["hit"]) { ?>
										<div class="badge bg-warning badge-shadow">Хит</div>
									<?php } ?>
									<?php if($product["new_product"]) { ?>
										<div class="badge bg-success badge-shadow">Новинка</div>
									<?php } ?>
									<?php if($product["sale"]) { ?>
										<div class="badge bg-danger badge-shadow">Скидка</div>
									<?php } ?>
									<?php if($_SESSION['user']['id']) { 
										$bookmarks = \R::count('product_bookmarks', 'product_id = ? AND user_id = ?', [$product["id"], $_SESSION['user']['id']]);
										if($bookmarks==1){
									?>
										<button id="wishlist-<?=$product["id"]?>" class="btn-wishlist2 btn-sm" type="button" data-bs-toggle="tooltip" data-bs-placement="left" title="" data-bs-original-title="Wishlist" aria-label="Wishlist"><i class="far fa-heart"></i></button>
									<?php } else { ?>
										<button id="wishlist-<?=$product["id"]?>" class="btn-wishlist btn-sm" type="button" data-id="<?=$product["id"]?>" data-userid="<?=$_SESSION['user']['id']?>" data-bs-toggle="tooltip" data-bs-placement="left" title="Добавить в избранное" data-bs-original-title="Add to wishlist" aria-label="Add to wishlist"><i class="far fa-heart"></i></button>
									<?php } ?>
									<?php } ?>
									<?php if(!$_SESSION['comparison'][$product["id"]]) { ?>
										<button id="comparison-<?=$product["id"]?>" class="btn-comparison btn-sm" type="button" data-id="<?=$product["id"]?>" data-categoryid="<?=$product["category_id"]?>" data-bs-toggle="tooltip" data-bs-placement="left" title="Добавить в сравнени" data-bs-original-title="Comparison" aria-label="Comparison"><i class="far fa-tasks"></i></button>
									<?php } else { ?>
										<button id="comparison-<?=$product["id"]?>" class="btn-comparison2 btn-sm" type="button" data-bs-toggle="tooltip" data-bs-placement="left" title="Добавить в сравнени" data-bs-original-title="Comparison" aria-label="Comparison"><i class="far fa-tasks"></i></button>
									<?php } ?>
									</div>						            
						            <a class="card-img-top d-block overflow-hidden" href="/<?=$product["alias"];?>">
							            <img src="images/product/mini/<?=$product["img"];?>" alt="" />
						            </a>
									<?php $cat_prod = \R::findOne('category', "id = ?", [$product["category_id"]]); ?>
						            <div class="card-body py-2"><span class="product-meta d-block fs-xs pb-1"><?=$cat_prod["name"]?></span>
							            <h3 class="product-title fs-sm text-truncate"><a href="/<?=$product["alias"];?>"><?=$product["name"];?></a></h3>
							            <div class="product-price">
											<div class="product-sku">Код: <?=$product["article"];?></div>
											<div class="product-curr">
									            <span class="item_price"><?=$curr['symbol_left'];?> <?php echo round($product["price"] / $curr['value'], 2); ?> <?=$curr['symbol_right'];?></span>
									            <?php if($product["old_price"]): ?>
									            	<small><del><?=$curr['symbol_left'];?> <?php echo round($product["old_price"] / $curr['value'], 2); ?> <?=$curr['symbol_right'];?></del></small>
									            <?php endif; ?>
											</div>
							            </div>									
						            <?php // модификации
										$modification = \R::getAll("SELECT quantity FROM modification WHERE product_id = '".$product["id"]."'");
										if($modification) {
											foreach($modification as $item) {
												
													$quantity[$product["id"]] += $item["quantity"];												
											}
											$quantity[$product["id"]] = $quantity[$product["id"]] + $product["quantity"];
										}else{
											$quantity[$product["id"]] = $product["quantity"];
										}
									?>									
									<?php if($quantity[$product["id"]] > 0) { ?>
									<?php $cartQty=(int)($_SESSION['cart'][$product['id']]['qty']??0); ?>
									<div class="product-btn">
										<div class="product-floating-btn">
											<div class="quantity-block my_quant-<?=$product['id']?>" style="display:<?=$cartQty>0?'inline-flex':'none'?>">
												<button type="button" data-id="<?=$product['id']?>" data-qty="<?=$cartQty?>" class="my-minus-<?=$product['id']?> my-minus quantity-arrow-minus">−</button>
												<input data-id="<?=$product['id']?>" type="text" class="text-center input-number qty-item-<?=$product['id']?> input-text qty text" value="<?=$cartQty?:1?>" min="1" max="<?=$quantity[$product['id']]?>" inputmode="numeric">
												<button type="button" data-id="<?=$product['id']?>" data-qty="<?=$cartQty?>" class="my-plus-<?=$product['id']?> my-plus quantity-arrow-plus">+</button>
											</div>
											<a data-id="<?=$product['id']?>" class="btn btn-danger btn-shadow btn-cart add-to-cart-link korzina-<?=$product['id']?> clear-korzina" style="<?=$cartQty>0?'display:none;':''?>" href="cart/add?id=<?=$product['id']?>" data-max="<?=$quantity[$product['id']]?>"><i class="fas fa-cart-plus fs-base"></i> Купить</a>
										</div>
									</div>
									<div class="product-nalichie">
										<span class="btn-nalichie">В наличии: <?=$quantity[$product["id"]];?> шт.</span>
									</div>
									<?php }else{ ?>
									<div class="product-btn"></div>
									<div class="product-nonalichie">
										<span class="btn-nonalichie">Нет в наличии</span>
									</div>
									<?php } ?>
									</div>
					            </div>
				            </div>
                    <?php endforeach; ?>
                    <div class="clearfix"></div>
                    <div class="text-center">
                        <p>(<?=count($products)?> товара(ов) из <?=$total;?>)</p>
                        <?php if($pagination->countPages > 1): ?>
                            <?=$pagination;?>
                        <?php endif; ?>
                    </div>
                </div>
        <?php endif; ?>
            </div>            
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<!--product-end-->
