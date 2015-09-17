<?php
require_once("init.inc.php");

$pagetitle = "Membership Activation";

$javascripts[] = "js/jquery.min.js";
$javascripts[] = "js/jquery-ui.min.js";
$stylesheets[] = "css/ui.theme.css";
$stylesheets[] = "css/jquery-ui.css";
$stylesheets[] = "css/smoothness/jquery-ui-1.8.16.custom.css";

$customtags[] = "<BASE target=\"_self\" />";

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "Identifications");
App::LoadModuleClass("Membership", "AccountTypes");
App::LoadModuleClass("Loyalty", "OldCards");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "TempMembers");

App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("ComboBox");
App::LoadControl("RadioGroup");
App::LoadControl("DatePicker");

App::LoadCore('ErrorLogger.php');

$_Members = new Members();
$_AccountTypes = new AccountTypes();
$_TempMembers = new TempMembers();

$_Identification = new Identifications();
$_OldCards = new OldCards();
$_Sites = new Sites();
$_Log = new AuditTrail();

$fproc = new FormsProcessor();
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);
$ActivationDialogOpen = 'false';
$InvalidDialogOpen = 'false';
$IsInvalidCard = false;
$HasParamError = false;
$useCustomHeader = false;

$LoyatyCardNumber = "";
$NewMembershipCardNumber = "";
$CardPoints = "";
$CardName = "";
$siteName = "";

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "Birth Date: ");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
$dtBirthDate->ShowCaption = false;

$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;
$dtBirthDate->Size = 27;
$fproc->AddControl($dtBirthDate);

$txtplayername = new TextBox("txtplayername", "txtplayername", "Name: ");
$txtplayername->CssClass = "validate[required,custom[onlyLetterSp], minSize[2]]";
$txtplayername->Length = 60;
$txtplayername->Size = 30;
$fproc->AddControl($txtplayername);

$ConfirmButton = new Button("ConfirmButton", "ConfirmButton", "Confirm");
$ConfirmButton->IsSubmit = true;
$ConfirmButton->CssClass = "btnDefault roundedcorners";
$fproc->AddControl($ConfirmButton);

$CancelButton = new Button("CancelButton", "CancelButton", "Cancel");
$CancelButton->Args = "onclick='window.close()'";
$CancelButton->CssClass = "btnDefault roundedcorners";
$fproc->AddControl($CancelButton);

$txtAge = new TextBox("txtAge", "txtAge", "Age");
$txtAge->ShowCaption = false;
$txtAge->Length = 30;
$txtAge->Size = 2;
$txtAge->CssClass = "validate[required]";
$txtAge->ReadOnly = true;
$fproc->AddControl($txtAge);

$txtEmail = new TextBox("txtEmail", "txtEmail", "Email");
$txtEmail->ShowCaption = false;
$txtEmail->Length = 60;
$txtEmail->Size = 30;
$txtEmail->CssClass = "validate[custom[email]]";
$fproc->AddControl($txtEmail);

$txtMobile = new TextBox("txtMobile", "txtMobile", "Mobile Number");
$txtMobile->ShowCaption = false;
$txtMobile->Length = 13;
$txtMobile->Size = 30;
$txtMobile->Args = "onkeypress=\"javascript: return isNumber(event);\"";
$fproc->AddControl($txtMobile);

$txtplayerIDNumber = new TextBox("txtplayerIDNumber", "txtplayerIDNumber", "I.D: ");
$txtplayerIDNumber->Length = 30;
$txtplayerIDNumber->Size = 31;
$txtplayerIDNumber->CssClass = "validate[required,custom[onlyLetterNumber]]";
$fproc->AddControl($txtplayerIDNumber);

$arrid = $_Identification->SelectAll();
$ComboID = new ComboBox('ComboID', 'ComboID');
$ComboID->DataSource = $arrid;
$ComboID->DataSourceText = "IdentificationName";
$ComboID->DataSourceValue = "IdentificationID";
$ComboID->DataBind();
$fproc->AddControl($ComboID);

$rdoGroupGender = new RadioGroup("rdoGender", "rdoGender", "Gender");
$rdoGroupGender->AddRadio("1", "Male");
$rdoGroupGender->AddRadio("2", "Female");
$rdoGroupGender->ShowCaption = true;
$rdoGroupGender->CssClass = "validate[required]";
$rdoGroupGender->Initialize();
$fproc->AddControl($rdoGroupGender);

$fproc->ProcessForms();

if ((isset($_GET["oldnumber"]) && (htmlentities($_GET["oldnumber"])))
        && (isset($_GET["newnumber"]) && (htmlentities($_GET["newnumber"])))
        && (isset($_GET["site"]) && (htmlentities($_GET["site"])))
        && (isset($_GET["AID"]) && (htmlentities($_GET["AID"])))) {

    $LoyatyCardNumber = trim($_GET["oldnumber"]);
    $NewMembershipCardNumber = trim($_GET["newnumber"]);
    $siteCode = trim($_GET["site"]);
    $AID = trim($_GET["AID"]);

    $oldcardresult = $_OldCards->getOldCardInfo($LoyatyCardNumber);
    $OldCardStatus = $oldcardresult[0];
    $CardStatus = $OldCardStatus['CardStatus'];
    $isVIP = $OldCardStatus['IsVIP'];

    switch ($CardStatus) {
        case CardStatus::OLD:
            $isValid = true;
            break;
        case CardStatus::OLD_MIGRATED:
            $isValid = false;
            break;
        default:
            $isValid = false;
            break;
    }

    $oldCardInfo = $_OldCards->getOldCardInfo($LoyatyCardNumber);
    $ArrMemberInfo = $oldCardInfo [0];
    $oldCardName = $ArrMemberInfo['MemberName'];
    $oldCardBirthdate = $ArrMemberInfo['Birthdate'];
    $oldCardGender = $ArrMemberInfo['Gender'];
    $oldCardEmail = $ArrMemberInfo['Email'];

    $OldLoyaltyDetails = $_OldCards->getOldCardDetails($LoyatyCardNumber);
    $arrOldLoyaltyDetails = $OldLoyaltyDetails[0];
    $CardName = $arrOldLoyaltyDetails['CardName'];
    $CardPoints = $arrOldLoyaltyDetails['CurrentPoints'];

    $siteresult = $_Sites->getSiteByCode($siteCode);
    $arraysite = $siteresult[0];
    $siteName = $arraysite['SiteName'];
    $siteid = $arraysite['SiteID'];

    $dtBirthDate->SelectedDate = $oldCardBirthdate;
    $txtplayername->Text = $oldCardName;
    $txtEmail->Text = $oldCardEmail;

    if ($isValid) {
        $rdoGroupGender->SetSelectedValue($oldCardGender);
        
        if ($fproc->IsPostBack) {

            if ($ConfirmButton->SubmittedValue == "Confirm") {

                $dateCreated = "NOW(6)";
                if (empty($oldCardEmail)) {
                    $noemail = true;
                    $Memberstable["UserName"] = $NewMembershipCardNumber;
                } else {
                    $noemail = false;
                    $Memberstable["UserName"] = $txtEmail->SubmittedValue;
                }

                $tempEmail = 0;
                if($txtEmail->SubmittedValue != ""){
                    
                    //check if email is already verified in temp table
                    $tempEmail = $_TempMembers->chkTmpVerifiedEmailAddressWithSP(trim($txtEmail->SubmittedValue));
                    $tempEmail=='' ? COUNT(NULL):$tempEmail = COUNT($tempEmail);
                }
                
                if($tempEmail > 0){
                    App::SetErrorMessage("Email already verified. Please choose a different email address.");
                    $_Log->logAPI(AuditFunctions::MIGRATE_OLD, $tempEmail.':'.$txtEmail->SubmittedValue.':Failed', 0, 0);
                    $isSuccess = false;
                    $error = "Email already verified. Please choose a different email address.";
                    $logger->logger($logdate, $logtype, $error);
                } else {
                    $Memberstable['DateCreated'] = $dateCreated;
                    $Memberstable['Status'] = '1';

                    $PlayerName = $txtplayername->SubmittedValue;

                    $MemberInfo["FirstName"] = $PlayerName;
                    $MemberInfo["Birthdate"] = $dtBirthDate->SubmittedValue;
                    if(!$noemail) $MemberInfo["Email"] = $Memberstable["UserName"];
                    $MemberInfo["NationalityID"] = 1;
                    $MemberInfo["OccupationID"] = 1;
                    $MemberInfo["IdentificationID"] = $ComboID->SubmittedValue;
                    $MemberInfo["IdentificationNumber"] = $txtplayerIDNumber->SubmittedValue;
                    $MemberInfo["Email"] = $txtEmail->SubmittedValue;
                    $MemberInfo["MobileNumber"] = $txtMobile->SubmittedValue;
                    $MemberInfo["DateCreated"] = $dateCreated;
                    $MemberInfo["DateVerified"] = $dateCreated;

                    $rdoGroupGender->SubmittedValue == 1 ? $MemberInfo['Gender'] = 1 : $MemberInfo['Gender'] = 2;

                    $result = $_Members->Migrate($Memberstable, $MemberInfo, $AID, $siteid, $LoyatyCardNumber, $NewMembershipCardNumber, $oldCardEmail, $isVIP, false);
  
  //  -------------------------------------------------------------------------------------------------------->>>       
                    
                    App::LoadModuleClass("Loyalty", "GetCardInfoAPI");
                    $_GetCardInfoAPI = new GetCardInfoAPI();
                    $confirmPIN = '000000';
                    $pin = '000000';
                    $_GetCardInfoAPI->converttoesafe($LoyatyCardNumber, $_Members->password, $pin, $confirmPIN);
                            
  //  -------------------------------------------------------------------------------------------------------->>>
                    
                    $status = $result['status'];

                    if ($status == 'OK')
                    {
                        $isSuccess = true;
                        $_Log->logAPI(AuditFunctions::MIGRATE_OLD, $LoyatyCardNumber.':'.$NewMembershipCardNumber.':'.$_Members->password.':Success',$siteCode, $AID);
                    }
                    else
                    {
                        $isSuccess = false;
                        $error = $result['error'];
                        $_Log->logAPI(AuditFunctions::MIGRATE_OLD, $LoyatyCardNumber.':'.$NewMembershipCardNumber.':Failed', $siteCode, $AID);
                        $logger->logger($logdate, $logtype, $error);
                    }

                    /*
                     * Load message dialog box
                     */

                    $ActivationDialogOpen = 'true';

                    $displayPassword = $_Members->password; //$arrgetMID['Password'];
                }
            }
        }
    }
    else { //Not valid
        $InvalidDialogOpen = 'true';
        $IsInvalidCard = true;
        $error = "Invalid Card Status";
        $logger->logger($logdate, $logtype, $error);
    }
} else { //Parameters not set
    $InvalidDialogOpen = 'true';
    $HasParamError = true;
    $error = "Parameters not set";
    $logger->logger($logdate, $logtype, $error);
}
?>

<?php include 'header.php'; ?>
<script language="javascript" type="text/javascript"> 
    // number only
    // MKGE |07-04-2013
    function isNumber(evt) {
      var charCode = (evt.which) ? evt.which : event.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57)) {
         return false;
      }
      return true;
    }
    
    $(document).ready(
    function() 
    {
        dob1 = $('#dtBirthDate').val();
        dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
        var today = new Date();
        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
        if(isNaN(age))
        {
            var age = '';
        }
        $('#txtAge').val(age);
        $('#dtBirthDate').change(function()
        {
            //alert($('#dtBirthDate').val());
            dob1 = $('#dtBirthDate').val();
            dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
            var today = new Date();
            var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
            $('#txtAge').val(age);
        });  
        
        $( "#InvalidDialog" ).dialog({
            modal: true,
            height: 250,
            width: 350,
            autoOpen: <?php echo $InvalidDialogOpen; ?>,
            buttons: {
                Ok: function() {
                    $( this ).dialog( "close" );
                    window.close();
                }
            }
        });
        
        $( "#ActivationDialog" ).dialog({
            modal: true,
            height: 250,
            width: 350,
            autoOpen: <?php echo $ActivationDialogOpen; ?>,
            buttons: {
                Ok: function() {
                    $( this ).dialog( "close" );
                    window.close();
                }
            }
        });
    });

    var currenttime = '<?php print $curdate->GetCurrentDateFormat("F d, Y H:i:s"); ?>' //PHP method of getting server date
    var montharray=new Array("01","02","03","04","05","06","07","08","09","10","11","12")
    var serverdate=new Date(currenttime)

    var hours = serverdate.getHours();
    var dn="AM";

    if (hours>=12)
    {
        dn="PM";
    }
    if (hours>12){
        hours=hours-12
    }
    if (hours==0)
    {
        hours=12;
    }

    function padlength(what){
        var output=(what.toString().length==1)? "0"+what : what
        return output
    }

    function displaytime(){
        serverdate.setSeconds(serverdate.getSeconds()+1);
        var datestring=montharray[serverdate.getMonth()]+"-"+padlength(serverdate.getDate())+"-"+serverdate.getFullYear();
        var timestring=padlength(serverdate.getHours())+":"+padlength(serverdate.getMinutes());
        document.getElementById("servertime").innerHTML=datestring+" "+timestring;
    }

    window.onload=function(){
        setInterval("displaytime()", 1000)
    }

</script>   
<div id ="membersactivation">
    <h1> Membership Card Activation </h1>
    <table>
        <tr>
            <td align="left"> VIP Card No :</td>
            <td width="20%"><strong><?php echo $LoyatyCardNumber; ?></strong></td>
            <td width="30%" align="left"> &nbsp;&nbsp;&nbsp;Membership Card No :</td>
            <td width="30%"><strong><?php echo $NewMembershipCardNumber; ?></strong></td>
        </tr>
        <tr>
            <td align="left">Card Type :</td>
            <td><strong><?php echo $CardName; ?></strong></td>
            <td align="left">&nbsp;&nbsp;&nbsp;Issuing Cafe :</td>
            <td><strong><?php echo $siteName; ?></strong></td>
        </tr>
        <tr>
            <td align="left">Current Points :</td>
            <td><strong><?php echo $CardPoints; ?></strong></td>
            <td align="left">&nbsp;&nbsp;&nbsp;Date and Time :</td>
            <td><strong><span id="servertime"></span></strong></td>
        </tr>
    </table>
        <hr />
     <table>
        <tr>
            <td>Name*</td>
            <td><?php echo $txtplayername; ?></td>
            <td>ID No*</td>
            <td><?php echo $txtplayerIDNumber . '<br />' . $ComboID; ?></td>
        </tr>
        <tr>
            <td>Birthdate*</td>
            <td><?php echo $dtBirthDate; ?></td>
            <td>Email Address</td>
            <td><?php echo $txtEmail; ?></td>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td>Age*</td>
            <td><?php echo $txtAge; ?></td>  
            <td>Mobile Number</td>
            <td><?php echo $txtMobile; ?></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Gender*</td>
            <td><?php echo $rdoGroupGender->Radios[0]; ?> <?php echo $rdoGroupGender->Radios[1]; ?></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>&nbsp;</td>
            <td><?php echo $CancelButton; ?> <?php echo $ConfirmButton; ?> </td>
        </tr>
    </table>
</div>
</form>

<?php if ($ActivationDialogOpen == 'true') {
    ?>
    <div id="ActivationDialog" title="Member Card Activation">
    <?php if ($isSuccess) {
        ?>

            <p>Account migration is successful. <br />                    
                Temporary password for the membership website is  <b> <?php echo $displayPassword; ?></b>.
            </p>

        <?php } else {
        ?>
            <p>Account migration has failed. <br />
                <?php echo $error; ?>.<br />
                Contact customer service for assistance.
            </p>
            <?php }
        ?>
    </div>
    <?php }
?>

    <?php if ($InvalidDialogOpen == 'true') {
        ?>
    <div id="InvalidDialog" title="Member Card Activation">
        <?php if ($IsInvalidCard) {
            ?>
            <p>The card number is invalid.<br />
               Contact customer service for assistance.
            </p>

        <?php } elseif ($HasParamError) {
        ?>
            <p>Incomplete parameters. <br />Contact customer service for assistance..</p>
            <?php }
        ?>
    </div>
    <?php }
?>
    <?php include 'nofooter.php'; ?>
