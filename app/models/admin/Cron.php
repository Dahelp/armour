<?php

namespace app\models\admin;

use app\models\AppModel;

class Cron extends AppModel {

    public static function ensureSystemJobs(): void
    {
        $jobs = [
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
            $job = \R::dispense('cron');
            $job->name = $values['name'];
            $job->alias = $values['alias'];
            $job->urlParams = $systemName;
            $job->urlDownload = $values['url_download'];
            $job->hide = $values['hide'];
            $job->dateUpdate = null;
            \R::store($job);
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
