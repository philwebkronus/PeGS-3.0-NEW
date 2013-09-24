<?php

class MemberInfoModel extends CFormModel
{
    public function getMemberNameID($mid){
        
        $connection = Yii::app()->db4;
         
        $sql="SELECT mi.FirstName, mi.MiddleName, mi.LastName, ri.IdentificationName 
            FROM memberinfo mi INNER JOIN ref_identifications ri ON mi.IdentificationID = ri.IdentificationID 
            WHERE MID = :mid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':mid', $mid);
        $result = $command->queryAll();
         
        return $result;
        
    }
}
?>
