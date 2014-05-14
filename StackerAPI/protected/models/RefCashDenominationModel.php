<?php

/**
 * @description of RefCashDenominationModel
 * @author jshernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class RefCashDenominationModel {

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
    public function getDenominationIDByAmount($amount) {

        $amount = number_format($amount, 0, '', ',');
        $amount = Yii::app()->params['DenominationPrefix'] . $amount;

        $sql = "SELECT DenominationID FROM ref_cashdenomination WHERE Description = :amount AND Status = 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':amount' => $amount));
        $result = $command->queryRow();

        if (isset($result['DenominationID'])) {
            return $result['DenominationID'];
        } else {
            return 0;
        }
    }

}
