<?php

Yii::import('application.components.MicrogamingCAPIWrapper');
Yii::import('application.components.RealtimeGamingAPIWrapper');
Yii::import('application.components.RealtimeGamingUBAPIWrapper');
Yii::import('application.components.PlayTechAPIWrapper');
Yii::import('application.components.CasinoCAPIHandlerUB');
Yii::import('application.components.checkhost');
Yii::import('application.components.common');
Yii::import('application.components.Array2XML');


class CasinoApiUB {
    /**
     * Description: Configure for RTG
     * @param int $terminal_id
     * @param int $serverid
     * @return object $_CasinoAPIHandler
     */
    public function configureRTG($terminal_id,$serverid,$APIType = 0) {
        if(strpos(Yii::app()->params['service_api'][$serverid - 1], 'ECFTEST') !== false) {
            Yii::app()->params['deposit_method_id'] = 502;
            Yii::app()->params['withdrawal_method_id'] = 503;
        }        
        
        $configuration = array( 'URI' =>Yii::app()->params['service_api'][$serverid - 1],
            'URI_PID' =>Yii::app()->params['game_api'][$serverid - 1],
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'certFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/cert.pem',
            'keyFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/key.pem',
            'depositMethodId' => Yii::app()->params['deposit_method_id'],
            'withdrawalMethodId' => Yii::app()->params['withdrawal_method_id'],
            'APIType' => $APIType);

        $_CasinoAPIHandler = new CasinoCAPIHandlerUB( CasinoCAPIHandlerUB::RTG, $configuration );
        
         // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            return false;
        }
        else
            return $_CasinoAPIHandler;
    }
    
     /**
     * Description: Configure for RTG
     * @param int $terminal_id
     * @param int $serverid
     * @return object $_CasinoAPIHandler
     */
    public function configureRTG2($terminal_id,$serverid,$APIType = 0) {
        if(strpos(Yii::app()->params['service_api'][$serverid - 1], 'ECFTEST') !== false) {
            Yii::app()->params['deposit_method_id'] = 502;
            Yii::app()->params['withdrawal_method_id'] = 503;
        } 
        $configuration = array( 'URI' =>Yii::app()->params['service_api'][$serverid - 1],
            'URI_PID' =>Yii::app()->params['game_api'][$serverid - 1],
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'certFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/cert.pem',
            'keyFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/key.pem',
            'depositMethodId' => Yii::app()->params['deposit_method_id'],
            'withdrawalMethodId' => Yii::app()->params['withdrawal_method_id'],
            'APIType' => $APIType);

        $_CasinoAPIHandler = new CasinoCAPIHandlerUB( CasinoCAPIHandlerUB::RTG2, $configuration );
        
         // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            return false;
        }
        else
            return $_CasinoAPIHandler;
    }
    
    /**
     * Description: Configuration for MGCAPI
     * @param int $terminal_id
     * @param int $serverid
     * @return array array(object $_CasinoAPIHandler, string $mgaccount) 
     */
    public function configureMg($terminal_id, $serverid)
    {
        $_MGCredentials = Yii::app()->params['service_api'][$serverid - 1];
        list($mgurl, $mgserverID) =  $_MGCredentials;
        $configuration = array('URI' => $mgurl,
                               'isCaching' => FALSE,
                               'isDebug' => TRUE,
                               'authLogin'=>  Yii::app()->params['mgcapi_username'],
                               'authPassword'=>Yii::app()->params['mgcapi_password'],
                               'playerName'=>Yii::app()->params['mgcapi_playername'],
                               'serverID'=>$mgserverID);
        
        $_CasinoAPIHandler = new CasinoCAPIHandlerUB(CasinoCAPIHandlerUB::MG, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            return false;
        }
        else
            return $_CasinoAPIHandler;
    }
    
    
    /**
     * 
     * @param type $terminal_id
     * @param type $server_id
     * @return CasinoCAPIHandlerUB
     */
    public function configurePT($terminal_id, $server_id, $isRevert = 0){
        if($isRevert == 0){
                $url = Yii::app()->params['service_api'][$server_id -1];
                $configuration = array('URI'=>$url,
                                       'isCaching'=>FALSE,
                                       'isDebug'=>TRUE,
                                       'pt_casino_name'=>  Yii::app()->params['pt_casino_name'],
                                       'pt_secret_key'=>  Yii::app()->params['pt_secret_key']
                                      );

                $_CasinoAPIHandler = new CasinoCAPIHandlerUB(CasinoCAPIHandlerUB::PT, $configuration);
                
                // check if connected
                if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
                    return false;
                }
                
        } else {
                $url = Yii::app()->params['revertbroken_api']['URI'];
                $configuration = array('URI'=>'',
                                       'URI_RBAPI'=>$url,
                                       'isCaching'=>FALSE,
                                       'isDebug'=>TRUE,
                                       'REVERT_BROKEN_GAME_MODE' => Yii::app()->params['revertbroken_api']['REVERT_BROKEN_GAME_MODE'],
                                       'CASINO_NAME' => Yii::app()->params['revertbroken_api']['CASINO_NAME'],
                                       'PLAYER_MODE' => Yii::app()->params['revertbroken_api']['PLAYER_MODE'],
                                       'certFilePath' => Yii::app()->params['pt_cert_dir'].$server_id.'/cert.pem',
                                       'keyFilePath' => Yii::app()->params['pt_cert_dir'].$server_id.'/key.pem' 
                                      );

                $_CasinoAPIHandler = new CasinoCAPIHandlerUB(CasinoCAPIHandlerUB::PT, $configuration);
                
                
                // check if connected
                if (!(bool)$_CasinoAPIHandler->IsAPIServerOK2()) {
                    return false;
                }
        }
      
        
        return $_CasinoAPIHandler;
        
    }
    
    /**
     * Description: Get real balance
     * @param int $terminal_id
     * @param int $site_id
     * @param string $transtype
     * @param int $service_id
     * @return array  array($terminal_balance,$service_name,$terminalSessionsModel,$transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount)
     */
    public function getBalance($terminal_id, $site_id,$transtype='D',$service_id=null,$acct_id=null) {
        Yii::import('application.models.TerminalSessionsModel');
        Yii::import('application.models.TerminalsModel');
        //Yii::import('application.models.TransactionSummaryModel');
        Yii::import('application.models.RefServicesModel');
        //Yii::import('application.models.TransactionRequestLogsModel');
        //Yii::import('application.models.TransactionDetailsModel');
        
        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $terminalsModel = new TerminalsModel();
        $refServicesModel = new RefServicesModel();
        $transReqLogsModel = new TransactionRequestLogsModel();
        $transactionDetailsModel = new TransactionDetailsModel();
        $transactionSummaryModel = new TransactionSummaryModel();
        
        $mgaccount = '';
        
        // get service id
        if(!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);
        
        // get terminalname or terminal code
        $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        $service_name = $refServicesModel->getServiceNameById($service_id);
        
        if(strpos($service_name, 'RTG') !== false) {
            $service_name = 'RTG';
        }

       if(strpos($service_name, 'MG') !== false){
           $service_name = 'MG';
       }
       
       if(strpos($service_name, 'PT') !== false){
           $service_name = 'PT';
       }
        //var_dump($service_name);exit;
        switch($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id,$service_id);
                Yii::app()->db->setActive(false);
                if(!$casinoApiHandler){
                    $message = 'Can\'t connect to RTG';
                    $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return $message;
                }
                else
                   $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
            break;
            case 'MG':
                $casinoApiHandler = $this->configureMg($terminal_id,$service_id);
                Yii::app()->db->setActive(false);
                if(!$casinoApiHandler){
                    $message = 'Can\'t connect to MG';
                    $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return $message;
                }
                else
                    $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                break;
            case 'PT':
                $casinoApiHandler = $this->configurePT($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                if(!$casinoApiHandler){
                    $message = 'Can\'t connect to PT';
                    $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return $message;
                }
                else
                    $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                break;
        }
              
        
        if(isset($balanceinfo['BalanceInfo']['Balance'])) {
            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if(isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }

            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if(isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }

            // delete terminal session if balance if zero
//            if($terminal_balance == 0 && $transtype != 'R' && $transtype != 'D') {
//                $udate = CasinoApi::udate('YmdHisu');
//                $transReqLogsModel->insertDueToZeroBalance($udate, 0, 'W', $terminal_id, $site_id, $service_id);
//                $trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($terminal_id, $site_id);
//                $transactionSummaryModel->updateRedeem($trans_summary_id, 0);
//                $transactionDetailsModel->insert($udate, $trans_summary_id, $site_id, 
//                        $terminal_id, 'W', 0, $service_id, $acct_id, '1');
//
//                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
//                $transReqLogsModel->updateTransReqLogDueZeroBal($terminal_id, $site_id, $transtype);
//            }

            $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
            return array($terminal_balance,$service_name,$terminalSessionsModel,$transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount);
        }
        else{
            //$this->log("ErrorCode: ".$balanceinfo['ErrorCode']." ErrorMessage: ".$balanceinfo['ErrorMessage']);
            return 'Casino: Can\'t get balance';
        }
    }
    
    
    /**
     * Get Balance method for user-based
     * Purpose : to separate logic from terminal based for future changes
     */
    public function getBalanceUB($terminal_id, $site_id, $transtype='D', $service_id = '', $acct_id = '', 
            $casinoUsername= ' ', $casinoPassword = '', $casinoHashedPwd = ''){
        Yii::import('application.models.TerminalSessionsModel');
        Yii::import('application.models.TerminalsModel');
        Yii::import('application.models.RefServicesModel');
        Yii::import('application.models.TransactionRequestLogsModel');
        
        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $refServicesModel = new RefServicesModel();
        $transReqLogsModel = new TransactionRequestLogsModel();
        
        $mgaccount = '';
        
        // get service id
        if(!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);
        
        //verify if terminal has an active session
        if($transtype == 'R' || $transtype == 'W'){
            $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);

            if($is_terminal_active === false) {
                $message = 'Error: Can\'t get status.';
                $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return $message;
            }

            if($is_terminal_active < 1) {
                $message = 'Error: Terminal has no active session.';
                $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return $message;
            }
        }
        
        $service_name   = $refServicesModel->getServiceNameById($service_id);
        //get service group
        $service_group  = $refServicesModel->getServiceGrpNameById($service_id);
        
        if(strpos($service_group, 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if(strpos($service_group, 'MG') !== false){
           $service_name = 'MG';
        }
        
        if(strpos($service_group, 'PT') !== false){
            $service_name = 'PT';
        }
        
        if(strpos($service_group, 'RTG2') !== false){
            $service_name = 'RTG2';
        }
        switch($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id,$service_id);
                Yii::app()->db->setActive(false);
                if(!$casinoApiHandler){
                    $message = 'Can\'t connect to RTG';
                    $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return $message;
                }
                else
                $balanceinfo = $casinoApiHandler->GetBalance($casinoUsername);
                break;
            case 'MG':
                $casinoApiHandler = $this->configureMg($terminal_id,$service_id);
                Yii::app()->db->setActive(false);
                if(!$casinoApiHandler){
                    $message = 'Can\'t connect to MG';
                    $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return $message;
                }
                else
                $balanceinfo = $casinoApiHandler->GetBalance($casinoUsername);
                break;
            case 'PT':
                $casinoApiHandler = $this->configurePT($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                if(!$casinoApiHandler){
                    $message = 'Can\'t connect to PT';
                    $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return $message;
                }
                else
                $balanceinfo = $casinoApiHandler->GetBalance($casinoUsername);
                break;
            case 'RTG2':
                $casinoApiHandler = $this->configureRTG2($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                if(!$casinoApiHandler){
                    $message = 'Can\'t connect to PT';
                    $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return $message;
                }
                else
                {
                    $balanceinfo = $casinoApiHandler->GetBalance($casinoUsername);
                }
                break;
        }
        if(!isset($balanceinfo['BalanceInfo']['Balance'])) {
            return 'Casino: Can\'t get balance';
        }
        else{
            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if(isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }

            $currentbet = 0;
        
            //For PT --> denied redemption if there was a current bet
            if($service_name == 'PT'&& $transtype == 'W') {
                if($balanceinfo['BalanceInfo']['CurrentBet'] > 0) {
                    $currentbet = $balanceinfo['BalanceInfo']['CurrentBet'];
                } else {
                    $currentbet = 0;
                }
            }
        
            $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
            return array($terminal_balance,$service_name,$terminalSessionsModel,$transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount,$currentbet);
    
        }
    }
    
    
    public function getEgmBalance($terminal_id, $service_id){
        Yii::import('application.models.TerminalsModel');
        Yii::import('application.models.RefServicesModel');
        Yii::import('application.models.TerminalSessionsModel');
        
        // instance of model
        $terminalsModel = new TerminalsModel();
        $refServicesModel = new RefServicesModel();
        $terminalSessionsModel = new TerminalSessionsModel();
        
        // get terminalname or terminal code
        $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        $service_name = $refServicesModel->getServiceNameById($service_id);
        
        if(strpos($service_name, 'RTG') !== false) {
            $service_name = 'RTG';
        }

       if(strpos($service_name, 'MG') !== false){
           $service_name = 'MG';
       }
       
        $balanceinfo = array();
        switch($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id,$service_id);
                Yii::app()->db->setActive(false);
                $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
            break;
            case 'MG':
                $casinoApiHandler = $this->configureMg($terminal_id,$service_id);
                Yii::app()->db->setActive(false);
                $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                break;
            case 'Rockin\' Reno':
                // TODO
            break;
        }
        
        
        if(isset($balanceinfo['BalanceInfo']['Balance'])) {
             $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if(isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }

            //return array($terminal_balance,$service_name,$redeemable_amount,$casinoApiHandler);
            $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
            return $terminal_balance;
        }
        else{
            $this->log("ErrorCode: ".$balanceinfo['ErrorCode']." ErrorMessage: ".$balanceinfo['ErrorMessage']);
            return 'Casino: Can\'t get balance';
        }
    }
    
    public function getBalanceUserBased($terminal_id, $service_id, $cardnumber, $return_transfer, $user_mode) {

        Yii::import('application.models.TerminalsModel');
        Yii::import('application.models.RefServicesModel');
        Yii::import('application.components.LoyaltyAPIWrapper');

        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $terminalsModel = new TerminalsModel();
        $refServicesModel = new RefServicesModel();
        $loyaltyAPIWrapper = new LoyaltyAPIWrapper();

        // get service id
        if (!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);

        // get terminalname or terminal code
        $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        $service_name = $refServicesModel->getServiceNameById($service_id);
        //get service group
        $service_group  = $refServicesModel->getServiceGrpNameById($service_id);
        
        if(strpos($service_group, 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if(strpos($service_group, 'MG') !== false){
           $service_name = 'MG';
        }
        
        if(strpos($service_group, 'PT') !== false){
            $service_name = 'PT';
        }
        
        if(strpos($service_group, 'RTG2') !== false){
            $service_name = 'RTG2';
        }

        switch ($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                if (!$casinoApiHandler) {
                    $message = 'Can\'t connect to RTG';
                    $this->log($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                    return $message;
                } else {
                    if($user_mode == 1){
                        $cardInfo = $loyaltyAPIWrapper->getCardInfo($cardnumber, $return_transfer);
                        $service_name = $terminalSessionsModel->getServiceUserName($terminal_id);
                        if(!empty($service_name)){
                            $balanceinfo = $casinoApiHandler->GetBalance($service_name);
                        } else {
                            $balanceinfo = NULL;
                        }
                    } else {
                        $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                    }
                    
                    return $balanceinfo;
                }
                break;
            case 'RTG2':
                $casinoApiHandler = $this->configureRTG2($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                if (!$casinoApiHandler) {
                    $message = 'Can\'t connect to RTG';
                    $this->log($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                    return $message;
                } else {
                    if($user_mode == 1){
                        $cardInfo = $loyaltyAPIWrapper->getCardInfo($cardnumber, $return_transfer);
                        $service_name = $terminalSessionsModel->getServiceUserName($terminal_id);
                        if(!empty($service_name)){
                            $balanceinfo = $casinoApiHandler->GetBalance($service_name);
                        } else {
                            $balanceinfo = NULL;
                        }
                    } else {
                        $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                    }
                    
                    return $balanceinfo;
                }
                break;
            case 'MG':
                $casinoApiHandler = $this->configureMg($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                if (!$casinoApiHandler) {
                    $message = 'Can\'t connect to MG';
                    $this->log($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                    return $message;
                } else {
                    $cardInfo = $loyaltyAPIWrapper->getCardInfo($cardnumber, $return_transfer);
                    $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                    return $balanceinfo;
                }
                break;
            case 'PT':
                $casinoApiHandler = $this->configurePT($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                if (!$casinoApiHandler) {
                    $message = 'Can\'t connect to PT';
                    $this->log($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                    return $message;
                } else {
                    $cardInfo = $loyaltyAPIWrapper->getCardInfo($cardnumber, $return_transfer);
            
                    $service_name = $terminalSessionsModel->getServiceUserName($terminal_id);
                    
                    $balanceinfo = $casinoApiHandler->GetBalance($service_name);
                    
                    return $balanceinfo;
                }
                break;
        }
    }

    /**
     * Description: end the program and send a message with a header of 404
     */
    public static function throwError($message) {
        header('HTTP/1.0 404 Not Found');
        
            $this->_sendResponse(200, CJSON::encode(array('DoTransaction'=>(array('ErrorMessage'=>$message)))));
    }
    
    /**
     * Description: this will return date with milliseconds
     * @param string $format date format to be return
     * @param string $utimestamp
     * @return string date 
     */
    public static function udate($format, $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
    
    
    /**
     * PlayTech : Additional casino rules before session ending
     * @param str $service_name
     * @param str $terminal_name 
     */
    public function _doCasinoRules($terminal_id, $service_id,$username = '')
    {

        Yii::import('application.models.TerminalsModel');
        Yii::import('application.models.RefServicesModel');
        
        if($username == null) {
                $terminalModels = new TerminalsModel();
                $username = $terminalModels->getTerminalName($terminal_id);
        }

        $refservicesmodel = new RefServicesModel();
        $service_name = $refservicesmodel->getServiceNameById($service_id);

        //if PT, freeze and force logout its account
        if(strpos($service_name, 'PT') !== false || strpos($service_name, 'Rockin\' Reno') !== false) {

            $casinoApiHandler = $this->configurePT($terminal_id, $service_id);
            
            $kickPlayerResult = $casinoApiHandler->KickPlayer($username);
            
            $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($username, 1);

            if(!$changeStatusResult['IsSucceed']){
                $message = $changeStatusResult['ErrorMessage'];
                logger($message);
                CasinoApi::throwError($message);
            }

            if(!$kickPlayerResult['IsSucceed']){
                $message = $kickPlayerResult['ErrorMessage'];
                logger($message);
                CasinoApi::throwError($message);
            }
        }
    }
    
    /**
     * Get RTG Pending games
     * @param int $terminal_id
     * @param int $serverid
     * @param str $PID
     * @return obj
     */
    public function GetPendingGames($terminal_id, $serverid, $PID){
        $casinoAPIHandler = $this->configureRTG($terminal_id, $serverid,2);
        $pendingGames = $casinoAPIHandler->GetPendingGames($PID);
        return $pendingGames;
    }
    
    /**
     * Reverts PT Pending games
     * @param int $terminal_id
     * @param int $service_id
     * @param str $username
     * @return type
     */
    public function RevertBrokenGamesAPI($terminal_id,$service_id,$username)
    {
        $isRevert = 1; //0-No, 1-Yes
        $_casinoAPIHandler = $this->configurePT($terminal_id,$service_id, $isRevert);
        $game_mode = Yii::app()->params['revertbroken_api']['REVERT_BROKEN_GAME_MODE'];
        $player_mode = Yii::app()->params['revertbroken_api']['PLAYER_MODE'];
        
        $response = $_casinoAPIHandler->RevertBrokenGamesAPI($username, $player_mode, $game_mode);
        return $response;
    }
    
    /**
     * Call sapi to lock | unlock lp terminal
     * @param int $commandId lock | unlock
     * @param int $terminal_id
     * @param str $login_uname
     * @param str $login_pwd
     * @param int $service_id
     */
    public function callSpyderAPI($commandId, $terminal_id, $login_uname, $login_pwd,
                                     $service_id, $spyder_enabled)
    {
        //if spyder call was enabled in cashier config, call SAPI
        if($spyder_enabled == 1){
            
            Yii::import('application.components.AsynchronousRequest');
            Yii::import('application.models.TerminalsModel');
            Yii::import('application.models.SpyderRequestLogsModel');
            
            $terminalsModel = new TerminalsModel();
            $spyderRequestLogsModel = new SpyderRequestLogsModel(); 
            $asynchronousRequest = new AsynchronousRequest();

            $terminalName = $terminalsModel->getTerminalName($terminal_id);
            $spyder_req_id = $spyderRequestLogsModel->insert($terminalName, $commandId);

            $terminal = substr($terminalName, strlen("ICSA-")); //removes the "icsa-
            $computerName = str_replace("VIP", '', $terminal);

            $params = array('TerminalName'=>$computerName,'CommandID'=>$commandId,
                            'UserName'=>$login_uname,'Password'=>$login_pwd,'Type'=> Yii::app()->params['SAPI_Type'],
                            'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$service_id);
                        
            $asynchronousRequest->curl_request_async(Yii::app()->params['Asynchronous_URI'], $params);
        }
    }
    
    protected function log($message) 
    {
        Yii::log($message, 'error', 'egm.components.CasinoApi');
    }
    
}
