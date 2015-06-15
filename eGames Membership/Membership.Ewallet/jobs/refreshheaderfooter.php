<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-05-21
 * Company: Philweb
 * ***************** */
$curdir = dirname(__FILE__);
require_once($curdir . "/../init.inc.php");

App::LoadCore("File.class.php");

//$header = "http://192.168.29.212/pegs/header.php";
//$footer = "http://192.168.29.212/pegs/footer.php";
$header = "http://www.egamescasino.ph/header.php";
$footer = "http://www.egamescasino.ph/footer.php";

$headerfile = $curdir . "/../templates/headertemplate.php";
$footerfile = $curdir . "/../templates/footertemplate.php";

$s = curl_init();
curl_setopt($s, CURLOPT_URL, $header);
curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
$headerstring = curl_exec($s);
$headerfp = new File($headerfile);
$headerfp->Write($headerstring);


$s = curl_init();
curl_setopt($s, CURLOPT_URL, $footer);
curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
$footerstring = curl_exec($s);
$footerfp = new File($footerfile);
$footerfp->Write($footerstring);
?>
