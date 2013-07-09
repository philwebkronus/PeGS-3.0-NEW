<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("init.inc.php");

$pagetitle = "Membership";

$customjavascripts[] = "js/jquery.tinycarousel.min.js";
$stylesheets[] = "css/tinycarousel.css";

$useCustomHeader = true;
$showcouponredemptionwindow = false;
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
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");

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

//Load Core
App::LoadCore('ErrorLogger.php');

$_Rewards = new Rewards();
$_RewardItems = new RewardItems();
$cities = new Cities();
$regions = new Regions();
$_Log = new AuditTrail();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";


$arrcities = $cities->SelectAll();
$actualcities = "";
for ($i = 0; $i < count($arrcities); $i++)
{
    $city = $arrcities[$i];
    $actualcities[$city["CityID"]] = $city["CityName"];
}


$arrregions = $regions->SelectAll();
$actualregions = "";
for ($i = 0; $i < count($arrregions); $i++)
{
    $region = $arrregions[$i];
    $actualregions[$region["RegionID"]] = $region["RegionName"];
}

$fproc = new FormsProcessor();
/**
 * Carousel Controls 
 */
$viewbutton = new Button("viewbutton", "viewbutton", "View More");
$viewbutton->CssClass = "btnDefault roundedcorners";

$txtQuantity = new TextBox('txtQuantity', 'txtQuantity', 'Quantity ');
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

$txtRedeemMiddleName = new TextBox("MiddleName", "MiddleName", "MiddleName");
$txtRedeemMiddleName->ShowCaption = false;
$txtRedeemMiddleName->Length = 30;
$txtRedeemMiddleName->Size = 15;
$txtRedeemMiddleName->CssClass = "validate[custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtRedeemMiddleName);

$txtRedeemLastName = new TextBox("LastName", "LastName", "Last Name: ");
$txtRedeemLastName->ShowCaption = false;
$txtRedeemLastName->Length = 30;
$txtRedeemLastName->Size = 15;
$txtRedeemLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtRedeemLastName);

$txtRedeemNickName = new TextBox("NickName", "NickName", "NickName");
$txtRedeemNickName->ShowCaption = false;
$txtRedeemNickName->Length = 30;
$txtRedeemNickName->Size = 15;
$txtRedeemNickName->CssClass = "validate[custom[onlyLetterSp]]";
$fproc->AddControl($txtRedeemNickName);

$txtRedeemMobileNumber = new TextBox("MobileNumber", "MobileNumber", "Mobile Number: ");
$txtRedeemMobileNumber->ShowCaption = false;
$txtRedeemMobileNumber->Length = 30;
$txtRedeemMobileNumber->Size = 15;
$txtRedeemMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtRedeemMobileNumber);

$txtRedeemEmail = new TextBox("Email", "Email", "Email: ");
$txtRedeemEmail->ShowCaption = false;
$txtRedeemEmail->Length = 30;
$txtRedeemEmail->Size = 15;
$txtRedeemEmail->CssClass = "validate[required, custom[email]]";
$fproc->AddControl($txtRedeemEmail);

$dtRedeemBirthDate = new DatePicker("Birthdate", "Birthdate", "Birth Date: ");
$dtRedeemBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtRedeemBirthDate->MinDate = $dsmindate->CurrentDate;
//$dtBirthDate->SelectedDate = $dsmaxdate->PreviousDate;
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

$hdnMID = new Hidden("MID", "MID", "MID: ");
$hdnMID->ShowCaption = true;
$hdnMID->Text = "";
$fproc->AddControl($hdnMID);

App::LoadModuleClass("Membership", "Cities");
$_Ref_city = new Cities();
$arrRef_city = $_Ref_city->SelectAll();
$arrRef_cityList = new ArrayList($arrRef_city);
$cboCityID = new ComboBox("CityID", "CityID", "City: ");
$cboCityID->ShowCaption = false;
$cboCityID->CssClass = 'validate[required]';
$cboCityID->DataSourceText = "CityName";
$cboCityID->DataSourceValue = "CityID";
$cboCityID->DataSource = $arrRef_cityList;
$cboCityID->DataBind();
$fproc->AddControl($cboCityID);

App::LoadModuleClass("Membership", "Regions");
$_Ref_region = new Regions();
$arrRef_region = $_Ref_region->SelectAll();
$arrRef_regionList = new ArrayList($arrRef_region);
$cboRegionID = new ComboBox("RegionID", "RegionID", "Region: ");
$cboRegionID->ShowCaption = false;
$cboRegionID->CssClass = 'validate[required]';
$cboRegionID->DataSourceText = "RegionName";
$cboRegionID->DataSourceValue = "RegionID";
$cboRegionID->DataSource = $arrRef_regionList;
$cboRegionID->DataBind();
$fproc->AddControl($cboRegionID);

$hdnMemberInfoID = new Hidden("MemberInfoID", "MemberInfoID", "MemberInfoID: ");
$hdnMemberInfoID->ShowCaption = true;
$hdnMemberInfoID->Text = "";
$fproc->AddControl($hdnMemberInfoID);

$hdnRewardItemID = new Hidden("RewardItemID", "RewardItemID", "RewardItemID: ");
$hdnRewardItemID->ShowCaption = true;
$hdnRewardItemID->Text = "";
$fproc->AddControl($hdnRewardItemID);

if (!isset($_SESSION["MemberInfo"]))
{
    include_once("controller/logincontroller.php");
}

if (isset($_SESSION["MemberInfo"]))
{
    $MID = $_SESSION["MemberInfo"]["Member"]["MID"];
    include_once("controller/profilecontroller.php");
    $txtRedeemFirstName->Text = $arrmemberinfo["FirstName"];
    $txtRedeemLastName->Text = $arrmemberinfo["LastName"];
    $txtRedeemAddress1->Text = $arrmemberinfo["Address1"];
    $cboCityID->SetSelectedValue($arrmemberinfo["CityID"]);
    $cboRegionID->SetSelectedValue($arrmemberinfo["RegionID"]);
    $txtRedeemMobileNumber->Text = $arrmemberinfo["MobileNumber"];
    $hdnMID->Text = $MID;
    $txtRedeemEmail->Text = $arrmemberinfo["Email"];
    $dtRedeemBirthDate->SelectedDate = $arrmemberinfo["Birthdate"];
    $hdnMemberInfoID->Text = $arrmemberinfo["MemberInfoID"];
}

$cardtypeid = "";
if (isset($_SESSION['MemberInfo']))
{
    $cardtypeid = $_SESSION["MemberInfo"]["CardTypeID"];
}

$arrRewardItems = $_RewardItems->getActiveRewardItemsByCardType($cardtypeid);

if (!$fproc->IsFormProcessed)
{
    $fproc->ProcessForms();
}

if ($fproc->IsPostBack)
{
//    App::Pr($hdnMID->SubmittedValue);
//    App::Pr($hdnRewardItemID->SubmittedValue);
//    App::Pr($txtQuantity->SubmittedValue);
    if (!(isset($_SESSION["PreviousRemdeption"])) && $hdnMID->SubmittedValue != "" && $hdnRewardItemID->SubmittedValue != "" && $txtQuantity->SubmittedValue != "")
    {
        $sendemail = false;

        $redeemMID = $hdnMID->SubmittedValue;
        $redeemRewardITemID = $hdnRewardItemID->SubmittedValue;
        $redeemQuantity = $txtQuantity->SubmittedValue;
        $_RewardOffers = new RewardOffers();
        $arrRedeemItem = $_RewardOffers->getRewardItemDetailsByRewardItemID($redeemRewardITemID, $cardtypeid);
        $redeemItem = $arrRedeemItem[0];
        $pointsPerItem = $redeemItem["RequiredPoints"];
        $totalRedeemPoints = $pointsPerItem * $redeemQuantity;

        $CommonPDOConnection = null;

        $_MemberCards = new MemberCards();
        $_MemberCards->StartTransaction();
        $_MemberCards->Redeem($redeemMID, $cardNumber, $totalRedeemPoints);
        $CommonPDOConnection = $_MemberCards->getPDOConnection();

        if (!App::HasError())
        {
            $_CouponRedemptionLogs = new CouponRedemptionLogs();
            $_CouponRedemptionLogs->setPDOConnection($CommonPDOConnection);
            $_CouponRedemptionLogs->Redeem($redeemMID, $redeemRewardITemID, $redeemQuantity, 1, 1);
            $CouponRedemptionLogID = $_CouponRedemptionLogs->LastInsertID;
        }

        if (!App::HasError())
        {
            $_RaffleCoupons = new RaffleCoupons();
            $_RaffleCoupons->setPDOConnection($CommonPDOConnection);
            $_RaffleCoupons->Redeem($CouponRedemptionLogID, $redeemRewardITemID, $redeemQuantity);
        }

        if (App::HasError())
        {
            $_MemberCards->RollBackTransaction();
            //App::SetErrorMessage($errormessage);
        }
        else
        {
            $_SESSION["PreviousRemdeption"] = $CouponRedemptionLogID;
            $_MemberCards->CommitTransaction();
            
            $hdnRewardItemID->Text = "";

            $redemptioninfo = $_RaffleCoupons->getCouponRedemptionInfo($CouponRedemptionLogID);

            /* Prepare player and redemption information for display */
            $playername = trim($arrmemberinfo["FirstName"]) . " " . trim($arrmemberinfo["LastName"]);
            $address = $arrmemberinfo["Address1"];
            $cityid = $arrmemberinfo["CityID"];
            $regionid = $arrmemberinfo["RegionID"];
            $birthdate = $arrmemberinfo["Birthdate"];
            $email = $arrmemberinfo["Email"];
            $contactno = $arrmemberinfo["MobileNumber"];
            if (isset($site["SiteName"]))
            {
                $sitecode = $site["SiteName"];
            }
            else
            {
                $sitecode = "Website";
            }
            $arrcouponredemptionloginfo = $redemptioninfo[0];
            $mincouponnumber = str_pad($arrcouponredemptionloginfo["MinCouponNumber"], 7, "0", STR_PAD_LEFT);
            $maxcouponnumber = str_pad($arrcouponredemptionloginfo["MaxCouponNumber"], 7, "0", STR_PAD_LEFT);

            if ($arrcouponredemptionloginfo["MinCouponNumber"] == $arrcouponredemptionloginfo["MaxCouponNumber"])
            {
                $couponseries = $mincouponnumber;
            }
            else
            {
                $couponseries = $mincouponnumber . " - " . $maxcouponnumber;
            }

            if ($arrcouponredemptionloginfo["MaxCouponNumber"] == 0)
            {
                $actualquantity = 0;
                $couponseries = "";
                $showcouponredemptionwindow = false;
                App::SetErrorMessage("Insufficient Raffle Coupons. Please Try Again Later");
            }
            else
            {
                $showcouponredemptionwindow = true;
                $actualquantity = $maxcouponnumber - $mincouponnumber + 1;
            }

            $serialnumber = str_pad($CouponRedemptionLogID, 7, "0", STR_PAD_LEFT) . "A" . $_RaffleCoupons->getMod10($mincouponnumber) . "B" . $_RaffleCoupons->getMod10($maxcouponnumber);
            $sendemail = true;
            $redemptiondate = date("F j, Y, g:i a");

            $checkstring = $couponseries . $actualquantity . $cardNumber . $playername . date("F j, Y", strtotime($birthdate)) . $email . $contactno;
            $checksum = crc32($checkstring);


            if ($showcouponredemptionwindow == true)
            {
                $imagesdir = str_replace(URL::CurrentPage(), "admin/loyalty/images/", curPageURL());
                App::LoadCore("File.class.php");
                $filename = dirname(__FILE__) . "/admin/template/couponredemptiontemplate.php";
                $fp = new File($filename);
                $emailmessage = $fp->ReadToEnd();
                $emailmessage = str_replace('$playername', $playername, $emailmessage);
                $emailmessage = str_replace('$address', $address, $emailmessage);
                $emailmessage = str_replace('$couponseries', $couponseries, $emailmessage);
                $emailmessage = str_replace('$quantity', $actualquantity, $emailmessage);
                $emailmessage = str_replace('$sitecode', $sitecode, $emailmessage);
                $emailmessage = str_replace('$redemptiondate', $redemptiondate, $emailmessage);
                $emailmessage = str_replace('$cardno', $cardNumber, $emailmessage);
                $emailmessage = str_replace('$birthdate', date("F j, Y", strtotime($birthdate)), $emailmessage);
                $emailmessage = str_replace('$email', $email, $emailmessage);
                $emailmessage = str_replace('$contactno', $contactno, $emailmessage);
                $emailmessage = str_replace('$checksum', $checksum, $emailmessage);
                $emailmessage = str_replace('$serialnumber', $serialnumber, $emailmessage);
                $emailmessage = str_replace('$actualcity', $actualcities[1], $emailmessage);
                $emailmessage = str_replace('$actualregion', $actualregions[1], $emailmessage);
                $emailmessage = str_replace('$imagesdir', $imagesdir, $emailmessage);

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
                //$pm->AddAddress($email, $playername);
                $pm->Body = $emailmessage;
                $pm->IsHTML(true);
                
                $pm->From = "loyaltyadmin@pagcoregames.com";
                $pm->FromName = "Loyalty Admin";
                $pm->Host = "localhost";
                $pm->Subject = "Loyalty Coupon Redemption";
                //$pm->Send();
            }

            $memberinfo = $_MemberInfo->getMemberInfo($MID);
            $arrmemberinfo = $memberinfo[0];
            $points = $_MemberCards->getMemberPoints($cardNumber);
            $currentPoints = $points[0]['CurrentPoints'];
            $lifetimePoints = $points[0]['LifetimePoints'];
            $bonusPoints = $points[0]['BonusPoints'];
            $redeemedPoints = $points[0]['RedeemedPoints'];
        }
    }
}

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

function reloadParent()
{
    echo "<script>parent.window.location.href='index.php';</script>";
}

$canRedeem = false;
if (isset($_SESSION["MemberInfo"]))
{
    $canRedeem = true;
}
?>
<?php include "header.php"; ?> 
<script type="text/javascript" language="javascript">
        
    $(document).ready(
    function() 
    {
        
<?php
if ($showcouponredemptionwindow == true)
{
    ?>             
                if ($("#couponmessagebody").dialog( "isOpen" ) !== true)
                {
                    $("#couponmessagebody").dialog({
                                                                                                                                                                                                                                                
                        modal: true,
                        buttons: {
                            "Print" : function() 
                            {
                                window.print();
                                                                                                                                                                                                                                    
                            },
                            "Close": function() {
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

        function InitializeDialog($element, page) {
            $element.dialog({
                modal: true,
                autoOpen: true,
                width: 600,
                //height: auto,
                title: "Reward Item",
                closeOnEscape: true,
                position: "center",

                buttons: {
                    "<?php echo $canRedeem ? "REDEEM ITEM" : "LOGIN TO REDEEM"; ?>": function() {
<?php if (!$canRedeem)
{ ?>
                            $(this).dialog("close");
    <?php
}
else
{
    ?>
                                $("#profileupdate").validationEngine();
                                        
                                    
                                if ($("#redemptionquantity").dialog( "isOpen" ) !== true)
                                {
                                    $("#redemptionquantity").dialog({
                                                                                                                                                                                                                        
                                        modal: true,
                                        buttons: {
                                            "Next" : function() 
                                            {

                                                if ($("#MainForm").validationEngine('validate'))
                                                {
                                                    $(this).dialog("hide");
                                                    //itemid = $(this).attr('rewarditemid');
                                                    //$("#RewardItemID").val(itemid);
                                                    if ($("#profileinfo").dialog( "isOpen" ) !== true)
                                                    {
                                                        //$("#dialog:ui-dialog").dialog("destroy");
                                                        $("#profileinfo").dialog({
                                            
                                                            modal: true,
                                                            width: 550,
                                                            height: 'auto',
                                                            position: 'center',
                                            
                                                            buttons: {
                                                                "Submit": function() 
                                                                {
                                                                    if ($("#profileupdate").validationEngine('validate'))
                                                                    {
                                                                        //alert($('#profileupdate').serialize());
                                                                        $.post("ajaxhandler.php", 
                                                                        { 'Module' : 'Membership'
                                                                            , 'Class' : 'MemberInfo' 
                                                                            , 'Method' : 'updateProfileForCouponAjax' 
                                                                            , 'MethodArgs': $('#profileupdate').serialize()
                                                                        },
                                                                        function(data) 
                                                                        {
                                                                            var datalength = data.length;
                                                                            if (data != "Profile Updated Successfully.")
                                                                            {
                                                                                if(data == 'Session Expired'){
                                                                                    alert(data);
                                                                                    window.location.href = "index.php";
                                                                                }
                                                                                else{
                                                                                    alert("Error updating profile. Please try again.");
                                                                                }
                                                                            }
                                                                            else
                                                                            {
                                                                                $('#MainForm').submit();
                                                                            }
                                                                        }, "json");
                                                                    }
                                                                },
                                                
                                                                "Cancel" : function() 
                                                                {
                                                                    $("#RewardItemID").val('');
                                                                    $(this).dialog("close");
                                                                }
                                                
                                                            },
                                                            open: function(event, ui) 
                                                            {
                                                                $("#profileupdate").validationEngine();
                                                            },
                                                            close: function(event, ui) 
                                                            {
                                                                $("#profileupdate").validationEngine('hideAll');
                                                            },
                                                            title: 'Update Account Information'
                                                        }).parent().appendTo($("#profileupdate"));
                                                    }
                                                }
                                            },
                                            "Cancel": function() {
                                                $(this).dialog("close");
                                            }
                                        },
                                        open: function(event, ui) {
                                            $("#MainForm").validationEngine();
                                            //$("#frmRedemption").hide();
                                        },
                                        close: function(event, ui) {
                                            $("#MainForm").validationEngine('hideAll');
                                            //$("#frmRedemption").show();
                                        },
                                        width: 550,
                                        title: "Redeem Item"

                                    }).parent().appendTo($("#MainForm"));
                                }
<?php } ?>
                    }
                },

                open: function (event, ui) {
                    $element.load(page);                        
                }
                                                    
            });
        }

        jQuery.fn.ForceNumericOnly = function()
        {
            return this.each(function()
            {
                $(this).keydown(function(e)
                {
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
        
        function init()
        {
            $('#carousel').hide();
        }
        window.onload = init;
        
        $('#carousel').tinycarousel({ display: 3});
        $('#slider').tinycarousel(
        {
            interval: true,
            intervaltime: 3000,
            animation: true,
            duration: 1000
        });
        $('#slider').tinycarousel_start();
        $(".popup").click(function (e)
        {
            $("#RewardItemID").val($(this).attr("rewarditemid"));
            $("#ItemName").html($(this).attr("itemname"));
            $("#ItemPoints").html($(this).attr("itempoints"));
            InitializeDialog($("#detailbox"), $(this).attr("href"));
            e.preventDefault();
            $("#detailbox").dialog("open");
            $("#txtQuantity").val("0");
            
        });
    
        $('#slider .viewmore').click(function()
        {
            $("#slider").hide(); 
            $("#carousel").show();
            $("#carousel").css("display","inline");
        });
        
        defaultquantity = "0";
        $("#txtQuantity").click(function()
        {
            if ($("#txtQuantity").val() == defaultquantity)
            {
                $("#txtQuantity").val("");
            }
        });
        
        $("#txtQuantity").keyup(function()
        {
            $("#txtQuantity").change();
        });
       
        $("#txtQuantity").blur(function()
        {
            $("#txtQuantity").change();
        });
        $("#txtQuantity").change(function()
        {
            if ($("#txtQuantity").val() == "" || $("#txtQuantity").val() == defaultquantity)
            {
                $("#txtQuantity").val(defaultquantity);
            }
            else
            {
                $("#TotalItemPoints").html('Total Points: ' + parseInt($("#ItemPoints").html()) * parseInt($("#txtQuantity").val()))
            }
        });
        
        $("#txtQuantity").ForceNumericOnly();
    });
    
    
</script>
<div id="redemptionquantity" style="display:none;">
    <?php echo $hdnMID; ?>
    <?php echo $hdnRewardItemID; ?>
    Item Name: <span id="ItemName"></span><br/>
    Points per Item: <span id="ItemPoints"></span><br/><br/>
    Please enter quantity to be redeemed. <?php echo $txtQuantity; ?><br/>
    <span id="TotalItemPoints"></span>
</div>
</form>
<form name="profileupdate" ID="profileupdate">
    <div id="profileinfo" class="profileinfo" style="display:none; font-size: 10pt; text-align: left;">
        <!-- strong>e-Coupon Form<br/>
            Promotion Title: Luxury Knows No Limits (Omega Raffle)<br/>
            Promo Period: May to July, 2013<br/>
        </strong>
        <hr width="500" / -->
        Card Number: <?php 
        if(!isset($cardNumber)){
            $cardNumber = '';
        }
        echo $cardNumber; ?><br/>
        <br/>
        <?php echo $hdnMemberInfoID; ?>
        First Name: <?php echo $txtRedeemFirstName; ?><br/>
        Last Name: <?php echo $txtRedeemLastName; ?><br/>
        Birth Date: <?php echo $dtRedeemBirthDate; ?><br/>
        Address: <?php echo $txtRedeemAddress1; ?><br/>
        City: <?php echo $cboCityID; ?><br/>
        Region: <?php echo $cboRegionID; ?><br/>
        Email: <?php echo $txtRedeemEmail; ?><br/>
        Mobile Number: <?php echo $txtRedeemMobileNumber; ?><br/>
        <br/>
        <input type="checkbox" id="TermsAndConditions" class="validate[required]" name="TermsAndConditions"><label for="TermsAndConditions" class="formlabel">Player has read and accepted the promo mechanics and terms and conditions</label>
        <br/><br/><br/><br/>
    </div>

    <?php
    // if redemption is successful, show redemption window
    if ($showcouponredemptionwindow == true)
    {
        echo $emailmessage;
    }
    ?>
</form>
<div id="main"> 
    <table>
        <tr>
            <td>
                <div id="slider">
                    <a class="buttons prev" href="#">left</a>
                    <div class="viewport">
                        <ul class="overview">
                            <?php foreach ($arrRewardItems as $rewarditem)
                            { ?>
                                <li><img src ="images/rewarditems/<?php echo $rewarditem['ImagePath']; ?>" height="350" width="600" /></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <a class="buttons next" href="#">right</a>
                    <div id="moreitems" align="right"><input class="yellow-btn viewmore" type="button" name="more" value="View More" /></div>
                </div>

                <!--Carousel-->
                <div id="carousel">
                    <a class="buttons prev" href="#">left</a>
                    <div class="viewport">
                        <ul class="overview">
                            <?php foreach ($arrRewardItems as $rewarditem)
                            { ?>
                                <a class="popup" rewarditemid="<?php echo $rewarditem["RewardItemID"]; ?>" value="Redeem" itemname="<?php echo $rewarditem["RewardItemName"]; ?>" promoname="<?php echo $rewarditem["PromoName"]; ?>" itempoints="<?php echo $rewarditem["Points"]; ?>" href="imageinfo.php?PathID=<?php echo $rewarditem["RewardItemID"]; ?>&CardTypeID=<?php echo $cardtypeid; ?>">
                                    <li><center><img src ="images/rewarditems/<?php echo $rewarditem['ImagePath']; ?>" /></center>
                                    <p><strong><u><?php echo $rewarditem['RewardItemName']; ?></u></strong></p></li></a>
                            <?php } ?>
                        </ul>
                    </div>
                    <a class="buttons next" href="#">right</a>
                </div>
                <!-- End Carousel Wrapper -->
            </td>
            <td>
                <div id="rightcol">
                    <?php
                    if (isset($_SESSION["MemberInfo"]))
                    {
                        include('profile.php');
                    }
                    else
                    {
                        include('login.php');
                    }
                    ?>
                    <div id="home-latest-news">
                        <h3>Latest Events</h3>
                        <div id="home-latest-wrapper">                                    
                            <div>&#187; <a href="http://www.egamescasino.ph/events/luxury-knows-no-limits-raffle-promo/">Luxury Knows No Limits Raffle</a></div>
                            <div>&#187; <a href="http://www.egamescasino.ph/events/12th-e-games-operators-meeting/">Bar Tour Activations</a></div>
                        </div>
                    </div>
                </div><!-- #home-login-box -->
                <div id="social-buttons-container" style="text-align:right;">
                    <div class="row-fluid">

                        <div class="span4 pull-right">
                            <a href="http://www.twitter.com"><img src="http://staging.pegs.com/wp-content/themes/pegs_theme/img/twitter_icon.png" alt="Twitter" title="Twitter"></a>
                            <a href="http://www.facebook.com"><img src="http://staging.pegs.com/wp-content/themes/pegs_theme/img/fb_icon.png" alt="Facebook" title="Facebook"></a>
                        </div>

                    </div>

                </div><!-- #social-buttons-container --> 
                </div>
                <!-- End Login Wrapper -->
            </td>
        </tr>
    </table>

    <div id="detailbox"></div>
    <?php
    // if redemption is successful, show redemption window
    if ($showcouponredemptionwindow == true)
    {
        echo $emailmessage;
    }
    ?>
</div>
<!--  For Javascript Alert Dialog (Errors)  -->        
<?php
    if(isset($_GET['mess']))
       {
        $msg = $_GET['mess'];
?>
<script type="text/javascript" language="javascript">
    $(document).ready(function(){
        <?php echo "alert('".$msg."');"; ?>
    });
</script>
<?php
      }
?>
<?php include "footer.php"; ?>
