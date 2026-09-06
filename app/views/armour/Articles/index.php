
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
			<div id="primary" class="content-area articles-content-area">
				<main id="main" class="site-main">		
					<header class="page-header">
						<h1 class="entry-title"><span><?=$type->name;?></span></h1>
					</header>
					<div class="articles-grid">
						<?php foreach($conts as $item) { ?>
							<article class="article-card">
								<?php
								$articleImage = basename((string) $item['img']);
								$articleImageUrl = '';
								if ($articleImage !== '' && is_file(WWW . '/images/contents/mini/' . $articleImage)) {
									$articleImageUrl = PATH . '/images/contents/mini/' . rawurlencode($articleImage);
								} elseif (preg_match('~<img[^>]+src=["\']([^"\']+)["\']~i', (string) $item['content'], $imageMatch)) {
									$imagePath = (string) parse_url(html_entity_decode($imageMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), PHP_URL_PATH);
									if (str_starts_with($imagePath, '/images/contents/legacy/') && is_file(WWW . $imagePath)) {
										$articleImageUrl = PATH . $imagePath;
									}
								}
								?>
								<a class="article-card__image" href="<?=htmlspecialchars($type->param_url . '/' . $item['alias'], ENT_QUOTES, 'UTF-8');?>" tabindex="-1" aria-hidden="true">
										<?php if($articleImageUrl !== '') { ?>
											<img src="<?=htmlspecialchars($articleImageUrl, ENT_QUOTES, 'UTF-8');?>" alt="<?=htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');?>" loading="lazy" />
										<?php } else { ?>
											<span class="article-card__placeholder"><span class="article-card__wheel" aria-hidden="true"></span><strong>ТЕХШИНА</strong></span>
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
