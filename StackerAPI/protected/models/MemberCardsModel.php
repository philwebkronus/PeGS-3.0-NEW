<?php

/**
 * @description of MemberCardsModel
 * @author jshernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class MemberCardsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db3;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TerminalsModel();
        return self::$_instance;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $mCardNumber
     * @return object
     */
    public function isCardNumberExists($mCardNumber) {
        $sql = "SELECT COUNT(MemberCardID) CountMCardID FROM membercards WHERE CardNumber = :m_card_number";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':m_card_number' => $mCardNumber));
        $countMCNumber = $command->queryRow();

        return $countMCNumber['CountMCardID'];
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $mCardNumber
     * @return object
     */
    public function getMIDByCardNumber($mCardNumber) {
        $sql = "SELECT MID FROM membercards WHERE CardNumber = :m_card_number AND Status = 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':m_card_number' => $mCardNumber));
        $result = $command->queryRow();

        if (isset($result['MID'])) {
            return $result['MID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $cardNumber
     * @return object
     */
    public function getMID($cardNumber) {
        $sql = 'SELECT MID FROM membercards WHERE CardNumber = :card_number';
        $param = array(':card_number'=>$cardNumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['MID']))
            return false;
        return $result['MID'];
    }

}
