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
    
    function updateAvailableItemCount($RewardItemID, $ItemCount, $UpdatedByAID){
        $query = "UPDATE  $this->TableName SET AvailableItemCount = AvailableItemCount - $ItemCount,
                            UpdatedByAID=$UpdatedByAID, DateUpdated=now_usec()
                            WHERE RewardItemID = $RewardItemID";
        return parent::ExecuteQuery($query);
    }
    
    /**
    * @Description: Get All Reward Offers in a specific cardtype and sortable by specific field name either asc or desc 
    * @author aqdepliyan
    * @param $CardTypeID, $sortby, $isAsc
    * @return array   
    */
    public function getAllRewardOffersBasedOnPlayerClassification($IsVIP, $sortby, $isAsc = 0){
        $sorttype = $isAsc == 0 ? "asc":"desc";
        if($IsVIP == 0){ //Regular
            $playerclassification = 2;
        } else if($IsVIP == 1) { //VIP
            $playerclassification = 3;
        }
        $query = "SELECT ri.RewardID, ri.RewardItemID, ri.SubText as Description, ri.AvailableItemCount,
                            ri.ItemName as ProductName, rp.PartnerName, ri.RequiredPoints as Points, 
                            ri.ThumbnailLimitedImage, ri.ECouponImage, ri.WebsiteSliderImage,
                            ri.LearnMoreLimitedImage, ri.LearnMoreOutOfStockImage, ri.ThumbnailOutOfStockImage,
                            ri.PromoName
                            FROM $this->TableName ri
                            LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                            WHERE ri.PClassID = $playerclassification
                            AND ri.Status = 1 
                            AND ri.OfferEndDate >= curdate()
                            ORDER BY $sortby $sorttype";
       
        return parent::RunQuery($query);
    }
    
    /**
    * @Description: Get Reward Item Offer End Date.
    * @author aqdepliyan
    * @param $RewardItemID
    * @return array
    */
    function getOfferEndDate($RewardItemID){
        $query = "SELECT  OfferEndDate, curdate() as CurrentDate FROM $this->TableName
                            WHERE RewardItemID=$RewardItemID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    /**
    * @Description: Get Reward Offer Date Range of availability
    * @author aqdepliyan
    * @param $RewardItemID
    * @return array
    */
    function getOfferDateRange($RewardItemID){
        $query = "SELECT OfferStartDate as StartDate, OfferEndDate as EndDate, DrawDate
                            FROM $this->TableName WHERE RewardItemID = $RewardItemID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    
    /**
    * @Author aqdepliyan
     * @Description: Get Reward Item Serial End Code.
    * @param $RewardItemID
    * @return array
    */
    function getSerialCodeEnd($RewardItemID){
        $query = "SELECT  PartnerID, PartnerItemID FROM $this->TableName
                            WHERE RewardItemID=$RewardItemID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    /**
     * @Author: aqdepliyan
     * @Description: Get About the Reward and Terms & Condition of the reward 
     * @param type $rewarditemid
     * @return array
     */
    function getAboutandTerms($rewarditemid){
        $query = "SELECT About, Terms, SubText, PromoCode, PromoName FROM $this->TableName WHERE RewardItemID=".$rewarditemid.";";
        $result = parent::RunQuery($query);
        if(isset($result[0])){
            return $result[0];
        } else {
            $result = App::GetErrorMessage();;
            return $result;
        }
        
    }
    
    /**
     * @Author: aqdepliyan
     * @Description: Get RewardID using rewarditemid
     * @param type $rewarditemid
     * @return array
     */
    function getRewardID($rewarditemid){
        $query = "SELECT RewardID FROM $this->TableName WHERE RewardItemID=".$rewarditemid;
        return parent::RunQuery($query);
    }
    
    /**
     * @Description: For fetching learn more image.
     * @Author: aqdepliyan
     * @param int $rewarditemid
     * @return array
     */
    function getLearnMorePageImage($rewarditemid){
        $query = "SELECT LearnMoreLimitedImage, LearnMoreOutOfStockImage
                            FROM $this->TableName WHERE RewardItemID=".$rewarditemid;
        $result = parent::RunQuery($query);
        return $result[0];
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
