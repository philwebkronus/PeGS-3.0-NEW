<?php

/**
 * @description of StackerSessionCashDenomModel
 * @author JunJun S. Hernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class StackerSessionCashDenomModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TerminalsModel();
        return self::$_instance;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $amount
     * @return object
     */
    public function getDenomCountBySessionIDAndDenomID($stackerSessionID, $denominationID) {

        $sql = "SELECT DenominationCount FROM stackersessioncashdenom WHERE StackerSessionID = :stacker_session_id AND DenominationID = :denomination_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':stacker_session_id' => $stackerSessionID, ':denomination_id' => $denominationID));
        $result = $command->queryRow();

        if (isset($result['DenominationCount'])) {
            return $result['DenominationCount'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $amount
     * @return object
     */
    public function updateCashDenominationDetails($stackerSessionID, $denominationCount) {

        $amount = number_format($amount, 0, '', ',');
        $amount = Yii::app()->params['DenominationPrefix'] . $amount;

        $sql = "SELECT DenominationID, DenominationCount FROM ref_cashdenomination WHERE Description = :amount AND Status = 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':amount' => $amount));
        $result = $command->queryRow();

        if (isset($result['DenominationID'])) {
            return $result['DenominationID'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $amount
     * @return object
     */
    public function isDenominationExists($stackerSessionID, $denominationID) {

        $sql = "SELECT COUNT(DenominationID) ctrDenomination FROM stackersessioncashdenom WHERE DenominationID = :denominationID AND StackerSessionID = :stackerSessionID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':denominationID' => $denominationID, ':stackerSessionID' => $stackerSessionID));
        $result = $command->queryRow();

        if (isset($result['ctrDenomination'])) {
            return $result['ctrDenomination'];
        } else {
            return 0;
        }
    }

}
