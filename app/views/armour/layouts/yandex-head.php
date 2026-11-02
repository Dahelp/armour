<?php
$yandexVerification = trim((string)\R::getCell("SELECT znachenie FROM options WHERE alt_name = ? LIMIT 1", ['option_yandex_verification']));
$yandexCounter = trim((string)\R::getCell("SELECT znachenie FROM options WHERE alt_name = ? OR name_op LIKE ? LIMIT 1", ['option_yandex_metrika', '%Метрика%']));
?>
<?php if (preg_match('/^[a-f0-9]{16}$/i', $yandexVerification)): ?>
<meta name="yandex-verification" content="<?=h($yandexVerification)?>" />
<?php endif; ?>
<?php if (preg_match('/^[1-9][0-9]*$/D', $yandexCounter)): ?>
<meta name="yandex-metrika-counter" content="<?=h($yandexCounter)?>" />
<?php endif; ?>
