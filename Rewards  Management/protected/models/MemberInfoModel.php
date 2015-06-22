<?php

class MemberInfoModel extends CFormModel
{
    public function getMemberNameID($mid){
        
        $connection = Yii::app()->db4;
        $neededfields = "FirstName,MiddleName,LastName";
        $sql1="CALL sp_select_data(1,1,0,$mid,'$neededfields',@ReturnCode, @ReturnMessage, @ReturnFields);";
        $command = $connection->createCommand($sql1);
        $data = $command->queryAll();
        $result =  array();
        $keys = explode(",", $neededfields);
        $infodata = explode(';', $data[0]['OUTfldListRet']);
        foreach ($keys as $key => $value) {
            $result[0][trim($value," ")] = $infodata[$key];
        }
        
        $sql2="SELECT ri.IdentificationName FROM memberinfo mi 
                INNER JOIN ref_identifications ri ON mi.IdentificationID = ri.IdentificationID 
                WHERE MID = :mid";
        $command = $connection->createCommand($sql2);
        $command->bindValue(':mid', $mid);
        $idenData = $command->queryAll();
        isset($idenData[0]['IdentificationName']) ? $result[0]['IdentificationName']=$idenData[0]['IdentificationName']:$result[0]['IdentificationName']='';

        return $result;
        
    }
}
?>
