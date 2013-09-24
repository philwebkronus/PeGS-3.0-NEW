<?php

class PartnerInfoModel extends CFormModel
{
    public function getPartnerEmail($partnerpid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT Email FROM partnersinfo WHERE PartnerPID = :partnerpid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':partnerpid', $partnerpid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    
}
?>
