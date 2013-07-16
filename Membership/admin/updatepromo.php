<?php
/**
 * Update Promo
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: July 11, 2013
 */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Update Promo";
$currentpage = "Promo Maintenance";

App::LoadModuleClass("Loyalty","Promos");
App::LoadModuleClass("Membership","AuditTrail");
App::LoadModuleClass("Membership","AuditFunctions");

App::LoadControl("Button");
App::LoadControl("DatePicker");
App::LoadControl("TextBox");

$Promos = new Promos();
$_Log = new AuditTrail();

if (isset($_REQUEST['success']))
{
    $openSuccessDialog = true;
    if (isset($_SESSION['MSG']['SUCCESS']))
    {
        $msg = $_SESSION['MSG']['SUCCESS'];
    }    
    unset ($_SESSION['MSG']['SUCCESS']);
}

if (isset($_SESSION['PromoID']))
{
    $promoID = strip_tags(mysql_escape_string($_SESSION['PromoID']));
}
else
{
    unset($_SESSION['PromoID']);
    $promoID = strip_tags(mysql_escape_string($_POST['promoID']));
}
//Check if the Promo ID is set and not null
if (isset($promoID) && $promoID != NULL || isset($hdnPromoID))
{
    $promoDetails = $Promos->loadPromoByID($promoID); // Load Promo by ID
    if (count($promoDetails) > 0) //Check if promo exist
    {
        //If exist, retrieve details
        foreach ($promoDetails as $details)
        {
            $promoName          = $details['Name'];
            $promoDescription   = $details['Description'];
            $start_date         = $details['StartDate'];
            $end_date           = $details['EndDate'];
            $draw_date          = $details['DrawDate'];
        }
    }
    else
    {
        App::SetErrorMessage("No Promo Found");
        $promoName          = NULL;
        $promoDescription   = NULL;
        $start_date         = NULL;
        $end_date           = NULL;
    }
    App::LoadControl("Button");
    App::LoadControl("Hidden");
    App::LoadControl("DatePicker");
    App::LoadControl("TextBox");

    $fproc = new FormsProcessor();

    $txtPromoName = new TextBox("txtPromoName","txtPromoName","Promo Name: ");
    $txtPromoName->Length = 30;
    $txtPromoName->Size = 27;
    $txtPromoName->Args = 'onkeypress="javascript: return AlphaNumericOnlyWithSpace(event)"';
    $txtPromoName->Text = $promoName;
    $fproc->AddControl($txtPromoName);
    
    $txtPromoDescription = new TextBox("txtPromoDescription","txtPromoDescription","Promo Description: ");
    $txtPromoDescription->Multiline = true;
    $txtPromoDescription->Rows = 5;
    $txtPromoDescription->Columns = 29;
    $txtPromoDescription->Size = 50;
    $txtPromoDescription->Length = 50;
    $txtPromoDescription->Style = "border: 1px solid #999";
    $txtPromoDescription->Args = 'maxlength="50" onkeypress="javascript: return AlphaNumericOnlyWithSpace(event)"';
    $txtPromoDescription->Text = $promoDescription;
    $fproc->AddControl($txtPromoDescription);

    //$dsmaxdate = new DateSelector();
    //$dsmindate = new DateSelector();

    $startDate = new DatePicker("startDate","startDate","Start Date: ");
    //$startDate->MaxDate = $dsmaxdate->CurrentDate;
    //$startDate->MinDate = $dsmindate->CurrentDate;
    $startDate->SelectedDate = $start_date;
    $startDate->YearsToDisplay = "-100";
    $startDate->CssClass = "validate[required]";
    $startDate->isRenderJQueryScript = true;
    $startDate->Size = 25;
    $startDate->Style = "z-index: 200";
    $startDate->Value = $start_date;
    $fproc->AddControl($startDate);

    $endDate = new DatePicker("endDate","endDate","End Date: ");
    //$startDate->MaxDate = $dsmaxdate->CurrentDate;
    //$startDate->MinDate = $dsmindate->CurrentDate;
    $endDate->SelectedDate = $end_date  ;
    $endDate->YearsToDisplay = "-100";
    $endDate->CssClass = "validate[required]";
    $endDate->isRenderJQueryScript = true;
    $endDate->Size = 25;
    $endDate->Value = $end_date;
    $fproc->AddControl($endDate);
    
    $drawDate = new DatePicker("drawDate","drawDate","Draw Date: ");
    //$startDate->MaxDate = $dsmaxdate->CurrentDate;
    //$startDate->MinDate = $dsmindate->CurrentDate;
    $drawDate->SelectedDate = $draw_date;
    $drawDate->YearsToDisplay = "-100";
    $drawDate->CssClass = "validate[required]";
    $drawDate->isRenderJQueryScript = true;
    $drawDate->Size = 25;
    $fproc->AddControl($drawDate);
    
    $btnSubmit = new Button("btnSubmit", "btnSubmit", "Update Details");
    $btnSubmit->IsSubmit = true;
    $btnSubmit->Enabled = true;
    $btnSubmit->Style = "margin-left: 10px;";
    $fproc->AddControl($btnSubmit);
    
    $btnChangeStatus = new Button("btnChangeStatus","btnChangeStatus","Change Status");
    $btnChangeStatus->Enabled = true;
    $btnChangeStatus->Style = "margin-top: 20px;margin-left: 60px;position:relative";
    $fproc->AddControl($btnChangeStatus);
    
    $hdnPromoID = new Hidden("promoID", "promoID");
    $hdnPromoID->Args = "value='$promoID'";
    $fproc->AddControl($hdnPromoID);
    
    $fproc->ProcessForms();

    if ($fproc->IsPostBack)
    {
        if ($btnSubmit->SubmittedValue == "Update Details")
        {
            //check if valid information is entered
            if ((strlen($txtPromoName->SubmittedValue) == 0) || (strlen($txtPromoDescription->SubmittedValue) == 0))
            {
                $errormsg = "<span style='color:red'>ERROR:</span> All fields are required";
                $openErrorDialog = true;
            }
            //check if date range is valid
            else if (strtotime($startDate->SubmittedValue) > strtotime($endDate->SubmittedValue))
            {
                $errormsg = "<span style='color:red'>ERROR:</span> Please enter a valid date range";
                $openErrorDialog = true;
            }
            //Check if Draw Date is less than End Date
            else if (strtotime($drawDate->SubmittedValue) < strtotime($endDate->SubmittedValue))
            {
                $errormsg = "<span style='color:red'>ERROR:</span> Please enter a Draw Date greater than End Date";
                $openErrorDialog = true;
            }
            else
            {
                //Update details
                $Promos->StartTransaction();
                $arrEntries['PromoID'] = $promoID;
                $arrEntries['Name'] = $txtPromoName->SubmittedValue;
                $arrEntries['Description'] = $txtPromoDescription->SubmittedValue;
                $arrEntries['DateUpdated'] = date("Y-m-d");
                $arrEntries['UpdatedByAID'] = $_SESSION['userinfo']['AID'];
                $arrEntries['StartDate'] = $startDate->SubmittedValue;
                $arrEntries['EndDate'] = $endDate->SubmittedValue;
                $arrEntries['DrawDate'] = $drawDate->SubmittedValue;
                $arrEntries['Status'] = 1;
                $Promos->UpdateByArray($arrEntries);
                
                if ($Promos->HasError)
                {
                    $Promos->RollBackTransaction();
                    $errormsg = "<span style='color:red'>ERROR:</span> There's an error occured while updating the promo";
                    $openErrorDialog = true;
                }
                else 
                {
                    $Promos->CommitTransaction();
                    //Log to audit trail
                    $username = $_SESSION['userinfo']['Username'];
                    $AID = $_SESSION['userinfo']['AID'];
                    $sessionID = $_SESSION['userinfo']['SessionID'];
                    $_Log->logEvent(AuditFunctions::MARKETING_UPDATE_PROMO, $username.":Successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                    //Redirect to view promo with success dialog box
                    unset($_SESSION['MESSAGE']['SUCCESS']);
                    $_SESSION['MESSAGE']['SUCCESS'] = "The promo was successfully updated";
                    URL::Redirect("viewpromo.php?success");
                }
            }
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
        $("#btnChangeStatus").live("click",function(){
            $("#changepromostat").submit();
        });
    });
</script> 
<script type="text/javascript">
    $(document).ready(function(){
       $("#msgsuccess").html("<?php echo $msg; ?>");
       $("#successDialog").dialog({
          autoOpen: <?php echo $openSuccessDialog; ?>,
          modal: true,
          resizable: false,
          buttons: {
                "OK": function(){
                    $(this).dialog("close");
                }
          }
       });
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#errorDialog").dialog({
          autoOpen: <?php echo $openErrorDialog; ?>,
          modal: true,
          resizable: false,
          buttons: {
                "OK": function(){
                    $(this).dialog("close");
                }
            }
        });
    });
</script>    
<div align="center">
    </form>
    <form name="updatepromo" id="updatepromo" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                    <br><br>
                    <div class="title">Update Promo Details</div>
                        <table id="tbladdpromo">
                            <tr>
                                <td id="caption" valign="top">Promo Name: </td>
                                <td id="field"><?php echo $txtPromoName;?></td>
                            </tr>
                            <tr>
                                <td id="caption" valign="top">Promo Description: </td>
                                <td id="field">
                                    <?php echo $txtPromoDescription; ?><br />
                                    <span id="remainingchars" style="font-style: italic">Maximum characters: 50</span>
                                </td>
                            </tr>
                            <tr>
                                <td id="caption" valign="top">Start Date: </td>
                                <td id="field"><?php echo $startDate; ?></td>
                            </tr>
                            <tr>
                                <td id="caption" valign="top">End Date: </td>
                                <td id="field"><?php echo $endDate; ?></td>
                            </tr>
                            <tr>
                                <td id="caption" valign="top">Draw Date: </td>
                                <td id="field"><?php echo $drawDate; ?></td>
                            </tr>
                            <tr>
                                <td id="caption"></td>
                                <td id="field"><?php echo $btnChangeStatus; echo $btnSubmit; ?></td>
                            </tr>
                        </table>    
                    <br /><br />
                    <?php echo $hdnPromoID; ?>
                    <div align="center" id="pagination">
                        <span id="errorMessage"></span>
                    </div>
            </div>
            <!--------success dialog box----------->
            <div id="successDialog" title="Success Message">
                <p id="msgsuccess"></p>
            </div>
            <!------------------------------------->
            <!----error notification dialog box----->
            <div id="errorDialog" title="Error Message">
                <p id="msgerror">
                    <?php 
                        if (isset($errormsg))
                            echo $errormsg; 
                    ?>
                </p>
            </div>
            <!------------------------------------------->
        </div>
    </form>
    <form action="changepromostatus.php" id="changepromostat" method="post">
        <input type="hidden" value="<?php echo $promoID; ?>" name="hdnPromoID" />
    </form>    
</div>
<?php include("footer.php"); ?>