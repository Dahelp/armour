<?php

namespace app\controllers;

use app\models\Sendmail;
use ishop\App;

class SendmailController extends AppController {	
	
	public function viewAction(){
		if($_POST){
			$uemail = $_POST["uemail"];
			$title = $_POST["title"];
			$name = $_POST["name"];
			$tell_modal = $_POST["tell_modal"];
			$note = $_POST["note"];
			$sendmail = new Sendmail();
			if($user_id == "") { $user_id = "0"; }
			$first = substr($tell_modal, "0",5);		
			if($first != "+7 (9") { $this->errors['unique'][] = "Запрос не обработан! Вы робот? Если нет, попробуйте заполнить форму обратной связи еще раз!"; } else {					
				$sendmail -> addSendmail($uemail, $user_id, $title, $name, $tell_modal, $note);			            
			}
		}
		redirect();		
	}	
}