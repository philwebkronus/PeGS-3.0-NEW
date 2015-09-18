<?php

include("../sys/class/Login.class.php");
require '../sys/core/init.php';

$sessionid = '';
$aid = 0;

if(isset($_SESSION['sessionID']) && isset($_SESSION['accID']))
{
     $sessionid = $_SESSION['sessionID'];
     $aid = $_SESSION['accID'];   
}

$ologin = new Login($_DBConnectionString[0]);
$openconn = $ologin->open();

//generating date with microseconds
$date = $ologin->getDate();
$ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);

if($openconn)
{
    //insert to audittrail table
    if($aid > 0 && strlen($sessionid) > 0){
        $transdetails = $gsysversion;
        $vauditfuncID = 2;
        $ologin->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $vauditfuncID);
        $ologin->deletesession($aid);
    }
   
    unset($sessionid, $aid, $_SESSION['acctype']);

    session_destroy();
    $ologin->close();
    header('Location:  ../login.php');
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}

?>
