<?php

/*
 * @author : owliber
 * @date : 2013-05-03
 */

require_once("init.inc.php");

App::LoadCore("URL.class.php");
session_destroy();
URL::Redirect("index.php");

?>

