<?php

namespace app\models\admin;

use ishop\App;
use app\models\AppModel;

class FilterGroup extends AppModel{

    public $attributes = [
        'title' => '',
		'url_params' => '',
    ];

    public $rules = [
        'required' => [
            ['title'],
        ],
    ];
	
	public function addClassGroup($data){
		
        $fileName = ucfirst($data["url_params"]);
		mkdir(''. APP . '/views/'.$fileName.'', 0700); //создание папки
		$dir_view = APP . '/views/'.$fileName.'/view.php'; //путь файла контента
		
		$dir_index = APP . '/views/'.$fileName.'/index.php'; //путь файла всех фильтров
			
			$phpContent_index = '
<div class="breadcrumbs">
    <div class="container">
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active"><?=$group->title;?></li>
            </ol>
		</nav>
    </div>
</div>
<div class="contents">
    <div class="container">
		<div class="row">
			<div class="cont-blok">
				
			</div>
		</div>
	</div>	
</div>
';
		file_put_contents($dir_index, $phpContent_index);
		
		
		$dir_controller = APP . '/controllers/'.$fileName.'Controller.php';	
		
		$phpContent_view = '
<!--prdt-starts-->
<div class="prdt">
    <div class="container">
		<!--start-breadcrumbs-->
		<nav class="mb-4 breadcrumb-blok" aria-label="breadcrumb">
			<ol class="breadcrumb flex-lg-nowrap">
                <li class="breadcrumb-item"><a href="<?= PATH ?>"><i class="fas fa-home"></i></a></li>
				<li class="breadcrumb-item active"><a href="<?=$group->url_params?>"><?=$group->title;?></a></li>				
                <li class="breadcrumb-item active"><?=$find->value;?></li>
            </ol>
		</nav>
		<!--end-breadcrumbs-->
		<section class="align-items-center">
            <h1 class="h2 mb-3 mb-md-0 me-3">
				<?php
					if($inseo->name) { 					
						echo $name = \ishop\App::seoreplacefilter($inseo->name, $find->id);
					}
					else { echo $find->name; }
				?>
			</h1>			
        </section>		
			<div class="prdt-top">
            <div class="col-md-12">                
				<?php if(!empty($products)): ?>
                    <div class="row g-0 mx-n2 product-one">
                        <?php $curr = \ishop\App::$app->getProperty(\'currency\'); ?>
                        <?php foreach($products as $product): ?>
                            <div class="col-xl-3 col-lg-6 col-md-4 col-sm-6 mb-3">
					            <div class="card product-card card-static pb-3">
									<div class="znachki">
										<?php if($product->hit) { ?>
											<div class="badge bg-warning badge-shadow">Хит</div>
										<?php } ?>
										<?php if($product->new_product) { ?>
											<div class="badge bg-success badge-shadow">Новинка</div>
										<?php } ?>
										<?php if($product->sale) { ?>
											<div class="badge bg-danger badge-shadow">Скидка</div>
										<?php } ?>
										<button class="btn-wishlist btn-sm" type="button" data-bs-toggle="tooltip" data-bs-placement="left" title="" data-bs-original-title="Add to wishlist" aria-label="Add to wishlist"><i class="far fa-heart"></i></button>
									</div>						            
						            <a class="card-img-top d-block overflow-hidden" href="/<?=$product->alias;?>">
							            <img src="images/product/mini/<?=$product->img;?>" alt="" />
						            </a>
									<?php $cat_prod = \R::findOne(\'category\', "id = ?", [$product->category_id]); ?>
						            <div class="card-body py-2"><span class="product-meta d-block fs-xs pb-1"><?=$cat_prod["name"]?></span>
							            <h3 class="product-title fs-sm text-truncate">
											<a href="/<?=$product->alias;?>">
											<?php
												$inseo_prod = \R::findOne(\'plagins_inseo\', "tip = ? AND category_id = ? AND hide = \'show\'", [\'product\', $category->id]);
												if($inseo_prod->name) { 					
													echo $name = \ishop\App::seoreplace($inseo_prod->name, $product->id);
												}
												else { echo $product->name; }
											?>
											</a>
										</h3>
							            <div class="product-price">
											<div class="product-sku">Код: <?=$product->article;?></div>
											<div class="product-curr">
									            <span class="item_price"><?=$curr[\'symbol_left\];?> <?php echo round($product->price / $curr[\'value\'], 2); ?> <?=$curr[\'symbol_right\'];?></span>
									            <?php if($product->old_price): ?>
									            	<small><del><?=$curr[\'symbol_left\'];?> <?php echo round($product->old_price / $curr[\'value\'], 2); ?> <?=$curr[\'symbol_right\'];?></del></small>
									            <?php endif; ?>
											</div>
							            </div>									
						            									
									<?php if($product->quantity > 0) { ?>
									<div class="product-btn">
										<div class="product-floating-btn">										
											<?php if($_SESSION[\'cart\'][$product->id]) { ?>
												<a data-id="<?=$product->id;?>" class="btn btn-danger btn-shadow btn-cart add-to-cart-link korzina-<?=$product->id;?> clear-korzina" style="display:none;" href="cart/add?id=<?=$product->id;?>" data-max="<?=$product->quantity?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus fs-base"></i> Купить</a>
												<button class="btn btn-success btn-shadow btn-cart vkorzine-<?=$product->id?> clear-vkorzine" style="padding: 4px 10px 4px 10px;">В корзине</button>
											<?php }else{ ?>
												<a data-id="<?=$product->id;?>" class="btn btn-danger btn-shadow btn-cart add-to-cart-link korzina-<?=$product->id;?> clear-korzina" href="cart/add?id=<?=$product->id;?>" data-max="<?=$product->quantity?>" data-toggle="modal" data-target="#exampleModalLive"><i class="fas fa-cart-plus fs-base"></i> Купить</a>
												<button class="btn btn-success btn-shadow btn-cart vkorzine-<?=$product->id?> clear-vkorzine" style="display:none; padding: 4px 10px 4px 10px;">В корзине</button>
											<?php } ?>
										</div>
									</div>
									<div class="product-nalichie">
										<span class="btn-nalichie">В наличии: <?=$product->quantity;?> шт.</span>
									</div>
									<?php }else{ ?>
									<div class="product-btn"></div>
									<div class="product-nonalichie">
										<span class="btn-nonalichie">Нет в наличии</span>
									</div>
									<?php } ?>
									</div>
					            </div>
				            </div>
                        <?php endforeach; ?>
                        <div class="clearfix"></div>
                        <div class="text-center">                            
                            <?php if($pagination->countPages > 1): ?>
                                <?=$pagination;?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <h3>В этой категории товаров пока нет...</h3>
                <?php endif; ?>
				<div class="catalog_text"><?=$find->content?></div>
            </div>
            <div class="clearfix"></div>
        </div>		
	</div>
</div>
<!--product-end-->		
';
		$phpContent_controller = '<?php

namespace app\controllers;

use app\models\Breadcrumbs;
use ishop\App;
use ishop\libs\Pagination;

class ' . $fileName . 'Controller extends AppController {

    public function viewAction(){
		
		$alias = $this->route[\'alias\'];
		$find = \R::findOne(\'attribute_value\', \'value = ?\', [$alias]);
		if(!$find){
            throw new \Exception("Страница не найдена", 404);
        }
		
		$page = isset($_GET[\'page\']) ? (int)$_GET[\'page\'] : 1;
        $perpage = App::$app->getProperty(\'pagination\');
		
		if(!empty($_GET[\'sort\'])){
			if($_GET[\'sort\'] == "price") { $sql_sort = "ORDER BY product.price ASC"; }
			if($_GET[\'sort\'] == "nal") { $sql_sort = "ORDER BY product.stock_status_id DESC"; }
			if($_GET[\'sort\'] == "rate") { $sql_sort = "ORDER BY product.hit DESC"; }
		}
		
        $total = \R::exec("SELECT product_id FROM attribute_product, product WHERE attribute_product.product_id = product.id AND attribute_product.attr_id = \'".$size->id."\' $sql_sort");
		$ids = \R::getAll("SELECT product_id FROM attribute_product, product WHERE attribute_product.product_id = product.id AND attribute_product.attr_id = \'".$size->id."\' $sql_sort");
		foreach($ids as $ds){
			$prid .= "".$ds["product_id"].",";
		}
		$ids = rtrim($prid, ",");
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();
		
        $products = \R::find(\'product\', "hide = \'show\' AND id IN ($ids) $sql_sort LIMIT $start, $perpage");
		
        //InSEO
		$params = \R::findOne(\'attribute_group\', "url_params = ?", [\'size\']);
		$inseo = \R::findOne(\'plagins_inseo\', "tip = ? AND category_id = ? AND hide = \'show\'", [\'attribute_group\', $params["id"]]);
		if($inseo->title) {
			$title = \ishop\App::seoreplacefilter($inseo->title, $size->id);
		}else{ $title = $category->title; }
		if($inseo->description) {
			$description = \ishop\App::seoreplacefilter($inseo->description, $size->id);
		}else{ $description = $category->description; }
		if($inseo->keywords) {
			$keywords = \ishop\App::seoreplacefilter($inseo->keywords, $size->id);
		}else{ $keywords = $category->keywords; }

		$this->setMeta($title, $description, $keywords);
		
        $this->set(compact(\'size\', \'products\', \'breadcrumbs\', \'pagination\', \'total\', \'ids\'));
		
		$type = \R::findOne(\'attribute_group\', \'id = ?\', [$find->attr_group_id]);
		$this->setMeta($find->title, $find->description, $find->keywords);
        $this->set(compact(\'find\', \'type\'));
    }';
$phpContent_controller .= '
	public function indexAction(){
		$alias = $_SERVER[\'REQUEST_URI\'];
		$alias = str_replace(\'/\', \'\', $alias);
		$type = \R::findOne(\'attribute_group\', \'url_params = ?\', [$alias]);
		$group = \R::findAll(\'attribute_value\', \'attr_group_id = ?\', [$type->id]);
		$this->setMeta($type->title, $type->description, $type->keywords);
        $this->set(compact(\'group\', \'type\'));
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
Router::add('^".$data["url_params"]."/(?P<alias>[a-z0-9-]+)/?$', ['controller' => '".$fileName."', 'action' => 'view']);
//And".$fileName."//
//  Add here";
		
		$FileSourse = file_get_contents($dir_route);
		$FileSourse = str_replace('//  Add here',$phpRoute,$FileSourse);
		file_put_contents($dir_view, $phpContent_view);
		file_put_contents($dir_controller, $phpContent_controller);
		file_put_contents($dir_route, $FileSourse);
    }
	
	public function checkUnique(){
        $type = \R::findOne('attribute_group', 'title = ? AND url_params = ?', [$this->attributes['title'], $this->attributes['url_params']]);
        if($type){
            if($type->title == $this->attributes['title']){
                $this->errors['unique'][] = 'Название группы фильтров уже существует';
            }
			if($type->url_params == $this->attributes['url_params']){
                $this->errors['unique'][] = 'Системное имя уже существует';
            }
            return false;
        }
        return true;
    }
}
