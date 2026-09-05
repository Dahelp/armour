<?php $inseo_prod = $inseoProd; ?>
<!--start-breadcrumbs-->
<div class="storefront-breadcrumb">
	<div class="col-full">
		<nav class="woocommerce-breadcrumb" aria-label="breadcrumbs">
			<?=$breadcrumbs;?>
		</nav>
	</div>
</div>
<!--end-breadcrumbs-->
<!--prdt-starts-->
<?php $curr = \ishop\App::$app->getProperty('currency'); ?>
<div id="content" class="site-content left-sidebar" tabindex="-1">
    <div class="col-full">
		<!-- right content -->
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<header class="woocommerce-products-header" itemscope="" itemtype="https://schema.org/Product">
					<h1 itemprop="name"><?=$category->name?></h1>
				</header>
				<?php if($subcategories) { ?>
					<div class="sub-category sub-category-custom ">
						<div class="cat-tab">
							<div class="cat-tab__content sub-category-hide-content-mobile">
								<div id="catalog" class="active">
									<div class="woocommerce">
										<ul class="products products__odd">
											<?php foreach($subcategories as $podcat) { ?>
												<li class="product-category">
													<a href="<?=$podcat["alias"]?>">
												<?php if (!empty($podcat['img'])): ?>
													<img src="/images/category/baseimg/<?=htmlspecialchars($podcat['img'], ENT_QUOTES, 'UTF-8')?>" alt="категория <?=htmlspecialchars(mb_strtolower($podcat['name']), ENT_QUOTES, 'UTF-8')?>">
												<?php endif; ?>
														<h2 class="woocommerce-loop-category__title"><?=$podcat["name"]?></h2>
													</a>
												</li>
											<?php } ?>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if($ids) { ?>
					<div class="filters">
						<section class="d-md-flex justify-content-between align-items-center pb-4">
							<div class="w_sidebar col-md-12 fltr">
									<?php new \app\widgets\filter\Filter($ids);	?>
							</div>            
						</section>
					</div>
				<?php } ?>
				<div class="table_wrap facetwp-template" style="display: block;">
					<div class="casters-block product-one">
						<table>
							<thead>								
								<?php require APP . '/widgets/product/table_'.$category->table_alt.'_tpl.php'; ?>	
							</thead>
							<tbody>
								<?php foreach($products as $product): ?>
									
					<?php new \app\widgets\product\Product($product, $curr, $productAttributes[(int)$product['id']] ?? [], $brands[(int)$product['brand_id']] ?? [], 'product_table_'.$category->table_alt.'_tpl.php'); ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="text-center" style="width:100%">                            
					<?php if($pagination->countPages > 1): ?>
						<?=$pagination;?>
					<?php endif; ?>
				</div>
				<div class="catalog_text" itemprop="description"><?=$category->content?></div>
			</main>
		</div>
		<!-- left menu -->
		<div id="secondary" class="widget-area" role="complementary">
			<div id="nav_menu-2" class="widget widget_nav_menu">
                <span>Каталог продукции</span>				
					<ul id="menu-katalog" class="menu">
					<?php new \app\widgets\menu\Menusite($alias); ?>
					</ul>
					<script>
						// Получаем текущий URL страницы
						const currentUrl = '<?=$alias?>';

						// Находим все ссылки на странице
						const links = document.querySelectorAll('a');

						// Перебираем ссылки и проверяем совпадение
						links.forEach(link => {
						  if (link.getAttribute('href') === currentUrl) {
							link.classList.add('active'); // Добавляем класс для подсветки
						  }
						});
				  </script>	
			</div>
		</div>
    </div>
</div>
<!--product-end-->
