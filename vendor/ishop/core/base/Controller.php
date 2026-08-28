<?php

namespace ishop\base;

abstract class Controller{

    public $route;
    public $controller;
    public $model;
    public $view;
    public $prefix;
    public $layout;
    public $data = [];
    public $meta = ['title' => '', 'desc' => '', 'keywords' => ''];

    public function __construct($route){
        $this->route = $route;
        $this->controller = $route['controller'];
        $this->model = $route['controller'];
        $this->view = $route['action'];
        $this->prefix = $route['prefix'];
        $this->meta['robots'] = $this->robotsDirective();
    }

    public function getView(){
        $viewObject = new View($this->route, $this->layout, $this->view, $this->meta);
        $viewObject->render($this->data);
    }

    public function set($data){
        $this->data = $data;
    }

    public function setMeta($title = '', $desc = '', $keywords = '', $shop_name = '', $shop_img = '', $shop_url = ''){
        $this->meta['title'] = h($title);
        $this->meta['desc'] = h($desc);
        $this->meta['keywords'] = h($keywords);
		$this->meta['shop_name'] = h($shop_name);
		$this->meta['shop_img'] = h($shop_img);
		$canonicalUrl = $this->route['canonical_url'] ?? $shop_url;
		if ($canonicalUrl === '' && $this->meta['robots'] === 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1') {
			$requestPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
			$requestPath = '/' . ltrim($requestPath, '/');
			$canonicalUrl = rtrim(PATH, '/') . ($requestPath === '/' ? '/' : rtrim($requestPath, '/'));
		}
		$this->meta['shop_url'] = h($canonicalUrl);
    }

    private function robotsDirective(): string
    {
        if ($this->prefix !== '') {
            return 'noindex, nofollow';
        }

        $noIndexControllers = ['Cart', 'Comparison', 'Complete', 'Cron', 'Search', 'User'];
        if (in_array($this->controller, $noIndexControllers, true)) {
            return 'noindex, nofollow';
        }

        return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    }

    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function loadView($view, $vars = []){
        extract($vars);
        require APP . "/views/" . TEMPLATE . "/{$this->prefix}{$this->controller}/{$view}.php";
        die;
    }
	
	public function loadViewAdmin($view, $vars = []){
        extract($vars);
        require APP . "/views/" . TEMPLATE . "/admin/{$this->prefix}{$this->controller}/{$view}.php";
        die;
    }
}
