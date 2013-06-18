<?php
/*
 * @author : owliber
 * @date : 2013-05-20
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Redemption";
$currentpage = "Redemption";

App::LoadModuleClass('Loyalty', 'RewardItems');
App::LoadModuleClass('Loyalty', 'RewardOffers');
App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass('Loyalty', 'CouponRedemptionLogs');
App::LoadModuleClass('Loyalty', 'RaffleCoupons');
App::LoadModuleClass('Membership', 'Cities');
App::LoadModuleClass('Membership', 'Regions');
App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass('Kronus', 'Sites');

App::LoadCore("PHPMailer.class.php");

App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("ComboBox");
App::LoadControl("CheckBox");
App::LoadControl("DatePicker");
App::LoadControl("Hidden");

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

/* Initialize models */
$_RewardItems = new RewardItems();
$_MemberInfo = new MemberInfo();
$cities = new Cities();
$regions = new Regions();

/* Initialize forms */
$fproc = new FormsProcessor();

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

/* Initialize Controls */
$txtQuantity = new TextBox('txtQuantity', 'txtQuantity', 'Quantity ');
$txtQuantity->ShowCaption = false;
$txtQuantity->CssClass = 'validate[required,custom[integer],min[1]]';
$txtQuantity->Style = 'color: #666';
$txtQuantity->Length = 5;
$txtQuantity->Size = 5;
$txtQuantity->Text = "0";
$fproc->AddControl($txtQuantity);

$txtFirstName = new TextBox("FirstName", "FirstName", "First Name: ");
$txtFirstName->ShowCaption = true;
$txtFirstName->Length = 30;
$txtFirstName->Size = 15;
$txtFirstName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtFirstName);

$txtMiddleName = new TextBox("MiddleName", "MiddleName", "MiddleName");
$txtMiddleName->ShowCaption = true;
$txtMiddleName->Length = 30;
$txtMiddleName->Size = 15;
$txtMiddleName->CssClass = "validate[custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtMiddleName);

$txtLastName = new TextBox("LastName", "LastName", "Last Name: ");
$txtLastName->ShowCaption = true;
$txtLastName->Length = 30;
$txtLastName->Size = 15;
$txtLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtLastName);

$txtNickName = new TextBox("NickName", "NickName", "NickName");
$txtNickName->ShowCaption = true;
$txtNickName->Length = 30;
$txtNickName->Size = 15;
$txtNickName->CssClass = "validate[custom[onlyLetterSp]]";
$fproc->AddControl($txtNickName);

$txtMobileNumber = new TextBox("MobileNumber", "MobileNumber", "Mobile Number: ");
$txtMobileNumber->ShowCaption = true;
$txtMobileNumber->Length = 30;
$txtMobileNumber->Size = 15;
$txtMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtMobileNumber);

$txtEmail = new TextBox("Email", "Email", "Email: ");
$txtEmail->ShowCaption = true;
$txtEmail->Length = 30;
$txtEmail->Size = 15;
$txtEmail->CssClass = "validate[required, custom[email]]";
$fproc->AddControl($txtEmail);

$dtBirthDate = new DatePicker("Birthdate", "Birthdate", "Birth Date: ");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
//$dtBirthDate->SelectedDate = $dsmaxdate->PreviousDate;
$dtBirthDate->ShowCaption = true;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;
$fproc->AddControl($dtBirthDate);

$txtAddress1 = new TextBox("Address1", "Address1", "Address: ");
$txtAddress1->ShowCaption = true;
$txtAddress1->Length = 30;
$txtAddress1->Size = 15;
$txtAddress1->CssClass = "validate[required]";
$fproc->AddControl($txtAddress1);

$hdnMID = new Hidden("MID", "MID", "MID: ");
$hdnMID->ShowCaption = true;
$hdnMID->Text = "";
$fproc->AddControl($hdnMID);

$hdnMemberInfoID = new Hidden("MemberInfoID", "MemberInfoID", "MemberInfoID: ");
$hdnMemberInfoID->ShowCaption = true;
$hdnMemberInfoID->Text = "";
$fproc->AddControl($hdnMemberInfoID);

$hdnRewardItemID = new Hidden("RewardItemID", "RewardItemID", "RewardItemID: ");
$hdnRewardItemID->ShowCaption = true;
$hdnRewardItemID->Text = "";
$fproc->AddControl($hdnRewardItemID);

App::LoadModuleClass("Membership", "Cities");
$_Ref_city = new Cities();
$arrRef_city = $_Ref_city->SelectAll();
$arrRef_cityList = new ArrayList($arrRef_city);
$cboCityID = new ComboBox("CityID", "CityID", "City: ");
$cboCityID->ShowCaption = true;
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
$cboRegionID->ShowCaption = true;
$cboRegionID->CssClass = 'validate[required]';
$cboRegionID->DataSourceText = "RegionName";
$cboRegionID->DataSourceValue = "RegionID";
$cboRegionID->DataSource = $arrRef_regionList;
$cboRegionID->DataBind();
$fproc->AddControl($cboRegionID);

/* Let the file below process the forms */
include_once("controller/cardsearchcontroller.php");

//$fproc->ProcessForms();
$arrmemberinfo = null;

if (isset($_SESSION['CardInfo']["MID"]))
{
    $MID = $_SESSION['CardInfo']["MID"];
    $CardTypeID = $_SESSION['CardInfo']["CardTypeID"];
    $arrmemberinfo = $_MemberInfo->getMemberInfo($MID);
    $arrmemberinfo = $arrmemberinfo[0];
    $txtFirstName->Text = $arrmemberinfo["FirstName"];
    $txtLastName->Text = $arrmemberinfo["LastName"];
    $txtAddress1->Text = $arrmemberinfo["Address1"];
    $cboCityID->SetSelectedValue($arrmemberinfo["CityID"]);
    $cboRegionID->SetSelectedValue($arrmemberinfo["RegionID"]);
    $txtMobileNumber->Text = $arrmemberinfo["MobileNumber"];
    $hdnMID->Text = $MID;
    $txtEmail->Text = $arrmemberinfo["Email"];
    $dtBirthDate->SelectedDate = $arrmemberinfo["Birthdate"];
    $hdnMemberInfoID->Text = $arrmemberinfo["MemberInfoID"];

    /* Get all active promo items for the given card type */
    $arrRewardItems = $_RewardItems->getActiveRewardItemsByCardType($CardTypeID);
    $showcardinfo = true;
}

if (isset($_SESSION['userinfo']['SiteID']))
{
    $_Sites = new Sites();
    $arrsites = $_Sites->getSite($_SESSION['userinfo']['SiteID']);
    $site = $arrsites[0];
}

if ($fproc->IsPostBack)
{
    if (!(isset($_SESSION["PreviousRemdeption"])) && $hdnMID->SubmittedValue != "" && $hdnRewardItemID->SubmittedValue != "" && $txtQuantity->SubmittedValue != "")
    {
        $sendemail = false;

        $redeemMID = $hdnMID->SubmittedValue;
        $redeemRewardITemID = $hdnRewardItemID->SubmittedValue;
        $redeemQuantity = $txtQuantity->SubmittedValue;
        $_RewardOffers = new RewardOffers();
        $arrRedeemItem = $_RewardOffers->getRewardItemDetailsByRewardItemID($redeemRewardITemID, $CardTypeID);
        $redeemItem = $arrRedeemItem[0];
        $pointsPerItem = $redeemItem["RequiredPoints"];
        $totalRedeemPoints = $pointsPerItem * $redeemQuantity;

        $CommonPDOConnection = null;

        $_MemberCards = new MemberCards();
        $_MemberCards->StartTransaction();
        $_MemberCards->Redeem($redeemMID, $CardNumber, $totalRedeemPoints);
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
            $playername = $arrmemberinfo["FirstName"] . " " . $arrmemberinfo["LastName"];
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

            $checkstring = $couponseries . $actualquantity . $CardNumber . $playername . date("F j, Y", strtotime($birthdate)) . $email . $contactno;
            $checksum = crc32($checkstring);


            if ($showcouponredemptionwindow == true)
            {
                $imagesdir = str_replace(URL::CurrentPage(), "loyalty/images/", curPageURL());
                App::LoadCore("File.class.php");
                $filename = dirname(__FILE__) . "/template/couponredemptiontemplate.php";
                $fp = new File($filename);
                $emailmessage = $fp->ReadToEnd();
                $emailmessage = str_replace('$playername', $playername, $emailmessage);
                $emailmessage = str_replace('$address', $address, $emailmessage);
                $emailmessage = str_replace('$couponseries', $couponseries, $emailmessage);
                $emailmessage = str_replace('$quantity', $actualquantity, $emailmessage);
                $emailmessage = str_replace('$sitecode', $sitecode, $emailmessage);
                $emailmessage = str_replace('$redemptiondate', $redemptiondate, $emailmessage);
                $emailmessage = str_replace('$cardno', $CardNumber, $emailmessage);
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

            if (isset($_SESSION['CardInfo']))
            {

                $CardNumber = $_SESSION['CardInfo']["CardNumber"];
                $MID = $_SESSION['CardInfo']["MID"];
                $siteName = "";
                $transDate = "";
                $txtSearch->Text = $CardNumber;
                $arrCards = $_Cards->getCardInfo($CardNumber);
                $arrTransactions = $_CardTransactions->getLastTransaction($CardNumber);


                $cardinfo = $arrCards[0];
                unset($arrCards);
                $CardTypeID = $cardinfo["CardTypeID"];
                $_SESSION['CardInfo']["CardTypeID"] = $CardTypeID;
                $loyaltyinfo = $_MemberCards->getActiveMemberCardInfo($MID);
                $loyaltyinfo = $loyaltyinfo[0];
                $currentPoints = $loyaltyinfo['CurrentPoints'];
                $lifetimePoints = $loyaltyinfo['LifetimePoints'];
                $bonusPoints = $loyaltyinfo['BonusPoints'];
                $redeemedPoints = $loyaltyinfo['RedeemedPoints'];
                $loyaltyinfo['CardTypeID'] = $CardTypeID;

                if (count($arrTransactions) > 0)
                {
                    $site = $_Sites->getSite($arrTransactions[0]['SiteID']);
                    $siteName = $site[0]['SiteName'];
                    $transDate = date('M d, Y ', strtotime($arrTransactions[0]['TransactionDate']));
                }

                $loyaltyinfo["LastTransactionDate"] = $transDate;
                $loyaltyinfo["LastSitePlayed"] = $siteName;
            }
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
?>
<?php include('header.php'); ?>
<script language="javascript" type="text/javascript">
    $(document).ready(
    function() 
    {
        
        $("#frmRedemption").validationEngine();
        $("#profileupdate").validationEngine();
        
        $('.redeemitem').click(function()
        {
            //alert($(this).attr("rewarditemid"));
            $("#ItemName").html($(this).attr("itemname"));
            $("#ItemPoints").html($(this).attr("itempoints"));
            $("#txtQuantity").val("0");
            $("#RewardItemID").val($(this).attr("rewarditemid"));
            
            if ($("#redemptionquantity").dialog( "isOpen" ) !== true)
            {
                $("#redemptionquantity").dialog({
                                                                                                                                                                                                
                    modal: true,
                    buttons: {
                        "Next" : function() 
                        {
                            //$("#frmRedemption").validationEngine();
                            //$('#frmRedemption').submit();
                            if ($("#frmRedemption").validationEngine('validate'))
                            {
                                $(this).dialog("hide");
                                itemid = $(this).attr('itemid');
                                $("#itemid").val(itemid);
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
                                                            alert("Error updating profile. Please try again.");
                                                        }
                                                        else
                                                        {
                                                            $('#frmRedemption').submit();
                                                        }
                                                    }, "json");
                                                }
                                            },
                        
                                            "Cancel" : function() 
                                            {
                                                $("#itemid").val('');
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
                        $("#frmRedemption").validationEngine();
                        //$("#frmRedemption").hide();
                    },
                    close: function(event, ui) {
                        $("#frmRedemption").validationEngine('hideAll');
                        //$("#frmRedemption").show();
                    },
                    width: 550,
                    title: "Redeem Item"

                }).parent().appendTo($("#frmRedemption"));
            }
        });
        
        jQuery.fn.ForceNumericOnly =
            function()
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
        
        $("#txtQuantity").ForceNumericOnly();
        
        defaultquantity = "0";
        $("#txtQuantity").click(function(){
            if ($("#txtQuantity").val() == defaultquantity)
            {
                $("#txtQuantity").val("");
            }
        });
        $("#txtQuantity").keyup(function(){
            $("#txtQuantity").change();
        });
        $("#txtQuantity").blur(function(){
            $("#txtQuantity").change();
        });
        $("#txtQuantity").change(function(){
            if ($("#txtQuantity").val() == "" || $("#txtQuantity").val() == defaultquantity)
            {
                $("#txtQuantity").val(defaultquantity);
            }
            else
            {
                $("#TotalItemPoints").html('Total Points: ' + parseInt($("#ItemPoints").html()) * parseInt($("#txtQuantity").val()))
            }
            
            
        });
        
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
    });
    
</script>
<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">
            <?php include('cardsearch.php'); ?>

            <form name="frmRedemption" id="frmRedemption" method="post" action="" />
            <?php if (isset($arrRewardItems) && count($arrRewardItems) > 0)
            { ?>
                <br/>
                <span class="title">Redeemable Items</span>
                <table class="defaulttable" width="100%">
                    <tr class="tableheader">
                        <th>Item</td>
                        <th>Points</td>
                        <th>Description</td>
                        <th>Promo Name</td>
                        <th>Action</td>
                    </tr>
                    <?php
                    for ($i = 0; $i < count($arrRewardItems); $i++)
                    {
                        $rewarditem = $arrRewardItems[$i];
                        ?>
                        <tr>
                            <td><?php echo $rewarditem["RewardItemName"]; ?></td>
                            <td align="right"><?php echo number_format($rewarditem["Points"], 0); ?></td>
                            <td><?php echo $rewarditem["RewardItemDescription"]; ?></td>
                            <td><?php echo $rewarditem["PromoName"]; ?></td>
                            <td align="center"><input type="button" class="redeemitem" rewarditemid="<?php echo $rewarditem["RewardItemID"]; ?>" value="Redeem" itemname="<?php echo $rewarditem["RewardItemName"]; ?>" itempoints="<?php echo $rewarditem["Points"]; ?>" <?php echo $currentPoints < $rewarditem["Points"] ? "disabled='disabled'" : ""; ?>/></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
            <div id="redemptionquantity" style="display:none;">
                <?php echo $hdnMID; ?>
                <?php echo $hdnRewardItemID; ?>
                Item Name: <span id="ItemName"></span><br/>
                Points per Item: <span id="ItemPoints"></span><br/><br/>
                Please enter quantity to be redeemed. <?php echo $txtQuantity; ?><br/>
                <span id="TotalItemPoints"></span>
            </div>

        </div>
        </form>
        <form name="profileupdate" ID="profileupdate">
            <div id="profileinfo" class="profileinfo" style="display:none; font-size: 10pt; text-align: left;">

                <strong>e-Coupon Form<br/>
                    Promotion Title: Luxury Knows No Limits (Omega Raffle)<br/>
                    Promo Period: May to July, 2013<br/>
                </strong>
                <hr width="500" />
                Card Number: <?php echo $CardNumber; ?><br/>
                <?php echo $hdnMemberInfoID; ?>
                <?php echo $txtFirstName; ?><br/>
                <?php echo $txtLastName; ?><br/>
                <?php echo $dtBirthDate; ?><br/>
                <?php echo $txtAddress1; ?><br/>
                <?php echo $cboCityID; ?><br/>
                <?php echo $cboRegionID; ?><br/>
                <?php echo $txtEmail; ?><br/>
                <?php echo $txtMobileNumber; ?><br/>
                <br/>
                <input type="checkbox" id="TermsAndConditions" class="validate[required]" name="TermsAndConditions"><label for="TermsAndConditions">Player has read and accepted the promo mechanics and terms and conditions</label>

                <br/><br/><br/><br/>
            </div>

            <?php
            // if redemption is successful, show redemption window
            if ($showcouponredemptionwindow == true)
            {
                echo $emailmessage;
            }
            ?>
    </div>
</div>
<?php include('footer.php'); ?>
