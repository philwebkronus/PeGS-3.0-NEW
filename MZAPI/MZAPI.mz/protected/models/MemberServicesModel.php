<?php

class MemberServicesModel extends CFormModel {

    public $connection;

    public function __construct() {
        $this->connection = Yii::app()->db2;
    }

    public function getCasinoCredentials($mid, $serviceid) {
        $sql = "SELECT ServiceUsername, ServicePassword, HashedServicePassword FROM memberservices WHERE MID = :mid AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();

        return $result;
    }

}

?>
