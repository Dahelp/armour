<?php if(!empty($_SESSION['cart'])): ?>
<div class="prdt-top cart-page-card">
            <div class="col-md-12">
                <div class="bg-light rounded-3 py-5 px-4 px-xxl-5">
                    <div class="register-top heading">
                        <h2>Оформление заказа</h2>
                    </div> 
					
                    <div id="prodcart" class="table-responsive">
                            <table class="table table-hover cart-items-table">
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
											<div class="cart-quantity-control">
												<button type="button" data-id="<?=$id;?>" class="my-minus-<?=$id;?> my-minus" aria-label="Уменьшить количество"><i class="fas fa-minus" aria-hidden="true"></i></button>
												<span class="qty-item qty-item-<?=$id;?>"><?=$item['qty'];?></span>
												<?php if($item['qty'] < $item['max']) { ?><button type="button" data-id="<?=$id;?>" class="my-plus-<?=$id;?> my-plus" aria-label="Увеличить количество"><i class="fas fa-plus" aria-hidden="true"></i></button><?php } ?>
											</div>
										</td>
                                        <td><?=$item['price'] ?></td>
                                        <td><button type="button" data-id="<?=$id;?>" class="del-items cart-remove-button" aria-label="Удалить товар"><i class="fas fa-times" aria-hidden="true"></i></button></td>
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
						<div class="product-info cart-dimensions">
						<div class="col-md-6 bg-light px-xxl-5" id="prodinfo">
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
<?php else: ?>
<h3 class="cart-empty-state">Корзина пуста</h3>
<?php endif; ?>
