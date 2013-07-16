<?php
/**
 * Add Promo
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: July 11, 2013
 */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Add Promo";
$currentpage = "Promo Maintenance";

App::LoadModuleClass("Loyalty","Promos");
App::LoadModuleClass("Membership","AuditTrail");
App::LoadModuleClass("Membership","AuditFunctions");

$Promos = new Promos();
$_Log   = new AuditTrail();

App::LoadControl("Button");
App::LoadControl("DatePicker");
App::LoadControl("TextBox");

$fproc = new FormsProcessor();

$txtPromoName = new TextBox("txtPromoName","txtPromoName","Promo Name: ");
$txtPromoName->CssClass = "validate[required]";
$txtPromoName->Length = 30;
$txtPromoName->Size = 27;
$txtPromoName->Args = 'onkeypress="javascript: return AlphaNumericOnlyWithSpace(event)"';
$fproc->AddControl($txtPromoName);

$txtPromoDescription = new TextBox("txtPromoDescription","txtPromoDescription","Promo Description: ");
$txtPromoDescription->Multiline = true;
$txtPromoDescription->Rows = 5;
$txtPromoDescription->Columns = 29;
$txtPromoDescription->Size = 50;
$txtPromoDescription->Style = "border: 1px solid #999";
$txtPromoDescription->CssClass = "validate[required]";
$txtPromoDescription->Args = 'maxlength="50" onkeypress="javascript: return AlphaNumericOnlyWithSpace(event)"';
$fproc->AddControl($txtPromoDescription);

//$dsmaxdate = new DateSelector();
//$dsmindate = new DateSelector();

$startDate = new DatePicker("startDate","startDate","Start Date: ");
//$startDate->MaxDate = $dsmaxdate->CurrentDate;
//$startDate->MinDate = $dsmindate->CurrentDate;
$startDate->SelectedDate = "";
$startDate->Value = date('Y-m-d');
$startDate->YearsToDisplay = "-100";
$startDate->CssClass = "validate[required]";
$startDate->isRenderJQueryScript = true;
$startDate->Size = 25;
$startDate->Style = "z-index: 200";
$startDate->Args = "placeholder='YYYY-MM-DD'";
$fproc->AddControl($startDate);

$endDate = new DatePicker("endDate","endDate","End Date: ");
//$startDate->MaxDate = $dsmaxdate->CurrentDate;
//$startDate->MinDate = $dsmindate->CurrentDate;
$endDate->SelectedDate = "";
$endDate->Value = date('Y-m-d');
$endDate->YearsToDisplay = "-100";
$endDate->CssClass = "validate[required]";
$endDate->isRenderJQueryScript = true;
$endDate->Size = 25;
$endDate->Args = "placeholder='YYYY-MM-DD'";
$fproc->AddControl($endDate);

$drawDate = new DatePicker("drawDate","drawDate","Draw Date: ");
//$startDate->MaxDate = $dsmaxdate->CurrentDate;
//$startDate->MinDate = $dsmindate->CurrentDate;
$drawDate->SelectedDate = "";
$drawDate->Args = "placeholder='YYYY-MM-DD'";
$drawDate->Value = date('Y-m-d');
$drawDate->YearsToDisplay = "-100";
$drawDate->CssClass = "validate[required]";
$drawDate->isRenderJQueryScript = true;
$drawDate->Size = 25;
$fproc->AddControl($drawDate);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
$btnSubmit->IsSubmit = true;
$btnSubmit->Enabled = true;
$btnSubmit->Style = "margin-top: 20px;margin-left: 180px;position:relative";
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

if ($fproc->IsPostBack)
{
    if ($btnSubmit->SubmittedValue == "Submit")
    {
        //check if valid information is entered
        if ((strlen($txtPromoName->SubmittedValue) == 0) || (strlen($txtPromoDescription->SubmittedValue) == 0)
                                                         || (strlen($startDate->SubmittedValue) == 0)
                                                         || (strlen($endDate->SubmittedValue) == 0)
                                                         || (strlen($drawDate->SubmittedValue) == 0))
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
            //Insert all the information entered in promos table
            $Promos->StartTransaction();
            $arrEntries['Name'] = $txtPromoName->SubmittedValue;
            $arrEntries['Description'] = $txtPromoDescription->SubmittedValue;
            $arrEntries['DateCreated'] = date('Y-m-d');
            $arrEntries['CreatedByAID'] = $_SESSION['userinfo']['AID'];
            $arrEntries['DateUpdated'] = NULL;
            $arrEntries['StartDate'] = $startDate->SubmittedValue;
            $arrEntries['EndDate'] = $endDate->SubmittedValue;
            $arrEntries['DrawDate'] = $drawDate->SubmittedValue;
            $arrEntries['Status'] = 1;
            $Promos->Insert($arrEntries);
            
            if ($Promos->HasError)
            {
                $Promos->RollBackTransaction();
                $errormsg = "<span style='color:red'>ERROR:</span> There's an error occured while adding the promo";
                $openErrorDialog = true;
            }
            else 
            {
                /**
                 * Update the PromoCode since it was emptied because PromoID is needed to append in the word "PROMO"
                 * @example PROMO + $PromoID = PROMO1
                 */
                $lastPromoID = $Promos->LastInsertID;
                $arrPromo['PromoID'] = $lastPromoID;
                $arrPromo['PromoCode'] = "PROMO".$lastPromoID;
                $Promos->UpdateByArray($arrPromo);
                
                $Promos->CommitTransaction();
                
                $successmsg = "The Promo was successfully Added";
                $openSuccessDialog = true;
                //Log to audit trail
                $username = $_SESSION['userinfo']['Username'];
                $AID = $_SESSION['userinfo']['AID'];
                $sessionID = $_SESSION['userinfo']['SessionID'];
                $_Log->logEvent(AuditFunctions::MARKETING_ADD_PROMO, $username.":Successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                //--------------------------//
            }
        }
    }
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
                "OK": function(){
                    $(this).dialog("close");
                }
            }
        });
    });
</script>
<script type='text/javascript'>
    $(document).ready(function(){
        $("#successDialog").dialog({
          autoOpen: <?php echo $openSuccessDialog; ?>,
          modal: true,
          resizable: false,
          buttons: {
                "OK": function(){
                    $(this).dialog("close");
                    $("#txtPromoName").val("");
                    $("#txtPromoDescription").val("");
                    $("#startDate").val("");
                    $("#endDate").val("");
                    $("#drawDate").val("");
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
                    <div class="title">Add Promo</div>
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
                                <td id="field"><?php echo $btnSubmit; ?></td>
                            </tr>
                        </table>    
                    <br /><br />
                    <div align="center" id="pagination">
                        <span id="errorMessage"></span>
                    </div>
            </div>
            <!---------error dialog box-------->
            <div id="errorDialog" title="Error Message">
                <p id="msgerror">
                    <?php
                        if (isset($errormsg))
                            echo $errormsg; 
                    ?>
                </p>
            </div>    
            <!--------------------------------->
            <!---------success dialog box-------->
            <div id="successDialog" title="Success Message">
                <p id="msgsuccess">
                    <?php
                        if (isset($successmsg))
                            echo $successmsg; 
                    ?>
                    </p>
            </div>    
            <!--------------------------------->
        </div>
    </form>
</div>
<?php include("footer.php"); ?>