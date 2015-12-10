<?php

/**
 * Date Created 11 2, 11 7:09:58 PM <pre />
 * Description of RefServicesModel
 * @author Bryan Salazar
 */
class RefServicesModel extends MI_Model{
    protected $_db = 'db';
    
    public function getAllRefServices() {
        $sql = 'SELECT ServiceID, Code FROM ref_services';
        $this->exec($sql);
        return $this->findAll();
    }
    
    public function getAllRefServicesByKeyServiceId() {
        $refservices = $this->getAllRefServices();
        $services = array();
        foreach($refservices as $refservice) {
            $services[$refservice['ServiceID']] = $refservice['Code'];
        }
        return $services;
    }
    
    /**
     * Checks service mode (0 - Terminal Based, 1 - User Based)
     * date 03-07-13
     * @author elperez
     * @version Kronus UB
     * @param int $serviceID
     * @return obj 
     */
    public function getServiceById($id) {
        $sql = 'SELECT ServiceID, Code, UserMode FROM ref_services WHERE ServiceID = :serviceid';
        $param = array(':serviceid'=>$id);
        $this->exec($sql,$param);
        return $this->find();
    }
    
    public function getAliasById($service_id) {
        $sql = 'SELECT Alias FROM ref_services WHERE ServiceID = :serviceid';
        $param = array(':serviceid'=>$service_id);
        $this->exec($sql,$param);
        
        $result =  $this->find();
        if(!isset($result['Alias']))
            return false;
        return $result['Alias'];
    }
    
    public function getServiceNameById($service_id){
        $sql = 'SELECT ServiceName FROM ref_services WHERE ServiceID = :serviceid';
        $param = array(':serviceid'=>$service_id);
        $this->exec($sql,$param);
        $result =  $this->find();
        if(!isset($result['ServiceName']))
            return false;
        return $result['ServiceName'];
    }
    
    public function getServiceGrpNameById($service_id){
        $sql = 'SELECT rsg.ServiceGroupName FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceID = :serviceid';
        $param = array(':serviceid'=>$service_id);
        $this->exec($sql,$param);
        $result =  $this->find();
        if(!isset($result['ServiceGroupName']))
            return false;
        return $result['ServiceGroupName'];
    }
}

