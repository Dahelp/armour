<!--start-breadcrumbs-->
<div class="breadcrumbs">
    <div class="container">
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
				<li class="breadcrumb-item"><a href="<?= PATH ?>/user/cabinet">Личный кабинет</a></li>
                <li class="breadcrumb-item active">Закладки</li>
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
                        <h3>Закладки</h3>
                    </div>
					<?php if($bookmarks): ?>
						<div class="table-responsive">
							<table class="table table-bordered table-hover table-striped table-condensed" style="background: #fff;">
								<thead>
								<tr>
									<th style="width: 8%">Фото</th>
									<th style="width: 5%">Артикул</th>
									<th style="width: 47%">Наименование</th>									
									<th style="width: 10%">Наличие</th>
									<th style="width: 5%">Цена</th>
									<th style="width: 5%"></th>
								</tr>
								</thead>
								<tbody>
								<?php foreach($bookmarks as $item): ?>									
									<tr>
										<td><img src="images/product/mini/<?=$item["img"]?>" /></td>
										<td><?=$item["article"]?></td>
										<td><a href="/<?=$item["alias"]?>"><?=$item["name"]?></a></td>
										<td><?=$item["quantity"]?></td>
										<td><?=$curr['symbol_left'];?> <?=$item["price"]?> <?=$curr['symbol_right'];?></td>
										<td><a href="user/bookmarks-delete?id=<?=$item["id"]?>"><i class="fas fa-times-circle text-danger"></i></a></td>
									</tr>
								<?php endforeach; ?>
								</tbody>															
							</table>
						</div>
					<?php else: ?>
						<p class="text-danger">Вы пока не добавляли товары в закладки.</p>
					<?php endif; ?>
				</div>
            </div>
        </div>
    </div>
</div>
<!--product-end-->
