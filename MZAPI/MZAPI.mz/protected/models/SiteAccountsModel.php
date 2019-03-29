<?php

class SiteAccountsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public function checkIfLinkedAccount($SiteID, $AID) {
        $sql = 'SELECT Count(*) as Count FROM siteaccounts WHERE SiteID = :SiteID AND AID = :AID AND Status =:Status';
        $param = array(':SiteID' => $SiteID, ':AID' => $AID, ':Status' => 1);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

}
