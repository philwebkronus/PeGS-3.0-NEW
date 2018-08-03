<?php

class MemberCardsModel extends MI_Model {

    public function updatePlayerHabaneroPoints($PointsWithdrawn, $MID) {

        $sql = 'UPDATE membercards SET VvCompPoints = VvCompPoints + :points WHERE MID = :MID';
        $param = array(
            ':MID' => $MID,
            ':points' => $PointsWithdrawn
        );
        return $this->exec4($sql, $param);
    }

    public function getPlayerMID($LoyaltyCardNumber) {

        $sql = 'SELECT MID FROM membercards WHERE CardNumber = :CardNumber';
        $param = array(
            ':CardNumber' => $LoyaltyCardNumber
        );
        return $this->exec4($sql, $param);
    }

}

?>
