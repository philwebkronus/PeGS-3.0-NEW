<?php

require_once '../models/LPConfig.php';
require_once '../Helper/Logger.class.php';
require_once 'MicrogamingCAPIWrapper.php';
require_once 'RealtimeGamingAPIWrapper.php';
require_once 'RealtimeGamingUBAPIWrapper.php';
require_once 'PlayTechAPIWrapper.php';
require_once 'CasinoCAPIHandler.php';
require_once 'checkhost.php';
require_once 'common.php';
require_once 'Array2XML.php';


/**
 * Modified casinoApi class to standard call of casino API's
 * @package application.modules.launchpad.components.casinoapi
 * @author Bryan Salazar
 * @author Edson Perez
 */
class CasinoApi
{
    
    private static $_logdir;
    
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
            $message = 'File: launchpad.components.CasinoApi, Message: Can\'t connect to RTG,'.' TerminalCode='.$terminalCode. ', ServiceID='.$serviceID;
            $this->logerror($message);
            throw new CHttpException(404, "Can\'t connect to RTG");
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
            $message = 'File: launchpad.components.CasinoApi, Message: Can\'t connect to MG,'.' TerminalCode='.$terminalCode. ', ServiceID='.$serviceID;
            $this->logerror($message);
            throw new CHttpException(404, "Can\'t connect to MG");
        }
        
        return $_CasinoAPIHandler;
    }
    
    public function generatePTConfig($terminal_id, $server_id, $isRevert = 0){
        
        if($isRevert == 0){
            $url =  LPConfig::app()->params['service_api'][$server_id -1];
            $configuration = array('URI'=>$url,
                                   'isCaching'=>FALSE,
                                   'isDebug'=>TRUE,
                                   'pt_casino_name'=> LPConfig::app()->params['pt_config']['pt_casino_name'],
                                   'pt_secret_key'=>  LPConfig::app()->params['pt_config']['pt_secret_key']
                                  );

        } else {
            
                $url = LPConfig::app()->params['revertbroken_api']['URI'];
                $configuration = array('URI'=>'',
                                       'URI_RBAPI'=>$url,
                                       'isCaching'=>FALSE,
                                       'isDebug'=>TRUE,
                                       'REVERT_BROKEN_GAME_MODE' => LPConfig::app()->params['revertbroken_api']['REVERT_BROKEN_GAME_MODE'],
                                       'CASINO_NAME' => LPConfig::app()->params['revertbroken_api']['CASINO_NAME'],
                                       'PLAYER_MODE' => LPConfig::app()->params['revertbroken_api']['PLAYER_MODE'],
                                       'certFilePath' => LPConfig::app()->params['pt_config']['PTClientCertsPath'].$server_id.'/cert.pem',
                                       'keyFilePath' => LPConfig::app()->params['pt_config']['PTClientKeyPath'].$server_id.'/key.pem' 
                                      );

        }
        
        
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::PT, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
            $message = 'File: launchpad.components.CasinoApi, Message: Can\'t connect to PT,'.' TerminalCode='.$terminalCode. ', ServiceID='.$serviceID;
            $this->logerror($message);
            throw new CHttpException(404, "Can\'t connect to PT");
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
            case 'rtg2' : 
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
            $message = 'File: launchpad.components.CasinoApi, Message: Error: Can\'t get balance,'.' TerminalCode='.$terminalCode. ', ServiceID='.$serviceID;
            $this->logerror($message);
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
     * Reverts PT Pending games
     * @param int $terminal_id
     * @param int $service_id
     * @param str $username
     * @return type
     */
    public function RevertBrokenGamesAPI($terminal_id, $service_id, $username){
        $isRevert = 1; //0-No, 1-Yes
        $_casinoAPIHandler = $this->generatePTConfig($terminal_id,$service_id, $isRevert);
        $game_mode = LPConfig::app()->params['revertbroken_api']['REVERT_BROKEN_GAME_MODE'];
        $player_mode = LPConfig::app()->params['revertbroken_api']['PLAYER_MODE'];
        $response = $_casinoAPIHandler->RevertBrokenGamesAPI($username, $player_mode, $game_mode);
        return $response;
    }
    

    protected function logerror($message, $errortype = "ERROR"){
        $logger = new Logger(LPConfig::app()->params['logpath']);
        $logger->logger($message);
    }
}