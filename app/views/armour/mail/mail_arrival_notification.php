<!doctype html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Сообщить о поступлении</title></head>
<body style="font-family:Tahoma,Arial,sans-serif;color:#212529">
	<h2>Заявка «Сообщить о поступлении»</h2>
	<p><b>Товар:</b> <?=h($name_tovar)?></p>
	<p><b>Ссылка:</b> <a href="<?=h($product_url)?>"><?=h($product_url)?></a></p>
	<p><b>Имя:</b> <?=h($fio)?></p>
	<p><b>E-mail:</b> <a href="mailto:<?=h($email)?>"><?=h($email)?></a></p>
	<p><b>Дата заявки:</b> <?=date('d.m.Y H:i:s')?></p>
</body>
</html>
