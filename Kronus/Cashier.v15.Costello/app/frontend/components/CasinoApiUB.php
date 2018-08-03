<?php

Mirage::loadComponents(array(
    'MicrogamingCAPIWrapper.class',
    'RealtimeGamingAPIWrapper.class',
    'RealtimeGamingUBAPIWrapper.class',
    'PlayTechAPIWrapper.class',
    'CasinoCAPIHandler.class',
    'CasinoCAPIHandlerUB.class',
    'checkhost.class',
    'common.class',
    'Array2XML.class'));

/**
 * Date Created 11 4, 11 9:41:13 AM <pre />
 * Date Modified May 6, 2013
 * Casino configuration settings, Get Balance and other common api calls
 * @author Bryan Salazar
 * @author Edson Perez <elperez@philweb.com.ph>
 * @version Kronus UB
 */
class CasinoApiUB {

    /**
     * Description: Configure for RTG
     * @param int $terminal_id
     * @param int $serverid
     * @return object $_CasinoAPIHandler
     */
    public function configureRTG($terminal_id, $serverid, $APIType = 0) {

        if (strpos(Mirage::app()->param['service_api'][$serverid - 1], 'ECFTEST') !== false) {
            Mirage::app()->param['deposit_method_id'] = 503; //502
            Mirage::app()->param['withdrawal_method_id'] = 502; //503
        }

        if (strpos(Mirage::app()->param['service_api'][$serverid - 1], 'PHPCOSTELLO') !== false) {
            Mirage::app()->param['deposit_method_id'] = 503; //502
            Mirage::app()->param['withdrawal_method_id'] = 502; //503
        }

        $configuration = array('URI' => Mirage::app()->param['service_api'][$serverid - 1],
            'URI_PID' => Mirage::app()->param['game_api'][$serverid - 1],
            'URI_PID2' => Mirage::app()->param['player_api'][$serverid - 1],
            'URI_PID3' => Mirage::app()->param['WCFPlayerAPI'],
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'certFilePath' => Mirage::app()->param['rtg_cert_dir'] . $serverid . '/cert.pem',
            'keyFilePath' => Mirage::app()->param['rtg_cert_dir'] . $serverid . '/key.pem',
            'certKeyFilePath' => Mirage::app()->param['rtg_cert_dir'] . $serverid . '/cert-key.pem',
            'depositMethodId' => Mirage::app()->param['deposit_method_id'],
            'withdrawalMethodId' => Mirage::app()->param['withdrawal_method_id'],
            'APIType' => $APIType);

        $_CasinoAPIHandler = new CasinoCAPIHandlerUB(CasinoCAPIHandlerUB::RTG, $configuration);

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'Can\'t connect to RTG';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $serverid);
            self::throwError($message);
        }

        return $_CasinoAPIHandler;
    }

    /**
     * Description: Configuration for  Habanero
     * @param int $terminal_id
     * @param int $serverid
     * @param int $APIType
     * @return array array(object $_CasinoAPIHandler, string $mgaccount) 
     */
    public function configureHabanero($terminal_id, $serverid, $APIType = 0) {
        $CVcounter = 0;
        $CPVcounter = 0;
        while ($CVcounter < count(Mirage::app()->param['CasinoVersions'])) {
            $CasinoVersions = Mirage::app()->param['CasinoVersions'][$CVcounter];
            $CPVarray = Mirage::app()->param['CasinoPerVersion'][$CasinoVersions];
            if (in_array($serverid, $CPVarray)) {
                $CPV = $CasinoVersions;
                break;
            } else {
                $CVcounter++;
            }
        }

        $_URL = Mirage::app()->param['player_api'][$serverid - 1];
        $configuration = array('URI' => $_URL,
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'brandID' => Mirage::app()->param['HB_BrandID'],
            'APIkey' => Mirage::app()->param['HB_APIkey'],
            'currencyCode' => Mirage::app()->param['HB_CurrencyCode'],
            'CasinoService' => $CPV,
            'APIType' => $APIType
        );

        $_CasinoAPIHandler = new CasinoCAPIHandlerUB(CasinoCAPIHandlerUB::Habanero, $configuration);

        // check if connected
        if (!(bool) $_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'Can\'t connect to Habanero';
            logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $serverid);
            self::throwError($message);
        }

        return $_CasinoAPIHandler;
    }

    /**
     * Get Balance method for user-based
     * Purpose : to separate logic from terminal based for future changes
     */
    public function getBalanceUB($terminal_id, $site_id, $transtype = 'D', $service_id = '', $acct_id = '', $casinoUsername = ' ', $casinoPassword = '', $casinoHashedPwd = '') {
        Mirage::loadModels(array('TerminalSessionsModel', 'TerminalsModel',
            'RefServicesModel', 'TransactionRequestLogsModel'));

        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $refServicesModel = new RefServicesModel();
        $transReqLogsModel = new TransactionRequestLogsModel();

        $mgaccount = '';

        // get service id
        if (!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);

        //verify if terminal has an active session
        if ($transtype == 'R' || $transtype == 'W') {
            $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);

            if ($is_terminal_active === false) {
                $message = 'Error: Can\'t get status.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }

            if ($is_terminal_active < 1) {
                $message = 'Error: Terminal has no active session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }
        }

        $service_name = $refServicesModel->getServiceNameById($service_id);

        if (strpos(strtoupper($service_name), 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if (strpos(strtoupper($service_name), 'HAB') !== false) {
            $service_name = 'HAB';
        }

        switch ($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id, $service_id);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalance($casinoUsername);
                break;
            /*
             * John Aaron Vida
             * 06/19/2017
             * Added ::Habanero
             */
            case 'HAB':
                $casinoApiHandler = $this->configureHabanero($terminal_id, $service_id, 0);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalanceHabanero($casinoUsername, $casinoPassword);
                break;
        }

        if ($service_name == 'HAB') {
            $terminal_balance = $balanceinfo['TransactionInfo']['RealBalance'];
            $redeemable_amount = $terminal_balance;
        }

        if ($service_name == 'RTG') {
            if (!isset($balanceinfo['BalanceInfo']['Balance'])) {
                $message = 'Error: Can\'t get balance';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id .
                        ' ErrorMessage=' . $balanceinfo['ErrorMessage']);
                self::throwError($message);
            }

            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if (isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }
        }

        $currentbet = 0;

        $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
        return array($terminal_balance, $service_name, $terminalSessionsModel, $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount, $currentbet);
    }

    public function getBalanceForceT($terminal_id, $site_id, $transtype = 'D', $service_id = '', $acct_id = '', $casinoUsername = ' ', $casinoPassword = '', $casinoHashedPwd = '') {
        Mirage::loadModels(array('TerminalSessionsModel', 'TerminalsModel',
            'RefServicesModel', 'TransactionRequestLogsModel'));

        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $refServicesModel = new RefServicesModel();
        $transReqLogsModel = new TransactionRequestLogsModel();


        $service_name = $refServicesModel->getServiceNameById($service_id);

        if (strpos(strtoupper($service_name), 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if (strpos(strtoupper($service_name), 'HAB') !== false) {
            $service_name = 'HAB';
        }

        switch ($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id, $service_id);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalance($casinoUsername);
                break;
            /*
             * John Aaron Vida
             * 06/19/2017
             * Added ::Habanero
             */
            case 'HAB':
                $casinoApiHandler = $this->configureHabanero($terminal_id, $service_id, 0);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalanceHabanero($casinoUsername, $casinoPassword);
                break;
        }

        if ($service_name == 'HAB') {
            $terminal_balance = $balanceinfo['TransactionInfo']['RealBalance'];
            $redeemable_amount = $terminal_balance;
        }

        if ($service_name == 'RTG') {
            if (!isset($balanceinfo['BalanceInfo']['Balance'])) {
                $message = 'Error: Can\'t get balance';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id .
                        ' ErrorMessage=' . $balanceinfo['ErrorMessage']);
                self::throwError($message);
            }

            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if (isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }
        }

        $currentbet = 0;


        return array($terminal_balance, $service_name, $terminalSessionsModel, $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $currentbet);
    }

    /**
     * Description: Get real balance
     * @param int $terminal_id
     * @param int $site_id
     * @param string $transtype
     * @param int $service_id
     * @return array  array($terminal_balance,$service_name,$terminalSessionsModel,$transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount)
     */
    public function getBalance($terminal_id, $site_id, $transtype = 'D', $service_id = '', $acct_id = '') {
        Mirage::loadModels(array('TerminalSessionsModel', 'TerminalsModel',
            'RefServicesModel', 'TransactionRequestLogsModel'));

        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $terminalsModel = new TerminalsModel();
        $refServicesModel = new RefServicesModel();
        $transReqLogsModel = new TransactionRequestLogsModel();

        $mgaccount = '';

        // get service id
        if (!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);

        //verify if terminal has an active session
        if ($transtype == 'R' || $transtype == 'W') {
            $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);

            if ($is_terminal_active === false) {
                $message = 'Error: Can\'t get status.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }

            if ($is_terminal_active < 1) {
                $message = 'Error: Terminal has no active session.';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id);
                CasinoApi::throwError($message);
            }
        }

        // get terminalname or terminal code
        $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        $service_name = $refServicesModel->getServiceGrpNameById($service_id);

        //get terminal password 
        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];

        if (strpos(strtoupper($service_name), 'RTG') !== false) {
            $service_name = 'RTG';
        }

        if (strpos(strtoupper($service_name), 'HAB') !== false) {
            $service_name = 'HAB';
        }

        switch ($service_name) {
            // RTG Magic Macau
            case 'RTG':
                $casinoApiHandler = $this->configureRTG($terminal_id, $service_id);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                break;
            /*
             * John Aaron Vida
             * 06/19/2017
             * Added ::Habanero
             */
            case 'HAB':
                $casinoApiHandler = $this->configureHabanero($terminal_id, $service_id, 0);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalanceHabanero($terminal_name, $terminal_pwd);
                break;
        }

        if ($service_name == 'HAB') {
            $terminal_balance = $balanceinfo['TransactionInfo']['RealBalance'];
            $redeemable_amount = $terminal_balance;
        }

        if ($service_name == 'RTG') {
            if (!isset($balanceinfo['BalanceInfo']['Balance'])) {
                $message = 'Error: Can\'t get balance';
                logger($message . ' TerminalID=' . $terminal_id . ' ServiceID=' . $service_id .
                        ' ErrorMessage=' . $balanceinfo['ErrorMessage']);
                self::throwError($message);
            }

            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if (isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }
        }
        $currentbet = 0;

        $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
        return array($terminal_balance, $service_name, $terminalSessionsModel, $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount, $currentbet);
    }

    /**
     * Description: This get balance use for refresh. It will continue to next terminal to get balance
     *  even the previews casino was failed to get balance
     * @param int $terminal_id
     * @param int $site_id
     * @param int $transtype
     * @param int $service_id
     * @return array array($terminal_balance,$service_name,$terminalSessionsModel,$transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) 
     */
    public function getBalanceContinue($terminal_id, $site_id, $transtype = 'D', $service_id = null, $acct_id = null) {
        Mirage::loadModels(array('TerminalSessionsModel', 'TerminalsModel', 'RefServicesModel',
            'TransactionRequestLogsModel'));

        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $terminalsModel = new TerminalsModel();
        $refServicesModel = new RefServicesModel();
        $transReqLogsModel = new TransactionRequestLogsModel();

        $mgaccount = '';

        // get service id
        if (!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);

        // get terminalname or terminal code
        $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        $service_name = $refServicesModel->getAliasById($service_id);

        //get terminal password 
        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];

        switch ($service_name) {
            // RTG
            case 'Magic Macau':
                $casinoApiHandler = $this->configureRTG($terminal_id, $service_id);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalance($terminal_name);
                break;
            /*
             * John Aaron Vida
             * 09/21/2018
             * Added ::Habanero
             */
            //case 'Habanero':
            case 'Viva Las Vegas':
                $casinoApiHandler = $this->configureHabanero($terminal_id, $service_id, 0);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalanceHabanero($terminal_name, $terminal_pwd);

                break;
        }

        /*
         * John Aaron Vida
         * 09/21/2018
         * Added :: For Habanero
         */
        //if ($service_name == 'habanero') {
        if ($service_name == 'Viva Las Vegas') {
            $terminal_balance = $balanceinfo['TransactionInfo']['RealBalance'];
            $redeemable_amount = $terminal_balance;
        }


        if ($service_name == "Magic Macau") {
            if (!isset($balanceinfo['BalanceInfo']['Balance'])) {
                return false;
            }

            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if (isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }
        }

        // delete terminal session if balance if zero
//        if($terminal_balance == 0 && $transtype != 'R' && $transtype != 'D') {
//            
//            //Get Last Transaction Summary ID
//            $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
//            
//            if(!$trans_summary_id){
//                $message = 'Redeem Session Failed. Please check if the terminal
//                            has a valid start session.';
//                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
//            }
//            
//            $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($terminal_id);
//       
//            foreach ($casinoUBDetails as $val){
//                $mid = $val['MID'];
//                $loyaltyCardNo = $val['LoyaltyCardNumber'];
//                $casinoUserMode = $val['UserMode'];
//            }
//            
//            $this->_doCasinoRules($casinoApiHandler, $service_name, $terminal_name);
//        
//            $udate = CasinoApi::udate('YmdHisu');
//            
//            $paymentType = 1; //always cash upon withdrawal
//            $transRegLogsId = $transReqLogsModel->insert($udate, 0, 'W', $paymentType, $terminal_id, 
//                    $site_id, $service_id, $loyaltyCardNo, $mid, $casinoUserMode);
//            
//            $transactionSummaryModel->updateRedeem($trans_summary_id, 0);
//            
//             $transactionDetailsModel->insert($udate, $trans_summary_id, $site_id, 
//                    $terminal_id, 'W', 0, $service_id, $acct_id, '1', $loyaltyCardNo, $mid);
//            
//            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
//            
//            $transReqLogsModel->updateTransReqLogDueZeroBal($terminal_id, $site_id, $transtype, $transRegLogsId);
//            
//            return false;
//        }

        $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
        return array($terminal_balance, $service_name, $terminalSessionsModel, $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount);
    }

    /**
     * Purpose : to separate logic from terminal based for future changes
     * Description: This get balance use for refresh. It will continue to next terminal to get balance
     *  even the previews casino was failed to get balance
     * @param int $terminal_id
     * @param int $site_id
     * @param int $transtype
     * @param int $service_id
     * @return array array($terminal_balance,$service_name,$terminalSessionsModel,$transReqLogsModel,
     *                    $redeemable_amount,$casinoApiHandler,$mgaccount) 
     */
    public function getUBBalanceContinue($terminal_id, $site_id, $transtype = 'D', $service_id = '', $acct_id = '', $casinoUsername = ' ', $casinoPassword = '') {
        Mirage::loadModels(array('TerminalSessionsModel', 'RefServicesModel',
            'TransactionRequestLogsModel'));

        // instance of model
        $terminalSessionsModel = new TerminalSessionsModel();
        $refServicesModel = new RefServicesModel();
        $transReqLogsModel = new TransactionRequestLogsModel();

        $mgaccount = '';

        // get service id
        if (!$service_id)
            $service_id = $terminalSessionsModel->getServiceId($terminal_id);

        // get terminalname or terminal code
        $service_name = $refServicesModel->getAliasById($service_id);

        switch ($service_name) {
            // RTG
            case 'Magic Macau':
                $casinoApiHandler = $this->configureRTG($terminal_id, $service_id);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalance($casinoUsername);
                break;

            /*
             * John Aaron Vida
             * 09/21/2018
             * Added ::Habanero
             */
            case 'Viva Las Vegas':
                $casinoApiHandler = $this->configureHabanero($terminal_id, $service_id, 0);
                MI_Database::close();
                $balanceinfo = $casinoApiHandler->GetBalanceHabanero($casinoUsername, $casinoPassword);
                break;
        }

        /* John Aaron Vida
         * 09/21/2018
         * Added :: For Habanero
         */
        if ($service_name == 'Viva Las Vegas') {
            $terminal_balance = $balanceinfo['TransactionInfo']['RealBalance'];
            $redeemable_amount = $terminal_balance;
        }

        if ($service_name == "Magic Macau") {
            if (!isset($balanceinfo['BalanceInfo']['Balance'])) {
                return false;
            }

            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if (isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }
        }

        $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
        return array($terminal_balance, $service_name, $terminalSessionsModel, $transReqLogsModel, $redeemable_amount, $casinoApiHandler, $mgaccount);
    }

    /**
     * Description: end the program and send a message with a header of 404
     */
    public static function throwError($message) {
        header('HTTP/1.0 404 Not Found');
        echo $message;
        Mirage::app()->end();
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
    public function _doCasinoRules($terminal_id, $service_id, $username = '') {

        Mirage::loadModels(array('TerminalsModel', 'RefServicesModel'));
        if ($username == null) {
            $terminalModels = new TerminalsModel();
            $username = $terminalModels->getTerminalName($terminal_id);
        }

        $refservicesmodel = new RefServicesModel();
        $service_name = $refservicesmodel->getServiceNameById($service_id);
    }

    /**
     * Get RTG Pending games
     * @param int $terminal_id
     * @param int $serverid
     * @param str $PID
     * @return obj
     */
    public function GetPendingGames($terminal_id, $serverid, $PID) {
        $casinoAPIHandler = $this->configureRTG($terminal_id, $serverid, 2);
        $pendingGames = $casinoAPIHandler->GetPendingGames($PID);
        return $pendingGames;
    }

    /**
     * Get Habanero Pending games
     * @param str terminal_name
     * @return obj
     */
    public function GetPendingGamesHabanero($terminal_id, $serverid, $casinoUsername, $casinoPassword) {
        $casinoAPIHandler = $this->configureHabanero($terminal_id, $serverid, 0);
        $pendingGames = $casinoAPIHandler->GetPendingGamesHabanero($casinoUsername, $casinoPassword);
        return $pendingGames;
    }

    /**
     * Logout RTG Player
     * @param int $terminal_id
     * @param int $serverid
     * @param str $PID
     * @return obj
     */
    public function LogoutPlayer($terminal_id, $serverid, $PID) {
        $casinoAPIHandler = $this->configureRTG($terminal_id, $serverid, 3);
        $pendingGames = $casinoAPIHandler->LogoutPlayer($PID);
        return $pendingGames;
    }

    /**
     * Logout Habanero Player
     * @param int $terminal_id
     * @param int $serverid
     * @param int $Username
     * @param int $Password
     * @param str $PID
     * @return obj
     */
    public function LogoutPlayerHabanero($terminal_id, $serverid, $casinoUsername, $casinoPassword) {
        $casinoAPIHandler = $this->configureHabanero($terminal_id, $serverid, 0);
        $LogoutPlayer = $casinoAPIHandler->LogoutPlayerHabanero($casinoUsername, $casinoPassword);
        return $LogoutPlayer;
    }

    /**
     * Call sapi to lock | unlock lp terminal
     * @param int $commandId lock | unlock
     * @param int $terminal_id
     * @param str $login_uname
     * @param str $login_pwd
     * @param int $service_id
     */
    public function callSpyderAPI($commandId, $terminal_id, $login_uname, $login_pwd, $service_id) {
        //if spyder call was enabled in cashier config, call SAPI
        if ($_SESSION['spyder_enabled'] == 1) {

            Mirage::loadComponents('AsynchronousRequest.class');
            Mirage::loadModels(array('TerminalsModel', 'SpyderRequestLogsModel'));

            $terminalsModel = new TerminalsModel();
            $spyderRequestLogsModel = new SpyderRequestLogsModel();
            $asynchronousRequest = new AsynchronousRequest();

            $terminalName = $terminalsModel->getTerminalName($terminal_id);
            $spyder_req_id = $spyderRequestLogsModel->insert($terminalName, $commandId);

            $terminal = substr($terminalName, strlen("ICSA-")); //removes the "icsa-
            $computerName = str_replace("VIP", '', $terminal);

            $params = array('r' => 'spyder/run', 'TerminalName' => $computerName, 'CommandID' => $commandId,
                'UserName' => $login_uname, 'Password' => $login_pwd, 'Type' => Mirage::app()->param['SAPI_Type'],
                'SpyderReqID' => $spyder_req_id, 'CasinoID' => $service_id);

            $asynchronousRequest->sapiconnect(http_build_query($params));
        }
    }

    public function ChangePlayerClassification($terminal_id, $serverid, $PID, $PlayerClassID) {
        $casinoAPIHandler = $this->configureRTG($terminal_id, $serverid, 4);
        $ChangeClass = $casinoAPIHandler->ChangePlayerClassification($PID, $PlayerClassID);
        return $ChangeClass;
    }

}
