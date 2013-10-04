<?php

class RefPartnerModel extends CFormModel
{
    
    public function getPartners($id = null){
        
        $connection = Yii::app()->db;
        if (isset($id) && $id != NULL)
        {
            $sql="SELECT PartnerID, PartnerName FROM ref_partners WHERE PartnerID = :partnerid AND 
                  Status = 1"; 
        }
        else
        {
            $sql="SELECT PartnerID, PartnerName FROM ref_partners WHERE Status = 1 ORDER BY PartnerName ASC;";
        }
        $command = $connection->createCommand($sql);
        $command->bindParam(":partnerid", $id);
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
    /**
     * Get Partner Name (Company Name) by Contact Person. 
     * @param int $partnerpid Partner user ID
     * @return array Array of Partner/s
     * @author Mark Kenneth Esguerra
     * @date October 3, 2013
     */
    public function getPartnerByContactPerson($partnerpid)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT PartnerID, PartnerName FROM ref_partners a
                  INNER JOIN partners b ON a.PartnerID = b.RefPartnerID
                  WHERE b.PartnerPID = :partnerpid
                  ";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerpid", $partnerpid);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Check if the partner is active. If Inactive, block the user
     * from logging in.
     * @param int $partnerpid Partner user ID
     * @return int Status ID
     * @author Mark Kenneth Esguerra
     * @date October 3, 2013
     */
    public function checkIfActive($partnerpid)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT rp.Status FROM ref_partners rp
                  INNER JOIN partners p ON rp.PartnerID = p.RefPartnerID
                  WHERE p.PartnerPID = :partnerpid
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerpid", $partnerpid);
        $result = $command->queryAll();
        
        foreach($result as $row)
        {
            $status = $row['Status'];
        }
        
        return $status;
    }
}
?>
