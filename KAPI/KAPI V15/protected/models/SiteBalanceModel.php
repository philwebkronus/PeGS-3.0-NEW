<?php

/**
 * Date Created 11 7, 11 8:18:09 AM <pre />
 * Date Modified 10/12/12
 * Description of SiteBalanceModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class SiteBalanceModel extends CFormModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new SiteBalanceModel();
        return self::$_instance;
    }
    
    public function getSiteBalance($site_id) {
        $sql = 'SELECT Balance FROM sitebalance WHERE SiteID = :siteid';
        $param = array(':siteid'=>$site_id);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
    
    //update bcf - EGM
    public function updateBcf($newbal, $site_id, $transdtl) {
        $sql = 'UPDATE sitebalance SET Balance = :newbal, LastTransactionDate = NOW(6), ' . 
                'LastTransactionDescription = :transdtl WHERE SiteID = :siteid';
        $param = array(':newbal'=>$newbal,':transdtl'=>$transdtl,':siteid'=>$site_id);
        $command = $this->_connection->createCommand($sql);
        $isUpdated = $command->execute($param);
        if(!$isUpdated){
            $this->log($command->getText().$command->getBound());
        }
        return $isUpdated;
    }
}

