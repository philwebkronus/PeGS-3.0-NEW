<?php
/*
 * Author: Lea Tuazon
 * Date Created:
 * 
 * Modified By: Edson L. Perez
 * Purpose: For checking balance of MG Account, Redemption
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

//$omg= new CSManagement($_DBConnectionString[0]);
$omg = new TopUp($_DBConnectionString[0]);
$connected = $omg->open();

if($connected)
{
/************** SESSION CHECKING ******************/
    $isexist=$omg->checksession($aid);
    if($isexist == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $omg->close();
      if($omg->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$omg->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
       session_destroy();
       $msg = "Not Connected";
       $omg->close();
       header("Location: login.php?mess=".$msg);
    }
/************** END SESSION CHECKING ******************/
  $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
  $vdate = $omg->getDate();
  $login= $_SESSION['login'];
  
  $serverId = $_SESSION['serverid'];

          mt_srand( (double) microtime() * 1000000 );
          $dtTransactionId =  mt_rand( 10000, 99999 );
          $_MGCredentials = $_ServiceAPI[$serverId -1]; 
          list($mgurl, $mgserverID) =  $_MGCredentials;
          
          //config for MG
          $configuration = array( 'URI' => $mgurl,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'authLogin'=>$_CAPIUsername,
                                'authPassword'=>$_CAPIPassword,
                                'playerName'=>$_CAPIPlayerName,
                                'serverID'=>$mgserverID,
                                'currency' => $_MicrogamingCurrency );

            $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::MG, $configuration );
            

            if ( (bool)$_CasinoAPIHandler->IsAPIServerOK() )
            {
                if(isset($_SESSION['chkbalance']) == 'CheckBalance')
                {
                  //GetBalance
                  $rbalance = array();
                  
                  $rbalance = $_CasinoAPIHandler->GetBalance( $login );
                  
                  if ($rbalance['IsSucceed'] == true)
                  {
                      if(isset($rbalance['BalanceInfo']['Balance'])){
                          $mgbalance = $rbalance['BalanceInfo']['Balance'];
                          $response->Balance = number_format($mgbalance, 2, '.', ',');
                          echo json_encode($response);
                      } else {
                          echo 'Cannot connect to the casino';
                      }
                  }
                  else
                  {
                     echo "Error retrieving balance ".$rbalance['ErrorMessage'];
                  }
                  $omg->close();
                  exit;
                }

                if(isset ($_SESSION['Withdraw']) == 'Withdraw')
                {
                    $ramount = ereg_replace(",", "", $_SESSION['txtamount']); //format number replace (,)
                    $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
                    $redeemedAmount = number_format($ramount,2, '.', ','); //added number format for alert
                    
                    
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
                    $vterminalID = $omg->viewTerminalID($login);
                    $servicePwdResult = $omg->getterminalcredentials($vterminalID['TerminalID'], $serverId);
                    $methodname = $_MicrogamingMethod;
                    $originID = 2; //manual redemption origin ID
                    
                    //$manualredemptionID = $omg->getLastInsertedID();
                    $manualredemptionID = $omg->insertserviceTransRef($serverId, $originID);
                    if(!$manualredemptionID){
                        $msg = "Manual Redemption: Error on inserting servicetransactionref";
                    }
                    else{
                        $transactionID = $manualredemptionID;
                        $eventID = $_CAPIEventID;
                       
                        //Withdraw
                        $rwithdraw = $_CasinoAPIHandler->Withdraw($login, $ramount, $servicePwdResult['ServicePassword'], $transactionID , $eventID, $transactionID, $methodname );
                        
                        //check first if the API responded
                        if($rwithdraw['IsSucceed'] == true )
                        {

                            //fetch the information when calling the MG Withdraw Method
                            foreach($rwithdraw as $results)
                            {
                                $riswithdraw = $rwithdraw['IsSucceed']; 
                                $rwamount = abs($rwithdraw['TransactionInfo']['TransactionAmount']/100);
                                $rtransactionID = $rwithdraw['TransactionInfo']['TransactionId'];
                                $rerrorcode = $rwithdraw['ErrorCode'];
                                if($rerrorcode <> 0)
                                {
                                    $rerrormsg = $rwithdraw['ErrorMessage'];
                                }
                            }

                            $vsiteID = $_SESSION['site'];
                            $vterminalID = $_SESSION['terminal'];
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

                            $server = $omg->getCasinoName($cmbServerID);
                            foreach ($server as $value2) {
                                $servername = $value2['ServiceName'];
                                $stat = $value2['Status'];
                                $usermode = $value2['UserMode'];
                            }
                            
                            //check if withdrawal result was successfull and if there was no error
                            if($riswithdraw == true && $rerrorcode == 0)
                            {
                                $vtransStatus = "Transaction Approved";
                                $transsummaryid = $omg->getLastSummaryID($vterminalID);
                                $transsummaryid = $transsummaryid['summaryID'];
                                if($cardnumber == ''){
                                    $issucess = $omg->insertmanualredemption($vsiteID, $vterminalID,
                                                $vreportedAmt, $vactualAmt, $vtransactionDate, 
                                                $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, 
                                                $vstatus, $vtransactionID, $transsummaryid,$vticket, $cmbServerID,$vtransStatus);
                                }
                                else {
                                    $mid = $_SESSION['txtmid'];
                                    $issucess = $omg->insertmanualredemptionub($vsiteID, $vterminalID,
                                                $vreportedAmt, $vactualAmt, $vtransactionDate, 
                                                $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, 
                                                $vstatus, $vtransactionID, $transsummaryid,$vticket, $cmbServerID,$vtransStatus, $cardnumber, $mid, $usermode);
                                }
                                
                                //check if successfully inserted on DB
                                if($issucess > 0)
                                {
                                   $omg->updateTerminalSessions($balance, $vterminalID);
                                   //insert into audit trail
                                   $vtransdetails = "transaction id ".$vtransactionID.",amount ".$vreportedAmt;
                                   $vauditfuncID = 7;
                                   $omg->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                                   $msg = "Redeemed: ".$redeemedAmount."; Remaining Balance: ".$balance;
                                }
                                else
                                {
                                   $msg = "Manual Redemption: Error on inserting manual redemption";
                                }
                            }
                            else
                            {
                                $msg = $rerrormsg; //error message when calling the withdrawal result
                            }
                        }
                        else
                        {
                            $msg = $rwithdraw['ErrorMessage']; //error message when initially calling the MG API
                        }
                    }
                    
                    $_SESSION['mess'] = $msg;
                    $omg->close();
                    unset($login);
                    unset($serverId);
                    unset($_SESSION['site']);
                    unset($_SESSION['terminal']);
                    unset($_SESSION['chkbalance']);
                    unset($_SESSION['Withdraw']);
                    unset($_SESSION['txtamount']);
                    unset($_SESSION['agentid']);
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
    
 
  
  $omg->close();
}
else{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
