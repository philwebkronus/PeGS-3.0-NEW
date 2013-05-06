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
    
    protected function _getTransHistory($date,$enddate,$start,$limit) {
        Mirage::loadModels('TransactionSummaryModel');
        $transactionSummaryModel = new TransactionSummaryModel();
        
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
        $total_rows = $transactionSummaryModel->getTransSummaryTotals($this->site_id,$this->site_code, $date, $enddate);
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;
        if(isset($_POST['startlimit'])) 
            $displayingpageof = 'Displaying page ' . (($start)? $start : '0') . ' of ' . $page_count;
        
        return array($rows,$total_rows,$page_count,$displayingpageof);
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
            list($rows,$total_rows,$page_count,$displayingpageof) = $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            
            echo json_encode(array('rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,
                'displayingpageof'=>$displayingpageof,'coverage'=>$coverage));
            Mirage::app()->end();            
        } else {
            list($rows,$total_rows,$page_count,$displayingpageof) = $this->_getTransHistoryPerCashier($start_date, $end_date, $start, $limit);
            $coverage = 'Coverage ' . date('l , F d, Y ',strtotime($start_date)) . ' ' .Mirage::app()->param['cut_off'].' AM to ' . date('l , F d, Y ',strtotime($end_date)) . ' ' .Mirage::app()->param['cut_off'].' AM';
            $this->renderPartial('reports_transaction_history_cashier',array('reportsFormModel'=>$reportsFormModel,
                'rows'=>$rows,'total_rows'=>$total_rows,'page_count'=>$page_count,'displayingpageof'=>$displayingpageof,'coverage'=>$coverage));
        }
    }
    
    protected function _getTransHistoryPerCashier($start_date, $end_date, $start, $limit) {
        Mirage::loadModels('TransactionSummaryModel');
        $transactionSummaryModel = new TransactionSummaryModel();
        
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
        
        $total_rows = $transactionSummaryModel->getTransactionSummaryPerCashierTotals($this->site_id,$this->acc_id,$start_date,$end_date);
        $displayingpageof = 'Displaying page ' . (($page_count)? '1' : '0') . ' of ' . $page_count;
        if(isset($_POST['startlimit'])) {
            if($page_count == 0) {
                $start = 0;
            }
            $displayingpageof = 'Displaying page ' . (($start)? $start : '0') . ' of ' . $page_count;
        }
            
        return array($rows,$total_rows,$page_count,$displayingpageof);
    }
    
}