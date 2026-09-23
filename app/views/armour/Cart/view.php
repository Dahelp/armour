<?=\app\models\Breadcrumbs::render([['label' => 'Корзина']])?>
<!--prdt-starts-->
<div class="prdt">
    <div class="container">
		<?php if(!empty($_SESSION['cart'])):?>
			<form method="post" action="cart/checkout" role="form" data-toggle="validator" enctype="multipart/form-data">
			<div class="product-cart cart-page-card">
			<div class="prdt-top">			
            <div class="col-md-12">
                <div class="bg-light rounded-3 py-5 px-4 px-xxl-5">
                    <div class="register-top heading">
                        <h2>Оформление заказа</h2>
                        <button type="button" class="cart-clear-button" onclick="clearCart()">Очистить корзину</button>
                    </div> 
					
                    <div id="prodcart" class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                <tr>
                                    <th>Фото</th>
                                    <th>Наименование</th>
                                    <th>Кол-во</th>
                                    <th>Цена</th>
                                    <th><span class="visually-hidden">Удалить</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($_SESSION['cart'] as $id => $item): ?>
                                    <tr>
                                        <td><a href="/<?=$item['alias'] ?>"><img src="images/product/mini/<?= $item['img'] ?>" alt="<?=$item['name'] ?>"></a></td>
                                        <td><a href="/<?=$item['alias'] ?>"><?=$item['name'] ?></a></td>
                                        <td style="text-align:center">
											<div class="cart-quantity-control quantity-block">
												<button type="button" data-id="<?=$id;?>" class="quantity-arrow-minus my-minus-<?=$id;?> my-minus" aria-label="Уменьшить количество"><span aria-hidden="true">-</span></button>
												<span class="qty-item qty-item-<?=$id;?>"><?=$item['qty'];?></span>
												<?php if($item['qty'] < $item['max']) { ?><button type="button" data-id="<?=$id;?>" class="quantity-arrow-plus my-plus-<?=$id;?> my-plus" aria-label="Увеличить количество"><span aria-hidden="true">+</span></button><?php } ?>
											</div>
										</td>
                                        <td><?=$item['price'] ?></td>
                                        <td><button type="button" data-id="<?=$id;?>" class="del-items cart-remove-button" aria-label="Удалить товар"><span aria-hidden="true">×</span></button></td>
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
						<div class="cart-dimensions-card bg-light px-xxl-5" id="prodinfo">
							<div class="register-top heading">
								<h2>Габаритные размеры</h2>
							</div>
							<ul class="list-unstyled fs-sm pt-4 pb-2 border-bottom">
								<?php if ((float)($_SESSION['cart.weight'] ?? 0) > 0): ?><li class="d-flex justify-content-between align-items-center"><span class="me-2">Вес, кг:</span><span class="text-end fw-medium simpleCart_weight"><?=$_SESSION['cart.weight']?></span></li><?php endif; ?>
								<?php if ((float)($_SESSION['cart.volume'] ?? 0) > 0): ?><li class="d-flex justify-content-between align-items-center"><span class="me-2">Объём, м³:</span><span class="text-end fw-medium simpleCart_volume"><?=$_SESSION['cart.volume']?></span></li><?php endif; ?>
							</ul>                            
						</div>
				</div>
            </div>
		</div>
		</div>
        <div class="prdt-top mt-5 cart-section cart-delivery-section">
			<div class="row gx-1 gy-3">
			
				<div class="col-md-12 bg-light rounded-3 py-5 px-4 px-xxl-5 mr-4">
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
        <div class="prdt-top mb-5 cart-section cart-contact-section">			
			<div class="col-xl-12 pt-5">
				<div class="bg-light rounded-3 py-5 px-4 px-xxl-5">
					<div class="register-top heading">
                        <h2>Информация для связи</h2>
                    </div>
					
					<div class="row gx-4 gy-3">	
						<?php 
							$compusers = !empty($_SESSION['user']['id']) ? \R::findOne('company', 'user_id = ?', [$_SESSION['user']['id']]) : null;
							if(!$compusers) { ?>
							<div class="col-sm-6">
								<label class="form-label" for="name">Вид <span class="text-danger">*</span></label>
								<select name="groups" class="form-control" id="vidurlface" onchange="val()">
									<option value = "3" selected="selected">Выберите вид клиента</option>
									<option value = "3">Физическое лицо</option>
									<option value = "4">Юридическое лицо</option>								
								</select>
							</div>
							<div id="vid_urlface" class="cart-company-fields" style="display:none">
								<div class="col-sm-12">
									<div class="cart-company-panel">
										<div class="row gx-4 gy-3">
											<div class="col-sm-6">
												<label class="form-label" for="nds">Система налогообложения <span class="text-danger">*</span></label>
												<select name="nds" class="form-control cart-company-required" id="nds">
													<option value = "" selected="selected">Выберите систему налогообложения</option>
													<option value = "1"<?= (isset($_SESSION['form_data']['nds']) && $_SESSION['form_data']['nds'] == '1') ? ' selected' : '' ?>>с НДС</option>
													<option value = "2"<?= (isset($_SESSION['form_data']['nds']) && $_SESSION['form_data']['nds'] == '2') ? ' selected' : '' ?>>без НДС</option>
												</select>
											</div>
											<div class="col-sm-6">
												<label class="form-label" for="dogovor">Условия поставки</label>
												<select name="dogovor" class="form-control" id="dogovor">
													<option value = "" selected="selected">Выберите условия поставки</option>
													<option value = "1"<?= (isset($_SESSION['form_data']['dogovor']) && $_SESSION['form_data']['dogovor'] == '1') ? ' selected' : '' ?>>Договор</option>
													<option value = "2"<?= (isset($_SESSION['form_data']['dogovor']) && $_SESSION['form_data']['dogovor'] == '2') ? ' selected' : '' ?>>Счёт-договор</option>
												</select>
											</div>
											<div class="col-sm-12">
												<label class="form-label" for="rekvizity">Прикрепить реквизиты</label>
												<input class="form-control" type="file" name="rekvizity" id="rekvizity">
											</div>
										</div>
									</div>
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
								<?php $consentId = 'checkout-privacy-accept'; require APP . '/views/' . TEMPLATE . '/partials/privacy-consent.php'; ?>
								<div class="pt-2">
									<button class="btn btn-primary d-block w-100" type="submit">Оформить заказ</button>
								</div>
                            
					</div>
					
                </div>
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
