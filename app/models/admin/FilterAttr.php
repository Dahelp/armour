<?php

namespace app\models\admin;

use app\models\AppModel;

class FilterAttr extends AppModel{

    public $attributes = [
        'value' => '',
        'attr_group_id' => '',
		'content' => '',
		'title' => '',
		'description' => '',
        'keywords' => '',		
        'hide' => '',
    ];

    public $rules = [
        'required' => [
            ['value'],
            ['attr_group_id'],
			['hide'],
        ],
        'integer' => [
            ['attr_group_id'],
        ]
    ];

	public function checkUnique(){
        $attribute = \R::findOne('attribute_value', 'value = ? AND attr_group_id = ?', [$this->attributes['value'], $this->attributes['attr_group_id']]);
        if($attribute){
            if($attribute->value == $this->attributes['value'] && $attribute->attr_group_id == $this->attributes['attr_group_id']){
                $this->errors['unique'][] = 'Это название фильтра уже существует';
            }
            return false;
        }
        return true;
    }
}