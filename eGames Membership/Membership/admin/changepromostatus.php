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
App::LoadControl("TextBox");

$Promos = new Promos();
$RewardOffers = new RewardOffers();
$_Log = new AuditTrail();
//Unset sessions for messages
unset ($_SESSION['UPDATE']['SUCCESS']);
unset ($_SESSION['CHANGE']['SUCCESS']);

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
    
    $txtCurrentStatus = new TextBox("txtCurrentStatus","txtCurrentStatus","Current Status: ");
    $txtCurrentStatus->ReadOnly = true;
    $txtCurrentStatus->ShowCaption = false;
    $txtCurrentStatus->Text = Helper::determinePromoStatus($status);
    $fproc->AddControl($txtCurrentStatus);
    
    $rdoGroupStatus = new RadioGroup("rdoGroupStatus", "rdoGroupStatus","Status");
    $rdoGroupStatus->AddRadio("0", "Inactive");
    $rdoGroupStatus->AddRadio("1", "Active");
    $rdoGroupStatus->AddRadio("2", "Deactivated");
    $rdoGroupStatus->ShowCaption = true;
    $rdoGroupStatus->Style = "margin-left: 0px;";
    $rdoGroupStatus->Initialize();
    $rdoGroupStatus->SetSelectedValue($status);
    
    $btnSubmit = new Button("btnSubmit","btnSubmit","Submit");
    $btnSubmit->IsSubmit = true;
    $btnSubmit->Style = "margin-top: 10px;margin-left: 210px;";
    
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
                //Update the promo status in the promos table
                $CommonPDOConnection = null;

                $Promos->StartTransaction();
                $arrEntries['PromoID'] = $promoID;
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
                    //Check if status is unchanged
                    $affected = $Promos->AffectedRows;
                    if ($affected > 0)
                    {
                        //Update the Date Updated and UpdatedBy
                        $Promos->StartTransaction();
                        $arrEntries['PromoID'] = $promoID;
                        $arrEntries['DateUpdated'] = date("Y-m-d");
                        $arrEntries['UpdatedByAID'] = $_SESSION['userinfo']['AID'];
                        $Promos->UpdateByArray($arrEntries);
                        
                        if (App::HasError())
                        {
                            $Promos->RollBackTransaction();
                            $errormsg = "<span style='color:red'>ERROR:</span> There's an error occured while changing the promo status";
                            $openErrorDialog = true;
                        }
                        else
                        {
                            //unset session for Success message and promoID before setting up new session
                            unset($_SESSION['CHANGE']['SUCCESS']); 
                            unset($_SESSION['PromoID']);
                            //set new session for Success message and promoID
                            $_SESSION['CHANGE']['SUCCESS'] = "The Status of the Promo is successfully changed into ".Helper::DeterminePromoStatus($rdoGroupStatus->SubmittedValue);
                            $_SESSION['PromoID'] = $promoID;
                            URL::Redirect("viewpromo.php?success");
                            //Log to audit trail
                            $username = $_SESSION['userinfo']['Username'];
                            $AID = $_SESSION['userinfo']['AID'];
                            $sessionID = $_SESSION['userinfo']['SessionID'];
                            $_Log->logEvent(AuditFunctions::MARKETING_CHANGE_PROMO_STATUS, $username.":Successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                        }
                    }
                    else
                    {
                        $_SESSION['CHANGE']['SUCCESS'] = "Promo status unchanged";
                        $_SESSION['PromoID'] = $promoID;
                        URL::Redirect("viewpromo.php?success");
                    }
                }
            }
        }
        else if ($btnCancel->SubmittedValue == "Cancel")
        {
           //Redirect to updatepromo.php and set session for PromoID to be able to display the Promo 
           unset ($_SESSION['PromoID']); //Unset session first before setting up a new session
           $_SESSION['PromoID'] = $promoID;
           URL::Redirect("viewpromo.php");
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
          resizable: false,
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
                    <br />
                    Current Status: <?php echo $txtCurrentStatus; ?><br /><br />
                    <?php echo $hdnPromoID; ?>
                    <div style="margin-left: 80px;">
                        <?php echo $rdoGroupStatus->Radios[1]; ?>
                        <?php echo $rdoGroupStatus->Radios[0]; ?>
                        <?php echo $rdoGroupStatus->Radios[2]; ?><br />
                    </div>
                    <?php echo $btnSubmit; ?>
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