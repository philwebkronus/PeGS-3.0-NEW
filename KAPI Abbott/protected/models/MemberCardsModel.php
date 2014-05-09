<?php

/**
 * Date Created 10 09, 13 01:00:00 PM <pre />
 * Description of MembersModel
 * @author JunJun S. Hernandez
 */

class MemberCardsModel {
    public static $_instance = null;
    public $_connection;

    CONST ACTIVE = 1;
    CONST INACTIVE = 0;
    CONST DEACTIVATED = 2;
    CONST ACTIVE_TEMPORARY = 5;
    CONST NEW_MIGRATED = 7;
    CONST TEMPORARY_MIGRATED = 8;
    CONST BANNED_CARD = 9;
    
    public function __construct() {
        $this->_connection = Yii::app()->db2;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MembersModel();
        return self::$_instance;
    }
    
    public function getMID($card_number) {
        $sql = 'SELECT MID FROM membercards WHERE CardNumber = :card_number';
        $param = array(':card_number'=>$card_number);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['MID']))
            return false;
        return $result['MID'];
    }
    /**
     * Check Card Status is <b>ACTIVE</b>. To retrieve the Status use <b>getStatus()</b> function
     * @param mixed $card Membership Card
     * @return int/string Return integer 1 if status is Active else String of message
     * @author Mark Kenneth Esguerra [02-18-14]
     */
    public function checkCardStatus($card)
    {
        $query = "SELECT Status FROM membercards 
                  WHERE CardNumber = :cardnumber";
        $command = $this->_connection->createCommand($query);
        $command->bindParam(":cardnumber", $card);
        $result = $command->queryRow();
        
        if ($result['Status'] == self::ACTIVE)
        {
            return (int)$result['Status'];
        }
        else //(0 - Inactive; 1 - Active; 2 - Deactivated; 5 - Active Temporary; 7 - New Migrated; 8 - Temporary Migrated; 9 - Banned Card )
        {
            $msg = "The membership card you entered is not supported.  Please use the red membership card.";
            
            return $msg;
        }
    }
    public function getCardStatus($card)
    {
        $query = "SELECT Status FROM membercards 
                  WHERE CardNumber = :cardnumber";
        $command = $this->_connection->createCommand($query);
        $command->bindParam(":cardnumber", $card);
        $result = $command->queryRow();
        
        switch($result['Status'])
        {
            case self::ACTIVE:
                $msg = "Active";
                break;
            case self::INACTIVE: 
                $msg = "Inactive"; 
                break;
            case self::DEACTIVATED: 
                $msg = "Deactivated"; 
                break;
            case self::ACTIVE_TEMPORARY: 
                $msg = "Active Temporary"; 
                break;
            case self::NEW_MIGRATED:
                $msg = "New Migrated"; 
                break;
            case self::TEMPORARY_MIGRATED:
                $msg = "Temporary Migrated"; 
                break;
            case self::BANNED_CARD:
                $msg = "Banned"; 
                break;
        }
        return $msg;
    }
    
    public function isVip($MID) {
        $sql = "SELECT COUNT(MID) ctrMID FROM members WHERE isVip = 1 AND MID = :mid";
        $param = array(':mid'=>$MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result['ctrMID'];
    }
}