<?php

namespace app\models\admin;

use app\models\AppModel;

class PlaginsTiposize extends AppModel{

    public $attributes = [
        'value_id' => '',
        'content' => '',
		'title' => '',
		'description' => '',
        'keywords' => '',		
        'hide' => '',
    ];

    public $rules = [
        'required' => [
            ['value_id'],            
        ],        
    ];

	public function checkUnique(){
        $attribute = \R::findOne('tiposize', 'value_id = ?', [$this->attributes['value_id']]);
        if($attribute){
            if($attribute->value_id == $this->attributes['value_id']){
                $this->errors['unique'][] = 'Это название кросса уже существует';
            }
            return false;
        }
        return true;
    }
}