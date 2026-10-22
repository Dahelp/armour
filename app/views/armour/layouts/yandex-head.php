<?php
$yandexVerification = trim((string)\R::getCell("SELECT znachenie FROM options WHERE alt_name = ? LIMIT 1", ['option_yandex_verification']));
$yandexCounter = trim((string)\R::getCell("SELECT znachenie FROM options WHERE alt_name = ? OR name_op LIKE ? LIMIT 1", ['option_yandex_metrika', '%Метрика%']));
?>
<?php if (preg_match('/^[a-f0-9]{16}$/i', $yandexVerification)): ?>
<meta name="yandex-verification" content="<?=h($yandexVerification)?>" />
<?php endif; ?>
<?php if (preg_match('/^[1-9][0-9]*$/D', $yandexCounter)): ?>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j=0;j<document.scripts.length;j++){if(document.scripts[j].src===r){return;}}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a);
    })(window,document,'script','https://mc.yandex.ru/metrika/tag.js?id=<?=$yandexCounter?>','ym');
    ym(<?=$yandexCounter?>,'init',{ssr:true,webvisor:true,clickmap:true,ecommerce:"dataLayer",referrer:document.referrer,url:location.href,accurateTrackBounce:true,trackLinks:true});
</script>
<!-- /Yandex.Metrika counter -->
<?php endif; ?>
