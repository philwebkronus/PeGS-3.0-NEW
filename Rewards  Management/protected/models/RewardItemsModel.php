<?php

class RewardItemsModel extends CFormModel
{
    public function getRewardID($rewarditemid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT RewardID FROM rewarditems 
            WHERE RewardItemID = :rewarditemid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':rewarditemid', $rewarditemid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    
    public function getAuditTrailDetails($rewarditemid){
        $connection = Yii::app()->db;
         
        $sql="SELECT PartnerID, ItemName, Status FROM rewarditems 
            WHERE RewardItemID = :rewarditemid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':rewarditemid', $rewarditemid);
        $result = $command->queryAll();
        return $result[0];
    }

        public function getRewardItems($partnerid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT RewardItemID, ItemName FROM rewarditems WHERE PartnerID = :partnerid ORDER BY ItemName ASC";
        $command = $connection->createCommand($sql);
        $command->bindValue(':partnerid', $partnerid);
        $result = $command->queryAll();
        
        return $result;
        
    }
    
    
    public function getRewardName($rewarditemid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT ItemName FROM rewarditems 
            WHERE RewardItemID = :rewarditemid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':rewarditemid', $rewarditemid);
        $result = $command->queryRow();
         
        return $result;
        
    }
    /**
     * Select Active Rewards Items
     * @author Mark Kenneth Esguerra
     * @date Sep-06-13
     * @return Array Array of RewardItemIDs and ItemNames
     */
    public function selectRewardItems()
    {
        $connection = Yii::app()->db;
        
        $sql = "SELECT RewardItemID, ItemName FROM rewarditems WHERE Status = 1";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Get Raffle Item
     * @author Mark Kenneth Esguerra
     * @date Sep-19-13
     * @return array Array of Raffle Items
     * 
     */
    public function selectRaffleItems()
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT RewardItemID, ItemName FROM rewarditems
                  WHERE Status = 1 AND RewardID = 2";
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Get Reward Items by Contact Person. Join to Partners table get <br />
     * the RefPartnerID then select items through it<br />
     * @param type $partnerpid Partner user ID
     * @return array Array of Reward Items
     * @author Mark Kenneth Esguerra]
     * @date October 3, 2013
     */
    public function getRewardItemsJoinPartners($partnerpid)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT a.RewardItemID, a.ItemName FROM rewarditems a
                  INNER JOIN partners b ON a.PartnerID = b.RefPartnerID
                  WHERE b.PartnerPID = :partnerpid
                  ORDER BY ItemName ASC
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerpid", $partnerpid);
        $result = $command->queryAll();
        
        return $result;
    }

    
    /**
     * @Description: For Fetching Reward Item/Coupon List
     * @Author: aqdepliyan
     * @DateCreated: 2013-09-23
     * @param int $rewardtype
     * @param int $filterby
     * @return array
     */
    public function getRewardItemsForManageRewards($rewardtype, $filterby){
        $connection = Yii::app()->db;
        
        switch ($filterby){
            case 0: //All
                $query = "SELECT ri.RewardItemID,rp.PartnerName, ri.ItemName, rc.Description as Category, ri.RequiredPoints as Points,rpc.Description as Eligibility, 
                                    CASE ri.Status
                                        WHEN 1 THEN 'Active'
                                        WHEN 2 THEN 'Inactive'
                                        WHEN 3 THEN 'Out-Of-Stock'
                                    END as Status, ri.AvailableItemCount, ri.OfferStartDate, ri.OfferEndDate, ri.About, ri.Terms, ri.SubText as Description
                                    FROM rewarditems ri
                                    LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                                    INNER JOIN ref_playerclassification rpc ON rpc.PClassID = ri.PClassID
                                    LEFT JOIN ref_category rc ON rc.CategoryID = ri.CategoryID
                                    WHERE RewardID = ".$rewardtype." AND ri.Status IN (1,2,3)";
                break;
            case 1: //Active
                $query = "SELECT ri.RewardItemID,rp.PartnerName, ri.ItemName, rc.Description as Category, ri.RequiredPoints as Points,rpc.Description as Eligibility, 
                                    CASE ri.Status
                                        WHEN 1 THEN 'Active'
                                        WHEN 2 THEN 'Inactive'
                                        WHEN 3 THEN 'Out-Of-Stock'
                                    END as Status, ri.AvailableItemCount, ri.OfferStartDate, ri.OfferEndDate, ri.About, ri.Terms, ri.SubText as Description
                                    FROM rewarditems ri
                                    LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                                    INNER JOIN ref_playerclassification rpc ON rpc.PClassID = ri.PClassID
                                    LEFT JOIN ref_category rc ON rc.CategoryID = ri.CategoryID
                                    WHERE RewardID = ".$rewardtype." AND ri.Status = ".$filterby;
                break;
            case 2: //Inactive
                $query = "SELECT ri.RewardItemID,rp.PartnerName, ri.ItemName, rc.Description as Category, ri.RequiredPoints as Points,rpc.Description as Eligibility, 
                                    CASE ri.Status
                                        WHEN 1 THEN 'Active'
                                        WHEN 2 THEN 'Inactive'
                                        WHEN 3 THEN 'Out-Of-Stock'
                                    END as Status, ri.AvailableItemCount, ri.OfferStartDate, ri.OfferEndDate, ri.About, ri.Terms, ri.SubText as Description
                                    FROM rewarditems ri
                                    LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                                    INNER JOIN ref_playerclassification rpc ON rpc.PClassID = ri.PClassID
                                    LEFT JOIN ref_category rc ON rc.CategoryID = ri.CategoryID
                                    WHERE RewardID = ".$rewardtype." AND ri.Status = ".$filterby;
                break;
            case 3: //Out-Of-Stock
                $query = "SELECT ri.RewardItemID,rp.PartnerName, ri.ItemName, rc.Description as Category, ri.RequiredPoints as Points,rpc.Description as Eligibility, 
                                    CASE ri.Status
                                        WHEN 1 THEN 'Active'
                                        WHEN 2 THEN 'Inactive'
                                        WHEN 3 THEN 'Out-Of-Stock'
                                    END as Status, ri.AvailableItemCount, ri.OfferStartDate, ri.OfferEndDate, ri.About, ri.Terms, ri.SubText as Description
                                    FROM rewarditems ri
                                    LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                                    INNER JOIN ref_playerclassification rpc ON rpc.PClassID = ri.PClassID
                                    LEFT JOIN ref_category rc ON rc.CategoryID = ri.CategoryID
                                    WHERE RewardID = ".$rewardtype." AND ri.Status = ".$filterby;
                break;
        }
        
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        $count = count($result);
        
        for($ctr=0; $ctr < $count;$ctr++) {
            if((int)$result[$ctr]['AvailableItemCount'] <= 0 && $result[$ctr]["Status"] != "Out-Of-Stock"){
                $pdo = $connection->beginTransaction();
                try {
                    $updatequery = "UPDATE rewarditems SET Status = 3 WHERE RewardItemID=".$result[$ctr]['RewardItemID'];
                    $sql = Yii::app()->db->createCommand($updatequery);
                    $updateresult = $sql->execute();
                    if($updateresult > 0){
                        try {
                            $pdo->commit();
                            $result[$ctr]["Status"] = "Out-Of-Stock";
                        } catch (CDbException $e) {
                            $pdo->rollback();
                            return array('TransMsg'=>'Error: '. $e->getMessage(),'TransCode'=>2);
                        }
                    } else {
                        return array('TransMsg'=>'Failed to update the status of rewarditems with zero item count..', 'TransCode'=>1);
                    }
                } catch (CDbException $e) {
                    $pdo->rollback();
                    return array('TransMsg'=>'Error: '. $e->getMessage(), 'TransCode'=>2);
                }
                if($filterby != 3 || $filterby != 0){
                    unset($result[$ctr]);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * @Description: For Updating Reward Item/Coupon
     * @Author: aqdepliyan
     * @DateCreated: 2013-09-25
     * @param int $rewarditemid
     * @param int $status
     * @return array
     */
    public function updateRewardStatus($rewarditemid, $status){
        $connection = Yii::app()->db;
        $pdo = $connection->beginTransaction();
        $AID = Yii::app()->session['AID'];
        if($status == 4){
             $query = "UPDATE rewarditems SET Status = $status , DateDeactivated = now_usec(), DeactivatedByAID = $AID  WHERE RewardItemID=".$rewarditemid;
        } else {
             $query = "UPDATE rewarditems SET Status = $status , DateUpdated = now_usec(), UpdatedByAID = $AID  WHERE RewardItemID=".$rewarditemid;
        }
       
        $sql = Yii::app()->db->createCommand($query);
        $updateresult = $sql->execute();
        if($updateresult > 0){
            try {
                $pdo->commit();
                return array('TransMsg'=>'Partner\'s Details is successfully updated.','TransCode'=>0);
            } catch (CDbException $e) {
                $pdo->rollback();
                return array('TransMsg'=>'Error: '. $e->getMessage(),'TransCode'=>2);
            }
        } else {
            return array('TransMsg'=>'No record was updated.', 'TransCode'=>1);
        }
    }
    
    /**
     * @Description: For Fetching Rewards List
     * @Author: aqdepliyan
     * @DateCreated: 2013-09-23
     * @param int $rewarditemid
     * @return array
     */
    public function getRewardDetailsUsingRewardItemID($rewarditemid){
        $connection = Yii::app()->db;
        $query = "SELECT ri.RewardItemID,ri.PartnerID, rp.PartnerName, ri.ItemName, ri.CategoryID, rc.Description as Category, ri.RequiredPoints as Points,ri.PClassID, rpc.Description as Eligibility, 
                            CASE ri.Status
                                WHEN 1 THEN 'Active'
                                WHEN 2 THEN 'Inactive'
                                WHEN 3 THEN 'Out-Of-Stock'
                                WHEN 4 THEN 'Deactivated'
                            END as Status, ri.Status as StatusID, ri.AvailableItemCount, ri.OfferStartDate, ri.OfferEndDate, ri.SubText as Subtext, 
                            ri.PromoName, ri.PromoCode, ri.DrawDate, ri.About, ri.Terms
                            FROM rewarditems ri
                            LEFT JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID
                            INNER JOIN ref_playerclassification rpc ON rpc.PClassID = ri.PClassID
                            LEFT JOIN ref_category rc ON rc.CategoryID = ri.CategoryID
                            WHERE RewardItemID =".$rewarditemid."";
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @Description: For replenishing item available count.
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-01
     * @param int $rewarditemid
     * @param int $newitemcount
     * @return array
     */
    public function replenishItem($rewarditemid, $newitemcount, $currentitemcount, $addeditemcount, $newserialcodeend, $status){
        $connection = Yii::app()->db;
        $CreatedByAID = Yii::app()->session['AID'];
        $pdo = $connection->beginTransaction();
        if($status == 2){
            $query = "UPDATE rewarditems SET AvailableItemCount = $newitemcount, SerialCodeEnd = '$newserialcodeend', DateUpdated = now_usec(),
                            UpdatedByAID = $CreatedByAID WHERE RewardItemID = ".$rewarditemid;
        } else {
            $query = "UPDATE rewarditems SET AvailableItemCount = $newitemcount, SerialCodeEnd = '$newserialcodeend', DateUpdated = now_usec(),
                            UpdatedByAID = $CreatedByAID, Status = 1 WHERE RewardItemID = ".$rewarditemid;
        }
        $sql = Yii::app()->db->createCommand($query);
        $updateresult = $sql->execute();
        if($updateresult > 0){
            try {
                $replenishlogs = "INSERT INTO  replenishmentlogs(RewardItemID, CurrentItemCount, ReplenishItemCount, EndingItemCount, DateCreated, CreatedByAID)
                                    VALUES($rewarditemid, $currentitemcount, $addeditemcount, $newitemcount, now_usec(), $CreatedByAID)";
                $replenishlogssql = Yii::app()->db->createCommand($replenishlogs);
                $insertresult = $replenishlogssql->execute();
                if($insertresult > 0){
                    try {
                        $pdo->commit();
                        return array('TransMsg'=>'Reward Item/Coupon has been successfully replenished.','TransCode'=>0);
                    } catch (CDbException $e) {
                        $pdo->rollback();
                        return array('TransMsg'=>'Error: '. $e->getMessage(),'TransCode'=>2);
                    }
                    
                } else {
                    return array('TransMsg'=>'No log was inserted.', 'TransCode'=>1);
                }
            } catch (CDbException $e) {
                $pdo->rollback();
                return array('TransMsg'=>'Error: '. $e->getMessage(),'TransCode'=>2);
            }
        } else {
            return array('TransMsg'=>'No record was updated.', 'TransCode'=>1);
        }
    }
    
    /**
     * @Description: Get the current inventory balance
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-01
     * @param int $rewarditemid
     * @return array
     */
    public function getCurrentInventory($rewarditemid){
        $connection = Yii::app()->db;
        
        $query = "SELECT AvailableItemCount FROM rewarditems
                  WHERE RewardItemID = ".$rewarditemid;
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    
    
    /**
     * @Description: Update Reward Item Details
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-11
     * @param int $rewarditemid
     * @param int $rewardid
     * @param string $itemname
     * @param int $points
     * @param int $pclassid
     * @param int $status
     * @param string $startdate
     * @param string $enddate
     * @param int $partnerid
     * @param int $categoryid
     * @param string $subtext
     * @param string $about
     * @param string $terms
     * @param string $thblimitedphoto
     * @param string $thboutofstockphoto
     * @param string $ecouponphoto
     * @param string $lmlimitedphoto
     * @param string $lmoutofstockphoto
     * @param string $websliderphoto
     * @return array
     */
    public function UpdateRewardItem($rewarditemid, $rewardid, $itemname, $points, $pclassid, $status, $startdate, $enddate, $partnerid, $categoryid,
                                                                            $subtext, $about,  $terms, $thblimitedphoto, $thboutofstockphoto, $ecouponphoto, 
                                                                            $lmlimitedphoto, $lmoutofstockphoto, $websliderphoto,$drawdate)
    {
            if($drawdate == ''){
                $drawdate = null;
            } else { $drawdate = $drawdate; }
            if($about == ''){
                $about = null;
            } else { $about = $about; }
            if($terms == ''){
                $terms = null;
            } else { $terms = $terms; }
            if($subtext == ''){
                $subtext = null;
            } else { $subtext = $subtext; }
            if($partnerid == ''){
                $partnerid = null;
            } else { $partnerid = $partnerid; }
            if($categoryid == ''){
                $categoryid = null;
            } else { $categoryid = $categoryid; }
            if($thblimitedphoto == ''){
                $thblimited = '';
            } else { $thblimitedphoto = $thblimitedphoto; $thblimited =  "ThumbnailLimitedImage = :thblimited,";}
            if($thboutofstockphoto == ''){
                $thboutofstock = '';
            } else { $thboutofstockphoto = $thboutofstockphoto; $thboutofstock =  "ThumbnailOutOfStockImage = :thboutofstock,";}
            if($ecouponphoto == ''){
                $ecoupon = '';
            } else { $ecouponphoto = $ecouponphoto; $ecoupon =  "ECouponImage = :ecoupon,";}
            if($lmlimitedphoto == ''){
                $lmlimited = '';
            } else { $lmlimitedphoto = $lmlimitedphoto; $lmlimited =  "LearnMoreLimitedImage = :lmlimited,";}
            if($lmoutofstockphoto == ''){
                $lmoutofstock = '';
            } else { $lmoutofstockphoto = $lmoutofstockphoto; $lmoutofstock =  "LearnMoreOutOfStockImage = :lmoutofstock,";}
            if($websliderphoto == ''){
                $webslider = '';
            } else { $websliderphoto = $websliderphoto; $webslider =  "WebsiteSliderImage = :webslider,";}
        
        $connection = Yii::app()->db;

        $updatedbyaid = Yii::app()->session['AID'];
        if($rewardid == 2){
            $query = "UPDATE rewarditems SET ItemName = :itemname, RequiredPoints = :points,
                            PClassID = :pclassid, SubText = :subtext, OfferStartDate = :startdate, OfferEndDate = :enddate, ".$thblimited."
                            ".$thboutofstock." ".$ecoupon." ".$lmlimited." ".$lmoutofstock." ".$webslider." About = :about, Terms = :terms, Status = :status,
                            DateUpdated = now_usec(), UpdatedByAID = :updatedbyaid, DrawDate = :drawdate
                            WHERE RewardItemID = :rewarditemid";
            $command = $connection->createCommand($query);
            $command->bindParam(":itemname", $itemname,PDO::PARAM_STR);
            $command->bindParam(":points", $points, PDO::PARAM_INT);
            $command->bindParam(":pclassid", $pclassid,PDO::PARAM_INT);
            $command->bindParam(":subtext", $subtext);
            $command->bindParam(":startdate", $startdate, PDO::PARAM_STR);
            $command->bindParam(":enddate", $enddate, PDO::PARAM_STR);
            if($thblimited != "")
                $command->bindParam(":thblimited", $thblimitedphoto, PDO::PARAM_STR);
            if($thboutofstock != "")
                $command->bindParam(":thboutofstock", $thboutofstockphoto, PDO::PARAM_STR);
            if($ecoupon != "")
                $command->bindParam(":ecoupon", $ecouponphoto, PDO::PARAM_STR);
            if($lmlimited != "")
                $command->bindParam(":lmlimited", $lmlimitedphoto, PDO::PARAM_STR);
            if($lmoutofstock != "")
                $command->bindParam(":lmoutofstock", $lmoutofstockphoto, PDO::PARAM_STR);
            if($webslider != "")
                $command->bindParam(":webslider", $websliderphoto, PDO::PARAM_STR);
            $command->bindParam(":about", $about);
            $command->bindParam(":terms", $terms);
            $command->bindParam(":status", $status,PDO::PARAM_INT);
            $command->bindParam(":updatedbyaid", $updatedbyaid, PDO::PARAM_INT);
            $command->bindParam(":drawdate", $drawdate, PDO::PARAM_STR);
            $command->bindParam(":rewarditemid", $rewarditemid, PDO::PARAM_INT);
        } else {
            $query = "UPDATE rewarditems SET PartnerID = :partnerid, ItemName = :itemname, CategoryID = :categoryid, RequiredPoints = :points,
                            PClassID = :pclassid, SubText = :subtext, OfferStartDate = :startdate, OfferEndDate = :enddate, ".$thblimited."
                            ".$thboutofstock." ".$ecoupon." ".$lmlimited." ".$lmoutofstock." ".$webslider." About = :about, Terms = :terms, Status = :status,
                            DateUpdated = now_usec(), UpdatedByAID = :updatedbyaid
                            WHERE RewardItemID = :rewarditemid";
            $command = $connection->createCommand($query);
            $command->bindParam(":partnerid", $partnerid);
            $command->bindParam(":itemname", $itemname,PDO::PARAM_STR);
            $command->bindParam(":categoryid", $categoryid);
            $command->bindParam(":points", $points, PDO::PARAM_INT);
            $command->bindParam(":pclassid", $pclassid,PDO::PARAM_INT);
            $command->bindParam(":subtext", $subtext);
            $command->bindParam(":startdate", $startdate, PDO::PARAM_STR);
            $command->bindParam(":enddate", $enddate, PDO::PARAM_STR);
            if($thblimited != "")
                $command->bindParam(":thblimited", $thblimitedphoto, PDO::PARAM_STR);
            if($thboutofstock != "")
                $command->bindParam(":thboutofstock", $thboutofstockphoto, PDO::PARAM_STR);
            if($ecoupon != "")
                $command->bindParam(":ecoupon", $ecouponphoto, PDO::PARAM_STR);
            if($lmlimited != "")
                $command->bindParam(":lmlimited", $lmlimitedphoto, PDO::PARAM_STR);
            if($lmoutofstock != "")
                $command->bindParam(":lmoutofstock", $lmoutofstockphoto, PDO::PARAM_STR);
            if($webslider != "")
                $command->bindParam(":webslider", $websliderphoto, PDO::PARAM_STR);
            $command->bindParam(":about", $about);
            $command->bindParam(":terms", $terms);
            $command->bindParam(":status", $status,PDO::PARAM_INT);
            $command->bindParam(":updatedbyaid", $updatedbyaid, PDO::PARAM_INT);
            $command->bindParam(":rewarditemid", $rewarditemid, PDO::PARAM_INT);
        }

        try {
            $command->execute();
            return array('TransMsg'=>'Reward Item/Coupon has been successfully updated.','TransCode'=>0);
        } catch (CDbException $e) {
            return array('TransMsg'=>'Error: '. $e->getMessage(),'TransCode'=>2);
        }
    }
    
    
    /**
     * @Description: Function for inserting new reward item on rewarditems table.
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-14
     * @param int $partneritemid
     * @param int $rewardid
     * @param string $itemname
     * @param int $points
     * @param int $pclassid
     * @param int $status
     * @param string $startdate
     * @param string $enddate
     * @param int $partnerid
     * @param int $categoryid
     * @param string $subtext
     * @param string $about
     * @param string $terms
     * @param int $itemcount
     * @param string $thblimitedphoto
     * @param string $thboutofstockphoto
     * @param string $ecouponphoto
     * @param string $lmlimitedphoto
     * @param string $lmoutofstockphoto
     * @param string $websliderphoto
     * @param string $promocode
     * @param string $promoname
     * @param string $drawdate
     * @param string $serialcodestart
     * @param string $serialcodeend
     * @return array
     */
    public function InsertRewardItem($partneritemid, $rewardid, $itemname, $points, $pclassid, $status, $startdate, $enddate, $partnerid, $categoryid,
                                                                            $subtext, $about,  $terms,  $itemcount, $thblimitedphoto, $thboutofstockphoto, $ecouponphoto,
                                                                            $lmlimitedphoto, $lmoutofstockphoto, $websliderphoto, $promocode, $promoname, $drawdate, 
                                                                            $serialcodestart, $serialcodeend)
    {
        $AID = Yii::app()->session['AID'];
        $connection = Yii::app()->db;

        $insertrewarditem = "INSERT INTO  rewarditems(PartnerID, PartnerItemID, RewardID, CategoryID, PClassID, ItemName,  AvailableItemCount,
                                                RequiredPoints, PromoCode, PromoName, OfferStartDate, OfferEndDate, DrawDate, SerialCodeStart,  SerialCodeEnd, 
                                                SubText, ThumbnailLimitedImage, ECouponImage, WebsiteSliderImage, LearnMoreLimitedImage,
                                                LearnMoreOutOfStockImage, ThumbnailOutOfStockImage, About, Terms, DateCreated, CreatedByAID, Status)
                                                VALUES(:partnerid, :partneritemid, :rewardid, :categoryid, :pclassid, :itemname, :itemcount, :points, :promocode,
                                                :promoname, :startdate, :enddate, :drawdate, :serialcodestart, :serialcodeend, :subtext, :thblimitedphoto, :ecouponphoto,
                                                :websliderphoto, :lmlimitedphoto, :lmoutofstockphoto, :thboutofstockphoto, :about, :terms, now_usec(), :aid, :status)";
        $command = $connection->createCommand($insertrewarditem);
        $command->bindParam(":partnerid", $partnerid,PDO::PARAM_INT);
        $command->bindParam(":partneritemid", $partneritemid,PDO::PARAM_INT);
        $command->bindParam(":rewardid", $rewardid,PDO::PARAM_INT);
        $command->bindParam(":categoryid", $categoryid,PDO::PARAM_INT);
        $command->bindParam(":pclassid", $pclassid,PDO::PARAM_INT);
        $command->bindParam(":itemname", $itemname,PDO::PARAM_STR);
        $command->bindParam(":itemcount", $itemcount,PDO::PARAM_INT);
        $command->bindParam(":points", $points, PDO::PARAM_INT);
        $command->bindParam(":promocode", $promocode,PDO::PARAM_STR);
        $command->bindParam(":promoname", $promoname,PDO::PARAM_STR);
        $command->bindParam(":startdate", $startdate, PDO::PARAM_STR);
        $command->bindParam(":enddate", $enddate, PDO::PARAM_STR);
        $command->bindParam(":drawdate", $drawdate, PDO::PARAM_STR);
        $command->bindParam(":serialcodestart", $serialcodestart, PDO::PARAM_STR);
        $command->bindParam(":serialcodeend", $serialcodeend, PDO::PARAM_STR);
        $command->bindParam(":subtext", $subtext);
        $command->bindParam(":thblimitedphoto", $thblimitedphoto, PDO::PARAM_STR);
        $command->bindParam(":ecouponphoto", $ecouponphoto, PDO::PARAM_STR);
        $command->bindParam(":websliderphoto", $websliderphoto, PDO::PARAM_STR);
        $command->bindParam(":lmlimitedphoto", $lmlimitedphoto, PDO::PARAM_STR);
        $command->bindParam(":lmoutofstockphoto", $lmoutofstockphoto, PDO::PARAM_STR);
        $command->bindParam(":thboutofstockphoto", $thboutofstockphoto, PDO::PARAM_STR);
        $command->bindParam(":about", $about);
        $command->bindParam(":terms", $terms);
        $command->bindParam(":aid", $AID,PDO::PARAM_INT);
        $command->bindParam(":status", $status,PDO::PARAM_INT);

        try {
            $command->execute();
            $lastinsertedid = $connection->getLastInsertID();
            return array('TransMsg'=>'Reward Item/Coupon has been successfully added.','TransCode'=>0, 'LastInsertID' => $lastinsertedid);
        } catch (CDbException $e) {
            return array('TransMsg'=>'Error: '. $e->getMessage(),'TransCode'=>2);
        }
    }
    
    /**
     * @Description: Use for validating the reward item to be inserted.
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-14
     * @param string $itemname
     * @return array
     */
    public function ValidateItem($itemname){

       $connection = Yii::app()->db;
        
        $query = "SELECT Status FROM rewarditems WHERE ItemName ='".$itemname."'";
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    
    /**
     * @Description: Use to fetch the last PartnerItemID under a specific partner id.
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-15
     * @param int $partnerid
     * @return array
     */
    public function GetPartnerItemID($partnerid){

       $connection = Yii::app()->db;
        
        $query = "SELECT MAX(PartnerItemID) as lastpartneritemid FROM rewarditems WHERE PartnerID =".$partnerid;
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    
    /**
     * @Description: Use to fetch the SerialCodeEnd of a specific rewarditem.
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-15
     * @param int $rewarditemid
     * @return array
     */
    public function GetSerialCodeEnd($rewarditemid){

       $connection = Yii::app()->db;
        
        $query = "SELECT SerialCodeEnd  FROM rewarditems WHERE RewardItemID =".$rewarditemid;
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    
}
?>
