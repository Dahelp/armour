
<div class="storefront-breadcrumb">
	<div class="col-full">
		<nav class="woocommerce-breadcrumb" aria-label="breadcrumbs">
			<a href="<?= PATH ?>">Главная</a>
			<span class="breadcrumb-separator"> / </span><?=$type->name;?>
		</nav>
	</div>
</div>
<div id="content" class="site-content" tabindex="-1">
    <div class="col-full">
		<div class="woocommerce"></div>
			<div id="primary" class="content-area">
				<main id="main" class="site-main">		
					<header class="page-header">
						<h1 class="entry-title"><span><?=$type->name;?></span></h1>
					</header>
					<div class="articles-grid">
						<?php foreach($conts as $item) { ?>
							<article class="article-card">
								<a class="article-card__image" href="<?=htmlspecialchars($type->param_url . '/' . $item['alias'], ENT_QUOTES, 'UTF-8');?>">
										<?php if($item["img"] !="") { ?>
											<img src="<?=PATH;?>/images/contents/mini/<?=htmlspecialchars($item['img'], ENT_QUOTES, 'UTF-8');?>" alt="<?=htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');?>" loading="lazy" />
										<?php } else { ?>
											<img src="<?=PATH;?>/images/no_image.jpg" alt="" loading="lazy" />
										<?php } ?>
								</a>
								<div class="article-card__body">
										<?php if($type["hide_date_post"] == "show") { ?>
											<time class="article-card__date" datetime="<?=date('Y-m-d', strtotime($item['date_post']));?>">
												<?php echo \ishop\App::contdate($item["date_post"]); ?>
											</time>
										<?php } ?>
										<h2 class="article-card__title"><a href="<?=htmlspecialchars($type->param_url . '/' . $item['alias'], ENT_QUOTES, 'UTF-8');?>"><?=htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');?></a></h2>
										<?php if (trim((string) $item['anons']) !== '') { ?>
											<p class="article-card__excerpt"><?=htmlspecialchars(mb_strimwidth(strip_tags($item['anons']), 0, 180, '…'), ENT_QUOTES, 'UTF-8');?></p>
										<?php } ?>
									</div>
							</article>
						<?php } ?>
					</div>
					<div class="blog-nav">
						<?php if($pagination->countPages > 1): ?>
							<?=$pagination;?>
						<?php endif; ?>
					</div>
			</main>
		</div>
	</div>
	<div class="col-full"></div>
</div>
