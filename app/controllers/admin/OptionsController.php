<?php

namespace app\controllers\admin;

class OptionsController extends AppController{

    public function indexAction(){
		if(!empty($_POST)){
			foreach ((array)($_POST['altname'] ?? []) as $altname => $attribute) {
				$optionId = (int)$altname;
				if ($optionId > 0) {
					\R::exec('UPDATE options SET znachenie = ? WHERE option_id = ?', [(string)($attribute['znachenie'] ?? ''), $optionId]);
				}
			}
			
			$_SESSION['success'] = "Изменения сохранены";
            redirect();			
		}
        $options = \R::getAll("SELECT*FROM options GROUP BY tip");
        $this->setMeta('Основные настройки');
        $this->set(compact('options'));
    }
	
}
