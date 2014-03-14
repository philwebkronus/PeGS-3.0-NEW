<?php
/*
 * Author: Lea Tuazon
 * Date Created:
 * 
 * Modified By: Edson L. Perez
 * Purpose: For checking balance of MG Account, Redemption
 */

require_once __DIR__.'/../sys/core/init.php';
include __DIR__.'/../sys/class/CSManagement.class.php';
require_once __DIR__.'/../sys/class/helper/common.class.php';
include_once __DIR__.'/../sys/class/CasinoAPIHandler.class.php';

ini_set('display_errors',true);
ini_set('log_errors',true);

if (!isset($_SESSION))
{
    session_start();
}

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

$omg= new CSManagement($_DBConnectionString[0]);
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
  $ragentID = $_SESSION['agentid'];
  
  //check first if MG AGENT is not null
  if($ragentID <> null)
  {
      $rsession = $omg->getagentsession($ragentID);
      //validate if has session
      if(count($rsession) > 0)
      {
          foreach ($rsession as $results)
          {
            $sessionGUID = $results['ServiceAgentSessionID']; //get the agent session guid
          }

          mt_srand( (double) microtime() * 1000000 );
          $dtTransactionId = date("YmdHis") . mt_rand( 10000, 99999 );

          //config for MG
          $configuration = array( 'URI' => $_ServiceAPI[$serverId-1],
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'sessionGUID' => $sessionGUID,
                                'currency' => $_MicrogamingCurrency );

            $_CasinoAPIHandler = new CasinoAPIHandler( CasinoAPIHandler::MG, $configuration );

            if ( (bool)$_CasinoAPIHandler->IsAPIServerOK() )
            {
                if(isset($_SESSION['chkbalance']) == 'CheckBalance')
                {
                  //GetBalance
                  $rbalance = array();
                  $rbalance = $_CasinoAPIHandler->GetBalance( $login );
                  if ($rbalance['IsSucceed'] == true)
                  {
                     $mgbalance = $rbalance['BalanceInfo']['Balance'];
                     $response->Balance = number_format($mgbalance, 2, '.', ',');
                     echo json_encode($response);
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
                    $rwithdraw = array();
                    //Withdraw
                    $rwithdraw = $_CasinoAPIHandler->Withdraw($login, $ramount, $tracking1 = '', $tracking2 = '', $tracking3 = '', $tracking4 = '');

                    //check first if the API responded
                    if($rwithdraw['IsSucceed'] == true )
                    {
                        //fetch the information when calling the MG Withdraw Method
                        foreach($rwithdraw as $results)
                        {
                            $riswithdraw = $results['WithdrawalResult']['IsSucceed']; 
                            $rwamount = $results['WithdrawalResult']['TransactionAmount'];
                            $rtransactionID = $results['WithdrawalResult']['TransactionId'];
                            $rerrorcode = $results['WithdrawalResult']['ErrorCode'];
                            if($rerrorcode <> 0)
                            {
                                $rerrormsg = $results['WithdrawalResult']['ErrorMessage'];
                            }
                        }

                        $vsiteID = $_SESSION['site'];
                        $vterminalID = $_SESSION['terminal'];
                        $vreportedAmt = $ramount;
                        $vactualAmt = $rwamount;
                        $vtransactionDate = $vdate;
                        $vreqByAID = $aid;
                        $vprocByAID = $aid;
                        $vremarks = "Transaction Approved";
                        $vdateEffective = $vdate;
                        $vstatus = 1;
                        $vtransactionID = $rtransactionID;

                        //check if withdrawal result was successfull and if there was no error
                        if($riswithdraw == true && $rerrorcode == 0)
                        {
                            $withdrawRTG = $omg->insertmanualredemption($vsiteID, $vterminalID, $vreportedAmt, $vactualAmt, 
                                $vtransactionDate, $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, $vstatus, $vtransactionID);
                            //check if successfully inserted on DB
                            if($withdrawRTG > 0)
                            {
                               //insert into audit trail
                               $vtransdetails = "transaction id ".$vtransactionID.",amount ".$vreportedAmt;
                               $vauditfuncID = 7;
                               $omg->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                               $msg = "Successfully redeemed";
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
                    header("Location: ../manualredemption.php");
                }
            }
            else
            {
                $errmsg = 'Casino server not available.';
                $response->error = $errmsg;
                echo json_encode($response);
            }
      }
      else
      {
          echo "No MG Session Found";
      }
  }
  else
  {
      echo "No MG Agent Found";
  }
  
  $omg->close();
}
else{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
