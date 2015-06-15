<?php

/*
* Description: Player Item/Coupon Redemption Controller
* @author: aqdepliyan
* DateCreated: 2013-07-10 09:38AM
*/

$redemptiondata["MID"] = $MID;
$redemptiondata["RewardItemID"] = $_SESSION['RewardItemsInfo']['RewardItemID'];
$redemptiondata["RewardOfferID"] = $_SESSION['RewardItemsInfo']['RewardOfferID'];
$redemptiondata["IsCoupon"] = $_SESSION['RewardItemsInfo']['IsCoupon'];
$redemptiondata["PlayerPoints"] = $_SESSION['RewardItemsInfo']['PlayerPoints'];
$redemptiondata["ItemName"] = $hdnItemName->SubmittedValue;
$redemptiondata["Quantity"] = $txtQuantity->SubmittedValue;
$redemptiondata["TotalItemPoints"] = $hdnTotalItemPoints->SubmittedValue;
$redemptiondata["CardNumber"] = $hdnCardNumber->SubmittedValue;
$redemptiondata["PlayerName"] = $playername;
$redemptiondata["Birthdate"] = $birthdate;
$redemptiondata["Email"] = $email;
$redemptiondata["MobileNumber"] = $contactno;

if($redemptiondata["PlayerPoints"] < $redemptiondata["TotalItemPoints"]){
    $message = "Player Redemption: Transaction Failed. Card may have insufficient points.";
    $_AuditTrail->StartTransaction();
    $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
    if(!App::HasError()){
        $_AuditTrail->CommitTransaction();
        App::SetErrorMessage($message);
    } else {
        $message = "Failed to log event on database.";
        $_AuditTrail->RollBackTransaction();
        App::SetErrorMessage($message);
    }
} else {
    $IsCoupon = ($redemptiondata["IsCoupon"] == 0 || $redemptiondata["IsCoupon"] == "0") ? false:true;
     
    if($IsCoupon){

        //Check if the available coupon is greater than or match with the quantity avail by the player.
        $availablecoupon = $_RaffleCoupons->getAvailableCoupons($redemptiondata["RewardItemID"], $redemptiondata["Quantity"]);

        if(count($availablecoupon) == $redemptiondata["Quantity"]){

            //Redemption Process for Coupon
            $offerenddate = $_RewardOffers->getOfferEndDate($redemptiondata["RewardOfferID"]);
            $RedeemedDate = $offerenddate["CurrentDate"];
            
            //check if the availing date  is greater than the End date of the reward offer.
            if($RedeemedDate < $offerenddate){
                $_CouponRedemptionLogs->StartTransaction();
                $_CouponRedemptionLogs->insertCouponLogs($redemptiondata["MID"], $redemptiondata["RewardItemID"], $redemptiondata["Quantity"],1, $RedeemedDate);

                if(!App::HasError()){
                    $_CouponRedemptionLogs->CommitTransaction();
                    $CouponRedemptionLogID = $_CouponRedemptionLogs->LastInsertID;
                    
                    $_RaffleCoupons->StartTransaction();
                    $itr = 0;
                    do{
                        $_RaffleCoupons->updateRaffleCouponsStatus($availablecoupon[$itr]["RaffleCouponID"], $CouponRedemptionLogID, $redemptiondata["RewardItemID"],$redemptiondata["MID"]);
                        $itr++;
                    }while($itr != count($availablecoupon));

                    if(!App::HasError()){
                        $_RaffleCoupons->CommitTransaction();
                        
                        $_MemberCards->StartTransaction();
                        $_MemberCards->updatePlayerPoints($redemptiondata["MID"], $redemptiondata["TotalItemPoints"]);
                        $CommonPDOConn = $_MemberCards->getPDOConnection();

                        if(!App::HasError()){
                            $status = 1;
                            $couponlogsdetail = $_CouponRedemptionLogs->getSource($CouponRedemptionLogID);
                            $_CouponRedemptionLogs->setPDOConnection($CommonPDOConn);
                            $_CouponRedemptionLogs->updateLogsStatus($CouponRedemptionLogID, $couponlogsdetail['Source'], $status, $couponlogsdetail["MID"]);
                            if(!App::HasError()){
                                $_MemberCards->CommitTransaction();
                                $message = "Player Redemption: Transaction Successful.";
                                $_AuditTrail->StartTransaction();
                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                                if(!$_AuditTrail->HasError){
                                    $_AuditTrail->CommitTransaction();
                                    $_SESSION["PreviousRedemption"] = $CouponRedemptionLogID;
                                    $redemptioninfo = $_RaffleCoupons->getCouponRedemptionInfo($CouponRedemptionLogID);

                                    $arrcouponredemptionloginfo = $redemptioninfo[0];
                                    $mincouponnumber = str_pad($arrcouponredemptionloginfo["MinCouponNumber"], 7, "0", STR_PAD_LEFT);
                                    $maxcouponnumber = str_pad($arrcouponredemptionloginfo["MaxCouponNumber"], 7, "0", STR_PAD_LEFT);

                                    if ($arrcouponredemptionloginfo["MinCouponNumber"] == $arrcouponredemptionloginfo["MaxCouponNumber"]) {
                                        $couponseries = $mincouponnumber;
                                    } else {
                                        $couponseries = $mincouponnumber . " - " . $maxcouponnumber;
                                    }

                                    $serialnumber = str_pad($CouponRedemptionLogID, 7, "0", STR_PAD_LEFT) . "A" . $_RaffleCoupons->getMod10($mincouponnumber) . "B" . $_RaffleCoupons->getMod10($maxcouponnumber);
                                    $checkstring = $couponseries . $redemptiondata["Quantity"] . $redemptiondata["CardNumber"]  . $redemptiondata["PlayerName"]  . date("F j, Y", strtotime($redemptiondata["Birthdate"])) . 
                                                                $redemptiondata["Email"] . $redemptiondata["MobileNumber"];
                                    $checksum = crc32($checkstring);
                                    $_SESSION['RewardOfferCopy']['CouponSeries'] = $couponseries;
                                    $_SESSION['RewardOfferCopy']['Quantity'] = $redemptiondata["Quantity"];
                                    $_SESSION['RewardOfferCopy']['RedemptionDate'] = $RedeemedDate;
                                    $_SESSION['RewardOfferCopy']['CheckSum'] = $checksum;
                                    $_SESSION['RewardOfferCopy']['SerialNumber'] = $serialnumber;
                                    $showcouponredemptionwindow = true;
                                    return $message;
                                } else {
                                    $message = "Failed to log event on database.";
                                    $_AuditTrail->RollBackTransaction();
                                    App::SetErrorMessage($message);
                                }
                            } else {
                                $_MemberCards->RollBackTransaction();
                                $message = "Player Redemption: Error in updating redemption log.";
                                $_AuditTrail->StartTransaction();
                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                                if(!$_AuditTrail->HasError){
                                    $_AuditTrail->CommitTransaction();
                                    App::SetErrorMessage($message);
                                } else {
                                    $message = "Failed to log event on database.";
                                    $_AuditTrail->RollBackTransaction();
                                    App::SetErrorMessage($message);
                                }
                            }
                        } else {
                            $_MemberCards->RollBackTransaction();
                            $status = 2;
                            $couponlogsdetail = $_CouponRedemptionLogs->getSource($CouponRedemptionLogID);
                            $_CouponRedemptionLogs->StartTransaction();
                            $_CouponRedemptionLogs->updateLogsStatus($CouponRedemptionLogID, $couponlogsdetail['Source'], $status, $couponlogsdetail["MID"]);

                            if(!$_CouponRedemptionLogs->HasError){
                                $_CouponRedemptionLogs->CommitTransaction();
                            } else {
                                $_CouponRedemptionLogs->RollBackTransaction();
                            }

                            $message = "Player Redemption: Transaction Failed. Please try again.";
                            $_AuditTrail->StartTransaction();
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                            if(!$_AuditTrail->HasError){
                                $_AuditTrail->CommitTransaction();
                                App::SetErrorMessage($message);
                            } else {
                                $message = "Failed to log event on database.";
                                $_AuditTrail->RollBackTransaction();
                                App::SetErrorMessage($message);
                            }
                        }
                        
                    } else {
                        $_RaffleCoupons->RollBackTransaction();
                        $status = 2;
                        $couponlogsdetail = $_CouponRedemptionLogs->getSource($CouponRedemptionLogID);
                        $_CouponRedemptionLogs->StartTransaction();
                        $_CouponRedemptionLogs->updateLogsStatus($CouponRedemptionLogID, $couponlogsdetail['Source'], $status, $couponlogsdetail["MID"]);

                        if(!$_CouponRedemptionLogs->HasError){
                            $_CouponRedemptionLogs->CommitTransaction();
                        } else {
                            $_CouponRedemptionLogs->RollBackTransaction();
                        }

                        $message = "Player Redemption: Transaction Failed. Please try again.";
                        $_AuditTrail->StartTransaction();
                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                        if(!$_AuditTrail->HasError){
                            $_AuditTrail->CommitTransaction();
                            App::SetErrorMessage($message);
                        } else {
                            $message = "Failed to log event on database.";
                            $_AuditTrail->RollBackTransaction();
                            App::SetErrorMessage($message);
                        }
                    }
                } else {
                    $_CouponRedemptionLogs->RollBackTransaction();
                    $message = "Player Redemption: Error in redemption logging.";
                    $_AuditTrail->StartTransaction();
                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                    if(!$_AuditTrail->HasError){
                        $_AuditTrail->CommitTransaction();
                        App::SetErrorMessage($message);
                    } else {
                        $message = "Failed to log event on database.";
                        $_AuditTrail->RollBackTransaction();
                        App::SetErrorMessage($message);
                    }
                }

            } else {
                $message = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                $_AuditTrail->StartTransaction();
                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                if(!$_AuditTrail->HasError){
                    $_AuditTrail->CommitTransaction();
                    App::SetErrorMessage($message);
                } else {
                    $message = "Failed to log event on database.";
                    $_AuditTrail->RollBackTransaction();
                    App::SetErrorMessage($message);
                }
            }
        } else {
            $message = "Player Redemption: Transaction Failed.Number of available coupon is insufficient.";
            $_AuditTrail->StartTransaction();
            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
            if(!$_AuditTrail->HasError){
                $_AuditTrail->CommitTransaction();
                App::SetErrorMessage($message);
            } else {
                $message = "Failed to log event on database.";
                $_AuditTrail->RollBackTransaction();
                App::SetErrorMessage($message);
            }
        }

    } else {

        //Check if the available item is greater than or match with the quantity avail by the player.
        $availableitemcount = $_RewardItems->getAvailableItemCount($redemptiondata["RewardItemID"]);
 
        if($availableitemcount["AvailableItemCount"] >= $redemptiondata["Quantity"]){    
            //Redemption Process for Item
            $offerenddate = $_RewardOffers->getOfferEndDate($redemptiondata["RewardOfferID"]);
            $RedeemedDate = $offerenddate["CurrentDate"];
            
            //check if the availing date  is greater than the End date of the reward offer.
            if($RedeemedDate < $offerenddate["OfferEndDate"]){
                $_ItemRedemptionLogs->StartTransaction();
                $_ItemRedemptionLogs->insertItemLogs($RedeemedDate, $redemptiondata["MID"], $redemptiondata["RewardItemID"], $redemptiondata["Quantity"],1);
                
                if(!App::HasError()){
                    $_ItemRedemptionLogs->CommitTransaction();
                    $ItemRedemptionLogID = $_ItemRedemptionLogs->LastInsertID;

                    $_RewardItems->StartTransaction();
                    $_RewardItems->updateAvailableItemCount($redemptiondata["RewardItemID"], $redemptiondata["Quantity"]);
                    $CommonPDOConn = $_RewardItems->getPDOConnection();

                    $_MemberCards->setPDOConnection($CommonPDOConn);
                    $_MemberCards->updatePlayerPoints($redemptiondata["MID"], $redemptiondata["TotalItemPoints"]);

                    if(!App::HasError()){
                        $status = 1;
                        $itemlogsdetail = $_ItemRedemptionLogs->getSource($ItemRedemptionLogID);
                        $_ItemRedemptionLogs->setPDOConnection($CommonPDOConn);
                        $_ItemRedemptionLogs->updateLogsStatus($ItemRedemptionLogID, $itemlogsdetail['Source'], $status, $itemlogsdetail['MID']);

                        if(!App::HasError()){
                            $_RewardItems->CommitTransaction();
                            $message = "Player Redemption: Transaction Successful.";
                            $_AuditTrail->StartTransaction();
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                            if(!$_AuditTrail->HasError){
                                $_AuditTrail->CommitTransaction();
                                $_SESSION["PreviousRedemption"] = $ItemRedemptionLogID;
                                $serialnumber = str_pad($ItemRedemptionLogID, 4, "0", STR_PAD_LEFT)."A".str_pad($redemptiondata["MID"], 4, "0", STR_PAD_LEFT)."B" ;
                                $checkstring = $_SESSION['RewardItemsInfo']['RewardOfferID'] .$redemptiondata["Quantity"] . $redemptiondata["CardNumber"]  . $redemptiondata["PlayerName"]  . date("F j, Y", strtotime($redemptiondata["Birthdate"])) . 
                                                            $redemptiondata["Email"] . $redemptiondata["MobileNumber"];
                                $checksum = abs(crc32($checkstring));

                                $_SESSION['RewardOfferCopy']['Quantity'] = $redemptiondata["Quantity"];
                                $_SESSION['RewardOfferCopy']['RedemptionDate'] = $RedeemedDate;
                                $_SESSION['RewardOfferCopy']['CheckSum'] = $checksum;
                                $_SESSION['RewardOfferCopy']['SerialNumber'] = $serialnumber;
                                $showcouponredemptionwindow = true;
                                return $message;
                            } else {
                                $message = "Failed to log event on database.";
                                $_AuditTrail->RollBackTransaction();
                                App::SetErrorMessage($message);
                            }
                        } else {
                            $_RewardItems->RollBackTransaction();
                            $message = "Player Redemption: Transaction failed.Please try again.";
                            $_AuditTrail->StartTransaction();
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                            if(!$_AuditTrail->HasError){
                                $_AuditTrail->CommitTransaction();
                                App::SetErrorMessage($message);
                            } else {
                                $message = "Failed to log event on database.";
                                $_AuditTrail->RollBackTransaction();
                                App::SetErrorMessage($message);
                            }
                        }
                    } else {
                        $_RewardItems->RollBackTransaction();
                        $status = 2;
                        $itemlogsdetail = $_ItemRedemptionLogs->getSource($ItemRedemptionLogID);
                        $_ItemRedemptionLogs->StartTransaction();
                        $_ItemRedemptionLogs->updateLogsStatus($ItemRedemptionLogID, $itemlogsdetail['Source'], $status, $itemlogsdetail["MID"]);

                        if(!$_ItemRedemptionLogs->HasError){
                            $_ItemRedemptionLogs->CommitTransaction();
                            $message = "Player Redemption: Transaction Failed. Please try again.";
                            $_AuditTrail->StartTransaction();
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                            if(!$_AuditTrail->HasError){
                                $_AuditTrail->CommitTransaction();
                                App::SetErrorMessage($message);
                            } else {
                                $message = "Failed to log event on database.";
                                $_AuditTrail->RollBackTransaction();
                                App::SetErrorMessage($message);
                            }
                        } else {
                            $_ItemRedemptionLogs->RollBackTransaction();
                            $message = "Player Redemption: Transaction failed. Error in updating redemption log";
                            $_AuditTrail->StartTransaction();
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                            if(!$_AuditTrail->HasError){
                                $_AuditTrail->CommitTransaction();
                                App::SetErrorMessage($message);
                            } else {
                                $message = "Failed to log event on database.";
                                $_AuditTrail->RollBackTransaction();
                                App::SetErrorMessage($message);
                            }
                        }
                    }

                } else {
                    $_ItemRedemptionLogs->RollBackTransaction();
                    $message = "Player Redemption: Error in redemption logging.";
                    $_AuditTrail->StartTransaction();
                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
                    if(!$_AuditTrail->HasError){
                        $_AuditTrail->CommitTransaction();
                        App::SetErrorMessage($message);
                    } else {
                        $message = "Failed to log event on database.";
                        $_AuditTrail->RollBackTransaction();
                        App::SetErrorMessage($message);
                    }
                }
            }
        } else {
            $message = "Player Redemption: Transaction Failed.Number of available item is insufficient.";
            $_AuditTrail->StartTransaction();
            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION["MemberInfo"]["SessionID"]));
            if(!$_AuditTrail->HasError){
                $_AuditTrail->CommitTransaction();
                App::SetErrorMessage($message);
            } else {
                $message = "Failed to log event on database.";
                $_AuditTrail->RollBackTransaction();
                App::SetErrorMessage($message);
            }
        }


    }
}
    
?>
