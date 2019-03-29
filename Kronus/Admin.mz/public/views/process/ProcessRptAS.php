<?php
/**
* Created By: Edson L. Perez
* Created On: October 28, 2011
* Purpose: process for other requested reports (audit trail)
*/
include __DIR__."/../sys/class/ApplicationSupport.class.php";
require  __DIR__."/../sys/core/init.php";
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

$appsupport = new ApplicationSupport($_DBConnectionString[0]);
$connected = $appsupport->open();

$nopage = 0;
if($connected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $appsupport->getDate();
    /********** SESSION CHECKING **********/
    $isexist=$appsupport->checksession($aid);
    if($isexist == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $appsupport->close();
        
        if($appsupport->isAjaxRequest())
        {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        
        header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$appsupport->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
        session_destroy();
        $msg = "Not Connected";
        $appsupport->close();
        header("Location: login.php?mess=".$msg);
    }
    /********** END SESSION CHECKING **********/

    //checks if account was locked 
    $islocked = $appsupport->chkLoginAttempts($aid);
    if(isset($islocked['LoginAttempts']))
    {
        $loginattempts = $islocked['LoginAttempts'];
        if($loginattempts >= 3)
        {
            $appsupport->deletesession($aid);
            session_destroy();
            $msg = "Not Connected";
            $appsupport->close();
            header("Location: login.php?mess=".$msg);
            exit;
        }
    }

    /***************************** EXPORTING EXCEL STARTS HERE *******************************/
    // EDIT CCT 12/14/2018 BEGIN
    //if(isset($_GET['excel']) == "MCFHistory")
    if(isset($_GET['excel']))
    // EDIT CCT 12/14/2018 END
    {  
        // ADDED CCT 12/14/2018 BEGIN
        if($_GET['excel'] == "MCFHistory")
        {
        // ADDED CCT 12/14/2018 END
            //check if for ewallet or old MCF
            if (isset($_GET['IsEwallet']) == true) 
            { //Ewallet
                $datefrom = $_GET['DateFrom'];
                //$dateto = $_GET['DateTo'];
                $site= $_GET['Site'];
                $cardnumber = (trim($_GET['CardNumber']) != "") ? $cardnumber = $_GET['CardNumber'] : $cardnumber = null;
                $status = $_GET['Status'];
                $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));

                $fn = "MeSAFEFHistory_".$datefrom."_".$dateto.".xls"; //this will be the filename of the excel file
                //create the instance of the exportexcel format
                $excel_obj = new ExportExcel("$fn");
                //setting the values of the headers and data of the excel file
                //and these values comes from the other file which file shows the data
                $rheaders = array('Site', 'Transaction Type', 'Amount', 'Service Name', 'Transaction Date', 'Status', 'User Mode', 'Fulfilled By' );
                $completeexcelvalues = array();

                $datefrom = $datefrom.' 06:00:00';
                $dateto = $dateto.' 06:00:00';
                $start = null;
                $limit = null;

                $result = $appsupport->selectewallettransdtls($status, $datefrom, $dateto, $start, $limit, $cardnumber, $site);
                if(count($result) > 0)
                {                
                    foreach($result as $vview)
                    {    
                        switch( $vview['Status'])
                        {
                            case 0: $vstatus = 'Pending';break;
                            case 3: $vstatus = 'Fulfillment Approved';break;
                            case 4: $vstatus = 'Fulfillment Denied'; break;   
                            default: $vstatus = 'All'; break;
                        } 

                        switch($vview['TransType'])
                        {
                            case 'D': $vtranstype = 'Deposit';break;
                            case 'W': $vtranstype = 'Withdrawal';break;
                            case 'R': $vtranstype = 'Reload';break;
                            case 'RD': $vtranstype = 'Redeposit';break;
                        }    

                        switch ($vview['UserMode']) 
                        {
                            case 0: $usermode = 'Terminal Based'; break;
                            case 1: $usermode = 'User Based'; break;
                            case 2: $usermode = 'Terminal Based'; break;
                            // CCT ADDED 07/25/2018 BEGIN
                            case 3: $usermode = 'User Based'; break;
                            // CCT ADDED 07/25/2018 END
                            case 4: $usermode = 'Terminal Based'; break;
                            default: break;
                        }

                        $name = $appsupport->getNamebyAid($vview['UpdatedByAID']);

                        $results2 = $appsupport->getsitecode($site);
                        $results2 = $results2['SiteCode'];
                        $sitecode = preg_split("/ICSA-/", $vview['SiteCode']);
                        if (!is_null($vview['TerminalID']))
                        {
                            $getTerminalCode = $appsupport->getTerminalCode($vview['TerminalID']);
                            $terminalcode = $getTerminalCode['TerminalCode'];
                        }
                        else
                        {
                            $terminalcode = "N/A";
                        }
                        $excelvalues = array($sitecode[1],$vtranstype, number_format($vview['Amount'],2),$vview['ServiceName'],$vview['StartDate'],$vstatus, $usermode,$name);   
                        array_push($completeexcelvalues, $excelvalues);
                    }
                }

                $vauditfuncID = 41; //export to excel
                $vtransdetails = "";
                $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
            }
            else 
            { //Old MCF
                $datefrom = $_GET['DateFrom'];
                //$dateto = $_GET['DateTo'];
                $site= $_GET['Site'];
                $terminal = $_GET['Terminal'];
                $status = $_GET['Status'];

                $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
                //create the instance of the exportexcel format
                $excel_obj = new ExportExcel("$fn");
                //setting the values of the headers and data of the excel file
                //and these values comes from the other file which file shows the data
                $rheaders = array('Site','Terminal', 'Transaction Type', 'Amount', 'Service Name', 'Transaction Date', 'Status', 'User Mode', 'Fulfilled By' );
                $completeexcelvalues = array();

                $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
                $datefrom = $datefrom.' 06:00:00';
                $dateto = $dateto.' 06:00:00';

                $result = $appsupport->exportfulfillmenthistroy($site, $terminal, $status, $datefrom, $dateto);

                if(count($result) > 0)
                {                
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

                        switch ($vview['UserMode']) 
                        {
                            case 0: $usermode = 'Terminal Based'; break;
                            case 1: $usermode = 'User Based'; break;
                            case 2: $usermode = 'Terminal Based'; break;
                            // CCT ADDED 07/25/2018 BEGIN
                            case 3: $usermode = 'User Based'; break;
                            // CCT ADDED 07/25/2018 END
                            case 4: $usermode = 'Terminal Based'; break;
                            default: break;
                        }

                        $name = $appsupport->getNamebyAid($vview['CreatedByAID']);

                        $results2 = $appsupport->getsitecode($site);
                        $results2 = $results2['SiteCode'];
                        $results = preg_split("/$results2/", $vview['TerminalCode']);
                        $sitecode = preg_split("/ICSA-/", $vview['SiteCode']);
                        $excelvalues = array($sitecode[1],$results[1],$vtranstype, number_format($vview['Amount'],2),$vview['ServiceName'],$vview['TransactionDate'],$vstatus, $usermode,$name);   
                        array_push($completeexcelvalues, $excelvalues);
                    }
                }

                $vauditfuncID = 41; //export to excel
                $vtransdetails = "";
                $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
            }
        // ADDED CCT 12/14/2018 BEGIN    
        }
        elseif($_GET['excel'] == "MLCFHistory")
        {
            $datefrom = $_GET['DateFrom'];
            $site= $_GET['Site'];
            $terminal = $_GET['Terminal'];
            $status = $_GET['Status'];

            $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
            //create the instance of the exportexcel format
            $excel_obj = new ExportExcel("$fn");
            //setting the values of the headers and data of the excel file
            //and these values comes from the other file which file shows the data
            $rheaders = array('Terminal', 'Transfer ID', 'UB Card', 
                            'From Type', 'Amount', 'Service', 'Start Date', 'End Date', 'Service Trans ID', 'Service Status', 'Status', 'By', 
                            'To Type', 'Amount', 'Service', 'Start Date', 'End Date', 'Service Trans ID',  'Service Status', 'Status', 'By', 
                            'Transfer Status' );
            $completeexcelvalues = array();

            $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
            $datefrom = $datefrom.' 06:00:00';
            $dateto = $dateto.' 06:00:00';

            $result = $appsupport->exportmlpcfulfillmenthistroy($site, $terminal, $status, $datefrom, $dateto);

            if(count($result) > 0)
            {                
                foreach($result as $vview)
                {   
                    switch ($vview['FromTransactionType']) 
                    {
                        case 'D': $vfromtranstype = 'Deposit';
                            break;
                        case 'W': $vfromtranstype = 'Withdrawal';
                            break;
                        case 'RD': $vfromtranstype = 'Re-Deposit';
                            break;
                    }        

                    switch ($vview['FromStatus']) 
                    {
                        case 0: $vfromstatus = 'Pending';
                            break;
                        case 1: $vfromstatus = 'Successful';
                            break;
                        case 2: $vfromstatus = 'Failed';
                            break;
                        case 3: $vfromstatus = 'Fulfillment Approved';
                            break;
                        case 4: $vfromstatus = 'Fulfillment Denied';
                            break;
                        default: $vfromstatus = '';
                            break;                            
                    }                        

                    switch ($vview['ToTransactionType']) 
                    {
                        case 'D': $vtotranstype = 'Deposit';
                            break;
                        case 'W': $vtotranstype = 'Withdrawal';
                            break;
                        case 'RD': $vtotranstype = 'Re-Deposit';
                            break;
                        default: $vtotranstype = '';
                            break;                                
                    }        

                    switch ($vview['ToStatus']) 
                    {
                        case 0: $vtostatus = 'Pending';
                            break;
                        case 1: $vtostatus = 'Successful';
                            break;
                        case 2: $vtostatus = 'Failed';
                            break;
                        case 3: $vtostatus = 'Fulfillment Approved';
                            break;
                        case 4: $vtostatus = 'Fulfillment Denied';
                            break;
                        default: $vtostatus = '';
                            break;
                    }              

                    switch ($vview['TransferStatus']) 
                    {
                        case 0: $vtransferstatus = 'Pending Withdrawal';
                            break;
                        case 1: $vtransferstatus = 'Successful Withdrawal';
                            break;
                        case 2: $vtransferstatus = 'Failed Withdrawal';
                            break;
                        case 3: $vtransferstatus = 'Pending Deposit';
                            break;
                        case 4: $vtransferstatus = 'Successful Deposit';
                            break;
                        case 5: $vtransferstatus = 'Failed Deposit';
                            break;
                        case 6: $vtransferstatus = 'Pending Re-Deposit';
                            break;
                        case 7: $vtransferstatus = 'Successful Re-Deposit';
                            break;
                        case 8: $vtransferstatus = 'Failed Re-Deposit';
                            break;
                        case 9: $vtransferstatus = 'Successful transfer wallet (zero balance)';
                            break;
                        case 90: $vtransferstatus = 'Balance not zero after Withdrawal';
                            break;
                        case 91: $vtransferstatus = 'Balance not equal to withdrawn amount';
                            break;
                        case 92: $vtransferstatus = 'Re-Deposit amount not equal to withdrawn amount';
                            break;
                        case 93: $vtransferstatus = 'Re-Deposit, balance of current casino is not zero';
                            break;
                        case 100: $vtransferstatus = 'Manual Redemption (Floating Balance Fulfillment)';
                            break;
                        case 101: $vtransferstatus = 'Manual Redemption (LaunchPad Casino Fulfillment)';
                            break;                          
                    }   

                    $fromname = $appsupport->getNamebyAid($vview['FromUpdatedByAID']);
                    $toname = $appsupport->getNamebyAid($vview['ToUpdatedByAID']);

                    $excelvalues = 
                            array($vview['TerminalCode'], $vview['TransferID'], $vview['LoyaltyCardNumber'],
                                $vfromtranstype, number_format($vview['FromAmount'], 2),  $vview['FromServiceID'],
                                $vview['FromStartTransDate'], $vview['FromEndTransDate'], $vview['FromServiceTransID'],
                                $vview['FromServiceStatus'], $vfromstatus, $fromname, 
                                $vtotranstype, number_format($vview['ToAmount'], 2), $vview['ToServiceID'],
                                $vview['ToStartTransDate'], $vview['ToEndTransDate'], $vview['ToServiceTransID'],
                                $vview['ToServiceStatus'], $vtostatus, $toname,
                                $vtransferstatus);   
                    array_push($completeexcelvalues, $excelvalues);
                }
            }
            $vauditfuncID = 41; //export to excel
            $vtransdetails = "";
            $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
            $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
            $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
        }
        // ADDED CCT 12/14/2018 END
    }
    // EDIT CCT 12/14/2018 BEGIN
    //elseif(isset($_GET['pdf']) == 'MCFHistory')
    elseif(isset($_GET['pdf']))
    // EDIT CCT 12/14/2018 END
    {
        //ADDED CCT 12/14/2018 BEGIN
        if ($_GET['pdf'] == 'MCFHistory')
        {
        //ADDED CCT 12/14/2018 END
            //check if for ewallet or old MCF
            if (isset($_GET['IsEwallet']) == true) 
            {
                $completePDFArray = array();
                $datefrom = $_GET['DateFrom'];
                //$dateto = $_GET['DateTo'];
                $site= $_GET['Site'];

                $cardnumber = (trim($_GET['CardNumber']) != "") ? $cardnumber = $_GET['CardNumber'] : $cardnumber = null;
                $status = $_GET['Status'];

                $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
                $datefrom = $datefrom.' 06:00:00';
                $dateto = $dateto.' 06:00:00';
                $start = null;
                $limit = null;

                $queries = $appsupport->selectewallettransdtls($status, $datefrom, $dateto, $start, $limit, $cardnumber, $site);

                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('Manual e-SAFE Fulfillment History');
                $pdf->html.='<div style="text-align:center;">From ' . $datefrom . ' to '.$dateto.'</div>';
                $pdf->SetFontSize(10);
                $pdf->c_tableHeader2(
                    array(
                        array('value'=>'Site', 'width' => '40px'),
                        array('value'=>'Transaction Type'),
                        array('value'=>'Amount'),
                        array('value'=>'Service Name'),
                        array('value'=>'Transaction Date'),
                        array('value'=>'Status'),
                        array('value'=>'User Mode'),
                        array('value'=>'Fulfilled By','width' => '100px')
                    )
                );

                if(count($queries) > 0)
                {
                    foreach($queries as $vview)
                    {
                        switch( $vview['Status'])
                        {
                            case 0: $vstatus = 'Pending';break;
                            case 3: $vstatus = 'Fulfillment Approved';break;
                            case 4: $vstatus = 'Fulfillment Denied'; break;   
                            default: $vstatus = 'All'; break;
                        } 

                        switch($vview['TransType'])
                        {
                            case 'D': $vtranstype = 'Deposit';break;
                            case 'W': $vtranstype = 'Withdrawal';break;
                            case 'R': $vtranstype = 'Reload';break;
                            case 'RD': $vtranstype = 'Redeposit';break;
                        }    

                        switch ($vview['UserMode']) 
                        {
                            case 0: $usermode = 'Terminal Based'; break;
                            case 1: $usermode = 'User Based'; break;
                            case 2: $usermode = 'Terminal Based'; break;
                            // CCT ADDED 07/25/2018 BEGIN
                            case 3: $usermode = 'User Based'; break;
                            // CCT ADDED 07/25/2018 END
                            case 4: $usermode = 'Terminal Based'; break;
                            default: break;
                        }

                        $name = $appsupport->getNamebyAid($vview['UpdatedByAID']);

                        $results2 = $appsupport->getsitecode($site);
                        $results2 = $results2['SiteCode'];
                        $sitecode = preg_split("/ICSA-/", $vview['SiteCode']);

                        if (!is_null($vview['TerminalID']))
                        {
                            $getTerminalCode = $appsupport->getTerminalCode($vview['TerminalID']);
                            $terminalcode = trim(str_replace("ICSA-", "", $getTerminalCode['TerminalCode']));
                        }
                        else
                        {
                            $terminalcode = "N/A";
                        }
                        $pdf->c_tableRow2(
                            array(
                                array('value'=>$sitecode[1], 'width' => '40px'),
                                array('value'=>$vtranstype),
                                array('value'=>number_format($vview['Amount'],2), 'align' => 'right'),
                                array('value'=>$vview['ServiceName']),
                                array('value'=>$vview['StartDate']),
                                array('value'=>$vstatus),
                                array('value'=>$usermode),
                                array('value'=>$name, 'width' => '100px'),
                                )
                            );
                    }
                }
                else
                {
                    $pdf->html.='<div style="text-align:center;">No Results Found</div>';
                }

                $pdf->c_tableEnd();
                $vauditfuncID = 40; //export to pdf
                $vtransdetails = "";
                $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $pdf->c_generatePDF('MeSAFEFHistory_from_'."$datefrom".'_to_'."$dateto".'.pdf'); 
            }
            else 
            {
                $completePDFArray = array();
                $datefrom = $_GET['DateFrom'];
                //$dateto = $_GET['DateTo'];
                $site= $_GET['Site'];
                $terminal = $_GET['Terminal'];
                $status = $_GET['Status'];

                $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
                $datefrom = $datefrom.' 06:00:00';
                $dateto = $dateto.' 06:00:00';

                $queries = $appsupport->exportfulfillmenthistroy($site, $terminal, $status, $datefrom, $dateto);

                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('Manual Casino Fulfillment History');
                $pdf->html.='<div style="text-align:center;">From ' . $datefrom . ' to '.$dateto.'</div>';
                $pdf->SetFontSize(10);
                $pdf->c_tableHeader2(
                    array(
                        array('value'=>'Site', 'width' => '50px'),
                        array('value'=>'Terminal', 'width' => '50px'),
                        array('value'=>'Transaction Type'),
                        array('value'=>'Amount'),
                        array('value'=>'Service Name'),
                        array('value'=>'Transaction Date'),
                        array('value'=>'Status'),
                        array('value'=>'User Mode'),
                        array('value'=>'Fulfilled By', 'width' => '100px')
                    )
                );

                if(count($queries) > 0)
                {
                    foreach($queries as $vview)
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

                        switch ($vview['UserMode']) 
                        {
                            case 0: $usermode = 'Terminal Based'; break;
                            case 1: $usermode = 'User Based'; break;
                            case 2: $usermode = 'Terminal Based'; break;
                            // CCT ADDED 07/25/2018 BEGIN
                            case 3: $usermode = 'User Based'; break;
                            // CCT ADDED 07/25/2018 END
                            case 4: $usermode = 'Terminal Based'; break;
                            default: break;
                        }

                        $name = $appsupport->getNamebyAid($vview['CreatedByAID']);

                        $results2 = $appsupport->getsitecode($site);
                        $results2 = $results2['SiteCode'];
                        $results = preg_split("/$results2/", $vview['TerminalCode']);

                        $sitecode = preg_split("/ICSA-/", $vview['SiteCode']);
                        $pdf->c_tableRow2(
                            array(
                                array('value'=>$sitecode[1], 'width' => '50px'),
                                array('value'=> $results[1], 'width' => '50px'),
                                array('value'=>$vtranstype),
                                array('value'=>number_format($vview['Amount'],2), 'align' => 'right'),
                                array('value'=>$vview['ServiceName']),
                                array('value'=>$vview['TransactionDate']),
                                array('value'=>$vstatus),
                                array('value'=>$usermode),
                                array('value'=>$name, 'width' => '100px'),
                            )
                        );
                    }
                }
                else
                {
                    $pdf->html.='<div style="text-align:center;">No Results Found</div>';
                }

                $pdf->c_tableEnd();
                $vauditfuncID = 40; //export to pdf
                $vtransdetails = "";
                $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $pdf->c_generatePDF('MCFHistory_from_'."$datefrom".'_to_'."$dateto".'.pdf'); 
            }
        //ADDED CCT 12/14/2018 BEGIN
        }
        elseif ($_GET['pdf'] == 'MLCFHistory')
        {
            $completePDFArray = array();
            $datefrom = $_GET['DateFrom'];
            $site= $_GET['Site'];
            $terminal = $_GET['Terminal'];
            $status = $_GET['Status'];

            $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($datefrom)));
            $datefrom = $datefrom.' 06:00:00';
            $dateto = $dateto.' 06:00:00';

            $queries = $appsupport->exportmlpcfulfillmenthistroy($site, $terminal, $status, $datefrom, $dateto);

            $pdf = CTCPDF::c_getInstance();
            $pdf->c_commonReportFormat();
            $pdf->c_setHeader('Manual LaunchPad Casino Fulfillment History');
            $pdf->html.='<div style="text-align:center;">From ' . $datefrom . ' to '.$dateto.'</div>';
            $pdf->SetFontSize(4);
            $pdf->c_tableHeader2(
                array(
                    array('value'=>'Terminal', 'width' => '35px'),
                    array('value'=>'Transfer ID', 'width' => '30px'),
                    array('value'=>'UB Card', 'width' => '35px'),
                    array('value'=>'From Type', 'width' => '30px'),
                    array('value'=>'Amount', 'width' => '30px'),
                    array('value'=>'Service', 'width' => '35px'),
                    array('value'=>'Start Date', 'width' => '40px'),
                    array('value'=>'End Date', 'width' => '40px'),
                    array('value'=>'Service Trans ID', 'width' => '30px'),
                    array('value'=>'Service Status', 'width' => '30px'),
                    array('value'=>'Status', 'width' => '30px'),                
                    array('value'=>'By', 'width' => '40px'),
                    array('value'=>'To Type', 'width' => '30px'),
                    array('value'=>'Amount', 'width' => '30px'),
                    array('value'=>'Service', 'width' => '50px'),
                    array('value'=>'Start Date', 'width' => '40px'),
                    array('value'=>'End Date', 'width' => '40px'),
                    array('value'=>'Service Trans ID', 'width' => '30px'),
                    array('value'=>'Service Status', 'width' => '30px'),
                    array('value'=>'Status', 'width' => '30px'),                
                    array('value'=>'By', 'width' => '40px'),
                    array('value'=>'Transfer Status', 'width' => '40px')
                )
            );
                                    
            if(count($queries) > 0)
            {
                foreach($queries as $vview)
                {
                    switch ($vview['FromTransactionType']) 
                    {
                        case 'D': $vfromtranstype = 'Deposit';
                            break;
                        case 'W': $vfromtranstype = 'Withdrawal';
                            break;
                        case 'RD': $vfromtranstype = 'Re-Deposit';
                            break;
                    }        

                    switch ($vview['FromStatus']) 
                    {
                        case 0: $vfromstatus = 'Pending';
                            break;
                        case 1: $vfromstatus = 'Successful';
                            break;
                        case 2: $vfromstatus = 'Failed';
                            break;
                        case 3: $vfromstatus = 'Fulfillment Approved';
                            break;
                        case 4: $vfromstatus = 'Fulfillment Denied';
                            break;
                        default: $vfromstatus = '';
                            break;                            
                    }                        

                    switch ($vview['ToTransactionType']) 
                    {
                        case 'D': $vtotranstype = 'Deposit';
                            break;
                        case 'W': $vtotranstype = 'Withdrawal';
                            break;
                        case 'RD': $vtotranstype = 'Re-Deposit';
                            break;
                        default: $vtotranstype = '';
                            break;                                
                    }        

                    switch ($vview['ToStatus']) 
                    {
                        case 0: $vtostatus = 'Pending';
                            break;
                        case 1: $vtostatus = 'Successful';
                            break;
                        case 2: $vtostatus = 'Failed';
                            break;
                        case 3: $vtostatus = 'Fulfillment Approved';
                            break;
                        case 4: $vtostatus = 'Fulfillment Denied';
                            break;
                        default: $vtostatus = '';
                            break;
                    }              

                    switch ($vview['TransferStatus']) 
                    {
                        case 0: $vtransferstatus = 'Pending Withdrawal';
                            break;
                        case 1: $vtransferstatus = 'Successful Withdrawal';
                            break;
                        case 2: $vtransferstatus = 'Failed Withdrawal';
                            break;
                        case 3: $vtransferstatus = 'Pending Deposit';
                            break;
                        case 4: $vtransferstatus = 'Successful Deposit';
                            break;
                        case 5: $vtransferstatus = 'Failed Deposit';
                            break;
                        case 6: $vtransferstatus = 'Pending Re-Deposit';
                            break;
                        case 7: $vtransferstatus = 'Successful Re-Deposit';
                            break;
                        case 8: $vtransferstatus = 'Failed Re-Deposit';
                            break;
                        case 9: $vtransferstatus = 'Successful transfer wallet (zero balance)';
                            break;
                        case 90: $vtransferstatus = 'Balance not zero after Withdrawal';
                            break;
                        case 91: $vtransferstatus = 'Balance not equal to withdrawn amount';
                            break;
                        case 92: $vtransferstatus = 'Re-Deposit amount not equal to withdrawn amount';
                            break;
                        case 93: $vtransferstatus = 'Re-Deposit, balance of current casino is not zero';
                            break;
                        case 100: $vtransferstatus = 'Manual Redemption (Floating Balance Fulfillment)';
                            break;
                        case 101: $vtransferstatus = 'Manual Redemption (LaunchPad Casino Fulfillment)';
                            break;                         
                    }   

                    $fromname = $appsupport->getNamebyAid($vview['FromUpdatedByAID']);
                    $toname = $appsupport->getNamebyAid($vview['ToUpdatedByAID']);

                    $pdf->c_tableRow2(
                        array(
                            array('value'=>$vview['TerminalCode'], 'width' => '35px'),
                            array('value'=>$vview['TransferID'], 'width' => '30px'),
                            array('value'=>$vview['LoyaltyCardNumber'], 'width' => '35px'),
                            array('value'=>$vfromtranstype, 'width' => '30px'),
                            array('value'=>number_format($vview['FromAmount'], 2), 'align' => 'right', 'width' => '30px'),
                            array('value'=>$vview['FromServiceID'], 'width' => '35px'),
                            array('value'=>$vview['FromStartTransDate'], 'width' => '40px'),
                            array('value'=>$vview['FromEndTransDate'], 'width' => '40px'),
                            array('value'=>$vview['FromServiceTransID'], 'width' => '30px'),
                            array('value'=>$vview['FromServiceStatus'], 'width' => '30px'),
                            array('value'=>$vfromstatus, 'width' => '30px'),
                            array('value'=>$fromname, 'width' => '40px'),
                            array('value'=>$vtotranstype, 'width' => '30px'),
                            array('value'=>number_format($vview['ToAmount'], 2), 'align' => 'right', 'width' => '30px'),
                            array('value'=>$vview['ToServiceID'], 'width' => '50px'),
                            array('value'=>$vview['ToStartTransDate'], 'width' => '40px'),
                            array('value'=>$vview['ToEndTransDate'], 'width' => '40px'),
                            array('value'=>$vview['ToServiceTransID'], 'width' => '30px'),
                            array('value'=>$vview['ToServiceStatus'], 'width' => '30px'),
                            array('value'=>$vtostatus, 'width' => '30px'),
                            array('value'=>$toname, 'width' => '40px'),
                            array('value'=>$vtransferstatus, 'width' => '40px')
                        )
                    );
                }
            }
            else
            {
                $pdf->html.='<div style="text-align:center;">No Results Found</div>';
            }

            $pdf->c_tableEnd();
            $vauditfuncID = 40; //export to pdf
            $vtransdetails = "";
            $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
            $pdf->c_generatePDF('MLCFHistory_from_'."$datefrom".'_to_'."$dateto".'.pdf'); 
        }
        //ADDED CCT 12/14/2018 END
    }
}
else
{
    $msg = "Not Connected";  
    header("Location: login.php?mess=".$msg);    
}
?>