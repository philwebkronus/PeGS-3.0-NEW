<?php

/*
 * Created By: Edson L. Perez
 * Date Created: October 13, 2011
 */

include __DIR__."/../sys/class/RptSupervisor.class.php";
require __DIR__.'/../sys/core/init.php';
require_once __DIR__.'/../sys/class/CTCPDF.php';
require_once __DIR__."/../sys/class/class.export_excel.php";

function removeComma($money) {
    return str_replace(',', '', $money);
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

$orptsup = new RptSupervisor($_DBConnectionString[0]);
$connected = $orptsup->open();
$nopage = 0;

if($connected)
{
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $orptsup->getDate();
    /***** Session Checking *****/    
   $isexist=$orptsup->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $orptsup->close();
      if($orptsup->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   }    
   $isexistsession =$orptsup->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $orptsup->close();
      header("Location: login.php?mess=".$msg);
   }
/***** End Session Checking *****/   
        
   //checks if account was locked 
   $islocked = $orptsup->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $orptsup->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $orptsup->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
   $vcutofftime = $cutoff_time; //cutoff time set for report (web.config.php)
   //for JQGRID pagination
   if(isset ($_POST['paginate']))
   {
        $vpage = $_POST['paginate'];
        switch($vpage)
        {
            //for grid display only
            case 'GrossHold':
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                
                $vdatefrom = $_POST['strDate'];
                $vdateto = $_POST['endDate'];
                $dateFrom = $vdatefrom." ".$vcutofftime;
                
                $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdateto)))." ".$vcutofftime;
                $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
                $vsiteID = $rsiteID['SiteID'];
                
                //$rsitecashier = $orptsup->getsitecashier($vsiteID);
                $result = $orptsup->viewgrosshold($dateFrom, $dateTo, $vsiteID);
                
                if(count($result) > 0)
                {
                     $supdetails = array();
                     $mergedep = 0;
                     $mergerel = 0;
                     $mergewith = 0; 
                     foreach($result as $value) 
                     {         
                        if(!isset($supdetails[$value['CreatedByAID']])) 
                        {
                             $mergedep = 0;
                             $mergerel = 0;
                             $mergewith = 0; 
                             $supdetails[$value['CreatedByAID']] = array(
                                'CreatedByAID'=>$value['CreatedByAID'],
                                'Name'=>$value['Name'],
                                'DateCreated'=>$value['DateCreated'],
                                'TerminalID'=>$value['TerminalID'],
                                'SiteID'=>$value['SiteID'],
                                'Withdrawal'=>$mergedep,
                                'Deposit'=>$mergerel,
                                'Reload'=>$mergewith
                             ); 
                        }
                        $trans = array();
                        switch ($value['TransactionType']) 
                        {
                            case 'W':
                                $mergewith = $mergewith + $value['Amount'];
                                $trans = array('Withdrawal'=>$mergewith);
                            break;
                            case 'D':
                                $mergedep = $mergedep + $value['Amount'];
                                $trans = array('Deposit'=>$mergedep);
                            break;
                            case 'R':
                                $mergerel = $mergerel + $value['Amount']; 
                                $trans = array('Reload'=>$mergerel);
                            break;
                        }
                        $supdetails[$value['CreatedByAID']] = array_merge($supdetails[$value['CreatedByAID']], $trans);
                     }
                     
                     $count = count($supdetails);
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
                     $trans_details = $orptsup->paginatetransaction($supdetails, $start, $limit);
                     
                     $arrdepositamt = array();
                     $arrreloadamt = array();
                     $arrwithdrawamt = array();
                     $i = 0;
                     $response->page = $page;
                     $response->total = $total_pages;
                     $response->records = $count;        
                     foreach($trans_details as $vview)
                     {
                          $vAID = $vview['CreatedByAID'];
                          $depositamt = $vview['Deposit'];
                          $reloadamt = $vview['Reload'];
                          $withdrawamt = $vview['Withdrawal'];
                          $grossholdamt = $depositamt + $reloadamt - $withdrawamt;
                          
                          $response->rows[$i]['id']= $vAID;
                          $response->rows[$i]['cell'] = array($vview['Name'], number_format($depositamt, 2), 
                               number_format($reloadamt, 2), number_format($withdrawamt,2), 
                               number_format($grossholdamt, 2));
                          $i++;
                          //store the 3 transaction types in an array
                            array_push($arrdepositamt, $depositamt);
                            array_push($arrreloadamt, $reloadamt);
                            array_push($arrwithdrawamt, $withdrawamt);
                     }
                     // Get the sum of all  transaction types
                      $totaldeposit = array_sum($arrdepositamt); 
                      $totalreload = array_sum($arrreloadamt); 
                      $totalwithdraw = array_sum($arrwithdrawamt);

                      unset($arrdepositamt, $arrreloadamt, $arrwithdrawamt, $trans_details);
                      
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
                     $msg = "Gross Hold: No Results Found";
                     $response->msg = $msg;
                }
                echo json_encode($response);
                $orptsup->close();
                exit;
            break;    
        }
   }
   //Get totals per page, grand total
   elseif(isset($_POST['gettotal']) == "GetTotals")
   {
       $vdatefrom = $_POST['strDate'];
       $vdateto = $_POST['endDate'];
                
       $dateFrom = $vdatefrom." ".$vcutofftime;
       $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vdateto)))." ".$vcutofftime;
       
       $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
       $vsiteID = $rsiteID['SiteID'];
                
       //$rsitecashier = $orptsup->getsitecashier($vsiteID);
       $result = $orptsup->viewgrosshold($dateFrom, $dateTo,  $vsiteID, $start=null, $limit=null);
       
       //Get Total Load Cash and Tickets, Printed Tickets in EGM and Encashed Ticket in cashier
       $result2 = $orptsup->getdetails($dateFrom, $dateTo,  $vsiteID);

       if(isset($_SESSION['total']))
       {
          $arrtotal = $_SESSION['total'];
       }
       if(count($result) > 0)
       {
           $ctr1 = 0;
           $ctr2 = 0;
           $arrdeposit = array();
           $arrreload = array();
           $arrwithdraw = array();
           while($ctr1 < count($result))
           {
               switch($result[$ctr1]['TransactionType'])
               {
                     case 'D' :
                         array_push($arrdeposit, $result[$ctr1]['Amount']);
                     break;
                     case 'R':
                         array_push($arrreload, $result[$ctr1]['Amount']);
                     break;
                     case 'W':
                         array_push($arrwithdraw, $result[$ctr1]['Amount']);
                     break;
               }
               $ctr1++;
           }
           
            $loadcash = '0.00';
            $loadticket = '0.00';
            $loadcoupon = '0.00';
            $printedtickets = '0.00';
            $encashedtickets = '0.00';
            $redemptioncashier = '0.00';
            $manualredemption = 0.00;
           
           if(count($result2) > 0){
               while($ctr2 < count($result2))
                {
                    if($result2[$ctr2]['LoadCash'] != '0.00')
                        $loadcash = $result2[$ctr2]['LoadCash'];
                    if($result2[$ctr2]['LoadTicket'] != '0.00')
                        $loadticket = $result2[$ctr2]['LoadTicket'];
                    if($result2[$ctr2]['LoadCoupon'] != '0.00')
                        $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                    if($result2[$ctr2]['PrintedTickets'] != '0.00')
                        $printedtickets = $result2[$ctr2]['PrintedTickets'];
                    if($result2[$ctr2]['EncashedTickets'] != '0.00')
                        $encashedtickets = $result2[$ctr2]['EncashedTickets'];
                    if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                        $redemptioncashier = $result2[$ctr2]['RedemptionCashier'];
                    if($result2[$ctr2]['ManualRedemption'] != '0.00')
                        $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                    $ctr2++;
                }
           }
           
           /**** GET Total Summary *****/
           $granddeposit = array_sum($arrdeposit);
           $grandreload = array_sum($arrreload);
           $grandwithdraw = array_sum($arrwithdraw);

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
               $xgrossamt = $arrtotal["TotalDeposit"] + $arrtotal["TotalReload"] - $arrtotal["TotalWithdraw"];
               $vtotal->grosstotal = number_format($xgrossamt, 2, '.', ',');
               /**** GET Total Page Summary ******/
               $vtotal->granddeposit = number_format($arrgrand['GrandDeposit'], 2, '.', ',');
               $vtotal->grandreload = number_format($arrgrand['GrandReload'], 2, '.', ',');
               $vtotal->grandwithdraw = number_format($arrgrand["GrandWithdraw"], 2, '.', ',');
               $vtotal->grandsales = number_format($arrgrand["GrandDeposit"] + $arrgrand["GrandReload"], 2, '.', ',');
               
               $vtotal->loadcash = number_format($loadcash, 2, '.', ',');
               $vtotal->loadticket = number_format($loadticket, 2, '.', ',');
               $vtotal->loadcoupon = number_format($loadcoupon, 2, '.', ',');
               
               $vtotal->printedtickets = number_format($printedtickets, 2, '.', ',');
               $vtotal->encashedtickets = number_format($encashedtickets, 2, '.', ',');

               // count site grosshold
               $vgrossholdamt = $arrgrand["GrandDeposit"] + $arrgrand["GrandReload"] - $arrgrand["GrandWithdraw"];
               $vtotal->grosshold = number_format($vgrossholdamt, 2, '.', ',');
               
               // count site cash on hand
               $vcashonhandamt = $loadcash - $redemptioncashier - $manualredemption - $encashedtickets;
               $vtotal->cashonhand = number_format($vcashonhandamt, 2, '.', ',');
               echo json_encode($vtotal); 
           }
           else 
           {
               echo "No Results Found";
           }
       }
       else
       {
          echo "No Results Found";
       }
       unset($arrtotal, $arrdeposit, $arrreload, $arrdeposit, $arrgrand);
       $orptsup->close();
       exit;
   }
/***************************** EXPORTING EXCEL STARTS HERE *******************************/
   elseif(isset($_GET['excel']))
   {
       $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
       $vfromdate = $_GET['DateFrom'];
       $vtodate = $_GET['DateTo']; 
      
       $dateFrom = $vfromdate." ".$vcutofftime;
       $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vtodate)))." ".$vcutofftime;
            
       $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
       $vsiteID = $rsiteID['SiteID'];
                
       //$rsitecashier = $orptsup->getsitecashier($vsiteID);
       
      //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
      //setting the values of the headers and data of the excel file
      //and these values comes from the other file which file shows the data
        
        $rheaders = array('Cashier', 'Total Deposit', 'Total Reload', 'Total Redemption', 'Gross Hold','');
        
        $result = $orptsup->viewgrosshold($dateFrom, $dateTo, $vsiteID, $start = null, $limit = null);
        
        //Get Total Load Cash and Tickets, Printed Tickets in EGM and Encashed Ticket in cashier
       $result2 = $orptsup->getdetails($dateFrom, $dateTo,  $vsiteID);
        
        $combined = array();
        if(count($result) > 0)
        {   
             $mergedep = 0;
             $mergerel = 0;
             $mergewith = 0; 
             $supdetails = array();
             foreach($result as $value) 
             {         
                if(!isset($supdetails[$value['CreatedByAID']])) 
                {
                     $mergedep = 0;
                     $mergerel = 0;
                     $mergewith = 0; 
                     $supdetails[$value['CreatedByAID']] = array(
                        'CreatedByAID'=>$value['CreatedByAID'],
                        'Name'=>$value['Name'],
                        'DateCreated'=>$value['DateCreated'],
                        'TerminalID'=>$value['TerminalID'],
                        'SiteID'=>$value['SiteID'],
                        'Withdrawal'=>$mergedep,
                        'Deposit'=>$mergerel,
                        'Reload'=>$mergewith
                     ); 
                }
                $trans = array();
                switch ($value['TransactionType']) 
                {
                    case 'W':
                        $mergewith = $mergewith + $value['Amount'];
                        $trans = array('Withdrawal'=>$mergewith);
                    break;
                    case 'D':
                        $mergedep = $mergedep + $value['Amount'];
                        $trans = array('Deposit'=>$mergedep);
                    break;
                    case 'R':
                        $mergerel = $mergerel + $value['Amount']; 
                        $trans = array('Reload'=>$mergerel);
                    break;
                }
                $supdetails[$value['CreatedByAID']] = array_merge($supdetails[$value['CreatedByAID']], $trans);
             }
             
             $ctr2 = 0;
             
            $loadcash = '0.00';
            $loadticket = '0.00';
            $loadcoupon = '0.00';
            $printedtickets = '0.00';
            $encashedtickets = '0.00';
            $redemptioncashier = '0.00';
            $manualredemption = 0.00;

           if(count($result2) > 0){
               while($ctr2 < count($result2))
                {
                    if($result2[$ctr2]['LoadCash'] != '0.00')
                        $loadcash = $result2[$ctr2]['LoadCash'];
                    if($result2[$ctr2]['LoadTicket'] != '0.00')
                        $loadticket = $result2[$ctr2]['LoadTicket'];
                    if($result2[$ctr2]['LoadCoupon'] != '0.00')
                        $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                    if($result2[$ctr2]['PrintedTickets'] != '0.00')
                        $printedtickets = $result2[$ctr2]['PrintedTickets'];
                    if($result2[$ctr2]['EncashedTickets'] != '0.00')
                        $encashedtickets = $result2[$ctr2]['EncashedTickets'];
                    if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                        $redemptioncashier = $result2[$ctr2]['RedemptionCashier'];
                    if($result2[$ctr2]['ManualRedemption'] != '0.00')
                        $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                    $ctr2++;
                }
           }
           
            // count site cash on hand
            $vcashonhandamt = $loadcash - $redemptioncashier - $manualredemption - $encashedtickets;
             
             $vtotal = array();
             $vdeposit = array();
             $vreload = array();
             $vwithdraw = array();
             foreach($supdetails as $vview)
             {
                  $depositamt = $vview['Deposit'];
                  $reloadamt = $vview['Reload'];
                  $withdrawamt = $vview['Withdrawal'];
                  $grossholdamt = $depositamt + $reloadamt - $withdrawamt;
  
                  array_push($combined, array($vview['Name'], number_format($depositamt, 2, '.', ','), 
                      number_format($reloadamt, 2, '.', ','), number_format($withdrawamt, 2, '.', ','), 
                      number_format($grossholdamt, 2, '.', ',')));
               
                 /**** GET Total per page, stores in an array *****/
                    array_push($vtotal, $grossholdamt);
                    array_push($vdeposit, $depositamt);
                    array_push($vreload, $reloadamt);
                    array_push($vwithdraw, $withdrawamt);
             }
           unset($supdetails);
           /*** Get the Total sum of transaction types and gross hold***/
             $totalgh = array_sum($vtotal);
             $totaldeposit = array_sum($vdeposit);
             $totalreload = array_sum($vreload);
             $totalwithdraw = array_sum($vwithdraw);
                     
           //array variable to store an array of transaction types, GH; used on ajax call
             $arrtotal = array("TotalGH" => $totalgh, "TotalDeposit" => $totaldeposit, 
                         "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw);
           
           //array for displaying totals on excel file
            $totals1 = array(0 => '',
                      1 => '',
                      2 => '',
                      3 => '',
                      4 => '',
                      5 => ''
                     );
             $totals2 = array(0 => 'Summary per Page',
                        1 => '',
                        2 => 'Sales',
                        3 => number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ','),
                        4 => 'Redemption',
                        5 => number_format($arrtotal["TotalWithdraw"], 2, '.', ',')
                       );
             $totals3 = array(0 => 'Grand Total',
                        1 => '',
                        2 => 'Total Sales',
                        3 => number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ','),
                        4 => 'Total Redemption',
                        5 => number_format($arrtotal["TotalWithdraw"], 2, '.', ',')
                       );
             $totals4 = array(0 => '       ',
                        1 => '',
                        2 => '     Cash',
                        3 => number_format($loadcash, 2, '.', ','),
                        4 => 'Printed Tickets',
                        5 => number_format($printedtickets, 2, '.', ',')
                       );
             $totals5 = array(0 => '       ',
                        1 => '',
                        2 => '     Tickets',
                        3 => number_format($loadticket, 2, '.', ','),
                        4 => 'Encashed Tickets',
                        5 => number_format($encashedtickets, 2, '.', ',')
                       );
             $totals6 = array(0 => '       ',
                        1 => '     ',
                        2 => '     Coupons',
                        3 => number_format($loadcoupon, 2, '.', ','),
                        4 => 'Cash On Hand',
                        5 => number_format($vcashonhandamt, 2, '.', ',')
                       );
             array_push($combined, $totals1);
             array_push($combined, $totals2);
             array_push($combined, $totals3);
             array_push($combined, $totals4);
             array_push($combined, $totals5);
             array_push($combined, $totals6);
        }
        else
        {
            array_push($combined, array("No results found"));
        }
       
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Gross Hold";
        $orptsup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $combined);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
        unset($totals, $combined, $arrtotal, $vtotal, $vdeposit, $vreload, $vwithdraw);
        $orptsup->close();
        exit;
   }
   /***************************** EXPORTING PDF STARTS HERE *******************************/
   elseif(isset($_GET['pdf']))
   {
      $vfromdate = $_GET['DateFrom'];
      $vtodate = $_GET['DateTo']; 
      
      $dateFrom = $vfromdate." ".$vcutofftime;
      $dateTo = date ('Y-m-d' , strtotime ($gaddeddate, strtotime($vtodate)))." ".$vcutofftime;
       
      $rsiteID = $orptsup->viewsitebyowner($aid); //get all sites owned by operator
      $vsiteID = $rsiteID['SiteID'];
                
      $rsitecashier = $orptsup->getsitecashier($vsiteID);

      $result = $orptsup->viewgrosshold($dateFrom, $dateTo, $vsiteID, $start = null, $limit = null);

      //Get Total Load Cash and Tickets, Printed Tickets in EGM and Encashed Ticket in cashier
       $result2 = $orptsup->getdetails($dateFrom, $dateTo,  $vsiteID);
      
      $pdf = CTCPDF::c_getInstance();
      $pdf->c_commonReportFormat();
      $pdf->c_setHeader('Gross Hold');
      $pdf->html.='<div style="text-align:center; ">As of ' . $dateFrom . ' To '.$dateTo.'</div><br/>';
      $pdf->SetFontSize(10);
      $pdf->c_tableHeader2(array(
                array('value'=>'Cashier'),
                array('value'=>'Total Deposit'),
                array('value'=>'Total Reload'),
                array('value'=>'Total Redemption'),
                array('value'=>'Gross Hold'),
                array('value'=>'')
             ));

      $completepdfvalues = array();
      $combined = array();
      if(count($result) > 0)
      {   
             $mergedep = 0;
             $mergerel = 0;
             $mergewith = 0; 
             $supdetails = array();
             foreach($result as $value) 
             {         
                if(!isset($supdetails[$value['CreatedByAID']])) 
                {
                     $mergedep = 0;
                     $mergerel = 0;
                     $mergewith = 0; 
                     $supdetails[$value['CreatedByAID']] = array(
                        'CreatedByAID'=>$value['CreatedByAID'],
                        'Name'=>$value['Name'],
                        'DateCreated'=>$value['DateCreated'],
                        'TerminalID'=>$value['TerminalID'],
                        'SiteID'=>$value['SiteID'],
                        'Withdrawal'=>$mergedep,
                        'Deposit'=>$mergerel,
                        'Reload'=>$mergewith
                     ); 
                }
                $trans = array();
                switch ($value['TransactionType']) 
                {
                    case 'W':
                        $mergewith = $mergewith + $value['Amount'];
                        $trans = array('Withdrawal'=>$mergewith);
                    break;
                    case 'D':
                        $mergedep = $mergedep + $value['Amount'];
                        $trans = array('Deposit'=>$mergedep);
                    break;
                    case 'R':
                        $mergerel = $mergerel + $value['Amount']; 
                        $trans = array('Reload'=>$mergerel);
                    break;
                }
                $supdetails[$value['CreatedByAID']] = array_merge($supdetails[$value['CreatedByAID']], $trans);
             }
             
             $ctr2 = 0;
             
            $loadcash = '0.00';
            $loadticket = '0.00';
            $loadcoupon = '0.00';
            $printedtickets = '0.00';
            $encashedtickets = '0.00';
            $redemptioncashier = '0.00';
            $manualredemption = 0.00;

           if(count($result2) > 0){
               while($ctr2 < count($result2))
                {
                    if($result2[$ctr2]['LoadCash'] != '0.00')
                        $loadcash = $result2[$ctr2]['LoadCash'];
                    if($result2[$ctr2]['LoadTicket'] != '0.00')
                        $loadticket = $result2[$ctr2]['LoadTicket'];
                    if($result2[$ctr2]['LoadCoupon'] != '0.00')
                        $loadcoupon = $result2[$ctr2]['LoadCoupon'];
                    if($result2[$ctr2]['PrintedTickets'] != '0.00')
                        $printedtickets = $result2[$ctr2]['PrintedTickets'];
                    if($result2[$ctr2]['EncashedTickets'] != '0.00')
                        $encashedtickets = $result2[$ctr2]['EncashedTickets'];
                    if($result2[$ctr2]['RedemptionCashier'] != '0.00')
                        $redemptioncashier = $result2[$ctr2]['RedemptionCashier'];
                    if($result2[$ctr2]['ManualRedemption'] != '0.00')
                        $manualredemption = (float)$result2[$ctr2]['ManualRedemption'];
                    $ctr2++;
                }
           }
           
            // count site cash on hand
            $vcashonhandamt = $loadcash - $redemptioncashier - $manualredemption - $encashedtickets;
             
             $vtotal = array();
             $vdeposit = array();
             $vreload = array();
             $vwithdraw = array();
             foreach($supdetails as $vview)
             {
                  $depositamt = $vview['Deposit'];
                  $reloadamt = $vview['Reload'];
                  $withdrawamt = $vview['Withdrawal'];
                  $grossholdamt = $depositamt + $reloadamt - $withdrawamt;
  
                  $combined = array($vview['Name'], '<span style="text-align: right;">'.number_format($depositamt, 2, '.', ',').'</span>', 
                      '<span style="text-align: right;">'.number_format($reloadamt, 2, '.', ',').'</span>', 
                      '<span style="text-align: right;">'.number_format($withdrawamt, 2, '.', ',').'</span>', 
                      '<span style="text-align: right;">'.number_format($grossholdamt, 2, '.', ',').'</span>','');
                  
                  $pdf->c_tableRow($combined);
                 /**** GET Total per page, stores in an array *****/
                    array_push($vtotal, $grossholdamt);
                    array_push($vdeposit, $depositamt);
                    array_push($vreload, $reloadamt);
                    array_push($vwithdraw, $withdrawamt);
             }
           
             unset($supdetails);
              
             /*** Get the Total sum of transaction types and gross hold***/
              $totalgh = array_sum($vtotal);
              $totaldeposit = array_sum($vdeposit);
              $totalreload = array_sum($vreload);
              $totalwithdraw = array_sum($vwithdraw);
                     
           //array variable to store an array of transaction types, GH; used on ajax call
           $arrtotal = array("TotalGH" => $totalgh, "TotalDeposit" => $totaldeposit, 
                         "TotalReload" => $totalreload, "TotalWithdraw" => $totalwithdraw);
           
           //array for displaying totals on excel file
           //array for displaying totals on excel file
            $totals1 = array(0 => '',
                      1 => '',
                      2 => '',
                      3 => '',
                      4 => '',
                      5 => ''
                     );
             $totals2 = array(0 => 'Summary per Page',
                        1 => '',
                        2 => 'Sales',
                        3 => '<span style="text-align: right;">'.number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',').'</span>',
                        4 => 'Redemption',
                        5 => '<span style="text-align: right;">'.number_format($arrtotal["TotalWithdraw"], 2, '.', ',').'</span>'
                       );
             $totals3 = array(0 => 'Grand Total',
                        1 => '',
                        2 => 'Total Sales',
                        3 => '<span style="text-align: right;">'.number_format($arrtotal["TotalDeposit"] + $arrtotal["TotalReload"], 2, '.', ',').'</span>',
                        4 => 'Total Redemption',
                        5 => '<span style="text-align: right;">'.number_format($arrtotal["TotalWithdraw"], 2, '.', ',').'</span>'
                       );
             $totals4 = array(0 => '       ',
                        1 => '',
                        2 => '     Cash',
                        3 =>'<span style="text-align: right;">'. number_format($loadcash, 2, '.', ',').'</span>',
                        4 => 'Printed Tickets',
                        5 => '<span style="text-align: right;">'.number_format($printedtickets, 2, '.', ',').'</span>'
                       );
             $totals5 = array(0 => '       ',
                        1 => '',
                        2 => '     Tickets',
                        3 => '<span style="text-align: right;">'.number_format($loadticket, 2, '.', ',').'</span>',
                        4 => 'Encashed Tickets',
                        5 => '<span style="text-align: right;">'.number_format($encashedtickets, 2, '.', ',').'</span>'
                       );
             $totals6 = array(0 => '       ',
                        1 => '     ',
                        2 => '     Coupons',
                        3 => '<span style="text-align: right;">'.number_format($loadcoupon, 2, '.', ',').'</span>',
                        4 => 'Cash On Hand',
                        5 => '<span style="text-align: right;">'.number_format($vcashonhandamt, 2, '.', ',').'</span>'
                       );

           //array_push($combined, $totals); 
           $pdf->c_tableRow($totals1);
           $pdf->c_tableRow($totals2);
           $pdf->c_tableRow($totals3);
           $pdf->c_tableRow($totals4);
           $pdf->c_tableRow($totals5);
           $pdf->c_tableRow($totals6);
        }
        else
        {
            $pdf->html.='<div style="text-align:center;">No Results Found</div>';
        }
      $pdf->c_tableEnd();
      unset($totals,$arrtotal, $vtotal, $vdeposit, $vreload, $vwithdraw);
      $vauditfuncID = 40; //export to pdf
      $vtransdetails = "Gross Hold";
      $orptsup->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
      $pdf->c_generatePDF('GrossHold.pdf'); 
   }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>