<?php
/*
 * @author : owliber
 * @date : 2013-05-17
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Membership Administration";
$currentpage = "Membership Profile";

App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass('Membership', 'MembershipTemp');
App::LoadModuleClass('Loyalty', 'CardVersion');
App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass("Membership", "Identifications");
App::LoadModuleClass("Membership", "Nationality");
App::LoadModuleClass("Membership", "Occupation");
App::LoadModuleClass("Membership", "Referrer");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "Members");

App::LoadCore('Validation.class.php');

App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("DataGrid");
App::LoadControl("ComboBox");
App::LoadControl("Button");
App::LoadControl("RadioGroup");
App::LoadControl("Radio");
App::LoadControl("CheckBox");
App::LoadControl("Hidden");

App::LoadCore('ErrorLogger.php');

$_MemberInfo = new MemberInfo();
$_Members = new Members();
$_MemberCards = new MemberCards();
$_MemberTemp = new MembershipTemp();
$_Log = new AuditTrail();

$fproc = new FormsProcessor();

$evt = new EventListener($fproc);
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$autocomplete = false;
$isOpen = 'false'; //Hide dialog box
$showcardinfo = false;
$showprofile = false;

$readonly = true;
$isenabled = false;
$accounttypeid = $_SESSION['userinfo']['AccountTypeID'];

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

//only pegs ops and marketing are allowed to update player profile
if ($accounttypeid == 8 || $accounttypeid == 13) {
    $readonly = false;
    $isenabled = true;
}
/*
 * Profile Objects
 */
$txtFirstName = new TextBox("txtFirstName", "txtFirstName", "FirstName");
$txtFirstName->ReadOnly = $readonly;
$txtFirstName->ShowCaption = false;
$txtFirstName->Length = 30;
$txtFirstName->Size = 30;
$txtFirstName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtFirstName);

$txtMiddleName = new TextBox("txtMiddleName", "txtMiddleName", "MiddleName");
$txtMiddleName->ReadOnly = $readonly;
$txtMiddleName->ShowCaption = false;
$txtMiddleName->Length = 30;
$txtMiddleName->Size = 30;
$txtMiddleName->CssClass = "validate[custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtMiddleName);

$txtLastName = new TextBox("txtLastName", "txtLastName", "LastName");
$txtLastName->ReadOnly = $readonly;
$txtLastName->ShowCaption = false;
$txtLastName->Length = 30;
$txtLastName->Size = 30;
$txtLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtLastName);

$txtNickName = new TextBox("txtNickName", "txtNickName", "NickName");
$txtNickName->ReadOnly = $readonly;
$txtNickName->ShowCaption = false;
$txtNickName->Length = 10;
$txtNickName->Size = 10;
$txtNickName->CssClass = "validate[custom[onlyLetterSp]]";
$fproc->AddControl($txtNickName);

$txtMobileNumber = new TextBox("txtMobileNumber", "txtMobileNumber", "MobileNumber");
$txtMobileNumber->ReadOnly = $readonly;
$txtMobileNumber->ShowCaption = false;
$txtMobileNumber->Length = 13;
$txtMobileNumber->Size = 13;
$txtMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtMobileNumber);

$txtAlternateMobileNumber = new TextBox("txtAlternateMobileNumber", "txtAlternateMobileNumber", "AlternateMobileNumber");
$txtAlternateMobileNumber->ReadOnly = $readonly;
$txtAlternateMobileNumber->ShowCaption = false;
$txtAlternateMobileNumber->Length = 13;
$txtAlternateMobileNumber->Size = 13;
$txtAlternateMobileNumber->AutoComplete = false;
$txtAlternateMobileNumber->CssClass = "validate[custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtAlternateMobileNumber);

$txtEmail = new TextBox("txtEmail", "txtEmail", "Email");
$txtEmail->ShowCaption = false;
$txtEmail->Length = 100;
$txtEmail->Size = 40;
//$txtEmail->Args = 'onkeypress="javascript: return checkemail(txtEmail)"';
$txtEmail->CssClass = "validate[required, custom[email]]";
$fproc->AddControl($txtEmail);

$hdntxtEmail = new Hidden("hdntxtEmail", "hdntxtEmail", "Email");
$fproc->AddControl($hdntxtEmail);

$txtAlternateEmail = new TextBox("txtAlternateEmail", "txtAlternateEmail", "Username");
$txtAlternateEmail->ReadOnly = $readonly;
$txtAlternateEmail->ShowCaption = false;
$txtAlternateEmail->Length = 100;
$txtAlternateEmail->Size = 40;
$txtAlternateEmail->CssClass = "validate[custom[email]]";
$fproc->AddControl($txtAlternateEmail);

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "Birth Date: ");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
$dtBirthDate->ShowCaption = false;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;
$fproc->AddControl($dtBirthDate);

$txtAddress1 = new TextBox("txtAddress1", "txtAddress1", "Address1");
$txtAddress1->ReadOnly = $readonly;
$txtAddress1->ShowCaption = false;
$txtAddress1->Length = 100;
$txtAddress1->Size = 43;
$txtAddress1->CssClass = "validate[custom[address]]";
$fproc->AddControl($txtAddress1);

$txtAddress2 = new TextBox("txtAddress2", "txtAddress2", "Address2");
$txtAddress2->ReadOnly = $readonly;
$txtAddress2->ShowCaption = false;
$txtAddress2->Length = 100;
$txtAddress2->Size = 43;
$txtAddress2->CssClass = "validate[custom[address]]";
$fproc->AddControl($txtAddress2);

$txtIDPresented = new TextBox("txtIDPresented", "txtIDPresented", "IDPresented");
$txtIDPresented->ReadOnly = $readonly;
$txtIDPresented->ShowCaption = false;
$txtIDPresented->Length = 30;
$txtIDPresented->Size = 30;
$txtIDPresented->CssClass = "validate[required, custom[onlyLetterNumber]]";
$fproc->AddControl($txtIDPresented);

$_identifications = new Identifications();
$arrids = $_identifications->SelectAll();
$cboIDSelection = new ComboBox("cboIDSelection", "cboIDSelection", "cboIDSelection");
$cboIDSelection->ShowCaption = false;
$cboIDSelection->DataSource = $arrids;
$cboIDSelection->DataSourceText = "IdentificationName";
$cboIDSelection->DataSourceValue = "IdentificationID";
$cboIDSelection->Enabled = $isenabled;
$cboIDSelection->DataBind();
$fproc->AddControl($cboIDSelection);

$txtAge = new TextBox("txtAge", "txtAge", "Age");
$txtAge->ReadOnly = $readonly;
$txtAge->ShowCaption = false;
$txtAge->Length = 3;
$txtAge->Size = 3;
$txtAge->CssClass = "validate[required]";
$fproc->AddControl($txtAge);

$_nationality = new Nationality();
$arrnationality = $_nationality->SelectAll();
$cboNationality = new ComboBox("cboNationality", "cboNationality", "cboNationality");
$cboNationality->ShowCaption = false;
$cboNationality->DataSource = $arrnationality;
$cboNationality->DataSourceText = "Name";
$cboNationality->DataSourceValue = "NationalityID";
$cboNationality->Enabled = $isenabled;
$cboNationality->DataBind();
$fproc->AddControl($cboNationality);

$_Occupation = new Occupation();
$arrOccupation = $_Occupation->SelectAll();
$cboOccupation = new ComboBox("cboOccupation", "cboOccupation", "cboOccupation");
$cboOccupation->ShowCaption = false;
$cboOccupation->DataSource = $arrOccupation;
$cboOccupation->DataSourceText = "Name";
$cboOccupation->DataSourceValue = "OccupationID";
$cboOccupation->Enabled = $isenabled;
$cboOccupation->DataBind();
$fproc->AddControl($cboOccupation);

$rdoGroupGender = new RadioGroup("rdoGender", "rdoGender", "Gender");
$rdoGroupGender->AddRadio("1", "Male", true);
$rdoGroupGender->AddRadio("2", "Female");
$rdoGroupGender->ShowCaption = true;
$rdoGroupGender->Enabled = $isenabled;
$rdoGroupGender->ReadOnly = $readonly;
$rdoGroupGender->Initialize();
$fproc->AddControl($rdoGroupGender);

$rdoGroupSmoker = new RadioGroup("rdoGroupSmoker", "rdoGroupSmoker", "rdoGroupSmoker");
$rdoGroupSmoker->AddRadio("1", "Smoker", true);
$rdoGroupSmoker->AddRadio("2", "Non-Smoker");
$rdoGroupSmoker->ShowCaption = true;
$rdoGroupSmoker->Enabled = $isenabled;
$rdoGroupSmoker->Initialize();
$rdoGroupGender->Args = "onclick='\"window.close()\"'";
$fproc->AddControl($rdoGroupSmoker);

$btnUpdate = new Button('btnUpdate', 'btnUpdate', 'Update');
$btnUpdate->ShowCaption = true;
$btnUpdate->IsSubmit = true;
$btnUpdate->Enabled = $isenabled;
$fproc->AddControl($btnUpdate);

$hdnMID = new Hidden('hdnMID', 'hdnMID');
$fproc->AddControl($hdnMID);
include_once("controller/cardsearchcontroller.php");

/*
 * End Profile Objects
 */

$fproc->ProcessForms();

$result = null;

if (isset($_SESSION['CardRed'])) {
    unset($_SESSION['CardRed']);
}

if ($fproc->IsPostBack) {
    $showcardinfo = true;

    if (count($result) > 0) {
        $_SESSION['CardInfo']['MID'] = $MID;

        $row = $result[0];

        $hdnMID->Text = $MID;
        $txtFirstName->Text = $row['FirstName'];
        $txtMiddleName->Text = $row['MiddleName'];
        $txtLastName->Text = $row['LastName'];
        $txtNickName->Text = $row['NickName'];
        $txtMobileNumber->Text = $row['MobileNumber'];
        $txtAlternateMobileNumber->Text = $row['AlternateMobileNumber'];
        $rdoGroupGender->SetSelectedValue($row['Gender']);
        $rdoGroupSmoker->SetSelectedValue($row['IsSmoker']);
        $dtBirthDate->SelectedDate = $row['Birthdate'];
        $txtEmail->Text = $row['Email'];
        $hdntxtEmail->Text = $row['Email'];
        $txtAlternateEmail->Text = $row['AlternateEmail'];
        $txtAddress1->Text = $row['Address1'];
        $txtAddress2->Text = $row['Address2'];
        $txtIDPresented->Text = $row['IdentificationNumber'];
        $cboIDSelection->SetSelectedValue($row['IdentificationID']);
        $cboOccupation->SetSelectedValue($row['OccupationID']);
        $txtAge->Text = number_format((abs(strtotime($row['Birthdate']) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365), 0);
        $cboNationality->SetSelectedValue($row['NationalityID']);
    }

    if ($btnUpdate->SubmittedValue == 'Update') {
        $dateupdated = 'now_usec()';
        $arrMembers["DateUpdated"] = $dateupdated;

        $arrMembers["MID"] = $hdnMID->SubmittedValue;
        $arrMemberInfo["FirstName"] = $txtFirstName->SubmittedValue;
        $arrMemberInfo["MiddleName"] = $txtMiddleName->SubmittedValue;
        $arrMemberInfo['LastName'] = $txtLastName->SubmittedValue;
        $arrMemberInfo['NickName'] = $txtNickName->SubmittedValue;
        $arrMemberInfo['Email'] = $txtEmail->SubmittedValue;
        $arrMemberInfo['Address1'] = $txtAddress1->SubmittedValue;
        $arrMemberInfo['Address2'] = $txtAddress2->SubmittedValue;
        $arrMemberInfo['MobileNumber'] = $txtMobileNumber->SubmittedValue;
        $arrMemberInfo['AlternateMobileNumber'] = $txtAlternateMobileNumber->SubmittedValue;
        $arrMemberInfo['AlternateEmail'] = $txtAlternateEmail->SubmittedValue;
        $arrMemberInfo['Birthdate'] = $dtBirthDate->SubmittedValue;
        $arrMemberInfo['NationalityID'] = $cboNationality->SubmittedValue;
        $arrMemberInfo['OccupationID'] = $cboOccupation->SubmittedValue;

        $arrMemberInfo['IdentificationID'] = $cboIDSelection->SubmittedValue;
        $arrMemberInfo['IdentificationNumber'] = $txtIDPresented->SubmittedValue;

        $arrMemberInfo['Gender'] = $rdoGroupGender->SubmittedValue;
        $arrMemberInfo['IsSmoker'] = $rdoGroupSmoker->SubmittedValue;
        $HiddenMID = $hdnMID->SubmittedValue;
        $SubmittedEmail = $txtEmail->SubmittedValue;
        $_SESSION['HiddenEmail'] = $hdntxtEmail->SubmittedValue;

        //check if from old to new migrated card
        if (!is_null($_SESSION['HiddenEmail'])) {

            $tempMID = $_MemberTemp->getMID($_SESSION['HiddenEmail']);
            if(empty($tempMID)){
                $tempMID = 0;
            } else {
            foreach ($tempMID as $value) {
                $tempMID = $value['MID'];
            }
            }
            
            $emailcountz = $_MemberTemp->checkIfEmailExistsWithMID($tempMID, $SubmittedEmail);
            if ($emailcountz > 0) {
                foreach ($emailcountz as $value) {
                    $emailcount = $value['COUNT'];
                }
            } else {
                $emailcount = 0;
            }
            $emailcounty = $_MemberInfo->checkIfEmailExistsWithMID($HiddenMID, $SubmittedEmail);
            if ($emailcounty > 0) {
                foreach ($emailcounty as $value) {
                    $emailcounty = $value['COUNT'];
                }
            } else {
                $emailcounty = 0;
            }
        } else {
            $emailcount = 0;
            $emailcounty = 0;
        }
        $loyaltyinfo = $_MemberCards->getDeactivatedStatusByMID($HiddenMID);
        
        if(!empty($loyaltyinfo)){
            $message = "Sorry, Unable to update this card, Card is already Deactivated.";
            $isSuccess = false;
        }
        else{
            if (($emailcount > 0)||($emailcounty > 0)) {
                $message = "Sorry, " . $arrMemberInfo['Email'] . " already belongs to an existing account. Please enter another email address!";
                $isSuccess = false;
            } else {
                //Proceed with the update profile
                $_MemberInfo->StartTransaction();
                $_MemberInfo->updateProfileAdmin($HiddenMID, $arrMemberInfo);
                $CommonPDOConn = $_MemberInfo->getPDOConnection();
                $_Members->setPDOConnection($CommonPDOConn);
                if (App::HasError()) {
                    $_MemberInfo->RollBackTransaction();
                    $error = $_Members->errormessage;
                    $logger->logger($logdate, $logtype, $error);
                    $isSuccess = false;
                } else {
                    $_Members->setPDOConnection($CommonPDOConn);
                    $_Members->updateMemberUsernameAdmin($HiddenMID, $SubmittedEmail);
                    if ($_Members->HasError) {
                        $_MemberInfo->RollBackTransaction();
                        $error = $_Members->errormessage;
                        $logger->logger($logdate, $logtype, $error);
                        $isSuccess = false;
                    } else {
                        //if does not exists in membership_temp
                        if ($tempMID == 0) {
                            $isSuccess = true;
                            if (($_MemberInfo->AffectedRows > 0)||($_Members->AffectedRows > 0)) {
                                $_MemberInfo->CommitTransaction();
                                $_MemberInfo->updateProfileDateUpdatedAdmin($HiddenMID, $arrMemberInfo, $aid);
                                $retMsg = 'Profile updated successfully!';
                            } else {
                                $retMsg = 'Profile unchanged!';
                            }
                            unset($_SESSION['HiddenEmail']);
                            $_Log->logEvent(AuditFunctions::UPDATE_PROFILE, 'MID:' . $arrMembers["MID"] . ':Successful', array('ID' => $_SESSION['userinfo']['AID'], 'SessionID' => $_SESSION['userinfo']['SessionID']));
                        }
                        //if does exists in membership_temp
                        else {
                            $_MemberTemp->setPDOConnection($CommonPDOConn);
                            $_MemberTemp->updateTempProfileEmailAdmin($SubmittedEmail, $_SESSION['HiddenEmail']);
                            if ($_MemberTemp->HasError) {
                                $_MemberInfo->RollBackTransaction();
                                $error = $_MemberTemp->errormessage;
                                $logger->logger($logdate, $logtype, $error);
                                $isSuccess = false;
                            } else {
                                $_MemberTemp->updateTempMemberUsernameAdmin($SubmittedEmail, $_SESSION['HiddenEmail']);
                                if ($_MemberTemp->HasError) {
                                    $_MemberInfo->RollBackTransaction();
                                    $error = $_MemberTemp->errormessage;
                                    $logger->logger($logdate, $logtype, $error);
                                    $isSuccess = false;
                                } else {
                                    $isSuccess = true;
                                    if (($_MemberInfo->AffectedRows > 0)||($_Members->AffectedRows > 0)||($_MemberTemp->AffectedRows > 0)) {
                                        $_MemberInfo->CommitTransaction();
                                        $_MemberInfo->updateProfileDateUpdatedAdmin($HiddenMID, $arrMemberInfo, $aid);
                                        $_MemberTemp->updateTempProfileDateUpdatedAdmin($HiddenMID, $arrMemberInfo, $aid);
                                        $retMsg = 'Profile updated successfully!';
                                    } else {
                                        $retMsg = 'Profile unchanged!';
                                    }
                                    unset($_SESSION['HiddenEmail']);
                                    $_Log->logEvent(AuditFunctions::UPDATE_PROFILE, 'MID:' . $arrMembers["MID"] . ':Successful', array('ID' => $_SESSION['userinfo']['AID'], 'SessionID' => $_SESSION['userinfo']['SessionID']));
                                }
                            }
                        }
                    }
                }
            }
        }
        /*
         * Load message dialog box
         */
        $isOpen = 'true';
    }
}

if (isset($_SESSION['CardInfo'])) {
    $showcardinfo = true;
    $showprofile = true;

    if (isset($_SESSION['CardInfo']['Username'])) {
        $result = $_MemberInfo->getMemberInfoByUsername($_SESSION['CardInfo']['Username']);
        $MID = $result[0]['MID'];
    } else {
        $membercards = $_MemberCards->getMemberCardInfoByCard($_SESSION['CardInfo']['CardNumber']);
        $MID = $membercards[0]['MID'];

        $result = $_MemberInfo->getMemberInfo($MID);
    }

    $row = $result[0];

    $hdnMID->Text = $MID;
    $txtFirstName->Text = $row['FirstName'];
    $txtMiddleName->Text = $row['MiddleName'];
    $txtLastName->Text = $row['LastName'];
    $txtNickName->Text = $row['NickName'];
    $txtMobileNumber->Text = $row['MobileNumber'];
    $txtAlternateMobileNumber->Text = $row['AlternateMobileNumber'];
    $rdoGroupGender->SetSelectedValue($row['Gender']);
    $rdoGroupSmoker->SetSelectedValue($row['IsSmoker']);
    $dtBirthDate->SelectedDate = $row['Birthdate'];
    if (!empty($row['Email'])) {
        $txtEmail->Text = $row['Email'];
        $hdntxtEmail->Text = $row['Email'];
    } else {
        $txtEmail->Text = $row['Email'];
        $hdntxtEmail->Text = $row['Email'];
        $txtEmail->ReadOnly = false;
    }

    $txtAlternateEmail->Text = $row['AlternateEmail'];
    $txtAddress1->Text = $row['Address1'];
    $txtAddress2->Text = $row['Address2'];
    $txtIDPresented->Text = $row['IdentificationNumber'];
    $cboIDSelection->SetSelectedValue($row['IdentificationID']);
    $cboOccupation->SetSelectedValue($row['OccupationID']);
    $txtAge->Text = number_format((abs(strtotime($row['Birthdate']) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365), 0);
    $cboNationality->SetSelectedValue($row['NationalityID']);
    unset($_SESSION['CardInfo']);
}
?>
<?php include('header.php'); ?>
<?php echo $dtBirthDate->renderJQueryScript(); ?>
<script>
    $(document).ready(function() {
        $('#dtBirthDate').change(function()
        {
            dob1 = $('#dtBirthDate').val();
            dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) - 1, dob1.substr(8, 2));
            var today = new Date();
            var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
            $('#txtAge').val(age);
        });

        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title: 'Update Profile',
            closeOnEscape: true,
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                }
            }
        });

        $("#frmProfile").validationEngine();
    });
</script>
<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
         <?php include('cardsearch.php'); ?>
        <div class="content">
           

            <form name="frmProfile" id="frmProfile" method="post" action="" />

            <div class="result">             
                <?php
                if ((!empty($btnSearch->SubmittedValue) || !empty($btnUpdate->SubmittedValue) || isset($_SESSION['CardInfo'])) && $showprofile) {
                    ?>
                    <div class="title">Account Information</div>
                    <table>
                        <tr>
                            <td>First Name*</td>
                            <td><?php echo $txtFirstName; ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>Primary Email Address*</td>
                            <td><?php echo $txtEmail; ?></td>
                            <?php echo $hdntxtEmail; ?>
                        </tr>
                        <tr>
                            <td>Middle Name</td>
                            <td><?php echo $txtMiddleName; ?></td>
                            <td>Secondary Email Address</td>
                            <td><?php echo $txtAlternateEmail; ?></td>
                        </tr>
                        <tr>                        
                            <td>Last Name*</td>
                            <td><?php echo $txtLastName; ?></td>
                            <td>Mobile Number*</td>
                            <td><?php echo $txtMobileNumber; ?></td>
                        <tr>                        
                            <td>Nick Name</td>
                            <td><?php echo $txtNickName; ?></td>
                            <td>Alternate Mobile Number</td>
                            <td><?php echo $txtAlternateMobileNumber; ?></td>
                        </tr>
                        <tr>
                            <td>Permanent Address</td>
                            <td><?php echo $txtAddress1; ?><br/>
                                <?php echo $txtAddress2; ?><br/></td>
                            <td>Gender</td>
                            <td><?php echo $rdoGroupGender->Radios[0]; ?><br />
                                <?php echo $rdoGroupGender->Radios[1]; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>ID Presented*</td>
                            <td><?php echo $txtIDPresented; ?><br/>
                                <?php echo $cboIDSelection; ?></td>
                            <td>Birthdate*</td>
                            <td><?php echo $dtBirthDate; ?></td>
                        </tr>
                        <tr>
                            <td>Nationality</td>
                            <td><?php echo $cboNationality; ?></td>
                            <td>Age</td>
                            <td><?php echo $txtAge; ?></td>
                        </tr>
                        <tr>
                            <td>Occupation</td>
                            <td><?php echo $cboOccupation; ?></td>
                            <td><?php echo $rdoGroupSmoker->Radios[0]; ?>
                            </td>
                            <td><?php echo $rdoGroupSmoker->Radios[1]; ?></td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <br/>
                                <?php echo $btnUpdate; ?>
                                <?php echo $hdnMID; ?>
                            </td>
                        </tr>
                    </table> 
                <?php }
                ?>
            </div>
        </div>
        <div id="SuccessDialog" name="SuccessDialog">
            <?php if ($isOpen == 'true') {
                ?>
                <?php if ($isSuccess) {
                    ?>
                    <p>
                        <?php echo $retMsg; ?>
                    </p>
                    <?php
                } else {
                    ?>
                    <p>
                        <?php echo $message; ?>
                    </p>
                <?php }
                ?>
            <?php }
            ?>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>