<?php

/**
 * @Description: Fetching and encoding data into JSON array to be displayed in JQGRID for list of all reward offers available based in player card type.
 *@Author: aqdepliyan
 * @DateCreated: 07-16-2013 02:46 PM
 */

if(isset($_POST["functiontype"]) && $_POST["functiontype"] != ""){

            //Attach and Initialize framework
            require_once("../../init.inc.php");

            //Load modules needed
            App::LoadModuleClass("Loyalty", "RewardOffers");
            App::LoadModuleClass('Loyalty', 'RewardItems');
            App::LoadModuleClass('Membership', 'MemberInfo');
            App::LoadModuleClass('Membership', 'Cities');
            App::LoadModuleClass("Loyalty", "CouponBatches");
            App::LoadModuleClass('Loyalty', 'RaffleCoupons');
            App::LoadModuleClass('Loyalty', 'MemberCards');
            App::LoadModuleClass('Loyalty', 'Cards');
            App::LoadModuleClass('Loyalty', 'CardTransactions');
            App::LoadModuleClass('Kronus', 'Sites');

            App::LoadCore('Validation.class.php');
            App::LoadCore('ErrorLogger.php');

            //Initialize Modules
            $_RewardOffers = new RewardOffers();
            $_MemberCards = new MemberCards();
            $_CardTransactions = new CardTransactions();
            $_MemberInfo = new MemberInfo();
            $_Sites = new Sites();
            $_Ref_city = new Cities();
            $_CouponBatches = new CouponBatches();
            $_RaffleCoupons = new RaffleCoupons();

            $logger = new ErrorLogger();
            $logdate = $logger->logdate;
            $logtype = "Error ";

            $functionname = $_POST["functiontype"];
            if($functionname == "RewardOfferList"){
                                $response = null;
                                $page = $_POST['page'];
                                $limit = $_POST['rows'];

                                $rewardoffers = $_RewardOffers->getAllRewardOffers($_SESSION['CardRed']['CardTypeID'],"Points");
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
                                        $ProductName = $rewardoffers[$itr]["ProductName"];
                                        $RewardItemID = $rewardoffers[$itr]["RewardItemID"];
                                        $RewardOfferID = $rewardoffers[$itr]["RewardOfferID"];
                                        $IsCoupon = $rewardoffers[$itr]["IsCoupon"];
                                        $RequiredPoints = $rewardoffers[$itr]["Points"];
                                        $enabled = "";                     
                                        $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($_SESSION['CardRed']['CardNumber']);
                                        if( $CurrentPoints < $RequiredPoints){
                                            $rewardoffers[$itr]["Action"] = "<input type='button' value='Redeem' id='csredeem-button' disabled='disabled' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardOfferID='$RewardOfferID' IsCoupon='$IsCoupon' RequiredPoints='$RequiredPoints' >";
                                        } else {
                                            
                                            if($IsCoupon == 1 || $IsCoupon == "1"){
                                                //Set Table for raffle coupon based on active coupon batch.
                                                $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
                                                if(isset($getRaffleCouponSuffix[0]) && $getRaffleCouponSuffix[0]['CouponBatchID'] != ""){
                                                    $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
                                                    $rewardoffers[$itr]["Action"] = "<input type='button' value='Redeem' id='csredeem-button' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardOfferID='$RewardOfferID' IsCoupon='$IsCoupon' RequiredPoints='$RequiredPoints' >";
                                                } else {
                                                    $rewardoffers[$itr]["Action"] = "<input type='button' disabled value='Redeem' id='csredeem-button' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardOfferID='$RewardOfferID' IsCoupon='$IsCoupon' RequiredPoints='$RequiredPoints' >";
                                                }
                                            } else {
                                                $rewardoffers[$itr]["Action"] = "<input type='button' value='Redeem' id='csredeem-button' Email = '$EmailAddress' ProductName='$ProductName' RewardItemID='$RewardItemID' RewardOfferID='$RewardOfferID' IsCoupon='$IsCoupon' RequiredPoints='$RequiredPoints' >";
                                            }
                                        }

                                        $response->rows[$ctr]['id'] = $rewardoffers[$itr]["RewardOfferID"];
                                        $response->rows[$itr]['cell'] = array(
                                                                                                    $rewardoffers[$itr]["ProductName"],
                                                                                                    $rewardoffers[$itr]["Points"],
                                                                                                    $rewardoffers[$itr]["Description"],
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
                                            $result = $_MemberInfo->getMemberInfoByUsername($searchValue);

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
                                                $response["CurrentPoints"] = $cardInfo[0]['CurrentPoints'];
                                                $response["LifetimePoints"] = $cardInfo[0]['LifetimePoints'];
                                                $response["BonusPoints"] = $cardInfo[0]['BonusPoints'];
                                                $response["RedeemedPoints"] = $cardInfo[0]['RedeemedPoints'];
                                                $response["CardTypeID"] = $cardInfo[0]['CardTypeID'];
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
                                                    case 8:
                                                        $response["StatusMsg"] = "Card is Migrated.";
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
                                                $CardNumber = $searchValue;
                                                $email = $_MemberInfo->getEmail($MID);
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
                                                
                                                $memberinfo = $_MemberInfo->getMemberInfo($_SESSION["CardRed"]["MID"]);
                                                $ArrMemberInfo = $memberinfo[0];
                                                
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
                                                $response["CurrentPoints"] = $membercards[0]['CurrentPoints'];
                                                $response["LifetimePoints"] = $membercards[0]['LifetimePoints'];
                                                $response["BonusPoints"] = $membercards[0]['BonusPoints'];
                                                $response["RedeemedPoints"] = $membercards[0]['RedeemedPoints'];
                                                $response["CardTypeID"] = $membercards[0]['CardTypeID'];
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
                                                    case 8:
                                                        $response["StatusMsg"] = "Card is Migrated.";
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
