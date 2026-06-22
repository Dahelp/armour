
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
					<div class="flex flex-wrap blog">
						<?php foreach($conts as $item) { ?>
							<div class="col-md-3 cont-one">
								<div class="cont_ht border border-grey">
									<div class="cont_blok_img">
										<?php if($item["img"] !="") { ?>
											<img src="images/contents/mini/<?=$item["img"]?>" alt="<?=$item["name"]?>" title="<?=$item["name"]?>" style="width:100%" />
										<?php } else { ?>
											<img src="images/no_image.jpg" alt="" />
										<?php } ?>
									</div>
									<div class="cont_info">
										<?php if($type["hide_date_post"] == "show") { ?>
											<div class="cont_info_data">
												<?php echo \ishop\App::contdate($item["date_post"]); ?>
											</div>
										<?php } ?>
										<div class="cont_info_name">
											<a href="<?=$type->param_url;?>/<?=$item["alias"];?>"><?=$item["name"];?></a>
										</div>
										<div class="cont_info_anons">
											<?php echo mb_strimwidth($item["anons"], 0, 200, "...");?>
										</div>
									</div>
								</div>
							</div>
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
