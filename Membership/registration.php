<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */
require_once("init.inc.php");
$pagetitle = "Membership Registration";

App::LoadModuleClass("Membership", "TempMembers");
App::LoadModuleClass("Membership", "TempMemberInfo");
App::LoadModuleClass("Membership", "Identifications");
App::LoadModuleClass("Membership", "AccountTypes");
App::LoadModuleClass("Membership", "Nationality");
App::LoadModuleClass("Membership", "Occupation");
App::LoadModuleClass("Membership", "Referrer");

// Load Controls
App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("DataGrid");
App::LoadControl("ComboBox");
App::LoadControl("Button");
App::LoadControl("RadioGroup");
App::LoadControl("Radio");
App::LoadControl("CheckBox");

$fproc = new FormsProcessor();
$evt = new EventListener($fproc);
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();
$autocomplete = false;
$isOpen = 'false';
$useCustomHeader = true;

$_Members = new TempMembers();
$_AccountTypes = new AccountTypes();

$txtUserName = new TextBox("txtUserName", "txtUserName", "Username");
$txtUserName->ShowCaption = false;
$txtUserName->CssClass = "validate[required]";
$fproc->AddControl($txtUserName);

$txtFirstName = new TextBox("txtFirstName", "txtFirstName", "FirstName");
$txtFirstName->ShowCaption = false;
$txtFirstName->Length = 30;
$txtFirstName->Size = 15;
$txtFirstName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtFirstName);

$txtMiddleName = new TextBox("txtMiddleName", "txtMiddleName", "MiddleName");
$txtMiddleName->ShowCaption = false;
$txtMiddleName->Length = 30;
$txtMiddleName->Size = 15;
$txtMiddleName->CssClass = "validate[custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtMiddleName);

$txtLastName = new TextBox("txtLastName", "txtLastName", "LastName");
$txtLastName->ShowCaption = false;
$txtLastName->Length = 30;
$txtLastName->Size = 15;
$txtLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$fproc->AddControl($txtLastName);

$txtNickName = new TextBox("txtNickName", "txtNickName", "NickName");
$txtNickName->ShowCaption = false;
$txtNickName->Length = 30;
$txtNickName->Size = 15;
$txtNickName->CssClass = "validate[custom[onlyLetterSp]]";
$fproc->AddControl($txtNickName);

$txtMobileNumber = new TextBox("txtMobileNumber", "txtMobileNumber", "MobileNumber");
$txtMobileNumber->ShowCaption = false;
$txtMobileNumber->Length = 30;
$txtMobileNumber->Size = 15;
$txtMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtMobileNumber);

$txtAlternateMobileNumber = new TextBox("txtAlternateMobileNumber", "txtAlternateMobileNumber", "AlternateMobileNumber");
$txtAlternateMobileNumber->ShowCaption = false;
$txtAlternateMobileNumber->Length = 30;
$txtAlternateMobileNumber->Size = 15;
$txtAlternateMobileNumber->CssClass = "validate[custom[onlyNumber], minSize[9]]";
$txtAlternateMobileNumber->AutoComplete = false;
$fproc->AddControl($txtAlternateMobileNumber);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password");
$txtPassword->ShowCaption = false;
$txtPassword->Length = 40;
$txtPassword->Size = 15;
$txtPassword->Password = true;
$txtPassword->CssClass = "validate[required, custom[onlyLetterNumber], minSize[5]]";
$txtPassword->AutoComplete = false;
$fproc->AddControl($txtPassword);

$txtConfirmPassword = new TextBox("txtConfirmPassword", "txtConfirmPassword", "ConfirmPassword");
$txtConfirmPassword->ShowCaption = false;
$txtConfirmPassword->Length = 40;
$txtConfirmPassword->Size = 15;
$txtConfirmPassword->Password = true;
$txtConfirmPassword->CssClass = "validate[required, custom[onlyLetterNumber], equals[txtPassword]]";
$fproc->AddControl($txtConfirmPassword);

$txtEmail = new TextBox("txtEmail", "txtEmail", "Email");
$txtEmail->ShowCaption = false;
$txtEmail->Length = 30;
$txtEmail->Size = 15;
$txtEmail->CssClass = "validate[required, custom[email]]";
$fproc->AddControl($txtEmail);

$txtAlternateEmail = new TextBox("txtAlternateEmail", "txtAlternateEmail", "Username");
$txtAlternateEmail->ShowCaption = false;
$txtAlternateEmail->Length = 30;
$txtAlternateEmail->Size = 15;
$txtAlternateEmail->CssClass = "validate[custom[email]]";
$fproc->AddControl($txtAlternateEmail);

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "Birth Date: ");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
//$dtBirthDate->SelectedDate = $dsmaxdate->PreviousDate;
$dtBirthDate->ShowCaption = false;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;
$fproc->AddControl($dtBirthDate);

$txtAddress1 = new TextBox("txtAddress1", "txtAddress1", "Address1");
$txtAddress1->ShowCaption = false;
$txtAddress1->Length = 30;
$txtAddress1->Size = 15;
//$txtAddress1->CssClass = "validate[required]";
$fproc->AddControl($txtAddress1);

$txtAddress2 = new TextBox("txtAddress2", "txtAddress2", "Address2");
$txtAddress2->ShowCaption = false;
$txtAddress2->Length = 30;
$txtAddress2->Size = 15;
$fproc->AddControl($txtAddress2);

$txtIDPresented = new TextBox("txtIDPresented", "txtIDPresented", "IDPresented");
$txtIDPresented->ShowCaption = false;
$txtIDPresented->Length = 30;
$txtIDPresented->Size = 15;
$txtIDPresented->CssClass = "validate[required, custom[onlyLetterNumber]]";
$fproc->AddControl($txtIDPresented);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Register");
$btnSubmit->IsSubmit = true;
$btnSubmit->CssClass = "btnDefault roundedcorners yellow-btn";
$fproc->AddControl($btnSubmit);

$_identifications = new Identifications();
$arrids = $_identifications->SelectAll();
$cboIDSelection = new ComboBox("cboIDSelection", "cboIDSelection", "cboIDSelection");
$cboIDSelection->ShowCaption = false;
$cboIDSelection->DataSource = $arrids;
$cboIDSelection->DataSourceText = "IdentificationName";
$cboIDSelection->DataSourceValue = "IdentificationID";
$cboIDSelection->DataBind();
$fproc->AddControl($cboIDSelection);

$_Referrer = new Referrer();
$arrReferrer = $_Referrer->SelectAll();
$cboHowDidYouHear = new ComboBox("cboHowDidYouHear", "cboHowDidYouHear", "cboHowDidYouHear");
$cboHowDidYouHear->ShowCaption = false;
$cboHowDidYouHear->DataSource = $arrReferrer;
$cboHowDidYouHear->DataSourceText = "Name";
$cboHowDidYouHear->DataSourceValue = "ReferrerID";
$cboHowDidYouHear->DataBind();
$fproc->AddControl($cboHowDidYouHear);

$txtAge = new TextBox("txtAge", "txtAge", "Age");
$txtAge->ShowCaption = false;
$txtAge->Length = 30;
$txtAge->Size = 15;
$txtAge->CssClass = "validate[required]";
$txtAge->ReadOnly = true;
$fproc->AddControl($txtAge);

$_nationality = new Nationality();
$arrnationality = $_nationality->SelectAll();
$cboNationality = new ComboBox("cboNationality", "cboNationality", "cboNationality");
$cboNationality->ShowCaption = false;
$cboNationality->DataSource = $arrnationality;
$cboNationality->DataSourceText = "Name";
$cboNationality->DataSourceValue = "NationalityID";
$cboNationality->DataBind();
$fproc->AddControl($cboNationality);

$_Occupation = new Occupation();
$arrOccupation = $_Occupation->SelectAll();
$cboOccupation = new ComboBox("cboOccupation", "cboOccupation", "cboOccupation");
$cboOccupation->ShowCaption = false;
$cboOccupation->DataSource = $arrOccupation;
$cboOccupation->DataSourceText = "Name";
$cboOccupation->DataSourceValue = "OccupationID";
$cboOccupation->DataBind();
$fproc->AddControl($cboOccupation);

$chkEmailNotification = new CheckBox("chkEmailNotification", "chkEmailNotification", "Please send me offers, bonuses and casino announcements by email.");
$chkEmailNotification->ShowCaption = true;
$chkEmailNotification->Value = 1;
$fproc->AddControl($chkEmailNotification);

$chkSMSNotification = new CheckBox("chkSMSNotification", "chkSMSNotification", "Please send me offers, bonuses and casino announcements by SMS.");
$chkSMSNotification->ShowCaption = true;
$chkSMSNotification->Value = 1;
$fproc->AddControl($chkSMSNotification);

$chkConfirmAge = new CheckBox("chkConfirmAge", "chkConfirmAge", "");
$chkConfirmAge->ShowCaption = true;
$chkConfirmAge->Caption = "I hereby confirm that I am at least 21 years old and have read and accepted the <a href='#'>Terms and Conditions</a>.";
$chkConfirmAge->CssClass = 'validate[required]';
$fproc->AddControl($chkConfirmAge);

$rdoGroupGender = new RadioGroup("rdoGender", "rdoGender", "Gender");
$rdoGroupGender->AddRadio("1", "Male", true);
$rdoGroupGender->AddRadio("2", "Female");
$rdoGroupGender->ShowCaption = true;
$rdoGroupGender->Initialize();
$fproc->AddControl($rdoGroupGender);

$rdoGroupSmoker = new RadioGroup("rdoGroupSmoker", "rdoGroupSmoker", "rdoGroupSmoker");
$rdoGroupSmoker->AddRadio("1", "Smoker", true);
$rdoGroupSmoker->AddRadio("2", "Non-Smoker");
$rdoGroupSmoker->ShowCaption = true;
$rdoGroupSmoker->Initialize();
$fproc->AddControl($rdoGroupSmoker);

$fproc->ProcessForms();

$datecreated = "now_usec()";
$isEmailUnique = true;

App::GetErrorMessage();

if ($fproc->IsPostBack)
{
    if ($btnSubmit->SubmittedValue == "Register")
    {
        $arrMembers["UserName"] = $txtEmail->SubmittedValue;
        $arrMembers["Password"] = md5($txtPassword->SubmittedValue);
        //$arrMembers["AccountTypeID"] = $_AccountTypes->GetAccountTypeIDByName(AccountTypes::MEMBER);
        $arrMembers["ForChangePassword"] = 1;
        $arrMembers["DateCreated"] = $datecreated;
        $arrMembers["Status"] = 1;

        $arrMemberInfo["FirstName"] = $txtFirstName->SubmittedValue;
        $arrMemberInfo["MiddleName"] = $txtMiddleName->SubmittedValue;

        //$arrMemberInfo['Password'] = '288472173';
        $arrMemberInfo['LastName'] = $txtLastName->SubmittedValue;
        $arrMemberInfo['Address1'] = $txtAddress1->SubmittedValue;
        $arrMemberInfo['Address2'] = $txtAddress2->SubmittedValue;
        $arrMemberInfo['IdentificationNumber'] = $txtIDPresented->SubmittedValue;
        $arrMemberInfo['IdentificationID'] = $cboIDSelection->SubmittedValue;
        $arrMemberInfo['NickName'] = $txtNickName->SubmittedValue;
        $arrMemberInfo['MobileNumber'] = $txtMobileNumber->SubmittedValue;
        $arrMemberInfo['AlternateMobileNumber'] = $txtAlternateMobileNumber->SubmittedValue;
        $arrMemberInfo['Email'] = $txtEmail->SubmittedValue;
        $arrMemberInfo['AlternateEmail'] = $txtAlternateEmail->SubmittedValue;
        $arrMemberInfo['Birthdate'] = $dtBirthDate->SubmittedValue;
        $arrMemberInfo['NationalityID'] = $cboNationality->SubmittedValue;
        $arrMemberInfo['OccupationID'] = $cboOccupation->SubmittedValue;

        $arrMemberInfo['Gender'] = $rdoGroupGender->SubmittedValue;
        $arrMemberInfo['IsSmoker'] = $rdoGroupSmoker->SubmittedValue;

        $arrMemberInfo['DateCreated'] = 'now_usec()';

        $chkEmailNotification->SubmittedValue == 1 ? $arrMemberInfo['EmailSubscription'] = 1 : $arrMemberInfo['EmailSubscription'] = 0;
        $chkSMSNotification->SubmittedValue == 1 ? $arrMemberInfo['SMSSubscription'] = 1 : $arrMemberInfo['SMSSubscription'] = 0;

        $_Members->Register($arrMembers, $arrMemberInfo);

        $isOpen = 'true';
        if (!App::HasError())
        {
            $isSuccess = true;
        }
        else
        {
            $isSuccess = false;
            if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0)
            {
                $isEmailUnique = false;
                App::SetErrorMessage("Email already exists. Please choose a different email address.");
                $isOpen = false;
            }
        }

        
    }
}
?>
<?php include 'header.php'; ?>
<?php echo $dtBirthDate->renderJQueryScript(); ?>
<script language="javascript" type="text/javascript">
    $(document).ready(
    function() 
    {
        $('#dtBirthDate').change(function()
        {
            //alert($('#dtBirthDate').val());
            dob1 = $('#dtBirthDate').val();
            dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
            var today = new Date();
            var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
            $('#txtAge').val(age);
        });
        
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Registration',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                    window.location = "index.php";
                }
            }
        });
    });
</script>
<span class="pagesubtitle">Registration</span>
<table class="registrationtable">
    <tr>
        <td width="20%">First Name*</td>
        <td width="30%"><?php echo $txtFirstName; ?></td>
        <td width="20%">Nickname</td>
        <td width="30%"><?php echo $txtNickName; ?></td>
    </tr>
    <tr>
        <td>Middle Name</td>
        <td><?php echo $txtMiddleName; ?></td>
        <td>Mobile Number*</td>
        <td><?php echo $txtMobileNumber; ?></td>
    </tr>
    <tr>
        <td>Last Name*</td>
        <td><?php echo $txtLastName; ?></td>
        <td>Alternate Mobile Number</td>
        <td><?php echo $txtAlternateMobileNumber; ?></td>
    </tr>
    <tr>
        <td>Password*</td>
        <td><?php echo $txtPassword; ?></td>
        <td>Email Address*</td>
        <td><?php echo $txtEmail; ?></td>
    </tr>
    <tr>
        <td>Confirm Password*</td>
        <td><?php echo $txtConfirmPassword; ?></td>
        <td>Alternate Email</td>
        <td><?php echo $txtAlternateEmail; ?></td>
    </tr>
    <tr>
        <td>Permanent Address</td>
        <td><?php echo $txtAddress1; ?><br/>
            <?php echo $txtAddress2; ?><br/></td>
        <td>Gender</td>
        <td><?php echo $rdoGroupGender->Radios[0]; ?> <?php echo $rdoGroupGender->Radios[1]; ?></td>
    </tr>
    <tr>
        <td>ID No.*</td>
        <td><?php echo $txtIDPresented; ?></td>
        <td>Birthdate*</td>
        <td><?php echo $dtBirthDate; ?></td>
    </tr>
    <tr>
        <td>ID Presented*</td>
        <td><?php echo $cboIDSelection; ?></td>
        <td>Age</td>
        <td><?php echo $txtAge; ?></td>
    </tr>
    <tr>
        <td colspan="2">How did you hear about e-Games?<br/>
            <?php echo $cboHowDidYouHear; ?></td>
        <td>Nationality</td>
        <td><?php echo $cboNationality; ?></td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td>Occupation</td>
        <td><?php echo $cboOccupation; ?></td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td><?php echo $rdoGroupSmoker->Radios[0]; ?></td>
        <td><?php echo $rdoGroupSmoker->Radios[1]; ?></td>
    </tr>
    <tr>
        <td colspan="4"><?php echo $chkEmailNotification; ?></td>
    </tr>
    <tr>
        <td colspan="4"><?php echo $chkSMSNotification; ?></td>
    </tr>
    <tr>
        <td colspan="4"><?php echo $chkConfirmAge; ?></td>
    </tr>
    <tr>
        <td colspan="4"><?php echo $btnSubmit; ?></td>
    </tr>
</table>

<div id="SuccessDialog" name="SuccessDialog">
    <?php if ($isOpen == 'true')
    { ?>
        <?php if ($isSuccess)
        { ?>
            <p style="text-align: justify; text-justify: inter-word;">
                You have successfully registered! <br /><br />
                An active Temporary Account will be sent to your email address or mobile number,
                which can be used to start session/credit points in the absence of Membership Card.<br /><br />
                Please note that your Registered Account and Temporary Account will be activated only
                after 24 hours.<br />
            </p>
            <?php
        }
        else
        {
            if ($isEmailUnique)
            {
                ?>
                <p style="text-align: justify; text-justify: inter-word;">
                    Registration Failed. A problem was encountered during registration. Please retry or contact Customer Support.
                </p>
                <?php
            }
            if (!$isEmailUnique)
            {
                ?>
                <p style="text-align: justify; text-justify: inter-word;">
                    Registration Failed<br /><br />
                    Email already exists. Please choose a different email address.
                </p>
                <?php
            }
        }
    }
    ?>
</div>
<?php include 'footer.php'; ?>