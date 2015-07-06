<?php

/*
 * Created By: Edson L. Perez
 * Purpose: Process for PAGCOR Access; this may contain reports
 * Created On: January 02, 2012
 */

include __DIR__."/../sys/class/PagcorManagement.class.php";
require __DIR__.'/../sys/core/init.php';
require __DIR__.'/../sys/class/CTCPDF.php';
require_once __DIR__."/../sys/class/class.export_excel.php";

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

$opagcor= new PagcorManagement($_DBConnectionString[0]);
$connected = $opagcor->open();
if($connected)
{     
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $opagcor->getDate();    
/********** SESSION CHECKING **********/    
   $isexist=$opagcor->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $opagcor->close();
      if($opagcor->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   } 
   
   $isexistsession =$opagcor->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $opagcor->close();
      header("Location: login.php?mess=".$msg);
   }
/********** END SESSION CHECKING **********/   
   
   //checks if account was locked 
   $islocked = $opagcor->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $opagcor->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $opagcor->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
   //get all sites
   $sitelist = array();
   $sitelist = $opagcor->getallsites();
   $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection
   
   if(isset($_GET['action']))
   {
       $vaction = $_GET['action'];
       switch($vaction){
           //PAGCOR : GH Balance Per Cutoff version
           case 'GHBalancePerCutoff':
                $datenow = date("Y-m-d")." ".$cutoff_time;
                $startdate = date('Y-m-d');
                $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .$gaddeddate))." ".$cutoff_time;
                
                if(isset($_GET['startdate']))
                    $startdate = $_GET['startdate'];
                
//                if(isset($_GET['enddate']))
//                    $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .$gaddeddate))." ".$cutoff_time;
                
                $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .$gaddeddate))." ".$cutoff_time; 
                $startdate .= " ".$cutoff_time;
                $dir = $_GET['sord'];
                $sort = "s.SiteCode";
                $zsiteid = $_GET['site'];
                $rows = array();

                //check if queried date is date today
                if($datenow != $startdate)
                {
                    $rows = $opagcor->getoldGHBalance($sort, $dir, $startdate, $enddate, $zsiteid); //get gross hold balance, (past)
                }

                $page = $_GET['page']; // get the requested page
                $limit = $_GET['rows']; // get how many rows we want to have into the grid
                $count = count($rows); //count total rows
                if($count > 0 ) {
                       $total_pages = ceil($count/$limit);
                } else {
                      $total_pages = 0;
                }

                if ($page > $total_pages)
                {
                    $page = $total_pages;
                    $start = $limit * $page - $limit;           
                }
                if($page == 0)
                {
                    $start = 0;
                }
                else{
                    $start = $limit * $page - $limit;   
                }

                $limit = (int)$limit;
                $rresult =  $opagcor->paginatetransaction($rows, $start, $limit); //paginate grosshold balance array return

                $params = $opagcor->getJqgrid($count, 's.SiteCode'); //call jqgrid method to initialize start and limit
                $jqgrid = $params['jqgrid'];
                foreach($rresult as $row) {
                    $grosshold = ($row['initialdep'] + $row['reload'] - $row['redemption']) - $row['manualredemption'];
                    $endbal = $grosshold + $row['replenishment'] - $row['collection'];
                    $jqgrid->rows[] = array('id'=>$row['siteid'],'cell'=>array(
                        substr($row['sitecode'], strlen($terminalcode)),
                        $row['sitename'], 
                        $row['POSAccountNo'],
                        number_format($row['initialdep'],2),
                        number_format($row['reload'],2),
                        number_format($row['redemption'],2),
                        number_format($row['manualredemption'],2),
                        number_format($grosshold,2)
                    ));
                }
                $jqgrid->page = $page;
                echo json_encode($jqgrid); 
                unset($rresult, $rows);
                $opagcor->close();
                exit;
            break;
       }
   }
   //check if jqgrid (pagination) request
   if(isset($_POST['paginate']))
   {
        $vpaginate = $_POST['paginate'];
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $direction = $_POST['sord']; // get the direction
        $vdate1 = $_POST['txtDate1'];
        $vFrom = $vdate1." ".$cutoff_time;
        $vTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdate1)))." ".$cutoff_time;
        
       switch($vpaginate)
       {
           //page post for E-city transaction details tracking
            case 'LPTransactionDetails':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vsummaryID = $_POST['summaryID'];
                $vtranstype = $_POST['cmbtranstype'];
                
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionReferenceID"; //default sort name for transactiondetails
                }
                
                $rcount = $opagcor->counttransdetails($vSiteID,$vTerminalID, $vFrom, $vTo, $vsummaryID, $vtranstype); 

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
                $result = $opagcor->gettransactiondetails($vSiteID, $vTerminalID, 
                        $vFrom, $vTo, $vsummaryID, $vtranstype, $start, $limit, $sort, $direction);

                if(count($result) > 0)
                {
                     $i = 0;
                     $response->page = $page;
                     $response->total = $total_pages;
                     $response->records = $count;                    
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

                        $response->rows[$i]['id']=$vtranstype;
                        $response->rows[$i]['cell']=array($vtranstype,$vview['ServiceName'], number_format($vview['Amount'],2),$vview['DateCreated']);
                        $i++;
                     }
                }
                else
                {
                     $i = 0;
                     $response->page = $page;
                     $response->total = $total_pages;
                     $response->records = $count;
                     $msg = "E-City Tracking: No returned result";
                     $response->msg = $msg;
                }

                echo json_encode($response);
                unset($result);
                $opagcor->close();
                exit;
            break;
            //page post for transaction summary
            case 'LPTransactionSummary':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
//                $vdate1 = $_POST['txtDate1'];
//                $vFrom = $vdate1." ".$cutoff_time;
//                $vTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdate1)))." ".$cutoff_time;
                $vtranstype = $_POST['cmbtranstype'];
                
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "TransactionSummaryID"; //default sort name for transaction summary grid
                }
                
                $result = $opagcor->gettransactionsummary($vSiteID, $vTerminalID, $vFrom, $vTo, $vtranstype);


                 if(count($result) > 0)
                {
                     $transdetails = array();
                     foreach($result as $value) 
                     {                
                        if(!isset($transdetails[$value['TransactionSummaryID']])) 
                        {
                             if($vSiteID > 0)
                             {
                                 if(preg_match("/\d.*/", $value['TerminalCode'], $results)){ 
                                    $transdetails[$value['TransactionSummaryID']] = array(
                                      'LoyaltyCard'=>$value['LoyaltyCard'],  
                                      'TransactionSummaryID'=>$value['TransactionSummaryID'],
                                      'DateStarted'=>$value['DateStarted'],
                                      'DateEnded'=>$value['DateEnded'],
                                      'TerminalID'=>$value['TerminalID'],
                                      'TerminalCode'=>$results[0],
                                      'SiteID'=>$value['SiteID'],
                                      'UserName'=>$value['UserName'],
                                      'Withdrawal'=>'0.00',
                                      'Deposit'=>'0.00',
                                      'Reload'=>'0.00'
                                   ); 
                                }else{                   
                                   $transdetails[$value['TransactionSummaryID']] = array(
                                      'LoyaltyCard'=>$value['LoyaltyCard'],                                       
                                      'TransactionSummaryID'=>$value['TransactionSummaryID'],
                                      'DateStarted'=>$value['DateStarted'],
                                      'DateEnded'=>$value['DateEnded'],
                                      'TerminalID'=>$value['TerminalID'],
                                      'TerminalCode'=>$value['TerminalCode'],
                                      'SiteID'=>$value['SiteID'],
                                      'UserName'=>$value['UserName'],
                                      'Withdrawal'=>'0.00',
                                      'Deposit'=>'0.00',
                                      'Reload'=>'0.00'
                                   ); 
                                }
                             }
                        }
                        $trans = array();
                        switch ($value['TransactionType']) 
                        {
                            case 'W':
                                $trans = array('Withdrawal'=>$value['amount']);
                                break;
                            case 'D':
                                $trans = array('Deposit'=>$value['amount']);
                                break;
                            case 'R':
                                $trans = array('Reload'=>$value['amount']);
                                break;
                        }
                        $transdetails[$value['TransactionSummaryID']] = array_merge($transdetails[$value['TransactionSummaryID']], $trans);
                     }
                     
                     $count = count($transdetails);
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
                     
                     //paginate array
                     $ofindetails = $opagcor->paginatetransaction($transdetails, $start, $limit);
                     
                     $i = 0;
                     $response->page = $page;
                     $response->total = $total_pages;
                     $response->records = $count;                      
                     foreach($ofindetails as $vview)
                     {                     
                        $response->rows[$i]['id']=$vview['TransactionSummaryID'];
                        $response->rows[$i]['cell']=array($vview['TransactionSummaryID'], $vview['SiteID'], $vview['TerminalID'],  $vview['TerminalCode'], number_format($vview['Deposit'], 2), number_format($vview['Reload'],2), number_format($vview['Withdrawal'], 2), $vview['DateStarted'], $vview['DateEnded']);
                        $i++;
                     }
                }
                else
                {
                     $i = 0;
                     $response->page = 0;
                     $response->total = 0;
                     $response->records = 0;
                     $msg = "E-City Tracking: No returned result";
                     $response->msg = $msg;
                }

                echo json_encode($response);
                unset($result, $transdetails, $trans);
                $opagcor->close();
                exit;
            break;
            //page post for transaction request logs
            case 'LPTransactionLogs':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vtranstype = $_POST['cmbtranstype'];
//                $vdate1 = $_POST['txtDate1'];
//                $vdateFrom = $vdate1." ".$cutoff_time;
//                $vdateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdate1)))." ".$cutoff_time;
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
                
                $rcount = $opagcor->counttranslogslp($vSiteID,$vTerminalID, $vFrom, $vTo, $vtranstype, $vsummaryID); 
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
                
                $result = $opagcor->gettranslogslp($vSiteID, $vTerminalID, $vFrom, $vTo, $vtranstype, $vsummaryID, $start, $limit, $sort, $direction);

                if(count($result) > 0)
                {
                     $i = 0;
                     $response->page = $page;
                     $response->total = $total_pages;
                     $response->records = $count;                    
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
                        
                        $vsthistoryID = $vview['ServiceTransferHistoryID'];
                        $response->rows[$i]['id']=$vtranstype;
                        $response->rows[$i]['cell']=array($vtranstype,number_format($vview['Amount'], 2), $vview["ServiceName"], 
                                                          $vview['StartDate'], $vview['EndDate']);
                        $i++;
                     }
                }
                else
                {
                     $i = 0;
                     $response->page = $page;
                     $response->total = $total_pages;
                     $response->records = $count;
                     $msg = "E-City Tracking: No returned result";
                     $response->msg = $msg;
                }

                echo json_encode($response);
                unset($result);
                $opagcor->close();
                exit;
            break;
       }
   }
   elseif(isset ($_POST['page2']))
   {
       $vpage  = $_POST['page2'];
       switch ($vpage)
       {
          //POST providers / service upon loading of page (E-city transaction logs)
            case 'GetProviders':
                $rproviders = $opagcor->getallservices("ServiceID");
                echo json_encode($rproviders);
                unset($rproviders);
                $opagcor->close();
                exit;
            break;
          //POST providers / service upon loading of combo box
            case 'ShowProviders':
                $rproviders = $opagcor->getallservices("ServiceName");
                echo json_encode($rproviders);
                unset($rproviders);
                $opagcor->close();
                exit;
            break;
          //POST history details upon click of service history ID on the Transaction LOGs(Ecity) grid
            case 'GetTransferHistory':
                $vshistoryID = $_POST['shistory'];
                $rhistory = $opagcor->gethistorydetails(array($vshistoryID));
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
                $opagcor->close();
                exit;
            break;
            //populate combo box of transaction types 
            case 'GetTransactionTypes':
               $vtranstypes = $opagcor->gettranstypes();
               $arrtranstype = array(); 
               foreach($vtranstypes as $val)
               {
                   if($val['TransactionTypeCode'] <> 'RD')
                   {
                       $newarr = array('TransactionTypeID'=>$val['TransactionTypeID'], 
                                       'Description' => $val['Description'], 'TransactionTypeCode'=>$val['TransactionTypeCode']);
                       array_push($arrtranstype, $newarr);
                   }
               }
               echo json_encode($arrtranstype);
               unset($vtranstypes);
               unset($arrtranstype);
               $opagcor->close();
               exit;
            break;
       }
    }
    //for exporting of pdf and excel
    elseif(isset($_GET['export']))
    {
        $vgetpage = $_GET['export'];
        if(isset($_POST['txtDate1']) && isset($_POST['txtDate2']))
        {
            $vdate1 = $_POST['txtDate1'];
            $vdatefrom = $vdate1." ".$cutoff_time;
            $vdateto = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdate1)))." ".$cutoff_time;
        }
        
        if(isset($_GET['fn']))
        {
            $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
        
            //create the instance of the exportexcel format
            $excel_obj = new ExportExcel("$fn");
        }
            
        switch($vgetpage)
        {
            case 'ECityReport':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                //$vTerminalID = 0;
                $vsummaryID = 0;
                $vtranstype = $_POST['cmbtranstype'];
                $vdate1 = $_POST['txtDate1'];
                $vdatefrom = $vdate1." ".$cutoff_time;
                $vdateto = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdate1)))." ".$cutoff_time;
                
                               
                $header = array('Session Summary');
                
                /**** TRANSACTION SUMMARY (formatting of amount, names)***/
                $rsummary = $opagcor->gettransactionsummary($vSiteID, $vTerminalID, $vdatefrom, $vdateto, $vtranstype);
                $arrcomplsum = array();
                
                $transdetails = array();
                foreach($rsummary as $value) 
                {      
                    if(!isset($transdetails[$value['TransactionSummaryID']])) 
                    {
                        $results = preg_split("/\-/",$value['TerminalCode']);
                        $transdetails[$value['TransactionSummaryID']] = array(
                          'TransactionSummaryID'=>$value['TransactionSummaryID'],
                          'LoyaltyCard'=>$value['LoyaltyCard'],
                          'DateStarted'=>$value['DateStarted'],
                          'DateEnded'=>$value['DateEnded'],
                          'TerminalID'=>$value['TerminalID'],
                          'TerminalCode'=>$results[1],
                          'SiteID'=>$value['SiteID'],
                          'UserName'=>$value['UserName'],
                          'Withdrawal'=>'0.00',
                          'Deposit'=>'0.00',
                          'Reload'=>'0.00'
                        );
                    }
                    $trans = array();
                    switch ($value['TransactionType']) 
                    {
                        case 'W':
                            $trans = array('Withdrawal'=>$value['amount']);
                            break;
                        case 'D':
                            $trans = array('Deposit'=>$value['amount']);
                            break;
                        case 'R':
                            $trans = array('Reload'=>$value['amount']);
                            break;
                    }
                    $transdetails[$value['TransactionSummaryID']] = array_merge($transdetails[$value['TransactionSummaryID']], $trans);
                }
                foreach($transdetails as $val)
                {                     
                    $arrsummary = array("TransactionSummaryID"=>$val['TransactionSummaryID'], "SiteID"=>$val['SiteID'], 
                                                 "TerminalID"=>$val['TerminalID'],"LoyaltyCard"=>$val['LoyaltyCard'], "TerminalCode"=>$val['TerminalCode'],  "Deposit"=>number_format($val['Deposit'], 2), 
                                                 "Reload"=>number_format($val['Reload'],2), "Withdrawal"=>number_format($val['Withdrawal'], 2), 
                                                 "DateStarted"=>$val['DateStarted'], "DateEnded"=>$val['DateEnded']);
                    array_push($arrcomplsum, $arrsummary);
                }
                
                /***TRANSACTION DETAILS (formatting of amount, names)***/
                $rdetails = $opagcor->gettransactiondetails($vSiteID, $vTerminalID, $vdatefrom, $vdateto, 
                        $vsummaryID, $vtranstype, $start=null, $limit=null, $sort=null, $direction=null);
                
                $arrcompldet = array();
                foreach($rdetails as $vview)
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
                       case 'D': $rtranstype = 'Deposit';break;
                       case 'W': $rtranstype = 'Withdrawal';break;
                       case 'R': $rtranstype = 'Reload';break;
                       case 'RD': $rtranstype = 'Redeposit';break;
                    }               

                    $arrtransdetails = array("TransactionSummaryID"=>$vview['TransactionSummaryID'], "TransactionType"=>$rtranstype, "ServiceName"=>$vview['ServiceName'], "LoyaltyCard"=>$vview['LoyaltyCard'], 
                                             "Amount"=>number_format($vview['Amount'],2), "DateCreated"=>$vview['DateCreated']);
                    array_push($arrcompldet, $arrtransdetails);
                }
                
                 /********* TRANSACTION LOGS E-CITY (formatting of amount, names) ***********/
                $vsummaryID = 0;
                $rlogs = $opagcor->gettranslogslp($vSiteID, $vTerminalID, $vdatefrom, $vdateto, $vtranstype, $vsummaryID, $start=null, $limit=null, $sort=null, $direction=null);
                $logsdetails = array();
                $arrshistory = array();
                foreach($rlogs as $vview)
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
                        
                        $arrlogs = array("TransactionSummaryID"=>$vview['TransactionSummaryID'],"TransactionType"=>$vtranstype, "ServiceStatus"=>$vview['ServiceStatus'], $vview['LoyaltyCard'],
                                         "Amount"=>number_format($vview['Amount'], 2), "ServiceName"=>$vview['ServiceName'], 
                                         "StartDate"=>$vview['StartDate'], "EndDate"=>$vview['EndDate']);
                        
                        $arrhistory = array($vview['ServiceTransferHistoryID']);
                        array_push($logsdetails, $arrlogs);
                        
                }
                
                /**** PREPARING TO WRITE IN EXCEL *****/
                $ctr = 0;
                $arrcomplete = array();               
                while($ctr < count($arrcomplsum))
                {
                    $ctr2 = 0;
                    $vfirst = 1;
                    $svsummaryID = $arrcomplsum[$ctr]["TransactionSummaryID"];
                    
                    while($ctr2 < count($arrcompldet))
                    {
                        $ctr3 = 0;
                        $vtranssummaryID = $arrcomplsum[$ctr]['TransactionSummaryID'];
                        $vssiteID = $arrcomplsum[$ctr]['SiteID'];
                        $vsterminalID = $arrcomplsum[$ctr]['TerminalID'];
                        $vsterminalCode = $arrcomplsum[$ctr]['TerminalCode'];
                        $vsdeposit = $arrcomplsum[$ctr]['Deposit'];
                        $vsreload = $arrcomplsum[$ctr]['Reload'];
                        $vswithdraw = $arrcomplsum[$ctr]['Withdrawal'];
                        $vsdatestart = $arrcomplsum[$ctr]['DateStarted'];
                        $vsdateend = $arrcomplsum[$ctr]['DateEnded'];
                        $vdloyaltycrd = $arrcomplsum[$ctr]['LoyaltyCard'];
                        
                        if($vtranssummaryID == $arrcompldet[$ctr2]["TransactionSummaryID"])
                        {         
                            $vdsummaryID = $arrcompldet[$ctr2]['TransactionSummaryID'];
                            $vdtranstype = $arrcompldet[$ctr2]['TransactionType'];
                            $vdservicename = $arrcompldet[$ctr2]['ServiceName'];
                            $vdamount = $arrcompldet[$ctr2]['Amount'];
                            $vddatecrated = $arrcompldet[$ctr2]['DateCreated'];
                            
                            if($vfirst == 1)
                            {
                                //push transaction summary header
                                array_push($arrcomplete, array('Terminal Code', 'Deposit', 
                                        'Reload', 'Withdraw','Date Started','Date Ended'));
                                //push transaction summary results
                                array_push($arrcomplete, array($vsterminalCode, $vsdeposit, 
                                        $vsreload, $vswithdraw, $vsdatestart, $vsdateend));
                                //push transaction details header
                                array_push($arrcomplete, array(' ', 'Transaction Type', 'Service', 
                                        'Amount', 'Transaction Date'));
                                //push transcation details results
                                array_push($arrcomplete, array(' ', $vdtranstype, $vdservicename, $vdamount, 
                                        $vddatecrated));
                                
                                $vfirst = 2;
                            }
                            else
                            {
                                 array_push($arrcomplete, array(' ',$vdloyaltycrd, $vdtranstype, $vdservicename, $vdamount, 
                                        $vddatecrated));
                            }
                        }
                        $ctr2 = $ctr2 + 1;
                    }
                    $vfirst = 1;
                    while($ctr3 < count($logsdetails))
                    {
                        if($vdsummaryID == $logsdetails[$ctr3]["TransactionSummaryID"])
                        {
                            $vlsummaryID = $logsdetails[$ctr3]["TransactionSummaryID"];
                            $vltranstype = $logsdetails[$ctr3]["TransactionType"];
                            $vlamount = $logsdetails[$ctr3]["Amount"];
                            $vservicename = $logsdetails[$ctr3]["ServiceName"];
                            $vlstartdate = $logsdetails[$ctr3]["StartDate"];
                            $vlenddate = $logsdetails[$ctr3]["EndDate"];

                            if($vfirst == 1)
                            {
                                //push transaction logs (e-city) header
                                array_push($arrcomplete, array(' ',' ','Transaction Type', 'Amount', 
                                        'Service', 'Date Started', 'Date Ended'));
                                
                                //push transaction logs (e-city) results
                                array_push($arrcomplete, array(' ',' ', $vltranstype, $vlamount, 
                                        $vservicename, $vlstartdate, $vlenddate));
                            
                                $vfirst = 2;
                            }
                            else
                            {
                                array_push($arrcomplete, array(' ',' ', $vltranstype, $vlamount, 
                                        $vservicename, $vlstartdate, $vlenddate));
                            }
                        }
                        $ctr3 = $ctr3 + 1;
                    }
                    $ctr = $ctr + 1;
                }
               
                $excel_obj->setHeadersAndValues($header, $arrcomplete);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                
                //unsetting array values
                    unset($header);
                    unset($arrcomplsum);
                    unset($arrcompldet);
                    unset($logsdetails);
                    unset($arrshistory);
                    unset($arrcomplete);
                    unset($transdetails, $trans);
                    
                //Log to audit trail
                $vauditfuncID = 41; //export to excel
                $vtransdetails = "E-City Report";
                $opagcor->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $opagcor->close();
            break;
            case 'ECityLogs':
                $header = array('Row ID','Player ID', 'Transaction Type', 'Amount','Gaming Provider','Created On','Trans. Status','Trans. ID');
                $vresult = $opagcor->getLPTrans($vdatefrom, $vdateto);
                $vconsolidatedlp = array();
                $vctr = 0;
                while($vctr < count($vresult))
                {
                    $vrowid = $vresult[$vctr]['TransactionRequestLogLPID'];
                    $vplayerid = $vresult[$vctr]['TerminalCode'];
                    $vtranstype = $vresult[$vctr]['TransactionType'];
                    switch($vtranstype)
                    {
                        case 'D': 
                            $vtransdesc = 'Deposit';
                            break;
                        case 'R': 
                            $vtransdesc = 'Reload';
                            break;
                        case 'RD': 
                            $vtransdesc = 'Re-Deposit';
                            break;
                        case 'W': 
                            $vtransdesc = 'Withdrawal';
                            break;
                    }
                    $vamount = $vresult[$vctr]['Amount'];
                    $vgaming = $vresult[$vctr]['ServiceDescription'];
                    $vcreateon = $vresult[$vctr]['StartDate'];
                    $vtranstat = $vresult[$vctr]['ServiceStatus'];
                    if($vresult[$vctr]['Code'] == 'MM')
                    {
                        $vtransid = 'LP'.$vrowid.','.$vtranstype.','.$vresult[$vctr]['TerminalID'].','.$vresult[$vctr]['SiteID'];
                    }
                    if($vresult[$vctr]['Code'] == 'VV')
                    {
                        $vtransid = $vresult[$vctr]['ServiceTransactionID'];
                    }
                   array_push($vconsolidatedlp,array($vrowid,$vplayerid,$vtransdesc, number_format($vamount, 2),$vgaming,$vcreateon,$vtranstat,$vtransid));
                   $vctr++;
                }
                
                $excel_obj->setHeadersAndValues($header, $vconsolidatedlp);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                
                unset($vconsolidatedlp);
                
                //Log to audit trail
                    $vauditfuncID = 41; //export to excel
                    $vtransdetails = "E-City Logs";
                    $opagcor->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    $opagcor->close();
            break;
            case 'TransactionDetails':
                 $vservicecode = $_POST['cmbservices'];
                 if($vservicecode == 'RTG')
                 {
                     $header = array('Terminal ID','Type', 'Date/Time', 'Amount','Internal ID','Tracking Info','Created By');
                 }
                 else
                 {
                     $header = array('Terminal ID','Type', 'Date/Time', 'Amount','Internal ID','Receipt Number','Created By');
                 }
                 
                 $rproviders = $opagcor->getallservices("ServiceID");
                 $vserviceid = array();
                 foreach ($rproviders as $row)
                 {
                     $rname = $row['ServiceName'];
                     
                     //fetch only service id according to provider selected
                     $rproviders = preg_match('/'.$vservicecode.'/', $rname);
                     
                     if($rproviders == 1)
                     {
                         array_push($vserviceid, $row['ServiceID']);
                     }
                 }
                 
                 $vservicecount = 0;
                 $vconsolidated = array();
                 while( $vservicecount < count($vserviceid))
                 {
                   $vresult = $opagcor->getTransDet($vdatefrom,$vdateto,$vserviceid[$vservicecount]); 
                   $vctr = 0;   
                   while($vctr < count($vresult))
                   {
                      $vterminalCode =  $vresult[$vctr]['TerminalCode'];
                      $vtranstype = $vresult[$vctr]['TransactionType'];       
                      $vstartdate = date('m/d/Y h:i:s A' ,strtotime($vresult[$vctr]['DateCreated'])); 
                      $vamount= $vresult[$vctr]['Amount'];    
                      $vtransref = $vresult[$vctr]['TransactionReferenceID']; 
                     
                      switch($vservicecode)
                      {
                          case 'RTG':
                              $vserviceref = $vresult[$vctr]['TrackingInfo']; 
                              break;
                          case 'MG':
                              $vserviceref = $vresult[$vctr]['ServiceTransactionID']; 
                              break;
                      }
                      $vusername = $vresult[$vctr]['UserName']; 
                      array_push($vconsolidated,array($vterminalCode,$vtranstype,$vstartdate, number_format($vamount, 2),$vtransref,$vserviceref,$vusername));
                      $vctr++;
                   }
                   $vservicecount++;
                 }
                 
                 $excel_obj->setHeadersAndValues($header, $vconsolidated);
                 $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                 
                 //unsetting arrays
                 unset($vconsolidated);
                 unset($vserviceid);
                 unset($rproviders);
                 
                 //Log to audit trail
                    $vauditfuncID = 41; //export to excel
                    $vtransdetails = "Extract Transaction Details";
                    $opagcor->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    $opagcor->close();
            break;
            case 'grossholdbalanceexcel':
                $startdate = $_POST['startdate'];
//                $enddate = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($_POST['enddate'])))." ".$cutoff_time;           
                $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .$gaddeddate))." ".$cutoff_time; 
                $startdate .= " ".$cutoff_time;
                $header = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Initial Deposit','Reload','Redemption','Manual Redemption','Gross Hold');
                $vsitecode = $_POST['selsitecode'];
                $datenow = date("Y-m-d")." ".$cutoff_time;
                $rows = array();
                
                //check if queried date was previous dates
                if($datenow != $startdate)
                {
                    $rows = $opagcor->getoldGHCutoff($startdate, $enddate, $vsitecode);
                }

                $new_rows = array();
                foreach($rows as $row) {
                    $grosshold = ($row['initialdep'] + $row['reload'] - $row['redemption']) - $row['manualredemption']; // (D+R-W) - manual redemption
                    $endbal = $grosshold + $row['replenishment'] - $row['collection'];
                    $new_rows[] = array(
                                    substr($row['sitecode'], strlen($terminalcode)),
                                    $row['sitename'], 
                                    $row['POSAccountNo'],
                                    number_format($row['initialdep'],2),
                                    number_format($row['reload'],2),
                                    number_format($row['redemption'],2),
                                    number_format($row['manualredemption'], 2),
                                    number_format($grosshold,2)
                    );
                }

                 $excel_obj->setHeadersAndValues($header, $new_rows);
                 $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                 
                 //unsetting arrays
                 unset($new_rows, $rows);
                 unset($header);
                 
                 //Log to audit trail
                    $vauditfuncID = 41; //export to excel
                    $vtransdetails = "GrossHoldbalance Per Cut-off Excel";
                    $opagcor->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    $opagcor->close();
            break;
            case 'grossholdbalancepdf':
                $startdate = $_POST['startdate']; 
//                $enddate = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($_POST['enddate'])))." ".$cutoff_time;   
                $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .$gaddeddate))." ".$cutoff_time;   
                $startdate .= " ".$cutoff_time;
                $vsitecode = $_POST['selsitecode'];
                $datenow = date("Y-m-d")." ".$cutoff_time;
                $startdate = $_POST['startdate']." ".$cutoff_time; 
                $rows = array();
                
                //check if queried date was previous dates
                if($datenow != $startdate)
                {
                    $rows = $opagcor->getoldGHCutoff($startdate, $enddate, $vsitecode);
                }
                
                /**** set this configuration for exporting large quantity of records into pdf; *****/
                /**** prevents program from exceeding ,max execution time *****/
                ini_set('memory_limit', '-1'); 
                ini_set('max_execution_time', '220');
                
                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('Gross Hold Monitoring Per Cut-off');
                $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
                      date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
                $pdf->SetFontSize(6);
                $pdf->c_tableHeader2(array(
                        array('value'=>'Site / PEGS Code'),
                        array('value'=>'Site / PEGS Name','width' => '180px'),
                        array('value'=>'POS Account'),
                        array('value'=>'Initial Deposit','width' => '80px'),
                        array('value'=>'Reload','width' => '80px'),
                        array('value'=>'Redemption','width' => '80px'),
                        array('value'=>'Manual Redemption','width' => '80px'),
                        array('value'=>'Gross Hold','width' => '80px')
                     ));
                foreach($rows as $row) {
                    $grosshold = ($row['initialdep'] + $row['reload'] - $row['redemption']) - $row['manualredemption']; // (D+R-W) - manual redemption
                    $endbal = $grosshold + $row['replenishment'] - $row['collection'];
                    $pdf->c_tableRow2(array(
                        array('value'=>substr($row['sitecode'], strlen($terminalcode))),
                        array('value'=>$row['sitename'],'width' => '180px'),
                        array('value'=>$row['POSAccountNo']),
                        array('value'=>number_format($row['initialdep'],2), 'align' => 'right','width' => '80px'),
                        array('value'=>number_format($row['reload'],2), 'align' => 'right','width' => '80px'),
                        array('value'=>number_format($row['redemption'],2), 'align' => 'right','width' => '80px'),
                        array('value'=>number_format($row['manualredemption'], 2), 'align' => 'right','width' => '80px'),
                        array('value'=>number_format($grosshold,2), 'align' => 'right','width' => '80px')
                     ));
                }
                $pdf->c_tableEnd();
                $pdf->c_generatePDF('grossholdpercutoff.pdf');
                $opagcor->close();
                unset($rows);
            break;
        }
    }
    if(isset($_POST['sendSiteID']))
    {
        $vsiteID = $_POST['sendSiteID'];
        if($vsiteID <> "-1")
        {
            $rsitecode = $opagcor->getsitecode($vsiteID); //get the sitecode first
            $vresults = array();
            //get all terminals
            $vresults = $opagcor->viewterminals($vsiteID);  
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
        $opagcor->close();
        exit;
    }
    elseif(isset ($_GET['cmbterminal']))
    {
        $vterminalID = $_GET['cmbterminal'];
        $rresult = array();
        $rresult = $opagcor->getterminalname($vterminalID);
        $vterminalName->TerminalName = $rresult['TerminalName'];
        echo json_encode($vterminalName);
        unset($rresult);
        $opagcor->close();
        exit;
    }
    //for displaying site name on label
    elseif(isset($_POST['cmbsitename']))
    {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $opagcor->getsitename($vsiteID);
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
        $opagcor->close();
        exit;
   }
   elseif((isset($_POST['date1month']) && $_POST['date1month'] != null) && (isset($_POST['date1day']) && $_POST['date1day'] != null) 
           && (isset($_POST['date1year']) && $_POST['date1year'] != null))
    {
       $date1 = date("Y-m-d",mktime(0,0,0,$_POST['date1month'],$_POST['date1day'],$_POST['date1year']));
       $date2 = date("Y-m-d",mktime(0,0,0,$_POST['date1month'],$_POST['date1day']+1,$_POST['date1year']));
       echo "Date Range: ".$date1.' '.$cutoff_time." to ".$date2.' '."05:59:59";
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