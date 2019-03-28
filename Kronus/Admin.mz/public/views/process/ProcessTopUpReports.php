<?php
session_start();
/*
 * Created By: Arlene Salazar
 * Purpose: Controller for Top Up Reports
 * Created On: June 23,2011
 */
require '../../sys/core/init.php';
include "../../sys/class/DbReport.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

#$conn = new DBReport('mysql:host=172.16.102.35;dbname=npos','nposconn','npos');
$conn = new DBReport($_DBConnectionString[0]);
$dbconn = $conn->open();

if($dbconn)
{
   $type = strtolower($_GET['type']);
   
   if($type == "gh")
   {
       header('Location: ../views/TopUpGHMonitoring.php');
   }
   else if($type == 'ghb') {
       header('Location: ../views/GrossHoldBalance.php');
   }
   else if($type == 'cn') {
       header('Location: ../views/Confirmation.php');
   }
   else if($type == "pd")
   {
       header('Location: ../views/PostedDeposit.php');
   }
   else if($type == "rr")
   {
       header('Location: ../views/Replenishment.php');
   }
   else if($type == "at")
   {
       header('Location: ../views/AuditTrail.php');
   }
   else if($type == "rd")
   {
        header('Location: ../views/ReversalOfDeposits.php');
   }
   else if($type == "mt")
   {
        header('Location: ../views/ManualTopUp.php');
   }
   else if($type == "atp")
   {
       header('Location: ../views/AutoTopUp.php');
   }
   else if($type == "th")
   {
       header('Location: ../views/TopUpHistory.php');
   }
   else if($type == "mr")
   {
       header('Location: ../views/TopUpManualRedemption.php');
   }
   else if($type == "rm")
   {
       header('Location: ../views/ReversalOfManualTopUp.php');
   }
   else if($type == 'bc')
   {
       header('Location: ../views/BettingCredit.php');
   }
   elseif($type == "pb")
   {
       header('Location: ../views/PlayingBalance.php');
   }
   else
   {
       echo "You cannot access this page";
   }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
