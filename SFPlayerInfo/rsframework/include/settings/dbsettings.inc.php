<?php

/* * **********************************************
  Author: Roger Sanchez
  Date Created: May 12, 2010
  Company: Philweb
 * ************************************************ */

// POS DB Globals
global $_DBCONF;

$membership["host"] = "";
$membership["username"] = "";
$membership["password"] = "";
$membership["dbname"] = "membership";
$_DBCONF["membership"] = $membership;

$membershiptemp["host"] = "";
$membershiptemp["username"] = "";
$membershiptemp["password"] = "";
$membershiptemp["dbname"] = "membership_temp";
$_DBCONF["membershiptemp"] = $membershiptemp;

$loyalty["host"] = "";
$loyalty["username"] = "";
$loyalty["password"] = "";
$loyalty["dbname"] = "loyaltydb";
$_DBCONF["loyalty"] = $loyalty;

$kronus["host"] = "";
$kronus["username"] = "";
$kronus["password"] = "";
$kronus["dbname"] = "npos";
$_DBCONF["kronus"] = $kronus;
?>
