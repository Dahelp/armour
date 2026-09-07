<?php

namespace app\controllers;

use app\models\Callback;
use ishop\App;

class CallbackController extends AppController {	
	
	public function viewAction(){
		if($_POST){
			if (($_POST['privacy_accept'] ?? '') !== '1') {
				$_SESSION['error'] = 'Подтвердите согласие на обработку персональных данных.';
				redirect();
				return;
			}
			$phone = (string)($_POST["phone"] ?? '');
			$title = (string)($_POST["title"] ?? 'Обратный звонок');
			$callback = new Callback();
			$first = substr($phone, "0",5);
			$user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
			
			if($first != "+7 (9") { $_SESSION['error'] = "Запрос не обработан. Проверьте номер телефона и попробуйте ещё раз."; } else {
				$callback -> addCallback($phone, $user_id, $title);			            
			}
			
		}
		redirect();		
	}	
}
