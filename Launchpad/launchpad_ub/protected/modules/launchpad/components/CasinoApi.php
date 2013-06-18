<?php

Yii::import('application.modules.launchpad.components.MicrogamingCAPIWrapper');
Yii::import('application.modules.launchpad.components.RealtimeGamingAPIWrapper');
Yii::import('application.modules.launchpad.components.PlayTechAPIWrapper');
Yii::import('application.modules.launchpad.components.CasinoCAPIHandler');
Yii::import('application.modules.launchpad.components.checkhost');
Yii::import('application.modules.launchpad.components.common');
Yii::import('application.modules.launchpad.components.Array2XML');

/**
 * Modified casinoApi class to standard call of casino API's
 * @package application.modules.launchpad.components.casinoapi
 * @author Bryan Salazar
 * @author Edson Perez
 */
class CasinoApi
{
    /**
     * Generate configuration for RTG
     * @param int $serviceID
     * @return array 
     */
    public function generateRTGConfig($terminalCode, $serviceID, $APIType = 0)
    {
        
        $service_api = LPConfig::app()->params['service_api'][$serviceID-1];
        $game_api = LPConfig::app()->params['game_api'][$serviceID - 1];
        $rtgCert = LPConfig::app()->params['rtg_config']['RTGClientCertsPath'] . $serviceID . '/cert.pem';
        $rtgKey = LPConfig::app()->params['rtg_config']['RTGClientKeyPath'] . $serviceID . '/key.pem';
        $depositMethodID = LPConfig::app()->params['rtg_config']['deposit_method_id'];
        $withdrawalMethodID = LPConfig::app()->params['rtg_config']['withdraw_method_id'];
        
        if(stripos($service_api, 'test') !== false) {
            $depositMethodID = 502;
            $withdrawalMethodID = 503;
        }
        
        $configuration = array(
            'URI'=>$service_api,
            'URI_PID'=> $game_api,
            'certFilePath'=>$rtgCert,
            'keyFilePath'=>$rtgKey,
            'depositMethodId'=>$depositMethodID,
            'withdrawalMethodId'=>$withdrawalMethodID,
            'isCaching' => FALSE,
            'isDebug' => TRUE,
            'APIType' => $APIType,
        );
        
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::RTG, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'Can\'t connect to RTG';
            $this->log($message . ' TerminalCode='.$terminalCode. ' ServiceID='.$serviceID);
            throw new CHttpException(404, $message);
        }
        
        return $_CasinoAPIHandler;
    }
    
    /**
     * Generate configuration for MG
     * @param int $serviceID
     * @return array 
     */
    public function generateMGConfig($terminalCode, $serviceID)
    {
        $_MGCredentials = LPConfig::app()->params['service_api'][$serviceID-1];
        list($mgurl, $mgserverID) =  $_MGCredentials;
        $service_api = $mgurl;
        $currency = LPConfig::app()->params['mg_config']['currency'];
        $mgcapi_username = LPConfig::app()->params['mg_config']['mgcapi_username'];
        $mgcapi_password = LPConfig::app()->params['mg_config']['mgcapi_password'];
        $mgcapi_player = LPConfig::app()->params['mg_config']['mgcapi_playername'];
        $mgcapi_server = $mgserverID;
        
        $configuration = array(
            'URI'=>$service_api,
            'currency'=>$currency,
            'authLogin'=>$mgcapi_username,
            'authPassword'=>$mgcapi_password,
            'playerName'=>$mgcapi_player,
            'serverID'=>$mgcapi_server,
            'isCaching' => FALSE,
            'isDebug' => TRUE,
        );
        
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::MG, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'Can\'t connect to MG';
            $this->log($message . ' TerminalCode='.$terminalCode . ' ServiceID='.$serviceID);
            throw new CHttpException(404, $message);
        }
        
        return $_CasinoAPIHandler;
    }
    
    public function generatePTConfig($terminal_id, $server_id){
        $url =  LPConfig::app()->params['service_api'][$server_id -1];
        $configuration = array('URI'=>$url,
                               'isCaching'=>FALSE,
                               'isDebug'=>TRUE,
                               'pt_casino_name'=> LPConfig::app()->params['pt_config']['pt_casino_name'],
                               'pt_secret_key'=>  LPConfig::app()->params['pt_config']['pt_secret_key']
                              );
        
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::PT, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'Can\'t connect to PT';
            $this->log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$serverid);
            throw new CHttpException(404, $message);
        }
        
        return $_CasinoAPIHandler;
        
    }
    
    public function getBalance($terminalCode, $serviceID, $serviceName){
        switch ($serviceName){
            case 'rtg' : 
                $casinoApiHandler = $this->generateRTGConfig($terminalCode, $serviceID);
                LPDB::app()->setActive(false);
                $balanceInfo = $casinoApiHandler->GetBalance($terminalCode);
            break;
            case 'mg' :
                $casinoApiHandler = $this->generateMGConfig($terminalCode, $serviceID);
                LPDB::app()->setActive(false);
                $balanceInfo = $casinoApiHandler->GetBalance($terminalCode);
            break;
            case 'pt' :
                $casinoApiHandler = $this->generatePTConfig($terminalCode, $serviceID);
                LPDB::app()->setActive(false);
                $balanceInfo = $casinoApiHandler->GetBalance($terminalCode);
                break;
        }
        
        if(!isset($balanceInfo['BalanceInfo']['Balance'])){
            $message = 'Error: Can\'t get balance';
            $this->log($message . ' TerminalCode='.$terminalCode . ' ServiceID='.$serviceID);
            return 'N/A';
        }
        
        $currentBet = 0;
        if(isset($balanceInfo['BalanceInfo']['CurrentBet'])){
            $currentBet = $balanceInfo['BalanceInfo']['CurrentBet'];
        }
        
        $terminalBalance = $balanceInfo['BalanceInfo']['Balance'];
        
        return array("TerminalBalance"=>$terminalBalance, "CurrentBet"=>$currentBet,"CasinoAPIHandler"=>$casinoApiHandler);
    }
    
    
    /**
     * @Description Get pending games for RTG
     * @author aqdepliyan
     * @param int $terminal_id
     * @param int $serverid
     * @param int $PID
     * @return array $pendingGames
     */
    public function GetPendingGames($terminal_id, $serverid, $PID){
        $casinoAPIHandler = $this->generateRTGConfig($terminal_id, $serverid,2);
        $pendingGames = $casinoAPIHandler->GetPendingGames($PID);
        return $pendingGames;
    }
    
    /**
     *
     * @param string $message 
     */
    protected function log($message) 
    {
        Yii::log( '[HTTP_REFERER='.$_SERVER['HTTP_REFERER'].'] '.'[TerminalID='.
                Yii::app()->user->getState('terminalID') . ' TerminalCode='.
                Yii::app()->user->getState('terminalCode').'] '.$message, 'error', 
                'launchpad.components.CasinoApi');
    }  
}