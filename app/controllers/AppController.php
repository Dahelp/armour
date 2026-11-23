<?php

namespace app\controllers;

use app\models\AppModel;
use app\services\CsrfProtection;
use app\widgets\currency\Currency;
use ishop\App;
use ishop\base\Controller;
use ishop\Cache;

class AppController extends Controller{

    public function set($data){
        if ($this->prefix === '' && is_array($data)) {
            $cards = [];
            foreach (['products', 'hits', 'sales', 'new_products', 'related'] as $key) {
                foreach (($data[$key] ?? []) as $item) {
                    if ((is_array($item) || $item instanceof \ArrayAccess) && !empty($item['id'])) $cards[(int)$item['id']] = $item;
                }
            }
            foreach (($data['similar_categories'] ?? []) as $items) {
                foreach ($items as $item) $cards[(int)$item['id']] = $item;
            }
            if ($cards) {
                [$data['cardAttributes'], $data['cardBrands']] = (new \app\services\CatalogListingLoader())->load($cards);
            }
        }
        parent::set($data);
    }

    public function __construct($route){
        parent::__construct($route);
        CsrfProtection::validateRequest();
        new AppModel();
        App::$app->setProperty('currencies', Currency::getCurrencies());
        App::$app->setProperty('currency', Currency::getCurrency(App::$app->getProperty('currencies')));
        App::$app->setProperty('cats', self::cacheCategory());

    }

    public static function cacheCategory(){
        $cache = Cache::instance();
        $cats = $cache->get('cats');
        if(!$cats){
            $cats = \R::getAssoc("SELECT * FROM category");
            $cache->set('cats', $cats);
        }
        return $cats;
    }

}
