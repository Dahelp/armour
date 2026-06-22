<?php

namespace app\models;

use ishop\App;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Callback extends AppModel {

    public function addCallback($phone, $user_id, $title){
        $res = \R::exec("INSERT INTO `callback` (`user_id`, `topic`, `phone`, `date_create`, `date_modified`, `user_modified`, `status`, `hide`) VALUES ('".$user_id."', '".$title."', '".$phone."', '".date('Y-m-d H:i:s')."', '0000-00-00 00:00:00', '0', '0', 'show')");		
				if($res){
					if($_SESSION['user']['id']){ $customer_id = "".$_SESSION['user']['id'].""; }else{ $customer_id = "0"; }
					$last = \R::findLast('callback');				
					\R::exec("INSERT INTO `admin_last_history`(`gh_id`, `ah_id`, `name_tbl`, `id_tbl`, `date_modified`, `customer_id`) VALUES ('1','2','callback','".$last->id."','".date('Y-m-d H:i:s')."','".$customer_id."')");
					setcookie("request-mig", "1house", time()+3600);
					
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
					require APP . '/views/'.TEMPLATE.'/mail/mail_callback.php';
					$body = ob_get_clean();


					$message_admin = (new Swift_Message("Заказ обратного звонка на сайте " . App::$app->getProperty('shop_name')))
						->setFrom([App::$app->getProperty('smtp_login') => App::$app->getProperty('shop_name')])
						->setTo(App::$app->getProperty('admin_email'))
						->setBody($body, 'text/html')
					;
					
					
					$result = $mailer->send($message_admin);
					
					$_SESSION['success'] = 'Спасибо за заказ обратного звонка. Наш менеджер обязательно Вам позвонит по указаному номеру который вы указали. Ожидайте звонка в рабочее время с ПН-ПТ 09:00 до 17:00 по МСК.';
					
				}else{
					
				}
    }
	
}