<?php

/**
 * Date Created 11 2, 11 7:09:58 PM <pre />
 * Date Modified 10/12/12
 * Description of RefServicesModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class RefServicesModel extends CFormModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new RefServicesModel();
        return self::$_instance;
    }
    
    protected $_db = 'db';
    
    public function getAllRefServicesByKeyServiceId() {
        $refservices = $this->getAllRefServices();
        $services = array();
        foreach($refservices as $refservice) {
            $services[$refservice['ServiceID']] = $refservice['Code'];
        }
        return $services;
    }
    
    /**
     *
     * @param int $service_id
     * @return type 
     */
    public function getServiceNameById($service_id){
        $sql = 'SELECT ServiceName FROM ref_services WHERE ServiceID = :serviceid';
        $param = array(':serviceid'=>$service_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['ServiceName']))
            return false;
        return $result['ServiceName'];
    }
    
    public function getServiceUserMode($service_id){
        $sql = 'SELECT UserMode FROM ref_services WHERE ServiceID = :serviceid';
        $param = array(':serviceid'=>$service_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['UserMode']))
            return 'false';
        return $result['UserMode'];
    }
    
    public function getServiceGrpNameById($service_id){
        $sql = 'SELECT rsg.ServiceGroupName FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceID = :serviceid';
        $command = $this->_connection->createCommand($sql);
        $param = array(':serviceid'=>$service_id);
        $command->bindValues($param);
        $result = $command->queryRow();
        
        if(!isset($result['ServiceGroupName']))
            return false;
        return $result['ServiceGroupName'];
    }
    /**
     * Get Service's Alias and Code for viewing of Mapped Casino
     * @param int $serviceID Service ID
     * @return array Casino Info
     * @author Mark Kenneth Esguerra
     * @date April 16, 2014
     */
    public function getServiceInfo($serviceID)
    {
        $query = "SELECT ServiceID, Alias, Code FROM ref_services 
                  WHERE ServiceID = :serviceID";
        $command = $this->_connection->createCommand($query);
        $command->bindParam(":serviceID", $serviceID);
        $result = $command->queryRow();
        
        if (count($result) > 0)
        {
            return $result;
        }
        else
        {
            return "";
        }
    }
}

