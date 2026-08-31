<?php

namespace app\controllers\admin;

use app\services\UrlAliasRepository;

use app\models\admin\Brand;
use app\models\AppModel;
use ishop\App;

class BrandController extends AppController {

    public function indexAction(){
        $brands = \R::getAll("SELECT * FROM brand ORDER BY name");
        $this->setMeta('Список производителей');
        $this->set(compact('brands'));
    }
	
	public function addImageAction(){
        if(isset($_GET['upload'])){
            if($_POST['name'] == 'single'){
                $wmax = App::$app->getProperty('img_width_brand');
                $hmax = App::$app->getProperty('img_height_brand');
            }
            $name = $_POST['name'];
            $brand = new Brand();
            $brand->uploadImg($name, $wmax, $hmax);
        }
    }
	
	public function deleteAction(){
        $id = $this->getRequestID();        
        $brand = \R::load('brand', $id);
		\R::exec("DELETE FROM url_alias WHERE sef = ? AND view = ?", [$brand["alias"], 'Marka']);
		@unlink(WWW . "/images/brand/baseimg/".$brand["img"]."");	
        \R::trash($brand);
        $_SESSION['success'] = 'Производитель '.$brand["name"].' удален';
        redirect();
    }
	
	public function addAction(){
        if(!empty($_POST)){
            $brand = new Brand();
            $data = $_POST;
            $brand->load($data);
            $brand->getImg();

            if(!$brand->validate($data) || !$brand->checkUnique()){
                $brand->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $brand->save('brand')){
                $alias = AppModel::createAlias('brand', 'alias', $data['name'], $id);
                $b = \R::load('brand', $id);
				
				if($data['alias']!=""){
					$alias_str = AppModel::createAlias('brand', 'alias', $data['alias'], $id);
					$b->alias = "marka-".$alias_str."";
				}else{
					$b->alias = "marka-".$alias."";
				}
				/*Url_Alias*/
				(new UrlAliasRepository())->save((string)$b->alias, 'Marka', (int)$id);
				/*Url_Alias*/				
                
                \R::store($b);
                $_SESSION['success'] = 'Производитель добавлен';
            }
            redirect();
        }

        $this->setMeta('Новый производитель');
    }
	
	public function editAction(){
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $brand = new Brand();
            $data = $_POST;
            $brand->load($data);
            $brand->getImg();
            if(!$brand->validate($data)){
                $brand->getErrors();
                redirect();
            }
            if($brand->update('brand', $id)){               
                $alias = AppModel::createAlias('brand', 'alias', $data['name'], $id);
                $brand = \R::load('brand', $id);
				
				if($data['alias']!=""){
					$alias_str = AppModel::createAlias('brand', 'alias', $data['alias'], $id);
					$brand->alias = "marka-".$alias_str."";
				}else{
					$brand->alias = "marka-".$alias."";
				}
				
				/*Url_Alias*/
				(new UrlAliasRepository())->save((string)$brand->alias, 'Marka', (int)$id);
				/*Url_Alias*/
                
                \R::store($brand);
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }

        $id = $this->getRequestID();
        $brand = \R::load('brand', $id);
        App::$app->setProperty('parent_id', $brand->category_id);      		
        $this->setMeta("Редактирование производителя {$brand->name}");
        $this->set(compact('brand'));
    }
	
	public function deleteBaseimgAction(){
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $src = isset($_POST['src']) ? $_POST['src'] : null;
        if(!$id || !$src){
            return;
        }
        if(\R::exec("UPDATE brand SET img = '' WHERE id = ? AND img = ?", [$id, $src])){
            @unlink(WWW . "/images/brand/baseimg/$src");			
            exit('1');
        }
        return;
    }
}
