
<!--prdt-starts-->
<div class="prdt">
    <div class="container">
		<?=\app\models\Breadcrumbs::render([['label' => $params->title, 'url' => $params->url_params], ['label' => $find->value]])?>
		<section class="align-items-center">
            <h1 class="h2 mb-3 mb-md-0 me-3">
				<?php
					if($inseo->name) { 					
						echo $name = \ishop\App::seoreplacefilter($inseo->name, $find->id);
					}
					else { echo $find->name; }
				?>
			</h1>			
        </section>		
			<div class="prdt-top">
            <div class="col-md-12">                
				<?php if(!empty($products)): ?>					
                    <div class="row g-0 mx-n2 product-one">
                        <?php $curr = \ishop\App::$app->getProperty('currency'); ?>
                        <?php foreach($products as $product): ?>
                            <div class="col-xl-3 col-lg-6 col-md-4 col-sm-6 mb-3">
					            <?php new \app\widgets\product\Product($product, $curr, 'product_tpl.php'); ?>
				            </div>
                        <?php endforeach; ?>
                        <div class="clearfix"></div>
                        <div class="text-center">                            
                            <?php if($pagination->countPages > 1): ?>
                                <?=$pagination;?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning product-note"><?php if(!$params->notproduct) { ?>В этой категории товаров пока нет...<?php }else{ ?><?=$params->notproduct?><?php } ?></div>
                <?php endif; ?>
				<?php if(!empty($products)): ?>
					<?php foreach($products as $prod){ $value .= "".$prod["id"].","; } $values = rtrim($value,",");?>			
					<div class="catalog_text">
						<?php
							echo $find->content;
							if($inseo->content) { 					
								echo $content = \ishop\App::seoreplacetiposize($inseo->content, $values);
							}								
						?>							
					</div>
				<?php endif; ?>
            </div>
            <div class="clearfix"></div>
        </div>		
	</div>
</div>
<!--product-end-->		
