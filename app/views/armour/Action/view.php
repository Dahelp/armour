
<?=\app\models\Breadcrumbs::render(array_merge(
	$type->hide_anons ? [['label' => $type->name, 'url' => $type->param_url]] : [],
	[['label' => $find->name]]
))?>
<div class="contents">
    <div class="container">
		<div class="row">		
			<?php if(!empty($find)): 
				if($type->hide_clicks == "show") { \R::exec("UPDATE contents SET clicks = clicks+1 WHERE id = ?", [$find->id]); } ?>
			
				<div class="col-md-12">
					<div class="bg-light rounded-3">
						<div class="register-top heading">
							<h1><?=$find->name;?></h1>
						</div>
						<?php if($type["hide_date_post"] == "show") { ?>
							<div class="cont_info_data">
								<?php echo \ishop\App::contdate($find["date_post"]); ?>
							</div>
						<?php } ?>
						<div class="cont-inner">
							<?php if($find->img) { ?>
								<div class="cont-img">
									<img src="images/contents/baseimg/<?=$find->img;?>" alt="" />
								</div>
							<?php } ?>
							<div class="cont-desc">
								<?=$find->content;?>
							</div>
						</div>
					</div>					
				</div>
			<?php endif; ?>		
		</div>
	</div>	
</div>		
