<?php

class MemberCardsModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db3;
    }
    public function getCardNUmber($mid)
    {
        $sql = "SELECT CardNumber FROM membercards WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getMID($cardnumber)
    {
        $sql = "SELECT MID FROM membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getCardPoints($cardnumber)
    {
        $sql = "SELECT CurrentPoints, LifetimePoints, RedeemedPoints, BonusPoints FROM loyaltydb.membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getCardStatus($cardnumber) {
        $sql = "SELECT Status FROM membercards WHERE CardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
}
?>
