<?php

/*
 * Created  By: Edson L. Perez
 * Created On: February 11, 2011
 * Purpose : For Transaction Tracking Report
 */

include __DIR__."/../sys/class/RptFinance.class.php";
require __DIR__.'/../sys/core/init.php';
require __DIR__.'/../sys/class/CTCPDF.php';
require_once __DIR__."/../sys/class/class.export_excel.php";

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

$orptfinance = new RptFinance($_DBConnectionString[0]);
$connected = $orptfinance->open();
if($connected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $orptfinance->getDate();    
/********** SESSION CHECKING **********/    
   $isexist=$orptfinance->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $orptfinance->close();
      if($orptfinance->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   } 
   
   $isexistsession =$orptfinance->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $orptfinance->close();
      header("Location: login.php?mess=".$msg);
   }
/********** END SESSION CHECKING **********/   
   
   //checks if account was locked 
   $islocked = $orptfinance->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $orptfinance->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $orptfinance->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
    //get all sites
    $sitelist = array();
    $sitelist = $orptfinance->getallsites();
    $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection
    
    if(isset($_POST['paginate']))
   {
       $vpaginate = $_POST['paginate'];
       $page = $_POST['page']; // get the requested page
       $limit = $_POST['rows']; // get how many rows we want to have into the grid
       $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
       $direction = $_POST['sord']; // get the direction
       switch($vpaginate)
       {
           //page post for transaction details tracking
            case 'TransactionDetails':
                $vSiteID = $_POST['cmbsite'];
                $vTerminalID = $_POST['cmbterminal'];
                $vdate1 = $_POST['txtDate1'];
//                $vdate2 = $_POST['txtDate2'];
                
                $vFrom = $vdate1." ".$cutoff_time;
                $vTo = date ('Y-m-d' , strtotime ('+1 day' , strtotime($vdate1)))." ".$cutoff_time;
                
                $vtranstype = $_POST['cmbtranstype'];
                $vsitecode = $terminalcode.$_POST['sitecode'];
                $result = $orptfinance->showtranstracking($type="paginate",$vSiteID, $vTerminalID, $vtranstype, $vFrom, $vTo);
                
                if(count($result) > 0)
                {
                    
                     $transdetails = array();
                     foreach($result as $value) 
                     {                
                        if(!isset($transdetails[$value['TransactionSummaryID']])) 
                        {
                             $transdetails[$value['TransactionSummaryID']] = array(
                                'TransactionSummaryID'=>$value['TransactionSummaryID'],
                                'DateStarted'=>$value['DateStarted'],
                                'DateEnded'=>$value['DateEnded'],
                                'DateCreated'=>$value['DateCreated'],
                                'TerminalID'=>$value['TerminalID'],
                                'SiteID'=>$value['SiteID'],
                                'TerminalCode'=>$value['TerminalCode'],
                                'LoyaltyCard'=>$value['LoyaltyCard'],
                                'ServiceName'=>$value['ServiceName'],
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
                     $ofindetails = $orptfinance->paginatetransaction($transdetails, $start, $limit);
                     
                     $i = 0;
                     $response->page = $page;
                     $response->total = $total_pages;
                     $response->records = $count; 
                     $arrdepositamt = array();
                     $arrreloadamt = array();
                     $arrwithdrawamt = array();
                     foreach($ofindetails as $vview)
                     {                     
                         $rterminalCode = $vview['TerminalCode'];
                         //remove the "icsa-[SiteCode]"
                         $rterminalCode = substr($rterminalCode, strlen($vsitecode));

                         $vdeposit = $vview['Deposit'];
                         $vreload = $vview['Reload'];
                         $vwithdraw = $vview['Withdrawal'];
                         $response->rows[$i]['id']=$vview['TransactionSummaryID'];
                         $response->rows[$i]['cell']=array($vview['TransactionSummaryID'], $_POST['sitecode'], $rterminalCode, $vview['ServiceName'],
                            number_format($vdeposit, 2), number_format($vreload, 2), number_format($vwithdraw, 2),
                            $vview['DateStarted'], $vview['DateEnded']);
                         $i++;

                        //store the 3 transaction types in an array
                        array_push($arrdepositamt, $vdeposit);
                        array_push($arrreloadamt, $vreload);
                        array_push($arrwithdrawamt, $vwithdraw);
                     }
                     
                      // Get the sum of all  transaction types
                        $totaldeposit = array_sum($arrdepositamt); 
                        $totalreload = array_sum($arrreloadamt); 
                        $totalwithdraw = array_sum($arrwithdrawamt);

                        unset($arrdepositamt, $arrreloadamt, $arrwithdrawamt, $transdetails, $ofindetails);
                        
                        //session variable to store transaction types in an array; to used on ajax call later on this program
                        $_SESSION['total'] = array("TotalDeposit" => $totaldeposit, 
                                         "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw);
                }
                else
                {
                     $i = 0;
                     $response->page = 0;
                     $response->total = 0;
                     $response->records = 0;
                     $msg = "Finance: No returned result";
                     $response->msg = $msg;
                }

                echo json_encode($response);
                unset($result);
                $orptfinance->close();
                exit;
            break;
            
       }
   }
   //displaying of total, grandtotal on jqgrid pagination 
   elseif(isset($_POST['gettotal']) == "GetTotals")
   {
       $arrtotal = 0;
       $granddeposit = 0;
       $grandreload = 0;
       $grandwithdraw = 0;
       $arrdeposit = array();
       $arrreload = array();
       $arrwithdraw = array();
       
       if(isset($_SESSION['total']))
       {
          $arrtotal = $_SESSION['total'];
       }
       
       $vSiteID = $_POST['cmbsite'];
       $vTerminalID = $_POST['cmbterminal'];
       $vdate1 = $_POST['txtDate1'];
//       $vdate2 = $_POST['txtDate2'];
       $vFrom = $vdate1." ".$cutoff_time;
       $vTo = date ('Y-m-d' , strtotime ('+1 day' , strtotime($vdate1)))." ".$cutoff_time;
       $vtranstype = $_POST['cmbtranstype'];
     
       //used this method to get the grand total of all tranction types
       $result = $orptfinance->showtranstracking($ztype="paginate", $vSiteID, $vTerminalID, $vtranstype, $vFrom, $vTo);
       $ctr1 = 0;
       while($ctr1 < count($result))
       {
           switch($result[$ctr1]['TransactionType'])
           {
                 case 'D' :
                     array_push($arrdeposit, $result[$ctr1]['amount']);
                 break;
                 case 'R':
                     array_push($arrreload, $result[$ctr1]['amount']);
                 break;
                 case 'W':
                     array_push($arrwithdraw, $result[$ctr1]['amount']);
                 break;
           }
           $ctr1++;
       }
       
       /**** GET Total Summary *****/
       $granddeposit = array_sum($arrdeposit);
       $grandreload = array_sum($arrreload);
       $grandwithdraw = array_sum($arrwithdraw);
                        
       unset($arrdeposit, $arrreload, $arrwithdraw);
       
       // store the grand total of transaction types into an array 
       $arrgrand = array("GrandDeposit" => $granddeposit, 
                            "GrandReload" => $grandreload, "GrandWithdraw" => $grandwithdraw);
       
       //results will be fetch here:
       if((count($arrtotal) > 0) && (count($arrgrand) > 0))
       {
           /**** Get Total Per Page  *****/
           $vtotal->deposit = number_format($arrtotal["TotalDeposit"], 2, '.', ',');
           $vtotal->reload = number_format($arrtotal["TotalReload"], 2, '.', ',');
           $vtotal->withdraw = number_format($arrtotal["TotalWithdraw"], 2, '.', ',');
           $vtotal->sales = number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',');
           /**** GET Total Page Summary ******/
           $vtotal->granddeposit = number_format($arrgrand['GrandDeposit'], 2, '.', ',');
           $vtotal->grandreload = number_format($arrgrand['GrandReload'], 2, '.', ',');
           $vtotal->grandwithdraw = number_format($arrgrand["GrandWithdraw"], 2, '.', ',');
           $vtotal->grandsales = number_format($arrgrand["GrandDeposit"] + $arrgrand["GrandReload"], 2, '.', ',');
           
           // count site grosshold
           $vgrossholdamt = $arrgrand["GrandDeposit"] + $arrgrand["GrandReload"] - $arrgrand["GrandWithdraw"];
           $vtotal->grosshold = number_format($vgrossholdamt, 2, '.', ',');
           echo json_encode($vtotal); 
       }
       else 
       {
           echo "No Results Found";
       }
       unset($arrgrand, $arrtotal, $_SESSION['total']);
       //unset($_SESSION['total']);
       $orptfinance->close();
       exit;
   }
   elseif(isset ($_POST['page2']))
   {
       $vpage  = $_POST['page2'];
       switch ($vpage)
       {
          //POST providers / service upon loading of page (E-city transaction logs)
            case 'GetProviders':
                $rproviders = $orptfinance->getallservices("ServiceID");
                echo json_encode($rproviders);
                unset($rproviders);
                $orptfinance->close();
                exit;
            break;
        //populate combo box of transaction types 
            case 'GetTransactionTypes':
               $vtranstypes = $orptfinance->gettranstypes();
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
               exit;
            break;
       }
   }
   elseif(isset($_POST['sendSiteID']))
   {
        $vsiteID = $_POST['sendSiteID'];
        if($vsiteID <> "-1")
        {
            $rsitecode = $orptfinance->getsitecode($vsiteID); //get the sitecode first
            $vresults = array();
            //get all terminals
            $vresults = $orptfinance->viewterminals($vsiteID);  
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
        $orptfinance->close();
        exit;
    }
    //for displaying site name on label
    elseif(isset($_POST['cmbsitename']))
    {
        $vsiteName = null;
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $orptfinance->getsitename($vsiteID);
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
        $orptfinance->close();
        exit;
    }
    elseif(isset ($_GET['cmbterminal']))
    {
        $vterminalID = $_GET['cmbterminal'];
        $rresult = array();
        $rresult = $orptfinance->getterminalname($vterminalID);
        $vterminalName->TerminalName = $rresult['TerminalName'];
        echo json_encode($vterminalName);
        unset($rresult);
        $orptfinance->close();
        exit;
    }
    //exporting excel and PDF
    elseif(isset($_GET['export']))
    {
        $vgetpage = $_GET['export'];
        $vdate1 = $_POST['txtDate1'];
        //$vdate2 = $_POST['txtDate2'];
        $vdatefrom = $vdate1." ".$cutoff_time;
        $vdateto = date ('Y-m-d' , strtotime ('+1 day' , strtotime($vdate1)))." ".$cutoff_time;
        $vSiteID = $_POST['cmbsite'];
        $vTerminalID = $_POST['cmbterminal'];
        $vtranstype = $_POST['cmbtranstype'];
        $vsitecode = $terminalcode.$_GET['sitecode'];
        switch($vgetpage)
        {
            case 'ECityReport':
                $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
                //create the instance of the exportexcel format
                $excel_obj = new ExportExcel("$fn");

                $header = array('Transaction Summary ID', 'Site Code','Terminal Code','Service Name',
                    'Deposit','Reload','Withdrawal','Date Started','Date Ended');
                
                $result = $orptfinance->showtranstracking($type="export",$vSiteID, $vTerminalID, $vtranstype, $vdatefrom, $vdateto);
                $completeexcelvalues = array();
                if(count($result) > 0)
                {
                    $transdetails = array();
                    foreach($result as $value) 
                    {                
                        if(!isset($transdetails[$value['TransactionSummaryID']])) 
                        {
                             $transdetails[$value['TransactionSummaryID']] = array(
                                'TransactionSummaryID'=>$value['TransactionSummaryID'],
                                'DateStarted'=>$value['DateStarted'],
                                'DateEnded'=>$value['DateEnded'],
                                'DateCreated'=>$value['DateCreated'],
                                'TerminalID'=>$value['TerminalID'],
                                'SiteID'=>$value['SiteID'],
                                'TerminalCode'=>$value['TerminalCode'],
                                'ServiceName'=>$value['ServiceName'],
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

                    $granddeposit = 0;
                    $grandreload = 0;
                    $grandwithdraw = 0;
                    $arrdeposit = array();
                    $arrreload = array();
                    $arrwithdraw = array();
                    
                    $sitecode = substr($vsitecode, strlen($terminalcode));
                    foreach($transdetails as $vview)
                    {    
                      //remove the "icsa-[SiteCode]"
                      $rterminalCode = substr($vview['TerminalCode'], strlen($vsitecode));
                      $vdeposit = $vview['Deposit'];
                      $vreload = $vview['Reload'];
                      $vwithdraw = $vview['Withdrawal'];
                      $excelvalues = array(0 => $vview['TransactionSummaryID'],
                                           1 => $sitecode,
                                           2 => $rterminalCode,
                                           3 => $vview['ServiceName'],
                                           4 => number_format($vdeposit, 2, '.', ','), 
                                           5 => number_format($vreload, 2, '.', ','), 
                                           6 => number_format($vwithdraw, 2, '.', ','), 
                                           7 => $vview['DateStarted'],
                                           8 => $vview['DateEnded']
                                         );
                      array_push($completeexcelvalues,$excelvalues); //push the values for site transactions per day
                      array_push($arrdeposit, $vdeposit);
                      array_push($arrreload, $vreload);
                      array_push($arrwithdraw, $vwithdraw);
                   }

                    //get the total withdraw, deposit and reload
                    $granddeposit = array_sum($arrdeposit);
                    $grandreload = array_sum($arrreload);
                    $grandwithdraw = array_sum($arrwithdraw);

                    unset($arrdeposit, $arrreload, $arrwithdraw);
                    //$vsales = $granddeposit + $grandreload;
                    $vgrossholdamt = ($granddeposit + $grandreload) - $grandwithdraw; 

                    //array for displaying total deposit on excel file
                    $totaldeposit = array(0 => 'Total Deposit',
                                        1 => number_format($granddeposit, 2, '.',',')
                    );
                    array_push($completeexcelvalues, $totaldeposit); //push the total sales for the site transaction

                    //array for displaying total reload on excel file
                    $totalreload = array(0 => 'Total Reload',
                                         1 => number_format($grandreload, 2, '.', ','));
                    array_push($completeexcelvalues, $totalreload);

                    //array for displaying total redeemed on excel file
                    $totalredeem = array(0 => 'Total Withdrawal',
                                        1 => number_format($grandwithdraw, 2, '.', ',')
                     );
                    array_push($completeexcelvalues, $totalredeem); //push the total withdraw for the site transaction

                     //array for displaying total grosshold on excel file
                    $grosshold = array(0 => 'Total Grosshold',
                                       1 => number_format($vgrossholdamt, 2, '.', ',')
                    );
                    array_push($completeexcelvalues, $grosshold);
                }
                else
                {
                    array_push($completeexcelvalues, array(0=>'No Results Found'));
                }
                
                                
                /**** PREPARING TO WRITE IN EXCEL *****/
                $excel_obj->setHeadersAndValues($header, $completeexcelvalues);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                
                //unsetting array values
                unset($header, $completeexcelvalues, $grosshold, $totalredeem, $totalreload, $totaldeposit);
                    
                //Log to audit trail
                $vauditfuncID = 41; //export to excel
                $vtransdetails = "Transaction Tracking";
                $orptfinance->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
            break;
            case 'ExportToPDF':
                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('Transaction Tracking');
                $pdf->html.='<div style="text-align:center;">As of ' . $vdatefrom . '</div>';
                $pdf->SetFontSize(10);
                $pdf->c_tableHeader(array('Transaction Summary ID', 'Site Code','Terminal Code','Service Name',
                    'Deposit','Reload','Withdrawal','Date Started','Date Ended'));

                $result = $orptfinance->showtranstracking($type="export",$vSiteID, $vTerminalID, $vtranstype, $vdatefrom, $vdateto);
                
                if(count($result) > 0)
                {
                    $transdetails = array();
                    foreach($result as $value) 
                    {                
                        if(!isset($transdetails[$value['TransactionSummaryID']])) 
                        {
                             $transdetails[$value['TransactionSummaryID']] = array(
                                'TransactionSummaryID'=>$value['TransactionSummaryID'],
                                'DateStarted'=>$value['DateStarted'],
                                'DateEnded'=>$value['DateEnded'],
                                'DateCreated'=>$value['DateCreated'],
                                'TerminalID'=>$value['TerminalID'],
                                'SiteID'=>$value['SiteID'],
                                'TerminalCode'=>$value['TerminalCode'],
                                'ServiceName'=>$value['ServiceName'],
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

                    $granddeposit = 0;
                    $grandreload = 0;
                    $grandwithdraw = 0;
                    $arrdeposit = array();
                    $arrreload = array();
                    $arrwithdraw = array();

                    foreach($transdetails as $vview)
                    {    
                      //remove the "icsa-[SiteCode]"
                      $rterminalCode = substr($vview['TerminalCode'], strlen($vsitecode));
                      $vdeposit = $vview['Deposit'];
                      $vreload = $vview['Reload'];
                      $vwithdraw = $vview['Withdrawal'];
                      $sitecode = substr($vsitecode, strlen($terminalcode));
                      //push the values for site transactions per day
                      $pdf->c_tableRow(array(0 => $vview['TransactionSummaryID'],
                                             1 => $sitecode,
                                             2 => $rterminalCode,
                                             3 => $vview['ServiceName'],
                                             4 => number_format($vdeposit, 2, '.', ','), 
                                             5 => number_format($vreload, 2, '.', ','), 
                                             6 => number_format($vwithdraw, 2, '.', ','), 
                                             7 => $vview['DateStarted'],
                                             8 => $vview['DateEnded']
                                      ));

                      array_push($arrdeposit, $vdeposit);
                      array_push($arrreload, $vreload);
                      array_push($arrwithdraw, $vwithdraw);
                   }

                    //get the total withdraw, deposit and reload
                    $granddeposit = array_sum($arrdeposit);
                    $grandreload = array_sum($arrreload);
                    $grandwithdraw = array_sum($arrwithdraw);

                    unset($arrdeposit, $arrreload, $arrwithdraw, $transdetails);
                    //$vsales = $granddeposit + $grandreload;
                    $vgrossholdamt = ($granddeposit + $grandreload) - $grandwithdraw; 

                    $pdf->html.= '<div style="text-align: center;">';
                    $pdf->html.= ' Total Deposit '.number_format($granddeposit, 2, '.', ',');
                    $pdf->html.= ' Total Reload '.number_format($grandreload, 2, '.', ',');
                    $pdf->html.= ' Total Withdrawal '.number_format($grandwithdraw, 2, '.', ',');  
                    $pdf->html.= ' Total Grosshold '.number_format($vgrossholdamt, 2, '.', ',');
                    $pdf->html.= '</div>';
                }
                else
                {
                    $pdf->c_tableRow(array(0 => 'No Results Found'));
                }
                $pdf->c_tableEnd();
                $vauditfuncID = 40; //export to pdf
                $vtransdetails = "";
                $orptfinance->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $pdf->c_generatePDF('TransactionTracking.pdf');
            break;
        }
    }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
