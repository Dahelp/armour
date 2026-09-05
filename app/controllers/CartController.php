<?php

namespace app\controllers;

use app\models\admin\Company;
use app\models\Cart;
use app\models\Order;
use app\models\User;
use ishop\App;

class CartController extends AppController {

    public function addAction(){
		$id = max(0, (int)($_GET['id'] ?? 0));
		$qty = max(1, (int)($_GET['qty'] ?? 1));
		$mod_id = max(0, (int)($_GET['mod'] ?? 0));
		$modification = !empty($_GET['modification']);
        $mod = null;
		$max = 0;
		if ($id < 1) {
			return false;
		}
		$product = \R::findOne('product', 'id = ?', [$id]);
		if (!$product) {
			return false;
		}

        $cart = new Cart();
		
		if($modification){
			$sumMods = 0;
			$prices = [(float)$product->price];
			$mods = \R::findAll('modification', 'product_id = ?', [$product->id]);
			foreach ($mods as $modificationItem) {
				$sumMods += (int)$modificationItem->quantity;
				$prices[] = (float)$modificationItem->price;
			}
			$max = (int)$product->quantity + $sumMods;
			$mod = (object)[
				'id' => $mod_id,
				'name_modification' => 'unified',
				'price' => max($prices),
				'article' => (string)$product->article,
				'unit' => 'шт',
				'weight' => (float)($product->weight ?? 0),
				'volume' => (float)($product->volume ?? 0),
				'category_id' => (int)$product->category_id,
				'model' => (string)($product->model ?? ''),
			];
				
			$cart->addToCart($product, $qty, $max, $mod);
			
		}else{
			if($mod_id){
				$mod = \R::findOne('modification', 'id = ? AND product_id = ?', [$mod_id, $id]);
				if (!$mod) {
					return false;
				}
			}
			$max = (int)($mod->quantity ?? $product->quantity ?? 0);
			$cart->addToCart($product, $qty, $max, $mod);
			
		}
		if($this->isAjax() && ($_GET['format'] ?? '') === 'json'){
			$key=$mod ? "{$product->id}-{$mod->id}" : (string)$product->id;
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['result'=>(int)($_SESSION['cart'][$key]['qty']??0),'result2'=>(int)($_SESSION['cart.qty']??0)],JSON_THROW_ON_ERROR);
			die;
		}
		if($this->isAjax()){
            $this->loadView('cart_modal');
        }
        redirect();
    }
	
    public function showAction(){
        $this->loadView('cart_modal');
    }	
	
// delete product id modal
    public function deleteAction(){
        $id = !empty($_GET['id']) ? $_GET['id'] : null;
        if(isset($_SESSION['cart'][$id])){
            $cart = new Cart();
            $cart->deleteItem($id);
        }
        if($this->isAjax()){
            $this->loadView('cart_modal');
        }
        redirect();
    }
	
// delete product id cart	
	public function deletecartAction(){
        $id = !empty($_GET['id']) ? $_GET['id'] : null;
        if(isset($_SESSION['cart'][$id])){
            $cart = new Cart();
            $cart->deleteItem($id);
        }
        if($this->isAjax()){
            $this->loadView('cart_table');
        }
        redirect();
    }
	
// increase product id cart	
	public function pluscartAction(){
        if($_GET) {
			$id = !empty($_GET['id']) ? $_GET['id'] : null;
			$qty = isset($_GET['qty']) ? $_GET['qty'] : 1;
			if(isset($_SESSION['cart'][$id])){
				$cart = new Cart();
				$cart->pluscartItem($id);
			}						
			$qty = (int)($_SESSION['cart'][$id]['qty'] ?? 0);
			$total = (int)($_SESSION['cart.qty'] ?? 0);
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['result'=>$qty,'result2'=>$total],JSON_THROW_ON_ERROR);
			die;
		}        
    }


// reduce product id cart	
	public function minuscartAction(){
        if($_GET) {
			$id = !empty($_GET['id']) ? $_GET['id'] : null;
			$qty = isset($_GET['qty']) ? $_GET['qty'] : 1;
			if(isset($_SESSION['cart'][$id])){
				$cart = new Cart();
				$cart->minuscartItem($id);
			}			
			$qty = (int)($_SESSION['cart'][$id]['qty'] ?? 0);
			$total = (int)($_SESSION['cart.qty'] ?? 0);
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['result'=>$qty,'result2'=>$total],JSON_THROW_ON_ERROR);
			die;
		}
    }

    public function clearAction(){
        unset($_SESSION['cart']);
        unset($_SESSION['cart.qty']);
        unset($_SESSION['cart.sum']);
		unset($_SESSION['cart.weight']);
		unset($_SESSION['cart.volume']);
        unset($_SESSION['cart.currency']);
		unset($_SESSION['promocart']);
        $this->loadView('cart_modal');
    }

    public function viewAction(){
		/*SEO*/
		if($this->route["controller"]){ $path_controller = "/".mb_strtolower($this->route["controller"]).""; }else{ $path_controller = ""; }
		if($this->route["alias"]){ $path_alias = "/".$this->route["alias"].""; }else{ $path_alias = ""; }
		$this->setMeta('Корзина', 'Корзина', '', '' . App::$app->getProperty('shop_name') . '', ''.PATH.'/images/' . App::$app->getProperty('og_logo') . '', ''.PATH.''.$path_controller.''.$path_alias.'');
		/*SEO*/
    }
	
    public function checkoutAction(){
        if(!empty($_POST)){
			$usok = \R::findOne('user', 'email = ?', [$_POST['email']]);			
				if($usok["id"]) {
					$user_id = $usok["id"];
					$comp_id = $usok["comp_id"];
					$groups = $usok["groups"];
					$data = $_POST;				
				}else{
					// регистрация пользователя
					if(!User::checkAuth()){
						$user = new User();
						$data = $_POST;
						$first = substr($data["telefon"], 4,1);
						$first = (int)$first;
						if(!$first) { 
							$data["telefon"] = "";
							$_SESSION['error'] = 'Проверьте правильно введённый номер телефона!';
							$_SESSION['form_data'] = $data;
							redirect(); 
						}
						$user->load($data);
						if(!$user->validate($data)){
							$user->getErrors();
							$_SESSION['form_data'] = $data;
							redirect();
						}else{
							$user->attributes['password'] = password_hash($user->attributes['password'], PASSWORD_DEFAULT);
							if(!$user_id = $user->save('user')){
								$_SESSION['error'] = 'Ошибка!';
								redirect();
							}
						}
						if(!$_FILES['rekvizity']) {
							if($data['groups'] == 4){
								$company = new Company();						
								$company->load($data);
								
								if(!$company->validate($data) || !$company->checkUnique()){
									$company->getErrors();
									$_SESSION['form_data'] = $data;
									redirect();
								}
								$data['tip'] = 1;
								$data['user_id'] = $user_id;
								if($id = $company->save('company')){							
									\R::exec("UPDATE user SET comp_id = '".$id."' WHERE id = ?", [$user_id]);
									\R::exec("INSERT INTO `admin_last_history`(`gh_id`, `ah_id`, `name_tbl`, `id_tbl`, `date_modified`, `customer_id`) VALUES ('2','33','company','".$id."','".date('Y-m-d H:i:s')."','".$user_id."')");
								
								}
								
							}
						}
					}
				}		
				// сохранение заказа
				$data['user_id'] = isset($user_id) ? $user_id : $_SESSION['user']['id'];
				$data['comp_id'] = !empty($comp_id) ? $comp_id : $id;
				$data['comp_short_name'] = !empty($_POST['comp_short_name']) ? $_POST['comp_short_name'] : '';
				$data['inn'] = !empty($_POST['inn']) ? $_POST['inn'] : '';
				$data['note'] = !empty($_POST['note']) ? $_POST['note'] : '';
				$data['dostavka_id'] = !empty($_POST['dostavka_id']) ? $_POST['dostavka_id'] : '';
				$data['address'] = !empty($_POST['address']) ? $_POST['address'] : '';
				$data['transport_id'] = !empty($_POST['transport_id']) ? $_POST['transport_id'] : '';
				$city_name = !empty($_POST['city_name']) ? $_POST['city_name'] : '';
				$cit = \R::findOne('cities', 'city_name = ?', [$city_name]);
				$data['city_id'] = !empty($cit['city_id']) ? $cit['city_id'] : '';
				$data['branch_id'] = !empty($_POST['branch_id']) ? $_POST['branch_id'] : '';
				$data['groups'] = !empty($_SESSION['user']['groups']) ? $_SESSION['user']['groups'] : $_POST['groups'];
				$user_email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : $_POST['email'];
				if($_FILES['rekvizity']) { $rekvizity = '1'; }else{ $rekvizity = '0'; }
				$usm = \R::findOne('user', 'email = ?', [$user_email]);
				if($data['groups'] == 4){
					$comp = \R::findOne('company', 'user_id = ?', [$data['user_id']]);
					if($comp['nds']){
						$data['nds'] = $comp['nds'];
					}
				}
				if($usm["admin_id"] !="0"){
					$data['admin_id'] = $usm["admin_id"];
				}else{
					$data['admin_id'] = 0;
				}
				$order_id = Order::saveOrder($data);
				$ord = \R::findOne('order', 'id = ?', [$order_id]);			
				$dost = \R::findOne('dostavka', 'id = ?', [$ord["dostavka_id"]]);
				$bran = \R::findOne('branch_office', 'branch_id = ?', [$ord["branch_id"]]);
				$trans = \R::findOne('transport_company', 'id = ?', [$ord["transport_id"]]);
				
				if($trans["name"]) { $transport_company = "<b>Название ТК:</b> ".$trans["name"]."<br>"; }
				if($ord["address"] !="") { $address = "<br><b>Адрес:</b> ".$ord["address"]."<br>"; }
				if($data['user_id']) {
					if($usm["groups"] == 3) { $vid = "<b>Вид клиента:</b> Физическое лицо<br>"; }
					if($usm["groups"] == 4) { 				
						$vid = "<b>Вид клиента:</b> Юридическое лицо<br>";
						if($comp) {
							$compname = "<b>Компания (зарегистрирована):</b> ".$comp['comp_short_name']." (".$comp['inn'].")<br>";						
							if($comp["nds"] == "1") { $nds = "<b>Налогообложение:</b> c НДС<br>"; }
							if($comp["nds"] == "2") { $nds = "<b>Налогообложение:</b> без НДС<br>"; } 
							if($comp["dogovor"] == "1") { $dogovor = "<b>Условия поставки:</b> Договор<br>"; }
							if($comp["dogovor"] == "2") { $dogovor = "<b>Условия поставки:</b> Счёт-договор<br>"; }
						}else{
							if($data['inn']) {
								$compname = "<b>Компания:</b> ".$data['comp_short_name']." (".$data['inn'].")<br>";
							}
							if($_POST["nds"] == "1") { $nds = "<b>Налогообложение:</b> c НДС<br>"; }
							if($_POST["nds"] == "2") { $nds = "<b>Налогообложение:</b> без НДС<br>"; } 
							if($_POST["dogovor"] == "1") { $dogovor = "<b>Условия поставки:</b> Договор<br>"; }
							if($_POST["dogovor"] == "2") { $dogovor = "<b>Условия поставки:</b> Счёт-договор<br>"; }
						}							
					}
				}
				Order::mailOrder($order_id, $user_email, $usm["name"], $usm["telefon"], $usm["admin_id"], $ord["note"], $ord["date"], $dost["name"], $bran["branch_name"], $address, $transport_company, $city_name, $vid, $compname, $nds, $dogovor);
				
        }
        redirect();
    }
	
	public function dostavkaAction(){
		if($_GET) {
			$dostavka_id = isset($_GET['dostavka_id']) ? $_GET['dostavka_id'] : '';			
			$dos = \R::findOne('dostavka', 'id = ?', [$dostavka_id]);			

			echo json_encode(array('result'=>''.$dos.''));		
			die;
		}
	}

}
