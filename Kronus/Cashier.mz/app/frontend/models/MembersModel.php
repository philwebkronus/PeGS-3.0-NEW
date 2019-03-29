<?php

class MembersModel extends MI_Model {

    public function updateMemberOptionID1ByMID($ServiceID, $MID) {

        $sql = 'UPDATE members SET OptionID1= :serviceID WHERE MID = :MID';
        $param = array(
            ':MID' => $MID,
            ':serviceID' => $ServiceID
        );
        return $this->exec6($sql, $param);
    }

}
?>


