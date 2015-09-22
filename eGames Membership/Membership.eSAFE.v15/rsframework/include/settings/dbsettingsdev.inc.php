<?php


/************************************************
Author: Roger Sanchez
Date Created: May 12, 2010
Company: Philweb
**************************************************/

// POS DB Globals
global $_DBCONF;

/****** Development ********/

$membership["host"] = "";
$membership["username"] = "";
$membership["password"] = "";
$membership["dbname"] = "membership";
$_DBCONF["membership"] = $membership;

$membership["host"] = "";
$membership["username"] = "";
$membership["password"] = "";
$membership["dbname"] = "membership";
$_DBCONF["membershipqa"] = $membership;

$membership["host"] = "";
$membership["username"] = "";
$membership["password"] = "";
$membership["dbname"] = "membership_temp";
$_DBCONF["tempmembership"] = $membership;

$loyalty["host"] = "";
$loyalty["username"] = "";
$loyalty["password"] = "";
$loyalty["dbname"] = "loyaltydb";
$_DBCONF["loyalty"] = $loyalty;

$rewardsdb["host"] = "";
$rewardsdb["username"] = "";
$rewardsdb["password"] = "";
$rewardsdb["dbname"] = "rewardsdb";
$_DBCONF["rewardsdb"] = $rewardsdb;

$kronus["host"] = "";
$kronus["username"] = "";
$kronus["password"] = "";
$kronus["dbname"] = "npos";
$_DBCONF["kronus"] = $kronus;

?>
