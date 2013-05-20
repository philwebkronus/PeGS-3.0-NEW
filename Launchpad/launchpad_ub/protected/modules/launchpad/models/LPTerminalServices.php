<?php

/**
 * Description of LPTerminalServices
 * @package application.modules.launchpad.models
 * @author Bryan Salazar, elperez
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
        $sql = "SELECT t.ServiceID, r.ServiceName, r.Alias, r.Code FROM terminalservices AS t " . 
            "INNER JOIN ref_services AS r ON r.ServiceID = t.ServiceID " . 
            "WHERE t.TerminalID = :TerminalID AND t.isCreated = :isCreated AND t.Status = :status";
        
        $param = array(':TerminalID'=>$terminalID,':isCreated'=>1,':status'=>1);
        $command=$this->_connection->createCommand($sql);
        $rows = $command->queryAll(true,$param);
        
        if(!$rows) 
            $this->log ($command->getText().$command->getBound() . "Can't get available casino", 'launchpad.models.LPTerminalServices');
        
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
}
