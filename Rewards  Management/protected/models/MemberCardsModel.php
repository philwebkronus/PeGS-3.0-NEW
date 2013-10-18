<?php

class MemberCardsModel extends CFormModel
{
    public function getCardNumber($mid){
        
        $connection = Yii::app()->db3;
         
        $sql="SELECT CardNumber FROM membercards WHERE MID = :mid AND Status = 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(':mid', $mid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    
    public function checkStatus($mid)
    {
        $connection = Yii::app()->db3;
        
        $sql="SELECT Status FROM membercards WHERE MID = :mid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':mid', $mid);
        $result = $command->queryRow();
         
        return $result;
    }
}
?>
