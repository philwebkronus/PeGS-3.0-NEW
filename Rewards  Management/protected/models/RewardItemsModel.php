<?php

class RewardItemsModel extends CFormModel
{
    public function getRewardID($rewarditemid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT RewardID FROM rewarditems 
            WHERE RewardItemID = :rewarditemid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':rewarditemid', $rewarditemid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    
    
    
    public function getRewardItems($partnerid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT RewardItemID, ItemName FROM rewarditems WHERE PartnerID = :partnerid ORDER BY ItemName ASC";
        $command = $connection->createCommand($sql);
        $command->bindValue(':partnerid', $partnerid);
        $result = $command->queryAll();
        
        return $result;
        
    }
    
    
    public function getRewardName($rewarditemid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT ItemName FROM rewarditems 
            WHERE RewardItemID = :rewarditemid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':rewarditemid', $rewarditemid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    /**
     * Select Active Rewards Items
     * @author Mark Kenneth Esguerra
     * @date Sep-06-13
     * @return Array Array of RewardItemIDs and ItemNames
     */
    public function selectRewardItems()
    {
        $connection = Yii::app()->db;
        
        $sql = "SELECT RewardItemID, ItemName FROM rewarditems WHERE Status = 1";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Get Raffle Item
     * @author Mark Kenneth Esguerra
     * @date Sep-19-13
     * @return array Array of Raffle Items
     * 
     */
    public function selectRaffleItems()
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT RewardItemID, ItemName FROM rewarditems
                  WHERE Status = 1 AND RewardID = 2";
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Get Reward Items by Contact Person. Join to Partners table get <br />
     * the RefPartnerID then select items through it<br />
     * @param type $partnerpid Partner user ID
     * @return array Array of Reward Items
     * @author Mark Kenneth Esguerra]
     * @date October 3, 2013
     */
    public function getRewardItemsJoinPartners($partnerpid)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT a.RewardItemID, a.ItemName FROM rewarditems a
                  INNER JOIN partners b ON a.PartnerID = b.RefPartnerID
                  WHERE b.PartnerPID = :partnerpid
                  ORDER BY ItemName ASC
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerpid", $partnerpid);
        $result = $command->queryAll();
        
        return $result;
    }
}
?>
