<?php
/*
* Author: Lea Tuazon
* Date Created:
*
* Modified By: Edson L. Perez
*/

require_once __DIR__.'/../sys/core/init.php';
//include __DIR__.'/../sys/class/CSManagement.class.php';
include __DIR__.'/../sys/class/TopUp.class.php';
require_once __DIR__.'/../sys/class/helper/common.class.php';
include_once __DIR__.'/../sys/class/CasinoCAPIHandler.class.php';


$aid = 0;
if(isset($_SESSION['sessionID']))
{
   $new_sessionid = $_SESSION['sessionID'];
}
else
{
   $new_sessionid = '';
}
if(isset($_SESSION['accID']))
{
   $aid = $_SESSION['accID'];
}
$vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
//$ortg= new CSManagement($_DBConnectionString[0]);
$ortg = new TopUp($_DBConnectionString[0]);
$connected = $ortg->open();

if($connected)
{
/*************SESSION CHECKING ****************/
   $isexist=$ortg->checksession($aid);
   if($isexist == 0)
   {
     session_destroy();
     $msg = "Not Connected";
     $ortg->close();
     if($ortg->isAjaxRequest())
     {
         header('HTTP/1.1 401 Unauthorized');
         echo "Session Expired";
         exit;
     }
     header("Location: login.php?mess=".$msg);
   }    
   $isexistsession =$ortg->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
     session_destroy();
     $msg = "Not Connected";
     $ortg->close();
     header("Location: login.php?mess=".$msg);
   }
/************* END SESSION CHECKING ************/
$login = $_SESSION['login'];

$serverId = $_SESSION['serverid'];
mt_srand( (double) microtime() * 1000000 );
$dtTransactionId = date("YmdHis") . mt_rand( 10000, 99999 );
$servicegroupname = $ortg->getServiceGrpName($serverId);

//config for RTG, need to change depositMethodID and withdrawalMethodID when put on production

if(strpos($_ServiceAPI[$serverId-1], 'ECFTEST') !== false)
{
   $gdeposit = 502;
   $gwithdraw = 503;
}
else
{
   $gdeposit = 503;
   $gwithdraw = 502;
}

$usermode = 0;
$configuration = array( 'URI' => $_ServiceAPI[$serverId-1],
                        'isCaching' => FALSE,
                        'isDebug' => TRUE,
                        'certFilePath' => RTGCerts_DIR . $serverId  . '/cert.pem',
                        'keyFilePath' => RTGCerts_DIR . $serverId  . '/key.pem',
                        'depositMethodId' =>$gdeposit ,
                        'withdrawalMethodId' => $gwithdraw,
                        'userMode' => $usermode);
$usermode = 0;
$configuration = array( 'URI' => $_ServiceAPI[$serverId-1],
                       'isCaching' => FALSE,
                       'isDebug' => TRUE,
                       'certFilePath' => RTGCerts_DIR . $serverId  . '/cert.pem',
                       'keyFilePath' => RTGCerts_DIR . $serverId  . '/key.pem',
                       'depositMethodId' =>$gdeposit ,
                       'withdrawalMethodId' => $gwithdraw,
                       'userMode' => $usermode);

        if(preg_match('/RTG2/', $servicegroupname) != 0){
            $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG2, $configuration );
        } else {
            $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
        }

       if ( (bool)$_CasinoAPIHandler->IsAPIServerOK() )
       {
           if(isset($_SESSION['chkbalance']) == 'CheckBalance')
           {
             //GetBalance
             $rbalance = array();
             $rbalance = $_CasinoAPIHandler->GetBalance( $login );

             if($rbalance['IsSucceed'] == true)
             {
                 if(isset($rbalance['BalanceInfo']['Balance'])){
                     $rtgbalance = $rbalance['BalanceInfo']['Balance'];
                     $response->Balance = number_format($rtgbalance, 2, '.', ',');
                     echo json_encode($response);
                 } else {
                     echo 'Cannot connect to the casino';
                 }
             }
             else
             {
                echo "Error retrieving balance ".$rbalance['ErrorMessage'];
             }  
             $ortg->close();
             exit;
           }
           
           if(isset ($_SESSION['Withdraw']) == 'Withdraw')
           {
               $ramount = ereg_replace(",", "", $_SESSION['txtamount']); //format number replace (,)
               $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                   
               if($ramount > $_maxRedeem)
               {  
                   $balance = $ramount - $_maxRedeem;
                   $ramount = $_maxRedeem;
               }
               else
               {
                   $balance = 0;
               }
               
               $rwithdraw = array();
               $rwithdraw = $_CasinoAPIHandler->Withdraw($login, $ramount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '', $methodname = '');
               
               //check if redemption was successfull, and insert information on manualredemptions and audittrail
               if($rwithdraw['IsSucceed'] == true )
               {
                   //fetch the information when calling the MG Withdraw Method
                   foreach($rwithdraw as $results)
                   {
                       $riserror = $results['WithdrawGenericResult']['errorMsg'];
                       $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                       $rwamount = $results['WithdrawGenericResult']['amount'];
                       $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                       $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                   }
                   
                   $fmteffdate = str_replace("T", " ", $reffdate);
                   $vsiteID = $_SESSION['site'];
                   $vterminalID = $_SESSION['terminal'];
                   $vreportedAmt = $ramount;
                   $vactualAmt = $rwamount;
                   $vtransactionDate = $ortg->getDate();
                   $vreqByAID = $aid;
                   $vprocByAID = $aid;
                   //$vremarks = $rremarks;
                   $vdateEffective = $fmteffdate;
                   $vstatus = 1;
                   $vtransactionID = $rtransactionID;
                   $vremarks = $_SESSION['txtremarks'];
                   $vticket = $_SESSION['txtticket'];
                   $cmbServerID = $_SESSION["serverid"]; #Added on July 2, 2012
                   $cardnumber = $_SESSION['txtcardnumber'];
                   $mid = '';
                   
                   $server = $ortg->getCasinoName($cmbServerID);
                   foreach ($server as $value2) {
                       $servername = $value2['ServiceName'];
                       $stat = $value2['Status'];
                       $usermode = $value2['UserMode'];
                   }
                   //check if there was no error on withdrawal
                   if($riserror == "OK")
                   {
                       
                       $vtransStatus = $rremarks;
                       $transsummaryid = $ortg->getLastSummaryID($vterminalID);
                       $transsummaryid = $transsummaryid['summaryID'];
                       if($cardnumber == ''){
                           $issucess = $ortg->insertmanualredemption($vsiteID, $vterminalID,
                                       $vreportedAmt, $vactualAmt, $vtransactionDate,
                                       $vreqByAID, $vprocByAID, $vremarks, $vdateEffective,
                                       $vstatus, $vtransactionID, $transsummaryid,$vticket,$cmbServerID, $vtransStatus);
                       }
                       else {
                           $mid = $_SESSION['txtmid'];
                           $issucess = $ortg->insertmanualredemptionub($vsiteID, $vterminalID,
                                       $vreportedAmt, $vactualAmt, $vtransactionDate,
                                       $vreqByAID, $vprocByAID, $vremarks, $vdateEffective,
                                       $vstatus, $vtransactionID, $transsummaryid,$vticket,$cmbServerID, $vtransStatus, $cardnumber, $mid, $usermode);
                       }
                       
                     
                       if($issucess > 0)
                       {
                          //insert into audit trail
                          $vtransdetails = "transaction id ".$vtransactionID.",amount ".$vreportedAmt;
                          $vauditfuncID = 7;
                          $ortg->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                          $msg = "Redeemed: ".$ramount."; Remaining Balance: ".$balance;
                       }
                       else
                       {
                          $msg = "Manual Redemption: Error on inserting manual redemption";
                       }
                   }
                   else
                   {
                       if($riserror == "")
                       {
                           $msg = $rremarks;
                       }
                       else
                       {
                           $msg = $riserror; //error message when calling the withdrawal result
                       }
                   }
               }
               else
               {
                   $msg = $rwithdraw['ErrorMessage']; //error message when initially calling the RTG API
               }
               $_SESSION['mess'] = $msg;
               unset($login);
               unset($serverId);
               unset($_SESSION['site']);
               unset($_SESSION['terminal']);
               unset($_SESSION['chkbalance']);
               unset($_SESSION['Withdraw']);
               unset($_SESSION['txtamount']);
               $ortg->close();
               if($cardnumber == ''){
                  header("Location: ../manualredemption.php");
               }
               else{
                  header("Location: ../manualredemption_ub.php");
               }
               unset($_SESSION['txtcardnumber']);
               unset($_SESSION['txtmid']);
           }
       }
       else
       {
           $errmsg = 'Casino server not available.';
           $response->error = $errmsg;
           echo json_encode($response);
       }
       $ortg->close();
}
else{
   $msg = "Not Connected";    
   header("Location: login.php?mess=".$msg);
}
?>
