<?php
require_once("../init.inc.php");
if(App::getParam('referrer') != $_SERVER['HTTP_REFERER']) {
    header('HTTP/1.0 403 Forbidden');
    $option = '';
}
else {
    $option = 'Authorized';
}
echo $option;
    
?>
