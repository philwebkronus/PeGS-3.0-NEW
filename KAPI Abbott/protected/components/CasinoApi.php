<?php

Yii::import('application.components.MicrogamingCAPIWrapper');
Yii::import('application.components.RealtimeGamingAPIWrapper');
Yii::import('application.components.CasinoCAPIHandler');
Yii::import('application.components.checkhost');
Yii::import('application.components.common');
Yii::import('application.components.Array2XML');


class CasinoApi {
    /**
     * Description: Configure for RTG
     * @param int $terminal_id
     * @param int $serverid
     * @return object $_CasinoAPIHandler
     */
    public function configureRTG($terminal_id,$serverid) {
        if(strpos(Yii::app()->params['service_api'][$serverid - 1], 'ECFTEST') !== false) {
            Yii::app()->params['deposit_method_id'] = 502;
            Yii::app()->params['withdrawal_method_id'] = 503;
        }        
        
        $configuration = array( 'URI' =>Yii::app()->params['service_api'][$serverid - 1],
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'certFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/cert.pem',
            'keyFilePath' => Yii::app()->params['rtg_cert_dir'] . $serverid . '/key.pem',
            'depositMethodId' => Yii::app()->params['deposit_method_id'],
            'withdrawalMethodId' => Yii::app()->params['withdrawal_method_id'] );

        $_CasinoAPIHandler = new CasinoCAPIHandler( CasinoCAPIHandler::RTG, $configuration );
        
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
        
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::MG, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            return false;
        }
        else
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

            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if(isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }

            // delete terminal session if balance if zero
            if($terminal_balance == 0 && $transtype != 'R' && $transtype != 'D') {
                $udate = CasinoApi::udate('YmdHisu');
                $transReqLogsModel->insertDueToZeroBalance($udate, 0, 'W', $terminal_id, $site_id, $service_id);
                $trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($terminal_id, $site_id);
                $transactionSummaryModel->updateRedeem($trans_summary_id, 0);
                $transactionDetailsModel->insert($udate, $trans_summary_id, $site_id, 
                        $terminal_id, 'W', 0, $service_id, $acct_id, '1');

                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $transReqLogsModel->updateTransReqLogDueZeroBal($terminal_id, $site_id, $transtype);
            }

            $terminalSessionsModel->updateTerminalSessionById($terminal_id, $service_id, $terminal_balance);
            return array($terminal_balance,$service_name,$terminalSessionsModel,$transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount);
        }
        else{
            //$this->log("ErrorCode: ".$balanceinfo['ErrorCode']." ErrorMessage: ".$balanceinfo['ErrorMessage']);
            return 'Casino: Can\'t get balance';
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
    
    protected function log($message) 
    {
        Yii::log($message, 'error', 'egm.components.CasinoApi');
    }
    
}
