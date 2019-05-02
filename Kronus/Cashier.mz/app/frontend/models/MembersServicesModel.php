<?php

class MembersServicesModel extends MI_Model {

    public function getMemberServicesAccounts($MID) {

        $sql = 'SELECT ServiceID, ServiceUsername, ServicePassword, HashedServicePassword FROM memberservices WHERE MID = :MID';
        $param = array(
            ':MID' => $MID,
        );
        $this->exec6($sql, $param);
        return $this->findAll6();
    }

}

?>
