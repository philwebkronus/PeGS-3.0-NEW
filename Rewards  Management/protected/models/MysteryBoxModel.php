<?php
/**
 * Form model for Mystery Box
 * @author Noel Antonio
 * @dateCreated 11-07-2013
 */

class MysteryBoxModel extends CFormModel
{
    /**
     * Instantiate public variables/attributes
     */
    public $rewardItem;
    public $serialCode;
    public $securityCode;
    
    
    /**
     * Apply rules for each model attributes.
     */
    public function rules() {
        return array(
            array('rewardItem, serialCode, securityCode', 'required'),
            array('serialCode', 'length', 'max' => 30),
            array('securityCode', 'length', 'max' => 30),
        );
    }
    
    
    /**
     * Retrieves all mystery box reward items
     * @param int $partnerId Partner ID (default: egames)
     * @return array resultset of all available mysterybox reward items
     */
    public function getMysteryBoxRewardItems($partnerId)
    {
        $connection = Yii::app()->db;
         
        $sql="SELECT 
                    RewardItemID, ItemName 
                FROM 
                    rewarditems 
                WHERE 
                    PartnerID = :partnerId 
                    AND IsMystery = 1
                    AND AvailableItemCount = 0
                    AND Status = 1
                ORDER BY 
                    ItemName ASC";
        
        $command = $connection->createCommand($sql);
        $command->bindValue(':partnerId', $partnerId);
        $result = $command->queryAll();
        
        return $result;
    }
    
    
    /**
     * Check that user date is between start & end
     * @param date $start_date
     * @param date $end_date
     * @param date $date_from_user
     * @return boolean
     */
    public function check_in_range($start_date, $end_date, $date_from_user)
    {
        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);

        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }
}
?>
