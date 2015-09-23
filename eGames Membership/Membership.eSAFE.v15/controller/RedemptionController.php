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
    App::LoadModuleClass("Membership", "MemberInfo");
    $_MemberInfo = new MemberInfo();
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
        App::LoadCore('ErrorLogger.php');
        $logger = new ErrorLogger();
        
        //proceed with redemption process if it has session
        if($source == 1){
            $redemptiondata["MID"] = $MID;
            $redemptiondata["RewardItemID"] = $_SESSION['RewardItemsInfo']['RewardItemID'];
            $redemptiondata["RewardID"] = $_SESSION['RewardItemsInfo']['RewardID'];
            $redemptiondata["ItemName"] = $hdnItemName->SubmittedValue;
            if($redemptiondata["RewardID"] == 1){
                $redemptiondata["Quantity"] = $txtItemQuantity->SubmittedValue;
            } else {
                $redemptiondata["Quantity"] = $txtQuantity->SubmittedValue;
            }
            $redemptiondata["TotalItemPoints"] = $hdnTotalItemPoints->SubmittedValue;
            $redemptiondata["CardNumber"] = $hdnCardNumber->SubmittedValue;
            //$PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($redemptiondata["CardNumber"]);
            if(App::getParam('PointSystem') == 2) {
                $api = $_PcwsWrapper->getCompPoints($redemptiondata["CardNumber"], 1);
                $PlayerPoints = $api['GetCompPoints']['CompBalance'];
            }
            else {
                $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($redemptiondata["CardNumber"]);
                $PlayerPoints = $PlayerPoints[0]['CurrentPoints'];
            }
            $redemptiondata["PlayerPoints"] = $PlayerPoints;
            //$redemptiondata["PlayerPoints"] = $PlayerPoints[0]['CurrentPoints'];
            $redemptiondata["PlayerName"] = $playername;
            $redemptiondata["Birthdate"] = $birthdate;
            $redemptiondata["Email"] = $email;
            $redemptiondata["MobileNumber"] = $contactno;
        } else {
            $redemptiondata["MID"] = $_SESSION["CardRed"]["MID"];
            $redemptiondata["RewardItemID"] = $hdnRewardItemID->SubmittedValue;
            $redemptiondata["RewardID"] = $_SESSION['CardRed']['RewardID'];
            $redemptiondata["ItemName"] = $hdnItemName->SubmittedValue;
            if($redemptiondata["RewardID"] == 1){
                $redemptiondata["Quantity"] = $txtItemQuantity->SubmittedValue;
            } else {
                $redemptiondata["Quantity"] = $txtQuantity->SubmittedValue;
            }
            $redemptiondata["TotalItemPoints"] = $hdnTotalItemPoints->SubmittedValue;
            $redemptiondata["CardNumber"] = $_SESSION["CardRed"]["CardNumber"];
            //$PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($redemptiondata["CardNumber"]);
            if(App::getParam('PointSystem') == 2 ){
                $api = $_PcwsWrapper->getCompPoints($redemptiondata["CardNumber"], 1);
                $PlayerPoints = $api['GetCompPoints']['CompBalance'];
            }
            else {
                $PlayerPoints = $_MemberCards->getCurrentPointsByCardNumber($redemptiondata["CardNumber"]);
                $PlayerPoints = $PlayerPoints[0]['CurrentPoints'];
            }
            $redemptiondata["PlayerPoints"] = $PlayerPoints;
            //$redemptiondata["PlayerPoints"] = $PlayerPoints[0]['CurrentPoints'];
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
                $message = "Transaction Failed. Card may have insufficient points.";
                $_AuditTrail->StartTransaction();
                if($source == 1){
                    if($redemptiondata["RewardID"] == 1) {
                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                    } else {
                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                    }
                } else {
                    if($redemptiondata["RewardID"] == 1) {
                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                    } else {
                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                    }
                }
                if(!App::HasError()){
                    $_AuditTrail->CommitTransaction();
                    App::SetErrorMessage($message);
                    $txtQuantity->Text = "";
                    $hdnTotalItemPoints->Text = "";
                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                } else {
                    $logmessage = "Failed to log event on Audit Trail.";
                    $_AuditTrail->RollBackTransaction();
                    App::ClearStatus();
                    $txtQuantity->Text = "";
                    $hdnTotalItemPoints->Text = "";
                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                    $logtype = $redemptiondata["RewardID"] == 2 ? "[COUPON REDEMPTION ERROR] ": "[ITEM REDEMPTION ERROR] ";
                    $logger->log($logger->logdate,$logtype, $logmessage);
                }
                App::SetErrorMessage($message);
            } else {
                $IsCoupon = ($redemptiondata["RewardID"] == 1 || $redemptiondata["RewardID"] == "1") ? false:true;

                if($IsCoupon){

                    //Check if the available coupon is greater than or match with the quantity avail by the player.
                    $availablecoupon = $_RaffleCoupons->getAvailableCoupons($redemptiondata["RewardItemID"], $redemptiondata["Quantity"]);

                    if(count($availablecoupon) == $redemptiondata["Quantity"]){

                        //Redemption Process for Coupon
                        $offerenddate = $_RewardItems->getOfferEndDate($redemptiondata["RewardItemID"]);
                        $RedeemedDate = $offerenddate["ItemCurrentDate"];
                        
                        //check if the availing date  is greater than the End date of the reward offer.
                        if($RedeemedDate <= $offerenddate["OfferEndDate"]){
                            
                            $tobecurrentpoints = (int)$redemptiondata["PlayerPoints"] - (int)$redemptiondata["TotalItemPoints"];
                            
                            if($tobecurrentpoints < 0){
                                    $message = "Transaction Failed. Card may have insufficient points.";
                                    $_AuditTrail->StartTransaction();
                                    if($source == 1){
                                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                    } else {
                                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                    }
                                    if(!App::HasError()){
                                        $_AuditTrail->CommitTransaction();
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                    } else {
                                        $logmessage = "Failed to log event on Audit Trail.";
                                        $_AuditTrail->RollBackTransaction();
                                        App::ClearStatus();
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                        $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logmessage);
                                    }
                                    App::SetErrorMessage($message);
                            } else {
                                    $pendingredemption = $_PendingRedemption->checkPendingRedemption($redemptiondata['MID']);
                                    
                                    //Check if there is pending  redemption, if yes throw error message.
                                    if($pendingredemption){
                                            $message = "Transaction Failed. Card has a pending redemption.";
                                            $_AuditTrail->StartTransaction();
                                            if($source == 1){
                                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                            } else {
                                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                            }
                                            if(!App::HasError()){
                                                $_AuditTrail->CommitTransaction();
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                            } else {
                                                $logmessage = "Failed to log event on Audit Trail.";
                                                $_AuditTrail->RollBackTransaction();
                                                App::ClearStatus();
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logmessage);
                                            }
                                            App::SetErrorMessage($message);
                                    } else {
                                        
                                        //Process Coupon Redemption
                                        $resultsarray = $_RedemptionProcess->ProcessCouponRedemption($redemptiondata["MID"], $redemptiondata["RewardItemID"], $redemptiondata["Quantity"], $redemptiondata["TotalItemPoints"], 
                                                                                                                                                            $redemptiondata["CardNumber"], $source, $RedeemedDate);
                                        
                                        if($resultsarray["IsSuccess"]){
                                            $OldCP = number_format($resultsarray["OldCP"]);
                                            $RedeemedPoints = number_format($redemptiondata["TotalItemPoints"]);
                                            $message = "CP: ".$OldCP.", Item: ".$redemptiondata["ItemName"].", RP: ".$RedeemedPoints.", Series: ".$_SESSION['RewardOfferCopy']['CouponSeries'];
                                        } else {
                                            $message = $resultsarray["Message"];
                                        }
                                        
                                        $_AuditTrail->StartTransaction();
                                        if($source == 1){
                                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                        } else {
                                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                        }
                                        if(!$_AuditTrail->HasError){
                                            $_AuditTrail->CommitTransaction();
                                        } else {
                                            $logmessage = "Failed to log event on Audit Trail.";
                                            $_AuditTrail->RollBackTransaction();
                                            App::ClearStatus();
                                            $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logmessage);
                                        }
                                        
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                        
                                        if(!$resultsarray["IsSuccess"]){
                                            App::SetErrorMessage($message);
                                        } else {
                                            //send SMS alert to player
                                            sendSMS(SMSRequestLogs::COUPON_REDEMPTION, $redemptiondata["MobileNumber"], $RedeemedDate, $_SESSION['RewardOfferCopy']['SerialNumber'], $redemptiondata["Quantity"], "SMSC", $resultsarray["LastInsertedID"], '', $_SESSION['RewardOfferCopy']['CouponSeries']);    

                                            $showcouponredemptionwindow = true;
                                            $showitemredemptionwindow = false;
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
                                $txtQuantity->Text = "";
                                $hdnTotalItemPoints->Text = "";
                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                            } else {
                                $logmessage = "Failed to log event on Audit Trail.";
                                $_AuditTrail->RollBackTransaction();
                                App::ClearStatus();
                                $txtQuantity->Text = "";
                                $hdnTotalItemPoints->Text = "";
                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logmessage);
                            }
                            App::SetErrorMessage($message);
                        }
                    } else {
                        $message = "Transaction Failed. Raffle Coupon is either insufficient or unavailable.";
                        $_AuditTrail->StartTransaction();
                        if($source == 1){
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                        } else {
                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                        }
                        if(!$_AuditTrail->HasError){
                            $_AuditTrail->CommitTransaction();
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                        } else {
                            $logmessage = "Failed to log event on Audit Trail.";
                            $_AuditTrail->RollBackTransaction();
                            App::ClearStatus();
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                            $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logmessage);
                        }
                        App::SetErrorMessage($message);
                    }

                } else {

                    //Check if the available item is greater than or match with the quantity avail by the player.
                    $availableitemcount = $_RewardItems->getAvailableItemCount($redemptiondata["RewardItemID"]);

                    if($availableitemcount["AvailableItemCount"] >= $redemptiondata["Quantity"]){
                        
                        $availableserialcode = $_ItemSerialCodes->getAvailableSerialCodeCount($redemptiondata["RewardItemID"],$redemptiondata["Quantity"]);
                        
                        if(count($availableserialcode) >= $redemptiondata["Quantity"]) { 
                                //Redemption Process for Item
                                $offerenddate = $_RewardItems->getOfferEndDate($redemptiondata["RewardItemID"]);
                                $RedeemedDate = $offerenddate["ItemCurrentDate"];
                                $CurrentDate = $offerenddate["CurrentDate"];

                                //check if the availing date  is greater than the End date of the reward offer.
                                if($RedeemedDate <= $offerenddate["OfferEndDate"]){

                                    $tobecurrentpoints = (int)$redemptiondata['PlayerPoints'] - (int)$redemptiondata['TotalItemPoints'];

                                    if($tobecurrentpoints < 0){
                                            $message = "Transaction Failed. Card may have insufficient points.";
                                            $_AuditTrail->StartTransaction();
                                            if($source == 1){
                                                $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                            } else {
                                                $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                            }
                                            if(!$_AuditTrail->HasError){
                                                $_AuditTrail->CommitTransaction();
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                            } else {
                                                $logmessage = "Failed to log event on database.";
                                                $_AuditTrail->RollBackTransaction();
                                                App::ClearStatus();
                                                $txtQuantity->Text = "";
                                                $hdnTotalItemPoints->Text = "";
                                                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logmessage);
                                            }
                                            App::SetErrorMessage($message);
                                    } else {
                                            $pendingredemption = $_PendingRedemption->checkPendingRedemption($redemptiondata["MID"]);

                                            //Check if there is a pending redemption for this player.
                                            if($pendingredemption){
                                                    $message = "Transaction Failed. Card has a pending redemption.";
                                                    $_AuditTrail->StartTransaction();
                                                    if($source == 1){
                                                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                    } else {
                                                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                    }
                                                    if(!$_AuditTrail->HasError){
                                                        $_AuditTrail->CommitTransaction();
                                                        $txtQuantity->Text = "";
                                                        $hdnTotalItemPoints->Text = "";
                                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                    } else {
                                                        $logmessage = "Failed to log event on Audit Trail.";
                                                        $_AuditTrail->RollBackTransaction();
                                                        App::ClearStatus();
                                                        $txtQuantity->Text = "";
                                                        $hdnTotalItemPoints->Text = "";
                                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                                        $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logmessage);
                                                    }
                                                    App::SetErrorMessage($message);
                                            } else {

                                                    //Process Item Redemption
                                                    $resultsarray = $_RedemptionProcess->ProcessItemRedemption($redemptiondata["MID"], $redemptiondata["RewardItemID"], $redemptiondata["Quantity"], 
                                                                                                            $redemptiondata['TotalItemPoints'], $redemptiondata["CardNumber"], $source, $RedeemedDate);

                                                    if($resultsarray["IsSuccess"]){
                                                        $OldCP = number_format($resultsarray["OldCP"]);
                                                        $RedeemedPoints = number_format($redemptiondata["TotalItemPoints"]);
                                                        $message = "CP: ".$OldCP.", Item: ".$redemptiondata["ItemName"].", RP: ".$RedeemedPoints;
                                                    } else {
                                                        $message = $resultsarray["Message"];
                                                    }

                                                    $_AuditTrail->StartTransaction();
                                                    if($source == 1){
                                                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                                    } else {
                                                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                    }

                                                    if(!$_AuditTrail->HasError){
                                                        $_AuditTrail->CommitTransaction();
                                                    } else {
                                                        $logmessage = "Failed to log event on Audit Trail.";
                                                        $_AuditTrail->RollBackTransaction();
                                                        App::ClearStatus();
                                                        $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logmessage);
                                                    }

                                                    $txtQuantity->Text = "";
                                                    $hdnTotalItemPoints->Text = "";
                                                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";

                                                    if(!$resultsarray["IsSuccess"]){
                                                        App::SetErrorMessage($message);
                                                    } else {
                                                        //send SMS to player
                                                        $ctr = count($_SESSION['RewardOfferCopy']['SerialNumber']);
                                                        $totalpoints = $redemptiondata["TotalItemPoints"]/$redemptiondata["Quantity"];
                                                        for($itr = 0; $itr < $ctr; $itr++){
                                                            sendSMS(SMSRequestLogs::ITEM_REDEMPTION, $redemptiondata["MobileNumber"], $RedeemedDate, $_SESSION['RewardOfferCopy']['SerialNumber'][$itr], 1, "SMSI", 
                                                                                $resultsarray["LastInsertedID"][$itr], $totalpoints);          
                                                        }

                                                        //send SMS to player (preparation for per redemption sms alert)
                                                        //if(count($_SESSION['RewardOfferCopy']['SerialNumber']) > 1){ 
                                                            //$SerialCodeSeries = $_SESSION['RewardOfferCopy']['SerialNumber'][0]." - ".end($_SESSION['RewardOfferCopy']['SerialNumber']);
                                                            //$InsertedIDSeries = $resultsarray["LastInsertedID"][0]." - ".end($resultsarray["LastInsertedID"]);
                                                        //} else if(count($_SESSION['RewardOfferCopy']['SerialNumber']) == 1){ 
                                                            //$SerialCodeSeries = $_SESSION['RewardOfferCopy']['SerialNumber'][0];
                                                            //$InsertedIDSeries = $resultsarray["LastInsertedID"][0];
                                                        //}
                                                        //$totalpoints = $redemptiondata["TotalItemPoints"]/$redemptiondata["Quantity"];
                                                        //sendSMS(SMSRequestLogs::ITEM_REDEMPTION, $redemptiondata["MobileNumber"], $RedeemedDate, $SerialCodeSeries, 1, "SMSI", $InsertedIDSeries, $totalpoints);   

                                                        $showcouponredemptionwindow = true;
                                                        $showitemredemptionwindow = true;
                                                    }
                                            }
                                    }
                                } else {
                                    $message = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                                    $_AuditTrail->StartTransaction();
                                    if($source == 1){
                                        $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                    } else {
                                        $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                    }
                                    if(!$_AuditTrail->HasError){
                                        $_AuditTrail->CommitTransaction();
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                    } else {
                                        $logmessage = "Failed to log event on Audit Trail.";
                                        $_AuditTrail->RollBackTransaction();
                                        App::ClearStatus();
                                        $txtQuantity->Text = "";
                                        $hdnTotalItemPoints->Text = "";
                                        echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                        $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logmessage);
                                    }
                                    App::SetErrorMessage($message);
                                }
                        } else {
                                $message = "Transaction Failed.Serial Code is unavailable.";
                                $_AuditTrail->StartTransaction();
                                if($source == 1){
                                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                                } else {
                                    $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                }
                                if(!$_AuditTrail->HasError){
                                    $_AuditTrail->CommitTransaction();
                                    $txtQuantity->Text = "";
                                    $hdnTotalItemPoints->Text = "";
                                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                } else {
                                    $logmessage = "Failed to log event on Audit Trail.";
                                    $_AuditTrail->RollBackTransaction();
                                    App::ClearStatus();
                                    $txtQuantity->Text = "";
                                    $hdnTotalItemPoints->Text = "";
                                    echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                                    $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logmessage);
                                }
                                App::SetErrorMessage($message);
                        }
                    } else {
                        $message = "Transaction Failed.Number of available item is insufficient.";
                        $_AuditTrail->StartTransaction();
                        if($source == 1){
                            $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                        } else {
                            $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                        }
                        if(!$_AuditTrail->HasError){
                            $_AuditTrail->CommitTransaction();
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                        } else {
                            $logmessage = "Failed to log event on Audit Trail.";
                            $_AuditTrail->RollBackTransaction();
                            App::ClearStatus();
                            $txtQuantity->Text = "";
                            $hdnTotalItemPoints->Text = "";
                            echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                            $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logmessage);
                        }
                        App::SetErrorMessage($message);
                    }
                }
            }
        } else {
            $message = "Transaction Failed.Invalid Item/Coupon Quantity.";
            $_AuditTrail->StartTransaction();
            if($source == 1){
                if($redemptiondata["RewardID"] == 1) {
                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_ITEM_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                } else {
                    $_AuditTrail->logEvent(AuditFunctions::PLAYER_REDEMPTION, $message, array('ID'=>$redemptiondata["MID"], 'SessionID'=>$_SESSION['MemberInfo']['SessionID']));
                }
            } else {
                if($redemptiondata["RewardID"] == 1) {
                    $_AuditTrail->logEvent(AuditFunctions::CASHIER_ITEM_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                } else {
                    $_AuditTrail->logEvent(AuditFunctions::CASHIER_REDEMPTION, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                }
            }
            if(!$_AuditTrail->HasError){
                $_AuditTrail->CommitTransaction();
                $txtQuantity->Text = "";
                $hdnTotalItemPoints->Text = "";
                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
            } else {
                $logmessage = "Failed to log event on Audit Trail.";
                $_AuditTrail->RollBackTransaction();
                App::ClearStatus();
                $txtQuantity->Text = "";
                $hdnTotalItemPoints->Text = "";
                echo "<script type='text/javascript'>$('#TotalItemPoints').html('');</script>";
                $logtype = $redemptiondata["RewardID"] == 2 ? "[COUPON REDEMPTION ERROR] ": "[ITEM REDEMPTION ERROR] ";
                $logger->log($logger->logdate,$logtype, $logmessage);
            }
            App::SetErrorMessage($message);
        }
}

/**
 * @Description Function for sending SMS alert to player's mobile number upon registration or redemption.
 * @param int $methodid
 * @param string $mobileno
 * @param date $RedeemedDate
 * @param string $serialnumber
 * @param int $quantity
 * @param string $prefix_trackingID
 * @param string $couponseries , optional: depends on method used.
 */
function sendSMS($methodid, $mobileno, $RedeemedDate, $serialnumber, $quantity, $prefix_trackingID, $LastInsertedID, $redeemedpoints, $couponseries = ''){

        App::LoadModuleClass('Membership', 'MembershipSmsAPI');
        App::LoadModuleClass("Rewards", "SMSRequestLogs");
        App::LoadCore('ErrorLogger.php');
        $logger = new ErrorLogger();
        $_SMSRequestLogs = new SMSRequestLogs();

        //match to 09 or 639 in mobile number
        $match = substr($mobileno, 0, 3);
        if($match == "639"){
            $mncount = count($mobileno);
            if(!$mncount == 12){
                $idtype = $methodid == 1 ? "CouponRedemptionLogID: ": ($methodid == 2 ? "ItemRedemptionLogID: ": "");
                $logtype = $methodid == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodid == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                $message = "Failed to send SMS: Invalid Mobile Number [".$idtype." $LastInsertedID].";
                $logger->log($logger->logdate,$logtype, $message);
            } else {
                $templateid = $_SMSRequestLogs->getSMSMethodTemplateID($methodid);
                $smslastinsertedid = $_SMSRequestLogs->insertSMSRequestLogs($methodid, $mobileno, $RedeemedDate,$couponseries, $serialnumber, $quantity);
                if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                    $trackingid = $prefix_trackingID.$smslastinsertedid;
                    $apiURL = App::getParam("SMSURI");    
                    $app_id = App::getParam("app_id");    
                    $_MembershipSmsAPI = new MembershipSmsAPI($apiURL, $app_id);
                    if($couponseries != '' && $methodid == 1){
                        $smsresult = $_MembershipSmsAPI->sendCouponRedemption($mobileno, $templateid, $couponseries, $serialnumber, $quantity, $trackingid);
                    } else {
                        $smsresult = $_MembershipSmsAPI->sendItemRedemption($mobileno, $templateid, $serialnumber, $trackingid, $redeemedpoints);
                    }

                    if($smsresult['status'] != 1){
                        $idtype = $methodid == 1 ? "CouponRedemptionLogID: ": ($methodid == 2 ? "ItemRedemptionLogID: ": "");
                        $logtype = $methodid == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodid == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                        $message = "Failed to send SMS [".$idtype." $LastInsertedID].";
                        $logger->log($logger->logdate,$logtype, $message);
                    }
                } else {
                    $idtype = $methodid == 1 ? "CouponRedemptionLogID: ": ($methodid == 2 ? "ItemRedemptionLogID: ": "");
                    $logtype = $methodid == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodid == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                    $message = "Failed to send SMS: Failed to log event on database [".$idtype." $LastInsertedID].";
                    $logger->log($logger->logdate,$logtype, $message);
                }
            }
        } else {
            $match = substr($mobileno, 0, 2);
            if($match == "09"){
                $mncount = count($mobileno);
                if(!$mncount == 11){
                     $idtype = $methodid == 1 ? "CouponRedemptionLogID: ": ($methodid == 2 ? "ItemRedemptionLogID: ": "");
                     $logtype = $methodid == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodid == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                     $message = "Failed to send SMS: Invalid Mobile Number [".$idtype." $LastInsertedID].";
                     $logger->log($logger->logdate,$logtype, $message);
                 } else {
                    $mobileno = str_replace("09", "639", $mobileno);
                    $templateid = $_SMSRequestLogs->getSMSMethodTemplateID($methodid);
                    $smslastinsertedid = $_SMSRequestLogs->insertSMSRequestLogs($methodid, $mobileno, $RedeemedDate, $couponseries, $serialnumber, $quantity);
                    if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                        $trackingid = $prefix_trackingID.$smslastinsertedid;
                        $apiURL = App::getParam("SMSURI");    
                        $app_id = App::getParam("app_id");    
                        $_MembershipSmsAPI = new MembershipSmsAPI($apiURL, $app_id);
                        if($couponseries != '' && $methodid == 1){
                            $smsresult = $_MembershipSmsAPI->sendCouponRedemption($mobileno, $templateid, $couponseries, $serialnumber, $quantity, $trackingid);
                        } else {
                            $smsresult = $_MembershipSmsAPI->sendItemRedemption($mobileno, $templateid, $serialnumber, $trackingid,$redeemedpoints);
                        }
                        if($smsresult['status'] != 1){
                            $idtype = $methodid == 1 ? "CouponRedemptionLogID: ": ($methodid == 2 ? "ItemRedemptionLogID: ": "");
                            $logtype = $methodid == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodid == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                            $message = "Failed to send SMS [".$idtype." $LastInsertedID].";
                            $logger->log($logger->logdate,$logtype, $message);
                        }
                    } else {
                        $idtype = $methodid == 1 ? "CouponRedemptionLogID: ": ($methodid == 2 ? "ItemRedemptionLogID: ": "");
                        $logtype = $methodid == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodid == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                        $message = "Failed to send SMS: Error on logging event in database [".$idtype." $LastInsertedID].";
                        $logger->log($logger->logdate,$logtype, $message);
                    }
                 }
            } else {
                $idtype = $methodid == 1 ? "CouponRedemptionLogID: ": ($methodid == 2 ? "ItemRedemptionLogID: ": "");
                $logtype = $methodid == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodid == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                $message = "Failed to send SMS: Invalid Mobile Number [".$idtype." $LastInsertedID].";
                $logger->log($logger->logdate,$logtype, $message);
            }
        }
}

?>
