<?php

class RefServicesModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public function getServiceGrpNameById($ServiceID) {
        $sql = 'SELECT rsg.ServiceGroupName FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceID = :ServiceID';

        $param = array(':ServiceID' => $ServiceID);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result;
    }

}
