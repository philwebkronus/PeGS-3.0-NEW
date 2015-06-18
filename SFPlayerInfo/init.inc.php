<?php

/* * *****************
 * Author: Roger Sanchez
 * Date Created: 2013-04-01
 * Company: Philweb
 * ***************** */
require_once("rsframework/include/core/init.inc.php");//full path of init.inc.php of rsframework
App::ClearStatus();

//allowed domains
$domain[] = "test.salesforce.com";
$domain[] = "login.salesforce.com";
$domain[] = "force.com";
$_CONFIG["domain"] = $domain;

//set time zone
$_CONFIG["applicationtimezone"] = "Asia/Manila";
?>
