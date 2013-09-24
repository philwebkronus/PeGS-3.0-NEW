<?php

class RefPartnerModel extends CFormModel
{
    
    public function getPartners(){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT PartnerID, PartnerName FROM ref_partners WHERE Status = 1 ORDER BY PartnerName ASC;";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
        
    }
    
    
    public function getPartnerName($partnerid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT PartnerID, PartnerName FROM ref_partners WHERE PartnerID = :partnerid;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':partnerid', $partnerid);
        $result = $command->queryAll();
          
        return $result;
        
    }
    /**
     * Select Active Partners
     * @author Mark Kenneth Esguerra
     * @date Sep-06-13
     */
    public function selectPartners()
    {
        $connection = Yii::app()->db;
        
        $sql = "SELECT PartnerID, PartnerName FROM ref_partners WHERE Status = 1";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    
}
?>
