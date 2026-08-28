<?php

namespace ishop\base;

class View {

    public $route;
    public $controller;
    public $model;
    public $view;
    public $prefix;
    public $layout;
    public $data = [];
    public $meta = [];

    public function __construct($route, $layout = '', $view = '', $meta){
        $this->route = $route;
        $this->controller = $route['controller'];
        $this->view = $view;
        $this->model = $route['controller'];
        $this->prefix = $route['prefix'];
        $this->meta = $meta;
        if($layout === false){
            $this->layout = false;
        }else{
            $this->layout = $layout ?: LAYOUT;
        }
    }

    public function render($data){
        if(is_array($data)) extract($data);
		$this->prefix = str_replace('\\', '/', $this->prefix);
        $viewFile = APP . "/views/".TEMPLATE."/{$this->prefix}{$this->controller}/{$this->view}.php";
        if(is_file($viewFile)){
            ob_start();
            require_once $viewFile;
            $content = ob_get_clean();
        }else{
            throw new \Exception("Не найден вид {$viewFile}", 500);
        }
        if(false !== $this->layout){
            $layoutFile = APP . "/views/".TEMPLATE."/layouts/{$this->layout}.php";
            if(is_file($layoutFile)){
                require_once $layoutFile;
            }else{
                throw new \Exception("Не найден шаблон {$this->layout}", 500);
            }
        }
    }

    public function getMeta(){
		$title = $this->meta['title'] ?? '';
		$description = $this->meta['desc'] ?? '';
		$keywords = $this->meta['keywords'] ?? '';
		$shopName = $this->meta['shop_name'] ?? '';
		$shopImage = $this->meta['shop_img'] ?? '';
		$shopUrl = $this->meta['shop_url'] ?? '';
        $output = '<title>' . $title . '</title>' . PHP_EOL;
        $output .= '<meta name="description" content="' . $description . '" />' . PHP_EOL;
        $output .= '<meta name="keywords" content="' . $keywords . '" />' . PHP_EOL;
		$output .= '<meta property="og:type" content="website" />' . PHP_EOL;
		$output .= '<meta property="og:locale" content="ru_RU" />' . PHP_EOL;
		$output .= '<meta property="og:site_name" content="' . $shopName . '" />' . PHP_EOL;
		$output .= '<meta property="og:title" content="' . $title . '" />' . PHP_EOL;
		$output .= '<meta property="og:image" content="' . $shopImage . '" />' . PHP_EOL;
		$output .= '<meta property="og:description" content="' . $description . '" />' . PHP_EOL;
		$output .= '<meta property="og:url" content="' . $shopUrl . '" />' . PHP_EOL;
		if ($shopUrl !== '') {
			$output .= '<link rel="canonical" href="' . $shopUrl . '" />' . PHP_EOL;
		}
        return $output;
    }

}
