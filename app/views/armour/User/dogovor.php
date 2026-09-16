<?=\app\models\Breadcrumbs::render([['label' => 'Личный кабинет', 'url' => PATH . '/user/cabinet'], ['label' => 'Договор']])?>
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
