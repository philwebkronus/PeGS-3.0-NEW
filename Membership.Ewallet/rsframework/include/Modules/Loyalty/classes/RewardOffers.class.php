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
    
     /**
    * @author aqdepliyan
    * @param $rewarditemid, $cardtypeid
    * @return array
    * Get Reward Item Details Using RewardItemID and CardTypeID
    */
    function getRewardItemDetailsByRewardItemID($rewarditemid, $cardtypeid)
    {
        $query = "select * from $this->TableName where RewardItemID = $rewarditemid and CardTypeID = $cardtypeid and Status = 1;";
        return parent::RunQuery($query);
    }
    
    /**
    * @author aqdepliyan
    * @param $RewardOfferID
    * @return array
    * Get Reward Offer End Date.
    */
    function getOfferEndDate($RewardOfferID){
        $query = "SELECT  OfferEndDate, NOW(6) as CurrentDate FROM $this->TableName
                            WHERE RewardOfferID=$RewardOfferID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    /**
    * @author aqdepliyan
    * @param $CardTypeID, $sortby, $isAsc
    * @return array
    * Get All Reward Offers in a specific cardtype and sortable by specific field name either asc or desc
    */
    public function getAllRewardOffers($CardTypeID, $sortby, $isAsc = 0){
        $sorttype = $isAsc == 0 ? "asc":"desc";
        $query = "SELECT ro.RewardOfferID, ri.IsCoupon, ri.RewardItemID, ri.RewardItemDescription as Description, p.Name as PromoName, 
                            ri.RewardItemName as ProductName, rp.PartnerName, ro.RequiredPoints as Points
                            FROM $this->TableName ro
                            INNER JOIN rewarditems ri ON ri.RewardItemID = ro.RewardItemID
                            INNER JOIN promos p ON p.PromoID = ro.PromoID
                            LEFT JOIN ref_partners rp ON rp.PartnerID = ro.PartnerID
                            WHERE ro.CardTypeID = $CardTypeID
                            AND ri.Status = 1 
                            AND p.Status = 1 
                            AND ro.Status = 1
                            AND ro.OfferEndDate >= NOW(6)
                            ORDER BY $sortby $sorttype";
        return parent::RunQuery($query);
    }
    
     /**
    * @author aqdepliyan
    * @param $RewardOfferID
    * @return array
    * Get Reward Offer Date Range of availability
    */
    function getOfferDateRange($RewardOfferID){
        $query = "SELECT OfferStartDate as StartDate, OfferEndDate as EndDate
                            FROM $this->TableName WHERE RewardOfferID = $RewardOfferID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    /**
    * @author aqdepliyan
    * @param $RewardOfferID
    * @return array
    * Get Reward item Code using Reward Offer ID
    */
    function getRewardItemCode($RewardOfferID){
        $query = "SELECT ri.RewardItemCode as eCouponCode
                            FROM loyaltydb.rewarditems ri
                            INNER JOIN $this->TableName ro ON ro.RewardItemID = ri.RewardItemID
                            WHERE ro.RewardOfferID = $RewardOfferID";
        return parent::RunQuery($query);
    }
    
    /**
    * @author aqdepliyan
    * @param $RewardOfferID
    * @return array
    * Get Reward item Detail for reward offer copy using Reward Offer ID
    */
    function getRewardItemDetailsForCopy($RewardOfferID){
        $query = "SELECT ri.RewardItemCode as eCouponCode, ri.RewardItemName as ProductName, rp.PartnerName 
                            FROM $this->TableName ro
                            INNER JOIN loyaltydb.rewarditems ri ON ri.RewardItemID = ro.RewardItemID
                            INNER JOIN loyaltydb.promos p ON p.PromoID = ro.PromoID
                            LEFT JOIN loyaltydb.ref_partners rp ON rp.PartnerID = ro.PartnerID
                            WHERE ro.RewardOfferID = $RewardOfferID";
        return parent::RunQuery($query);
    }
    
     /**
    * @author Gerardo V. Jagolino Jr.
    * @param $status, $rewarditemid, $aid
    * @return int
    * update status by specific rewarditemid
    */ 
    function updateStatus($status, $rewarditemid, $aid){
        
        $query = "UPDATE rewardoffers SET Status = $status, 
            DateUpdated = 'NOW(6)', UpdatedByAID = $aid 
                WHERE RewardItemID = $rewarditemid";
    
        return parent::ExecuteQuery($query);
    }
    
     /**
    * @author Gerardo V. Jagolino Jr.
    * @param $rewarditemid
    * @return int
    * check if item exist in reward offers table
    */ 
    function checkifItemExist($rewarditemid){
        
        $query = "SELECT COUNT(RewardOfferID) AS Count FROM rewardoffers WHERE RewardItemID = $rewarditemid";
    
        return parent::RunQuery($query);
    }
     /**
     * Update the status of the promo in the rewardoffers table
     * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
     * Date Created: July 12, 2012
     * @param int $status The new status entered by the user
     * @param int $promoID 
     */
    function updatePromoStatus($status, $promoID)
    {
        switch ($status)
        {
            case 0: $status = 2;
                    break;
            case 1: $status = 1;
                    break;
            case 2: $status = 3;
                    break;
        }
        $query = "UPDATE $this->TableName SET Status = $status
                  WHERE PromoID = $promoID";
        return parent::ExecuteQuery($query);
    }
    /**
    * @author JunJun S. Hernandez
    * Get Reward Offers details.
    */ 
    function getRewardOffers()
    {
        $query = "select * from $this->TableName;";
        return parent::RunQuery($query);
    }
    
    function getRewardOffersID()
    {
        $query = "select RewardOfferID, RewardItemID, CardTypeID from $this->TableName;";
        return parent::RunQuery($query);
    }
    
    /**
    * @author JunJun S. Hernandez
    * Update Reward Offers with specific data.
    */ 
    function updateRewardOfferStat($rewardofferid, $rdogroupstat)
    {
        $query = "UPDATE $this->TableName SET Status = $rdogroupstat
                         WHERE RewardOfferID = $rewardofferid;";
        return parent::ExecuteQuery($query);
    }
    
    function updateRewardOfferStatus($rewardofferid, $rdogroupstat, $dateUpdated, $updatedBy)
    {
        $query = "UPDATE $this->TableName SET DateUpdated =  '$dateUpdated', UpdatedByAID = '$updatedBy'
                         WHERE RewardOfferID = $rewardofferid;";
        return parent::ExecuteQuery($query);
    }
    
    function updateRewardOffer($rewardofferid, $rewarditemid, $cardtypeid, $promoid, $partnerid, $offerstartdate, $offerenddate)
    {
        $query = "UPDATE $this->TableName SET
                         RewardItemID = $rewarditemid,
                         CardTypeID = $cardtypeid,
                         PromoID = $promoid,
                         PartnerID = $partnerid,
                         OfferStartDate = '$offerstartdate',
                         OfferEndDate = '$offerenddate',
                         WHERE RewardOfferID = $rewardofferid;";
        return parent::ExecuteQuery($query);
    }
    
}
?>
