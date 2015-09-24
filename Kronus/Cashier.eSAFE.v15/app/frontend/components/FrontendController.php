<?php
Mirage::loadComponents(array('Menu'));
Mirage::loadLibraries('util');
Mirage::loadModels('SiteBalanceModel');
@session_cache_expire(0);
if ( !isset( $_SESSION ) )
{
@session_start();
}
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
    
    protected $acc_type = null;
    
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
        
        Mirage::loadModels(array('AccountSessionsModel','SitesModel','SiteAccountsModel', 'AccountsModel'));             
        $accountSessionsModel = new AccountSessionsModel();
        $sitesModel = new SitesModel();  
        $siteAccountsModel = new SiteAccountsModel();
        $accountsModel = new AccountsModel();
        
        $detail = $sitesModel->getPosAccntAndAccntName($_SESSION['AccountSiteID']);
        $_SESSION['pos_account'] = $detail['POSAccountNo'];
        $_SESSION['account_name'] = $detail['Name'];
        
        $session_id = $accountSessionsModel->getSessionId($_SESSION['userid']);
        if($session_id != $_SESSION['sessionID']) {
            Mirage::app()->error401();
        }
        
        if(!isset($_SESSION['site_code'])) {
            $_SESSION['site_code'] = $siteAccountsModel->getSiteCodeByAccId($_SESSION['accID']);            
        }
        
        //session variable for value of Spyder status if it is ON / OFF
        $_SESSION['spyder_enabled'] = $sitesModel->getSpyderStatus($_SESSION['AccountSiteID']);
        
        $bgiOwner = Mirage::app()->param['BGI_ownerID'];    
        $isBGI = $siteAccountsModel->getSiteGroup($_SESSION['AccountSiteID'], $bgiOwner);
        $_SESSION['isbgi'] = $isBGI['ctrbgi']; //session variable for bgi and non-bgi
        
        if(Mirage::app()->getModuleName() != 'monitoring' && Mirage::app()->getControllerName() != 'Refresh' && in_array($_SESSION['acctype'], Mirage::app()->param['standalone_allowed_type'])) {
            $this->redirect($this->createUrl('monitoring/overview'));
        }
        
        $menu = $sitesModel->getMenu($_SESSION['AccountSiteID']);
        $tm = $menu['TMTab'];
        $srr = $menu['SRRTab'];
        
        if($srr == 1 && $tm == 1){
            $this->layout = 'layout/main';
        }
        else if($srr == 0 && $tm == 0){
            $this->layout = 'layout/main2';
        }
        else if($srr == 1 && $tm == 0){
            $this->layout = 'layout/main3';
        }
        else if($srr == 0 && $tm == 1){
            $this->layout = 'layout/main4';
        }
        
        $this->site_id = $_SESSION['AccountSiteID'];
        $this->site_code = $_SESSION['site_code']; 
        $this->acc_id = $_SESSION['accID'];
        $this->len = strlen($this->site_code) + 1;
        //$this->acc_type = $accountsModel->getAccountTypeIDByAID($this->acc_id);
        
        if(!$this->_is_terminal_monitoring) {
            unset($_SESSION['current_page']);
            unset($_SESSION['page']);
        }
    }
    
    /***************************** HELPER *************************************/
    protected function _getDenoCasinoMinMax($denomination_type) {
        Mirage::loadModels(array('SiteDenominationModel','TerminalsModel','TerminalServicesModel'));
        $siteDenominationModel = new SiteDenominationModel();
        $terminalsModel = new TerminalsModel();
        $terminalServicesModel = new TerminalServicesModel();
        $terminal_id = '';
        if(isset($_POST['terminal_id'])) {
            $terminal_id = $_POST['terminal_id'];
        } else {
            if(isset($_POST['StartSessionFormModel']['terminal_id'])){
                $terminal_id = $_POST['StartSessionFormModel']['terminal_id'];
            }
            else{
                $terminal_id = 0;
            }
        }
        
//        $denomination_type = DENOMINATION_TYPE::INITIAL_DEPOSIT;
//        if(isset($_POST['isreload'])) {
//            $denomination_type = DENOMINATION_TYPE::RELOAD;
//        }
        
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
        $refServicesModel = new RefServicesModel();
        $sitesModel = new SitesModel();
        $loyalty = new LoyaltyAPIWrapper();
        $voucherManagement = new VoucherManagement();
        $casinoAPI = new CasinoApi();
        $terminalSessionsModel = new TerminalSessionsModel();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $vmsrequestlogs = new VMSRequestLogsModel();
        $terminalsmodel = new TerminalsModel();
        
        $terminal_id = $startSessionFormModel->terminal_id;
        $siteid = $this->site_id;
        $accid = $this->acc_id;
        $vouchercode = '';
        $trackingId = '';
        $traceNumber = $startSessionFormModel->trace_number;
        $referenceNumber = $startSessionFormModel->reference_number;
        
        $terminaltype = $terminalsmodel->checkTerminalType($terminal_id);
        
        if($terminaltype == 1){
            $message = 'Reloads are allowed only through Genesis Terminal.';
            logger($message);
            $this->throwError($message);
        }
        
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
        
        $ref_service = $refServicesModel->getServiceById($casinoServiceID);
        
        if($ref_service['Code'] == 'MM'){
            list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count, $isewallet) = 
                $this->getCardInfo($loyaltyCardNo, $this->site_id, 2);
            if($isewallet > 0 && ($casinoServiceID == 19 || $casinoServiceID == 20)){
                $message = "Reload failed. Please reload in the e-SAFE Load Tab.";
                logger($message);
                $this->throwError($message);
            }
        }
        
        //check if voucher
        if(isset($startSessionFormModel->voucher_code) && $startSessionFormModel->voucher_code != '')
        {
                $paymentType = 2; //payment type is coupon method
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
                                $denominationtype = DENOMINATION_TYPE::RELOAD;
                                //check if the amount is still in denomination range
                                $denomination = $this->_getDenoCasinoMinMax($denominationtype);
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
                                        
                                        //Set locator name based on Site Classification
                                        if($casinoServiceID == 20){ //RTG V15
                                            $siteclassification = $sitesModel->getSiteClassification($siteid);
                                            if($siteclassification == 1){ //1 - Non Platinum, 2 - Platinum
                                                $locatorname = Mirage::app()->param['SkinNameNonPlatinum'];
                                            } else {
                                                $locatorname = Mirage::app()->param['SkinNamePlatinum'];
                                            }
                                        } else { $locatorname = ''; }
                                        
                                        $result = $commonUBReload->reload(toInt($this->getSiteBalance()),$amount, $paymentType,
                                            $terminal_id,$siteid,$cid,$accid,$loyaltyCardNo,$vouchercode,$trackingId, 
                                            $mid, $casinoUserMode,$casinoUsername,$casinoPassword, $casinoServiceID,'','',$locatorname);
                                    }
                                    
                                    $pos_account_no = $sitesModel->getPosAccountNo($siteid);            

//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>
       
                                            /************************ FOR LOYALTY *************************/
                                           
                                        //Check if Loyalty
                                         $isLoyalty = Mirage::app()->param['Isloyaltypoints'];

                                        //Loyalty points
                                        if ($isLoyalty == 1) {
                                            
                                            $loyalty = new LoyaltyAPIWrapper();
                                            
                                            //Insert to loyaltyrequestlogs
                                            $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminal_id, toInt($amount), $result["trans_details_id"], $paymentType, 1);
                                            $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
                                            $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $transdate, $paymentType, 'D', toInt($amount),$siteid, $result["trans_details_id"],
                                                                          $result['terminal_name'], 1, $vouchercode, $cid  ,  $isCreditable);
                                            
                                             //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                                            if($isSuccessful){
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                                            } else {
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                                            }
                                        }
                                        else{
                                            
                                            $comppointslogs = new CompPointsLogsModel();
                                            $comppoints = new ComppointsAPIWrapper();

                                            $usermode = $comppointslogs->checkUserMode($cid);
                                            if ($usermode == 0) {
                                                //Insert to comppointslogs
                                                $comppointslogs->insert($mid, $loyaltyCardNo, $terminal_id, $siteid,$cid,  toInt($amount), $transdate, 'D');

                                                //Insert to ewallettrans     
                                                $comppoints->processPoints($loyaltyCardNo, $transdate, $paymentType, 'R', 
                                                                       toInt($amount), $siteid,$result["trans_details_id"], $result['terminal_name'], 1, $vouchercode, $cid , $isCreditable);
                                            } 
                                        }
                
//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>                                         

                                    //Insert to vmsrequestlogs
                                    $vmsrequestlogsID = $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                    
                                    //use voucher and check result
                                    $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source, $siteid, $mid);
                                   
                                    //If first try of use voucher fails, retry
                                    if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                    {
                                            $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                            
                                            //verify tracking id, if tracking id is not found and voucher is unclaimed proceed to use voucher
                                            $verifyVoucherResult = $voucherManagement->verifyVoucher('', $accid, $source, $trackingId);
                                             
                                            //check if tracking result is not found that means transaction was not successful on the first try
                                            if(!isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] != 0){

                                                $trackingId = "c".$casinoAPI->udate('YmdHisu');
                                                
                                                //Insert to vmsrequestlogs
                                                $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                                
                                                $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source, $siteid, $mid);
                                                
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
                $paymentType = 1; //payment type is cash method
                $isCreditable = 1;
                
                //check if amount is other denomination
                if($startSessionFormModel->sel_amount == '' || $startSessionFormModel->sel_amount == '--'){
                    $amount = $startSessionFormModel->amount; //amount inputted
                } else {
                    $amount = $startSessionFormModel->sel_amount; //amount selected
                }
                
                //check if amount is bancnet
                if($startSessionFormModel->sel_amount=='bancnet'){
                    if($traceNumber==''){
                        $message = 'Trace number cannot be empty.';
                        logger($message);
                        $this->throwError($message);
                    }

                    if($referenceNumber==''){
                        $message = 'Reference Number cannot be empty.';
                        logger($message);
                        $this->throwError($message);
                    }
                    $amount = $startSessionFormModel->amount;

                }else if(!empty($traceNumber) && !empty($referenceNumber)){
                    $amount = $startSessionFormModel->amount;
                }
                
                 //checking if casino is terminal based
                if($casinoUserMode == 0){
                    
                    $result = $commonReload->reload(toInt($this->getSiteBalance()),$amount, $paymentType,
                            $terminal_id,$siteid,$cid,$accid,$loyaltyCardNo,$vouchercode,$trackingId, 
                            $mid, $casinoUserMode,$traceNumber, $referenceNumber);
                    
                } 

                //checking if casino is user based
                if($casinoUserMode == 1){
                    
                    //Set locator name based on Site Classification
                    if($casinoServiceID == 20){ //RTG V15
                        $siteclassification = $sitesModel->getSiteClassification($siteid);
                        if($siteclassification == 1){ //1 - Non Platinum, 2 - Platinum
                            $locatorname = Mirage::app()->param['SkinNameNonPlatinum'];
                        } else {
                            $locatorname = Mirage::app()->param['SkinNamePlatinum'];
                        }
                    } else { $locatorname = ''; }
                    
                    $result = $commonUBReload->reload(toInt($this->getSiteBalance()),$amount, $paymentType,
                        $terminal_id,$siteid,$cid,$accid,$loyaltyCardNo,$vouchercode,$trackingId, 
                        $mid, $casinoUserMode,$casinoUsername,$casinoPassword,$casinoServiceID, $traceNumber, $referenceNumber, $locatorname);
                }

                $pos_account_no = $sitesModel->getPosAccountNo($siteid);            

//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>
       
                                            /************************ FOR LOYALTY *************************/
                                           
                                        //Check if Loyalty
                                         $isLoyalty = Mirage::app()->param['Isloyaltypoints'];

                                        //Loyalty points
                                        if ($isLoyalty == 1) {
                                            
                                            $loyalty = new LoyaltyAPIWrapper();
                                            
                                            //Insert to loyaltyrequestlogs
                                            $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminal_id, toInt($amount), $result["trans_details_id"], $paymentType, 1);
                                            $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
                                            $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $transdate, $paymentType, 'D', toInt($amount) ,$siteid, $result["trans_details_id"],
                                                                          $result['terminal_name'], 1, $vouchercode, $cid  ,  $isCreditable);
                                            
                                             //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                                            if($isSuccessful){
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                                            } else {
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                                            }
                                        }
                                        else{
                                            
                                            $comppointslogs = new CompPointsLogsModel();
                                            $comppoints = new ComppointsAPIWrapper();

                                            $usermode = $comppointslogs->checkUserMode($cid);
                                            if ($usermode == 0) {
                                                //Insert to comppointslogs
                                                $comppointslogs->insert($mid, $loyaltyCardNo, $terminal_id, $siteid,$cid,  toInt($amount), $transdate, 'D');

                                                //Insert to ewallettrans     
                                                $comppoints->processPoints($loyaltyCardNo, $transdate, $paymentType, 'R', 
                                                                        toInt($amount), $siteid,$result["trans_details_id"], $result['terminal_name'], 1, $vouchercode, $cid , $isCreditable);
                                            } 
                                        }
                
//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>                                      
                                                     

        }
        
        echo json_encode($result);
        Mirage::app()->end();
    }
    
    
    protected function _reloadforcet($startSessionFormModel,$cid){
        
        Mirage::loadComponents(array('CommonReload','LoyaltyAPIWrapper.class','VoucherManagement',
                                     'CasinoApi','PCWSAPI.class'));
        
        Mirage::loadModels(array('SitesModel','RefServicesModel','TerminalSessionsModel',
                                 'LoyaltyRequestLogsModel','VMSRequestLogsModel'));

        $commonReload = new CommonReload();
//        $refServicesModel = new RefServicesModel();
        $sitesModel = new SitesModel();
        $loyalty = new LoyaltyAPIWrapper();
        $pcwsapi = new PCWSAPI();
        $voucherManagement = new VoucherManagement();
        $casinoAPI = new CasinoApi();
        $terminalSessionsModel = new TerminalSessionsModel();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $vmsrequestlogs = new VMSRequestLogsModel();
        $terminalsmodel = new TerminalsModel();
        
        $terminal_id = ($startSessionFormModel->terminal_id == '') ? 0:$startSessionFormModel->terminal_id;
        $siteid = $this->site_id;
        $accid = $this->acc_id;
        $vouchercode = '';
        $trackingId = '';
        $loyaltyCardNo = $startSessionFormModel->loyalty_card;
        $tracenumber = $startSessionFormModel->tracenumber;
        $referencenumber = $startSessionFormModel->referencenumber;
        
        list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count, $isewallet) = 
                            $this->getCardInfo($startSessionFormModel->loyalty_card, $this->site_id, 3);

        $casinoUsername = '';
        $casinoPassword = '';
        $casinoHashedPassword = '';
        $casinoServiceID = '';
        $casinoStatus = '';
        
        //verify if card is ewallet
        if($isewallet < 1){
            $message = "Load failed. Player's account must be e-SAFE.";
            logger($message);
            
            $result = array('message'=>$message);
            echo json_encode($result);
            Mirage::app()->end(); 
        }
        
        $casinos = $this->loopAndFindCasinoService($casinos, 'ServiceID', $cid);

        $casinoarray_count = count($casinos);

        for($ctr = 0; $ctr < $casinoarray_count; $ctr++)
        {
                if($cid == $casinos[$ctr]['ServiceID'] ){
                    $casinoUsername = $casinos[$ctr]['ServiceUsername'];
                    $casinoPassword = $casinos[$ctr]['ServicePassword'];
                    $casinoHashedPassword = $casinos[$ctr]['HashedServicePassword'];
                    $casinoServiceID = $casinos[$ctr]['ServiceID'];
                    $casinoStatus = $casinos[$ctr]['Status'];
                    $casinoUserMode = $casinos[$ctr]['UserMode'];
                    $casinoIsVIP = $casinos[$ctr]['isVIP'];
                }
        }
        
        //check if voucher
        if($startSessionFormModel->voucher_code != '')
        {
                $paymentType = 2; //payment type is coupon method
                $vouchercode = $startSessionFormModel->voucher_code;
                $source = Mirage::app()->param['voucher_source'];
                
                $verifyVoucherResult = $voucherManagement->verifyVoucher($vouchercode, $accid, $source);
                
                //verify if vms API has no error/reachable
                if(is_string($verifyVoucherResult)){
                    $this->status = 2;
                    $message = $verifyVoucherResult;
                    logger($message);
                    
                    $result = array('message'=>$message);
                    echo json_encode($result);
                    Mirage::app()->end(); 
                }

                //check if voucher is not yet claim
                if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] == 0)
                {
                        if(isset($verifyVoucherResult['VerifyVoucher']['Amount']) && $verifyVoucherResult['VerifyVoucher']['Amount'] != '')
                        {
                                $amount = $verifyVoucherResult['VerifyVoucher']['Amount'];
                                $isCreditable = $verifyVoucherResult['VerifyVoucher']['LoyaltyCreditable'];
                                $denominationtype = DENOMINATION_TYPE::RELOAD;
                                //check if the amount is still in denomination range
                                $denomination = $this->_getDenoCasinoMinMax($denominationtype);
                                $min_deno = $denomination['min_denomination'];
                                $max_deno = $denomination['max_denomination'];
                                
                                if(isset($min_deno) && $amount < toInt($min_deno)){
                                $min_deno = toInt($min_deno);
                                $message = 'Amount should be greater than or equal to '.number_format($min_deno,2);
                                logger($message);
                                
                                $result = array('message'=>$message);
                                echo json_encode($result);
                                Mirage::app()->end(); 
                                } elseif(isset($max_deno) && $amount > toInt($max_deno)) {
                                    $max_deno = toInt($max_deno);
                                    $message = 'Amount should be less than or equal to '.number_format($max_deno,2);
                                    logger($message);
                                    $this->throwError($message);
                                } elseif ($amount % 100 != 0 ) {
                                    $message = 'Amount should be divisible by 100';
                                    logger($message);
                                    
                                    $result = array('message'=>$message);
                                    echo json_encode($result);
                                    Mirage::app()->end(); 
                                } else {
                                    $trackingId = "c".$casinoAPI->udate('YmdHisu');
                                        $systemusername = Mirage::app()->param['pcwssysusername'];
                                        $result = $pcwsapi->Deposit($loyaltyCardNo, $cid, $paymentType, $amount, $siteid, $accid, $systemusername, $tracenumber, $referencenumber, $vouchercode,$trackingId);
                                    
                                    
//                                    $pos_account_no = $sitesModel->getPosAccountNo($siteid);            
//
//                                    //Insert to loyaltyrequestlogs
//                                    $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'R', $terminal_id, $amount, $result["trans_details_id"], $paymentType, $isCreditable);
//                                    $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
//                                    $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $transdate, 2, 'R', $amount,$siteid, $result["trans_details_id"],
//                                                                                                                $result['terminal_name'], $isCreditable,$startSessionFormModel->voucher_code, 7,1);
//                                        
//                                     //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
//                                    if($isSuccessful){
//                                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
//                                    } else {
//                                        $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
//                                    }
                                    
                                    //Insert to vmsrequestlogs
                                    $vmsrequestlogsID = $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                    
                                    //use voucher and check result
                                    $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source, $siteid, $mid);
                                   
                                    //If first try of use voucher fails, retry
                                    if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                    {
                                            $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                            
                                            //verify tracking id, if tracking id is not found and voucher is unclaimed proceed to use voucher
                                            $verifyVoucherResult = $voucherManagement->verifyVoucher('', $accid, $source, $trackingId);
                                             
                                            //check if tracking result is not found that means transaction was not successful on the first try
                                            if(!isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] != 0){

                                                $trackingId = "c".$casinoAPI->udate('YmdHisu');
                                                
                                                //Insert to vmsrequestlogs
                                                $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);
                                                
                                                $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source, $siteid, $mid);
                                                
                                                if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                                {
                                                        $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                                        
                                                        $result = array('message'=>$useVoucherResult['UseVoucher']['TransMsg']);
                                                        echo json_encode($result);
                                                        Mirage::app()->end(); 
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
                                
                                $result = array('message'=>$message);
                                echo json_encode($result);
                                Mirage::app()->end(); 
                        }
                }
                else
                {
                        $message = 'VMS: '.$verifyVoucherResult['VerifyVoucher']['TransMsg'];
                        logger($message);
                        
                        $result = array('message'=>$message);
                        echo json_encode($result);
                        Mirage::app()->end(); 
                }
        }
        else
        {
                $paymentType = 1; //payment type is cash method
                $isCreditable = 1;
                
                //check if amount is other denomination
                if($startSessionFormModel->sel_amount == '' || $startSessionFormModel->sel_amount == '--'){
                    $amount = $startSessionFormModel->amount; //amount inputted
                } else {
                    $amount = $startSessionFormModel->sel_amount; //amount selected
                }
                
                    $systemusername = Mirage::app()->param['pcwssysusername'];
                    $result = $pcwsapi->Deposit($loyaltyCardNo, $cid, $paymentType, $amount, $siteid, $accid, $systemusername, $tracenumber, $referencenumber);
                
//                $pos_account_no = $sitesModel->getPosAccountNo($siteid);            
//
//                //Insert to loyaltyrequestlogs
//                $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'R', $terminal_id, $amount, $result["trans_details_id"], $paymentType, $isCreditable);
//               $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
//                $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $transdate, 1, 'R', $amount,$siteid, $result["trans_details_id"],
//                                                                                                                $result['terminal_name'], $isCreditable,$startSessionFormModel->voucher_code, 7, 1);
//                
//                
//                //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
//                   if($isSuccessful){
//                       $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
//                   } else {
//                       $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
//                   }

        }
        if($result[0] == 200){
            $result = json_decode($result[1]);

            $result = $result->Deposit->TransactionMessage;
                    
            if(preg_match('/\Successful\b/', $result)) {
                $result = array('message'=>$result);

            }
            else{
                $result = array('message'=>$result);
                logger($message);
            }    
            
        }
        else{
            $message = 'Error: Deposit Failed';
            $result = array('message'=>$message);
            logger($message);
        }    
        
        echo json_encode($result);
        Mirage::app()->end();
    }
    
    /**
     * @param type $startSessionFormModel
     */
    protected function _redeem($startSessionFormModel) {
        Mirage::loadComponents(array('CommonRedeem','LoyaltyAPIWrapper.class','CommonUBRedeem',
                                     'AsynchronousRequest.class','CasinoApi'));
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
        
        $paymentType = 1; //always cash upon withdrawal
        $isCreditable = 1; 
        
        $bcf = $this->getSiteBalance();
        
        $service_id = $terminalSessionsModel->getServiceId($startSessionFormModel->terminal_id);
        
        $ref_service = $refServicesModel->getServiceById($service_id);
        
        $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($startSessionFormModel->terminal_id);
        
        $terminalName = $terminalsmodel->getTerminalName($startSessionFormModel->terminal_id);
        
        foreach ($casinoUBDetails as $val){
            $casinoUsername = $val['UBServiceLogin'];
            $casinoPassword = $val['UBServicePassword'];
            $casinoHashedPwd = $val['UBHashedServicePassword'];
            $mid = $val['MID'];
            $loyaltyCardNo = $val['LoyaltyCardNumber'];
            $casinoUserMode = $val['UserMode'];
            $casinoServiceID = $val['ServiceID'];
        }
        
//        if($ref_service['Code'] == 'MM'){
            list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count, $isewallet) = 
                $this->getCardInfo($loyaltyCardNo, $this->site_id, 2);
//            if($isewallet > 0){
//                $message = "Redemption failed. Player's account is already e-wallet.";
//                logger($message);
//                $this->throwError($message);
//            }
//        }
        
//        if($isewallet > 0 && ($service_id == 19 || $service_id == 20)){
//            //$result = $this->_lock($startSessionFormModel->terminal_id);
//            $message = 'Error: You are not allowed to end a session for an e-SAFE account.';
//            logger($message);
//            $this->throwError($message);
//        }
//        else{
            //checking if casino is terminal based
            if($ref_service['UserMode'] == 0){
                 $login_acct = $terminalName;
                 $terminal_pwd = $terminalsmodel->getTerminalPassword($startSessionFormModel->terminal_id, 
                                    $service_id);
                 $login_pwd = $terminal_pwd['HashedServicePassword'];
                 $result = $commonRedeem->redeem($login_pwd, $startSessionFormModel->terminal_id, $this->site_id, $bcf, 
                                $service_id, $startSessionFormModel->amount, $paymentType, $this->acc_id, 
                                $loyaltyCardNo, $mid, $casinoUserMode,$casinoUsername,
                                $casinoPassword,$casinoServiceID,$isewallet);
            } 

            //checking if casino is user based
            if($ref_service['UserMode'] == 1){
                
                //Set locator name based on Site Classification
                if($casinoServiceID == 20){ //RTG V15
                    $siteclassification = $sitesModel->getSiteClassification($this->site_id);
                    if($siteclassification == 1){ //1 - Non Platinum, 2 - Platinum
                        $locatorname = Mirage::app()->param['SkinNameNonPlatinum'];
                    } else {
                        $locatorname = Mirage::app()->param['SkinNamePlatinum'];
                    }
                } else { $locatorname = ''; }
                
                 $login_acct = $casinoUsername;
                 $login_pwd  = $casinoHashedPwd;
                 $result = $commonUBRedeem->redeem($login_pwd, $startSessionFormModel->terminal_id, $this->site_id, $bcf, 
                                $service_id, $startSessionFormModel->amount, $paymentType, $this->acc_id, 
                                $loyaltyCardNo, $mid, $casinoUserMode,$casinoUsername,
                                $casinoPassword,$casinoServiceID,$isewallet,$locatorname);
            }


            /**************************** LOYALTY *****************************/
            $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);

            //Insert to loyaltyrequestlogs
            $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'W', $startSessionFormModel->terminal_id, $startSessionFormModel->amount, $result["trans_details_id"],$paymentType,$isCreditable);
            $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
            $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $transdate, 1, 'W', $startSessionFormModel->amount,$this->site_id, $result["trans_details_id"],
                                                                                                                    $result['terminal_name'], $isCreditable,'', 7, 1);

             //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
            if($isSuccessful){
                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
            } else {
                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
            }
//        }
        
        echo json_encode($result);
        Mirage::app()->end();        
    }
    
    
    /**
     * @param type $startSessionFormModel
     */
    public function withdrawForcetAction() {
        Mirage::loadComponents(array('LoyaltyAPIWrapper.class', 'PCWSAPI.class'));
        Mirage::loadModels(array('TerminalSessionsModel','TransactionSummaryModel','TransactionDetailsModel','RefServicesModel'));
        Mirage::loadModels(array('SitesModel','TerminalSessionsModel','TerminalServicesModel',
                                 'RefServicesModel', 'LoyaltyRequestLogsModel',
                                 'SpyderRequestLogsModel','TerminalsModel'));
        

        $terminalSessionsModel = new TerminalSessionsModel();
        $pcws = new PCWSAPI();

        $loyaltycard = $_POST['cardnumber'];
        $pin = $_POST['pin'];
        $amount = $_POST['amount'];
        $systemusername = Mirage::app()->param['pcwssysusername'];
        $service_id = 20;
        
        $hassession = $terminalSessionsModel->checkSession($loyaltycard, $service_id);
        
        if($hassession > 0){
            $message = 'Error: Please end session first.';
            logger($message);
            
            $result = array('message'=>$message);
            echo json_encode($result);
            Mirage::app()->end();  
        }
        
        $checkpinresult = $pcws->CheckPin($loyaltycard, $pin, $systemusername);
        
        if($checkpinresult[0] == 200){
            $checkpinresult = json_decode($checkpinresult[1]);
        }
        else{
            $message = 'Error: Checking of PIN Failed';
            logger($message);
            
            $result = array('message'=>$message);
            echo json_encode($result);
            Mirage::app()->end(); 
        }
        
        $checkpinresult = $checkpinresult->checkPin->TransactionMessage;
        
        if(!preg_match('/\Successful\b/', $checkpinresult)){
            $message = $checkpinresult;
            logger($message);
            
            $result = array('message'=>$message);
            echo json_encode($result);
            Mirage::app()->end(); 
        }
        
        $terminalcount = $terminalSessionsModel->checkSession($loyaltycard, $service_id);
        
        if($terminalcount > 0){
            $message = 'User still has a pending terminal session.';
            logger($message);
            
            $result = array('message'=>$message);
            echo json_encode($result);
            Mirage::app()->end(); 
        }
        
        list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count, $isewallet) = 
                $this->getCardInfo($loyaltycard, $this->site_id, 2);

        $casinoUsername = '';
        $casinoPassword = '';
        $casinoHashedPassword = '';
        $casinoServiceID = '';
        $casinoStatus = '';
        
        //verify if card is ewallet
        if($isewallet < 1){
            $message = "Withdraw failed. Player's account must be e-SAFE.";
            logger($message);
            
            $result = array('message'=>$message);
            echo json_encode($result);
            Mirage::app()->end(); 
        }

        $casinos = $this->loopAndFindCasinoService($casinos, 'ServiceID', $service_id);

        
        if(empty($casinos)){
            $message = 'Please use appropriate membership/temporary card for this casino';
            logger($message);
            
            $result = array('message'=>$message);
            echo json_encode($result);
            Mirage::app()->end(); 
        }
        
        $casinoarray_count = count($casinos);

        for($ctr = 0; $ctr < $casinoarray_count; $ctr++)
        {
                    $casinoUsername = $casinos[$ctr]['ServiceUsername'];
                    $casinoPassword = $casinos[$ctr]['ServicePassword'];
                    $casinoHashedPassword = $casinos[$ctr]['HashedServicePassword'];
                    $casinoServiceID = $casinos[$ctr]['ServiceID'];
                    $casinoStatus = $casinos[$ctr]['Status'];
                    $casinoIsVIP = $casinos[$ctr]['isVIP'];
                    $casinoUserMode = $casinos[$ctr]['UserMode'];                
        }
        
        $result = $pcws->Withdraw($loyaltycard, (string)$service_id, $amount, $this->site_id, $this->acc_id, $systemusername);
            
        if($result[0] == 200){
            $result = json_decode($result[1]);
            
            $result = $result->Withdraw->TransactionMessage;
        
            if(preg_match('/\Successful\b/', $result)) {
                $result = array('message'=>$result);

            }
            else{
                $result = array('message'=>$result);
                logger($message);
            }  
        }
        else{
            $message = 'Error: Withdraw Transaction Failed';
            $result = array('message'=>$message);
            logger($message);
        } 
        
        echo json_encode($result);
        Mirage::app()->end();        
    }
    
    
    /**
     * @param type $startSessionFormModel
     */
    protected function _closeSession($startSessionFormModel) {
    
        Mirage::loadModels(array('TerminalSessionsModel', 'CommonTransactionsModel','TransactionRequestLogsModel'));
        Mirage::loadComponents(array('CasinoApiUB'));
        
        $terminalSessionsModel = new TerminalSessionsModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $transReqLogsModel = new TransactionRequestLogsModel();
        
        $terminalsessiondetails = $terminalSessionsModel->getLastSessionDetails($startSessionFormModel->terminal_id);
        
        if(!empty($terminalsessiondetails)){
            $loyalty_card = $terminalsessiondetails['LoyaltyCardNumber'];
            $mid = $terminalsessiondetails['MID'];
            
            $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($startSessionFormModel->terminal_id);
            $udate = CasinoApiUB::udate('YmdHisu');
            
            $trans_req_log_last_id = $transReqLogsModel->insert($udate, 0, 'W', 1,
                $startSessionFormModel->terminal_id, $this->site_id, 20,$loyalty_card, $mid, 1, '');
            
            $isredeemed = $commonTransactionsModel->redeemTransaction(0, $trans_summary_id, $udate, 
            $this->site_id, $startSessionFormModel->terminal_id, 'W', 1,20, $this->acc_id, 1,
                                                $loyalty_card, $mid);
            
            if(!$isredeemed){
                $result = 'Error: Failed update records in transaction tables';
                logger($result . ' TerminalID='.$this->terminal_id . ' ServiceID=20');
            }
            else{
                $transReqLogsModel->updateTransReqLogDueZeroBal($startSessionFormModel->terminal_id, $this->site_id, 'W', $trans_req_log_last_id);

                $isdeleted = $terminalSessionsModel->deleteTerminalSessionById($startSessionFormModel->terminal_id);

                if(!$isdeleted){
                    $result = 'Error: Failed update records in transaction tables';
                    logger($result . ' TerminalID='.$this->terminal_id . ' ServiceID=20');
                }
                else{
                    $result = array('message'=>'Info: Session has been ended.');
                }
            }
        }
        else{
            $result = array('message'=>'Session is already inactive');
            logger($result . ' TerminalID='.$this->terminal_id . ' ServiceID=20');
        }
        

        
        echo json_encode($result);
        Mirage::app()->end();        
    }
    
    public function _lock($terminalID){
        Mirage::loadComponents(array('PCWSAPI.class'));
        
        $pcwsAPI = new PCWSAPI();
        $terminalSessionsModel = new TerminalSessionsModel();
        $terminalsModel = new TerminalsModel();
        $casinoApi = new CasinoApi();
        
        $transsumid = $terminalSessionsModel->getLastSessSummaryID($terminalID);
        
        if(is_null($transsumid)  || $transsumid == ''){
            $message = 'Error: Please contact Application Support to remove terminal session manually.';
            logger($message);
            $this->throwError($message);
        }
       
        $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($terminalID);
        
        foreach ($casinoUBDetails as $val){
            $casinoUsername = $val['UBServiceLogin'];
            $casinoPassword = $val['UBServicePassword'];
            $login_pwd = $val['UBHashedServicePassword'];
            $mid = $val['MID'];
            $loyaltyCardNo = $val['LoyaltyCardNumber'];
            $casinoUserMode = $val['UserMode'];
            $casinoServiceID = $val['ServiceID'];
        }
        $systemusername = Mirage::app()->param['pcwssysusername'];
        
        $terminalType = $terminalsModel->checkTerminalType($terminalID);
        
        if($terminalType == 2){
            $casinoApi->callSpyderAPI($commandId = 9, $terminalID, $casinoUsername, $login_pwd, $casinoServiceID);
        }
        else{
            $casinoApi->callSpyderAPI($commandId = 1, $terminalID, $casinoUsername, $login_pwd, $casinoServiceID);
        }
        
        $result = $pcwsAPI->Lock($systemusername, $casinoUsername);
      
        if($result[0] == 200){
            $result = json_decode($result[1]);
        }
        else{
            $message = 'Error: Force Logout Failed.';
            logger($message);
            $this->throwError($message);
        }
        
        $result = $result->ForceLogout->TransactionMessage;
        
        if(preg_match('/\Successful\b/', $result)) {
            $result = array('message'=>$result);
        }
        else{
            $casinoApi->callSpyderAPI($commandId = 0, $terminalID, $casinoUsername, $login_pwd, $casinoServiceID);
            $message = $result;
            logger($message);
            $this->throwError($message);
        }        
        
        return $result; 
        unset($result);   
    }
    
    public function _Unlock($tCode, $cardNumber){
        Mirage::loadComponents(array('PCWSAPI.class', 'AsynchronousRequest.class'));
        Mirage::loadModels(array('TerminalServicesModel'));
         
        $pcwsAPI = new PCWSAPI();
        $terminalSessionsModel = new TerminalSessionsModel();
        $terminalsModel = new TerminalsModel();
        $terminalServices = new TerminalServicesModel();
        $spyderReqLogsModel = new SpyderRequestLogsModel();
        $asynchronousRequest = new AsynchronousRequest();
       
        $terminalID = $terminalsModel->getTerminalID($tCode);
        $service_id = $terminalServices->getServiceIDByTerminalID($terminalID);
        $terminaltype = $terminalsModel->checkTerminalType($terminalID);
        
        list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count) = 
                                            $this->getCardInfo($cardNumber, $this->site_id, $terminaltype);
        
        $casinos = $this->loopAndFindCasinoService($casinos, 'ServiceID', $service_id);

            $casinoarray_count = count($casinos);

            for($ctr = 0; $ctr < $casinoarray_count; $ctr++)
            {
                    if($service_id == $casinos[$ctr]['ServiceID'] ){
                        $casinoUsername = $casinos[$ctr]['ServiceUsername'];
                        $casinoPassword = $casinos[$ctr]['ServicePassword'];
                        $casinoHashedPassword = $casinos[$ctr]['HashedServicePassword'];
                        $casinoServiceID = $casinos[$ctr]['ServiceID'];
                        $casinoStatus = $casinos[$ctr]['Status'];
                        $casinoIsVIP = $casinos[$ctr]['isVIP'];
                    }
            }
        
        $terminalCode = trim(str_replace('ICSA-','',$tCode));
        $systemusername = Mirage::app()->param['pcwssysusername'];
        $result = $pcwsAPI->CreateSession($systemusername, $terminalCode, $service_id, $cardNumber);
        
        
        if($result[0] == 200){
            $result = json_decode($result[1]);
        }
        else{
            $message = 'Error: Unlock failed.';
            logger($message);
            $this->throwError($message);
        }
        
        $result = $result->CreateSession->TransactionMessage;
        
        if(preg_match('/\Successful\b/', $result)) {
            $result = array('message'=>$result,'Unlock'=>'1');
        }
        else{
            $message = $result;
            logger($message);
            $this->throwError($message);
        }
        
        //if spyder call was enabled in cashier config, call SAPI
//        if($_SESSION['spyder_enabled'] == 1){
//            $commandId = 0; //unlock
//            $spyder_req_id = $spyderReqLogsModel->insert($tCode, $commandId);
//            $terminal = substr($tCode, strlen("ICSA-")); //removes the "icsa-
//            $computerName = str_replace("VIP", '', $terminal);
//            
//            $params = array('r'=>'spyder/run','TerminalName'=>$computerName,'CommandID'=>$commandId,
//                            'UserName'=>$casinoUsername,'Password'=>$casinoHashedPassword,'Type'=> Mirage::app()->param['SAPI_Type'],
//                            'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$service_id);
//
//            $asynchronousRequest->sapiconnect(http_build_query($params));
//        }
        
        return $result;
        unset($result);
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
        $terminaltype = $terminalsmodel->checkTerminalType($terminal_id);
        //$bank = $startSessionFormModel->sel_bank;
        //$approvalCode = $startSessionFormModel->approval_code;
        $traceNumber = $startSessionFormModel->trace_number;
        $referenceNumber = $startSessionFormModel->reference_number;
        
        
        
        if($terminaltype == 1){
            $message = 'Please start a session using a Genesis Terminal.';
            logger($message);
            $this->throwError($message);
        }
        
       $isVIP = '';
       if(!isset($_POST['isvip'])){
            if(preg_match("/vip$/i", $terminalname, $results)){
                $isVIP = "1";
            } else {
                $isVIP = '0';
            }
       } else {
            $isVIP = $_POST['isvip'];
       }
       
       list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count, $isewallet,$statuscode) = 
                                            $this->getCardInfo($startSessionFormModel->loyalty_card, $this->site_id, $terminaltype);
       
       if($ref_service['ServiceID'] == 20 && $statuscode == 5){
           $message = 'Active temporary card cannot start a session on this terminal.';
            logger($message);
            $this->throwError($message);
       }
       
       if($isewallet > 0 && ($ref_service['ServiceID'] == 19 || $ref_service['ServiceID'] == 20)){
           if($terminaltype != 2){
                $message = 'e-SAFE card cannot start a session on this terminal. Map terminal to e-SAFE type.';
                logger($message);
                $this->throwError($message);
           }
           $result = $this->_Unlock($terminalname, $startSessionFormModel->loyalty_card);
       }
       else{
           //        //check if voucher
                if(isset($startSessionFormModel->voucher_code) && $startSessionFormModel->voucher_code !='')
                {
                            $paymentType = 2; //payment type is coupon 
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
                                    $denominationtype = DENOMINATION_TYPE::INITIAL_DEPOSIT;

                                    $denomination = $this->_getDenoCasinoMinMax($denominationtype);
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

//                                            if($ref_service['Code'] == 'MM'){
//                                                //verify if card is ewallet
//                                                if($isewallet > 0){
//                                                    $message = "Start session failed. Player's account is already e-wallet.";
//                                                    logger($message);
//                                                    $this->throwError($message);
//                                                }
//                                            }

                                            //checking if casino is terminal based
                                            if($ref_service['UserMode'] == 0){
                                                $login_acct = $terminalname;
                                                $terminal_pwd = $terminalsmodel->getTerminalPassword($terminal_id, $startSessionFormModel->casino);
                                                $login_pwd = $terminal_pwd['HashedServicePassword'];
                                                $result = $commonStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                                                   toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                                                   $startSessionFormModel->voucher_code, $trackingId, $casinoUsername,
                                                                   $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode']);
                                            } 

                                            //checking if casino is user based
                                            if($ref_service['UserMode'] == 1)
                                            {
                                                $login_acct = $casinoUsername;
                                                $login_pwd = $casinoHashedPassword;
                                                //check if isVIP of chosen casino is match with the isVIP parameter thrown by loyalty getCardInfo function.
        //                                       
                                                if($casinoIsVIP == 1){
                                                    if($isVIP == 0){
                                                        $terminalcode = $terminalsmodel->getTerminalName($terminal_id);
        //                                            
                                                        $terminalcode = $terminalcode.'VIP';
                                                        $terminal_id = $terminalsmodel->getTerminalID($terminalcode);
                                                    }
                                                }
                                                else{
                                                    if($isVIP == 1){
                                                        $terminalcode = $terminalsmodel->getTerminalName($terminal_id);
        //                                            
                                                        $rest = preg_match('/VIP/', $terminalcode);

                                                        if($rest > 0){
                                                            $terminalcode = substr($terminalcode, 0, -3);

                                                            $terminal_id = $terminalsmodel->getTerminalID($terminalcode);
                                                        }

                                                    }
                                                }
                                                
                                                //Set locator name based on Site Classification
                                                if($startSessionFormModel->casino == 20){ //RTG V15
                                                    $siteclassification = $sitesModel->getSiteClassification($siteid);
                                                    if($siteclassification == 1){ //1 - Non Platinum, 2 - Platinum
                                                        $locatorname = Mirage::app()->param['SkinNameNonPlatinum'];
                                                    } else {
                                                        $locatorname = Mirage::app()->param['SkinNamePlatinum'];
                                                    }
                                                } else { $locatorname = ''; }

                                                $result = $commonUBStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                                                   toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                                                   $startSessionFormModel->voucher_code,$trackingId, $casinoUsername,
                                                                   $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode'],'','',$locatorname);
                                            }

                                            $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);
                                            
                                            
//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>
       
                                            /************************ FOR LOYALTY *************************/
                                           
                                        //Check if Loyalty
                                         $isLoyalty = Mirage::app()->param['Isloyaltypoints'];

                                        //Loyalty points
                                        if ($isLoyalty == 1) {
                                            
                                            $loyalty = new LoyaltyAPIWrapper();
                                            
                                            //Insert to loyaltyrequestlogs
                                            $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminal_id, $amount, $result["trans_details_id"], $paymentType, 1);
                                            $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
                                            $isSuccessful = $loyalty->processPoints($card_number, $transdate, $paymentType, 'D', toInt($amount),$siteid, $result["trans_details_id"],
                                                                          $result['terminal_name'], 1, $startSessionFormModel->voucher_code, $startSessionFormModel->casino  ,  1);
                                            
                                             //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                                            if($isSuccessful){
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                                            } else {
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                                            }
                                        }
                                        else{
                                            
                                            $comppointslogs = new CompPointsLogsModel();
                                            $comppoints = new ComppointsAPIWrapper();

                                            $usermode = $comppointslogs->checkUserMode($startSessionFormModel->casino);
                                            if ($usermode == 0) {
                                                //Insert to comppointslogs
                                                $comppointslogs->insert($mid, $card_number, $terminal_id, $siteid, $startSessionFormModel->casino,  toInt($amount), $transdate, 'D');

                                                //Insert to ewallettrans     
                                                $comppoints->processPoints($card_number, $transdate, $paymentType, 'R', 
                                                                        toInt($amount), $siteid,$result["trans_details_id"], $result['terminal_name'], 1, $startSessionFormModel->voucher_code, $startSessionFormModel->casino , $isCreditable);
                                            } 
                                        }
                
//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>    
                                            //Insert to vmsrequestlogs
                                            $vmsrequestlogsID = $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);

                                            //use voucher, and check result
                                            $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source, $siteid, $mid);

                                            //If first try of use voucher fails, retry
                                            if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && $useVoucherResult['UseVoucher']['ErrorCode'] != 0)
                                            {
                                                $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);

                                                //verify tracking id, if tracking id is not found and voucher is unclaimed proceed to use voucher
                                                $verifyVoucherResult = $voucherManagement->verifyVoucher('', $accid, $source, $trackingId);

                                                //check if tracking result is not found that means transaction was not successful on the first try
                                                if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] != 0){

                                                    $trackingId = "c".$casinoAPI->udate('YmdHisu');

                                                    //Insert to vmsrequestlogs
                                                    $vmsrequestlogs->insert($vouchercode, $accid, $terminal_id,$trackingId);

                                                    $useVoucherResult = $voucherManagement->useVoucher($accid, $trackingId, $vouchercode, $terminal_id, $source, $siteid, $mid);

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
                    $paymentType = 1; //payment type is cash
                    $isCreditable = 1;

                    //check if amount is other denomination
                    if($startSessionFormModel->sel_amount == '' || $startSessionFormModel->sel_amount == '--'){
                        $amount = $startSessionFormModel->amount; //amount inputted
                    } else {
                        $amount = $startSessionFormModel->sel_amount; //amount selected
                    }

                    //check if amount is bancnet
                    if($startSessionFormModel->sel_amount=='bancnet'){
                        if($traceNumber==''){
                            $message = 'Trace number cannot be empty.';
                            logger($message);
                            $this->throwError($message);
                        }

                        if($referenceNumber==''){
                            $message = 'Reference Number cannot be empty.';
                            logger($message);
                            $this->throwError($message);
                        }
                        $amount = $startSessionFormModel->amount;

                    }else if(!empty($traceNumber) && !empty($referenceNumber)){
                        $amount = $startSessionFormModel->amount;
                    }

                    $casinoUsername = '';
                    $casinoPassword = '';
                    $casinoHashedPassword = '';
                    $casinoServiceID = '';
                    $casinoStatus = '';

//                    if($ref_service['Code'] == 'MM'){
//                        //verify if card is ewallet
//                        if($isewallet > 0){
//                            $message = "Start session failed. Player's account is already e-wallet.";
//                            logger($message);
//                            $this->throwError($message);
//                        }
//                    }

                    $casinos = $this->loopAndFindCasinoService($casinos, 'ServiceID', $ref_service['ServiceID']);

                    if($ref_service['UserMode'] == 1){
                        if(empty($casinos)){
                            $message = 'Please use appropriate membership/temporary card for this casino';
                            logger($message);
                            $this->throwError($message); 
                        }
                    }


                    $casinoarray_count = count($casinos);

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
                        $login_pwd = $terminal_pwd['HashedServicePassword'];

                        $result = $commonStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                           toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                           $vouchercode, $trackingId, $casinoUsername,
                                           $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode'],$traceNumber, $referenceNumber);
                    } 

                    //checking if casino is user based
                    if($ref_service['UserMode'] == 1)
                    {
                        $login_acct = $casinoUsername;
                        $login_pwd = $casinoHashedPassword;
                        // check if isVIP of chosen casino is match with the isVIP parameter thrown by loyalty getCardInfo function.

                        if($casinoIsVIP == 1){
                            if($isVIP == 0){
                                $terminalcode = $terminalsmodel->getTerminalName($terminal_id);

                                $terminalcode = $terminalcode.'VIP';
                                $terminal_id = $terminalsmodel->getTerminalID($terminalcode);

                            }
                        }
                        else{
                            if($isVIP == 1){
                                $terminalcode = $terminalsmodel->getTerminalName($terminal_id);

                                $rest = preg_match('/VIP/', $terminalcode);

                                if($rest > 0){
                                    $terminalcode = substr($terminalcode, 0, -3);

                                    $terminal_id = $terminalsmodel->getTerminalID($terminalcode);
                                }

                            }
                        }
                        
                        //Set locator name based on Site Classification
                        if($startSessionFormModel->casino == 20){ //RTG V15
                            $siteclassification = $sitesModel->getSiteClassification($siteid);
                            if($siteclassification == 1){ //1 - Non Platinum, 2 - Platinum
                                $locatorname = Mirage::app()->param['SkinNameNonPlatinum'];
                            } else {
                                $locatorname = Mirage::app()->param['SkinNamePlatinum'];
                            }
                        } else { $locatorname = ''; }

                        //$this->throwError($bank.' : '.$approvalCode);
                        $result = $commonUBStartSession->start($terminal_id, $siteid, 'D', $paymentType, $startSessionFormModel->casino,
                                           toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                                           $vouchercode,$trackingId, $casinoUsername,
                                           $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode'],$traceNumber, $referenceNumber,$locatorname);
                    }

                    $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);
                    
//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>
       
                                            /************************ FOR LOYALTY *************************/
                                           
                                        //Check if Loyalty
                                         $isLoyalty = Mirage::app()->param['Isloyaltypoints'];

                                        //Loyalty points
                                        if ($isLoyalty == 1) {
                                            
                                            $loyalty = new LoyaltyAPIWrapper();
                                            
                                            //Insert to loyaltyrequestlogs
                                            $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminal_id, toInt($amount), $result["trans_details_id"], $paymentType, 1);
                                            $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
                                            $isSuccessful = $loyalty->processPoints($card_number, $transdate, $paymentType, 'D', toInt($amount), $siteid, $result["trans_details_id"],
                                                                          $result['terminal_name'], 1, $vouchercode, $startSessionFormModel->casino,  1);
                                            
                                             //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                                            if($isSuccessful){
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
                                            } else {
                                                $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                                            }
                                        }
                                        else{
                                            
                                            $comppointslogs = new CompPointsLogsModel();
                                            $comppoints = new ComppointsAPIWrapper();

                                            $usermode = $comppointslogs->checkUserMode($startSessionFormModel->casino);
                                            if ($usermode == 0) {
                                                //Insert to comppointslogs
                                                $comppointslogs->insert($mid, $card_number, $terminal_id, $siteid, $startSessionFormModel->casino,  toInt($amount), $transdate, 'D');

                                                //Insert to ewallettrans     
                                                $comppoints->processPoints($card_number, $transdate, $paymentType, 'R', 
                                                                        toInt($amount), $siteid,$result["trans_details_id"], $result['terminal_name'], 1, $vouchercode, $startSessionFormModel->casino , 1);
                                            } 
                                        }
                
//------------------------------------------------------------------------------------------------------------>>>>>>>>>>>>>  

                }

                $res = $terminalSessionsModel->getDataById($terminal_id);
                $asof = ' as of '.date('m/d/Y H:i:s',strtotime($res['LastTransactionDate']));
                $time_playing = getTimePlaying($res['minutes']);
                $result = array_merge($result,array('id'=>$terminal_id,'casino'=>$ref_service['Code'],
                    'service_id'=>$startSessionFormModel->casino,'time_playing'=>$time_playing,
                    'asof'=>$asof,'Unlock'=>'0'));

                if($return) {
                    return $result;
                }

                //if spyder call was enabled in cashier config, call SAPI
                if($_SESSION['spyder_enabled'] == 1){
                    $commandId = 0; //unlock
                    $spyder_req_id = $spyderReqLogsModel->insert($terminalname, $commandId);
                    $terminal = substr($terminalname, strlen("ICSA-")); //removes the "icsa-
                    $computerName = str_replace("VIP", '', $terminal);

                    $params = array('r'=>'spyder/run','TerminalName'=>$computerName,'CommandID'=>$commandId,
                                    'UserName'=>$login_acct,'Password'=>$login_pwd,'Type'=> Mirage::app()->param['SAPI_Type'],
                                    'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$startSessionFormModel->casino);

                    $asynchronousRequest->sapiconnect(http_build_query($params));
                }
       }
        
        echo json_encode($result);
        Mirage::app()->end();
    }  
    
    
    
    /**
     * @param type $UnlockTerminalFormModel
     * @param type $return
     * @return type
     */
    protected function _unlockSession($UnlockTerminalFormModel,$return=false) {
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
        
        $terminal_id = $UnlockTerminalFormModel->terminal_id;
        $siteid = $this->site_id;
        $accid = $this->acc_id;
        $vouchercode = '';
        $trackingId = '';
        $ref_service = $refService->getServiceById($UnlockTerminalFormModel->casino);        
        $terminalname = $terminalsmodel->getTerminalName($terminal_id);
        $terminaltype = $terminalsmodel->checkTerminalType($terminal_id);
        
        if($terminaltype == 1){
            $message = 'Please start a session using a Genesis Terminal.';
            logger($message);
            $this->throwError($message);
        }
        
       $isVIP = '';
       if(!isset($_POST['isvip'])){
            if(preg_match("/vip$/i", $terminalname, $results)){
                $isVIP = "1";
            } else {
                $isVIP = '0';
            }
       } else {
            $isVIP = $_POST['isvip'];
       }
       
       
        $paymentType = 1; //payment type is cash
        $isCreditable = 1;

        //check if amount is other denomination
        if($UnlockTerminalFormModel->sel_amount == '' || $UnlockTerminalFormModel->sel_amount == '--'){
            $amount = $UnlockTerminalFormModel->amount; //amount inputted
        } else {
            $amount = $UnlockTerminalFormModel->sel_amount; //amount selected
        }


        list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count) = 
                $this->getCardInfo($UnlockTerminalFormModel->loyalty_card, $this->site_id, $terminaltype);

        $casinoUsername = '';
        $casinoPassword = '';
        $casinoHashedPassword = '';
        $casinoServiceID = '';
        $casinoStatus = '';

        $casinos = $this->loopAndFindCasinoService($casinos, 'ServiceID', $ref_service['ServiceID']);

        if($ref_service['UserMode'] == 1){
            if(empty($casinos)){
            $message = 'Please use appropriate membership/temporary card for this casino';
            logger($message);
            $this->throwError($message); 
            }
        }


        $casinoarray_count = count($casinos);

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
            $terminal_pwd = $terminalsmodel->getTerminalPassword($terminal_id, $UnlockTerminalFormModel->casino);
            $login_pwd = $terminal_pwd['HashedServicePassword'];
            $result = $commonStartSession->start($terminal_id, $siteid, 'D', $paymentType, $UnlockTerminalFormModel->casino,
                               toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                               $vouchercode, $trackingId, $casinoUsername,
                               $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode']);
        } 

        //checking if casino is user based
        if($ref_service['UserMode'] == 1)
        {
            $login_acct = $casinoUsername;
            $login_pwd = $casinoHashedPassword;
            // check if isVIP of chosen casino is match with the isVIP parameter thrown by loyalty getCardInfo function.

            if($casinoIsVIP == 1){
                if($isVIP == 0){
                    $terminalcode = $terminalsmodel->getTerminalName($terminal_id);

                    $terminalcode = $terminalcode.'VIP';
                    $terminal_id = $terminalsmodel->getTerminalID($terminalcode);

                }
            }
            else{
                if($isVIP == 1){
                    $terminalcode = $terminalsmodel->getTerminalName($terminal_id);

                    $rest = preg_match('/VIP/', $terminalcode);

                    if($rest > 0){
                        $terminalcode = substr($terminalcode, 0, -3);

                        $terminal_id = $terminalsmodel->getTerminalID($terminalcode);
                    }

                }
            }

            $result = $commonUBStartSession->unlock($terminal_id, $siteid, 'D', $paymentType, $UnlockTerminalFormModel->casino,
                               toInt($this->getSiteBalance()),toInt($amount),$accid,$card_number, 
                               $vouchercode,$trackingId, $casinoUsername,
                               $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $ref_service['UserMode']);
        }

        $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);

        /************************ FOR LOYALTY *************************/

        //Insert to loyaltyrequestlogs
        $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminal_id, toInt($amount), $result["trans_details_id"], $paymentType,$isCreditable);
        $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
        if($is_loyalty) {
            $isSuccessful = $loyalty->processPoints($UnlockTerminalFormModel->loyalty_card, $transdate, 1, 'D', $amount,$siteid, $result["trans_details_id"],
                                                                                    $result['terminal_name'], $isCreditable,$UnlockTerminalFormModel->voucher_code, 7, 1);
        }

         //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
        if($isSuccessful){
            $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);
        } else {
            $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
        }
                    
//        }
        
        $res = $terminalSessionsModel->getDataById($terminal_id);
        $asof = ' as of '.date('m/d/Y H:i:s',strtotime($res['LastTransactionDate']));
        $time_playing = getTimePlaying($res['minutes']);
        $result = array_merge($result,array('id'=>$terminal_id,'casino'=>$ref_service['Code'],
            'service_id'=>$UnlockTerminalFormModel->casino,'time_playing'=>$time_playing,
            'asof'=>$asof));
        
        if($return) {
            return $result;
        }
        
        //if spyder call was enabled in cashier config, call SAPI
        if($_SESSION['spyder_enabled'] == 1){
            $commandId = 0; //unlock
            $spyder_req_id = $spyderReqLogsModel->insert($terminalname, $commandId);
            $terminal = substr($terminalname, strlen("ICSA-")); //removes the "icsa-
            $computerName = str_replace("VIP", '', $terminal);
            
            $params = array('r'=>'spyder/run','TerminalName'=>$computerName,'CommandID'=>$commandId,
                            'UserName'=>$login_acct,'Password'=>$login_pwd,'Type'=> Mirage::app()->param['SAPI_Type'],
                            'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$UnlockTerminalFormModel->casino);

            $asynchronousRequest->sapiconnect(http_build_query($params));
        }
        
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
    
    public function getRedeemableAmountAndDetails2Action() {
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
        $terminal_id = $_POST['UnlockTerminalFormModel']['terminal_id'];
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
        
        if(isset($_POST['lock_click'])) {
            return $json;
        } else {
            echo json_encode($json);
            Mirage::app()->end();
        }
    }
    
    
    public function getRedeemableAmountAndDetails3Action() {
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
        $terminal_id = $_POST['ForceTFormModel']['terminal_id'];
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
        
        if(isset($_POST['close_click'])) {
            return $json;
        } else {
            echo json_encode($json);
            Mirage::app()->end();
        }
    }
    
    public function getRedeemableAmountAndDetails4Action() {
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
        $terminal_id = $_POST['LockTerminalFormModel']['terminal_id'];
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
        
        if(isset($_POST['close_click'])) {
            return $json;
        } else {
            echo json_encode($json);
            Mirage::app()->end();
        }
    }
    
    
    public function getRedeemableAmountAndDetails5Action() {
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
        $terminal_id = $_POST['UnlockTerminalFormModel']['terminal_id'];
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
        
        if(isset($_POST['close_click'])) {
            return $json;
        } else {
            echo json_encode($json);
            Mirage::app()->end();
        }
    }
    
    
    public function getRedeemableAmountAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        
        Mirage::loadComponents(array('CasinoApi','LoyaltyAPIWrapper.class'));
        Mirage::loadModels(array('TerminalSessionsModel','TransactionSummaryModel','TransactionDetailsModel','RefServicesModel'));
        
        $casinoApi = new CasinoApi();
        
        $loyaltycard = $_POST['loyalty_card'];
        
        list($is_loyalty, $card_number,$loyalty, $casinos, $mid, $casinoarray_count) = 
                $this->getCardInfo($loyaltycard, $this->site_id, 2);

        $casinoUsername = '';
        $casinoPassword = '';
        $casinoHashedPassword = '';
        $casinoServiceID = '';
        $casinoStatus = '';

        $casinos = $this->loopAndFindCasinoService($casinos, 'ServiceID', 19);

        
        if(empty($casinos)){
            $message = 'Please use appropriate membership/temporary card for this casino';
            logger($message);
            $this->throwError($message); 
        }
        
        $casinoarray_count = count($casinos);

        for($ctr = 0; $ctr < $casinoarray_count; $ctr++)
        {
                    $casinoUsername = $casinos[$ctr]['ServiceUsername'];
                    $casinoPassword = $casinos[$ctr]['ServicePassword'];
                    $casinoHashedPassword = $casinos[$ctr]['HashedServicePassword'];
                    $casinoServiceID = $casinos[$ctr]['ServiceID'];
                    $casinoStatus = $casinos[$ctr]['Status'];
                    $casinoIsVIP = $casinos[$ctr]['isVIP'];
                
        }
        
            list ($terminal_balance) = $casinoApi->getBalanceForceT(0, $this->site_id, 'W', 
                        20, $this->acc_id, $casinoUsername);
        
        $json = array('amount'=>toMoney($terminal_balance));
               
        
        if(isset($_POST['close_click'])) {
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
    protected function getCardInfo($barCode, $siteID, $terminaltype){
        $is_loyalty = false;
        $loyalty = new LoyaltyAPIWrapper();
        $card_number = '';

        //check if site group is BGI ; // change condition from EQ t 1 to NEQ TO 1
        // to allow all sites to access loyalty 1.6 7/2/2012
        if($_SESSION['isbgi'] <> 1)
        {
            if($barCode != '') {
                
                $result = $loyalty->getCardInfo($barCode, 1, $siteID);
                $obj_result = json_decode($result);
                
                if($obj_result->CardInfo->CardNumber == null) {
                    header('HTTP/1.0 404 Not Found');
                    echo 'Can\'t get card info';
                    Mirage::app()->end();
                }
                
                /*//if playername and if SiteGroup is BGI
                elseif($obj_result->CardInfo->MemberName == null){
                    Mirage::loadLibraries('LoyaltyScripts');
                    header('HTTP/1.0 401 Unauthorized');
                    Mirage::app()->end();
                }*/
                
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
                
                $result = $loyalty->getCardInfo($barCode,1, $siteID);
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
        
        //check if genesis checking is enabled in config file
        if(Mirage::app()->param['is_Genesis'] == 1){
            
           //if terminaltype is genesis and temp card is present return error meessage 
           if($terminaltype == 1){
                if($obj_result->CardInfo->StatusCode == 5){
                    header('HTTP/1.0 404 Not Found');
                        echo 'The membership card you entered is not supported. Please use the red membership card.';
                        Mirage::app()->end();
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
            $isewallet = $obj_result->CardInfo->IsEwallet;
            $statuscode = $obj_result->CardInfo->StatusCode;
            
        return array($is_loyalty, $card_number, $loyalty, $casinos, $mid, $casinoarray_count, $isewallet,$statuscode);
    }
    
        
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * Description: get Find a certain service in Casino Array
     */
      function loopAndFindCasinoService($array, $index, $search){
        $returnArray = array();
            foreach($array as $k=>$v){
                  if($v[$index] == $search){   
                       $returnArray[] = $v;
                  }
            }
      return $returnArray;
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
