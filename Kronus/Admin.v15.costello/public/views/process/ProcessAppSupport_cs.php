<?php

/*
 * Created By: Lea Tuazon
 * Date Created : June 8, 2011
 *
 * Modified By: Edson L. Perez
 */

include __DIR__."/../sys/class/CSManagement.class.php";
require __DIR__.'/../sys/core/init.php';
include __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
//include __DIR__.'/../sys/class/RealtimeGamingPlayerAPI.class.php';

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

$oas= new CSManagement($_DBConnectionString[0]);
$connected = $oas->open();
if($connected)
{     
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $oas->getDate();    
/********** SESSION CHECKING **********/    
   $isexist=$oas->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $oas->close();
      if($oas->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   } 
   
   $isexistsession =$oas->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $oas->close();
      header("Location: login.php?mess=".$msg);
   }
/********** END SESSION CHECKING **********/   
   
    //checks if account was locked 
   $islocked = $oas->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $oas->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $oas->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
    //get all services 
    $rserviceall = array();
    $rserviceall = $oas->getallservices("ServiceName");
    $_SESSION['serviceall'] = $rserviceall;

    //get all sites
    $sitelist = array();
    $sitelist = $oas->getallsites();
    $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection

    //for services --> RTG Servers only
    $rservice = array();
    $rservice = $oas->getallservices("ServiceName");
    $rservices = array();
    foreach($rservice as $row)
    {
        $rserverID = $row['ServiceID'];
        $rservername = $row['ServiceName'];

        if(strstr($rservername, "RTG"))
        {
           $newarr = array('ServiceID' => $rserverID, 'ServiceName' => $rservername);
           array_push($rservices, $newarr);   
        }
    }
    $_SESSION['getservices'] = $rservices; //session variable for RTG Servers selection
    
    if(isset($_POST['paginate']))
    {
        $vpaginate = $_POST['paginate'];
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $direction = $_POST['sord']; // get the direction
        $theProviders = $oas->getallservices("ServiceName"); //Added on June 13, 2012
        
        switch($vpaginate)
        {
            //page post for transaction tracking
            case 'ViewSupport':
                if(isset ($_POST['cmbsite']) && isset ($_POST['cmbterminal']) 
                 && isset ($_POST['txtDate1']) && isset ($_POST['txtDate2']) 
                 && isset($_POST['cmbstatus']))
                 {
                        $vSiteID = $_POST['cmbsite'];
                        $vTerminalID = $_POST['cmbterminal'];
                        $vdate1 = $_POST['txtDate1'];
                        $vdate2 = $_POST['txtDate2'];
                        $vFrom = $vdate1;
                        $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
                        $vtransstatus = $_POST['cmbstatus'];
                        $vtranstype = $_POST['cmbtranstype'];

                        /** Store status to an array **/
                        $arrstasssstus = array();
                        if($vtransstatus == 1)
                        {
                            $arrstatus = array($vtransstatus, '3');
                        }
                        elseif($vtransstatus == 2)
                        {
                            $arrstatus = array($vtransstatus, '4');
                        }
                        else{
                            $arrstatus = array($vtransstatus);
                        }

                        $rcount = $oas->counttransactiondetails($vSiteID,$vTerminalID,$arrstatus, $vtranstype, $vFrom,$vTo); 

                        $count = $rcount['count'];

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
                        $result = $oas->selecttransactiondetails($vSiteID,$vTerminalID,$arrstatus, $vtranstype, $vFrom,$vTo, $start, $limit);  

                        if(count($result) > 0)
                        {
                             $i = 0;
                             $responce->page = $page;
                             $responce->total = $total_pages;
                             $responce->records = $count;                    
                             foreach($result as $vview)
                             {                     
                                switch( $vview['Status'])
                                {
                                    case 0: $vstatus = 'Pending';break;
                                    case 1: $vstatus = 'Successful';    break;
                                    case 2: $vstatus = 'Failed';break;
                                    case 3: $vstatus = 'Timed Out';break;
                                    case 4: $vstatus = 'Transaction Approved (Late)'; break;   
                                    case 5: $vstatus = 'Transaction Denied (Late)';  break;
                                    default: $vstatus = 'All'; break;
                                } 

                                switch($vview['TransactionType'])
                                {
                                   case 'D': $vtranstype = 'Deposit';break;
                                   case 'W': $vtranstype = 'Withdrawal';break;
                                   case 'R': $vtranstype = 'Reload';break;
                                   case 'RD': $vtranstype = 'Redeposit';break;
                                }               

                                $responce->rows[$i]['id']=$vview['TransactionDetailsID'];
                                $responce->rows[$i]['cell']=array($vview['TransactionReferenceID'],$vview['TerminalID'],$vtranstype,$vview['ServiceTransactionID'], number_format($vview['Amount'],2),$vview['DateCreated'],$vstatus, $vview['UserName']);
                                $i++;
                             }
                        }
                        else
                        {
                             $i = 0;
                             $responce->page = $page;
                             $responce->total = $total_pages;
                             $responce->records = $count;
                             $msg = "Application Support: No returned result";
                             $responce->msg = $msg;
                        }

                        echo json_encode($responce);
                        unset($result);
                        $oas->close();
                        exit;
               }
            break;
            //page post for E-city transaction details tracking
            case 'LPTransactionDetails':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vdate1 = $_POST['txtDate1'];
                $vdate2 = $_POST['txtDate2'];
                $vFrom = $vdate1;
                $vTo = $vdate2; //date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
                $vsummaryID = $_POST['summaryID'];
                
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionReferenceID"; //default sort name for transactiondetails
                }
                
                $rcount = $oas->counttransdetails($vSiteID,$vTerminalID, $vFrom, $vTo, $vsummaryID); 

                $count = $rcount['ctrtdetails'];

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
                $start < 0 ? $start = 0 : $start = $start; //Added on June 14, 2012 to Handle negative starting page
                $limit = (int)$limit;   
                $result = $oas->gettransactiondetails($vSiteID, $vTerminalID, $vFrom, $vTo, $vsummaryID, $start, $limit, $sort, $direction);

                if(count($result) > 0)
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;                    
                     foreach($result as $vview)
                     {                     
                        switch( $vview['Status'])
                        {
                            case 0: $vstatus = 'Pending';break;
                            case 1: $vstatus = 'Successful';    break;
                            case 2: $vstatus = 'Failed';break;
                            case 3: $vstatus = 'Timed Out';break;
                            case 4: $vstatus = 'Transaction Approved (Late)'; break;   
                            case 5: $vstatus = 'Transaction Denied (Late)';  break;
                            default: $vstatus = 'All'; break;
                        } 

                        switch($vview['TransactionType'])
                        {
                           case 'D': $vtranstype = 'Deposit';break;
                           case 'W': $vtranstype = 'Withdrawal';break;
                           case 'R': $vtranstype = 'Reload';break;
                           case 'RD': $vtranstype = 'Redeposit';break;
                        }
                        
                        /**
                         *Added as of June 13, 2012
                         * @author Marx Lenin Topico 
                         */
                        if(array_key_exists($vview['ServiceID'], $theProviders)) {
                            $serviceID = $theProviders[$vview['ServiceID']]["ServiceName"];
                        }

                        $responce->rows[$i]['id']=$vview['TransactionReferenceID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionReferenceID'],$vview['TransactionSummaryID'],$vview['SiteID'], $vview['TerminalID'],$vtranstype,$serviceID, number_format($vview['Amount'],2),$vview['DateCreated'],$vview['UserName'], $vstatus);
                        $i++;
                     }
                }
                else
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;
                     $msg = "E-City Tracking: No returned result";
                     $responce->msg = $msg;
                     
                     /**
                      *Add as of June 13, 2012
                      * @author Marx - Lenin Topico
                      *  
                      */
                     $responce->rows[0]['id']= 1;
                     $responce->rows[0]['cell']=array('No Record',
                         'No Record','No Record', 
                         'No Record','No Record', 
                         'No Record','No Record',
                         'No Record', 'No Record','No Record');
                }

                echo json_encode($responce);
                unset($result);
                $oas->close();
                exit;
            break;
            //page post for transaction summary
            case 'LPTransactionSummary':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vdate1 = $_POST['txtDate1'];
                $vdate2 = $_POST['txtDate2'];
                $vFrom = $vdate1;
                $vTo = $vdate2; //date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionsSummaryID"; //default sort name for transaction summary grid
                }
                
                $rcount = $oas->counttranssummary($vSiteID,$vTerminalID, $vFrom, $vTo); 
                
                $count = $rcount['ctrtsum'];
                
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
                $start < 0 ? $start = 0 : $start = $start; //Added on June 14, 2012 to Handle negative starting page
                $limit = (int)$limit;   
                $result = $oas->gettransactionsummary($vSiteID, $vTerminalID, $vFrom, $vTo, $start, $limit, $sort, $direction);
                
                if(count($result) > 0)
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;                    
                     foreach($result as $vview)
                     {                     
                        $responce->rows[$i]['id']=$vview['TransactionsSummaryID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionsSummaryID'],
                            $vview['SiteID'], $vview['TerminalID'],  
                            number_format($vview['Deposit'], 2), 
                            number_format($vview['Reload'],2), 
                            number_format($vview['Withdrawal'], 2), 
                            $vview['DateStarted'], 
                            $vview['DateEnded'], 
                            $vview['UserName'],
                            number_format($vview['ActualAmount'], 2,'.',','),
                            $vview['TransactionDate']);
                        
                        $i++;
                     }
                }
                else
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;
                     $msg = "E-City Tracking: No returned result";
                     $responce->msg = $msg;
                     
                     /**
                      *Add as of June 13, 2012
                      * @author Marx - Lenin Topico
                      *  
                      */
                     $responce->rows[0]['id']= 1;
                     $responce->rows[0]['cell']=array('No Record',
                         'No Record','No Record', 
                         'No Record','No Record', 
                         'No Record','No Record',
                         'No Record', 'No Record','No Record', 'No Record');
                     
                }

                echo json_encode($responce);
                unset($result);
                $oas->close();
                exit;
            break;
            //page post for transaction request logs
            case 'LPTransactionLogs':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vdate1 = $_POST['txtDate1'];
                $vdate2 = $_POST['txtDate2'];
                $vFrom = $vdate1;
                $vTo = $vdate2; //date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
                $vsummaryID = $_POST['summaryID'];
                
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionRequestLogLPID"; //default sort name for transaction logs(E-City) grid
                }
                
                $rcount = $oas->counttranslogslp($vSiteID,$vTerminalID, $vFrom, $vTo, $vsummaryID); 
                
                $count = $rcount['ctrlogs'];

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
                $start < 0 ? $start = 0 : $start = $start; //Added on June 14, 2012 to Handle negative starting
                $limit = (int)$limit;   
                
                $result = $oas->gettranslogslp($vSiteID, $vTerminalID, $vFrom, $vTo, $vsummaryID, $start, $limit, $sort, $direction);

                if(count($result) > 0)
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;                    
                     foreach($result as $vview)
                     {
                        switch( $vview['Status'])
                        {
                            case 0: $vstatus = 'Pending';break;
                            case 1: $vstatus = 'Successful';    break;
                            case 2: $vstatus = 'Failed';break;
                            case 3: $vstatus = 'Fulfilled Approved';break;
                            case 4: $vstatus = 'Fulfilled Denied'; break;                               
                            default: $vstatus = 'All'; break;
                        } 
                        
                        switch($vview['TransactionType'])
                        {
                           case 'D': $vtranstype = 'Deposit Transfer';break;
                           case 'W': $vtranstype = 'Withdrawal Transfer';break;
                           case 'R': $vtranstype = 'Reload';break;
                           case 'RD': $vtranstype = 'Redeposit';break;
                        }
                        
                        /**
                         *Added as of June 13, 2012
                         * @author Marx Lenin Topico 
                         */
                        if(array_key_exists($vview['ServiceID'], $theProviders)) {
                            $serviceID = $theProviders[$vview['ServiceID']]["ServiceName"];
                        }
                        
                        $vsthistoryID = $vview['ServiceTransferHistoryID'];
                        $responce->rows[$i]['id']=$vview['TransactionRequestLogLPID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionRequestLogLPID'],$vview['TransactionReferenceID'], $vview['SiteID'], 
                                                          $vview['TerminalID'], $vtranstype, $vview['ServiceTransactionID'], 
                                                          $vview['ServiceStatus'], number_format($vview['Amount'], 2), $serviceID, 
                                                          $vview['StartDate'], $vview['EndDate'], $vstatus);
                        $i++;
                     }
                }
                else
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;
                     $msg = "E-City Tracking: No returned result";
                     $responce->msg = $msg;
                     
                     /**
                      *Add as of June 13, 2012
                      * @author Marx - Lenin Topico
                      *  
                      */
                     $responce->rows[0]['id']= 1;
                     $responce->rows[0]['cell']=array('No Record',
                         'No Record','No Record', 
                         'No Record','No Record', 
                         'No Record','No Record',
                         'No Record', 'No Record','No Record', 'No Record', 'No Record');
                     
                }

                echo json_encode($responce);
                unset($result);
                $oas->close();
                exit;
            break;
            case 'ViewMachineInfo':
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "CashierMachineInfoId_PK"; //default sort name for transaction logs(E-City) grid
                }
                $vsiteID = $_POST['siteid'];
                $rcount = $oas->countcashiermachineinfo($vsiteID);
                $count = $rcount['ctrmachine'];
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
                $result = $oas->getcashiermachineinfo($start, $limit, $vsiteID);
                if(count($result) > 0)
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;                    
                     foreach($result as $vview)
                     {
                         $cshmacID = $vview['CashierMachineInfoId_PK'];
                         $sitecode = substr($vview['SiteCode'], strlen($terminalcode));
                         $responce->rows[$i]['id']=$cshmacID;
                         $responce->rows[$i]['cell']=array($sitecode,$vview['ComputerName'], $vview['CPU_Id'], 
                                                          $vview['BIOS_SerialNumber'], $vview['MAC_Address'], 
                                                          $vview['Motherboard_SerialNumber'], $vview['OS_Id'], $vview['IPAddress'], 
                                                          "<input type=\"button\" value=\"Disable\" onclick=\"window.location.href='process/ProcessAppSupport.php?cshmacid=$cshmacID'+'&disable='+'DisableTerminal';\"/>");
                         $i++;
                     }
                }
                else
                {
                     $i = 0;
                     $responce->page = $page;
                     $responce->total = $total_pages;
                     $responce->records = $count;
                     $msg = "Disabling of cashier Terminal: No returned result";
                     $responce->msg = $msg;
                }

                echo json_encode($responce);
                unset($result);
                $oas->close();
                exit;
            break;
        }
    }
    
    if(isset ($_GET['disable']) == "DisableTerminal")
    {
       $vcshmacID = $_GET['cshmacid'];
       $_SESSION['cshmacid'] = $vcshmacID; //session variable to pass the account 
       $oas->close();
       header("Location: ../appdisableterminal.php");
    }
    //for passkey on/off
    if(isset($_POST['page2']))
    {
        $vpage2 = $_POST['page2'];
        switch ($vpage2)
        {
            case 'withpasskey':
                $cashierid = $_POST['cmbcashier'];
                $result = $oas->updatecashierpasskey($cashierid, $_POST['optpasskey'] );     
                if($result > 0)
                {
                   $msg ="Application Support : Passkey tag successfully updated";
                   //insert into audit trail
                   $vtransdetails = "cashier username ".$_POST['txtcashier'];
                   $vauditfuncID = 6;
                   $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                }
                else
                {
                   $msg ="Application Support : Passkey tag unchanged";
                }
                $oas->close();
                $_SESSION['mess']= $msg;
                header("Location: ../apppasskey.php");
            break;
            case 'ReAssignServer':
               if((isset($_POST['chosen'])) && (isset($_POST['cmbnewservice'])) 
                       && (isset ($_POST['cmboldservice'])) && (isset ($_POST['cmbsite'])) && isset($_POST['txtremarks']))
               {
                   $vremarks = trim($_POST['txtremarks']);
                   $vsiteID = $_POST['cmbsite'];
                   $vnewserviceID = $_POST['cmbnewservice'];
                   $voldserviceID = $_POST['cmboldservice'];
                   $varrterminalcode =  $_POST['chosen'];
                   $vsitecode = $_POST['txtsitecode'];
                   $vpasscode = $_POST['txtpasscode'];
                   //$rsitecode = $oas->getsitecode($vsiteID); //get the sitecode first
                   //$vsitecode = $rsitecode['SiteCode'];
                   //get terminalID for regular and vip terminal
                   $rterminals = $oas->getterminalID($varrterminalcode, $vsiteID, $vsitecode);
                   
                   //store all necessary information in the array
                   $rbatch = array();
                   foreach ($rterminals as $value)
                   {
                       foreach($value as $row)
                       {
                           $vterminalID = $row['TerminalID'];
                           $vterminalCode = $row['TerminalCode'];
                           $vnewarr = array("TerminalID" => $vterminalID, "OldServiceID" => $voldserviceID,
                                            "NewServiceID" => $vnewserviceID, "Remarks" => $vremarks,
                                            "TerminalCode"=>$vterminalCode);
                           array_push($rbatch, $vnewarr);
                       }
                   }
                   
                   //this will switch all terminals selected on a new server
                   $rresult = $oas->reassignbatchserver($rbatch);
                   if($rresult == 1)
                   {
                       $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                       foreach ($rbatch as $terminals)
                       {
                           $vlogin = $terminals['TerminalCode'];
                           $terminalPassword = $oas->getterminalcredentials2($terminals['TerminalID'], $terminals['OldServiceID']);
                           $vpassword = $terminalPassword['ServicePassword']; //site passcode will be the terminal password
                           
                           //config for RTG
                           $vprovidername = $_POST['txtnewserver'];
                           $login = $vlogin;
                           $password = $vpassword;
                           
                           
                           $country = 'PH';
                           $casinoID = 1;
                           $fname = '';
                           $lname = '';
                           $email = '';
                           $dayphone = '';
                           $evephone = '';
                           $addr1 = '';
                           $addr2 = '';
                           $city = '';
                           $state = '';
                           $zip = '';
                           $ip = '';
                           $mac = '';
                           $userID = 0;
                           $downloadID = 0;
                           $birthdate = '1981-01-01';
                           $clientID = 1;
                           $putInAffPID = 0;
                           $calledFromCasino = 0;
                           $agentID = '';
                           $currentPosition = 0;
                           $thirdPartyPID = '';
                           $alias = '';
                           $sex = '';
                           $fax = '';
                           $occupation = '';
                           
                           // Comment Out CCT 02/06/2018 BEGIN
                           //if provider is MG, then
                           //if(strstr($vprovidername, "RTG") == false)
                           //{
                           //     $_MGCredentials = $_PlayerAPI[$vnewserviceID -1]; 
                           //     list($mgurl, $mgserverID) =  $_MGCredentials;
                           //     $url = $mgurl;
                           //     $hashedPassword = '';
                           //     $aid = $_MicrogamingUserType;
                           //     $currency = $_MicrogamingCurrency;
                           //     $capiusername = $_CAPIUsername;
                           //     $capipassword = $_CAPIPassword;
                           //     $capiplayername = $_CAPIPlayerName;
                           //     $capiserverID = $mgserverID;
                           //}
                           // Comment Out CCT 02/06/2018 END
                           // EDITED CCT 02/06/2018 BEGIN
                           //else
                           if(strstr($vprovidername, "RTG") == true)
                           // EDITED CCT 02/06/2018 END
                           {
                               $url = $_PlayerAPI[$vnewserviceID -1]; 
                               $hashedpass = sha1($vpassword);
                               $hashedPassword = $hashedpass;
                               $aid = 0;
                               $currency = '';
                               $capiusername = '';
                               $capipassword = '';
                               $capiplayername = '';
                               $capiserverID = '';
                           }
                           
                           $vplayerResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, 
                                  $vnewserviceID, $url, $login, $password, $aid, $currency, $email, $fname, 
                                  $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, 
                                  $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
                                  $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, 
                                  $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, 
                                  $capipassword, $capiplayername, $capiserverID);
                       
                           if($vplayerResult['IsSucceed'] == true)
                           {
                               $vStatus = 1;
                           }
                           // Comment Out CCT 02/06/2018 BEGIN
                           //elseif(($vplayerResult['IsSucceed'] == false) && (strstr($vprovidername, "MG") == true))
                           //{
                           //    $vStatus = 6; //if account exists in MG
                           //}
                           // Comment Out CCT 02/06/2018 END
                           else
                           {
                               if($vplayerResult['ErrorID'] == 5)
                                   $vStatus = 5; //if account exists in RTG
                               else
                                   $vStatus = 0;
                           }
                       }
                       
                       
                       //base on RTG Player API DOC 
                       switch ($vStatus)
                       {
                          //if failed in RTG
                          case 0:
                              $msg = $vplayerResult['ErrorMessage'];
                          break;
                          case 1:
                              $msg = "RTG Server: successfully changed";
                          break;
                          //Account already exists in RTG but this must be created in Kronus
                          case 5:
                              $msg = "RTG Server: successfully changed";
                          break;
                          //Account already exists in MG but this must be created in Kronus
                          // Comment Out CCT 02/06/2018 BEGIN
                          //case 6:
                          //    $msg = "MG Server: successfully changed";
                          //break;
                          // Comment Out CCT 02/06/2018 END
                          default:
                              $msg = "RTG Server : Error in creating service terminal account";
                          break;
                       }
                       
                       //insert into audit trail
                       $arrtermcode = implode(",", $varrterminalcode);
                       $vtransdetails = "terminal code ".$arrtermcode." ;old server ".$_POST['txtoldserver']." ;new server ".$_POST['txtnewserver'];
                       $vauditfuncID = 44;
                       $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                       
                       $oas->close();
                       header("Location: appswitchserver.php?mess=".$msg);
                   }
                   elseif($rresult == 2){
                       $msg = "Re-Assign Server: Provider was successfully re-assigned";
                       $oas->close();
                       header("Location: appswitchserver.php?mess=".$msg);
                   }
                   else
                   {
                       $msg = "Re-Assign Server: error on re-assigning";
                       $oas->close();
                       header("Location: appswitchserver.php?mess=".$msg);
                   }
                   unset($rbatch, $rterminals);
               }
               else 
               {
                   $msg = "Re-Assign Server: Invalid Field";
                   $oas->close();
                   header("Location: appswitchserver.php?mess=".$msg);
               }
               
//               $_SESSION['mess'] = $msg;
//               
//               header("Location: appswitchserver.php");
            break;
            case 'RemoveServer':
               if(isset($_POST['cmbterminal']) && (isset($_POST['cmbnewservice'])) && (isset($_POST['txtremarks'])))
               { 
                   $vsiteID = $_POST['cmbsite'];
                   $vserviceID = $_POST['cmbnewservice'];
                   $varrterminalcode = array($_POST['txtterminalcode']);
                   $vremarks = trim($_POST['txtremarks']);
                   
                   $rsitecode = $oas->getsitecode($vsiteID); //get the sitecode first
                   $vsitecode = $rsitecode['SiteCode'];
                   
                   //get terminalID for regular and vip terminal
                   $rterminals = $oas->getterminalID($varrterminalcode, $vsiteID, $vsitecode); 
                   
                   //store terminalID in the array
                   $arrterminalID = array();
                   foreach ($rterminals as $value)
                   {
                       foreach($value as $row)
                       {
                           $vterminalID = $row['TerminalID'];
                           $vnewarr = array($vterminalID);
                           array_push($arrterminalID, $vnewarr);
                       }
                   }
                   
                   $rresult = $oas->removeservice($arrterminalID, $vserviceID, $vremarks);
                   if($rresult > 0)
                   {
                       $msg = "Casino Server successfully removed";
                        //insert into audit trail
                       $arrtermcode = implode(",", $varrterminalcode);
                       $vtransdetails = "terminal code ".$arrtermcode." ;from ".$_POST['txtoldserver'];
                       $vauditfuncID = 45;
                       $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                   }
                   else
                   {
                       $msg = "Error on removing the server";
                   }
                   unset($arrterminalID);
               }
               else 
               {
                   $msg = "Remove Server: Invalid Field";
               }
               $_SESSION['mess'] = $msg;
               $oas->close();
               header("Location: ../appremoveserver.php");
            break;
            //get the terminal by server (appswitchserver.php)
            case 'GetTerminals':
                $vsiteID = $_POST['SiteID'];
                $vserviceID = $_POST['ServiceID'];
                $rsitecode = $oas->getsitecode($vsiteID); //get the sitecode first
                $vresults = $oas->getterminalbyserverID($vsiteID, $vserviceID);
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
                    echo "No Terminal assigned to this provider";
                }
                $oas->close();
                exit;
            break;
            //POST history details upon click of service history ID on the Transaction LOGs(Ecity) grid
            case 'GetTransferHistory':
                $vshistoryID = $_POST['shistory'];
                $rhistory = $oas->gethistorydetails($vshistoryID);
                $vhistarr = array();
                foreach($rhistory as $row)
                {
                    $vservicehistid = $row['ServiceTransferHistoryID'];
                    $vthtermid = $row["TerminalID"];
                    $vthamount = $row["Amount"];
                    $vthfromcasino = $row["FromServiceID"];
                    $vthtocasino = $row["ToServiceID"];
                    $vthstat = $row ["Status"];
                    switch($vthstat)
                    {
                       case 0: $vnewstat = 'Failed';break;
                       case 1: $vnewstat = 'Successful';break;
                    }
                    $vthnewval = array("ServiceTransferHistoryID" => $vservicehistid,"TerminalID" => $vthtermid,"Amount" => $vthamount,"FromServiceID" => $vthfromcasino,"ToServiceID" => $vthtocasino,"Status" => $vnewstat);
                    array_push($vhistarr, $vthnewval);
                }                
                echo json_encode($vhistarr);
                unset($vhistarr,$rhistory,$vservicehistid,$vthtermid,$vthamount,$vthfromcasino,$vthtocasino,$vthstat,$vthnewval);
                $oas->close();
                exit;
            break;
            //POST providers / service upon loading of page (E-city transaction logs)
            case 'GetProviders':
                $rproviders = $oas->getallservices("ServiceName");
                echo json_encode($rproviders);
                $oas->close();
                exit;
            break;
            //Get log files upon loading of page
            case 'GetLogFile':
                $vrealfolder = $oas->getlogspath($cashierlogpath);
                if(is_dir($vrealfolder))
                {
                    $listfiles  = scandir($vrealfolder);
                    $vfiles = array();
                    foreach($listfiles as $file)
                    {
                        //hides (.), (..), (index), and temporary files upon viewing
                        if(($file != '..') && ($file != '.') && (strstr($file, "index") == false) && 
                                (strstr($file, "tmp") == false) && (strstr($file, "dev_application") == false))
                        {
                            $newarr = array(substr($file, 0, strrpos($file, ".")));
                            array_push($vfiles, $newarr);
                        }
                    }
                    arsort($vfiles); //arrange files by ascending
                    echo json_encode($vfiles);
                    unset($vfiles);
                    unset($listfiles);
                }
                else
                {
                    echo "The logs directory does not exist";
                }
                $oas->close();
                exit;
            break;
            //show log's content upon clicking of file
            case 'ShowLogContent':
                $vrealfolder = $oas->getlogspath($cashierlogpath);
                $vfile = $_POST['logfile']; 
                $vfullpath = $vrealfolder.$vfile.".log";
                $vdatenow = date("Y-m-d");
                //check first if file exists
                if(file_exists($vfullpath))   
                {
                    //then check if file is not empty
                    if(filesize($vfullpath) > 0)
                    {
                        $datemodified = date("Y-m-d", filemtime($vfullpath)); //get file modification/creation date
                        //check if date today is the same with date modification of file, then create temp file
                        if($vdatenow == $datemodified)
                        {
                            $tmpfile = $vrealfolder."tmp".$vdatenow.".log";
                            //validate if temp file was exists
                            if(file_exists($tmpfile) == true)
                            {
                                unlink($tmpfile); //removes the temp file if exists
                                file_put_contents($tmpfile, file_get_contents($vfullpath), FILE_APPEND | LOCK_EX); //re-create the temp file
                                $rcontent = $oas->getfilecontents($tmpfile);
                            }
                            else
                            {
                                file_put_contents($tmpfile, file_get_contents($vfullpath), FILE_APPEND | LOCK_EX); //create the temp file
                                $rcontent = $oas->getfilecontents($tmpfile); //get contents
                            }
                        }
                        else
                        {
                            $rcontent = $oas->getfilecontents($vfullpath);
                        }
                    }
                    else
                    {
                       $rcontent = "";
                    }
                    echo json_encode($rcontent);
                }   
                else   
                {  
                    $errmsg->error = "Log file does not exists";
                    echo json_encode($errmsg);
                } 
                exit;
            break;
            //Get log's content by modification date (onselect of datepicker)
            case 'GetContentByModDate':
                $vrealfolder = $oas->getlogspath($cashierlogpath);
                $listfiles  = scandir($vrealfolder);
                $vdate = $_POST['logfile'];
                $vfiles = array();
                $vdatenow = date("Y-m-d");
                $rcontent = "";
                //loop throughout the directory
                foreach($listfiles as $file)
                {
                    //hides (.), (..), (.svn), and temporary files upon viewing
                    if(($file != '..') && ($file != '.') && (strstr($file, "index") == false) && (strstr($file, "tmp") == false))
                    {
                        $datemodified = date("Y-m-d", filemtime($vrealfolder.$file)); //get modification date of each file
                        //is date today the same with date selected
                        if($vdate == $vdatenow)
                        {
                            //is date selected the same with date modification of a certain file
                            if($vdate == $datemodified)
                            {
                                $vfullpath = $vrealfolder.$file; //store the file into a variable
                                //check if the file is empty
                                if(filesize($vfullpath) > 0)
                                {
                                    $tmpfile = $vrealfolder."tmp".$vdatenow.".log";
                                    //validate if temp file was exists
                                    if(file_exists($tmpfile) == true)
                                    {                        
                                        unlink($tmpfile); //removes the temp file if exists
                                        file_put_contents($tmpfile, file_get_contents($vfullpath), FILE_APPEND | LOCK_EX); //re-create the temp file
                                        $rcontent = $oas->getfilecontents($tmpfile);
                                    }
                                    else
                                    {
                                        file_put_contents($tmpfile, file_get_contents($vfullpath), FILE_APPEND | LOCK_EX); //create the temp file
                                        $rcontent = $oas->getfilecontents($tmpfile); //get contents
                                    }
                                }
                                else
                                {
                                   $rcontent = "";
                                }
                            }
                        }
                        else
                        {
                            //check if date selected same with the modification date of a file
                            if($vdate == $datemodified)
                            {
                                $vfullpath = $vrealfolder.$file; //store to a variable
                                //check if file is empty
                                if(filesize($vfullpath) > 0)
                                {
                                    $rcontent = $oas->getfilecontents($vfullpath);
                                }
                                else
                                {
                                   $rcontent = "";
                                }
                            }
                        }
                    }
                }
                echo json_encode($rcontent);
                exit;
            break;
            //get the number of cashier machine count per site
            case 'CashierMachineCount':
                $vsiteID = $_POST['siteid'];
                $vcashiercount = $oas->countcashiermachine($vsiteID);
                echo json_encode($vcashiercount);
                exit;
            break;
            case 'AddCashierMachine':
                if(isset($_POST['cmbsite']) && (isset($_POST['txtaddcashier'])))
                {
                    $vsiteID = $_POST['cmbsite'];
                    $vsitecode = $_POST['txtsitecode'];
                    $vtotalcount = (int)$_POST['txtcurrent'];
                    $vaddcount = trim($_POST['txtaddcashier']);
                    $vaddcashier = $vtotalcount + (int)$vaddcount;
                    $vaid = $aid;
                    //validate if no record of cashier machine count; then insert records
                    if($vtotalcount > 0)
                    {
                        $rcashierterminal = $oas->updatecashiercount($vaddcashier, $vsiteID, $vaid);
                        if($rcashierterminal > 0)
                        {
                            $msg = "AddCashierMachine: Success on updating the cashier terminal count";
                            $vtransdetails = "Site Code ".$vsitecode." ;cashier terminal added = ".$vaddcount." ;Previous number of cashier = ".$vtotalcount;
                            $vauditfuncID = 56;
                            $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        }
                        else
                        {
                            $msg = "AddCashierMachine: Error on updating the cashier terminal count";
                        }
                    }
                    else
                    {
                        $vcashiercount = $oas->insertmachinecount($vsiteID, $vaid);
                        if($vcashiercount > 0)
                        {
                            $msg = "AddCashierMachine: Success on updating the cashier terminal count";
                            $vtransdetails = "Site Code ".$vsitecode." ;cashier terminal added = ".$vaddcount." ;Previous number of cashier = ".$vtotalcount;
                            $vauditfuncID = 56;
                            $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        }
                        else
                        {
                            $msg = "AddCashierMachine: Error on updating the cashier terminal count";
                        }
                    }
                }
                else
                {
                    $msg = "AddCashierMachine: Invalid Fields";
                }
                $_SESSION['mess'] = $msg;
                $oas->close();
                header("Location: ../appadjustterminal.php");
            break;
            case 'RTGServers':
                $rservice = array();
                $rservice = $oas->getallservices("ServiceName");
                $rservices = array();
                $vservicename = $_POST['servicename'];
                //verify if RTG Server
                if(strstr($vservicename, "RTG")){
                    $key = "RTG";
                }
                // Comment Out CCT 02/06/2018 BEGIN
                //verify if MG Server
                //if(strstr($vservicename, "MG")){
                //    $key = "MG";
                //}
                //verify if Playtech 
                //if(strstr($vservicename, "PT")){
                //    $key = "PT";
                //}
                // Comment Out CCT 02/06/2018 END
                foreach($rservice as $row)
                {
                    $rserverID = $row['ServiceID'];
                    $rservername = $row['ServiceName'];

                    if(strstr($rservername, $key))
                    {
                       $newarr = array('ServiceID' => $rserverID, 'ServiceName' => $rservername);
                       array_push($rservices, $newarr);  
                    }
                }
                echo json_encode($rservices);
                $oas->close();
                exit;
                unset($rservices);
                unset($rservice);
            break;
            case 'DisableTerminal':
                if(isset($_POST['txtremarks']))
                {
                    if(strlen($_POST['txtremarks']) > 0)
                    {
                        $vremarks = $_POST['txtremarks'];
                        $vcshmacID = $_POST['txtmacid'];
                        $isdisabled = $oas->disableterminal($vcshmacID, $vremarks);
                        //check if terminal was alreay disable or not
                        if($isdisabled > 0)
                        {
                            $msg = "Cashier Terminal was successfully disabled.";
                            //audit trail
                            $vtransdetails = "CashierMacID ".$vcshmacID;
                            $vauditfuncID = 43;
                            $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        }
                        else
                        {
                            $msg = "Terminal was already disabled";
                        }
                    }
                }
                $_SESSION['mess'] = $msg;
                $oas->close();
                header("Location: ../appdisableterminal.php");
            break;
            case 'ChangeTerminalPassword':
                if(isset($_POST['txttcode']) && isset($_POST['txtoldpwd']) 
                        && isset($_POST['txtnewpwd']) && isset($_POST['cmbnewservice']))
                {
                    if((strlen($_POST['txttcode']) > 0)&& (strlen($_POST['txtoldpwd']) > 0) && (strlen($_POST['txtnewpwd']) > 0))
                    {
                        $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                        
                        $vlogin = $_POST['txttcode'];
                        $voldpassword = $_POST['txtoldpwd'];
                        $vnewpassword = $_POST['txtnewpwd'];
                        $vcasinoID = 1; //always 1, based on RTG docs
                        $vserviceID = (int)$_POST['cmbnewservice'];
                        $vterminalID = $_POST['txtterminalID'];
                        $vprovidername = $_POST['txtservice'];
                        
                        // Comment Out CCT 02/06/2018 BEGIN
                        //if provider is MG, then
                        //if (strstr($vprovidername, "RTG") == false) {
                        //    $_MGCredentials = $_PlayerAPI[$vserviceID -1]; 
                        //    list($mgurl, $mgserverID) =  $_MGCredentials;
                        //    $url = $mgurl;
                        //    $aid = $_MicrogamingUserType;
                        //    $currency = $_MicrogamingCurrency;
                        //    $capiusername = $_CAPIUsername;
                        //    $capipassword = $_CAPIPassword;
                        //    $capiplayername = $_CAPIPlayerName;
                        //    $capiserverID = $mgserverID;
                        //} 
                        // Comment Out CCT 02/06/2018 END
                        // EDITED CCT 02/06/2018 BEGIN
                        //else 
                        if (strstr($vprovidername, "RTG") == true) 
                        // EDITED CCT 02/06/2018 END    
                        {
                            $url = $_PlayerAPI[$vserviceID -1];
                            $cashierurl = $_ServiceAPI[$vserviceID-1];
                            $aid = 0;
                            $currency = '';
                            $capiusername = '';
                            $capipassword = '';
                            $capiplayername = '';
                            $capiserverID = '';
                        }
                        
                        $changePwdResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, 
                                           $vserviceID, $url, $vcasinoID, $vlogin, $voldpassword, $vnewpassword, 
                                           $capiusername, $capipassword, $capiplayername, $capiserverID);
                       
                        if($changePwdResult['IsSucceed'] == true)
                        {
                            $isupdated = $oas->updateterminalpwd($vnewpassword, $vterminalID, $vserviceID);
                            if($isupdated > 0){
                                $msg = "Terminal password successfully updated";
                                $vtransdetails = "Terminal Code ".$vlogin."; Old Password ".$voldpassword."; New Password ".$vnewpassword;
                                $vauditfuncID = 60;
                                $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                            }
                            else
                                $msg = "Terminal password did not update";
                        }
                        else
                        {
                            //check if result is 6 (Old login/password do not match)
                            if($changePwdResult['ErrorCode'] == '6'){
                                $isupdated = $oas->updateterminalpwd($vnewpassword, $vterminalID, $vserviceID);
                                if($isupdated > 0){
                                    $msg = "Terminal password successfully updated";
                                    $vtransdetails = "Terminal Code ".$vlogin."; Old Password ".$voldpassword."; New Password ".$vnewpassword;
                                    $vauditfuncID = 60;
                                    $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                                }
                                else
                                    $msg = "Terminal password did not update";
                            }else{
                                $msg = $changePwdResult['ErrorMessage'];
                            }
                        }
                    }
                    else
                    {
                        $msg = "Invalid fields";
                    }
                }
                else
                {
                    $msg = "Invalid fields";
                }
                $_SESSION['mess'] = $msg;
                $oas->close();
                header("Location: ../appterminalpwd.php");
            break;
            case 'ViewTerminalPassword':
                if(isset ($_POST['terminalCode']))
                {
                    $vterminalCode = $_POST['terminalCode'];
                    $terminals = $oas->viewTerminalID($vterminalCode);
                    if($terminals)
                        echo json_encode($terminals);
                    else
                        echo 'Invalid terminal code';
                }
                else
                        echo 'Invalid terminal code';
                $oas->close();
                exit;
            break;
            case 'GetTerminalCredentials':
                if(isset ($_POST['terminalID']) && isset($_POST['serviceID']))
                {
                    $vterminalID = $_POST['terminalID'];
                    $vserviceID = $_POST['serviceID'];
                    $terminals = $oas->getterminalcredentials($vterminalID, $vserviceID);
                    if($terminals)
                        echo json_encode($terminals);
                    else
                        echo 'Invalid terminal code';
                }
                $oas->close();
                exit;
            break;
            default :
                $msg = "Page not found";
                $_SESSION['mess'] = $msg;
                $oas->close();
                header("Location: ../blank.php");
            break;
        }
    }   
    //this was used in transaction tracking
    if(isset($_POST['sendSiteID']))
    {
        $vsiteID = $_POST['sendSiteID'];
        if($vsiteID <> "-1")
        {
            
            $rsitecode = $oas->getsitecode($vsiteID); //get the sitecode first
            $vresults = array();
            //get all terminals
            $vresults = $oas->viewterminals($vsiteID);
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
        $oas->close();
        exit;
    }
    //this was used on removing the terminal
    elseif(isset($_POST['sendSiteID2']))
    {
        $vsiteID = $_POST['sendSiteID2'];
        $rsitecode = $oas->getsitecode($vsiteID); //get the sitecode first
        $vresults = array();
        //get all terminals
        $vresults = $oas->getterminals($vsiteID);  
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
        $oas->close();
        exit;
    }
    
    elseif(isset($_POST['cashiersiteID']))
    {
        $vcashiersiteID = $_POST['cashiersiteID'];
        $vresults = array();
        $vresults = $oas->getcashierpersite($vcashiersiteID);       
        echo json_encode($vresults);        
        unset($vresults);
        $oas->close();
        exit;
    }
    elseif(isset($_POST['cashierpasskey']))
    {
        $vcashierpasskey = $_POST['cashierpasskey'];
        $vresults = array();
        $vresults = $oas->getcashierpasskey($vcashierpasskey); //get passkey tagging for particular cashier
        echo json_encode($vresults);        
        unset($vresults);
        $oas->close();
        exit;
    }
    
    elseif(isset ($_GET['cmbterminal']))
    {
        $vterminalID = $_GET['cmbterminal'];
        $rresult = array();
        $rresult = $oas->getterminalname($vterminalID);
        $vterminalName->TerminalName = $rresult['TerminalName'];
        echo json_encode($vterminalName);
        unset($rresult);
        $oas->close();
        exit;
    }
    //for displaying site name on label
    elseif(isset($_POST['cmbsitename']))
    {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $oas->getsitename($vsiteID);
        foreach($rresult as $row)
        {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
        }
        if(count($rresult) > 0)
        {
            $vsiteName->SiteName = $rsitename;
            $vsiteName->POSAccNo = $rposaccno;
        }
        else
        {
            $vsiteName->SiteName = "";
            $vsiteName->POSAccNo = "";
        }
        echo json_encode($vsiteName);
        unset($rresult);
        $oas->close();
        exit;
    }
    //for displaying site credentials (Passcode, SiteCode)
    elseif(isset($_POST['sitecredentials']))
    {
        $vsiteID = $_POST['sitecredentials'];
        $rresult = array();
        $rresult = $oas->getsitecredentials($vsiteID);
        foreach($rresult as $row)
        {
            $rsitename = $row['SiteName'];
            $rsitecode = $row['SiteCode'];
            $rpasscode = $row['PassCode'];
        }
        if(count($rresult) > 0)
        {
            $vsiteName->SiteName = $rsitename;
            $vsiteName->SiteCode = $rsitecode;
            $vsiteName->PassCode = $rpasscode;
        }
        else
        {
            $vsiteName->SiteName = "";
            $vsiteName->SiteCode = "";
            $vsiteName->PassCode = "";
        }
        echo json_encode($vsiteName);
        unset($rresult);
        $oas->close();
        exit;
    }
    //this will get the server per terminal (appremoveserver.php)
    elseif(isset($_POST['terminalserver']))
    {
        $terminalID = $_POST['terminalserver'];
        $rresult = array();
        $rresult = $oas->getterminalprovider($terminalID);
        if(count($rresult) > 0)
        {
            echo json_encode($rresult);
        }
        else
        {
            echo "No Provider Found";
        }
        unset($rresult);
        $oas->close();
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