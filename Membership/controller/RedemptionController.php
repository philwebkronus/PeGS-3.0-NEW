<?php

/**
* @Description: Item/Coupon Redemption Controller
* @Author: aqdepliyan
* @DateCreated: 2013-07-10 09:38AM
*/

    //Check if Admin or Player if not, Set SessionID to zero else get the AID if admin and MID if player
    if(isset($_SESSION['userinfo'])){
        if(is_array($_SESSION['userinfo']) && count($_SESSION['userinfo']) > 0){
            if(isset($_SESSION['sessionID'])){
                $sessionid = $_SESSION['sessionID'];
                $aid = $_SESSION['aID'];
            } 
        }
    } else if(isset($_SESSION['MID'])){
        $sessionid = $_SESSION['sessionID'];
        $aid = $_SESSION['MID'];
    } else{
        $sessionid = 0;
        $aid = 0;
    }

    //For Admin: Check If Session already exist on database.
    if(isset($_SESSION['userinfo'])){
        App::LoadModuleClass("Admin", "AccessRights");
        App::LoadModuleClass("Admin", "AccountSessions");
        App::LoadModuleClass("Kronus", "Accounts");
        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);
        foreach ($sessioncount as $value) {
                foreach ($value as $value2) {
                    $sessioncount = $value2['Count'];
                }
        }
        
        if($sessioncount > 0)
        {
            $currentPage = URL::CurrentPage();
            $currentMenuID = $accessrights->getMenuID($currentPage);

            if(isset($_SESSION['menus']) && count($_SESSION['menus']) > 0)
            {
                $usermenu = $_SESSION['menus'];
                $accounttypeid = $usermenu['0']['AccountTypeID'];
            }
            else
            {

                $accounttypeid = $_SESSION["userinfo"]['AccountTypeID'];

                $usermenu = $accessrights->getAccessRights($accounttypeid);

                $_SESSION["menus"] = $usermenu;

            }
            $accessibleMenus = $accessrights->getAccessibleMenuID($accounttypeid);
            $accessibleSubMenus = $accessrights->getAccessibleSubMenuID($accounttypeid);

            if(!in_array($currentMenuID, $accessibleMenus))
            {
                if(count($accessibleSubMenus) > 0 )
                {
                    $currentSubMenuID = $accessrights->getSubMenuID($currentPage);

                    if(!in_array($currentSubMenuID, $accessibleSubMenus))
                        URL::Redirect ('forbidden.php');
                }
                else
                {
                     URL::Redirect ('forbidden.php');
                }

            }
        }
        
    } 
    
     //For Portal: Check If Session already exist on database.
    if(isset($_SESSION['MID'])){
        App::LoadModuleClass("Membership", "MemberSessions");
        $_MemberSessions = new MemberSessions();
        $sessioncount = $_MemberSessions->checkifsessionexist($aid, $sessionid);
        foreach ($sessioncount as $value) {
            foreach ($value as $value2) {
                $sessioncount = $value2['Count'];
            }
        }
    }

    //Check if session is existing, if not destroy session and redirect to login page.
    if($sessioncount > 0)
    {
        //proceed with redemption process if it has session
        if($source == 1){
            $redemptiondata["MID"] = $MID;
            $redemptiondata["RewardItemID"] = $_SESSION['RewardItemsInfo']['RewardItemID'];
            $redemptiondata["RewardOfferID"] = $_SESSION['RewardItemsInfo']['RewardOfferID'];
            $redemptiondata["IsCoupon"] = $_SESSION['RewardItemsInfo']['IsCoupon'];
            $redemptiondata["CardNumber"] = $hdnCardNumber->SubmittedValue;
            $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($redemptiondata["CardNumber"]);
            $redemptiondata["PlayerPoints"] = $PlayerPoints[0]['CurrentPoints'];
            $redemptiondata["ItemName"] = $hdnItemName->SubmittedValue;
            $redemptiondata["Quantity"] = $txtQuantity->SubmittedValue;
            $redemptiondata["TotalItemPoints"] = $hdnTotalItemPoints->SubmittedValue;
            $redemptiondata["PlayerName"] = $playername;
            $redemptiondata["Birthdate"] = $birthdate;
            $redemptiondata["Email"] = $email;
            $redemptiondata["MobileNumber"] = $contactno;
        } else {
            $redemptiondata["MID"] = $_SESSION["CardRed"]["MID"];
            $redemptiondata["RewardItemID"] = $hdnRewardItemID->SubmittedValue;
            $redemptiondata["RewardOfferID"] = $hdnRewardOfferID->SubmittedValue;
            $redemptiondata["IsCoupon"] = $hdnIsCoupon->SubmittedValue;
            $redemptiondata["CardNumber"] = $_SESSION["CardRed"]["CardNumber"];
            $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($redemptiondata["CardNumber"]);
            $redemptiondata["PlayerPoints"] = $PlayerPoints[0]['CurrentPoints'];
            $redemptiondata["ItemName"] = $hdnItemName->SubmittedValue;
            $redemptiondata["Quantity"] = $txtQuantity->SubmittedValue;
            $redemptiondata["TotalItemPoints"] = $hdnTotalItemPoints->SubmittedValue;

            $personaldetails = $_MemberInfo->SelectByWhere('WHERE MID = '.$redemptiondata["MID"]);
            if(isset($personaldetails[0]) && $personaldetails[0] != ""){
                $redemptiondata["PlayerName"] = $personaldetails[0]['FirstName']." ".$personaldetails[0]['LastName'];
                $redemptiondata["Birthdate"] = $personaldetails[0]['Birthdate'];
                $redemptiondata["Email"] = $personaldetails[0]['Email'];
                $redemptiondata["MobileNumber"] = $personaldetails[0]['MobileNumber'];
            }
        }

        if($redemptiondata["Quantity"] > 0){
            if($redemptiondata["PlayerPoints"] < $redemptiondata["TotalItemPoints"]){
                $message = "Player Redemption: Transaction Failed. Card may have insufficient points.";
                $_AuditTrail->StartTransaction();
                if($source == 1){
                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                } else {
                    $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                }
                if(!App::HasError()){
                    $_AuditTrail->CommitTransaction();
                    App::SetErrorMessage($message);
                    $txtQuantity->Text = "";
                    $hdnTotalItemPoints->Text = "";
                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                } else {
                    $message = "Failed to log event on database.";
                    $_AuditTrail->RollBackTransaction();
                    App::SetErrorMessage($message);
                    $txtQuantity->Text = "";
                    $hdnTotalItemPoints->Text = "";
                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
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
                            
                            $tobecurrentpoints = (int)$redemptiondata["PlayerPoints"] - (int)$redemptiondata["TotalItemPoints"];

                            //Check if there is a pending redemption for this player. 
                            if($tobecurrentpoints < 0){
                                    $message = "Player Redemption: Transaction Failed. Card may have insufficient points.";
                                    $_AuditTrail->StartTransaction();
                                    if($source == 1){
                                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                    } else {
                                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                    }
                                    if(!App::HasError()){
                                        $_AuditTrail->CommitTransaction();
                                        App::SetErrorMessage($message);
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                    } else {
                                        $message = "Failed to log event on database.";
                                        $_AuditTrail->RollBackTransaction();
                                        App::SetErrorMessage($message);
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                    }
                            } else {    
                                    $pendingredemption = $_PendingRedemption->checkPendingRedemption($redemptiondata["MID"]);

                                    //Check if there is pending redemption, if yes throw error message.
                                    if($pendingredemption){
                                            $message = "Player Redemption: Transaction Failed. Card has a pending redemption.";
                                            $_AuditTrail->StartTransaction();
                                            if($source == 1){
                                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                            } else {
                                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                            }
                                            if(!App::HasError()){
                                                $_AuditTrail->CommitTransaction();
                                                App::SetErrorMessage($message);
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                            } else {
                                                $message = "Failed to log event on database.";
                                                $_AuditTrail->RollBackTransaction();
                                                App::SetErrorMessage($message);
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                            }
                                    } else {
                                            $_CouponRedemptionLogs->StartTransaction();
                                            $_CouponRedemptionLogs->insertCouponLogs($redemptiondata["MID"], $redemptiondata["RewardItemID"], $redemptiondata["Quantity"],$source, $RedeemedDate);

                                            if(!App::HasError()){
                                                $_CouponRedemptionLogs->CommitTransaction();
                                                $CouponRedemptionLogID = $_CouponRedemptionLogs->LastInsertID;

                                                $_RaffleCoupons->StartTransaction();
                                                $itr = 0;
                                                do{
                                                    if($source == 1){
                                                        $_RaffleCoupons->updateRaffleCouponsStatus($availablecoupon[$itr]["RaffleCouponID"], $CouponRedemptionLogID, $redemptiondata["RewardItemID"],$redemptiondata["MID"]);
                                                    } else {
                                                        $_RaffleCoupons->updateRaffleCouponsStatus($availablecoupon[$itr]["RaffleCouponID"], $CouponRedemptionLogID, $redemptiondata["RewardItemID"],$_SESSION['userinfo']['AID']);
                                                    }
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
                                                            if($source == 1){
                                                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                            } else {
                                                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                            }
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
                                                                $txtQuantity->Text = "";
                                                                $hdnTotalItemPoints->Text = "";
                                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                            }
                                                        } else {
                                                            $_MemberCards->RollBackTransaction();
                                                            $message = "Player Redemption: Error in updating redemption log.";
                                                            $_AuditTrail->StartTransaction();
                                                            if($source == 1){
                                                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                            } else {
                                                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                            }
                                                            if(!$_AuditTrail->HasError){
                                                                $_AuditTrail->CommitTransaction();
                                                                App::SetErrorMessage($message);
                                                                $txtQuantity->Text = "";
                                                                $hdnTotalItemPoints->Text = "";
                                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                            } else {
                                                                $message = "Failed to log event on database.";
                                                                $_AuditTrail->RollBackTransaction();
                                                                App::SetErrorMessage($message);
                                                                $txtQuantity->Text = "";
                                                                $hdnTotalItemPoints->Text = "";
                                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
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
                                                        if($source == 1){
                                                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                        } else {
                                                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                        }
                                                        if(!$_AuditTrail->HasError){
                                                            $_AuditTrail->CommitTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        } else {
                                                            $message = "Failed to log event on database.";
                                                            $_AuditTrail->RollBackTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
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
                                                    if($source == 1){
                                                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                    } else {
                                                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                    }
                                                    if(!$_AuditTrail->HasError){
                                                        $_AuditTrail->CommitTransaction();
                                                        App::SetErrorMessage($message);
                                                        $txtQuantity->Text = "";
                                                        $hdnTotalItemPoints->Text = "";
                                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                    } else {
                                                        $message = "Failed to log event on database.";
                                                        $_AuditTrail->RollBackTransaction();
                                                        App::SetErrorMessage($message);
                                                        $txtQuantity->Text = "";
                                                        $hdnTotalItemPoints->Text = "";
                                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                    }
                                                }
                                            } else {
                                                $_CouponRedemptionLogs->RollBackTransaction();
                                                $message = "Player Redemption: Error in redemption logging.";
                                                $_AuditTrail->StartTransaction();
                                                if($source == 1){
                                                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                } else {
                                                    $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                }
                                                if(!$_AuditTrail->HasError){
                                                    $_AuditTrail->CommitTransaction();
                                                    App::SetErrorMessage($message);
                                                    $txtQuantity->Text = "";
                                                    $hdnTotalItemPoints->Text = "";
                                                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                } else {
                                                    $message = "Failed to log event on database.";
                                                    $_AuditTrail->RollBackTransaction();
                                                    App::SetErrorMessage($message);
                                                    $txtQuantity->Text = "";
                                                    $hdnTotalItemPoints->Text = "";
                                                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                }
                                            }
                                    }
                            }
                        } else {
                            $message = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                            $_AuditTrail->StartTransaction();
                            if($source == 1){
                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                            } else {
                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                            }
                            if(!$_AuditTrail->HasError){
                                $_AuditTrail->CommitTransaction();
                                App::SetErrorMessage($message);
                                $txtQuantity->Text = "";
                                $hdnTotalItemPoints->Text = "";
                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                            } else {
                                $message = "Failed to log event on database.";
                                $_AuditTrail->RollBackTransaction();
                                App::SetErrorMessage($message);
                                $txtQuantity->Text = "";
                                $hdnTotalItemPoints->Text = "";
                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                            }
                        }
                    } else {
                        $message = "Player Redemption: Transaction Failed.Number of available coupon is insufficient.";
                        $_AuditTrail->StartTransaction();
                        if($source == 1){
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                        } else {
                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                        }
                        if(!$_AuditTrail->HasError){
                            $_AuditTrail->CommitTransaction();
                            App::SetErrorMessage($message);
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                        } else {
                            $message = "Failed to log event on database.";
                            $_AuditTrail->RollBackTransaction();
                            App::SetErrorMessage($message);
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
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

                            $tobecurrentpoints = (int)$redemptiondata["PlayerPoints"] - (int)$redemptiondata["TotalItemPoints"];

                             if($tobecurrentpoints < 0){
                                    $message = "Player Redemption: Transaction Failed. Card may have insufficient points.";
                                    $_AuditTrail->StartTransaction();
                                    if($source == 1){
                                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                    } else {
                                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                    }
                                    if(!App::HasError()){
                                        $_AuditTrail->CommitTransaction();
                                        App::SetErrorMessage($message);
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                    } else {
                                        $message = "Failed to log event on database.";
                                        $_AuditTrail->RollBackTransaction();
                                        App::SetErrorMessage($message);
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                    }
                             } else {

                                    $pendingredemption = $_PendingRedemption->checkPendingRedemption($redemptiondata["MID"]);

                                    //Check if there is a pending redemption for this player. 
                                    if($pendingredemption){
                                            $message = "Player Redemption: Transaction Failed. Card has a pending redemption.";
                                            $_AuditTrail->StartTransaction();
                                            if($source == 1){
                                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                            } else {
                                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                            }
                                            if(!App::HasError()){
                                                $_AuditTrail->CommitTransaction();
                                                App::SetErrorMessage($message);
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                            } else {
                                                $message = "Failed to log event on database.";
                                                $_AuditTrail->RollBackTransaction();
                                                App::SetErrorMessage($message);
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                            }
                                    } else {
                                            $_ItemRedemptionLogs->StartTransaction();
                                            $_ItemRedemptionLogs->insertItemLogs($RedeemedDate, $redemptiondata["MID"], $redemptiondata["RewardItemID"], $redemptiondata["Quantity"],$source);

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
                                                        if($source == 1){
                                                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                        } else {
                                                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                        }
                                                        if(!$_AuditTrail->HasError){
                                                            $_AuditTrail->CommitTransaction();
                                                            $_SESSION["PreviousRedemption"] = $ItemRedemptionLogID;
                                                            $serialnumber = str_pad($ItemRedemptionLogID, 4, "0", STR_PAD_LEFT)."A".str_pad($redemptiondata["MID"], 4, "0", STR_PAD_LEFT)."B" ;
                                                            $checkstring = $redemptiondata["RewardOfferID"] .$redemptiondata["Quantity"] . $redemptiondata["CardNumber"]  . $redemptiondata["PlayerName"]  . date("F j, Y", strtotime($redemptiondata["Birthdate"])) . 
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
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        }
                                                    } else {
                                                        $_RewardItems->RollBackTransaction();
                                                        $message = "Player Redemption: Transaction failed.Please try again.";
                                                        $_AuditTrail->StartTransaction();
                                                        if($source == 1){
                                                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                        } else {
                                                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                        }
                                                        if(!$_AuditTrail->HasError){
                                                            $_AuditTrail->CommitTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        } else {
                                                            $message = "Failed to log event on database.";
                                                            $_AuditTrail->RollBackTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
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
                                                        if($source == 1){
                                                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                        } else {
                                                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                        }
                                                        if(!$_AuditTrail->HasError){
                                                            $_AuditTrail->CommitTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        } else {
                                                            $message = "Failed to log event on database.";
                                                            $_AuditTrail->RollBackTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        }
                                                    } else {
                                                        $_ItemRedemptionLogs->RollBackTransaction();
                                                        $message = "Player Redemption: Transaction failed. Error in updating redemption log";
                                                        $_AuditTrail->StartTransaction();
                                                        if($source == 1){
                                                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                        } else {
                                                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                        }
                                                        if(!$_AuditTrail->HasError){
                                                            $_AuditTrail->CommitTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        } else {
                                                            $message = "Failed to log event on database.";
                                                            $_AuditTrail->RollBackTransaction();
                                                            App::SetErrorMessage($message);
                                                            $txtQuantity->Text = "";
                                                            $hdnTotalItemPoints->Text = "";
                                                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        }
                                                    }
                                                }

                                            } else {
                                                $_ItemRedemptionLogs->RollBackTransaction();
                                                $message = "Player Redemption: Error in redemption logging.";
                                                $_AuditTrail->StartTransaction();
                                                if($source == 1){
                                                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                } else {
                                                    $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                }
                                                if(!$_AuditTrail->HasError){
                                                    $_AuditTrail->CommitTransaction();
                                                    App::SetErrorMessage($message);
                                                    $txtQuantity->Text = "";
                                                    $hdnTotalItemPoints->Text = "";
                                                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                } else {
                                                    $message = "Failed to log event on database.";
                                                    $_AuditTrail->RollBackTransaction();
                                                    App::SetErrorMessage($message);
                                                    $txtQuantity->Text = "";
                                                    $hdnTotalItemPoints->Text = "";
                                                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                }
                                            }
                                    }


                             }   
                        }
                    } else {
                        $message = "Player Redemption: Transaction Failed.Number of available item is insufficient.";
                        $_AuditTrail->StartTransaction();
                        if($source == 1){
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                        } else {
                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                        }
                        if(!$_AuditTrail->HasError){
                            $_AuditTrail->CommitTransaction();
                            App::SetErrorMessage($message);
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                        } else {
                            $message = "Failed to log event on database.";
                            $_AuditTrail->RollBackTransaction();
                            App::SetErrorMessage($message);
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                        }
                    }
                }
            }
        } else {
            $message = "Player Redemption: Transaction Failed.Invalid Item/Coupon Quantity.";
            $_AuditTrail->StartTransaction();
            if($source == 1){
                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
            } else {
                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
            }
            if(!$_AuditTrail->HasError){
                $_AuditTrail->CommitTransaction();
                App::SetErrorMessage($message);
                $txtQuantity->Text = "";
                $hdnTotalItemPoints->Text = "";
                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
            } else {
                $message = "Failed to log event on database.";
                $_AuditTrail->RollBackTransaction();
                App::SetErrorMessage($message);
                $txtQuantity->Text = "";
                $hdnTotalItemPoints->Text = "";
                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
            }
        }

} else {
        $msg = "Not Connected";
        session_destroy();
        echo'<script> alert("Session is Expired"); window.location="login.php"; </script> ';
}

?>
