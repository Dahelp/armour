<?php

namespace app\controllers;

use app\models\Oneclick;
use ishop\App;

class OneclickController extends AppController {	
	
	public function viewAction(){
		if($_POST){
			$fio_modal = $_POST["fio_modal"];
			$tell_modal = $_POST["tell_modal"];
			$name_tovar = $_POST["name_tovar"];
			$email_modal = $_POST["email_modal"];
			$prim_modal = $_POST["prim_modal"];
			$product_id = $_POST["product_id"];
			$user_id = $_POST["user_id"];
			$oneclick = new Oneclick();
			if($user_id == "") { $user_id = "0"; }
			$first = substr($tell_modal, "0",5);		
			if($first != "+7 (9") { $this->errors['unique'][] = "Запрос не обработан! Вы робот? Если нет, попробуйте заполнить форму обратной связи еще раз!"; } else {					
				$oneclick -> addOneclick($user_id, $fio_modal, $tell_modal, $name_tovar, $product_id, $email_modal, $prim_modal);			            
			}
		}
		redirect();		
	}	
}