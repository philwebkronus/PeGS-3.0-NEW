<?php

/**
 * @author fdlsison
 * 
 * @date 6-26-2014
 */

class RewardItemsModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new RewardItemsModel();
        return self::$_instance;
    }
    
    //@date 06-27-2014
    //@purpose get list of reward items based on player classification
    public function getAllRewardOffersBasedOnPlayerClassification($playerClassID) {
        $sql = 'SELECT ri.RewardID, ri.RewardItemID, ri.SubText as Description, ri.AvailableItemCount,
                            ri.ItemName as ProductName, rp.PartnerName, ri.RequiredPoints as Points, 
                            ri.ThumbnailLimitedImage, ri.ECouponImage, ri.WebsiteSliderImage,
                            ri.LearnMoreLimitedImage, ri.LearnMoreOutOfStockImage, ri.ThumbnailOutOfStockImage,
                            ri.PromoName, ri.IsMystery, ri.MysteryName, ri.MysteryAbout, ri.MysteryTerms, ri.MysterySubtext, ri.About, ri.Terms
                            FROM rewarditems ri
                            LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                            WHERE ri.PClassID IN (:PlayerClassID, 1)
                            AND ri.Status IN (1, 3)
                            AND ri.OfferStartDate <= NOW(6) 
                            AND ri.OfferEndDate >= NOW(6)';
        $param = array(':PlayerClassID' => $playerClassID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll(true, $param);
        
        return $result;
    }
    
    //@date 07-01-2014
    //@purpose get reward item offer end date
    public function getOfferEndDate($rewardItemID) {
        $sql = 'SELECT OfferEndDate, curdate() AS CurrentDate, NOW(6) AS ItemCurrentDate
                FROM rewarditems
                WHERE RewardItemID = :RewardItemID';
        $param = array(':RewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-03-2014
    //@purpose for checking of rewarditem status before completing redemption
    public function checkStatus($rewardItemID) {
        $sql = "SELECT CASE Status
                    WHEN 1 THEN 'Active'
                    WHEN 2 THEN 'Inactive'
                    WHEN 3 THEN 'Out-Of-Stock'
                    WHEN 4 THEN 'Deleted'
                    END as Status
                FROM rewarditems
                WHERE RewardItemID = :RewardItemID";
        $param = array(':RewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-11-2014
    //@purpose get reward item details
    public function getItemDetails($rewardItemID) {
        $sql = 'SELECT *
                FROM rewarditems
                WHERE RewardItemID = :rewardItemID';
        $param = array(':rewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-14-2014
    //@purpose get the available item count
    public function getAvailableItemCount($rewardItemID) {
        $sql = 'SELECT AvailableItemCount
                FROM rewarditems
                WHERE RewardItemID = :rewardItemID';
        $param = array(':rewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-15-2014
    //@purpose update the available item count
    public function updateAvailableItemCount($rewardItemID, $updatedByAID) {
        $startTrans = $this->_connection->beginTransaction();
        $itemCount = 1;
        
        try {
            $sql = 'UPDATE rewarditems
                    SET AvailableItemCount = AvailableItemCount - :itemCount, UpdatedByAID = :updatedByAID,
                        DateUpdated = NOW(6)
                    WHERE RewardItemID = :rewardItemID';
            $param = array(':itemCount' => $itemCount, ':updatedByAID' => $updatedByAID, ':rewardItemID' => $rewardItemID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
            
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    //@purpose get reward item serial end code.
    public function getSerialCodePrefix($rewardItemID) {
        $sql = 'SELECT PartnerID, PartnerItemID
                FROM rewarditems
                WHERE RewardItemID = :rewardItemID';
        $param = array(':rewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 08-04-2014
    //@purpose check if reward is mystery item
    public function checkIfMystery($rewardItemID) {
        $sql = 'SELECT IsMystery
                FROM rewarditems
                WHERE RewardItemID = :rewardItemID';
        $param = array(':rewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 08-05-2014
    //@purpose get ecoupon image
    public function getRewardItemDetails($rewardItemID) {
        $sql = 'SELECT ECouponImage, About, Terms, SubText, PromoCode, PromoName, AvailableItemCount, OfferStartDate as StartDate, OfferEndDate as EndDate, DrawDate, MysteryName, ItemName, IsMystery
                FROM rewarditems
                WHERE RewardItemID = :rewardItemID';
        $param = array(':rewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 09-17-2015
    //@purpose check item is coupon
    public function checkItem($rewardID, $rewardItemID) {
        $sql = 'SELECT * FROM rewardsdb.rewarditems WHERE RewardItemID = :rewardItemID and RewardID = :rewardID;';
        $param = array(':rewardItemID' => $rewardItemID, ':rewardID' => $rewardID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
            
}

