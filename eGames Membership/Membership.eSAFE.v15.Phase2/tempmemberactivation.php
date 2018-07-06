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
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "BlackLists");

//Load Controls
App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("ComboBox");
App::LoadControl("Button");
App::LoadControl("RadioGroup");

//Load Core
App::LoadCore('ErrorLogger.php');

$useCustomHeader = false;

$customtags[] = "<BASE target=\"_self\" />";

$_Members = new Members();
$_MemberInfo = new MemberInfo();
$_Cards = new Cards();
$_MemberCards = new MemberCards();
$_Sites = new Sites();
$_Log = new AuditTrail();
$_BlackLists = new BlackLists();

$fproc = new FormsProcessor();
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

$txtName = new TextBox("txtName", "txtName", "Name");
$txtName->ShowCaption = false;
$txtName->Length = 90;
$txtName->Size = 30;
$txtName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtName);

$txtFName = new TextBox("txtFName", "txtFName", "FName");
$txtFName->ShowCaption = false;
$txtFName->Length = 90;
$txtFName->Size = 20;
$txtFName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtFName);


$txtMName = new TextBox("txtMName", "txtMName", "MName");
$txtMName->ShowCaption = false;
$txtMName->Length = 20;
$txtMName->Size = 20;
$txtMName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtMName);

$txtLName = new TextBox("txtLName", "txtLName", "LName");
$txtLName->ShowCaption = false;
$txtLName->Length = 20;
$txtLName->Size = 20;
$txtLName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtLName);

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
$txtIDPresented->Size = 21;
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
$rdoGroupGender->AddRadio("1", "Male", true);
$rdoGroupGender->AddRadio("2", "Female");
$rdoGroupGender->ShowCaption = true;
$rdoGroupGender->Initialize();
$fproc->AddControl($rdoGroupGender);

$btnCancel = new Button("btnCancel", "btnCancel", "Cancel");
$$btnCancel->IsSubmit = true;
$btnCancel->CssClass = "btnDefault roundedcorners";
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
$sitename = "";
$currentpoints = "";
$isSubmitted = false;

if ((isset($_GET["tempnumber"]) && (htmlentities($_GET["tempnumber"]))) &&
        (isset($_GET["newnumber"]) && htmlentities($_GET["newnumber"])) &&
        (isset($_GET["mid"]) && htmlentities($_GET["mid"])) &&
        (isset($_GET["site"]) && htmlentities($_GET["site"])) &&
        (isset($_GET["aid"]) && htmlentities($_GET["aid"])) &&
        (isset($_GET["isreg"]) && htmlentities($_GET["isreg"]))) {
    $isSuccess = true;
    $tempAccountCode = trim($_GET["tempnumber"]);
    $MembershipCardNumber = trim($_GET["newnumber"]);
    $MID = trim($_GET["mid"]);
    $sitecode = trim($_GET["site"]);
    $AID = trim($_GET["aid"]);
    $isreg = trim($_GET["isreg"]);

    if ($isreg == 1) {
        $memberinfo = $_MemberInfo->getGenericInfo($MID);
        $memberinfo1 = $_MemberInfo->getMemberByMID($MID);
        $memberinfo2 = $_MemberInfo->getMemberDtlsByMID($MID);

        $siteresult = $_Sites->getSiteByCode($sitecode);

        $row = $memberinfo[0];
        $row1 = $memberinfo1;
        $row2 = $memberinfo2;

        $username = $row1[0];
        $membername = $row2[0] . " " . $row2[1] . " " . $row2[2];
        $fname = $row2[0];
        $mname = $row2[1];
        $lname = $row2[2];
        $createdon = date("m-d-Y H:i", strtotime($row["DateCreated"]));
        $birthdate = $row["Birthdate"];
        $gender = $row["Gender"];
        $idpresented = $row2[3];

        $site = $siteresult[0];
        $sitename = $site["SiteName"];
        $siteid = $site["SiteID"];

        $cardinfo = $_MemberCards->getMemberCardInfo($MID);

        if (count($cardinfo) > 0) {
            //Check if Loyalty                                     
            $isLoyalty = App::getParam('PointSystem');

            if ($isLoyalty == 1) {
                $currentpoints = $cardinfo[0]["CurrentPoints"];
            } else {
                App::LoadModuleClass("Loyalty", "GetCardInfoAPI");

                $_GetCardInfoAPI = new GetCardInfoAPI();
                $compPoints = $_GetCardInfoAPI->getCompPoints($tempAccountCode);
                $currentpoints = $compPoints;
            }
        } else {
            $currentpoints = "0";
        }

        $txtName->Text = $membername;
        $txtFName->Text = $fname;
        $txtMName->Text = $mname;
        $txtLName->Text = $lname;
        $dtBirthDate->SelectedDate = $birthdate;
        $rdoGroupGender->SetSelectedValue($gender);
        $txtIDPresented->Text = $idpresented;

        if ($fproc->IsPostBack) {
            $isSubmitted = true;

            if ($btnSubmit->SubmittedValue == "Confirm") {
                $datecreated = "NOW(6)";

                $isBlacklisted = $_BlackLists->checkIfExistSP($txtLName->SubmittedValue, $txtFName->SubmittedValue, $dtBirthDate->SubmittedValue, 4);

                if ($isBlacklisted == 0) {

                    $tempcardresult = $_Cards->getCardInfo($tempAccountCode); //getMemberCardInfoByCard($MembershipCardNumber);
                    $tempcardinfo = $tempcardresult[0];
                    $tempcardid = $tempcardinfo["CardID"];

                    $tempcardresult2 = $_MemberCards->getMemberCardInfoByCard($tempAccountCode);
                    $tempcarddetails = $tempcardresult2[0];


                    //Check if Loyalty                                     
                    $isLoyalty = App::getParam('PointSystem');

                    if ($isLoyalty == 1) {

                        $lifetimePoints = $tempcarddetails["LifetimePoints"];
                        $currentpoints = $tempcarddetails["CurrentPoints"];
                        $redeemedpoints = $tempcarddetails["RedeemedPoints"];
                    } else {
                        App::LoadModuleClass("Loyalty", "GetCardInfoAPI");

                        $_GetCardInfoAPI = new GetCardInfoAPI();

                        $compPoints = $_GetCardInfoAPI->getCompPoints($tempAccountCode);

                        //Add points from old loyalty card to lifetime and current points
                        $lifetimePoints = $compPoints;
                        $currentpoints = $compPoints;
                        $redeemedpoints = 0;
                    }

                    $cardresult = $_Cards->getCardInfo($MembershipCardNumber); //getMemberCardInfoByCard($MembershipCardNumber);
                    $cardinfo = $cardresult[0];
                    $cardid = $cardinfo["CardID"];

                    $arrMemberCards["MID"] = $MID;
                    $arrMemberCards["CardID"] = $cardid;
                    $arrMemberCards["SiteID"] = $siteid;
                    $arrMemberCards["CardNumber"] = $MembershipCardNumber;
                    $arrMemberCards["LifetimePoints"] = $lifetimePoints;
                    $arrMemberCards["CurrentPoints"] = $currentpoints;
                    $arrMemberCards["RedeemedPoints"] = $redeemedpoints;
                    $arrMemberCards["DateCreated"] = $datecreated;
                    $arrMemberCards["CreatedByAID"] = $AID;
                    $arrMemberCards["Status"] = CardStatus::ACTIVE;

                    $arrTempMemberCards["MemberCardID"] = $tempcardid;
                    $arrTempMemberCards["Status"] = CardStatus::TEMPORARY_MIGRATED;
                    $arrTempMemberCards["UpdatedByAID"] = $AID;
                    $arrTempMemberCards["DateUpdated"] = $datecreated;

                    $_MemberCards->processMemberCard($arrMemberCards, $arrTempMemberCards);

                    if (!App::HasError()) {
                        $arrNewCard["CardID"] = $cardid;
                        $arrNewCard["UpdatedByAID"] = $AID;
                        $arrNewCard["DateUpdated"] = $datecreated;
                        $arrNewCard["Status"] = CardStatus::ACTIVE;

                        $arrTempCard["CardID"] = $tempcardid;
//                    $arrTempCard["SiteID"] = $siteid; need to double check the saving of site id
                        //$arrTempCard["SiteID"] = 1;
                        $arrTempCard["UpdatedByAID"] = $AID;
                        $arrTempCard["DateUpdated"] = $datecreated;
                        $arrTempCard["Status"] = CardStatus::TEMPORARY_MIGRATED;

                        $_Cards->updateCardStatus($arrNewCard, $arrTempCard);

                        if (!App::HasError()) {
                            /* $playername = $txtName->SubmittedValue;
                              $list = explode(' ', $playername, 3);

                              if (count($list) == 1) {
                              $fname = $list[0];
                              $mname = "";
                              $lname = "";
                              }

                              if (count($list) == 2) {
                              $fname = $list[0];
                              $mname = $list[1];
                              $lname = "";
                              }

                              if (count($list) == 3) {
                              $fname = $list[0];
                              $mname = $list[1];
                              $lname = $list[2];
                              }

                              if (count($list) > 3) {
                              $fname = $list[0];
                              $mname = $list[1];
                              $lname = $list[2] . ' ' . $list[3];
                              }
                             */
                            $arrMemberInfo["MID"] = $MID;
                            $arrMemberInfo["FirstName"] = $txtFName->SubmittedValue;
                            $arrMemberInfo["MiddleName"] = $txtMName->SubmittedValue;
                            $arrMemberInfo["LastName"] = $txtLName->SubmittedValue;
                            $arrMemberInfo["Birthdate"] = $dtBirthDate->SubmittedValue;
                            $arrMemberInfo["Email"] = $username;
                            $arrMemberInfo["IdentificationID"] = $cboIDSelection->SubmittedValue;
                            $arrMemberInfo["IdentificationNumber"] = $txtIDPresented->SubmittedValue;
                            $arrMemberInfo["DateUpdated"] = $datecreated;
                            $arrMemberInfo["UpdatedByAID"] = $AID;
                            $rdoGroupGender->SubmittedValue == 1 ? $arrMemberInfo['Gender'] = 1 : $arrMemberInfo['Gender'] = 2;

                            $r = $_Members->UpdateMemberProfile($arrMemberInfo);

                            if ($r['TransCode'] == 0) {

                                $isSuccess = true;
                                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $tempAccountCode . ':' . $MembershipCardNumber . ':Success', $sitecode, $AID);
                            } else {
                                $isSuccess = false;
                                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $tempAccountCode . ':' . $MembershipCardNumber . ':Failed', $sitecode, $AID);
                                $error = "Failed to update member profile in memberinfo table";
                                $logger->logger($logdate, $logtype, $error);
                            }
                        } else {
                            $isSuccess = false;
                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $tempAccountCode . ':' . $MembershipCardNumber . ':Failed', $sitecode, $AID);
                            $error = "Failed to update card status";
                            $logger->logger($logdate, $logtype, $error);
                        }
                    } else {
                        $isSuccess = false;
                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $tempAccountCode . ':' . $MembershipCardNumber . ':Failed', $sitecode, $AID);
                        $error = "Failed to insert in membercards table";
                        $logger->logger($logdate, $logtype, $error);
                    }
                } else {
                    $isSuccess = false;
                    $isSubmitted = false;
                    $error = "Applicant is on the National Database of Restricted Persons List. Please ask Player to contact Customer Service Hotline.";
                    $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $tempAccountCode . ':' . $MembershipCardNumber . ':Failed', $sitecode, $AID);
                    $logger->logger($logdate, $logtype, $error);
	            echo "<script>alert(" . $error . ");</script>";

                    $txtName->Text = $membername;
                    $txtFName->Text = $txtFName->SubmittedValue;
                    $txtMName->Text = $txtMName->SubmittedValue;
                    $txtLName->Text = $txtLName->SubmittedValue;
                    $dtBirthDate->SelectedDate = $dtBirthDate->SubmittedValue;
                    $rdoGroupGender->SetSelectedValue($rdoGroupGender->SubmittedValue);
                    $txtIDPresented->Text = $txtIDPresented->SubmittedValue;
                }
            }
        } else {
            $isSubmitted = false;
            $error = "Values not submitted";
            $logger->logger($logdate, $logtype, $error);
        }
    } else {
        $isSuccess = false;
        $error = "One or more parameters have no values.";
        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $tempAccountCode . ':' . $MembershipCardNumber . ':Failed', $sitecode, $AID);
        $isSubmitted = false;
        $error = "Parameters are not set";
        $logger->logger($logdate, $logtype, $error);
    }
}
?>
<?php include 'header.php'; ?>
<?php //echo $dtBirthDate->renderJQueryScript();                       ?>
<script language="javascript" type="text/javascript">
    $(document).ready(
            function()
            {
                dob1 = $('#dtBirthDate').val();
                dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) - 1, dob1.substr(8, 2));
                var today = new Date();
                var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
                if (isNaN(age))
                {
                    var age = '';
                }
                $('#txtAge').val(age);

                $('#dialog').dialog(
                        {
                            modal: true,
                            //show: 'fade',
                            height: 250,
                            width: 350,
                            //hide: 'fade',
                            resizable: false,
                            draggable: false,
                            buttons: {OK: function() {
                                    $(this).dialog('close');
                                    window.close()
                                }}
                        });
                $('#dialog').dialog('widget').find('.ui-dialog-titlebar-close').hide();

                $('#dialog1').dialog(
                        {
                            modal: true,
                            //show: 'fade',
                            //hide: 'fade',
                            height: 250,
                            width: 350,
                            resizable: false,
                            draggable: false,
                            buttons: {OK: function() {
                                    $(this).dialog('close');
                                    window.close()
                                }}
                        });
                $('#dialog1').dialog('widget').find('.ui-dialog-titlebar-close').hide();
                //$('#dialog').dialog('open');

                $('#dtBirthDate').change(function()
                {
                    //alert($('#dtBirthDate').val());
                    dob1 = $('#dtBirthDate').val();
                    dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) - 1, dob1.substr(8, 2));
                    var today = new Date();
                    var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
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
    var montharray = new Array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12")
    var serverdate = new Date(currenttime)

    var hours = serverdate.getHours();
    var dn = "AM";

    if (hours >= 12)
    {
        dn = "PM";
    }
    if (hours > 12) {
        hours = hours - 12
    }
    if (hours == 0)
    {
        hours = 12;
    }

    function padlength(what) {
        var output = (what.toString().length == 1) ? "0" + what : what
        return output
    }

    function displaytime() {
        serverdate.setSeconds(serverdate.getSeconds() + 1)
        var datestring = montharray[serverdate.getMonth()] + "-" + padlength(serverdate.getDate()) + "-" + serverdate.getFullYear()
        var timestring = padlength(serverdate.getHours()) + ":" + padlength(serverdate.getMinutes())
        document.getElementById("servertime").innerHTML = datestring + " " + timestring
    }

    window.onload = function() {
        setInterval("displaytime()", 1000)
    }
</script>
<div id="membersactivation">
    <h1>Membership Card Activation</h1>
    <table>
        <tr>
            <td align="left" width="20%">Temp Acct Code :</td>
            <td width="20%"><strong><?php echo $tempAccountCode; ?></strong></td>
            <td width="30%" align="left">&nbsp;&nbsp;&nbsp;&nbsp;Membership Card No :</td>
            <td width="30%"><strong><?php echo $MembershipCardNumber; ?></strong></td>
        </tr>
        <tr>
            <td align="left">Created On :</td>
            <td width="25%"><strong><?php echo $createdon; ?></strong></td>
            <td align="left">&nbsp;&nbsp;&nbsp;&nbsp;Issuing Cafe :</td>
            <td><strong><?php echo $sitename; ?></strong></td>
        </tr>
        <tr>
            <td align="left">Current Points :</td>
            <td><strong><?php echo $currentpoints; ?></strong></td>
            <td align="left">&nbsp;&nbsp;&nbsp;&nbsp;Date and Time :</td>
            <td><strong><span id="servertime"></span></strong></td>
        </tr>
    </table>
    <hr>
    <table>
        <tr>
            <td>First Name</td>
            <td><?php echo $txtFName; ?></td>
            <td>ID No</td>
            <td><?php echo $txtIDPresented; ?> <br /> <?php echo $cboIDSelection; ?></td>
        </tr>
        <tr>
            <td>Middle Name</td>
            <td><?php echo $txtMName; ?></td>
            <td>Birthdate</td>
            <td><?php echo $dtBirthDate; ?></td>
        </tr>
        <tr>
            <td>Last Name</td>
            <td><?php echo $txtLName; ?></td>
            <td>Age</td>
            <td><?php echo $txtAge; ?></td>
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
if ($isSubmitted) {

    if ($isSuccess) {
        ?>

                                                                                   <!-- <script>alert('Temporary Account Migration Successful!'); </script>-->
        <div id="dialog" title="Membership">
            <p>Temporary Account Migration Successful!</p>
        </div>
    <?php } else {
        ?>

                                        <!--<script > alert('Temporary Account Migration Failed!');</script>-->
        <div id="dialog1" title="Membership">
            <p>Temporary Account Migration Failed!</p>
        </div>
        <?php
    }
} else {
    if (!$isSuccess) {
        ?>

        <script>alert('<?php echo $error; ?>');</script>
        <?php
    }
}
?>
<?php include 'nofooter.php'; ?>
