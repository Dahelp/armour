
<?=\app\models\Breadcrumbs::render(array_merge(
	$type->hide_anons === 'show' ? [['label' => $type->name, 'url' => $type->param_url]] : [],
	[['label' => $find->name]]
))?>
<div id="content" class="site-content" tabindex="-1">
	<div class="col-full">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<?php if(!empty($find)): 
						if($type->hide_clicks == "show") { \R::exec("UPDATE contents SET clicks = clicks+1 WHERE id = ?", [$find->id]); } ?>
				<article itemscope itemtype="http://schema.org/NewsArticle" id="post-<?=$find->id?>" class="post-<?=$find->id?> page type-page status-publish hentry">
					<header class="entry-header">
						<h1 class="entry-title"><?=$find->name;?></h1>
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
											<img src="images/contents/baseimg/<?=$find->img;?>" alt="" />
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
						<?php if (!empty($related)): ?>
<section class="related-products"><h2>Связанные товары</h2>
<?php \app\widgets\product\Product::listing($related, $curr, $cardAttributes ?? [], $cardBrands ?? []); ?>
</section>
<?php endif; ?>
				 
					<?php endif; ?>		
				</div>
				</article>            
			</main>
		</div>
	</div>
</div>	
