<?php

namespace app\controllers;

use app\models\Callback;
use ishop\App;

class CallbackController extends AppController {	
	
	public function viewAction(){
		if($_POST){
			$phone = $_POST["phone"];
			$title = $_POST["title"];
			$callback = new Callback();
			$first = substr($phone, "0",5);
			if($user_id == "") { $user_id = "0"; }
			
			if($first != "+7 (9") { $this->errors['unique'][] = "Запрос не обработан! Вы робот? Если нет, попробуйте заполнить форму обратной связи еще раз!"; } else {					
				$callback -> addCallback($phone, $user_id, $title);			            
			}
			
		}
		redirect();		
	}	
}