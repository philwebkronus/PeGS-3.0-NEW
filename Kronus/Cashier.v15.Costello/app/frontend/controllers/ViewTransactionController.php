<?php

Mirage::loadComponents(array('FrontendController'));
Mirage::loadModels('ViewTransactionFormModel');
Mirage::loadModels('SitesModel');

/**
 * Description of ViewTransactionController
 *
 * @author Bryan Salazar
 */
class ViewTransactionController extends FrontendController {

    public $title = 'View Transaction History';

    public function overviewAction() {
        if (!$this->isAjaxRequest()) {
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
        if ($start_date == $datenow) {
            //if date is today, check the cutoff time;
            if ($time < $cutoff) {
                //get the -1 day
                $start_date = minusOneDay($start_date);
            }
        }
        $end_date = addOneDay($start_date);
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit, $start_date, $end_date);

        $coverage = 'Coverage ' . date('l , F d, Y ', strtotime($start_date)) . ' ' . $cutoff . ' AM to ' . date('l , F d, Y ', strtotime($end_date)) . ' ' . $cutoff . ' AM';
        $transactionHistory = json_encode(array('trans_details' => $rows, 'site_code' => $_SESSION['site_code'], 'coverage' => $coverage));

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $this->renderPartial('viewtransaction_overview', array('transactionHistory' => $transactionHistory, 'siteAmountInfo' => $siteAmountInfo));
    }

    public function overview2Action() {

        if (!$this->isAjaxRequest()) {
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
        if ($start_date == $datenow) {
            //if date is today, check the cutoff time;
            if ($time < $cutoff) {
                //get the -1 day
                $start_date = minusOneDay($start_date);
            }
        }
        $end_date = addOneDay($start_date);
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit, $start_date, $end_date);

        $coverage = 'Coverage ' . date('l , F d, Y ', strtotime($start_date)) . ' ' . $cutoff . ' AM to ' . date('l , F d, Y ', strtotime($end_date)) . ' ' . $cutoff . ' AM';
        $transactionHistory = json_encode(array('trans_details' => $rows, 'site_code' => $_SESSION['site_code'], 'coverage' => $coverage));

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $this->renderPartial('viewtransaction_overview2', array('transactionHistory' => $transactionHistory, 'siteAmountInfo' => $siteAmountInfo));
    }

    /*
     * Added : 06 22 2018
     * JAVIDA
     */


    public function overview3Action() {
        if (!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }

        Mirage::loadModels(array('TransactionDetailsModel'));
        $transactionDetailsModel = new TransactionDetailsModel();
        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $transactionHistory = '';

        if (!empty($_GET['date'])) {

            $start_date = $_GET['date'];
            $end_date = addOneDay($start_date);

            $cutoff = Mirage::app()->param['cut_off'];

            $TDrows = $transactionDetailsModel->getTransactionDetailsPerCutOff($start_date, $end_date, $this->site_id);

            $MRrows = $transactionDetailsModel->getManualRedemptionsPerCutOff($start_date, $end_date, $this->site_id);

            date_default_timezone_set('Asia/Taipei');
            $dateNow = date('mdYHis', time());
            $formattedDateNow = date('m d Y H:i:s', time());

            $coverageStart = $start_date . ' ' . $cutoff . ' AM';

            if ($start_date === date('Y-m-d', time())) {
                $coverageEnd = date('Y-m-d h:i:s A', time());
            } else {
                $coverageEnd = $end_date . ' ' . '05:59:59 AM';
            }

            $transactionHistory = json_encode(array('trans_details' => $TDrows, 'manual_redemption' => $MRrows, 'coverageStart' => $coverageStart, 'coverageEnd' => $coverageEnd, 'site_code' => str_replace('ICSA-', '', $_SESSION['site_code']), 'generated_date' => $dateNow, 'genreated_date_formatted' => $formattedDateNow));

            echo $transactionHistory;
            Mirage::app()->end();
        } else {
            $this->renderPartial('viewtransaction_overview3', array('siteAmountInfo' => $siteAmountInfo));
        }
    }

    public function viewTransactionAction() {
        if (!$this->isAjaxRequest()) {
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
        if ($start_date == $datenow) {
            //if date is today, check the cutoff time;
            if ($time < $cutoff) {
                //get the -1 day
                $start_date = minusOneDay($start_date);
            }
        }
        $end_date = addOneDay($start_date);
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit, $start_date, $end_date);
        $coverage = 'Coverage ' . date('l , F d, Y ', strtotime($start_date)) . ' ' . $cutoff . ' AM to ' . date('l , F d, Y ', strtotime($end_date)) . ' ' . $cutoff . ' AM';

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        echo json_encode(array('trans_details' => $rows, 'site_code' => $_SESSION['site_code'], 'coverage' => $coverage, 'siteAmountInfo' => $siteAmountInfo));
        Mirage::app()->end();
    }

    public function viewTransactionPerVirtualCashierAction() {
        if (!$this->isAjaxRequest()) {
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
        if ($start_date == $datenow) {
            //if date is today, check the cutoff time;
            if ($time < $cutoff) {
                //get the -1 day
                $start_date = minusOneDay($start_date);
            }
        }
        $end_date = addOneDay($start_date);
        $rows = $transactionDetailsModel->getTransactionDetails($createdBy, $limit, $start_date, $end_date);
        $coverage = 'Coverage ' . date('l , F d, Y ', strtotime($start_date)) . ' ' . $cutoff . ' AM to ' . date('l , F d, Y ', strtotime($end_date)) . ' ' . $cutoff . ' AM';

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        echo json_encode(array('trans_details' => $rows, 'site_code' => $_SESSION['site_code'], 'coverage' => $coverage, 'siteAmountInfo' => $siteAmountInfo));
        Mirage::app()->end();
    }

    public function historyAction() {
        $viewTransactionFormModel = new ViewTransactionFormModel();
        $history_type = array(
            '' => 'Please select history type',
            $this->createUrl('viewtrans/overview') => 'Transaction History Per Cashier',
            $this->createUrl('viewtrans/overview2') => 'Transaction History Per Virtual Cashier',
            $this->createUrl('viewtrans/overview3') => 'Transaction Details Per Cut-off',
                // CCT BEGIN comment
                /*
                  $this->createUrl('viewtrans/ewalletPerSite')=>'e-SAFE Transaction History Per Site',
                  $this->createUrl('viewtrans/ewalletPerCashier')=>'e-SAFE Transaction History Per Cashier',
                  $this->createUrl('viewtrans/ewalletPerVCashier')=>'e-SAFE Transaction History Per Virtual Cashier',
                 */
                // CCT END comment            
        );

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $this->render('viewtransaction_history', array('viewTransactionFormModel' => $viewTransactionFormModel, 'history_type' => $history_type, 'siteAmountInfo' => $siteAmountInfo));
    }

    public function viewEwalletTransactionPerCashierAction() {
        if (!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }

        Mirage::loadModels(array('EWalletTransModel'));
        $eWalletModel = new EWalletTransModel();

        $jsonMode = false;
        if (isset($_GET['limit']) && isset($_GET['date'])) {
            $jsonMode = true;
            $limit = $_GET['limit'];
            $createdBy = $_SESSION['accID'];
            $start_date = $_GET['date'];
            $cutoff = Mirage::app()->param['cut_off'];
        } else {
            $limit = 50;
            $createdBy = $_SESSION['accID'];
            $start_date = date('Y-m-d');
            $cutoff = Mirage::app()->param['cut_off'];
        }
        $datenow = date('Y-m-d');
        $time = date('H:i:s');

        //if time was less than the cutoff
        if ($start_date == $datenow) {
            //if date is today, check the cutoff time;
            if ($time < $cutoff) {
                //get the -1 day
                $start_date = minusOneDay($start_date);
            }
        }
        $end_date = addOneDay($start_date);
        $rows = $eWalletModel->getEWalletTransactionPerCashierWithOrder($start_date, $end_date, $this->site_id, $this->acc_id, $limit);
        $coverage = 'Coverage ' . date('l , F d, Y ', strtotime($start_date)) . ' ' . $cutoff . ' AM to ' . date('l , F d, Y ', strtotime($end_date)) . ' ' . $cutoff . ' AM';

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $transactionHistory = json_encode(array('trans_details' => $rows, 'site_code' => $_SESSION['site_code'], 'coverage' => $coverage, 'siteAmountInfo' => $siteAmountInfo));
        if ($jsonMode) {
            echo $transactionHistory;
        } else {
            $this->renderPartial('viewtransaction_ewallet_per_cashier', array('transactionHistory' => $transactionHistory, 'siteAmountInfo' => $siteAmountInfo));
        }
    }

    public function viewEwalletTransactionPerVirtualCashierAction() {
        if (!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }

        Mirage::loadModels(array('EWalletTransModel', 'AccountsModel'));
        $eWalletModel = new EWalletTransModel();
        $accounts = new AccountsModel();

        $jsonMode = false;
        if (isset($_GET['limit']) && isset($_GET['date'])) {
            $jsonMode = true;
            $limit = $_GET['limit'];
            $start_date = $_GET['date'];
            $cutoff = Mirage::app()->param['cut_off'];
        } else {
            $limit = 50;
            $start_date = date('Y-m-d');
            $cutoff = Mirage::app()->param['cut_off'];
        }
        $datenow = date('Y-m-d');
        $time = date('H:i:s');

        $vcaid = $accounts->getVirtualCashier($this->site_id);
        $createdBy = $vcaid['AID'];

        //if time was less than the cutoff
        if ($start_date == $datenow) {
            //if date is today, check the cutoff time;
            if ($time < $cutoff) {
                //get the -1 day
                $start_date = minusOneDay($start_date);
            }
        }
        $end_date = addOneDay($start_date);
        $rows = $eWalletModel->getEWalletTransactionPerCashierWithOrder($start_date, $end_date, $this->site_id, $createdBy, $limit);
        $coverage = 'Coverage ' . date('l , F d, Y ', strtotime($start_date)) . ' ' . $cutoff . ' AM to ' . date('l , F d, Y ', strtotime($end_date)) . ' ' . $cutoff . ' AM';

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $transactionHistory = json_encode(array('trans_details' => $rows, 'site_code' => $_SESSION['site_code'], 'coverage' => $coverage, 'siteAmountInfo' => $siteAmountInfo));
        if ($jsonMode) {
            echo $transactionHistory;
        } else {
            $this->renderPartial('viewtransaction_ewallet_per_virtualcashier', array('transactionHistory' => $transactionHistory, 'siteAmountInfo' => $siteAmountInfo));
        }
    }

    public function viewEwalletTransactionPerSiteAction() {
        if (!$this->isAjaxRequest()) {
            Mirage::app()->error404();
        }

        Mirage::loadModels(array('EWalletTransModel'));
        $eWalletModel = new EWalletTransModel();

        $jsonMode = false;
        if (isset($_GET['limit']) && isset($_GET['date'])) {
            $jsonMode = true;
            $limit = $_GET['limit'];
            $start_date = $_GET['date'];
            $cutoff = Mirage::app()->param['cut_off'];
        } else {
            $limit = 50;
            $start_date = date('Y-m-d');
            $cutoff = Mirage::app()->param['cut_off'];
        }
        $datenow = date('Y-m-d');
        $time = date('H:i:s');

        //if time was less than the cutoff
        if ($start_date == $datenow) {
            //if date is today, check the cutoff time;
            if ($time < $cutoff) {
                //get the -1 day
                $start_date = minusOneDay($start_date);
            }
        }
        $end_date = addOneDay($start_date);
        $rows = $eWalletModel->getEWalletTransactionPerSiteWithOrder($start_date, $end_date, $this->site_id, $limit);
        $coverage = 'Coverage ' . date('l , F d, Y ', strtotime($start_date)) . ' ' . $cutoff . ' AM to ' . date('l , F d, Y ', strtotime($end_date)) . ' ' . $cutoff . ' AM';

        $sitesModel = new SitesModel();
        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $transactionHistory = json_encode(array('trans_details' => $rows, 'site_code' => $_SESSION['site_code'], 'coverage' => $coverage));
        if ($jsonMode) {
            echo $transactionHistory;
        } else {
            $this->renderPartial('viewtransaction_ewallet_per_site', array('transactionHistory' => $transactionHistory, 'siteAmountInfo' => $siteAmountInfo));
        }
    }

}

