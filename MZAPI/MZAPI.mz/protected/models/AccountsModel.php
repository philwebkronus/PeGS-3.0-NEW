<?php

class AccountsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public function authenticateCredentials($Username, $Password) {
        $sql = 'SELECT COUNT(*) as Count, AID FROM `accounts` WHERE `Username`=:Username AND `Password`=:Password AND `Status`=:Status AND `AccountTypeID` IN (3,4) LIMIT 1';
        $param = array(':Username' => $Username, ':Password' => $Password, ':Status' => 1);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

    public function authenticateUserNameCredentials($Username) {
        $sql = 'SELECT COUNT(`AID`) as Count FROM `accounts` WHERE `Username`=:Username LIMIT 1';
        $param = array(':Username' => $Username);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

    public function checkStatusUsingUserNameCredentials($Username) {
        $sql = 'SELECT Status FROM `accounts` WHERE `Username`=:Username LIMIT 1';
        $param = array(':Username' => $Username);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

}
