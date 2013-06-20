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
class ProcessTopUpGenerateReports extends BaseProcess{
    //Gross Hold Monitoring Report (PDF)
    public function grossHoldMonPdf() {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $siteID = $_POST['selSiteCode'];
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $venddate = $_POST['enddate'];  
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;
        $rows = $topreport->grossHolMonitoring($startdate, $enddate, $siteID);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Gross Hold Monitoring');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
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
        foreach($rows as $row) {
            $gross_hold = (($row['Deposit'] + $row['Reload'] - $row['Withdrawal']) - $row['ActualAmount']);
            if($_POST['selwithconfirmation'] != '') {
                if($row['withconfirmation'] != $_POST['selwithconfirmation']) {
                    continue;
                }
            }
            
            if($_POST['sellocation'] != '') {
                if($row['PickUpTag'] != $_POST['sellocation']) {
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
    public function grossHoldMonExcel() {
        $siteID = $_POST['selSiteCode'];
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $venddate = $_POST['enddate'];  
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;   
        $_SESSION['report_header'] = array('Site / PEGS Name','Site / PEGS Code', 'POS Account','BCF','Deposit','Reload','Withdrawal','Manual Redemption','Gross Hold', 'With Confirmation','Location');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->grossHolMonitoring($startdate, $enddate, $siteID);
        
        $new_rows = array();
        foreach($rows as $row) {
            $gross_hold = (($row['Deposit'] + $row['Reload'] - $row['Withdrawal']) - $row['ActualAmount']);
            if($_POST['selwithconfirmation'] != '') {
                if($row['withconfirmation'] != $_POST['selwithconfirmation']) {
                    continue;
                }
            }
            if($_POST['sellocation'] != '') {
                if($row['PickUpTag'] != $_POST['sellocation']) {
                    continue;
                }
            }
            
            $new_rows[] = array(
                    $row['SiteName'],
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
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
    public function bankDepositPdf() {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])));
        $rows = $topreport->bankDeposit($startdate, $venddate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Collection History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Bank'),
                array('value'=>'Branch'),
                array('value'=>'Bank Transaction ID'),
                array('value'=>'Deposit Date'),
                array('value'=>'Cheque Number'),
                array('value'=>'Amount'),
                array('value'=>'Remittance Type'),
                array('value'=>'Site Transaction Date'),
                array('value'=>'Date Created'),
                array('value'=>'Verified By')
                
             ));
        foreach($rows as $row) {
            $pdf->c_tableRow2(array(
                array('value'=>$row['siteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>$row['bankname']),
                array('value'=>$row['Branch']),
                array('value'=>$row['BankTransactionID']),
                array('value'=>$row['DateCreated']),
                array('value'=>$row['ChequeNumber']),
                array('value'=>number_format($row['Amount'],2),'align'=>'right'),
                array('value'=>$row['remittancename']),
                array('value'=>$row['DateUpdated']),
                array('value'=>$row['DateCreated']),
                array('value'=>$row['username'])
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('collectionhistory.pdf');
        $topreport->close();
    }
    
    //Collection History Report (Excel)
    public function bankDepositExcel() {
        $startdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])));
        $_SESSION['report_header'] = array('Site / PEGS Name','POS Account','Bank','Branch','Bank Transaction ID','Deposit Date','Cheque Number','Amount','Remittance Type','Site Transaction Date','Date Created', 'Verified By');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $rows = $topreport->bankDeposit($startdate, $venddate);
        $new_rows = array();
        foreach($rows as $row) {
            $new_rows[] = array(
                    $row['siteName'],
                    $row['POSAccountNo'],
                    $row['bankname'],
                    $row['Branch'],
                    $row['BankTransactionID'],
                    $row['DateCreated'],
                    $row['ChequeNumber'],
                    number_format($row['Amount'],2),
                    $row['remittancename'],
                    $row['DateUpdated'], //site transaction date
                    $row['DateCreated'], // Date Created
                    $row['username'] // Verified By
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'collectionhistory';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
    }
    
    //Betting Credit Fund Report (PDF)
    public function bettingCreditPdf() {
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
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Balance')
             ));
        foreach($rows as $row) {
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
    public function bettingCreditExcel() {
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Balance');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vownerAID = $_POST['sel_operator'];
        $vsiteID = $_POST['sel_site_code'];
        $vreport = $_GET['report'];
        $rows = $topreport->bettingcredit($vownerAID, $vsiteID, $vreport);
        $new_rows = array();
        foreach($rows as $row) {
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
    public function topupHistoryPdf() {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '180');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        if(isset($_POST['startdate']) && isset($_POST['enddate']))
        {
            $startdate = $_POST['startdate'];
            $venddate = $_POST['enddate'];  
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)));
        }
        else
        {
            $startdate = date("Y-m-d");
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)));
        }
        
        $rows = $topreport->topUpHistory($startdate, $enddate, $_POST['seltopuptype'],$_POST['selSiteCode']);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Top-up History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
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
        foreach($rows as $row) {
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
                array('value'=>$this->_topupType($row['TopupType'])),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('tuhistory.pdf');
        $topreport->close();
    }
    
    //Top-up History (Manual, Auto) Report (Excel)
    public function topupHistoryExcel() {
        $_SESSION['report_header'] = array('Site / PEGS Name', 'Site / PEGS Code','POS Account','Start Balance',
            'End Balance','Min Balance','Max Balance','Top-up Count','Top-up Amount','Total Top-up Amount',
            'Transaction Date','Top-up Type');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        
        if(isset($_POST['startdate']) && isset($_POST['enddate']))
        {
            $startdate = $_POST['startdate'];
            $venddate = $_POST['enddate'];  
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)));
        }
        else
        {
            $startdate = date("Y-m-d");
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)));
        }
        
        $rows = $topreport->topUpHistory($startdate, $enddate, $_POST['seltopuptype'],$_POST['selSiteCode']);
        
        $new_rows = array();
        foreach($rows as $row) {
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
                    $this->_topupType($row['TopupType'])
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'tuhistory';
        include 'ProcessTopUpExcel.php';
        $topreport->close();
    }
    
     //this method will be called when defining Top-up Type
    private function _topupType($type) {
        if($type == 0) {
            return 'Manual';
        } else {
            return 'Auto';
        }
    }
    
    // Manual Top-up Reversal History Report (PDF)
    public function reversalManualPdf() {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vstartdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])));
        $rows = $topreport->reversalManual($vstartdate, $venddate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Manual Top-up Reversal History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
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
        foreach($rows as $row) {
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
    public function reversalManualExcel() {
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Start Balance','End Balance',
            'Reversed Amount','Transaction Date','Reversed By');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vstartdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])));
        $rows = $topreport->reversalManual($vstartdate, $venddate);
        $new_rows = array();
        foreach($rows as $row) {
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
    public function manualRedemptionPdf() {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vstartdate = $_POST['startdate'];
        $venddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])));
        $rows = $topreport->manualRedemption($vstartdate, $venddate);
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Manual Redemption History');
        $pdf->html.='<di style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
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
                array('value'=>'Transaction ID'),
                array('value'=>'Remarks'),
                array('value'=>'Status'),
                array('value'=>'Service Name'),
             ));
        foreach($rows as $row) {
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['SiteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>substr($row['TerminalCode'], strlen($row['SiteCode']))),
                array('value'=>number_format($row['ReportedAmount'],2),'align'=>'right'),
                array('value'=>$row['UserName']),
                array('value'=>$row['TransDate']),
                array('value'=>$row['TicketID']),
                array('value'=>$row['TransactionID']),
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
    public function manualRedemptionExcel() {
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Terminal Code','Reported Amount',
            'Requested By','Transaction Date','Ticket ID','Transaction ID','Remarks','Status', 'Service Name');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vstartdate = $_POST['startdate'];
        $venddate = $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_POST['enddate'])));
        $rows = $topreport->manualRedemption($vstartdate, $venddate);
        $new_rows = array();
        foreach($rows as $row) {
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    $row['POSAccountNo'],
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                    number_format($row['ReportedAmount'],2),
                    $row['UserName'],
                    $row['TransDate'],
                    $row['TicketID'],
                    $row['TransactionID'],
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
    public function playingBalancePdf() {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        //$rows = $_SESSION['playing_balance'];
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vsitecode = $_POST['selsite'];
        $rows = $topreport->getRptActiveTerminals($vsitecode);
        
        foreach($rows as $key => $row) {
            $balance = $this->getBalance($row);
            
            /********************* GET BALANCE API ****************************/
            if(is_string($balance['Balance'])) {
                $rows[$key]['PlayingBalance'] = number_format((double)$balance['Balance'],2, '.', ',');
            } else {
                $rows[$key]['PlayingBalance'] = number_format($balance['Balance'],2, '.', ',');
            }
        }
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Playing Balance');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Terminal Code'),
                array('value'=>'Playing Balance'),
                array('value'=>'Service Name'),
                array('value'=>'User Mode')
             ));
        foreach($rows as $row) {
            if($row['UserMode'] == 0){
                $row['UserMode'] = "Terminal Based";
            }
            else{
                $row['UserMode'] = "User Based";
            }
            
            if($row['PlayingBalance'] == 0){
                    $row['PlayingBalance'] = "N/A";
            }
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))), //removes ICSA-
                array('value'=>$row['SiteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>substr($row['TerminalCode'], strlen($row['SiteCode']))), //removes ICSA-($row['SiteCode'])
                array('value'=>$row['PlayingBalance'],'align'=>'right'),
                array('value'=>$row['ServiceName']),
                array('value'=>$row['UserMode']),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('PlayingBalance.pdf');
        $topreport->close();
    }
    
    //Playing Balance History Report (PDF)
    public function playingBalancePdfUB() {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        //$rows = $_SESSION['playing_balance'];
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $cardnumber = $_POST['txtcardnumber'];
        $rows = $topreport->getRptActiveTerminalsUB($cardnumber);
        
        foreach($rows as $key => $row) {
            $balance = $this->getBalanceUB($row);
            
            /********************* GET BALANCE API ****************************/
            if(is_string($balance['Balance'])) {
                $rows[$key]['PlayingBalance'] = number_format((double)$balance['Balance'],2, '.', ',');
            } else {
                $rows[$key]['PlayingBalance'] = number_format($balance['Balance'],2, '.', ',');
            }
        }
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Playing Balance');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Terminal Code'),
                array('value'=>'Playing Balance'),
                array('value'=>'Service Name'),
                array('value'=>'User Mode')
             ));
        foreach($rows as $row) {
            if($row['UserMode'] == 0){
                $row['UserMode'] = "Terminal Based";
            }
            else{
                $row['UserMode'] = "User Based";
            }
            
            if($row['PlayingBalance'] == 0){
                    $row['PlayingBalance'] = "N/A";
            }
                
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))), //removes ICSA-
                array('value'=>$row['SiteName']),
                array('value'=>$row['POSAccountNo']),
                array('value'=>substr($row['TerminalCode'], strlen($row['SiteCode']))), //removes ICSA-($row['SiteCode'])
                array('value'=>$row['PlayingBalance'],'align'=>'right'),
                array('value'=>$row['ServiceName']),
                array('value'=>$row['UserMode']),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('PlayingBalance.pdf');
        $topreport->close();
    }
    
    //Playing Balance History Report (Excel)
    public function playingBalanceExcel() {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Terminal Code','Playing Balance','Service Name', 'User Mode');
        //$rows = $_SESSION['playing_balance'];
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $vsitecode = $_POST['selsite'];
        $rows = $topreport->getRptActiveTerminals($vsitecode);
        
        foreach($rows as $key => $row) {
            $balance = $this->getBalance($row);
            
            /********************* GET BALANCE API ****************************/
            
            $rows[$key]['PlayingBalance'] = $balance['Balance'];
        }
        
        $actualBalance = 0;
        $new_rows = array();
        foreach($rows as $row) {
            
            if(is_string($row['PlayingBalance'])) {
                $actualBalance = (float)$row['PlayingBalance'];
            } else {
                $actualBalance = $row['PlayingBalance'];
            }
            if($row['UserMode'] == 0){
                $row['UserMode'] = "Terminal Based";
            }
            else{
                $row['UserMode'] = "User Based";
            }
            
            if($actualBalance == 0){
                    $actualBalance = "N/A";
            }
            else{
                $actualBalance = number_format($actualBalance,2, '.', ',');
            }
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    $row['POSAccountNo'],
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                   $actualBalance,
                    $row['ServiceName'],
                    $row['UserMode']
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'PlayingBalance';
        include 'ProcessTopUpExcel.php';  
        $topreport->close();
    }
    
    
    //Playing Balance History Report (Excel)
    public function playingBalanceExcelUB() {
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Terminal Code','Playing Balance','Service Name', 'User Mode');
        //$rows = $_SESSION['playing_balance'];
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $cardnumber = $_POST['txtcardnumber'];
        $rows = $topreport->getRptActiveTerminalsUB($cardnumber);
        
        foreach($rows as $key => $row) {
            $balance = $this->getBalanceUB($row);
            
            /********************* GET BALANCE API ****************************/
            
            $rows[$key]['PlayingBalance'] = $balance['Balance'];
        }
        
        $actualBalance = 0;
        $new_rows = array();
        foreach($rows as $row) {
            
            if(is_string($row['PlayingBalance'])) {
                $actualBalance = (float)$row['PlayingBalance'];
            } else {
                $actualBalance = $row['PlayingBalance'];
            }
            if($row['UserMode'] == 0){
                $row['UserMode'] = "Terminal Based";
            }
            else{
                $row['UserMode'] = "User Based";
            }
            
            if($actualBalance == 0){
                    $actualBalance = "N/A";
            }
            else{
                $actualBalance = number_format($actualBalance,2, '.', ',');
            }
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'],
                    $row['POSAccountNo'],
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                    $actualBalance,
                    $row['ServiceName'],
                    $row['UserMode']
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'PlayingBalance';
        include 'ProcessTopUpExcel.php';  
        $topreport->close();
    }
    
    
    //Replenishment History Report (PDF)
    public function replenishPdf() {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate'];
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_POST['enddate'])) .BaseProcess::$gaddeddate));
        $rows = $topreport->replenish($startdate, $enddate);
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Replenishment History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'POS Account'),
                array('value'=>'Amount'),
                array('value'=>'Date Credited'),
                array('value'=>'Date Created'),
                array('value'=>'Created By')
             ));
        foreach($rows as $row) {
            $pdf->c_tableRow2(array(
                array('value'=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode))),
                array('value'=>$row['POSAccountNo']),
                array('value'=>number_format($row['Amount'],2),'align'=>'right'),
                array('value'=>date('Y-m-d H:i:s',strtotime($row['DateCredited']))),
                array('value'=>date('Y-m-d H:i:s',strtotime($row['DateCreated']))),
                array('value'=>$row['UserName']),
             ));
        }
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('replenishment.pdf');
        $topreport->close();      
    }
    
    //Replenishment History Report (Excel)
    public function replenishExcel() {
        $_SESSION['report_header'] = array('Site / PEGS Code','POS Account','Amount', 'Date Credited','Date Created','Created By');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate'];
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_POST['enddate'])) .BaseProcess::$gaddeddate));
        $rows = $topreport->replenish($startdate, $enddate);
        $new_rows = array();
        foreach($rows as $row) {
            $new_rows[] = array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['POSAccountNo'],
                    number_format($row['Amount'],2),
                    date('Y-m-d H:i:s',strtotime($row['DateCredited'])),
                    date('Y-m-d H:i:s',strtotime($row['DateCreated'])),
                    $row['UserName']
                );
        }
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'replenishment';
        include 'ProcessTopUpExcel.php';  
        $topreport->close();
    }
    
    //Confirmation History Report (PDF)
    public function confirmationPdf() {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate'];
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_POST['enddate'])) .BaseProcess::$gaddeddate));
        $rows = $topreport->confirmation($startdate, $enddate);
        
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Confirmation History');
        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
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
        foreach($rows as $row) {
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
    public function confirmationExcel() {
        $_SESSION['report_header'] = array('Account Name','Site / PEGS Code','POS Account','Date Credited','Date Created','Who','Amount', 'Created By');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate'];
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_POST['enddate'])) .BaseProcess::$gaddeddate));
        $rows = $topreport->confirmation($startdate, $enddate);
        $new_rows = array();
        foreach($rows as $row) {
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
    public function grossHoldCutoffPdf() {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '180');
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open();
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $venddate = $_POST['enddate'];  
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;   
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
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Site / PEGS Code'),
                array('value'=>'Site / PEGS Name'),
                array('value'=>'POS Account'),
                array('value'=>'Cut Off Date'),
                array('value'=>'Beginning Balance'),
                array('value'=>'Initial Deposit'),
                array('value'=>'Reload'),
                array('value'=>'Redemption'),
                array('value'=>'Manual Redemption'),
                array('value'=>'Gross Hold'),
                array('value'=>'Replenishment'),
                array('value'=>'Collection'),
                array('value'=>'Ending Balance')
             ));
        
        if(count($rows) > 0){
            foreach($rows as $row) {
                $grosshold = ($row['initialdep'] + $row['reload'] - $row['redemption']) - $row['manualredemption']; // (D+R-W) - manual redemption
                $endbal = $grosshold + $row['replenishment'] - $row['collection'];
                $pdf->c_tableRow2(array(
                    array('value'=>substr($row['sitecode'], strlen(BaseProcess::$sitecode))),
                    array('value'=>$row['sitename']),
                    array('value'=>$row['POSAccountNo']),
                    array('value'=> $row['cutoff']),
                    array('value'=>number_format($row['begbal'],2)),
                    array('value'=>number_format($row['initialdep'],2)),
                    array('value'=>number_format($row['reload'],2)),
                    array('value'=>number_format($row['redemption'],2)),
                    array('value'=>number_format($row['manualredemption'], 2)),
                    array('value'=>number_format($grosshold,2)),
                    array('value'=>number_format($row['replenishment'],2)),
                    array('value'=>number_format($row['collection'],2)),
                    array('value'=>number_format($endbal,2))
                 ));
            }
        }
        
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('grossholdpercutoff.pdf');
        unset($startdate, $venddate, $enddate, $vsitecode, $datenow,
              $rows);
        $topreport->close();
    }
    
    //added on 11-18-2011, for gross hold monitoring per cut off (Excel)
    public function grossHoldCutoffExcel() {
        $startdate = $_POST['startdate']." ".BaseProcess::$cutoff;
        $venddate = $_POST['enddate'];  
        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($venddate)))." ".BaseProcess::$cutoff;           
        $_SESSION['report_header'] = array('Site / PEGS Code','Site / PEGS Name', 'POS Account','Cut Off Date','Beginning Balance','Initial Deposit','Reload','Redemption','Manual Redemption','Gross Hold', 'Replenishment','Collection','Ending Balance');
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
        
        if(count($rows) > 0){
            foreach($rows as $row) {
                $grosshold = ($row['initialdep'] + $row['reload'] - $row['redemption']) - $row['manualredemption']; // (D+R-W) - manual redemption
                $endbal = $grosshold + $row['replenishment'] - $row['collection'];
                $new_rows[] = array(
                                substr($row['sitecode'], strlen(BaseProcess::$sitecode)),
                                $row['sitename'], 
                                $row['POSAccountNo'],
                                $row['cutoff'],
                                number_format($row['begbal'],2),
                                number_format($row['initialdep'],2),
                                number_format($row['reload'],2),
                                number_format($row['redemption'],2),
                                number_format($row['manualredemption'], 2),
                                number_format($grosshold,2),
                                number_format($row['replenishment'],2),
                                number_format($row['collection'],2),
                                number_format($endbal,2)
                );
            }
        }
        
        $_SESSION['report_values'] = $new_rows;
        $_GET['fn'] = 'grossholdpercutoff';
        include 'ProcessTopUpExcel.php';
        unset($new_rows, $rows, $startdate, $venddate, $enddate, $datenow);
        $topreport->close();
    }
    
    //method for get balance through API (Playing Balance)
    protected function getBalance($row) {
        include_once __DIR__.'/../sys/class/helper/common.class.php';
        
        $providername = $this->CasinoRptType($row['ServiceID']);  
        
        switch (true)
        {
            case (strstr($providername, "RTG")):
               $url = self::$service_api[$row['ServiceID'] - 1];
               $capiusername = '';
               $capipassword = '';
               $capiplayername = '';
               $capiserverID = '';
                break;
            case (strstr($providername, "MG")):
                $_MGCredentials = self::$service_api[$row['ServiceID'] - 1];
               list($mgurl, $mgserverID) =  $_MGCredentials;
               $url = $mgurl;
               $capiusername = self::$capi_username;
               $capipassword = self::$capi_password;
               $capiplayername = self::$capi_player;
               $capiserverID = $mgserverID;
                break;
            case (strstr($providername, "PT")):
               $url = self::$player_api[$row['ServiceID'] - 1];
               $capiusername =  self::$ptcasinoname;
               $capipassword = self::$ptSecretKey;
               $capiplayername = '';
               $capiserverID = '';
                break;
        }
        switch (true)
        {
                case (strstr($providername, "RTG")):
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case (strstr($providername, "MG")):
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case (strstr($providername, "PT")):
                    $CasinoGamingCAPI = new CasinoGamingCAPI();

                    if($row['UserMode'] == 0){
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    }
                    else
                    {
                        $topreport = new TopUpReportQuery($this->getConnection());
                        $topreport->open();
                        $serviceusername = $topreport->getUBServiceLogin($row['TerminalID']);
                        $topreport->close();
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $serviceusername, $capiusername, $capipassword, $capiplayername, 
                            $capiserverID); 
                    }    
                    
                    break;
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
                       for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {   
                          
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
    
    
    protected function getBalanceUB($row) {
      
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
            case (strstr($providername, "MG")):
                $_MGCredentials = self::$service_api[$row['ServiceID'] - 1];
               list($mgurl, $mgserverID) =  $_MGCredentials;
               $url = $mgurl;
               $capiusername = self::$capi_username;
               $capipassword = self::$capi_password;
               $capiplayername = self::$capi_player;
               $capiserverID = $mgserverID;
                break;
            case (strstr($providername, "PT")):
               $url = self::$player_api[$row['ServiceID'] - 1];
               $capiusername =  self::$ptcasinoname;
               $capipassword = self::$ptSecretKey;
               $capiplayername = '';
               $capiserverID = '';
                break;
        }
        
        
        switch (true)
        {
                case (strstr($providername, "RTG")):
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case (strstr($providername, "MG")):
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case (strstr($providername, "PT")):
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $usermode = $_SESSION['UserMode'];
                    if($usermode == 0){
                        $CasinoGamingCAPI = new CasinoGamingCAPI();
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    }
                    else
                    {
                         $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $_SESSION['ServiceUsername'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);    
                    }    
                    
                    
                    break;
        }
        
        return array("Balance"=>$balance, "Casino"=>$providername);    
  
    }
    
    function CasinoRptType($serviceId) {
        $topreport = new TopUpReportQuery($this->getConnection());
        $topreport->open(); 
        $rows = $topreport->getRefServices(); 
        $casino = array();
        foreach($rows as $row) {
            $casino[$row['ServiceID']] = $row['ServiceName'];
        }
        return $casino[$serviceId];
        $topreport->close();
    }
}

$reports = new ProcessTopUpGenerateReports();

if(!isset($_GET['action']))
    die('Page not found');

switch($_GET['action']) {
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
    case 'grossholdbalancepdf':
        $reports->grossHoldCutoffPdf();
        break;
    case 'grossholdbalanceexcel':
        $reports->grossHoldCutoffExcel();
        break;
    default :
        die('Page not found');
}
