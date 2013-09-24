<?php

class MemberCardsModel extends CFormModel
{
    public function getCardNumber($mid){
        
        $connection = Yii::app()->db3;
         
        $sql="SELECT CardNumber FROM membercards WHERE MID = :mid AND Status IN (1,9)";
        $command = $connection->createCommand($sql);
        $command->bindValue(':mid', $mid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    
    
}
?>
