<?php

namespace app\models;

use ishop\App;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Sendmail extends AppModel {

    public function addSendmail($uemail, $user_id, $title, $name, $tell_modal, $note){

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
		require APP . '/views/'.TEMPLATE.'/mail/mail_sendmail.php';
		$body = ob_get_clean();


		$message_admin = (new Swift_Message("Обратная связь на сайте " . App::$app->getProperty('shop_name')))
			->setFrom([App::$app->getProperty('smtp_login') => App::$app->getProperty('shop_name')])
			->setTo(App::$app->getProperty('admin_email'))
			->setBody($body, 'text/html')
		;
		
		
		$result = $mailer->send($message_admin);
		
		$_SESSION['success'] = 'Ваше сообщение принято. Мы отправим ответ на почту которую Вы указали.';
		
	
	}
	
}