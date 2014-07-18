<?php

/**
 * @author Noel Antonio
 * @dateCreated 03-13-2014
 */

$generateURI = "http://<CHANGE URI HERE>/cron/ticketgeneration.php";
$expiryURI = "http://<CHANGE URI HERE>/cron/ticketexpiration.php";

$update_limit = 5000; // This portion is used to setup 5000 tickets to be updated every run of cron.

$host = "";
$user = "";
$pass = "";
$dbvms = "vouchermanagement";
$dbspyder = "spyder";

global $connString;

$connString[0] = 'mysql:host='.$host.';dbname='.$dbvms.','.$user.','.$pass;
$connString[1] = 'mysql:host='.$host.';dbname='.$dbspyder.','.$user.','.$pass;
?>
