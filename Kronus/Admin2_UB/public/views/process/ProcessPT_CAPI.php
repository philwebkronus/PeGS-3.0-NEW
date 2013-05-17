<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__.'/../sys/core/init.php';
include __DIR__.'/../sys/class/TopUp.class.php';
require_once __DIR__.'/../sys/class/helper/common.class.php';
include_once __DIR__.'/../sys/class/CasinoCAPIHandler.class.php';

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

//$opt= new CSManagement($_DBConnectionString[0]);
$opt = new TopUp($_DBConnectionString[0]);
$connected = $opt->open();

if($connected)
{
/************** SESSION CHECKING ******************/
    $isexist=$opt->checksession($aid);
    if($isexist == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $opt->close();
      if($opt->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$opt->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
       session_destroy();
       $msg = "Not Connected";
       $opt->close();
       header("Location: login.php?mess=".$msg);
    }
/************** END SESSION CHECKING ******************/
  $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
  $vdate = $opt->getDate();
  $login= $_SESSION['login'];
  
  $serverId = $_SESSION['serverid'];
  mt_srand( (double) microtime() * 1000000 );
  $dtTransactionId =  mt_rand( 10000, 99999 );

    //config for PT
  $configuration = array('URI'=>$_ServiceAPI[$serverId-1],
                         'isCaching'=>FALSE,
                         'isDebug'=>TRUE,
                         'authLogin'=>$_ptcasinoname,
                         'secretKey'=>$_ptsecretkey);
  
  $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::PT,$configuration);
  
  if((bool)$_CasinoAPIHandler->IsAPIServerOK())
  {
      //GetBalance 
      if(isset($_SESSION['chkbalance']) == 'CheckBalance')
      {
          $rbalance = array();
          
          $rbalance = $_CasinoAPIHandler->GetBalance($login);
          
          if($rbalance['IsSucceed'] == true)
          {
              $ptbalance = $rbalance['BalanceInfo']['Balance'];
              $response->Balance = number_format($ptbalance, 2, '.', ',');
              echo json_encode($response);
          } else {
              echo 'Error retrieving balance '.$rbalance['ErrorMessage'];
          }
          $opt->close();
          exit();
      }
      
      //PT Manual Redemption
      if(isset($_SESSION['Withdraw']) == 'Withdraw')
      {
          //get manualredemptionID
          $originID = 2;
          $manualredemptionID = $opt->insertserviceTransRef($serverId, $originID);
          if(!$manualredemptionID){
                $msg = "Manual Redemption: Error on inserting servicetransactionref";
          } else {
                $tracking2 = $manualredemptionID;
            
                //format number, replace (,)
                $ramount = ereg_replace(",", "", $_SESSION['txtamount']); 
                $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)

                if($ramount > $_maxRedeem)
                {  
                    $balance = $ramount - $_maxRedeem;
                    $ramount = $_maxRedeem;
                }
                else
                    $balance = 0;
                
                $rwithdraw = array();
                $vterminalID = '';
                $vsiteID = '';
                $vterminalID = $_SESSION['terminal'];
                
                //Get PT Terminal Password
                $servicePwdResult = $opt->getterminalcredentials($vterminalID, $serverId);
                $tracking1 = $servicePwdResult['ServicePassword'];

                $rwithdraw = $_CasinoAPIHandler->Withdraw($login, $ramount, $tracking1, $tracking2, $tracking3='', $tracking4='', $methodname='');
                
                //check if redemption was successfull and insert information on manualredemptions and audittrail
                if($rwithdraw['IsSucceed'] == true)
                {
                    foreach ($rwithdraw as $results)
                    {
                          $riswithdraw= $rwithdraw['IsSucceed'];
                          $rwamount = abs($rwithdraw['TransactionInfo']['PT']['balance']);
                          $rtransactionID = $rwithdraw['TransactionInfo']['PT']['tranid'];
                          $rerrorcode = $rwithdraw['ErrorCode'];
                          if($rerrorcode <> 0)
                          {
                              $rerrormsg = $rwithdraw['ErrorMessage'];
                          }
                    }
                    
                    $vsiteID = $_SESSION['site'];
                    $vreportedAmt = $ramount;
                    $vactualAmt = $rwamount;
                    $vtransactionDate = $vdate;
                    $vreqByAID = $aid;
                    $vprocByAID = $aid;
                    $vremarks = $_SESSION['txtremarks'];
                    $vticket = $_SESSION['txtticket'];
                    $vdateEffective = $vdate;
                    $vstatus = 1;
                    $vtransactionID = $rtransactionID;
                    $cmbServerID = $_SESSION["serverid"]; #Added on July 2, 2012
                    $cardnumber = $_SESSION['txtcardnumber'];
                    $mid = '';
                    
                    $server = $opt->getCasinoName($cmbServerID);
                    foreach ($server as $value2) {
                        $servername = $value2['ServiceName'];
                        $stat = $value2['Status'];
                        $usermode = $value2['UserMode'];
                    }
                    
                    //check if withdrawal result was successfull and if there was no error
                    if($riswithdraw == true && $rerrorcode == 0)
                    {   
                          $vtransStatus = "approved";
                          $transsummaryid = $opt->getLastSummaryID($vterminalID);
                          $transsummaryid = $transsummaryid['summaryID'];
                          if($cardnumber == ''){
                              $issucess = $opt->insertmanualredemption($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus);
                          }
                          else
                          {
                              $mid = $_SESSION['txtmid'];
                              $issucess = $opt->insertmanualredemptionub($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid, $vticket, $cmbServerID, $vtransStatus, $cardnumber, $mid, $usermode);
                          }

                          //check if successfully inserted on DB
                          if ($issucess > 0) {
                             //insert into audit trail
                             $vtransdetails = "transaction id " . $vtransactionID . ",amount " . $vreportedAmt;
                             $vauditfuncID = 7;
                             $opt->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                             $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                          } else {
                            $msg = "Manual Redemption: Error on inserting manual redemption";
                          }
                    } else {
                        $msg = $rerrormsg; //error message when calling the withdrawal result.
                    }

                } else {
                    $msg = $rwithdraw['ErrorMessage']; //error message when calling the withdrawal result
                }
          }
      } else {
          $msg = $rwithdraw['ErrorMessage']; //error message when initially calling the PT API.
      }
      
      $_SESSION['mess'] = $msg;
      unset($login);
      unset($serverId);
      unset($_SESSION['site']);
      unset($_SESSION['terminal']);
      unset($_SESSION['chkbalance']);
      unset($_SESSION['Withdraw']);
      unset($_SESSION['txtamount']);
      $opt->close();
        if($cardnumber == ''){
            header("Location: ../manualredemption.php"); 
        }
        else{
            header("Location: ../manualredemption_ub.php"); 
        }
      unset($_SESSION['txtcardnumber']);
      unset($_SESSION['txtmid']);
      
  } else {
      $errmsg = 'Casino server not available.';
      $response->error = $errmsg;
      echo json_encode($response);
  }

  $opt->close();
}
else{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}

?>
