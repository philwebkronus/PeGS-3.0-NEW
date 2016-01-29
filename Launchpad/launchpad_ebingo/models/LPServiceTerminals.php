<?php

/**
 * Description of LPServiceTerminals
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPServiceTerminals extends LPModel
{
    /**
     *
     * @var LPServiceTerminals 
     */
    private static $_instance;
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPServiceTerminals
     * @return LPServiceTerminals 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPServiceTerminals();
        return self::$_instance;
    }
    
    
    /**
     * Get MG account and sessionguid
     * @param int $terminalID
     * @return array array('mgAccount'=>'','sessionGuid'=>'') 
     */
    public function getMgAccountAndSessionGuid($terminalID)
    {
        $query = 'SELECT sas.ServiceAgentSessionID, st.ServiceTerminalAccount from serviceterminals st ' . 
            'INNER JOIN terminalmapping tm ON st.ServiceTerminalID = tm.ServiceTerminalID ' . 
            'INNER JOIN serviceagentsessions sas ON st.ServiceAgentID = sas.ServiceAgentID ' . 
            'WHERE tm.TerminalID = :terminalID';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalID',$terminalID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPServiceTerminals, Message: Can't get session guid or MgAccount");
        }
        return array('mgAccount'=>$result['ServiceTerminalAccount'],'sessionGuid'=>$result['ServiceAgentSessionID']);
    }
    
    
    
    
}
