<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Change Reward Offer Status";
$currentpage = "Administration";

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass('Kronus', 'Sites');
App::LoadModuleClass('Loyalty', 'RewardItems');
App::LoadModuleClass('Loyalty', 'CardTypes');
App::LoadModuleClass('Loyalty', 'Promos');
App::LoadModuleClass('Loyalty', 'Partners');
App::LoadModuleClass('Loyalty', 'RewardOffers');

App::LoadControl("DatePicker");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("DataGrid");
App::LoadControl("Hidden");
App::LoadControl("ComboBox");
App::LoadControl("RadioGroup");

$_RewardItems = new RewardItems();
$_CardTypes = new CardTypes();
$_Promos = new Promos();
$_Partners = new Partners();
$_RewardOffers = new RewardOffers();
$fproc = new FormsProcessor();

$rdoGroupStatus = new RadioGroup("rdoStatus", "rdoStatus", "");
$rdoGroupStatus->AddRadio("1", "Active");
$rdoGroupStatus->AddRadio("2", "Inactive");
$rdoGroupStatus->AddRadio("3", "Deactivated");
$rdoGroupStatus->AddRadio("4", "Expired");
$rdoGroupStatus->ShowCaption = true;
$rdoGroupStatus->Initialize();
$fproc->AddControl($rdoGroupStatus);

$hdnOfferID = new Hidden("hdnOfferID", "hdnOfferID", "");
$fproc->AddControl($hdnOfferID);

$txtCurrentStatus = new TextBox("txtCurrentStatus", "txtCurrentStatus", "");
$txtCurrentStatus->ReadOnly = true;
$fproc->AddControl($txtCurrentStatus);

$btnChangeStat = new Button("btnChangeStat", "btnChangeStat", "Change Status");
$btnChangeStat->ShowCaption = true;
$btnChangeStat->Enabled = true;
$btnChangeStat->IsSubmit = true;
$fproc->AddControl($btnChangeStat);

$fproc->ProcessForms();

if ($fproc->IsPostBack) {

    if ($fproc->GetPostVar('btnChangeStatus') == "Change Status") {

        $hdnRewardOfferID = $fproc->GetPostVar('hdnRewardOffer');
        $hdnStatus = $fproc->GetPostVar('hdnOfferStatus');

        if ($hdnStatus == 1) {
            $txtCurrentStatus->Text = 'Active';
        } else if ($hdnStatus == 2) {
            $txtCurrentStatus->Text = 'Inactive';
        } else if ($hdnStatus == 3) {
            $txtCurrentStatus->Text = 'Deactivated';
        } else {
            $txtCurrentStatus->Text = 'Expired';
        }
        $hdnOfferID->Text = $hdnRewardOfferID;
        $rdoGroupStatus->SetSelectedValue($hdnStatus);
    }

    if ($fproc->GetPostVar('btnChangeStat') == "Change Status") {

        $rdogroupstat = $rdoGroupStatus->SubmittedValue;
        $rewardoffer_id = $hdnOfferID->SubmittedValue;

        $_RewardOffers->StartTransaction();
        
        $_RewardOffers->updateRewardOfferStat($rewardoffer_id, $rdogroupstat);
        
        if ($_RewardOffers->HasError) {
                $_RewardOffers->RollBackTransaction();
                $_SESSION['msg'] = "Failed to update reward offer : ".$_RewardOffers->getErrors();
                header("Location: viewrewardoffers.php");
            } else {
                
                $rowcount = $_RewardOffers->AffectedRows;

                if ($rowcount == 0) {
                    $_SESSION['msg'] = "Reward Offer details unchanged.";
                    header("Location: viewrewardoffers.php");
                } else {
                    $_RewardOffers->updateRewardOfferStatus($rewardoffer_id, $rdogroupstat, "now_usec()", $_SESSION['aID']);
                    if ($_RewardOffers->HasError) {
                    $_RewardOffers->RollBackTransaction();
                    $_SESSION['msg'] = "Cannot add the Date of Update.";
                    header("Location: viewrewardoffers.php");
                    }else{
                    $_RewardOffers->CommitTransaction();
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
        $('#btnChangeStat').click(function()
        {
            var answer = confirm("Update Reward Offer Status?");
            if (answer) {
                $('#rewardofferschangestatus').attr('action', 'editrewardoffers.php');
                $('#rewardofferschangestatus').submit();
            }
            else {
                return false;
            }
        })
    });

</script>
<div align="center">
    <form name="rewardofferschangestatus" id="rewardoffersmgt" method="POST" action="rewardofferstatusupdate.php">
        <div class="maincontainer">
           <?php include('menu.php'); ?>
            <br />
            <div style="float: left;" class="title">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update Reward Offer Status:</div>
            <br />
            <br />
            <table>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Current Status</td><td align="left"><?php echo $txtCurrentStatus; ?></td></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td align="left">
                        <br />
                        <?php echo $hdnOfferID; ?>
                        <?php echo $rdoGroupStatus->Radios[0] . " "; ?>
                        <?php echo $rdoGroupStatus->Radios[1] . " "; ?>
                        <?php echo $rdoGroupStatus->Radios[2] . " "; ?>
                        <?php echo $rdoGroupStatus->Radios[3] . " "; ?>
                    </td></tr>
                <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td align="right">
                        <br />
                        <?php echo $btnChangeStat; ?>
                    </td></tr>
            </table>
            <div class="content">
                <div id="results">

                </div>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>