<?php

namespace app\models;

use ishop\App;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Oneclick extends AppModel {

    public function addOneclick($user_id, $fio_modal, $tell_modal, $name_tovar, $product_id, $email_modal, $prim_modal){

		// Create the Transport
		$transport = (new Swift_SmtpTransport(App::$app->getProperty('smtp_host'), App::$app->getProperty('smtp_port'), App::$app->getProperty('smtp_protocol')))
			->setUsername(App::$app->getProperty('smtp_login'))
			->setPassword(App::$app->getProperty('smtp_password'))
		;
		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);
		$namecomp = App::$app->getProperty('shop_name');
		$tell_site = \ishop\App::options('option_telefon');
		
		// Create a message
		ob_start();
		require APP . '/views/'.TEMPLATE.'/mail/mail_oneclick.php';
		$body = ob_get_clean();


		$message_admin = (new Swift_Message("Заказ в один клик на сайте " . App::$app->getProperty('shop_name')))
			->setFrom([App::$app->getProperty('smtp_login') => App::$app->getProperty('shop_name')])
			->setTo(App::$app->getProperty('admin_email'))
			->setBody($body, 'text/html')
		;
		
		
		$result = $mailer->send($message_admin);
		
		$_SESSION['success'] = 'Ваш закза в один клик принят. Наш менеджер свяжется с Вами в ближайшее время.';
		
	
	}
	
}