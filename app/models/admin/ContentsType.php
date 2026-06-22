<?php

namespace app\models\admin;

use ishop\App;
use app\models\AppModel;

class ContentsType extends AppModel{

    public $attributes = [
        'name' => '',
		'param_url' => '',
		'hide' => '',
		'hide_anons' => '',
		'hide_clicks' => '',
		'hide_date_post' => '',
		'hide_rss' => '',
		'title' => '',
		'description' => '',
		'keywords' => '',
		
    ];

    public $rules = [
        'required' => [
            ['name'],
			['param_url'],
        ],
    ];
	
	public function addClassContents($data){
		
        $fileName = ucfirst($data["param_url"]);
		mkdir(''. APP . '/views/' . TEMPLATE . '/'.$fileName.'', 0700); //создание папки
		$dir_view = APP . '/views/' . TEMPLATE . '/'.$fileName.'/view.php'; //путь файла контента
		if($data["hide_anons"]=="show") {
			$dir_index = APP . '/views/' . TEMPLATE . '/'.$fileName.'/index.php'; //путь файла анонс для контента
			
			$phpContent_index = '
<div class="storefront-breadcrumb">
	<div class="col-full">
		<nav class="woocommerce-breadcrumb" aria-label="breadcrumbs">
			<a href="<?= PATH ?>">Главная</a>
			<span class="breadcrumb-separator"> / </span><?=$type->name;?>
		</nav>
	</div>
</div>
<div id="content" class="site-content" tabindex="-1">
    <div class="col-full">
		<div class="woocommerce"></div>
			<div id="primary" class="content-area">
				<main id="main" class="site-main">		
					<header class="page-header">
						<h1 class="entry-title"><span><?=$type->name;?></span></h1>
					</header>
					<div class="flex flex-wrap blog">
						<?php foreach($conts as $item) { ?>
							<div class="col-md-3 cont-one">
								<div class="cont_ht border border-grey">
									<div class="cont_blok_img">
										<?php if($item["img"] !="") { ?>
											<img src="images/contents/mini/<?=$item["img"]?>" alt="<?=$item["name"]?>" title="<?=$item["name"]?>" style="width:100%" />
										<?php } else { ?>
											<img src="images/no_image.jpg" alt="" />
										<?php } ?>
									</div>
									<div class="cont_info">
										<?php if($type["hide_date_post"] == "show") { ?>
											<div class="cont_info_data">
												<?php echo \ishop\App::contdate($item["date_post"]); ?>
											</div>
										<?php } ?>
										<div class="cont_info_name">
											<a href="<?=$type->param_url;?>/<?=$item["alias"];?>"><?=$item["name"];?></a>
										</div>
										<div class="cont_info_anons">
											<?php echo mb_strimwidth($item["anons"], 0, 200, "...");?>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="blog-nav">
						<?php if($pagination->countPages > 1): ?>
							<?=$pagination;?>
						<?php endif; ?>
					</div>
			</main>
		</div>
	</div>
	<div class="col-full"></div>
</div>
';
		file_put_contents($dir_index, $phpContent_index);
		
		}else{
			@unlink(APP . '/views/' . TEMPLATE . '/'.$fileName.'/index.php');
		}
		$dir_controller = APP . '/controllers/'.$fileName.'Controller.php';	//создание контроллера
		$phpContent_view = '
<div class="storefront-breadcrumb">
	<div class="col-full">
		<nav class="woocommerce-breadcrumb" aria-label="breadcrumbs">
			<a href="<?= PATH ?>">Главная</a>
			<?php if($type->hide_anons=="show") { ?>
				<span class="breadcrumb-separator"> / </span>
				<a href="<?=$type->param_url?>"><?=$type->name;?></a>
			<?php } ?>
			<span class="breadcrumb-separator"> / </span>
			<?=$find->name;?>			
		</nav>
	</div>
</div>
<div id="content" class="site-content" tabindex="-1">
	<div class="col-full">
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<?php if(!empty($find)): 
						if($type->hide_clicks == "show") { \R::exec("UPDATE contents SET clicks = clicks+1 WHERE id = ?", [$find->id]); } ?>
				<article itemscope itemtype="http://schema.org/NewsArticle" id="post-<?=$find->id?>" class="post-<?=$find->id?> page type-page status-publish hentry">
					<header class="entry-header">
						<h1 class="entry-title"><?=$find->name;?></h1>
					</header>
						<div class="entry-content">							
							<?php if($type["hide_date_post"] == "show") { ?>
								<div class="cont_info_data">
									<time itemprop="datePublished" datetime="<?=date("c", strtotime($find["date_post"]))?>"><?php echo \ishop\App::contdate($find["date_post"]); ?></time>
								</div>
							<?php } ?>
							<meta itemprop="dateModified" content="<?=date("c", strtotime($find["date_last_modified"]))?>">							
							<div class="cont-inner">
								<?php if($find->img) { ?>
									<?php if($find->img_hide == "show") { ?>
										<div class="cont-img">
											<img src="images/contents/baseimg/<?=$find->img;?>" alt="" />
										</div>
									<?php } ?>
								<?php } ?>
								<div class="cont-desc" itemprop="articleBody">
									<?=$find->content;?>
								</div>
							</div>
						<?php
							$curr = \ishop\App::$app->getProperty(\'currency\');
							$cats = \ishop\App::$app->getProperty(\'cats\');
						?>
						<!-- Related products-->
						  <?php if($related): ?>
						  <div class="related_prod">
						  <section class="pb-5 mb-2 mb-xl-4 recomend-1">
							<h2 class="h3 pb-2 mb-grid-gutter text-center">Связанные товары</h2>
							<div class="review-wrap">

							<div class="wrap-container">
							<div class="inner-container">				

							<div class="swiper-container swiper1">
								<div class="swiper-wrapper">
								
								<?php foreach($related as $product): ?>
								
									<div class="swiper-slide">					                        
										
												<?php new \app\widgets\product\Product($product, $curr, \'product_tpl.php\'); ?>
																
									</div>
									
								<?php endforeach; ?>
								
								</div>
										
								</div>
									
								</div>
									
								</div>
								
							</div>
							<div class="swiper-button-inner">
								<div class="swiper-button-next swiper-button-next-1"></div>
								<div class="swiper-button-prev swiper-button-prev-1"></div>
							</div>
						  </section>
						  </div>
						  <?php endif; ?>
						  <!-- /Related products-->		  
				 
					<?php endif; ?>		
				</div>
				</article>            
			</main>
		</div>
	</div>
</div>	
';
		$phpContent_controller = '<?php

namespace app\controllers;

use app\models\Breadcrumbs;
use ishop\App;
use ishop\libs\Pagination;

class ' . $fileName . 'Controller extends AppController {

    public function viewAction(){
		
		$alias = $this->route[\'alias\'];
		$up_registr = App::upRegistrLetter($alias);
		$find = \R::findOne(\'contents\', \'alias = ?\', [$alias]);
		if(!$find){
            throw new \Exception("Страница не найдена", 404);
        }
		$type = \R::findOne(\'content_type\', \'id = ?\', [$find->type_id]);

		// связанные товары
        $related = \R::getAll("SELECT * FROM content_related JOIN product ON product.id = content_related.related_id WHERE content_related.content_id = ?", [$find->id]);
		
		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["alias"]){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
		if($find->img){$find_img = "".PATH."/images/contents/baseimg/".$find->img.""; }else{ $find_img = "".PATH."/images/".App::$app->getProperty(\'og_logo\').""; }
		$this->setMeta($find->title, $find->description, $find->keywords, \'\' . App::$app->getProperty(\'shop_name\') . \'\', \'\'.$find_img.\'\', \'\'.PATH.\'\'.$path_controller.\'\'.$path_alias.\'\');
		/*SEO*/
		
        $this->set(compact(\'find\', \'type\', \'related\'));
    }';
$phpContent_controller .= '
	public function indexAction(){
		$alias = strtok($_SERVER["REQUEST_URI"],\'?\');
		$alias = str_replace(\'/\', \'\', $alias);
		$type = \R::findOne(\'content_type\', \'param_url = ?\', [$alias]);
		
		$page = isset($_GET[\'page\']) ? (int)$_GET[\'page\'] : 1;
        $perpage = App::$app->getProperty(\'pagination\');
		
		$total = \R::count(\'contents\', "hide = \'show\' AND type_id = \'$type->id\'");
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();
		
		$conts = \R::findAll(\'contents\', \'type_id = ? ORDER BY date_post DESC LIMIT ?, ?\', [$type->id, $start, $perpage]);

		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["alias"]){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
		$this->setMeta($type->title, $type->description, $type->keywords, \'\' . App::$app->getProperty(\'shop_name\') . \'\', \'\'.PATH.\'/images/\' . App::$app->getProperty(\'og_logo\') . \'\', \'\'.PATH.\'\'.$path_controller.\'\'.$path_alias.\'\');
		/*SEO*/
		
        $this->set(compact(\'conts\', \'type\', \'pagination\'));
	}
';
$phpContent_controller .= '
} ';
		$dir_route = CONF . '/routes.php';
		
		$FileSourse_del = file_get_contents($dir_route);
		$FileSourse_del = preg_replace("#
//".$fileName."//.*//And".$fileName."//#is", '', $FileSourse_del);
		file_put_contents($dir_route, $FileSourse_del);
		
		$phpRoute = "//".$fileName."//
Router::add('^".$data["param_url"]."/(?P<alias>[a-z0-9-]+)/?$', ['controller' => '".$fileName."', 'action' => 'view']);
//And".$fileName."//
//  Add here";
		
		$FileSourse = file_get_contents($dir_route);
		$FileSourse = str_replace('//  Add here',$phpRoute,$FileSourse);
		file_put_contents($dir_view, $phpContent_view);
		file_put_contents($dir_controller, $phpContent_controller);
		file_put_contents($dir_route, $FileSourse);
    }
	
	public function checkUnique(){
        $type = \R::findOne('content_type', 'name = ? AND param_url = ?', [$this->attributes['name'], $this->attributes['param_url']]);
        if($type){
            if($type->name == $this->attributes['name']){
                $this->errors['unique'][] = 'Название типа контента уже существует';
            }
			if($type->param_url == $this->attributes['param_url']){
                $this->errors['unique'][] = 'Служебное URL уже существует';
            }
            return false;
        }
        return true;
    }
}