<?php
Mirage::loadComponents(array('FrontendController'));
Mirage::loadModels('ViewTransactionFormModel');
/**
 * Description of ViewTransactionController
 *
 * @author Bryan Salazar
 */
class ViewTransactionController extends FrontendController {
    
    public $title = 'View Transaction History';
    
    public function overviewAction()
    {
        if(!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }
        
        Mirage::loadModels(array('TransactionDetailsModel'));
        $transactionDetailsModel = new TransactionDetailsModel();
        $limit = 50;
        $createdBy = $_SESSION['accID'];
        $start_date = date('Y-m-d');
        $datenow = date('Y-m-d');
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
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
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit,$start_date,$end_date);
        $transactionHistory = json_encode(array('trans_details'=>$rows,'site_code'=>$_SESSION['site_code']));
        
        $this->renderPartial('viewtransaction_overview', array('transactionHistory'=>$transactionHistory));
    }
    
    public function overview2Action()
    {
        
         if(!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }
        
        Mirage::loadModels(array('TransactionDetailsModel'));
        Mirage::loadModels(array('AccountsModel'));
        $accountsmodel = new AccountsModel();
        
        $transactionDetailsModel = new TransactionDetailsModel();
        $limit = 50;
        $vcashier = $accountsmodel->getVirtualCashier($this->site_id);
        $createdBy = $vcashier['AID'];
        
        $start_date = date('Y-m-d');
        $datenow = date('Y-m-d');
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
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
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit,$start_date,$end_date);
        $transactionHistory = json_encode(array('trans_details'=>$rows,'site_code'=>$_SESSION['site_code']));
        
        
        $this->renderPartial('viewtransaction_overview2', array('transactionHistory'=>$transactionHistory));
    }
    
    public function viewTransactionAction()
    {
        if(!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }
        Mirage::loadModels(array('TransactionDetailsModel'));
        
        
        $transactionDetailsModel = new TransactionDetailsModel();
        $limit = $_GET['limit'];
        $createdBy = $_SESSION['accID'];
        $start_date = $_GET['date'];
        $datenow = date('Y-m-d');
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
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
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit,$start_date,$end_date);
        echo json_encode(array('trans_details'=>$rows,'site_code'=>$_SESSION['site_code']));
        Mirage::app()->end();
    }
    
    public function viewTransactionPerVirtualCashierAction()
    {
        if(!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }
        Mirage::loadModels(array('TransactionDetailsModel'));
        Mirage::loadModels(array('AccountsModel'));
        $accountsmodel = new AccountsModel();
        $transactionDetailsModel = new TransactionDetailsModel();
        
        $vcashier = $accountsmodel->getVirtualCashier($this->site_id);
        $limit = $_GET['limit'];
        $createdBy = $vcashier['AID'];
        $start_date = $_GET['date'];
        $datenow = date('Y-m-d');
        $time = date('H:i:s');
        $cutoff = Mirage::app()->param['cut_off'];
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
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit,$start_date,$end_date);
        echo json_encode(array('trans_details'=>$rows,'site_code'=>$_SESSION['site_code']));
        Mirage::app()->end();
    }
    
    public function historyAction(){
        $viewTransactionFormModel = new ViewTransactionFormModel();
        $history_type = array(
            ''=>'Please select history type',
            $this->createUrl('viewtrans/overview')=>'Transaction History Per Cashier',
            $this->createUrl('viewtrans/overview2')=>'Transaction History Per Virtual Cashier',
            $this->createUrl('viewtrans/ewalletPerCashier')=>'e-SAFE Transaction History Per Cashier'
        );
        
        $this->render('viewtransaction_history', array('viewTransactionFormModel'=>$viewTransactionFormModel, 'history_type'=>$history_type));
    }
    
    
    public function viewEwalletTransactionPerCashierAction()
    {
        if(!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }
        
        Mirage::loadModels(array('EWalletTransModel'));
        $eWalletModel = new EWalletTransModel();
        
        $jsonMode = false;
        if(isset($_GET['limit']) && isset($_GET['date'])){
            $jsonMode=true;
            $limit = $_GET['limit'];
            $createdBy = $_SESSION['accID'];
            $start_date = $_GET['date'];
            $cutoff = Mirage::app()->param['cut_off'];
        }else{
            $limit = 50;
            $createdBy = $_SESSION['accID'];
            $start_date = date('Y-m-d');
            $cutoff = Mirage::app()->param['cut_off'];
        }
        $datenow = date('Y-m-d');
        $time = date('H:i:s');
       
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
        $rows = $eWalletModel->getEWalletTransactionPerCashier($start_date, $end_date, $this->site_id, $this->acc_id, $limit);
        $transactionHistory = json_encode(array('trans_details'=>$rows,'site_code'=>$_SESSION['site_code']));
        if($jsonMode){
            echo $transactionHistory;
        }else{
            $this->renderPartial('viewtransaction_ewallet_per_cashier', array('transactionHistory'=>$transactionHistory));
        }
    }
    
    
}