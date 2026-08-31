<?php

namespace app\models\admin;

use ishop\App;
use app\models\AppModel;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_Attachment;

class Order extends AppModel {
	
	public function addCompany($data): int {
		$date_create = date('Y-m-d H:i:s');
		$company = \R::dispense('company');
		$company->comp_name = (string)($data['comp_name'] ?? '');
		$company->user_id = (int)($data['user_id'] ?? 0);
		$company->tip = 1;
		$company->url_address = '';
		$company->postal_address = '';
		$company->ogrn = '';
		$company->inn = '';
		$company->kpp = '';
		$company->bik = '';
		$company->raschet = '';
		$company->korschet = '';
		$company->bank = '';
		$company->dir_name = '';
		$company->nds = (string)($data['nds'] ?? '');
		$company->dogovor = '';
		$company->data_create = $date_create;
		$company->hide = 'show';

		return (int)\R::store($company);
	}
	
	public function addUser($data): int {
		$number = 8;
		$password = \ishop\App::generate_password($number);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$user = \R::dispense('user');
		$user->password = $password;
		$user->email = (string)($data['email'] ?? '');
		$user->name = (string)($data['user_name'] ?? '');
		$user->telefon = (string)($data['telefon'] ?? '');
		$user->role = 'user';
		$user->groups = (int)($data['vid'] ?? 0);
		$user->admin_id = (int)($_SESSION['user']['id'] ?? 0);
		$user->comp_id = '';
		$user->date_create = date('Y-m-d H:i:s');
		$user->newsletter = '';
		$user->uxeh = '';
		$user->uid_ya = '';
		$user->uid_gg = '';
		$user->uid_vk = '';

		return (int)\R::store($user);
	}
	
	public function addOrder($data): int {
		$order_prefix = \ishop\App::options('order_prefix');
		$curr = \R::findOne('currency');
		$order = \R::dispense('order');
		$order->inv = '';
		$order->user_id = (int)($data['user_id'] ?? 0);
		$order->admin_id = (int)($data['admin_id'] ?? 0);
		$order->comp_id = (int)($data['comp_id'] ?? 0);
		$order->seller = (int)($data['seller'] ?? 0);
		$order->status = 1;
		$order->date = date('Y-m-d H:i:s');
		$order->update_at = '';
		$order->dostavka_id = (int)($data['dostavka_id'] ?? 0);
		$order->transport_id = (int)($data['transport_id'] ?? 0);
		$order->branch_id = (int)($data['branch_id'] ?? 0);
		$order->city_id = (int)($data['city_id'] ?? 0);
		$order->address = (string)($data['address'] ?? '');
		$order->currency = (string)($curr['code'] ?? '');
		$order->note = (string)($data['note'] ?? '');
		$id = (int)\R::store($order);
		$order->inv = (string)$order_prefix . $id;
		\R::store($order);

		return $id;
	}
	
	public function addOrderProduct($id, $data){
		$this->insertOrderProducts((int)$id, (array)($data['order_zakaz'] ?? []));
	}

	public function editOrder($id, $data){
		\R::exec('UPDATE `order` SET comp_id = ?, dostavka_id = ?, transport_id = ?, branch_id = ?, city_id = ?, address = ? WHERE id = ?', [
			(int)($data['comp_id'] ?? 0),
			(int)($data['dostavka_id'] ?? 0),
			(int)($data['transport_id'] ?? 0),
			(int)($data['branch_id'] ?? 0),
			(int)($data['city_id'] ?? 0),
			(string)($data['address'] ?? ''),
			(int)$id,
		]);
	}
	
	public function editOrderProduct($id, $data){
		// удалим все и запишем новые
		\R::exec('DELETE FROM order_product WHERE order_id = ?', [(int)$id]);
		$this->insertOrderProducts((int)$id, (array)($data['order_zakaz'] ?? []));
	}

	private function insertOrderProducts(int $orderId, array $products): void {
		$rows = [];
		$bindings = [];
		foreach ($products as $product) {
			$productId = (int)($product['product_id'] ?? 0);
			if ($productId < 1) {
				continue;
			}
			$productName = (string)\R::getCell('SELECT name FROM product WHERE id = ? LIMIT 1', [$productId]);
			$rows[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
			array_push($bindings,
				$orderId,
				$productId,
				(string)($product['article'] ?? ''),
				(float)($product['quantity'] ?? 0),
				$productName,
				(float)($product['price'] ?? 0),
				(float)($product['discount_value'] ?? 0),
				(string)($product['discount_type'] ?? ''),
				(float)($product['discount'] ?? 0),
				(float)($product['discount_amount'] ?? 0)
			);
		}
		if ($rows !== []) {
			\R::exec(
				'INSERT INTO order_product (order_id, product_id, article, qty, name, price, discount_value, discount_type, discount, discount_amount) VALUES ' . implode(',', $rows),
				$bindings
			);
		}
	}
	
	public function managerEmail($email, $order_id, $user){
		$order = \R::findOne('order', 'id = ?', [$order_id]);
		$order_product = \R::getAll('SELECT * FROM `order_product` WHERE `order_id` = ?', [$order_id]);
		$dost = \R::findOne('dostavka', 'id = ?', [$order["dostavka_id"]]);
		$bran = \R::findOne('branch_office', 'branch_id = ?', [$order["branch_id"]]);
		$trans = \R::findOne('transport_company', 'id = ?', [$order["transport_id"]]);
		$cit = \R::findOne('cities', 'city_id = ?', [$order["city_id"]]);
		$transport_company = '';
		$address = '';
		$vid = '';
		$nds = '';
		$dogovor = '';
		if($trans["name"] ?? '') { $transport_company = "<b>Название ТК:</b> ".$trans["name"]."<br>"; }
		if($order["address"] !="") { $address = "<br><b>Адрес:</b> ".$order["address"]."<br>"; }
		if($user["groups"] == 3) { $vid = "<b>Вид клиента:</b> Физическое лицо<br>"; }
		if($user["groups"] == 4) {
			$comp = \R::findOne('company', 'user_id = ?', [$user['id']]);
			$vid = "<b>Вид клиента:</b> Юридическое лицо<br>";
			if($comp["nds"] == "1") { $nds = "<b>Налогообложение:</b> c НДС<br>"; }
			if($comp["nds"] == "2") { $nds = "<b>Налогообложение:</b> без НДС<br>"; } 
			if($comp["dogovor"] == "1") { $dogovor = "<b>Условия поставки:</b> Договор<br>"; }
			if($comp["dogovor"] == "2") { $dogovor = "<b>Условия поставки:</b> Счёт-договор<br>"; }																		
		}

		// Create the Transport
        $transport = (new Swift_SmtpTransport(App::$app->getProperty('smtp_host'), App::$app->getProperty('smtp_port'), App::$app->getProperty('smtp_protocol')))
            ->setUsername(App::$app->getProperty('smtp_login'))
            ->setPassword(App::$app->getProperty('smtp_password'))
        ;
        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);
		$namecomp = App::$app->getProperty('shop_name');
		$tell_site = \ishop\App::options('option_telefon');

		// Create a message
        ob_start();
        require APP . '/views/'.TEMPLATE.'/mail/mail_manager.php';
        $body = ob_get_clean();

        $message_manager = (new Swift_Message("Сделан заказ №{$order["inv"]} на сайте " . App::$app->getProperty('shop_name')))
            ->setFrom([App::$app->getProperty('smtp_login') => App::$app->getProperty('shop_name')])
            ->setTo($email)
            ->setBody($body, 'text/html')
		;

        $result = $mailer->send($message_manager);
	}
	
	public function changeEmail($order_id, $user, $template){
		$order = \R::findOne('order', 'id = ?', [$order_id]);
		$order_product = \R::getAll('SELECT `product`.`name`, `product`.`alias` FROM `order_product`, `product` WHERE `order_product`.`product_id` = `product`.`id` AND `order_product`.`order_id` = ?', [$order_id]);
		// Create the Transport
        $transport = (new Swift_SmtpTransport(App::$app->getProperty('smtp_host'), App::$app->getProperty('smtp_port'), App::$app->getProperty('smtp_protocol')))
            ->setUsername(App::$app->getProperty('smtp_login'))
            ->setPassword(App::$app->getProperty('smtp_password'))
        ;
        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);
		$namecomp = App::$app->getProperty('shop_name');
		$tell_site = \ishop\App::options('option_telefon');

		// Create a message
        ob_start();
        require APP . '/views/'.TEMPLATE.'/mail/'.$template.'.php';
        $body = ob_get_clean();

        $message_manager = (new Swift_Message("Изменён статус заказа №{$order["inv"]} на сайте " . App::$app->getProperty('shop_name')))
            ->setFrom([App::$app->getProperty('smtp_login') => App::$app->getProperty('shop_name')])
            ->setTo($user["email"])
            ->setBody($body, 'text/html')
		;

        $result = $mailer->send($message_manager);
	}
	
}
