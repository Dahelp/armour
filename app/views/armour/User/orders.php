<!--start-breadcrumbs-->
<div class="breadcrumbs">
    <div class="container">
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
				<li class="breadcrumb-item"><a href="<?= PATH ?>/user/cabinet">Личный кабинет</a></li>
                <li class="breadcrumb-item active">История заказов</li>
            </ol>
		</nav>
    </div>
</div>
<!--end-breadcrumbs-->
<?php $curr = \ishop\App::$app->getProperty('currency'); ?>
<?php $order_prefix = \ishop\App::options('order_prefix'); ?>
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
                        <h3>Заказы</h3>
                    </div>
					<?php if($orders): ?>
						<div class="table-responsive">
							<table class="table table-bordered table-hover table-striped table-condensed" style="background: #fff;">
								<thead>
								<tr>
									<th style="width: 10%">ID</th>
									<th style="width: 30%">Статус</th>
									<th style="width: 20%">Сумма</th>
									<th style="width: 20%">Дата создания</th>
									<th style="width: 20%">Дата изменения</th>
								</tr>
								</thead>
								<tbody>
								<?php foreach($orders as $order): ?>
									<?php $status = \R::findOne('order_status', 'id = ?', [$order['status']]);
									if($order['status'] == '7'){
										$class = 'bg-danger';										
									}else{									
										$class = 'bg-success';									
									}
									?>
									<tr class="<?=$class;?>">
										<td><a href="user/order?id=<?=$order["id"];?>"><?=$order_prefix?><?=$order["id"];?></a></td>
										<td><?=$status['status_name'];?></td>
										<td><?=$curr['symbol_left'];?> <?=$order["sum"]?> <?=$curr['symbol_right'];?></td>
										<td><?=$order["date"];?></td>
										<td><?=$order["update_at"]?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else: ?>
						<p class="text-danger">Вы пока не совершали заказов.</p>
					<?php endif; ?>
				</div>
            </div>
        </div>
    </div>
</div>
<!--product-end-->