<?php
Mirage::loadComponents(array('FrontendController','TerminalMonitoringPager'));
/**
 * Classname: TerminalController
 * Author: Bryan Salazar
 * Required: FrontendController and TerminalMonitoringPager
 */
class TerminalController extends FrontendController {
    
    /**
     * Description: This is override from FrontendController so that
     *  the refresh menu will show only in terminal monitoring
     * @var boolean true 
     */
    public $show_refresh = true;
    
    /**
     * Description: This is override from FrontendController so that
     *  it will not delete the session of current page
     * @var boolean  true
     */
    protected $_is_terminal_monitoring = true;
    
    
    /**
     * Description: This page can only access through ajax and post request.
     *  It will get the limit for denomination. If isset($_POST['isreload']),
     *  it will also get the transaction details , terminal details and
     *  the realtime terminal balance and update the database
     * 
     * Optional Param: $_POST['isreload'] to get other details
     * 
     * @param $_POST['terminal_id'];
     * @return json
     */
    public function getDenominationAndCasinoAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        if(isset($_POST['isreload'])) {
            $denominationtype = DENOMINATION_TYPE::RELOAD;
        }
        else {
            $denominationtype = DENOMINATION_TYPE::INITIAL_DEPOSIT;
        }
        $deno_casino_min_max = $this->_getDenoCasinoMinMax($denominationtype);
        if(isset($_POST['isreload'])) {
            Mirage::loadComponents('CasinoApi');
            Mirage::loadModels(array('TransactionSummaryModel','TransactionDetailsModel','TerminalSessionsModel'));
            $transactionSummaryModel = new TransactionSummaryModel();
            $transactionDetailModel = new TransactionDetailsModel();
            $terminalSessionsModel = new TerminalSessionsModel();
            $casinoApi = new CasinoApi();
            
            $terminal_session_data = $terminalSessionsModel->getDataById($_POST['terminal_id']);
            
            $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($_POST['terminal_id']);
       
            foreach ($casinoUBDetails as $val){
                $casinoUsername = $val['UBServiceLogin'];
                $casinoPassword = $val['UBServicePassword'];
                $mid = $val['MID'];
                $loyaltyCardNo = $val['LoyaltyCardNumber'];
                $casinoUserMode = $val['UserMode'];
                $casinoServiceID = $val['ServiceID'];
            }

            if($casinoUserMode == 0)
                list($terminal_balance) = $casinoApi->getBalance($_POST['terminal_id'], $this->site_id, 'R', 
                        $terminal_session_data['ServiceID'],  $this->acc_id);

            if($casinoUserMode == 1)
                list ($terminal_balance) = $casinoApi->getBalanceUB($_POST['terminal_id'], $this->site_id, 'R', 
                            $casinoServiceID, $this->acc_id, $casinoUsername, $casinoPassword);
            
            //$last_trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($_POST['terminal_id'],$this->site_id);
            $last_trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($_POST['terminal_id']);
            $trans_details = $transactionDetailModel->getSessionDetails($last_trans_summary_id);
            $terminal_session_data['DateStarted'] = date('Y-m-d h:i:s A',strtotime($terminal_session_data['DateStarted']));
            $deno_casino_min_max = array_merge($deno_casino_min_max,array('trans_details'=>$trans_details,
                'terminal_session_data'=>$terminal_session_data,'terminal_balance'=>toMoney($terminal_balance)));
        }
        
        echo json_encode($deno_casino_min_max);
        Mirage::app()->end();
    }
    
    
    
    public function getDenominationAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        if(isset($_POST['isreload'])) {
            $denominationtype = DENOMINATION_TYPE::RELOAD;
        }
        else {
            $denominationtype = DENOMINATION_TYPE::INITIAL_DEPOSIT;
        }
        $deno_casino_min_max = $this->_getDenoCasinoMinMax($denominationtype);
        
        echo json_encode($deno_casino_min_max);
        Mirage::app()->end();
    }
    
    /**
     * Description: This page is default page for terminal monitoring.
     *  This page will also call when you change the page number through
     *  ajax request. If request type is ajax will return json else html
     */
    public function overviewAction() {
        
        $site_code = explode('-', $_SESSION['site_code']);
        $_SESSION['last_code'] = $site_code[1];
        
        $siteid = $this->site_id; 
        $start = 0;
        $len = strlen($this->site_code) + 1;
        
        if(isset($_SESSION['page'])) {
            $start = $_SESSION['page'];
        }
        
        if(isset($_GET['page'])) {
            $start = ($_GET['page'] - 1) * 2;
            $_SESSION['current_page'] = $_GET['page'];
            $_SESSION['page'] = $start;
        }

        Mirage::loadModels(array('TerminalsModel','RefServicesModel'));
        
        $terminalModel = new TerminalsModel();
        $refservicesModel = new RefServicesModel();
        
        $total_terminal = $terminalModel->getNumberOfTerminalsPerSite($siteid);
        $terminals = $terminalModel->getTerminalPerPage($siteid, $start, (Mirage::app()->param['terminal_per_page'] * 4), $len);
        $services = $terminalModel->getServicesGroupByTerminal($siteid);
        $refservices = $refservicesModel->getAllRefServicesByKeyServiceId();

        if($this->isAjaxRequest()) {
            $json = new JsonTerminal();
            if(isset($_GET['page']))
                $json->current_page = $_GET['page'];
            $json->terminals = $terminals;
            $json->services = $services;
            $json->refservices = $refservices;
            $json->server_date = date('Y-m-d H:i:s');
            echo json_encode($json);
            Mirage::app()->end();
        } else {
            $this->render('terminal_overview',array('total_terminal'=>$total_terminal,
                'terminals'=>$terminals,'services'=>$services,'refservices'=>$refservices,'server_date'=>date('Y-m-d H:i:s')));
        }
    }
    
    /**
     * Description: This page will call when you change the 
     *  terminal code in redeeming using hot key. It will return the realtime
     *  terminal balance and update the database
     */
    public function redeemGetBalanceAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        
        Mirage::loadComponents('CasinoApi');
        Mirage::loadModels('TerminalSessionsModel');
        $casinoApi = new CasinoApi();
        $site_id = $this->site_id;
        $terminal_id = $_POST['StartSessionFormModel']['terminal_id'];
        $terminalSessionsModel = new TerminalSessionsModel();
        
        $service_id = $terminalSessionsModel->getServiceId($terminal_id);
        
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
            list($terminal_balance) = $casinoApi->getBalance($terminal_id, $site_id, 'W', $service_id);
        
        if($casinoUserMode == 1)
            list ($terminal_balance) = $casinoApi->getBalanceUB($terminal_id, $site_id, 'W', 
                        $casinoServiceID, $acct_id = '', $casinoUsername, $casinoPassword);
        
        echo toMoney($terminal_balance);
        Mirage::app()->end();
    }
    
    /***************************** HOT KEY  ***********************************/
    
    /**
     * Description: This page will call using hot key (D) and when you submit
     *  the form for startsession through hot key. This page can be access
     *  through ajax request only
     */
    public function startSessionHotkeyAction() {
        if(!$this->isAjaxRequest()) 
            Mirage::app()->error404();
        
        Mirage::loadModels(array('StartSessionFormModel','TerminalsModel', 'BanksModel'));
        $startSessionFormModel = new StartSessionFormModel();
        
        $terminalsModel = new TerminalsModel();
        $banksModel = new BanksModel();
        
        $terminals = $terminalsModel->getAllNotActiveTerminals($this->site_id, $this->len);
        $denomination = array();
        $casinos = array();
        //$banks = $banksModel->generateBanks();
        
        if(isset($_POST['StartSessionFormModel'])) {
            $denominationtype = DENOMINATION_TYPE::INITIAL_DEPOSIT;
            $deno_casino_min_max = $this->_getDenoCasinoMinMax($denominationtype);
            $startSessionFormModel->max_deposit = $deno_casino_min_max['max_denomination'];
            $startSessionFormModel->min_deposit = $deno_casino_min_max['min_denomination'];
            $casinos = $deno_casino_min_max['casino'];
            $startSessionFormModel->sel_amount = $deno_casino_min_max['denomination'];
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
            $denomination = $deno_casino_min_max['denomination'];
            $startSessionFormModel->amount = toInt($startSessionFormModel->amount);
            $this->_startSession($startSessionFormModel);
            
        }
        $this->renderPartial('terminal_startsession_hk',array('startSessionFormModel'=>$startSessionFormModel,
            'terminals'=>$terminals,'denomination'=>$denomination,'casinos'=>$casinos));
    }        
    
    /**
     * Description: This page will call using hot key (W) and when you submit
     *  the form for redeem through hot key. This page can be access
     *  through ajax request only
     */    
    public function redeemHotkeyAction() {
        if(!$this->isAjaxRequest())
            Mirage::app()->error404();
        
        Mirage::loadModels(array('StartSessionFormModel','TerminalsModel'));
        $startSessionFormModel = new StartSessionFormModel();
        
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveTerminals($this->site_id, $this->len);
        if(isset($_POST['StartSessionFormModel'])) {
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
            if($startSessionFormModel->isValid(array('terminal_id','amount'),true)) {
                $this->_redeem($startSessionFormModel);
            }
            $this->throwError('Invalid Input');
        }
        $this->renderPartial('terminal_redeem_hk',array('startSessionFormModel'=>$startSessionFormModel,'terminals'=>$terminals));
    }   
    
    
    /**
     * Description: This page will call using hot key (W) and when you submit
     *  the form for redeem through hot key. This page can be access
     *  through ajax request only
     */    
    public function closeHotkeyAction() {
        if(!$this->isAjaxRequest())
            Mirage::app()->error404();
        
        Mirage::loadModels(array('UnlockTerminalFormModel','TerminalsModel'));
        $unlockTerminalFormModel = new UnlockTerminalFormModel();
        
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        if(isset($_POST['UnlockTerminalFormModel'])) {
            $unlockTerminalFormModel->setAttributes($_POST['UnlockTerminalFormModel']);
            if($unlockTerminalFormModel->isValid(array('terminal_id','amount'),true)) {
                $this->_closeSession($unlockTerminalFormModel);
            }
            $this->throwError('Invalid Input');
        }
        $this->renderPartial('terminal_close_hk',array('UnlockTerminalFormModel'=>$unlockTerminalFormModel,'terminals'=>$terminals));
    }
    
    
    public function closeHotkey2Action() {
        if(!$this->isAjaxRequest())
            Mirage::app()->error404();
        
            $unlockTerminalFormModel = new UnlockTerminalFormModel();
            $unlockTerminalFormModel->setAttributes($_POST['UnlockTerminalFormModel']);
            if($unlockTerminalFormModel->isValid(array('terminal_id','amount'),true)) {
                $this->_closeSession($unlockTerminalFormModel);
            }
            $this->throwError('Invalid Input');
        
        $this->renderPartial('terminal_close_hk',array('UnlockTerminalFormModel'=>$unlockTerminalFormModel,'terminals'=>$terminals));
    }
    
    /**
     * Description: This page will call using hot key (R) and when you submit
     *  the form for reload through hot key. This page can be access
     *  through ajax request only
     */       
    public function reloadHotkeyAction() {
        if(!$this->isAjaxRequest())
            Mirage::app()->error404();
        
        $site_id = $this->site_id;
        $len = strlen($this->site_code) + 1;
        $accid = $this->acc_id;    
        $is_vip = 0;
        $terminal_balance = null;
        $denomination = array();
        $trans_details = array();
//        $login = '';
        $terminal_session_data = null;
        
        Mirage::loadModels(array('StartSessionFormModel','TerminalsModel'));
        $startSessionFormModel = new StartSessionFormModel();
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveTerminals($site_id, $len);
        if(isset($_POST['StartSessionFormModel'])) {
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
            Mirage::loadComponents('CasinoApi');
            Mirage::loadModels('TerminalSessionsModel');
            $terminalSessionsModel = new TerminalSessionsModel();
            $startSessionFormModel->amount = toInt($startSessionFormModel->amount);
            $service_id = $terminalSessionsModel->getServiceId($startSessionFormModel->terminal_id);
            $this->_reload($startSessionFormModel,$service_id);
        }
        $this->renderPartial('terminal_reload_hk',array('startSessionFormModel'=>$startSessionFormModel,'terminals'=>$terminals,
            'is_vip'=>$is_vip,'terminal_balance'=>$terminal_balance,'denomination'=>$denomination,'trans_details'=>$trans_details,
            'terminal_session_data'=>$terminal_session_data));
    }
    
    /**
     * Description: This page will call using hot key (U) and when you submit
     *  the form for unlock through hot key. This page can be access
     *  through ajax request only
     */
    public function unlockHotKeyAction() {
        if(!$this->isAjaxRequest()) 
            Mirage::app()->error404();
        
        Mirage::loadModels(array('UnlockTerminalFormModel','TerminalsModel'));
        $UTFormModel = new UnlockTerminalFormModel();
        
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllNotActiveForceTTerminals($this->site_id, $this->len);
        $denomination = array();
        $casinos = array();
        
        if(isset($_POST['UnlockTerminalFormModel'])) {
            $denominationtype = DENOMINATION_TYPE::INITIAL_DEPOSIT;
            $deno_casino_min_max = $this->_getDenoCasinoMinMax($denominationtype);
            $UTFormModel->max_deposit = $deno_casino_min_max['max_denomination'];
            $UTFormModel->min_deposit = $deno_casino_min_max['min_denomination'];
            $casinos = $deno_casino_min_max['casino'];
            $UTFormModel->sel_amount = $deno_casino_min_max['denomination'];
            $UTFormModel->setAttributes($_POST['UnlockTerminalFormModel']);
            $denomination = $deno_casino_min_max['denomination'];
            $UTFormModel->amount = toInt($UTFormModel->amount);
            $this->_unlockSession($UTFormModel);
        }
        $this->renderPartial('terminal_unlock_hk',array('unlock'=>$UTFormModel,
            'terminals'=>$terminals,'denomination'=>$denomination,'casinos'=>$casinos));
    }   
    
    /***************************** END HOT KEY  *******************************/
    
    /***************************** CLICK ACTION *******************************/
    
    /**
     * Description: This reload session is call when button is click instead of hot key.
     *  This action will also call when post back
     */
    public function reloadClickAction() {
        if(!$this->isAjaxRequest() && !$this->isPostRequest())
            Mirage::app()->error404();
        
        Mirage::loadComponents('CasinoApi');
        Mirage::loadModels(array('StartSessionFormModel','SiteDenominationModel','RefServicesModel','TerminalSessionsModel'));
        $startSessionFormModel = new StartSessionFormModel();
        $siteDenominationModel = new SiteDenominationModel();
        $refServicesModel = new RefServicesModel();
        $terminalSession = new TerminalSessionsModel();
        
        $casinoApi = new CasinoApi();
        
        $bcf = $this->getSiteBalance();
        
        $is_vip = $_POST['is_vip'];
        $tcode = $_POST['tcode'];
        $tid = $_POST['tid'];
        //$cid = $_POST['cid'];
        $cid = $terminalSession->getServiceId($tid);

        $casinoUBDetails = $terminalSession->getLastSessionDetails($tid);
       
        foreach ($casinoUBDetails as $val){
            $casinoUsername = $val['UBServiceLogin'];
            $casinoPassword = $val['UBServicePassword'];
            $mid = $val['MID'];
            $loyaltyCardNo = $val['LoyaltyCardNumber'];
            $casinoUserMode = $val['UserMode'];
            $casinoServiceID = $val['ServiceID'];
        }
        
        // get balance
        if($casinoUserMode == 0)
            list($terminal_balance) = $casinoApi->getBalance($tid, $this->site_id, 'R', $cid);
        
        if($casinoUserMode == 1)
            list ($terminal_balance) = $casinoApi->getBalanceUB($tid, $this->site_id, 'R', 
                        $casinoServiceID, $acct_id = '', $casinoUsername, $casinoPassword);
        
        // get denomination base on minimum and maximum denomination of sites
        $denomination = $siteDenominationModel->getDenominationPerSiteAndType($this->site_id, DENOMINATION_TYPE::RELOAD, $is_vip);
        $casino = $refServicesModel->getAliasById($cid);
        // set min and max deposit in hidden field
        $startSessionFormModel->min_deposit = toMoney(SiteDenominationModel::$min);
        $startSessionFormModel->max_deposit = toMoney(SiteDenominationModel::$max);   

        list($terminal_session_data,$trans_details) = $this->_getSessionDetail($tid, $this->site_id);
        
        if(isset($_POST['StartSessionFormModel'])) {
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
            $startSessionFormModel->amount = toInt($startSessionFormModel->amount);
                $startSessionFormModel->terminal_id = $tid;
                $this->_reload($startSessionFormModel,$cid);
        }
        
        $this->renderPartial('terminal_reload',array('startSessionFormModel'=>$startSessionFormModel,
            'denomination'=>$denomination,'tcode'=>$tcode,'tid'=>$tid,'terminal_session_data'=>$terminal_session_data,
            'trans_details'=>$trans_details,'is_vip'=>$is_vip,'cid'=>$cid,'terminal_balance'=>$terminal_balance,'casino'=>$casino));
    }
    
    /**
     * Description: This start session is call when button is click instead of hot key.
     *  This action will also call when post back
     */
    public function startSessionClickAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest()) // ajax request only
            Mirage::app()->error404();
        
        // load required models
        Mirage::loadModels(array('StartSessionFormModel','SiteDenominationModel','TerminalServicesModel','RefServicesModel','BanksModel'));
        
        // instance of model
        $startSessionFormModel = new StartSessionFormModel();
        $siteDenomination = new SiteDenominationModel();
        $terminalServices = new TerminalServicesModel();
        //$banksModel = new BanksModel();
        
        $siteid = $this->site_id;
        $accid = $this->acc_id;
        
        // post from ajax and hidden field
        $is_vip = $_POST['isvip'];
        $tcode = $_POST['tcode'];
        
        // get denomination base on minimum and maximum denomination of sites
        $denomination = $siteDenomination->getDenominationPerSiteAndType($siteid, DENOMINATION_TYPE::INITIAL_DEPOSIT, $is_vip);
        
        //get banks
        //$banks = $banksModel->generateBanks();
        
        // set min and max deposit in hidden field
        $startSessionFormModel->min_deposit = toMoney(SiteDenominationModel::$min);
        $startSessionFormModel->max_deposit = toMoney(SiteDenominationModel::$max);
        
        if(isset($_POST['tid'])) {
            $startSessionFormModel->terminal_id = $_POST['tid'];
            $terminal_id = $_POST['tid'];
        }    
        // if StartSessionFormModel is posted
        if(isset($_POST['StartSessionFormModel'])) {
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
            $terminal_id = $startSessionFormModel->terminal_id;
            $startSessionFormModel->amount = toInt($startSessionFormModel->amount);
            $this->_startSession($startSessionFormModel);
            $startSessionFormModel->amount = toMoney($startSessionFormModel->amount);
        }
        
        $casinos = $terminalServices->getCasinoByTerminal($terminal_id);
        
        $this->renderPartial('terminal_startsession',array('startSession'=>$startSessionFormModel,
            'denomination'=>$denomination,'casinos'=>$casinos,'is_vip'=>$is_vip,'tcode'=>$tcode));
    }
    
    /**
     * Description: This redeem is call when button is click instead of hot key.
     *  When redeeming the balance the redeemHotkey will be called
     */
    public function redeemClickAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest()) // ajax and post request only
            Mirage::app()->error404();
            
        $site_id = $this->site_id;
        $accid = $this->acc_id;
        $_POST['redeem_click'] = true;
        Mirage::loadModels(array('StartSessionFormModel'));
        
        $data = $this->getRedeemableAmountAndDetailsAction();
        
        if(isset($_POST['showdetails'])) {
            echo json_encode($data);
            Mirage::app()->end();
        } else {
            $terminal_id = $_POST['StartSessionFormModel']['terminal_id'];
            $this->renderPartial('terminal_redeem',array('data'=>$data,'terminal_id'=>$terminal_id));
        }
    }
    
    public function lockClickAction(){
        if(!$this->isAjaxRequest() || !$this->isPostRequest()) // ajax and post request only
            Mirage::app()->error404();
            
        $site_id = $this->site_id;
        $accid = $this->acc_id;
        $_POST['lock_click'] = true;
        Mirage::loadModels(array('UnlockTerminalFormModel'));
        
        $data = $this->getRedeemableAmountAndDetails2Action();
        
        if(isset($_POST['showdetails'])) {
            echo json_encode($data);
            Mirage::app()->end();
        } else {
            $terminal_id = $_POST['UnlockTerminalFormModel']['terminal_id'];
            $this->renderPartial('terminal_lock',array('data'=>$data,'terminal_id'=>$terminal_id));
        }
    }
    
    public function unlockClickAction(){
       if(!$this->isAjaxRequest() || !$this->isPostRequest()) // ajax request only
            Mirage::app()->error404();
        
        // load required models
        Mirage::loadModels(array('UnlockTerminalFormModel','SiteDenominationModel','TerminalServicesModel','RefServicesModel'));
        
        // instance of model
        $unlockTerminalFM = new UnlockTerminalFormModel();
        $siteDenomination = new SiteDenominationModel();
        $terminalServices = new TerminalServicesModel();
        
        $siteid = $this->site_id;
        $accid = $this->acc_id;
        
        // post from ajax and hidden field
        $is_vip = $_POST['isvip'];
        $tcode = $_POST['tcode'];
        
        // get denomination base on minimum and maximum denomination of sites
        $denomination = $siteDenomination->getDenominationPerSiteAndType($siteid, DENOMINATION_TYPE::INITIAL_DEPOSIT, $is_vip);
        
        // set min and max deposit in hidden field
        $unlockTerminalFM->min_deposit = toMoney(SiteDenominationModel::$min);
        $unlockTerminalFM->max_deposit = toMoney(SiteDenominationModel::$max);
        
        if(isset($_POST['tid'])) {
            $unlockTerminalFM->terminal_id = $_POST['tid'];
            $terminal_id = $_POST['tid'];
        }    
        // if StartSessionFormModel is posted
        if(isset($_POST['UnlockTerminalFormModel'])) {
            $unlockTerminalFM->setAttributes($_POST['UnlockTerminalFormModel']);
            $terminal_id = $unlockTerminalFM->terminal_id;
            $unlockTerminalFM->amount = toInt($unlockTerminalFM->amount);
            $this->_unlockSession($unlockTerminalFM);
            $unlockTerminalFM->amount = toMoney($unlockTerminalFM->amount);
        }
        
        $casinos = $terminalServices->getCasinoByTerminal($terminal_id);
        
        $this->renderPartial('terminal_unlock',array('unlock'=>$unlockTerminalFM,
            'denomination'=>$denomination,'casinos'=>$casinos,'is_vip'=>$is_vip,'tcode'=>$tcode));
    }
    
    public function callLockAction(){
        if(!$this->isAjaxRequest())
            Mirage::app()->error404();
        
        Mirage::loadModels(array('UnlockTerminalFormModel','TerminalsModel'));
        $unlockTerminalFormModel = new UnlockTerminalFormModel();
        
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        if(isset($_POST['UnlockTerminalFormModel'])) {
            $unlockTerminalFormModel->setAttributes($_POST['UnlockTerminalFormModel']);
            if($unlockTerminalFormModel->isValid(array('terminal_id','amount'),true)) {
                $this->_lock($unlockTerminalFormModel->terminal_id);
            }
            $this->throwError('Invalid Input');
        }
        $this->renderPartial('terminal_close_hk',array('UnlockTerminalFormModel'=>$unlockTerminalFormModel,'terminals'=>$terminals));
        
    }
    
    
    public function callUnlockAction(){
        if(!$this->isAjaxRequest())
            Mirage::app()->error404();
        
        Mirage::loadModels(array('UnlockTerminalFormModel','TerminalsModel'));
        $unlockTerminalFormModel = new UnlockTerminalFormModel();
        
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        if(isset($_POST['UnlockTerminalFormModel'])) {
            if(isset($_POST['UnlockTerminalFormModel']['loyalty_card'])
                && isset($_POST['sitecode']) 
                && isset($_POST['tcode'])){
                $cardNumber = $_POST['UnlockTerminalFormModel']['loyalty_card'];
                $terminalCode = $_POST['sitecode'].$_POST['tcode'];
                $this->_Unlock($terminalCode,$cardNumber);
            }
            $this->throwError('Invalid Input');
        }
        
    }
    /**************************** END CLICK ACTION ****************************/
    
    /**
     * Description: This page will get site balance. It will return the
     *  current site balance. This can be acces through ajax request only
     */
    public function getSiteBalanceAction() {
        if(!$this->isAjaxRequest()) 
            Mirage::app()->error404();
        
        echo $this->getSiteBalance();
        Mirage::app()->end();
    }
    
    public function pingAction(){
        die('ok');
    }
    
    
}
