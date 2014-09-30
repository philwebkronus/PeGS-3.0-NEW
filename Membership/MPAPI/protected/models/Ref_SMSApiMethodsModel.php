<?php

/**
 * @author fdlsison
 * 
 * @date 07-11-2014
 */

class Ref_SMSApiMethodsModel {
    
    const COUPON_REDEMPTION = 1;
    const ITEM_REDEMPTION = 2;
    const PLAYER_REGISTRATION = 3;
    const PLAYER_REGISTRATION_BT = 4;
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_SMSApiMethodsModel();
        return self::$_instance;
    }
    
    //@date 07-11-2014
    //@purpose fetching of SMS Template ID
    public function getSMSMethodTemplateID($methodID) {
        $sql = 'SELECT SMSTemplateID
                FROM ref_smsapimethods
                WHERE SMSMethodID = :methodID';
        $param = array(':methodID' => $methodID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    
            
}

