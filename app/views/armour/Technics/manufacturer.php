<?=\app\models\Breadcrumbs::render([
	['label' => 'Каталог техники', 'url' => PATH . '/technics'],
	['label' => 'Производители ' . $type['seoname_1'], 'url' => PATH . '/technics/type/' . $type['alias']],
	['label' => \ishop\App::upFirstLetter($type['seoname_3']) . ' ' . $manufacturer['name']],
])?>
<!--start-single-->
<div class="single contact">
    <div class="container">
		<div class="register-top heading">
			<h1><?php echo \ishop\App::upFirstLetter($type["seoname_3"]);?> <?=$manufacturer["name"]?></h1>
		</div>
        <div class="technics-inner row">
			<?php foreach($technics as $item) { ?>
			<a href="technics/<?=$item["alias"]?>" title="<?=$item["model"]?>" class="col-md-3">
				<div class="p_cat">
					<div class="cb-img">
						<?php if($item["img"]) { ?>
							<img src="images/technics/baseimg/<?=$item["img"]?>" alt="<?=$item["model"]?>" title="<?=$item["model"]?>" width="150">
						<?php }else{ ?>
							<img src="images/no_image.jpg" alt="" title="" width="150">
						<?php } ?>
					</div>
					<div class="cb-span">
						<h2><?=$item["model"]?></h2>
					</div>
				</div>
			</a>
			<?php } ?>
		</div>
		<?php if($manufacturer["content"]) { ?>
		
			<div class="catalog_text col-md-12">
				<?=$manufacturer["content"]?>
			</div>
		
		<?php } ?>
    </div>
</div>
<!--end-single-->
