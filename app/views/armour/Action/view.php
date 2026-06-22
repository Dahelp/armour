
<div class="breadcrumbs">
    <div class="container">
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
				<?php if($type->hide_anons) { ?>
					<li class="breadcrumb-item active"><a href="<?=$type->param_url?>"><?=$type->name;?></a></li>
				<?php } ?>
                <li class="breadcrumb-item active"><?=$find->name;?></li>
            </ol>
		</nav>
    </div>
</div>
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
