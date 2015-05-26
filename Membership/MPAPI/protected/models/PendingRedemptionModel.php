<?php

/**
 * @author fdlsison
 * 
 * @date 07-01-2014
 */

class PendingRedemptionModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new PendingRedemptionModel();
        return self::$_instance;
    }
    
    //@date 07-01-2014
    //@purpose check pending redemption per MID
    public function checkPendingRedemption($MID) {
        $sql = 'SELECT *
                FROM pendingredemption
                WHERE MID = :MID';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if(isset($result['MID']) && $result['MID'] != '') {
            return true;
        }
        else {
            return false;
        }
   }           
}