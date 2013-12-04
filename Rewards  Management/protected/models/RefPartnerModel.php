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
    /**
     * Get Current Status of the Partner
     * @param int $partnerID ID of the partner
     * @return string Current status of the partner
     * @author Mark Kenneth Esguerra
     * @date December 3, 2013
     */
    public function getCurrentStatus($partnerID)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT Status FROM ref_partners WHERE PartnerID = :partnerID";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerID", $partnerID);
        
        $result = $command->queryRow();
        
        return $result['Status'];
    }
}
?>
