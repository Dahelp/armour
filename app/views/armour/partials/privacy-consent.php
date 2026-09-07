<?php $consentId = isset($consentId) ? (string)$consentId : 'privacy-accept'; ?>
<div class="privacy-consent form-group">
    <label for="<?=h($consentId)?>">
        <input id="<?=h($consentId)?>" type="checkbox" name="privacy_accept" value="1" required>
        <span>Я принимаю <a href="/politika-konfidencialnosti" target="_blank">Политику конфиденциальности</a> и даю <a href="/soglasie-na-obrabotku-personalnyh-dannyh" target="_blank">согласие на обработку персональных данных</a>.</span>
    </label>
</div>
