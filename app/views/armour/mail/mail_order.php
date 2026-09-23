<!doctype html>
<html lang="ru">
<?php
$escapeMailValue = static function ($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
$orderDetails = [
    'Способ доставки' => $dostavka_name ?? '',
    'Пункт самовывоза' => $branch_name ?? '',
    'Транспортная компания' => $transport_company ?? '',
    'Город' => $city_name ?? '',
    'Адрес' => $address ?? '',
    'Вид клиента' => $vid ?? '',
    'Компания' => $compname ?? '',
    'Налогообложение' => $nds ?? '',
    'Условия поставки' => $dogovor ?? '',
    'Файл реквизитов' => $rekvizity_name ?? '',
    'Имя' => $uname ?? '',
    'Номер телефона' => $telefon ?? '',
    'E-mail' => $user_email ?? '',
    'Комментарий' => $note ?? '',
    'Время заказа' => $date ?? '',
];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Заказ №<?=htmlspecialchars((string)($ord['inv'] ?? $order_id), ENT_QUOTES, 'UTF-8')?> на сайте <?=htmlspecialchars($namecomp, ENT_QUOTES, 'UTF-8')?></title>
</head>
<body>
<table style="width:740px;background-color:#f4f6f9;font-family:Tahoma, Helvetica, sans-serif;color:#212529;font-size:13px;border:1px solid #eee">
	<tr>             
		<td style="padding:20px;width:300px"><img src="<?=PATH?>/images/logo_armour.png" alt="<?=htmlspecialchars((string)$namecomp, ENT_QUOTES, 'UTF-8')?>" style="width:100px;height:50px"></td>
		<td style="padding:20px;width:440px;font-weight:bold" align="right"> <a href="<?=PATH?>" style="color:#2C3E50">Главная</a> | <a href="<?=PATH?>/category" style="color:#2C3E50">Каталог</a> | <a href="<?=PATH?>/dostavka" style="color:#2C3E50">Доставка</a> | <a href="<?=PATH?>/contacts" style="color:#2C3E50">Контакты</a></td>
	</tr>
	<tr>
		<td colspan="2">
			<table cellspacing="0" cellpadding="0" style="width:700px;background: none repeat scroll 0% 0% rgb(255, 255, 255);font-size:13px" align="center">
				<tr>
					<td>						
						<table cellspacing="0" cellpadding="0" style="width:660px;padding:20px;font-family:Tahoma, Helvetica, sans-serif;color:#212529;font-size:13px" align="center">
							<tr>
								<td colspan="4" style="padding:20px 0 20px 0">
									<p>Здравствуйте <?=htmlspecialchars((string)$uname, ENT_QUOTES, 'UTF-8')?>.<br><br>
										Благодарим Вас за заказ!<br>
										Ваш заказ на сайте <?=htmlspecialchars((string)$namecomp, ENT_QUOTES, 'UTF-8')?> оформлен. В ближайшее время с вами свяжутся для подтверждения заказа по email.<br><br>
										<strong>Ваш заказ: № <?=htmlspecialchars((string)($ord['inv'] ?? $order_id), ENT_QUOTES, 'UTF-8')?> от <?=htmlspecialchars((string)$date, ENT_QUOTES, 'UTF-8')?></strong>
									</p>
									<table style="border: 1px solid #ddd; border-collapse: collapse; width: 100%;">
										<thead>
										<tr style="background: #f9f9f9;">
											<th style="padding: 8px; border: 1px solid #ddd;">Наименование</th>
											<th style="padding: 8px; border: 1px solid #ddd;">Кол-во</th>
											<th style="padding: 8px; border: 1px solid #ddd;">Цена</th>
											<th style="padding: 8px; border: 1px solid #ddd;">Сумма</th>
										</tr>
										</thead>
										<tbody>
										<?php foreach((array)($_SESSION['cart'] ?? []) as $item): ?>
											<tr>
												<td style="padding: 8px; border: 1px solid #ddd;"><?=htmlspecialchars((string)$item['name'], ENT_QUOTES, 'UTF-8') ?></td>
												<td style="padding: 8px; border: 1px solid #ddd;"><?=$item['qty'] ?></td>
												<td style="padding: 8px; border: 1px solid #ddd;"><?=number_format((float)$item['price'], 2, ',', ' ') ?></td>
												<td style="padding: 8px; border: 1px solid #ddd;"><?=number_format((float)$item['price'] * (int)$item['qty'], 2, ',', ' ') ?></td>
											</tr>
										<?php endforeach;?>
										<tr>
											<td colspan="3" style="padding: 8px; border: 1px solid #ddd;">Итого:</td>
											<td style="padding: 8px; border: 1px solid #ddd;"><?=$_SESSION['cart.qty'] ?></td>
										</tr>
										<tr>
											<td colspan="3" style="padding: 8px; border: 1px solid #ddd;">На сумму:</td>
											<td style="padding: 8px; border: 1px solid #ddd;"><?=htmlspecialchars((string)($_SESSION['cart.currency']['symbol_left'] ?? '') . number_format((float)($_SESSION['cart.sum'] ?? 0), 2, ',', ' ') . (string)($_SESSION['cart.currency']['symbol_right'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
										</tr>
										</tbody>
									</table>
									<br><br>
									<table style="border: 1px solid #ddd; border-collapse: collapse; width: 100%;">
										<tbody>
										<?php foreach($orderDetails as $label => $value): ?>
											<?php if(trim((string)$value) === '') continue; ?>
											<tr>
												<td style="padding: 8px; border: 1px solid #ddd; width: 35%; font-weight: bold;"><?=$escapeMailValue($label)?></td>
												<td style="padding: 8px; border: 1px solid #ddd;"><?=$escapeMailValue($value)?></td>
											</tr>
										<?php endforeach; ?>
										</tbody>
									</table>
									<br><br>
									С уважением, <?=htmlspecialchars((string)$namecomp, ENT_QUOTES, 'UTF-8')?> <br>
									<b>Телефон:</b> <?=htmlspecialchars((string)$tell_site, ENT_QUOTES, 'UTF-8')?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="padding:20px"></td>
	</tr>
</table>

</body>
</html>
