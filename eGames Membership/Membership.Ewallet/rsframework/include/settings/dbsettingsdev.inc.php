<?php


/************************************************
Author: Roger Sanchez
Date Created: May 12, 2010
Company: Philweb
**************************************************/

// POS DB Globals
global $_DBCONF;

/****** Development ********/

$membership["host"] = "172.16.102.157";
//$membership["host"] = "localhost";
$membership["username"] = "pegsconn";
$membership["password"] = "pegsconnpass";
$membership["dbname"] = "membership";
$_DBCONF["membership"] = $membership;

$membership["host"] = "172.16.102.158";
$membership["username"] = "pegsconn";
$membership["password"] = "pegsconnpass";
$membership["dbname"] = "membership";
$_DBCONF["membershipqa"] = $membership;

$membership["host"] = "172.16.102.157";
//$membership["host"] = "localhost";
$membership["username"] = "pegsconn";
$membership["password"] = "pegsconnpass";
$membership["dbname"] = "membership_temp";
$_DBCONF["tempmembership"] = $membership;

$loyalty["host"] = "172.16.102.157";
//$loyalty["host"] = "localhost";
$loyalty["username"] = "pegsconn";
$loyalty["password"] = "pegsconnpass";
$loyalty["dbname"] = "loyaltydb";
$_DBCONF["loyalty"] = $loyalty;

$rewardsdb["host"] = "172.16.102.157";
//$rewardsdb["host"] = "localhost";
$rewardsdb["username"] = "pegsconn";
$rewardsdb["password"] = "pegsconnpass";
$rewardsdb["dbname"] = "rewardsdb";
$_DBCONF["rewardsdb"] = $rewardsdb;

$kronus["host"] = "172.16.102.157";
//$kronus["host"] = "localhost";
$kronus["username"] = "pegsconn";
$kronus["password"] = "pegsconnpass";
$kronus["dbname"] = "npos";
$_DBCONF["kronus"] = $kronus;

?>
