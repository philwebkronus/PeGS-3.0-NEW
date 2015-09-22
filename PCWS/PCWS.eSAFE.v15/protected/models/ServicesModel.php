<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServicesModel
 *
 * @author jdlachica
 */
class ServicesModel extends CFormModel{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getUserMode($serviceID)
    {
        $sql = "SELECT UserMode FROM ref_services WHERE ServiceID = :service_id";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":service_id", $serviceID);
        $result = $command->queryRow();
        
        return $result;
    }
    
}
