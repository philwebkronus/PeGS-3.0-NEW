<?php

class RefPartnerModel extends CFormModel
{
    
    /**
     * @modifiedBy: Noel Antonio 11-11-2013
     * @description: mystery partner to be excluded in the set of partners
     * @param int $id Partner ID
     * @return array resultset of partners
     */
    public function getPartners($id = null){
        
        $connection = Yii::app()->db;
        $name = Yii::app()->params['mysteryPartner'];
        if (isset($id) && $id != NULL || $id != "")
        {
            $sql="SELECT PartnerID, PartnerName FROM ref_partners WHERE PartnerID = :partnerid AND 
                  Status = 1 AND PartnerName NOT LIKE :partnerName"; 
            
            $command = $connection->createCommand($sql);
            $command->bindParam(":partnerid", $id);
        }
        else
        {
            $sql="SELECT PartnerID, PartnerName FROM ref_partners WHERE Status = 1 AND PartnerName NOT LIKE :partnerName ORDER BY PartnerName ASC;";
            $command = $connection->createCommand($sql);
        }
        
        $keyword = "%".$name."%";
        $command->bindParam(':partnerName', $keyword, PDO::PARAM_STR);
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
     * @Description: Get Partner ID Using Partner Name
     * @param string $partnername
     * @return int or null
     */
    public function getPartnerIDUsingName($partnername){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT PartnerID FROM ref_partners 
                    WHERE Status = 1 AND PartnerName LIKE :partnername;";
        $command = $connection->createCommand($sql);
        $keyword = "%".$partnername."%";
        $command->bindParam(':partnername', $keyword, PDO::PARAM_STR);
        $result = $command->queryAll();
          
        if(isset($result[0]["PartnerID"])){
            return $result[0]["PartnerID"];
        } else {
            return null;
        }
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
     * @modifiedBy: Noel Antonio 11-11-2013
     * @description: mystery partner to be excluded in the set of partners
     */
    public function getPartnerByContactPerson($partnerpid)
    {
        $connection = Yii::app()->db;
        $name = Yii::app()->params['mysteryPartner'];
        
        $query = "SELECT PartnerID, PartnerName FROM ref_partners a
                  INNER JOIN partners b ON a.PartnerID = b.RefPartnerID
                  WHERE b.PartnerPID = :partnerpid AND PartnerID NOT LIKE :partnerName
                  ";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerpid", $partnerpid);
        $keyword = "%".$name."%";
        $command->bindParam(':partnerName', $keyword, PDO::PARAM_STR);
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
    /**
     * Check if the Partner added was already existing. Checking will base on
     * the Partner's Name
     * @author Mark Kenneth Esguerra
     * @date October 16, 2013
     */
    public function checkPartnerIfExist($partnername, $id = NULL)
    {
        $connection = Yii::app()->db;
        if (is_null($id))
        {
            $query = "SELECT COUNT(PartnerName) as ctrpartner FROM ref_partners WHERE PartnerName = :partnername";
            $command = $connection->createCommand($query);
            $command->bindParam(":partnername", $partnername);
        }
        else
        {
            $query = "SELECT COUNT(PartnerName) as ctrpartner FROM ref_partners
                      WHERE PartnerName = :partnername AND PartnerID <> :partnerid";
            $command = $connection->createCommand($query);
            $command->bindParam(":partnername", $partnername);
            $command->bindParam(":partnerid", $id);
        }


        $result = $command->queryRow();
        
        return $result['ctrpartner'];
    }
    
    /**
     * @Description: Update No. Of Reward Offerings per partner
     * @Author: aqdepliyan
     * @param int $partnerid
     * @param int $offeringscount
     */
    public function UpdateNoOfOfferings($partnerid, $offeringscount){
        $connection = Yii::app()->db;

        $query = "UPDATE partnerdetails SET NumberOfRewardOffers = :offeringcount WHERE PartnerID = :partnerid";
        $command = $connection->createCommand($query);
        $command->bindParam(":offeringcount", $offeringscount,PDO::PARAM_INT);
        $command->bindParam(":partnerid", $partnerid,PDO::PARAM_INT);
        $command->execute();
    }
    
}
?>
