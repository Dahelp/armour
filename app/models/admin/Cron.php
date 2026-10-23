<?php

namespace app\models\admin;

use app\models\AppModel;

class Cron extends AppModel {

    public static function ensureSystemJobs(): void
    {
        $jobs = [
            'refresh-filtry' => [
                'name' => 'Обновление цен и наличия фильтров',
                'alias' => 'https://its50.ru/xls/nalichie/filtrs.csv',
                'url_download' => 'filtrs.csv',
                'hide' => 'show',
            ],
            'refresh-kamery' => [
                'name' => 'Обновление цен и наличия камер',
                'alias' => 'https://its50.ru/xls/nalichie/kamery.csv',
                'url_download' => 'kamery.csv',
                'hide' => 'show',
            ],
            'refresh-tovars' => [
                'name' => 'Обновление цен и наличия',
                'alias' => '',
                'url_download' => 'tovars.csv',
                'hide' => 'hide',
            ],
            'sitemap' => [
                'name' => 'Обновление карты сайта',
                'alias' => '',
                'url_download' => 'sitemap.xml',
                'hide' => 'show',
            ],
        ];

        foreach ($jobs as $systemName => $values) {
            if (\R::findOne('cron', 'url_params = ?', [$systemName])) {
                continue;
            }
            \R::exec(
                'INSERT INTO cron (name, alias, url_params, url_download, hide, date_update) VALUES (?, ?, ?, ?, ?, ?)',
                [$values['name'], $values['alias'], $systemName, $values['url_download'], $values['hide'], date('Y-m-d H:i:s')]
            );
        }
    }

    public $attributes = [
		'name' => '',
        'alias' => '',
		'url_params' => '',
		'url_download' => '',
		'hide' => '',
    ];

    public $rules = [
        'required' => [
            ['name'],			
        ],
    ];

    public function checkUnique(){
		if($this->attributes['alias'] !=""){
			$cron = \R::findOne('cron', 'alias = ?', [$this->attributes['alias']]);
		
			if($cron){
				if($cron->alias == $this->attributes['alias']){
					$this->errors['unique'][] = 'CRON с таким url уже существует';
				}
				return false;
			}
		}
        return true;
	}

}
