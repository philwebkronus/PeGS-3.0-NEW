<?php
define('MIRAGE_DEBUG', false);
date_default_timezone_set('Asia/Manila');
require_once 'app/mi/Mirage.php';

$app_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'frontend';
$config_path = $app_path . DIRECTORY_SEPARATOR . 'config';

Mirage::createWebApp($app_path, $config_path)->run();
