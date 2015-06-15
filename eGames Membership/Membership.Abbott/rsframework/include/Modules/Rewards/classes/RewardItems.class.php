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
        //$this->ConnString = "loyalty";
        $this->ConnString = "rewardsdb";
        $this->TableName = "rewarditems";
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->Identity = "RewardItemID";
    }
    
    function getAvailableItemCount($RewardItemID){
        $query = "SELECT AvailableItemCount FROM $this->TableName WHERE RewardItemID = $RewardItemID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    function updateAvailableItemCount($RewardItemID, $UpdatedByAID){
        $ItemCount = 1;
        $query = "UPDATE  $this->TableName SET AvailableItemCount = AvailableItemCount - $ItemCount,
                            UpdatedByAID=$UpdatedByAID, DateUpdated=now_usec()
                            WHERE RewardItemID = $RewardItemID";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
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
                            ri.PromoName, ri.IsMystery, ri.MysteryName, ri.MysteryAbout, ri.MysteryTerms, ri.MysterySubtext
                            FROM $this->TableName ri
                            LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                            WHERE ri.PClassID IN ($playerclassification, 1)
                            AND ri.Status IN (1,3)
                            AND ri.OfferStartDate <= now_usec() 
                            AND ri.OfferEndDate >= now_usec()
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
        $query = "SELECT  OfferEndDate, curdate() as CurrentDate, now_usec() as ItemCurrentDate FROM $this->TableName
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
        $query = "SELECT OfferStartDate as StartDate, OfferEndDate as EndDate, DrawDate, AvailableItemCount, MysteryName, ItemName, IsMystery
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
    function getSerialCodePrefix($RewardItemID){
        $query = "SELECT  PartnerID, PartnerItemID FROM $this->TableName
                            WHERE RewardItemID=$RewardItemID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    /**
     * @Description: For Checking of RewardItem Status before completing the redemption.
     * @param int $RewardItemID
     * @return string
     */
    function CheckStatus($RewardItemID){
        $query = "SELECT CASE Status
                                        WHEN 1 THEN 'Active'
                                        WHEN 2 THEN 'Inactive'
                                        WHEN 3 THEN 'Out-Of-Stock'
                                        WHEN 4 THEN 'Deleted'
                            END as Status FROM $this->TableName WHERE RewardItemID = $RewardItemID";
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
        $query = "SELECT About, Terms, SubText, PromoCode, PromoName, AvailableItemCount FROM $this->TableName WHERE RewardItemID=".$rewarditemid.";";
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
     * @Description: Get Mystery Details if it's a Mystery Reward
     * @param type $rewarditemid
     * @return array
     */
    function getMysteryDetails($rewarditemid){
        $query = "SELECT MysteryAbout, MysteryTerms, MysterySubtext, MysteryName FROM $this->TableName WHERE RewardItemID=".$rewarditemid.";";
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

    function getAllRewardItems()
    {
        $query = "SELECT ri.RewardItemID, ri.RewardItemName, ri.RewardItemDescription, 
                            ri.RewardItemPrice, ri.RewardItemCode, ri.ExpiryDate, ri.RewardItemCount, ri.AvailableItemCount, ri.ShowInHomePage,
                            ri.IsCoupon, ri.Status, ri.RewardItemImagePath, rid.HeaderOne, rid.HeaderTwo, rid.HeaderThree, rid.DetailsOneA, 
                            rid.DetailsOneB, rid.DetailsOneC, rid.DetailsTwoA, rid.DetailsTwoB, rid.DetailsTwoC, 
                            rid.DetailsThreeA, rid.DetailsThreeB, rid.DetailsThreeC 
                            FROM $this->TableName ri 
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
        $query = "SELECT COUNT(RewardItemID) AS count FROM $this->TableName";

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

        $query = "UPDATE $this->TableName SET RewardItemName = '$rewarditemname', RewardItemDescription = '$rewarditemdesc',
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
        $query = "SELECT RewardItemID, RewardItemName FROM $this->TableName";
        return parent::RunQuery($query);
    }
    
    function getRewardNameByID($rewarditemid)
    {
        $query = "SELECT RewardItemName FROM $this->TableName WHERE RewardItemID = $rewarditemid";
        return parent::RunQuery($query);
    }

}

?>
