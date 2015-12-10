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
            $this->createUrl('reports/eWalletPerSite')=>'e-SAFE Transaction History Per Site',
            $this->createUrl('reports/eWalletPerCashier')=>'e-SAFE Transaction History Per Cashier',
            $this->createUrl('reports/siteCashOnHand')=>'Site Cash On Hand'
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
            list($rows,$total_rows,$page_count,$displayingpageof)=  $this->_getTransHistory($date, $enddate, $start, $limit);    

            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($enddate)) . ' ' .Mirage::app()->param['cut_off'].' AM';
//            $arrdata='';
//            foreach ($rows as $key => $values){
//                    $arrdata .= $key.": TransactionSummaryID=".$values['TransactionSummaryID'].",IseSAFETrans=".$values['IseSAFETrans'].", AccountTypeID=".$values['AccountTypeID']."\n";
//            }
//            
//            echo json_encode(array('rows'=>$arrdata));
//            Mirage::app()->end();
            echo json_encode(array('rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof,'coverage'=>$coverage));
            Mirage::app()->end();
        } else {
            list($rows,$total_rows,$page_count,$displayingpageof)=  $this->_getTransHistory($date, $enddate, $start, $limit);

            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($enddate)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            $this->renderPartial('reports_transaction_history',array('reportsFormModel'=>$reportsFormModel,
                'rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage));
        }
    }
    
    public function transactionHistorySalesAction() {
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
            
            $tID = '';
            $transSummID = '';
            $IsTotal = 0;
            $IsSales = 1;
            $arrdata = array();
            $arrdata['TotalRegCash'] = 0;
            $arrdata['TotalRegTicket'] = 0;
            $arrdata['TotalRegCoupon'] = 0;
            $arrdata['TotalCashierRedemption'] = 0;
            
            $enddate = addOneDay($date);
            list($rows,$total_rows,$page_count,$displayingpageof,$eWalletDeposits, $eWalletWithdrawals, $eWalletCashDeposits,
                    $eWalletCouponDeposits,$eWalletTicketDeposits,$eWalletTicketWithdrawals)=  $this->_getTransHistory($date, $enddate, $start, $limit,$tID,$transSummID,$IsTotal,$IsSales);    
                       
            foreach($total_rows as $value){
                $arrdata['TotalRegCash'] += $value['RegDCash'];
                $arrdata['TotalRegCash'] += $value['RegRCash'];
                $arrdata['TotalRegCash'] += $value['GenDCash'];
                $arrdata['TotalRegCash'] += $value['GenRCash'];
                $arrdata['TotalRegTicket'] += $value['RegDTicket'];
                $arrdata['TotalRegTicket'] += $value['RegRTicket'];
                $arrdata['TotalRegTicket'] += $value['GenDTicket'];
                $arrdata['TotalRegTicket'] += $value['GenRTicket'];
                $arrdata['TotalRegCoupon'] += $value['RegDCoupon'];
                $arrdata['TotalRegCoupon'] += $value['RegRCoupon'];
                $arrdata['TotalRegCoupon'] += $value['GenDCoupon'];
                $arrdata['TotalRegCoupon'] += $value['GenRCoupon'];
                $arrdata['TotalCashierRedemption'] += $value['WCashier'];
            }
            
            $ticketlist = $this->getTicketList($date, $enddate);
            $getActiveTicketsForTheDay = $this->getActiveTicketsForTheDay($date, $enddate);
            
            $manualredemptions = $this->getmanualRedemptions($date, $enddate);
            
            $runningactivetickets = $this->getrunningactivetickets($date, $enddate);
            
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($enddate)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            echo json_encode(array('total_rows'=>$arrdata,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof,'coverage'=>$coverage, 'ticketlist'=>$ticketlist, 'ActiveTickets' => $getActiveTicketsForTheDay, 'manualredemptions'=>$manualredemptions, 
                'runningactivetickets'=>$runningactivetickets,'eWalletDeposits'=>$eWalletDeposits, 'eWalletWithdrawals'=>$eWalletWithdrawals,
                'eWalletCashDeposits'=>$eWalletCashDeposits,'eWalletCouponDeposits'=>$eWalletCouponDeposits,'eWalletTicketDeposits'=>$eWalletTicketDeposits,'eWalletTicketWithdrawals'=>$eWalletTicketWithdrawals));
            Mirage::app()->end();
        }
    }
    
    public function transactionHistoryTotalAction() {
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

            $enddate = addOneDay($date);
            $tID = '';
            $transSummID = '';
            $IsTotal = 1;
            list($rows,$total_rows,$page_count,$displayingpageof)=  $this->_getTransHistory($date, $enddate, $start, $limit,$tID,$transSummID,$IsTotal);    

            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($enddate)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            echo json_encode(array('total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage));
            Mirage::app()->end();
        }
    }
    
    public function transactionHistoryPerTerminalIDAction() {
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
            $terminalID = $reportsFormModel->terminal_id;
            $transSumID = $reportsFormModel->trans_sum_id;
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

            $enddate = addOneDay($date);
            list($rows,$total_rows,$page_count,$displayingpageof)=  $this->_getTransHistory($date, $enddate, $start, $limit,$terminalID,$transSumID);    
            
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($enddate)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            echo json_encode(array('rows'=>$rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage));
            Mirage::app()->end();
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
    
    public function getActiveTicketsForTheDay($date, $enddate){
        $transactionSummaryModel = new TransactionSummaryModel();
        
        $getActiveTicketsForTheDay = $transactionSummaryModel->getActiveTicketsForTheDay($this->site_id, $date, $enddate);
        
        return $getActiveTicketsForTheDay;
    }
    
    public function getTicketListperCashier($date, $enddate, $aid){
        $transactionSummaryModel = new TransactionSummaryModel();
        
        $ticketlist = $transactionSummaryModel->getTicketListperCashier($this->site_id, $date, $enddate, $aid);
        
        return $ticketlist;
    }
    
    public function getActiveTicketsForTheDayPerCashier($date, $enddate, $aid){
        $transactionSummaryModel = new TransactionSummaryModel();
        
        $getActiveTicketsForTheDay = $transactionSummaryModel->getActiveTicketsForTheDayPerCashier($this->site_id, $date, $enddate, $aid);
        
        return $getActiveTicketsForTheDay;
    }

    protected function _getTransHistory($date,$enddate,$start,$limit,$terminalID='',$transSumID='',$IsTotal = 0,$IsSales = 0) {
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
        
        $eWalletDeposits = 0;
        $eWalletCashDeposits = 0;
        $eWalletCouponDeposits = 0;
        $eWalletTicketDeposits = 0;
        $eWalletWithdrawals = 0;
        $eWalletTicketWithdrawals = 0;
        
        if(empty($terminalID) && empty($transSumID) && !$IsTotal && !$IsSales){ //For Transaction History Per Site Grid
            $rows = $transactionSummaryModel->_getTransSummaryPaging($this->site_id,  $this->site_code, $date, $enddate, $startlimit, $limit);
            $total_rows = $transactionSummaryModel->_getTransSummaryTotalsPerCG($this->site_id,  $this->site_code, $date, $enddate, $startlimit, $limit);       
        } else if($IsTotal == 1 && empty($terminalID) && empty($transSumID)){ //For Total Link Popup
            $rows = 0;
            $total_rows = $transactionSummaryModel->getTransSummaryTotalsPerCG($this->site_id,  $this->site_code, $date, $enddate, $startlimit, $limit);
        } else if($IsSales == 1 && empty($terminalID) && empty($transSumID)){ //For Sales Link Popup
            $rows = 0;
            $total_rows = $transactionSummaryModel->getTransSummaryTotalsPerCG($this->site_id,  $this->site_code, $date, $enddate, $startlimit, $limit);
            $eWalletDeposits = $eWalletTransModel->getDepositSumPerSite($date, $enddate, $this->site_id);
            $eWalletCashDeposits = $eWalletTransModel->getCashDepositSumPerSite($date, $enddate, $this->site_id);
            $eWalletCouponDeposits = $eWalletTransModel->getCouponDepositSumPerSite($date, $enddate, $this->site_id);
            $eWalletTicketDeposits = $eWalletTransModel->getTicketDepositSumPerSite($date, $enddate, $this->site_id);
            $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerSite($date,$enddate, $this->site_id);
            $eWalletTicketWithdrawals = $eWalletTransModel->getWithdrawalTicketSumPerSite($date,$enddate, $this->site_id);
        } else if(!empty($terminalID) && !empty($transSumID)){ //For Terminal Code Link PopUp
            $rows = $transactionSummaryModel->getTransSummaryPagingWithTerminalID($this->site_id,  $this->site_code, $terminalID, $transSumID, $date, $enddate, $startlimit, $limit);
            $total_rows = 0;
        }
        
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;

        if(isset($_POST['startlimit'])) 
            $displayingpageof = 'Displaying page ' . (($start)? $start : '0') . ' of ' . $page_count;
        
        return array($rows,$total_rows,$page_count,$displayingpageof, $eWalletDeposits, $eWalletWithdrawals, $eWalletCashDeposits,$eWalletCouponDeposits,$eWalletTicketDeposits,$eWalletTicketWithdrawals);
    }
    
    public function transactionHistoryPerCashierAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        

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
            
            list($rows,$total_rows,$page_count,$displayingpageof)=  $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit); 
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            echo json_encode(array('rows'=>$rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof, 'coverage'=>$coverage));
            Mirage::app()->end();            
        } else {
            list($rows,$total_rows,$page_count,$displayingpageof) = $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            $this->renderPartial('reports_transaction_history_cashier',array('reportsFormModel'=>$reportsFormModel,
                'rows'=>$rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof, 'coverage'=>$coverage));
        }
    }
    
    
    public function transactionHistoryPerCashierSalesAction() {
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
            
            $end_date = addOneDay($start_date);   
            $arrdata = array();
            $tID = '';
            $transSummID = '';
            $IsTotal = 0;
            $IsSales = 1;
            
            list($rows,$total_rows,$page_count,$displayingpageof,$eWalletDeposits, $eWalletWithdrawals, 
                    $eWalletCashDeposits,$eWalletCouponDeposits,$eWalletTicketDeposits)=  $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit,$tID,$transSummID,$IsTotal,$IsSales); 
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $ticketlist = $this->getTicketListperCashier($start_date, $end_date, $this->acc_id);
            
            foreach($total_rows as $value){
                $arrdata['TotalRegCash'] += $value['DCash'];
                $arrdata['TotalRegCash'] += $value['RCash'];
                $arrdata['TotalRegTicket'] += $value['DTicket'];
                $arrdata['TotalRegTicket'] += $value['RTicket'];
                $arrdata['TotalRegCoupon'] += $value['DCoupon'];
                $arrdata['TotalRegCoupon'] += $value['RCoupon'];
                $arrdata['TotalCashierRedemption'] += $value['WCashier'];
            }
            
            echo json_encode(array('rows'=>$rows,'total_rows'=>$arrdata,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof, 'coverage'=>$coverage,'ticketlist'=>$ticketlist, 'eWalletDeposits'=>$eWalletDeposits, 'eWalletWithdrawals'=>$eWalletWithdrawals,
                'eWalletCashDeposits'=>$eWalletCashDeposits, 'eWalletCouponDeposits' => $eWalletCouponDeposits, 'eWalletTicketDeposits' => $eWalletTicketDeposits));
            Mirage::app()->end();            
        }
    }
    
    
    public function transactionHistoryPerCashierTotalAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        

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
            
            $end_date = addOneDay($start_date);   
            $tID = '';
            $transSummID = '';
            $IsTotal = 1;

            list($rows,$total_rows,$page_count,$displayingpageof)=  $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit,$tID,$transSummID,$IsTotal); 
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            echo json_encode(array('rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof, 'coverage'=>$coverage));
            Mirage::app()->end();            
        } 
    }
    
    
    public function transactionHistoryPerCashierWithTerminalIDAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        

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
        
        if(isset($_POST['ReportsFormModel'])) {
            $reportsFormModel->setAttributes($_POST['ReportsFormModel']);
            $start_date = $reportsFormModel->date;
            $terminalID = $reportsFormModel->terminal_id;
            $transSumID = $reportsFormModel->trans_sum_id;
            
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

            list($rows,$total_rows,$page_count,$displayingpageof) = $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit,$terminalID,$transSumID);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
           
            echo json_encode(array('rows'=>$rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof, 'coverage'=>$coverage));
            Mirage::app()->end();            
        } 
    }
    
    protected function _getTransHistoryPerCashier($start_date, $end_date, $start, $limit,$terminalID='',$transSumID='',$IsTotal=0,$IsSales=0) {
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
        
        $eWalletDeposits = 0;
        $eWalletCashDeposits = 0;
        $eWalletCouponDeposits = 0;
        $eWalletTicketDeposits = 0;
        $eWalletWithdrawals = 0;

        if(empty($terminalID) && empty($transSumID) && !$IsTotal && !$IsSales){ //For Transaction History Per Site Grid
            $rows = $transactionSummaryModel->_getTransactionSummaryPerCashier($this->site_id,$this->acc_id,$this->site_code, $start_date, $end_date, $startlimit, $limit);      
            $total_rows = 0;
        } else if($IsTotal == 1 && empty($terminalID) && empty($transSumID)){ //For Total Link Popup
            $rows = 0;
            $total_rows = $transactionSummaryModel->_getTransactionSummaryPerCashierTotals($this->site_id,$this->site_code,$this->acc_id,$start_date,$end_date);
        } else if($IsSales == 1 && empty($terminalID) && empty($transSumID)){ //For Sales Link Popup
            $rows = 0;
            $total_rows = $transactionSummaryModel->_getTransactionSummaryPerCashierTotals($this->site_id,$this->site_code,$this->acc_id,$start_date,$end_date);
            $eWalletDeposits = $eWalletTransModel->getDepositSumPerCashier($start_date, $end_date, $this->site_id, $this->acc_id);
            $eWalletCashDeposits = $eWalletTransModel->getCashDepositSumPerCashier($start_date, $end_date, $this->site_id, $this->acc_id);
            $eWalletCouponDeposits = $eWalletTransModel->getCouponDepositSumPerCashier($start_date, $end_date, $this->site_id, $this->acc_id);
            $eWalletTicketDeposits = $eWalletTransModel->getTicketDepositSumPerCashier($start_date, $end_date, $this->site_id, $this->acc_id);
            $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerCashier($start_date,$end_date, $this->site_id, $this->acc_id);
        } else if(!empty($terminalID) && !empty($transSumID)){ //For Terminal Code Link PopUp
            $rows = $transactionSummaryModel->getTransactionSummaryPerCashierWithTerminalID($this->site_id,$this->acc_id,$this->site_code,$terminalID,$transSumID,$start_date, $end_date, $startlimit, $limit);
            $total_rows = 0;
        }
        
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;

        return array($rows,$total_rows,$page_count,$displayingpageof, $eWalletDeposits, $eWalletWithdrawals, $eWalletCashDeposits,$eWalletCouponDeposits,$eWalletTicketDeposits);
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

            echo json_encode(array('rows'=>$rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage));
            Mirage::app()->end();            
        } else {
            list($rows,$total_rows,$page_count,$displayingpageof) = $this->_getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            $this->renderPartial('reports_transaction_history_virtual_cashier',array('reportsFormModel'=>$reportsFormModel,
                'rows'=>$rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage)); 
        }
    }
    
    public function transactionHistoryPerVirtualCashierSalesAction() {
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

            $end_date = addOneDay($start_date);   
            $tID = '';
            $transSummID = '';
            $IsTotal = 0;
            $IsSales = 1;
            
            list($rows,$total_rows,$page_count,$displayingpageof,$eWalletDeposits, $eWalletWithdrawals, $eWalletCashDeposits,$eWalletTicketDeposits) = $this->_getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit,$tID,$transSummID,$IsTotal,$IsSales);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            $vcaid = $accounts->getVirtualCashier($this->site_id);
            $vcaid = $vcaid['AID'];
            
            $ticketlist = $this->getTicketListperCashier($start_date, $end_date, $vcaid);
            $getActiveTicketsForTheDay = $this->getActiveTicketsForTheDayPerCashier($start_date, $end_date, $vcaid);
            $manualredemptions = $this->getmanualRedemptions($start_date, $end_date);
            
            $runningactivetickets = $this->getrunningactivetickets($start_date, $end_date);
            
            foreach($total_rows as $value){
                $arrdata['TotalRegCash'] += $value['DCash'];
                $arrdata['TotalRegCash'] += $value['RCash'];
                $arrdata['TotalRegTicket'] += $value['DTicket'];
                $arrdata['TotalRegTicket'] += $value['RTicket'];
                $arrdata['TotalRegCoupon'] += $value['DCoupon'];
                $arrdata['TotalRegCoupon'] += $value['RCoupon'];
                $arrdata['TotalCashierRedemption'] += $value['WCashier'];
            }
            
            echo json_encode(array('total_rows'=>$arrdata,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof,'coverage'=>$coverage,'ticketlist'=>$ticketlist, 'ActiveTickets' => $getActiveTicketsForTheDay, 'manualredemptions'=>$manualredemptions, 'runningactivetickets'=>$runningactivetickets,
                'eWalletDeposits'=>$eWalletDeposits,'eWalletWithdrawals'=>$eWalletWithdrawals,'eWalletCashDeposits'=>$eWalletCashDeposits,'eWalletTicketDeposits'=>$eWalletTicketDeposits));
            Mirage::app()->end();            
        }
    }
    
    public function transactionHistoryPerVirtualCashierTotalAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        

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

            $end_date = addOneDay($start_date);   
            $tID = '';
            $transSummID = '';
            $IsTotal = 1;
            
            list($rows,$total_rows,$page_count,$displayingpageof,$eWalletDeposits,
                    $eWalletWithdrawals,$eWalletCashDeposits,$eWalletTicketDeposits) = $this->_getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit,$tID,$transSummID,$IsTotal);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            echo json_encode(array('total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage,
                'eWalletWithdrawals'=>$eWalletWithdrawals,'eWalletCashDeposits'=>$eWalletCashDeposits,'eWalletTicketDeposits'=>$eWalletTicketDeposits));
            Mirage::app()->end();            
        }
    }
    
    public function transactionHistoryPerVirtualCashierWithTerminalIDAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();        
        
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

        if(isset($_POST['ReportsFormModel'])) {
            $reportsFormModel->setAttributes($_POST['ReportsFormModel']);
            $start_date = $reportsFormModel->date;
            $terminalID = $reportsFormModel->terminal_id;
            $transSumID = $reportsFormModel->trans_sum_id;
            
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

            $end_date = addOneDay($start_date);   
            
            list($rows,$total_rows,$page_count,$displayingpageof,$eWalletDeposits,
                    $eWalletWithdrawals,$eWalletCashDeposits,$eWalletTicketDeposits,$IseSAFETrans) = $this->_getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit,$terminalID,$transSumID);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';

            echo json_encode(array('rows'=>$rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage,
                'eWalletWithdrawals'=>$eWalletWithdrawals,'eWalletCashDeposits'=>$eWalletCashDeposits,'eWalletTicketDeposits'=>$eWalletTicketDeposits,'IseSAFETrans'=>$IseSAFETrans));
            Mirage::app()->end();            
        }
    }
    
    
    protected function _getTransHistoryPerVirtualCashier($start_date, $end_date, $start, $limit,$terminalID='',$transSumID='',$IsTotal=0,$IsSales=0) {
        Mirage::loadModels(array('TransactionSummaryModel', 'AccountsModel','EWalletTransModel'));
        $transactionSummaryModel = new TransactionSummaryModel();
        $accounts = new AccountsModel();
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
        
        $vcaid = $accounts->getVirtualCashier($this->site_id);
        $vcaid = $vcaid['AID'];
        
        $eWalletDeposits = 0;
        $eWalletCashDeposits = 0;
        $eWalletTicketDeposits = 0;
        $eWalletWithdrawals = 0;

        if(empty($terminalID) && empty($transSumID) && !$IsTotal && !$IsSales){ //For Transaction History Per Site Grid
            $rows = $transactionSummaryModel->_getTransactionSummaryPerVCashier($this->site_id,$vcaid,$this->site_code, $start_date, $end_date, $startlimit, $limit);     
            $total_rows = 0;
        } else if($IsTotal == 1 && empty($terminalID) && empty($transSumID)){ //For Total Link Popup
            $rows = 0;
            $total_rows = $transactionSummaryModel->_getTransactionSummaryPerCashierTotals($this->site_id,$this->site_code,$vcaid,$start_date,$end_date);
            $eWalletCashDeposits = $eWalletTransModel->getCashDepositSumPerVCashier($start_date, $end_date, $this->site_id, $vcaid);
            $eWalletTicketDeposits = $eWalletTransModel->getTicketDepositSumPerVCashier($start_date, $end_date, $this->site_id, $vcaid);
            $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerVCashier($start_date,$end_date, $this->site_id, $vcaid);
        } else if($IsSales == 1 && empty($terminalID) && empty($transSumID)){ //For Sales Link Popup
            $rows = 0;
            $total_rows = $transactionSummaryModel->_getTransactionSummaryPerCashierTotals($this->site_id,$this->site_code,$vcaid,$start_date,$end_date);
            $eWalletDeposits = $eWalletTransModel->getDepositSumPerVCashier($start_date, $end_date, $this->site_id, $vcaid);
            $eWalletCashDeposits = $eWalletTransModel->getCashDepositSumPerVCashier($start_date, $end_date, $this->site_id, $vcaid);
            $eWalletTicketDeposits = $eWalletTransModel->getTicketDepositSumPerVCashier($start_date, $end_date, $this->site_id, $vcaid);
            $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerVCashier($start_date,$end_date, $this->site_id, $vcaid);
        } else if(!empty($terminalID) && !empty($transSumID)){ //For Terminal Code Link PopUp
            $rows = $transactionSummaryModel->getTransactionSummaryPerCashierWithTerminalID($this->site_id,$vcaid,$this->site_code,$terminalID,$transSumID,$start_date, $end_date, $startlimit, $limit);
            $IseSAFETrans = $eWalletTransModel->CheckIfeSAFETrans($transSumID);
            $eWalletCashDeposits = $eWalletTransModel->getCashDepositSumPerVCashierPerTerminal($start_date, $end_date, $this->site_id, $vcaid,$transSumID,$terminalID);
            $eWalletTicketDeposits = $eWalletTransModel->getTicketDepositSumPerVCashierPerTerminal($start_date, $end_date, $this->site_id, $vcaid,$transSumID,$terminalID);
            $eWalletWithdrawals = $eWalletTransModel->getWithdrawalSumPerVCashierPerTerminal($start_date,$end_date, $this->site_id, $vcaid,$transSumID,$terminalID);
            $total_rows = 0;
        }
        
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;

        return array($rows,$total_rows,$page_count,$displayingpageof,$eWalletDeposits, $eWalletWithdrawals, $eWalletCashDeposits,$eWalletTicketDeposits,$IseSAFETrans);
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

            echo json_encode(array('data'=>$result, 'coverage'=>$coverage));          
        } else {
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $result = $ewalletTransModel->getEWalletTransactionPerSite($start_date, $end_date, $this->site_id);

            $this->renderPartial('reports_ewallet_transaction_history_site', array('reportsFormModel'=>$reportsFormModel, 'data'=>$result, 'coverage'=>$coverage));
        }
        
    }
    
    public function eWalletTransactionHistoryPerSiteTotalAction(){
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
            $result = $ewalletTransModel->getEWalletTransactionPerSiteTotal($start_date, $end_date, $this->site_id);
            $cashOnHand = $this->_computeCashOnHandPerSite($start_date, $end_date);
            
            echo json_encode(array('data'=>$result, 'coverage'=>$coverage, 'cashOnHand'=>$cashOnHand));          
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
            
            echo json_encode(array('data'=>$result, 'coverage'=>$coverage));          
        } else {
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $result = $ewalletTransModel->getEWalletTransactionPerCashier($start_date, $end_date, $this->site_id,$this->acc_id);
            
            $this->renderPartial('reports_ewallet_transaction_history_cashier', array('reportsFormModel'=>$reportsFormModel, 'data'=>$result, 'coverage'=>$coverage));
        }
        
    }
    
    public function eWalletTransactionHistoryPerCashierTotalAction(){
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
            $result = $ewalletTransModel->getEWalletTransactionPerCashierTotal($start_date, $end_date, $this->site_id,$this->acc_id);
            $cashOnHand = $this->_computeCashOnHandPerCashier($start_date, $end_date);
            
            echo json_encode(array('data'=>$result, 'coverage'=>$coverage, 'cashOnHand'=>$cashOnHand));          
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
    
    public function cashOnHandSiteReportAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        
        $reportsFormModel = new ReportsFormModel();
        
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
            $enddate = addOneDay($date);
            $transdetails=  $this->_getSiteCashOnHand($date, $enddate);    
            
            echo json_encode(array('transdetails'=>$transdetails));
            Mirage::app()->end();
        } else {
            $transdetails=$this->_getSiteCashOnHand($date, $enddate);
            
            $this->renderPartial('reports_site_cashonhand',array('reportsFormModel'=>$reportsFormModel, 'transdetails'=>$transdetails));
        }
    }
    
    protected function _getSiteCashOnHand($startdate,$enddate) {
            Mirage::loadModels(array('TransactionSummaryModel', 'EWalletTransModel'));
            $transactionSummaryModel = new TransactionSummaryModel();
            $eWalletTransModel = new EWalletTransModel();

            $transdetails = $transactionSummaryModel->getTransactionDetailsForCOH($startdate, $enddate, $this->site_id);
            $encashedtickets = $transactionSummaryModel->getEncashedTickets($startdate, $enddate, $this->site_id);
            $esafeloads = $eWalletTransModel->geteSAFELoadsAndWithdrawals($startdate, $enddate, $this->site_id);

            $transdetails['LoadCash'] += (float)$esafeloads['eSAFELoadCash'];
            $transdetails['LoadCoupon'] += (float)$esafeloads['eSAFELoadCoupon'];
            $transdetails['LoadBancnet'] += (float)$esafeloads['eSAFELoadBancnet'];
            $transdetails['LoadTicket'] += (float)$esafeloads['eSAFELoadTicket'];
            $transdetails['WCash'] += (float)$esafeloads['eSAFECashierRedemption'];
            $transdetails['WTicket'] += (float)$esafeloads['eSAFEGenesisRedemption'];
            
            !isset($transdetails['EncashedTickets']) ? $transdetails['EncashedTickets']=(float)$encashedtickets:'';
            !isset($transdetails['eSAFEGenesisRedemption']) ? $transdetails['eSAFEGenesisRedemption']=(float)$esafeloads['eSAFEGenesisRedemption']:'';
            return array($transdetails);

    }
}