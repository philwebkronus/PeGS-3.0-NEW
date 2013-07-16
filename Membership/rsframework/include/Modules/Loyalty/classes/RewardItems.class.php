<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-05-31
 * Company: Philweb
 * ***************** */

class RewardItems extends BaseEntity
{

    public function RewardItems()
    {
        $this->ConnString = "loyalty";
        $this->TableName = "rewarditems";
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->Identity = "RewardItemID";
    }
    
    function getAvailableItemCount($RewardItemID){
        $query = "SELECT AvailableItemCount FROM $this->TableName WHERE RewardItemID = $RewardItemID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    function updateAvailableItemCount($RewardItemID, $ItemCount){
        $query = "UPDATE  $this->TableName SET AvailableItemCount = AvailableItemCount - $ItemCount WHERE RewardItemID = $RewardItemID";
        return parent::ExecuteQuery($query);
    }

    function getActiveRewardItemsByCardType($cardtypeid = '')
    {
        if (!empty($cardtypeid))
        {
            $where = "b.CardTypeID = $cardtypeid 
                       and b.Status = 1";
        }
        else
        {
            $where = " b.Status = 1";
        }

        $query = "SELECT
                    a.RewardItemID,
                    a.RewardItemName AS RewardItemName,
                    a.RewardItemDescription AS RewardItemDescription,
                    a.RewardItemCode AS RewardCode,
                    a.RewardItemImagePath AS ImagePath,
                    b.RequiredPoints AS Points,
                    b.OfferStartDate AS StartDate,
                    b.OfferEndDate AS EndDate,
                    c.CardTypeName AS CardName,
                    d.Name `PromoName`
                  FROM rewardoffers AS b
                    INNER JOIN rewarditems AS a
                      ON b.RewardItemID = a.RewardItemID
                    INNER JOIN ref_cardtypes AS c
                      ON b.CardTypeID = c.CardTypeID
                    INNER JOIN promos as d
                    on b.PromoID = d.PromoID
                  WHERE $where";

        return parent::RunQuery($query);
    }
    
    function getAllRewardItems()
    {
        $query = "SELECT ri.RewardItemID, ri.RewardItemName, ri.RewardItemDescription, 
            ri.RewardItemPrice, ri.RewardItemCode, ri.ExpiryDate, ri.RewardItemCount, ri.AvailableItemCount, ri.ShowInHomePage,
            ri.IsCoupon, ri.Status, ri.RewardItemImagePath, rid.HeaderOne, rid.HeaderTwo, rid.HeaderThree, rid.DetailsOneA, 
            rid.DetailsOneB, rid.DetailsOneC, rid.DetailsTwoA, rid.DetailsTwoB, rid.DetailsTwoC, 
            rid.DetailsThreeA, rid.DetailsThreeB, rid.DetailsThreeC FROM rewarditems ri 
            INNER JOIN rewarditemdetails rid ON ri.RewardItemID = rid.RewardItemID";

        return parent::RunQuery($query);
    }

     /**
    * @author Gerardo V. Jagolino Jr.
    * @return int
    * count all reward items
    */ 
    function countAllRewardItems()
    {
        $query = "SELECT COUNT(RewardItemID) AS count FROM rewarditems";

        return parent::RunQuery($query);
    }
    
     /**
    * @author Gerardo V. Jagolino Jr.
    * @param $itemid
    * @return int
    * get all reward item per ID
    */ 
    function getAllRewardItemsperItemID($itemid)
    {
        $query = "SELECT ri.RewardItemID, ri.RewardItemName, ri.RewardItemDescription, 
            ri.RewardItemPrice, ri.RewardItemCode, ri.ExpiryDate, ri.RewardItemCount, ri.AvailableItemCount, ri.ShowInHomePage,
            ri.IsCoupon, ri.Status, ri.RewardItemImagePath, rid.HeaderOne, rid.HeaderTwo, rid.HeaderThree, rid.DetailsOneA, 
            rid.DetailsOneB, rid.DetailsOneC, rid.DetailsTwoA, rid.DetailsTwoB, rid.DetailsTwoC, 
            rid.DetailsThreeA, rid.DetailsThreeB, rid.DetailsThreeC FROM rewarditems ri 
            INNER JOIN rewarditemdetails rid ON ri.RewardItemID = rid.RewardItemID
            WHERE ri.RewardItemID = $itemid;";

        return parent::RunQuery($query);
    }
    
    
     /**
    * @author Gerardo V. Jagolino Jr.
    * @param $rewarditemname = '', $rewarditemdesc = '',$rewarditemcode = '',
             $rewarditemimagepath = '', $expdate = '', $rewarditemcount = '',$rewarditemprice = '',
            $iscoupon = '',$showinhomepage = '',$aid = '', $rewarditemid = ''
    * @return int
    * update reward items
    */ 
    function updateRewardItem($rewarditemname = '', $rewarditemdesc = '',$rewarditemcode = '',
             $rewarditemimagepath = '', $expdate = '', $rewarditemcount = '',$rewarditemprice = '',
            $iscoupon = '',$showinhomepage = '',$aid = '', $rewarditemid = '')
    {

        $query = "UPDATE rewarditems SET RewardItemName = '$rewarditemname', RewardItemDescription = '$rewarditemdesc',
            RewardItemCode = '$rewarditemcode', RewardItemImagePath = '$rewarditemimagepath', ExpiryDate = '$expdate', 
                RewardItemCount = $rewarditemcount, RewardItemPrice = $rewarditemprice, IsCoupon = $iscoupon, 
                    ShowInHomePage = $showinhomepage, DateUpdated = 'now_usec()', UpdatedByAID = $aid 
                        WHERE RewardItemID = $rewarditemid";

        return parent::ExecuteQuery($query);
    }
    
    /*
     * Description: Get the Reward ID and Name only
     * @author: Junjun S. Hernandez
     * DateCreated: July 12, 2013 12:26:35PM
     */
    function getRewardIDAndName()
    {
        $query = "SELECT RewardItemID, RewardItemName FROM rewarditems";
        return parent::RunQuery($query);
    }
    
    function getRewardNameByID($rewarditemid)
    {
        $query = "SELECT RewardItemName FROM rewarditems WHERE RewardItemID = $rewarditemid";
        return parent::RunQuery($query);
    }

}

?>
