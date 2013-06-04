<?php
require_once("init.inc.php");
$pagetitle = "Temporary Membership Activation";

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "Occupation");
App::LoadModuleClass("Membership", "Identifications");
App::LoadModuleClass("Membership", "Nationality");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Membership", "TempMembers");
App::LoadModuleClass("Loyalty", "ProcessPointsAPI");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Kronus", "Sites");

//Load Controls
App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("ComboBox");
App::LoadControl("Button");
App::LoadControl("RadioGroup");

$useCustomHeader = false;
//$tempAccountCode = "eGamesLPGHT";
//$MembershipCardNumber = "UB62027560322460";

$customtags[] = "<BASE target=\"_self\" />";

$_Members = new Members();
$_MemberInfo = new MemberInfo();
$_Cards = new Cards();
$_MemberCards = new MemberCards();
$_Sites = new Sites();

$fproc = new FormsProcessor();
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$txtName = new TextBox("txtName", "txtName", "Name");
$txtName->ShowCaption = false;
$txtName->Length = 90;
$txtName->Size = 30;
$txtName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "BirthDate");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
$dtBirthDate->ShowCaption = false;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;
$dtBirthDate->Size = 27;
$fproc->AddControl($dtBirthDate);

$txtIDPresented = new TextBox("txtIDPresented", "txtIDPresented", "IDPresented");
$txtIDPresented->ShowCaption = false;
$txtIDPresented->Length = 30;
$txtIDPresented->Size = 31;
$txtIDPresented->CssClass = "validate[required, custom[onlyLetterNumber]]";
$fproc->AddControl($txtIDPresented);

$_identifications = new Identifications();
$arrids = $_identifications->SelectAll();
$cboIDSelection = new ComboBox("cboIDSelection", "cboIDSelection", "cboIDSelection");
$cboIDSelection->ShowCaption = false;
$cboIDSelection->DataSource = $arrids;
$cboIDSelection->DataSourceText = "IdentificationName";
$cboIDSelection->DataSourceValue = "IdentificationID";
$cboIDSelection->DataBind();
$fproc->AddControl($cboIDSelection);

$txtAge = new TextBox("txtAge", "txtAge", "Age");
$txtAge->ReadOnly = true;
$txtAge->Length = 30;
$txtAge->Size = 2;
$fproc->AddControl($txtAge);

$rdoGroupGender = new RadioGroup("rdoGender", "rdoGender", "Gender");
$rdoGroupGender->AddRadio("1", "Male",true);
$rdoGroupGender->AddRadio("2", "Female");
$rdoGroupGender->ShowCaption = true;
$rdoGroupGender->Initialize();
$fproc->AddControl($rdoGroupGender);

$btnCancel = new Button ("btnCancel","btnCancel", "Cancel");
$$btnCancel->IsSubmit=true;
$btnCancel->CssClass="btnDefault roundedcorners";
$fproc->AddControl($btnCancel);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Confirm");
$btnSubmit->IsSubmit = true;
$btnSubmit->CssClass = "btnDefault roundcorners";
$fproc->AddControl($btnSubmit);

$fproc->AddControl($txtName);

$fproc->ProcessForms();

//Set default value
$tempAccountCode = "";
$createdon = "";
$MembershipCardNumber = "";
$sitecode = "";
$sitename ="";
$currentpoints = "";
$isSubmitted = false;
    
if((isset($_GET["tempnumber"]) && (htmlentities($_GET["tempnumber"]))) && 
(isset($_GET["newnumber"]) && htmlentities($_GET["newnumber"])) && 
(isset($_GET["mid"]) && htmlentities($_GET["mid"])) &&
(isset($_GET["site"]) && htmlentities($_GET["site"])) &&
(isset($_GET["aid"]) && htmlentities($_GET["aid"])) &&
(isset($_GET["isreg"]) && htmlentities($_GET["isreg"])))
{
    $isSuccess = true;
    $tempAccountCode = $_GET["tempnumber"];
    $MembershipCardNumber = $_GET["newnumber"];
    $MID = $_GET["mid"];
    $sitecode = $_GET["site"];
    $AID = $_GET["aid"];
    $isreg = $_GET["isreg"];
    
    if($isreg == 1)
    {    
        $memberinfo = $_MemberInfo->getMemberInfo($MID);
        $siteresult = $_Sites->getSiteByCode($sitecode);
                
        $row = $memberinfo[0];
  
        $username = $row["UserName"];
        $membername = $row["FirstName"]." ".$row["MiddleName"]." ".$row["LastName"];
        $createdon = date("m-d-Y h:i A", strtotime($row["DateCreated"]));
        $birthdate = $row["Birthdate"];
        $gender = $row["Gender"];

        $site = $siteresult[0];
        $sitename = $site["SiteName"];

        $cardinfo = $_MemberCards->getMemberCardInfo($MID);

        if(count($cardinfo) > 0)
        {
            $currentpoints = $cardinfo[0]["CurrentPoints"];
        }
        else
        {            
            $currentpoints = "0";
        }
            
        $txtName->Text = $membername;
        $dtBirthDate->SelectedDate = $birthdate;
        $rdoGroupGender->SetSelectedValue($gender);

        if($fproc->IsPostBack)
        {
            $isSubmitted = true;

            if($btnSubmit->SubmittedValue == "Confirm")
            {
                $datecreated = "now_usec()";
                
                $tempcardresult = $_Cards->getCardInfo($tempAccountCode); //getMemberCardInfoByCard($MembershipCardNumber);
                $tempcardinfo = $tempcardresult[0];
                $tempcardid = $tempcardinfo["CardID"];
                
                $cardresult = $_Cards->getCardInfo($MembershipCardNumber); //getMemberCardInfoByCard($MembershipCardNumber);
                $cardinfo = $cardresult[0];
                $cardid = $cardinfo["CardID"];

                $arrMemberCards["MID"] = $MID;
                $arrMemberCards["CardID"]= $cardid;
                $arrMemberCards["CardNumber"] = $MembershipCardNumber;
                $arrMemberCards["LifetimePoints"] = "0";
                $arrMemberCards["CurrentPoints"] = "0";
                $arrMemberCards["RedeemedPoints"] = "0";
                $arrMemberCards["DateCreated"] = $datecreated;
                $arrMemberCards["CreatedByAID"] = $AID;
                $arrMemberCards["Status"] = CardStatus::ACTIVE;
                
                $arrTempMemberCards["MemberCardID"] = $tempcardid;
                $arrTempMemberCards["Status"] = CardStatus::TEMPORARY_MIGRATED;
                $arrTempMemberCards["UpdatedByAID"] = $AID;
                $arrTempMemberCards["DateUpdated"] = $datecreated;
                
                $_MemberCards->processMemberCard($arrMemberCards, $arrTempMemberCards);

                if(!App::HasError())
                {
                    $arrNewCard["CardID"] = $cardid;
                    $arrNewCard["UpdatedByAID"] = $AID;
                    $arrNewCard["DateUpdated"] = $datecreated;
                    $arrNewCard["Status"]= CardStatus::ACTIVE;

                    $arrTempCard["CardID"] = $tempcardid;
                    $arrTempCard["UpdatedByAID"] = $AID;
                    $arrTempCard["DateUpdated"] = $datecreated;
                    $arrTempCard["Status"]= CardStatus::TEMPORARY_MIGRATED;

                    $_Cards->updateCardStatus($arrNewCard, $arrTempCard);
                    
                    if(!App::HasError())
                    {
                        $playername = $txtName->SubmittedValue;
                        $list = explode(' ', $playername, 3);
                        
                        if(count($list) == 1)
                        {
                            $fname = $list[0];
                            $mname = "";
                            $lname = "";
                        }
                        
                        if(count($list) == 2)
                        {
                            $fname = $list[0];
                            $mname = $list[1];
                            $lname = "";
                        }
                        
                        if(count($list) == 3)
                        {
                            $fname = $list[0];
                            $mname = $list[1];
                            $lname = $list[2];
                        }
                        
                        if(count($list) > 3)
                        {
                            $fname = $list[0];
                            $mname = $list[1];
                            $lname = $list[2] . ' ' . $list[3];
                        }
                        
                        $arrMemberInfo["MID"] = $MID;
                        $arrMemberInfo["FirstName"] = $fname;
                        $arrMemberInfo["MiddleName"] = $mname;
                        $arrMemberInfo["LastName"] = $lname;
                        $arrMemberInfo["Birthdate"] = $dtBirthDate->SubmittedValue;
                        $arrMemberInfo["Email"] = $username;
                        $arrMemberInfo["IdentificationID"] = $cboIDSelection->SubmittedValue;
                        $arrMemberInfo["IdentificationNumber"] = $txtIDPresented->SubmittedValue;
                        $arrMemberInfo["DateUpdated"] = $datecreated;
                        $arrMemberInfo["UpdatedByAID"] = $AID;
                        $rdoGroupGender->SubmittedValue == 1 ? $arrMemberInfo['Gender'] = 1 : $arrMemberInfo['Gender'] = 2; 

                        $_Members->UpdateProfile($arrMemberInfo);

                        if(!App::HasError()){

                            $isSuccess = true;

                        }else
                        {
                            $isSuccess = false;
                        }
                    }
                    else
                    {
                        $isSuccess = false;
                    }                    
                }
                else
                {
                    $isSuccess = false;
                }
                
            }
        }
        else
        {
            $isSubmitted = false;
        }
    }
    else
    {
        $isSuccess = false;
        $error = "One or more parameters have no values.";
    }
}
?>
<?php include 'header.php'; ?>
<?php //echo $dtBirthDate->renderJQueryScript(); ?>
<script language="javascript" type="text/javascript">
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
        
        $('#dialog').dialog(
        {
            modal: true,
            show: 'fade',
            hide: 'fade',
            resizable: false,
            draggable: false,
            buttons: {OK: function(){$(this).dialog('close'); window.close()}}
        });
        $('#dialog').dialog('widget').find('.ui-dialog-titlebar-close').hide();
        
        $('#dialog1').dialog(
        {
            modal: true,
            //show: 'fade',
            //hide: 'fade',
            resizable: false,
            draggable: false,
            buttons: {OK: function(){$(this).dialog('close'); window.close()}}
        });
        $('#dialog1').dialog('widget').find('.ui-dialog-titlebar-close').hide();
        //$('#dialog').dialog('open');
        
        $('#dtBirthDate').change(function()
        {
            //alert($('#dtBirthDate').val());
            dob1 = $('#dtBirthDate').val();
            dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
            var today = new Date();
            var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
            $('#txtAge').val(age);
        });
        $('#btnCancel').click(function()
        {
            window.close();
        });
        
        $('#btnSubmit').click(function()
        {
            $('#dialog').dialog('open');
            $('#dialog1').dialog('open');
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
<div id="membersactivation">
<h1>Membership Card Activation</h1>
<table>
    <tr>
        <td width="20%">Temp Account Code </td>
        <td width="30%"><strong><?php echo $tempAccountCode; ?></strong></td>
        <td width="25%">Membership Card No </td>
        <td width="25%"><strong><?php echo $MembershipCardNumber; ?></strong></td>
    </tr>
    <tr>
        <td>Created On </td>
        <td><strong><?php echo $createdon; ?></strong></td>
        <td>Issuing Cafe </td>
        <td><strong><?php echo $sitename; ?></strong></td>
    </tr>
    <tr>
        <td>Current Points </td>
        <td><strong><?php  echo $currentpoints; ?></strong></td>
        <td>Date and Time </td>
        <td><strong><span id="servertime"></span></strong></td>
    </tr>
</table>
<hr>
<table>
    <tr>
        <td>Name</td>
        <td><?php echo $txtName; ?></td>
        <td colspan="2">&nbsp;&nbsp;ID Number</td>
    </tr>
    <tr>
        <td>Birthdate</td>
        <td><?php echo $dtBirthDate; ?></td>
        <td>&nbsp;</td>
        <td><?php echo $txtIDPresented; ?> <br /> <?php echo $cboIDSelection; ?></td>
    </tr>
    <tr>
        <td>Age</td>
        <td><?php echo $txtAge; ?></td>
        <td colspan="2">&nbsp</td>
    </tr>
    <tr>
        <td>Gender</td>
        <td><?php echo $rdoGroupGender->Radios[0]; ?><?php echo $rdoGroupGender->Radios[1]; ?></td>
        <td>&nbsp</td>
        <td><?php echo $btnCancel; ?><?php echo $btnSubmit; ?></td>
    </tr>
</table>
</div>
<?php 
if($isSubmitted){

    if($isSuccess) 
    {?>

    <!-- <script>alert('Temporary Account Migration Successful!'); </script>-->
    <div id="dialog" title="Membership" style="display:none">
    <center><h2><?php echo "Temporary Account Migration Successful!"; ?></h2></center>
    </div>
    <?php
    }else{?>
        <!-- <script>alert('Temporary Account Migration Failed!');</script>-->
    <div id="dialog1" title="Membership" style="display:none">
    <center><h2><?php echo "Temporary Account Migration Failed!"; ?></h2></center>
    </div>
    <?php
    }
}
else
{
    if(!$isSuccess) 
    {?>

    <script>alert('<?php echo $error; ?>'); </script>
    <?php
    }

}?>
<?php include 'nofooter.php'; ?>