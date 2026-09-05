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
											<span data-id="<?=$id;?>" class="my-minus-<?=$id;?> my-minus"><i class="fa fa-minus" aria-hidden="true"></i></span>
												<span class="qty-item qty-item-<?=$id;?>"><?=$item['qty'];?></span>
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
