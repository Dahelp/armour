<!--start-breadcrumbs-->
<div class="breadcrumbs">
    <div class="container">
        <!--start-breadcrumbs-->
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class='fas fa-home'></i></a></li>
                <li class="breadcrumb-item active">Корзина</li>
            </ol>
		</nav>
    </div>
</div>
<!--end-breadcrumbs-->
<!--prdt-starts-->
<div class="prdt">
    <div class="container">
		<?php if(!empty($_SESSION['cart'])):?>
			<form method="post" action="cart/checkout" role="form" aria-hidden="true" data-toggle="validator" enctype="multipart/form-data">
			<div class="product-cart">
			<div class="prdt-top">			
            <div class="col-md-12">
                <div class="bg-light rounded-3 py-5 px-4 px-xxl-5">
                    <div class="register-top heading">
                        <h2>Оформление заказа</h2>
                    </div> 
					
                    <div id="prodcart" class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th>Фото</th>
                                    <th>Наименование</th>
                                    <th>Кол-во</th>
                                    <th>Цена</th>
                                    <th><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($_SESSION['cart'] as $id => $item): ?>
                                    <tr>
                                        <td><a href="/<?=$item['alias'] ?>"><img src="images/product/mini/<?= $item['img'] ?>" alt="<?=$item['name'] ?>"></a></td>
                                        <td><a href="/<?=$item['alias'] ?>"><?=$item['name'] ?></a></td>
                                        <td style="text-align:center">
											<?php if($item['qty'] > 1) { ?><span data-id="<?=$id;?>" class="my-minus-<?=$id;?> my-minus"><i class="fa fa-minus" aria-hidden="true"></i></span><?php } ?>
												<span class="qty-item"><?=$item['qty'];?></span>
											<?php if($item['qty'] < $item['max']) { ?><span data-id="<?=$id;?>" class="my-plus-<?=$id;?> my-plus"><i class="fa fa-plus" aria-hidden="true"></i></span><?php } ?>
										</td>
                                        <td><?=$item['price'] ?></td>
                                        <td><span data-id="<?=$id;?>" class="glyphicon glyphicon-remove text-danger del-items" aria-hidden="true"><i class="fas fa-times"></i></span></td>
                                    </tr>
                                <?php endforeach;?>
                                <tr>
                                    <td>Итого:</td>
                                    <td colspan="4" class="text-right cart-qty"><?=$_SESSION['cart.qty'] ?></td>
                                </tr>
                                <tr>
                                    <td>На сумму:</td>
                                    <td colspan="4" class="text-right cart-sum"><?= $_SESSION['cart.currency']['symbol_left'] . $_SESSION['cart.sum'] . " {$_SESSION['cart.currency']['symbol_right']}" ?></td>
                                </tr>
                                </tbody>
                            </table>
						</div>
                    </div>                                            
				<div class="product-info">
						<div class="col-md-6 bg-light px-xxl-5" id="prodinfo">
							<div class="register-top heading">
								<h2>Габаритные размеры</h2>
							</div>
							<ul class="list-unstyled fs-sm pt-4 pb-2 border-bottom">
								<li class="d-flex justify-content-between align-items-center"><span class="me-2">Вес, кг:</span><span class="text-end fw-medium simpleCart_weight"><?=$_SESSION['cart.weight']?></span></li>
								<li class="d-flex justify-content-between align-items-center"><span class="me-2">Объем, м3:</span><span class="text-end fw-medium simpleCart_volume"><?=$_SESSION['cart.volume']?></span></li>
							</ul>                            
						</div>
				</div>
            </div>
		</div>
		</div>
        <div class="prdt-top mt-5">
			<div class="row gx-1 gy-3">
			
				<div class="col-md-5 bg-light rounded-3 py-5 px-4 px-xxl-5 mr-4" style="width: 100%;">
					<div class="register-top heading">
                        <h2>Способы получения</h2>
                    </div>
						<select class="form-control" name="dostavka_id" id="dostavka_id" onchange="val()">						
							<option value="1">Выберите способ получения</option>
						    <?php $dostavka = \R::getAll("SELECT * FROM dostavka WHERE hide='show'");
									foreach($dostavka as $ds){ ?>
										<option value="<?=$ds["id"]?>"><?=$ds["name"]?></option>
							<?php } ?>							
						</select>
						<p></p>
                        <div class="notauth" id="another_transport" style="display:none">
						    <select class="form-control" name="transport_id">
								<option value="">Выберите транспортную компанию</option>
								<option value="">-------------------------</option>
								<?php $transport = \R::getAll("SELECT * FROM transport_company WHERE hide='show'");
									foreach($transport as $tr){ ?>
										<option value="<?=$tr["id"]?>"><?=$tr["name"]?></option>
									<?php } ?>
							</select>
						</div>
						<p></p>
                        <div class="notauth" id="another_sklad" style="display:none">
						    <select class="form-control" name="branch_id">
								<option value="">Выберите место самовывоза</option>								
								<?php $branch = \R::getAll("SELECT * FROM branch_office WHERE hide='show'");
									foreach($branch as $br){ ?>
										<option value="<?=$br["branch_id"]?>">г. <?=$br["branch_name"]?></option>
									<?php } ?>
							</select>
						</div>
                        <div class="zakaz-inpt" id="another_city" style="display:none">
							<select class="form-control" name="city_id">
								<option value="0">Выберите город</option>
								<option value="">-------------------------</option>
								<?php $cities = \R::getAll("SELECT * FROM cities ORDER BY city_name");									
									foreach($cities as $city){ ?>
										<option value="<?=$city["city_id"]?>"><?=$city["city_name"]?></option>
									<?php } ?>
							</select>
						</div>
						<p></p>
						<div id="another_adress" style="display:none">
							<input type="text" name="address" class="form-control" id="address" placeholder="Адрес доставки товаров">							
						</div>
                            
                </div>					
			</div>
		</div>
        <div class="prdt-top mb-5">			
			<div class="col-xl-7 pt-5">
				<div class="bg-light rounded-3 py-5 px-4 px-xxl-5">
					<div class="register-top heading">
                        <h2>Информация для связи</h2>
                    </div>
					
					<div class="row gx-4 gy-3">	
						<?php 
							$compusers = \R::findOne('company', 'user_id = ?', [$_SESSION['user']['id']]);
							if(!$compusers) { ?>
							<div class="col-sm-6">
								<label class="form-label" for="name">Вид <span class="text-danger">*</span></label>
								<select name="groups" class="form-control" id="vidurlface" onchange="val()">
									<option value = "3" selected="selected">Выберите вид клиента</option>
									<option value = "3">Физическое лицо</option>
									<option value = "4">Юридическое лицо</option>								
								</select>
							</div>
							<div id="vid_urlface" style="display:none">
								<div class="col-sm-6">
									<label class="form-label" for="nds">Система налогообложения</label>								
									<select name="nds" class="form-control">
										<option value = "" selected="selected">Выберите систему налогообложения</option>
										<option value = "1">с НДС</option>
										<option value = "2">без НДС</option>								
									</select>							
								</div>
								<p></p>
								<div class="col-sm-6">
									<label class="form-label" for="dogovor">Условия поставки</label>								
									<select name="dogovor" class="form-control">
										<option value = "" selected="selected">Выберите условия поставки</option>
										<option value = "1">Договор</option>
										<option value = "2">Счёт-договор</option>								
									</select>							
								</div>
								<p></p>
								<div class="col-sm-6">
									<label class="form-label" for="rekvizity">Прикрепить реквизиты</label>								
									<input class="btn btn-default" type="file" name="rekvizity" />							
								</div>
							</div>
							<p></p>
							<?php } ?>
                        <?php if(!isset($_SESSION['user'])): ?>    
                                    <div class="col-sm-6">
                                        <label class="form-label" for="name">Имя <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" id="name" placeholder="Имя" value="<?= isset($_SESSION['form_data']['name']) ? $_SESSION['form_data']['name'] : '' ?>" required>
                                        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    </div>
									<div class="col-sm-6">
                                        <label class="form-label" for="telefon">Телефон <span class="text-danger">*</span></label>
                                        <input type="text" name="telefon" class="form-control" id="phone-input" required>
                                        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" id="email" placeholder="Email" value="<?= isset($_SESSION['form_data']['email']) ? $_SESSION['form_data']['email'] : '' ?>" required>
                                        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    </div>                                    
                                <?php endif; ?>
                                <div class="col-sm-12">
                                    <label class="form-label" for="note">Комментарий</label>
                                    <textarea name="note" class="form-control"></textarea>
                                </div>
								<div class="pt-2">
									<button class="btn btn-primary d-block w-100" type="submit">Оформить заказ</button>
								</div>
                            
					</div>
					
                </div>
			</div>
			
			<div class="col-xl-5 pt-5 pl-4">
				<!-- block right-->				
			</div>		
			
        </div>
		</form>
        <?php if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']); ?>
		<?php else: ?>
                        <h3>Корзина пуста</h3>
        <?php endif;?>
    </div>
</div>
<!--product-end-->
