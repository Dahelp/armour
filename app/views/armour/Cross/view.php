<!--start-breadcrumbs-->
<div class="breadcrumbs">
    <div class="container">
        <!--start-breadcrumbs-->
		<nav class="breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
				<li class='breadcrumb-item'><a class='text-nowrap' href='https://its-center.ru'><i class='fas fa-home'></i></a></li>
				<li class='breadcrumb-item'><a href='https://its-center.ru/category'>Каталог</a></li>
				<li class='breadcrumb-item'><a href='https://its-center.ru/category/filtry'>Фильтры</a></li>
				<li class='breadcrumb-item'><a href='https://its-center.ru/category/<?=$cat_prod->alias?>'><?=$cat_prod->name?></a></li>
				<li class='breadcrumb-item text-nowrap active'><?php echo "".$product->name." аналог фильтра ".$cross->cross_name." ".$crossvendor->name.""; ?></li>		
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
						<li><img src="images/product/baseimg/<?=$product->img;?>" alt="<?php echo "".$product->name." аналог фильтра ".$cross->cross_name." ".$crossvendor->name.""; ?>"></li>
						<?php foreach($gallery as $item): ?>
						<li>
							<img src="images/product/gallery/<?=$item->img;?>" />
						</li>
						<?php endforeach; ?>
					  </ul>
					</div>
					<div id="carousel" class="flexslider">
					  <ul class="slides">
						<li><img src="images/product/baseimg/<?=$product->img;?>" alt=""></li>
						<?php foreach($gallery as $item): ?>
						<li>
							<img src="images/product/gallery/<?=$item->img;?>" />
						</li>
						<?php endforeach; ?>
					  </ul>
					</div>
				
				<?php else: ?>
                    <div id="slider" class="flexslider">
					  <ul class="slides">
						<li><img src="images/product/baseimg/<?=$product->img;?>" alt=""></li>
						</ul>
					</div>
				<?php endif; ?>
				</section>
            </div>
            <div class="col-xl-5 mb-3 byp-float-rght">
              <div class="h-100 bg-light rounded-3 px-4 px-sm-5">
				<?php $administr = \R::findOne('user', 'id = ?', [$_SESSION['user']['id']]); ?>
				<?php if($administr['groups'] == "1") { ?>
					<div class="edit_prod"><a target="_blank" href="<?= ADMIN ?>/product/edit?id=<?=$product->id?>"><i class="far fa-edit"></i> Редактировать</a></div>
				<?php } ?>
				<a class="product-meta d-block fs-sm pb-2" href="/<?=$cat_prod->alias?>" title="<?=$cat_prod->name?>"><?=$cat_prod->name?></a>
                <h1 class="h3"><?php echo "".$product->name." аналог фильтра ".$cross->cross_name." ".$crossvendor->name.""; ?></h1>
                <div class="fw-normal">
					<div class="item_price_2 text-accent" id="base-price" data-base="<?=$product->price * $curr['value'];?>">
						<span>Цена: </span>
							<?php if($action->product_id): ?>
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
						
								<del style="float: left;">
									<?=$curr['symbol_left'];?>
									<?=$product->price * $curr['value'];?>
									<?=$curr['symbol_right'];?>
								</del>
							<?php else: ?>
								<?=$curr['symbol_left'];?>
								<?=$product->price * $curr['value'];?>
								<?=$curr['symbol_right'];?>
							<?php endif; ?>
					</div>	
				</div>
				<div class="fw-normal">
					<div class="vnalichie">
						<?php if($product->quantity == 0) { ?><span class="nalich_no"><i class="far fa-times-circle fa-tabls" aria-hidden="true"></i> Нет в наличии, о поступлении уточняйте у менеджера</span><?php } ?>
						<?php if($product->quantity > 0) { ?><span class="nalich_ok"><i class="fas fa-check" aria-hidden="true"></i> В наличии: <?=$product->quantity?> шт.</span>
							<?php 
								$rezerv = \R::findOne('in_stock', 'product_id = ? AND branch_id = ?', [$product->id, 9]);
								if($rezerv["quantity"] > 0) { echo "(В резерве: ".$rezerv["quantity"]." шт.)"; } 
							?>
						<?php } ?>					
						
					</div>		
				</div>				
				<div class="d-flex flex-wrap align-items-center pt-4 pb-2 mb-3 quantity">
					<?php if($product->quantity > 0) { ?>
					<?php if($_SESSION['cart'][$product->id]) { ?>
						<input class="form-control detail-quantity korzina-<?=$product->id;?> clear-korzina" style="display:none;" name="quantity" type="number" value="1" min="1" max="<?=$product->quantity?>" data-max="<?=$product->quantity?>" data-min="1">                  
						<a data-id="<?=$product->id;?>" class="btn btn-danger btn-shadow add-to-cart-link korzina-<?=$product->id;?> clear-korzina" style="display:none;" href="cart/add?id=<?=$product->id;?>" data-max="<?=$product->quantity?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus"></i> В корзину</a>
						<button class="btn btn-warning one-click korzina-<?=$product->id;?> clear-korzina" style="display:none;" type="submit">Купить в 1 клик</button>					
						<button href="cart/show" onclick="getCart(); return false;" class="btn btn-success vkorzine-<?=$product->id;?> clear-vkorzine">В корзине</button>
					<?php }else{ ?>
						<input class="form-control detail-quantity korzina-<?=$product->id;?> clear-korzina" name="quantity" type="number" value="1" min="1" max="<?=$product->quantity?>" data-max="<?=$product->quantity?>" data-min="1">                  
						<a data-id="<?=$product->id;?>" class="btn btn-danger btn-shadow add-to-cart-link korzina-<?=$product->id;?> clear-korzina" href="cart/add?id=<?=$product->id;?>" data-max="<?=$product->quantity?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus"></i> В корзину</a>
						<button class="btn btn-warning one-click korzina-<?=$product->id;?> clear-korzina" type="submit" data-toggle="modal" data-target="#Modalclick">Купить в 1 клик</button>					
						<button href="cart/show" onclick="getCart(); return false;" class="btn btn-success vkorzine-<?=$product->id;?> clear-vkorzine" style="display:none;">В корзине</button>						
					<?php } ?>
					<?php } ?>
						<button class="btn btn-primary" id="btnpdf" type="submit"><i class="fas fa-print"></i> Коммерческое предложение</button>
						<button class="btn btn-icon btn-secondary" type="submit" data-bs-toggle="tooltip" title="" data-bs-original-title="Add to Wishlist" aria-label="Add to Wishlist"><i class="far fa-heart"></i></button>
				</div>
				<div class="info">
					<?php $filters = \R::getAll('SELECT attribute_group.title, attribute_group.url_params, attribute_value.value FROM attribute_group, attribute_category, attribute_product, attribute_value WHERE attribute_category.group_id = attribute_group.id AND attribute_product.attr_id = attribute_value.id AND attribute_value.attr_group_id = attribute_group.id AND attribute_product.product_id = ? GROUP BY attribute_group.title', [$product['id']]); ?>
					<ul class="list-unstyled">
							<li >Фильтр аналог: <span itemprop="name"><?php echo "".$cross->cross_name." ".$crossvendor->name.""; ?></span></li>
							<li >Краткое наименование: <span itemprop="name"><?=$cross->cross_abbreviated_name?></span></li>							
						<?php foreach($filters as $filter) { 
							if( $filter['url_params'] != "" ) { ?>
							<li ><?=$filter['title']?>: <span itemprop="name"><a href="<?=$filter['url_params']?>/<?php echo mb_strtolower($filter['value']); ?>" title="<?=$filter['value']?>"><?=$filter['value']?></a></span></li>
							<?php }else{ ?>
							<li ><?=$filter['title']?>: <span itemprop="name"><?=$filter['value']?></span></li>
						<?php } } ?>
							<li >Модель: <span itemprop="name"><a href="/<?=$product->alias?>" title="<?=$product->name?>"><?=$product->model?></a></span></li>
            		</ul>
				</div>
              </div>
            </div>
          </section>
		  <!-- Descriptions -->
			<section class="desc-prod-inner rounded-3 bg-light">
			<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
				<li class="nav-item" role="presentation">
					<a class="nav-link active" id="pills-harakteristics-tab" data-toggle="pill" href="#pills-harakteristics" role="tab" aria-controls="pills-harakteristics" aria-selected="false">Характеристики</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="pills-opisanie-tab" data-toggle="pill" href="#pills-opisanie" role="tab" aria-controls="pills-opisanie" aria-selected="true">Описание</a>
				</li>
			<?php if($cross) { ?>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="pills-analog-tab" data-toggle="pill" href="#pills-analog" role="tab" aria-controls="pills-analog" aria-selected="false">Аналоги</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="pills-oem-tab" data-toggle="pill" href="#pills-oem" role="tab" aria-controls="pills-oem" aria-selected="false">OEM номера</a>
				</li>
			<?php } ?>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="pills-delivery-tab" data-toggle="pill" href="#pills-delivery" role="tab" aria-controls="pills-delivery" aria-selected="false">Доставка</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="pills-pay-tab" data-toggle="pill" href="#pills-pay" role="tab" aria-controls="pills-pay" aria-selected="false">Оплата</a>
				</li>
			<?php if($product->url_video) { ?>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="pills-video-tab" data-toggle="pill" href="#pills-video" role="tab" aria-controls="pills-video" aria-selected="false">Видео</a>
				</li>
			<?php } ?>
			</ul>
			<div class="tab-content" id="pills-tabContent">				
				<div class="tab-pane fade show active" id="pills-harakteristics" role="tabpanel" aria-labelledby="pills-harakteristics-tab">
				<!-- harakteristics -->
					<?php if($attribute_group): ?>
					<table class="table table-bordered table-striped">
						<?php foreach($attribute_group as $group): ?>
						
							<thead>
                                <tr>
                                    <td colspan="2" class="hide_td">
										<div class="hide_td_1"><strong><?=$group["attribute_name"]?></strong></div>											    
									</td>
                                </tr>
                            </thead>
							<tbody>
							<?php foreach($attributs as $att): ?>
								
								<tr><td><?=$att["attribute_name"]?></td><td><?=$att["attribute_text"]?></td></tr>
							
							<?php endforeach; ?>
							</tbody>
						
						<?php endforeach; ?>
					</table>
					<?php endif; ?>
				<!-- /harakteristics -->
				</div>
				<div class="tab-pane fade" id="pills-opisanie" role="tabpanel" aria-labelledby="pills-opisanie-tab">
					<p>Аналогом для фильтра <?php echo "".$cross->cross_name." ".$crossvendor->name.""; ?> является <?=$product->name?>.</p>
					<p>Фильтр EKKA (Польский бренд) соответствует европейским стандартам. Полностью совпадает по всем характеристикам с оригиналом. Успешно прошёл испытания в России, получен сертификат соответствия.</p>
					<p>Купить фильтр можно у нас в ИТС-Центр по низким ценам с доставкой во все регионы РФ. Самовывоз из 5 складов: Климовск (Московская обл.), Санкт-Петербург, Воронеж, Краснодар, Екатеринбург. Позвоните нам по телефону или напишите нам на почту, мы поможем Вам подобрать фильтр по OEM номеру, подберём по марке техники, проконсультируем по всем вопросам.</p>									
				</div>
				<?php if($cross) { ?>
					<div class="tab-pane fade" id="pills-analog" role="tabpanel" aria-labelledby="pills-analog-tab">
						<?php $cross_analog = \R::getAll('SELECT plagins_cross_vendor.name, plagins_cross.cross_name, plagins_cross.cross_abbreviated_name, plagins_cross.tip_cross, plagins_cross.equipment_vendor FROM plagins_cross, plagins_cross_vendor WHERE plagins_cross.vendor_id = plagins_cross_vendor.id AND plagins_cross.equipment_vendor = ? AND plagins_cross.product_id = ? AND plagins_cross.cross_abbreviated_name != ?', [2, $product['id'], $cross->cross_abbreviated_name]); ?>
						<table class="table table-bordered table-striped">
							<tbody>
							<?php foreach($cross_analog as $analog): ?>									
								<tr><td><?=$analog["name"]?></td><td><a href="cross/<?=$analog["cross_abbreviated_name"]?>" title="<?=$analog["cross_name"]?>"><?=$analog["cross_name"]?></a></td>
								<?php if($analog['tip_cross'] < 3) { 
									if($analog['tip_cross'] == 1) { $analog_tip = "Внешняя часть"; }
									if($analog['tip_cross'] == 2) { $analog_tip = "Внутренняя часть"; }
									if($analog['tip_cross'] == 3) { $analog_tip = "Не определено"; }
									if($analog['tip_cross'] == 4) { $analog_tip = "Комплект из 2х частей"; }
									echo "<td>$analog_tip</td>";
								
								}?>
								</tr>								
							<?php endforeach; ?>
							</tbody>
						</table>										
					</div>
					<div class="tab-pane fade" id="pills-oem" role="tabpanel" aria-labelledby="pills-oem-tab">
						<?php $cross_oem = \R::getAll('SELECT plagins_cross_vendor.name, plagins_cross.cross_name, plagins_cross.cross_abbreviated_name, plagins_cross.tip_cross, plagins_cross.equipment_vendor FROM plagins_cross, plagins_cross_vendor WHERE plagins_cross.vendor_id = plagins_cross_vendor.id AND plagins_cross.equipment_vendor = ? AND plagins_cross.product_id = ? AND plagins_cross.cross_abbreviated_name != ?', [1, $product['id'], $cross->cross_abbreviated_name]); ?>
						<table class="table table-bordered table-striped">
							<tbody>
							<?php foreach($cross_oem as $oem): ?>									
								<tr><td><?=$oem["name"]?></td><td><a href="cross/<?=$oem["cross_abbreviated_name"]?>" title="<?=$oem["cross_name"]?>"><?=$oem["cross_name"]?></a></td>
								<?php if($oem['tip_cross'] < 3) { 
									if($oem['tip_cross'] == 1) { $oem_tip = "Внешняя часть"; }
									if($oem['tip_cross'] == 2) { $oem_tip = "Внутренняя часть"; }
									if($oem['tip_cross'] == 3) { $oem_tip = "Не определено"; }
									if($oem['tip_cross'] == 4) { $oem_tip = "Комплект из 2х частей"; }
									echo "<td>$oem_tip</td>";
								} ?>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>					</div>
				<?php } ?>
				<div class="tab-pane fade" id="pills-delivery" role="tabpanel" aria-labelledby="pills-delivery-tab">
				<!-- dostavka -->
					<?php echo $op_dostavka = \ishop\App::options('option_dostavka'); ?>
				<!-- /dostavka-->
				</div>
				<!-- oplata -->
				<div class="tab-pane fade" id="pills-pay" role="tabpanel" aria-labelledby="pills-pay-tab">
					<?php echo $op_oplata = \ishop\App::options('option_oplata'); ?>
				</div>
				<!-- /oplata -->
				<?php if($product->url_video) { ?>
				<!-- video -->
				<div class="tab-pane fade video-tab" id="pills-video" role="tabpanel" aria-labelledby="pills-video-tab">
					<iframe class="video_frame" src="<?php parse_str( parse_url( $product->url_video, PHP_URL_QUERY ), $video ); echo "https://www.youtube.com/embed/".$video['v'].""; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
				<!-- /video -->
				<?php } ?>
			</div>
			</section>
		  <!-- /Descriptions -->
		  
          <!-- Related products-->
		  <?php if($related): ?>
		  <div class="related_prod">
          <section class="pb-5 mb-2 mb-xl-4 recomend-1">
            <h2 class="h3 pb-2 mb-grid-gutter text-center">С этим товаром также просматривают</h2>
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
						            <a class="card-img-top d-block overflow-hidden" href="/<?=$item["alias"];?>">
							            <img src="images/product/mini/<?=$item["img"];?>" alt="" />
						            </a>
									<?php $cat_prod = \R::findOne('category', "id = ?", [$item["category_id"]]); ?>
						            <div class="card-body py-2"><span class="product-meta d-block fs-xs pb-1"><?=$cat_prod["name"]?></span>
							            <h3 class="product-title fs-sm text-truncate"><a href="/<?=$item["alias"];?>"><?=$item["name"];?></a></h3>
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
										<span class="btn-nalichie">В наличии: <?=$product->quantity;?> шт.</span>
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
		  
        
      </section>
    </div>
</div>
<!--end-single-->

<!-- Modal 1 click -->
<form action="/product/<?=$product->alias?>" method="post" class="modal fade" id="Modalclick" tabindex="-1" role="dialog" aria-labelledby="ModalclickLabel" aria-hidden="true" data-toggle="validator">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
            <div class="modal-header">
				<h5 class="modal-title" id="exampleModalLiveLabel">Купить в 1 клик</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
      </div>
            <div class="modal-body">
				<div class="fc-3"><?=$product->name;?>
					<input type="hidden" name="name_tovar" value="<?=$product->name;?>">
					<input type="hidden" name="product_id" value="<?=$product->id;?>">
				</div>
				<div class="fc-1">
					<div class="fc_name">Ф.И.О. <span>*</span></div>
					<div class="fc_input"><input type="text" name="fio_click" placeholder="Укажите Ф.И.О." value="<?=$_SESSION['user']['name']?>" required></div>
				</div>
				<div class="fc-1">
					<div class="fc_name">Телефон <span>*</span></div>
					<div class="fc_input"><input type="tel" name="tell_click" id="phone-input2" required></div>
				</div>
				<div class="fc-1">
					<div class="fc_name">E-Mail <span>*</span></div>
					<div class="fc_input"><input type="text" name="email_click" placeholder="Укажите ваш e-mail" required></div>
				</div>
				<div class="fc-1">
					<div class="fc_name">Комментарий к заказу</div>
					<div class="fc_textarea"><textarea name="prim_click" placeholder="Комментарий к заказу (количество, время для обратного звонка или другую необходимую информацию)"></textarea></div>
				</div>
            </div>
            <div class="modal-footer">
                <table class="zayvka_1click"><tbody><tr><td><input type="checkbox" name="politika" value="pk" required></td><td style="font-size:14px;padding:0 0 0 20px">Я соглашаюсь с<br><a href="/pages/privacy">политикой конфиденциальности.</a></td></tr></tbody></table>
				<button type="submit" name="oneclick" value="<?php echo md5(date('Y-m-d')); ?>" class="btn btn-danger">Отправить</button>
            </div>
        </div>
  </div>
</form>

<?php 
function urlimagesbase64($path) {
	$type = pathinfo($path, PATHINFO_EXTENSION);
	$data = file_get_contents($path);
	$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
	return $base64;
}
$logo = urlimagesbase64('images/Logo_round.jpg');
$logos = urlimagesbase64('images/logo-2.png');
$images = urlimagesbase64('images/product/baseimg/'.$product->img.'');
?>
<script>
document.getElementById('btnpdf').onclick = function() {
var docDefinition = {
	info: {
		title:'<?=$product->name;?>',
		author:'ИТС-Центр',
		subject:'Товары',
		keywords:'Шины, диски, фильтры, на спецтехнику'
	},
	
	pageSize:'A4',
	pageOrientation:'portrait',
	pageMargins:[30,30,30,30],
	content:[
        {
			columns: [
				{
						image: "<?=$logo?>",
						width: 80
				},
				[
					{
						text:'Общество с ограниченной ответственностью «ИТС-Центр»',
						fontSize:14,
						alignment: 'center',
						margin:[0, 0, 0, 10]
					},
					{
						text:'142117, Московская область, г. Подольск, деревня Коледино, ул. Троицкая, д.1Г, стр.1, помещение В-348/49,\nтел./факс +7 (495) 424-98-90, e-mail: info@its50.ru, ИНН/КПП 5036103305/503601001, р/с 40702810901080002314\n в филиале «Центральный Банк ВТБ (ПАО), корр/с 30101810145250000411, БИК 044525411',
						alignment: 'center',
						fontSize:8,
						margin:[0, 0, 0, 15]
					}
				]			
				 
			]
			
		},
		{
			table: {
				widths:['*'],				
				body: [
					[
						{
							border: [false,'#00ffff', false, '#00ffff'],
							text:'',							
						}
					]
				]
			}
		},
		{
			columns: [
				{
					text:'Коммерческое предложение',
					fontSize:14,
					alignment: 'center',
					margin:[0, 20, 0, 0],
					bold: true
				}
			]
		},
		{
			columns: [
				{
					text:'<?=$product->name;?>',
					fontSize:14,
					alignment: 'center',
					margin:[0, 20, 0, 20],
					style: 'header',
					bold: true
				}
			]
		},
		{
			columns: [
				{
					stack: [
						{ 
							image: "<?=$images?>",
							width: 200,
							margin:[20, 20, 0, 0]
						},
						{
							text: 'Артикул: <?=$product->article?>',
							fontSize:8,
							alignment: 'center',
							margin: [0,20,0,20]
						}						
					]
				},
				[
					{
							table:{
							widths:['*'],							
							body:[	
									[{text:'Цена: <?=$curr['symbol_left'];?> <?=$product->price * $curr['value'];?>	<?=$curr['symbol_right'];?>', fontSize:14, bold: true}],									
							],
							headerRows:1
						},
						layout: 'lightHorizontalLines',
						margin: [40,0,20,20]
					},
					{
						style: 'tableExample',
						table:{
							widths:['*','auto'],							
							body:[
								<?php foreach($attribute_group as $group): ?>
								[{text: '<?=$group["attribute_name"]?>',fontSize: 10, style: 'tableHeader'}, {text: ''}],					
									<?php foreach($attributs as $att): ?>
									[{text: '<?=$att["attribute_name"]?>',fontSize: 10, style: 'tableHeader'},{text: '<?=$att["attribute_text"]?>',fontSize: 10, style: 'tableHeader'}],
									<?php endforeach; ?>
								<?php endforeach; ?>
							],
							headerRows:1
						},
						layout: 'lightHorizontalLines',
						margin: [40,0,20,0]
					}
				]			
				 
			]
			
		},
		{
			columns: [
				{
					text: '<?=$product->content;?>',
					margin: [0,50,0,30],
					fontSize:8
				}
			]				
		}
		
		
	],
	footer:[
		{				
			columns: [
				{
						image: "<?=$logos?>",
						width: 110,
						margin: [30,0]					
				},
				[
					{
						text:'Телефон: +7 (495) 424-98-90\nWhatsApp: +7 (916) 562-52-79',
						fontSize:10,
						alignment: 'left',
						margin:[90, 0, 0, 10],
						width: 280						
					}
					
				],
				[
					{
						text:'Email: info@its-center.ru\nСайт: its-center.ru',
						fontSize:10,
						alignment: 'left',
						margin:[100, 0, 0, 10],
						width: 250
					}
				]				 
			]
		}
    
	],
	styles: {
		footer: {			
			margin:[30, 0, 30, 0],
			background: '#cccccc'
		}
	}
};


pdfMake.createPdf(docDefinition).download('KP_<?=$product->alias?>.pdf');
}
</script>
