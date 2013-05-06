<?php
$ip=$_SERVER['REMOTE_ADDR'];
// filter localhost only
if($ip != '127.0.0.1' && $ip != '::1') {
    die('Forbidden');
}

define('MIRAGE_DEBUG', true);
date_default_timezone_set('Asia/Manila');
require_once 'app/mi/Mirage.php';

$app_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'frontend';

// use the config of cashier
$config_file = $app_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

// use own route
$route_file = $app_path . DIRECTORY_SEPARATOR . 'sub_modules' . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.php';

Mirage::createWebApp($app_path, array('config_file'=>$config_file,'route_file'=>$route_file))->run();