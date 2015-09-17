<?php

class Rewards extends BaseEntity{
    
    function Rewards (){
        $this->ConnString = "loyalty";
    }
    
    function getRewardItems($cardtypeid = '') 
    {
        if (!empty($cardtypeid)) {            
           $where = "b.CardTypeID = $cardtypeid 
                       and b.Status = 1";
        } else {
           $where = " b.Status = 1";
        }
        
         $query = "SELECT
                    a.RewardItemID,
                    a.RewardItemName AS RewardItemName,
                    a.RewardItemDescription AS RewardDescription,
                    a.RewardItemCode AS RewardCode,
                    a.RewardItemImagePath AS ImagePath,
                    b.RequiredPoints AS Points,
                    b.OfferStartDate AS StartDate,
                    b.OfferEndDate AS EndDate,
                    c.CardTypeName AS CardName
                  FROM rewardoffers AS b
                    INNER JOIN rewarditems AS a
                      ON b.RewardItemID = a.RewardItemID
                    INNER JOIN ref_cardtypes AS c
                      ON b.CardTypeID = c.CardTypeID
                  WHERE $where";
        

        return parent::RunQuery($query);
    }
    
    function getRewardOffers($PathID,$CardTypeID=''){
        
        if(!empty($CardTypeID)){
            $where = "b.CardTypeID = $CardTypeID 
                and a.RewardItemID = $PathID
                        and b.Status = 1";           
            
        }else{
            $where = "a.RewardItemID = $PathID
                        and b.Status = 1";
        }
        
        $query = "SELECT
                    a.RewardItemName AS RewardItemName,
                    a.RewardItemDescription AS RewardDescription,
                    a.RewardItemCode AS RewardCode,
                    a.RewardItemImagePath AS ImagePath,
                    b.RequiredPoints AS Points,
                    b.OfferStartDate AS StartDate,
                    b.OfferEndDate AS EndDate,
                    c.CardTypeName AS CardName
                  FROM rewardoffers AS b
                    INNER JOIN rewarditems AS a
                      ON b.RewardItemID = a.RewardItemID
                    INNER JOIN ref_cardtypes AS c
                      ON b.CardTypeID = c.CardTypeID
                  WHERE $where;";
        
        return parent::RunQuery($query);
    }
}
?>
