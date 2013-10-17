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
    
}
?>
