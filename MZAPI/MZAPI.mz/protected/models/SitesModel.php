<?php

class SitesModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public function checkSiteCode($SiteCode) {
        $sql = 'SELECT Count(*) as Count, SiteID FROM sites WHERE SiteCode = :SiteCode AND Status =:Status';

        $param = array(':SiteCode' => $SiteCode, ':Status' => 1);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

    public function checkSiteStatus($SiteCode) {
        $sql = 'SELECT Status FROM sites WHERE SiteCode = :SiteCode';

        $param = array(':SiteCode' => $SiteCode);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }

}
