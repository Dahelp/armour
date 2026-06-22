<?php
use ishop\App;
use ishop\Router;
use app\models\AppModel;

$urli = $_SERVER['REQUEST_URI'];
//$urli = urldecode($urli);
$baseUrl = strtok($urli, '?');
$urli = trim($baseUrl, '/');
//debug($urli); exit();
$urls = new AppModel();
$urlalias = $urli !== '' ? $urls->urlalias($urli) : false;
if($urlalias){ 
	Router::add('^(?P<alias>[a-z0-9\-*.\/]+)/?$', ['controller' => ''.$urlalias->view.'', 'action' => 'view']);
}else{
//Router::add('^category/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Category', 'action' => 'view']);
//Router::add('^podbor/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Podbor', 'action' => 'index']);
//Router::add('^size/(?P<alias>[a-z0-9-./\s]+)/?$', ['controller' => 'Size', 'action' => 'view']);
//Router::add('^cross/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Cross', 'action' => 'view']);
//Router::add('^technics/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Technics', 'action' => 'view']);
//Router::add('^technics/type/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Technics', 'action' => 'type']);
//Router::add('^technics/(?P<type>[a-z0-9-]+)/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Technics', 'action' => 'manufacturer']);


//Pages//
Router::add('^pages/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Pages', 'action' => 'view']);
//AndPages//
//Articles//
Router::add('^articles/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Articles', 'action' => 'view']);
//AndArticles//
//News//
Router::add('^news/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'News', 'action' => 'view']);
//AndNews//
//Protect//
Router::add('^protect/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Protect', 'action' => 'view']);
//AndProtect//
////
Router::add('^/(?P<alias>[a-z0-9-]+)/?$', ['controller' => '', 'action' => 'view']);
//And//
//Tiposize//
Router::add('^tiposize/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Tiposize', 'action' => 'view']);
//AndTiposize//
//Diameter//
Router::add('^diameter/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Diameter', 'action' => 'view']);
//AndDiameter//
//Marka//
Router::add('^marka/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Marka', 'action' => 'view']);
//AndMarka//
//Disk//
Router::add('^disk/(?P<alias>[a-z0-9-]+)/?$', ['controller' => 'Disk', 'action' => 'view']);
//AndDisk//
//  Add here

// default routes
Router::add('^admin$', ['controller' => 'Main', 'action' => 'index', 'prefix' => 'admin']);
Router::add('^admin/?(?P<controller>[a-z-]+)/?(?P<action>[a-z-]+)?$', ['prefix' => 'admin']);

Router::add('^$', ['controller' => 'Main', 'action' => 'index']);
Router::add('^(?P<controller>[a-z-]+)/?(?P<action>[a-z-]+)?$');
}
