<?php

/**
 * Description of LPTerminalServices
 * @package application.modules.launchpad.models
 * @author 
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPTerminalServices extends LPModel
{
    
    private static $_instance = null;    
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPTerminalServices
     * @return LPTerminalServices 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPTerminalServices();
        return self::$_instance;
    }
    
    /**
     * Get available casino by TerminalID
     * @param int $terminalID
     * @return array 
     */
    public function getAllAvailableCasino($terminalID)
    {
        $sql = "SELECT rsg.ServiceGroupName, t.ServiceID, r.ServiceName, r.Alias, r.Code FROM terminalservices AS t " . 
                    "INNER JOIN ref_services AS r ON r.ServiceID = t.ServiceID " . 
                    "INNER JOIN ref_servicegroups rsg ON r.ServiceGroupID = rsg.ServiceGroupID " . 
                    "WHERE t.TerminalID = :TerminalID AND t.isCreated = :isCreated AND t.Status = :status";
        
        $rqst = $this->_pdoconn->prepare($sql);
        $rqst->bindParam(':TerminalID',$terminalID);
        $rqst->bindParam(':isCreated',1);
        $rqst->bindParam(':status',1);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) 
            $this->logerror("File: launchpad.models.LPTerminalServices, Message: Can't get available casino");
        
        $casinos = array();
        $casinoType = $this->_params["casino_type"];
        foreach($result as $row) {
            foreach($casinoType as $type) {
                if(strpos(strtolower($row['ServiceName']), strtolower($type)) !== false) {
                    $casinos[] = array_merge($row, array('type'=>$type));
                    break;
                }
            }
        }
        return $casinos;
    }
    
    /**
     * @Description: Get the Terminal Based Credentials
     * @param int $terminalID
     * @param int $serviceID
     * @return array
     * @throws CHttpException
     */
    public function getTBCredentials($terminalCode, $serviceID)
    {
        $query = 'SELECT ts.HashedServicePassword, ts.ServicePassword,ts.ServiceID, rs.Code 
                            FROM terminalservices ts
                            INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                            INNER JOIN ref_services rs ON ts.ServiceID = rs.ServiceID
                            WHERE t.TerminalCode = :terminalcode AND ts.ServiceID = :serviceid 
                            AND ts.Status = 1';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalcode',$terminalCode);
        $rqst->bindParam(':serviceid',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if(count($result) == 0) {
            $this->logerror("File: launchpad.models.LPServiceTerminals, Message: Can't get terminal Credentials");
        }
        return $result;
    }
}
