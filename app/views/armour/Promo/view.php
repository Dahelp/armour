
<div class="breadcrumbs">
    <div class="container">
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
				<?php if($type->hide_anons=="show") { ?>
					<li class="breadcrumb-item active"><a href="<?=$type->param_url?>"><?=$type->name;?></a></li>
				<?php } ?>
                <li class="breadcrumb-item active"><?=$find->name;?></li>
            </ol>
		</nav>
    </div>
</div>
<div class="contents">
    <div class="container">
		<div class="row">		
			<?php if(!empty($find)): 
				if($type->hide_clicks == "show") { \R::exec("UPDATE contents SET clicks = clicks+1 WHERE id = ?", [$find->id]); } ?>
			
				<div class="col-md-12">
					<div class="bg-light rounded-3">
						<div class="register-top heading">
							<h1><?=$find->name;?></h1>
						</div>
						<?php if($type["hide_date_post"] == "show") { ?>
							<div class="cont_info_data">
								<?php echo \ishop\App::contdate($find["date_post"]); ?>
							</div>
						<?php } ?>
						<div class="cont-inner">
							<?php if($find->img) { ?>
								<?php if($find->img_hide == "show") { ?>
									<div class="cont-img">
										<img src="images/contents/baseimg/<?=$find->img;?>" alt="" />
									</div>
								<?php } ?>
							<?php } ?>
							<div class="cont-desc">
								<?=$find->content;?>
							</div>
						</div>
					</div>					
				</div>
				<?php
					$curr = \ishop\App::$app->getProperty('currency');
					$cats = \ishop\App::$app->getProperty('cats');
				?>
				<!-- Related products-->
		  <?php if($related): ?>
		  <div class="related_prod">
          <section class="pb-5 mb-2 mb-xl-4 recomend-1">
            <h2 class="h3 pb-2 mb-grid-gutter text-center">Связанные товары</h2>
            <div class="review-wrap">

			<div class="wrap-container">
			<div class="inner-container">				

			<div class="swiper-container swiper1">
				<div class="swiper-wrapper">
				
				<?php foreach($related as $item): ?>
				
					<div class="swiper-slide">					                        
						
					            <div class="card product-card card-static pb-3">
									<div class="znachki">
									<?php if($item["hit"]) { ?>
										<div class="badge bg-warning badge-shadow">Хит</div>
									<?php } ?>
									<?php if($item["new_product"]) { ?>
										<div class="badge bg-success badge-shadow">Новинка</div>
									<?php } ?>
									<?php if($item["sale"]) { ?>
										<div class="badge bg-danger badge-shadow">Скидка</div>
									<?php } ?>
									<button class="btn-wishlist btn-sm" type="button" data-bs-toggle="tooltip" data-bs-placement="left" title="" data-bs-original-title="Add to wishlist" aria-label="Add to wishlist"><i class="far fa-heart"></i></button>
									</div>						            
						            <a class="card-img-top d-block overflow-hidden" href="product/<?=$item["alias"];?>">							
							            <img src="images/product/mini/<?=$item["img"];?>" alt="" />
						            </a>
									<?php $cat_prod = \R::findOne('category', "id = ?", [$item["category_id"]]); ?>
						            <div class="card-body py-2"><span class="product-meta d-block fs-xs pb-1"><?=$cat_prod["name"]?></span>
							            <h3 class="product-title fs-sm text-truncate"><a href="product/<?=$item["alias"];?>"><?=$item["name"];?></a></h3>
							            <div class="product-price">
											<div class="product-sku">Код: <?=$item["article"];?></div>
											<div class="product-curr">
									            <span class="item_price"><?=$curr['symbol_left'];?> <?php echo round($item["price"] / $curr['value'], 2); ?> <?=$curr['symbol_right'];?></span>
									            <?php if($item["old_price"]): ?>
									            	<small><del><?=$curr['symbol_left'];?> <?php echo round($item["old_price"] / $curr['value'], 2); ?> <?=$curr['symbol_right'];?></del></small>
									            <?php endif; ?>
											</div>
							            </div>									
						            									
									<?php if($item["quantity"] > 0) { ?>
									<div class="product-btn">
										<div class="product-floating-btn">										
											<?php if($_SESSION['cart'][$item["id"]]) { ?>
												<a data-id="<?=$item["id"];?>" class="btn btn-danger btn-shadow btn-cart add-to-cart-link korzina-<?=$item["id"];?> clear-korzina" style="display:none;" href="cart/add?id=<?=$item["id"];?>" data-max="<?=$item["quantity"]?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus fs-base"></i>  Купить</a>
												<button class="btn btn-success btn-shadow btn-cart vkorzine-<?=$item["id"]?> clear-vkorzine" style="padding: 4px 10px 4px 10px;">В корзине</button>
											<?php }else{ ?>
												<a data-id="<?=$item["id"];?>" class="btn btn-danger btn-shadow btn-cart add-to-cart-link korzina-<?=$item["id"];?> clear-korzina" href="cart/add?id=<?=$item["id"];?>" data-max="<?=$item["quantity"]?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus fs-base"></i> Купить</a>
												<button class="btn btn-success btn-shadow btn-cart vkorzine-<?=$item["id"]?> clear-vkorzine" style="display:none; padding: 4px 10px 4px 10px;">В корзине</button>
											<?php } ?>
										</div>
									</div>
									<div class="product-nalichie">
										<span class="btn-nalichie">В наличии: <?=$item["quantity"];?> шт.</span>
									</div>									
									<?php } else { ?>
									<div class="product-btn"></div>
									<div class="product-nonalichie">
										<span class="btn-nonalichie">Нет в наличии</span>
									</div>	
									<?php } ?>
									</div>
					            </div>
				            					
					</div>
					
				<?php endforeach; ?>
				
				</div>
						
				</div>
					
				</div>
					
				</div>
				
			</div>
			<div class="swiper-button-inner">
				<div class="swiper-button-next swiper-button-next-1"></div>
				<div class="swiper-button-prev swiper-button-prev-1"></div>
			</div>
          </section>
		  </div>
		  <?php endif; ?>
		  <!-- /Related products-->		  
		 
			<?php endif; ?>		
		</div>
	</div>	
</div>		
