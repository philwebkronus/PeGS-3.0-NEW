<?php
/**
 * Reward Type Model
 * @author Mark Kenneth Esguerra
 * @date Sep-06-13
 * @copyright (c) 2013, Philweb Corp.
 */
class RewardTypeModel extends CFormModel
{
    CONST REWARDS_E_COUPONS = 1;
    CONST RAFFLE_E_COUPONS = 2;
    CONST ALL = 0;
    /**
     * Select Category (Reward Type)
     * @date Sep-06-13
     */
    public function selectCategory()
    {
        $connection = Yii::app()->db;
        $sql = "SELECT RewardID, Description FROM ref_rewardtype";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        //include ALL option
        array_unshift($result, array('RewardID'=>'0','Description'=>'All'));
        
        return $result;
    }
    /**
     * Get reward type description (name)
     * @param int $rewardID Reward ID
     * @return string Description
     * @author Mark Kenneth Esguerra
     * @date December 12, 2013
     */
    public function getRewardTypeDesp($rewardID)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT Description FROM ref_rewardtype 
                  WHERE RewardID = :rewardID";
        $command = $connection->createCommand($query);
        $command->bindParam(":rewardID", $rewardID);
        $result = $command->queryRow();
        
        return $result['Description'];
    }
    
}
?>
