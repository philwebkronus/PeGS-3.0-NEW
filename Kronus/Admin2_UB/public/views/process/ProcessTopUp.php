<?php

/* Created by: Lea Tuazon
 * Date Created : June 8, 2011
 * Modified By: Edson L. Perez
 */

include __DIR__."/../sys/class/TopUp.class.php";
include __DIR__."/../sys/class/LoyaltyUBWrapper.class.php";
require __DIR__.'/../sys/core/init.php';
include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';

function removeComma($money) {
    return str_replace(',', '', $money);
}

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$otopup= new TopUp($_DBConnectionString[0]);
$loyalty= new LoyaltyUBWrapper();
$CasinoGamingCAPI = new CasinoGamingCAPI();
$connected = $otopup->open();
$nopage = 0;

if($connected)
{

/***************  SESSION CHECKING **************/
   $isexist=$otopup->checksession($aid );
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $otopup->close();
      if($otopup->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   }
   $isexistsession =$otopup->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $otopup->close();
      if($otopup->isAjaxRequest())
       {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
       }
       header("Location: login.php?mess=".$msg);
   }
/*************** END SESSION CHECKING ***********/
   
   //checks if account was locked 
   $islocked = $otopup->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          if($otopup->isAjaxRequest())
          {
              header('HTTP/1.1 401 Unauthorized');
              echo "Session Expired";
              exit;
          }
          $otopup->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $otopup->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $otopup->getDate();
   
   //pagination starts here
   if(isset($_POST['paginate']))
   {     
            $page = $_POST['page']; // get the requested page
            $limit = $_POST['rows']; // get how many rows we want to have into the grid
            $rsiteID = $_POST['cmbsite'];
            $resultcount = array();
            $resultcount = $otopup->countrevdeposits($rsiteID);
            $count = $resultcount['count'];
            //this is for computing the limit
            if($count > 0 ) {
              $total_pages = ceil($count/$limit);
            } else {
              $total_pages = 0;
            }
            if ($page > $total_pages)
            {
              $page = $total_pages;
            }

            $start = $limit * $page - $limit;
            $limit = (int)$limit;
            
            //this is for proper rendering of results, if count is 0 $result is also must be 0
            if($count > 0)
            {
               $result = $otopup->viewreversalpage($rsiteID, $start, $limit);
            }
            else{
               $result = 0; 
            }
            if($result > 0)
            {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               foreach($result as $vview) {
                    $rremitID = $vview['SiteRemittanceID'];
                    if($vview['Status'] == 1)
                    {
                        $vstatus = "Invalid";
                    }
                    elseif($vview['Status'] == 2){
                        $vstatus = "Pending";
                    }
                    else
                    {
                        $vstatus = "Valid";
                    }
                    $responce->rows[$i]['id']= $rremitID;
                    $responce->rows[$i]['cell']=array($vview['RemittanceName'],$vview['BankCode'], $vview['Branch'],number_format($vview['Amount'],2), $vview['BankTransactionID'], $vview['BankTransactionDate'], $vview['ChequeNumber'], $vview['Particulars'], $vview['SiteID'], $vview['SiteName'], $vstatus, "<input type=\"button\" value=\"Verified\" onclick=\"window.location.href='process/ProcessTopUp.php?remitid=$rremitID'+'&remittance='+'UpdateReversal';\"/>");
                    $i++;
               }
           }
           else
           {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               $msg = "Topup Management: No returned result";
               $responce->msg = $msg;
           }
           echo json_encode($responce);
           unset($result);
           $otopup->close();
           exit;
   }
   
   if(isset($_POST['paginate2']))
   {     
            $page = $_POST['page']; // get the requested page
            $limit = $_POST['rows']; // get how many rows we want to have into the grid
            $rsiteID = $_POST['cmbsite'];
            $resultcount = array();
            $resultcount = $otopup->countrevdeposits2($rsiteID);
            $count = $resultcount['count'];
            //this is for computing the limit
            if($count > 0 ) {
              $total_pages = ceil($count/$limit);
            } else {
              $total_pages = 0;
            }
            if ($page > $total_pages)
            {
              $page = $total_pages;
            }

            $start = $limit * $page - $limit;
            $limit = (int)$limit;
            
            //this is for proper rendering of results, if count is 0 $result is also must be 0
            if($count > 0)
            {
               $result = $otopup->viewreversalpage2($rsiteID, $start, $limit);
            }
            else{
               $result = 0; 
            }
            if($result > 0)
            {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               foreach($result as $vview) {
                    $rremitID = $vview['SiteRemittanceID'];
                    if($vview['Status'] == 3)
                    {
                        $vstatus = "Verified";
                    }
                    
                    $responce->rows[$i]['id']= $rremitID;
                    $responce->rows[$i]['cell']=array($vview['SiteRemittanceID'],$vview['RemittanceName'],
                        $vview['BankCode'], $vview['Branch'],number_format($vview['Amount'],2), $vview['BankTransactionID'], 
                        $vview['BankTransactionDate'], $vview['ChequeNumber'], $vview['Particulars'], $vview['SiteID'], 
                        $vview['SiteName'], $vstatus, "<input type=\"button\" value=\"Valid\" 
                        onclick=\"window.location.href='process/ProcessTopUp.php?remitid2=$rremitID'+'&remitstat=0'+'&remittance2='+'UpdateVerifiedRemit';\"/><input type=\"button\" value=\"Invalid\" onclick=\"window.location.href='process/ProcessTopUp.php?remitid2=$rremitID'+'&remitstat=1'+'&remittance2='+'UpdateVerifiedRemit';\"/>");
                    $i++;
               }
           }
           else
           {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               $msg = "Topup Management: No returned result";
               $responce->msg = $msg;
           }
           echo json_encode($responce);
           unset($result);
           $otopup->close();
           exit;
   }  
   if(isset($_POST['page']))
    {
       $vpage = $_POST['page'];
       switch($vpage)
       {
           case "GetAllBCF":
               if(isset($_POST['cmbsite']) || isset($_POST['txtposacc']))
               {
                   //validate if site dropdown box was selected
                  if($_POST['cmbsite'] > 0)
                  {
                       $vSiteID = $_POST['cmbsite'];
                  }
                  else
                  {
                      //validate if pos account textfield have value
                      if(strlen($_POST['txtposacc']) > 0)
                      {
                         //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                         if(strlen($_POST['txtposacc']) == 10)
                         {
                             $vposaccno = $_POST['txtposacc'];
                         }
                         else
                         {
                             $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                         }
                         
                         $rsite = $otopup->getidbyposacc($vposaccno);
                         //check if pos account is valid
                         if($rsite)
                         {    
                            $vSiteID = $rsite['SiteID']; 
                         }
                         else
                         {    
                            echo "Invalid POS Account Number";  
                            $otopup->close();
                            exit;
                         }
                      }
                  }
                  
                  $rBCF = $otopup->getallbcf($vSiteID);
                  if(count($rBCF) > 0)
                  {
                      echo json_encode($rBCF);
                  }
                  else
                  {
                      echo "No BCF Found for this site/pegs";
                  }
                  $otopup->close();
                  exit;
               }
           break;
           
           //Get Membership Card user information    
           case "GetLoyaltyCard":

                $cardnumber = $_POST['txtcardnumber'];
                if(strlen($cardnumber) > 0) {
                    $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);

                    $obj_result = json_decode($loyaltyResult);

                    $statuscode = $obj_result->CardInfo->StatusCode;
                    
                    if(!is_null($statuscode) ||$statuscode == '')
                    {
                            if($statuscode == 1 || $statuscode == 5)
                            {
                               $casinoarray_count = count($obj_result->CardInfo->CasinoArray);

                               if($casinoarray_count != 0)
                               {
                                   for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {   
                                       $casinoinfo = array(
                                           array(
                                                 'UserName'  => $obj_result->CardInfo->MemberName,
                                                 'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                                 'Email'  => $obj_result->CardInfo->Email,
                                                 'Birthdate' => $obj_result->CardInfo->Birthdate,
                                                 'Casino' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID,
                                                 'CardNumber' => $obj_result->CardInfo->CardNumber,
                                             ),
                                       );

                                       $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                                       $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                                       echo json_encode($casinoinfo);
                                   }
                              }
                              else
                              {
                               $services = "User Based Redemption: Casino is empty";
                               echo "$services";
                              }
                           }
                           else
                           {  
                               //check membership card status
                               $statusmsg = $otopup->membershipcardStatus($statuscode);
                               $services = "User Based Redemption: ".$statusmsg;
                              echo "$services";

                           }                        
                        
                    }
                    else
                    {
                        $statuscode = 100;
                        //check membership card status
                           $statusmsg = $otopup->membershipcardStatus($statuscode);
                           $services = "User Based Redemption: ".$statusmsg;
                          echo "$services";
                    }
                    
                }
                else {
                    echo "User Based Redemption: Invalid input detected.";
                }

                $otopup->close();
                exit;
           break;
              
           //User Based Redemption using Loyalty Card
           case "Withdraw":
                  
              $login = $_POST['terminalcode'];
              $provider = $_POST['txtservices'];
              $vterminalID = $_POST['cmbterminal'];
              $ubserviceID = $_POST['txtserviceid'];
              $ubterminalID = $_POST['txtterminalid'];
              $vserviceBalance = $_POST['txtamount2'];
              $ticketub = $_POST['txtticketub'];
              $remarksub = $_POST['txtremarksub'];
              $loyaltycardnumber = $_POST['txtcardnumber'];
              $mid = $_POST['txtmid'];
              $usermode = $_POST['txtusermode'];
              
                if(isset($usermode)){
                    $login = $_SESSION['ServiceUserName'];
                    $transid = $otopup->getMaxTransreqlogid($loyaltycardnumber, $ubserviceID);
                    $sitester = $otopup->getSiteTer($transid);
                    foreach ($sitester as $row) {
                        $siteID = $row['SiteID'];
                        $ubterminalID = $row['TerminalID'];
                    }
                  
                }
                else {
                    $terminalcode = $otopup->getTCodeSiteID($ubterminalID);
                    foreach ($terminalcode as $value) {
                        $login = $value['TerminalCode'];
                        $siteID = $value['SiteID'];
                    }
                }
                            
              $server = $otopup->getCasinoName($ubserviceID);
              foreach ($server as $value2) {
                  $servername = $value2['ServiceName'];
                  $stat = $value2['Status'];
                  $usermode = $value2['UserMode'];
              }
              
              $ramount = ereg_replace(",", "", $vserviceBalance); //format number replace (,)
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
              
              $vsiteID = $siteID;
              $vterminalID = $ubterminalID;
              $vreportedAmt = $ramount;
              $vactualAmt = 0;
              $vtransactionDate = $otopup->getDate();
              $vreqByAID = $aid;
              $vprocByAID = $aid;
              //$vremarks = $rremarks;
              $vdateEffective = $vdate;
              $vstatus = 0;
              $vtransactionID = 0;
              $vremarks = $remarksub;
              $vticket = $ticketub;
              $cmbServerID = $ubserviceID; #Added on July 2, 2012
                                    
              $vtransStatus = '';
              $transsummaryid = $otopup->getLastSummaryID($vterminalID);
              $transsummaryid = $transsummaryid['summaryID'];
              
              $trans_req_log_last_id = $otopup->getMaxTransreqlogid($loyaltycardnumber, $ubserviceID);
              
              $lastmrid = $otopup->insertmanualredemptionub($vsiteID, $vterminalID,
                                $vreportedAmt, $vactualAmt, $vtransactionDate, 
                                $vreqByAID, $vprocByAID, $vremarks, $vdateEffective, 
                                $vstatus, $vtransactionID, $transsummaryid,$vticket,$cmbServerID, 
                        $vtransStatus, $loyaltycardnumber, $mid, $usermode);
              
              if($lastmrid > 0)
              {
                switch (true){
                    case strstr($servername, "RTG"): //if provider is PT, then
                        $url = $_ServiceAPI[$ubserviceID-1];
                        $capiusername = $_CAPIUsername;
                        $capipassword = $_CAPIPassword;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = '';
                        $tracking1 = $trans_req_log_last_id;
                        $tracking2 = "MR"."$lastmrid";
                        $tracking3 = $vterminalID;
                        $withdraw = array();
                        //withdraw rtg casino
                        $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, $capipassword, 
                                $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3, $tracking4 = '', $methodname = '');
                        break;
                    case strstr($servername, "MG"): //if provider is MG, then
                        $vterminalID = $otopup->viewTerminalID($login);
                        $servicePwdResult = $otopup->getterminalcredentials($vterminalID['TerminalID'], $ubserviceID);
                        $methodname = $_MicrogamingMethod;
                        $originID = 2; //manual redemption origin ID

                        $manualredemptionID = $otopup->insertserviceTransRef($ubserviceID, $originID);
                        if(!$manualredemptionID){
                            $msg = "Manual Redemption: Error on inserting servicetransactionref";
                        }
                        else
                        {
                            $transactionID = $manualredemptionID;
                            $eventID = $_CAPIEventID;

                            $_MGCredentials = $_ServiceAPI[$ubserviceID-1];
                            list($mgurl, $mgserverID) =  $_MGCredentials;
                            $url = $mgurl;
                            $capiusername = $_CAPIUsername;
                            $capipassword = $_CAPIPassword;
                            $capiplayername = $_CAPIPlayerName;
                            $capiserverID = $mgserverID;
                            $withdraw = array();  
                            //withdraw mg casino
                            $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, 
                                    $capiusername, $capipassword, $capiplayername, $capiserverID, $ramount, $servicePwdResult['ServicePassword'], $transactionID, $eventID, $transactionID, $methodname);

                        }   
                        break;
                    case strstr($servername, "PT"): //if provider is PT, then
                        $originID = 2;
                        //insert service transaction reference pass to casino 
                        $manualredemptionID = "MR"."$lastmrid";
                        
                          $tracking2 = $manualredemptionID; 
                          $tracking1 = $trans_req_log_last_id;
                          $tracking3 = $vterminalID;
                          $vterminalID = '';
                          $vsiteID = '';
                          $vterminalID = $ubterminalID;

                          //Get PT Terminal Password
                            if(isset($usermode)){
                                $tracking1 = $_SESSION['ServicePassword'];
                            }
                            else {
                                $servicePwdResult = $otopup->getterminalcredentials($vterminalID, $ubserviceID);
                                $tracking1 = $servicePwdResult['ServicePassword'];
                            }

                          $url = $_ServiceAPI[$ubserviceID-1];
                          $capiusername = $_ptcasinoname;
                          $capipassword = $_ptsecretkey;
                          $capiplayername = $_CAPIPlayerName;
                          $capiserverID = '';
                          $withdraw = array();
                          //withdraw pt casino
                          $withdraw = $CasinoGamingCAPI->Withdraw($servername, $ubserviceID, $url, $login, $capiusername, 
                                  $capipassword, $capiplayername, $capiserverID, $ramount, $tracking1, $tracking2, $tracking3='', $tracking4='', $methodname='');
                        
                        break;
                    default :
                        echo "Error: Invalid Casino Provider";
                        break;
                        }
                        
                        
                         switch (true){
                            case strstr($servername, "RTG"): //if provider is MG, then
                                //check if redemption was successfull, and insert information on manualredemptions and audittrail
                                if($withdraw['IsSucceed'] == true )
                                {
                                    //fetch the information when calling the MG Withdraw Method
                                    foreach($withdraw as $results)
                                    {
                                        $riserror = $results['WithdrawGenericResult']['errorMsg'];
                                        $reffdate = $results['WithdrawGenericResult']['effDate']; //uses ISO8601 date format
                                        $rwamount = $results['WithdrawGenericResult']['amount'];
                                        $rremarks = $results['WithdrawGenericResult']['transactionStatus'];
                                        $rtransactionID = $results['WithdrawGenericResult']['transactionID'];
                                    }

                                    $fmteffdate = str_replace("T", " ", $reffdate);
                                    $vsiteID = $siteID;
                                    $vterminalID = $ubterminalID;
                                    $vreportedAmt = $ramount;
                                    $vactualAmt = $rwamount;
                                    $vtransactionDate = $otopup->getDate();
                                    $vreqByAID = $aid;
                                    $vprocByAID = $aid;
                                    //$vremarks = $rremarks;
                                    $vdateEffective = $fmteffdate;
                                    $vstatus = 1;
                                    $vtransactionID = $rtransactionID;
                                    $vremarks = $remarksub;
                                    $vticket = $ticketub;
                                    $cmbServerID = $ubserviceID; #Added on July 2, 2012

                                    //check if there was no error on withdrawal
                                    if($riserror == "OK")
                                    {
                                        $vtransStatus = $rremarks;
                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                        $transsummaryid = $transsummaryid['summaryID'];
                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, 
                                                $vtransactionID, $fmteffdate, $vtransStatus, $lastmrid);

                                        if($issucess > 0)
                                        {
                                           //insert into audit trail
                                           $vtransdetails = "transaction id ".$vtransactionID.",amount ".$vreportedAmt;
                                           $vauditfuncID = 7;
                                           $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
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
                                    $msg = $withdraw['ErrorMessage']; //error message when initially calling the RTG API
                                }
                                break;
                            case strstr($servername, "MG"): //if provider is MG, then
                                //MG withdraw checking
                                //check first if the API responded
                                if($withdraw['IsSucceed'] == true )
                                {

                                    //fetch the information when calling the MG Withdraw Method
                                    foreach($withdraw as $results)
                                    {
                                        $riswithdraw = $withdraw['IsSucceed']; 
                                        $rwamount = abs($withdraw['TransactionInfo']['TransactionAmount']/100);
                                        $rtransactionID = $withdraw['TransactionInfo']['TransactionId'];
                                        $rerrorcode = $withdraw['ErrorCode'];
                                        if($rerrorcode <> 0)
                                        {
                                            $rerrormsg = $withdraw['ErrorMessage'];
                                        }
                                    }

                                    $vsiteID = $siteID;
                                    $vterminalID = $ubterminalID;
                                    $vreportedAmt = $ramount;
                                    $vactualAmt = $rwamount;
                                    $vtransactionDate = $vdate;
                                    $vreqByAID = $aid;
                                    $vprocByAID = $aid;
                                    $vremarks = $remarksub;

                                    $vticket = $ticketub;
                                    $vdateEffective = $vdate;
                                    $vstatus = 1;
                                    $vtransactionID = $rtransactionID;
                                    $cmbServerID = $ubserviceID; #Added on July 2, 2012

                                    //check if withdrawal result was successfull and if there was no error
                                    if($riswithdraw == true && $rerrorcode == 0)
                                    {
                                        $vtransStatus = "Transaction Approved";
                                        $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                        $transsummaryid = $transsummaryid['summaryID'];
                                        $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, 
                                                $vtransactionID, $vtransactionDate, $riswithdraw, $lastmrid);

                                        //check if successfully inserted on DB
                                        if($issucess > 0)
                                        {
                                           //insert into audit trail
                                           $vtransdetails = "transaction id ".$vtransactionID.",amount ".$vreportedAmt;
                                           $vauditfuncID = 7;
                                           $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                                           $msg = "Redeemed: ".$ramount."; Remaining Balance: ".$balance;
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
                                    $msg = $withdraw['ErrorMessage']; //error message when initially calling the MG API
                                }
                                break;
                            case strstr($servername, "PT"): //if provider is PT, then
                                //check if redemption was successfull and insert information on manualredemptions and audittrail
                                if($withdraw['IsSucceed'] == true)
                                {
                                    foreach ($withdraw as $results)
                                    {
                                          $riswithdraw= $withdraw['IsSucceed'];
                                          $rwamount = abs($withdraw['TransactionInfo']['PT']['balance']);
                                          $rtransactionID = $withdraw['TransactionInfo']['PT']['tranid'];
                                          $rerrorcode = $withdraw['ErrorCode'];
                                          if($rerrorcode <> 0)
                                          {
                                              $rerrormsg = $withdraw['ErrorMessage'];
                                          }
                                    }

                                    $vsiteID = $siteID;
                                    $vreportedAmt = $ramount;
                                    $vactualAmt = $rwamount;
                                    $vtransactionDate = $vdate;
                                    $vreqByAID = $aid;
                                    $vprocByAID = $aid;
                                    $vremarks = $remarksub;
                                    $vticket = $ticketub;
                                    $vdateEffective = $vdate;
                                    $vstatus = 1;
                                    $vtransactionID = $rtransactionID;
                                    $cmbServerID = $ubserviceID; #Added on July 2, 2012

                                    //check if withdrawal result was successfull and if there was no error
                                    if($riswithdraw == true && $rerrorcode == 0)
                                    {   
                                          $vtransStatus = "approved";
                                          $transsummaryid = $otopup->getLastSummaryID($vterminalID);
                                          $transsummaryid = $transsummaryid['summaryID'];

                                          $issucess = $otopup->updateManualRedemptionub($vstatus, $vactualAmt, 
                                                $vtransactionID, $vtransactionDate, $vtransStatus, $lastmrid);

                                          //check if successfully inserted on DB
                                          if ($issucess > 0) 
                                          {
                                             //insert into audit trail
                                             $vtransdetails = "transaction id " . $vtransactionID . ",amount " . $vreportedAmt;
                                             $vauditfuncID = 7;
                                             $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vtransactionDate, $vipaddress, $vauditfuncID);
                                             $msg = "Redeemed: " . $ramount . "; Remaining Balance: " . $balance;
                                          } 
                                          else 
                                          {
                                            $msg = "Manual Redemption: Error on inserting manual redemption";
                                          }
                                    } 
                                    else 
                                    {
                                        $msg = $rerrormsg; //error message when calling the withdrawal result.
                                    }

                                } 
                                else 
                                {
                                    $msg = $withdraw['ErrorMessage']; //error message when calling the withdrawal result
                                }
                                break;
                            default :
                                echo "Error: Invalid Casino Provider";
                                break;
                     }  
              }    
              else
              {
                  $msg = "Error: Failed to insert in Manual Redemptions Table";
              }    
              
               
                        
                echo json_encode($msg);
                $otopup->close();
           break;
              
          //Get Casino Services provided by membership card    
           case "GetCasino":
                $casino = $_SESSION['CasinoArray'];
                $rmid = $_SESSION['MID'];   
                $casinoarray_count = count($casino);

                $casinos = array();
                if($casinoarray_count != 0)
                    for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {
                        $casinos[$ctr] = array(
                                            array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                                  'ServicePassword' => $casino[$ctr]->ServicePassword,
                                                  'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                                  'ServiceID' => $casino[$ctr]->ServiceID,
                                                  'UserMode' => $casino[$ctr]->UserMode,
                                                  'isVIP' => $casino[$ctr]->isVIP,
                                                  'Status' => $casino[$ctr]->Status )
                                        );

                        $service = array();

                      //loop through array output to get casino array from membership card
                      foreach ($casinos[$ctr] as $value) {
                            $rserviceuname = $value['ServiceUsername'];
                            $rservicepassword = $value['ServicePassword'];
                            $rserviceid = $value['ServiceID'];
                            $rusermode = $value['UserMode'];
                            $risvip = $value['isVIP'];
                            $hashedpassword = $value['HashedServicePassword'];
                            $rstatus = $value['Status'];

                            $servicename = $otopup->getCasinoName($rserviceid);

                          //loop htrough services to get if has pedning balance
                          foreach ($servicename as $service2) {
                                $serviceName = $service2['ServiceName'];
                                $serviceStatus = $service2['Status'];
                                
                                 switch (true){
                                case strstr($serviceName, "RTG"): //if provider is PT, then
                                   //call get balance method of RTG
                                    $url = $_ServiceAPI[$rserviceid-1];
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = '';
                                    $balance = $CasinoGamingCAPI->getBalance($serviceName, $rserviceid, $url, 
                                                        $rserviceuname, $capiusername, $capipassword, 
                                                        $capiplayername, $capiserverID); 
                                    break;
                                case strstr($serviceName, "MG"): //if provider is MG, then
                                     //call getbalance method of MG
                                    $_MGCredentials = $_ServiceAPI[$rserviceid-1];
                                    list($mgurl, $mgserverID) =  $_MGCredentials;
                                    $url = $mgurl;
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = $mgserverID;
                                    $balance = $CasinoGamingCAPI->getBalance($serviceName, $rserviceid,
                                                    $url, $rserviceuname, $capiusername, $capipassword, 
                                                    $capiplayername, $capiserverID); 
                                    break;
                                case strstr($serviceName, "PT"): //if provider is PT, then
                                    //call getbalance method of PT
                                    $url = $_ServiceAPI[$rserviceid-1];
                                    $capiusername = $_ptcasinoname;
                                    $capipassword = $_ptsecretkey;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = '';
                                    $balance = $CasinoGamingCAPI->getBalance($serviceName, 
                                                    $rserviceid, $url, $rserviceuname, $capiusername, 
                                                    $capipassword, $capiplayername, $capiserverID); 
                                    break;
                                default :
                                    echo "Error: Invalid Casino Provider";
                                    break;
                                }

                                        $terminalid = $otopup->viewTerminalID($rserviceuname);
                                        switch ($serviceStatus){
                                            case 1: $serviceStatus = "Active";
                                                break;
                                            case 0: $serviceStatus = "InActive";
                                        }  

                                       $casino2 = array(
                                                    "UserName"  => "$rserviceuname",
                                                    "Password"  => $rservicepassword,
                                                    "HashedPassword" => "$hashedpassword",    
                                                    "ServiceName"  => $serviceName,
                                                    "ServiceID"  => $rserviceid,
                                                    "TerminalID"  => $terminalid['TerminalID'],    
                                                    "UserMode" => "$rusermode",
                                                    "IsVIP" => "$risvip",
                                                    "MemberID" => "$rmid",
                                                    "Status" => "$rstatus",
                                                    "Balance" => "$balance",
                                      );
                                      $_SESSION['ServicePassword'] = $rservicepassword;
                                      $_SESSION['ServiceUserName'] = $rserviceuname;
                                      
                             array_push($service, $casino2);  
                          }
                      }

                    }  
              echo json_encode($service);


              unset($casino, $_SESSION['CasinoArray']);
              unset($casino2, $_SESSION['MID']);
              $otopup->close();
           break;
          
              
           case "PostManualTopUp":
                //check if all variables are set; all are required fields
               if(isset ($_POST['txtamount']) && isset ($_POST['txtminbal']) && isset ($_POST['txtmaxbal']) && isset ($_POST['optpick']))
               {
                    //validate if site dropdown box was selected
                    if($_POST['cmbsite'] > 0)
                    {
                       $vSiteID = $_POST['cmbsite'];
                    }
                    else
                    {
                       //validate if pos account textfield have value
                       if(strlen($_POST['txtposacc']) > 0)
                       {
                          //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                          if(strlen($_POST['txtposacc']) == 10)
                          {
                              $vposaccno = $_POST['txtposacc'];
                          }
                          else
                          {
                              $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                          }
                          
                          $rsite = $otopup->getidbyposacc($vposaccno);
                          if($rsite)
                          {    
                            $vSiteID = $rsite['SiteID']; 
                          }
                          else
                          {    
                            $msg = "Invalid POS Account Number";
                            $otopup->close();
                            $_SESSION['mess'] = $msg;
                            header("Location: ../topupmanual.php");
                          }
                       }
                       else
                       {
                           $msg = "Post Manual Top-up: Invalid fields.";
                       }
                    }
                    
                    $vAmount = removeComma($_POST['txtamount']); # inputted amount
                    $vBalance =removeComma($_POST['txtamount']);                    
                    $vMinBalance = removeComma($_POST['txtminbal']);
                    $vMaxBalance = removeComma($_POST['txtmaxbal']);
                    if(((float)$vAmount > 0.00) && ((float)$vMinBalance > 0.00) && ((float)$vMaxBalance > 0.00))
                    {
                        $vLastTransactionDate = null ; # always null
                        $vLastTransactionDescription = null ; #always null
                        $vTopUpType = 0;
                        $vPickUpTag = $_POST['optpick'];
                        $vPrevBalance = 0;
                        $vNewBalance = removeComma($_POST['txtamount']); # inputted amount
                        //session id
                        $vCreatedByAID = $aid;

                        $vDateCreated= $vdate;
                        $vStartBalance = 0; # result based on select bcf where siteid = 12
                        $vEndBalance = $vStartBalance + $vNewBalance; #start balance + inputted amount
                        $vToupAmount = removeComma($_POST['txtamount']); # inputted amount;
                        $vTotalTopupAmount = removeComma($_POST['txtamount']);  # inputted amount;
                        $vTopupCount =0; # if TopUpType is fixed then TopupCount = 1 else TopupCount = 0
                        $vRemarks = "Manual TopUp";
                        $vAutoTopUpEnabled = 0; // auto top up trigger
                        $vAutoTopUpAmount = 0; //default to 0; for auto topup
                        $vStatus= 1;//0 - Pending; 1 - Successful; 2 - Failed
                        $vTopupTransactionType = 0; //0 - Manual Topup; 1 - AutoTopup
                        $postedmanualtopup = $otopup->insertsitebalance($vSiteID,$vBalance,$vMinBalance,$vMaxBalance,$vLastTransactionDate,
                                $vLastTransactionDescription,$vTopUpType,$vPickUpTag,$vAmount,$vPrevBalance,$vNewBalance,$vCreatedByAID,
                                $vDateCreated,$vStartBalance,$vEndBalance, $vToupAmount,$vTotalTopupAmount,$vTopupCount,$vRemarks,
                                $vAutoTopUpEnabled,$vAutoTopUpAmount,$vTopupTransactionType,$vStatus);
                        if($postedmanualtopup == 0)
                        {
                           $msg = "Post Manual TopUp: Error in inserting record in sitebalance";
                        }
                        else
                        {
                           $msg = "Post Manual TopUp: Record inserted in sitebalance";
                           //insert into audit trail
                           $vtransdetails = "SiteCode ".$_POST['txtsitecode']."Amount ".$_POST['txtamount'];
                           $vauditfuncID = 17;
                           $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        }
                    }
                    else
                    {
                        $msg = "Post Manual Top-up: Zero input is not allowed";
                    }
                }
                else
                {
                    $msg = "Post Manual Top-up: Invalid fields.";
                }
                $nopage = 1;
                //redirect to site view page with corresponding popup message
                $otopup->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../topupmanual.php");
            break;
            case "UpdateSiteParam":
                //get variables
                $vSiteID = $_POST['txtsite'];
                $vMinBalance = removeComma($_POST['txtminbal']);
                $vMaxBalance = removeComma($_POST['txtmaxbal']);
                if(((float)$vMinBalance > 0.00) && ((float)$vMaxBalance > 0.00))
                {
                    $vPickUpTag = $_POST['optpick'];
                    $vOptType = $_POST['opttype'];
                    $vCreatedByAID = $aid;
                    $vDateCreated = $vdate;   
                    $vupatedrow = $otopup->updatesiteparam($vSiteID,$vMinBalance,$vMaxBalance,$vOptType,$vPickUpTag );
                    if($vupatedrow > 0)
                    {
                        $msg ="Site Balance parameters successfully updated";
                        //insert into audit trail
                        $arrnewdetails = array($vMinBalance, $vMaxBalance, $vPickUpTag, $vOptType);
                        $newdetails = implode(",", $arrnewdetails);
                        $vtransdetails = "sitecode ".$_POST['txtsitecode']." ;old params ".$_POST['txtolddetails']." ;new params ".$newdetails;
                        $vauditfuncID = 18;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    }
                    else
                    {
                        $msg = "Update Site Balance Parameter: Record unchanged.";
                    }
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    unset($arrnewdetails);
                    header("Location: ../topupview.php");
                }
                else
                {
                    $msg = "Update Site Balance Parameter: Zero input is not allowed";
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    header("Location:  ../topupupdateparam.php");
                }
            break;
            case "UpdateSiteBalance":
                // get variables with value
                $vSiteID = $_POST['txtsite'];
                $vAmount = removeComma($_POST['txtamount']); # inputted amount
                $vPrevBalance = removeComma($_POST['txtprevbal']);
                $vMinBalance = removeComma($_POST['txtminbal']);
                $vMaxBalance = removeComma($_POST['txtmaxbal']);
                if(((float)$vAmount > 0.00) && ((float)$vMinBalance > 0.00) && ((float)$vMaxBalance > 0.00))
                {
                    $vPickUpTag = $_POST['optpick'];
                    $vOptType = $_POST['opttype'];
                    $vNewBalance = $vPrevBalance + $vAmount;
                    $vCreatedByAID = $aid;
                    $vDateCreated = $vdate;   
                    $vRemarks = "Manual TopUp";
                    $vTopUpCount = 0; //for manual topup
                    $vTopupTransactionType = 0; //for manual topup
                    $vStatus = 1; //for manual topup

                    $vupatedrow = $otopup->updatebalance($vAmount,$vSiteID,$vPrevBalance,$vNewBalance,
                            $vCreatedByAID,$vDateCreated,$vOptType, $vMinBalance,$vMaxBalance,$vPickUpTag ,
                            $vTopUpCount,$vStatus,$vRemarks,$vTopupTransactionType);
                    $nopage = 1;
                    if($vupatedrow  > 0)
                    {
                        $msg = "Update Site Balance: Balance successfully updated.";
                        $vsitecode = $otopup->getsitecode($vSiteID);
                        //insert into audit trail
                        $vtransdetails = "SiteCode ".$vsitecode['SiteCode']."Old bcf ".$_POST['txtprevbal']."New bcf ".$vNewBalance;
                        $vauditfuncID = 19;
                        $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    }
                    else
                    {
                        $msg = "Update Site Balance: Record unchanged";
                    }
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    header("Location: ../topupview.php");
                }
                else
                {
                    $msg = "Update Site Balance : Zero input is not allowed";
                    $otopup->close();
                    $_SESSION['mess'] = $msg;
                    header("Location: ../topupupdatebal.php");
                }
           break;
           case "ReversalofDeposits":
               //get variables
               $vSiteRemittanceID = $_POST['cmbsiteremit'];              
               $vupatedrow = $otopup->updatesiteremittancestatus($vSiteRemittanceID,$aid,$vdate);
               if($vupatedrow == 0)
               {
                  $msg = "Verify Deposits: Record unchanged";
               }
               else
               {
                  $msg =  "Verify Deposits: Success in updating status in site remittance";
                  //insert into audit trail
                  $vtransdetails = "TopUp Reversal:Verify Deposits, sitremittanceid = ".$vSiteRemittanceID." msg-".$msg;
                  $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
               }
               $nopage = 1;
               $otopup->close();
               $_SESSION['mess'] = $msg;
               header("Location: ../reversaldeposit.php");
           break;

          case "ReversalofDeposits2":
               //get variables
               $vSiteRemittanceID = $_POST['cmbsiteremit'];  
               $vstatus = $_POST['optstatus'];  
               $vupatedrow = $otopup->updateverifiedsiteremittance($vSiteRemittanceID,$vstatus,$aid,$vdate);               
               if($vupatedrow == 0)
               {
                  $msg = "Update Verified Deposits: Record unchanged";
               }
               else
               {
                  $msg =  "Update Verified Deposits: Success in updating status in site remittance";
                  //insert into audit trail
                  $vtransdetails = "Update Verified Deposits, sitremittanceid = ".$vSiteRemittanceID.' status ='.$vstatus." msg-".$msg;
                  $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
               }
               $nopage = 1;
               $otopup->close();
               $_SESSION['mess'] = $msg;
               header("Location: ../reversaldeposit2.php");
           break;

           case "ManualTopUpReversal":
             if(isset($_POST['txtusername']) && isset($_POST['txtpassword']) && isset($_POST['cmbsite']))
             {
                  //validate if site dropdown box was selected
                  if($_POST['cmbsite'] > 0)
                  {
                       $vSiteID = $_POST['cmbsite'];
                  }
                  else
                  {
                      //validate if pos account textfield have value
                      if(strlen($_POST['txtposacc']) > 0)
                      {
                         //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                         if(strlen($_POST['txtposacc']) == 10)
                         {
                             $vposaccno = $_POST['txtposacc'];
                         }
                         else
                         {
                             $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                         }

                         $rsite = $otopup->getidbyposacc($vposaccno);
                         //check if pos account is valid
                         if($rsite)
                         {    
                            $vSiteID = $rsite['SiteID']; 
                         }
                         else
                         {    
                            $msg = "Invalid POS Account Number";
                         } 
                      }
                  }
                  $vaccname = $_POST['txtusername'];
                  $vaccpass = $_POST['txtpassword'];
                  $rresult = $otopup->checkaccountdetails($vaccname, $vaccpass);
                  $isexist = $rresult['ctracc'];
                  if($isexist > 0)
                  {
                     $vTopUpType = 1;
                     $vCreatedByAID = $aid;
                     $vDateCreated = $vdate;
                     $vAmount = removeComma($_POST['txtamount']);
                     
                     $rBCF = $otopup->getallbcf($vSiteID);
                     
                     foreach($rBCF as $rresultbcf)
                     {
                        $vPrevBalance = $rresultbcf['Balance'];
                        $vMinBalance = $rresultbcf['MinBalance'];
                        $vMaxBalance = $rresultbcf['MaxBalance'];
                        $vTopUpType = $rresultbcf['TopUpType'];
                        $vPickUpTag = $rresultbcf['PickUpTag '];
                     }
                     if($vAmount > 0 && $vamount <= $vPrevBalance)
                     {
                         $vRemarks = "Manual TopUp";
                         $vTopUpCount = 0; //for manual topup
                         $vTopupTransactionType = 2; //for manual topup reversal
                         $vStatus = 1; //for manual topup
                         $vRemarks = 'Reversal of Manual TopUp';  
                         //compute new balance :
                         //$vNewBalance =  $vPrevBalance + $vAmount;
                         $vNewBalance =  $vPrevBalance - $vAmount;
                         $vupatedrow = $otopup->updatereversal($vAmount, $vSiteID, $vPrevBalance, $vNewBalance, $vTopUpType, $vCreatedByAID, $vDateCreated,
                                 $vTopUpType, $vMinBalance,$vMaxBalance,$vPickUpTag ,$vTopUpCount,$vStatus,$vRemarks,$vTopupTransactionType);
                         if($vupatedrow == 0)
                         {
                             $msg = "ManualTopUpReversal: Error in manual topup reversal";                 
                         }
                         else
                         {
                             $msg = "ManualTopUpReversal: Successful manual topup reversal";
                             //insert into audit trail
                             $vtransdetails = "Site code ".$_POST['txtsitecode']."old amt ".$vPrevBalance."new amt ".$vNewBalance;
                             $vauditfuncID = 20;
                             $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                         }
                         //insert into audit trail --> For authorizations
                          $vtransdetails = "ManualTopUpReversal: Authorized by: ".$vaccname;
                          $vauditfuncID = 50;
                          $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                     }
                     else
                     {
                         $msg = "ManualTopUpReversal: Invalid amount"; 
                     }
              
                  }
                  else 
                  {
                      $msg = "Account Details: Invalid Account";
                  }
              }
              else
              {
                  $msg = "Account Details: Invalid Fields";
              }  
             
             $nopage = 1;
             $_SESSION['mess'] = $msg;
             //redirect to site view page with corresponding popup message
             $otopup->close();
             header("Location: ../topupreversal.php");
          break;
          
          case "VerifiedDeposit":
                $vSiteRemittanceID = $_POST['cmbsiteremit'];
                $vupatedrow = $otopup->updatesiteremittancestatus($vSiteRemittanceID,$aid,$vdate);
                if($vupatedrow == 0)
                {
                  $msg =" Verification of Deposits: Record unchanged";
                }
                else
                {
                  $msg =  " Verification of Deposits: Success in updating status in site remittance";
                  //insert into audit trail
                  $vtransdetails = " Verification of Deposits:VerifiedDeposit, sitremittanceid = ".$vSiteRemittanceID." msg-".$msg;
                  $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
                }
                
                $nopage = 1;
                $otopup->close();
                $_SESSION['mess'] = $msg;
                header("Location: ../reversaldeposit.php");
          break;
          case "PostingOfDeposit":
              if(isset($_POST['ddlRemittanceType']) && isset($_POST['txtAmount']) 
                      && isset($_POST['txtChequeNo']) && isset ($_POST['txtParticulars']))
              {
                 //validate if site dropdown box was selected
                 if($_POST['cmbsitename'] > 0)
                 {
                   $vsiteID = $_POST['cmbsitename'];
                 }
                 else
                 {
                   //validate if pos account textfield have value
                   if(strlen($_POST['txtposacc']) > 0)
                   {
                      //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                      if(strlen($_POST['txtposacc']) == 10)
                      {
                          $vposaccno = $_POST['txtposacc'];
                      }
                      else
                      {
                          $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                      }

                      $rsite = $otopup->getidbyposacc($vposaccno);
                      //check if pos account is valid
                      if($rsite)
                      {
                          $vsiteID = $rsite['SiteID']; 
                      }
                      else
                      {
                          $msg = "Invalid POS Account Number";
                          $_SESSION['mess'] = $msg;
                          $otopup->close();
                          header("Location: ../cashierdeposit.php");
                      }
                      
                   }
                   else
                   {
                       $msg = "Posting of Deposits: Invalid fields.";
                   }
                 }
                 $vremittancetypeID = $_POST['ddlRemittanceType'];
                 $vamount = removeComma($_POST['txtAmount']);
                 $vcheckno = $_POST['txtChequeNo'];
                 $vparticulars = $_POST['txtParticulars'];
                 $vCreatedByAID = $aid;
                 $vDateCreated = $vdate;
                 $vStatus = 3; //set status to verified    
                 $vsitedate = $_POST['txtdate'];
                 //if remittance type is bank
                 if($vremittancetypeID == 1 || $vremittancetypeID == 3 || $vremittancetypeID == 4 || $vremittancetypeID == 6) 
                 { 
                   $vbankID = $_POST['ddlBank'];
                   $vbranch = $_POST['txtBranch'];    
                   $vbanktransID = $_POST['txtBankTransID'];
                   $vbanktransdate = $_POST['txtBankTransDate'];
                   $rresult = $otopup->insertdepositposting($vremittancetypeID, $vbankID, $vbranch, $vamount, $vbanktransID, $vbanktransdate, $vcheckno, $vCreatedByAID, $vparticulars, $vsiteID, $vStatus, $vDateCreated, $vsitedate);
                 } 
                 //else, remittance type is walk-in
                 else 
                 { 
                   $vbankID = null;
                   $vbranch = null;    
                   $vbanktransID = null;
                   $vbanktransdate = $_POST['txtBankTransDate'];
                   $rresult = $otopup->insertdepositposting($vremittancetypeID, $vbankID, $vbranch, $vamount, $vbanktransID, $vbanktransdate, $vcheckno, $vCreatedByAID, $vparticulars, $vsiteID, $vStatus, $vDateCreated, $vsitedate);
                 }
                 
                 if($rresult > 0)
                 {
                     $msg = "Posting of Deposits: Successfully created";
                     $vtransdetails = "Deposit id ".$vremittancetypeID."site code".$_POST['txtsitecode']."bank code".$_POST['txtbankcode']."for date ".$vsitedate."amount ".$vamount;
                     $vauditfuncID = 4;
                     $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                 }
                 else
                 {
                     $msg = "Posting of Deposits: Error on post";
                 }
                 
                 $_SESSION['mess'] = $msg;
                 $otopup->close();
                 header("Location: ../cashierdeposit.php");
              }
              else
              {
                 $msg = "Posting of Deposits: Invalid fields.";
                 $_SESSION['mess'] = $msg;
                 $otopup->close();
                 header("Location: ../cashierdeposit.php");
              }
          break;
          //populate combobox with bank namesm, (postingofdeposit)
          case "GetBankName":
              $rbanknames = array();
              $rbanknames = $otopup->getbanknames();
              if(count($rbanknames) > 0)
              {
                  echo json_encode($rbanknames);
              }
              else
              {
                  echo "No Results Found";
              }
              unset($rbanknames);
              $otopup->close();
              exit;
          break;
          //Get operator info (display on lightbox)
          case "GetOperator":
              //validate if site dropdown box was selected
              if($_POST['cmbsitename'] > 0)
              {
                   $vsiteID = $_POST['cmbsitename'];
              }
              else
              {
                   //validate if pos account textfield have value
                   if(strlen($_POST['txtposacc']) > 0)
                   {
                      //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                      if(strlen($_POST['txtposacc']) == 10)
                      {
                          $vposaccno = $_POST['txtposacc'];
                      }
                      else
                      {
                          $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                      }

                      $rsite = $otopup->getidbyposacc($vposaccno); //get site ID
                      //check if pos account is valid
                      if($rsite)
                      {
                          $vsiteID = $rsite['SiteID']; 
                      }
                      else
                      {
                          echo "Invalid POS Account Number";
                          $otopup->close();
                          exit;
                      }
                      
                   }
                   else
                   {
                       echo "Get Operator: Invalid fields.";
                       $otopup->close();
                       exit;
                   }
              }
              
              $rroperator = array();
              $rroperator = $otopup->getoperator($vsiteID);
              if(count($rroperator) > 0)
              {
                  echo json_encode($rroperator);
              }
              else
              {
                  echo "No operator found for this site";
              }
              unset($rroperator);
              $otopup->close();
              exit;
          break;
          case "InsertPegsConfirmation":
              if(isset ($_POST['txtwho']) && isset($_POST['txtamount']))
              {
                  $vsiteID = $_POST['txtsiteID'];
                  $vdatecredited = $_POST['txtdate'];
                  $vsiterep = $_POST['txtwho'];
                  $vamount = removeComma($_POST['txtamount']);
                  $vCreatedByAID = $aid;
                  $vDateCreated = $vdate;
                  $rconfirmationID = $otopup->insertconfirmation($vsiteID, $vdatecredited, $vsiterep, $vamount, $vCreatedByAID, $vDateCreated);
                  if($rconfirmationID > 0)
                  {
                      $msg = "PEGS Confirmation: Successfully confirmed";
                      $vtransdetails = "replenishment id ".$rconfirmationID.
                                       ",SiteCode ".$_POST['txtsitecode'].",for date ".$vdate.",amount ".$vamount;
                      $vauditfuncID = 22;
                      $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                  }
                  else
                  {
                      $msg = "PEGS Confirmation: Error on insert";
                  }
              }
              else
              {
                  $msg = "PeGs Confirmation: Invalid Fields";
              }
              $_SESSION['mess'] = $msg;
              $otopup->close();
              header("Location: ../pegsconfirmation.php");
          break;
          //
          case "ManualRedemption":
              $siteID = $_POST['cmbsite'];
              $login = $_POST['terminalcode'];
              $serverId = $_POST['cmbservices'];
              $provider = $_POST['txtservices'];
              $vterminalID = $_POST['cmbterminal'];
              $isredeem = 0;
              $vremarks = '';
              $vticket = '';
              
              if(isset($_POST['chkbalance']))
              {
                 $vbalance = $_POST['chkbalance'];
              }
              else
              {
                 $vbalance = null;
              }
              
              if(isset($_POST['Withdraw']))
              {
                 $vwithdraw = $_POST['Withdraw'];
              }
              else
              {
                 $vwithdraw = null;
              }
              
                            
              
              $vamount = 0;
              if(isset($_POST['txtamount']))
              {
                   $vamount = $_POST['txtamount'];
              }
              if(isset($_POST['txtremarks']))
              {
                   $vremarks = trim($_POST['txtremarks']);
              }
              if(isset($_POST['txtticket']))
              {
                   $vticket = trim($_POST['txtticket']);
              }
              
              //to check if the sname of provider matches on the posted data, and redirect to its
                //respective process
              $sRTG = preg_match('/RTG/', $provider);
              if($sRTG == 0)
              {
                 $sMG = preg_match('/MG/', $provider);
                 if($sMG == 0)
                 {
                     $sPT = preg_match('/PT/', $provider);
                     if($sPT == 0)
                     {
                         echo 'Invalid Casino.';
                     } else {
                            $_SESSION['site'] = $siteID;
                            $_SESSION['terminal'] = $vterminalID;
                            $_SESSION['login'] = $login;
                            $_SESSION['serverid'] = $serverId;
                            $_SESSION['chkbalance'] = $vbalance;
                            $_SESSION['Withdraw'] = $vwithdraw;
                            $_SESSION['Redeem'] = $isredeem;
                            $_SESSION['txtamount'] = $vamount;
                            $_SESSION['txtremarks'] = $vremarks;
                            $_SESSION['txtticket'] = $vticket;
                            $_SESSION['txtcardnumber'] = '';
                            $redirect = "ProcessPT_CAPI.php";
                     }
                 }
                 else
                 {
                     //this part gets MG username
//                     $rloginresult = $otopup->getmglogin($vterminalID);
//                     foreach ($rloginresult as $results)
//                     {
//                        $login = $results['ServiceTerminalAccount'];
//                        $ragentID = $results['ServiceAgentID'];
//                     }
                         
                     $_SESSION['site'] = $siteID;
                     $_SESSION['terminal'] = $vterminalID;
                     $_SESSION['serverid'] = $serverId;
                     $_SESSION['chkbalance'] = $vbalance;
                     $_SESSION['Withdraw'] = $vwithdraw;
                     $_SESSION['Redeem'] = $isredeem;
                     $_SESSION['login'] = $login;
                     $_SESSION['txtamount'] = $vamount;
                     $_SESSION['txtremarks'] = $vremarks;
                     $_SESSION['txtticket'] = $vticket;
                     $_SESSION['txtcardnumber'] = '';
                     //$_SESSION['agentid'] = $ragentID;
                     $redirect = "ProcessMG_CAPI.php";
                 }
              }
              //pass session variables to ProcessRTG.php
              else
              {
                 $_SESSION['site'] = $siteID;
                 $_SESSION['terminal'] = $vterminalID;
                 $_SESSION['login'] = $login;
                 $_SESSION['serverid'] = $serverId;
                 $_SESSION['chkbalance'] = $vbalance;
                 $_SESSION['Withdraw'] = $vwithdraw;
                 $_SESSION['Redeem'] = $isredeem;
                 $_SESSION['txtamount'] = $vamount;
                 $_SESSION['txtremarks'] = $vremarks;
                 $_SESSION['txtticket'] = $vticket;
                 $_SESSION['txtcardnumber'] = '';
                 $redirect = "ProcessRTG_CAPI.php";
              }
              $otopup->close();
              header("Location: $redirect");
          break;
          
          case "ManualRedemptionUB":
              $siteID = $_POST['cmbsite'];
              $login = $_POST['terminalcode'];
              $serverId = $_POST['cmbservices'];
              $provider = $_POST['txtservices'];
              $vterminalID = $_POST['cmbterminal'];
              $loyaltycardnumber = $_POST['txtcardnumber'];
              $mid = $_POST['txtmid'];
              $isvip = $_POST['txtisvip'];

              $isredeem = 0;
              $vremarks = '';
              $vticket = '';
              
//              if($isvip == 1){
//                  $login = $login."VIP";
//              }

              if(isset($_POST['chkbalance']))
              {
                 $vbalance = $_POST['chkbalance'];
              }
              else
              {
                 $vbalance = null;
              }
              
                        if(isset($_POST['Withdraw']))
                        {
                           $vwithdraw = $_POST['Withdraw'];
                        }
                        else
                        {
                           $vwithdraw = null;
                        }



                        $vamount = 0;
                        if(isset($_POST['txtamount']))
                        {
                             $vamount = $_POST['txtamount'];
                        }
                        if(isset($_POST['txtremarks']))
                        {
                             $vremarks = trim($_POST['txtremarks']);
                        }
                        if(isset($_POST['txtticket']))
                        {
                             $vticket = trim($_POST['txtticket']);
                        }

                        //to check if the sname of provider matches on the posted data, and redirect to its
                          //respective process
                        $sRTG = preg_match('/RTG/', $provider);
                        if($sRTG == 0)
                        {
                           $sMG = preg_match('/MG/', $provider);
                           if($sMG == 0)
                           {
                               $sPT = preg_match('/PT/', $provider);
                               if($sPT == 0)
                               {
                                   echo 'Invalid Casino.';
                               } 
                               else 
                               {
                                      $_SESSION['site'] = $siteID;
                                      $_SESSION['terminal'] = $vterminalID;
                                      $_SESSION['login'] = $login;
                                      $_SESSION['serverid'] = $serverId;
                                      $_SESSION['chkbalance'] = $vbalance;
                                      $_SESSION['Withdraw'] = $vwithdraw;
                                      $_SESSION['Redeem'] = $isredeem;
                                      $_SESSION['txtamount'] = $vamount;
                                      $_SESSION['txtremarks'] = $vremarks;
                                      $_SESSION['txtticket'] = $vticket;
                                      $_SESSION['txtcardnumber'] = $loyaltycardnumber;
                                      $_SESSION['txtmid'] = $mid;

                                      $redirect = "ProcessPT_CAPI.php";
                               }
                           }
                           else
                           {
                               //this part gets MG username
          //                     $rloginresult = $otopup->getmglogin($vterminalID);
          //                     foreach ($rloginresult as $results)
          //                     {
          //                        $login = $results['ServiceTerminalAccount'];
          //                        $ragentID = $results['ServiceAgentID'];
          //                     }

                               $_SESSION['site'] = $siteID;
                               $_SESSION['terminal'] = $vterminalID;
                               $_SESSION['serverid'] = $serverId;
                               $_SESSION['chkbalance'] = $vbalance;
                               $_SESSION['Withdraw'] = $vwithdraw;
                               $_SESSION['Redeem'] = $isredeem;
                               $_SESSION['login'] = $login;
                               $_SESSION['txtamount'] = $vamount;
                               $_SESSION['txtremarks'] = $vremarks;
                               $_SESSION['txtticket'] = $vticket;
                               $_SESSION['txtcardnumber'] = $loyaltycardnumber;
                               $_SESSION['txtmid'] = $mid;

                               //$_SESSION['agentid'] = $ragentID;
                               $redirect = "ProcessMG_CAPI.php";
                           }
                        }
                        //pass session variables to ProcessRTG.php
                        else
                        {
                           $_SESSION['site'] = $siteID;
                           $_SESSION['terminal'] = $vterminalID;
                           $_SESSION['login'] = $login;
                           $_SESSION['serverid'] = $serverId;
                           $_SESSION['chkbalance'] = $vbalance;
                           $_SESSION['Withdraw'] = $vwithdraw;
                           $_SESSION['Redeem'] = $isredeem;
                           $_SESSION['txtamount'] = $vamount;
                           $_SESSION['txtremarks'] = $vremarks;
                           $_SESSION['txtticket'] = $vticket;
                           $_SESSION['txtcardnumber'] = $loyaltycardnumber;
                           $_SESSION['txtmid'] = $mid;

                           $redirect = "ProcessRTG_CAPI.php";
                        }
                        $otopup->close();
                        header("Location: $redirect");               
          break;
          
          
          case 'InsertReplenishment':
              if(isset($_POST['txtamount']) && isset($_POST['cmbreplenishment']))
              {
                  $vsiteID = $_POST['txtsiteID'];
                  $vdatecredited = $_POST['txtdate'];
                  $vamount = removeComma($_POST['txtamount']);
                  $vreplenishmenttype = $_POST['cmbreplenishment'];
                  $vCreatedByAID = $aid;
                  $vDateCreated = $vdate;
                  
                  $rreplenishmentID = $otopup->insertreplenishment($vsiteID, $vamount, $vCreatedByAID, $vdatecredited);
                  if($rreplenishmentID > 0)
                  {
                      $msg = "Replenishment Platform: Successfully posted";
                      $vtransdetails = "replenishment id ".$rreplenishmentID.
                                       ",SiteCode ".$_POST['txtsitecode'].",for date ".$vdate.",amount ".$vamount;
                      $vauditfuncID = 21;
                      $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                  }
                  else 
                  {
                      $msg = "Replenishment Platform: Error on posting";
                  }
              }
              else
              {
                  $msg = "Replenishment Platform: Invalid Fields";
              }
              $_SESSION['mess'] = $msg;
              $otopup->close();
              header("Location: ../topupreplenishment.php");
          break;
          case 'TopupViewAccount':
              if(isset($_POST['txtusername']) && isset($_POST['txtpassword']))
              {
                  //validate if site dropdown box was selected
                  if($_POST['cmbsite'] > 0)
                  {
                       $vsiteID = $_POST['cmbsite'];
                  }
                  else
                  {
                      //validate if pos account textfield have value
                      if(strlen($_POST['txtposacc']) > 0)
                      {
                         //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                         if(strlen($_POST['txtposacc']) == 10)
                         {
                             $vposaccno = $_POST['txtposacc'];
                         }
                         else
                         {
                             $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                         }

                         $rsite = $otopup->getidbyposacc($vposaccno);
                         //check if pos account is valid
                         if($rsite)
                         {
                              $vsiteID = $rsite['SiteID']; 
                         }
                         else
                         {
                              $msg = "Invalid POS account number";
                              $_SESSION['mess'] = $msg;
                              $otopup->close();
                              header("Location: ../topupview.php");
                         }
                      }
                  }
                  $vaccname = $_POST['txtusername'];
                  $vaccpass = $_POST['txtpassword'];
                  $rresult = $otopup->checkaccountdetails($vaccname, $vaccpass);
                  $isexist = $rresult['ctracc'];
                  if($isexist > 0)
                  {
                      $rBCF = $otopup->getallbcf($vsiteID);
                      $_SESSION['site'] = array($vsiteID, $_POST['txtsitecode']);
                      $_SESSION['BCF'] = $rBCF;
                      $_SESSION['mess'] = $msg;
                      
                      //insert into audit trail
                      $vtransdetails = "Update Manual Top-up: Authorized by: ".$vaccname;
                      $vauditfuncID = 50;
                      $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                      
                      $otopup->close();
                      header("Location: topupupdateparam.php");
                  }
                  else 
                  {
                      $msg = "Account Details: Invalid Account";
                      $_SESSION['mess'] = $msg;
                      $otopup->close();
                      header("Location: ../topupview.php?mess=".$msg);
                  }
              }
              else
              {
                  $msg = "Account Details: Invalid Fields";
                  $_SESSION['mess'] = $msg;
                  $otopup->close();
                  header("Location: ../topupview.php?mess=".$msg);
              }
          break;
        }       
   }   
   //view details, page request from topupview for update details.php
   elseif(isset($_GET['page']) <> "")
   {       
            $pageloc = $_GET['page'];
            
             //validate if site dropdown box was selected
            if($_GET['siteid'] > 0)
            {
                $vsiteID = $_GET['siteid'];
            }
            else
            {
                //validate if pos account textfield have value
                if(strlen($_GET['txtposacc']) > 0)
                {
                    //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                    if(strlen($_GET['txtposacc']) == 10)
                    {
                        $vposaccno = $_GET['txtposacc'];
                    }
                    else
                    {
                        $vposaccno = str_pad($_GET['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                    }

                    $rsite = $otopup->getidbyposacc($vposaccno);
                    //check if pos account is valid
                    if($rsite)
                    {
                        $vsiteID = $rsite['SiteID'];
                    }
                    else
                    {
                        $msg = "Invalid POS account number";
                        $_SESSION['mess'] = $msg;
                        $otopup->close();
                        header("Location: ../topupview.php");
                    }
                }
            }
            $_SESSION['siteid'] = $vsiteID;
            $rBCF = array();
            $rBCF = $otopup->getallbcf($vsiteID);
            $_SESSION['BCF'] = $rBCF;
            $otopup->close();           
            if($pageloc =='Edit')
            {
                $otopup->close();
                header("Location: ../topupupdateparam.php");
            }
            else
            {
                $otopup->close();
                header("Location: ../topupupdatebal.php");
            }
   }
   //change status from pending to verified, when updating inside grid
   elseif(isset ($_GET['remittance']) == "UpdateReversal")
   {
       //get variables
       $vSiteRemittanceID = $_GET['remitid'];
       $vupatedrow = $otopup->updatesiteremittancestatus($vSiteRemittanceID,$aid,$vdate);
       if($vupatedrow == 0)
       {
         $msg =" Verification of Deposits: Record unchanged";
       }
       else
       {
         $msg =  " Verification of Deposits: Success in updating status in site remittance";
         //insert into audit trail
         $vtransdetails = "sitremittanceid = ".$vSiteRemittanceID." msg-".$msg;
         $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
       }
       $nopage = 1;
       $otopup->close();
       $_SESSION['mess'] = $msg;
       header("Location: ../reversaldeposit.php");
   }
   elseif(isset($_GET['remittance2']) == "UpdateVerifiedRemit")
   {
      //get variables
      $vSiteRemittanceID = $_GET['remitid2'];
      $vstatus = $_GET['remitstat'];
      $vupatedrow = $otopup->updateverifiedsiteremittance($vSiteRemittanceID,$vstatus,$aid,$vdate);
      if($vupatedrow == 0)
       {
         $msg =" Update Verified Deposits: Record unchanged";
       }
       else
       {
         $msg =  " Update Verified Deposits: Success in updating status in site remittance";
         //insert into audit trail
         $vtransdetails = "sitremittanceid = ".$vSiteRemittanceID." status = ".$vstatus." msg-".$msg;
         $otopup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress);
       }
       $nopage = 1;
       $otopup->close();
       $_SESSION['mess'] = $msg;
       header("Location: ../reversaldeposit2.php");
   }
   //views of reversal of deposit when remittance id is selected
   elseif (isset ($_GET['remitpage'])=='ViewSiteRemit')
   {
       $vSiteRemittanceID = $_GET['remitid'];
       $rresultreverse = $otopup->viewsiteremittance($vSiteRemittanceID);
       echo json_encode($rresultreverse);
       unset($rresultreverse);
       $otopup->close();
       exit;
   }
    //views of verified of deposit when remittance id is selected
   elseif (isset ($_GET['remitpage2'])=='ViewVerifiedSiteRemit')
   {
       $vSiteRemittanceID = $_GET['remitid2'];
       $rresultreverse = $otopup->viewverifiedsiteremittance($vSiteRemittanceID);
       echo json_encode($rresultreverse);
       unset($rresultreverse);
       $otopup->close();
       exit;
   }   
     //views of all reversal id which populates the combo box
    elseif(isset ($_POST['sendRemitID']))
    {
        //to post data to terminals combo box
        $vsiteID = $_POST['sendRemitID'];
        $rtopupview = $otopup->getsiteremittanceid($vsiteID);
        echo json_encode($rtopupview);
        unset($rtopupview);
        $otopup->close();
        exit;
    }
     //views of all verified remittances to combo box
    elseif(isset ($_POST['sendRemitID2']))
    {
        //to post data to terminals combo box
        $vsiteID = $_POST['sendRemitID2'];
        $rtopupview = $otopup->getsiteremittanceid2($vsiteID);
        echo json_encode($rtopupview);
        unset($rtopupview);
        $otopup->close();
        exit;
    }
    //for displaying the site name
    elseif(isset($_POST['cmbsitename']))
    {
        //validate if site dropdown box was selected
        if($_POST['cmbsitename'] > 0)
        {
            $vsiteID = $_POST['cmbsitename'];
        }
        else
        {
            //validate if pos account textfield have value
            if(strlen($_POST['txtposacc']) > 0)
            {
                 //check if size of pos account value was exactly entered as 10; otherwise padded it by zero
                 if(strlen($_POST['txtposacc']) == 10)
                 {
                     $vposaccno = $_POST['txtposacc'];
                 }
                 else
                 {
                     $vposaccno = str_pad($_POST['txtposacc'], 10, "0", STR_PAD_LEFT); //pos account number must be 10 in length padded by zero
                 }

                 $rsite = $otopup->getidbyposacc($vposaccno);
                 //check if pos account is valid
                 if($rsite)
                 {
                      $vsiteID = $rsite['SiteID']; 
                 }
                 else
                 {
                      echo "Invalid POS account number";
                      $otopup->close();
                      exit;
                 }
            }
        }
        
        $rresult = array();
        $rresult = $otopup->getsitename($vsiteID);
        foreach($rresult as $row)
        {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
            $rsitecode = $row['SiteCode'];
        }
        if(count($rresult) > 0)
        {
            $vsiteName->SiteName = $rsitename;
            $vsiteName->SiteCode = substr($rsitecode, strlen($terminalcode));
            $vsiteName->POSAccNo = $rposaccno;
        }
        else
        {
            $vsiteName->SiteName = "";
            $vsiteName->SiteCode = "";
            $vsiteName->POSAccNo = "";
        }
        echo json_encode($vsiteName);
        unset($rresult);
        $otopup->close();
        exit;
   }
   elseif(isset ($_POST['cmbterminal']))
    {
        $vterminalID = $_POST['cmbterminal'];
        $rresult = array();
        $rresult = $otopup->getterminalvalues($vterminalID);
        foreach($rresult as $row)
        {
          $vterminals->TerminalName = $row['TerminalName'];
          $vterminals->TerminalCode = $row['TerminalCode']; 
        }
        echo json_encode($vterminals);
        unset($rresult);
        $otopup->close();
        exit;
    }
    //get transummary and loyalty card number
    elseif(isset ($_POST['cmbservices']))
    {
        $vterminalID = $_POST['cmbservices'];
        $rresult = array();
        $rresult = $otopup->getTransSummary($vterminalID);
        foreach($rresult as $row)
        {
            $vloyaltycard->summaryID = $row['summaryID'];
            $vloyaltycard->loyaltyCard = $row['loyaltyCard']; 
        }
        echo json_encode($vloyaltycard);
        unset($rresult);
        $otopup->close();
        exit;
    }
   
   else
   {
//        $rremittanceid = $otopup->getsiteremittanceid();
//        $_SESSION['siteremit'] = $rremittanceid;
       
       $_SESSION['sites'] = $otopup->getsites(); //session variable to get all site
       $_SESSION['remittype'] = $otopup->getremittancetypes(); //session variable to get all remittance types
   }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>