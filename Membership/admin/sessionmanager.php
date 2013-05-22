<?php

/*
 * @author : owliber
 * @date : 2013-05-17
 */
require_once("../init.inc.php");
App::LoadCore("URL.class.php");

if(!isset($_SESSION['Username']) && !isset($_SESSION['AID']))
{
    URL::Redirect('login.php');
}
?>
