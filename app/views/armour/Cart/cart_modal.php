<?php if(!empty($_SESSION['cart'])): ?>
    <div class="table-responsive">
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
                    <td><a href="product/<?=$item['alias'];?>"><img src="images/product/mini/<?=$item['img'];?>" alt=""></a></td>
                    <td><a href="product/<?=$item['alias'];?>"><?=$item['name'];?></td>
                    <td style="text-align:center;width:72px">
						<?php if($item['qty'] > 1) { ?><span data-id="<?=$id;?>" class="my-minus-<?=$id;?> my-minus"><i class="fa fa-minus" aria-hidden="true"></i></span><?php } ?>
						<span class="qty-item"><?=$item['qty'];?></span>
						<?php if($item['qty'] < $item['max']) { ?><span data-id="<?=$id;?>" class="my-plus-<?=$id;?> my-plus"><i class="fa fa-plus" aria-hidden="true"></i></span><?php } ?>
					</td>
                    <td><?=$item['price'];?></td>
                    <td><span data-id="<?=$id;?>" class="glyphicon glyphicon-remove text-danger del-item" aria-hidden="true"><i class="fas fa-times"></i></span></td>
                </tr>
            <?php endforeach; ?>
                <tr>
                    <td>Итого:</td>
                    <td colspan="4" class="text-right cart-qty"><?=$_SESSION['cart.qty'];?></td>
                </tr>
                <tr>
                    <td>На сумму:</td>
                    <td colspan="4" class="text-right cart-sum"><?= $_SESSION['cart.currency']['symbol_left'] . $_SESSION['cart.sum'] . $_SESSION['cart.currency']['symbol_right'];?></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <h3>Корзина пуста</h3>
<?php endif; ?>