
<div class="storefront-breadcrumb">
	<div class="col-full">
		<nav class="woocommerce-breadcrumb" aria-label="breadcrumbs">
			<a href="<?= PATH ?>">Главная</a>
			<?php if($type->hide_anons=="show") { ?>
				<span class="breadcrumb-separator"> / </span>
				<a href="<?=PATH . '/' . h($type->param_url)?>"><?=h($type->name);?></a>
			<?php } ?>
			<span class="breadcrumb-separator"> / </span>
			<?=h($find->name);?>
		</nav>
	</div>
</div>
<div id="content" class="site-content" tabindex="-1">
	<div class="col-full">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<?php if(!empty($find)): 
						if($type->hide_clicks == "show") { \R::exec("UPDATE contents SET clicks = clicks+1 WHERE id = ?", [$find->id]); } ?>
				<article itemscope itemtype="https://schema.org/Article" id="post-<?=(int)$find->id?>" class="post-<?=(int)$find->id?> page type-page status-publish hentry">
					<header class="entry-header">
						<h1 class="entry-title"><?=h($find->name);?></h1>
					</header>
						<div class="entry-content">							
							<?php if($type["hide_date_post"] == "show") { ?>
								<div class="cont_info_data">
									<time itemprop="datePublished" datetime="<?=date("c", strtotime($find["date_post"]))?>"><?php echo \ishop\App::contdate($find["date_post"]); ?></time>
								</div>
							<?php } ?>
							<meta itemprop="dateModified" content="<?=date("c", strtotime($find["date_last_modified"]))?>">							
							<div class="cont-inner">
								<?php if($find->img) { ?>
									<?php if($find->img_hide == "show") { ?>
										<div class="cont-img">
									<img src="<?=PATH?>/images/contents/baseimg/<?=h($find->img);?>" alt="<?=h($find->name);?>" loading="lazy" decoding="async" />
										</div>
									<?php } ?>
								<?php } ?>
								<div class="cont-desc" itemprop="articleBody">
									<?=$find->content;?>
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
								
								<?php foreach($related as $product): ?>
								
									<div class="swiper-slide">					                        
										
												<?php new \app\widgets\product\Product($product, $curr, 'product_tpl.php'); ?>
																
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
				</article>            
			</main>
		</div>
	</div>
</div>	
