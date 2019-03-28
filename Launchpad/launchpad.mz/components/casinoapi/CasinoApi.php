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
    public function generateRTGConfig($terminalCode, $serviceID)
    {
        
        $service_api = LPConfig::app()->params['service_api'][$serviceID-1];
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
            'certFilePath'=>$rtgCert,
            'keyFilePath'=>$rtgKey,
            'depositMethodId'=>$depositMethodID,
            'withdrawalMethodId'=>$withdrawalMethodID,
            'isCaching' => FALSE,
            'isDebug' => TRUE,
        );
        
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::RTG, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'Can\'t connect to RTG';
            Yii::log($message . ' TerminalCode='.$terminalCode. ' ServiceID='.$serviceID);
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
            Yii::log($message . ' TerminalCode='.$terminalCode . ' ServiceID='.$serviceID);
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
                LPDB::app()->setActive(true);
                $balanceInfo = $casinoApiHandler->GetBalance($terminalCode);
            break;
        }
        
        if(!isset($balanceInfo['BalanceInfo']['Balance'])){
            $message = 'Error: Can\'t get balance';
            Yii::log($message . ' TerminalCode='.$terminalCode . ' ServiceID='.$serviceID);
            return 'N/A';
        }
        
        $terminalBalance = $balanceInfo['BalanceInfo']['Balance'];
        
        return array("TerminalBalance"=>$terminalBalance, "CasinoAPIHandler"=>$casinoApiHandler);
    }
}