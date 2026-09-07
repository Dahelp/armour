<?php

namespace app\controllers;

use app\models\Sendmail;
use app\services\PersonalDataConsent;
use ishop\App;

class SendmailController extends AppController {	
	
	public function viewAction(){
		if($_POST){
			if (!PersonalDataConsent::accepted($_POST)) {
				PersonalDataConsent::reject($_POST);
				redirect();
				return;
			}
			$uemail = (string)($_POST["uemail"] ?? '');
			$title = (string)($_POST["title"] ?? 'Сообщение с сайта');
			$name = (string)($_POST["name"] ?? '');
			$tell_modal = (string)($_POST["tell_modal"] ?? '');
			$note = (string)($_POST["note"] ?? '');
			$sendmail = new Sendmail();
			$user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
			$first = substr($tell_modal, "0",5);		
			if($first != "+7 (9") { $_SESSION['error'] = "Запрос не обработан. Проверьте номер телефона и попробуйте ещё раз."; } else {
				$sendmail -> addSendmail($uemail, $user_id, $title, $name, $tell_modal, $note);			            
			}
		}
		redirect();		
	}	
}
