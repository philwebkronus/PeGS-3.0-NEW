<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Processing
 * @date 07-01-2014
 * @author fdlsison
 * @purpose class for processing function(s)
 */
class Processing
{
    //process the redeeming of coupons, updating tables connected to coupon redemption
    public function processCouponRedemption($MID, $rewardItemID, $quantity, $redeemedPoints, $cardNumber, $source, $redeemedDate) {
        $raffleCouponsModel = new RaffleCouponsModel();
        $memberCardsModel = new MemberCardsModel();
        $couponBatchesModel = new CouponBatchesModel();
        $rewardItemsModel = new RewardItemsModel();
        $couponRedemptionLogsModel = new CouponRedemptionLogsModel();
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $pcwsWrapper = new PcwsWrapper();
        
        $apiMethod = 8;
        $oldCurrentPoints = 0;
        
        //set table for raffle coupon based on active coupon batch
        $raffleCouponSuffix = $couponBatchesModel->getRaffleCouponSuffix();
        if(isset($raffleCouponSuffix) && $raffleCouponSuffix['CouponBatchID'] != '') {
            
            $AID = $MID;
            //$module = 'Coupon Redemption';
            
            try {
                //insert to coupon redemption log, initial status is 0 - pending
                $lastInsertedID = $couponRedemptionLogsModel->insertCouponLogs($MID, $rewardItemID, $quantity, $redeemedDate);
                
                if($lastInsertedID != '' && $lastInsertedID > 0) {
                    
                    //check available raffle coupon
                    $availableCoupon = $raffleCouponsModel->getAvailableCoupons($rewardItemID, $quantity);
                    
                    if(count($availableCoupon) == $quantity) {
                        //get current points for validation
                        //$playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                        $playerPoints = $pcwsWrapper->getCompPoints($cardNumber, 0);
                        $playerPoints = $playerPoints['GetCompPoints']['CompBalance']; 
                        
                        $oldCurrentPoints = $playerPoints;
                        if($oldCurrentPoints >= $redeemedPoints) {
                            //update card points (deduct the total redeemed points)
                            $isPointsUpdated = $memberCardsModel->updateCardPoints($MID, $redeemedPoints);
                            $amt = $oldCurrentPoints - $redeemedPoints;
                            $isPointsDeducted = $pcwsWrapper->deductCompPoints($cardNumber, $amt, '', 0);
                            $playerPoints = $pcwsWrapper->getCompPoints($cardNumber, 0);
                            $playerPoints = $playerPoints['GetCompPoints']['CompBalance'];
                            $oldCurrentPoints = $playerPoints;
                            //$playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                            
                            //check if points is greater than or equal to 0
                            if($isPointsUpdated > 0 && $isPointsDeducted && $oldCurrentPoints >= 0) {
                                
                                //get and check status of the reward item
                                $isActive = $rewardItemsModel->checkStatus($rewardItemID);
                                
                                if($isActive['Status'] == 'Active') {
                                    //update raffle coupons
                                    $raffleCouponResults = $raffleCouponsModel->updateRaffleCouponsStatus($quantity, $lastInsertedID, $rewardItemID, $AID);
//                                    
                                    //$raffleCouponResults = $raffleCouponsModel->lockRaffleCoupons();
                                    if($raffleCouponResults['IsSuccess'] === true) {
                             
                                        //get details for serial code and coupon series for this reward e-coupon
                                        $redemptionInfo = $raffleCouponsModel->getCouponRedemptionInfo($lastInsertedID);
                                        $minCouponNumber = str_pad($redemptionInfo['MinCouponNumber'], 7, "0", STR_PAD_LEFT);
                                        $maxCouponNumber = str_pad($redemptionInfo['MaxCouponNumber'], 7, "0", STR_PAD_LEFT);

                                        //prepare coupon series for this reward e-coupon
                                        if($redemptionInfo['MinCouponNumber'] == $redemptionInfo['MaxCouponNumber'])
                                            $couponSeries = $minCouponNumber;
                                        else
                                            $couponSeries = $minCouponNumber . " - " . $maxCouponNumber;

                                        //prepare serial code and security code for this reward e-coupon
                                        $serialCode = str_pad($lastInsertedID, 7, "0", STR_PAD_LEFT) . "A" . Utilities::getMod10($minCouponNumber) . "B" . Utilities::getMod10($maxCouponNumber);
                                        $securityCode = Utilities::mt_rand_str(8);

                                        $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 1, $MID, $redeemedPoints, $serialCode, $securityCode);
                                       // var_dump($isStatusUpdated);
                                        if($isStatusUpdated > 0) {
     
                                            //if redemption is successful, build sessions for needed data and return an array of response.
                                            $session['PreviousRedemption'] = $lastInsertedID;
                                            $session['RewardOfferCopy']['CouponSeries'] = $couponSeries;
                                            $session['RewardOfferCopy']['Quantity'] = $quantity;
                                            $session['RewardOfferCopy']['RedemptionDate'] = $redeemedDate;
                                            $session['RewardOfferCopy']['CheckSum'] = $securityCode;
                                            $session['RewardOfferCopy']['SerialNumber'] = $serialCode;
                                            
                                            

//                                                    $transMsg = 'No Error, Transaction successful.';
//                                                    $errorCode = 0;
//                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode, $lastInsertedID, $oldCurrentPoints)));

//                                            $resultArray = array('LastInsertedID' => $lastInsertedID, 'OldCurrentPoints' => $oldCurrentPoints);
//                                            return $resultArray;
                                            $errMsg["Message"] = "Player Redemption: Transaction Successful.";
                                            $errMsg["LastInsertedID"] = $lastInsertedID;
                                            $errMsg["IsSuccess"] = true;
                                            $errMsg["OldCP"] = $oldCurrentPoints;
                                            $errMsg["CouponSeries"] = $session['RewardOfferCopy']['CouponSeries'];
                                            $errMsg["Quantity"] = $session['RewardOfferCopy']['Quantity'];
                                            $errMsg["CheckSum"] = $session['RewardOfferCopy']['CheckSum'];
                                            $errMsg["SerialNumber"] = $session['RewardOfferCopy']['SerialNumber'];
                                            $errMsg["RedemptionDate"] = $session['RewardOfferCopy']['RedemptionDate'];
                                            return $errMsg;
                                        }
                                        else {
                                            $errMsg["Message"] = "Pending Redemption. Error in updating redemption log.";
                                            $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
                                            $apiDetails = 'REDEEMITEMS-UpdateLogsStatus-Failed: Updating of Logs status of couponredemptionlogs. CouponRedemptionLogID = '.$lastInsertedID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            }
                                            $errMsg["LastInsertedID"] = $lastInsertedID;
                                            $errMsg["IsSuccess"] = false;
                                            return $errMsg;
       
                                        }
                                    }
                                    else {
                                            
                                        //update coupon redemption logs status to 2 - Failed
                                        $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID, 0);
                                        
//                                        $transMsg = 'Error in Locking.';
//                                        $errorCode = 27;
//                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                        if($isStatusUpdated > 0) {
                                            switch ($raffleCouponResults["StatusCode"]) {
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
                                            $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                        }
                                        else {
                                            switch ($raffleCouponResults["StatusCode"]) {
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
                                            $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                        }
                                        $errMsg["LastInsertedID"] = $lastInsertedID;
                                        $errMsg["IsSuccess"] = false;
                                        return $errMsg;
                                    }   
                                }
                                else {
                                    if($isActive['Status'] != 'Active' && $isActive['Status'] != "Deleted") {
                                        $errMsg["Message"] = "Transaction Failed. The Item you try to redeem is currently ".$isActive["Status"].".";
                                        $hiddenmsg = "Transaction Failed. The Item you try to redeem is currently ".$isActive["Status"]."[CardNumber: ".$cardNumber."].";
                                        $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $hiddenmsg);
                                        $apiDetails = 'REDEEMITEMS-Failed: Item being redeemed is '.$isActive['Status'].'. RewardItemID = '.$rewardItemID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        }
                                        $errMsg["LastInsertedID"] = $lastInsertedID;
                                        $errMsg["IsSuccess"] = false;
                                        return $errMsg;
                                    }
                                    else {
                                        $errMsg["Message"] = "Transaction Failed. This Reward Item  no longer exists.";
                                        $hiddenmsg = "Transaction Failed. This Reward Item  no longer exists [CardNumber: ".$cardNumber."].";
                                        $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $hiddenmsg);
                                        $errMsg["LastInsertedID"] = $lastInsertedID;
                                        $errMsg["IsSuccess"] = false;
                                        return $errMsg; 
                                    }
                                }
                            }
                            else {
                                //update couponredemptionlog status to 2 - Failed
                                $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID, 0);
                                if($isStatusUpdated > 0) {
                                    $errMsg["Message"] = "Failed in updating Card points. Card may have insufficient points.";
                                    $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
//                                    $transMsg = 'Transaction failed. Card has insufficient points.';
//                                    $errorCode = 24;
//                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                                    $logMessage = 'Transaction failed. Card has insufficient points.';
//                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
//                                    $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$playerPoints['CurrentPoints'];
//                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
//                                    if($isInserted == 0) {
//                                        $logMessage = "Failed to insert to APILogs.";
//                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
//                                    }
                                }
                                else {
                                    $errMsg["Message"] = "Pending Redemption. Failed in updating Card points. Card may have insufficient points.";
                                    $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
//                                    $transMsg = 'Pending Redemption. Card has insufficient points.';
//                                    $errorCode = 44;
//                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                                    $logMessage = 'Pending Redemption. Card has insufficient points';
//                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
//                                    $apiDetails = 'REDEEMITEMS-Failed: Pending Redemption. Card has insufficient points. CurrenPoints = '.$playerPoints['CurrentPoints'];
//                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
//                                    if($isInserted == 0) {
//                                        $logMessage = "Failed to insert to APILogs.";
//                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
//                                    }
                                }
                                $apiDetails = 'REDEEMITEMS-Failed: Pending Redemption. Card has insufficient points. CurrenPoints = '.$playerPoints['CurrentPoints'];
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                }
                                $errMsg["IsSuccess"] = false;
                                return $errMsg;
                            }
                        } else {
                            //update couponredemptionlog status to 2 - Failed
                            $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID, 0);
                            if($isStatusUpdated > 0) {
                                $errMsg["Message"] = "Transaction Failed. Card may have insufficient points.";
                                $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
//                                $transMsg = 'Transaction failed. Card has insufficient points.';
//                                $errorCode = 24;
//                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                
                            }
                            else {
//                                $transMsg = 'Pending Redemption. Card has insufficient points.';
//                                $errorCode = 44;
//                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                $errMsg["Message"] = "Pending Redemption. Card may have insufficient points.";
                                $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
                            }
                            $errMsg["LastInsertedID"] = $lastInsertedID;
                            $errMsg["IsSuccess"] = false;
                            return $errMsg;
                        }   
                    }
                    else {
                        //update couponredemptionlog status to 2 - Failed
                        $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID, 0);
                        if($isStatusUpdated > 0) {
                            $errMsg["Message"] = "Transaction Failed. Raffle Coupons is either insufficient or unavailable.";
                            $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $raffleCouponResults);
                        }
                        else {
//                          $_CouponRedemptionLogs->RollBackTransaction();
                            $errMsg["Message"] = "Pending Redemption. Raffle Coupons is either insufficient or unavailable.";
                            $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $raffleCouponResults);   
                        }
                        $errMsg["LastInsertedID"] = $lastInsertedID;
                        $errMsg["IsSuccess"] = false;
                        return $errMsg;
                    }
                }
                else {
                    $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $e->getMessage());
                    $errMsg["LastInsertedID"] = "";
                    $errMsg["Message"] = "Transaction Failed. Error in redemption logging.";
                    $errMsg["IsSuccess"] = false;
                    return $errMsg;
                }              
            } catch (Exception $e) {
                $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $e->getMessage());
                $errMsg["Message"] = "Transaction Failed. Failed to Start Coupon Redemption.";
                $errMsg["LastInsertedID"] = "";
                $errMsg["IsSuccess"] = false;
                return $errMsg;
            }
        }
        else {
            $errMsg["Message"] = "Transaction Failed. Raffle Coupons is unavailable.";
            $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR] ", $errMsg["Message"]);
            $errMsg["LastInsertedID"] = "";
            $errMsg["IsSuccess"] = false;
            return $errMsg;
        }
    }
    
    //process redeeming of items, updating tables connected to item redemption
    public function processItemRedemption($MID, $rewardItemID, $quantity, $redeemedPoints, $cardNumber, $source, $redeemedDate) {
     
     //var_dump($MID, $rewardItemID, $quantity, $redeemedPoints, $cardNumber, $redeemedDate);
        $apiMethod = 8;
        $oldCurrentPoints = 0;
        
        $logger = new ErrorLogger();
        $itemSerialCodesModel = new ItemSerialCodesModel();
        $memberCardsModel = new MemberCardsModel();
        $rewardItemsModel = new RewardItemsModel();
        $itemRedemptionLogsModel = new ItemRedemptionLogsModel();
        $apiLogsModel = new APILogsModel();
        $helpers = new Helpers();
        $pcwsWrapper = new PcwsWrapper();
        
        $AID = $MID;
        $totalPoints = $redeemedPoints/$quantity;
        $itemQtyItr = $quantity;
        
        for($itr = 0; $itr < (int)$quantity; $itr++) {
            $processedItemQty = (int)$quantity - (int)$itemQtyItr;
            
            $processedItemQtyInWord = $helpers->convertToWord($processedItemQty);
            
            try {
                $lastInsertedID = $itemRedemptionLogsModel->insertItemLogs($redeemedDate, $MID, $rewardItemID, 1);
                if($lastInsertedID != '' && $lastInsertedID != 0) {
                    //check item serial code availability
                    $availableSerialCode = $itemSerialCodesModel->getAvailableSerialCodeCount($rewardItemID, 1);
                    if(count($availableSerialCode) == 1) {
                        //$playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                        $playerPoints = $pcwsWrapper->getCompPoints($cardNumber, 0);
                        $playerPoints = $playerPoints['GetCompPoints']['CompBalance'];
                        $oldCurrentPoints = $playerPoints;
                        if($oldCurrentPoints >= $totalPoints) {
                            $isPointsUpdated = $memberCardsModel->updateCardPoints($MID, $totalPoints);
                            $amt = $oldCurrentPoints - $totalPoints;
                            $isPointsDeducted = $pcwsWrapper->deductCompPoints($cardNumber, $amt, '', 0);
                            //$playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                            $playerPoints = $pcwsWrapper->getCompPoints($cardNumber, 0);
                            $playerPoints = $playerPoints['GetCompPoints']['CompBalance'];
                            $oldCurrentPoints = $playerPoints;
                            if($isPointsUpdated > 0 && $oldCurrentPoints > 0 && $isPointsDeducted) {
                                $currentItemCount = $rewardItemsModel->getAvailableItemCount($rewardItemID);
                                if($currentItemCount['AvailableItemCount'] >= $itemQtyItr && $currentItemCount['AvailableItemCount'] != 0) {
                                    $isItemCountUpdated = $rewardItemsModel->updateAvailableItemCount($rewardItemID, $AID);
                                    if($isItemCountUpdated > 0) {
                                        $isActive = $rewardItemsModel->checkStatus($rewardItemID); 
                                        if($isActive['Status'] == 'Active') {
                                            $isSerialCodeUpdated = $itemSerialCodesModel->updateSerialCodeStatus($AID, $rewardItemID);
                                            
                                            if($isSerialCodeUpdated['IsSuccess'] === true) {
                                                $serialCodeSuffix = $isSerialCodeUpdated['StatusCode'];
                                                $serial = $rewardItemsModel->getSerialCodePrefix($rewardItemID);
                                                $partnerID = $serial['PartnerID'];
                                                $partnerItemID = $serial['PartnerItemID'];
                                                $serialCode = str_pad($partnerID, 2, "0", STR_PAD_LEFT).str_pad($partnerItemID, 2, "0", STR_PAD_LEFT).$serialCodeSuffix;
                                                $securityCode = Utilities::mt_rand_str(8);
                                                
                                                //calculate validity end date of the reward item
                                                $date = new DateTime($redeemedDate);
                                                $date->add(new DateInterval('P6M'));
                                                $validTo = $date->format('Y-m-d H:i:s.u');
                                                
                                                $isStatusUpdated = $itemRedemptionLogsModel->updateLogsStatus($lastInsertedID, 1, $MID, $totalPoints, $serialCode, $securityCode, $redeemedDate, $validTo);
                                                if($isStatusUpdated > 0) {
                                                    $session['PreviousRedemption'] = $lastInsertedID;
                                                    
                                                    $session['RewardOfferCopy']['Quantity'] = 1;
                                                    $session['RewardOfferCopy']['RedemptionDate'] = $redeemedDate;
                                                    $session['RewardOfferCopy']['SecurityCode'][$itr] = $securityCode;
                                                    $session['RewardOfferCopy']['SerialNumber'][$itr] = $serialCode;
                                                    
                                                    //var_dump($session['RewardOfferCopy']['SerialNumber']);
                                                    //exit;
                                                    //format valid until date
                                                    $validDate = new DateTime(date($validTo));
                                                    $validityDate = $validDate->format("F j, Y");
                                                    $session['RewardOfferCopy']['ValidUntil'][$itr] = $validityDate;
                                                    $errMsg['LastInsertedID'][$itr] = $lastInsertedID;
                                                    $itemQtyItr--;
                                                    if($itr < (int)$quantity)
                                                        continue;
                                                }
                                                else {
                                                    $errMsg["Message"] = "Pending Redemption. Error in updating redemption log.";
                                                    $errMsg["HiddenMessage"] = "Pending Redemption. Error in updating redemption log. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID. ";
                                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]."(".$errMsg.")");
                                                    $errMsg["LastInsertedID"] = $lastInsertedID;
                                                    $errMsg["IsSuccess"] = false;
//                                                    $logMessage = 'Transaction failed. Raffle coupons is either insufficient or unavailable.';
//                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-UpdateLogsStatus-Failed: Updating logs status of itemredemptionlogs.';
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                    return $errMsg;
                                                }
                                                
                                            }
                                            else {
                                                //update itemredemptionlog status to 2 - failed
                                                $isStatusUpdated = $itemRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID);
                                                if($isStatusUpdated > 0) {
                                                    switch($isSerialCodeUpdated['StatusCode']) {
                                                        case 1:
                                                            $errMsg["Message"] = "Transaction Failed. [Err: 0001] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Error in locking Item Serial Code table. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                        case 2:
                                                            $errMsg["Message"] = "Transaction Failed. [Err: 0002] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Error in unlocking Item Serial Code table. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                        case 3:
                                                            $errMsg["Message"] = "Transaction Failed. [Err: 0003] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Error in updating Item Serial Code table. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                        case 4:
                                                            $errMsg["Message"] = "Transaction Failed. Serial Code is unavailable.";
                                                            $errMsg["HiddenMessage"] = "Transaction Failed. Serial Code is unavailable. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                    }
                                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
//                                                    $logMessage = 'Transaction failed. Raffle coupons is either insufficient or unavailable.';
//                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                }
                                                else {
                                                    switch ($isSerialCodeUpdated["StatusCode"]) 
                                                    {
                                                        case 1:
                                                            $errMsg["Message"] = "Pending Redemption. [Err: 0001] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Error in locking Item Serial Code table. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                        case 2:
                                                            $errMsg["Message"] = "Pending Redemption. [Err: 0002] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Error in unlocking Item Serial Code table. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                        case 3:
                                                            $errMsg["Message"] = "Pending Redemption. [Err: 0003] Error in transactional table.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Error in updating Item Serial Code table. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                        case 4:
                                                            $errMsg["Message"] = "Pending Redemption. Serial Code is unavailable.";
                                                            $errMsg["HiddenMessage"] = "Pending Redemption. Serial Code is unavailable. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                            break;
                                                    }
                                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                                    $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                }
                                                $errMsg["LastInsertedID"] = $lastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                return $errMsg;
                                            }
                                        } else {
                                            if($isActive['Status'] != 'Active' && $isActive['Status'] != 'Deleted') {
                                                $errMsg["Message"] = "Transaction Failed. The Item you try to redeem is currently ".$isActive["Status"].".";
                                                $errMsg["HiddenMessage"] = "Transaction Failed. The Item you try to redeem is currently ".$isActive["Status"]." Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                //$logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                                //App::ClearStatus();
                                                $errMsg["LastInsertedID"] = $lastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                                $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                }
                                                return $errMsg;
                                            } else {
                                                $errMsg["Message"] = "Transaction Failed. This Reward Item  no longer exists.";
                                                $errMsg["HiddenMessage"] = "Transaction Failed. This Reward Item  no longer exists. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                //$logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                                //App::ClearStatus();
                                                $errMsg["LastInsertedID"] = $lastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                                $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                }
                                                return $errMsg;
                                            }
                                        }
                                    } else {
                                        //update itemredemptionlog status to 2 - Failed
                                        $isStatusUpdated = $itemRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID);
                                        if($isStatusUpdated > 0) {
                                           
                                            $errMsg["Message"] = "Transaction Failed. Failed in updating item inventory.";
                                            $errMsg["HiddenMessage"] = "Transaction Failed. Failed in updating item inventory. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                            $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                            $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            }
                                        } else {
                               
                                            $errMsg["Message"] = "Pending Redemption. Failed in updating item inventory.";
                                            $errMsg["HiddenMessage"] = "Pending Redemption. Failed in updating item inventory. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                            $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                            $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            }
                                        }
                                        $errMsg["LastInsertedID"] = $lastInsertedID;
                                        $errMsg["IsSuccess"] = false;
                                        return $errMsg;
                                    }
                                } else {
                                    //update itemredemptionlog status to 2 - Failed
                                    $isStatusUpdated = $itemRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID);
                                    if($isStatusUpdated > 0) {
                                        $errMsg["Message"] = "Transaction Failed. Number of available item is insufficient.";
                                        $errMsg["HiddenMessage"] = "Transaction Failed. Number of available item is insufficient. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                        $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        }
                                    }
                                    else {
                                        if($quantity == $itemQtyItr){
                                            $errMsg["Message"] = "Transaction Failed. Number of available item is insufficient.";
                                            $errMsg["HiddenMessage"] = "Transaction Failed. Number of available item is insufficient. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                        } else {
                                            $errMsg["Message"] = "Pending Redemption. Number of available item is insufficient. Total no. of Item successfully redeemed: ".$processedItemQtyInWord." (".$processedItemQty.")";
                                            $errMsg["HiddenMessage"] = "Pending Redemption. Number of available item is insufficient. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                        }
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                        $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        }
                                    }
                                    $errMsg["LastInsertedID"] = $lastInsertedID;
                                    $errMsg["IsSuccess"] = false;
                                    return $errMsg;
                                    }
                                }
                                else {
                                    //update itemredemptionlog status to 2 - Failed
                                    $isStatusUpdated = $itemRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID);
                                    if($isStatusUpdated > 0) {
                                        $errMsg["Message"] = "Failed in updating Card points. Card may have insufficient points.";
                                        $errMsg["HiddenMessage"] = "Failed in updating Card points. Card may have insufficient points. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                    } else {
                                        $errMsg["Message"] = "Pending Redemption. Failed in updating Card points. Card may have insufficient points";
                                        $errMsg["HiddenMessage"] = "Pending Redemption. Failed in updating Card points. Card may have insufficient points. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                    }
                                    $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                    }
                                    $errMsg["LastInsertedID"] = $lastInsertedID;
                                    $errMsg["IsSuccess"] = false;
                                    return $errMsg;
                               }
                            }
                            else {
                               //update itemredemptionlog status to 2 - Failed
                               $isStatusUpdated = $itemRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID);
                               if($isStatusUpdated > 0) {
                                   $errMsg["Message"] = "Transaction Failed. Card may have insufficient points.";
                                    $errMsg["HiddenMessage"] = "Transaction Failed. Card may have insufficient points. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                } else {
                                    $errMsg["Message"] = "Pending Redemption. Card may have insufficient points.";
                                    $errMsg["HiddenMessage"] = "Pending Redemption. Card may have insufficient points. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                                }
                                $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                }
                                $errMsg["LastInsertedID"] = $lastInsertedID;
                                $errMsg["IsSuccess"] = false;
                                return $errMsg;
                            }
                                
                        }
                        else {
                            //update itemredemptionlog status to 2 - failed
                            $isStatusUpdated = $itemRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID);
                            if($isStatusUpdated > 0) {
                                $errMsg["Message"] = "Transaction Failed. Serial Code is unavailable.";
                                $errMsg["HiddenMessage"] = "Transaction Failed. Serial Code is unavailable. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                            } else {
                                $errMsg["Message"] = "Pending Redemption. Serial Code is unavailable.";
                                $errMsg["HiddenMessage"] = "Pending Redemption. Serial Code is unavailable. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                            }
                            $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                            }
                            $errMsg["LastInsertedID"] = $lastInsertedID;
                            $errMsg["IsSuccess"] = false;
                            return $errMsg;
                        }
                    } else {
                        //$logger->log($logger->logdate, '[ITEM REDEMPTION ERROR] ', '');
                        $errMsg["Message"] = "Transaction Failed. Error in redemption logging.";
                        $errMsg["HiddenMessage"] = "Transaction Failed. Error in redemption logging. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                    "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["Message"]);
                        $errMsg["LastInsertedID"] = "";
                        $errMsg["IsSuccess"] = false;
                        $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg["HiddenMessage"];
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, $lastInsertedID, 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        }
                        return $errMsg;
                    }
        
            } catch (Exception $e) {
                //var_dump($e->getMessage());
                //$logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $e->getMessage());
                $errMsg["Message"] = "Transaction Failed. Failed to Start Item Redemption.";
                $errMsg["HiddenMessage"] = "Transaction Failed. Failed to Start Item Redemption. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                $apiDetails = 'REDEEMITEMS-Failed: '.$errMsg['HiddenMessage'];
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                }
                $errMsg["LastInsertedID"] = "";
                $errMsg["IsSuccess"] = false;
                return $errMsg;
                
            }
        }
        
        
        
//        $transMsg = 'No Error, Transaction successful.';
//        $errorCode = 0;
//        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
        $errMsg["Message"] = "Player Redemption: Transaction Successful.";
        $errMsg["OldCP"] = $oldCurrentPoints;
        $errMsg["IsSuccess"] = true;
        $errMsg["LastInsertedID"] = $lastInsertedID;
        $errMsg['SessionSerialCode'] = $session['RewardOfferCopy']['SerialNumber'];
        $errMsg['SessionSecurityCode'] = $session['RewardOfferCopy']['SecurityCode'];
        $errMsg['ValidUntil'] = $session['RewardOfferCopy']['ValidUntil'];
        $errMsg['RedemptionDate'] = $session['RewardOfferCopy']['RedemptionDate'];
        return $errMsg;
    }
    
   
    
//    /**
//     * @Description: Convert the interger to word (range: 1-5 only)
//     * @Author: aqdepliyan
//     * @DateCreated: 2014-06-19
//     * @param type $digit
//     * @return string
//     */
//    private function convertToWord($digit){
//        switch ($digit) {
//            case 1:
//                return "One";
//                break;
//            case 2:
//                return "Two";
//                break;
//            case 3:
//                return "Three";
//                break;
//            case 4:
//                return "Four";
//                break;
//            case 5:
//                return "Five";
//                break;
//            default:
//                return "Zero";
//                break;
//        }
//    }
}

?>
