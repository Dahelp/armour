<?php 

use ishop\App;

$date_price = date("Y-m-d");
$date_update = date("Y-m-d H:i:s");
$viewcrons = \R::findOne('cron', 'id = ?', [$_GET["id"]]);

if($viewcrons["alias"]==""){ $fileprod = "".$crons["alias"].""; $cron_id = $crons["id"]; }
else { $fileprod = "".$viewcrons["alias"].""; $cron_id = $viewcrons["id"]; }

$exp = explode("/", $fileprod);
$file_name = end($exp); //myimage.jpg
$url_download = $viewcrons["url_download"];
$path = "cron/$url_download";

$ch = curl_init($fileprod);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$html = curl_exec($ch);
curl_close($ch); 

file_put_contents($path, $html);

$data = File("cron/$url_download");
$cnt = count($data);
for ($i=1;$i<count($data);$i++) {
 
    list($c, $e, $o, $d, $r, $f, $rkl, $g, $rkr, $k, $rv, $l, $rspb, $ek, $ekr, $t, $p) = explode(";", $data[$i]);  
  
		$c = ltrim("$c", '0');
		$c = trim($c); //article
		$e = trim($e); //tcena  
		$o = trim($o); //tcena opt
		$d = trim($d); //svobodnoe_kolichestvo
		$r = trim($r); //obshiy_rezerv
		$f = trim($f); //klimovsk
		$rkl = trim($rkl); //rezerv_klimovsk
		$g = trim($g); //krasnodar
		$rkr = trim($rkr); //rezerv_krasnodar
		$k = trim($k); //voronezh
		$rv = trim($rv); //rezerv_voronezh
		$l = trim($l); //sankt-peterburg
		$rspb = trim($rspb); //rezerv_sankt-peterburg
		$ek = trim($ek); //ekaterinburg
		$ekr = trim($ekr); //rezerv ekaterinburg
		$t = trim($t); //kol_postupleniya
		$p = trim($p); //data_postupleniya
		if($p == ""){ $p = "0000-00-00"; }
	    if($c !="") {		  
			  
			$article = $c;
			$quantity = $d+$r;
			$price = $e;
			$opt_price =$o;
			if($quantity !="0") { $stock_status_id = "1"; }
			else {$stock_status_id = "0";}

			$pssql = \R::findOne('product', 'article = ?', [$article]);				
			
			if($pssql["id"]) {
				$updt[] = \R::exec("UPDATE product SET price = '".$price."', data_edit_price = '".$date_price."', opt_price = '".$opt_price."', stock_status_id = '".$stock_status_id."', quantity = '".$quantity."' WHERE id = '".$pssql["id"]."'");			
			
				$branch = \R::getAll("SELECT * FROM branch_office");
				foreach($branch as $br){
					
					$stock = \R::findOne('in_stock', 'product_id = ? AND branch_id = ?', [$pssql["id"], $br["branch_id"]]);
					if($stock){
						$updatestock = \R::exec("UPDATE in_stock SET `quantity` = '".${$br["tbl"]}."', `date_scheduling` = '".$p."' WHERE `product_id` = '".$pssql["id"]."' AND `branch_id` = '".$br["branch_id"]."'");
					}else{		
						$insertstock = \R::exec("INSERT INTO `in_stock`(`branch_id`, `product_id`, `quantity`, `date_scheduling`) VALUES ('".$br["branch_id"]."','".$pssql["id"]."','".${$br["tbl"]}."','".$p."')");					
					}
				}
				
			}else{				
				$mdsql = \R::findOne('modification', 'article = ?', [$article]);
				
				if($mdsql["id"]) {
					$updtmd[] = \R::exec("UPDATE modification SET price = '".$price."', quantity = '".$quantity."' WHERE id = '".$mdsql["id"]."'");			
				
					$branch = \R::getAll("SELECT * FROM branch_office");
					foreach($branch as $br){
						
						$stock = \R::findOne('in_stock', 'product_id = ? AND branch_id = ?', [$mdsql["id"], $br["branch_id"]]);
						if($stock){
							$updatestock = \R::exec("UPDATE in_stock SET `quantity` = '".${$br["tbl"]}."', `date_scheduling` = '".$p."' WHERE `product_id` = '".$mdsql["id"]."' AND `branch_id` = '".$br["branch_id"]."'");
						}else{		
							$insertstock = \R::exec("INSERT INTO `in_stock`(`branch_id`, `product_id`, `quantity`, `date_scheduling`) VALUES ('".$br["branch_id"]."','".$mdsql["id"]."','".${$br["tbl"]}."','".$p."')");					
						}
					}
					
				}
				
				
			}
			
		}
			
}
  


$xcol = array_key_last($updt);
if($xcol <= $cnt ){ 
	\R::exec("UPDATE cron SET date_update = '".$date_update."' WHERE id = '".$cron_id."'");	
	if($_SESSION['user']['id']) { \R::exec("INSERT INTO `admin_last_history`(`gh_id`, `ah_id`, `name_tbl`, `id_tbl`, `date_modified`, `customer_id`) VALUES ('2','49','cron','".$_GET["id"]."','".date('Y-m-d H:i:s')."','".$_SESSION['user']['id']."')"); }
	else { \R::exec("INSERT INTO `admin_last_history`(`gh_id`, `ah_id`, `name_tbl`, `id_tbl`, `date_modified`, `customer_id`) VALUES ('2','51','cron','".$_GET["id"]."','".date('Y-m-d H:i:s')."','NULL')");  }
}
$_SESSION['success'] = 'Задание "'.$viewcrons["name"].'" выполнено!';
redirect("".PATH."/admin/cron");
?>