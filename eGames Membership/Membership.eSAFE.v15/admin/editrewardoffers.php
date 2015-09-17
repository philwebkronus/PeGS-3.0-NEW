<?php

/* * ***************** 
 * Author: Junjun S. Hernandez
 * Date Created: 2013-07-12
 * Description: Page for updating Reward Offer details.
 * ***************** */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Edit Reward Offers";
$currentpage = "Administration";

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass('Kronus', 'Sites');
App::LoadModuleClass('Rewards', 'RewardItems');
App::LoadModuleClass('Loyalty', 'CardTypes');
App::LoadModuleClass('Loyalty', 'Promos');
App::LoadModuleClass('Rewards', 'Partners');
App::LoadModuleClass('Loyalty', 'RewardOffers');

App::LoadControl("DatePicker");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("DataGrid");
App::LoadControl("Hidden");
App::LoadControl("ComboBox");

$_RewardItems = new RewardItems();
$_CardTypes = new CardTypes();
$_Promos = new Promos();
$_Partners = new Partners();
$_RewardOffers = new RewardOffers();
$fproc = new FormsProcessor();

$cboRewardItem = new ComboBox("RewardItem", "RewardItem", "");
$opt1 = null;
$opt1[] = new ListItem("Select One", "-1", true);
$cboRewardItem->Items = $opt1;
$arrrewarditems = $_RewardItems->getRewardIDAndName();
$list_details1 = new ArrayList();
$list_details1->AddArray($arrrewarditems);
$cboRewardItem->DataSource = $list_details1;
$cboRewardItem->ShowCaption = true;
$cboRewardItem->DataSourceValue = "RewardItemID";
$cboRewardItem->DataSourceText = "RewardItemName";
$cboRewardItem->DataBind();
$fproc->AddControl($cboRewardItem);

$cboCardType = new ComboBox("CardType", "CardType", "");
$opt2 = null;
$opt2[] = new ListItem("Select One", "-1", true);
$cboCardType->Items = $opt1;
$arrcardtype = $_CardTypes->getCardTypes();
$list_details2 = new ArrayList();
$list_details2->AddArray($arrcardtype);
$cboCardType->DataSource = $list_details2;
$cboCardType->ShowCaption = true;
$cboCardType->DataSourceValue = "CardTypeID";
$cboCardType->DataSourceText = "CardTypeName";
$cboCardType->DataBind();
$fproc->AddControl($cboCardType);

$cboPromo = new ComboBox("Promo", "Promo", "");
$opt3 = null;
$opt3[] = new ListItem("Select One", "-1", true);
$cboPromo->Items = $opt1;
$arrpromo = $_Promos->getPromos();
$list_details3 = new ArrayList();
$list_details3->AddArray($arrpromo);
$cboPromo->DataSource = $list_details3;
$cboPromo->ShowCaption = true;
$cboPromo->DataSourceValue = "PromoID";
$cboPromo->DataSourceText = "Name";
$cboPromo->DataBind();
$fproc->AddControl($cboPromo);

$cboPartner = new ComboBox("Partners", "Partners", "");
$opt4 = null;
$opt4[] = new ListItem("Select One", "-1", true);
$cboPartner->Items = $opt1;
$arrpartners = $_Partners->getPartners();
$list_details4 = new ArrayList();
$list_details4->AddArray($arrpartners);
$cboPartner->DataSource = $list_details4;
$cboPartner->ShowCaption = true;
$cboPartner->DataSourceValue = "PartnerID";
$cboPartner->DataSourceText = "PartnerName";
$cboPartner->DataBind();
$fproc->AddControl($cboPartner);

$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$dsmaxdate->AddYears(+21);
$dsmindate->AddYears(+0);

$thestime = date('Y-m-d H:i:s');
$fromDate = new DatePicker("fromDate", "fromDate", "From");
$fromDate->MaxDate = $dsmaxdate->CurrentDate;
$fromDate->MinDate = $dsmindate->CurrentDate;
$fromDate->ShowCaption = false;
$fromDate->YearsToDisplay = "-0:+100";
$fromDate->CssClass = "validate[required]";
$fromDate->isRenderJQueryScript = true;
$fromDate->Size = 27;
$fproc->AddControl($fromDate);

$toDate = new DatePicker("toDate", "toDate", "To");
$toDate->MaxDate = $dsmaxdate->CurrentDate;
$toDate->MinDate = $dsmindate->CurrentDate;
$toDate->ShowCaption = false;
$toDate->YearsToDisplay = "-0:+100";
$toDate->CssClass = "validate[required]";
$toDate->isRenderJQueryScript = true;
$toDate->Size = 27;
$fproc->AddControl($toDate);

$hdnRewardOffer = new Hidden("hdnRewardOffer", "hdnRewardOffer", "");
$fproc->AddControl($hdnRewardOffer);

$hdnOfferStatus = new Hidden("hdnOfferStatus", "hdnOfferStatus", "");
$fproc->AddControl($hdnOfferStatus);

$RequiredPoints = new TextBox("RequiredPoints", "RequiredPoints", "");
$RequiredPoints->Args = 'onkeypress="javascript: return numberonly(event)"';
$fproc->AddControl($RequiredPoints);

$btnChangeStatus = new Button("btnChangeStatus", "btnChangeStatus", "Change Status");
$btnChangeStatus->ShowCaption = true;
$btnChangeStatus->Enabled = true;
$btnChangeStatus->IsSubmit = true;
$fproc->AddControl($btnChangeStatus);

$btnUpdateDetails = new Button("btnUpdateDetails", "btnUpdateDetails", "Submit");
$btnUpdateDetails->ShowCaption = true;
$btnUpdateDetails->Enabled = true;
$btnUpdateDetails->IsSubmit = true;
$fproc->AddControl($btnUpdateDetails);

$fproc->ProcessForms();

//Clear the session for Redemtion
if(isset($_SESSION['CardRed'])){
    unset($_SESSION['CardRed']);
}

if ($fproc->IsPostBack) {
    
    if ($fproc->GetPostVar('btnUpdateRewardOffer') == 'Update Details') {

        
        $hdnRewardOfferID = $fproc->GetPostVar('hdnRewardOfferID');
        $hdnRewardItemID = $fproc->GetPostVar('hdnRewardItemID');
        $hdnCardTypeID = $fproc->GetPostVar('hdnCardTypeID');
        $hdnPromoID = $fproc->GetPostVar('hdnPromoID');
        $hdnPartnerID = $fproc->GetPostVar('hdnPartnerID');
        $rpoints = $fproc->GetPostVar('hdnRequiredPoints');
        $hdnStartDate = $fproc->GetPostVar('hdnStartDate');
        $hdnEndDate = $fproc->GetPostVar('hdnEndDate');
        $hdnStatus = $fproc->GetPostVar('hdnStatus');

        $hdnRewardOffer->Text = $hdnRewardOfferID;
        $cboRewardItem->SetSelectedValue($hdnRewardItemID);
        $cboCardType->SetSelectedValue($hdnCardTypeID);
        $cboPromo->SetSelectedValue($hdnPromoID);
        $cboPartner->SetSelectedValue($hdnPartnerID);
        $RequiredPoints->Text = $rpoints;
        $fromDate->SelectedDate = $hdnStartDate;
        $fromDate->Value = $hdnStartDate;
        $toDate->SelectedDate = $hdnEndDate;
        $hdnOfferStatus->Text = $hdnStatus;
    }

    if (($btnUpdateDetails->SubmittedValue) == 'Submit') {

        $rewardofferid = $hdnRewardOffer->SubmittedValue;
        $rewarditemid = $cboRewardItem->SubmittedValue;
        $cardtypeid = $cboCardType->SubmittedValue;
        $promoid = $cboPromo->SubmittedValue;
        $partnerid = $cboPartner->SubmittedValue;
        $reqpoints = $RequiredPoints->SubmittedValue;
        $offerstartdate = $fromDate->SubmittedValue;
        $offerenddate = $toDate->SubmittedValue;

        $_RewardOffers->StartTransaction();

        $updateOffers["RewardOfferID"] = $rewardofferid;
        $updateOffers["RewardItemID"] = $rewarditemid;
        $updateOffers["CardTypeID"] = $cardtypeid;
        $updateOffers["PromoID"] = $promoid;
        $updateOffers["PartnerID"] = $partnerid;
        $updateOffers['RequiredPoints'] = $reqpoints;
        $updateOffers["OfferStartDate"] = $offerstartdate;
        $updateOffers["OfferEndDate"] = $offerenddate;
        $_RewardOffers->UpdateByArray($updateOffers);

        if ($_RewardOffers->HasError) {
            $_RewardOffers->RollBackTransaction();
            $_SESSION['msg'] = "Failed to update reward offer : " . $_RewardOffers->getErrors();
        } else {
            $_RewardOffers->CommitTransaction();
            $updateOffers["DateUpdated"] = "NOW(6)";
            $updateOffers["UpdatedByAID"] = $_SESSION['aID'];
            $_RewardOffers->StartTransaction();
            if ($_RewardOffers->HasError) {
                $_RewardOffers->RollBackTransaction();
                $_SESSION['msg'] = "Failed to update reward offer : " . $_RewardOffers->getErrors();
                header("Location: viewrewardoffers.php");
            } else {
                $rowcount = $_RewardOffers->AffectedRows;

                if ($rowcount == 0) {
                    $_SESSION['msg'] = "Reward Offer details unchanged.";
                    header("Location: viewrewardoffers.php");
                } else {
                    $updateOffers["DateUpdated"] = "NOW(6)";
                    $updateOffers["UpdatedByAID"] = $_SESSION['aID'];
                    $_RewardOffers->UpdateByArray($updateOffers);
                    $_SESSION['msg'] = "Reward offer successfully updated.";
                    header("Location: viewrewardoffers.php");
                }
            }
        }
    }
}
?>
<?php include("header.php"); ?>
</form>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>

    $(document).ready(function() {

        $('#btnChangeStatus').click(function()
        {
            $('#editrewardoffers').attr('action', 'rewardofferstatusupdate.php');
            $('#editrewardoffers').submit();
        }),
        $('#btnUpdateDetails').click(function()
        {
            var RewardItem = document.getElementById('RewardItem').value;
            var CardType = document.getElementById('CardType').value;
            var Promo = document.getElementById('Promo').value;
            var Partners = document.getElementById('Partners').value;
            var RequiredPoints = document.getElementById('RequiredPoints').value;
            var fromDate = document.getElementById('fromDate').value;
            var toDate = document.getElementById('toDate').value;
            if (RewardItem == '-1') {
                alert('Please Select a Reward Item!');
                return false;
            }
            if (CardType == '-1') {
                alert('Please Select a Card Type!');
                return false;
            }
            if (Promo == '-1') {
                alert('Please Select a Promo!');
                return false;
            }
            if (Partners == '-1') {
                alert('Please Select a Partner!');
                return false;
            }
            if (RequiredPoints == '') {
                alert('Please enter valid Required Points!');
                return false;
            }

            var date = new Date();
            var curr_date = date.getDate();
            var curr_month = date.getMonth();
            curr_month = curr_month + 1;
            var curr_year = date.getFullYear();

            if (curr_month < 10)
            {
                curr_month = "0" + curr_month;
                if (curr_date < 10)
                    curr_date = "0" + curr_date;
            }
            
            if ((fromDate) > (toDate))
            {
                alert("End Date must be greater than the Start Date!");
                $('#results').hide();
                return false;
            }
            else
            {
                var answer = confirm("Update Reward Offer?");
                if (answer) {
                    $('#editrewardoffers').attr('action', 'editrewardoffers.php');
                    $('#editrewardoffers').submit();
                }
                else {
                    return false;
                }
            }

        });
    });

</script>
<div align="center">
    <form name="editrewardoffers" id="editrewardoffers" method="POST" action="editrewardoffers.php">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <br />
            <div style="float: left;" class="title">&nbsp;&nbsp;&nbsp;&nbsp;Update Reward Offer:</div>
            <div class="pad5" align="right"> </div>
                            <br/><br/>
                            <hr color="black">
                            <br>
                            <div class="pad5" align="right"></div>
            <table>
                <?php echo $hdnRewardOffer; ?>
                <?php echo $hdnOfferStatus; ?>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Reward Item</td><td align="left"><?php echo $cboRewardItem; ?></td></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Card Type</td><td align="left"><?php echo $cboCardType; ?></td></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Promo</td><td align="left"><?php echo $cboPromo; ?></td></tr></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Partner</td><td align="left"><?php echo $cboPartner; ?></td></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Required Points</td><td align="left"><?php echo $RequiredPoints; ?></td></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Start Date</td><td align="left"><?php echo $fromDate; ?></td></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End Date</td><td align="left"><?php echo $toDate; ?> </td></tr>
                <tr><td align="left"></td><td align="right"><br><?php echo $btnChangeStatus; ?><?php echo $btnUpdateDetails; ?></td></tr>    
            </table>
            <div class="content">
                <div id="results">

                </div>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>