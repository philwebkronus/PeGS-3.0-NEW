<?php
/*
* Author: Claire Marie Tamayo
* Date Created: 11/24/2017
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
else
{
   $new_sessionid = '';
}

if(isset($_SESSION['accID']))
{
   $aid = $_SESSION['accID'];
}

$vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$otopupmembership = new TopUp($_DBConnectionString[5]);
$ohab = new TopUp($_DBConnectionString[0]);
$connected = $ohab->open();

if($connected)
{
    /*************SESSION CHECKING ****************/
    $isexist=$ohab->checksession($aid);
    if($isexist == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $ohab->close();
        if($ohab->isAjaxRequest())
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=".$msg);
    }    
    
    $isexistsession =$ohab->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $ohab->close();
        header("Location: login.php?mess=".$msg);
    }
    /************* END SESSION CHECKING ************/
    
    $login = $_SESSION['login'];
    $serverId = $_SESSION['serverid'];

    $configuration = array( 'URI' => $_ServiceAPI[$serverId-1],
                            'isCaching' => FALSE,
                            'isDebug' => TRUE,
                            'brandID' => $_HABbrandID,
                            'apiKey' => $_HABapiKey);
    $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::HAB, $configuration );

    if ( (bool)$_CasinoAPIHandler->IsAPIServerOK() )
    {
        if(isset($_SESSION['chkbalance']) == 'CheckBalance')  // FOR MR - checking of balance only
        {
            $vterminalID =  $_SESSION['terminal'];
            //$MIDResult = $ohab->getMIDInfo($vterminalID, $serverId);            
            //$serviceUBResult = $ohab->getUBInfo($MIDResult['MID'], $serverId);                   

            $servicePwdResult = $ohab->getterminalcredentials($vterminalID, $serverId);
            
            // Pass UB UserName and Password
            $rbalance = array();
            //$rbalance = $_CasinoAPIHandler->GetBalance( $serviceUBResult['ServiceUserName'], $serviceUBResult['ServicePassword'] );
            
            // Pass TB UserName and Password
            $rbalance = $_CasinoAPIHandler->GetBalance($login, $servicePwdResult['ServicePassword'] );

            if($rbalance['IsSucceed'] == true)
            {
                if(isset($rbalance['BalanceInfo']['Balance']))
                {
                    $habbalance = $rbalance['BalanceInfo']['Balance'];
                    $response->Balance = number_format($habbalance, 2, '.', ',');
                    echo json_encode($response);
                } 
                else 
                {
                    echo 'Cannot connect to the casino';
                }
            }
            else
            {
                echo "Error retrieving balance ".$rbalance['ErrorMessage'];
            }  
            $ohab->close();
            exit;
       }

       if(isset ($_SESSION['Withdraw']) == 'Withdraw') // FOR MR - Terminal Based
       {
           $ramount = ereg_replace(",", "", $_SESSION['txtamount']); //format number replace (,)
           $repamount = $ramount;
           
           $_maxRedeem = ereg_replace(",", "", $_maxRedeem); //format number replace (,)
           $redeemedAmount = number_format($ramount,2, '.', ','); // added for number format in alert (,)

            if($ramount > $_maxRedeem)
            {  
               $balance = $ramount - $_maxRedeem;
               $ramount = $_maxRedeem;
            }
            else
            {
               $balance = 0;
            }
           
            $vsiteID = $_SESSION['site'];
            $vterminalID = $_SESSION['terminal'];       
            $vdateEffective = $ohab->getDate();   
            $vreqByAID = $aid;               
            $vprocByAID = $aid;                
            $vremarks = $_SESSION['txtremarks'];
            $vstatus = 0;                
            $vtransactionID = 0;                
            $transsummaryid = $ohab->getLastSummaryID($vterminalID);
            $transsummaryid = $transsummaryid['summaryID'];
            $vticket =  $_SESSION['txtticket'];
            $vtransStatus = '';
            //$MIDResult = $ohab->getMIDInfo($vterminalID, $serverId);            
            //$MID = $MIDResult['MID'];
            //$loyaltycardnumber = $MIDResult['LoyaltyCardNumber'];
            $usermode = "1";
            $vtransactionDate = $ohab->getDate();
            
            // INSERT TO MANUALREDEMPTIONS TABLE
            //$lastmrid = $ohab->insertmanualredemptionub($vsiteID, $vterminalID, $repamount, $ramount, $vtransactionDate, $vreqByAID, 
            //        $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid,$vticket, $serverId, 
            //        $vtransStatus, $loyaltycardnumber, $MID, $usermode);
            
            $lastmrid = $ohab->insertmanualredemptionub($vsiteID, $vterminalID, $repamount, $ramount, $vtransactionDate, $vreqByAID, 
                    $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID, $transsummaryid,$vticket, $serverId, 
                    $vtransStatus, Null, Null, 0);

            if($lastmrid > 0)
            {
                //$serviceUBResult = $ohab->getUBInfo($MID, $serverId);
                $servicePwdResult = $ohab->getterminalcredentials($vterminalID, $serverId);

                $tracking1 = "MR" . $lastmrid;
                $tracking2 = $vsiteID;
                $tracking3 = $vterminalID;
                $tracking4 = "";
                $methodname = "";
                $locatorName = "" ;
                
                // Pass UB UserName and Password
                $rwithdraw = array();
                //$rwithdraw = $_CasinoAPIHandler->Withdraw($serviceUBResult['ServiceUserName'], $ramount, $tracking1, $tracking2, $tracking3, 
                //        $tracking4, $methodname, $locatorName, $serviceUBResult['ServicePassword'] );
                
                // Pass TB UserName and Password
                $rwithdraw = $_CasinoAPIHandler->Withdraw($login, $ramount, $tracking1, $tracking2, $tracking3, 
                        $tracking4, $methodname, $locatorName, $servicePwdResult['ServicePassword'] );

                //check if redemption was successfull
                if($rwithdraw['IsSucceed'] == true )
                {
                   $riserror = $rwithdraw['TransactionInfo']['withdrawmethodResult']['Message'];
                   $rwamount = $rwithdraw['TransactionInfo']['withdrawmethodResult']['Amount'];
                  // $rwamountneg = $rwithdraw['TransactionInfo']['withdrawmethodResult']['Amount'] * -1;
                   $rtransactionID = $rwithdraw['TransactionInfo']['withdrawmethodResult']['TransactionId'];

                   //check if there was no error on withdrawal
                   if($riserror == "Withdrawal Success")
                   {
                       // Check if Amount < 0, Withdrawal still returns Withdrawal Success even if Previous Balance before Withdrawal is already zero
                       if ($rwamount < 0)
                       {
                           $vstatus = 1;
                           //$rtransactionID = ""; // GUID                          
                           $vdateEffective = $ohab->getDate();   
                           $rremarks = $riserror;
                            
                           // UPDATE MANUAL REDEMPTIONS TABLE BASED ON RETURN OF API CALL
                           $issucess = $ohab->updateManualRedemptionub($vstatus, $rwamount * -1, $rtransactionID, $vdateEffective, $rremarks, $lastmrid);
                           if($issucess > 0)
                           {
                                //get new balance after redemption
                                // Pass UB UserName and Password
                                $rbalance = array();
                                //$rbalance = $_CasinoAPIHandler->GetBalance($serviceUBResult['ServiceUserName'], $serviceUBResult['ServicePassword']);
                                
                                // Pass TB UserName and Password
                                $rbalance = $_CasinoAPIHandler->GetBalance($login, $servicePwdResult['ServicePassword']);

                                if($rbalance['IsSucceed'] == true)
                                {
                                    if(isset($rbalance['BalanceInfo']['Balance']))
                                    {
                                        $habbalance = $rbalance['BalanceInfo']['Balance'];
                                        $balance = $habbalance;
                                    } 
                                }
                               // UPDATE TERMINALSESSIONS
                               $ohab->updateTerminalSessions($balance, $vterminalID);
                               $balance = number_format($balance,2, '.', ',');
                               
                               //insert into audit trail
                               $rwamountnonneg = $rwamount * -1;
                               $rwamountnonneg = number_format($rwamountnonneg,2, '.', ','); 
                               $vtransdetails = "Transaction ID: ".$rtransactionID.", Amount: ". $rwamountnonneg;
                               $vauditfuncID = 7;
                               $vtransactionDate = $ohab->getDate();   
                               $ohab->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                               $msg = "Redeemed: ".$rwamountnonneg."; Remaining Balance: ".$balance;
                            }
                            else
                            {
                                // UPDATE MANUAL REDEMPTIONS TABLE BASED ON RETURN OF UPDATE STATEMENT
                                $status = 2;
                                $ohab->updateManualRedemptionFailedub($status, $lastmrid);
                                $msg = "Manual Redemption: Error on updating  manual redemption record.";
                            }
                        }
                        else 
                        {
                            // UPDATE MANUAL REDEMPTIONS TABLE BASED ON RETURN OF API CALL
                            $status = 2;
                            $ohab->updateManualRedemptionFailedub($status, $lastmrid);
                            $msg = "Manual Redemption: Player Balance is already zero.";
                        }
                   }
                   else
                   {
                       $msg = $riserror;
                   }    
                }
                else
                {
                    $msg = $rwithdraw['ErrorMessage'];
                }
            }           
            else 
            {
                $msg = "Error: Failed to insert manual redemption record.";
            }

            $_SESSION['mess'] = $msg;
            unset($login);
            unset($serverId);
            unset($_SESSION['site']);
            unset($_SESSION['terminal']);
            unset($_SESSION['chkbalance']);
            unset($_SESSION['Withdraw']);
            unset($_SESSION['txtamount']);
            $ohab->close();
            if($cardnumber == '')
            {
               header("Location: ../manualredemption.php"); 
            }
            else
            {
               header("Location: ../manualredemption_ub.php"); 
            }
            //unset($_SESSION['txtcardnumber']);
            //unset($_SESSION['txtmid']);
        }
    }
    else
    {
        $errmsg = 'Casino server not available.';
        $response->error = $errmsg;
        echo json_encode($response);
    }
    $ohab->close();
}
else
{
   $msg = "Not Connected";    
   header("Location: login.php?mess=".$msg);
}
?>