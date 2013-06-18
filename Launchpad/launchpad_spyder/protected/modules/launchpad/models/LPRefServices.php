<?php

/**
 * Description of LPRefServices
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
class LPRefServices extends LPModel
{
    /**
     *
     * @var LPRefServices 
     */
    private static $_instance = null;
    
    private function __construct() 
    {
        $this->_connection = LPDB::app();
    }
    
    /**
     * Get instance of LPRefServices
     * @return LPRefServices 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPRefServices();
        return self::$_instance;
    }    
    
    /**
     * Get service by ServiceID
     * @param int $serviceID
     * @return bool|array false if no row affected
     */
    public function getServiceInfo($serviceID)
    {
        $query = 'SELECT ServiceName, Alias, Code FROM ref_services WHERE ServiceID = :serviceID';
        $command = $this->_connection->createCommand($query);
        $row =  $command->queryRow(true,array(':serviceID'=>$serviceID));
        if(!$row) {
            $this->log('','launchpad.models.LPRefServices');
            throw new CHttpException(404, 'Service ID not found');
        }
            
        return $row;
    }
    
    /**
     * Get service by ServiceID including what type define in config [casino_type]
     * @param int $serviceID
     * @return array 
     */
    public function getServiceInfoWithType($serviceID)
    {
        $row = $this->getServiceInfo($serviceID);
        $row['type'] = '';
        $casino = array();
        $casinoType = LPConfig::app()->params['casino_type'];
        foreach($casinoType as $type) {
            if(strpos(strtolower($row['ServiceName']), strtolower($type)) !== false) {
                $row['type'] = $type;
                break;
            }
        }
        return $row;
    }
}
