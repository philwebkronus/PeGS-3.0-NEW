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
        
        //set table for raffle coupon based on active coupon batch
        $raffleCouponSuffix = $couponBatchesModel->getRaffleCouponSuffix();
        if(isset($raffleCouponSuffix) && $raffleCouponSuffix['CouponBatchID'] != '') {
            
            $AID = $MID;
            $module = 'Coupon Redemption';
            
            try {
                //insert to coupon redemption log, initial status is 0 - pending
                $lastInsertedID = $couponRedemptionLogsModel->insertCouponLogs($MID, $rewardItemID, $quantity, $redeemedDate);
                if($lastInsertedID != '' && $lastInsertedID > 0) {
                    
                    //check available raffle coupon
                    $availableCoupon = $raffleCouponsModel->getAvailableCoupons($rewardItemID, $quantity);
                    if(count($availableCoupon) == $quantity) {
                        //get current points for validation
                        $playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                        $oldCurrentPoints = $playerPoints['CurrentPoints'];
                        if($oldCurrentPoints >= $redeemedPoints) {
                            //update card points (deduct the total redeemed points)
                            $isPointsUpdated = $memberCardsModel->updateCardPoints($MID, $redeemedPoints);
                            
                            $playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                            //check if points is greater than or equal to 0
                            if($isPointsUpdated > 0 && $playerPoints['CurrentPoints'] >= 0) {
                                
                                //get and check status of the reward item
                                $isActive = $rewardItemsModel->checkStatus($rewardItemID);
                                if($isActive['Status'] == 'Active') {
                                    //update raffle coupons
                                    $raffleCouponResults = $raffleCouponsModel->lockRaffleCoupons();
                                    if($raffleCouponResults > 0) {
                                        $availableCoupon = $raffleCouponsModel->getAvailableCoupons($rewardItemID, $quantity);
                                        if(count($availableCoupon) == $quantity) {
                                            //proceed with update query if table is successfully locked
                                            $isUpdated = $raffleCouponsModel->updateRaffleCouponsStatus($quantity, $lastInsertedID, $rewardItemID, $AID);
                                            if($isUpdated == 0) {
                                                $isUnlocked = $raffleCouponsModel->unlockRaffleCoupons();
                                                $isUnlocked == 0 ? $errorCode = 29: $errorCode = 28;
                                                if($errorCode == 28) {
                                                    $transMsg = 'Error in Unlocking.';
                                                }    
                                                else {
                                                    $transMsg = 'Error in Updating.';
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                }
                                                //update couponredemptionlog status to 2 - Failed
                                                $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID,2,$MID, 0);
                                                
                                            }
                                            else {
                                            
                                                $isUnlocked = $raffleCouponsModel->unlockRaffleCoupons();
                                                if($isUnlocked == 0) {
                                                    $transMsg = 'Error in Unlocking.';
                                                    $errorCode = 28;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                }

                                                //if successful
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
                                                if($isStatusUpdated > 0) {
                                                    //if redemption is successful, build sessions for needed data and return an array of response.
                                                    Yii::app()->session['PreviousRedemption'] = $lastInsertedID;
                                                    Yii::app()->session['RewardOfferCopy']['CouponSeries'] = $couponSeries;
                                                    Yii::app()->session['RewardOfferCopy']['Quantity'] = $quantity;
                                                    Yii::app()->session['RewardOfferCopy']['RedemptionDate'] = $redeemedDate;
                                                    Yii::app()->session['RewardOfferCopy']['CheckSum'] = $securityCode;
                                                    Yii::app()->session['RewardOfferCopy']['SerialNumber'] = $serialCode;

                                                    $transMsg = 'No Error, Transaction successful.';
                                                    $errorCode = 0;
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode, $lastInsertedID, $oldCurrentPoints)));
                                                    $resultArray = array('LastInsertedID' => $lastInsertedID, 'OldCurrentPoints' => $oldCurrentPoints);
                                                    return $resultArray;
                                                    
                                                }
                                                else {
                                                    $transMsg = "Pending Redemption. Error in updating redemption log.";
                                                    $errorCode = 31;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode))); 
                                                    return 0;
                                                }
                                            }
                                            //return $lastInsertedID;
//                                            $transMsg = 'No Error, Transaction successful.';
//                                            $errorCode = 0;
//                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                        }
                                        else {
                                            $isUnlocked = $raffleCouponsModel->unlockRaffleCoupons();
                                            if($isUnlocked == 0) {
                                                $transMsg = 'Error in Unlocking.';
                                                $errorCode = 28;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                            }
                                            
                                            $transMsg = 'Serial code unavailable.';
                                            $errorCode = 30;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));              
                                            
                                        }
                                    }
                                    else {
                                        $transMsg = 'Error in Locking.';
                                        $errorCode = 27;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                    }
                                    
                                    
                                }
                                else {
                                    if($isActive['Status'] != 'Active' && $isActive['Status'] != "Deleted") {
                                        $transMsg = 'Transaction failed. The item you are trying to redeem is currently'.$isActive['Status'].'.';
                                        $errorCode = 43;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                     
                                    }
                                    else {
                                        $transMsg = 'Transaction failed. This reward item no longer exists.';
                                        $errorCode = 43;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                        
                                    }
                                    return 0;
                                }
                            }
                            else {
                                //update couponredemptionlog status to 2 - Failed
                                $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID, 0);
                                if($isStatusUpdated > 0) {
                                    $transMsg = 'Transaction failed. Card has insufficient points.';
                                    $errorCode = 24;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                }
                                else {
                                    $transMsg = 'Pending Redemption. Card has insufficient points.';
                                    $errorCode = 44;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                }
                                return 0;
                            }
                        }
                        else {
                            //update couponredemptionlog status to 2 - Failed
                            $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID, 0);
                            if($isStatusUpdated > 0) {
                                $transMsg = 'Transaction failed. Card has insufficient points.';
                                $errorCode = 24;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                            }
                            else {
                                $transMsg = 'Pending Redemption. Card has insufficient points.';
                                $errorCode = 44;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                            }
                            return 0;
                        }
                    }
                    else {
                        //update couponredemptionlog status to 2 - Failed
                        $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 2, $MID, 0);
                        if($isStatusUpdated > 0) {
                            $transMsg = 'Transaction failed. Raffle coupons is either insufficient or unavailable.';
                            $errorCode = 47;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                        }
                        else {
                            $transMsg = 'Pending Redemption. Raffle coupons is either insufficient or unavailable.';
                            $errorCode = 48;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                        }
                        return 0;
                    }
                }
                else {
                    $transMsg = 'Transaction failed. Error in redemption logging.';
                    $errorCode = 45;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                    return 0;
                }              
            } catch (Exception $e) {
                $transMsg = 'Transaction Failed. Failed to Start Coupon Redemption.';
                $errorCode = 46;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                return 0;
            }
        }
        else {
            $transMsg = 'Transaction Failed. Raffle Coupons is either insufficient or unavailable.';
            $errorCode = 47;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
            return 0;
        }
    }
    
    //process redeeming of items, updating tables connected to the item redemption
    public function processItemRedemption($MID, $rewardItemID, $quantity, $redeemedPoints, $cardNumber, $source, $redeemedDate) {
        $module = "Item Redemption";
        
        $logger = new ErrorLogger();
        $itemSerialCodesModel = new ItemSerialCodesModel();
        $memberCardsModel = new MemberCardsModel();
        $rewardItemsModel = new RewardItemsModel();
        $itemRedemptionLogsModel = new ItemRedemptionLogsModel();
        
        $AID = $MID;
        $totalPoints = $redeemedPoints/$quantity;
        $itemQtyItr = $quantity;
        for($itr = 0; $itr < (int)$quantity; $itr++) {
            $processedItemQty = (int)$quantity - (int)$itemQtyItr;
            $processedItemQtyInWord = $this->convertToWord($processedItemQty);
            
            try {
                $lastInsertedID = $itemRedemptionLogsModel->insertItemLogs($redeemedDate, $MID, $rewardItemID, 1);
                if($lastInsertedID != '' && $lastInsertedID != 0) {
                    //check item serial code availability
                    $availableSerialCode = $itemSerialCodesModel->getAvailableSerialCodeCount($rewardItemID, 1);
                    if(count($availableSerialCode) == 1) {
                        $playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                        $oldCurrentPoints = $playerPoints['CurrentPoints'];
                        if($playerPoints['CurrentPoints'] >= $totalPoints) {
                            $isPointsUpdated = $memberCardsModel->updateCardPoints($MID, $totalPoints);
                            $playerPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                            if($isPointsUpdated > 0 && $playerPoints['CurrentPoints'] > 0) {
                                $currentItemCount = $rewardItemsModel->getAvailableItemCount($rewardItemID);
                                if($currentItemCount['AvailableItemCount'] >= $itemQtyItr && $currentItemCount['AvailableItemCount'] != 0) {
                                    $isItemCountUpdated = $rewardItemsModel->updateAvailableItemCount($rewardItemID, $AID);
                                    if($isItemCountUpdated > 0) {
                                        $isActive = $rewardItemsModel->checkStatus($rewardItemID);
                                        
                                        if($isActive['Status'] == 'Active') {
                                            $isSerialCodeUpdated = $itemSerialCodesModel->updateSerialCodeStatus($AID, $rewardItemID);
                                            if($isSerialCodeUpdated['IsSuccess'] == true) {
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
                                                    Yii::app()->session['PreviousRedemption'] = $lastInsertedID;
                                                    Yii::app()->session['RewardOfferCopy']['Quantity'] = 1;
                                                    Yii::app()->session['RewardOfferCopy']['RedemptionDate'] = $redeemedDate;
                                                    Yii::app()->session['RewardOfferCopy']['SecurityCode'][$itr] = $securityCode;
                                                    Yii::app()->session['RewardOfferCopy']['SerialNumber'][$itr] = $serialCode;
                                                    //format valid until date
                                                    $validDate = new DateTime(date($validTo));
                                                    $validityDate = $validDate->format("F j, Y");
                                                    Yii::app()->session['RewardOfferCopy']['ValidUntil'][$itr] = $validityDate;
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
                                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
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
                                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
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
                                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                                App::ClearStatus();
                                                $errMsg["LastInsertedID"] = $lastInsertedID;
                                                $errMsg["IsSuccess"] = false;
                                                return $errMsg;
                                            } else {
                                                $errMsg["Message"] = "Transaction Failed. This Reward Item  no longer exists.";
                                                $errMsg["HiddenMessage"] = "Transaction Failed. This Reward Item  no longer exists. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                                App::ClearStatus();
                                                $errMsg["LastInsertedID"] = $lastInsertedID;
                                                $errMsg["IsSuccess"] = false;
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
                                            $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                        } else {
                               
                                            $errMsg["Message"] = "Pending Redemption. Failed in updating item inventory.";
                                            $errMsg["HiddenMessage"] = "Pending Redemption. Failed in updating item inventory. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                            "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                            $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
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
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
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
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
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
                                        $errMsg["Message"] = "Transaction Failed. Failed in updating Card points. Card may have insufficient points.";
                                        $errMsg["HiddenMessage"] = "Transaction Failed. Failed in updating Card points. Card may have insufficient points. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                    } else {
                                        $errMsg["Message"] = "Pending Redemption. Failed in updating Card points. Card may have insufficient points";
                                        $errMsg["HiddenMessage"] = "Pending Redemption. Failed in updating Card points. Card may have insufficient points. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
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
                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                                } else {
                                    $errMsg["Message"] = "Pending Redemption. Card may have insufficient points.";
                                    $errMsg["HiddenMessage"] = "Pending Redemption. Card may have insufficient points. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                    $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
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
                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                            } else {
                                $errMsg["Message"] = "Pending Redemption. Serial Code is unavailable.";
                                $errMsg["HiddenMessage"] = "Pending Redemption. Serial Code is unavailable. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                                "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                            }
                            $errMsg["LastInsertedID"] = $lastInsertedID;
                            $errMsg["IsSuccess"] = false;
                            return $errMsg;
                        }
                    } else {
                        $message = 'Trnsaction failed. Error in inserting to item logs';
                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $message);
                        $errMsg["Message"] = "Transaction Failed. Error in redemption logging.";
                        $errMsg["HiddenMessage"] = "Transaction Failed. Error in redemption logging. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                                                    "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                        $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $errMsg["HiddenMessage"]);
                        $errMsg["LastInsertedID"] = "";
                        $errMsg["IsSuccess"] = false;
                        return $errMsg;
                    }
        
            } catch (Exception $e) {
                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $e->getMessage());
                $transMsg = "Transaction Failed. Failed to start item redemption.";
                $hiddenMsg = "Transaction Failed. Failed to Start Item Redemption. Processed By: $AID, Request By:  $MID, RewardItemID: $rewardItemID, ".
                                             "Total Quantity Requested: $quantity, Total no. of Item successfully redeemed: $processedItemQty";
                $logger->log($logger->logdate, "[ITEM REDEMPTION ERROR] ", $hiddenMsg);
                $errorCode = 50;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                return 0;
            }
        }
        
        $transMsg = 'No Error, Transaction successful.';
        $errorCode = 0;
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
        $errMsg["Message"] = "Player Redemption: Transaction Successful.";
        $errMsg["OldCP"] = $oldCurrentPoints;
        $errMsg["IsSuccess"] = true;
        return $errMsg;
    }
    
    /**
     *
     * @param type $status
     * @param string $body
     * @param type $content_type 
     * @link http://www.yiiframework.com/wiki/175/how-to-create-a-rest-api
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 200:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
                    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                        <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                    </head>
                    <body>
                        <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                        <p>' . $message . '</p>
                        <hr />
                        <address>' . $signature . '</address>
                    </body>
                    </html>';

            echo $body;
        }
        //Yii::app()->end();
    }
    
    /**
     * HTTP Status Code Message
     * @param string $status
     * @return bool
     */
    private function _getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            200 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
    /**
     * @Description: Convert the interger to word (range: 1-5 only)
     * @Author: aqdepliyan
     * @DateCreated: 2014-06-19
     * @param type $digit
     * @return string
     */
    private function convertToWord($digit){
        switch ($digit) {
            case 1:
                return "One";
                break;
            case 2:
                return "Two";
                break;
            case 3:
                return "Three";
                break;
            case 4:
                return "Four";
                break;
            case 5:
                return "Five";
                break;
            default:
                return "Zero";
                break;
        }
    }
}

?>
