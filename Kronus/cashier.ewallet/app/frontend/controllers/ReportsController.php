<?php
Mirage::loadComponents('FrontendController');
Mirage::loadModels('ReportsFormModel');
/**
 * Date Created 11 17, 11 8:47:16 AM <pre />
 * Description of ReportsController
 * @author Bryan Salazar
 */
class ReportsController extends FrontendController{
    
    public $title = 'Reports';
    
    public function overviewAction() {
        $reportsFormModel = new ReportsFormModel();
        $reports_type = array(
            ''=>'Please select report type',
            $this->createUrl('reports/transactionhistory')=>'Transaction History Per Site',
            $this->createUrl('reports/transactionhistorypercashier')=>'Transaction History Per Cashier',
            $this->createUrl('reports/transactionhistorypervirtualcashier')=>'Transaction History Per Virtual Cashier',
            $this->createUrl('reports/eWalletPerSite')=>'e-wallet Transaction History Per Site',
            $this->createUrl('reports/eWalletPerCashier')=>'e-wallet Transaction History Per Cashier'
        );
        $this->render('reports_overview',array('reportsFormModel'=>$reportsFormModel,'reports_type'=>$reports_type));
    }
    
    public function transactionHistoryAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        
        $reportsFormModel = new ReportsFormModel();
        
        $start = 1;
        $limit = 10;
        $datenow = date('Y-m-d');
        $date = $datenow;
        $time = date('H:i:s'); //current time
        $cutoff = Mirage::app()->param['cut_off'];
        //if date is today, check the cutoff time;
        if($time < $cutoff)
        {
            //get the -1 day
            $date = minusOneDay($date); 
        }
        $enddate = addOneDay($date);
        if(isset($_POST['ReportsFormModel'])) {
            $reportsFormModel->setAttributes($_POST['ReportsFormModel']);
            $date = $reportsFormModel->date;
            //check if date is today
            if($date == $datenow)
            {
                //if date is today, check the cutoff time;
                if($time < $cutoff)
                {
                    //get the -1 day
                    $date = minusOneDay($date); 
                }
            }
            $start = $_POST['startlimit'];
            if($start < 1) {
                $start = 1;
            }
            $enddate = addOneDay($date);
            list($rows,$total_rows,$page_count,$displayingpageof,$eWalletDeposits, $eWalletWithdrawals)=  $this->_getTransHistory($date, $enddate, $start, $limit);    
            
            $ticketlist = $this->getTicketList($date, $enddate);
            
            $manualredemptions = $this->getmanualRedemptions($date, $enddate);
            
            //$forcetredemptions = $this->getforcetRedemptions($date, $enddate);
            
            $runningactivetickets = $this->getrunningactivetickets($date, $enddate);
            
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($enddate)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            echo json_encode(array('rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof,'coverage'=>$coverage, 'ticketlist'=>$ticketlist, 'manualredemptions'=>$manualredemptions, 'runningactivetickets'=>$runningactivetickets,'eWalletDeposits'=>$eWalletDeposits, 'eWalletWithdrawals'=>$eWalletWithdrawals));
            Mirage::app()->end();
        } else {
            list($rows,$total_rows,$page_count,$displayingpageof, $eWalletDeposits, $eWalletWithdrawals)=  $this->_getTransHistory($date, $enddate, $start, $limit);
            
            $ticketlist = $this->getTicketList($date, $enddate);
            
            $manualredemptions = $this->getmanualRedemptions($date, $enddate);
            
            //$forcetredemptions = $this->getforcetRedemptions($date, $enddate);
            
            $runningactivetickets = $this->getrunningactivetickets($date, $enddate);
         
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($enddate)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            $this->renderPartial('reports_transaction_history',array('reportsFormModel'=>$reportsFormModel,
                'rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage, 'ticketlist'=>$ticketlist, 'manualredemptions'=>$manualredemptions, 'runningactivetickets'=>$runningactivetickets,'eWalletDeposits'=>(int)$eWalletDeposits, 'eWalletWithdrawals'=>$eWalletWithdrawals));
        }
    }
    
    public function getmanualRedemptions($date, $enddate){
        Mirage::loadModels('ManualRedemptionsModel');
        $manualredemptionsModel = new ManualRedemptionsModel();
        
        $manualredemptions = $manualredemptionsModel->getManualRedemptions($date, $enddate, $this->site_id);
        $manualredemptions = $manualredemptions['Amount'];
        if(is_null($manualredemptions)){
            $manualredemptions = 0;
        }
        return $manualredemptions;
    }
    
    
    public function getforcetRedemptions($date, $enddate, $aid = ''){
        Mirage::loadModels('ForceTRedemptions');
        $forcetredemptions = new ForceTRedemptions();
        if($aid == ''){
            $totalforcetredemptions = $forcetredemptions->getforcetredemptions($date, $enddate, $this->site_id);
        }
        else{
            $totalforcetredemptions = $forcetredemptions->getforcetredemptions($date, $enddate, $this->site_id, $this->acc_id);
        }
        $totalforcetredemptions = $totalforcetredemptions['Amount'];
        if(is_null($totalforcetredemptions)){
            $totalforcetredemptions = 0;
        }
        return $totalforcetredemptions;
    }
    
    public function getrunningactivetickets($date, $enddate){
        Mirage::loadModels('SiteGrossHoldCuttOffModel');
        Mirage::loadModels('TicketsModel');
        
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $date = $date .' '.$cutoff_time;
        $enddate = $enddate .' '.$cutoff_time;
        
        $sitegrossholdcuttoff = new SiteGrossHoldCuttOffModel();
        $tickets = new TicketsModel();
        
        $datetoday = date('Y-m-d').' 06:00:00';
        
        $selecteddate = strtotime($date);
        
        $newdate = strtotime($datetoday) - $selecteddate;

        $newdate = $newdate / 86400;
        
        if($newdate == 0){
            $runningactivetickets1 =  $tickets->getrunningactivetickets($date, $enddate, $this->site_id);
            
            $date2 = date('Y-m-d H:i:s', strtotime($date. ' - 1 days'));
            
            $enddate2 = date('Y-m-d H:i:s', strtotime($enddate. ' - 1 days'));
            
            $enddate2s = date('Y-m-d', strtotime($enddate. ' - 1 days'));
            
            $runningactivetickets2 =  $tickets->getrunningactivetickets($date2, $enddate2, $this->site_id);
            
            $expiredtickets = $tickets->getExpiredTickets($enddate2s, $this->site_id);
            
            $runningactivetickets2 = $runningactivetickets2 - $expiredtickets;
            
            $date3 = date('Y-m-d H:i:s', strtotime($date. ' - 2 days'));
            
            $enddate3 = date('Y-m-d H:i:s', strtotime($enddate. ' - 2 days'));
            
            $enddate3s = date('Y-m-d', strtotime($enddate. ' - 2 days'));
            
            $runningactivetickets3 = $sitegrossholdcuttoff->getrunningActiveTickets($enddate3, $this->site_id);
            
            $expiredtickets2 = $tickets->getExpiredTickets($enddate3s, $this->site_id);
            
            $runningactivetickets3 = $runningactivetickets3 - $expiredtickets2;
            
            $runningactivetickets = $runningactivetickets1 + $runningactivetickets2 + $runningactivetickets3;
            
        }
        else{
            if($newdate >= 2){
                
                $runningactivetickets = $sitegrossholdcuttoff->getrunningActiveTickets($enddate, $this->site_id);
            }
            else{
                                
                $twoday = date('Y-m-d H:i:s', strtotime($datetoday. ' - 2 days'));
                
                $oneday = date('Y-m-d H:i:s', strtotime($datetoday. ' - 1 days'));
                
                $onedays = date('Y-m-d', strtotime($datetoday. ' - 1 days'));
                
                $runningactivetickets = $sitegrossholdcuttoff->getrunningActiveTickets($oneday, $this->site_id);
                
                $runningactivetickets2 =  $tickets->getrunningactivetickets($oneday, $datetoday, $this->site_id);
                
                $expiredtickets = $tickets->getExpiredTickets($onedays, $this->site_id);
                
                $runningactivetickets2 = $runningactivetickets2 - $expiredtickets;
                
                $runningactivetickets = $runningactivetickets + $runningactivetickets2;
            }
        }
        
        return $runningactivetickets;
        
        
    }
    
    public function getTicketList($date, $enddate){
        $transactionSummaryModel = new TransactionSummaryModel();
        
        $ticketlist = $transactionSummaryModel->getTicketList($this->site_id, $date, $enddate);
        
        return $ticketlist;
    }
    
    public function getTicketListperCashier($date, $enddate, $aid){
        $transactionSummaryModel = new TransactionSummaryModel();
        
        $ticketlist = $transactionSummaryModel->getTicketListperCashier($this->site_id, $date, $enddate, $aid);
        
        return $ticketlist;
    }


    protected function _getTransHistory($date,$enddate,$start,$limit) {
        Mirage::loadModels(array('TransactionSummaryModel', 'EWalletTransModel'));
        $transactionSummaryModel = new TransactionSummaryModel();
        $eWalletTransModel = new EWalletTransModel();
        
        // get total row count
        $row_count = $transactionSummaryModel->getCountTransSummary($date, $enddate, $this->site_id);
        
        // get page count
        $page_count = ceil($row_count / $limit); 

        $startlimit = ($start * $limit) - $limit;
        
        if($start > $page_count) {
            $startlimit = 0;
            $start = 1;
        }    
        // get rows
        $rows = $transactionSummaryModel->getTransSummaryPaging($this->site_id,  $this->site_code, $date, $enddate, $startlimit, $limit);
        $total_rows = $transactionSummaryModel->getTransSummaryTotalsPerCG($this->site_id,  $this->site_code, $date, $enddate, $startlimit, $limit);
//        $total_rows = $transactionSummaryModel->getTransSummaryTotals($this->site_id,$this->site_code, $date, $enddate);
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;
        $eWalletDeposits = $eWalletTransModel->getDepositSumPerSite($date, $enddate, $this->site_id);
        $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerSite($date,$enddate, $this->site_id);
        
        if(isset($_POST['startlimit'])) 
            $displayingpageof = 'Displaying page ' . (($start)? $start : '0') . ' of ' . $page_count;
        
        return array($rows,$total_rows,$page_count,$displayingpageof, $eWalletDeposits, $eWalletWithdrawals);
    }
    
    public function transactionHistoryPerCashierAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        
        
        Mirage::loadModels('ManualRedemptionsModel');
        $start = 1;
        $limit = 10;
        
        $datenow = date('Y-m-d');
        $start_date = $datenow;
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
        //if date is today, check the cutoff time;
        if($time < $cutoff)
        {
            //get the -1 day
            $start_date = minusOneDay($start_date); 
        }
        $end_date = addOneDay($start_date);
        $reportsFormModel = new ReportsFormModel();
        $manualRedemptionsModel = new ManualRedemptionsModel();
        
        if(isset($_POST['ReportsFormModel'])) {
            $reportsFormModel->setAttributes($_POST['ReportsFormModel']);
            $start_date = $reportsFormModel->date;
            //if time was less than the cutoff
            if($start_date == $datenow)
            {
                //if date is today, check the cutoff time;
                if($time < $cutoff)
                {
                    //get the -1 day
                    $start_date = minusOneDay($start_date); 
                }
            }
            $start = $_POST['startlimit'];
            if($start < 1) {
                $start = 1;
            }
            $end_date = addOneDay($start_date);   
            
            //$forcetredemptions = $this->getforcetRedemptions($start_date, $end_date, $this->acc_id);
            
            list($rows,$total_rows,$page_count,$displayingpageof, $eWalletDeposits, $eWalletWithdrawals) = $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $ticketlist = $this->getTicketListperCashier($start_date, $end_date, $this->acc_id);
            
           
            echo json_encode(array('rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof, 'coverage'=>$coverage,'ticketlist'=>$ticketlist, 'eWalletDeposits'=>$eWalletDeposits, 'eWalletWithdrawals'=>$eWalletWithdrawals));
            Mirage::app()->end();            
        } else {
            list($rows,$total_rows,$page_count,$displayingpageof, $eWalletDeposits, $eWalletWithdrawals) = $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit);
            $ticketlist = $this->getTicketListperCashier($start_date, $end_date, $this->acc_id);
            //$forcetredemptions = $this->getforcetRedemptions($start_date, $end_date, $this->acc_id);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $manualRedemption = $manualRedemptionsModel->getManualRedemptionsPerCashier($start_date, $end_date, $this->site_id, $this->acc_id);
            
            $this->renderPartial('reports_transaction_history_cashier',array('reportsFormModel'=>$reportsFormModel,
                'rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof, 'coverage'=>$coverage,'ticketlist'=>$ticketlist, 'eWalletDeposits'=>$eWalletDeposits, 'eWalletWithdrawals'=>$eWalletWithdrawals));
        }
    }
    
    protected function _getTransHistoryPerCashier($start_date, $end_date, $start, $limit) {
        Mirage::loadModels(array('TransactionSummaryModel','EWalletTransModel'));
        $transactionSummaryModel = new TransactionSummaryModel();
        $eWalletTransModel = new EWalletTransModel();
        
        // get total row count
        $row_count = $transactionSummaryModel->getTransactionSummaryperCashierCount($this->acc_id, $start_date, $end_date);
        
        // get page count
        $page_count = ceil($row_count / $limit); 

        $startlimit = ($start * $limit) - $limit;
        
        if($start > $page_count) {
            $startlimit = 0;
            $start = 1;
        }
        $rows = $transactionSummaryModel->getTransactionSummaryPerCashier($this->site_id,$this->acc_id,$this->site_code, $start_date, $end_date, $startlimit, $limit);
        $eWalletDeposits = $eWalletTransModel->getDepositSumPerCashier($start_date, $end_date, $this->site_id, $this->acc_id);
        $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerCashier($start_date,$end_date, $this->site_id, $this->acc_id);
        
        $total_rows = $transactionSummaryModel->getTransactionSummaryPerCashierTotals($this->site_id,$this->site_code,$this->acc_id,$start_date,$end_date);
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;
        if(isset($_POST['startlimit'])) {
            if($page_count == 0) {
                $start = 0;
            }
            $displayingpageof = 'Displaying page ' . (($start)? $start : '0') . ' of ' . $page_count;
        }
            
        return array($rows,$total_rows,$page_count,$displayingpageof, $eWalletDeposits, $eWalletWithdrawals);
    }
    
    
    public function transactionHistoryPerVirtualCashierAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        
            Mirage::loadModels('AccountsModel');
        
        $start = 1;
        $limit = 10;
        
        $datenow = date('Y-m-d');
        $start_date = $datenow;
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
        //if date is today, check the cutoff time;
        if($time < $cutoff)
        {
            //get the -1 day
            $start_date = minusOneDay($start_date); 
        }
        $end_date = addOneDay($start_date);
        $reportsFormModel = new ReportsFormModel();
        $accounts = new AccountsModel();
        
        if(isset($_POST['ReportsFormModel'])) {
            $reportsFormModel->setAttributes($_POST['ReportsFormModel']);
            $start_date = $reportsFormModel->date;
            //if time was less than the cutoff
            if($start_date == $datenow)
            {
                //if date is today, check the cutoff time;
                if($time < $cutoff)
                {
                    //get the -1 day
                    $start_date = minusOneDay($start_date); 
                }
            }
            $start = $_POST['startlimit'];
            if($start < 1) {
                $start = 1;
            }
            $end_date = addOneDay($start_date);   
            list($rows,$total_rows,$page_count,$displayingpageof) = $this->_getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            list($rows2,$total_rows2,$page_count2,$displayingpageof2)=  $this->_getTransHistory($start_date, $end_date, $start, $limit);    
            
            $vcaid = $accounts->getVirtualCashier($this->site_id);
       
            $vcaid = $vcaid['AID'];
            
            $ticketlist = $this->getTicketListperCashier($start_date, $end_date, $vcaid);
            $manualredemptions = $this->getmanualRedemptions($start_date, $end_date);
            
            $runningactivetickets = $this->getrunningactivetickets($start_date, $end_date);
            echo json_encode(array('rows'=>$rows,'rows2'=>$rows2,'total_rows'=>$total_rows,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof,'coverage'=>$coverage,'ticketlist'=>$ticketlist, 'manualredemptions'=>$manualredemptions, 'runningactivetickets'=>$runningactivetickets));
            Mirage::app()->end();            
        } else {
            list($rows,$total_rows,$page_count,$displayingpageof) = $this->_getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            list($rows2,$total_rows2,$page_count2,$displayingpageof2)=  $this->_getTransHistory($start_date, $end_date, $start, $limit);   
            
            $vcaid = $accounts->getVirtualCashier($this->site_id);
       
            $vcaid = $vcaid['AID'];
            
            $ticketlist = $this->getTicketListperCashier($start_date, $end_date, $vcaid);
            $manualredemptions = $this->getmanualRedemptions($start_date, $end_date);
            
            $runningactivetickets = $this->getrunningactivetickets($start_date, $end_date);
            $this->renderPartial('reports_transaction_history_virtual_cashier',array('reportsFormModel'=>$reportsFormModel,
                'rows'=>$rows,'rows2'=>$rows2,'total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage,'ticketlist'=>$ticketlist, 'manualredemptions'=>$manualredemptions, 'runningactivetickets'=>$runningactivetickets));
        }
    }
    
    
    protected function _getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit) {
        Mirage::loadModels(array('TransactionSummaryModel', 'AccountsModel'));
        $transactionSummaryModel = new TransactionSummaryModel();
        $accounts = new AccountsModel();
        // get total row count
        $row_count = $transactionSummaryModel->getTransactionSummaryperCashierCount($this->acc_id, $start_date, $end_date);
        
        // get page count
        $page_count = ceil($row_count / $limit); 

        $startlimit = ($start * $limit) - $limit;
        
        if($start > $page_count) {
            $startlimit = 0;
            $start = 1;
        }
        
        $vcaid = $accounts->getVirtualCashier($this->site_id);
       
        $vcaid = $vcaid['AID'];
        
        $rows = $transactionSummaryModel->getTransactionSummaryPerCashier($this->site_id,$vcaid,$this->site_code, $start_date, $end_date, $startlimit, $limit);
        
        $total_rows = $transactionSummaryModel->getTransactionSummaryPerCashierTotals($this->site_id,$vcaid,$start_date,$end_date);
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;
        if(isset($_POST['startlimit'])) {
            if($page_count == 0) {
                $start = 0;
            }
            $displayingpageof = 'Displaying page ' . (($start)? $start : '0') . ' of ' . $page_count;
        }
        
        return array($rows,$total_rows,$page_count,$displayingpageof);
    }
    
    public function eWalletTransactionHistoryPerSiteAction(){
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        
            
        Mirage::loadModels(array('ReportsFormModel', 'EWalletTransModel'));
        $reportsFormModel = new ReportsFormModel();
        $ewalletTransModel = new EWalletTransModel();
        
        $datenow = date('Y-m-d');
        $start_date = $datenow;
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
        //if date is today, check the cutoff time;
        if($time < $cutoff)
        {
            //get the -1 day
            $start_date = minusOneDay($start_date); 
        }
        $end_date = addOneDay($start_date);
        
        $cutoff_time = Mirage::app()->param['cut_off'];
        if(isset($_POST['ReportsFormModel'])) {
            $reportsFormModel->setAttributes($_POST['ReportsFormModel']);
            $start_date = $reportsFormModel->date;
            
            //if time was less than the cutoff
            if($start_date == $datenow)
            {
                //if date is today, check the cutoff time;
                if($time < $cutoff)
                {
                    //get the -1 day
                    $start_date = minusOneDay($start_date); 
                }
            }
            $start = $_POST['startlimit'];
            if($start < 1) {
                $start = 1;
            }
            $end_date = addOneDay($start_date);   
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $result = $ewalletTransModel->getEWalletTransactionPerSite($start_date, $end_date, $this->site_id);
            $cashOnHand = $this->_computeCashOnHandPerSite($start_date, $end_date);
            
            echo json_encode(array('data'=>$result, 'coverage'=>$coverage, 'cashOnHand'=>$cashOnHand));          
        } else {
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $result = $ewalletTransModel->getEWalletTransactionPerSite($start_date, $end_date, $this->site_id);
            $cashOnHand = $this->_computeCashOnHandPerSite($start_date, $end_date);
            $this->renderPartial('reports_ewallet_transaction_history_site', array('reportsFormModel'=>$reportsFormModel, 'data'=>$result, 'coverage'=>$coverage, 'cashOnHand'=>$cashOnHand));
        }
        
    }
    
    public function eWalletTransactionHistoryPerCashierAction(){
        Mirage::loadModels(array('ReportsFormModel', 'EWalletTransModel'));
        $reportsFormModel = new ReportsFormModel();
        $ewalletTransModel = new EWalletTransModel();
        
        $datenow = date('Y-m-d');
        $start_date = $datenow;
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
        //if date is today, check the cutoff time;
        if($time < $cutoff)
        {
            //get the -1 day
            $start_date = minusOneDay($start_date); 
        }
        $end_date = addOneDay($start_date);
        
        $cutoff_time = Mirage::app()->param['cut_off'];
        if(isset($_POST['ReportsFormModel'])) {
            $reportsFormModel->setAttributes($_POST['ReportsFormModel']);
            $start_date = $reportsFormModel->date;
            
            //if time was less than the cutoff
            if($start_date == $datenow)
            {
                //if date is today, check the cutoff time;
                if($time < $cutoff)
                {
                    //get the -1 day
                    $start_date = minusOneDay($start_date); 
                }
            }
            $start = $_POST['startlimit'];
            if($start < 1) {
                $start = 1;
            }
            $end_date = addOneDay($start_date);   
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $result = $ewalletTransModel->getEWalletTransactionPerCashier($start_date, $end_date, $this->site_id,$this->acc_id);
            $cashOnHand = $this->_computeCashOnHandPerCashier($start_date, $end_date);
            echo json_encode(array('data'=>$result, 'coverage'=>$coverage, 'cashOnHand'=>$cashOnHand));          
        } else {
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $result = $ewalletTransModel->getEWalletTransactionPerCashier($start_date, $end_date, $this->site_id,$this->acc_id);
            $cashOnHand = $this->_computeCashOnHandPerCashier($start_date, $end_date);
            $this->renderPartial('reports_ewallet_transaction_history_cashier', array('reportsFormModel'=>$reportsFormModel, 'data'=>$result, 'coverage'=>$coverage,'cashOnHand'=>$cashOnHand));
        }
        
    }
    
    private function _computeCashOnHandPerSite($date, $enddate){
        Mirage::loadModels(array('TransactionSummaryModel', 'EWalletTransModel'));
        $transactionSummaryModel = new TransactionSummaryModel();
        $eWalletTransModel = new EWalletTransModel();
        
        $total_rows = $transactionSummaryModel->getTransSummaryTotalsPerCG($this->site_id,  $this->site_code, $date, $enddate, 0, 0);
        
        $ticketlist = $this->getTicketList($date, $enddate);
        $manualredemptions = $this->getmanualRedemptions($date, $enddate);
        $eWalletDeposits = $eWalletTransModel->getDepositSumPerSite($date, $enddate, $this->site_id);
        $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerSite($date,$enddate, $this->site_id);
      
        $regdepositcash = '';
        $regdepositticket = '';
        $regdepositcoupon = '';

        $regreloadcash = '';
        $regreloadticket = '';
        $regreloadcoupon = '';

        $gendepositcash = '';
        $gendepositticket = '';
        $gendepositcoupon = '';

        $genreloadcash = '';
        $genreloadticket = '';
        $genreloadcoupon = '';


        $withdrawalcashier2 = '';
        $withdrawalgenesis2 = '';
        

        foreach ($total_rows as $r) {
            $regdepositcash += $r['RegDCash'];
            $regdepositticket += $r['RegDTicket'];
            $regdepositcoupon += $r['RegDCoupon'];

            $regreloadcash += $r['RegRCash'];
            $regreloadticket += $r['RegRTicket'];
            $regreloadcoupon += $r['RegRCoupon'];

            $gendepositcash += $r['GenDCash'];
            $gendepositticket += $r['GenDTicket'];
            $gendepositcoupon += $r['GenDCoupon'];

            $genreloadcash += $r['GenRCash'];
            $genreloadticket += $r['GenRTicket'];
            $genreloadcoupon += $r['GenRCoupon'];


            $withdrawalcashier2 += $r['WCashier'];
            $withdrawalgenesis2 += $r['WGenesis'];
        }


        $subtotaldcash = $regdepositcash + $gendepositcash;
        $subtotalrcash = $regreloadcash + $genreloadcash;
//
         $totalcash = $subtotaldcash + $subtotalrcash;
         $cashonhand = $totalcash-($withdrawalcashier2 + $manualredemptions + $ticketlist[0]['EncashedTickets']) + ($eWalletDeposits - $eWalletWithdrawals);
        
         return $cashonhand;
    }
    
    private function _computeCashOnHandPerCashier($start_date, $end_date){
        Mirage::loadModels(array('TransactionSummaryModel', 'EWalletTransModel'));
        $transactionSummaryModel = new TransactionSummaryModel();
        $eWalletTransModel = new EWalletTransModel();
        
        $eWalletDeposits = $eWalletTransModel->getDepositSumPerCashier($start_date, $end_date, $this->site_id, $this->acc_id);
        $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerCashier($start_date,$end_date, $this->site_id, $this->acc_id);
        $total_rows = $transactionSummaryModel->getTransactionSummaryPerCashierTotals($this->site_id,$this->site_code,$this->acc_id,$start_date,$end_date);
        $ticketlist = $this->getTicketListperCashier($start_date, $end_date, $this->acc_id);
        
        $depositcash = 0;
        $reloadcash = 0;
        $withdrawalcashier = 0;
        foreach($total_rows as $r){
            $depositcash += $r['RegDCash'];
            $reloadcash += $r['RegRCash'];
            $withdrawalcashier += $r['WCashier'];
        }

        $subtotaldcash = $depositcash + $reloadcash;

        $totalcash = $subtotaldcash;

        $cashonhand = $totalcash-($withdrawalcashier + $ticketlist[0]['EncashedTickets']) + ($eWalletDeposits - $eWalletWithdrawals);
      
        return $cashonhand;
    }
}