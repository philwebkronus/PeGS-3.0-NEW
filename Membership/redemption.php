<?php

/**
 * @Description: For Player Redemption
 * @Author: aqdepliyan
 * @DateCreated: 2013-07-10
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("init.inc.php");
include 'sessionmanager.php';

if(isset($_SESSION['RewardItemsInfo'])){
    
    $pagetitle = "Redemption";

    $useCustomHeader = true;
    $showcouponredemptionwindow = false;
    $sendemailtoadmin = false;
    $emailmessage = "";
    $site = "";
    $arrmemberinfo = "";
    $MID = $_SESSION["MemberInfo"]["Member"]["MID"];

    /* Used to determine min and max dates for birthdate field */
    $dsmaxdate = new DateSelector();
    $dsmindate = new DateSelector();
    $dsmaxdate->AddYears(-21);
    $dsmindate->AddYears(-100);

    App::LoadCore("URL.class.php");
    App::LoadCore("Hashing.class.php");
    App::LoadCore("Validation.class.php");
    App::LoadCore("File.class.php");
    App::LoadCore("PHPMailer.class.php");

    App::LoadModuleClass("Membership", "MemberInfo");
    App::LoadModuleClass("Membership", "AuditTrail");
    App::LoadModuleClass("Membership", "AuditFunctions");
    App::LoadModuleClass('Membership', 'Cities');
    App::LoadModuleClass('Membership', 'Regions');
    App::LoadModuleClass('Membership', 'Helper');
    App::LoadModuleClass('Membership', 'MembershipSmsAPI');

    App::LoadModuleClass("Rewards", "CouponBatches");
    App::LoadModuleClass("Loyalty", "MemberCards");
    App::LoadModuleClass("Loyalty", "Cards");
    App::LoadModuleClass("Loyalty", "CardTypes");
    App::LoadModuleClass("Loyalty", "Rewards");
    App::LoadModuleClass("Loyalty", "CardTransactions");
    App::LoadModuleClass('Rewards', 'RewardItems');
    App::LoadModuleClass('Rewards', 'CouponRedemptionLogs');
    App::LoadModuleClass('Rewards', 'RaffleCoupons');
    App::LoadModuleClass("Rewards", "ItemRedemptionLogs");
    App::LoadModuleClass("Rewards", "RedemptionProcess");
    App::LoadModuleClass("Rewards", "PendingRedemption");
    App::LoadModuleClass("Rewards", "SMSRequestLogs");
    App::LoadModuleClass("Rewards", "ItemSerialCodes");
    App::LoadModuleClass("Rewards", "Partners");

    App::LoadModuleClass("Kronus", "Sites");

    App::LoadControl("DatePicker");
    App::LoadControl("TextBox");
    App::LoadControl("DataGrid");
    App::LoadControl("ComboBox");
    App::LoadControl("Button");
    App::LoadControl("RadioGroup");
    App::LoadControl("Radio");
    App::LoadControl("CheckBox");
    App::LoadControl("Hidden");
    
    //Initialize Modules
    $_CouponBatches = new CouponBatches();
    $_AuditTrail = new AuditTrail();
    $_ItemRedemptionLogs = new ItemRedemptionLogs();
    $_CouponRedemptionLogs = new CouponRedemptionLogs();
    $_RaffleCoupons = new RaffleCoupons();
    $_RewardItems = new RewardItems();
    $_MemberCards = new MemberCards();
    $_MemberInfo = new MemberInfo();
    $_Ref_city = new Cities();
    $_Ref_region = new Regions();
    $_Helper = new Helper();
    $_PendingRedemption = new PendingRedemption();
    $_SMSRequestLogs = new SMSRequestLogs();
    $_ItemSerialCodes = new ItemSerialCodes();
    $_Partners = new Partners();
    $_RedemptionProcess = new RedemptionProcess();
    
    //Check if the coupon batch is active, if not display error message.
    if($_SESSION['RewardItemsInfo']['RewardID'] == 2 || $_SESSION['RewardItemsInfo']['RewardID'] == "2"){
        //Set Table for raffle coupon based on active coupon batch.
        $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
        if(isset($getRaffleCouponSuffix[0]) && $getRaffleCouponSuffix[0]['CouponBatchID'] != ""){
            $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
            $IsAvailableCouponBatchID = 1; //Yes
        } else {
            $IsAvailableCouponBatchID = 0; //No
            App::SetErrorMessage("Raffle Coupons are unavailable.");
        }
    }
    
    //for loading reward item details
    $partnersd = $_Partners->getPartnerDetailsUsingPartnerName($_SESSION['RewardItemsInfo']['PartnerName']);
    $rewarditemdetails = $_RewardItems->getAboutandTerms($_SESSION['RewardItemsInfo']['RewardItemID']);
    $mysterydetails = $_RewardItems->getMysteryDetails($_SESSION['RewardItemsInfo']['RewardItemID']);
    
    if($_SESSION['RewardItemsInfo']['IsMystery'] == 1 && (isset($rewarditemdetails["AvailableItemCount"]) && $rewarditemdetails["AvailableItemCount"] > 0)){
        $ProductName =  $mysterydetails["MysteryName"];
    } else {
        $ProductName =   $_SESSION['RewardItemsInfo']['ProductName'];
    }

    $fproc = new FormsProcessor();
    
    $btnRedeemButton = new Button("redeem-button", "redeem-button", "REDEEM NOW");
    $btnRedeemButton->CssClass = "yellow-btn-redeem-button";
    
    //Get Player's Current Points
    $results = $_MemberCards->getCurrentPointsByMID($MID);
    $PlayerPoints = $results[0]['CurrentPoints'];
    
    $availableitemcount = $_RewardItems->getAvailableItemCount($_SESSION['RewardItemsInfo']['RewardItemID']);
    $CurrentStatus = $_RewardItems->CheckStatus($_SESSION['RewardItemsInfo']['RewardItemID']);
    //If Player Points is less than the Reward Item Points disabled the redeem button
    //If not, whether coupon or item. For coupon check if the coupon batch is active, if not disabled the redeem button.
    if($CurrentStatus["Status"] == "Active"){
        if($PlayerPoints < $_SESSION['RewardItemsInfo']['Points']){
            $btnRedeemButton->Enabled = false;
        } else {
            if($_SESSION['RewardItemsInfo']['RewardID'] == 2 || $_SESSION['RewardItemsInfo']['RewardID'] == '2'){
                if($IsAvailableCouponBatchID == 0){
                    $btnRedeemButton->Enabled = false;
                } else {
                    if((int)$availableitemcount['AvailableItemCount'] <= 0){
                        $learnmoreimage = $_RewardItems->getLearnMorePageImage($_SESSION['RewardItemsInfo']['RewardItemID']);
                        unset($_SESSION['RewardItemsInfo']['LearnMoreImage']);
                        $_SESSION['RewardItemsInfo']['LearnMoreImage'] = $learnmoreimage['LearnMoreOutOfStockImage'];
                        $btnRedeemButton->Enabled = false;
                    } else {
                        $learnmoreimage = $_RewardItems->getLearnMorePageImage($_SESSION['RewardItemsInfo']['RewardItemID']);
                        unset($_SESSION['RewardItemsInfo']['LearnMoreImage']);
                        $_SESSION['RewardItemsInfo']['LearnMoreImage'] = $learnmoreimage['LearnMoreLimitedImage'];
                        $btnRedeemButton->Enabled = true;
                    }
                }
            } else {
                if((int)$availableitemcount['AvailableItemCount'] <= 0){
                    $learnmoreimage = $_RewardItems->getLearnMorePageImage($_SESSION['RewardItemsInfo']['RewardItemID']);
                    unset($_SESSION['RewardItemsInfo']['LearnMoreImage']);
                    $_SESSION['RewardItemsInfo']['LearnMoreImage'] = $learnmoreimage['LearnMoreOutOfStockImage'];
                    $btnRedeemButton->Enabled = false;
                } else {
                    $learnmoreimage = $_RewardItems->getLearnMorePageImage($_SESSION['RewardItemsInfo']['RewardItemID']);
                    unset($_SESSION['RewardItemsInfo']['LearnMoreImage']);
                    $_SESSION['RewardItemsInfo']['LearnMoreImage'] = $learnmoreimage['LearnMoreLimitedImage'];
                    $btnRedeemButton->Enabled = true;
                }
            }
        }
    } else {
        $btnRedeemButton->Enabled = false;
    }
    
    $fproc->AddControl($btnRedeemButton);
    
    $hdnCardNumber = new Hidden("CardNumber", "CardNumber", "CardNumber: ");
    $hdnCardNumber->ShowCaption = true;
    $hdnCardNumber->Text = "'";
    $fproc->AddControl($hdnCardNumber);
    
    $hdnMemberInfoID = new Hidden("MemberInfoID", "MemberInfoID", "MemberInfoID: ");
    $hdnMemberInfoID->ShowCaption = true;
    $hdnMemberInfoID->Text = "";
    $fproc->AddControl($hdnMemberInfoID);
    
    $hdnPlayerPoints = new Hidden("PlayerPoints", "PlayerPoints", "PlayerPoints: ");
    $hdnPlayerPoints->ShowCaption = true;
    $hdnPlayerPoints->Text = $PlayerPoints;
    $fproc->AddControl($hdnPlayerPoints);
    
    $hdnItemName = new Hidden("hdnItemName", "hdnItemName", "hdnItemName: ");
    $hdnItemName->ShowCaption = true;
    $hdnItemName->Text = "";
    $fproc->AddControl($hdnItemName);
    
    $hdnTotalItemPoints = new Hidden("hdnTotalItemPoints", "hdnTotalItemPoints", "hdnTotalItemPoints: ");
    $hdnTotalItemPoints->ShowCaption = true;
    $hdnTotalItemPoints->Text = "";
    $fproc->AddControl($hdnTotalItemPoints);
    
    $hdnItemPoints = new Hidden("hdnItemPoints", "hdnItemPoints", "hdnItemPoints: ");
    $hdnItemPoints->ShowCaption = true;
    $hdnItemPoints->Text = "";
    $fproc->AddControl($hdnItemPoints);
    
    $hdnProductName = new Hidden("hdnProductName", "hdnProductName", "hdnProductName: ");
    $hdnProductName->ShowCaption = true;
    $hdnProductName->Text = $ProductName;
    $fproc->AddControl($hdnProductName);
    
    $txtQuantity = new TextBox('Quantity', 'Quantity', 'Quantity ');
    $txtQuantity->ShowCaption = false;
    $txtQuantity->CssClass = 'validate[required,custom[integer],min[1]]';
    $txtQuantity->Style = 'color: #666; width: 100px;';
    $txtQuantity->Length = 5;
    $txtQuantity->Size = 5;
    $txtQuantity->Text = "";
    $txtQuantity->Args = "placeholder='0' ";
    $fproc->AddControl($txtQuantity);
    
    $txtItemQuantity = new TextBox('ItemQuantity', 'ItemQuantity', 'ItemQuantity ');
    $txtItemQuantity->ShowCaption = false;
    $txtItemQuantity->CssClass = 'validate[required,custom[integer],min[1]]';
    $txtItemQuantity->Style = 'color: #666; width: 100px;';
    $txtItemQuantity->Length = 1;
    $txtItemQuantity->Size = 1;
    $txtItemQuantity->Text = "";
    $txtItemQuantity->Args = "placeholder='0' ";
    $fproc->AddControl($txtItemQuantity);
    
    $txtRedeemFirstName = new TextBox("FirstName", "FirstName", "First Name: ");
    $txtRedeemFirstName->ShowCaption = false;
    $txtRedeemFirstName->Length = 30;
    $txtRedeemFirstName->Size = 15;
    $txtRedeemFirstName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
    $fproc->AddControl($txtRedeemFirstName);
    
    $txtRedeemLastName = new TextBox("LastName", "LastName", "Last Name: ");
    $txtRedeemLastName->ShowCaption = false;
    $txtRedeemLastName->Length = 30;
    $txtRedeemLastName->Size = 15;
    $txtRedeemLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
    $fproc->AddControl($txtRedeemLastName);
    
    $dtRedeemBirthDate = new DatePicker("Birthdate", "Birthdate", "Birth Date: ");
    $dtRedeemBirthDate->MaxDate = $dsmaxdate->CurrentDate;
    $dtRedeemBirthDate->MinDate = $dsmindate->CurrentDate;
    $dtRedeemBirthDate->ShowCaption = false;
    $dtRedeemBirthDate->YearsToDisplay = "-100";
    $dtRedeemBirthDate->CssClass = "validate[required]";
    $dtRedeemBirthDate->isRenderJQueryScript = true;
    $fproc->AddControl($dtRedeemBirthDate);
    
    $txtRedeemAddress1 = new TextBox("Address1", "Address1", "Address: ");
    $txtRedeemAddress1->ShowCaption = false;
    $txtRedeemAddress1->Length = 30;
    $txtRedeemAddress1->Size = 15;
    $txtRedeemAddress1->CssClass = "validate[required]";
    $fproc->AddControl($txtRedeemAddress1);
    
    $cboCityID = new ComboBox("CityID", "CityID", "City: ");
    $opt1[] = new ListItem("Select City", "", true);
    $cboCityID->Items = $opt1;
    $cboCityID->ShowCaption = false;
    $cboCityID->CssClass = 'validate[required]';
    $fproc->AddControl($cboCityID);
    

    $arrRef_region = $_Ref_region->SelectAll();
    $arrRef_regionList = new ArrayList($arrRef_region);
    $cboRegionID = new ComboBox("RegionID", "RegionID", "Region: ");
    $opt2[] = new ListItem("Select Region", "", true);
    $cboRegionID->Items = $opt2;
    $cboRegionID->ShowCaption = false;
    $cboRegionID->CssClass = 'validate[required]';
    $cboRegionID->DataSourceText = "RegionName";
    $cboRegionID->DataSourceValue = "RegionID";
    $cboRegionID->DataSource = $arrRef_regionList;
    $cboRegionID->DataBind();
    $fproc->AddControl($cboRegionID);
    
    $txtRedeemEmail = new TextBox("Email", "Email", "Email: ");
    $txtRedeemEmail->ShowCaption = false;
    $txtRedeemEmail->Length = 30;
    $txtRedeemEmail->Size = 15;
    $txtRedeemEmail->CssClass = "validate[required, custom[email]]";
    $fproc->AddControl($txtRedeemEmail);
    
    $txtRedeemMobileNumber = new TextBox("MobileNumber", "MobileNumber", "Mobile Number: ");
    $txtRedeemMobileNumber->ShowCaption = false;
    $txtRedeemMobileNumber->Length = 30;
    $txtRedeemMobileNumber->Size = 15;
    $txtRedeemMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
    $fproc->AddControl($txtRedeemMobileNumber);
    
    //check if player has login session.
    if(isset($_SESSION['MemberInfo'])){
        $memberinfo = $_MemberInfo->getMemberInfo($MID);
        $ArrMemberInfo = $memberinfo[0];
        
        //get player card details
        $cardinfo = $_MemberCards->getActiveMemberCardInfo($MID);
        if (!isset($cardinfo[0]['CardNumber'])) {
            unset($_SESSION['MemberInfo']);
            App::SetErrorMessage("Account Banned");
            echo "<script>parent.window.location.href='index.php';</script>";
        }
        $cardnumber = $cardinfo[0]['CardNumber'];
        $hdnCardNumber->Text = $cardnumber;
        $txtRedeemFirstName->Text = $ArrMemberInfo["FirstName"];
        $txtRedeemLastName->Text = $ArrMemberInfo["LastName"];
        $txtRedeemAddress1->Text = $ArrMemberInfo["Address1"];
        if(isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != null && $ArrMemberInfo["RegionID"] != ''){
            $cboRegionID->SetSelectedValue($ArrMemberInfo["RegionID"]);
            $arrRef_city = $_Ref_city->getCitiesUsingRegionID($ArrMemberInfo["RegionID"]);
            
            $arrRef_cityList = new ArrayList($arrRef_city);
            $cboCityID->DataSourceText = "CityName";
            $cboCityID->DataSourceValue = "CityID";
            $cboCityID->DataSource = $arrRef_cityList;
            $cboCityID->DataBind();
            if($ArrMemberInfo["CityID"] != ""){
                $cboCityID->SetSelectedValue($ArrMemberInfo["CityID"]);
            }
        } else {
            $arrRef_cityList = '';
            $opt3[] = new ListItem("Select City", "", true);
            $cboCityID->Items = $opt3;
            if($ArrMemberInfo["CityID"] != ""){
                $cboCityID->SetSelectedValue($ArrMemberInfo["CityID"]);
            }
        }
        
        
        $txtRedeemMobileNumber->Text = $ArrMemberInfo["MobileNumber"];
        $txtRedeemEmail->Text = $ArrMemberInfo["Email"];
        $dtRedeemBirthDate->SelectedDate = $ArrMemberInfo["Birthdate"];
        $hdnMemberInfoID->Text = $ArrMemberInfo["MemberInfoID"];
    }
    
    $fproc->ProcessForms();
    
    
    if($fproc->IsPostBack){
         if(!(isset($_SESSION["PreviousRedemption"])) && ($txtQuantity->SubmittedValue != "" || $txtItemQuantity->SubmittedValue != "") && $hdnItemName->SubmittedValue != "" 
                && $hdnItemPoints->SubmittedValue != "" && $hdnTotalItemPoints->SubmittedValue != "" && $hdnCardNumber->SubmittedValue != ""){

                //Get Reward Offer Coupon/Item Transaction details
                $cardinfo = $_MemberCards->getActiveMemberCardInfo($MID);
                if (!isset($cardinfo[0]['CardNumber'])) {
                    unset($_SESSION['MemberInfo']);
                    App::SetErrorMessage("Account Banned");
                    echo "<script>parent.window.location.href='index.php';</script>";
                }
                
                //Check if the coupon batch is active, if not display error message.
                if($_SESSION['RewardItemsInfo']['RewardID'] == 2 || $_SESSION['RewardItemsInfo']['RewardID'] == "2"){
                    //Set Table for raffle coupon based on active coupon batch.
                    $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
                    if(isset($getRaffleCouponSuffix[0]) && $getRaffleCouponSuffix[0]['CouponBatchID'] != ""){
                        $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
                    } else {
                        App::SetErrorMessage("Raffle Coupons are unavailable.");
                    }
                }
                //check if player has region id and city id, if not set both region id and city id to 0;
                if((isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != null && $ArrMemberInfo["RegionID"] != '' && $ArrMemberInfo["RegionID"] != 0) && (isset($ArrMemberInfo["CityID"]) && $ArrMemberInfo["CityID"] != null &&$ArrMemberInfo["CityID"] != '' && $ArrMemberInfo["CityID"] != 0)){
                    $regionname = $_Ref_region->getRegionName($ArrMemberInfo["RegionID"]);
                    $cityname = $_Ref_city->getCityName($ArrMemberInfo["CityID"]);
                } else {
                    $regionname = "";
                    $cityname = "";
                }
                
                $playername = $ArrMemberInfo["FirstName"]." ".$ArrMemberInfo["LastName"];
                $address = $ArrMemberInfo["Address1"];
                $birthdate = $ArrMemberInfo["Birthdate"];
                $email = $ArrMemberInfo["Email"];
                $sitecode = "Website";
                $contactno = $ArrMemberInfo["MobileNumber"];
                $source = 1; //0-Cashier; 1-Player
                
                //Redemption Process for both Coupon and Item.
                include("controller/RedemptionController.php");
                
                //Check if coupon or item and display appropriate reward 
                //offer transaction printable copy and send to legit player email.
                if ($showcouponredemptionwindow == true && isset($_SESSION['RewardOfferCopy']))
                {
                    $RewardItemID = $_SESSION['RewardItemsInfo']['RewardItemID'];
                    $dateRange = $_RewardItems->getOfferDateRange($RewardItemID);

                    //Set Redemption Date and Time format.
                    $rdate = new DateTime(date($_SESSION['RewardOfferCopy']["RedemptionDate"]));
                    $redemptiondate = $rdate->format("F j, Y, g:i a");

                    //Set Promo Period Date Format
                    if(isset($_SESSION['RewardOfferCopy']["CouponSeries"])){
                        $startyear = date('Y', strtotime($dateRange["StartDate"]));
                        $endyear = date('Y', strtotime($dateRange["EndDate"]));
                        if($startyear == $endyear){
                            $sdate = new DateTime(date($dateRange["StartDate"]));
                            $startdate = $sdate->format("F j");
                            $edate = new DateTime(date($dateRange["EndDate"]));
                            $enddate = $edate->format("F j, Y");
                            $promoperiod = $startdate." to ".$enddate;
                        } else {
                            $sdate = new DateTime(date($dateRange["StartDate"]));
                            $startdate = $sdate->format("F j, Y");
                            $edate = new DateTime(date($dateRange["EndDate"]));
                            $enddate = $edate->format("F j, Y");
                            $promoperiod = $startdate." to ".$enddate;
                        }
                    } else {
                        
                        if($_SESSION['RewardItemsInfo']['IsMystery'] == 1 && $dateRange["AvailableItemCount"] > 0){
                            $itemname = $dateRange['MysteryName'];
                        } else {
                            $itemname = $_SESSION['RewardItemsInfo']['ProductName'];
                        }
                        
                        $partnername = $_SESSION['RewardItemsInfo']['PartnerName'];   
                        
                        $sdate = new DateTime(date($dateRange["StartDate"]));
                        $startdate = $sdate->format("F j, Y");
                        $edate = new DateTime(date($dateRange["EndDate"]));
                        $enddate = $edate->format("F j, Y");
                        $promoperiod = $startdate." to ".$enddate;
                    }
                    

                    // For Coupon Only : Set Draw Date Format.
                    if($dateRange["DrawDate"] != '' && $dateRange["DrawDate"] != null){
                        $ddate = new DateTime(date($dateRange["DrawDate"]));
                        $drawdate = $ddate->format("F j, Y gA");
                    } else {
                        $drawdate = '';
                    }
                    
                    //Get Header, Footer and Item/Coupon Image.
                    $newheader = App::getParam('extra_imagepath')."extra_images/newheader.jpg";
                    $newfooter = App::getParam('extra_imagepath')."extra_images/newfooter.jpg";
                    $itemimage = App::getParam('rewarditem_imagepath').$_SESSION['RewardItemsInfo']['eCouponImage'];
                    $importantreminder = App::getParam('extra_imagepath')."important_reminders.jpg";
                    
                    //Get About the Reward Description and its terms and condition
                    $rewarddetails = $_RewardItems->getAboutandTerms($_SESSION['RewardItemsInfo']['RewardItemID']);
                    if(isset($rewarddetails['About'])){
                        $about = $rewarddetails['About'];
                        $term = $rewarddetails['Terms'];
                        $promoname = $rewarddetails['PromoName'];
                        $promocode = $rewarddetails['PromoCode'];
                    } else {
                        $about = '';
                        $term = '';
                        $promoname = '';
                        $promocode = '';
                    }
                    
                    //Format Reward Item Copy for email and popup window.
                    if(!isset($_SESSION['RewardOfferCopy']["CouponSeries"])){
                        
                        //Get Partner Details
                        $partnersd = $_Partners->getPartnerDetailsUsingPartnerName($partnername);
                        if(isset($partnersd[0])){
                            $companyaddress = $partnersd[0]['CompanyAddress'];
                            $companyphone = $partnersd[0]['CompanyPhone'];
                            $companywebsite = $partnersd[0]['CompanyWebsite'];
                        } else {
                            $companyaddress = '';
                            $companyphone = '';
                            $companywebsite = '';
                        }

                        $ctr = count($_SESSION['RewardOfferCopy']["SerialNumber"]);
           
                        for($itr=0; $itr < $ctr; $itr++){
                            $_Helper->sendEmailItemRedemption($email, $newheader, $itemimage, $itemname, $partnername,$playername,$cardnumber,$redemptiondate,
                                                                                                $_SESSION['RewardOfferCopy']["SerialNumber"][$itr],$_SESSION['RewardOfferCopy']["SecurityCode"][$itr],$_SESSION['RewardOfferCopy']['ValidUntil'][$itr],
                                                                                                $companyaddress,$companyphone, $companywebsite, $importantreminder,$about, $term, $newfooter);
                            
                            if($_SESSION['RewardItemsInfo']['IsMystery'] == 1){
                                $rdate = new DateTime(date($_SESSION['RewardOfferCopy']["RedemptionDate"]));
                                $redeemeddate = $rdate->format("m-d-Y");
                                $redeemedtime = $rdate->format("G:i A");
                                $sender = App::getParam('MarketingEmail');
                                if($_SESSION["MemberInfo"]["IsVIP"] == 0){
                                    $statusvalue = "Regular";
                                } else {
                                    $statusvalue = "VIP";
                                }
                                $modeofredemption = "online";
                                $_Helper->sendMysteryRewardEmail($redeemeddate, $redeemedtime, $_SESSION['RewardOfferCopy']["SerialNumber"][$itr], $_SESSION['RewardOfferCopy']["SecurityCode"][$itr], 
                                                                                                            $dateRange['MysteryName'], $_SESSION['RewardItemsInfo']['ProductName'], $cardnumber, $playername, 
                                                                                                            $statusvalue, $modeofredemption, $sender);
                            }
                        }
                        
                    } else {
                        $fbirthdate = date("F j, Y", strtotime($birthdate));
                        App::LoadCore("File.class.php");
                        $filename = dirname(__FILE__) . "/admin/template/couponredemptiontemplate.php";
                        $fp = new File($filename);
                        $emailmessage = $fp->ReadToEnd();
                        $emailmessage = str_replace('$playername', $playername, $emailmessage);
                        $emailmessage = str_replace('$address', $address, $emailmessage);
                        $emailmessage = str_replace('$sitecode', $sitecode, $emailmessage);
                        $emailmessage = str_replace('$cardno', $cardnumber, $emailmessage);
                        $emailmessage = str_replace('$birthdate', $fbirthdate, $emailmessage);
                        $emailmessage = str_replace('$email', $email, $emailmessage);
                        $emailmessage = str_replace('$contactno', $contactno, $emailmessage);
                        $emailmessage = str_replace('$actualcity', $cityname, $emailmessage);
                        $emailmessage = str_replace('$actualregion', $regionname, $emailmessage);
                        $emailmessage = str_replace('$newheader', $newheader, $emailmessage);
                        $emailmessage = str_replace('$newfooter', $newfooter, $emailmessage);
                        $emailmessage = str_replace('$couponimage', $itemimage, $emailmessage);
                        $emailmessage = str_replace('$couponseries', $_SESSION['RewardOfferCopy']["CouponSeries"], $emailmessage);
                        $emailmessage = str_replace('$quantity', $_SESSION['RewardOfferCopy']["Quantity"], $emailmessage);
                        $emailmessage = str_replace('$checksum', $_SESSION['RewardOfferCopy']["CheckSum"], $emailmessage);
                        $emailmessage = str_replace('$serialcode', $_SESSION['RewardOfferCopy']["SerialNumber"], $emailmessage);
                        $emailmessage = str_replace('$redemptiondate', $redemptiondate, $emailmessage);
                        $emailmessage = str_replace('$promocode', $promocode, $emailmessage);
                        $emailmessage = str_replace('$promoname', $promoname, $emailmessage);
                        $emailmessage = str_replace('$promoperiod', $promoperiod, $emailmessage);
                        $emailmessage = str_replace('$drawdate', $drawdate, $emailmessage);
                        $emailmessage = str_replace('$about', $about, $emailmessage);
                        $emailmessage = str_replace('$term', $term, $emailmessage);
                        
                        $_Helper->sendEmailCouponRedemption($playername,$address,$sitecode,$cardnumber,$fbirthdate,$email,$contactno,$cityname,
                                                                                        $regionname,$newheader,$newfooter,$itemimage,$_SESSION['RewardOfferCopy']["CouponSeries"],
                                                                                        $_SESSION['RewardOfferCopy']["Quantity"],$_SESSION['RewardOfferCopy']["CheckSum"],
                                                                                        $_SESSION['RewardOfferCopy']["SerialNumber"],$redemptiondate,$promocode,
                                                                                        $promoname,$promoperiod,$drawdate, $about, $term);
                        
                        unset($_SESSION['RewardOfferCopy']);
                    }
                }
        }
    }
    
    ?>
    <?php include "header.php"; ?> 
    <script type="text/javascript">
        $(document).ready(function(){
                $("#profileupdate").validationEngine();
                var localhost = location.host;
                <?php
                if ($showcouponredemptionwindow == true && $_SESSION['RewardItemsInfo']['RewardID'] == 2)
                {
                    ?>             
                                if ($("#couponmessagebody").dialog( "isOpen" ) !== true){
                                    $("#couponmessagebody").dialog({
                                        modal: true,
                                        buttons: {
                                            "Print" : function() {
                                                $("#Quantity").val("");
                                                $("#ItemQuantity").val("");
                                                var mywindow = window.open('http://'+localhost+'membership.rewards/admin/template/couponredemptiontemplate.php');
                                                mywindow.document.write('</head><body >');
                                                mywindow.document.write($("#couponmessagebody").html());
                                                mywindow.document.write('</body></html>');
                                                mywindow.print();
                                                mywindow.close();
                                                window.location="profile.php";
                                            },
                                            "Close": function() {
                                                $("#Quantity").val("");
                                                $("#ItemQuantity").val("");
                                                $(this).dialog("close");
                                                 window.location="profile.php";
                                            }
                                        },
                                        open: function(event, ui) {
                                            $("#frmRedemption").hide();
                                        },
                                        close: function(event, ui) {
                                            $("#frmRedemption").show();
                                             window.location="profile.php";
                                        },
                                        width: 1100,
                                        title: "Redemption Successful"
                                    });
                                }
                <?php } else if ($showcouponredemptionwindow == true && $_SESSION['RewardItemsInfo']['RewardID'] == 1) { ?>
                                if ($("#itemmessagebody").dialog( "isOpen" ) !== true) {
                                    $("#itemmessagebody").dialog({
                                        modal: true,
                                        buttons: {
                                            "Print" : function() {
                                                $("#Quantity").val("");
                                                $("#ItemQuantity").val("");
                                                var mywindow = window.open('http://'+localhost+'membership.rewards/admin/template/admin/template/itemredemptiontemplate.php');
                                                mywindow.document.write('</head><body >');
                                                mywindow.document.write($("#itemmessagebody").html());
                                                mywindow.document.write('</body></html>');
                                                mywindow.print();
                                                mywindow.close();
                                                window.location="profile.php";
                                            },
                                            "Close": function() {
                                                $("#Quantity").val("");
                                                $("#ItemQuantity").val("");
                                                $(this).dialog("close");
                                                window.location="profile.php";
                                            }
                                        },
                                        open: function(event, ui) {
                                            $("#frmRedemption").hide();
                                        },
                                        close: function(event, ui) {
                                            $("#frmRedemption").show();
                                             window.location="profile.php";
                                        },
                                        width: 1100,
                                        title: "Redemption Successful"
                                    });
                                }
                <?php } ?>
            
                //for restricting txtbox input for quantity
                jQuery.fn.ForceNumericOnly = function() {
                    return this.each(function() {
                        $(this).keydown(function(e) {
                            var key = e.charCode || e.keyCode || 0;
                            // allow backspace, tab, delete, arrows, numbers and keypad numbers ONLY
                            // home, end, period, and numpad decimal
                            return (
                            key == 8 || 
                                key == 9 ||
                                key == 46 ||
                                key == 110 ||
                                key == 190 ||
                                (key >= 35 && key <= 40) ||
                                (key >= 48 && key <= 57) ||
                                (key >= 96 && key <= 105));
                        });
                    });
                };
                
                //validates input: accept numbers only
                function numberonly(evt)
                {
                    var charCode = (evt.which) ? evt.which : evt.keyCode;
                    if (charCode > 31 && (charCode < 48 || charCode > 57))
                          return false;
                    else if(charCode == 9)
                      return true;
                    else
                      return true;
                }
                
                //validates input: accept numbers only ranging from 0-5
                function numberonlyonetofive(evt)
                {
                    var charCode = (evt.which) ? evt.which : evt.keyCode;
                    if (charCode > 31 && (charCode < 48 || charCode > 53))
                          return false;
                    else if(charCode == 9)
                      return true;
                    else
                      return true;
                }
                
                //Txtbox Quantity Events
                defaultquantity = "";
                $("#Quantity").click(function() {
                    if ($("#Quantity").val() == defaultquantity) {
                        $("#Quantity").val("");
                    }
                });

                $("#Quantity").keyup(function() {
                    $("#Quantity").change();
                });

                $("#Quantity").blur(function() {
                    $("#Quantity").change();
                });

                $("#Quantity").change(function() {
                    if ($("#Quantity").val() == "") {
                        $("#Quantity").val("");
                        $("#TotalItemPoints").html("");
                    } else {
                        var totalitempoints = parseInt($("#ItemPoints").html()) * parseInt($("#Quantity").val());
                        $("#TotalItemPoints").html('Total Points: ' + totalitempoints.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                        $("#hdnTotalItemPoints").val(parseInt($("#ItemPoints").html()) * parseInt($("#Quantity").val()));
                    }
                });

                $("#Quantity").keypress(function(event){
                    return numberonly(event);
                });
                
                //Txtbox ItemQuantity Events
                defaultquantity = "";
                $("#ItemQuantity").click(function() {
                    if ($("#ItemQuantity").val() == defaultquantity) {
                        $("#ItemQuantity").val("");
                    }
                });

                $("#ItemQuantity").keyup(function() {
                    $("#ItemQuantity").change();
                });

                $("#ItemQuantity").blur(function() {
                    $("#ItemQuantity").change();
                });

                $("#ItemQuantity").change(function() {
                    if ($("#ItemQuantity").val() == "") {
                        $("#ItemQuantity").val("");
                        $("#TotalItemPoints").html("");
                    } else {
                        var totalitempoints = parseInt($("#ItemPoints").html()) * parseInt($("#ItemQuantity").val());
                        $("#TotalItemPoints").html('Total Points: ' + totalitempoints.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                        $("#hdnTotalItemPoints").val(parseInt($("#ItemPoints").html()) * parseInt($("#ItemQuantity").val()));
                    }
                });

                $("#ItemQuantity").keypress(function(event){
                    return numberonlyonetofive(event);
                });
                
                function getCitiesList(regionid){
                        var functionname = "GetCities";
                        $.ajax({
                                    url: "admin/Helper/helper.rewardoffersredemption.php",
                                    type: 'post',
                                    data : {
                                                    functiontype : function() {return functionname; },
                                                    regionid : function(){return regionid;}
                                                },
                                    dataType: 'json',
                                    success: function(data)
                                    {
                                        $("#CityID").html("");
                                        $("#CityID").append("<option value=''>Select City</option>");
                                        for(var itr = 0; itr < data.CountOfCities; itr++){
                                            $("#CityID").append("<option value='"+data.ListOfCities[itr].CityID+"'>"+data.ListOfCities[itr].CityName+"</option>");
                                        }
                                    }
                            });
                    }

                    $("#RegionID").live("change",function(){
                        var regionid = $("#RegionID").val();
                        if(regionid != ""){
                            getCitiesList(regionid);
                        } else{
                            $("#CityID").html("");
                            $("#CityID").append("<option value=''>Select City</option>");
                        }
                    });
                
                
                //Redeem Button Click Event Function
                $("#redeem-button").live("click",function(){
                    $("#profileupdate").validationEngine();
                        if ($("#redemptionquantity").dialog( "isOpen" ) !== true){
                        var ProductName = $("#hdnProductName").val();
                        var ItemPoints = "<?php echo $_SESSION['RewardItemsInfo']['Points']; ?>";
                        $("#ItemName").html(ProductName);
                        $("#ItemPoints").html(ItemPoints);
                        $("#hdnItemName").val(ProductName);
                        $("#hdnItemPoints").val(ItemPoints);
                        var email = "<?php echo $result = $_MemberInfo->getEmail($_SESSION["MemberInfo"]["Member"]["MID"]); ?>";
                        if(email  == ""){
                                $("#redemptionquantity").dialog({
                                    modal: true,
                                    buttons: {
                                        "Next": function(){
                                                if ($("#MainForm").validationEngine('validate')){
                                                    $(this).dialog("hide");
                                                    if ($("#profileinfo").dialog( "isOpen" ) !== true){
                                                        $("#profileinfo").dialog({
                                                            modal: true,
                                                            width: 550,
                                                            height: 'auto',
                                                            position: 'center',
                                                            buttons: {
                                                                "Submit": function(){
                                                                    if ($("#profileupdate").validationEngine('validate')){
                                                                        $.post("ajaxhandler.php", 
                                                                        { 'Module' : 'Membership'
                                                                            , 'Class' : 'MemberInfo' 
                                                                            , 'Method' : 'updateProfileWithNoEmail' 
                                                                            , 'MethodArgs': $('#profileupdate').serialize()
                                                                        },
                                                                        function(data) 
                                                                        {
                                                                            if (data != "Profile Updated Successfully.")
                                                                            {
                                                                                $("#failedmessage").html("<center><p>"+data+"</p></center>");
                                                                                $("#failedmessage").dialog({
                                                                                    modal: true,
                                                                                    width: 350,
                                                                                    height: 'auto',
                                                                                    position: 'center',
                                                                                    buttons: {
                                                                                        "Ok": function(){
                                                                                            $("#Quantity").val("");
                                                                                            $("#ItemQuantity").val("");
                                                                                            $("#TotalItemPoints").html("");
                                                                                            $(this).dialog('close');
                                                                                            $("#MainForm").submit();
                                                                                        }
                                                                                    }
                                                                                });
                                                                            } else {
                                                                                $(this).dialog('close');
                                                                                $('#MainForm').submit();
                                                                            }
                                                                        }, "json");
                                                                    }
                                                                },
                                                                "Cancel" : function(){
                                                                    $("#Quantity").val("");
                                                                    $("#ItemQuantity").val("");
                                                                    $("#TotalItemPoints").html("");
                                                                    $(this).dialog("close");
                                                                }

                                                            },
                                                            open: function(event, ui) {
                                                                $("#profileupdate").validationEngine();
                                                            },
                                                            close: function(event, ui) {
                                                                $("#profileupdate").validationEngine('hideAll');
                                                            },
                                                            title: 'Update Account Information'
                                                        }).parent().appendTo($("#profileupdate"));
                                                    }
                                                }
                                        },
                                        "Cancel": function(){
                                            $("#Quantity").val("");
                                            $("#ItemQuantity").val("");
                                            $("#TotalItemPoints").html("");
                                            $(this).dialog("close");
                                        }
                                    },
                                    open: function(event, ui) {
                                        $("#MainForm").validationEngine();
                                    },
                                    close: function(event, ui) {
                                        $("#MainForm").validationEngine('hideAll');
                                    },
                                    width: 550,
                                    title: "Redeem Item/Coupon"
                                }).parent().appendTo($("#MainForm"));
                        } else {
                            $("#redemptionquantity").dialog({
                            modal: true,
                            buttons: {
                                "Submit": function(){
                                    if ($("#MainForm").validationEngine('validate')){
                                        <?php unset($_SESSION["PreviousRedemption"]); ?>
                                        $(this).dialog('close');
                                        $("#MainForm").submit();
                                    }
                                },
                                "Cancel": function(){
                                    $("#Quantity").val("");
                                    $("#ItemQuantity").val("");
                                    $("#TotalItemPoints").html("");
                                    $(this).dialog("close");
                                }
                            },
                            open: function(event, ui) {
                                $("#MainForm").validationEngine();
                            },
                            close: function(event, ui) {
                                $("#MainForm").validationEngine('hideAll');
                            },
                            width: 550,
                            title: "Redeem Item/Coupon"
                        }).parent().appendTo($("#MainForm"));
                        }
                        }
            });
                
        });
    </script>
    <link href="css/slider/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="css/slider/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
    <link href="css/slider/prof_slider/style.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="css/slider/prof_slider/ad_gallery.css">
    <br/>
   <div id="bread-crumbs"><a href="profile.php">Home</a> |  Entertainment City</div>           
   <?php include('admin/template/itemredemptiontemplate.php'); ?>
    <div class="membership-inner-wrapper">
        <div class="row-fluid test">
            <div class="span7">
                <!--<div class="limited-ribbon-full"></div>-->
                <!--<img src="images/slider/membership_innerpages/product_image_full.jpg"></div>-->
                <img src="<?php $imagepath = App::getParam('rewarditem_imagepath').$_SESSION['RewardItemsInfo']['LearnMoreImage']; echo $imagepath; ?>"></div>
            <div class="span5">
                 <div style="background-color:#cecece; text-align:center; padding: 20px 30px;">
                     <h1><?php echo number_format($_SESSION['RewardItemsInfo']['Points'], 0, "", ",") ?> Points</h1>
                 </div>
                <div class="miw-product-wrapper" style="padding:14px 30px;">
                    <div class="miw-product-name" style="padding:6px 0;">
                        <h4>
                            <?php  if($_SESSION['RewardItemsInfo']['IsMystery'] == 1 && (int)$availableitemcount['AvailableItemCount'] > 0) { 
                                            echo $mysterydetails["MysteryName"];
                                        } else {
                                            echo $_SESSION['RewardItemsInfo']['ProductName'];
                                        } ?>
                        </h4>
                    </div>
                     <div class="miw-product-desc" style="font-size:12px; line-height: 12px;">
                        <?php // if(isset($itemDetails["DetailsOneA"])){ echo $itemDetails["DetailsOneA"]; }?>
                         <?php  if($_SESSION['RewardItemsInfo']['IsMystery'] == 1 && (int)$availableitemcount['AvailableItemCount'] > 0) { 
                                            echo $mysterydetails["MysterySubtext"];
                                        } else {
                                            echo $rewarditemdetails['SubText'];
                                        } ?>
                     </div>
                     <div class="miw-partner-desc" style="font-weight: bold; margin-top:10px;"><?php echo $_SESSION['RewardItemsInfo']['PartnerName']; ?></div>
                     <br>
                     <?php echo $btnRedeemButton; ?>
                </div>
            </div>
            </div>
        </div><!-- .membership-inner-wrapper -->
        <br>
        <div class="round-gold membership-inner-padding">
            <?php // if($itemDetails != 0){ ?>
            <div class="row-fluid">
                <?php if($_SESSION['RewardItemsInfo']['RewardID'] == 1){ ?>
                <div class="span7">
                    <h3>ABOUT THIS REWARD</h3>
                    <hr>
                    <?php  if($_SESSION['RewardItemsInfo']['IsMystery'] == 1 && (int)$availableitemcount['AvailableItemCount'] > 0) { 
                                            echo $mysterydetails["MysteryAbout"];
                                        } else {
                                            echo $rewarditemdetails['About'];
                                        } ?>
                </div>
                <div class="span5">
                    <h3>COMPANY INFO</h3>
                    <hr>
                        <strong>
                            <?php echo $_SESSION['RewardItemsInfo']['PartnerName'] ?>
                        </strong>
                        <p>
                            <?php echo $partnersd[0]['CompanyAddress']; ?>
                        </p>
                        <p>
                            Tel. Nos.: <?php echo $partnersd[0]['CompanyPhone']; ?>
                        </p>
                        <p>
                            Website: <?php echo $partnersd[0]['CompanyWebsite']; ?>
                        </p>
                </div>
                <?php } else { ?>
                <div class="span12">
                    <h3>ABOUT THIS REWARD</h3>
                    <hr>
                    <?php echo $rewarditemdetails['SubText']; ?>
                </div>
                <?php } ?>
            </div>
            <br>
            <div class="row-fluid">
                <div class="span12">
                    <h3>TERMS AND CONDITIONS</h3>
                    <hr>
                    <?php  if($_SESSION['RewardItemsInfo']['IsMystery'] == 1 && (int)$availableitemcount['AvailableItemCount'] > 0) { 
                                            echo $mysterydetails["MysteryTerms"];
                                        } else {
                                            echo $rewarditemdetails['Terms'];
                                        } ?>
                </div>
            </div>
            <?php // } else { echo "<p style='font-size: 14px;'>Reward Item has no details provided.</p>"; } ?>
        </div>
        <!--popup dialog box for redemption-->
        <div id="redemptionquantity" style="display:none;">
            <?php echo $hdnMemberInfoID; ?>
            <?php echo $hdnItemName; ?>
            <?php echo $hdnProductName; ?>
            <?php echo $hdnItemPoints; ?>
            <?php echo $hdnTotalItemPoints; ?>
            <?php echo $hdnCardNumber; ?>
            Item Name: <span id="ItemName"></span><br/>
            Points per Item: <span id="ItemPoints"></span><br/><br/>
            <?php if($_SESSION['RewardItemsInfo']['RewardID'] == 1){ echo 'Please enter quantity to be redeemed (max. 5 items). '; echo $txtItemQuantity; } else { echo 'Please enter quantity to be redeemed. '; echo $txtQuantity; } ?><br/>
            <span id="TotalItemPoints"></span>
        </div>
        <!-------------------------------------------------->
        </form>
        
        <!------------------PROFILE UPDATE VIEW------------------->
        <form name="profileupdate" id="profileupdate">
            <div id="profileinfo" class="profileinfo" style="display:none; font-size: 10pt; text-align: left;">
                <?php echo $hdnMemberInfoID; ?>
                <table>
                    <tr>
                        <td id="profileinfo-td-label" style="padding-bottom: 5px;">Card Number:</td>
                        <td style="padding-bottom: 5px;"><?php echo $cardnumber; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">First Name:</td>
                        <td><?php echo $txtRedeemFirstName; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">Last Name:</td>
                        <td><?php echo $txtRedeemLastName; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">Birth Date:</td>
                        <td><?php echo $dtRedeemBirthDate; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">Address:</td>
                        <td><?php echo $txtRedeemAddress1; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">Region: </td>
                        <td><?php echo $cboRegionID; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">City: </td>
                        <td><?php echo $cboCityID; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">Email: </td>
                        <td><?php echo $txtRedeemEmail; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">Mobile Number: </td>
                        <td><?php echo $txtRedeemMobileNumber; ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="checkbox" id="TermsAndConditions" class="validate[required]" name="TermsAndConditions"><label for="TermsAndConditions" class="formlabel">Player has read and accepted the promo mechanics and terms and conditions</label></td>
                    </tr>
                </table>
            </div>

            <?php
            // if redemption is successful, show redemption window
            if ($showcouponredemptionwindow == true) {
                echo $emailmessage;
            }
            ?>
            
    <div id="failedmessage" style="display:none; color: red;">
        
    </div>
    <?php include "footer.php"; 
} else { echo'<script> alert("Session is Expired"); window.location="index.php"; </script> '; } ?>
