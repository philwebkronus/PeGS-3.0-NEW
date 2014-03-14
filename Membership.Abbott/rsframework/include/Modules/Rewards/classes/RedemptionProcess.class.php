<?php
/**
* @Description: Class for processing redemptions (item/coupon)
* @Author: aqdepliyan
* @DateCreated: 2013-11-08 11:43AM
*/

Class RedemptionProcess extends BaseEntity {
    
    public function RedemptionProcess() {
        //init
        $this->DatabaseType = DatabaseTypes::PDO;
    }


    /**
     * @Description: Process the Redeeming of Items, Updating Tables Connected to the Item Redemption
     * @param int $MID
     * @param int $RewardItemID
     * @param int $Quantity
     * @param int $RedeemedPoints
     * @param string $CardNumber
     * @param int $Source
     * @param string $RedeemedDate
     * @return array
     */
    public function ProcessItemRedemption($MID, $RewardItemID, $Quantity, $RedeemedPoints, $CardNumber, $Source, $RedeemedDate){
        App::LoadModuleClass('Rewards', 'ItemSerialCodes');
        App::LoadModuleClass('Loyalty', 'MemberCards');
        App::LoadModuleClass("Rewards", "RewardItems");
        App::LoadModuleClass("Rewards", "ItemRedemptionLogs");

        $errorLogger = new ErrorLogger();
        $_ItemSerialCodes = new ItemSerialCodes();
        $_MemberCards = new MemberCards();
        $_RewardItems = new RewardItems();
        $_ItemRedemptionLogs = new ItemRedemptionLogs();
        $Source == 1 ? $AID = $MID:$AID = $_SESSION['userinfo']['AID'];
        $totalpoints = $RedeemedPoints/$Quantity;
        
        for($itr = 0; $itr < (int)$Quantity; $itr++)
        { 
            try 
            {
                $_ItemRedemptionLogs->StartTransaction();
                $LastInsertedID = $_ItemRedemptionLogs->insertItemLogs($RedeemedDate, $MID, $RewardItemID, 1, $Source);
                if($LastInsertedID != "" && !$_ItemRedemptionLogs->HasError)
                {
                    $CommonPDO = $_ItemRedemptionLogs->getPDOConnection();
                    
                    //Check Item Serial Code Availability
                    $availableserialcode = $_ItemSerialCodes->getAvailableSerialCodeCount($RewardItemID,1);
                    if(count($availableserialcode) == 1) 
                    { 
                        $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($CardNumber);
                        $OldCP = $PlayerPoints[0]['CurrentPoints'];
                        if($PlayerPoints[0]['CurrentPoints'] >= $totalpoints)
                        {
                            $_MemberCards->StartTransaction();
                            $IsPointsUpdated = $_MemberCards->UpdateCardPoints($MID, $totalpoints);
                            $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($CardNumber);
                            if($IsPointsUpdated > 0 && $PlayerPoints[0]['CurrentPoints'] > 0)
                            {
                                $CurrentItemCount = $_RewardItems->getAvailableItemCount($RewardItemID);
                                if($CurrentItemCount["AvailableItemCount"] >= $Quantity)
                                {
                                    $_RewardItems->setPDOConnection($CommonPDO);
                                    $IsItemCountUpdated = $_RewardItems->updateAvailableItemCount($RewardItemID, $Quantity, $AID);
                                    if($IsItemCountUpdated > 0)
                                    {
                                        $_ItemSerialCodes->setPDOConnection($CommonPDO);
                                        $IsActive = $_RewardItems->CheckStatus($RewardItemID);

                                        if($IsActive['Status'] == "Active")
                                        {
                                            $IsSerialCodeUpdated = $_ItemSerialCodes->updateSerialCodeStatus($AID,$RewardItemID);

                                            if($IsSerialCodeUpdated["IsSuccess"] === true)
                                            {
                                                $SerialCodeSuffix = $IsSerialCodeUpdated["StatusCode"];
                                                $Serial = $_RewardItems->getSerialCodePrefix($RewardItemID);
                                                $PartnerID =$Serial['PartnerID'];
                                                $PartnerItemID =$Serial['PartnerItemID'];
                                                $SerialCode = str_pad($PartnerID, 2, "0", STR_PAD_LEFT).str_pad($PartnerItemID, 2, "0", STR_PAD_LEFT).$SerialCodeSuffix;
                                                $SecurityCode = App::mt_rand_str(8);

                                                //Calculate Validity End Date of the Reward Item.
                                                $date = new DateTime($RedeemedDate);
                                                $date->add(new DateInterval('P6M'));
                                                $validto = $date->format('Y-m-d H:i:s.u');

                                                $IsStatusUpdated = $_ItemRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 1, $MID, $totalpoints,$SerialCode, $SecurityCode, $RedeemedDate, $validto);

                                                if($IsStatusUpdated > 0 && !$_ItemRedemptionLogs->HasError)
                                                {
                                                        $_MemberCards->CommitTransaction();
                                                        $_ItemRedemptionLogs->CommitTransaction();

                                                        $_SESSION["PreviousRedemption"] = $LastInsertedID;
                                                        $_SESSION['RewardOfferCopy']['Quantity'] = 1;
                                                        $_SESSION['RewardOfferCopy']['RedemptionDate'] = $RedeemedDate;
                                                        $_SESSION['RewardOfferCopy']['SecurityCode'][$itr] = $SecurityCode;
                                                        $_SESSION['RewardOfferCopy']['SerialNumber'][$itr] = $SerialCode;
                                                        //Format the Valid Until Date
                                                        $validdate = new DateTime(date($validto));
                                                        $validitydate = $validdate->format("F j, Y");
                                                        $_SESSION['RewardOfferCopy']['ValidUntil'][$itr] = $validitydate;
                                                        $errMsg["LastInsertedID"][$itr] = $LastInsertedID;
                                                        if($itr < (int)$Quantity)
                                                            continue;
                                                } else {
                                                    $_MemberCards->RollBackTransaction();
                                                    $_ItemRedemptionLogs->RollBackTransaction();
                                                    $errMsg["Message"] = "Pending Redemption. Error in updating redemption log.";
                                                    $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]."(".$_ItemRedemptionLogs->getErrors().")");
                                                    App::ClearStatus();
                                                    $errMsg["LastInsertedID"] = $LastInsertedID;
                                                    $errMsg["IsSuccess"] = false;
                                                    return $errMsg;
                                                }
                                            } else {
                                                $_MemberCards->RollBackTransaction();
                                                $_ItemRedemptionLogs->RollBackTransaction();
                                                App::ClearStatus();
                                                
                                                //Update ItemRedemptionLog Status to 2 - Failed
                                                $_ItemRedemptionLogs->StartTransaction();
                                                $IsStatusUpdated = $_ItemRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID);
                                                if($IsStatusUpdated > 0)
                                                {
                                                    $_ItemRedemptionLogs->CommitTransaction();
                                                    switch ($IsSerialCodeUpdated["StatusCode"]) 
                                                    {
                                                        case 1:
                                                            $errMsg["Message"] = "Transaction Failed. [Err: 0001] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Error in locking Item Serial Code table.";
                                                            break;
                                                        case 2:
                                                            $errMsg["Message"] = "Transaction Failed. [Err: 0002] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Error in unlocking Item Serial Code table.";
                                                            break;
                                                        case 3:
                                                            $errMsg["Message"] = "Transaction Failed. [Err: 0003] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Error in updating Item Serial Code table.";
                                                            break;
                                                        case 4:
                                                            $errMsg["Message"] = "Transaction Failed. Serial Code is unavailable.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Serial Code is unavailable.";
                                                            break;
                                                    }
                                                    $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                                } else {
                                                    $_ItemRedemptionLogs->RollBackTransaction();
                                                    App::ClearStatus();
                                                    switch ($IsSerialCodeUpdated["StatusCode"]) 
                                                    {
                                                        case 1:
                                                            $errMsg["Message"] = "Pending Redemption. [Err: 0001] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Error in locking Item Serial Code table.";
                                                            break;
                                                        case 2:
                                                            $errMsg["Message"] = "Pending Redemption. [Err: 0002] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Error in unlocking Item Serial Code table.";
                                                            break;
                                                        case 3:
                                                            $errMsg["Message"] = "Pending Redemption. [Err: 0003] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Error in updating Item Serial Code table.";
                                                            break;
                                                        case 4:
                                                            $errMsg["Message"] = "Pending Redemption. Serial Code is unavailable.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Serial Code is unavailable.";
                                                            break;
                                                    }
                                                    $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                                }
                                                $errMsg["LastInsertedID"] = $LastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                return $errMsg;
                                            }
                                        } else {
                                            $_MemberCards->RollBackTransaction();
                                            $_ItemRedemptionLogs->RollBackTransaction();
                                            if($IsActive['Status'] != "Active" && $IsActive['Status'] != "Deleted") 
                                            {
                                                $errMsg["Message"] = "Transaction Failed. The Item you try to redeem is currently ".$IsActive["Status"].".";
                                                $hiddenmsg = "Transaction Failed. The Item you try to redeem is currently ".$IsActive["Status"]." [CardNumber: ".$CardNumber."].";
                                                $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $hiddenmsg);
                                                App::ClearStatus();
                                                $errMsg["LastInsertedID"] = $LastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                return $errMsg;
                                            } else {
                                                $errMsg["Message"] = "Transaction Failed. This Reward Item  no longer exists.";
                                                $hiddenmsg = "Transaction Failed. This Reward Item  no longer exists [CardNumber: ".$CardNumber."].";
                                                $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $hiddenmsg);
                                                App::ClearStatus();
                                                $errMsg["LastInsertedID"] = $LastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                return $errMsg;
                                            } 
                                        }
                                    } else {
                                        $_MemberCards->RollBackTransaction();
                                        $_ItemRedemptionLogs->RollBackTransaction();
                                        App::ClearStatus();
                                        //Update ItemRedemptionLog Status to 2 - Failed
                                        $_ItemRedemptionLogs->StartTransaction();
                                        $IsStatusUpdated = $_ItemRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID);
                                        if($IsStatusUpdated > 0)
                                        {
                                            $_ItemRedemptionLogs->CommitTransaction();
                                            $errMsg["Message"] = "Transaction Failed. Failed in updating item inventory.";
                                            $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                        } else {
                                            $_ItemRedemptionLogs->RollBackTransaction();
                                            App::ClearStatus();
                                            $errMsg["Message"] = "Pending Redemption. Failed in updating item inventory.";
                                            $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                        }
                                        $errMsg["LastInsertedID"] = $LastInsertedID;
                                        $errMsg["IsSuccess"] = false;
                                        return $errMsg;
                                    }
                                } else {
                                    $_MemberCards->RollBackTransaction();
                                    $_ItemRedemptionLogs->RollBackTransaction();
                                    App::ClearStatus();
                                    //Update ItemRedemptionLog Status to 2 - Failed
                                    $_ItemRedemptionLogs->StartTransaction();
                                    $IsStatusUpdated = $_ItemRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID);
                                    if($IsStatusUpdated > 0)
                                    {
                                        $_ItemRedemptionLogs->CommitTransaction();
                                        $errMsg["Message"] = "Transaction Failed. Number of available item is insufficient.";
                                        $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                    } else {
                                        $_ItemRedemptionLogs->RollBackTransaction();
                                        App::ClearStatus();
                                        $errMsg["Message"] = "Pending Redemption. Number of available item is insufficient.";
                                        $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                    }
                                    $errMsg["LastInsertedID"] = $LastInsertedID;
                                    $errMsg["IsSuccess"] = false;
                                    return $errMsg;
                                }
                            } else {
                                $_MemberCards->RollBackTransaction();
                                $_ItemRedemptionLogs->RollBackTransaction();
                                App::ClearStatus();
                                //Update ItemRedemptionLog Status to 2 - Failed
                                $_ItemRedemptionLogs->StartTransaction();
                                $IsStatusUpdated = $_ItemRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID);
                                if($IsStatusUpdated > 0)
                                {
                                    $_ItemRedemptionLogs->CommitTransaction();
                                    $errMsg["Message"] = "Transaction Failed. Failed in updating Card points. Card may have insufficient points.";
                                    $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                } else {
                                    $_ItemRedemptionLogs->RollBackTransaction();
                                    App::ClearStatus();
                                    $errMsg["Message"] = "Pending Redemption. Failed in updating Card points. Card may have insufficient points";
                                    $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                }
                                $errMsg["LastInsertedID"] = $LastInsertedID;
                                $errMsg["IsSuccess"] = false;
                                return $errMsg;
                            }
                        } else {
                            $_ItemRedemptionLogs->RollBackTransaction();
                            App::ClearStatus();
                            //Update ItemRedemptionLog Status to 2 - Failed
                            $_ItemRedemptionLogs->StartTransaction();
                            $IsStatusUpdated = $_ItemRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID);
                            if($IsStatusUpdated > 0)
                            {
                                $_ItemRedemptionLogs->CommitTransaction();
                                $errMsg["Message"] = "Transaction Failed. Card may have insufficient points.";
                                $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                            } else {
                                $_ItemRedemptionLogs->RollBackTransaction();
                                App::ClearStatus();
                                $errMsg["Message"] = "Pending Redemption. Card may have insufficient points.";
                                $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                            }
                            $errMsg["LastInsertedID"] = $LastInsertedID;
                            $errMsg["IsSuccess"] = false;
                            return $errMsg;
                        }
                    } else {
                        $_ItemRedemptionLogs->CommitTransaction();
                        App::ClearStatus();

                        //Update ItemRedemptionLog Status to 2 - Failed
                        $_ItemRedemptionLogs->StartTransaction();
                        $IsStatusUpdated = $_ItemRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID);
                        if($IsStatusUpdated > 0)
                        {
                            $_ItemRedemptionLogs->CommitTransaction();
                            $errMsg["Message"] = "Transaction Failed. Serial Code is unavailable.";
                            $errMsg["HiddenMessage"] = "Transaction Failed. Serial Code is unavailable.";
                            $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                        } else {
                            $_ItemRedemptionLogs->RollBackTransaction();
                            App::ClearStatus();
                            $errMsg["Message"] = "Pending Redemption. Serial Code is unavailable.";
                            $errMsg["HiddenMessage"] = "Pending Redemption. Serial Code is unavailable.";
                            $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                        }
                        $errMsg["LastInsertedID"] = $LastInsertedID;
                        $errMsg["IsSuccess"] = false;
                        return $errMsg;
                    }
                } else {
                    $_ItemRedemptionLogs->RollBackTransaction();
                    $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", App::GetErrorMessage());
                    App::ClearStatus();
                    $errMsg["Message"] = "Transaction Failed. Error in redemption logging.";
                    $errMsg["LastInsertedID"] = "";
                    $errMsg["IsSuccess"] = false;
                    return $errMsg;
                }
            } catch (Exception $error){
                $_ItemRedemptionLogs->RollBackTransaction();
                App::ClearStatus();
                $errorLogger->log($errorLogger->logdate, "[ITEM REDEMPTION ERROR] ", $error->getMessage());
                $errMsg["Message"] = "Transaction Failed. Failed to Start Item Redemption.";
                $errMsg["LastInsertedID"] = "";
                $errMsg["IsSuccess"] = false;
                return $errMsg;
            }
        }
        
        $errMsg["Message"] = "Player Redemption: Transaction Successful.";
        $errMsg["OldCP"] = $OldCP;
        $errMsg["IsSuccess"] = true;
        return $errMsg;
    }
    
    
    /**
     * @Description: Process the Redeeming of Coupons, Updating Tables Connected to the Coupon Redemption
     * @Author: aqdepliyan 
     * @DateCreated: 2013-10-24
     * @param int $MID
     * @param int $RewardItemID
     * @param int $Quantity
     * @param int $RedeemedPoints
     * @param string $CardNumber
     * @param int $Source
     * @param string $RedeemedDate
     * @return array
     */
    public function ProcessCouponRedemption($MID, $RewardItemID, $Quantity, $RedeemedPoints, $CardNumber, $Source, $RedeemedDate){
        App::LoadModuleClass('Rewards', 'RaffleCoupons');
        App::LoadModuleClass('Loyalty', 'MemberCards');
        App::LoadModuleClass("Rewards", "CouponBatches");
        App::LoadModuleClass("Rewards", "RewardItems");
        App::LoadModuleClass("Rewards", "CouponRedemptionLogs");
        
        $errorLogger = new ErrorLogger();
        $_RaffleCoupons = new RaffleCoupons();
        $_MemberCards = new MemberCards();
        $_CouponBatches = new CouponBatches();
        $_RewardItems = new RewardItems();
        $_CouponRedemptionLogs = new CouponRedemptionLogs();

        //Set Table for raffle coupon based on active coupon batch.
        $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
        if(isset($getRaffleCouponSuffix[0]) && $getRaffleCouponSuffix[0]['CouponBatchID'] != ""){
            $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
            //$_RaffleCoupons->TableName = "rafflecoupons";
            $_CouponRedemptionLogs->StartTransaction();
            $Source == 1 ? $AID = $MID:$AID=$_SESSION['userinfo']['AID'];
            
            try
            {
                //Insert Coupon Redemption Log, initial status is 0 - pending
                $LastInsertedID = $_CouponRedemptionLogs->insertCouponLogs($MID, $RewardItemID, $Quantity,$Source, $RedeemedDate);
                if($LastInsertedID != "" && !$_CouponRedemptionLogs->HasError){
                    
                    //Check the Available Raffle Coupon
                    $availablecoupon = $_RaffleCoupons->getAvailableCoupons($RewardItemID, $Quantity);
                    if(count($availablecoupon) == $Quantity){
                            //Build Common PDO for the nested tables under same database
                            $CommonPDO = $_CouponRedemptionLogs->getPDOConnection();
                            
                            //Get the Current Points for validation
                            $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($CardNumber);
                            $OldCP = $PlayerPoints[0]['CurrentPoints'];
                            if($PlayerPoints[0]['CurrentPoints'] >= $RedeemedPoints){
                                
                                //Start transaction for membercards since it in a separate database.
                                //Update the card points (deduct the total redeemed points)
                                $_MemberCards->StartTransaction();
                                $IsPointsUpdated = $_MemberCards->UpdateCardPoints($MID, $RedeemedPoints);
                                
                                //Check if the points is greater than or  equal to 0
                                $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($CardNumber);
                                if($IsPointsUpdated > 0 && $PlayerPoints[0]['CurrentPoints'] >= 0){
                                    $_RaffleCoupons->setPDOConnection($CommonPDO);
                                    
                                    //Get and Check Status of the Reward Item
                                    $IsActive = $_RewardItems->CheckStatus($RewardItemID);
                                    if($IsActive["Status"] == "Active"){
                                        
                                        //Update the raffle coupons
                                        $RaffleCouponResults = $_RaffleCoupons->updateRaffleCouponsStatus($Quantity, $LastInsertedID, $RewardItemID, $AID);
                                        if(!$_RaffleCoupons->HasError && $RaffleCouponResults["IsSuccess"] === true){
                                            
                                            //Get Details for Serial Code and Coupon Series For this Reward e-Coupon
                                            $redemptioninfo = $_RaffleCoupons->getCouponRedemptionInfo($LastInsertedID);
                                            $arrcouponredemptionloginfo = $redemptioninfo[0];
                                            $mincouponnumber = str_pad($arrcouponredemptionloginfo["MinCouponNumber"], 7, "0", STR_PAD_LEFT);
                                            $maxcouponnumber = str_pad($arrcouponredemptionloginfo["MaxCouponNumber"], 7, "0", STR_PAD_LEFT);

                                            //Prepare the Coupon Series For this Reward e-Coupon
                                            if ($arrcouponredemptionloginfo["MinCouponNumber"] == $arrcouponredemptionloginfo["MaxCouponNumber"]) {
                                                $CouponSeries = $mincouponnumber;
                                            } else {
                                                $CouponSeries = $mincouponnumber . " - " . $maxcouponnumber;
                                            }

                                            //Prepare the Serial Code and Security Code For this Reward e-Coupon
                                            $SerialCode = str_pad($LastInsertedID, 7, "0", STR_PAD_LEFT) . "A" . $_RaffleCoupons->getMod10($mincouponnumber) . "B" . $_RaffleCoupons->getMod10($maxcouponnumber);
                                            $SecurityCode = App::mt_rand_str(8);

                                            $IsStatusUpdated = $_CouponRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 1, $MID, $RedeemedPoints, $SerialCode, $SecurityCode);

                                            if($IsStatusUpdated > 0){
                                                $_MemberCards->CommitTransaction();
                                                $_CouponRedemptionLogs->CommitTransaction();
                                                
                                                //On-Success of Redemption, Build Sessions for needed data and Return an array of response.
                                                $_SESSION["PreviousRedemption"] = $LastInsertedID;
                                                $_SESSION['RewardOfferCopy']['CouponSeries'] = $CouponSeries;
                                                $_SESSION['RewardOfferCopy']['Quantity'] = $Quantity;
                                                $_SESSION['RewardOfferCopy']['RedemptionDate'] = $RedeemedDate;
                                                $_SESSION['RewardOfferCopy']['CheckSum'] = $SecurityCode;
                                                $_SESSION['RewardOfferCopy']['SerialNumber'] = $SerialCode;
                                                
                                                $errMsg["Message"] = "Player Redemption: Transaction Successful.";
                                                $errMsg["LastInsertedID"] = $LastInsertedID;
                                                $errMsg["IsSuccess"] = true;
                                                $errMsg["OldCP"] = $OldCP;
                                                return $errMsg;
                                            } else {
                                                $_MemberCards->RollBackTransaction();
                                                $_CouponRedemptionLogs->RollBackTransaction();
                                                $errMsg["Message"] = "Pending Redemption. Error in updating redemption log.";
                                                $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
                                                App::ClearStatus();
                                                $errMsg["LastInsertedID"] = $LastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                return $errMsg;
                                            }
                                        } else {
                                            $_MemberCards->RollBackTransaction();
                                            $_CouponRedemptionLogs->RollBackTransaction();
                                             App::ClearStatus();
                                             
                                            //Update CouponRedemptionLog Status to 2 - Failed
                                            $_CouponRedemptionLogs->StartTransaction();
                                            $IsStatusUpdated = $_CouponRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID, 0);
                                            if($IsStatusUpdated > 0){
                                                $_CouponRedemptionLogs->CommitTransaction();
                                                switch ($RaffleCouponResults["StatusCode"]) {
                                                    case 1:
                                                        $errMsg["Message"] = "Transaction Failed. [Err: 0001] Error in transactional table.";
                                                        $errMsg["HiddenMessage"] = "Transaction Failed. Error in locking Raffle Coupons table.";
                                                        break;
                                                    case 2:
                                                        $errMsg["Message"] = "Transaction Failed. [Err: 0002] Error in transactional table.";
                                                        $errMsg["HiddenMessage"] = "Transaction Failed. Error in unlocking Raffle Coupons table.";
                                                        break;
                                                    case 3:
                                                        $errMsg["Message"] = "Transaction Failed. [Err: 0003] Error in transactional table.";
                                                        $errMsg["HiddenMessage"] = "Transaction Failed. Error in updating Raffle Coupons table.";
                                                        break;
                                                    case 4:
                                                        $errMsg["Message"] = "Transaction Failed. Raffle Coupons is either insufficient or unavailable.";
                                                        $errMsg["HiddenMessage"] = "Transaction Failed. Raffle Coupons is either insufficient or unavailable.";
                                                        break;
                                                }
                                                $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                            } else {
                                                $_CouponRedemptionLogs->RollBackTransaction();
                                                App::ClearStatus();
                                                switch ($RaffleCouponResults["StatusCode"]) {
                                                    case 1:
                                                        $errMsg["Message"] = "Pending Redemption. [Err: 0001] Error in transactional table..";
                                                        $errMsg["HiddenMessage"] = "Pending Redemption. Error in locking Item Serial Code table.";
                                                        break;
                                                    case 2:
                                                        $errMsg["Message"] = "Pending Redemption. [Err: 0002] Error in transactional table..";
                                                        $errMsg["HiddenMessage"] = "Pending Redemption. Error in unlocking Item Serial Code table.";
                                                        break;
                                                    case 3:
                                                        $errMsg["Message"] = "Pending Redemption. [Err: 0003] Error in transactional table..";
                                                        $errMsg["HiddenMessage"] = "Pending Redemption. Error in updating Item Serial Code table.";
                                                        break;
                                                    case 4:
                                                        $errMsg["Message"] = "Pending Redemption. Raffle Coupons is either insufficient or unavailable.";
                                                        $errMsg["HiddenMessage"] = "Pending Redemption. Raffle Coupons is either insufficient or unavailable.";
                                                        break;
                                                }
                                                $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                            }
                                            $errMsg["LastInsertedID"] = $LastInsertedID;
                                            $errMsg["IsSuccess"] = false;
                                            return $errMsg;
                                        }
                                    } else {
                                        $_MemberCards->RollBackTransaction();
                                        $_CouponRedemptionLogs->RollBackTransaction();
                                        if($IsActive['Status'] != "Active" && $IsActive['Status'] != "Deleted") {
                                            $errMsg["Message"] = "Transaction Failed. The Item you try to redeem is currently ".$IsActive["Status"].".";
                                            $hiddenmsg = "Transaction Failed. The Item you try to redeem is currently ".$IsActive["Status"]."[CardNumber: ".$CardNumber."].";
                                            $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $hiddenmsg);
                                            App::ClearStatus();
                                            $errMsg["LastInsertedID"] = $LastInsertedID;
                                            $errMsg["IsSuccess"] = false;
                                            return $errMsg;
                                        } else {
                                            $errMsg["Message"] = "Transaction Failed. This Reward Item  no longer exists.";
                                            $hiddenmsg = "Transaction Failed. This Reward Item  no longer exists [CardNumber: ".$CardNumber."].";
                                            $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $hiddenmsg);
                                            App::ClearStatus();
                                            $errMsg["LastInsertedID"] = $LastInsertedID;
                                            $errMsg["IsSuccess"] = false;
                                            return $errMsg;
                                        } 
                                    }
                                } else {
                                    $_MemberCards->RollBackTransaction();
                                    $_CouponRedemptionLogs->RollBackTransaction();
                                    
                                    //Update CouponRedemptionLog Status to 2 - Failed
                                    $_CouponRedemptionLogs->StartTransaction();
                                    $IsStatusUpdated = $_CouponRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID, 0);
                                    if($IsStatusUpdated > 0){
                                        $errMsg["Message"] = "Transaction Failed. Failed in updating Card points. Card may have insufficient points.";
                                        $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
                                    } else {
                                        $errMsg["Message"] = "Pending Redemption. Failed in updating Card points. Card may have insufficient points.";
                                        $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
                                    }
                                    $errMsg["IsSuccess"] = false;
                                    App::ClearStatus();
                                    return $errMsg;
                                }
                            } else {
                                $_CouponRedemptionLogs->RollBackTransaction();
                                
                                //Update CouponRedemptionLog Status to 2 - Failed
                                $_CouponRedemptionLogs->StartTransaction();
                                $IsStatusUpdated = $_CouponRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID, 0);
                                if($IsStatusUpdated > 0){
                                    $errMsg["Message"] = "Transaction Failed. Card may have insufficient points.";
                                    $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
                                } else {
                                    $errMsg["Message"] = "Pending Redemption. Card may have insufficient points.";
                                    $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
                                }
                                $errMsg["LastInsertedID"] = $LastInsertedID;
                                $errMsg["IsSuccess"] = false;
                                App::ClearStatus();
                                return $errMsg;
                            }
                    } else {
                            $_CouponRedemptionLogs->CommitTransaction();
                            
                            //Update CouponRedemptionLog Status to 2 - Failed
                            $_CouponRedemptionLogs->StartTransaction();
                            $IsStatusUpdated = $_CouponRedemptionLogs->updateLogsStatus($LastInsertedID, $Source, 2, $MID, 0);
                            if($IsStatusUpdated > 0){
                                $_CouponRedemptionLogs->CommitTransaction();
                                $errMsg["Message"] = "Transaction Failed. Raffle Coupons is either insufficient or unavailable.";
                                $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $RaffleCouponResults);
                                
                            } else {
                                $_CouponRedemptionLogs->RollBackTransaction();
                                $errMsg["Message"] = "Pending Redemption. Raffle Coupons is either insufficient or unavailable.";
                                $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $RaffleCouponResults);
                            }
                            $errMsg["LastInsertedID"] = $LastInsertedID;
                            $errMsg["IsSuccess"] = false;
                            App::ClearStatus();
                            return $errMsg;
                    }
                } else {
                    $_CouponRedemptionLogs->RollBackTransaction();
                    $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", App::GetErrorMessage());
                    App::ClearStatus();
                    $errMsg["LastInsertedID"] = "";
                    $errMsg["Message"] = "Transaction Failed. Error in redemption logging.";
                    $errMsg["IsSuccess"] = false;
                    return $errMsg;
                }
            }  catch (Exception $error){
                $_CouponRedemptionLogs->RollBackTransaction();
                $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $error->getMessage());
                $errMsg["Message"] = "Transaction Failed. Failed to Start Coupon Redemption.";
                $errMsg["LastInsertedID"] = "";
                $errMsg["IsSuccess"] = false;
                return $errMsg;
            }
        } else {
            $errMsg["Message"] = "Transaction Failed. Raffle Coupons is unavailable.";
            $errorLogger->log($errorLogger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
            $errMsg["LastInsertedID"] = "";
            $errMsg["IsSuccess"] = false;
            return $errMsg;
        }
    }
    
}

?>
