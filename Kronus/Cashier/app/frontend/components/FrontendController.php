<?php
Mirage::loadComponents(array('Menu'));
Mirage::loadLibraries('util');
Mirage::loadModels('SiteBalanceModel');
@session_cache_expire(0);
@session_start();

/**
 * Date Created 10 27, 11 10:07:34 AM <pre />
 * Description of FrontendController
 * @author Bryan Salazar
 */
class FrontendController extends MI_Controller {
    
    /**
     * @var string default layout
     */
    public $layout = 'layout/main';
    
    /**
     * @var string default title of page
     */
    public $title = 'Terminal Monitoring';
    
    /**
     * @var int site id
     */
    protected $site_id = null;
    
    /**
     * @var string site code
     */
    protected $site_code = null;
    
    /**
     * @var int length of site code + 1
     */
    protected $len = null;
    /**
     * @var int account id
     */
    protected $acc_id = null;
    
    /**
     * @var int transaction status
     */
    protected $status = null;


    public $show_refresh = false;
    
    protected $_is_terminal_monitoring = false;
    
    /**
     * Description: get balance base on site id
     * @return string money formatted value
     */
    public function getSiteBalance() {
        $sitebalanceModel = new SiteBalanceModel();
        $site_balance = $sitebalanceModel->getSiteBalance($this->site_id);
        return toMoney($site_balance['Balance']);
    }
    
    /**
     * Description: Check if browser use is IE. Return true if IE else false
     * @return boolean 
     */
    protected function isIEBrowser() {
        $agent = (isset($_SERVER['HTTP_USER_AGENT']))?$_SERVER['HTTP_USER_AGENT'] : '';
        
        if((stripos($agent, 'msie') !== false) && (stripos($agent, 'opera') === false))
            return true;
        return false;
    }
    
    /**
     * constructor
     */
    public function FrontendController() {
        
        if(!MIRAGE_DEBUG) {
            if(!$this->isIEBrowser() && Mirage::app()->getModuleName() != 'monitoring' && Mirage::app()->getControllerName() != 'Refresh') {
                if($this->isAjaxRequest()) {
                    $this->throwError('Invalid Browser');
                }
                $this->redirect(Mirage::app()->param['logout_page']);
            }
        }

        if(!isset($_SESSION['success_login'])) {
            Mirage::app()->error401();
        }
        
        if(!isset($_SESSION['AccountSiteID']))
            Mirage::app()->error401();
        
        if(!isset($_SESSION['userid']))
            Mirage::app()->error401();
       
        if(isset($_GET['siteid'])) {
            $_SESSION['AccountSiteID'] = $_GET['siteid'];
            $_SESSION['site_code'] = $_GET['sitecode'];
            $_SESSION['isbgi'] = '';
        }        
        
        Mirage::loadModels(array('AccountSessionsModel','SitesModel','SiteAccountsModel'));             
        $accountSessionsModel = new AccountSessionsModel();
        $sitesModel = new SitesModel();  
        $siteAccountsModel = new SiteAccountsModel();
        $detail = $sitesModel->getPosAccntAndAccntName($_SESSION['AccountSiteID']);
        $_SESSION['pos_account'] = $detail['POSAccountNo'];
        $_SESSION['account_name'] = $detail['Name'];
        $session_id = $accountSessionsModel->getSessionId($_SESSION['userid']);
        if($session_id != $_SESSION['sessionID']) {
            Mirage::app()->error401();
        }
        
        if(!isset($_SESSION['site_code'])) {
            //Mirage::loadModels('SiteAccountsModel');
            //$siteAccountsModel = new SiteAccountsModel();
            $_SESSION['site_code'] = $siteAccountsModel->getSiteCodeByAccId($_SESSION['accID']);            
        
        }       
        $bgiOwner = Mirage::app()->param['BGI_ownerID'];    
        $isBGI = $siteAccountsModel->getSiteGroup($_SESSION['AccountSiteID'], $bgiOwner);
        $_SESSION['isbgi'] = $isBGI['ctrbgi']; //session variable for bgi and non-bgi
        
        if(Mirage::app()->getModuleName() != 'monitoring' && Mirage::app()->getControllerName() != 'Refresh' && in_array($_SESSION['acctype'], Mirage::app()->param['standalone_allowed_type'])) {
            $this->redirect($this->createUrl('monitoring/overview'));
        }
        
        $this->site_id = $_SESSION['AccountSiteID'];
        $this->site_code = $_SESSION['site_code']; 
        $this->acc_id = $_SESSION['accID'];
        $this->len = strlen($this->site_code) + 1;
        
        if(!$this->_is_terminal_monitoring) {
            unset($_SESSION['current_page']);
            unset($_SESSION['page']);
        }
    }
    
    /***************************** HELPER *************************************/
    protected function _getDenoCasinoMinMax() {
        Mirage::loadModels(array('SiteDenominationModel','TerminalsModel','TerminalServicesModel'));
        $siteDenominationModel = new SiteDenominationModel();
        $terminalsModel = new TerminalsModel();
        $terminalServicesModel = new TerminalServicesModel();
        $terminal_id = '';
        if(isset($_POST['terminal_id'])) {
            $terminal_id = $_POST['terminal_id'];
        } else {
            $terminal_id = $_POST['StartSessionFormModel']['terminal_id'];
        }
        
        $denomination_type = DENOMINATION_TYPE::INITIAL_DEPOSIT;
        if(isset($_POST['isreload'])) {
            $denomination_type = DENOMINATION_TYPE::RELOAD;
        }
        
        $terminal_data = $terminalsModel->getDataByTerminalId($terminal_id);
        $services = $terminalServicesModel->getCasinoByTerminal($terminal_id);
        $is_vip = $terminal_data['isVIP'];
        $denomination = $siteDenominationModel->getDenominationPerSiteAndType($this->site_id, $denomination_type, $is_vip);
        return array('denomination'=>$denomination,'min_denomination'=>toMoney(SiteDenominationModel::$min),
            'max_denomination'=>toMoney(SiteDenominationModel::$max),'casino'=>$services);
    }  
    
    protected function _getSessionDetail($tid,$site_id) {
        Mirage::loadModels(array('TerminalSessionsModel','TransactionSummaryModel','TransactionDetailsModel'));
        $terminalSessionsModel = new TerminalSessionsModel();
        $transactionSummaryModel = new TransactionSummaryModel();
        $transactionDetailModel = new TransactionDetailsModel();
        
        $terminal_session_data = $terminalSessionsModel->getDataById($tid);
        $last_trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($tid);
        //$last_trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($tid,$site_id);
        $trans_details = $transactionDetailModel->getSessionDetails($last_trans_summary_id);
        return array($terminal_session_data,$trans_details);
    }
    
    protected function _reload($startSessionFormModel,$cid){
        
        Mirage::loadComponents(array('CommonReload','LoyaltyAPIWrapper.class','VoucherManagement',
                                     'CasinoApi','CommonUBReload'));
        
        Mirage::loadModels(array('SitesModel','RefServicesModel','TerminalSessionsModel',
                                 'LoyaltyRequestLogsModel','VMSRequestLogsModel'));
        
        $commonReload = new CommonReload();
        $commonUBReload = new CommonUBReload();
//        $refServicesModel = new RefServicesModel();
        $sitesModel = new SitesModel();
        $loyalty = new LoyaltyAPIWrapper();
        $voucherManagement = new VoucherManagement();
        $casinoAPI = new CasinoApi();
        $terminalSessionsModel = new TerminalSessionsModel();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $vmsrequestlogs = new VMSRequestLogsModel();
        
        $terminal_id = $startSessionFormModel->terminal_id;
        $siteid = $this->site_id;
        $accid = $this->acc_id;
        $vouchercode = '';
        $trackingId = '';
        
        //get user details in terminal sessions 
        $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($terminal_id);
       
        foreach ($casinoUBDetails as $val){
            $casinoUsername = $val['UBServiceLogin'];
            $casinoPassword = $val['UBServicePassword'];
            $mid = $val['MID'];
            $loyaltyCardNo = $val['LoyaltyCardNumber'];
            $casinoUserMode = $val['UserMode'];
            $casinoServiceID = $val['ServiceID'];
        }
        
        //check if voucher
        if(isset($startSessionFormModel->voucher_code) && $startSessionFormModel->voucher_code != '')
        {
                $paymentType = 2;
                $vouchercode = $startSessionFormModel->voucher_code;
                $source = Mirage::app()->param['voucher_source'];
                
                $verifyVoucherResult = $voucherManagement->verifyVoucher($vouchercode, $accid, $source);
                
                //verify if vms API has no error/reachable
                if(is_string($verifyVoucherResult)){
                    $this->status = 2;
                    $message = $verifyVoucherResult;
                    logger($message);
                    $this->throwError($message);
                }

                //check if voucher is not yet claim
                if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] == 0)
                {
                        if(isset($verifyVoucherResult['VerifyVoucher']['Amount']) && $verifyVoucherResult['VerifyVoucher']['Amount'] != '')
                        {
                                $amount = $verifyVoucherResult['VerifyVoucher']['Amount'];
                                $isCreditable = $verifyVoucherResult['VerifyVoucher']['LoyaltyCreditable'];
                                
                                //check if the amount is still in denomination range
                                $denomination = $this->_getDenoCasinoMinMax();
                                $min_deno = $denomination['min_denomination'];
                                $max_deno = $denomination['max_denomination'];
                                
                                if(isset($min_deno) && $amount < toInt($min_deno)){
                                $min_deno = toInt($min_deno);
                                $message = 'Amount should be greater than or equal to '.number_format($min_deno,2);
                                logger($message);
                                $this->throwError($message);
                                } elseif(isset($max_deno) && $amount > toInt($max_deno)) {
                                    $max_deno = toInt($max_deno);
                                    $message = 'Amount should be less than or equal to '.number_format($max_deno,2);
                                    logger($message);
                                    $this->throwError($message);
                                } elseif ($amount % 100 != 0 ) {
                                    $message = 'Amount should be divisible by 100';
                                    logger($message);
                                    $this->throwError($message);  
                                } else {
                                    
                                    $trackingId = "c".$casinoAPI->udate('YmdHisu');
                                    
                                    //checking if casino is terminal based
                                    if($casinoUserMode == 0){
                                        $result = $commonReload->reload(toInt($this->getSiteBalance()),$amount, $paymentType,
                                            $terminal_id,$siteid,$cid,$accid,$loyaltyCardNo,$vouchercode,$trackingId, 
                                            $mid, $casinoUserMode,$casinoUsername,$casinoPassword, $casinoServiceID);
                                    } 
                                    
                                    //checking if casino is user based
                                    if($casinoUserMode == 1){
                                        $result = $commonUBReload->reload(toInt($this->getSiteBalance()),$amount, $paymentType,
                                            $terminal_id,$siteid,$cid,$accid,$loyaltyCardNo,$vouchercode,$trackingId, 
                                            $mid, $casinoUserMode,$casinoUsername,$casinoPassword, $casinoServiceID);
                                    }
                                    
                                    $pos_account_no = $sitesModel->getPosAccountNo($siteid);            

                                    //Insert to loyaltyrequestlogs
                                    $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'R', $terminal_id, $amount, $result["trans_details_id"], $paymentType, $isCreditable);

                                        $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $result['udate'], 2, 'R', $amount,$siteid, $result["trans_details_id"],
                                                                                                                $result['terminal_name'], $isCreditable,$startSessionFormModel->voucher_code, 7,1);
                                        
                                     //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                                    if($isSuccessful){
                                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                                    } else {
                                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                                    }
                                    
                                    //Insert to vmsrequestlogs
                                    $vmsrequestlogsID = $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                    
                                    //use voucher and check result
                                    $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source);
                                    
                                    if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                    {
                                            $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                            
                                            //verify tracking id, if voucher is unclaimed proceed to use voucher
                                            $verifyVoucherResult = $voucherManagement->verifyVoucher($vouchercode, $accid, $source, $trackingId);
                                            if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] == 0){

                                                $trackingId = "c".$casinoAPI->udate('YmdHisu');
                                                
                                                //Insert to vmsrequestlogs
                                                $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                                
                                                $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source);
                                                if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                                {
                                                        $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                                        $this->throwError($useVoucherResult['UseVoucher']['TransMsg']);
                                                } else {
                                                        //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                                        $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                                }

                                            }
                                    } else {
                                        //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                        $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                    }
                                }
                        }
                        else
                        {
                                $message = 'Amount is not set';
                                logger($message);
                                $this->throwError($message);
                        }
                }
                else
                {
                        $message = 'VMS: '.$verifyVoucherResult['VerifyVoucher']['TransMsg'];
                        logger($message);
                        $this->throwError($message);
                }
        }
        else
        {
                $paymentType = 1; 
                $isCreditable = 1;
                
                //check if amount is other denomination
                if($startSessionFormModel->sel_amount == '' || $startSessionFormModel->sel_amount == '--'){
                    $amount = $startSessionFormModel->amount; //amount inputted
                } else {
                    $amount = $startSessionFormModel->sel_amount; //amount selected
                }
                
                 //checking if casino is terminal based
                if($casinoUserMode == 0){
                    $result = $commonReload->reload(toInt($this->getSiteBalance()),$amount, $paymentType,
                        $terminal_id,$siteid,$cid,$accid,$loyaltyCardNo,$vouchercode,$trackingId, 
                        $mid, $casinoUserMode,$casinoUsername,$casinoPassword,$casinoServiceID);
                } 

                //checking if casino is user based
                if($casinoUserMode == 1){
                    $result = $commonUBReload->reload(toInt($this->getSiteBalance()),$amount, $paymentType,
                        $terminal_id,$siteid,$cid,$accid,$loyaltyCardNo,$vouchercode,$trackingId, 
                        $mid, $casinoUserMode,$casinoUsername,$casinoPassword,$casinoServiceID);
                }

                $pos_account_no = $sitesModel->getPosAccountNo($siteid);            

                //Insert to loyaltyrequestlogs
                $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'R', $terminal_id, $amount, $result["trans_details_id"], $paymentType, $isCreditable);
               
                $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $result['udate'], 1, 'R', $amount,$siteid, $result["trans_details_id"],
                                                                                                                $result['terminal_name'], $isCreditable,$startSessionFormModel->voucher_code, 7, 1);
                
                
                //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                   if($isSuccessful){
                       $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                   } else {
                       $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                   }

        }
        
        echo json_encode($result);
        Mirage::app()->end();
    }
    
    /**
     * @param type $startSessionFormModel
     */
    protected function _redeem($startSessionFormModel) {
        Mirage::loadComponents(array('CommonRedeem','LoyaltyAPIWrapper.class','CommonUBRedeem',
                                     'AsynchronousRequest.class'));
        Mirage::loadModels(array('SitesModel','TerminalSessionsModel','TerminalServicesModel',
                                 'RefServicesModel', 'LoyaltyRequestLogsModel',
                                 'SpyderRequestLogsModel','TerminalsModel'));
        
        $commonRedeem = new CommonRedeem();
        $commonUBRedeem = new CommonUBRedeem();
        $terminalSessionsModel = new TerminalSessionsModel();
        $refServicesModel = new RefServicesModel();
        $sitesModel = new SitesModel();
        $loyalty = new LoyaltyAPIWrapper();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $spyderRequestLogsModel = new SpyderRequestLogsModel();
        $terminalsmodel = new TerminalsModel();
        $asynchronousRequest = new AsynchronousRequest();
        
        $paymentType = 1;
        $isCreditable = 1;
        
        $bcf = $this->getSiteBalance();
        
        $service_id = $terminalSessionsModel->getServiceId($startSessionFormModel->terminal_id);
        
        $ref_service = $refServicesModel->getServiceById($service_id);
        
        $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($startSessionFormModel->terminal_id);
        
        $terminalName = $terminalsmodel->getTerminalName($startSessionFormModel->terminal_id);
        
        foreach ($casinoUBDetails as $val){
            $casinoUsername = $val['UBServiceLogin'];
            $casinoPassword = $val['UBServicePassword'];
            $mid = $val['MID'];
            $loyaltyCardNo = $val['LoyaltyCardNumber'];
            $casinoUserMode = $val['UserMode'];
            $casinoServiceID = $val['ServiceID'];
        }
        
        //checking if casino is terminal based
        if($ref_service['UserMode'] == 0){
             $login_acct = $terminalName;
             $terminal_pwd = $terminalsmodel->getTerminalPassword($startSessionFormModel->terminal_id, 
                                $service_id);
             $login_pwd = $terminal_pwd;
             $result = $commonRedeem->redeem($startSessionFormModel->terminal_id, $this->site_id, $bcf, 
                            $service_id, $startSessionFormModel->amount, $paymentType, $this->acc_id, 
                            $loyaltyCardNo, $mid, $casinoUserMode,$casinoUsername,
                            $casinoPassword,$casinoServiceID);
        } 

        //checking if casino is user based
        if($ref_service['UserMode'] == 1){
             $login_acct = $casinoUsername;
             $login_pwd  = $casinoPassword;
             $result = $commonUBRedeem->redeem($startSessionFormModel->terminal_id, $this->site_id, $bcf, 
                            $service_id, $startSessionFormModel->amount, $paymentType, $this->acc_id, 
                            $loyaltyCardNo, $mid, $casinoUserMode,$casinoUsername,
                            $casinoPassword,$casinoServiceID);
        }
        
        
        /**************************** LOYALTY *****************************/
        $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);
        
        //Insert to loyaltyrequestlogs
        $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'W', $startSessionFormModel->terminal_id, $startSessionFormModel->amount, $result["trans_details_id"],$paymentType,$isCreditable);
        
        $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $result['udate'], 1, 'W', $startSessionFormModel->amount,$this->site_id, $result["trans_details_id"],
                                                                                                                $result['terminal_name'], $isCreditable,'', 7, 1);
        
         //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
        if($isSuccessful){
            $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
        } else {
            $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
        }
        
        
        //call Spyder API
        $commandId = 1; //unlock
        $spyder_req_id = $spyderRequestLogsModel->insert($terminalName, $commandId);
        $computerName = substr($terminalName, strlen("ICSA-")); //removes the "icsa-
        
        $params = array('r'=>'spyder/run','TerminalName'=>$computerName,'CommandID'=>$commandId,
                        'UserName'=>$login_acct,'Password'=>$login_pwd,'Type'=> Mirage::app()->param['SAPI_Type'],
                        'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$service_id);
                    
        $asynchronousRequest->curl_request_async(Mirage::app()->param['Asynchronous_URI'], $params);
        
        echo json_encode($result);
        Mirage::app()->end();        
    }
    
    /**
     * @param type $startSessionFormModel
     * @param type $return
     * @return type
     */
    protected function _startSession($startSessionFormModel,$return=false) {
        Mirage::loadComponents(array('CommonStartSession','LoyaltyAPIWrapper.class','VoucherManagement',
                                     'CasinoApi','CommonUBStartSession','AsynchronousRequest.class'));
        
        Mirage::loadModels(array('TerminalSessionsModel','RefServicesModel','SitesModel', 'LoyaltyRequestLogsModel', 
                                    'VMSRequestLogsModel','TerminalsModel','SpyderRequestLogsModel'));
        
        $terminalSessionsModel = new TerminalSessionsModel();
        $refService = new RefServicesModel();
        $commonStartSession = new CommonStartSession();
        $commonUBStartSession = new CommonUBStartSession();
        $sitesModel = new SitesModel();
        $voucherManagement = new VoucherManagement();
        $casinoAPI = new CasinoApi();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $vmsrequestlogs = new VMSRequestLogsModel();
        $terminalsmodel = new TerminalsModel();
        $asynchronousRequest = new AsynchronousRequest();
        $spyderReqLogsModel = new SpyderRequestLogsModel();
        
        $terminal_id = $startSessionFormModel->terminal_id;
        $siteid = $this->site_id;
        $accid = $this->acc_id;
        $vouchercode = '';
        $trackingId = '';
        $ref_service = $refService->getServiceById($startSessionFormModel->casino);        
        $terminalname = $terminalsmodel->getTerminalName($terminal_id);
       
       $isVIP = '';
       if(preg_match("/vip$/i", $terminalname, $results)){
           $isVIP = "1";
       } else {
            $isVIP = $_POST['isvip'];
       }
         
        //check if voucher
        if(isset($startSessionFormModel->voucher_code) && $startSessionFormModel->voucher_code !='')
        {
                    $paymentType = 2;
                    $vouchercode = $startSessionFormModel->voucher_code;
                    $source = Mirage::app()->param['voucher_source'];
                    $trackingId = '';
                    $verifyVoucherResult = $voucherManagement->verifyVoucher($vouchercode, $accid, $source, $trackingId);
                   
                    //verify if vms API has no error/reachable
                    if(is_string($verifyVoucherResult)){
                        $message = $verifyVoucherResult;
                        logger($message);
                        $this->throwError($message);
                    }

                    //check if voucher is not yet claimed
                    if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] == 0)
                    {
                        if(isset($verifyVoucherResult['VerifyVoucher']['Amount']) && $verifyVoucherResult['VerifyVoucher']['Amount'] != '')
                        {
                            $isCreditable = $verifyVoucherResult['VerifyVoucher']['LoyaltyCreditable'];
                            $amount = $verifyVoucherResult['VerifyVoucher']['Amount'];
                            
                            $denomination = $this->_getDenoCasinoMinMax();
                            $min_deno = $denomination['min_denomination'];
                            $max_deno = $denomination['max_denomination'];

                            //check if the amount of initial deposit is in denomination range and divisible by 100
                            if(isset($min_deno) && $amount < toInt($min_deno)){
                                $min_deno = toInt($min_deno);
                                $message = 'Amount should be greater than or equal to '.number_format($min_deno,2);
                                logger($message);
                                $this->throwError($message);
                            } elseif(isset($max_deno) && $amount > toInt($max_deno)) {
                                    $max_deno = toInt($max_deno);
                                    $message = 'Amount should be less than or equal to '.number_format($max_deno,2);
                                    logger($message);
                                    $this->throwError($message);       
                            } elseif ($amount % 100 != 0 ) {
                                    $message = 'Amount should be divisible by 100';
                                    logger($message);
                                    $this->throwError($message);  
                            } else {
                                
                                    $trackingId = "c".$casinoAPI->udate('YmdHisu');

                                    list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count) = 
                                            $this->getCardInfo($startSessionFormModel->loyalty_card);
                                    
                                    $casinoUsername = '';
                                    $casinoPassword = '';
                                    $casinoHashedPassword = '';
                                    $casinoServiceID = '';
                                    $casinoStatus = '';

                                    for($ctr = 0; $ctr < $casinoarray_count; $ctr++)
                                    {
                                            if($ref_service['ServiceID'] == $casinos[$ctr]['ServiceID'] ){
                                                $casinoUsername = $casinos[$ctr]['ServiceUsername'];
                                                $casinoPassword = $casinos[$ctr]['ServicePassword'];
                                                $casinoHashedPassword = $casinos[$ctr]['HashedServicePassword'];
                                                $casinoServiceID = $casinos[$ctr]['ServiceID'];
                                                $casinoStatus = $casinos[$ctr]['Status'];
                                                $casinoIsVIP = $casinos[$ctr]['isVIP'];
                                            }
                                    }

                                    //checking if casino is terminal based
                                    if($ref_service['UserMode'] == 0){
                                        $login_acct = $terminalname;
                                        $terminal_pwd = $terminalsmodel->getTerminalPassword($terminal_id, $startSessionFormModel->casino);
                                        $login_pwd = $terminal_pwd['ServicePassword'];
                                        $result = $commonStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                                           toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                                           $startSessionFormModel->voucher_code, $trackingId, $casinoUsername,
                                                           $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode']);
                                    } 

                                    //checking if casino is user based
                                    if($ref_service['UserMode'] == 1)
                                    {
                                        $login_acct = $casinoUsername;
                                        $login_pwd = $casinoPassword;
                                        //check if isVIP of chosen casino is match with the isVIP parameter thrown by loyalty getCardInfo function.
                                        if($casinoIsVIP != $isVIP) {
                                            $message = 'Please choose the appropriate regular/vip terminal classification for this card.';
                                            logger($message);
                                            $this->throwError($message);
                                        }
                                        
                                        $result = $commonUBStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                                           toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                                           $startSessionFormModel->voucher_code,$trackingId, $casinoUsername,
                                                           $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode']);
                                    }

                                    $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);
                                    
                                    /************************ FOR LOYALTY *************************/
                                    
                                    //Insert to loyaltyrequestlogs
                                    $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminal_id, $amount, $result["trans_details_id"], $paymentType, $isCreditable);
                                    
                                    if($is_loyalty) {
                                        $isSuccessful = $loyalty->processPoints($startSessionFormModel->loyalty_card, $result['udate'], 2, 'D', $amount,$siteid, $result["trans_details_id"],
                                                                                                                $result['terminal_name'], $isCreditable,$startSessionFormModel->voucher_code, 7, 1);
                                    }
                                                                
                                     //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                                    if($isSuccessful){
                                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                                    } else {
                                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                                    }
                                    
                                    //Insert to vmsrequestlogs
                                    $vmsrequestlogsID = $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                    
                                    //use voucher, and check result
                                    $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source);

                                    if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                    {
                                        $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                        
                                        //verify tracking id, if voucher is unclaimed proceed to use voucher
                                        $verifyVoucherResult = $voucherManagement->verifyVoucher($vouchercode, $accid, $source, $trackingId);
                                        if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] == 0){

                                            $trackingId = "c".$casinoAPI->udate('YmdHisu');
                                            
                                            //Insert to vmsrequestlogs
                                            $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                            
                                            $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source);
                                            if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                            {
                                                    $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                                    $this->throwError($useVoucherResult['UseVoucher']['TransMsg']);
                                            } else {
                                                    //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                                    $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                            }
                                        } 
                                    } else {
                                        //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                        $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                    }
                                    
                                }
                        } 
                        else 
                        {
                                $message = 'Amount is not set';
                                logger($message);
                                $this->throwError($message);
                        }
                    } 
                    else 
                    {
                        $message = 'VMS: '.$verifyVoucherResult['VerifyVoucher']['TransMsg'];
                        logger($message);
                        $this->throwError($message);
                    }
        } 
        else 
        {
                    $paymentType = 1;
                    $isCreditable = 1;
                    
                    //check if amount is other denomination
                    if($startSessionFormModel->sel_amount == '' || $startSessionFormModel->sel_amount == '--'){
                        $amount = $startSessionFormModel->amount; //amount inputted
                    } else {
                        $amount = $startSessionFormModel->sel_amount; //amount selected
                    }
                    
                            
                    list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count) = 
                            $this->getCardInfo($startSessionFormModel->loyalty_card);

                    $casinoUsername = '';
                    $casinoPassword = '';
                    $casinoHashedPassword = '';
                    $casinoServiceID = '';
                    $casinoStatus = '';
                    
                    for($ctr = 0; $ctr < $casinoarray_count; $ctr++)
                    {
                            if($ref_service['ServiceID'] == $casinos[$ctr]['ServiceID'] ){
                                $casinoUsername = $casinos[$ctr]['ServiceUsername'];
                                $casinoPassword = $casinos[$ctr]['ServicePassword'];
                                $casinoHashedPassword = $casinos[$ctr]['HashedServicePassword'];
                                $casinoServiceID = $casinos[$ctr]['ServiceID'];
                                $casinoStatus = $casinos[$ctr]['Status'];
                                $casinoIsVIP = $casinos[$ctr]['isVIP'];
                            }
                    }
                    
                    /**
                     * @todo add checking here if casino status is active
                     */
                    
                    //checking if casino is terminal based
                    if($ref_service['UserMode'] == 0){
                        $login_acct = $terminalname;
                        $terminal_pwd = $terminalsmodel->getTerminalPassword($terminal_id, $startSessionFormModel->casino);
                        $login_pwd = $terminal_pwd['ServicePassword'];
                        $result = $commonStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                           toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                           $vouchercode, $trackingId, $casinoUsername,
                                           $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode']);
                    } 
                    
                    //checking if casino is user based
                    if($ref_service['UserMode'] == 1)
                    {
                        $login_acct = $casinoUsername;
                        $login_pwd = $casinoPassword;
                        //check if isVIP of chosen casino is match with the isVIP parameter thrown by loyalty getCardInfo function.
                        if($casinoIsVIP != $isVIP) {
                            $message = 'Please choose the appropriate regular/vip terminal classification for this card.';
                            logger($message);
                            $this->throwError($message);
                        }
                        
                        $result = $commonUBStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                           toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                           $vouchercode,$trackingId, $casinoUsername,
                                           $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode']);
                    }

                    $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);

                    /************************ FOR LOYALTY *************************/
                    
                    //Insert to loyaltyrequestlogs
                    $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminal_id, $amount, $result["trans_details_id"], $paymentType,$isCreditable);
                    
                    if($is_loyalty) {
                        $isSuccessful = $loyalty->processPoints($startSessionFormModel->loyalty_card, $result['udate'], 1, 'D', $amount,$siteid, $result["trans_details_id"],
                                                                                                $result['terminal_name'], $isCreditable,$startSessionFormModel->voucher_code, 7, 1);
                    }
                    
                     //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                    if($isSuccessful){
                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                    } else {
                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                    }
                    
        }
        
        $res = $terminalSessionsModel->getDataById($terminal_id);
        $asof = ' as of '.date('m/d/Y H:i:s',strtotime($res['LastTransactionDate']));
        $time_playing = getTimePlaying($res['minutes']);
        $result = array_merge($result,array('id'=>$terminal_id,'casino'=>$ref_service['Code'],
            'service_id'=>$startSessionFormModel->casino,'time_playing'=>$time_playing,
            'asof'=>$asof));
        
        if($return) {
            return $result;
        }
        
        //call Spyder API
        $commandId = 0; //unlock
        $spyder_req_id = $spyderReqLogsModel->insert($terminalname, $commandId);
        $computerName = substr($terminalname, strlen("ICSA-")); //removes the "icsa-
        
        $params = array('r'=>'spyder/run','TerminalName'=>$computerName,'CommandID'=>$commandId,
                        'UserName'=>$login_acct,'Password'=>$login_pwd,'Type'=> Mirage::app()->param['SAPI_Type'],
                        'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$startSessionFormModel->casino);
                    
        $asynchronousRequest->curl_request_async(Mirage::app()->param['Asynchronous_URI'], $params);
        
        echo json_encode($result);
        Mirage::app()->end();
    }  
    
    public function getRedeemableAmountAndDetailsAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        
        Mirage::loadComponents('CasinoApi');
        Mirage::loadModels(array('TerminalSessionsModel','TransactionSummaryModel','TransactionDetailsModel','RefServicesModel'));
        
        //$transactionSummaryModel = new TransactionSummaryModel();
        $transactionDetailModel = new TransactionDetailsModel();
        $refServicesModel = new RefServicesModel();
        $casinoApi = new CasinoApi();
        $terminalSessionsModel = new TerminalSessionsModel();
        
        $site_id = $this->site_id;
        $terminal_id = $_POST['StartSessionFormModel']['terminal_id'];
        //$last_trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($terminal_id,$this->site_id);
        
        $service_id = $terminalSessionsModel->getServiceId($terminal_id);
        
        $last_trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        
        $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($terminal_id);
       
        foreach ($casinoUBDetails as $val){
            $casinoUsername = $val['UBServiceLogin'];
            $casinoPassword = $val['UBServicePassword'];
            $mid = $val['MID'];
            $loyaltyCardNo = $val['LoyaltyCardNumber'];
            $casinoUserMode = $val['UserMode'];
            $casinoServiceID = $val['ServiceID'];
        }
        
        if($casinoUserMode == 0)
            list($terminal_balance) = $casinoApi->getBalance($terminal_id, $site_id, 'W', $service_id, $this->acc_id);
        
        if($casinoUserMode == 1)
            list ($terminal_balance) = $casinoApi->getBalanceUB($terminal_id, $site_id, 'W', 
                        $casinoServiceID, $this->acc_id, $casinoUsername, $casinoPassword);
        
        $json = array('amount'=>toMoney($terminal_balance));
        
        if(isset($_POST['showdetails'])) {
            $trans_details = $transactionDetailModel->getSessionDetails($last_trans_summary_id);    
            $terminal_session_data = $terminalSessionsModel->getDataById($terminal_id);
            $terminal_session_data['DateStarted'] = date('Y-m-d h:i:s A',strtotime($terminal_session_data['DateStarted']));
            $json = array_merge ($json,array('trans_details'=>$trans_details,'terminal_session_data'=>$terminal_session_data));
        } else {
            $casino = $refServicesModel->getAliasById($service_id);
            $total_detail = $transactionDetailModel->getTotalDetails($last_trans_summary_id);
            $json = array_merge($json,array('total_detail'=>$total_detail,'casino'=>$casino));
        }        
        
        if(isset($_POST['redeem_click'])) {
            return $json;
        } else {
            echo json_encode($json);
            Mirage::app()->end();
        }
    }
    
    protected function throwError($message) {
        header('HTTP/1.0 404 Not Found');
        echo $message;
        Mirage::app()->end();
    }
    
    /**
     * Get card info and validate its status
     * @param type $barCode 
     */
    protected function getCardInfo($barCode){
        $is_loyalty = false;
        $loyalty = new LoyaltyAPIWrapper();
        $card_number = '';

        //check if site group is BGI ; // change condition from EQ t 1 to NEQ TO 1
        // to allow all sites to access loyalty 1.6 7/2/2012
        if($_SESSION['isbgi'] <> 1)
        {
            if($barCode != '') {
                
                $result = $loyalty->getCardInfo($barCode, 1);
                $obj_result = json_decode($result);
                
                if($obj_result->CardInfo->CardNumber == null) {
                    header('HTTP/1.0 404 Not Found');
                    echo 'Can\'t get card info';
                    Mirage::app()->end();
                }
                
                //if playername and if SiteGroup is BGI
                elseif($obj_result->CardInfo->MemberName == null){
                    Mirage::loadLibraries('LoyaltyScripts');
                    header('HTTP/1.0 401 Unauthorized');
                    Mirage::app()->end();
                }
                
                //if player status is deactivated and Site Group is BGI
                elseif($obj_result->CardInfo->StatusCode != 1 && $obj_result->CardInfo->StatusCode != 5 )
                {
                    header('HTTP/1.0 401 Unauthorized');
                    echo 'Card is inactive or deactivated';
                    Mirage::app()->end();
                }
                
                else {
                    $is_loyalty = true;
                    $card_number = $obj_result->CardInfo->CardNumber;
                }
            }
            else
            {
                header('HTTP/1.0 404 Not Found');
                echo 'Please enter your loyalty card number';
                Mirage::app()->end();
            }
        }
        else
        {
            if($barCode != '') {
                
                $result = $loyalty->getCardInfo($barCode,1);
                $obj_result = json_decode($result);
                
                if($obj_result->CardInfo->CardNumber == null) {
                    header('HTTP/1.0 404 Not Found');
                    echo 'Can\'t get card info';
                    Mirage::app()->end();
                }
                else {
                    $is_loyalty = true;
                }
            }
        }

        $casinoarray_count = count($obj_result->CardInfo->CasinoArray);
        $casinos = array();
        if($casinoarray_count != 0)
            for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {
                $casinos[$ctr] = array('ServiceUsername' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                                                                    'ServicePassword' => $obj_result->CardInfo->CasinoArray[$ctr]->ServicePassword,
                                                                    'HashedServicePassword' => $obj_result->CardInfo->CasinoArray[$ctr]->HashedServicePassword,
                                                                    'ServiceID' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID,
                                                                    'UserMode' => $obj_result->CardInfo->CasinoArray[$ctr]->UserMode,
                                                                    'isVIP' => $obj_result->CardInfo->CasinoArray[$ctr]->isVIP,
                                                                    'Status' => $obj_result->CardInfo->CasinoArray[$ctr]->Status );
            }
        
            $mid = $obj_result->CardInfo->MID;
            
        return array($is_loyalty, $card_number, $loyalty, $casinos, $mid, $casinoarray_count);
    }
}

/**
 * Description: Minimum and Maximum denomination will depend if initial deposit or reload.
 *  This class will act as enum
 */
class DENOMINATION_TYPE {
    const INITIAL_DEPOSIT = 1;
    const RELOAD = 2;
}

/**
 * Description: Helper class for creating json
 */
class JsonTerminal {
    public $terminals = array();
    public $services = array();
    public $refservices = array();
    public $current_page = null;
    public $server_date = null;
}
