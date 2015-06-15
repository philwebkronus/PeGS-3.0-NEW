<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-02-14
 * Company: Philweb
 * ***************** */
require_once("include/core/init.inc.php");
App::LoadCore("PHPMailer.class.php");
App::LoadCore("File.class.php");

$fp = new File("ecoupontemplate.php");
$body = $fp->ReadToEnd();

$pm = new PHPMailer();
$pm->AddAddress("rpsanchez@philweb.com.ph", "Roger Sanchez");
//$pm->AddAddress("mmdapula@philweb.com.ph", "Mikko M. Dapula");
//$pm->AddAddress("acmanabal@philweb.com.ph", "Alexander C. Manabal");
//$pm->AddAddress("ammarcos@philweb.com.ph", "Maan Marcos");

$pm->Body = $body;
$pm->AddReplyTo("rpsanchez@philweb.com.ph", "Roger Sanchez");
$pm->From = "viprewardsadmin@philweb.com.ph";
$pm->FromName = "E-Games VIP Rewards Admin";
//$pm->Host = "mail.philweb.com.ph";
$pm->Host = "localhost";
$pm->Subject = "(Sample) E-Games Coupon Redemption";
$pm->IsHTML();
//$pm->AddAttachment($csvdir . $filename);
$pm->Send();
?>
