<?php

/**
 * Description of LPServiceTerminals
 * @package application.modules.launchpad.models
 * @author Bryan Salazar, elperez
 */
class LPServiceTerminals extends LPModel
{
    /**
     *
     * @var LPServiceTerminals 
     */
    private static $_instance;
    
    private function __construct() 
    {
        $this->_connection = LPDB::app();
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
    
//    public function getSessionGuid($terminalID)
//    {
//        $query = 'SELECT sas.ServiceAgentSessionID from serviceterminals st ' . 
//            'INNER JOIN terminalmapping tm ON st.ServiceTerminalID = tm.ServiceTerminalID ' . 
//            'INNER JOIN serviceagentsessions sas ON st.ServiceAgentID = sas.ServiceAgentID ' . 
//            'WHERE tm.TerminalID = :terminalID';
//        $command = $this->_connection->createCommand($query);
//        $row =  $command->queryRow(true,array(':terminalID'=>$terminalID));
//        if(!$row) {
//            $this->log($command->getText().$command->getBound()." Can't get session guid", 'launchpad.models.LPServiceTerminals');
//            throw new CHttpException(404, 'Can\'t get session guid');
//        }
//            
//        return $row['ServiceAgentSessionID'];
//    }
    
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
        $command = $this->_connection->createCommand($query);
        $row =  $command->queryRow(true,array(':terminalID'=>$terminalID));
        if(!$row) {
            $this->log($command->getText().$command->getBound()." Can't get session guid or MgAccount", 'launchpad.models.LPServiceTerminals');
            throw new CHttpException(404, 'Can\'t get session guid');
        }
        return array('mgAccount'=>$row['ServiceTerminalAccount'],'sessionGuid'=>$row['ServiceAgentSessionID']);
    }
}
