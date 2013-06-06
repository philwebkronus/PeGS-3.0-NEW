<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-05
 * Company: Philweb
 * ***************** */
class RewardOffers extends BaseEntity
{

    public function RewardOffers()
    {
        $this->ConnString = "loyalty";
        $this->TableName = "rewardoffers";
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->Identity = "RewardOfferID";
    }
    
    function getRewardItemDetailsByRewardItemID($rewarditemid)
    {
        $query = "select * from $this->TableName where RewardItemID = $rewarditemid;";
        return parent::RunQuery($query);
    }
}
?>
