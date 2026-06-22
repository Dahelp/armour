<!--start-breadcrumbs-->
<div class="breadcrumbs">
    <div class="container">
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
				<li class="breadcrumb-item"><a href="<?= PATH ?>/user/cabinet">Личный кабинет</a></li>
                <li class="breadcrumb-item active">Договор</li>
            </ol>
		</nav>
    </div>
</div>
<!--end-breadcrumbs-->
<!--prdt-starts-->
<div class="prdt">
    <div class="container">
        <div class="prdt-top">
            <div class="col-md-12 cab-inner">
				<div class="col-md-3 float-left p-3">
					<?php new \app\widgets\cabinet\Cabinet('cabinet_tpl.php'); ?>
				</div>
                <div class="col-md-9 float-left p-3">
                    <div class="register-top heading">
                        <h3>Договор</h3>
                    </div>
					<?php if($dogovor): ?>
						<div class="table-responsive">
							
						</div>
					<?php else: ?>
						<p class="text-danger">Договор пока не заключён.</p>
					<?php endif; ?>
				</div>
            </div>
        </div>
    </div>
</div>
<!--product-end-->