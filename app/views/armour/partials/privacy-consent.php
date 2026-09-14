<?php
$consentId = isset($consentId) ? (string)$consentId : 'privacy-accept';
$consentClass = isset($consentClass) ? (string)$consentClass : '';
?>
<div class="privacy-consent form-group">
    <label for="<?=h($consentId)?>">
        <input id="<?=h($consentId)?>" type="checkbox" name="privacy_accept" value="1"<?php if ($consentClass !== ''): ?> class="<?=h($consentClass)?>"<?php endif; ?> required aria-required="true">
        <span>Я принимаю <a href="/politika-konfidencialnosti" target="_blank">Политику конфиденциальности</a> и даю <a href="/soglasie-na-obrabotku-personalnyh-dannyh" target="_blank">согласие на обработку персональных данных</a>.</span>
    </label>
</div>
