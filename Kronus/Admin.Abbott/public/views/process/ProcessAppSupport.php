<?php

/*
 * Created By: Lea Tuazon
 * Date Created : June 8, 2011
 *
 * Modified By: Edson L. Perez
 */

include __DIR__."/../sys/class/ApplicationSupport.class.php";
include __DIR__."/../sys/class/LoyaltyUBWrapper.class.php";
require __DIR__.'/../sys/core/init.php';
include __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
include __DIR__.'/../sys/class/CasinoGamingCAPIUB.class.php';
include __DIR__.'/../sys/class/helper.class.php';
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

$oas= new ApplicationSupport($_DBConnectionString[0]);
$loyalty= new LoyaltyUBWrapper();
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
      if($oas->isAjaxRequest())
       {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
       }
       header("Location: login.php?mess=".$msg);
   }
   else
   {
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
   }    
/********** END SESSION CHECKING **********/   
   
   //checks if account was locked 
//   $islocked = $oas->chkLoginAttempts($aid);
//   if(isset($islocked['LoginAttempts'])){
//      $loginattempts = $islocked['LoginAttempts'];
//      if($loginattempts >= 3){
//          $oas->deletesession($aid);
//          session_destroy();
//          $msg = "Not Connected";
//          $oas->close();
//          header("Location: login.php?mess=".$msg);
//          exit;
//      }
//   }
   
 
    if(isset($_POST['page']))
    {
       $vpage = $_POST['page'];
       switch($vpage)
       {     
        //for casino services dropdown retreived from casino array   
         case "GetServices":
             $cardnumber = $_POST['txtcardnumber'];
             
             if(strlen($cardnumber) > 0) {
             
                    $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);

                    $obj_result = json_decode($loyaltyResult);

                    $statuscode = $obj_result->CardInfo->StatusCode;

                    //validate if membership card is invalid
                    if($statuscode == 1 || $statuscode == 5 || $statuscode == 9){
                        
                        $casino = $obj_result->CardInfo->CasinoArray;
                        $casinoarray_count = count($casino);

                        $casinos = array();
                        $casinoz = array();
                        if($casinoarray_count != 0)
                            for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {
                                $casinos = 
                                              array('ServiceUsername' => $casino[$ctr]->ServiceUsername,
                                                    'ServicePassword' => $casino[$ctr]->ServicePassword,
                                                    'HashedServicePassword' => $casino[$ctr]->HashedServicePassword,
                                                    'ServiceID' => $casino[$ctr]->ServiceID,
                                                    'UserMode' => $casino[$ctr]->UserMode,
                                                    'isVIP' => $casino[$ctr]->isVIP,
                                                    'Status' => $casino[$ctr]->Status 
                                );

                                array_push($casinoz, $casinos);
                        
                            }
                            $value2 = $oas->loopAndFindService($casinoz, 'ServiceID');
                            
                            
                        if(empty($casino) || empty($value2)){
                            echo "Reset Casino Account User Based: No Services Assigned";
                        }
                        else
                        {          
                            $service = implode(", ", $value2);

                            $vresults = $oas->getServices($service);

                            $services = array();
                            foreach($vresults as $row)
                            {
                                $rterminalID = $row['ServiceID'];
                                $rterminalCode = $row['ServiceName'];

                                //create a new array to populate the combobox
                                $newvalue = array("ServiceID" => $rterminalID, "ServiceName" => $rterminalCode);
                                array_push($services, $newvalue);
                            }
                            echo json_encode($services);   
                         } 
                       
                    }
                    else
                    {
                      $statusmsg = $oas->membershipcardStatus($statuscode);
                      echo "Reset Casino Account User Based: ".$statusmsg;
                 }
             }    
             else{
                 echo "Reset Casino Account User Based: Invalid input detected.";
             }
                    
         
             unset($vresults, $services, $newvalue, $value2, $casino);
                
             $oas->close();
             exit;
          break;   
          
         //show loyalty card information on a pop up box  
         case "GetLoyaltyCard":
             
                //validate if card number field was empty
                $cardnumber = $_POST['txtcardnumber'];
                
                if(strlen($cardnumber) > 0) {
                    $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
                
                    $obj_result = json_decode($loyaltyResult);

                    $statuscode = $obj_result->CardInfo->StatusCode;

                    if(!is_null($statuscode))
                    {
                       //allow active memeebership card and active temp account
                        if($statuscode == 1 || $statuscode == 5 || $statuscode == 9){

                            $casinoarray_count = count($obj_result->CardInfo->CasinoArray);

                            $casinoinfo2 = array();
                            if($casinoarray_count != 0){
                                for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {   
                                    $service = $oas->getServices($obj_result->CardInfo->CasinoArray[$ctr]->ServiceID);
                                    foreach ($service as $value) {
                                        $casinoname = $value['ServiceName'];
                                    }

                                    $casinoinfo = 
                                                array(
                                                  'UserName'  => $obj_result->CardInfo->MemberName,
                                                  'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                                  'Email'  => $obj_result->CardInfo->Email,
                                                  'Birthdate' => $obj_result->CardInfo->Birthdate,
                                                  'Casino' => $casinoname,
                                                  'Login' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                                                  'CardNumber' => $obj_result->CardInfo->Username,
                                                  'StatusCode' => $obj_result->CardInfo->StatusCode,  
                                                
                                    );
                                    
                                    array_push($casinoinfo2, $casinoinfo);

                                    $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                                    $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                                    
                                    
                                }
                                echo json_encode($casinoinfo2);
                          }
                          else 
                          {
                              $services = "Reset Casino Account: Casino is empty";
                              echo "$services";
                          }  
 
                        } else {
                           $statusmsg = $oas->membershipcardStatus($statuscode);
                           echo "Reset Casino Account: ".$statusmsg;
                        } 
                    }
                    else
                    {
                        $statuscode = 100;
                        $statusmsg = $oas->membershipcardStatus($statuscode);
                           echo "Reset Casino Account: ".$statusmsg;
                    }    
                    
                } else {
                    echo "Reset Casino Account: Invalid input detected";
                }
                
                unset($loyaltyResult, $casino);
                $oas->close();
                exit;
          break;
          
          
           //show loyalty card information on a pop up box  
         case "GetLoyaltyCard2":
             
                //validate if card number field was empty
                $cardnumber = $_POST['txtcardnumber'];
                
                if(strlen($cardnumber) > 0) {
                    $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
                
                    $obj_result = json_decode($loyaltyResult);

                    $statuscode = $obj_result->CardInfo->StatusCode;

                    if(!is_null($statuscode))
                    {
                       //allow active memeebership card and active temp account
                        if($statuscode == 1 || $statuscode == 5 || $statuscode == 9){

                            $casinoarray_count = count($obj_result->CardInfo->CasinoArray);

                            $casinoinfo2 = array();
                            if($casinoarray_count != 0){
                                for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {   
                                    $service = $oas->getServices($obj_result->CardInfo->CasinoArray[$ctr]->ServiceID);
                                    foreach ($service as $value) {
                                        $casinoname = $value['ServiceName'];
                                    }

                                    $casinoinfo = 
                                                array(
                                                  'UserName'  => $obj_result->CardInfo->MemberName,
                                                  'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                                  'Email'  => $obj_result->CardInfo->Email,
                                                  'Birthdate' => $obj_result->CardInfo->Birthdate,
                                                  'Casino' => $casinoname,
                                                  'Login' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                                                  'CardNumber' => $obj_result->CardInfo->Username,
                                                  'StatusCode' => $obj_result->CardInfo->StatusCode,  
                                                
                                    );
                                    
                                    array_push($casinoinfo2, $casinoinfo);

                                    $_SESSION['CasinoArray'] = $obj_result->CardInfo->CasinoArray;
                                    $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                                    
                                    
                                }
                                echo json_encode($casinoinfo2);
                          }
                          else 
                          {
                              $services = "UB Transaction Tracking: Casino is empty";
                              echo "$services";
                          }  
 
                        } else {
                            if($statuscode == 8){
                                echo json_encode($statuscode);
                            }
                            else
                            {
                                $statusmsg = $oas->membershipcardStatus($statuscode);
                           echo "UB Transaction Tracking: ".$statusmsg;
                            }    
                           
                        } 
                    }
                    else
                    {
                        $statuscode = 100;
                        $statusmsg = $oas->membershipcardStatus($statuscode);
                           echo "UB Transaction Tracking: ".$statusmsg;
                    }    
                    
                } else {
                    echo "UB Transaction Tracking: Invalid input detected";
                }
                
                unset($loyaltyResult, $casino);
                $oas->close();
                exit;
          break;  
          
        
       }
   }
    if(isset($_POST['paginate']))
    {
        $vpaginate = $_POST['paginate'];
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $direction = $_POST['sord']; // get the direction
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
                                $results2 = $oas->getsitecode($vSiteID);
                                $results2 = $results2['SiteCode'];
                                $results = preg_split("/$results2/", $vview['TerminalCode']);
                                
                                $responce->rows[$i]['id']=$vview['TransactionDetailsID'];
                                $responce->rows[$i]['cell']=array($vview['TransactionReferenceID'],$results[1],$vtranstype,$vview['ServiceTransactionID'], number_format($vview['Amount'],2),$vview['DateCreated'],$vstatus, $vview['Name']);
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
            
            //view transaction logs for user based transactions
            case 'ViewSupportUB':
                if(isset ($_POST['cmbsource']) && isset ($_POST['txtDate1']) && isset ($_POST['txtDate2']) 
                 && isset($_POST['cmbstatus']))
                 {
                        $vSource = $_POST['cmbsource'];
                        $vCardNum = $_POST['txtcardnumber'];
                        $vdate1 = $_POST['txtDate1'];
                        $vdate2 = $_POST['txtDate2'];
                        $vFrom = $vdate1;
                        $vTo = $vdate2;
                        $vtransstatus = $_POST['cmbstatus'];
                        $vtranstype = $_POST['cmbtranstype'];
                        
                        switch ($vSource){
                            //if source is Cashier
                            case 1:
                                //get total number of transactiondetails for cashier source
                                $rcount = $oas->countcashierTranslogs($vCardNum, $vtransstatus, $vtranstype, $vFrom,$vTo); 

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
                                //select transactiondetails for cashier source
                                $result = $oas->getcashierTranslogs($vCardNum, $vtransstatus, $vtranstype, $vFrom,$vTo, $start, $limit);

                                if(!empty($result)){ 

                                        if(count($result) > 0)
                                        {
                                            $i = 0;
                                            $responce->page = $page;
                                            $responce->total = $total_pages;
                                            $responce->records = $count;  
                                            
                                            foreach($result as $vview)
                                            {
                                                $transrefid = $vview['TransactionReferenceID'];
                                                //get cashier username
                                                $user = $oas->getCashierUsername($vFrom, $vTo, $transrefid, $vCardNum);

                                               switch( $vview['Status'])
                                               {
                                                   case 0: $vstatus = 'Pending';break;
                                                   case 1: $vstatus = 'Successful';    break;
                                                   case 2: $vstatus = 'Failed';break;
                                                   case 3: $vstatus = 'Fulfillment Approved'; break;   
                                                   case 4: $vstatus = 'Fulfillment Denied';  break;
                                                   default: $vstatus = 'All'; break;
                                               } 

                                               switch($vview['TransactionType'])
                                               {
                                                  case 'D': $vtranstype = 'Deposit';break;
                                                  case 'W': $vtranstype = 'Withdrawal';break;
                                                  case 'R': $vtranstype = 'Reload';break;
                                                  case 'RD': $vtranstype = 'Redeposit';break;
                                               }

                                               list($sites, $sitecodez) = split("-", $vview['SiteCode']);
                                               $results = substr($vview['TerminalCode'], strlen($vview['SiteCode']));
                                               $responce->rows[$i]['id']=$vview['TransactionReferenceID'];
                                               $responce->rows[$i]['cell']=array($sitecodez,$results,$vview['ServiceName'],$vtranstype,$vview['ServiceTransactionID'], 
                                                   number_format($vview['Amount'],2),$vview['StartDate'],$vview['EndDate'],$vstatus, $user);
                                               $i++;

                                            }
                                        }
                                }
                                else
                                {
                                     $i = 0;
                                     $responce->page = $page;
                                     $responce->total = $total_pages;
                                     $responce->records = $count;
                                     $msg = "User Based Transaction Trancking: No returned result";
                                     $responce->msg = $msg;
                                }
                            break;
                            //if source is launchpad
                            case 2:
                                //get total number of transactionrequestlogslp transaction for launchpad source
                                $rcount = $oas->countlptranslogsLP($vCardNum, $vtransstatus, $vtranstype, $vFrom,$vTo); 

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
                                //select transactionrequestlogslp transaction for launchpad source
                                $result = $oas->getlptranslogsLP($vCardNum, $vtransstatus, $vtranstype, $vFrom,$vTo, $start, $limit);

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
                                           case 3: $vstatus = 'Fulfillment Approved'; break;   
                                           case 4: $vstatus = 'Fulfillment Denied';  break;
                                           default: $vstatus = 'All'; break;
                                       } 

                                       switch($vview['TransactionType'])
                                       {
                                          case 'D': $vtranstype = 'Deposit';break;
                                          case 'W': $vtranstype = 'Withdrawal';break;
                                          case 'R': $vtranstype = 'Reload';break;
                                          case 'RD': $vtranstype = 'Redeposit';break;
                                       }               
                                       list($site, $sitecode) = split("-", $vview['SiteCode']);
                                       preg_match("/\d.*/", $vview['TerminalCode'], $results);
                                       $results = substr($vview['TerminalCode'], strlen($vview['SiteCode']));
                                       $responce->rows[$i]['id']=$vview['TransactionReferenceID'];
                                       $responce->rows[$i]['cell']=array($sitecode,$results,$vview['ServiceName'],$vtranstype,$vview['ServiceTransactionID'], 
                                           number_format($vview['Amount'],2),$vview['StartDate'], $vview['EndDate'],$vstatus);
                                       $i++;
                                    }
                                }
                                else
                                {
                                     $i = 0;
                                     $responce->page = $page;
                                     $responce->total = $total_pages;
                                     $responce->records = $count;
                                     $msg = "User Based Transaction Trancking: No returned result";
                                     $responce->msg = $msg;
                                }
                            break;
                            //for manual redemption
                            case 3:
                                //get total number of transactions for manual redemptions source
                                $rcount = $oas->countmanualredemptionsub($vCardNum, $vtransstatus, $vFrom,$vTo); 

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
                                //select manualredemptions transaction for manual redemption source
                                $result = $oas->selectmanualredemptionsub($vCardNum, $vtransstatus, $vFrom,$vTo, $start, $limit);

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
                                           default: $vstatus = 'All'; break;
                                       } 

                                       list($site, $sitecode) = split("-", $vview['SiteCode']);
                                       $results = substr($vview['TerminalCode'], strlen($vview['SiteCode']));
                                       $responce->rows[$i]['id']=$vview['ManualRedemptionsID'];
                                       $responce->rows[$i]['cell']=array($sitecode,$results,$vview['ServiceName'],$vview['TransactionID'], 
                                           number_format($vview['ReportedAmount'],2),$vview['TransactionDate'],$vstatus);
                                       $i++;
                                    }
                                }
                                else
                                {
                                     $i = 0;
                                     $responce->page = $page;
                                     $responce->total = $total_pages;
                                     $responce->records = $count;
                                     $msg = "User Based Transaction Trancking: No returned result";
                                     $responce->msg = $msg;
                                }
                            break;
                            default:
                            echo "Error: Invalid Source!";
                        }
                        
                        echo json_encode($responce);
                        unset($result);
                        unset($responce);
                        $oas->close();
                        exit;
              }
            break;
            
            case 'MCFHistory':
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
                        $vFrom = $vFrom.' 06;00:00';
                        $vTo = $vTo.' 06;00:00';
                        
                        $rcount = $oas->countfulfillmenthistroy($vSiteID,$vTerminalID,$vtransstatus, $vFrom,$vTo); 

                        $count = $rcount['Count'];

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
                        $result = $oas->getfulfillmenthistroy($vSiteID,$vTerminalID,$vtransstatus, $vFrom,$vTo, $start, $limit);  

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
                                    case 3: $vstatus = 'Fulfillment Approved';break;
                                    case 4: $vstatus = 'Fulfillment Denied'; break;   
                                    default: $vstatus = 'All'; break;
                                } 

                                switch($vview['TransactionType'])
                                {
                                   case 'D': $vtranstype = 'Deposit';break;
                                   case 'W': $vtranstype = 'Withdrawal';break;
                                   case 'R': $vtranstype = 'Reload';break;
                                   case 'RD': $vtranstype = 'Redeposit';break;
                                }    
                                
                                switch ($vview['UserMode']) {
                                    case 0: $usermode = 'Terminal Based'; break;
                                    case 1: $usermode = 'User Based'; break;
                                    default:
                                        break;
                                }
                                
                                $name = $oas->getNamebyAid($vview['CreatedByAID']);
                                
                                $results2 = $oas->getsitecode($vSiteID);
                                $results2 = $results2['SiteCode'];
                                $results = preg_split("/$results2/", $vview['TerminalCode']);
                                
                                $sitecode = preg_split("/ICSA-/", $vview['SiteCode']);
                                
                                $responce->rows[$i]['id']=$vview['TransactionRequestLogID'];
                                $responce->rows[$i]['cell']=array($sitecode[1],$results[1],$vtranstype, number_format($vview['Amount'],2),$vview['ServiceName'],$vview['TransactionDate'],$vstatus, $usermode,$name);
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
                $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
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
                        
                        $results2 = $oas->getsitecode($vSiteID);
                        $results2 = $results2['SiteCode'];
                        $results = preg_split("/$results2/", $vview['TerminalCode']);
                        
                        $responce->rows[$i]['id']=$vview['TransactionReferenceID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionReferenceID'],$vview['TransactionSummaryID'],$vview['POSAccountNo'], $results[1],$vtranstype,$vview['ServiceName'], number_format($vview['Amount'],2),$vview['DateCreated'],$vview['Name'], $vstatus);
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
                $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
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
                        $results2 = $oas->getsitecode($vSiteID);
                        $results2 = $results2['SiteCode'];
                        $results = preg_split("/$results2/", $vview['TerminalCode']); 
                        $responce->rows[$i]['id']=$vview['TransactionsSummaryID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionsSummaryID'],$vview['POSAccountNo'], $results[1],  number_format($vview['Deposit'], 2), number_format($vview['Reload'],2), number_format($vview['Withdrawal'], 2), $vview['DateStarted'], $vview['DateEnded'], $vview['Name']);
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
                $vTo = date ('Y-m-d', strtotime ('+1 day' , strtotime($vdate2)));
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
                           case 'D': $vtranstype = 'Deposit';break;
                           case 'W': $vtranstype = 'Withdrawal';break;
                           case 'R': $vtranstype = 'Reload';break;
                           case 'RD': $vtranstype = 'Redeposit';break;
                        }
                        
                        $results2 = $oas->getsitecode($vSiteID);
                        $results2 = $results2['SiteCode'];
                        $results = preg_split("/$results2/", $vview['TerminalCode']);
                        $vsthistoryID = $vview['ServiceTransferHistoryID'];
                        $responce->rows[$i]['id']=$vview['TransactionRequestLogLPID'];
                        $responce->rows[$i]['cell']=array($vview['TransactionRequestLogLPID'],$vview['TransactionReferenceID'], $vview['POSAccountNo'], 
                                                          $results[1], $vtranstype, $vview['ServiceTransactionID'], 
                                                          $vview['ServiceStatus'], number_format($vview['Amount'], 2), $vview['ServiceName'], 
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
                $data = $oas->checkpasskeydetails($cashierid);
                $genpasskey = '';
                $passkeyexpirydate = '';
                
                if(($data['Passkey'] == NULL || $data['Passkey'] == '') && ($data['DatePasskeyExpires'] == NULL || $data['DatePasskeyExpires'] == '')){
                    $genpasskey = '12345678';
                    $ddate = new DateTime(date());
                    $ddate->sub(date_interval_create_from_date_string('8 hour'));
                    $passkeyexpirydate = $ddate->format('Y-m-d H:i:s');
                }
                
                $result = $oas->updatecashierpasskey($cashierid, $_POST['optpasskey'], $genpasskey, $passkeyexpirydate);     
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
                   $vprovidername = $_POST['txtnewserver'];
                   $vaccID = $aid; // Account ID
                   
                   if($vsiteID > 0)
                   {
                       $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                       $_CasinoGamingPlayerAPIUB = new CasinoGamingCAPIUB();
                       
                       $usermode = $oas->getServiceUserMode($vnewserviceID);
                       
                       $servicegroupname = $oas->getServiceGrpNameById($vnewserviceID);
                       
                       $vprovidername = $servicegroupname;
                       
                       $country = 'PH';
                       $casinoID = 1;
                       $fname = 'ICSA';
                       $email = strtolower($fname).'@yopmail.com';
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
                       $sex = '';
                       $fax = '';
                       $occupation = '';
                       
                       $rterminals = $oas->getterminalacct($varrterminalcode, $vsiteID, $vsitecode, $voldserviceID);
                       $isapicreated = 0;
                       
                       //store all necessary information in the array
                       $rbatch = array();
                       foreach ($rterminals as $value)
                       {
                           foreach($value as $row)
                           {
                               $vnewarr = array("TerminalID" => $row['TerminalID'], "OldServiceID" =>$voldserviceID,
                                                "NewServiceID"=>$vnewserviceID, "Remarks" => $vremarks,
                                                "TerminalCode"=>$row['TerminalCode'], "ServiceGroupID"=>$row['ServiceGroupID'], 
                                                'OldPassword'=>$row['ServicePassword'],
                                                'OldHashedPwd'=>$row['HashedServicePassword']);
                               array_push($rbatch, $vnewarr);
                           }
                       }
                       
                       $roldsite = $oas->chkoldsite($vsiteID);
                   
                       $vgenpwdid = 0;
                       $isoldsite = 0;
                       
                       //check if this a existing site and Status is active and use
                       if(isset($roldsite['GeneratedPasswordBatchID']) && $roldsite['GeneratedPasswordBatchID'] > 0){
                           $vgenpwdid = $roldsite['GeneratedPasswordBatchID'];
                           $isoldsite = 1;
                       }
                       else
                       {
                           $isoldsite = 0;
                           $rpwdbatch = $oas->chkpwdbatch();
                           if($rpwdbatch)
                                $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                       }
                       
                       //checking of available plain and hashed password
                       if($vgenpwdid > 0)
                       {
                            $isexists = 0;
                            $apisuccess = 0;
                            $arrsuccess = array();
                            $arrerror = array();
                            $arrsucccreated = array();
                            $errmsg = '';
                            
                            foreach($rbatch as $val)
                            {
                               $vterminalID = $val['TerminalID'];
                               $login = $val['TerminalCode'];
                               $vservicegrpid = $val['ServiceGroupID'];
                               $vserviceID = $val['NewServiceID'];
                               $voldpassword = $val['OldPassword'];
                               $voldhashedpwd = $val['OldHashedPwd'];
                               
                               $lname = substr($login, strlen($terminalcode));
                               $alias = substr($login, strlen($terminalcode));
                               
                               //get plain and encrypted password
                               $vretrievepwd = $oas->getgeneratedpassword($vgenpwdid, $vservicegrpid);
                               $vgenpassword = $vretrievepwd['PlainPassword'];
                               $vgenhashed = $vretrievepwd['EncryptedPassword'];
                               
                               $password = $vgenpassword;
                               
                               //if provider is MG, then
                               if(strstr($vprovidername, "MG") == true)
                               {
                                    $_MGCredentials = $_PlayerAPI[$vserviceID -1]; 
                                    list($mgurl, $mgserverID) =  $_MGCredentials;
                                    $url = $mgurl;
                                    $hashedPassword = '';
                                    $aid = $_MicrogamingUserType;
                                    $currency = $_MicrogamingCurrency;
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = $mgserverID;

                                    //Call API to verify if account exists in MG
                                    $vapiResult = $_CasinoGamingPlayerAPI->validateCasinoAccount($login, $vserviceID, 
                                                    $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                    $vaccountExist = '';

                                    //Verify if API Call was successful
                                    if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true){
                                        $vaccountExist = $vapiResult['AccountInfo']['UserExists'];
                                        
                                        //check if account exists for MG Casino
                                        if($vaccountExist)
                                        {
                                            $apisuccess = 1;
                                            $isexists = 1;
                                            //Call Reset Password API if MG
                                            $vapiResult = $_CasinoGamingPlayerAPI->resetCasinoPassword($login, $password, $vserviceID, $url, 
                                                                                    $capiusername, $capipassword, $capiplayername, $capiserverID);
                                        } 
                                        else 
                                        {
                                            $isexists = 0;
                                            
                                            //call CasinoAPI creation (RTG / MG) if account does not exist
                                            $vapiResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, 
                                                                  $vserviceID, $url, $login, $password, $aid, $currency, $email, $fname, 
                                                                  $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, 
                                                                  $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
                                                                  $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, 
                                                                  $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, 
                                                                  $capipassword, $capiplayername, $capiserverID);
                                            $vnewarr = array("TerminalCode"=>$login,"Casino"=>$vprovidername);
                                            array_push($arrsucccreated, $vnewarr);
                                        }
                                    }
                                    else
                                    {
                                       $errmsg = $vapiResult['ErrorMessage'];
                                       $apisuccess = 0;
                                    }
                               }
                               else
                               {
                                   $cashierurl = $_ServiceAPI[$vserviceID -1]; 
                                   $playerurl = $_PlayerAPI[$vserviceID -1];
                                   $hashedpass = sha1($password);
                                   $hashedPassword = $hashedpass;
                                   $aid = 0;
                                   $currency = '';
                                   $capiusername = '';
                                   $capipassword = '';
                                   $capiplayername = '';
                                   $capiserverID = '';


                                   //Call API to get Account Info, for RTG casino
                                   $vapiResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $vserviceID, $cashierurl,'', $vprovidername);
                                   
                                   //Verify if API Call was successful
                                   if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                   {
                                       //check if exists in RTG
                                       if(isset($vapiResult['AccountInfo']['password']) && 
                                                $vapiResult['AccountInfo']['password'] <> null)
                                       {
                                           $isexists = 1;
                                           $vrtgoldpwd = $vapiResult['AccountInfo']['password'];
                                           
                                           //Call API Change Password
                                           if($usermode == 0){
                                               $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, 
                                                            $vserviceID, $playerurl, $casinoID, $login, $vrtgoldpwd, $password, 
                                                            $capiusername, $capipassword, $capiplayername, $capiserverID);
                                           }
                                           
                                           if($usermode == 1){
                                               $vapiResult = array('IsSucceed'=>true);
                                           }
                                       }
                                       else
                                       {
                                           $isexists = 0;
                                       }
                                   }
                                   else
                                   {
                                       //call CasinoAPI creation (RTG / MG)
                                       if($usermode == 0){
                                           $vapiResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, 
                                                              $vserviceID, $playerurl, $login, $password, $aid, $currency, $email, $fname, 
                                                              $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, 
                                                              $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
                                                              $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, 
                                                              $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, 
                                                              $capipassword, $capiplayername, $capiserverID);
                                       }
                                       
                                       if($usermode == 1){
                                           $vapiResult = array('IsSucceed'=>true);
                                       }
                                       
                                       $vnewarr = array("TerminalCode"=>$login,"Casino"=>$vprovidername);
                                       array_push($arrsucccreated, $vnewarr);
                                   }
                               }
                               
                               ///Check if API Result was successfull
                               if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                               {
                                   $apisuccess = 1;
                                   
                                   if($usermode == 0){
                                       if($vprovidername == 'RTG2'){
                                            if(strstr($login, "VIP") == true){
                                                $pid = $vapiResult['PID'];
                                                $playerClassID = 2;
                                                $_CasinoGamingPlayerAPI->ChangePlayerClassification($vprovidername, $playerurl, $pid, $playerClassID, $userID, $vserviceID);
                                            }
                                        }
                                   }
                                   
                                   
                                   $vnewarr = array("TerminalID" => $vterminalID, "TerminalCode"=>$login, 
                                                    "PlainPassword"=>$vgenpassword, "HashedPassword"=>$vgenhashed, 
                                                    "OldPassword"=>$voldpassword, "OldHashedPwd"=>$voldhashedpwd,
                                                    "Casino"=>$vprovidername, "OldServiceID" =>$voldserviceID,
                                                    "NewServiceID"=>$vnewserviceID,"Remarks"=>$vremarks);
                                   array_push($arrsuccess, $vnewarr);
                               }
                               else
                               {
                                   $apisuccess = 0;
                                   $errmsg = $vapiResult['ErrorMessage'];
                                   $vnewarr = array("TerminalID" => $vterminalID, "TerminalCode"=>$login, 
                                                    "PlainPassword"=>$vgenpassword, "HashedPassword"=>$vgenhashed, 
                                                    "OldPassword"=>$voldpassword, "OldHashedPwd"=>$voldhashedpwd,
                                                    "Casino"=>$vprovidername, "OldServiceID" =>$voldserviceID,
                                                    "NewServiceID"=>$vnewserviceID,"Remarks"=>$vremarks);
                                   array_push($arrerror, $vnewarr);
                               }
                            }
                            
                            //Verify if casino API was successfull, then insert plain and hashed password to Kronus DB
                            if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                            {
                                $rresult = $oas->reassignbatchserver($arrsuccess);
                                if($rresult > 0)
                                {
                                    if($isoldsite == 0){
                                        $updbatchpwd = $oas->updateGenPwdBatch($vsiteID, $vgenpwdid, 0);
                                        if(!$updbatchpwd)
                                            $msg = "Re-Assign Server:  Records unchanged in generatedpasswordbatch";
                                    }
                                    
                                    $msg = "Re-Assign Server: Provider was successfully re-assigned";    
                                   
                                    //insert into audit trail
                                    $arrtermcode = implode(",", $varrterminalcode);
                                    $vtransdetails = "terminal code ".$arrtermcode." ;old server ".$_POST['txtoldserver']." ;new server ".$_POST['txtnewserver'];
                                    $vauditfuncID = 44;
                                    $oas->logtoaudit($new_sessionid, $vaccID, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                                    
                                    //Log the created terminals in Casino's
                                    if(count($arrsucccreated) > 0){
                                        $logdir = __DIR__."/../sys/log/CreatedTerminals";
                                        $logfile = $logdir."/CreatedTerminals_".$vdate.".log";
                                        //$writelogs = $oas->logTerminalsCreated($arrsucccreated, $logfile);
                                    }
                                    
                                } else 
                                    $msg = "Re-Assign Server: error in re-assigning terminal/s";
                            } else 
                                $msg = "Re-Assign Server: API Error=".$errmsg;
                       }
                       else
                           $msg = "Re-Assign Server: No available site to get plain and encrypted password.";
                   }
                   else 
                       $msg = "Re-Assign Server: Invalid Field";
               }
               else 
                   $msg = "Re-Assign Server: Invalid Fields";
               unset($rterminals, $rbatch, $varrterminalcode, $arrsuccess, 
                     $vapiResult, $arrerror, $arrtrans, $arrsucccreated);
               $oas->close();
               header("Location: appswitchserver.php?mess=".$msg);
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
                   
                   //Checking if terminal has session
                   $hassession = $oas->chkTerminalSession($arrterminalID);
                   if(isset($hassession['ctrsession'])){
                       $ctrsession = $hassession['ctrsession'];
                       if($ctrsession == 0){
                           $rresult = $oas->removeservice($arrterminalID, $vserviceID, $vremarks);
                           if($rresult > 0)
                           {
                               $msg = "Removing of casino: Casino Server successfully removed";
                                //insert into audit trail
                               $arrtermcode = implode(",", $varrterminalcode);
                               $terminalCode = $vsitecode.$arrtermcode;
                               $vtransdetails = "Terminal= ".$terminalCode.";Casino= ".$_POST['txtoldserver'];
                               $vauditfuncID = 45;
                               $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                           } 
                           else
                               $msg = "Removing of casino: Error on removing the server";
                       } 
                       else 
                           $msg = "Removing of casino: This terminal has an existing session";
                   }
                   
                   unset($arrterminalID, $hassession,$vtransdetails);
               }
               else 
               {
                   $msg = "Removing of casino: Invalid Field";
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
            //Get Launch Pad log files upon loading of page
            case 'GetLaunchPadLogFile':
                $vrealfolder = $oas->getlogspath($launchPadLogPath);
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
                            if(!preg_match("/gii-1.1/", $file)) { //Added on July 3, 2012 To remove gii-1.1 folder from list
                                $newarr = array(substr($file, 0, strrpos($file, ".")));
                                array_push($vfiles, $newarr);
                            }
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
            //Get log files upon loading of page
            case 'GetAdminLogFile':
                $vrealfolder = $oas->getadminlogspath($adminlogpath);
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
            //show Launch Pad log's content upon clicking of file
            case 'ShowLaunchPadLogContent':
                $vrealfolder = $oas->getlogspath($launchPadLogPath);
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
            case 'ShowAdminLogContent':
                $vrealfolder = $oas->getadminlogspath($adminlogpath);
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
                //verify if MG Server
                if(strstr($vservicename, "MG")){
                    $key = "MG";
                }
                //verify if Playtech 
                if(strstr($vservicename, "PT")){
                    $key = "PT";
                }
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
                if((isset($_POST['cmbsite']) && isset($_POST['optpwd'])) || isset($_SESSION['createterminals']))
                {
                    $varrterminalcode = '';
                    $lpdeployment = '';
                    if(isset($_POST['chosen']))
                        $varrterminalcode = $_POST['chosen']; 
                    if(isset($_POST['optpwd']))
                        $lpdeployment = $_POST['optpwd'];     
                    $vaccID = $aid;
                    $vsiteID = $_POST['cmbsite'];
                    $vsitecode = $_POST['txtsitecode'];
                    
                    if($vsiteID > 0 || isset($_SESSION['createterminals']))
                    {
                       $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                       $_CasinoGamingPlayerAPIUB = new CasinoGamingCAPIUB();
                       
                       $country = 'PH';
                       $casinoID = 1;
                       $fname = 'ICSA';
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
                       $sex = '';
                       $fax = '';
                       $occupation = '';
                       
                       if(!isset($_SESSION['createterminals'])){
                           $rterminals = $oas->getterminalacct($varrterminalcode, $vsiteID, $vsitecode);
                           $isapicreated = 0;
                       }
                       else{
                           $rterminals = $_SESSION['createterminals'];
                           $vsiteID = $_SESSION['siteid'];
                           $lpdeployment = $_SESSION['lpdeployment'];
                           $isapicreated = 1;
                       }
                           
                       //store all necessary information in the array
                       $rbatch = array();
                       foreach ($rterminals as $value)
                       {
                           foreach($value as $row)
                           {
                               $vnewarr = array("TerminalID" => $row['TerminalID'], "ServiceID" => $row['ServiceID'], 
                                                "TerminalCode"=>$row['TerminalCode'], "ServiceName"=>$row['ServiceName'],
                                                "ServiceGroupID"=>$row['ServiceGroupID'], 'OldPassword'=>$row['ServicePassword'],
                                                'OldHashedPwd'=>$row['HashedServicePassword']);
                               array_push($rbatch, $vnewarr);
                           }
                       }
                       
                       $roldsite = $oas->chkoldsite($vsiteID);
                   
                       $vgenpwdid = 0;
                       $isoldsite = 0;
                       
                       //check if selected option is for lp deployment
                       if($lpdeployment > 0){
                           //check if this a existing site and Status is active and use
                           if(isset($roldsite['GeneratedPasswordBatchID']) && $roldsite['GeneratedPasswordBatchID'] > 0){
                               $vgenpwdid = $roldsite['GeneratedPasswordBatchID'];
                               $isoldsite = 1;
                           }
                           else
                           {
                               $isoldsite = 0;
                               $rpwdbatch = $oas->chkpwdbatch();
                               if($rpwdbatch)
                                    $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                           }
                       } 
                       else
                       {
                           $isoldsite = 0;
                           $rpwdbatch = $oas->chkpwdbatch();
                           if($rpwdbatch)
                                $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                       }
                           
                       //checking of available plain and hashed password
                       if($vgenpwdid > 0)
                       {
                            $isexists = 0;
                            $apisuccess = 0;
                            $arrsuccess = array();
                            $arrerror = array();
                            $arrsucccreated = array();
                            $errmsg = '';
                            foreach($rbatch as $val)
                            {
                               $vterminalID = $val['TerminalID'];
                               $login = $val['TerminalCode'];
                               $vservicegrpid = $val['ServiceGroupID'];
                               $vserviceID = $val['ServiceID'];
                               $vprovidername = $val['ServiceName'];
                               $voldpassword = $val['OldPassword'];
                               $voldhashedpwd = $val['OldHashedPwd'];
                               
                               $servicegroupname = $oas->getServiceGrpNameById($vserviceID);
                               
                               $vprovidername = $servicegroupname;
                               
                               $lname = substr($login, strlen($terminalcode));
                               $alias = substr($login, strlen($terminalcode));
                               
                               $usermode = $oas->getServiceUserMode($vserviceID);
                               
                               //get plain and encrypted password
                               $vretrievepwd = $oas->getgeneratedpassword($vgenpwdid, $vservicegrpid);
                               $vgenpassword = $vretrievepwd['PlainPassword'];
                               $vgenhashed = $vretrievepwd['EncryptedPassword'];
                               
                               $password = $vgenpassword;
                               
                               //if provider is MG, then
                               if(strstr($vprovidername, "MG") == true)
                               {
                                    $_MGCredentials = $_PlayerAPI[$vserviceID -1]; 
                                    list($mgurl, $mgserverID) =  $_MGCredentials;
                                    $url = $mgurl;
                                    $hashedPassword = '';
                                    $aid = $_MicrogamingUserType;
                                    $currency = $_MicrogamingCurrency;
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = $mgserverID;

                                    //Call API to verify if account exists in MG
                                    $vapiResult = $_CasinoGamingPlayerAPI->validateCasinoAccount($login, $vserviceID, 
                                                    $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
                                    $vaccountExist = '';

                                    //Verify if API Call was successful
                                    if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true){
                                        $vaccountExist = $vapiResult['AccountInfo']['UserExists'];
                                        
                                        //check if account exists for MG Casino
                                        if($vaccountExist)
                                        {
                                            $apisuccess = 1;
                                            $isexists = 1;
                                            //Call Reset Password API if MG
                                            $vapiResult = $_CasinoGamingPlayerAPI->resetCasinoPassword($login, $password, $vserviceID, $url, 
                                                                                    $capiusername, $capipassword, $capiplayername, $capiserverID);
                                        } 
                                        else 
                                        {
                                            $isexists = 0;
                                            
                                            if($isapicreated == 0){
                                                $_SESSION['createterminals'] = $rterminals;
                                                $_SESSION['siteid'] = $vsiteID;
                                                $_SESSION['lpdeployment'] = $lpdeployment;
                                                $oas->close();
                                                header("Location: ".$_SERVER['HTTP_REFERER']);
                                            }
                                            else{
                                                 //call CasinoAPI creation (RTG / MG) if account does not exist
                                                $vapiResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, 
                                                                      $vserviceID, $url, $login, $password, $aid, $currency, $email, $fname, 
                                                                      $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, 
                                                                      $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
                                                                      $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, 
                                                                      $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, 
                                                                      $capipassword, $capiplayername, $capiserverID);
                                                $vnewarr = array("TerminalCode"=>$login,"Casino"=>$vprovidername);
                                                array_push($arrsucccreated, $vnewarr);
                                            }
                                        }
                                    }
                                    else
                                    {
                                       $errmsg = $vapiResult['ErrorMessage'];
                                       $apisuccess = 0;
                                    }
                               }
                               //if provider is RTG, then
                               else if(strstr($vprovidername, "RTG") == true)
                               {
                                   $cashierurl = $_ServiceAPI[$vserviceID -1]; 
                                   $playerurl = $_PlayerAPI[$vserviceID -1];
                                   $hashedpass = sha1($password);
                                   $hashedPassword = $hashedpass;
                                   $aid = 0;
                                   $currency = '';
                                   $capiusername = '';
                                   $capipassword = '';
                                   $capiplayername = '';
                                   $capiserverID = '';
                                   if(strstr($login, "VIP") == true){
                                        $isVIP = 1;
                                    } else { $isVIP = 0; }
                                   
                                   //Call API to get Account Info, for RTG casino
                                   $vapiResult = $_CasinoGamingPlayerAPI->getCasinoAccountInfo($login, $vserviceID, $cashierurl,'',$vprovidername);
                                   
                                   //Verify if API Call was successful
                                   if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                   {
                                       //check if exists in RTG
                                       if(isset($vapiResult['AccountInfo']['password']) && 
                                                $vapiResult['AccountInfo']['password'] <> null)
                                       {
                                           $isexists = 1;
                                           $oldpassword = $vapiResult['AccountInfo']['password'];
                                           
                                           if($usermode == 0){
                                               $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, 
                                                            $vserviceID, $playerurl, $casinoID, $login, $oldpassword, $password, 
                                                            $capiusername, $capipassword, $capiplayername, $capiserverID);
                                           }
                                           
                                           if($usermode == 1){
                                               $vapiResult = array('IsSucceed'=>true);
                                           }
                                       }
                                       else
                                       {
                                           $isexists = 0;
                                       }
                                                                                  
                                   }
                                   else
                                   {
                                        $isexists = 0;
                                        if($isapicreated == 0){
                                            $_SESSION['createterminals'] = $rterminals;
                                            $_SESSION['siteid'] = $vsiteID;
                                            $_SESSION['lpdeployment'] = $lpdeployment;
                                            $oas->close();
                                            header("Location: ".$_SERVER['HTTP_REFERER']);
                                        }
                                        else{
                                            //call CasinoAPI creation (RTG / MG)
                                            
                                            if($usermode == 0){
                                                $vapiResult = $_CasinoGamingPlayerAPIUB->createTerminalAccount($vprovidername, 
                                                                  $vserviceID, $playerurl, $login, $password, $aid, $currency, $email, $fname, 
                                                                  $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, 
                                                                  $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
                                                                  $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, 
                                                                  $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, 
                                                                  $capipassword, $capiplayername, $capiserverID,$isVIP);
                                            }
                                            
                                            if($usermode == 1){
                                                $vapiResult = array('IsSucceed'=>true);
                                            }
                                            
                                            $vnewarr = array("TerminalCode"=>$login,"Casino"=>$vprovidername);
                                            array_push($arrsucccreated, $vnewarr);
                                        }
                                   }
                               }
                               //if provider is PT, then
                               else
                               {
                                   $url = $_PlayerAPI[$vserviceID-1];
                                   $oldpassword ='';
                                   $capiplayername = '';
                                   $capiserverID = '';
                                   $email = $lname."@yopmail.com";
                                   $addr1 = 'PH';
                                   $city = 'PH';
                                   $dayphone = '3385599';
                                   $zip = '1232';
                                   $currency ='Php';
                                   $capiusername = $_ptcasinoname;
                                   $capipassword = $_ptsecretkey;
                                   
                                   //replace number in the lastname with its equivalent value  in words.
                                    $number = 0;
                                    preg_match("/\d{1,}/", $lname,$number);
                                    $replace = strtoupper(helper::convert_number_to_words((int)$number[0]));
                                    $lname = preg_replace('/\d{1,}/', $replace, $lname);
                                   
                                   $vapiResult = $_CasinoGamingPlayerAPI->changeTerminalPassword($vprovidername, 
                                                            $vserviceID, $url, $casinoID, $login, $oldpassword, $password, 
                                                            $capiusername, $capipassword, $capiplayername, $capiserverID);
                                   
                                   //check if API result is success and response is not OK, then call create terminal API
                                   if($vapiResult['IsSucceed'] == TRUE && $vapiResult['PlayerInfo']['transaction']['@attributes']['result'] != "OK"){
                                        $isexists = 0;
                                        if($isapicreated == 0){
                                            $_SESSION['createterminals'] = $rterminals;
                                            $_SESSION['siteid'] = $vsiteID;
                                            $_SESSION['lpdeployment'] = $lpdeployment;
                                            $oas->close();
                                            header("Location: ".$_SERVER['HTTP_REFERER']);
                                        }  else {
                                            
                                            //check if VIP, pass appropriate VIP parameter.
                                                if(strstr($vprovidername, "VIP") == true){
                                                    $visVIP = $_ptvip;
                                                } else { $visVIP = $_ptreg; }
                                                
                                                     $vapiResult = $_CasinoGamingPlayerAPI->createTerminalAccount($vprovidername, 
                                                                           $vserviceID, $url, $login, $password, $aid, $currency, $email, $fname, 
                                                                           $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $state, 
                                                                           $zip, $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, 
                                                                           $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino, 
                                                                           $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $capiusername, 
                                                                           $capipassword, $capiplayername, $capiserverID,$visVIP);
                                                     $vnewarr = array("TerminalCode"=>$login,"Casino"=>$vprovidername);
                                                     array_push($arrsucccreated, $vnewarr);
                                        }
                                   } 
                               }
                   
                               ///Check if API Result was successfull
                               if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                               {
                                   $apisuccess = 1;
                                   $vnewarr = array("TerminalID" => $vterminalID, "ServiceID" => $vserviceID,
                                                    "TerminalCode"=>$login, "PlainPassword"=>$vgenpassword, 
                                                    "HashedPassword"=>$vgenhashed, 'OldPassword'=>$voldpassword,
                                                    'OldHashedPwd'=>$voldhashedpwd,"Casino"=>$vprovidername);
                                   array_push($arrsuccess, $vnewarr);
                               }
                               else
                               {
                                   $apisuccess = 0;
                                   $errmsg = $vapiResult['ErrorMessage'];
                                   $vnewarr = array("TerminalID" => $vterminalID, "ServiceID" => $vserviceID,
                                                    "TerminalCode"=>$login, "PlainPassword"=>$vgenpassword, 
                                                    "HashedPassword"=>$vgenhashed, 'OldPassword'=>$voldpassword,
                                                    'OldHashedPwd'=>$voldhashedpwd,"Casino"=>$vprovidername);
                                   array_push($arrerror, $vnewarr);
                               }
                            }

                            //Verify if casino API was successfull, then insert plain and hashed password to Kronus DB
                            if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true){
                                $isrecorded = $oas->updateterminalpwd($arrsuccess);
                                if($isrecorded > 0){
                                    
                                    $updbatchpwd = $oas->updateGenPwdBatch($vsiteID, $vgenpwdid, $lpdeployment);
                                    if(!$updbatchpwd)
                                        $msg = "Terminal Service Assignment:  Records unchanged in generatedpasswordbatch";
                                    else
                                    {
                                        if($lpdeployment > 0)
                                            $msg = "Change Terminal Password: Success in updating terminal/s for launchpad deployment";
                                        else
                                            $msg = "Change Terminal Password: Success in updating existing terminal password";
                                    }    
                                    
                                    $arrtrans = array();
                                    foreach($arrsuccess as $val){
                                        $zterminalCode = $val['TerminalCode'];
                                        $zcasino = $val['Casino'];
                                        $transdetails = "Terminal=".$zterminalCode." Casino=".$zcasino;
                                        array_push($arrtrans, $transdetails);
                                    }

                                    $vtransdetails = implode(";", $arrtrans);
                                     //Insert in audit trail
                                    $vauditfuncID = 60;
                                    
                                    $isaudit = $oas->logtoaudit($new_sessionid, $vaccID, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                                    
                                    $txtfile = __DIR__."/../sys/log/TerminalPasswords.txt";
                                    //Write the result/s in a Log file
                                    $writetologs = $oas->createTerminalPwdLogs($arrsuccess, $arrerror, $txtfile);
                                    
                                    if(count($arrsucccreated) > 0){
                                        $logdir = __DIR__."/../sys/log";
                                        $logfile = $logdir."/CreatedTerminals_".$vdate.".log";
                                        $writelogs = $oas->logTerminalsCreated($arrsucccreated, $logfile);
                                    }
                                    
                                } else {
                                    $msg = "Change Terminal Password: Passwords are already updated";
                                }
                            }
                            else{
                                $msg = "Change Terminal Password: API Error=".$errmsg;
                            }
                            
                            unset($rterminals, $rbatch, $varrterminalcode, $arrsuccess, $vapiResult, $arrerror, $arrtrans, $arrsucccreated);
                        }
                        else{
                            $msg = "Change Terminal Password: No available site to get plain and encrypted password.";
                        }
                    }
                    else
                    {
                        $msg = "Change Terminal Password: Invalid fields";
                    }
                }
                else
                {
                    $msg = "Change Terminal Password: Invalid fields";
                }
                unset($_SESSION['createterminals'], $_SESSION['siteid'], $_SESSION['lpdeployment']);
                $_SESSION['mess'] = $msg;
                $oas->close();
                header("Location: appbatchpassword.php?mess=".$msg);
            break;
            case 'GetTerminalsCreatedLogs':
                $logdir = __DIR__."/../sys/log";
                if(is_dir($logdir))
                {
                    $listfiles  = scandir($logdir);
                    $vfiles = array();
                    foreach($listfiles as $file)
                    {
                        //hides (.), (..), (index), and temporary files upon viewing
                        if(($file != '..') && ($file != '.') && (strstr($file, "PHP_errors") == false))
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
            case 'ShowTerminalsCreatedLogs':
                $logdir = __DIR__."/../sys/log/";
                $vfile = $_POST['logfile']; 
                $vfullpath = $logdir.$vfile.".log";
                $vdatenow = date("Y-m-d");
                //check first if file exists
                if(file_exists($vfullpath))   
                {
                    //then check if file is not empty
                    if(filesize($vfullpath) > 0)
                    {
                       $rcontent = $oas->getfilecontents($vfullpath);
                    }
                    else
                    {
                       $rcontent = "";
                    }
                    echo json_encode($rcontent);
                }   
                else   
                {  
                    $errmsg->error = "Logs Tracking: Log file does not exists";
                    echo json_encode($errmsg);
                } 
                exit;
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
            case 'GetServiceGroup':
                $vserviceid = $_POST['serviceid'];
                $rservicegrp = $oas->getterminalprovider(0, $vserviceid);
                echo json_encode($rservicegrp);
                $oas->close();
                exit;
            break;
            case 'ShowTerminalPassword':
                $vfile = ROOT_DIR."sys/log/TerminalPasswords.txt";
                $rresult = $oas->getfilecontents($vfile);
                if($rresult)
                    echo json_encode($rresult);
                else 
                    echo 'Change Terminal Password Logs: Log file/s not found';
                
                $oas->close();
                exit;
            break;
            //Reset's and unlock MG and PT Terminals
            case 'UnlockMGTerminal':
                if(isset($_POST['cmbsite']) && isset($_POST['cmbterminal'])
                   && isset($_POST['cmbnewservice'])){
                      $vsiteID = $_POST['cmbsite'];
                      $vterminalID = $_POST['cmbterminal'];
                      $vterminal = $_POST['txtterminalcode'];
                      $vserviceID = $_POST['cmbnewservice'];
                      $vsitecode = $_POST['txtsitecode'];
                      $vaid = $aid;
                      
                      $rservicegrpID = $oas->getterminalprovider(0, $vserviceID);
                      foreach ($rservicegrpID as $val){
                          $vservicegrpID = $val['ServiceGroupID'];
                      }
                      
                      switch($vservicegrpID){
                          case '2': //if provider is MG, then
                                $_MGCredentials = $_PlayerAPI[$vserviceID -1]; 
                                list($mgurl, $mgserverID) = $_MGCredentials;
                                $url = $mgurl;
                                $hashedPassword = '';
                                $aid = $_MicrogamingUserType;
                                $currency = $_MicrogamingCurrency;
                                $capiusername = $_CAPIUsername;
                                $capipassword = $_CAPIPassword;
                                $capiplayername = $_CAPIPlayerName;
                                $capiserverID = $mgserverID;
                                break;
                          case '3': //if provider is PT, then
                                $url = $_PlayerAPI[$vserviceID-1];
                                $capipassword = $_ptsecretkey;
                                $capiusername = $_ptcasinoname;
                                break;
                          default :
                                echo "Invalid Casino Provider";
                                break;
                      }
                      
                      $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                      $login = $terminalcode.$vsitecode.$vterminal;

                      $roldsite = $oas->chkoldsite($vsiteID);
                    
                      $vgenpwdid = 0;
                      $isoldsite = 0;
                       
                      //check if this a existing site and Status is active and use
                      if(isset($roldsite['GeneratedPasswordBatchID']) && $roldsite['GeneratedPasswordBatchID'] > 0){
                           $vgenpwdid = $roldsite['GeneratedPasswordBatchID'];
                           $isoldsite = 1;
                      }
                       else
                       {
                           $isoldsite = 0;
                           $rpwdbatch = $oas->chkpwdbatch();
                           if($rpwdbatch)
                                $vgenpwdid = $rpwdbatch['GeneratedPasswordBatchID'];
                       }
                      
                      //checking of available plain and hashed password
                      if($vgenpwdid > 0)
                      {
                          //get plain and encrypted password
                           $vretrievepwd = $oas->getgeneratedpassword($vgenpwdid, $vservicegrpID);
                           $vgenpassword = $vretrievepwd['PlainPassword'];
                           $vgenhashed = $vretrievepwd['EncryptedPassword'];

                           $password = $vgenpassword;
                           
                           switch($vservicegrpID){
                               case '2': //if provider is MG, then
                                    //Unlock Casino Account of MG
                                    $vunlockResult = $_CasinoGamingPlayerAPI->unlockCasinoAccount($login, $vserviceID,$url, 
                                                                                $capiusername, $capipassword, $capiplayername, $capiserverID);
                                    
                                   if(isset($vunlockResult['IsSucceed']) && $vunlockResult['IsSucceed'] == true){
                                       //Call Reset Password API of MG
                                    $vapiResult = $_CasinoGamingPlayerAPI->resetCasinoPassword($login, $password, $vserviceID, $url, 
                                                                                               $capiusername, $capipassword, $capiplayername, 
                                                                                               $capiserverID);
                                   } else {
                                       $vapiResult['IsSucceed'] = false;
                                   }
                                   
                                    break;
                                case '3': //if provider is PT, then
                                    //$frozen = 0 for unfreeze state
                                    $frozen = 0;
                                    //call unfreeze function in PT
                                    $vapiResult = $_CasinoGamingPlayerAPI->unfreeze($login, $url, $capiusername, $capipassword, $frozen);
                                    break;
                                default :
                                    echo "Invalid Casino Provider";
                                    break;
                           }
                           
                      }
                      
                      $updbatchpwd = 0;
                      if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true){
                          $vnewarr = array(array("TerminalID" => $vterminalID, "ServiceID" => $vserviceID,
                                            "TerminalCode"=>$login, "PlainPassword"=>$vgenpassword, 
                                            "HashedPassword"=>$vgenhashed));
                          
                          $isrecorded = $oas->updateterminalpwd($vnewarr);
                          
                          if($isrecorded > 0){
                              
                            if($isoldsite == 0){
                               $updbatchpwd = $oas->updateGenPwdBatch($vsiteID, $vgenpwdid, 1);
                               if(!$updbatchpwd)
                                   $msg = "Reset Casino Account: Records unchanged in generatedpasswordbatch";
                            }

                            //Insert in audit trail
                            $vauditfuncID = 70;
                            $vtransdetails = "Terminal: ".$login.", Server ID: ".$vserviceID;
                            $isaudit = $oas->logtoaudit($new_sessionid, $vaid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);          

                            $msg = "Reset Casino Account: Casino account was successfully reset.";
                          
                          }
                          else 
                              $msg = "Reset Casino Account: Passwords are already reset";  
                      } 
                      else
                          $msg = "Reset Casino Account: Error in unlocking casino account ".$vapiResult['ErrorMessage'];
                }
                else
                    $msg = "Reset Casino Account: Invalid fields.";
               unset($vapiResult,$vsiteID, $vterminal, $vserviceID, $vsitecode, 
                     $rservicegrpID, $_MGCredentials, $url, $login, $roldsite, 
                     $vgenpwdid, $isoldsite, $vretrievepwd);
               $_SESSION['mess'] = $msg;
               $oas->close();
               header("Location: ../appunlockterminal.php");
            break;
            
            //Casino Reset User Based
            case 'UnlockMGTerminalUB':
                
                $service = $_POST['cmbnewservice'];
                $cardnumber = $_POST['txtcardnumber'];
                $casino = $_SESSION['CasinoArray'];
                $servername = $_POST['txtservicename'];
                
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
                        $casino2 = $oas->loopAndFindCasinoService($casinos, 'ServiceID', $service);  
                    }
                }
                
                //catch if casino array is empty
                if(empty($casino2)){
                   $msg = 'Reset Casino Account User Based: Casino is Empty';
                }
                else
                {
                    if(isset($_POST['txtcardnumber']) && isset($_POST['cmbnewservice']))
                    {
                        foreach ($casino2 as $value) {
                                $rserviceuname = $value['ServiceUsername'];
                                $rservicepassword = $value['ServicePassword'];
                                $vserviceID = $value['ServiceID'];
                                $rusermode = $value['UserMode'];
                                $risvip = $value['isVIP'];
                                $hashedpassword = $value['HashedServicePassword'];
                                $rstatus = $value['Status'];
                        }
                     
                        $vaid = $aid;

                        //set casino credentials
                        switch (true){
                                case strstr($servername, "MG"): //if provider is MG, then
                                    $_MGCredentials = $_PlayerAPI[$vserviceID -1]; 
                                    list($mgurl, $mgserverID) = $_MGCredentials;
                                    $url = $mgurl;
                                    $hashedPassword = '';
                                    $aid = $_MicrogamingUserType;
                                    $currency = $_MicrogamingCurrency;
                                    $capiusername = $_CAPIUsername;
                                    $capipassword = $_CAPIPassword;
                                    $capiplayername = $_CAPIPlayerName;
                                    $capiserverID = $mgserverID;
                                    break;
                                case strstr($servername, "PT"): //if provider is PT, then
                                    $url = $_PlayerAPI[$vserviceID-1];
                                    $capipassword = $_ptsecretkey;
                                    $capiusername = $_ptcasinoname;
                                    break;
                                default :
                                    echo "Reset Casino Account User Based: Invalid Casino Provider";
                                    break;
                        }

                        $_CasinoGamingPlayerAPI = new CasinoGamingCAPI();
                        $login = $rserviceuname;
                        $password = $rservicepassword;

                        //call casino api's to reset particular casino account
                        switch (true){
                            case strstr($servername, "MG"): //if provider is MG, then
                                    //Unlock Casino Account of MG
                                    $vunlockResult = $_CasinoGamingPlayerAPI->unlockCasinoAccount($login, $vserviceID,$url, 
                                                                                $capiusername, $capipassword, $capiplayername, $capiserverID);

                                   if(isset($vunlockResult['IsSucceed']) && $vunlockResult['IsSucceed'] == true){
                                    //Call Reset Password API of MG
                                    $vapiResult = $_CasinoGamingPlayerAPI->resetCasinoPassword($login, $password, $vserviceID, $url, 
                                                                                               $capiusername, $capipassword, $capiplayername, 
                                                                                               $capiserverID);
                                   } else {
                                       $vapiResult['IsSucceed'] = false;
                                   }

                                    break;
                            case strstr($servername, "PT"): //if provider is PT, then
                                    //$frozen 0 for unfreeze state
                                    $frozen = 0;
                                    //call unfreeze function in PT
                                    $vapiResult = $_CasinoGamingPlayerAPI->unfreeze($login, $url, $capiusername, $capipassword, $frozen);
                                    break;
                            default :
                                echo "Reset Casino Account User Based Invalid Casino Provider";
                            break;
                       }


                       $updbatchpwd = 0;
                       if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true){
                              //Insert in audit trail
                              $vauditfuncID = 70;
                              $vtransdetails = "Terminal: ".$login.", Server ID: ".$vserviceID;
                              $isaudit = $oas->logtoaudit($new_sessionid, $vaid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);          

                              $msg = "Reset Casino Account User Based: Casino account was successfully reset.";
                       } 
                       else
                            $msg = "Reset Casino Account User Based: Error in unlocking casino account ".$vapiResult['ErrorMessage'];
                    }
                    else
                        $msg = "Reset Casino Account User Based: Invalid fields.";
                   
                    unset($vapiResult,$vsiteID, $vterminal, $vserviceID, $vsitecode, 
                         $rservicegrpID, $_MGCredentials, $url, $login, $roldsite, 
                         $vgenpwdid, $isoldsite, $vretrievepwd);
           }
           
           //show result message
           echo json_encode($msg);
           $oas->close();
           exit;
           break;
           //Update Spyder 
           case 'SpyderEnable':
               
                $site = $_POST['cmbsite'];
                $txtspyder = $_POST['txtspyder'];
                $txtoldspyder = $_POST['txtoldspyder'];

                if($site != '-1' || $txtspyder = '' || $txtoldspyder = ''){
                    
//                    $count = $oas->checkAccountSessions($site);

//                    //check number of sessions in a certain site
//                    if($count > 0)
//                    {
//                        $msg = 'Enabling of Spyder: Failed to Update Spyder, There is an existing cashier session for this site.';
//                    }
//                    else
//                    {    
                        //check if spyder status has changed
                         if($txtspyder == $txtoldspyder){
                           $msg = 'Enabling of Spyder: Spyder status did not change';  
                         }
                         else
                         {
                             //update spyder status in sites table
                              $upspy = $oas->updateSpyder($txtspyder, $site);
                              if($upspy > 0){
                                  $msg = 'Enabling of Spyder: Update Successful';

                                  $vtransdetails = "Site ID ".$site;
                                  $vauditfuncID = 74;
                                  $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                              }
                              else
                              {
                                  $msg = 'Enabling of Spyder: Failed to Update Spyder';
                              }    
                         }    
//                    }    
                }
                else
                {
                    $msg = 'Enabling of Spyder: All Details are Required';
                }    
                    echo json_encode($msg);
                    unset($count,$site,$txtoldspyder,$txtspyder);
                exit;    
           break;
           //Update Casher Version
           case 'UpCashierVersion':
               
                $site = $_POST['cmbsite'];
                $txtcversion = $_POST['txtcversion'];
                $txtoldcversion = $_POST['txtoldcversion'];

                if($site != '-1' || $txtcversion = '' || $txtoldcversion = ''){
                        //check if spyder status has changed
                         if($txtcversion == $txtoldcversion){
                           $msg = 'Cashier URL Management: Cashier Version did not change';  
                         }
                         else
                         {
                             //update spyder status in sites table
                              $upspy = $oas->updateCashierVersion($txtcversion, $site);
                              if($upspy > 0)
                              {
                                  $msg = 'Cashier URL Management: Update Successful';

                                  $vtransdetails = "Cashier URL Management: Update Cashier Version of Site ID ".$site;
                                  $vauditfuncID = 76;
                                  $oas->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                              }
                              else
                              {
                                  $msg = 'Cashier URL Management: Failed to Update Cashier Version';
                              }    
                         }  
                                      
                }
                else
                {
                    $msg = 'Cashier URL Management: All Details are Required';
                }    
                    echo json_encode($msg);
                    unset($site,$txtcversion,$txtoldcversion);
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
    
    elseif(isset($_POST['sendSiteID3']))
    {
        $vsiteID = $_POST['sendSiteID3'];
        $rsitecode = $oas->getsitecode($vsiteID); //get the sitecode first
        $vresults = array();
        //get all terminals
        $vresults = $oas->getterminals2($vsiteID);  
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
        $rresult = $oas->getterminalprovider($terminalID, 0);
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
    //ajax request: Get MG and PT Casino server's only
    elseif(isset($_POST['mgterminalserver']))
    {
        $terminalID = $_POST['mgterminalserver'];
        $rresult = array();
        $rresult = $oas->getterminalprovider($terminalID, 0);
        $mgprovider = array();
        foreach($rresult as $val){
            $vserver = $val['ServiceName'];
            $vserviceID = $val['ServiceID'];
            if(strstr($vserver, "MG")){
                array_push($mgprovider, array("ServiceName"=>$vserver,"ServiceID"=>$vserviceID));
            } else if(strstr($vserver, "PT")){
                array_push($mgprovider, array("ServiceName"=>$vserver,"ServiceID"=>$vserviceID));
            }
        }
        if(count($mgprovider) > 0)
        {
            echo json_encode($mgprovider);
        }
        else
        {
            echo "Reset Casino Account: No MG or PT Provider Found";
        }
        unset($rresult);
        $oas->close();
        exit;
    }
    elseif(isset($_POST['getsiteterminals'])){
        $vsiteID = $_POST['getsiteterminals'];
        $rsitecode = $oas->getsitecode($vsiteID); //get the sitecode first
        $vresults = array();
        $vresults = $oas->getsiteterminals($vsiteID);
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
    }
    //get Spyder from selected site
    elseif(isset ($_POST['cmbsites']))
    {
        $vsiteID = $_POST['cmbsites'];
        $rresult = $oas->getSpyder($vsiteID);
        $rresult = $rresult['Spyder'];
        echo json_encode($rresult);
        unset($rresult);
        $oas->close();
        exit;
    }
    
    elseif(isset ($_POST['cmbsitez']))
    {
        $vsiteID = $_POST['cmbsitez'];
        $rresult = $oas->getCashierVersion($vsiteID);
        $rresult = $rresult['CashierVersion'];
        if($rresult == null){
            $rresult = 0;
        }
        echo json_encode($rresult);
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