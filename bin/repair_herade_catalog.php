<?php
require_once dirname(__DIR__).'/config/environment.php';
$config=require dirname(__DIR__).'/config/config_db.php';
$db=new PDO($config['dsn'],$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$result=['ok'=>true];
$result['groups']=$db->query('SELECT id,title,url_params FROM attribute_group ORDER BY id')->fetchAll();
$result['allowed_groups']=$db->query('SELECT group_id FROM attribute_category WHERE category_id=(SELECT category_id FROM product WHERE article="12341")')->fetchAll();
$result['product_filters']=$db->query("SELECT ap.product_id,av.id,av.attr_group_id,av.value,av.alias FROM attribute_product ap JOIN attribute_value av ON av.id=ap.attr_id WHERE ap.product_id IN (SELECT id FROM product WHERE article IN ('12341','9162','10302')) ORDER BY ap.product_id,av.attr_group_id")->fetchAll();
$result['matching_values']=$db->query("SELECT id,attr_group_id,value,alias,hide FROM attribute_value ORDER BY attr_group_id,id")->fetchAll();
$result['size_values']=$db->query("SELECT av.id,av.attr_group_id,av.value,av.alias,av.hide FROM attribute_value av JOIN attribute_group ag ON ag.id=av.attr_group_id WHERE LOWER(ag.url_params)='tiposize' ORDER BY av.id")->fetchAll();
$result['size_aliases']=$db->query("SELECT sef,view,urlid FROM url_alias WHERE LOWER(view)='tiposize' ORDER BY urlid")->fetchAll();
$result['legacy_size_redirect_count']=(int)$db->query("SELECT COUNT(*) FROM legacy_url_redirect WHERE source_path LIKE 'tiposize-%.html' AND is_active=1")->fetchColumn();
$result['legacy_example']=$db->query("SELECT source_path,target_path,status_code,is_active FROM legacy_url_redirect WHERE source_path='tiposize-11.00-20.html'")->fetchAll();
echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);