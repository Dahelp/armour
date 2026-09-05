<?php if(!empty($products)): ?>
    <?php $curr=\ishop\App::$app->getProperty('currency'); ?>
    <table>
        <thead>
            <tr>
                <th>Фото</th><th>Типоразмер</th><th>Марка шин</th><th>Тип протектора</th><th>PR</th>
                <th class="c_price">Цена (с НДС)</th><th>Наличие</th><th></th><th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($products as $product): ?>
            <?php new \app\widgets\product\Product(
                $product,
                $curr,
                $productAttributes[(int)$product['id']]??[],
                $brands[(int)$product['brand_id']]??[],
                'product_table_'.$category->table_alt.'_tpl.php'
            ); ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="clearfix"></div>
    <div class="text-center">
        <p>(<?=count($products)?> товара(ов) из <?=$total;?>)</p>
        <?php if($pagination->countPages>1): ?><?=$pagination;?><?php endif; ?>
    </div>
<?php else: ?>
    <h3>Товаров не найдено...</h3>
<?php endif; ?>
