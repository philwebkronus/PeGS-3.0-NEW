<?php
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

    App::LoadModuleClass("Membership", "Members");
    App::LoadModuleClass("Membership", "MemberInfo");
    App::LoadModuleClass("Membership", "MemberSessions");
    App::LoadModuleClass("Membership", "Identifications");
    App::LoadModuleClass("Membership", "Nationality");
    App::LoadModuleClass("Membership", "Occupation");
    App::LoadModuleClass("Membership", "Referrer");
    App::LoadModuleClass("Membership", "AuditTrail");
    App::LoadModuleClass("Membership", "AuditFunctions");
    App::LoadModuleClass('Membership', 'Cities');
    App::LoadModuleClass('Membership', 'Regions');
    App::LoadModuleClass("Membership", "Cities");
    App::LoadModuleClass("Membership", "Regions");

    App::LoadModuleClass("Loyalty", "CouponBatches");
    App::LoadModuleClass("Loyalty", "MemberCards");
    App::LoadModuleClass("Loyalty", "Cards");
    App::LoadModuleClass("Loyalty", "CardTypes");
    App::LoadModuleClass("Loyalty", "Rewards");
    App::LoadModuleClass("Loyalty", "CardTransactions");
    App::LoadModuleClass('Loyalty', 'RewardItems');
    App::LoadModuleClass('Loyalty', 'RewardOffers');
    App::LoadModuleClass('Loyalty', 'MemberCards');
    App::LoadModuleClass('Loyalty', 'CouponRedemptionLogs');
    App::LoadModuleClass('Loyalty', 'RaffleCoupons');
    App::LoadModuleClass("Loyalty", "ItemRedemptionLogs");
    App::LoadModuleClass("Loyalty", "RewardItemDetails");
    App::LoadModuleClass("Loyalty", "Promos");

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
    $_RewardOffers = new RewardOffers();
    $_RewardItems = new RewardItems();
    $_MemberCards = new MemberCards();
    $_MemberInfo = new MemberInfo();
    $_Ref_city = new Cities();
    $_Ref_region = new Regions();
    $_RewardItemDetails = new RewardItemDetails();
    $_Promos = new Promos();
    
    //Set Table for raffle coupon based on active coupon batch.
    $getRaffleCouponSuffix = $_CouponBatches->SelectByWhere(" WHERE Status = 1 LIMIT 1");
    $_RaffleCoupons->TableName = "rafflecoupons_".$getRaffleCouponSuffix[0]['CouponBatchID'];
    
    //for loading reward item details
    $itemDetails = $_RewardItemDetails->SelectByID($_SESSION['RewardItemsInfo']['RewardItemID']);
    
    $fproc = new FormsProcessor();
    
    $btnRedeemButton = new Button("redeem-button", "redeem-button", "REDEEM NOW");
    $btnRedeemButton->CssClass = "yellow-btn-redeem-button";
    if($_SESSION['RewardItemsInfo']['PlayerPoints'] < $_SESSION['RewardItemsInfo']['Points']){
        $btnRedeemButton->Enabled = false;
    } else {
        $btnRedeemButton->Enabled = true;
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
    $hdnPlayerPoints->Text = $_SESSION['RewardItemsInfo']['PlayerPoints'];
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
    
    $txtQuantity = new TextBox('Quantity', 'Quantity', 'Quantity ');
    $txtQuantity->ShowCaption = false;
    $txtQuantity->CssClass = 'validate[required,custom[integer],min[1]]';
    $txtQuantity->Style = 'color: #666';
    $txtQuantity->Length = 5;
    $txtQuantity->Size = 5;
    $txtQuantity->Text = "0";
    $fproc->AddControl($txtQuantity);
    
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
    
    $arrRef_city = $_Ref_city->SelectAll();
    $arrRef_cityList = new ArrayList($arrRef_city);
    $cboCityID = new ComboBox("CityID", "CityID", "City: ");
    $opt1[] = new ListItem("Select City", "0", true);
    $cboCityID->Items = $opt1;
    $cboCityID->ShowCaption = false;
    $cboCityID->CssClass = 'validate[required]';
    $cboCityID->DataSourceText = "CityName";
    $cboCityID->DataSourceValue = "CityID";
    $cboCityID->DataSource = $arrRef_cityList;
    $cboCityID->DataBind();
    $fproc->AddControl($cboCityID);
    

    $arrRef_region = $_Ref_region->SelectAll();
    $arrRef_regionList = new ArrayList($arrRef_region);
    $cboRegionID = new ComboBox("RegionID", "RegionID", "Region: ");
    $opt2[] = new ListItem("Select Region", "0", true);
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
        $cardNumber = $cardinfo[0]['CardNumber'];
        $hdnCardNumber->Text = $cardNumber;
        $txtRedeemFirstName->Text = $ArrMemberInfo["FirstName"];
        $txtRedeemLastName->Text = $ArrMemberInfo["LastName"];
        $txtRedeemAddress1->Text = $ArrMemberInfo["Address1"];
        $cboCityID->SetSelectedValue($ArrMemberInfo["CityID"]);
        $cboRegionID->SetSelectedValue($ArrMemberInfo["RegionID"]);
        $txtRedeemMobileNumber->Text = $ArrMemberInfo["MobileNumber"];
        $txtRedeemEmail->Text = $ArrMemberInfo["Email"];
        $dtRedeemBirthDate->SelectedDate = $ArrMemberInfo["Birthdate"];
        $hdnMemberInfoID->Text = $ArrMemberInfo["MemberInfoID"];
    }
    
    $fproc->ProcessForms();
    
function curPageURL()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
        {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80")
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        }
        else
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }    
    
    if($fproc->IsPostBack){
         if (!(isset($_SESSION["PreviousRedemption"])) && $txtQuantity->SubmittedValue != "" && $hdnItemName->SubmittedValue != "" 
                && $hdnItemPoints->SubmittedValue != "" && $hdnTotalItemPoints->SubmittedValue != "" && $hdnCardNumber->SubmittedValue != ""){

                //Get Reward Offer Coupon/Item Transaction details
                $cardinfo = $_MemberCards->getActiveMemberCardInfo($MID);
                if (!isset($cardinfo[0]['CardNumber'])) {
                    unset($_SESSION['MemberInfo']);
                    App::SetErrorMessage("Account Banned");
                    echo "<script>parent.window.location.href='index.php';</script>";
                }
                
                //check if player has region id and city id, if not set both region id and city id to 0;
                if((isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != '') && (isset($ArrMemberInfo["RegionID"]) && $ArrMemberInfo["RegionID"] != '')){
                    $regionname = $_Ref_region->getRegionName($ArrMemberInfo["RegionID"]);
                    $cityname = $_Ref_city->getCityName($ArrMemberInfo["CityID"]);
                } else {
                    $regionname = 0;
                    $cityname = 0;
                }
                
                $playername = $ArrMemberInfo["FirstName"]." ".$ArrMemberInfo["LastName"];
                $address = $ArrMemberInfo["Address1"];
                $birthdate = $ArrMemberInfo["Birthdate"];
                $email = $ArrMemberInfo["Email"];
                $sitecode = "Website";
                $contactno = $ArrMemberInfo["MobileNumber"];
                
                //Redemption Process for both Coupon and Item.
                include("controller/PortalRedemptionController.php");
                
                //Check if coupon or item and display appropriate reward 
                //offer transaction printable copy and send to legit player email.
                if ($showcouponredemptionwindow == true && isset($_SESSION['RewardOfferCopy']))
                {
                    $RewardOfferID = $_SESSION['RewardItemsInfo']['RewardOfferID'];
                    $getPromoID = $_RewardOffers->SelectByID($RewardOfferID);
                    $dateRange = $_RewardOffers->getOfferDateRange($RewardOfferID);
                    $promodetails = $_Promos->getPromoDetails($getPromoID[0]["PromoID"]);

                    //Set Redemption Date and Time format.
                    $rdate = new DateTime(date($_SESSION['RewardOfferCopy']["RedemptionDate"]));
                    $redemptiondate = $rdate->format("F j, Y, g:i a");

                    //Set Promo Period Date Format
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

                    // For Coupon Only : Set Draw Date Format.
                    $ddate = new DateTime(date($promodetails["DrawDate"]));
                    $drawdate = $ddate->format("F j, Y gA");
                
                    //Format Reward Offer Copy for email and popup window.
                    if(!isset($_SESSION['RewardOfferCopy']["CouponSeries"])){
                        $imagesdir = str_replace(URL::CurrentPage(), "admin/loyalty/images/", curPageURL());
                        App::LoadCore("File.class.php");
                        $filename = dirname(__FILE__) . "/admin/template/itemredemptiontemplate.php";
                        $fp = new File($filename);
                        $emailmessage = $fp->ReadToEnd();
                        $emailmessage = str_replace('$playername', $playername, $emailmessage);
                        $emailmessage = str_replace('$address', $address, $emailmessage);
                        $emailmessage = str_replace('$quantity', $_SESSION['RewardOfferCopy']["Quantity"], $emailmessage);
                        $emailmessage = str_replace('$sitecode', $sitecode, $emailmessage);
                        $emailmessage = str_replace('$redemptiondate', $redemptiondate, $emailmessage);
                        $emailmessage = str_replace('$cardno', $cardNumber, $emailmessage);
                        $emailmessage = str_replace('$birthdate', date("F j, Y", strtotime($birthdate)), $emailmessage);
                        $emailmessage = str_replace('$email', $email, $emailmessage);
                        $emailmessage = str_replace('$contactno', $contactno, $emailmessage);
                        $emailmessage = str_replace('$checksum', $_SESSION['RewardOfferCopy']["CheckSum"], $emailmessage);
                        $emailmessage = str_replace('$serialnumber', $_SESSION['RewardOfferCopy']["SerialNumber"], $emailmessage);
                        $emailmessage = str_replace('$actualcity', $cityname, $emailmessage);
                        $emailmessage = str_replace('$actualregion', $regionname, $emailmessage);
                        $emailmessage = str_replace('$imagesdir', $imagesdir, $emailmessage);
                        $emailmessage = str_replace('$promocode', $promodetails["PromoCode"], $emailmessage);
                        $emailmessage = str_replace('$promoname', $promodetails["PromoName"], $emailmessage);
                        $emailmessage = str_replace('$promoperiod', $promoperiod, $emailmessage);

                //                eval('$emailmsg = $emailmessage; ');
                //                App::Pr($emailmessage);
                //                $filename = dirname(__FILE__) . "/posts.txt";
                //                $fp = new File($filename);
                //                $fp->Write($emailmessage);

                        $pm = new PHPMailer();

                        if ($sendemailtoadmin == 1)
                        {
                            $pm->AddAddress("rpsanchez@philweb.com.ph", "Roger Sanchez");
                            $pm->AddAddress("itqa@philweb.com.ph", "IT QA");
                            $pm->AddAddress("mmdapula@philweb.com.ph", "Mikko Dapula");
                            $pm->AddAddress("ammarcos@philweb.com.ph", "Maan Marcos");
                        }
                        $pm->AddAddress($email, $playername);
                        $pm->Body = $emailmessage;
                        $pm->IsHTML(true);

                        $pm->From = "membership@egamescasino.ph";
                        $pm->FromName = "Philweb Membership";
                        $pm->Host = "localhost";
                        $pm->Subject = "E-Games Membership";
                        $pm->Send();
                        unset($_SESSION['RewardOfferCopy']);
                    } else {
                        $imagesdir = str_replace(URL::CurrentPage(), "admin/loyalty/images/", curPageURL());
                        App::LoadCore("File.class.php");
                        $filename = dirname(__FILE__) . "/admin/template/couponredemptiontemplate.php";
                        $fp = new File($filename);
                        $emailmessage = $fp->ReadToEnd();
                        $emailmessage = str_replace('$playername', $playername, $emailmessage);
                        $emailmessage = str_replace('$address', $address, $emailmessage);
                        $emailmessage = str_replace('$sitecode', $sitecode, $emailmessage);
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

                //                eval('$emailmsg = $emailmessage; ');
                //                App::Pr($emailmessage);
                //                $filename = dirname(__FILE__) . "/posts.txt";
                //                $fp = new File($filename);
                //                $fp->Write($emailmessage);

                        $pm = new PHPMailer();

                        if ($sendemailtoadmin == 1)
                        {
                            $pm->AddAddress("rpsanchez@philweb.com.ph", "Roger Sanchez");
                            $pm->AddAddress("itqa@philweb.com.ph", "IT QA");
                            $pm->AddAddress("mmdapula@philweb.com.ph", "Mikko Dapula");
                            $pm->AddAddress("ammarcos@philweb.com.ph", "Maan Marcos");
                        }
                        $pm->AddAddress($email, $playername);
                        $pm->Body = $emailmessage;
                        $pm->IsHTML(true);

                        $pm->From = "membership@egamescasino.ph";
                        $pm->FromName = "Philweb Membership";
                        $pm->Host = "localhost";
                        $pm->Subject = "E-Games Membership";
                        $pm->Send();
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
                <?php
                if ($showcouponredemptionwindow == true && $_SESSION['RewardItemsInfo']['IsCoupon'] == 1)
                {
                    ?>             
                                if ($("#couponmessagebody").dialog( "isOpen" ) !== true){
                                    $("#couponmessagebody").dialog({
                                        modal: true,
                                        buttons: {
                                            "Print" : function() {
                                                $("#Quantity").val("");
                                                window.print();
                                            },
                                            "Close": function() {
                                                $("#Quantity").val("");
                                                $(this).dialog("close");
                                            }
                                        },
                                        open: function(event, ui) {
                                            $("#frmRedemption").hide();
                                        },
                                        close: function(event, ui) {
                                            $("#frmRedemption").show();
                                        },
                                        width: 1100,
                                        title: "Redemption Successful"
                                    });
                                }
                <?php } else if ($showcouponredemptionwindow == true && $_SESSION['RewardItemsInfo']['IsCoupon'] == 0) { ?>
                                if ($("#itemmessagebody").dialog( "isOpen" ) !== true) {
                                    $("#itemmessagebody").dialog({
                                        modal: true,
                                        buttons: {
                                            "Print" : function() {
                                                $("#Quantity").val("");
                                                window.print();
                                            },
                                            "Close": function() {
                                                $("#Quantity").val("");
                                                $(this).dialog("close");
                                            }
                                        },
                                        open: function(event, ui) {
                                            $("#frmRedemption").hide();
                                        },
                                        close: function(event, ui) {
                                            $("#frmRedemption").show();
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
                
                //Txtbox Quantity Events
                defaultquantity = "0";
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
                    if ($("#Quantity").val() == "" || $("#Quantity").val() == defaultquantity) {
                        $("#Quantity").val(defaultquantity);
                    } else {
                        $("#TotalItemPoints").html('Total Points: ' + parseInt($("#ItemPoints").html()) * parseInt($("#Quantity").val()));
                        $("#hdnTotalItemPoints").val(parseInt($("#ItemPoints").html()) * parseInt($("#Quantity").val()));
                    }
                });

                $("#Quantity").ForceNumericOnly();
                
                
                //Redeem Button Click Event Function
                $("#redeem-button").live("click",function(){
                    $("#profileupdate").validationEngine();
                    if ($("#redemptionquantity").dialog( "isOpen" ) !== true){
                        var ProductName = "<?php echo $_SESSION['RewardItemsInfo']['ProductName']; ?>";
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
                                                                                $("#failedmessage").html("<p>Error updating profile. Please try again.</p>");
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
                                        $("#MainForm").validationEngine();
                                    },
                                    close: function(event, ui) {
                                        $("#MainForm").validationEngine('hideAll');
                                    },
                                    width: 550,
                                    title: "Redeem Item"
                                }).parent().appendTo($("#MainForm"));
                        } else {
                            $("#redemptionquantity").dialog({
                            modal: true,
                            buttons: {
                                "Submit": function(){
                                    <?php unset($_SESSION["PreviousRedemption"]); ?>
                                    $(this).dialog('close');
                                    $("#MainForm").submit();
                                },
                                "Cancel": function(){
                                    $("#Quantity").val("");
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
                            title: "Redeem Item"
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
   <?php if(count($itemDetails) != 0){ ?>
    <div class="membership-inner-wrapper">
        <div class="row-fluid test">
            <div class="span7">
                <div class="limited-ribbon-full"></div>
                <img src="images/slider/membership_innerpages/product_image_full.jpg"></div>
            <div class="span5">
                 <div style="background-color:#cecece; text-align:center; padding: 20px 30px;">
                     <h1><?php echo number_format($_SESSION['RewardItemsInfo']['Points'], 2, ".", ",") ?></h1>
                 </div>
                <div class="miw-product-wrapper" style="padding:14px 30px;">
                    <div class="miw-product-name" style="padding:6px 0;"><h4><?php echo $_SESSION['RewardItemsInfo']['ProductName']; ?></h4></div>
                     <div class="miw-product-desc" style="font-size:12px; line-height: 12px;">
                        <?php echo $itemDetails[0]["DetailsOneA"]; ?>
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
            <div class="row-fluid">
                <div class="span7">
                    <h3><?php echo $itemDetails[0]["HeaderOne"]; ?></h3>
                    <hr>
                    <p>
                        <?php echo $itemDetails[0]["DetailsOneA"]; ?>
                    </p>
                    <p>
                        <?php echo $itemDetails[0]["DetailsOneB"]; ?>
                    </p>
                    <p>
                        <?php echo $itemDetails[0]["DetailsOneC"]; ?>                                                       
                    </p>
                </div>
                <div class="span5">
                    <h3><?php echo $itemDetails[0]["HeaderTwo"]; ?></h3>
                    <hr>
                        <strong><?php echo $itemDetails[0]["DetailsTwoA"]; ?> </strong>
                        <p>
                            <?php echo $itemDetails[0]["DetailsTwoB"]; ?>
                        </p>
                        <p>
                            <?php echo $itemDetails[0]["DetailsTwoC"]; ?>
                        </p>
                </div>
            </div>
            <br>
            <div class="row-fluid">
                <div class="span12">
                    <h3><?php echo $itemDetails[0]["HeaderThree"]; ?></h3>
                    <hr>
                     <p>
                         <?php echo $itemDetails[0]["DetailsThreeA"]; ?>
                    </p>
                    <p>
                        <?php echo $itemDetails[0]["DetailsThreeB"]; ?>
                    </p>
                    <p>
                        <?php echo $itemDetails[0]["DetailsThreeC"]; ?>                                                          
                    </p>                             
                </div>
            </div>
        </div>
        <?php } ?>
        <!--popup dialog box for redemption-->
        <div id="redemptionquantity" style="display:none;">
            <?php echo $hdnMemberInfoID; ?>
            <?php echo $hdnItemName; ?>
            <?php echo $hdnItemPoints; ?>
            <?php echo $hdnTotalItemPoints; ?>
            <?php echo $hdnCardNumber; ?>
            Item Name: <span id="ItemName"></span><br/>
            Points per Item: <span id="ItemPoints"></span><br/><br/>
            Please enter quantity to be redeemed. <?php echo $txtQuantity; ?><br/>
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
                        <td style="padding-bottom: 5px;"><?php echo $cardNumber; ?></td>
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
                        <td id="profileinfo-td-label">City: </td>
                        <td><?php echo $cboCityID; ?></td>
                    </tr>
                    <tr>
                        <td id="profileinfo-td-label">Region: </td>
                        <td><?php echo $cboRegionID; ?></td>
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
