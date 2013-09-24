<?php

class PartnersModel extends CFormModel
{
    
    public function getPartnerPID($refpartnerid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT RefPartnerID FROM partners
            WHERE RefPartnerID = :refpartnerid LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(':refpartnerid', $refpartnerid);
        $result = $command->queryAll();
         
        return $result;
        
        
    }
}
?>
