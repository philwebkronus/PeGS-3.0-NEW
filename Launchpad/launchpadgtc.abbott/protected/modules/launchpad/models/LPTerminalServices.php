<?php

/**
 * Description of LPTerminalServices
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
class LPTerminalServices extends LPModel 
{
    /**
     *
     * @var LPTerminalServices
     */
    private static $_instance;
    
    private function __construct() 
    {
        $this->_connection = LPDB::app();
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
        
        $param = array(':TerminalID'=>$terminalID,':isCreated'=>1,':status'=>1);
        $command=$this->_connection->createCommand($sql);
        $rows = $command->queryAll(true,$param);
        
        if(!$rows) 
            $this->log ("Can't get available casino", 'launchpad.models.LPTerminalServices');
        
        $casinos = array();
        $casinoType = LPConfig::app()->params['casino_type'];
        foreach($rows as $row) {
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
        $query = 'SELECT ts.HashedServicePassword, ts.ServicePassword 
                            FROM terminalservices ts
                            INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                            WHERE t.TerminalCode = :terminalcode AND ts.ServiceID = :serviceid 
                            AND ts.Status = 1';
        $command = $this->_connection->createCommand($query);
        $row =  $command->queryRow(true,array(':terminalcode'=>$terminalCode, ':serviceid'=>$serviceID));

        if(count($row) == 0) {
            $this->log(''," Can't get terminal Credentials", 'launchpad.models.LPServiceTerminals');
            throw new CHttpException(404, 'Can\'t get terminal Credentials');
        }
        return $row;
    }
}
