<div class="storefront-breadcrumb">
	<div class="col-full">
		<nav class="woocommerce-breadcrumb" aria-label="breadcrumbs">
			<?=$breadcrumbs;?>
		</nav>
	</div>
</div>
<?php
    $curr = \ishop\App::$app->getProperty('currency');
?>
<div id="content" class="site-content single-product" tabindex="-1">
	<div class="col-full">
		<div class="woocommerce"></div>
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<div id="product-8632" class="i-product product type-product post-8632 status-publish first instock product_cat-bolshegruznye-obrezinennye product_cat-dlja-vyshek-tur-i-stroitelnyh-lesov product_cat-dlja-musornyh-kontejnerov-tbo product_cat-kolesa-po-oblasti-primenenija product_cat-kolesa-i-kolesnye-opory has-post-thumbnail sale purchasable product-type-simple">
					 <div class="grid full-box">
						<div class="width-1-3 gallery-width">
							<div class="woocommerce-product-gallery woocommerce-product-gallery--with-images woocommerce-product-gallery--columns-6 images" data-columns="4" style="opacity: 1; transition: opacity 0.25s ease-in-out;">
								<div class="flex-viewport" style="overflow: hidden; position: relative;">
									<figure class="woocommerce-product-gallery__wrapper">
										<div class="woocommerce-product-gallery__image flex-active-slide">
											<picture>
												<img src="images/product/baseimg/<?=$product->img;?>" class="wp-post-image" alt="" title="" />
											</picture>											
										</div>										
									</figure>
								</div>								
							</div>
						</div>
						<div class="width-2-3 plansh-width" id="tab-buy" style="padding-top:0;">
							<div class="summary entry-summary grid">
								<h1 class="product_title entry-title" itemprop="name"><?=$product->name?></h1>
								<div class="width-3-5">
									<div class="box-price-btn">
										<div class="grid">
											<div class="width-2-3">
												<div class="flex">
													<span class="prod-sku">Артикул: <?=$product->article?></span>													
												</div>
												<div class="flex pt-2">
													<span class="prod-print"><a href="javascript:window.print(); void 0;">Распечатать КП</a></span>
												</div>
												<div class="flex pt-3">
													<a href="#tab-reviews">
														<div class="rating">
												<?php $rwcount = (int)($reviewStats['review_count'] ?? 0); ?>
												<?php $srew = (float)($reviewStats['average_rating'] ?? 0); ?>
														<?php for ($i = 1; $i <= 5; $i++) { ?>
															<?php if ($srew < $i) { ?>
																<span class="fa fa-stack"><i class="far fa-star fa-stack-2x"></i></span>
															<?php } else { ?>
																<span class="fa fa-stack"><i class="fas fa-star fa-stack-2x"></i><i class="fa fa-star-o fa-stack-2x"></i></span>
															<?php } ?>
														<?php } ?>
														</div>
														<div class="rating-count"><?=$rwcount?> отзывов</div>
													</a>
												</div>
											</div>
											<div class="width-1-3 pt-2">
												<a class="add_to_cart_button button br_compare_button br_product_8632 br_compare_button_inited br_compare_added" data-id="8632" href="/comparison">
													<i class="fa fa-square-o"></i>
													<i class="fa fa-check-square-o"></i>
													<span class="br_compare_button_text" data-added="В сравнении" data-not_added="В сравнение">В сравнении</span>
												</a>
												<a class="product-card__cw-wish product-wish single-product-wish hlp-inited on" data-tooltip="В избранное" data-tooltip-added="В избранном" data-product-id="8632">
													<span class="icon-wish"></span>
													<div class="wish-tooltip">В избранном</div>
												</a>
											</div>
											<div class="brand_and_top_box"><a href="#tab-additional_information">Все характеристики</a></div>
										</div>                                        
										
										<?php $filters = $productFilters; ?>
					
										<div class="group_attr">
											<table>
												<tbody>
												<?php foreach($filters as $filter) { 
													if( $filter['url_params'] != "" ) { ?>
													<tr>
														<td><?=$filter['title']?></td>
														<td><?=$filter['value']?></td>
													</tr>
												<?php } } ?>
												 </tbody>
											</table>
										</div>
										
										
                            
                    </div>
                </div>
				<div class="width-2-5">
                    <div class="wrap-link">
						<div class="price pb-3" itemprop="offers" itemscope="" itemtype="https://schema.org/Offer">
							<span>Цена: </span><span class="woocommerce-Price-amount amount"><bdi><?=$product->price * $curr['value'];?>&nbsp;<span class="woocommerce-Price-currencySymbol">руб.</span></bdi></span>
						</div>
                        <div class="nalichie pb-3">
                            <span class="instock">В наличии: <?php if($mods) { 								
							  echo $quantity = $product->quantity + $sum_mods; }
							  else{ echo $product->quantity; }					
						?> шт.</span>
						</div>              
								
    
						<?php if($product->quantity > 0) { ?>
							<div class="quantity">
							<?php if($_SESSION['cart'][$product->id]) { ?>
								<div class="quantity-block my_quant-<?=$product->id;?>" style="display:inline-flex;">
									<button type="button" class="quantity-arrow-minus my-minus-<?=$product->id?> my-minus" data-id="<?=$product->id?>" data-qty="1"> - </button>
									<input type="number" data-id="<?=$product["id"]?>" placeholder="1" class="input-text qty text qty-item-<?=$product["id"]?>" step="1" min="1" max="<?=$product["quantity"]?>" name="quantity" value="<?php if($_SESSION['cart'][$product["id"]]['qty']){ echo $_SESSION['cart'][$product["id"]]['qty']; }else{ echo "1"; }?>" title="Кол-во" maxlength="4">
									<button type="button" class="quantity-arrow-plus my-plus-<?=$product->id?> my-plus" data-id="<?=$product->id?>" data-qty="1"> + </button>
								</div>
								<div class="my_btn my_btn-<?=$product->id;?>" style="display:inline-flex;">
									<a data-id="<?=$product->id;?>" type="submit" class="single_add_to_cart_button add-to-cart-link button btn-green-back korzina-<?=$product->id;?> clear-korzina" href="cart/add?id=<?=$product->id;?>" data-max="<?=$product->quantity?>" style="display:none">В корзину</a>
								</div>
								<div class="my_btn my_btn-<?=$product->id;?>" style="display:inline-flex;">
									<a href="#" rel="form4" class="one-click show_form korzina-<?=$product->id;?> clear-korzina" style="display:none;">Купить в 1 клик</a>								
								</div>
								<div class="vkorzine-<?=$product->id;?>" style="display:inline-flex;">
									<button type="submit" class="single_add_to_cart_button button btn-green-back added-to-cart vkorzine-<?=$product->id;?> clear-vkorzine" id="ajax_add_to_cart_button" data-cart-url="/cart">Добавлено</button>
								</div>
							<?php }else{ ?>
								<div class="quantity-block my_quant-<?=$product->id;?>" style="display:none">
									<button type="button" class="quantity-arrow-minus my-minus-<?=$product->id?> my-minus" data-id="<?=$product->id?>" data-qty="1"> - </button>
									<input type="number" data-id="<?=$product["id"]?>" placeholder="1" class="input-text qty text qty-item-<?=$product["id"]?>" step="1" min="1" max="<?=$product["quantity"]?>" name="quantity" value="<?php if($_SESSION['cart'][$product["id"]]['qty']){ echo $_SESSION['cart'][$product["id"]]['qty']; }else{ echo "1"; }?>" title="Кол-во" maxlength="4">
									<button type="button" class="quantity-arrow-plus my-plus-<?=$product->id?> my-plus" data-id="<?=$product->id?>" data-qty="1"> + </button>
								</div>
								<div class="my_btn my_btn-<?=$product->id;?>">
									<a data-id="<?=$product->id;?>" type="submit" class="single_add_to_cart_button add-to-cart-link button btn-green-back korzina-<?=$product->id;?> clear-korzina" href="cart/add?id=<?=$product->id;?>" data-max="<?=$product->quantity?>">В корзину</a>			
									</div>
								<div class="my_btn my_btn-<?=$product->id;?>">
									<a href="#" rel="form4" class="one-click show_form korzina-<?=$product->id;?> clear-korzina">Купить в 1 клик</a>								
								</div>
								<div class="vkorzine-<?=$product->id;?>" style="display:none">
									<button type="submit" class="single_add_to_cart_button button btn-green-back added-to-cart vkorzine-<?=$product->id;?> clear-vkorzine" id="ajax_add_to_cart_button" data-cart-url="/cart">Добавлено</button>
								</div>
							<?php } ?>
							</div>
						<?php } ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
                        <div class="zsc pt-3">
							<a class="detail_button btn-blue-back show_form" href="#" rel="form5">Запросить счёт на оплату</a>
						</div>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            
                    </div>

                    <div class="block_dopolnit"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="product_simple">
		<ul class="flex flex-wrap tab-list">
			<!--  1  -->
			<li>
				<a href="#tab-buy">Купить</a>
			</li>
        <!--  2  -->
			<?php if($attribute_group): ?>
				<li>
					<a href="#tab-additional_information">Технические характеристики</a>
				</li>
			<?php endif; ?>
                <!--  3  -->
            <li>
                <a href="#tab-description">Описание</a>
            </li>
                <!--  4  -->                    
            <li>
                <a href="#tab-reviews">Отзывы <span class="reviews-count"><?php echo count($review); ?></span></a>
            </li>
         </ul>
		 <?php if($attribute_group): ?>
			<div id="tab-additional_information">
				<div class="tech_attr">
					<h3 class="tab-caption">Технические характеристики</h3>
				</div>
				<table class="shop_attributes woocommerce-group-attributes-layout-1">
					<tbody>
						<?php foreach($attribute_group as $group): ?>
							<tr class="attribute_group_row attribute_group_row_Основные характеристики">
								<th class="attribute_group_name" colspan="1"><?=$group["attribute_name"]?></th>
							</tr>
							
							<tr class="attribute_row attribute_row_Основные характеристики">
								<td>
									<table class="attribute_name_values">
										<tbody>
											<?php 
											// аттрибуты товаров
											$attributs = $productAttributesByGroup[(int)$group['attribute_group_id']] ?? [];
											foreach($attributs as $att): ?>
											<?php $attribute[$att['attribute_id']] = $att["attribute_text"]; ?>
												<tr>
													<th class="attribute_name 1"><?=$att["attribute_name"]?></th><td class="attribute_value"><?=$att["attribute_text"]?></td>
												</tr>
											<?php endforeach; ?>							
										</tbody>
									</table>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	<div id="tab-description">
		<h3 class="tab-caption">Описание</h3>
		<div class="description_box">
			<div class="description_text" itemprop="description">            
				<?php
					if($inseo->content) { 					
						echo $content = \ishop\App::seoreplace($inseo->content, $product->id);
					} 
					echo $product->content;
				?>
			</div>        
		</div>
	</div>
	
	<div id="tab-reviews" class="woocommerce-Tabs-panel woocommerce-Tabs-panel--reviews entry-content wc-tab">
		<h3 class="tab-caption">Отзывы</h3>
		<div class="flex box-otzyv">
			<div class="flex">
				<div class="box-otzyv__rating flex">
					<div>
						<p class="rating__srednee-ocenka"><?=$srew?></p>
						<p class="rating__srednee">средняя оценка</p>
						<div class="star-rating" role="img">
							<?php for ($i = 1; $i <= 5; $i++) { ?>
						<?php if ($srew < $i) { ?>
							<span class="fa fa-stack"><i class="far fa-star fa-stack-2x"></i></span>
						<?php } else { ?>
							<span class="fa fa-stack"><i class="fas fa-star fa-stack-2x"></i><i class="fa fa-star-o fa-stack-2x"></i></span>
						<?php } ?>
					<?php } ?>
						</div>
						<p class="rating__reviews"><span class="count"><?=$rwcount?></span> отзыв</p>
					</div>
				</div>
				<div class="box-otzyv__ratingbar">
					<div>
																		<div> 1                            <div class="st">
									<span style="width: 0%"></span>
								</div>
							</div>
																		<div> 2                            <div class="st">
									<span style="width: 0%"></span>
								</div>
							</div>
																		<div> 3                            <div class="st">
									<span style="width: 0%"></span>
								</div>
							</div>
																		<div> 4                            <div class="st">
									<span style="width: 0%"></span>
								</div>
							</div>
																		<div> 5                            <div class="st">
									<span style="width: 100%"></span>
								</div>
							</div>
										</div>
				</div>
			</div>
			<div class="list-review"><div id="reviews" class="woocommerce-Reviews">
		<div id="comments">
			<h2 class="woocommerce-Reviews-title">Отзывы на <span><?=$product->name?></span></h2>

			<a rel="form-reviews" href="" class="one-click one-click-reviews btn-border-green show_form">Оставить отзыв</a>

						<ol class="commentlist">
					<li style="display:none"></li><!-- #comment-## -->
				</ol>

							</div>

				<div id="form-reviews" rel="form-reviews" class="black">
				<div class="big_box_close"></div>
				<div class="form_box">
					<a class="a_close_box">x</a>
					<div class="form_title">
						Оставьте отзыв
					</div>
						<p class="form_text">

						</p>
					<div class="form_form">
						<div id="review_form_wrapper">
							<div id="review_form">
									<div id="respond" class="comment-respond">
			<span id="reply-title" class="comment-reply-title">Добавить отзыв</span><form action="" method="post" id="commentform" class="comment-form wpcf7lazy" novalidate="" style="padding-bottom: 40px;"><p class="comment-notes"><span id="email-notes">Ваш адрес email не будет опубликован.</span> <span class="required-field-message">Обязательные поля помечены <span class="required">*</span></span></p><div class="comment-form-rating"><label for="rating">Ваша оценка <span class="required">*</span></label><p class="stars">						<span>							<a class="star-1" href="#">1</a>							<a class="star-2" href="#">2</a>							<a class="star-3" href="#">3</a>							<a class="star-4" href="#">4</a>							<a class="star-5" href="#">5</a>						</span>					</p><select name="rating" id="rating" required="" style="display: none;">
							<option value="">Оценка…</option>
							<option value="5">Отлично</option>
							<option value="4">Хорошо</option>
							<option value="3">Средне</option>
							<option value="2">Неплохо</option>
							<option value="1">Очень плохо</option>
						</select></div><input type="hidden" class="form-captcha-input" name="form-captcha" value=""><p class="form-captcha-position comment-form-comment"><label for="comment">Ваш отзыв&nbsp;</label><textarea id="comment" name="comment" cols="45" rows="8" placeholder="Здесь вы можете написать свой отзыв о товаре."></textarea></p><p class="comment-form-author"><label for="author">Имя&nbsp;<span class="required">*</span></label><input id="author" name="author" type="text" value="" size="30" required="" placeholder="Иван Иванович"></p>
	<p class="comment-form-email"><label for="email">Email</label><input id="email" name="email" type="email" value="" size="30" placeholder="mail@gmail.com"></p>
	<p class="form-submit"><input name="submit" type="submit" id="submit" class="submit" value="Отправить"> <input type="hidden" name="comment_post_ID" value="8632" id="comment_post_ID">
	<input type="hidden" name="comment_parent" id="comment_parent" value="0">
	</p></form>	</div><!-- #respond -->
								</div>
						</div>
					</div>
				</div>
			</div>
		
		<div class="clear"></div>
	</div>
	</div>
		</div>
	</div>
</div>
</div>

<?php if (!empty($related_count) && !empty($related)): ?>
<section class="related products">
    <h4>Связанные товары</h4>

    <div class="table_wrap facetwp-template" style="display: block;">

        <?php
        $related_categories = [];

        foreach ($related as $rel_item) {
            $related_categories[$rel_item['category_id']][] = $rel_item;
        }
        ?>

        <?php foreach ($related_categories as $category_id => $items): ?>
            <?php
            $cat_related = $recommendationCategories[(int)$category_id] ?? null;

            if (!$cat_related) {
                continue;
            }

            $table_alt = !empty($cat_related["table_alt"]) ? $cat_related["table_alt"] : 'default';
            $table_head_file = APP . '/widgets/product/table_' . $table_alt . '_tpl.php';
            $product_tpl = 'product_table_' . $table_alt . '_tpl.php';
            ?>

            <h5><?= h($cat_related["name"]) ?></h5>

            <div class="casters-block product-one">
                <table>
                    <thead>
                        <?php
                        if (is_file($table_head_file)) {
                            require $table_head_file;
                        }
                        ?>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $item2): ?>
                            <?php
                            new \app\widgets\product\Product(
                                $item2,
                                $curr,
                                $recommendationAttributes[(int)$item2['id']] ?? [],
                                $recommendationBrands[(int)$item2['brand_id']] ?? [],
                                $product_tpl
                            );
                            ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endforeach; ?>

    </div>
</section>
<?php endif; ?>

<?php if (!empty($similar_count) && !empty($similar_categories)): ?>
<section class="related products">
    <h4>Похожие товары</h4>

    <div class="table_wrap facetwp-template" style="display: block;">

        <?php foreach ($similar_categories as $category_id => $items): ?>
            <?php
            $cat_similar = $recommendationCategories[(int)$category_id] ?? null;

            if (!$cat_similar) {
                continue;
            }

            $table_alt = !empty($cat_similar["table_alt"]) ? $cat_similar["table_alt"] : 'default';
            $table_head_file = APP . '/widgets/product/table_' . $table_alt . '_tpl.php';
            $product_tpl = 'product_table_' . $table_alt . '_tpl.php';
            ?>

            <h5><?= htmlspecialchars($cat_similar["name"], ENT_QUOTES, 'UTF-8') ?></h5>

            <div class="casters-block product-one">
                <table>
                    <thead>
                        <?php
                        if (is_file($table_head_file)) {
                            require $table_head_file;
                        }
                        ?>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $item2): ?>
                            <?php
                            new \app\widgets\product\Product(
                                $item2,
                                $curr,
                                $recommendationAttributes[(int)$item2['id']] ?? [],
                                $recommendationBrands[(int)$item2['brand_id']] ?? [],
                                $product_tpl
                            );
                            ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endforeach; ?>

    </div>
</section>
<?php endif; ?>

		
				</main><!-- #main -->
		</div><!-- #primary -->

		

	

</div><!-- .col-full -->

<div class="col-full">
    </div>

</div>

<script>
function Selected(a) {
  var label = a.value;
    if (label=="Open") {
       document.getElementById("Block1").style.display='block';
   } else {
       document.getElementById("Block1").style.display='none';
   } 
}
</script>
