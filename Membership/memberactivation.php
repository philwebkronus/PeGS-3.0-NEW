<?php
require_once("init.inc.php");
$pagetitle = "Membership Activation";

$javascripts[] = "js/jquery.min.js";
$javascripts[] = "js/jquery-ui.min.js";
$stylesheets[] = "css/ui.themes.css";
$stylesheets[] = "css/jquery-ui.css";
$stylesheets[] = "css/smoothness/jquery-ui-1.8.16.custom.css";
$stylesheets[] = "css/tabbed.css";

App::LoadModuleClass("Membership", "Helper");
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "Occupation");
App::LoadModuleClass("Membership", "Identifications");
App::LoadModuleClass("Membership", "Nationality");
App::LoadModuleClass("Membership", "MemberServices");
App::LoadModuleClass("Membership", "MigrateMember");
App::LoadModuleClass("Loyalty", "OldCards");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Loyalty", "LoyaltyPointsTransferFromOld");
App::LoadModuleClass("Kronus", "CasinoServices");
App::LoadModuleClass("Kronus","Sites");

App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("ComboBox");
App::LoadControl("RadioGroup");
App::LoadControl("DatePicker");

$_Helper = new Helper();
$_Members = new Members();
$_Occupation = new Occupation();
$_Identification = new Identifications();
$_Nationality = new Nationality();
$_OldCards = new OldCards();
$_Cards = new Cards();
$_LoyaltyPoints = new LoyaltyPointsTransferFromOld();
$_CasinoServices = new CasinoServices();
$_MemberServices = new MemberServices();
$_MigrateMembers = new MigrateMember();
$_Sites = new Sites();

$fproc = new FormsProcessor();
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);
$ActivationDialogOpen = 'false';
$InvalidDialogOpen = 'false';
$IsInvalidCard = false;
$HasParamError = false;

$LoyatyCardNumber = "";
$NewMembershipCardNumber = "";
$CardPoints = "";
$CardName = "";
$SiteName = "";




        $dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "Birth Date: ");
        $dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
        $dtBirthDate->MinDate = $dsmindate->CurrentDate;
        $dtBirthDate->ShowCaption = false;
        
        $dtBirthDate->YearsToDisplay = "-100";
        $dtBirthDate->CssClass = "validate[required]";
        $dtBirthDate->isRenderJQueryScript = true;
        $fproc->AddControl($dtBirthDate);

        $txtplayername = new TextBox("txtplayername", "txtplayername", "Name: ");        
        $fproc->AddControl($txtplayername);
        $txtplayerage = new TextBox("txtplayerage", "txtplayerage", "Age: ");
        $fproc->AddControl($txtplayerage);
        $txtplayerIDNumber = new TextBox("txtplayerIDNumber", "txtplayerIDNumber", "I.D: ");
        $fproc->AddControl($txtplayerIDNumber);

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
        $txtAge->Size = 3;
        $txtAge->CssClass = "validate[required]";
        $fproc->AddControl($txtAge);

        $arrid = $_Identification->SelectAll();
        $ComboID = new ComboBox('ComboID', 'ComboID');
        $ComboID->DataSource = $arrid;
        $ComboID->DataSourceText = "IdentificationName";
        $ComboID->DataSourceValue = "IdentificationName";
        $ComboID->DataBind();
        $fproc->AddControl($ComboID);

        $rdoGroupGender = new RadioGroup("rdoGender", "rdoGender", "Gender");
        $rdoGroupGender->AddRadio("1", "Male");
        $rdoGroupGender->AddRadio("2", "Female", true);
        $rdoGroupGender->ShowCaption = true;
        $rdoGroupGender->Initialize();
        $fproc->AddControl($rdoGroupGender);
        
         

        $fproc->ProcessForms();
        
if ((isset($_GET["oldnumber"]) && (htmlentities($_GET["oldnumber"]))) &&
(isset($_GET["newnumber"]) && (htmlentities($_GET["newnumber"]))) &&
(isset($_GET["site"]) && (htmlentities($_GET["site"]))) &&
(isset($_GET["AID"]) && (htmlentities($_GET["AID"])))) 
{


$LoyatyCardNumber = $_GET["oldnumber"];
$NewMembershipCardNumber = $_GET["newnumber"];
$SiteName = $_GET["site"];
$AID = $_GET["AID"];

$oldcardresult = $_OldCards->getOldCardInfo($LoyatyCardNumber);
$OldCardStatus = $oldcardresult[0];
$CardStatus = $OldCardStatus['CardStatus'];



switch ($CardStatus)
{
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


////Set Default Occupation//
$where = " where Name = 'Employee' ";
$arrOccu = $_Occupation->SelectByWhere($where);
$arrOcupation = $arrOccu[0];
$arrOcupationID = $arrOcupation['OccupationID'];

////Set the Default Nationality//
$Nationality = "where Name = 'Filipino'";
$arrNation = $_Nationality->SelectByWhere($Nationality);
$arrNationality = $arrNation[0];
$arrNationantilyID = $arrNationality['NationalityID'];

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

$siteresult = $_Sites->getSiteByCode($SiteName);
$arraysite = $siteresult[0];
$site = $arraysite['SiteName'];

$dtBirthDate->SelectedDate = $oldCardBirthdate;
$txtplayername->Text = $oldCardName;    
        
     if($isValid)
     { 
         
        $rdoGroupGender->SetSelectedValue($oldCardGender);
        
        if ($fproc->IsPostBack) {
            if ($ConfirmButton->SubmittedValue == "Confirm") {
                $dateCreated = "now_usec()";
                $Memberstable["UserName"] = $oldCardEmail;
                $Memberstable["AccountTypeID"] = $_Helper->GetAccountTypeIDByName('Member');
                $Memberstable['DateCreated'] = $dateCreated;
                $Memberstable['Status'] = '1';

                $ValidIDName = $ComboID->SubmittedValue;
                $ValidIdentificationID = " where IdentificationName  = '$ValidIDName'";
                $arrValid = $_Identification->SelectByWhere($ValidIdentificationID);
                $ArrValidationID = $arrValid[0];
                $ValidIdentificationIDNumber = $ArrValidationID['IdentificationID'];

                $PlayerName = $txtplayername->SubmittedValue;

                list($fname, $lname) = explode(' ', $PlayerName, 2);

                $MemberInfo["FirstName"] = $fname;
                $MemberInfo ["LastName"] = $lname;
                $MemberInfo ["Birthdate"] = $dtBirthDate->SubmittedValue;
                $MemberInfo ["Email"] = $Memberstable["UserName"];
                $MemberInfo ["NationalityID"] = $arrNationantilyID;
                $MemberInfo ["OccupationID"] = $arrOcupationID;
                $MemberInfo["IdentificationID"] = $ValidIdentificationIDNumber;
                $MemberInfo ["IdentificationNumber"] = $txtplayerIDNumber->SubmittedValue;
                $MemberInfo ["DateCreated"] = $dateCreated;
                $rdoGroupGender->SubmittedValue == 1 ? $MemberInfo['Gender'] = 1 : $MemberInfo['Gender'] = 2;

                $_Members->Migrate($Memberstable, $MemberInfo, false);

                if (!App::HasError()) {
                    $UserName = $oldCardEmail;
                    $getMID = $_Members->getMID($UserName);
                    $arrgetMID = $getMID[0];
                    $ArrCardID = $_OldCards->getOldCardDetails($LoyatyCardNumber);
                    $ArrayOldCardID = $ArrCardID[0];
                    $ArrNewCardID = $_Cards->getCardInfo($NewMembershipCardNumber);
                    $ArrayNewCardID = $ArrNewCardID[0];

                    $arrMemberCards['MID'] = $arrgetMID['MID'];
                    $arrMemberCards['CardID'] = $ArrayNewCardID['CardID'];
                    $arrMemberCards['CardNumber'] = $ArrayNewCardID['CardNumber'];
                    $arrMemberCards['MemberCardName'] = $txtplayername->SubmittedValue;
                    $arrMemberCards['LifetimePoints'] = $ArrayOldCardID['LifetimePoints'];
                    $arrMemberCards['CurrentPoints'] = $ArrayOldCardID['CurrentPoints'];
                    $arrMemberCards['RedeemedPoints'] = $ArrayOldCardID['RedeemedPoints'];
                    $arrMemberCards['DateCreated'] = $dateCreated;
                    $arrMemberCards['CreatedByAID'] = $AID;
                    $arrMemberCards['Status'] = '1';

                    $arrCards['Status'] = '1';

                    $arrOldCards['CardStatus'] = '4';

                    $arrCardPointsTransfer['MID'] = $arrgetMID['MID'];
                    $arrCardPointsTransfer['FromOldCardID'] = $ArrayOldCardID['OldCardID'];
                    $arrCardPointsTransfer['LifeTimePoints'] = $ArrayOldCardID['LifetimePoints'];
                    $arrCardPointsTransfer['CurrentPoints'] = $ArrayOldCardID['CurrentPoints'];
                    $arrCardPointsTransfer['RedeemedPoints'] = $ArrayOldCardID['RedeemedPoints'];
                    $arrCardPointsTransfer['DateTransferred'] = $dateCreated;
                    $arrCardPointsTransfer['TransferredByAID'] = '1';
                    $arrCardPointsTransfer['OldToNew'] = '1';

                    $_LoyaltyPoints->ProcessCardPointsTransferOld($arrMemberCards, $arrCardPointsTransfer, $arrCards, $arrOldCards);

                    if (!App::HasError()) {
                        $MemberServiceMID = $arrgetMID['MID'];
                        $arrServices = $_CasinoServices->generateCasinoAccounts($MemberServiceMID);

                        $_MemberServices->CreateCasinoAccount($arrServices);

                        if (!App::HasError()) {
                            $_MigrateMembers->processCasinoAccount($MemberServiceMID);
                        }
                    }

                    $isSuccess = true;
                } 
                else 
                {
                    $isSuccess = false;
                }

                /*
                 * Load message dialog box
                 */

                $ActivationDialogOpen = 'true';

                $displayPassword = $arrgetMID['Password'];
            }
        }
     }
     else //Not valid
     {
         $InvalidDialogOpen = 'true';
         $IsInvalidCard = true;
         
     }
}
else //Parameters not set
{
    $InvalidDialogOpen = 'true';
    $HasParamError = true;
    
}
?>

<?php include 'header.php'; ?>
<?php echo $headerinfo; ?>
<?php //echo $dtBirthDate->renderJQueryScript(); ?>
<script language="javascript" type="text/javascript">   
    $(document).ready(
    function() 
    {
        dob1 = $('#dtBirthDate').val();
        dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
        var today = new Date();
        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
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
        serverdate.setSeconds(serverdate.getSeconds()+1)
        var datestring=montharray[serverdate.getMonth()]+"-"+padlength(serverdate.getDate())+"-"+serverdate.getFullYear()
        var timestring=padlength(serverdate.getHours())+":"+padlength(serverdate.getMinutes())+" "+dn
        document.getElementById("servertime").innerHTML=datestring+" "+timestring
    }

    window.onload=function(){
        setInterval("displaytime()", 1000)
    }

</script>   
<h1> Membership Card Activation </h1>
<div id ="membersactivation">
    <table>
        <tr>
            <td> VIP Rewards Card Number: <?php echo $LoyatyCardNumber; ?> </td>
            <td>Membership Card Number: <?php echo $NewMembershipCardNumber; ?></td>
            <td></td>
        </tr>
        <tr>
            <td>Card Type: <?php echo $CardName; ?>
            </td>
            <td>Issuing Cafe:<?php echo $site; ?> </td>

        </tr>
        <tr>
            <td>Current Point Balance: <?php echo $CardPoints; ?></td>

            <td>Date and Time: <span id="servertime"></span> </td>

        </tr>
    </table>
    </br>
    <table>
        <tr>
            <td>Name:  <?php echo $txtplayername; ?></td>
            <td></td>
        </tr>
        <tr>
            <td>Birthdate: <?php echo $dtBirthDate; ?></td>
            <td>I.D: <?php echo $txtplayerIDNumber; echo $ComboID;?> </td>
        </tr>
        <tr>
            <td>Age: <?php echo $txtAge; ?></td>
            <td> </td>
        </tr>
        <tr>
            <td>Gender: <?php echo $rdoGroupGender->Radios[0]; ?> <?php echo $rdoGroupGender->Radios[1]; ?> </td>
            <td><?php echo $CancelButton; ?> <?php echo $ConfirmButton; ?> </td>
        </tr>
    </table>
</div>
</form>

<?php if ($ActivationDialogOpen == 'true') 
{?>
    <div id="ActivationDialog" title="Member Card Activation">
    <?php if ($isSuccess) 
    {?>

            <p><span class="ui-icon ui-icon-circle-check" style="float: left; margin: 0 7px 50px 0;"></span>
                Account Migration Successful! <br />                    
                Temporary Password for the Membership website is  <b> <?php echo $displayPassword; ?></b>.</p>

     <?php 
     } else {?>
            <p><span class="ui-icon" style="float: left; margin: 0 7px 50px 0;"></span>
                Account Migration Failed! Please try again later. </p>
     <?php
     }?>
     </div>
 <?php 
}?>

<?php if ($InvalidDialogOpen == 'true') 
{?>
    <div id="InvalidDialog" title="Member Card Activation">
    <?php if ($IsInvalidCard) 
    {?>
            <p><span class="ui-icon ui-icon-circle-check" style="float: left; margin: 0 7px 50px 0;"></span>
                The Card is either invalid or already migrated.</p>

     <?php 
     } elseif ($HasParamError) {?>
            <p><span class="ui-icon" style="float: left; margin: 0 7px 50px 0;"></span>
                Incomplete parameters, please contact administrator.</p>
     <?php
     }?>
     </div>
 <?php 
}?>
<?php include 'footer.php'; ?>
