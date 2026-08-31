<?php

namespace app\controllers\admin;

use app\services\ImageFileStorage;
use app\services\AdminAuditLogger;
use app\services\RelationWriter;
use app\services\UrlAliasRepository;

use app\models\admin\FiltrsAttr;
use app\models\admin\FiltrsGroup;
use app\models\AppModel;
use ishop\App;

class FiltrsController extends AppController{

    public function groupDeleteAction(){
        $id = $this->getRequestID();
        $count = \R::count('attribute_value', 'attr_group_id = ?', [$id]);
        if($count){
            $_SESSION['error'] = 'Удаление невозможно, в группе есть атрибуты';
            redirect();
        }
		$group = \R::findOne('attribute_group', 'id = ?', [$id]);
		if($group["url_params"]) {
		$fileName = ucfirst($group["url_params"]);
		@unlink(APP . "/controllers/".$fileName."Controller.php");
		$dir = "".APP."/views/".TEMPLATE."/".$fileName."";
		if (file_exists($dir) && is_dir($dir)) {
			chmod($dir, 0777 );
			if ($elements = glob($dir."/*")) {
			  foreach($elements as $element) {
				is_dir($element) ? removeDirectory($element) : unlink($element);
			  }
			}
			rmdir($dir);
		}
		$dir_route = CONF . '/routes.php';
		$FileSourse_del = file_get_contents($dir_route);
		$FileSourse_del = preg_replace("#
//".$fileName."//.*//And".$fileName."//#is", '', $FileSourse_del);
		file_put_contents($dir_route, $FileSourse_del);
		}
		$group_controller = App::upFirstLetter($group["url_params"]);
        \R::exec('DELETE FROM attribute_group WHERE id = ?', [$id]);
		\R::exec('DELETE FROM attribute_category WHERE group_id = ?', [$id]);
		\R::exec("DELETE FROM url_alias WHERE sef = ? AND view = ?", [$group["url_params"], $group_controller]);
		AdminAuditLogger::log(2, 30, 'attribute_group', (int)$id);
        $_SESSION['success'] = 'Удалено';
        redirect();
    }
	
	public function addImageAction(){
        if(isset($_GET['upload'])){
            if($_POST['name'] == 'single'){
                $wmax = App::$app->getProperty('img_width_filter');
                $hmax = App::$app->getProperty('img_height_filter');
            }
            $name = $_POST['name'];
            $attr = new FiltrsAttr();
            $attr->uploadImg($name, $wmax, $hmax);
        }
    }
	
    public function attributeDeleteAction(){
        $id = $this->getRequestID();
        \R::exec("DELETE FROM attribute_product WHERE attr_id = ?", [$id]);
        \R::exec("DELETE FROM attribute_value WHERE id = ?", [$id]);
        $_SESSION['success'] = 'Удалено';
        redirect();
    }

    public function attributeEditAction(){
        if(!empty($_POST)){			
            $id = $this->getRequestID(false);
            $attr = new FiltrsAttr();
            $data = $_POST;			
            $attr->load($data);
			$attr->getImg();
            if(!$attr->validate($data)){
                $attr->getErrors();
                redirect();
            }
			if($attr->update('attribute_value', $id)){               
                $alias = AppModel::createAlias('attribute_value', 'alias', $data['value'], $id);
                $attr = \R::load('attribute_value', $id);
				
				$last = \R::load('attribute_group', $attr->attr_group_id);
				$last_controller = App::upFirstLetter($last->url_params);
				
				if($data['alias']!=""){
					$alias_str = AppModel::createAlias('attribute_value', 'alias', $data['alias'], $id);
					$attr->alias = "".$last->url_params."-".$alias_str."";
				}else{
					$attr->alias = "".$last->url_params."-".$alias."";
				}
				
				/*Url_Alias*/
				(new UrlAliasRepository())->save((string)$attr->alias, $last_controller, (int)$id);
				/*Url_Alias*/
                
                \R::store($attr);
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }
        }
        $id = $this->getRequestID();
        $attr = \R::load('attribute_value', $id);
        $attrs_group = \R::findAll('attribute_group');
        $attrs = \R::getCell("SELECT url_params FROM attribute_group WHERE id = ?", [$attr->attr_group_id]);
        
        $this->setMeta('Редактирование атрибута');
        $this->set(compact('attr', 'attrs_group', 'attrs'));
    }

    public function attributeAddAction(){
        if(!empty($_POST)){
            $attr = new FiltrsAttr();
            $data = $_POST;
            $attr->load($data);
			$attr->getImg();
            if(!$attr->validate($data) || !$attr->checkUnique()){
                $attr->getErrors();
                redirect();
            }            
			if($id = $attr->save('attribute_value', false)){
                $alias = AppModel::createAlias('attribute_value', 'alias', $data['value'], $id);				
                $attr = \R::load('attribute_value', $id);
				$last = \R::load('attribute_group', $attr->attr_group_id);
				$last_controller = App::upFirstLetter($last->url_params);
				
				if($data['alias']!=""){
					$alias_str = AppModel::createAlias('attribute_value', 'alias', $data['alias'], $id);
					$attr->alias = "".$last->url_params."-".$alias_str."";
				}else{
					$attr->alias = "".$last->url_params."-".$alias."";
				}
				/*Url_Alias*/
				(new UrlAliasRepository())->save((string)$attr->alias, $last_controller, (int)$id);
				/*Url_Alias*/	
				
                
                \R::store($attr);
                $_SESSION['success'] = 'Атрибут добавлен';
            }
        }
        $group = \R::findAll('attribute_group');
        $this->setMeta('Новый фильтр');		
        $this->set(compact('group'));
    }

    public function groupEditAction(){
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $existingGroup = \R::load('attribute_group', $id);
            if (!$existingGroup->id) {
                throw new \RuntimeException('Группа фильтров не найдена', 404);
            }
            $previousSef = (string)$existingGroup->url_params;
            $previousController = $previousSef !== '' ? App::upFirstLetter($previousSef) : '';
            $group = new FiltrsGroup();
            $data = $_POST;
            $group->load($data);
            if(!$group->validate($data)){
                $group->getErrors();
                redirect();
            }
			if($data['template'] == 0){ // autotemplate || handmade
				$groups = \R::findOne('attribute_group', 'id = ?', [$id]);
				if($groups["url_params"]) {
				list($url1, $url2) = explode('-', $groups["url_params"]);
				list($url1, $url2) = explode('_', $groups["url_params"]);
				$fileName = "".ucfirst($url1)."".ucfirst($url2)."";
				@unlink(APP . "/controllers/".$fileName."Controller.php");
				$dir = "".APP."/views/".TEMPLATE."/".$fileName."";
				if (file_exists($dir) && is_dir($dir)) {
					chmod($dir, 0777 );
					if ($elements = glob($dir."/*")) {
					  foreach($elements as $element) {
						is_dir($element) ? removeDirectory($element) : unlink($element);
					  }
					}
					rmdir($dir);
				}
				$dir_route = CONF . '/routes.php';
				$FileSourse_del = file_get_contents($dir_route);
				$FileSourse_del = preg_replace("#
//".$fileName."//.*//And".$fileName."//#is", '', $FileSourse_del);
				file_put_contents($dir_route, $FileSourse_del);
			}
			}
            if($group->update('attribute_group', $id)){
				if($data['template'] == 0){ // autotemplate || handmade
					$group->addClassGroup($data);
				}
				
				$updatedGroup = \R::load('attribute_group', $id);
				$currentSef = (string)$updatedGroup->url_params;
				$currentController = $currentSef !== '' ? App::upFirstLetter($currentSef) : '';
				/*Url_Alias*/
				$aliasRepository = new UrlAliasRepository();
				if ($currentSef !== '') {
					$aliasRepository->save($currentSef, $currentController, (int)$id);
				}
				if ($previousSef !== '' && ($previousSef !== $currentSef || $previousController !== $currentController)) {
					$aliasRepository->remove($previousSef, $previousController);
				}
				/*Url_Alias*/
				
				(new RelationWriter())->replace('attribute_category', 'group_id', (int)$id, 'category_id', (array)($_POST['category_id'] ?? []));
				AdminAuditLogger::log(2, 29, 'attribute_group', (int)$id);
                $_SESSION['success'] = 'Изменения сохранены';
                redirect();
            }

        }
        $id = $this->getRequestID();
        $group = \R::load('attribute_group', $id);
        $this->setMeta("Редактирование группы {$group->title}");
		$category = \R::findAll('category');		
        $this->set(compact('group', 'category'));
    }

    public function groupAddAction(){
        if(!empty($_POST)){
            $group = new FiltrsGroup();
            $data = $_POST;
            $group->load($data);
            if(!$group->validate($data) || !$group->checkUnique()){
                $group->getErrors();
                redirect();
            }
            if($id = $group->save('attribute_group')){
				$group->addClassGroup($data);
				$createdGroup = \R::load('attribute_group', $id);
				
				/*Url_Alias*/
				if($createdGroup->url_params != ""){
					$createdController = App::upFirstLetter((string)$createdGroup->url_params);
					(new UrlAliasRepository())->save((string)$createdGroup->url_params, $createdController, (int)$id);
				}				
				/*Url_Alias*/
				
				(new RelationWriter())->replace('attribute_category', 'group_id', (int)$id, 'category_id', (array)($_POST['category_id'] ?? []));
				AdminAuditLogger::log(2, 28, 'attribute_group', (int)$id);
                $_SESSION['success'] = 'Группа добавлена';
                redirect();
            }
        }
        $this->setMeta('Новая группа фильтров');
		$category = \R::findAll('category');
		$this->set(compact('category'));
    }

    public function attributeGroupAction(){
        $attrs_group = \R::findAll('attribute_group');
        $this->setMeta('Группы фильтров');
        $this->set(compact('attrs_group'));
    }

    public function attributeAction(){
        $attrs = \R::getAll("SELECT attribute_value.*, attribute_group.title as gname, attribute_group.url_params FROM attribute_value JOIN attribute_group ON attribute_group.id = attribute_value.attr_group_id ORDER BY attribute_value.value");
        $this->setMeta('Фильтры');
        $this->set(compact('attrs'));
    }
	
	public function deleteBaseimgAction(){
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $src = ImageFileStorage::safeName((string)($_POST['src'] ?? ''));
        if(!$id || !$src){
            return;
        }
        if(\R::exec("UPDATE attribute_value SET img = '' WHERE id = ? AND img = ?", [$id, $src])){
            ImageFileStorage::delete(WWW . '/images/filtrs/baseimg', $src);
            exit('1');
        }
        return;
    }

}
