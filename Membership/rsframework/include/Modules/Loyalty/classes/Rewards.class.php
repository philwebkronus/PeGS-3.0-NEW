<?php

class Rewards extends BaseEntity{
    
    function Rewards (){
        $this->ConnString = "loyalty";
    }
        function getRewardItems($cardtypeid=''){
         if(empty($cardtypeid)){
            $where = " b.Status = 1";
        }else
        {
            $where = "b.CardTypeID = $cardtypeid 
                       and b.Status = 1";
        }
        $query = "Select a.RewardItemID, a.RewardItemName as RewardItemName, a.RewardItemDescription as RewardDescription, 
        a.RewardItemCode as RewardCode, a.RewardItemImagePath as ImagePath, b.RequiredPoints as Points, 
        b.OfferStartDate as StartDate, b.OfferEndDate as EndDate, c.CardTypeName as CardName
        from rewardoffers as b 
        inner join rewarditems as a
        on b.RewardItemID = a.RewardItemID
        inner join ref_cardtypes as c
        on b.CardTypeID = c.CardTypeID
        where $where";
       
        
        
//        $query = "Select * from rewarditems where Status = 1";
         return parent::RunQuery($query);
    }
    function getRewardOffers($PathID,$CardTypeID=''){
        
        if(empty($CardTypeID)){
            $where = "a.RewardItemID = $PathID
                        and b.Status = 1";
        }else{
            $where = "b.CardTypeID = $CardTypeID 
                and a.RewardItemID = $PathID
                        and b.Status = 1";
        }
        $query = "Select a.RewardItemName as RewardItemName, a.RewardItemDescription as RewardDescription, 
        a.RewardItemCode as RewardCode, a.RewardItemImagePath as ImagePath, b.RequiredPoints as Points, 
        b.OfferStartDate as StartDate, b.OfferEndDate as EndDate, c.CardTypeName as CardName
        from rewardoffers as b 
        inner join rewarditems as a
        on b.RewardItemID = a.RewardItemID
        inner join ref_cardtypes as c
        on b.CardTypeID = c.CardTypeID
        where $where";
        return parent::RunQuery($query);
    }
}
?>
