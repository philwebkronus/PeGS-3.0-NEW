<?php

Yii::import('application.components.MicrogamingCAPIWrapper');
Yii::import('application.components.RealtimeGamingAPIWrapper');
Yii::import('application.components.CasinoCAPIHandler');
Yii::import('application.components.checkhost');
Yii::import('application.components.common');
Yii::import('application.components.Array2XML');

class CasinoApi2 {

    /**
     * Description: Configure for RTG
     * @param int $terminal_id
     * @param int $serverid
     * @return object $_CasinoAPIHandler
     */
    public function configureRTG($terminal_id, $serverid) {
        if (strpos(Yii::app()->params['service_api'][$serverid - 1], 'ECFTEST') !== false) {
            Yii::app()->params['deposit_method_id'] = 502;
            Yii::app()->params['withdrawal_method_id'] = 503;
        }

        $configuration = array('URI' => Yii::app()->params['service_api'][$serverid - 1],
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'certFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/cert.pem',
            'keyFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/key.pem',
            'depositMethodId' => Yii::app()->params['deposit_method_id'],
            'withdrawalMethodId' => Yii::app()->params['withdrawal_method_id']);

        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::RTG, $configuration);

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK()) {
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
    public function configureMg($terminal_id, $serverid) {
        $_MGCredentials = Yii::app()->params['service_api'][$serverid - 1];
        list($mgurl, $mgserverID) = $_MGCredentials;
        $configuration = array('URI' => $mgurl,
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'authLogin' => Yii::app()->params['mgcapi_username'],
            'authPassword' => Yii::app()->params['mgcapi_password'],
            'playerName' => Yii::app()->params['mgcapi_playername'],
            'serverID' => $mgserverID);

        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::MG, $configuration);

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK()) {
            return false;
        }
        else
            return $_CasinoAPIHandler;
    }

    /**
     * 
     * @param type $terminal_id
     * @param type $server_id
     * @return CasinoCAPIHandler
     */
    public function configurePT($terminal_id, $server_id, $isRevert = 0) {
        if ($isRevert == 0) {
            $url = Yii::app()->params['service_api'][$server_id - 1];
            $configuration = array('URI' => $url,
                'isCaching' => FALSE,
                'isDebug' => TRUE,
                'pt_casino_name' => Yii::app()->params['pt_casino_name'],
                'pt_secret_key' => Yii::app()->params['pt_secret_key']
            );
            $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::PT, $configuration);
        } else {
            $url = Yii::app()->params['revertbroken_api']['URI'];
            $configuration = array('URI' => '',
                'URI_RBAPI' => $url,
                'isCaching' => FALSE,
                'isDebug' => TRUE,
                'REVERT_BROKEN_GAME_MODE' => Yii::app()->params['revertbroken_api']['REVERT_BROKEN_GAME_MODE'],
                'CASINO_NAME' => Yii::app()->params['revertbroken_api']['CASINO_NAME'],
                'PLAYER_MODE' => Yii::app()->params['revertbroken_api']['PLAYER_MODE'],
                'certFilePath' => Yii::app()->params['pt_cert_dir'] . $server_id . '/cert.pem',
                'keyFilePath' => Yii::app()->params['pt_cert_dir'] . $server_id . '/key.pem'
            );

            $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::PT, $configuration);
        }

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'Can\'t connect to PT';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $server_id);
            self::throwError($message);
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
    public function getBalance($terminal_id, $service_id) {
        Yii::import('application.models.TerminalSessionsModel');
        Yii::import('application.models.TerminalsModel');
        Yii::import('application.models.RefServicesModel');

        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $terminalsModel = new TerminalsModel();
        $refServicesModel = new RefServicesModel();

        // get service id
        if (!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);

        // get terminalname or terminal code
        $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        $service_name = $refServicesModel->getServiceNameById($service_id);

        if (strpos($service_name, 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if (strpos($service_name, 'MG') !== false) {
            $service_name = 'MG';
        }

        if (strpos($service_name, 'PT') !== false) {
            $service_name = 'PT';
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
                    $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
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
                    $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                    return $balanceinfo;
                }
                break;
        }
    }
    
    public function multiexplode ($delimiters,$string) {
   
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
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

        if (strpos($service_name, 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if (strpos($service_name, 'MG') !== false) {
            $service_name = 'MG';
        }

        if (strpos($service_name, 'PT') !== false) {
            $service_name = 'PT';
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

    public function getEgmBalance($terminal_id, $service_id) {
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

        if (strpos($service_name, 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if (strpos($service_name, 'MG') !== false) {
            $service_name = 'MG';
        }

        $balanceinfo = array();
        switch ($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                break;
            case 'MG':
                $casinoApiHandler = $this->configureMg($terminal_id, $service_id);
                Yii::app()->db->setActive(false);
                $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                break;
            case 'Rockin\' Reno':
                // TODO
                break;
        }


        if (isset($balanceinfo['BalanceInfo']['Balance'])) {
            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if (isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }

            //return array($terminal_balance,$service_name,$redeemable_amount,$casinoApiHandler);
            $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
            return $terminal_balance;
        } else {
            $this->log("ErrorCode: " . $balanceinfo['ErrorCode'] . " ErrorMessage: " . $balanceinfo['ErrorMessage']);
            return 'Casino: Can\'t get balance';
        }
    }

    /**
     * Description: end the program and send a message with a header of 404
     */
    public static function throwError($message) {
        header('HTTP/1.0 404 Not Found');

        $this->_sendResponse(200, CJSON::encode(array('DoTransaction' => (array('ErrorMessage' => $message)))));
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

    protected function log($message) {
        Yii::log($message, 'error', 'egm.components.CasinoApi');
    }

}
