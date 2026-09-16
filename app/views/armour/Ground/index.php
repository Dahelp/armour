
<?=\app\models\Breadcrumbs::render([['label' => $type->page_name]])?>
<div class="contents">
    <div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php if(!empty($groups)): ?>
					<div class="register-top heading">
						<h1><?=$type->page_name;?></h1>
					</div>
					<div class="cont-inner">
						<div class="group-filtr">
						<?php foreach($groups as $group): ?>
							<div class="filtr-one">
								<a href="<?=$type->url_params;?>/<?=$group["alias"]?>" title="<?=$group["value"]?>">
									<?php if($group->img) { ?><div class="filtrs-img"><img src="images/filtrs/baseimg/<?=$group["img"]?>" alt="<?=$group["value"]?>" title="<?=$group["value"]?>" width="150" height="120"></div><?php } ?>
									<div class="filtrs-value"><?=$group["value"]?></div>
								</a>
							</div>
						<?php endforeach; ?>
						</div>
					</div>
					<div class="catalog_text col-md-12">
						<?=$type->seo_content;?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>	
</div>
