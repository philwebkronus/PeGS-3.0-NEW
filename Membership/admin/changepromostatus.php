<?php
/**
 * Change Promo Status
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: July 11, 2013
 */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Change Promo Status";
$currentpage = "Promo Maintenance";

$modulename = "Loyalty";
App::LoadModuleClass($modulename,"Promos");
App::LoadModuleClass($modulename,"Helper");
App::LoadModuleClass($modulename,"RewardOffers");
App::LoadModuleClass("Membership","AuditTrail");
App::LoadModuleClass("Membership","AuditFunctions");

App::LoadControl("Button");
App::LoadControl("RadioGroup");
App::LoadControl("Radio");
App::LoadControl("Hidden");

$Promos = new Promos();
$RewardOffers = new RewardOffers();
$_Log = new AuditTrail();

$promoID = strip_tags(mysql_escape_string($_POST['hdnPromoID']));
//If question string is not available in the URL (maybe changed by the user),  it will redirect to View Promo
if (isset($promoID) && $promoID != NULL || isset($hdnPromoID))
{
    $promoStatus = $Promos->getPromoStatus($promoID);
    if (count($promoStatus) > 0)
    {
        $status = $promoStatus[0]['Status'];
    }
    else
    {
        App::SetErrorMessage("No Promo Found");
        $status = null;
    }
    $fproc = new FormsProcessor();
    
    $rdoGroupStatus = new RadioGroup("rdoGroupStatus", "rdoGroupStatus","Status");
    $rdoGroupStatus->AddRadio("0", "Inactive");
    $rdoGroupStatus->AddRadio("1", "Active");
    $rdoGroupStatus->AddRadio("2", "Deactivated");
    $rdoGroupStatus->ShowCaption = true;
    $rdoGroupStatus->Style = "margin-left: 40px;";
    $rdoGroupStatus->Initialize();
    $rdoGroupStatus->SetSelectedValue($status);
    
    $btnSubmit = new Button("btnSubmit","btnSubmit","Submit");
    $btnSubmit->IsSubmit = true;
    $btnSubmit->Style = "margin-top: 20px;margin-left: 60px;position:relative";
    
    $btnCancel = new Button("btnCancel","btnCancel","Cancel");
    $btnCancel->IsSubmit = true;
    $btnCancel->Enabled = true;
    $btnCancel->Style = "margin-left: 10px;";
    
    $hdnPromoID = new Hidden("hdnPromoID", "promoID");
    $hdnPromoID->Args = "value='$promoID'";
    $fproc->AddControl($hdnPromoID);
    
    $fproc->AddControl($rdoGroupStatus);
    $fproc->AddControl($btnSubmit);
    $fproc->AddControl($btnCancel);
    
    $fproc->ProcessForms();

    if ($fproc->IsPostBack)
    {
        if ($btnSubmit->SubmittedValue == "Submit")
        {
            if ($rdoGroupStatus->SubmittedValue != null)
            {
                //check if the status is changed if not display error message
                if ($rdoGroupStatus->SubmittedValue == $status)
                {
                    $errormsg = "<span style='color:red'>ERROR:</span> The status of the promo has not been changed";
                    $openErrorDialog = true;
                }
                else
                {
                    //Update the promo status in the promos table
                    $CommonPDOConnection = null;
                    
                    $Promos->StartTransaction();
                    $arrEntries['PromoID'] = $promoID;
                    $arrEntries['DateUpdated'] = date("Y-m-d");
                    $arrEntries['UpdatedByAID'] = $_SESSION['userinfo']['AID'];
                    $arrEntries['Status'] = $rdoGroupStatus->SubmittedValue;
                    $Promos->UpdateByArray($arrEntries);
                    $CommonPDOConnection = $Promos->getPDOConnection();
                    
                    if (!App::HasError())
                    {
                        //If there no error occured update the promo status in the rewardoffers table
                        $RewardOffers->setPDOConnection($CommonPDOConnection);
                        $RewardOffers->updatePromoStatus($rdoGroupStatus->SubmittedValue, $promoID);
                    }
                    if (App::HasError())
                    {
                        $Promos->RollBackTransaction();
                        $errormsg = "<span style='color:red'>ERROR:</span> There's an error occured while changing the promo status";
                        $openErrorDialog = true;
                    }
                    else
                    {
                        $Promos->CommitTransaction();
                        //unset session for Success message and promoID before setting up new session
                        unset($_SESSION['MSG']['SUCCESS']); 
                        unset($_SESSION['PromoID']);
                        //set new session for Success message and promoID
                        $_SESSION['MSG']['SUCCESS'] = "The Status of the Promo is successfully changed into ".Helper::DeterminePromoStatus($rdoGroupStatus->SubmittedValue);
                        $_SESSION['PromoID'] = $promoID;
                        URL::Redirect("updatepromo.php?success");
                        App::SetSuccessMessage("The Status of the Promo is successfully changed into ".Helper::DeterminePromoStatus($rdoGroupStatus->SubmittedValue));
                        //Log to audit trail
                        $username = $_SESSION['userinfo']['Username'];
                        $AID = $_SESSION['userinfo']['AID'];
                        $sessionID = $_SESSION['userinfo']['SessionID'];
                        $_Log->logEvent(AuditFunctions::MARKETING_CHANGE_PROMO_STATUS, $username.":Successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                    }
                }
            }
        }
        else if ($btnCancel->SubmittedValue == "Cancel")
        {
           //Redirect to updatepromo.php and set session for PromoID to be able to display the Promo 
           unset ($_SESSION['PromoID']); //Unset session first before setting up a new session
           $_SESSION['PromoID'] = $promoID;
           URL::Redirect("updatepromo.php");
        }
    }
}
else
{
    URL::Redirect("viewpromo.php");
}
?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
<script type='text/javascript'>
    $(document).ready(function(){
       $("#errorDialog").dialog({
          autoOpen: <?php echo $openErrorDialog; ?>,
          modal: true,
          buttons: {
                "OK":function(){
                    $(this).dialog("close");
                }
          }
       }); 
    });
</script>
<div align="center">
    </form>
    <form name="bannedaccountlists" id="bannedaccountlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                    <br><br>
                    <div class="title">Change Promo Status</div>
                    <br /><br />
                    <b>Status: </b><br />
                    <?php echo $hdnPromoID; ?>
                    <?php echo $rdoGroupStatus->Radios[1]; ?><br />
                    <?php echo $rdoGroupStatus->Radios[0]; ?><br />
                    <?php echo $rdoGroupStatus->Radios[2]; ?><br />
                    <?php echo $btnSubmit; echo $btnCancel; ?>
                    <div align="center" id="pagination">
                        <span id="errorMessage"></span>
                    </div>
            </div>
            <!----success error dialog box---->
            <div id="successDialog" title="Success Message">
                <p id="msg"></p>
            </div>
            <!-------------------------------->
            <!--------error dialog box---------------->
            <div id="errorDialog" title="Error Message">
                <p id="msg">
                    <?php  
                    if (isset($errormsg))
                        echo $errormsg;
                    ?>
                </p>
            </div>
            <!------------------------------------->
        </div>
    </form>
</div>
<?php include("footer.php"); ?>