<?php
// запрет прямого обращения
define('BASEPATH', TRUE);

require_once dirname(__DIR__) . '/config/init.php';
require_once LIBS . '/functions.php';
require_once CONF . '/routes.php';

new \ishop\App();

