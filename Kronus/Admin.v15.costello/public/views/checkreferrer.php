<?php
require_once "sys/core/init.php";

if(referrer != substr($_SERVER['HTTP_REFERER'],0,strlen(referrer))) {
    header('HTTP/1.0 403 Forbidden');
    $option = '';
    
    //$referrer = $_SERVER['HTTP_REFERER'];
    //echo $referrer;
    //return $header;
}
else {
    $option = 'Authorized';
}
echo $option;
//header("Location: login.php");
?>