<?php
/**
 * Manual e-SAFE Fulfillment Process
 * @author Mark Kenneth Esguerra
 * @date Febraury 3, 2015
 */

include __DIR__."/../sys/class/ManualAPIFulfillment.class.php";
include __DIR__."/../sys/class/LoyaltyUBWrapper.class.php";
require __DIR__.'/../sys/core/init.php';
include __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
include __DIR__.'/../sys/class/helper.class.php';

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

$maf= new ManualAPIFulfillment($_DBConnectionString[0]);
$maf2= new ManualAPIFulfillment($_DBConnectionString[2]);
$CasinoGamingCAPI = new CasinoGamingCAPI();
$loyalty = new LoyaltyUBWrapper();
$connected = $maf->open();
$connected2 = $maf2->open();
if($connected && $connected2)
{     
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $maf->getDate();    
/********** SESSION CHECKING **********/    
   $isexist=$maf->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $maf->close();
      if($maf->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   } 
   
   $isexistsession =$maf->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $maf->close();
      if($maf->isAjaxRequest())
       {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
       }
       header("Location: login.php?mess=".$msg);
   }
   else{
    //get all sites
    $sitelist = array();
    $sitelist = $maf->getallsites();
    $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection
   }
/********** END SESSION CHECKING **********/   
   if(isset($_POST['page']))
   {
       $vpage = $_POST['page'];
       
       switch ($vpage)
       {
           //Check if the player has pending transaction
           case "CheckPendingTrans": 
               $cardnumber = trim($_POST['cardnumber']);
               //check if cardnumber is blank
               if ($cardnumber != "") 
               {
                   //check if card number exist
                   $isExist = $maf->checkMemberCardExist($cardnumber);
                   if (!empty($isExist))
                   {
                       //Check Card Status
                       $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
                       $obj_result = json_decode($loyaltyResult);
                       if (!empty($obj_result)) {
                            $statuscode = $obj_result->CardInfo->StatusCode;
                            //Active and Active Temp cards are the only cards allowed for fulfillment
                            if ($statuscode == 1 || $statuscode == 5)
                            {
                                 $MID = $isExist['MID'];
                                 //check if player has pending trans
                                 $hasPending = $maf->checkPendingEwalletTrans($MID);
                                 if ($hasPending['Count'] > 0) 
                                 {
                                     $result = array('ErrorCode' => 0, 
                                             'CardNumber' => $cardnumber, 
                                             'MID' => $MID, 
                                             'Source' => $hasPending['RequestSource'], 
                                             'Message' => 'Manual e-SAFE Fulfillment: Has Pending Transaction.');
                                 }
                                 else
                                 {
                                     $result = array('ErrorCode' => 1, 
                                             'Message' => 'Manual e-SAFE Fulfillment: Player has no pending transaction.');
                                 }   
                            }
                            else
                            {
                                $msg = $maf->membershipcardStatus($statuscode);
                                $result = array('ErrorCode' => 1, 
                                                'Message' => $msg);
                            } 
                       }
                       else {
                           $result = array('ErrorCode' => 2, 
                                           'Message' => 'Can\'t get card info.');
                       }      
                   }
                   else
                   {
                       $result = array('ErrorCode' => 2, 
                                   'Message' => 'Card Number not exist.');
                   }
               }
               else
               {
                   $result = array('ErrorCode' => 2, 
                                   'Message' => 'Please enter Card Number.');
               }
               echo json_encode($result);
           break;
           //Get Transaction Details of the Pending Transaction
           case "GetTransDetails": 
               $cardnumber  = $_POST['cardnumber'];
               $MID         = $_POST['mid'];
               $source      = $_POST['source'];
               //get transaction details in e-SAFE trans
               $transdetails = $maf->getEwalletTransInfo($MID);
               //display source
               switch ($source)
               {
                   case 0: $txtsource = "Cashier";
                       break;
                   case 1: $txtsource = "Launchpad";
                       break;
                   case 2: $txtsource = "EGM";
                       break;
                   default: $txtsource = "CAshier";
                       break;
               }
               if (count($transdetails) > 0)
               {
                   //retrieve info
                   foreach ($transdetails as $details)
                   {
                       $serviceID       = $details['ServiceID'];
                       $eWalletTransID  = $details['EwalletTransID'];
                       $amount          = $details['Amount'];
                       $transType       = ManualAPIFulfillment::stringTransType($details['TransType']);
                       $usermode        = ($details['UserMode'] == 1) ? "User Based" : "Terminal Based";
                       $siteID          = $details['SiteID'];
                       $terminalID      = $details['TerminalID'];
                   }
                   $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
                   $obj_result = json_decode($loyaltyResult);

                   $casinoarray_count = count($obj_result->CardInfo->CasinoArray);
                   $serviceids = '';
                   $serviceusername = '';
                   $casinos = array();
                   //get service name
                   $casinoname = $maf->getServiceName($serviceID);
                   foreach ($casinoname as $rowz) {
                       $servicename = $rowz['ServiceName'];
                   }
                   if($casinoarray_count != 0)
                   {
                       for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {
                           $casinos[$ctr] = array(
                               'ServiceID' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID,
                               'ServiceUserName' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                           );
                       }
                       $casino2 = $maf->loopAndFindCasinoService($casinos, 'ServiceID', $serviceID);
                       if(!empty($casino2)){
                           $casinoinfo = array(
                                 'UserName'  => $obj_result->CardInfo->MemberName,
                                 'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                 'Email'  => $obj_result->CardInfo->Email,
                                 'Birthdate' => $obj_result->CardInfo->Birthdate,
                                 'Casino' => $servicename, 
                                 'Login' => $casino2[0]['ServiceUserName'],
                                 'CardNumber' => $obj_result->CardInfo->CardNumber, 
                                 'EWalletTransID' => $eWalletTransID, 
                                 'Amount' => $amount, 
                                 'TransType' => $transType, 
                                 'UserMode' => $usermode, 
                                 'SiteID' => $siteID, 
                                 'TerminalID' => $terminalID, 
                                 'ServiceID' => $serviceID, 
                                 'Source' => $txtsource, 
                                 'ErrorCode' => 0
                           );
                         }
                         else{
                             $casinoinfo = array(
                                  'UserName'  => $obj_result->CardInfo->MemberName,
                                  'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                  'Email'  => $obj_result->CardInfo->Email,
                                  'Birthdate' => $obj_result->CardInfo->Birthdate,
                                  'Casino' => $servicename, 
                                  'Login' => $obj_result->CardInfo->CasinoArray[0]->ServiceUsername,
                                  'CardNumber' => $obj_result->CardInfo->CardNumber, 
                                  'EWalletTransID' => $eWalletTransID, 
                                  'Amount' => $amount, 
                                  'TransType' => $transType, 
                                  'UserMode' => $usermode, 
                                  'SiteID' => $siteID, 
                                  'TerminalID' => $terminalID, 
                                  'ServiceID' => $serviceID, 
                                  'Source' => $txtsource, 
                                  'ErrorCode' => 0
                            );
                         }
                         echo json_encode($casinoinfo);
                         $_SESSION['MID'] = $obj_result->CardInfo->MID;
                         $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                  }
                  else 
                  {
                      $result = array('ErrorCode' => 1, 
                                      'Casino' => $serviceID, 
                                      'Message' => 'Manual e-SAFE Fulfillment: Casino is empty');
                      echo json_encode($result);
                      
                  }
               }
               else
               {
                   $result = array('ErrorCode' => 1, 
                                   'Message' => 'Unable to get Transaction details.');
                   echo json_encode($result);
               }
               break;
           case "VerifyCasino": 
                //verify casino and transaction search information
                $txtcasino          = $_POST['txtcasino'];
                $serviceID          = $_POST['txtserviceid'];
                $cardnumber         = $_POST['txtcardnumber'];
                $transtype          = $_POST['txttranstype'];
                $ewallettransid     = $_POST['txtewallettransid'];
                $terminalid         = $_POST['txtterminalid'];
                $siteid             = $_POST['txtsiteid'];
                $source             = $_POST['txtsource'];
                //show transaction type
                switch ($transtype){
                    case 'Deposit':
                        $transtype = 'D';
                    break;   

                    case 'Reload':
                        $transtype = 'R';
                    break;

                    case 'Withdraw':
                        $transtype = 'W';
                    break;

                    case 'ReDeposit':
                        $transtype = 'RD';
                    break;
                }

                $tracking1 = $ewallettransid;
                $tracking2 = $transtype;
                $tracking3 = $terminalid;
                $tracking4 = $siteid;

                $usermode = $maf->getusermode($serviceID);
                if($usermode == 1){
                    $casino = $_SESSION['CasinoArray'];

                        $casinoarray_count = count($casino);

                        $casinos = array();

                        if($casinoarray_count != 0) {
                            for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {
                                $casinos[$ctr] = 
                                                 array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                                        'ServicePassword' => $casino[$ctr]->ServicePassword,
                                                        'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                                        'ServiceID' => $casino[$ctr]->ServiceID,
                                                        'UserMode' => $casino[$ctr]->UserMode,
                                                        'isVIP' => $casino[$ctr]->isVIP,
                                                        'Status' => $casino[$ctr]->Status 
                                                 );

                                //get details by pick casino
                                $casino2 = $maf->loopAndFindCasinoService($casinos, 'ServiceID', $serviceID);  
                            }
                        }
                        $login = $casino2[0]['ServiceUsername'];
                }
                else{
                    $login = $maf->getTerminalCode($terminalid);
                    $login = $login['TerminalCode'];
                }


                $servicegroupname = $maf->getServiceGrpNameByName($txtcasino);
                //check if casino is RTG, MG or PT
                switch ($servicegroupname)
                {
                    case "RTG":

                        $url = $_ServiceAPI[$serviceID-1];
                        $capiusername = $_CAPIUsername;
                        $capipassword = $_CAPIPassword;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = '';
                        $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);

                    break;

                    case "RTG2":
                        
                        $url = $_ServiceAPI[$serviceID-1];
                        $capiusername = $_CAPIUsername;
                        $capipassword = $_CAPIPassword;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = '';
                        $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);
                    break;    
                    case "MG":

                        //check source if cashier or launchpad
                        if($source == 'Cashier'){
                            $transactionid = $maf->getServiceTransactionID($transrefid);
                            $tracking4 = $transactionid['ServiceTransactionID'];

                        }
                        elseif($source == 'Launchpad')
                        {
                            $transactionid = $maf->getServiceTransactionIDLP($transrefid);
                            $tracking4 = $transactionid['ServiceTransactionID'];

                        }

                        $_MGCredentials = $_ServiceAPI[$serviceID-1];
                        list($mgurl, $mgserverID) =  $_MGCredentials;
                        $url = $mgurl;
                        $capiusername = $_CAPIUsername;
                        $capipassword = $_CAPIPassword;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = $mgserverID;

                        $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);

                    break;
                    case "PT":

                        //check source if cashier or launchpad
                        if($source == 'Cashier'){
                            $transactionid = $maf->getTransactionID($serviceID, $cmbterm);
                            $tracking1 = $transactionid['TransactionRequestLogID'];
                        }
                        elseif($source == 'Launchpad')
                        {
                            $transactionid = $maf->getTransactionIDLP($serviceID, $cmbterm);
                            $tracking1 = $transactionid['TransactionRequestLogLPID'];
                            $tracking1 = "LP".$tracking1;

                        }    

                        $url = $_ServiceAPI[$serviceID-1];
                        $capiusername = $_ptcasinoname;
                        $capipassword = $_ptsecretkey;
                        $capiplayername = $_CAPIPlayerName;
                        $capiserverID = '';
                        $transSearchInfo = $CasinoGamingCAPI->TransSearchInfo($servicegroupname, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4);

                    break;
                }
                    //check if transaction is not successful
                    if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false) {
                       $transstatus = 2;
                       $apiresult = false;
                       $transrefid = '';
                       $_SESSION['servicetransid'] = $transrefid;
                       $_SESSION['servicestatus'] = $apiresult;
                    }
                    else
                    {
                        if(isset($transSearchInfo['TransactionInfo']))
                        {
                            //RTG / Magic Macau
                            if(isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']))
                            {

                                $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                                $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                            }
                            //MG / Vibrant Vegas
                            elseif(isset($transSearchInfo['TransactionInfo']['MG']))
                            {                       
                                $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                                $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];     
                            }
                            //PT / PlayTech
                            elseif(isset($transSearchInfo['TransactionInfo']['PT']))
                            {
                                $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];

                                if($apiresult == 'missing'){
                                    $transrefid = 0;
                                }
                                else
                                {
                                    $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                                }

                            }
                        }
                        else
                        {     
                            echo json_encode($transstatus);
                        }

                        //$apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];

                        if($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                            $transstatus = 1;
                            $_SESSION['servicetransid'] = $transrefid;
                            $_SESSION['servicestatus'] = $apiresult;
                        } 
                        else 
                        {
                            $transstatus = 2;
                            $_SESSION['servicetransid'] = $transrefid;
                            $_SESSION['servicestatus'] = $apiresult;
                        }  
                    }    


                    echo json_encode($transstatus);

                    unset($transstatus, $transSearchInfo, $apiresult, $transrefid);
                    $maf->close();
            break; 
            case "Proceed":
                $txttransstatus = $_POST['txttransstatus'];
                $txtsource = $_POST['txtsource'];
                $txttranstype = $_POST['txttranstype'];
                $txtewallettransid = $_POST['txtewallettransid'];
                $site = $_POST['txtsite'];
                $terminal = $_POST['txtterminal'];
                $amount = $_POST['txtamount'];
                $txtcardnumber = $_POST['txtcardnumber'];
                $serviceid = $_POST['txtserviceid'];
                $txtcasino = $_POST['txtcasino'];
                $txtusername = $_POST['txtusername'];
                $usermode = $_POST['txtusermode'];
                $transrefid = $_SESSION['servicetransid'];
                $apiresult = $_SESSION['servicestatus'];
                
                $servicegroupname = $maf->getServiceGrpNameByName($txtcasino);
                $txtcasino = $servicegroupname;
                //check mode if user mode
                if($usermode == 'User Based'){
                    $usermode = 1;

                      $casino = $_SESSION['CasinoArray'];

                      $casinoarray_count = count($casino);

                      $casinos = array();

                      if($casinoarray_count != 0) {
                          for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {
                              $casinos[$ctr] = 
                                               array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                                      'ServicePassword' => $casino[$ctr]->ServicePassword,
                                                      'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                                      'ServiceID' => $casino[$ctr]->ServiceID,
                                                      'UserMode' => $casino[$ctr]->UserMode,
                                                      'isVIP' => $casino[$ctr]->isVIP,
                                                      'Status' => $casino[$ctr]->Status 
                                               );

                              //get details by pick casino
                              $casino2 = $maf->loopAndFindCasinoService($casinos, 'ServiceID', $serviceid);  
                          }
                      }
                      $ubservicepassword = $casino2[0]['ServicePassword'];
                      $serviceusername = $casino2[0]['ServiceUsername'];
                      $hashedpass = $casino2[0]['HashedServicePassword'];

                }
                else{
                    $usermode = 0;
                    $ubservicepassword = '';
                    $hashedpass = '';
                }
                //get site balance for a certain site
                $bcf = $maf->getSiteBalance($site);
                $mid = $_SESSION['MID'];
                $transstatus = 1;
                $trans_summary_max_id = null;
                
                //get FromBalance
                $getBalance = $maf->getFromBalance($txtewallettransid);
                
                //get the TransSummaryID to check if has session
                $getTransSumID = $maf->getTransSummaryID($txtewallettransid);
                $transsummaryid = $getTransSumID['TransactionSummaryID'];
                //TRANSACTION APPROVED
                if ($txttransstatus == 1)
                {
                    //add the Amount and FromBalance
                    $toBalance = $amount + $getBalance['FromBalance']; 
                    
                    if($txtsource == 'Cashier')
                    {
                          //update transaction request logs status to transaction fulfilled
                          $status = '3';
                          switch (true)
                          {
                              case (strstr($txtcasino, "MG")):
                                $converted_res = ($apiresult) ? 'true' : 'false';
                              break;
                              case (strstr($txtcasino, "PT")):
                                $converted_res = $apiresult;
                              break;
                              case (strstr($txtcasino, "RTG")):
                                $converted_res = $apiresult;
                              break;
                          }
                          $totalWalletReloads = null;
                          if (!is_null($getTransSumID['TransactionSummaryID']))
                          {
                              //get wallet reloads
                              $getWalletReloads = $maf->getWalletReloads($transsummaryid);
                              $totalWalletReloads = $getWalletReloads['WalletReloads'] + $amount;
                          }
                          $updatetrans = $maf->updateEwalletTransStat($txtewallettransid, $status, $toBalance, $converted_res, $transrefid, $transsummaryid, $totalWalletReloads, $aid);
                          //$uptrans = $maf->uptransactionreqlogs($status, $txttransrefid, $transrefid, $apiresult);

                          if($updatetrans > 0)
                          {
//                                if($checkterminaltype['TerminalType'] == 1){
//                                    $egmreqid = $maf->getEgmReqID($cmbterm, $mid);
//
//                                    if(empty($egmreqid) || $egmreqid == ''){
//                                        $maf->updateEgmRequestLogs($status, $transrefid, $egmreqid);
//                                    }
//                                }  
                               $newbal = $bcf - $amount;
                               $updatebcf = $maf->updateBcfEwallet($newbal, $site, "Start Session");
                               if ($updatebcf)
                               {
                                    $msg = 'Manual e-SAFE Fulfillment: Transaction Successful';

                                    $zaid = $aid;
                                    $zdate = $vdate;
                                    $ztransdetails = 'Casino Service = '."$serviceid".' Status = '."$status".' TerminalID = '."$terminal".' UserMode = '."$usermode";
                                    $zauditfunctionID = 73;
                                    $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);    
                               }
                               else
                               {
                                    $msg = 'Manual e-SAFE Fulfillment: Failed to update BCF.';
                               }
                          }
                          else
                          {
                              $msg = 'Manual e-SAFE Fulfillment: Failed to Update Transaction Status';
                          }
                    }
                    else if ($txtsource == "KAPI")
                    {
                        
                    }
                }
                //TRANSACTION DENIED
                else
                {
                    //retain the frombalance and pass to toBalance
                    $toBalance = $getBalance['FromBalance'];
                    //update transaction request logs status to transaction denied
                    if($txtsource == 'Cashier')
                    {//update transaction request logs status to transaction fulfilled
                          $status = '4';
                          $totalWalletReloads = null;
                          switch (true)
                          {
                              case (strstr($txtcasino, "MG")):
                                $converted_res = ($apiresult) ? 'true' : 'false';
                              break;
                              case (strstr($txtcasino, "PT")):
                                $converted_res = $apiresult;
                              break;
                              case (strstr($txtcasino, "RTG")):
                                $converted_res = $apiresult;
                              break;
                          }
                            
                          $updatetrans = $maf->updateEwalletTransStat($txtewallettransid, $status, $toBalance, $converted_res, $transrefid, $transsummaryid, $totalWalletReloads, $aid);
                          //$uptrans = $maf->uptransactionreqlogs($status, $txttransrefid, $transrefid, $apiresult);

                          if($updatetrans > 0)
                          {
//                                if($checkterminaltype['TerminalType'] == 1){
//                                    $egmreqid = $maf->getEgmReqID($cmbterm, $mid);
//
//                                    if(empty($egmreqid) || $egmreqid == ''){
//                                        $maf->updateEgmRequestLogs($status, $transrefid, $egmreqid);
//                                    }
//                                }  
                                $msg = 'Manual e-SAFE Fulfillment: Transaction Successful';

                                $zaid = $aid;
                                $zdate = $vdate;
                                $ztransdetails = 'Casino Service = '."$serviceid".' Status = '."$status".' TerminalID = '."$terminal".' UserMode = '."$usermode";
                                $zauditfunctionID = 73;
                                $maf->logtoaudit($new_sessionid, $zaid, $ztransdetails, $zdate, $vipaddress, $zauditfunctionID);    
                          }
                          else
                          {
                              $msg = 'Manual e-SAFE Fulfillment: Failed to Update Transaction Status';
                          }
                    }
                    else if ($txtsource == "KAPI")
                    {
                        
                    }
                }
                echo json_encode($msg);
                unset($updatetrans, $txttranstype, $amount, $bcf, $msg);
                unset($_SESSION['servicetransid'], $_SESSION['servicestatus']);
                $maf->close();  
                break;
       }
   }
    //this was used in transaction tracking
    if(isset($_POST['sendSiteIDz']))
    {
        $vsiteID = $_POST['sendSiteIDz'];
        
        //check if selected site is valid
        if($vsiteID <> "-1")
        {
            $rsitecode = $maf->getsitecode($vsiteID); //get the sitecode first
            $vresults = array();
            //get all terminals
            $vresults = $maf->selectterminals($vsiteID);  
            if(count($vresults) > 0)
            {
                $terminals = array();
                foreach($vresults as $row)
                {
                    $rterminalID = $row['TerminalID'];
                    $rterminalCode = $row['TerminalCode'];
                    $sitecode = $terminalcode;
                    //remove the "icsa-[SiteCode]"
                        $rterminalCode = substr($row['TerminalCode'], strlen($rsitecode['SiteCode']));

                    //create a new array to populate the combobox
                    $newvalue = array("TerminalID" => $rterminalID, "TerminalCode" => $rterminalCode);
                    array_push($terminals, $newvalue);
                }
                echo json_encode($terminals);
                unset($terminals);
            }
            else
            {
                echo "No Terminal Assigned";
            }
            unset($vresults);
        }
        else
        {
            echo "No Terminal Assigned";
        }
        unset($vresults);
        $maf->close();
        exit;
    }
    elseif(isset ($_GET['cmbterminal']))
    {
        $vterminalID = $_GET['cmbterminal'];
        $rresult = array();
        $rresult = $maf->getterminalname($vterminalID);
        $vterminalName->TerminalName = $rresult['TerminalName'];
        echo json_encode($vterminalName);
        unset($rresult);
        $maf->close();
        exit;
    }
    
    else
    {
    }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>