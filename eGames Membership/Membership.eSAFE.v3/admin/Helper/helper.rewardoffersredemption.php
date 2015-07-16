<?php

/**
 *@Description: Fetching and encoding data into JSON array to be displayed in JQGRID for list of all reward offers available based on player card type.
 *@Author: aqdepliyan
 *@DateCreated: 07-16-2013 02:46 PM
 */

if(isset($_POST["functiontype"]) && $_POST["functiontype"] != ""){

            //Attach and Initialize framework
            require_once("../../init.inc.php");

            //Load modules needed
            App::LoadModuleClass('Rewards', 'RewardItems');
            App::LoadModuleClass('Membership', 'MemberInfo');
            App::LoadModuleClass('Membership', 'Cities');
            App::LoadModuleClass("Rewards", "CouponBatches");
            App::LoadModuleClass('Rewards', 'RaffleCoupons');
            App::LoadModuleClass('Loyalty', 'MemberCards');
            App::LoadModuleClass('Loyalty', 'Cards');
            App::LoadModuleClass('Loyalty', 'CardTransactions');
            App::LoadModuleClass('Kronus', 'Sites');
            App::LoadModuleClass('Membership', 'PcwsWrapper');

            App::LoadCore('Validation.class.php');
            App::LoadCore('ErrorLogger.php');

            //Initialize Modules
            $_RewardItems = new RewardItems();
            $_MemberCards = new MemberCards();
            $_CardTransactions = new CardTransactions();
            $_MemberInfo = new MemberInfo();
            $_Sites = new Sites();
            $_Ref_city = new Cities();
            $_CouponBatches = new CouponBatches();
            $_RaffleCoupons = new RaffleCoupons();
            $_PcwsWrapper = new PcwsWrapper();

            $logger = new ErrorLogger();
            $logdate = $logger->logdate;
            $logtype = "Error ";

            $functionname = $_POST["functiontype"];
            if($functionname == "RewardOfferList"){
                                $response = null;
                                $page = $_POST['page'];
                                $limit = $_POST['rows'];

                                $rewardoffers = $_RewardItems->getAllRewardOffersBasedOnPlayerClassification($_SESSION['CardRed']['IsVIP'],"Points");
                                if(count($rewardoffers) > 0){

                                    $total_pages = ceil(count($rewardoffers)/$limit);
                                    if ($page > $total_pages) {
                                        $page = $total_pages;
                                    }

                                    $ctr = 0;
                                    $response->page = $page;
                                    $response->total = $total_pages;
                                    $response->records = count($rewardoffers);
                                    $EmailAddress = $_SESSION['CardRed']['Email'];
                                    for ($itr=0;$itr < count($rewardoffers); $itr++) {
                                        preg_match('/\((.*?)\)/', $rewardoffers[$itr]["ProductName"], $rewardname);
                                        if(is_array($rewardname) && isset($rewardname[1])){
                                            unset($rewardoffers[$itr]["ProductName"]);
                                            $rewardoffers[$itr]["ProductName"] = $rewardname[1];
                                        }

                                        if($rewardoffers[$itr]['IsMystery'] == 1 && $rewardoffers[$itr]['AvailableItemCount'] > 0){ 
                                            $ProductName =  $rewardoffers[$itr]['MysteryName']; 
                                            $Description = $rewardoffers[$itr]['MysterySubtext']; 
                                        } else { 
                                            $ProductName =  $rewardoffers[$itr]['ProductName']; 
                                            $Description =  $rewardoffers[$itr]['Description']; 
                                        }
                                        $RewardItemID = $rewardoffers[$itr]["RewardItemID"];
                                        $RewardID = $rewardoffers[$itr]["RewardID"];
                                        $RequiredPoints = $rewardoffers[$itr]["Points"];
                                        $eCouponImage = $rewardoffers[$itr]["ECouponImage"];
                                        $PartnerName = $rewardoffers[$itr]["PartnerName"];
                                        $availableitemcount = $rewardoffers[$itr]["AvailableItemCount"];
                                        $IsMystery= $rewardoffers[$itr]['IsMystery'];
                                        $enabled = "";                
//                                        $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($_SESSION['CardRed']['CardNumber']);
                                        $CurrentPoints = $_PcwsWrapper->getCompPoints($_SESSION['CardRed']['CardNumber'], 0);
                                        $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                                        if( $CurrentPoints < $RequiredPoints){
                                            $rewardoffers[$itr]["Action"] = "<input type='button' value='Redeem' id='csredeem-button' disabled='disabled' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardID='$RewardID' RequiredPoints='$RequiredPoints' eCouponImage='$eCouponImage' PartnerName='$PartnerName' >";
                                        } else {
                                            //Check if the Item/Coupons available item count is greater than zero.
                                                if((int)$availableitemcount['AvailableItemCount'] > 0){
                                                        if($RewardID == 2 || $RewardID == "2"){
                                                            //Set Table for raffle coupon based on active coupon batch.
                                                            $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
                                                            if(isset($getRaffleCouponSuffix[0]) && $getRaffleCouponSuffix[0]['CouponBatchID'] != ""){
                                                                $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
                                                                $rewardoffers[$itr]["Action"] = "<input type='button' value='Redeem' id='csredeem-button' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardID='$RewardID' RequiredPoints='$RequiredPoints' eCouponImage='$eCouponImage' PartnerName='$PartnerName' IsMystery = '$IsMystery' >";
                                                            } else {
                                                                $rewardoffers[$itr]["Action"] = "<input type='button' disabled value='Redeem' id='csredeem-button' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardID='$RewardID' RequiredPoints='$RequiredPoints' eCouponImage='$eCouponImage' PartnerName='$PartnerName' IsMystery = '$IsMystery' >";
                                                            }
                                                        } else {
                                                            $rewardoffers[$itr]["Action"] = "<input type='button' value='Redeem' id='csredeem-button' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardID='$RewardID' RequiredPoints='$RequiredPoints' eCouponImage='$eCouponImage' PartnerName='$PartnerName' IsMystery = '$IsMystery' >";
                                                        }
                                                } else {
                                                    $rewardoffers[$itr]["Action"] = "<input type='button' value='Redeem' id='csredeem-button' disabled='disabled' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardID='$RewardID' RequiredPoints='$RequiredPoints' eCouponImage='$eCouponImage' PartnerName='$PartnerName' IsMystery = '$IsMystery' >";
                                                }
                                        }

                                        $response->rows[$ctr]['id'] = $rewardoffers[$itr]["RewardItemID"];
                                        $response->rows[$itr]['cell'] = array(
                                                                                                    $ProductName,
                                                                                                    $rewardoffers[$itr]["Points"],
                                                                                                    $Description,
                                                                                                    $rewardoffers[$itr]["PromoName"],
                                                                                                    $rewardoffers[$itr]["Action"]
                                                                                                );
                                        $ctr++;
                                    }
                                } else {
                                    $ctr = 0;
                                    $response->page = 0;
                                    $response->total = 0;
                                    $response->records = 0;
                                    $msg = "Record is Empty.";
                                    $response->msg = $msg;
                                }

            } else if( $functionname == "CardDetails"){
                                if(isset($_POST["datavar"]) && $_POST["datavar"] != ""){
                                        unset($_SESSION["CardRed"]);
                                        $response = null;
                                        $validate = new Validation();
                                        $searchValue = $_POST["datavar"];
                                        if ($validate->validateEmail($searchValue))
                                        {
                                            $result = $_MemberInfo->getMemberInfoByUsernameSP($searchValue);
                                            if (count($result) > 0)
                                            {
                                                $_SESSION['CardRed']['Username'] = $searchValue;
                                                $MID = $result[0]['MID'];
                                                $_SESSION['CardRed']['Email'] = $result[0]['Email'];
                                                $cardInfo = $_MemberCards->getMemberCardInfoRedemption($MID);
                                                $CardNumber = $cardInfo[0]['CardNumber'];
                                                $_SESSION['CardRed']['CardNumber'] = $CardNumber;
                                                $_SESSION['CardRed']['MID'] = $MID;
                                                $_SESSION['CardRed']['CardTypeID'] = $cardInfo[0]['CardTypeID'];
                                                $_SESSION['CardRed']['IsVIP'] = $result[0]['IsVIP'];
                                                //check if region and city are valid
                                                if(isset($result[0]["CityID"]) && $result[0]["CityID"] != "" && isset($result[0]["RegionID"]) && $result[0]["RegionID"] != ""){
                                                    $validCityAndRegion = $_Ref_city->checkCitiesAndRegionsValidity($result[0]["RegionID"], $result[0]["CityID"]);
                                                } else { $validCityAndRegion = ""; }
                                                
                                                if($validCityAndRegion != ""){
                                                    $response["CityID"] = $result[0]["CityID"];
                                                    $response["RegionID"] = $result[0]["RegionID"];
                                                } else {
                                                    $response["RegionID"] = $result[0]["RegionID"];
                                                    $response["CityID"] = "";
                                                }
                                                
                                                $CurrentPoints = $_PcwsWrapper->getCompPoints($CardNumber, 0);
                                                $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                                                
                                                //get player card details
                                                $response["FirstName"] = $result[0]["FirstName"];
                                                $response["LastName"] = $result[0]["LastName"];
                                                $response["Address1"] = $result[0]["Address1"];
                                                $response["MobileNumber"] = $result[0]["MobileNumber"];
                                                $response["Email"] = $result[0]["Email"];
                                                $response["Birthdate"] = $result[0]["Birthdate"];
                                                $response["MemberInfoID"] = $result[0]["MemberInfoID"];
                                                $_SESSION['CardRed']['MemberInfoID'] = $response["MemberInfoID"];
                                                
                                                $response["Error"] = "";
                                                $response["CardNumber"] = $CardNumber;
                                                $response["CardType"] = $cardInfo[0]['CardType'];
                                                //$response["CurrentPoints"] = number_format($cardInfo[0]['CurrentPoints'], 0, '', ',');
                                                $response["CurrentPoints"] = number_format($CurrentPoints, 0, '', ',');
                                                $response["LifetimePoints"] = number_format($cardInfo[0]['LifetimePoints'], 0, '', ',');
                                                //$response["BonusPoints"] = number_format($cardInfo[0]['BonusPoints'], 0, '', ',');
                                                $response["BonusPoints"] = number_format(0, 0, '', ',');
                                                $response["RedeemedPoints"] = number_format($cardInfo[0]['RedeemedPoints'], 0, '', ',');
                                                $response["CardTypeID"] = $cardInfo[0]['CardTypeID'];
                                                $response["IsVIP"] = $result[0]['IsVIP'];
                                                $response["Status"] = $cardInfo[0]['Status'];
                                                
                                                switch ($response["Status"]) {
                                                    case 0:
                                                        $response["StatusMsg"] = "Card is Inactive.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 2:
                                                        $response["StatusMsg"] = "Card is Deactivated.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 7:
                                                        $response["StatusMsg"] = "Red Card is already Migrated.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 8:
                                                        $response["StatusMsg"] = "Temporary Card is Migrated.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 9:
                                                        $response["StatusMsg"] = "Card is Banned.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                }

                                                $arrTransactions = $_CardTransactions->getLastTransaction($CardNumber);
                                                if(count($arrTransactions) > 0){
                                                    $site = $_Sites->getSite($arrTransactions[0]['SiteID']);
                                                    $siteName = $site[0]['SiteName'];
                                                    $transDate = date('M d, Y ', strtotime($arrTransactions[0]['TransactionDate']));
                                                } else {
                                                    $siteName = "";
                                                    $transDate = "";
                                                }

                                                $response["LastSitePlayed"] = $siteName;
                                                $response["LastTransactionDate"] = $transDate;
                                            }
                                            else
                                            {
                                                $response["Error"] = "Username not found";
                                                $logger->logger($logdate, $logtype, $response["Error"]);
                                            }
                                        }
                                        else
                                        {
                                            $membercards = $_MemberCards->getMemberCardInfoByCard($searchValue);
                                            if (count($membercards) > 0)
                                            {
                                                $MID = $membercards[0]['MID'];
                                                $_SESSION['CardRed']['CardNumber'] = $searchValue;
                                                $_SESSION['CardRed']['MID'] = $MID;
                                                $_SESSION['CardRed']['CardTypeID'] = $membercards[0]['CardTypeID'];
                                                $_SESSION['CardRed']['IsVIP'] = $membercards[0]['IsVIP'];
                                                $CardNumber = $searchValue;
                                                $email = $_MemberInfo->getEmailSP($MID);
                                                $_SESSION['CardRed']['Email'] = $email;
                                                switch ($membercards[0]['CardTypeID']) {
                                                    case 1:
                                                        $CardType = "Gold";
                                                        break;
                                                    case 2:
                                                        $CardType = "Green";
                                                        break;
                                                    case 3:
                                                        $CardType = "Temp";
                                                        break;
                                                }
                                                
                                                $memberinfo = $_MemberInfo->getMemInfoUsingSP($_SESSION["CardRed"]["MID"]);
                                                $ArrMemberInfo = $memberinfo;
                                                
                                                //check if region and city are valid
                                                if(isset($ArrMemberInfo["CityID"]) && $ArrMemberInfo["CityID"] != "" && isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != ""){
                                                    $validCityAndRegion = $_Ref_city->checkCitiesAndRegionsValidity($ArrMemberInfo["RegionID"], $ArrMemberInfo["CityID"]);
                                                } else { $validCityAndRegion = ""; }
                                                
                                                if($validCityAndRegion != ""){
                                                    $response["CityID"] = $ArrMemberInfo["CityID"];
                                                    $response["RegionID"] = $ArrMemberInfo["RegionID"];
                                                } else {
                                                    $response["CityID"] = "";
                                                    $response["RegionID"] = $ArrMemberInfo["RegionID"];
                                                }
                                                
                                                $CurrentPoints = $_PcwsWrapper->getCompPoints($CardNumber, 0);
                                                $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                                                
                                                //get player card details
                                                $response["FirstName"] = $ArrMemberInfo["FirstName"];
                                                $response["LastName"] = $ArrMemberInfo["LastName"];
                                                $response["Address1"] = $ArrMemberInfo["Address1"];
                                                $response["MobileNumber"] = $ArrMemberInfo["MobileNumber"];
                                                $response["Email"] = $ArrMemberInfo["Email"];
                                                $response["Birthdate"] = $ArrMemberInfo["Birthdate"];
                                                $response["MemberInfoID"] = $ArrMemberInfo["MemberInfoID"];
                                                $_SESSION['CardRed']['MemberInfoID'] = $response["MemberInfoID"];

                                                $response["Error"] = "";
                                                $response["CardNumber"] = $CardNumber;
                                                $response["CardType"] = $CardType;
                                                
                                                //$response["CurrentPoints"] = number_format($membercards[0]['CurrentPoints'], 0, '', ',');
                                                $response["CurrentPoints"] = number_format($CurrentPoints, 0, '', ',');
                                                $response["LifetimePoints"] = number_format($membercards[0]['LifetimePoints'], 0, '', ',');
                                                //$response["BonusPoints"] = number_format($membercards[0]['BonusPoints'], 0, '', ',');
                                                $response["BonusPoints"] = number_format(0, 0, '', ',');
                                                $response["RedeemedPoints"] = number_format($membercards[0]['RedeemedPoints'], 0, '', ',');
                                                $response["CardTypeID"] = $membercards[0]['CardTypeID'];
                                                $response["IsVIP"] = $membercards[0]['IsVIP'];
                                                $response["Status"] = $membercards[0]['Status'];
                                                
                                                switch ($response["Status"]) {
                                                    case 0:
                                                        $response["StatusMsg"] = "Card is Inactive.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 2:
                                                        $response["StatusMsg"] = "Card is Deactivated.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 7:
                                                        $response["StatusMsg"] = "Red Card is  already Migrated.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 8:
                                                        $response["StatusMsg"] = "Temporary Card is Migrated.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                    case 9:
                                                        $response["StatusMsg"] = "Card is Banned.";
                                                        unset($_SESSION['CardRed']);
                                                        break;
                                                }

                                                $arrTransactions = $_CardTransactions->getLastTransaction($CardNumber);
                                                if(count($arrTransactions) > 0){
                                                    $site = $_Sites->getSite($arrTransactions[0]['SiteID']);
                                                    $siteName = $site[0]['SiteName'];
                                                    $transDate = date('M d, Y ', strtotime($arrTransactions[0]['TransactionDate']));
                                                } else {
                                                    $siteName = "";
                                                    $transDate = "";
                                                }

                                                $response["LastSitePlayed"] = $siteName;
                                                $response["LastTransactionDate"] = $transDate;
                                            }
                                            else
                                            {
                                                $response["Error"] = "Invalid Card Number";
                                                $logger->logger($logdate, $logtype, $response["Error"]);
                                            }
                                        }
                                }
            } elseif ($functionname == "GetCities") {
                if(isset($_POST["regionid"]) && $_POST["regionid"] != ""){
                    $regionid = $_POST["regionid"];
                    $listofcities = $_Ref_city->getCitiesUsingRegionID($regionid);
                    $response["ListOfCities"] = $listofcities;
                    $response["CountOfCities"] = count($listofcities);
                }
            } elseif($functionname == "CheckCouponAvailibility"){
                //Set Table for raffle coupon based on active coupon batch.
                $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
                if(isset($getRaffleCouponSuffix[0]) && $getRaffleCouponSuffix[0]['CouponBatchID'] != ""){
                    $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
                    $response["IsAvailableCouponBatchID"] = 1; //Yes
                } else {
                    $response["IsAvailableCouponBatchID"] = 0; //No
                }
            }
            
        echo  json_encode($response);
        exit;
}

?>
