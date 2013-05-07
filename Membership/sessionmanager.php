<?php

require_once("init.inc.php");

App::LoadCore("URL.class.php");

if(!isset($_SESSION["MemberInfo"]) && $_SESSION["MemberInfo"] == "")
{
    URL::Redirect("index.php");
}
?>
