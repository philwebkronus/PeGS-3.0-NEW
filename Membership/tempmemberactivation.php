<?php
require_once("init.inc.php");
$pagetitle = "Temporary Membership Activation";

App::LoadModuleClass("Membership", "Members");
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


//$tempAccountCode = "eGamesLPGHT";
//$MembershipCardNumber = "UB62027560322460";

$_Members = new Members();
$_Occupation = new Occupation();
$_Identification = new Identifications();
$_Nationality = new Nationality();
$_TempMembers = new TempMembers();
$_Cards = new Cards();
$_ProcessPointsAPI = new ProcessPointsAPI();
$_MemberCards = new MemberCards();
$_Sites = new Sites();

$fproc = new FormsProcessor();
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$txtName = new TextBox("txtName", "txtName", "Name");
$txtName->ShowCaption = false;
$txtName->Length = 30;
$txtName->Size = 30;

$txtName->CssClass = "validate[required]";

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "BirthDate");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
$dtBirthDate->ShowCaption = false;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;

$fproc->AddControl($dtBirthDate);

$txtIDPresented = new TextBox("txtIDPresented", "txtIDPresented", "IDPresented");
$txtIDPresented->ShowCaption = false;
$txtIDPresented->Length = 30;
$txtIDPresented->Size = 15;
$txtIDPresented->CssClass = "validate[required]";
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
$txtAge->Size = 15;
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
    
    if($isreg == "1")
    {
        //Set Default Occupation//
        $where = " where Name = 'Employee' ";
        $arrOccu = $_Occupation->SelectByWhere($where);
        $arrOcupation = $arrOccu[0];
        $arrOcupationID  = $arrOcupation['OccupationID'];
        //Set the Default Nationality//
        $Nationality = "where Name = 'Filipino'";
        $arrNation = $_Nationality->SelectByWhere($Nationality);
        $arrNationality = $arrNation[0];
        $arrNationantilyID = $arrNationality['NationalityID'];

        $tempmemberresult = $_TempMembers->getTempMemberInfo($tempAccountCode);
        $newcardresult = $_Cards->getCardInfo($MembershipCardNumber);
        $siteresult = $_Sites->getSiteByCode($sitecode);
        
        //Check if temp & new cards exists
        if(empty($tempmemberresult))
        {
            $tempAccountCode = "";
            $createdon = "";
            $MembershipCardNumber = "";
            $site = "";
            $currentpoints = "";
            $isSuccess = false;

            $error = "Temporary Card Not Found!.";
        }
        elseif(empty($newcardresult))
        {
            $tempAccountCode = "";
            $createdon = "";
            $MembershipCardNumber = "";
            $sitecode= "";
            $currentpoints = "";
            $isSuccess = false;

            $error = "Membership Card Not Found!";
        }
        elseif(empty($siteresult))
        {
            $tempAccountCode = "";
            $createdon = "";
            $MembershipCardNumber = "";
            $sitecode = "";
            $currentpoints = "";
            $isSuccess = false;

            $error = "Site Not Found!";
        }
        //proceed to migration
        else
        {
            $tempmember = $tempmemberresult[0];
            $username = $tempmember["UserName"];
            $membername = $tempmember["FirstName"]." ".$tempmember["MiddleName"]." ".$tempmember["LastName"];
            $createdon = date("m-d-Y h:i A", strtotime($tempmember["DateCreated"]));
            $birthdate = $tempmember["Birthdate"];
            $gender = $tempmember["Gender"];
            
            $site = $siteresult[0];
            $sitename = $site["SiteName"];

            $newcard = $newcardresult[0];
            $arrchecknewcard["CardID"] = $newcard["CardID"];
            $arrchecknewcard["CardNumber"] = $newcard["CardNumber"];

            //check if temp card exist in cards table
            $tempcardresult1 = $_Cards->getCardInfo($tempAccountCode);
            if(empty($tempcardresult1))
            {
                $currentpoints = "0";
            }
            else
            {
                $tempcardnum = $tempcardresult1[0]["CardNumber"];
                $currentpointsresult = $_MemberCards->getMemberCardInfoByCard($tempcardnum);

                if(empty($currentpointsresult))
                {
                    $currentpoints = "0";
                }
                else
                {
                    $currentpoints = $currentpointsresult[0]["CurrentPoints"];
                }
            }
            
            $verifytempcard = $tempcardresult1[0]["Status"];
            $verifynewcard = $newcard["Status"];
            
            //verify card status
            if($verifytempcard != CardStatus::ACTIVE_TEMPORARY)
            {
                $tempAccountCode = "";
                $createdon = "";
                $MembershipCardNumber = "";
                $sitecode = "";
                $currentpoints = "";
                
                if($verifytempcard == CardStatus::TEMPORARY_MIGRATED)
                {
                    $isSuccess = false;
                    $error = "Migrated Temp Card!";
                }
            }
            elseif($verifynewcard != CardStatus::INACTIVE)
            {
                $tempAccountCode = "";
                $createdon = "";
                $MembershipCardNumber = "";
                $sitecode = "";
                $currentpoints = "";
                
                if($verifynewcard == CardStatus::ACTIVE)
                {
                    $isSuccess = false;
                    $error = "Membership card already Active!";
                }
                elseif($verifynewcard == CardStatus::DEACTIVATED)
                {
                    $isSuccess = false;
                    $error = "Membership card Deactived!";
                }
                elseif($verifynewcard == CardStatus::NEW_MIGRATED)
                {
                    $isSuccess = false;
                    $error = "Membership card already Migrated!";
                }
            }
            else
            {
                $txtName->Text = $membername;
                $dtBirthDate->SelectedDate = $birthdate;
                $rdoGroupGender->SetSelectedValue($gender);
                if($fproc->IsPostBack)
                {
                    $isSubmitted = true;

                    if($btnSubmit->SubmittedValue == "Confirm")
                    {
                        $datecreated = "now_usec()";

                        $tempmemcardresult = $_MemberCards->getMemberCardInfoByCard($tempAccountCode);
                        $tempmemcard = $tempmemcardresult[0];
                        $tempmemcardmid = $tempmemcard["MID"];

                        $arrMemberCards["MID"] = $tempmemcardmid;
                        $arrMemberCards["CardID"]= $arrchecknewcard["CardID"];
                        $arrMemberCards["CardNumber"] = $arrchecknewcard["CardNumber"];
                        $arrMemberCards["MemberCardName"] = $txtName->SubmittedValue;
                        $arrMemberCards["LifetimePoints"] = "0";
                        $arrMemberCards["CurrentPoints"] = "0";
                        $arrMemberCards["RedeemedPoints"] = "0";
                        $arrMemberCards["DateCreated"] = $datecreated;
                        $arrMemberCards["CreatedByAID"] = $tempmemcardmid;
                        $arrMemberCards["Status"] = "1";
                        $_MemberCards->Insert($arrMemberCards);

                        $newstatus = CardStatus::ACTIVE;
                        $arrNewCard["CardID"] = $arrchecknewcard["CardID"];
                        $arrNewCard["UpdatedByAID"] = $AID;
                        $arrNewCard["DateUpdated"] = $datecreated;
                        $arrNewCard["Status"]= $newstatus;

                        $tempcardresult2 = $_Cards->getCardInfo($tempAccountCode);
                        //$tempcardresult = $_Cards->getMemberCardInfo("eGamesXH8S2");
                        if(empty($tempcardresult2))
                        {
                            $_Cards->updateCardStatus($arrNewCard);
                        }
                        else
                        {
                            $tempstatus = CardStatus::TEMPORARY_MIGRATED;
                            $tempcard = $tempcardresult2[0];
                            $arrchecktempcard["CardID"] = $tempcard["CardID"];

                            $arrTempCard["CardID"] = $arrchecktempcard["CardID"];
                            $arrTempCard["UpdatedByAID"] = $AID;
                            $arrTempCard["DateUpdated"] = $datecreated;
                            $arrTempCard["Status"]= $tempstatus;

                            $_Cards->updateCardStatus($arrNewCard, $arrTempCard);
                        }                


                        //$valididname = $cboIDSelection->SubmittedValue;
                        //App::Pr($valididname);
                        //$valididentificationid = " where IdentificationName  = '$valididname'";
                        //$validresult = $_Identification->SelectByWhere($valididentificationid);
                        //$arrValid = $validresult[0];
                        //$validid["IdentificationID"] = $arrValid["IdentificationID"];        
                        //App::Pr($validid["IdentificationID"]);
                        $playername = $txtName->SubmittedValue;
                        list($fname, $mname, $lname) = explode(' ', $playername, 3);
                        $arrMemberInfo["MID"] = $tempmemcardmid;
                        $arrMemberInfo["FirstName"] = $fname;
                        $arrMemberInfo["MiddleName"] = $mname;
                        $arrMemberInfo ["LastName"] = $lname;
                        $arrMemberInfo ["Birthdate"] = $dtBirthDate->SubmittedValue;
                        $arrMemberInfo ["Email"] = $username;
                        $arrMemberInfo ["NationalityID"] = $arrNationantilyID;
                        $arrMemberInfo ["OccupationID"] = $arrOcupationID;
                        $arrMemberInfo["IdentificationID"] = $cboIDSelection->SubmittedValue;
                        $arrMemberInfo ["IdentificationNumber"] = $txtIDPresented->SubmittedValue;
                        $arrMemberInfo ["DateUpdated"] = $datecreated;
                        $arrMemberInfo["UpdatedByMID"] = $MID;
                        $rdoGroupGender->SubmittedValue == 1 ? $arrMemberInfo['Gender'] = 1 : $arrMemberInfo['Gender'] = 2; 
                        
                        $_Members->UpdateProfile($arrMemberInfo);

                        if(!App::HasError()){

                            $isSuccess = true;

                        }else
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
        }
    }
}
else
{
    $isSuccess = false;
    if((!isset($_GET["tempnumber"]) || $_GET["tempnumber"] == "") || 
       (!isset($_GET["newnumber"]) || $_GET["newnumber"] == "") ||
       (!isset($_GET["mid"]) || $_GET["mid"] == "") ||
       (!isset($_GET["site"]) || $_GET["site"] == "") ||
       (!isset($_GET["aid"]) || $_GET["aid"] == "") ||
       (!isset($_GET["isreg"]) || $_GET["isreg"] == "")
      )
    {
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
<h1>Membership Card Activation</h1>
<table>
    <tr>
        <td>Temporary Account Code: <?php echo $tempAccountCode; ?></td>
        <td>Membership Card Number: <?php echo $MembershipCardNumber; ?></td>
    </tr>
    <tr>
        <td>Created on: <?php echo $createdon; ?></td>
        <td>Issuing Cafe: <?php echo $sitename; ?></td>
    </tr>
    <tr>
        <td>Current Points Balance: <?php  echo $currentpoints; ?></td>
        <td>Date and Time: <span id="servertime"></span></td>
    </tr>
</table>
<hr>
<table>
    <tr>
        <td>Name:</td>
        <td><?php echo $txtName; ?></td>
        <td>&nbsp</td>
        <td>&nbsp</td>
    </tr>
    <tr>
        <td>Birthdate:</td>
        <td><?php echo $dtBirthDate; ?></td>
        <td>ID:</td>
        <td><?php echo $txtIDPresented; ?><?php echo $cboIDSelection; ?></td>
    </tr>
    <tr>
        <td>Age:</td>
        <td><?php echo $txtAge; ?></td>
        <td>&nbsp</td>
        <td>&nbsp</td>
    </tr>
    <tr>
        <td>Gender:</td>
        <td><?php echo $rdoGroupGender->Radios[0]; ?><?php echo $rdoGroupGender->Radios[1]; ?></td>
        <td>&nbsp</td>
        <td><?php echo $btnCancel; ?><?php echo $btnSubmit; ?></td>
    </tr>
</table>
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
<?php include 'footer.php'; ?>