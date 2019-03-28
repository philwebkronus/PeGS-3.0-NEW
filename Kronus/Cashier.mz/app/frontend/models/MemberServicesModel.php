<?php

/**
 * Check Terminal if Activated or Not
 *
 * @author John Aaron Vida
 * @datecreated 12/19/2017
 * 
 */
class MemberServicesModel extends MI_Model {

    public function getMemberAccountDetails($MID) {
        $sql = 'SELECT ServiceUsername,ServicePassword,HashedServicePassword FROM memberservices WHERE MID = :MID';
        $param = array(':MID' => $MID);

        $this->exec6($sql, $param);

        return $this->find6();
    }

}
?>

