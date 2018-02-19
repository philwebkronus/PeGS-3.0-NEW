<?php
/**
 * Date Created10 3, 11 4:35:09 PM
 * Description of ProcessTopUpGeneratePDF
 * @author Bryan Salazar
 * modified by Edson L. Perez
 */

include_once __DIR__.'/../sys/class/CTCPDF.php';
include_once __DIR__.'/../sys/class/TopUpReportQuery.php';
include_once 'BaseProcess.php';

class ProcessTopUpGenerateReports extends BaseProcess
{
    // CCT ADDED 02/19/2018 BEGIN
    //for gross hold monitoring per cut off (Excel)
    public function grossHoldCutoffExcelPAGCOR() 
    {
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        $_SESSION['report_header'] = array('Site / PEGS Code','Cut Off Date','Beginning Balance','Deposit', 'e-SAFE Loads', 'Reload','Redemption', 'e-SAFE Withdrawal','Manual Redemption','Printed Tickets','Active Tickets for the Day','Coupon','Cash on Hand', 'Replenishment','Collection','Ending Balance');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vsitecode = $_POST['selsitecode'];
        $datenow = date("Y-m-d")." ".BaseProcess::$cutoff;
        $rows = array();
        $new_rows = array();
        
        //check if queried date is date today
        if($datenow != $startdate)
        {
            $servProvider = $_GET['servProviderID'];
            $rows = $topreport->getoldGHCutoffPAGCOR($startdate, $enddate, $vsitecode, $servProvider);
        }
        
        if(count($rows) > 0)
        {
            foreach($rows as $row) 
            {
                $grosshold = (($row['InitialDeposit'] + $row['Reload']) - $row['Redemption']) - $row['ManualRedemption'];
                if ($startdate < BaseProcess::$deploymentdate) 
                {
                    $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon']) - ($row['RedemptionCashier'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                }
                else 
                {
                    $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon'] + $row['LoadTickets']) - ($row['TotalRedemption'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                }
                $endbal = $cashonhand + $row['Replenishment'] - $row['Collection'];
                $new_rows[] = array(
                                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                                $row['CutOff'],
                                number_format($row['BegBal'],2),
                                number_format($row['InitialDeposit'],2),
                                number_format($row['EwalletLoads'],2),
                                number_format($row['Reload'],2),
                                number_format($row['Redemption'],2),
                                number_format($row['EwalletWithdraw'],2),
                                number_format($row['ManualRedemption'],2),
                                number_format($row['PrintedTickets'],2),
                                number_format($row['UnusedTickets'],2),
                                number_format($row['Coupon']+$row['ewalletCoupon'],2),
                                number_format($cashonhand,2),
                                number_format($row['Replenishment'],2),
                                number_format($row['Collection'],2),
                                number_format($endbal,2),
                );
            }
        }
        
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'grossholdpercutoffPAGCOR';
        include 'ProcessTopUpExcel.php';
        unset($new_rows, $rows, $startdate, $enddate, $datenow);
        $topreport->close();
    }    
    
    public function grossHoldCutoffPdfPAGCOR() 
    {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '180');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;    
        $vsitecode = $_POST['selsitecode'];
        $datenow = date("Y-m-d")." ".BaseProcess::$cutoff;
        $rows = array();
        
        //check if queried date is date today
        if($datenow != $startdate)
        {
            $servProvider = $_GET['servProviderID'];
            $rows = $topreport->getoldGHCutoffPAGCOR($startdate, $enddate, $vsitecode, $servProvider);
        }
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold Monitoring Per Cut-off');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(6);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Cut Off Date', 'width' => '70px'),
                array('value'=>'Beginning Balance'),
                array('value'=>'Deposit'),
                array('value'=>'e-SAFE Loads'),
                array('value'=>'Reload'),
                array('value'=>'Redemption'),
                array('value'=>'e-SAFE Withdrawal'),
                array('value'=>'Manual Redemption'),
                array('value'=>'Printed Tickets'),
                array('value'=>'Active Tickets for the Day'),
                array('value'=>'Coupon'),
                array('value'=>'Cash on Hand'),
                array('value'=>'Replenishment'),
                array('value'=>'Collection'),
                array('value'=>'Ending Balance')
             ));
        
        if(count($rows) > 0)
        {
            foreach($rows as $row) 
            {
                $grosshold = (($row['InitialDeposit'] + $row['Reload']) - $row['Redemption']) - $row['ManualRedemption'];
                if ($startdate < BaseProcess::$deploymentdate) 
                {
                     $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon']) - ($row['RedemptionCashier'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                }
                else 
                {
                    $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon'] + $row['LoadTickets']) - ($row['TotalRedemption'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                }
                $endbal = $cashonhand + $row['Replenishment'] - $row['Collection'];
                $pdf->c_tableRow2(array(
                    array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                    array('value'=> $row['CutOff'], 'width' => '70px'),
                    array('value'=>number_format($row['BegBal'],2), 'align' => 'right'),
                    array('value'=>number_format($row['InitialDeposit'],2), 'align' => 'right'),
                    array('value'=>number_format($row['EwalletLoads'],2), 'align' => 'right'),
                    array('value'=>number_format($row['Reload'],2), 'align' => 'right'),
                    array('value'=>number_format($row['Redemption'],2), 'align' => 'right'),
                    array('value'=>number_format($row['EwalletWithdraw'],2), 'align' => 'right'),
                    array('value'=>number_format($row['ManualRedemption'], 2), 'align' => 'right'),
                    array('value'=>number_format($row['PrintedTickets'], 2), 'align' => 'right'),
                    array('value'=>number_format($row['UnusedTickets'], 2), 'align' => 'right'),
                    array('value'=>number_format($row['Coupon'] + $row['ewalletCoupon'], 2), 'align' => 'right'),
                    array('value'=>number_format($cashonhand, 2), 'align' => 'right'),
                    array('value'=>number_format($row['Replenishment'],2), 'align' => 'right'),
                    array('value'=>number_format($row['Collection'],2), 'align' => 'right'),
                    array('value'=>number_format($endbal,2), 'align' => 'right'),
                 ));
            }
        }
        
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('grossholdpercutoffPAGCOR.pdf');
        unset($startdate, $venddate, $enddate, $vsitecode, $datenow, $rows);
        $topreport->close();
    } 
    // CCT ADDED 02/19/2018 END
    
    //Gross Hold Monitoring Report (PDF)
    public function grossHoldMonPdf() 
    {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $siteID = $_POST['selSiteCode'];
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        //$venddate = $_POST['enddate'];  
        //$enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;
        $rows = $topreport->grossHolMonitoring($startdate, $enddate, $siteID);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold Monitoring');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Name'),
                array('value'=>'Site / PEGS Code'),
                array('value'=>'POS Account'),
                array('value'=>'BCF'),
                array('value'=>'Deposit'),
                array('value'=>'Reload'),
                array('value'=>'Withdrawal'),
                array('value'=>'Manual Redemption'),
                array('value'=>'Gross Hold'),
                array('value'=>'With Confirmation'),
                array('value'=>'Location'),
             ));
        foreach($rows as $row) 
        {
            $gross_hold = (($row['Deposit'] + $row['Reload'] - $row['Withdrawal']) - $row['ActualAmount']);
            if($_POST['selwithconfirmation'] != '') 
            {
                if($row['withconfirmation'] != $_POST['selwithconfirmation']) 
                {
                    continue;
                }
            }
            
            if($_POST['sellocation'] != '') 
            {
                if($row['PickUpTag'] != $_POST['sellocation']) 
                {
                    continue;
                }
            }
            
            $pdf->c_tableRow2(array(
                array('value'=>$row['SiteName']),
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['POSAccountNo']),
                array('value'=>number_format($row['Balance'],2)),
                array('value'=>number_format($row['Deposit'],2)),
                array('value'=>number_format($row['Reload'],2)),
                array('value'=>number_format($row['Withdrawal'],2)),
                array('value'=>number_format($row['ActualAmount'],2)),
                array('value'=>number_format($gross_hold,2)),
                array('value'=>$row['withconfirmation']),
                array('value'=>$row['PickUpTag'])
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('GrossHoldMonitoring.pdf');
        $topreport->close();
    }
    
    //Gross Hold Monitoring Report (Excel)
    public function grossHoldMonExcel() 
    {
        $siteID = $_POST['selSiteCode'];
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        //$venddate = $_POST['enddate'];  
        //$enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;   
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;   
        $_SESSION['report_header'] = array('Site / PEGS Name','Site / PEGS Code', 'POS Account','BCF','Deposit','Reload','Withdrawal','Manual Redemption','Gross Hold', 'With Confirmation','Location');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->grossHolMonitoring($startdate, $enddate, $siteID);
        
        $new_rows = array();
        foreach($rows as $row) 
        {
            $gross_hold = (($row['Deposit'] + $row['Reload'] - $row['Withdrawal']) - $row['ActualAmount']);
            if($_POST['selwithconfirmation'] != '') 
            {
                if($row['withconfirmation'] != $_POST['selwithconfirmation']) 
                {
                    continue;
                }
            }
            if($_POST['sellocation'] != '') 
            {
                if($row['PickUpTag'] != $_POST['sellocation']) 
                {
                    continue;
                }
            }
            
            $new_rows[] = array(
                    $row['SiteName'],
                    substr($row['SiteCode'],strlen(BaseProcess::$sitecode)),
                    $row['POSAccountNo'],
                    number_format($row['Balance'],2),
                    number_format($row['Deposit'],2),
                    number_format($row['Reload'],2),
                    number_format($row['Withdrawal'],2),
                    number_format($row['ActualAmount'],2),
                    number_format($gross_hold, 2),
                    $row['withconfirmation'],
                    $row['PickUpTag']
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'GrossHoldMonitoring';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
    }
    
    //Collection History Report (PDF)
    public function bankDepositPdf() 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();

        $startdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;

        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
//      $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;

        $rows = $topreport->bankDeposit($startdate, $venddate);
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Collection History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS'),
                array('value'=>'POS Account'),
                array('value'=>'Bank Name'),
                array('value'=>'Branch'),
                array('value'=>'Bank Transaction ID'),
                array('value'=>'Bank Transaction Date'),
                array('value'=>'Cheque Number'),
                array('value'=>'Amount'),
                array('value'=>'Particulars'),
                array('value'=>'Remittance Type'),
                array('value'=>'Date Created'),
                array('value'=>'Processed By')
                
             ));
        foreach($rows as $row) 
        {
            $particulars = $row['Particulars'];
//            $particulars = str_split((string)$particulars, 15);
//            $particulars = implode("<br/>",$particulars);
            $particulars = wordwrap($particulars, 15, "\n", true);
            
            $pdf->c_tableRow2(array(
                array('value'=>$row['siteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>$row['bankname']),
                array('value'=>$row['Branch']),
                array('value'=>$row['BankTransactionID']),
                array('value'=>$row['BankTransactionDate']),
                array('value'=>$row['ChequeNumber']),
                array('value'=>number_format($row['Amount'],2),'align'=>'right'),
                array('value'=>$particulars),
                array('value'=>$row['remittancename']),
                array('value'=>$row['DateCreated']),
                array('value'=>$row['name'])
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('collectionhistory.pdf');
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " DateRange:".$startdate." To ".$venddate;
        $date = $topreport->getDate();
        $auditfuncid = 100;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
    //Collection History Report (Excel)
    public function bankDepositExcel() 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);

        $startdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;

        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
//        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;

        $_SESSION['report_header'] = array('Site / PEGS','POS Account','Bank name','Branch','Bank Transaction ID','Bank Transaction Date','Cheque Number','Amount','Particulars','Remittance Type','Date Created', 'Processed By');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->bankDeposit($startdate, $venddate);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    $row['siteName'],
                    $row['POSAccountNo'],
                    $row['bankname'],
                    $row['Branch'],
                    $row['BankTransactionID'],
                    $row['BankTransactionDate'],
                    $row['ChequeNumber'],
                    number_format($row['Amount'],2),
                    $row['Particulars'],
                    $row['remittancename'],
                    $row['DateCreated'], // Date Created
                    $row['name'] // Verified By
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'collectionhistory';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " DateRange:".$startdate." To ".$venddate;
        $date = $topreport->getDate();
        $auditfuncid = 99;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
    //Betting Credit Fund Report (PDF)
    public function bettingCreditPdf() 
    {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vownerAID = $_POST['sel_operator'];
        $vsiteID = $_POST['sel_site_code'];
        $vreport = $_GET['report'];
        $rows = $topreport->bettingcredit($vownerAID, $vsiteID,$vreport);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Betting Credit');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Balance')
             ));
        foreach($rows as $row) 
        {
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['SiteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>number_format($row['Balance'],2),'align'=>'right'),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('bcf.pdf');
        $topreport->close();
    }
    
    //Betting Credit Fund Report (Excel)
    public function bettingCreditExcel() 
    {
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Balance');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vownerAID = $_POST['sel_operator'];
        $vsiteID = $_POST['sel_site_code'];
        $vreport = $_GET['report'];
        $rows = $topreport->bettingcredit($vownerAID, $vsiteID, $vreport);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    $row['POSAccountNo'],
                    number_format($row['Balance'],2)
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'bcf';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
    }
    
    //Top-up History (Manual, Auto) Report (PDF)
    public function topupHistoryPdf() 
    {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '180');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        
        $startdate = date("Y-m-d");
        
        if(isset($_POST['startdate']))
            $startdate = $_POST['startdate'];
        
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;
        
        if(isset($_POST['enddate']))
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $startdate .= " ".BaseProcess::$cutoff;
        
        $rows = $topreport->topUpHistory($startdate, $enddate, $_POST['seltopuptype'],$_POST['selSiteCode']);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Top-up History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'SiteName'),
                array('value'=>'SiteCode'),
                array('value'=>'POSAccountNo'),
                array('value'=>'StartBalance'),
                array('value'=>'EndBalance'),
                array('value'=>'MinBalance'),
                array('value'=>'MaxBalance'),
                array('value'=>'Top-up Count'),
                array('value'=>'Top-up Amount'),
                array('value'=>'Total Top-up Amount'),
                array('value'=>'Transaction Date'),
                array('value'=>'Top-upType')
            ));
        foreach($rows as $row) 
        {
            $pdf->c_tableRow2(array(
                array('value'=>$row['SiteName']),
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['POSAccountNo']),
                array('value'=>number_format($row['StartBalance'],2),'align'=>'right'),
                array('value'=>number_format($row['EndBalance'],2),'align'=>'right'),
                array('value'=>number_format($row['MinBalance'],2),'align'=>'right'),
                array('value'=>number_format($row['MaxBalance'],2),'align'=>'right'),
                array('value'=>$row['TopupCount']),
                array('value'=>number_format($row['TopupAmount'],2),'align'=>'right'),
                array('value'=>number_format($row['TotalTopupAmount'],2),'align'=>'right'),
                array('value'=>$row['DateCreated']),
                array('value'=>$this->_topupType($row['TopupTransactionType'])),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('tuhistory.pdf');
        $topreport->close();
    }
    
    //Top-up History (Manual, Auto) Report (Excel)
    public function topupHistoryExcel() 
    {
        $_SESSION['report_header'] = array('Site / PEGS Name', 'Site / PEGS Code','POS Account','Start Balance',
            'End Balance','Min Balance','Max Balance','Top-up Count','Top-up Amount','Total Top-up Amount',
            'Transaction Date','Top-up Type');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
          
        $startdate = date("Y-m-d");
        
        if(isset($_POST['startdate']))
            $startdate = $_POST['startdate'];
        
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;
        
        if(isset($_POST['enddate']))
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $startdate .= " ".BaseProcess::$cutoff;
        
        $rows = $topreport->topUpHistory($startdate, $enddate, $_POST['seltopuptype'],$_POST['selSiteCode']);
        
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    $row['SiteName'],
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['POSAccountNo'],
                    number_format($row['StartBalance'],2),
                    number_format($row['EndBalance'],2),
                    number_format($row['MinBalance'],2),
                    number_format($row['MaxBalance'],2),
                    $row['TopupCount'],
                    number_format($row['TopupAmount'],2),
                    number_format($row['TotalTopupAmount'],2),
                    $row['DateCreated'],
                    $this->_topupType($row['TopupTransactionType'])
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'tuhistory';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
    }
    
     //this method will be called when defining Top-up Type
    private function _topupType($type) 
    {
        if($type == 0) 
        {
            return 'Manual';
        } 
        else 
        {
            return 'Auto';
        }
    }
    
    // Manual Top-up Reversal History Report (PDF)
    public function reversalManualPdf() 
    {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        
        $vstartdate = date("Y-m-d");
        if(isset($_POST['startdate']))
            $vstartdate = $_POST['startdate'];
        
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($vstartdate)))." ".BaseProcess::$cutoff;
        if(isset($_POST['enddate']))
            $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $vstartdate .= " ".BaseProcess::$cutoff;

        $rows = $topreport->reversalManual($vstartdate, $venddate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Manual Top-up Reversal History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Start Balance'),
                array('value'=>'End Balance'),
                array('value'=>'Reversed Amount'),
                array('value'=>'Transaction Date'),
                array('value'=>'Reversed By')
             ));
        foreach($rows as $row) 
        {
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['SiteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>number_format($row['StartBalance'],2),'align'=>'right'),
                array('value'=>number_format($row['EndBalance'],2),'align'=>'right'),
                array('value'=>number_format($row['ReversedAmount'],2),'align'=>'right'),
                array('value'=>$row['TransDate']),
                array('value'=>$row['ReversedBy'])
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('topupreversal.pdf');
        $topreport->close();
    }
    
    // Manual Top-up Reversal History Report (Excel)
    public function reversalManualExcel() 
    {
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Start Balance','End Balance',
            'Reversed Amount','Transaction Date','Reversed By');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();

        $vstartdate = date("Y-m-d");
        if(isset($_POST['startdate']))
            $vstartdate = $_POST['startdate'];
        
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($vstartdate)))." ".BaseProcess::$cutoff;
        if(isset($_POST['enddate']))
            $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $vstartdate .= " ".BaseProcess::$cutoff;

        $rows = $topreport->reversalManual($vstartdate, $venddate);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    $row['POSAccountNo'],
                    number_format($row['StartBalance'],2),
                    number_format($row['EndBalance'],2),
                    number_format($row['ReversedAmount'],2),
                    $row['TransDate'],
                    $row['ReversedBy']
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'topupreversal';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
    }
    
    //this method will be called when defining Manual Redemption Status
    private function redemptionstatus($status)
    {
        switch($status)
        {
            case 0:
                return 'Pending';
                break;
            case 1:
                return 'Successful';
                break;
            case 2:
                return 'Failed';
                break;
        }
    }
    
    //Manual Redemption History Report (PDF)
    public function manualRedemptionPdf() 
    {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();

        $vstartdate = date("Y-m-d");
        if(isset($_POST['startdate']))
            $vstartdate = $_POST['startdate'];
        
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($vstartdate)))." ".BaseProcess::$cutoff;
        if(isset($_POST['enddate']))
            $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $vstartdate .= " ".BaseProcess::$cutoff;

        $rows = $topreport->manualRedemption($vstartdate, $venddate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Manual Redemption History');
        $pdf->html.='<di style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Terminal Code'),
                array('value'=>'Reported Amount'),
                array('value'=>'Requested By'),
                array('value'=>'Transaction Date'),
                array('value'=>'Ticket ID'),
                array('value'=>'Remarks'),
                array('value'=>'Status'),
                array('value'=>'Service Name'),
             ));
        foreach($rows as $row) 
        {
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['SiteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>substr($row['TerminalCode'], strlen($row['SiteCode']))),
                array('value'=>number_format($row['ReportedAmount'],2),'align'=>'right'),
                array('value'=>$row['Name']),
                array('value'=>$row['TransDate']),
                array('value'=>$row['TicketID']),
                array('value'=> strtolower($row['Remarks'])), //lowers string for proper rendering on PDF
                array('value'=>$this->redemptionstatus($row['Status'])),
                array('value'=> $row["ServiceName"]),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('manualredemption.pdf');
        $topreport->close();
    }
    
    //Manual Redemption History Report (Excel)
    public function manualRedemptionExcel() 
    {
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Terminal Code','Reported Amount',
            'Requested By','Transaction Date','Ticket ID','Remarks','Status', 'Service Name');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();

        $vstartdate = date("Y-m-d");
        if(isset($_POST['startdate']))
            $vstartdate = $_POST['startdate'];
        
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($vstartdate)))." ".BaseProcess::$cutoff;
        if(isset($_POST['enddate']))
            $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $vstartdate .= " ".BaseProcess::$cutoff;

        $rows = $topreport->manualRedemption($vstartdate, $venddate);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    $row['POSAccountNo'],
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                    number_format($row['ReportedAmount'],2),
                    $row['Name'],
                    $row['TransDate'],
                    $row['TicketID'],
                    $row['Remarks'],
                    $this->redemptionstatus($row['Status']),
                    $row["ServiceName"]
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'manualredemption';
        include 'ProcessTopUpExcel.php';        
        $topreport->close();
    }
    
    //Playing Balance History Report (PDF)
    public function playingBalancePdf() 
    {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        //$rows = $_SESSION['playing_balance'];
        $acctype = $_SESSION['acctype'];
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vsitecode = $_POST['selsite'];
        $vterminalid = $_POST['selterminal'];
        if ($vterminalid=='all')
        {
           $vipTerminal= 'all'; 
        }
        else
        {
            $terminalCode = $topreport->getTerminalCode($vsitecode, $vterminalid);
            $terminal=$terminalCode."VIP";
            $terminalVip = $topreport->getVipTerminal($vsitecode,$terminal);
            $vipTerminal = $terminalVip;
        }
        $rows = $topreport->getRptActiveTerminals($vsitecode,$vterminalid,$vipTerminal);

        foreach($rows as $key => $row) 
        {
            $balance = $this->getBalance($row);
            /********************* GET BALANCE API ****************************/
            if(is_string($balance['Balance'])) 
            {
                $rows[$key]['PlayingBalance'] = number_format((double)$balance['Balance'],2, '.', ',');
            } 
            else 
            {
                $rows[$key]['PlayingBalance'] = number_format($balance['Balance'],2, '.', ',');
            }
        }

        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Playing Balance');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        
        $header = array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'Terminal Code'),
                array('value'=>'Playing Balance'),
                array('value'=>'Service Name'),
                array('value'=>'User Mode'),
                array('value'=>'Terminal Type'),
                array('value'=>'e-SAFE?')
             );
        
        if($acctype == 6 || $acctype == 18)
        {
            array_pop($header);
        }
        
        $pdf->c_tableHeader2($header);

        foreach($rows as $row) 
        {
            $row['IsEwallet'] == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $row['UserMode'] == 1 ? $row['UserMode'] = "User Based" : $row['UserMode'] = "Terminal Based";
            
            if($row['PlayingBalance'] == 0)
            {
                $row['PlayingBalance'] = "0.00";
            }
            
            $data = array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))), //removes ICSA-
                array('value'=>$row['SiteName']),
                array('value'=>substr($row['TerminalCode'], strlen($row['SiteCode']))), //removes ICSA-($row['SiteCode'])
                array('value'=>$row['PlayingBalance'],'align'=>'right'),
                array('value'=>$row['ServiceName']),
                array('value'=>$row['UserMode']),
                array('value'=>$row['TerminalType']),
                array('value'=>$isEwallet)
            );
            
            if($acctype == 6 || $acctype == 18)
            {
                array_pop($data);
            }
                    
            $pdf->c_tableRow2($data);
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('PlayingBalance.pdf');
        $topreport->close();
    }
    
    //Playing Balance History Report (PDF)
    public function playingBalancePdfUB() 
    {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        //$rows = $_SESSION['playing_balance'];
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $cardnumber = $_POST['txtcardnumber'];
        $rows = $topreport->getRptActiveTerminalsUB($cardnumber);
        
        foreach($rows as $key => $row) 
        {
            // CCT EDITED 01/24/2018 BEGIN
            //if($row['UserMode'] == 0)
            if (($row['UserMode'] == 0) || ($row['UserMode'] == 2) || ($row['UserMode'] == 4))
            // CCT EDITED 01/24/2018 END
            {
                $balance = $this->getBalance($row);
            }
            else
            {
                $balance = $this->getBalanceUB($row);
            }
            /********************* GET BALANCE API ****************************/
            if(is_string($balance['Balance'])) 
            {
                $rows[$key]['PlayingBalance'] = number_format((double)$balance['Balance'],2, '.', ',');
            } 
            else 
            {
                $rows[$key]['PlayingBalance'] = number_format($balance['Balance'],2, '.', ',');
            }
        }
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Playing Balance');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'Terminal Code'),
                array('value'=>'Playing Balance'),
                array('value'=>'Service Name'),
                array('value'=>'User Mode'),
                array('value'=>'Terminal Type'),
                array('value'=>'e-SAFE?'),
             ));
        foreach($rows as $row) 
        {
            $row['IsEwallet'] == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $row['UserMode'] == 1 ? $row['UserMode'] = "User Based" : $row['UserMode'] = "Terminal Based";

            if($row['PlayingBalance'] == 0)
            {
                //$row['PlayingBalance'] = "N/A";
                $row['PlayingBalance'] = "0.00";
            }
                
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))), //removes ICSA-
                array('value'=>$row['SiteName']),
                array('value'=>substr($row['TerminalCode'], strlen($row['SiteCode']))), //removes ICSA-($row['SiteCode'])
                array('value'=>$row['PlayingBalance'],'align'=>'right'),
                array('value'=>$row['ServiceName']),
                array('value'=>$row['UserMode']),
                array('value'=>$row['TerminalType']),
                array('value'=>$isEwallet)
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('PlayingBalance.pdf');
        $topreport->close();
    }
    
    //Playing Balance History Report (Excel)
    public function playingBalanceExcel() 
    {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        
        $acctype = $_SESSION['acctype'];
        
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name','Terminal Code','Playing Balance','Service Name', 'User Mode', 'Terminal Type', 'e-SAFE?');
        
        if($acctype == 6 || $acctype == 18)
        {
            array_pop($_SESSION['report_header']);
        }
        
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vsitecode = $_POST['selsite'];
        $vterminalid = $_POST['selterminal'];
        if ($vterminalid=='all')
        {
            $vipTerminal= 'all'; 
        }
        else
        {
            $terminalCode = $topreport->getTerminalCode($vsitecode, $vterminalid);
            $terminal=$terminalCode."VIP";
            $terminalVip = $topreport->getVipTerminal($vsitecode,$terminal);
            $vipTerminal = $terminalVip;
        }
        $rows = $topreport->getRptActiveTerminals($vsitecode,$vterminalid,$vipTerminal);
        
        foreach($rows as $key => $row) 
        {
            $balance = $this->getBalance($row);
            /********************* GET BALANCE API ****************************/
            $rows[$key]['PlayingBalance'] = $balance['Balance'];
        }
        
        $actualBalance = 0;
        $new_rows = array();
        foreach($rows as $row) 
        {
            if(is_string($row['PlayingBalance'])) 
            {
                $actualBalance = (float)$row['PlayingBalance'];
            } 
            else 
            {
                $actualBalance = $row['PlayingBalance'];
            }
            
            $row['IsEwallet'] == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $row['UserMode'] == 1 ? $row['UserMode'] = "User Based" : $row['UserMode'] = "Terminal Based";
            
            if($actualBalance == 0)
            {
                $actualBalance = "0.00";
            }
            else
            {
                $actualBalance = number_format($actualBalance,2, '.', ',');
            }
            
            $new_rows1[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                    $actualBalance,
                    $row['ServiceName'],
                    $row['UserMode'],
                    $row['TerminalType']
                );
            
            $new_rows2[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                    $actualBalance,
                    $row['ServiceName'],
                    $row['UserMode'],
                    $row['TerminalType'],
                    $isEwallet
                );
            
            if($acctype == 6 || $acctype == 18)
            {
                $new_rows = $new_rows1;
            }
            else
            {
                $new_rows = $new_rows2;
            }
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'PlayingBalance';
        include 'ProcessTopUpExcel.php';  
        $topreport->close();
    }
    
    //Playing Balance History Report (Excel)
    public function playingBalanceExcelUB() 
    {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'Terminal Code','Playing Balance','Service Name', 'User Mode', 'Terminal Type', 'e-SAFE?');
        //$rows = $_SESSION['playing_balance'];
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $cardnumber = $_POST['txtcardnumber'];
        $rows = $topreport->getRptActiveTerminalsUB($cardnumber);
        
        foreach($rows as $key => $row) 
        {
            // CCT EDITED 01/24/2018 BEGIN
            //if($row['UserMode'] == 0)
            if (($row['UserMode'] == 0) || ($row['UserMode'] == 2) || ($row['UserMode'] == 4))
            // CCT EDITED 01/24/2018 END
            {
                $balance = $this->getBalance($row);
            }
            else
            {
                $balance = $this->getBalanceUB($row);
            }
            /********************* GET BALANCE API ****************************/
            $rows[$key]['PlayingBalance'] = $balance['Balance'];
        }
        
        $actualBalance = 0;
        $new_rows = array();
        foreach($rows as $row) 
        {
            if(is_string($row['PlayingBalance'])) 
            {
                $actualBalance = (float)$row['PlayingBalance'];
            } 
            else 
            {
                $actualBalance = $row['PlayingBalance'];
            }
            
            $row['IsEwallet'] == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $row['UserMode'] == 1 ? $row['UserMode'] = "User Based" : $row['UserMode'] = "Terminal Based";
            
            if($actualBalance == 0)
            {
                //$actualBalance = "N/A";
                $actualBalance = "0.00";
            }
            else
            {
                $actualBalance = number_format($actualBalance,2, '.', ',');
            }
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                    $actualBalance,
                    $row['ServiceName'],
                    $row['UserMode'],
                    $row['TerminalType'],
                    $isEwallet
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'PlayingBalance';
        include 'ProcessTopUpExcel.php';  
        $topreport->close();
    }
    
    //Replenishment History Report (PDF)
    //@date modified 03-03-2015
    public function replenishPdf() 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();

        $startdate = date("Y-m-d");
        if(isset($_POST['startdate']))
            $startdate = $_POST['startdate'];
        
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;
        if(isset($_POST['enddate']))
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $startdate .= " ".BaseProcess::$cutoff;

        $rows = $topreport->replenish($startdate, $enddate);
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Replenishment History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'POS Account'),
                array('value'=>'Amount'),
                array('value'=>'Date Created'),
                array('value'=>'Processed By'),
                array('value'=>'Reference Number'),
                array('value'=>'Type')
             ));
        foreach($rows as $row) 
        {
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['POSAccountNo']),
                array('value'=>number_format($row['Amount'],2),'align'=>'right'),
                //array('value'=>date('Y-m-d H:i:s',strtotime($row['DateCreated']))),
                array('value'=>$row['DateCreated']),
                array('value'=>$row['Name']),
                array('value'=>$row['ReferenceNumber']),
                array('value'=>$row['ReplenishmentName'])
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('replenishment.pdf');
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " DateRange:".$startdate." To ".$enddate;
        $date = $topreport->getDate();
        $auditfuncid = 103;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();      
    }
    
    //Replenishment History Report (Excel)
    //@date modified 03-03-2015
    public function replenishExcel() 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        $_SESSION['report_header'] = array('Site / PEGS Code','POS Account','Amount', 'Date Created','Processed By', 'Reference Number', 'Type');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        
        $startdate = date("Y-m-d");
        if(isset($_POST['startdate']))
            $startdate = $_POST['startdate'];
        
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;
        if(isset($_POST['enddate']))
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;
        
        $startdate .= " ".BaseProcess::$cutoff;

        $rows = $topreport->replenish($startdate, $enddate);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['POSAccountNo'],
                    number_format($row['Amount'],2),
                    $row['DateCreated'],
                    $row['Name'],
                    $row['ReferenceNumber'],
                    $row['ReplenishmentName']
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'replenishment';
        include 'ProcessTopUpExcel.php';  
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " DateRange:".$startdate." To ".$enddate;
        $date = $topreport->getDate();
        $auditfuncid = 102;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close(); 
    }
    
    //Confirmation History Report (PDF)
    public function confirmationPdf() 
    {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_POST['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $rows = $topreport->confirmation($startdate, $enddate);
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Confirmation History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Account Name'),
                array('value'=>'Site / PEGS Code'),
                array('value'=>'POS Account'),
                array('value'=>'Date Credited'),
                array('value'=>'Date Created'),
                array('value'=>'Who'),
                array('value'=>'Amount'),
                array('value'=>'Created By'),
             ));
        foreach($rows as $row) 
        {
            $pdf->c_tableRow2(array(
                array('value'=>$row['UserName']),
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['POSAccountNo']),
                array('value'=>date('Y-m-d H:i:s',strtotime($row['DateCredited']))),
                array('value'=>date('Y-m-d H:i:s',strtotime($row['DateCreated']))),
                array('value'=>$row['SiteRepresentative']),
                array('value'=>number_format($row['AmountConfirmed'],2),'align'=>'right'),
                array('value'=>$row['UserName']),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('confirmation.pdf');
        $topreport->close();
    }
    
    //Confirmation History Report (Excel)
    public function confirmationExcel() 
    {
        $_SESSION['report_header'] = array('Account Name','Site / PEGS Code','POS Account','Date Credited','Date Created','Who','Amount', 'Created By');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_POST['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $rows = $topreport->confirmation($startdate, $enddate);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    $row['UserName'],
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['POSAccountNo'],
                    date('Y-m-d H:i:s',strtotime($row['DateCredited'])),
                    date('Y-m-d H:i:s',strtotime($row['DateCreated'])),
                    $row['SiteRepresentative'],
                    number_format($row['AmountConfirmed'],2),
                    $row['UserName']
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'confirmation';
        include 'ProcessTopUpExcel.php';  
        $topreport->close();
    }
    
    //added on 11-18-2011, for gross hold monitoring per cut off (PDF)
    public function grossHoldCutoffPdf() 
    {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '180');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
//        $venddate = $_POST['enddate'];  
//        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;   
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;    
        $vsitecode = $_POST['selsitecode'];
        $datenow = date("Y-m-d")." ".BaseProcess::$cutoff;
        $rows = array();
        
        //check if queried date is date today
        if($datenow != $startdate)
        {
            $rows = $topreport->getoldGHCutoff($startdate, $enddate, $vsitecode);
        }
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold Monitoring Per Cut-off');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(6);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Cut Off Date', 'width' => '70px'),
                array('value'=>'Beginning Balance'),
                array('value'=>'Deposit'),
                array('value'=>'e-SAFE Loads'),
                array('value'=>'Reload'),
                array('value'=>'Redemption'),
                array('value'=>'e-SAFE Withdrawal'),
                array('value'=>'Manual Redemption'),
                array('value'=>'Printed Tickets'),
                array('value'=>'Active Tickets for the Day'),
                array('value'=>'Coupon'),
                array('value'=>'Cash on Hand'),
//                array('value'=>'Gross Hold'),
                array('value'=>'Replenishment'),
                array('value'=>'Collection'),
                array('value'=>'Ending Balance')
             ));
        
        if(count($rows) > 0)
        {
            foreach($rows as $row) 
            {
                $grosshold = (($row['InitialDeposit'] + $row['Reload']) - $row['Redemption']) - $row['ManualRedemption'];
                if ($startdate < BaseProcess::$deploymentdate) 
                {
                     $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon']) - ($row['RedemptionCashier'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                   //$cashonhand = ((((($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['EwalletWithdrawals']) - $row['ManualRedemption']) - $row['EncashedTickets'];
                }
                else 
                {
                    $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon'] + $row['LoadTickets']) - ($row['TotalRedemption'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                    //$cashonhand = ((((($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash'] + $row['DepositTicket'] + $row['ReloadTicket'] + $row['EwalletTicketDeposit'] + $row['Coupon']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['EwalletWithdrawals']) - $row['ManualRedemption']) - $row['EncashedTicketsV15'];
                }
                $endbal = $cashonhand + $row['Replenishment'] - $row['Collection'];
                $pdf->c_tableRow2(array(
                    array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                    array('value'=> $row['CutOff'], 'width' => '70px'),
                    array('value'=>number_format($row['BegBal'],2), 'align' => 'right'),
                    array('value'=>number_format($row['InitialDeposit'],2), 'align' => 'right'),
                    array('value'=>number_format($row['EwalletLoads'],2), 'align' => 'right'),
                    array('value'=>number_format($row['Reload'],2), 'align' => 'right'),
                    array('value'=>number_format($row['Redemption'],2), 'align' => 'right'),
                    array('value'=>number_format($row['EwalletWithdraw'],2), 'align' => 'right'),
                    array('value'=>number_format($row['ManualRedemption'], 2), 'align' => 'right'),
                    array('value'=>number_format($row['PrintedTickets'], 2), 'align' => 'right'),
                    array('value'=>number_format($row['UnusedTickets'], 2), 'align' => 'right'),
                    array('value'=>number_format($row['Coupon'] + $row['ewalletCoupon'], 2), 'align' => 'right'),
                    array('value'=>number_format($cashonhand, 2), 'align' => 'right'),
//                    array('value'=>number_format($grosshold,2), 'align' => 'right'),
                    array('value'=>number_format($row['Replenishment'],2), 'align' => 'right'),
                    array('value'=>number_format($row['Collection'],2), 'align' => 'right'),
                    array('value'=>number_format($endbal,2), 'align' => 'right'),
                 ));
            }
        }
        
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('grossholdpercutoff.pdf');
        unset($startdate, $venddate, $enddate, $vsitecode, $datenow, $rows);
        $topreport->close();
    }
    
    public function grossHoldCutoffViewDetailsPdf() 
    {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '180');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['hdnvStartDate'];
        $enddate = $_POST['hdnvEndDate'];     
        $vsiteid = $_POST['hdnvSiteID'];
        $datenow = date("Y-m-d")." ".BaseProcess::$cutoff;
        $rows = array();
        
        //check if queried date is date today
        if($datenow != $startdate)
        {
            $rows = $topreport->getoldGHCutoff($startdate, $enddate, $vsiteid);
        }
        
        $pdf = new CTCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold Monitoring Per Cut-off Details');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(10);
        $pdf->SetCellPadding(1);
        if(count($rows) > 0)
        {
            foreach($rows as $row) 
            {
                $grosshold = (($row['InitialDeposit'] + $row['Reload']) - $row['Redemption']) - $row['ManualRedemption'];
                $cashonhand = ((($row['DepositCash'] + $row['ReloadCash']) - $row['RedemptionCashier']) - $row['ManualRedemption']) - $row['EncashedTickets'];

                $pdf->html.='<table id="ghbalviewdetails" style="border: 1px solid black;">
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;"><b>Site/ PEGS Name</b></td><td id="pegsname" style="padding: 8px; border: 1px solid black;" >'.$row["SiteName"].'</td><td style="padding: 8px; border: 1px solid black;"></td><td style="padding: 8px; border: 1px solid black;"></td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Site/ PEGS Code</b></td><td id="pegscode" style="padding: 8px; border: 1px solid black;" >'.substr($row['SiteCode'], strlen(BaseProcess::$sitecode)).'</td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>POS Account</b></td><td id="posaccount" style="padding: 8px; border: 1px solid black;">'.$row["POSAccountNo"].'</td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Cut Off Date</b></td><td id="cutoff" style="padding: 8px; border: 1px solid black;">'.$row["CutOff"].'</td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Beginning Balance</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="begbal" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($row['BegBal'],2).'</td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Deposit</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="deposit" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($row['InitialDeposit'],2).'</td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Cash</b></td><td id="depositcash" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($row["DepositCash"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Ticket</b></td><td id="depositticket" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($row["DepositTicket"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Coupon</b></td><td id="depositcoupon" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($row["DepositCoupon"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Reloads</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="reload" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($row['Reload'],2).'</td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Cash</b></td><td id="reloadcash" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row["ReloadCash"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Ticket</b></td><td id="reloadticket" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row["ReloadTicket"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Coupon</b></td><td id="reloadcoupon" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row["ReloadCoupon"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Redemption</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="redemption" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row['Redemption'],2).'</td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Cashier</b></td><td id="redemptioncashier" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row["RedemptionCashier"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ><b>Genesis</b></td><td id="redemptiongenesis" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row["RedemptionGenesis"],2).'</td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Manual Redemption</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="manualredemption" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row['ManualRedemption'], 2).'</td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Encashed Tickets</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="encashedtickets" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row["EncashedTickets"],2).'</td>
                                </tr>
                                <tr style="background-color: white; height: 40px;" style="height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Cash on Hand</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="cashonhand" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($cashonhand, 2).'</td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Gross Hold</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="grosshold" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($grosshold,2).'</td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Replenishment</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="replenishment" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row['Replenishment'],2).'</td>
                                </tr>
                                <tr style="background-color: white; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Collection</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="collection" style="padding: 8px; border: 1px solid black; text-align: right;">'.number_format($row['Collection'],2).'</td>
                                </tr>
                                <tr style="background-color: #DCDCDC; height: 40px;">
                                    <td style="padding: 8px; border: 1px solid black;" ><b>Ending Balance</b></td><td style="padding: 8px; border: 1px solid black;" ></td><td style="padding: 8px; border: 1px solid black;" ></td><td id="endbal" style="padding: 8px; border: 1px solid black; text-align: right;" >'.number_format($row['EndBal'],2).'</td>
                                </tr>
                            </table>';
                break;
            }
        }
        
        $pdf->c_generatePDF('grossholdpercutoffdetails.pdf');
        unset($startdate, $enddate, $vsiteid, $datenow,$rows);
        $topreport->close();
    }
    
    //added on 11-18-2011, for gross hold monitoring per cut off (Excel)
    public function grossHoldCutoffExcel() 
    {
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
//        $venddate = $_POST['enddate'];  
//        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;           
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        $_SESSION['report_header'] = array('Site / PEGS Code','Cut Off Date','Beginning Balance','Deposit', 'e-SAFE Loads', 'Reload','Redemption', 'e-SAFE Withdrawal','Manual Redemption','Printed Tickets','Active Tickets for the Day','Coupon','Cash on Hand', 'Replenishment','Collection','Ending Balance');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vsitecode = $_POST['selsitecode'];
        $datenow = date("Y-m-d")." ".BaseProcess::$cutoff;
        $rows = array();
        $new_rows = array();
        
        //check if queried date is date today
        if($datenow != $startdate)
        {
            $rows = $topreport->getoldGHCutoff($startdate, $enddate, $vsitecode);
        }
        
        if(count($rows) > 0)
        {
            foreach($rows as $row) 
            {
                $grosshold = (($row['InitialDeposit'] + $row['Reload']) - $row['Redemption']) - $row['ManualRedemption'];
                if ($startdate < BaseProcess::$deploymentdate) 
                {
                    $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon']) - ($row['RedemptionCashier'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                   // $cashonhand = ((((($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['EwalletWithdrawals']) - $row['ManualRedemption']) - $row['EncashedTickets'];
                }
                else 
                {
                    $cashonhand = (($row['DepositCash'] + $row['Coupon'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['ewalletCoupon'] + $row['LoadTickets']) - ($row['TotalRedemption'] + $row['EwalletWithdraw']) - $row['ManualRedemption']) - $row["EncashedTicketsV15"];
                    //$cashonhand = ((((($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash'] + $row['DepositTicket'] + $row['ReloadTicket'] + $row['EwalletTicketDeposit'] + $row['Coupon']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['EwalletWithdrawals']) - $row['ManualRedemption']) - $row['EncashedTicketsV15'];
                }
                $endbal = $cashonhand + $row['Replenishment'] - $row['Collection'];
                $new_rows[] = array(
                                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                                $row['CutOff'],
                                number_format($row['BegBal'],2),
                                number_format($row['InitialDeposit'],2),
                                number_format($row['EwalletLoads'],2),
                                number_format($row['Reload'],2),
                                number_format($row['Redemption'],2),
                                number_format($row['EwalletWithdraw'],2),
                                number_format($row['ManualRedemption'],2),
                                number_format($row['PrintedTickets'],2),
                                number_format($row['UnusedTickets'],2),
                                number_format($row['Coupon']+$row['ewalletCoupon'],2),
                                number_format($cashonhand,2),
//                                number_format($grosshold,2),
                                number_format($row['Replenishment'],2),
                                number_format($row['Collection'],2),
                                number_format($endbal,2),
                );
            }
        }
        
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'grossholdpercutoff';
        include 'ProcessTopUpExcel.php';
        unset($new_rows, $rows, $startdate, $enddate, $datenow);
        $topreport->close();
    }
    
    public function cohAdjustmentExcel() 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);

        $startdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;

        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
//        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;

        $_SESSION['report_header'] = array('Site / PEGS Name','POS Account','Amount','Reason','Approved By','Processed By','Date Created');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
    
        $rows = $topreport->getCohAdjustment($startdate, $venddate);
   
        $new_rows = array();
        foreach($rows as $row) 
        {
            $new_rows[] = array(
                    $row['SiteName'],
                    $row['POSAccountNo'],
                    number_format($row['Amount'],2),
                    $row['Reason'],
                    $row['ApprovedBy'],
                    $row['CreatedBy'],
                    $row['DateCreated']
                    //date('Y-m-d  H:i:s ', strtotime($row['DateCreated']))
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'cohadjustmenthistory';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = "DateRange:".$startdate." To ".$venddate;
        $date = $topreport->getDate();
        $auditfuncid = 97;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
    public function cohAdjustmentPdf() 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();

        $startdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;

        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
//        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])))." ".BaseProcess::$cutoff;

        $rows = $topreport->getCohAdjustment($startdate, $venddate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Cash on Hand Adjustment History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Amount'),
                array('value'=>'Reason'),
                array('value'=>'Approved By'),
                array('value'=>'Processed By'),
                array('value'=>'Date Created')
                
             ));
        foreach($rows as $row) 
        {
            $pdf->c_tableRow2(array(
                array('value'=>$row['SiteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>number_format($row['Amount'],2),'align'=>'right'),
                //array('value'=>$row['Reason']),
                array('value'=>wordwrap($row['Reason'], 15, "\n", true)),
                array('value'=>$row['ApprovedBy']),
                array('value'=>$row['CreatedBy']),
                array('value'=>$row['DateCreated']),
                //array('value'=>date('Y-m-d H:i:s', strtotime($row['DateCreated'])))
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('cohadjustmenthistory.pdf');
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = "DateRange:".$startdate." To ".$venddate;
        $date = $topreport->getDate();
        $auditfuncid = 98;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
    //e-SAFE Transaction History per site Report (PDF)
    public function ewalletTransactionsitehistoryPDF($site, $transType, $transStatus, $startDate, $endDate) 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->geteWalletTransactionHistoryReport($site, $transType, $transStatus, $startDate, $endDate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('e-SAFE Transaction History Per Site');
        
        $pdf->html.='<div style="text-align:center;">Date Range: <b>  From </b> ' .$startDate. ' AM <b>     To </b>' .$endDate.' AM </div>';
        $pdf->SetFontSize(8);
        $pdf->c_tableHeader2(array(
                array('value'=>'Card Number'),
                array('value'=>'Terminal Code'),
                array('value'=>'Start Date'),
                array('value'=>'End Date'),
                array('value'=>'Amount'),
                array('value'=>'Transaction Type'),
                array('value'=>'Status'),
                array('value'=>'Created By'),
             ));
        foreach($rows as $row) 
        {
            $trans_type = 'Withdraw';
            if($row['TransType'] == 'D')
            {
                $trans_type = "Load";
            }
            $pdf->c_tableRow2(array(
                array('value'=>$row['LoyaltyCardNumber']),
                array('value'=>trim(str_replace('ICSA-', '',$row['TerminalCode']))),
                array('value'=>$row['StartDate']),
                array('value'=>$row['EndDate']),
                array('value'=>number_format($row['Amount'],2),'align'=>'right'),
                array('value'=>$trans_type),
                array('value'=>$row['Status']),
                array('value'=>$row['Name']), 
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('eSAFETransactionHistoryPerSite.pdf');
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " (SiteID:".$site." TransType:".$transType." TransStatus:".$transStatus." DateRange:".$startDate." To ".$endDate.")";
        $date = $topreport->getDate();
        $auditfuncid = 91;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
     //e-SAFE Transaction History Report per site (Excel)
    public function ewalletTransactionsitehistoryExcel($site, $transType, $transStatus, $startDate, $endDate) 
    {
        $_SESSION['report_header'] = array('Card Number','Terminal Code','Start Date', 'End Date','Amount','Transaction Type',
            'Status','Created By');
        
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->geteWalletTransactionHistoryReport($site, $transType, $transStatus, $startDate, $endDate);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $trans_type = 'Withdraw';
            if($row['TransType'] == 'D')
            {
                $trans_type = "Load";
            }
            $new_rows[] = array(
                    $row['LoyaltyCardNumber'],
                    str_replace('ICSA-', '', $row['TerminalCode']),
                    $row['StartDate'],
                    $row['EndDate'],
                    number_format($row['Amount'],2),
                    $trans_type,
                    $row['Status'],
                    $row['Name'],
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'eSAFETransactionHistoryPerSite';
        include 'ProcessTopUpExcel.php';        
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " (SiteID:".$site." TransType:".$transType." TransStatus:".$transStatus." DateRange:".$startDate." To ".$endDate.")";
        $date = $topreport->getDate();
        $auditfuncid = 90;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
     //e-SAFE Transaction History per card Report (PDF)
    public function ewalletTransactioncardhistoryPDF($cardNumber, $transType, $transStatus, $startDate, $endDate) 
    {
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->geteWalletTransactionCardHistoryReport($cardNumber, $transType, $transStatus, $startDate, $endDate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('e-SAFE Transaction History Per Membership Card'); 
        $pdf->html.='<div style="text-align:center;">Date Range: <b>  From </b> ' .$startDate. ' AM <b>     To </b>' .$endDate.' AM </div>';
        $pdf->SetFontSize(8);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code', 'width' => '80px'),
                array('value'=>'Card Number'),
                array('value'=>'Start Date', 'width' => '120px'),
                array('value'=>'End Date', 'width' => '120px'),
                array('value'=>'Amount'),
                array('value'=>'Transaction Type', 'width' => '80px'),
                array('value'=>'Status'),
                array('value'=>'Created By'),
             ));
        foreach($rows as $row) 
        {
            $trans_type = 'Withdraw';
            if($row['TransType'] == 'D')
            {
                $trans_type = "Load";
            }
            $sitecode = $topreport->getsitecode($row['SiteID']);
            $pdf->c_tableRow2(array(
                array('value'=>substr($sitecode['SiteCode'], strlen(BaseProcess::$sitecode)), 'width' => '80px'),
                array('value'=>$row['LoyaltyCardNumber']),
                array('value'=>$row['StartDate'], 'width' => '120px'),
                array('value'=>$row['EndDate'], 'width' => '120px'),
                array('value'=>number_format($row['Amount'],2),'align'=>'right'),
                array('value'=>$trans_type, 'width' => '80px'),
                array('value'=>$row['Status']),
                array('value'=>$row['Name']), 
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('eSAFETransactionHistoryPerMembershipCard.pdf');
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " (CardNumber:".$cardNumber." TransType:".$transType." TransStatus:".$transStatus." DateRange:".$startDate." To ".$endDate.")";
        $date = $topreport->getDate();
        $auditfuncid = 93;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
     //e-SAFE Transaction History Report per card (Excel)
    public function ewalletTransactioncardhistoryExcel($cardNumber, $transType, $transStatus, $startDate, $endDate) 
    {
        $_SESSION['report_header'] = array('Site / PEGS Code','Card Number','Start Date', 'End Date','Amount','Transaction Type',
            'Status','Created By');
        
        $aid = 0;
        if(isset($_SESSION['sessionID'])) 
        {
            $sessionid = $_SESSION['sessionID'];
        } 
        else  
        {
            $sessionid = '';
        }
        if(isset($_SESSION['accID'])) 
        {
            $aid = $_SESSION['accID'];
        }
        
        $ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->geteWalletTransactionCardHistoryReport($cardNumber, $transType, $transStatus, $startDate, $endDate);
        $new_rows = array();
        foreach($rows as $row) 
        {
            $trans_type = 'Withdraw';
            if($row['TransType'] == 'D')
            {
                $trans_type = "Load";
            }
            $sitecode = $topreport->getsitecode($row['SiteID']);
            $new_rows[] = array(
                    substr($sitecode['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['LoyaltyCardNumber'],
                    $row['StartDate'],
                    $row['EndDate'],
                    number_format($row['Amount'],2),
                    $trans_type,
                    $row['Status'],
                    $row['Name'],
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'eSAFETransactionHistoryPerMembershipCard';
        include 'ProcessTopUpExcel.php';        
        $topreport->close();
        
        //Point the connection to master DB to insert audit trail
        $topreport = new TopUpReportQuery($this->getMasterConnection());
        $topreport->open();
        $transdetails = " (CardNumber:".$cardNumber." TransType:".$transType." TransStatus:".$transStatus." DateRange:".$startDate." To ".$endDate.")";
        $date = $topreport->getDate();
        $auditfuncid = 92;
        $topreport->logtoaudit($sessionid, $aid, $transdetails, $date, $ipaddress, $auditfuncid);
        $topreport->close();
    }
    
    //method for get balance through API (Playing Balance)
    protected function getBalance($row) 
    {
        include_once __DIR__.'/../sys/class/helper/common.class.php';
        
        $providername = $this->CasinoRptType($row['ServiceID']);  
        
        switch (true)
        {
// CCT BEGIN
            // CCT ADDED BEGIN 11/29/2017 
            //case "HAB": 
            case (strstr($providername, "HAB")):
                $url = self::$service_api[$row['ServiceID'] - 1];
                $capiusername = self::$habbrandid ;
                $capipassword = self::$habapikey;
                $capiplayername = '';
                $capiserverID = '';
                break;           
            // CCT ADDED END 11/29/2017 
// CCT END               
            // CCT ADDED BEGIN 01/22/2018
            case (strstr($providername, "EB")):
                $url = '';
                $capiusername = '';
                $capipassword = '';
                $capiplayername = '';
                $capiserverID = '';
                break;           
            // CCT ADDED END 01/22/2018
            //case "RTG2":
            case (strstr($providername, "RTG2")):
                $url = self::$service_api[$row['ServiceID'] - 1];
                $capiusername = '';
                $capipassword = '';
                $capiplayername = '';
                $capiserverID = '';
                break;
            case (strstr($providername, "RTG")):
               $url = self::$service_api[$row['ServiceID'] - 1];
               $capiusername = '';
               $capipassword = '';
               $capiplayername = '';
               $capiserverID = '';
               break;
            // Comment Out CCT 02/06/2018 BEGIN
            //case (strstr($providername, "MG")):
            //    $_MGCredentials = self::$service_api[$row['ServiceID'] - 1];
            //   list($mgurl, $mgserverID) =  $_MGCredentials;
            //   $url = $mgurl;
            //   $capiusername = self::$capi_username;
            //   $capipassword = self::$capi_password;
            //   $capiplayername = self::$capi_player;
            //   $capiserverID = $mgserverID;
            //   break;
            //case (strstr($providername, "PT")):
            //   $url = self::$player_api[$row['ServiceID'] - 1];
            //   $capiusername =  self::$ptcasinoname;
            //   $capipassword = self::$ptSecretKey;
            //   $capiplayername = '';
            //   $capiserverID = '';
            //   break;
           // Comment Out CCT 02/06/2018 END
        }
        switch (true)
        {
            // CCT ADDED BEGIN 11/29/2017
            //case "HAB": 
            case (strstr($providername, "HAB")):
                $CasinoGamingCAPI = new CasinoGamingCAPI();
                $topreport = new TopUpReportQuery($this->getConnection());
                $topreport->open();
                //$MIDResult = $topreport->getMIDInfo($row['TerminalID'], $row['ServiceID']);            
                //$serviceUBResult = $topreport->getUBInfo($MIDResult['MID'], $row['ServiceID']);                   
                //$login = $serviceUBResult['ServiceUserName'];
                //$password = $serviceUBResult['ServicePassword'];
                $servicePwdResult = $topreport->getterminalcredentials($row['TerminalID'], $row['ServiceID']);
                $login = $row['TerminalCode'];
                $password = $servicePwdResult['ServicePassword'];
                $topreport->close();
                $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                    $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $row['UserMode'], $password);
                
                break;
            // CCT ADDED END 11/29/2017
            // CCT ADDED BEGIN 01/22/2018
            case (strstr($providername, "EB")):
                $CasinoGamingCAPI = new CasinoGamingCAPI();
                $login = '';
                $password = '';
                $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                    $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $row['UserMode'], $password);
                break;
            // CCT ADDED END 01/22/2018 
            // case "RTG2":
            case (strstr($providername, "RTG2")):
                $CasinoGamingCAPI = new CasinoGamingCAPI();
                $topreport = new TopUpReportQuery($this->getConnection());
                $topreport->open();
                $serviceusername = $topreport->getUBServiceLogin($row['TerminalID']);
                $topreport->close();
                $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                    $serviceusername, $capiusername, $capipassword, $capiplayername, $capiserverID); 
                break;
            case (strstr($providername, "RTG")):
                $CasinoGamingCAPI = new CasinoGamingCAPI();
                if($row['UserMode'] == 0)
                {
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                        $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, $capiserverID);
                }
                else
                {
                    $topreport = new TopUpReportQuery($this->getConnection());
                    $topreport->open();
                    $serviceusername = $topreport->getUBServiceLogin($row['TerminalID']);
                    $topreport->close();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                        $serviceusername, $capiusername, $capipassword, $capiplayername, $capiserverID); 
                }
                break;
            // Comment Out CCT 02/06/2018 BEGIN    
            //case (strstr($providername, "MG")):
            //    $CasinoGamingCAPI = new CasinoGamingCAPI();
            //    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
            //            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    break;
            //case (strstr($providername, "PT")):
            //    $CasinoGamingCAPI = new CasinoGamingCAPI();
            //    
            //    if($row['UserMode'] == 0)
            //    {
            //        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
            //            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    }
            //    else
            //    {
            //        $topreport = new TopUpReportQuery($this->getConnection());
            //        $topreport->open();
            //        $serviceusername = $topreport->getUBServiceLogin($row['TerminalID']);
            //        $topreport->close();
            //        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
            //            $serviceusername, $capiusername, $capipassword, $capiplayername, $capiserverID); 
            //    }    
            //    break;
            // Comment Out CCT 02/06/2018 END
        }
        
        return array("Balance"=>$balance, "Casino"=>$providername);    
    }
    
    //check loyalty card number
    public function getCardNumber() 
    {  
        include_once __DIR__.'/../sys/class/LoyaltyUBWrapper.class.php';       
        $loyalty = new LoyaltyUBWrapper();
        $cardnumber = $_POST['txtcardnumber'];
        $cardinfo = BaseProcess::$cardinfo;
        $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
        
        $obj_result = json_decode($loyaltyResult);

        $statuscode = $obj_result->CardInfo->StatusCode;
                    
        if(!is_null($statuscode) ||$statuscode == '')
        {
            if($statuscode == 1 || $statuscode == 5 || $statuscode == 9)
            {
               $casinoarray_count = count($obj_result->CardInfo->CasinoArray);

               if($casinoarray_count != 0)
               {
                   for($ctr = 0; $ctr < $casinoarray_count;$ctr++) 
                   {   
                       $_SESSION['ServiceUsername'] = $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername;
                       $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                       $_SESSION['UserMode'] = $obj_result->CardInfo->CasinoArray[$ctr]->UserMode;
                       return true;
                   }
              }
              else
              {
               return false;
              }
           }
           else
           {  
               return false;
           }
        }
        else
        {
            return false;
        }
    }
    
    protected function getBalanceUB($row) 
    {
       include_once __DIR__.'/../sys/class/helper/common.class.php';
        
       $providername = $this->CasinoRptType($row['ServiceID']);  
       $this->getCardNumber();
       switch (true)
       {
            case (strstr($providername, "RTG")):
               $url = self::$service_api[$row['ServiceID'] - 1];
               $capiusername = '';
               $capipassword = '';
               $capiplayername = '';
               $capiserverID = '';
               break;
            // Comment Out CCT 02/06/2018 BEGIN
            //case (strstr($providername, "MG")):
            //    $_MGCredentials = self::$service_api[$row['ServiceID'] - 1];
            //   list($mgurl, $mgserverID) =  $_MGCredentials;
            //   $url = $mgurl;
            //   $capiusername = self::$capi_username;
            //   $capipassword = self::$capi_password;
            //   $capiplayername = self::$capi_player;
            //   $capiserverID = $mgserverID;
            //   break;
            //case (strstr($providername, "PT")):
            //   $url = self::$player_api[$row['ServiceID'] - 1];
            //   $capiusername =  self::$ptcasinoname;
            //   $capipassword = self::$ptSecretKey;
            //   $capiplayername = '';
            //   $capiserverID = '';
            //   break;
           // Comment Out CCT 02/06/2018 END
        }
        
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $CasinoGamingCAPI = new CasinoGamingCAPI();
                $usermode = $_SESSION['UserMode'];
                if($usermode == 0)
                {
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                        $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, $capiserverID);
                }
                else
                {
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                        $row['UBServiceLogin'], $capiusername, $capipassword, $capiplayername, $capiserverID);   
                }
                break;
            // Comment Out CCT 02/06/2018 BEGIN                
            //case (strstr($providername, "MG")):
            //    $CasinoGamingCAPI = new CasinoGamingCAPI();
            //    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
            //            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    break;
            //case (strstr($providername, "PT")):
            //    $CasinoGamingCAPI = new CasinoGamingCAPI();
            //    $usermode = $_SESSION['UserMode'];
            //    if($usermode == 0)
            //    {
            //        $CasinoGamingCAPI = new CasinoGamingCAPI();
            //        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
            //            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    }
            //    else
            //    {
            //         $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
            //            $_SESSION['ServiceUsername'], $capiusername, $capipassword, $capiplayername, $capiserverID);    
            //    }    
            //    break;
            // Comment Out CCT 02/06/2018 END
        }
        return array("Balance"=>$balance, "Casino"=>$providername);    
    }
    
    function CasinoRptType($serviceId) 
    {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open(); 
        $rows = $topreport->getRefServices(); 
        $casino = array();
        foreach($rows as $row) 
        {
            $casino[$row['ServiceID']] = $row['ServiceGroupName'];
        }
        return $casino[$serviceId];
        $topreport->close();
    }
}

$reports = new ProcessTopUpGenerateReports();

if(!isset($_GET['action']))
    die('Page not found');

switch($_GET['action']) 
{
    case 'confirmationpdf':
        $reports->confirmationPdf();
        break;
    case 'confirmationexcel':
        $reports->confirmationExcel();
        break;
    case 'replenishpdf':
        $reports->replenishPdf();
        break;
    case 'replenishexcel':
        $reports->replenishExcel();
        break;
    case 'bettingcreditpdf':
        $reports->bettingCreditPdf();
        break;
    case 'bettingcreditexcel':
        $reports->bettingCreditExcel();
        break;
    case 'topuphistorypdf':
        $reports->topupHistoryPdf();
        break;
    case 'topuphistoryexcel':
        $reports->topupHistoryExcel();
        break;
    case 'grossholdmonpdf':
        $reports->grossHoldMonPdf();
        break;
    case 'grossholdmonexcel':
        $reports->grossHoldMonExcel();
        break;
    case 'getdataposteddepositpdf':
        $reports->bankDepositPdf();
        break;
    case 'getdataposteddepositexcel':
        $reports->bankDepositExcel();
        break;
    case 'reversalmanualpdf':
        $reports->reversalManualPdf();
        break;
    case 'reversalmanualexcel':
        $reports->reversalManualExcel();
        break;
    case 'manualredemppdf':
        $reports->manualRedemptionPdf();
        break;
    case 'manualredempexcel':
        $reports->manualRedemptionExcel();
        break;
    case 'playingbalpdf':
        $reports->playingBalancePdf();
        break;
    case 'playingbalpdfub':
        $reports->playingBalancePdfUB();
        break;
    case 'playingbalexcel':
        $reports->playingBalanceExcel();
        break;
    case 'playingbalexcelub':
        $reports->playingBalanceExcelUB();
        break;
    // CCT ADDED 02/19/2018 BEGIN
        case 'grossholdbalancepdfpagcor':
        $reports->grossHoldCutoffPdfPAGCOR();
        break;
    case 'grossholdbalanceexcelpagcor':
        $reports->grossHoldCutoffExcelPAGCOR();
        break;    
    // CCT ADDED 02/19/2018 END
    case 'grossholdbalancepdf':
        $reports->grossHoldCutoffPdf();
        break;
    case 'grossholdbalanceviewdetailspdf':
        $reports->grossHoldCutoffViewDetailsPdf();
        break;
    case 'grossholdbalanceexcel':
        $reports->grossHoldCutoffExcel();
        break;
    case 'getcohadjustmentexcel':
        $reports->cohAdjustmentExcel();
        break;
    case 'getcohadjustmentpdf':
        $reports->cohAdjustmentPdf();
        break;
    case 'ewalletTransactionsitehistoryPDF':
        $startDate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $transStatus="";
        $transType="";
        $site="";
        
        if(isset($_GET['dateFrom']))
            $startDate = $_GET['dateFrom']." ".BaseProcess::$cutoff;
//        if(isset($_GET['dateTo']))
            $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['cmbtransStatus']))
            $transStatus = $_GET['cmbtransStatus'];
        if(isset($_GET['cmbtransType']))
            $transType = $_GET['cmbtransType'];
        if(isset($_GET['cmbsite']))
            $site = $_GET['cmbsite'];
        
        $reports->ewalletTransactionsitehistoryPDF($site, $transType, $transStatus, $startDate, $endDate);
        break;
    case 'ewalletTransactionsitehistoryExcel':
        $startDate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $transStatus="";
        $transType="";
        $site="";

        if(isset($_GET['dateFrom']))
            $startDate = $_GET['dateFrom']." ".BaseProcess::$cutoff;
//            if(isset($_GET['dateTo']))
           $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['cmbtransStatus']))
            $transStatus = $_GET['cmbtransStatus'];
        if(isset($_GET['cmbtransType']))
            $transType = $_GET['cmbtransType'];
        if(isset($_GET['cmbsite']))
            $site = $_GET['cmbsite'];

        $reports->ewalletTransactionsitehistoryExcel($site, $transType, $transStatus, $startDate, $endDate);
        break;
    case 'ewalletTransactioncardhistoryPDF':
        $startDate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $transStatus="";
        $transType="";
        $cardNumber="";
        
        if(isset($_GET['dateFrom']))
            $startDate = $_GET['dateFrom']." ".BaseProcess::$cutoff;
//        if(isset($_GET['dateTo']))
            $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['cmbtransStatus']))
            $transStatus = $_GET['cmbtransStatus'];
        if(isset($_GET['cmbtransType']))
            $transType = $_GET['cmbtransType'];
        if(isset($_GET['cardNum']))
            $cardNumber = $_GET['cardNum'];
        
        $reports->ewalletTransactioncardhistoryPDF($cardNumber, $transType, $transStatus, $startDate, $endDate);
        break;
    case 'ewalletTransactioncardhistoryExcel':
        $startDate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $transStatus="";
        $transType="";
        $cardNumber="";

        if(isset($_GET['dateFrom']))
            $startDate = $_GET['dateFrom']." ".BaseProcess::$cutoff;
//            if(isset($_GET['dateTo']))
            $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['cmbtransStatus']))
            $transStatus = $_GET['cmbtransStatus'];
        if(isset($_GET['cmbtransType']))
            $transType = $_GET['cmbtransType'];
        if(isset($_GET['cardNum']))
            $cardNumber = $_GET['cardNum'];

        $reports->ewalletTransactioncardhistoryExcel($cardNumber, $transType, $transStatus, $startDate, $endDate);
        break;
    default :
        die('Page not found');
}