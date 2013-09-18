<?php
/**
 * @Author : owliber
 * @DateCreated : 2013-05-20
 */

/**
* @Author: aqdepliyan
* @DateUpdated: 2013-07-16 01:04PM
*/

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Redemption";
$currentpage = "Redemption";

//Load Modules Classes
App::LoadModuleClass('Membership', 'Cities');
App::LoadModuleClass('Membership', 'Regions');
App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass('Membership', 'Helper');

App::LoadModuleClass("Loyalty", "CouponBatches");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "CardTypes");
App::LoadModuleClass("Loyalty", "Rewards");
App::LoadModuleClass("Loyalty", "CardTransactions");
App::LoadModuleClass('Loyalty', 'RewardItems');
App::LoadModuleClass('Loyalty', 'RewardOffers');
App::LoadModuleClass('Loyalty', 'CouponRedemptionLogs');
App::LoadModuleClass('Loyalty', 'RaffleCoupons');
App::LoadModuleClass("Loyalty", "ItemRedemptionLogs");
App::LoadModuleClass("Loyalty", "Promos");
App::LoadModuleClass("Loyalty", "PendingRedemption");

App::LoadModuleClass('Kronus', 'Sites');

//Load Core Classes
App::LoadCore('ErrorLogger.php');
App::LoadCore("URL.class.php");
App::LoadCore("Hashing.class.php");
App::LoadCore("Validation.class.php");
App::LoadCore("File.class.php");
App::LoadCore("PHPMailer.class.php");

//Load Control Classes
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("ComboBox");
App::LoadControl("CheckBox");
App::LoadControl("DatePicker");
App::LoadControl("Hidden");


//Initialize Modules
$_CouponBatches = new CouponBatches();
$_AuditTrail = new AuditTrail();
$_ItemRedemptionLogs = new ItemRedemptionLogs();
$_CouponRedemptionLogs = new CouponRedemptionLogs();
$_RaffleCoupons = new RaffleCoupons();
$_RewardOffers = new RewardOffers();
$_RewardItems = new RewardItems();
$_MemberCards = new MemberCards();
$_MemberInfo = new MemberInfo();
$_Ref_city = new Cities();
$_Ref_region = new Regions();
$_Promos = new Promos();
$_Sites = new Sites();
$_Helper = new Helper();
$_PendingRedemption = new PendingRedemption();

/* Initialize variables and default values */
$sendemailtoadmin = false;
$emailmessage = "";
$site = "";
$arrmemberinfo = "";
$showcouponredemptionwindow = false;


/* Used to determine min and max dates for birthdate field */
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();
$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);
$_Log = new AuditTrail();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

//Set Default value of search text field
$defaultsearchvalue = "Enter Card Number";

/* Initialize forms */
$fproc = new FormsProcessor();


/* Controls for form redemption */
$txtQuantity = new TextBox('Quantity', 'Quantity', 'Quantity ');
$txtQuantity->ShowCaption = false;
$txtQuantity->CssClass = 'validate[required,custom[integer],min[1]]';
$txtQuantity->Style = 'color: #666';
$txtQuantity->Length = 5;
$txtQuantity->Size = 5;
$txtQuantity->Text = "";
$txtQuantity->Args = "placeholder='0' ";
$fproc->AddControl($txtQuantity);

$hdnMemberInfoID = new Hidden("MemberInfoID", "MemberInfoID", "MemberInfoID: ");
$hdnMemberInfoID->ShowCaption = true;
$hdnMemberInfoID->Text = "";
$fproc->AddControl($hdnMemberInfoID);

$hdnRewardItemID = new Hidden("RewardItemID", "RewardItemID", "RewardItemID: ");
$hdnRewardItemID->ShowCaption = true;
$hdnRewardItemID->Text = "";
$fproc->AddControl($hdnRewardItemID);

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

$hdnIsCoupon = new Hidden("IsCoupon", "IsCoupon", "IsCoupon: ");
$hdnIsCoupon->ShowCaption = true;
$hdnIsCoupon->Text = "";
$fproc->AddControl($hdnIsCoupon);

$hdnRewardOfferID = new Hidden("RewardOfferID", "RewardOfferID", "RewardOfferID: ");
$hdnRewardOfferID->ShowCaption = true;
$hdnRewardOfferID->Text = "";
$fproc->AddControl($hdnRewardOfferID);

$hdnCardTypeID = new Hidden("CardTypeID", "CardTypeID", "CardTypeID: ");
$hdnCardTypeID->ShowCaption = true;
$hdnCardTypeID->Text = "";
$fproc->AddControl($hdnCardTypeID);

/* ----------------------------------------- */

$txtRedeemFirstName = new TextBox("FirstName", "FirstName", "First Name: ");
$txtRedeemFirstName->ShowCaption = false;
$txtRedeemFirstName->Length = 30;
$txtRedeemFirstName->Size = 15;
$txtRedeemFirstName->Args = "style='padding:2px;width: 245px;' ";
$txtRedeemFirstName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtRedeemFirstName);

$txtRedeemLastName = new TextBox("LastName", "LastName", "Last Name: ");
$txtRedeemLastName->ShowCaption = false;
$txtRedeemLastName->Length = 30;
$txtRedeemLastName->Size = 15;
$txtRedeemLastName->Args = "style='padding:2px;width: 245px;' ";
$txtRedeemLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtRedeemLastName);

$txtRedeemMobileNumber = new TextBox("MobileNumber", "MobileNumber", "Mobile Number: ");
$txtRedeemMobileNumber->ShowCaption = false;
$txtRedeemMobileNumber->Length = 30;
$txtRedeemMobileNumber->Size = 15;
$txtRedeemMobileNumber->Args = "style='padding:2px;width: 245px;' ";
$txtRedeemMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtRedeemMobileNumber);

$txtRedeemEmail = new TextBox("Email", "Email", "Email: ");
$txtRedeemEmail->ShowCaption = false;
$txtRedeemEmail->Length = 30;
$txtRedeemEmail->Size = 15;
$txtRedeemEmail->Args = "style='padding:2px;width: 245px;' ";
$txtRedeemEmail->CssClass = "validate[required, custom[email]]";
$fproc->AddControl($txtRedeemEmail);

$dtRedeemBirthDate = new DatePicker("Birthdate", "Birthdate", "Birth Date: ");
$dtRedeemBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtRedeemBirthDate->MinDate = $dsmindate->CurrentDate;
$dtRedeemBirthDate->ShowCaption = false;
$dtRedeemBirthDate->Args = "style='padding:2px;width: 245px;' ";
$dtRedeemBirthDate->YearsToDisplay = "-100";
$dtRedeemBirthDate->CssClass = "validate[required]";
$dtRedeemBirthDate->isRenderJQueryScript = true;
$fproc->AddControl($dtRedeemBirthDate);

$txtRedeemAddress1 = new TextBox("Address1", "Address1", "Address: ");
$txtRedeemAddress1->ShowCaption = false;
$txtRedeemAddress1->Length = 30;
$txtRedeemAddress1->Size = 15;
$txtRedeemAddress1->Args = "style='padding:2px;width: 245px;' ";
$txtRedeemAddress1->CssClass = "validate[required]";
$fproc->AddControl($txtRedeemAddress1);

$cboCityID = new ComboBox("CityID", "CityID", "City: ");
$opt1[] = new ListItem("Select City", "", true);
$cboCityID->Items = $opt1;
$cboCityID->ShowCaption = false;
$cboCityID->Args = "style='padding:2px;width: 250px;' ";
$cboCityID->CssClass = 'validate[required]';
$fproc->AddControl($cboCityID);


$arrRef_region = $_Ref_region->SelectAll();
$arrRef_regionList = new ArrayList($arrRef_region);
$cboRegionID = new ComboBox("RegionID", "RegionID", "Region: ");
$opt2[] = new ListItem("Select Region", "", true);
$cboRegionID->Items = $opt2;
$cboRegionID->ShowCaption = false;
$cboRegionID->Args = "style='padding:2px; width: 250px;' ";
$cboRegionID->CssClass = 'validate[required]';
$cboRegionID->DataSourceText = "RegionName";
$cboRegionID->DataSourceValue = "RegionID";
$cboRegionID->DataSource = $arrRef_regionList;
$cboRegionID->DataBind();
$fproc->AddControl($cboRegionID);

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->AutoComplete = false;
$txtSearch->Args = 'placeholder="Enter Card Number"';
$fproc->AddControl($txtSearch);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->Enabled = false;
$fproc->AddControl($btnSearch);

$btnClear = new Button('btnClear', 'btnClear', 'Clear');
$btnClear->ShowCaption = true;
$btnClear->IsSubmit = true;
$fproc->AddControl($btnClear);

$fproc->ProcessForms();

function curPageURL() {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}    

if($fproc->IsPostBack){
    //For redemption process
    if(!(isset($_SESSION["PreviousRedemption"])) && $txtQuantity->SubmittedValue != "" && $hdnItemName->SubmittedValue != "" 
            && $hdnItemPoints->SubmittedValue != "" && $hdnTotalItemPoints->SubmittedValue != ""){
            $_SESSION['CardRed']['IsCoupon'] = $hdnIsCoupon->SubmittedValue;
            
            $memberinfo = $_MemberInfo->getMemberInfo($_SESSION["CardRed"]["MID"]);
            $ArrMemberInfo = $memberinfo[0];
            
            //Check if the coupon batch is active, if not display error message.
            if($_SESSION['CardRed']['IsCoupon'] == 1 || $_SESSION['CardRed']['IsCoupon'] == "1"){
                //Set Table for raffle coupon based on active coupon batch.
                $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
                
                if(isset($getRaffleCouponSuffix[0]) && $getRaffleCouponSuffix[0]['CouponBatchID'] != ""){
                    $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
                    
                    //Get Reward Offer Coupon/Item Transaction details
                    //check if player has region id and city id, if not set both region id and city id to 0;
                    if((isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != '' && $ArrMemberInfo["RegionID"] != 0) && (isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != '' && $ArrMemberInfo["RegionID"] != 0)){
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
                    $sitecode = $_SESSION['userinfo']['SiteID'];

                    $siteresult = $_Sites->getSiteName($sitecode);
                    if(count($siteresult) > 0){
                        $sitename = $siteresult[0]["SiteName"];
                    } else {
                        $sitename = "";
                    }

                    $contactno = $ArrMemberInfo["MobileNumber"];
                    $source = 0; //0-Cashier; 1-Player
                    //Redemption Process for both Coupon and Item.
                    include("../controller/RedemptionController.php");
                    
                } else {
                    $txtQuantity->Text = '';
                    App::SetErrorMessage("Redemption Failed: Raffle Coupons are unavailable.");
                }
            } else {
                //Get Reward Offer Coupon/Item Transaction details
                //check if player has region id and city id, if not set both region id and city id to 0;
                if((isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != '' && $ArrMemberInfo["RegionID"] != 0) && (isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != '' && $ArrMemberInfo["RegionID"] != 0)){
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
                $sitecode = $_SESSION['userinfo']['SiteID'];

                $siteresult = $_Sites->getSiteName($sitecode);
                if(count($siteresult) > 0){
                    $sitename = $siteresult[0]["SiteName"];
                } else {
                    $sitename = "";
                }

                $contactno = $ArrMemberInfo["MobileNumber"];
                $source = 0; //0-Cashier; 1-Player
                //Redemption Process for both Coupon and Item.
                include("../controller/RedemptionController.php");
            }
            
            

            //Check if coupon or item and display appropriate reward 
            //offer transaction printable copy and send to legit player email.
            if ($showcouponredemptionwindow == true && isset($_SESSION['RewardOfferCopy']))
            {
                $RewardOfferID = $hdnRewardOfferID->SubmittedValue;
                $cardNumber = $_SESSION["CardRed"]["CardNumber"];
                $getPromoID = $_RewardOffers->SelectByID($RewardOfferID);
                $dateRange = $_RewardOffers->getOfferDateRange($RewardOfferID);
                $promodetails = $_Promos->getPromoDetails($getPromoID[0]["PromoID"]);

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
                    
                    $getResult = $_RewardOffers->getRewardItemDetailsForCopy($RewardOfferID);
                    if(isset($getResult[0])){
                        $rewardoffersdetails = $getResult[0];
                        preg_match('/\((.*?)\)/', $rewardoffersdetails["ProductName"], $rewardname);
                        if(is_array($rewardname) && isset($rewardname[1])){
                            unset($rewardoffersdetails["ProductName"]);
                            $rewardoffersdetails["ProductName"] = $rewardname[1];
                        }
                        $productname = $rewardoffersdetails["ProductName"];
                        $partnername = $rewardoffersdetails["PartnerName"];
                        $rewarditemcode = $rewardoffersdetails["eCouponCode"];
                    }
                    
                    $sdate = new DateTime(date($dateRange["StartDate"]));
                    $startdate = $sdate->format("F j, Y");
                    $edate = new DateTime(date($dateRange["EndDate"]));
                    $enddate = $edate->format("F j, Y");
                    $promoperiod = $startdate." to ".$enddate;
                }
                

                // For Coupon Only : Set Draw Date Format.
                $ddate = new DateTime(date($promodetails["DrawDate"]));
                $drawdate = $ddate->format("F j, Y gA");

                //Format Reward Offer Copy for email and popup window.
                if(!isset($_SESSION['RewardOfferCopy']["CouponSeries"])){
                    $imagesdir = str_replace(URL::CurrentPage(), "loyalty/images/", curPageURL());
                    App::LoadCore("File.class.php");
                    $filename = dirname(__FILE__) . "/template/itemredemptiontemplate.php";
                    $fp = new File($filename);
                    $emailmessage = $fp->ReadToEnd();
                    $emailmessage = str_replace('$playername', $playername, $emailmessage);
                    $emailmessage = str_replace('$sitecode', $sitename, $emailmessage);
                    $emailmessage = str_replace('$redemptiondate', $redemptiondate, $emailmessage);
                    $emailmessage = str_replace('$cardno', $cardNumber, $emailmessage);
                    $emailmessage = str_replace('$imagesdir', $imagesdir, $emailmessage);
                    $emailmessage = str_replace('$startperiod', $startdate, $emailmessage);
                    $emailmessage = str_replace('$endperiod', $enddate, $emailmessage);
                    $emailmessage = str_replace('$itemname', $productname, $emailmessage);
                    $emailmessage = str_replace('$partnername', $partnername, $emailmessage);
                    $emailmessage = str_replace('$rewarditemcode', $rewarditemcode, $emailmessage);
                    $emailmessage = str_replace('$checksum', $_SESSION['RewardOfferCopy']["CheckSum"], $emailmessage);

                    $newheader = $imagesdir."newheader.jpg";
                    $newfooter = $imagesdir."newfooter.jpg";
                    $item = $imagesdir."sampleitem1.jpg";

                    $_Helper->sendEmailItemRedemption($playername,$email,$sitecode,$redemptiondate,$cardNumber,$newheader,$newfooter,$item,
                                                                                            $startdate,$enddate,$productname,$partnername,$rewarditemcode, $_SESSION['RewardOfferCopy']["CheckSum"]);
                    unset($_SESSION['RewardOfferCopy']);
                } else {
                    $imagesdir = str_replace(URL::CurrentPage(), "loyalty/images/", curPageURL());
                    $fbirthdate = date("F j, Y", strtotime($birthdate));
                    App::LoadCore("File.class.php");
                    $filename = dirname(__FILE__) . "/template/couponredemptiontemplate.php";
                    $fp = new File($filename);
                    $emailmessage = $fp->ReadToEnd();
                    $emailmessage = str_replace('$playername', $playername, $emailmessage);
                    $emailmessage = str_replace('$address', $address, $emailmessage);
                    $emailmessage = str_replace('$sitecode', $sitename, $emailmessage);
                    $emailmessage = str_replace('$cardno', $cardNumber, $emailmessage);
                    $emailmessage = str_replace('$birthdate', date("F j, Y", strtotime($birthdate)), $emailmessage);
                    $emailmessage = str_replace('$email', $email, $emailmessage);
                    $emailmessage = str_replace('$contactno', $contactno, $emailmessage);
                    $emailmessage = str_replace('$actualcity', $cityname, $emailmessage);
                    $emailmessage = str_replace('$actualregion', $regionname, $emailmessage);
                    $emailmessage = str_replace('$imagesdir', $imagesdir, $emailmessage);
                    $emailmessage = str_replace('$couponseries', $_SESSION['RewardOfferCopy']["CouponSeries"], $emailmessage);
                    $emailmessage = str_replace('$quantity', $_SESSION['RewardOfferCopy']["Quantity"], $emailmessage);
                    $emailmessage = str_replace('$checksum', $_SESSION['RewardOfferCopy']["CheckSum"], $emailmessage);
                    $emailmessage = str_replace('$serialnumber', $_SESSION['RewardOfferCopy']["SerialNumber"], $emailmessage);
                    $emailmessage = str_replace('$redemptiondate', $redemptiondate, $emailmessage);
                    $emailmessage = str_replace('$promocode', $promodetails["PromoCode"], $emailmessage);
                    $emailmessage = str_replace('$promoname', $promodetails["PromoName"], $emailmessage);
                    $emailmessage = str_replace('$promoperiod', $promoperiod, $emailmessage);
                    $emailmessage = str_replace('$drawdate', $drawdate, $emailmessage);

                    $newheader = $imagesdir."newheader.jpg";
                    $newfooter = $imagesdir."newfooter.jpg";
                    $coupon = $imagesdir."toyota.jpg";


                    $_Helper->sendEmailCouponRedemption($playername,$address,$sitecode,$cardNumber,$fbirthdate,$email,$contactno,$cityname,
                                                                                    $regionname,$newheader,$newfooter,$coupon,$_SESSION['RewardOfferCopy']["CouponSeries"],
                                                                                    $_SESSION['RewardOfferCopy']["Quantity"],$_SESSION['RewardOfferCopy']["CheckSum"],
                                                                                    $_SESSION['RewardOfferCopy']["SerialNumber"],$redemptiondate,$promodetails["PromoCode"],
                                                                                    $promodetails["PromoName"],$promoperiod,$drawdate);

                    unset($_SESSION['RewardOfferCopy']);
                }
            }
    }
    
    if ($btnClear->SubmittedValue == "Clear")
    {
        unset($_SESSION['CardRed'], $_SESSION['PreviousRedemption']);
        $txtSearch->Text = "";
    }
}

?>
<?php include('header.php'); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.redemption.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type="text/javascript">
    $(document).ready( function() {
        var url = "Helper/helper.rewardoffersredemption.php";
        function loadCardData(){
            var datavar = "<?php if(isset($_SESSION["CardRed"])){ $txtSearch->Text = $_SESSION["CardRed"]["CardNumber"]; echo $_SESSION["CardRed"]["CardNumber"]; }?>";
            if(datavar != ''){
                getCardData(datavar);
            }
        }
        loadCardData();
        
        function getCitiesList(regionid){
            var functionname = "GetCities";
            $.ajax({
                        url: "Helper/helper.rewardoffersredemption.php",
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
        
        //Function for Checking of Coupon Batch Status
        function checkCouponBatchAvailability(){
            var functionname = "CheckCouponAvailibility";
            var availability;
            $.ajax({
                        url: "Helper/helper.rewardoffersredemption.php",
                        type: 'post',
                        data : {
                                        functiontype : function() {return functionname; }
                                    },
                        async: false,
                        dataType: 'json',
                        success: function(data)
                        {
                             if(data.IsAvailableCouponBatchID == 0){
                                    availability = "False";
                             } else {
                                    availability = "True";
                             }
                        }
                });
                
                return availability;
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
        
        function getCardData(datavar){
            var functionname = "CardDetails";
            $.ajax({
                        url: "Helper/helper.rewardoffersredemption.php",
                        type: 'post',
                        data : {
                                        functiontype : function() {return functionname; },
                                        datavar : function(){return datavar;}
                                    },
                        dataType: 'json',
                        success: function(data)
                        {
                            if(data.Error == ""){
                                $("#cardinfo").removeAttr("style");
                                $("#cardinfo").css("display","block");
                                $("#idcardnumber").html("<span>"+data.CardNumber+"</span>");
                                $("#idcardtype").html("<span>"+data.CardType+"</span>");
                                $("#idcurrentpoints").html("<span>"+data.CurrentPoints+"</span>");
                                $("#idlifetimepoints").html("<span>"+data.LifetimePoints+"</span>");
                                $("#idbonuspoints").html("<span>"+data.BonusPoints+"</span>");
                                $("#idredeemedpoints").html("<span>"+data.RedeemedPoints+"</span>");
                                $("#idsitename").html("<span>"+data.LastSitePlayed+"</span>");
                                $("#idtransdate").html("<span>"+data.LastTransactionDate+"</span>");
                                
                                //Set Player Data
                                $("#FirstName").val(data.FirstName);
                                $("#LastName").val(data.LastName);
                                $("#MobileNumber").val(data.MobileNumber);
                                $("#Email").val(data.Email);
                                $("#Address1").val(data.Address1);
                                
                                $("#RegionID").get(0).selectedIndex = data.RegionID;
                                if(data.RegionID != ""){
                                    getCitiesList(data.RegionID);
                                    if($("#hdnCityID") != ""){
                                        $("#CityID").get(0).selectedIndex = data.CityID;
                                    }
                                }
                                
                                $("#Birthdate").datepicker("setDate", new Date(data.Birthdate) );
                                $("#CardNumber").html("<span>"+data.CardNumber+"</span>");

                                $("#CardTypeID").val(data.CardTypeID);
                                if(data.CardTypeID != 3){
                                    if(data.Status == 1){
                                        getRedeemableOffers();
                                    } else {
                                        $("#cardinfo").css("display","none");
                                        $("#error-msg").html("<center>"+data.StatusMsg+"</center>");
                                        $("#error-msg").dialog({
                                            modal: true,
                                            width: 350,
                                            height: 'auto',
                                            position: 'center',
                                            title: "Player Redemption",
                                            buttons: {
                                                "Ok": function(){
                                                    $(this).dialog('close');
                                                    $("#txtSearch").val("");
                                                }
                                            }
                                        });
                                    }
                                } else if(data.CardTypeID == 3 && data.Status == 5){
                                    $("#temp-msg").css("display","block");
                                } else {
                                    $("#cardinfo").css("display","none");
                                    $("#error-msg").html("<center>"+data.StatusMsg+"</center>");
                                    $("#error-msg").dialog({
                                        modal: true,
                                        width: 350,
                                        height: 'auto',
                                        position: 'center',
                                        title: "Player Redemption",
                                        buttons: {
                                            "Ok": function(){
                                                $(this).dialog('close');
                                                $("#txtSearch").val("");
                                            }
                                        }
                                    });
                                }
                            } else {
                                $("#error-msg").html("<center>"+data.Error+"</center>");
                                $("#error-msg").dialog({
                                    modal: true,
                                    width: 350,
                                    height: 'auto',
                                    position: 'center',
                                    title: "Player Redemption",
                                    buttons: {
                                        "Ok": function(){
                                            $(this).dialog('close');
                                            $("#txtSearch").val("");
                                        }
                                    }
                                });
                            }
                            
                        }
                });
        }
        
        function getRedeemableOffers()
        {
            var datavar = "";
            var functionname = "RewardOfferList";
            jQuery('#rewardofferslist').GridUnload();
            jQuery("#rewardofferslist").jqGrid({
                    url:url,
                    mtype: 'POST',
                    postData: {
                                functiontype : function() {return functionname; },
                                datavar : function() {return datavar; }
                              },
                    datatype: "JSON",
                    colNames:['Item', 'Points', 'Description', 'Promo Name', 'Action'],
                    colModel:[
                            {name:'ProductName',index:'ProductName',align: 'left', width: 150},
                            {name:'Points',index:'Points', align: 'right',width: 60},
                            {name:'Description',index:'Description', align: 'left'},
                            {name:'PromoName',index:'PromoName', align: 'left'},
                            {name:'Action',index:'Action', align: 'center', width: 70},
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    rowheight: 300,
                    height: 300,
                    width: 970,
                    pager: '#pagerrewardofferslist',
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"Redeemable Items/Coupon"
            });
            jQuery("#rewardofferslist").jqGrid('navGrid','#pagerrewardofferslist',
                    { edit:false,add:false,del:false, search:false, refresh: true });
        }
        
        $('#btnSearch').live('click', function(){
            var txtsearch = $("#txtSearch").val();
            if (txtsearch.substr(0,1) === " "){
                alert("Trailing space/s is/are not allowed");
            } else {
                jQuery('#rewardofferslist').GridUnload();
                getCardData($("#txtSearch").val());
            }    
        });
        
        <?php
            if ($showcouponredemptionwindow == true && $_SESSION['CardRed']['IsCoupon'] == 1)
            {
                ?>             
                            if ($("#couponmessagebody").dialog( "isOpen" ) !== true){
                                $("#couponmessagebody").dialog({
                                    modal: true,
                                    buttons: {
                                        "Print" : function() {
                                            $("#Quantity").val("");
                                            window.print();
                                            window.location.href = "redemption.php";
                                        },
                                        "Close": function() {
                                            $("#Quantity").val("");
                                            $(this).dialog("close");
                                            window.location.href = "redemption.php";
                                        }
                                    },
                                    open: function(event, ui) {
                                        $("#frmRedemption").hide();
                                    },
                                    close: function(event, ui) {
                                        $("#frmRedemption").show();
                                        window.location.href = "redemption.php";
                                    },
                                    width: 1100,
                                    title: "Redemption Successful"
                                });
                            }
            <?php } else if ($showcouponredemptionwindow == true && $_SESSION['CardRed']['IsCoupon'] == 0) { ?>
                            if ($("#itemmessagebody").dialog( "isOpen" ) !== true) {
                                $("#itemmessagebody").dialog({
                                    modal: true,
                                    buttons: {
                                        "Print" : function() {
                                            $("#Quantity").val("");
                                            window.print();
                                            window.location.href = "redemption.php";
                                        },
                                        "Close": function() {
                                            $("#Quantity").val("");
                                            $(this).dialog("close");
                                            window.location.href = "redemption.php";
                                        }
                                    },
                                    open: function(event, ui) {
                                        $("#frmRedemption").hide();
                                    },
                                    close: function(event, ui) {
                                        $("#frmRedemption").show();
                                        window.location.href = "redemption.php";
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
                $("#TotalItemPoints").html('Total Points: ' + parseInt($("#ItemPoints").html()) * parseInt($("#Quantity").val()));
                $("#hdnTotalItemPoints").val(parseInt($("#ItemPoints").html()) * parseInt($("#Quantity").val()));
            }
        });

        $("#Quantity").keypress(function(event){
            return numberonly(event);
        });
        
        
        $('#csredeem-button').live('click', function(){
               $("#profileupdate").validationEngine();            
                    var returnValue = checkCouponBatchAvailability();
                    if ($("#redemptionquantity").dialog( "isOpen" ) !== true){
                        var ProductName = $(this).attr("ProductName");
                        var ItemPoints = $(this).attr("RequiredPoints");
                        var IsCoupon = $(this).attr("IsCoupon");
                        var RewardOfferID = $(this).attr("RewardOfferID");
                        var RewardItemID = $(this).attr("RewardItemID");
                        var email = $(this).attr("Email");
                        $("#ItemName").html(ProductName);
                        $("#ItemPoints").html(ItemPoints);
                        $("#hdnItemName").val(ProductName);
                        $("#hdnItemPoints").val(ItemPoints);
                        $("#RewardItemID").val(RewardItemID);
                        $("#IsCoupon").val(IsCoupon);
                        $("#RewardOfferID").val(RewardOfferID);
                        
                        //Check if the coupon batch is active, if not display error message.
                        if(returnValue == 'False' && $("#IsCoupon").val() == 1){ 
                            $('#redemption-errormsg').html('<p style="padding-top: 5px; padding-bottom: 5px; padding-left: 3px;">Raffle Coupons are unavailable.</p>');
                            $('#redemption-errormsg').css('display','block');
                        } else {
                            $('#redemption-errormsg').html('');
                            $('#redemption-errormsg').css('display','none');
                        }
                        
                        if(email  == ""){
                                $("#redemptionquantity").dialog({
                                    modal: true,
                                    buttons: {
                                        "Next": function(){
                                                if ($("#frmRedemption").validationEngine('validate')){
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
                                                                                            $("#TotalItemPoints").html("");
                                                                                            $(this).dialog('close');
                                                                                            $("#frmRedemption").submit();
                                                                                        }
                                                                                    }
                                                                                });
                                                                            } else {
                                                                                $(this).dialog('close');
                                                                                $('#frmRedemption').submit();
                                                                            }
                                                                        }, "json");
                                                                    }
                                                                },
                                                                "Cancel" : function(){
                                                                    $("#Quantity").val("");
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
                                            $("#TotalItemPoints").html("");
                                            $(this).dialog("close");
                                        }
                                    },
                                    open: function(event, ui) {
                                        $("#frmRedemption").validationEngine();
                                    },
                                    close: function(event, ui) {
                                        $("#frmRedemption").validationEngine('hideAll');
                                    },
                                    width: 550,
                                    title: "Redeem Item/Coupon"
                                }).parent().appendTo($("#frmRedemption"));
                        } else {
                            $("#redemptionquantity").dialog({
                            modal: true,
                            buttons: {
                                "Submit": function(){
                                    if ($("#frmRedemption").validationEngine('validate')){
                                        <?php unset($_SESSION["PreviousRedemption"]); ?>
                                        $(this).dialog('close');
                                        $("#frmRedemption").submit();
                                    }
                                },
                                "Cancel": function(){
                                    $("#Quantity").val("");
                                    $("#TotalItemPoints").html("");
                                    $(this).dialog("close");
                                }
                            },
                            open: function(event, ui) {
                                $("#frmRedemption").validationEngine();
                            },
                            close: function(event, ui) {
                                $("#frmRedemption").validationEngine('hideAll');
                            },
                            width: 550,
                            title: "Redeem Item/Coupon"
                        }).parent().appendTo($("#frmRedemption"));
                    }
                }
        });
        
    });
</script>
<div align="center">
    </form>
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">

            <form name="frmrewardlist" id="frmrewardlist" method="post">
                <?php include('redemptioncardsearch.php'); ?>
                    <div align="center" id="pagination">
                        <table border="1" id="rewardofferslist">

                        </table>
                        <div id="pagerrewardofferslist"></div>
                        <span id="errorMessage"></span>
                    </div> 
                    <p id="temp-msg" style='display: none; font-size: 14px;'>Please migrate your Temporary Account to a Membership Card to activate Redemption.</p>
                    <div id="error-msg" style="display:none; font-size: 14px;"></div>
            </form>
            <form name="frmRedemption" id="frmRedemption" method="post">
                <div id="redemptionquantity" style="display:none;">
                    <div id="redemption-errormsg" style="display: none; font-size: 12px; background-color: red; color: white; width: 100%;"></div>
                    <?php echo $hdnMemberInfoID; ?>
                    <?php echo $hdnItemName; ?>
                    <?php echo $hdnItemPoints; ?>
                    <?php echo $hdnTotalItemPoints; ?>
                    <?php echo $hdnRewardItemID; ?>
                    <?php echo $hdnRewardOfferID; ?>
                    <?php echo $hdnIsCoupon; ?>
                    Item Name: <span id="ItemName"></span><br/>
                    Points per Item: <span id="ItemPoints"></span><br/><br/>
                    Please enter quantity to be redeemed. <?php echo $txtQuantity; ?><br/>
                    <span id="TotalItemPoints"></span>
                </div>
            </form>
        <form name="profileupdate" id="profileupdate">
            <div id="profileinfo" class="profileinfo" style="display:none; font-size: 10pt; text-align: left;">
                <table>
                    <tr>
                        <td id="profileinfo-td-label" style="padding-bottom: 5px;">Card Number:</td>
                        <td style="padding-bottom: 5px;"><label id="CardNumber"></label></td>
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
                        <td colspan="2"><input type="checkbox" id="TermsAndConditions" class="validate[required]" name="TermsAndConditions"><label for="TermsAndConditions" class="formlabel">&nbsp;Player has read and accepted the promo mechanics and terms and conditions</label></td>
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
        </form>
    </div>
</div>
<?php include('footer.php'); ?>
