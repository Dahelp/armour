<!--start-breadcrumbs-->
<div class="breadcrumbs">
    <div class="container">
        <!--start-breadcrumbs-->
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
				<li class="breadcrumb-item"><a href="technics">Каталог техники</a></li>
				<li class="breadcrumb-item"><a href="technics/type/<?=$type["alias"]?>">Производители <?=$type["seoname_1"]?></a></li>
				<li class="breadcrumb-item"><a href="technics/<?=$type["alias"]?>/<?=$manufacturer["alias"]?>"><?php echo \ishop\App::upFirstLetter($type["seoname_3"]);?> <?=$manufacturer["name"]?></a></li>
				<li class="breadcrumb-item active"><?=$type->name?> <?=$manufacturer->name?> <?=$technics->model?></li>
            </ol>
		</nav>
    </div>
</div>
<!--end-breadcrumbs-->
<?php
    $curr = \ishop\App::$app->getProperty('currency');
    $cats = \ishop\App::$app->getProperty('cats');
?>
<!--start-single-->
<div class="single contact">
    <div class="container">
        <section class="ps-lg-4 pe-lg-3">
          <!-- Content-->
          <!-- Product Gallery + description-->
          <section class="row g-0 mx-n2">
            <div class="col-xl-7 byp-rght-pdd-15 mb-3 byp-float-lft">
              	<section class="slider">			
				<?php if($gallery): ?>		
				
					<div id="slider" class="flexslider">
					  <ul class="slides">						
						<li><img itemprop="image" src="images/technics/baseimg/<?=$technics->img;?>" alt=""></li>
						<?php foreach($gallery as $item): ?>
						<li>
							<img itemprop="image" src="images/technics/gallery/<?=$item->img;?>" />
						</li>
						<?php endforeach; ?>						
					  </ul>
					</div>
					<div id="carousel" class="flexslider">
					  <ul class="slides">
						<li><img itemprop="image" src="images/technics/baseimg/<?=$technics->img;?>" alt=""></li>
						<?php foreach($gallery as $item): ?>
						<li>
							<img itemprop="image" src="images/technics/gallery/<?=$item->img;?>" />
						</li>
						<?php endforeach; ?>
					  </ul>
					</div>
				
				<?php else: ?>
                    <div id="slider" class="flexslider">
					  <ul class="slides">
						<?php if($technics->img) { ?>
						<li><img itemprop="image" src="images/technics/baseimg/<?=$technics->img;?>" alt=""></li>
						<?php }else{ ?>
						<li>
							<img itemprop="image" src="images/no_image.jpg" style="width:250px" />
						</li>
						<?php } ?>
						</ul>
					</div>
				<?php endif; ?>
				</section>
            </div>
            <div class="col-xl-5 mb-3 byp-float-rght">
              <div class="h-100 bg-light rounded-3 px-4 px-sm-5">
				<?php $administr = \R::findOne('user', 'id = ?', [$_SESSION['user']['id']]); ?>
				<?php if($administr['groups'] == "1") { ?>
					<div class="edit_prod"><a target="_blank" href="<?= ADMIN ?>/plagins/technics-edit?id=<?=$technics->id?>"><i class="far fa-edit"></i> Редактировать</a></div>
				<?php } ?>
				<a class="product-meta d-block fs-sm pb-2" href="category/<?=$cat_prod->alias?>" title="<?=$cat_prod->name?>"><?=$cat_prod->name?></a>				
                <h1 class="h3">
					<?=$type->name?> <?=$manufacturer->name?> <?=$technics->model?>
				</h1>
			
                <div class="fw-normal">											
						<table class="table table-bordered table-striped">						
							<thead>
                                <tr>
                                    <td colspan="2" class="hide_td">
										<div class="hide_td_1"><strong>Характеристики:</strong></div>											    
									</td>
                                </tr>
                            </thead>
							<tbody>
								<tr><td>Тип техники:</td><td><?=$type->name?></td></tr>
								<tr><td>Производитель:</td><td><?=$manufacturer->name?></td></tr>
								<tr><td>Модель техники:</td><td><?=$technics->model?></td></tr>								
							</tbody>
							<?php	
									$prodsizes = \R::getAll("SELECT * FROM technics_tiposize, attribute_value WHERE technics_tiposize.value_id = attribute_value.id AND technics_tiposize.technics_id = '".$technics->id."'");
									foreach($prodsizes as $prodsize) {										
										$psize .= "'".$prodsize["value"]."', ";										
									}									
									$psize = rtrim($psize, ", ");
									
									$sizes = \R::getAll("SELECT * FROM technics_tiposize, attribute_value WHERE technics_tiposize.value_id = attribute_value.id AND technics_tiposize.technics_id = '".$technics->id."' AND tip_size = '1'");
									$sizes_back = \R::getAll("SELECT * FROM technics_tiposize, attribute_value WHERE technics_tiposize.value_id = attribute_value.id AND technics_tiposize.technics_id = '".$technics->id."' AND tip_size = '2'");
									$sizes_alt = \R::getAll("SELECT * FROM technics_tiposize, attribute_value WHERE technics_tiposize.value_id = attribute_value.id AND technics_tiposize.technics_id = '".$technics->id."' AND tip_size = '3'");
									$sizes_alt_back = \R::getAll("SELECT * FROM technics_tiposize, attribute_value WHERE technics_tiposize.value_id = attribute_value.id AND technics_tiposize.technics_id = '".$technics->id."' AND tip_size = '4'");
									$sizes_vse = \R::getAll("SELECT * FROM technics_tiposize, attribute_value WHERE technics_tiposize.value_id = attribute_value.id AND technics_tiposize.technics_id = '".$technics->id."'");
									if($sizes or $sizes_back or $sizes_alt or $sizes_alt_back) {
										
							?>
							<thead>
                                <tr>
                                    <td colspan="2" class="hide_td">
										<div class="hide_td_1"><strong>Заводские размеры шин:</strong></div>											    
									</td>
                                </tr>
                            </thead>
							<tbody>
								<?php if($sizes AND $sizes_back) { ?>
								<tr><td>Размер передних:</td><td>
								<?php
									 
									foreach($sizes as $size) {
										$vsize .= "<a href=\"size/".$size["alias"]."\" title=\"Все шины размера ".$size["value"]."\">".$size["value"]."</a>, ";																			
									} 
									echo $vsize = rtrim($vsize, ", ");								
								 ?>
								</td></tr>
								<tr><td>Размер задних:</td><td>
								<?php
									 
									foreach($sizes_back as $back) {
										$bsize .= "<a href=\"size/".$back["alias"]."\" title=\"Все шины размера ".$back["value"]."\">".$back["value"]."</a>, ";																			
									} 
									echo $bsize = rtrim($bsize, ", ");									
								 ?>
								</td></tr>
								<?php }else{ ?>
								<tr><td>Размер:</td><td>
								<?php
									 
									foreach($sizes as $size) {
										$vsize .= "<a href=\"size/".$size["alias"]."\" title=\"Все шины размера ".$size["value"]."\">".$size["value"]."</a>, ";																		
									} 
									echo $vsize = rtrim($vsize, ", ");									
								 ?>
								</td></tr>
								<?php } ?>
							</tbody>
							
							<?php if($sizes_alt or $sizes_alt_back) { ?>
							<thead>
                                <tr>
                                    <td colspan="2" class="hide_td">
										<div class="hide_td_1"><strong>Альтернативные размеры шин:</strong></div>											    
									</td>
                                </tr>
                            </thead>
							<tbody>
							<?php if($sizes_alt AND $sizes_alt_back) { ?>
								<tr><td>Размер передних:</td><td>
								<?php
									 
									foreach($sizes_alt as $asize) {
										$vasize .= "<a href=\"size/".$asize["alias"]."\" title=\"Все шины размера ".$asize["value"]."\">".$asize["value"]."</a>, ";																			
									} 
									echo $vasize = rtrim($vasize, ", ");								
								 ?>
								</td></tr>
								<tr><td>Размер задних:</td><td>
								<?php
									 
									foreach($sizes_alt_back as $aback) {
										$basize .= "<a href=\"size/".$aback["alias"]."\" title=\"Все шины размера ".$aback["value"]."\">".$aback["value"]."</a>, ";																			
									} 
									echo $basize = rtrim($basize, ", ");									
								 ?>
								</td></tr>
								<?php }else{ 
								if($sizes_alt) {
								?>
								<tr><td>Размер:</td><td>
								<?php
									 
									foreach($sizes_alt as $asize) {
										$vasize .= "<a href=\"size/".$asize["alias"]."\" title=\"Все шины размера ".$asize["value"]."\">".$asize["value"]."</a>, ";																		
									} 
									echo $vasize = rtrim($vasize, ", ");
									
								 ?>
								</td></tr>
								<?php } 
								if($sizes_alt_back) {
								?>
								<tr><td>Размер:</td><td>
								<?php
									 
									foreach($sizes_alt_back as $aback) {
										$basize .= "<a href=\"size/".$aback["alias"]."\" title=\"Все шины размера ".$aback["value"]."\">".$aback["value"]."</a>, ";																			
									} 
									echo $basize = rtrim($basize, ", ");									
								 ?>
								</td></tr>
								<?php } } ?>
							</tbody>
							<?php } ?>
							
							<?php } ?>
						</table>					
				</div>
								
				
              </div>
            </div>
          </section>
		  <?php 
		  if($psize) {
		  $values = \R::getAll("SELECT * FROM attribute_value WHERE value IN ($psize)"); ?>
		  <div class="row g-0 mx-n2 product-one mb-3">
			<?php 
				if($values) {
				
				foreach($values as $v) {
								
					$ids = \R::getAll("SELECT product_id FROM attribute_product, product WHERE attribute_product.product_id = product.id AND attribute_product.attr_id = '".$v["id"]."'");
					if($ids){
						foreach($ids as $ds){
							$prid .= "".$ds["product_id"].",";
						}
						$ids = rtrim($prid, ',');
						
						$products = \R::find('product', "hide = 'show' AND id IN ($ids)");
					}
				}
				foreach($products as $product){ ?>
				
					<div class="col-xl-3 col-lg-6 col-md-4 col-sm-6 mb-3">
					            <div class="card product-card card-static pb-3">
									<div class="znachki">
										<?php if($product->hit) { ?>
											<div class="badge bg-warning badge-shadow">Хит</div>
										<?php } ?>
										<?php if($product->new_product) { ?>
											<div class="badge bg-success badge-shadow">Новинка</div>
										<?php } ?>
										<?php if($product->sale) { ?>
											<div class="badge bg-danger badge-shadow">Скидка</div>
										<?php } ?>
										<button class="btn-wishlist btn-sm" type="button" data-bs-toggle="tooltip" data-bs-placement="left" title="" data-bs-original-title="Add to wishlist" aria-label="Add to wishlist"><i class="far fa-heart"></i></button>
									</div>						            
						            <a class="card-img-top d-block overflow-hidden" href="product/<?=$product->alias;?>">							
							            <img src="images/product/mini/<?=$product->img;?>" alt="" />
						            </a>
									<?php $cat_prod = \R::findOne('category', "id = ?", [$product->category_id]); ?>
						            <div class="card-body py-2"><span class="product-meta d-block fs-xs pb-1"><?=$cat_prod["name"]?></span>
							            <h3 class="product-title fs-sm text-truncate">
											<a href="product/<?=$product->alias;?>">
											<?php
												$inseo_prod = \R::findOne('plagins_inseo', "tip = ? AND category_id = ? AND hide = 'show'", [product, $category->id]);												
												if($inseo_prod->name) { 					
													echo $name = \ishop\App::seoreplace($inseo_prod->name, $product->id);
												}
												else { echo $product->name; }
											?>
											</a>
										</h3>
							            <div class="product-price">
											<div class="product-sku">Код: <?=$product->article;?></div>
											<div class="product-curr">								
												
												<?php 
												$date = date("Y-m-d H:i:s");
												$action = \R::findOne('actions', "product_id = ? AND hide = 'show' AND date_end > '".$date."'", [$product->id]);
												if($action->product_id): ?>
												<span class="item_price">
													<?=$curr['symbol_left'];?> <?php
														if($action['type_id'] == "1") {
																	$skidka = $product['price']-($product['price'] / 100 * $action['znachenie']);
																	$skidka = explode('.', $skidka);  
																	$skidka = $skidka[0];
																	$skidka = round($skidka, -1);
																}
																if($action['type_id'] == "2") {
																	$skidka = $product['price']-$action['znachenie'];
																}
														echo $skidka * $curr['value'];
													?> <?=$curr['symbol_right'];?>
												</span>
													<del style="float: left;"><small>
														<?=$curr['symbol_left'];?>
														<?=$product->price * $curr['value'];?>
														<?=$curr['symbol_right'];?>
													</small></del>
												<?php else: ?>
												<span class="item_price">
													<?=$curr['symbol_left'];?>
													<?=$product->price * $curr['value'];?>
													<?=$curr['symbol_right'];?>
												</span>
												<?php endif; ?>
												
											</div>
							            </div>									
						            									
									<?php // модификации
										$modification = \R::getAll("SELECT quantity FROM modification WHERE product_id = '".$product["id"]."'");
										if($modification) {
											foreach($modification as $item) {
												
													$quantity[$product["id"]] += $item["quantity"];												
											}
											$quantity[$product["id"]] = $quantity[$product["id"]] + $product->quantity;
										}else{
											$quantity[$product["id"]] = $product->quantity;
										}
									?>									
									<?php if($quantity[$product["id"]] > 0) { ?>
									<div class="product-btn">
										<div class="product-floating-btn">										
											<?php if($_SESSION['cart'][$product->id]) { ?>
												<a data-id="<?=$product->id;?>" class="btn btn-danger btn-shadow btn-cart add-to-cart-link korzina-<?=$product->id;?> clear-korzina" style="display:none;" href="cart/add?id=<?=$product->id;?>" data-max="<?=$quantity[$product["id"]]?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus fs-base"></i> Купить</a>
												<button class="btn btn-success btn-shadow btn-cart vkorzine-<?=$product->id?> clear-vkorzine" style="padding: 4px 10px 4px 10px;">В корзине</button>
											<?php }else{ ?>
												<a data-id="<?=$product->id;?>" class="btn btn-danger btn-shadow btn-cart add-to-cart-link korzina-<?=$product->id;?> clear-korzina" href="cart/add?id=<?=$product->id;?>" data-max="<?=$quantity[$product["id"]]?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus fs-base"></i> Купить</a>
												<button class="btn btn-success btn-shadow btn-cart vkorzine-<?=$product->id?> clear-vkorzine" style="display:none; padding: 4px 10px 4px 10px;">В корзине</button>
											<?php } ?>
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

				<?php }
			?>
		  </div>
		  <?php } }?>
		  <div class="catalog_text">
			<?php 
				foreach($sizes_vse as $vse) {
					$vsesize .= "<a href=\"size/".$vse["alias"]."\" title=\"Все шины размера ".$vse["value"]."\">".$vse["value"]."</a>, ";																			
				}
				$vsesize = rtrim($vsesize, ", ");
			?>
			<p>Шины для <?=$type["seoname_2"]?> занимают не последнее место в перечне запчастей к <?php if($type->name != "Квадроцикл") { ?>спецтехнике<?php }else{ ?>колёсной мототехнике<?php } ?>. Основное предназначение покрышек демпфирование ударов, передаваемых подвеске и мосту от покрытия и обеспечение достаточного сцепления колес с грунтом. От конструкции и качества шин для <?php if($type->name != "Квадроцикл") { ?>специализированной <?php }else{ ?>мото<?php } ?>техники зависят коэффициент сцепления, расход топлива, проходимость в целом эффективность работы транспортного средства.</p>
			<p><?php if($type->name != "Квадроцикл") { ?>Спецтехника<?php }else{ ?>Квадроцикл<?php } ?>, которая интенсивно эксплуатируется с большими нагрузками, требует регулярной замены резины. При этом шины для <?=$type["seoname_1"]?> должны быть качественными, прочными и износостойкими. Всем этим критериям отвечает резина от различных производителей, которые предлагает ООО ИТС-Центр.</p>
			<p>На данный тип <?php if($type->name != "Квадроцикл") { ?>спецтехники<?php }else{ ?>техники<?php } ?> <?=$type->name?> <?=$manufacturer->name?> <?=$technics->model?> ООО ИТС-Центр предлагает резину размеры наружного и посадочного диаметра <?=$vsesize?>, которые реализуются нашей компанией. Шины отличаются по материалу и способу изготовления и делятся на два типа: <?php if($type->name != "Квадроцикл") { ?>цельнолитые и диагональные<?php }else{ ?>направленый и ненаправленный рисунок протектора<?php } ?>.</p>
			<p>Именно такие шины, которые соответствуют самым строгим требованиям нормативов и стандартов, и реализует наша компания.</p>

			<h2>Преимущества шин для <?=$type["seoname_1"]?> от нашей компании</h2>
			<ul>
				<li>Мы поставляем сверхпрочные шины, которые характеризуются следующими качествами</li>
				<li>повышенная износостойкость</li>
				<li>длительный срок эксплуатации</li>
				<li>улучшенное сцепление и управляемость <?=$type["seoname_2"]?></li>
				<li>надежная посадка на обод колеса</li>
				<li>стойкость к повреждениям шин</li>
				<li>легкость монтажа на <?php echo \ishop\App::downFirstLetter($type->name);?></li>
				<li>отличная амортизация ударов</li>
			</ul>
			<p>Продукция, представленная в каталоге, имеется в наличии на нашем складе. Вы сможете самостоятельно подобрать шины для <?=$type["seoname_2"]?> <?=$manufacturer->name?> <?=$technics->model?> в размере <?=$vsesize?> либо воспользоваться консультацией наших специалистов. У нас можно приобрести резину по минимальным ценам. На крупные заказы и для постоянных клиентов имеется система скидок.</p>
			<p>Купить шины для <?php if($type->name != "Квадроцикл") { ?>погрузчика<?php }else{ ?>техники<?php } ?> можно, найдя их в каталоге различных брендов на сайте its-center.ru. Наш интернет-магазин предоставляет широкий ассортимент товаров. Найти нужный товар можно по указанному артикулу. Если вы решили купить шины на <?php echo \ishop\App::downFirstLetter($type->name);?> <?=$manufacturer->name?> <?=$technics->model?> в размере <?=$vsesize?> ждем ваших звонков.</p>
			<p>Наши консультанты знают все о продукции и предоставят квалифицированную помощь при выборе шин на <?php if($type->name != "Квадроцикл") { ?>спецтехнику<?php }else{ ?>квадроцикл<?php } ?>. Они подберут резину нужного размера, с подходящим рисунком протектора и оптимальной нормой слойности. У нас вы найдете шины проверенных торговых марок, поэтому можете быть уверены в их качестве и долговечности.</p>

		  </div>
      </section>
    </div>
</div>
<!--end-single-->


