<?php

/**
 * Generate configuration for casino
 * @package application.modules.launchpad.components
 * @author Bryan Salazar
 */
class CasinoConfigGenBehavior extends CBehavior
{
    /**
     * Generate configuration for RTG
     * @param int $serviceID
     * @return array 
     */
    public function generateRTGConfig($serviceID)
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
        
        return $configuration;
    }
    
    /**
     * Generate configuration for MG
     * @param int $serviceID
     * @return array 
     */
    public function generateMGConfig($serviceID)
    {
        $_MGCredentials = LPConfig::app()->params['service_api'][$serviceID-1];
        list($mgurl, $mgserverID) =  $_MGCredentials;
        $service_api = $mgurl;
        //$service_api = LPConfig::app()->params['service_api'][$serviceID-1];
        $currency = LPConfig::app()->params['mg_config']['currency'];
        $mgcapi_username = LPConfig::app()->params['mg_config']['mgcapi_username'];
        $mgcapi_password = LPConfig::app()->params['mg_config']['mgcapi_password'];
        $mgcapi_player = LPConfig::app()->params['mg_config']['mgcapi_playername'];
        //$mgcapi_server = LPConfig::app()->params['mg_config']['mgcapi_serverid'];
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
        
        return $configuration;
    }
}
