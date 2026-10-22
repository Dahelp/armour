<?php

namespace app\controllers\admin;

class OptionsController extends AppController{

    public function indexAction(){
		if(!empty($_POST)){
			$yandexOptions = \R::getAll("SELECT option_id, alt_name, name_op FROM options WHERE alt_name IN ('option_yandex_metrika', 'option_yandex_verification') OR name_op LIKE '%Метрика%'");
			foreach ($yandexOptions as $option) {
				$id = $option['option_id'];
				if (!isset($_POST['altname'][$id])) continue;
				$value = trim((string)($_POST['altname'][$id]['znachenie'] ?? ''));
				$isVerification = $option['alt_name'] === 'option_yandex_verification';
				$pattern = $isVerification ? '/^[a-f0-9]{16}$/iD' : '/^[1-9][0-9]*$/D';
				if ($value !== '' && !preg_match($pattern, $value)) {
					$_SESSION['error'] = $isVerification ? 'Укажите только код Яндекс Вебмастера: 16 символов, без метатега.' : 'Укажите только номер счётчика Яндекс Метрики: цифры, без HTML-кода.';
					redirect();
					return;
				}
				$_POST['altname'][$id]['znachenie'] = $value;
			}
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
