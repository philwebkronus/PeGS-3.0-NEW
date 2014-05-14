<?php

/**
 * @description of TerminalsModel
 * @author jshernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class TerminalsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
        $this->_connection2 = Yii::app()->db2;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TerminalsModel();
        return self::$_instance;
    }

    public function getTerminalIDByCode($terminalCode) {
        $terminalCodeVip = $terminalCode . "VIP";
        $sql = 'SELECT TerminalID FROM terminals WHERE TerminalCode IN (:terminal_code, :terminal_code_vip)';
        $param = array(":terminal_code" => $terminalCode, ":terminal_code_vip" => $terminalCodeVip);
        $command = $this->_connection2->createCommand($sql);
        $result = $command->queryAll(true, $param);
        if (!isset($result))
            return $result;
        return $result;
    }

}
