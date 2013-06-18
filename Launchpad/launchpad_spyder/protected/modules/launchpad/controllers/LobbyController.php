<?php

/**
 * Default controller
 * @package application.modules.launchpad.controllers
 * @author Bryan Salazar
 */
Yii::import('application.modules.launchpad.components.CasinoApi');

class LobbyController extends CController 
{
    /**
     *
     * @var int 
     */
    protected $_casinoTransactionID;
    
    /**
     *
     * @var string 
     */
    protected $_apiActualResult;
    
    public function actionScreensaver()
    {
        $this->pageTitle = 'Screen Saver';
        $this->layout = 'screensaver';
        $identity = LPUserIdentity::app();
        $casinoCode = array();
        $casino_serviceId = array();
        if(Yii::app()->user->getState('tcode')) {
            $_GET['terminalCode'] = Yii::app()->user->getState('tcode');
            if($identity->authenticate()) {
                // login
                Yii::app()->user->login($identity);
                Yii::app()->user->setState('currServiceID',$identity->serviceID);
                Yii::app()->user->setState('terminalID',$identity->terminalID);
                Yii::app()->user->setState('siteID',$identity->siteID);
                Yii::app()->user->setState('transSummaryID',$identity->transSummaryID);
                Yii::app()->user->setState('terminalPassword', 'pass1');
                $terminalPassword = LPTerminalSessions::model()->getTerminalPassword($identity->terminalID, 
                                $identity->serviceID);
                 Yii::app()->user->setState('terminalPwd', $terminalPassword['ServicePassword']);
                 Yii::app()->user->setState('encryptPwd', $terminalPassword['HashedServicePassword']);
                 $casinos = LPTerminalServices::model()->getAllAvailableCasino(Yii::app()->user->getState('terminalID'));
                $this->redirect(Yii::app()->createUrl('/launchpad/lobby/index'));
            } else {
                // logout
                Yii::app()->user->logout();
            }
            $terminalID = LPTerminals::model()->getTerminalID($_GET['terminalCode']);
            
            $casinos = LPTerminalServices::model()->getAllAvailableCasino($terminalID['TerminalID']);
            $count = count($casinos);
            for($ctr = 0;$ctr< $count;$ctr++){
                $casinoCode[$ctr] = $casinos[$ctr]['Code'];
                $casino_serviceId[$ctr] = $casinos[$ctr]['ServiceID'];
            }
        }
     
        $this->render('lobby_screensaver',array('CasinoCode'=>$casinoCode,
                      'ServiceID'=> $casino_serviceId));
    }
    
    public function actionPing()
    {
        if(!Yii::app()->request->isAjaxRequest)
            throw new CHttpException (404, 'Page not found');
        echo "The page can be pinged or reached";
    }
    
    public function actionSaveTerminalcode() 
    {
        if(!Yii::app()->request->isAjaxRequest)
            throw new CHttpException(404, "Page not found");
        
        if(isset($_GET['terminalCode'])) {
            Yii::app()->user->setState('tcode',$_GET['terminalCode']);
            echo "The terminal with code {$_GET['terminalCode']} has been saved";
        } else {
            echo "The terminal with code {$_GET['terminalCode']} is not saved";
        }
        Yii::app()->end();    
    }
    
    public function actionIndex()
    {
        if(Yii::app()->user->isGuest) {
            $this->redirect($this->createUrl('screensaver'));
        } else {
            $this->layout = 'main_layout';
            $casinos = LPTerminalServices::model()->getAllAvailableCasino(Yii::app()->user->getState('terminalID'));
            
            //get terminal password
            $terminalPassword = LPTerminalSessions::model()->getTerminalPassword(Yii::app()->user->getState('terminalID'), 
                                $this->getCurrentServiceID());
            
            Yii::app()->user->setState('terminalPwd', $terminalPassword['ServicePassword']);
            Yii::app()->user->setState('encryptPwd', $terminalPassword['HashedServicePassword']);
            
            // attach behavior CasinoConfigGenBehavior for configuration in getBalance
            $this->attachBehavior('casinoConfigGenerator', new CasinoConfigGenBehavior());
            
            $info = $this->getCurrCasinoCasAndType($casinos);
            $cas = $info['cas'];
            $currentCasino = $info['currentCasino'];
            $type = $info['type'];
            $this->setCurrentServiceType($type);
            $currentServiceID = $this->getCurrentServiceID();
            
            if(!$currentServiceID) {
                throw new CHttpException(404, "Can't get current casino");
            } else {
                list($currentBalance, $currentBet, $casinoApiHandler) = $this->getBalance($currentServiceID, $type);
            }
            
            $this->render('lobby_index',array(
                    'cas'=>$cas,
                    'currentCasino'=>$currentCasino,
                    'type'=>$type,
                    'currentBalance'=>$currentBalance,
                    'currentServiceID'=>$currentServiceID,
                ));
        }
    }
    
    /**
     *
     * @param array $casinos
     * @return array 
     */
    protected function getCurrCasinoCasAndType($casinos)
    {
        
        $casinoPosition = LPConfig::app()->params['casino_position'];
        $checkBy = $casinoPosition['check_by'];
        $positions = $casinoPosition['position'];
        $cas = array();
        $type = 'N/A';
        $currentCasino = 'N/A';
        foreach($positions as $position) { // foreach casino position
            foreach($casinos as $casino) { // foreach available casino
                if($casino['ServiceID'] == Yii::app()->user->getState('currServiceID')) {
                    $currentCasino = $casino['Alias'];
                    $type=$casino['type'];
                }
                if($casino[$checkBy] == $position) {
                    $cas[$position] = $casino;
                }
            }
            if(!isset($cas[$position])) {
                $cas[$position] = 'N/A';
            }
        }
        
        return array('currentCasino'=>$currentCasino,'cas'=>$cas,'type'=>$type);
    }
    
    /**
     * This will call upon clicking of casino
     */
    public function actionCasinoClick()
    {
        if(!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(404, "Page not found");
        }
        
        header('Content-type: application/json');
        $currentServiceID = $this->getCurrentServiceID();
        
        if(!$currentServiceID) {
            //echo CJSON::encode(array('istransfer'=>false,'html'=>'not ok')); //Deprecated message on 08/30/2012
            echo CJSON::encode(array('istransfer'=>false,'html'=> "Current Service ID {$currentServiceID} cannot be transferred"));
            Yii::app()->end(); 
        }
        
        $transferCasino = $_GET['serviceid'];
        if($currentServiceID != $_GET['serviceid']) {
            $html = $this->_getDisplaytransfer($currentServiceID, $transferCasino);
            $response = array('istransfer'=>true,'html'=>$html);
        } else {
            $html = $this->_getDisplaySame($currentServiceID);
            $response = array('istransfer'=>false,'html'=>$html);
        }
        echo CJSON::encode($response);
        Yii::app()->end();        
    }
    
    /**
     * This action will be call if there is error in request
     */
    public function actionError()
    {
        if(($error=Yii::app()->errorHandler->error)) {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->redirect (Yii::app()->createUrl('launchpad/lobby/screensaver'));
        }
    }
    
    /**
     * This will call if click yes to transfer casino
     */
    public function actionTransfer()
    {
        if(!Yii::app()->request->isAjaxRequest) {
            $this->log('Request should be should be ajax');
            throw new CHttpException (404, 'Invalid Request');
        }       
        if(!isset($_GET['serviceID']) || $_GET['serviceID'] == '') {
            $this->log('serviceID cannot be empty');
            throw new CHttpException (404, 'Invalid Request');
        }
        
        if(!isset($_GET['serviceType']) || $_GET['serviceType'] == '') {
            $this->log('$_GET[\'serviceType\'] cannot be empty');
            throw new CHttpException(404, 'Invalid Request');
        }
            
        // attach behavior CasinoConfigGenBehavior
        $this->attachBehavior('casinoConfigGenerator', new CasinoConfigGenBehavior());        
        
        header('Content-type: application/json');
        $currentServiceID = $this->getCurrentServiceID();
        if(!$currentServiceID) {
            throw new CHttpException(404,'<b style=\"width:125px\">Session Ended</b>');
        }
        
        $currentServiceType = $this->getCurrentServiceType();
        $pickServiceID = $_GET['serviceID'];
        $pickServiceType = $_GET['serviceType'];
        
        list($currentBalance, $currentBet, $wcasinoApiHandler) = $this->getBalance($this->getCurrentServiceID(), $this->getCurrentServiceType());
        
        // die if can't get balance of current casino or rtg casino certificate is outdated
        if($currentBalance == 'N/A' || !is_object($wcasinoApiHandler)) {
            $this->log('[ServiceID: ' . $this->getCurrentServiceID() .'] can\'t get balance');
            throw new CHttpException(404, 'Server is busy please try again. [ServiceID: ' . $this->getCurrentServiceID() .'] can\'t get balance');
            
        // die if current balance is less than or equal to zero    
        } else if($currentBalance <= 0) {
            $this->log('[ServiceID: ' . $this->getCurrentServiceID() .'] not enough balance');
            throw new CHttpException(404, 'Unable to process request. You don\'t have enough balance. [ServiceID: ' . $this->getCurrentServiceID() .'] not enough balance');
        }
        
        if($currentBet > 0){
            $ptcurrentBet = $currentBet;
        } else {
            $ptcurrentBet = 0;
        }
        
        //check if there was a pending game bet for RTG
        if(strpos($currentServiceType, 'rtg') !== false ){
            $terminalID = Yii::app()->user->getState('terminalID');
            $casinoAPI = new CasinoApi();
            $PID = $wcasinoApiHandler->GetPID(Yii::app()->user->getState('tcode'));
            $pendingGames = $casinoAPI->GetPendingGames($terminalID, $currentServiceID, $PID);
        } else {
            $pendingGames = '';
        }

        //Log and Display error message if there was a pending game bet (for RTG casino only).
        if(is_array($pendingGames) && $pendingGames['IsSucceed'] == true){
            $message = "Unable to process request. There was a pending game bet on  ".$pendingGames['PendingGames']['GetPendingGamesByPIDResult']['Gamename'].'. TerminalID='.$terminalID. ' ServiceID='.$currentServiceID;
            $this->log($message);
            $message = "Unable to process request. There was a pending game bet.";
            throw new CHttpException(404,$message);  
        }
        
        list($pickCasinoBalance, $currentBet, $dcasinoApiHandler) = $this->getBalance($pickServiceID, $pickServiceType);
        
        // die if can't get balance if pick casino or rtg casino certificate is outdated
        if($pickCasinoBalance == 'N/A' || !is_object($dcasinoApiHandler)) {
            $this->log('[ServiceID: ' . $pickServiceID .'] can\'t get balance');
            throw new CHttpException(404, 'Server is busy please try again. [ServiceID: ' . $pickServiceID .'] can\'t get balance');
            
        // die if pick casino has greater than 0 balance    
        } else if($pickCasinoBalance > 0) {
            $this->log('[ServiceID: ' . $pickServiceID .'] has existing balance');
            throw new CHttpException(404, 'Unable to process request. Next casino has an existing balance. [ServiceID: ' . $pickServiceID .'] has existing balance');   
        }
        
        // die if failed to insert to servicetransferhistory
        if(!LPServiceTransferHistory::model()->insert($currentBalance, $pickServiceID)) {
            $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to servicetransferhistory");
            throw new CHttpException(404, "Unable to process request. Server is Busy. Please try again. [CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to transaction table [0003].");
        }
        
        //get membership card number
        $terminalSessionDetails = LPTerminalSessions::model()->getTerminalDetails(Yii::app()->user->getState('terminalID'));
        $loyaltyCardNo = $terminalSessionDetails['LoyaltyCardNumber'];
        $mid = $terminalSessionDetails['MID'];
        $userMode = $terminalSessionDetails['UserMode'];
        
        $lpTransactionID = '';
        if(strpos($currentServiceType, 'mg') !== false ){
            $lpTransactionID = LPServiceTransferHistory::model()->insertServiceTransRef($currentServiceID, $origin_id = 1);
        }
        
        //get lastinsertid from table servicetransferhistory
        $lastServID = LPServiceTransferHistory::model()->getLastInsertID();
        
        // die if failed to insert to transactionrequestlogslp
        if(!LPTransactionRequestLogsLp::model()->insert($currentBalance, $lastServID, 'W', 
                $currentServiceID, $loyaltyCardNo, $mid, $userMode, $lpTransactionID)) {
            $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to transactionrequestlogslp");
            throw new CHttpException(404, "Unable to process request. Server is Busy. Please try again. [CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to transaction table [0002].");           
        }
        
        $transRequestLogLPID = LPTransactionRequestLogsLp::model()->getLastInsertID();
        
        // get reference insert id from transactionrequestlogslp
        $referenceID = LPTransactionRequestLogsLp::model()->getReferenceID();
        
        $tracking = array('LP'.$transRequestLogLPID,'W',$this->getTerminalID(),$this->getSiteID());
        
        //get last inserted id from transactionrequestlogslp

        /********************************* WITHDRAW ***************************/
        if(!$this->withdraw($lpTransactionID,$currentServiceID, $currentServiceType, $currentBalance, $tracking, $wcasinoApiHandler, $ptcurrentBet)) {
            $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to withdraw");
            
            //update servicetransferhistory
            LPServiceTransferHistory::model()->update(0, $lastServID);
            
            //update transactionrequestlogslp
            LPTransactionRequestLogsLp::model()->update(2, $this->_casinoTransactionID, $this->_apiActualResult, date('Y-m-d H:i:s'),$referenceID, $transRequestLogLPID);
            
            throw new CHttpException(404, "Unable to process request. Server is Busy. Please try again. [CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to withdraw");
            
        }
        /**************************** END WITHDRAW ****************************/
        
        //update transactionrequestlogslp
        LPTransactionRequestLogsLp::model()->update(1, $this->_casinoTransactionID, $this->_apiActualResult, date('Y-m-d H:i:s'), $referenceID, $transRequestLogLPID);
        
        $serviceInfo = LPRefServices::model()->getServiceInfoWithType($pickServiceID);
        if(strpos($serviceInfo['type'], 'mg') !== false ){
            $lpTransactionID = LPServiceTransferHistory::model()->insertServiceTransRef($pickServiceID, 1);
        }
        
        if(!LPTransactionRequestLogsLp::model()->insert($currentBalance, $lastServID, 'D', 
                $pickServiceID, $loyaltyCardNo, $mid, $userMode,$lpTransactionID)) {
            $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to transactionrequestlogslp for deposit"); 
            throw new CHttpException(404, "Unable to process request. Server is Busy. Please try again. [CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to transaction table for deposit [0002].");
            
        }
        
        $transRequestLogLPID = LPTransactionRequestLogsLp::model()->getLastInsertID();
        
        // get last insert id from transactionrequestlogslp
        $referenceID = LPTransactionRequestLogsLp::model()->getReferenceID();
        
        $tracking = array('LP'.$transRequestLogLPID,'D',$this->getTerminalID(),$this->getSiteID());
        
        $terminalPassword = LPTerminalSessions::model()->getTerminalPassword($this->getTerminalID(), 
                                $pickServiceID);
        
        Yii::app()->user->setState('terminalPwd', $terminalPassword['ServicePassword']); // get current password by current service ID
        Yii::app()->user->setState('encryptPwd', $terminalPassword['HashedServicePassword']); //get encrypted password by current service ID
        
        /***************************** DEPOSIT ********************************/
        if(!$this->deposit($lpTransactionID,$pickServiceID, $pickServiceType, $currentBalance, $tracking, $dcasinoApiHandler)) {
            
            LPTransactionRequestLogsLp::model()->update(2, $this->_casinoTransactionID, $this->_apiActualResult, date('Y-m-d H:i:s'), $referenceID, $transRequestLogLPID);
            
            $serviceInfo = LPRefServices::model()->getServiceInfoWithType($currentServiceID);
            if(strpos($serviceInfo['type'], 'mg') !== false ){
                $lpTransactionID = LPServiceTransferHistory::model()->insertServiceTransRef($currentServiceID, $origin_id = 1);
            }
            
            if(!LPTransactionRequestLogsLp::model()->insert($currentBalance, $lastServID, 'RD', 
                    $currentServiceID, $loyaltyCardNo, $mid, $userMode, $lpTransactionID)) { 
                $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to transactionrequestlogslp for redeposit");
                throw new CHttpException(404, "Unable to process request. Server is Busy. Please try again. [CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to insert to transaction table for redeposit [0002].");
                
            }
            
            $transRequestLogLPID = LPTransactionRequestLogsLp::model()->getLastInsertID();
            
            $referenceID = LPTransactionRequestLogsLp::model()->getReferenceID();
            
            $terminalPassword = LPTerminalSessions::model()->getTerminalPassword($this->getTerminalID(), 
                                $currentServiceID);
        
            Yii::app()->user->setState('terminalPwd', $terminalPassword['ServicePassword']); // get current password by current service ID
            Yii::app()->user->setState('encryptPwd', $terminalPassword['HashedServicePassword']); //get encrypted password by current service ID
            
            $tracking = array('LP'.$transRequestLogLPID,'RD',$this->getTerminalID(),$this->getSiteID());
            
            /************************** REDEPOSIT *****************************/
            if(!$this->deposit($lpTransactionID,$currentServiceID, $currentServiceType, $currentBalance, $tracking, $wcasinoApiHandler)) {
                if(!LPTransactionRequestLogsLp::model()->update(0, $this->_casinoTransactionID, $this->_apiActualResult, date('Y-m-d H:i:s'), $referenceID, $transRequestLogLPID))
                    $this->log ("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to update to transactionrequestlogslp for redeposit");
            
                if(!LPServiceTransferHistory::model()->update(0, $lastServID)) {
                    $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to update to servicetransferhistory for redeposit");
                }
            } else {
                if(!LPTransactionRequestLogsLp::model()->update(1, $this->_casinoTransactionID, $this->_apiActualResult, date('Y-m-d H:i:s'), $referenceID, $transRequestLogLPID)) {
                    $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to update to transactionrequestlogslp for redeposit");
                }
                $terminalPassword = LPTerminalSessions::model()->getTerminalPassword($this->getTerminalID(), 
                                $currentServiceID);
                
                Yii::app()->user->setState('terminalPwd', $terminalPassword['ServicePassword']); // get current password by current service ID
                Yii::app()->user->setState('encryptPwd', $terminalPassword['HashedServicePassword']); //get current encrypted password by current service ID
            }
            /************************ END REDEPOSIT ***************************/
            
            throw new CHttpException(404, 'Unable to process request. Server is Busy. Please try again.');
        }
        /*************************** END DEPOSIT ******************************/
        
        if(!LPServiceTransferHistory::model()->update(1, $lastServID)) {
            $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to update to servicetransferhistory for deposit");
        }
        
        if(!LPTransactionRequestLogsLp::model()->update(1, $this->_casinoTransactionID, $this->_apiActualResult, date('Y-m-d H:i:s'), $referenceID, $transRequestLogLPID)) {
            $this->log("[CurrentServiceID:$currentServiceID, PickServiceID:$pickServiceID] failed to update to transactionrequestlogslp for deposit");
        }
        
        LPTerminalSessions::model()->updateCurrentServiceID($this->getTerminalID(), $pickServiceID);
        $this->setCurrentServiceID($pickServiceID);
        $this->setCurrentServiceType($pickServiceType);
        
        echo CJSON::encode(array('html'=> $this->_getDisplaySuccessTransfer($currentBalance, $currentServiceID, $pickServiceID),'password'=>$terminalPassword['HashedServicePassword']));
        Yii::app()->end();
    }
    
    public function actions()
    {
        return array(
            'checkLogin'=>'application.modules.launchpad.components.actions.CheckLoginAction',
            'getBalance'=>'application.modules.launchpad.components.actions.GetBalanceAction',
            'getcasinoandabalance'=>'application.modules.launchpad.components.actions.GetCasinoAndBalanceAction',
        );
    }
    
    /**
     *
     * @param string $currentBalance
     * @param int $oldServiceID
     * @param int $newServiceID
     * @return string 
     */
    private function _getDisplaySuccessTransfer($currentBalance,$oldServiceID,$newServiceID)
    {
        $oldCasino = LPRefServices::model()->getServiceInfo($oldServiceID);
        $newCasino = LPRefServices::model()->getServiceInfo($newServiceID);
        $terminalPassword = LPTerminalSessions::model()->getTerminalPassword($this->getTerminalID(), 
                                $newServiceID);
        
        Yii::app()->user->setState('terminalPwd', $terminalPassword['ServicePassword']); // get current password by current service ID
        Yii::app()->user->setState('encryptPwd', $terminalPassword['HashedServicePassword']); 
        $currentBalance=str_replace(',', '', $currentBalance);
        $html = '<div class="textCenter">You have successfully transferred</div>';
        $html.= '<div class="textCenter">the amount of Php ' . number_format($currentBalance,2) . ' to</div>';
        $html.='<div class="textCenter">' . $newCasino['Alias'] . '</div>';
        return $html;
    }
    
    /**
     *
     * @param string $message 
     */
    protected function log($message) 
    {
        Yii::log( '[HTTP_REFERER='.$_SERVER['HTTP_REFERER'].'] '.'[TerminalID='.Yii::app()->user->getState('terminalID') . ' TerminalCode='.Yii::app()->user->getState('terminalCode').'] '.$message, 'error', 'launchpad.controllers.LobbyController');
    }    
    
    /**
     * Get terminal ID. This was set in launchpad.components.actions.CheckLoginAction
     * @return int 
     */
    protected function getTerminalID()
    {
        return Yii::app()->user->getState('terminalID');
    }
    
    /**
     * 
     * @return string 
     */
    protected function getTerminalCode()
    {
        return Yii::app()->user->getState('terminalCode');
    }
    
    /**
     *
     * @return int 
     */
    protected function getSiteID()
    {
        return Yii::app()->user->getState('siteID');
    }
    
    protected function getCurrentServiceID()
    {
        try {
            $row=LPTerminalSessions::model()->getCurrentCasino($this->getTerminalCode());
        }catch(Exception $e) {
            if(!isset($row['ServiceID']))
                return false;
        }
        Yii::app()->user->setState('currServiceID',$row['ServiceID']);
        return Yii::app()->user->getState('currServiceID');
    }
    
    /**
     *
     * @param int $serviceID 
     */
    protected function setCurrentServiceID($serviceID)
    {
        Yii::app()->user->setState('currServiceID',$serviceID);
    }
    
    /**
     * Get casino balance
     * @param int $serviceID
     * @param string $serviceType
     * @return array 
     */
    protected function getBalance($serviceID,$serviceType)
    {
        $casinoApi = new CasinoApi();
        
        if($this->isUserBased() == 0)
            $login = $this->getTerminalCode();
        if($this->isUserBased() == 1)
            $login = $this->getUBLogin();
        
        $getBalanceResult = $casinoApi->getBalance($login, $serviceID, $serviceType);
        
        LPDB::app()->setActive(true);
        if(!is_array($getBalanceResult)) {
            return $balance = 'N/A';
        }
        
        $currentBet = $getBalanceResult['CurrentBet'];
        $balance = number_format($getBalanceResult['TerminalBalance'], 2);
        
        return array($balance, $currentBet, $getBalanceResult['CasinoAPIHandler']);
    }
    
    /**
     * Call Withdraw Transfer
     * @param int $serviceID
     * @param string $serviceType
     * @param int|string $amount
     * @param array $tracking
     * @param object $casinoApiHandler
     * @return bool  
     */
    protected function withdraw($lpTransactionID, $serviceID,$serviceType,$amount, array $tracking, $casinoApiHandler, $currentbet = 0)
    {
        
        $status = false;
        $apiresult = 'false';
        $transrefid = '';
        
        if($this->isUserBased() == 0){
            $login = $this->getTerminalCode();
            $plainPassword = $this->getCurrentTerminalPassword();
        }
        if($this->isUserBased() == 1){
            $login = $this->getUBLogin();
            $plainPassword = getUBPlainPwd();
        }
        
        switch($serviceType) {
            case 'rtg':
                LPDB::app()->setActive(false);
                
                $eventid = '';
                
                $withdrawResult = $casinoApiHandler->Withdraw($login, $amount, $tracking[0], $tracking[1], 
                              $tracking[2], $tracking[3], $plainPassword, 
                              $eventid, $lpTransactionID);
                
                $status = $withdrawResult['IsSucceed'];
                
                if(isset($withdrawResult['TransactionInfo']['WithdrawGenericResult'])) {
                    $transrefid = $withdrawResult['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                    $apiresult = $withdrawResult['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                }
                
                break;
            case 'mg':
                LPDB::app()->setActive(false);
                $origin_id = 1; //launchpad
                $eventid = LPConfig::app()->params['mg_config']['mgcapi_event_id'][1];
                
                
                // die if failed to insert to servicetransferref
                if(!$lpTransactionID) {
                    $this->log("[CurrentServiceID:$serviceID, OriginID:$origin_id] failed to insert to transaction table[0001]"); 
                    throw new CHttpException(404, "Unable to process request. Server is Busy. Please try again. 
                                [CurrentServiceID:$serviceID, OriginID:$origin_id] failed to insert to transaction table[0001]");
                }
                
                $withdrawResult = $casinoApiHandler->Withdraw($login, Lib::moneyToDecimal($amount), 
                              $tracking[0], $tracking[1], $tracking[2], $tracking[3], $plainPassword, 
                              $eventid, $lpTransactionID);
                
                $status = $withdrawResult['IsSucceed'];
                
                if(isset($withdrawResult['TransactionInfo']['MG'])){
                    $transrefid = $withdrawResult['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $withdrawResult['TransactionInfo']['MG']['TransactionStatus'];
                }
                
                break;
            case 'pt':
                LPDB::app()->setActive(false);
                
                $eventid = '';
                
                $kickPlayerResult = $casinoApiHandler->KickPlayer($login);
                
                $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($login, 1);
                
//                if($currentbet > 0){
//                    $casinoAPI = new CasinoApi();
//                    $revertbrokengames = $casinoAPI->RevertBrokenGamesAPI($this->getTerminalID(), $serviceID, $login);
//                    if($revertbrokengames['RevertBrokenGamesReponse'][0] == false){
//                        $this->log('[ServiceID: ' . $this->getCurrentServiceID() .'] Unable to revert bet on hand.');
//                        throw new CHttpException(404, 'Unable to revert bet on hand.');
//                    }
//                }
                
                $withdrawResult = $casinoApiHandler->Withdraw($this->getTerminalCode(), Lib::moneyToDecimal($amount), $tracking[0], $tracking[1], 
                              $tracking[2], $tracking[3], $this->getCurrentTerminalPassword(), 
                              $eventid, $lpTransactionID);
                
                $status = $withdrawResult['IsSucceed'];
               
                if(isset($withdrawResult['TransactionInfo']['PT'])) {
                    $transrefid = $withdrawResult['TransactionInfo']['PT']['TransactionId'];
                    $apiresult = $withdrawResult['TransactionInfo']['PT']['TransactionStatus'];
                } 
               
                break;
        }
        
        $this->_apiActualResult = $apiresult;
        $this->_casinoTransactionID = $transrefid;
        
        if(!is_null($withdrawResult['ErrorMessage'])){
           $this->log("API Error : Casino : ".$serviceType. " ; ErrorMessage : ".$withdrawResult['ErrorMessage']." ".
                      " ; ErrorCode : ".$withdrawResult['ErrorCode']);
        }

        LPDB::app()->setActive(true);
        return $status;
    }
    
    /**
     * Call Deposit Method Transfer
     * @param int $serviceID
     * @param string $serviceType
     * @param int|string $amount
     * @param array $tracking
     * @param object $casinoApiHandler
     * @return bool 
     */
    protected function deposit($lpTransactionID, $serviceID,$serviceType,$amount, array $tracking, $casinoApiHandler) {
        $status = false;
        $apiresult = 'false';
        $transrefid = '';
        
        if($this->isUserBased() == 0){
            $login = $this->getTerminalCode();
            $plainPassword = $this->getCurrentTerminalPassword();
        }
        if($this->isUserBased() == 1){
            $login = $this->getUBLogin();
            $plainPassword = getUBPlainPwd();
        }
        
        switch($serviceType) {
            case 'rtg':
                LPDB::app()->setActive(false);
                $eventid = '';
                
                $depositResult = $casinoApiHandler->Deposit($login, $amount, 
                                                    $tracking[0], $tracking[1], 
                                                    $tracking[2], $tracking[3], $plainPassword, 
                                                    $eventid, $lpTransactionID);
                
                $status = $depositResult['IsSucceed'];
                
                if(isset($depositResult['TransactionInfo']['DepositGenericResult'])) {
                    $transrefid = $depositResult['TransactionInfo']['DepositGenericResult']['transactionID'];
                    $apiresult = $depositResult['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                    $apierrmsg = $depositResult['TransactionInfo']['DepositGenericResult']['errorMsg'];
                }
                
                break;
            case 'mg':
                LPDB::app()->setActive(false);
                $origin_id = 1; //launchpad
                
                $eventid = LPConfig::app()->params['mg_config']['mgcapi_event_id'][0];
                
                // die if failed to insert to servicetransferref
                if(!$lpTransactionID) {
                    $this->log("[CurrentServiceID:$serviceID, OriginID:$origin_id] failed to insert to servicetransactionref");
                    throw new CHttpException(404, "Unable to process request. Server is Busy. Please try again. [CurrentServiceID:$serviceID, OriginID:$origin_id] failed to insert to transaction table[0001]");
                    
                }
                
                $depositResult = $casinoApiHandler->Deposit($this->getTerminalCode(), Lib::moneyToDecimal($amount), 
                                                    $tracking[0], $tracking[1], 
                                                    $tracking[2], $tracking[3], $this->getCurrentTerminalPassword(), 
                                                    $eventid, $lpTransactionID);
                
                $status = $depositResult['IsSucceed'];
                
                if(isset($depositResult['TransactionInfo']['MG'])) {
                    $transrefid = $depositResult['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $depositResult['TransactionInfo']['MG']['TransactionStatus'];
                    $apierrmsg = $depositResult['ErrorMessage'];
                } 
                
                break;
           case 'pt':
               LPDB::app()->setActive(false);
               
               $eventid = '';
               
               //unfreeze the PT account
               $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($this->getTerminalCode(), 0);
               
               
               $depositResult = $casinoApiHandler->Deposit($this->getTerminalCode(), Lib::moneyToDecimal($amount), 
                                                    $tracking[0], $tracking[1], 
                                                    $tracking[2], $tracking[3], $this->getCurrentTerminalPassword(), 
                                                    $eventid, $lpTransactionID);
               
               $status = $depositResult['IsSucceed'];
               
               if(isset($depositResult['TransactionInfo']['PT'])) {
                    $transrefid = $depositResult['TransactionInfo']['PT']['TransactionId'];
                    $apiresult = $depositResult['TransactionInfo']['PT']['TransactionStatus'];
               } 
               
               break;
        }
        
        $this->_apiActualResult = $apiresult;
        $this->_casinoTransactionID = $transrefid;
                
        if(!is_null($depositResult['ErrorMessage'])){
           $this->log("API Error : Casino : ".$serviceType. " ; ErrorMessage : ".$depositResult['ErrorMessage']." ".
                      " ; ErrorCode : ".$depositResult['ErrorCode']);
        }
        
        LPDB::app()->setActive(true);
        return $status;
    }

    /**
     *
     * @param string $type 
     */
    protected function setCurrentServiceType($type)
    {
        Yii::app()->user->setState('serviceType',$type);
    }
    
    protected function getCurrentServiceType()
    {
        return Yii::app()->user->getState('serviceType');
    }
    
    protected function getCurrentTerminalPassword()
    {
        return Yii::app()->user->getState('terminalPwd');
    }
    
    protected function getUBLogin(){
        return Yii::app()->user->getState('UBUsername');
    }
    
    protected function getUBPlainPwd(){
        return Yii::app()->user->getState('UBPlainPwd');
    }
    
    protected function getUBHashedPwd(){
        return Yii::app()->user->getState('UBHashedPwd');
    }
    
    protected function isUserBased(){
        return Yii::app()->user->getState('casinoMode');
    }
}
