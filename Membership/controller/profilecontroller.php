<?php
/*
 * @author : owliber
 * @date : 2013-04-19
 */

if (!isset($_SESSION["MemberInfo"]["Member"]["MID"]))
{
    App::SetErrorMessage("Account Banned");
    reloadParent();
}

$MID = $_SESSION["MemberInfo"]["Member"]["MID"];

/**
 * Load Models
 */
$_MemberInfo = new MemberInfo();
$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Sites = new Sites();

if (!isset($fproc))
{
    $fproc = new FormsProcessor();
}

$evt = new EventListener($fproc);
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();
$validate = new Validation();
$autocomplete = false;
$isOpen = 'false'; //Hide dialog box

$memberinfo = $_MemberInfo->getMemberInfo($MID);
$arrmemberinfo = $memberinfo[0];

$cardinfo = $_MemberCards->getActiveMemberCardInfo($MID);

if (!isset($cardinfo[0]['CardNumber']))
{

    //session_destroy();
    unset($_SESSION['MemberInfo']);
    App::SetErrorMessage("Account Banned");
    reloadParent();
}

$cardNumber = $cardinfo[0]['CardNumber'];

$points = $_MemberCards->getMemberPoints($cardNumber);

(!empty($arrmemberinfo['NickName']) ? $nick = $arrmemberinfo['NickName'] : $nick = $arrmemberinfo['FirstName']);

/**
 * Member Info
 */
$memberName = $arrmemberinfo['FirstName'] . ' ' . $arrmemberinfo['MiddleName'] . ' ' . $arrmemberinfo['LastName'];
$mobileNumber = $arrmemberinfo['MobileNumber'];
$email = $arrmemberinfo['Email'];

/**
 * Loyalty Points
 */
$currentPoints = $points[0]['CurrentPoints'];
$lifetimePoints = $points[0]['LifetimePoints'];
$bonusPoints = $points[0]['BonusPoints'];
$redeemedPoints = $points[0]['RedeemedPoints'];


/**
 * Member Profile
 */
$txtFirstName = new TextBox("txtFirstName", "txtFirstName", "FirstName");
$txtFirstName->ShowCaption = false;
$txtFirstName->Length = 30;
$txtFirstName->Size = 15;
$txtFirstName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$txtFirstName->Text = $arrmemberinfo['FirstName'];
$fproc->AddControl($txtFirstName);

$txtMiddleName = new TextBox("txtMiddleName", "txtMiddleName", "MiddleName");
$txtMiddleName->ShowCaption = false;
$txtMiddleName->Length = 30;
$txtMiddleName->Size = 15;
$txtMiddleName->CssClass = "validate[custom[onlyLetterSp], minSize[2]]";
;
$txtMiddleName->Text = $arrmemberinfo['MiddleName'];
$fproc->AddControl($txtMiddleName);

$txtLastName = new TextBox("txtLastName", "txtLastName", "LastName");
$txtLastName->ShowCaption = false;
$txtLastName->Length = 30;
$txtLastName->Size = 15;
$txtLastName->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
;
$txtLastName->Text = $arrmemberinfo['LastName'];
$fproc->AddControl($txtLastName);

$txtNickName = new TextBox("txtNickName", "txtNickName", "NickName");
$txtNickName->ShowCaption = false;
$txtNickName->Length = 30;
$txtNickName->Size = 15;
$txtNickName->Text = $arrmemberinfo['NickName'];
$txtNickName->CssClass = "validate[custom[onlyLetterSp]]";
$fproc->AddControl($txtNickName);

$txtMobileNumber = new TextBox("txtMobileNumber", "txtMobileNumber", "MobileNumber");
$txtMobileNumber->ShowCaption = false;
$txtMobileNumber->Length = 30;
$txtMobileNumber->Size = 15;
$txtMobileNumber->CssClass = "validate[required, custom[onlyNumber], minSize[9]]";
$txtMobileNumber->Text = $arrmemberinfo['MobileNumber'];
$fproc->AddControl($txtMobileNumber);

$txtAlternateMobileNumber = new TextBox("txtAlternateMobileNumber", "txtAlternateMobileNumber", "AlternateMobileNumber");
$txtAlternateMobileNumber->ShowCaption = false;
$txtAlternateMobileNumber->Length = 30;
$txtAlternateMobileNumber->Size = 15;
$txtAlternateMobileNumber->Text = $arrmemberinfo['AlternateMobileNumber'];
$txtAlternateMobileNumber->AutoComplete = false;
$txtAlternateMobileNumber->CssClass = "validate[custom[onlyNumber], minSize[9]]";
$fproc->AddControl($txtAlternateMobileNumber);

if (!isset($txtPassword))
{
    $txtPassword = new TextBox("txtPassword", "txtPassword", "Password");
}
$txtPassword->ShowCaption = false;
$txtPassword->Length = 40;
$txtPassword->Size = 15;
$txtPassword->Password = true;
$txtPassword->CssClass = "validate[custom[onlyLetterNumber], minSize[5]]";
$txtPassword->AutoComplete = false;
$fproc->AddControl($txtPassword);

$txtConfirmPassword = new TextBox("txtConfirmPassword", "txtConfirmPassword", "ConfirmPassword");
$txtConfirmPassword->ShowCaption = false;
$txtConfirmPassword->Length = 40;
$txtConfirmPassword->Size = 15;
$txtConfirmPassword->Password = true;
$txtConfirmPassword->CssClass = "validate[equals[txtPassword]]";
$fproc->AddControl($txtConfirmPassword);

$txtEmail = new TextBox("txtEmail", "txtEmail", "Email");
$txtEmail->ShowCaption = false;
$txtEmail->Length = 30;
$txtEmail->Size = 15;
$txtEmail->CssClass = "validate[required, custom[email]]";

if ($validate->validateEmail($arrmemberinfo['Email']))
    $txtEmail->ReadOnly = true;
else
    $txtEmail->ReadOnly = false;

$txtEmail->Text = $arrmemberinfo['Email'];
$fproc->AddControl($txtEmail);

$txtAlternateEmail = new TextBox("txtAlternateEmail", "txtAlternateEmail", "Username");
$txtAlternateEmail->ShowCaption = false;
$txtAlternateEmail->Length = 30;
$txtAlternateEmail->Size = 15;
$txtAlternateEmail->Text = $arrmemberinfo['AlternateEmail'];
$txtAlternateEmail->CssClass = "validate[custom[email]]";
$fproc->AddControl($txtAlternateEmail);

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "Birth Date: ");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
$dtBirthDate->SelectedDate = $arrmemberinfo['Birthdate'];
$dtBirthDate->ShowCaption = false;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->Size = 20;
$dtBirthDate->isRenderJQueryScript = true;
$fproc->AddControl($dtBirthDate);

$txtAddress1 = new TextBox("txtAddress1", "txtAddress1", "Address1");
$txtAddress1->ShowCaption = false;
$txtAddress1->Length = 30;
$txtAddress1->Size = 15;
$txtAddress1->Text = $arrmemberinfo['Address1'];
$fproc->AddControl($txtAddress1);

$txtAddress2 = new TextBox("txtAddress2", "txtAddress2", "Address2");
$txtAddress2->ShowCaption = false;
$txtAddress2->Length = 30;
$txtAddress2->Size = 15;
$txtAddress2->Text = $arrmemberinfo['Address2'];
$fproc->AddControl($txtAddress2);

$txtIDPresented = new TextBox("txtIDPresented", "txtIDPresented", "IDPresented");
$txtIDPresented->ShowCaption = false;
$txtIDPresented->Length = 30;
$txtIDPresented->Size = 15;
$txtIDPresented->CssClass = "validate[required, custom[onlyLetterNumber]]";
$txtIDPresented->Text = $arrmemberinfo['IdentificationNumber'];
$fproc->AddControl($txtIDPresented);

$_identifications = new Identifications();
$arrids = $_identifications->SelectAll();
$cboIDSelection = new ComboBox("cboIDSelection", "cboIDSelection", "cboIDSelection");
$cboIDSelection->ShowCaption = false;
$cboIDSelection->DataSource = $arrids;
$cboIDSelection->DataSourceText = "IdentificationName";
$cboIDSelection->DataSourceValue = "IdentificationID";
$cboIDSelection->DataBind();
$cboIDSelection->SetSelectedValue($arrmemberinfo['IdentificationID']);
$fproc->AddControl($cboIDSelection);

$txtAge = new TextBox("txtAge", "txtAge", "Age");
$txtAge->ShowCaption = false;
$txtAge->CssClass = "validate[required]";
$txtAge->ReadOnly = true;
$txtAge->Text = number_format((abs(strtotime($arrmemberinfo['Birthdate']) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365), 0);
$fproc->AddControl($txtAge);

$_nationality = new Nationality();
$arrnationality = $_nationality->SelectAll();
$cboNationality = new ComboBox("cboNationality", "cboNationality", "cboNationality");
$cboNationality->ShowCaption = false;
$cboNationality->DataSource = $arrnationality;
$cboNationality->DataSourceText = "Name";
$cboNationality->DataSourceValue = "NationalityID";
$cboNationality->DataBind();
$cboNationality->SetSelectedValue($arrmemberinfo['NationalityID']);
$fproc->AddControl($cboNationality);

$_Occupation = new Occupation();
$arrOccupation = $_Occupation->SelectAll();
$cboOccupation = new ComboBox("cboOccupation", "cboOccupation", "cboOccupation");
$cboOccupation->ShowCaption = false;
$cboOccupation->DataSource = $arrOccupation;
$cboOccupation->DataSourceText = "Name";
$cboOccupation->DataSourceValue = "OccupationID";
$cboOccupation->DataBind();
$cboOccupation->SetSelectedValue($arrmemberinfo['OccupationID']);
$fproc->AddControl($cboOccupation);

$rdoGroupGender = new RadioGroup("rdoGender", "rdoGender", "Gender");
$rdoGroupGender->AddRadio("1", "Male");
$rdoGroupGender->AddRadio("2", "Female");
$rdoGroupGender->ShowCaption = true;
$rdoGroupGender->Initialize();
$rdoGroupGender->SetSelectedValue($arrmemberinfo['Gender']);
$fproc->AddControl($rdoGroupGender);

$rdoGroupSmoker = new RadioGroup("rdoGroupSmoker", "rdoGroupSmoker", "rdoGroupSmoker");
$rdoGroupSmoker->AddRadio("1", "Smoker");
$rdoGroupSmoker->AddRadio("2", "Non-Smoker");
$rdoGroupSmoker->ShowCaption = true;
$rdoGroupSmoker->Initialize();
$rdoGroupSmoker->SetSelectedValue($arrmemberinfo['IsSmoker']);
$rdoGroupGender->Args = "onclick='\"window.close()\"'";
$fproc->AddControl($rdoGroupSmoker);

//Check if has scanned file

(!empty($arrmemberinfo['PhotoFileName'])) ? $memberfile = 'File: ' . $arrmemberinfo['PhotoFileName'] : $memberfile = "";

/*
 * End member profile
 */

$btnUpdate = new Button('btnUpdate', 'btnUpdate', 'Update Profile');
$btnUpdate->IsSubmit = false;
$btnUpdate->ShowCaption = true;
$btnUpdate->CssClass = "yellow-btn";
$fproc->AddControl($btnUpdate);

$btnClose = new Button('close', 'close', 'Close');
$btnClose->Args = "onclick='window.close()'";
$btnClose->ShowCaption = true;
$fproc->AddControl($btnClose);

$hdnUpdateProfile = new Hidden("hdnUpdateProfile", "hdnUpdateProfile", "hdnUpdateProfile");
$hdnUpdateProfile->Text = "";
$fproc->AddControl($hdnUpdateProfile);

$trans = $_CardTransactions->getLastTransaction($cardNumber);
if (count($trans) > 0)
{
    $site = $_Sites->getSite($trans[0]['SiteID']);
    $siteName = $site[0]['SiteName'];
    $transDate = date('M d, Y ', strtotime($trans[0]['TransactionDate']));

    $lastPlay = "<p>You last played in $siteName on $transDate.</p>";
}
else
{
    $lastPlay = "";
}

if (!$fproc->IsFormProcessed)
{
    $fproc->ProcessForms();
}
$dateupdated = "now_usec()";

echo App::GetErrorMessage();

if ($fproc->IsPostBack)
{
    //Check if password was changed.
    if ($hdnUpdateProfile->SubmittedValue == "update")
    {
        $hdnUpdateProfile->Text = "";
        (!empty($txtPassword->SubmittedValue)) ? $arrMembers["Password"] = md5($txtPassword->SubmittedValue) : "";
        $arrMembers["DateUpdated"] = $dateupdated;
        $arrMembers["MID"] = $MID;

        $arrMemberInfo["FirstName"] = $txtFirstName->SubmittedValue;
        $arrMemberInfo["MiddleName"] = $txtMiddleName->SubmittedValue;
        $arrMemberInfo['LastName'] = $txtLastName->SubmittedValue;
        $arrMemberInfo['NickName'] = $txtNickName->SubmittedValue;

        $arrMemberInfo['Address1'] = $txtAddress1->SubmittedValue;
        $arrMemberInfo['Address2'] = $txtAddress2->SubmittedValue;
        $arrMemberInfo['MobileNumber'] = $txtMobileNumber->SubmittedValue;
        $arrMemberInfo['AlternateMobileNumber'] = $txtAlternateMobileNumber->SubmittedValue;
        $arrMemberInfo['Email'] = $txtEmail->SubmittedValue;
        $arrMemberInfo['AlternateEmail'] = $txtAlternateEmail->SubmittedValue;
        $arrMemberInfo['Birthdate'] = $dtBirthDate->SubmittedValue;
        $arrMemberInfo['NationalityID'] = $cboNationality->SubmittedValue;
        $arrMemberInfo['OccupationID'] = $cboOccupation->SubmittedValue;
        $arrMemberInfo['IdentificationID'] = $cboIDSelection->SubmittedValue;
        $arrMemberInfo['IdentificationNumber'] = $txtIDPresented->SubmittedValue;

        $arrMemberInfo['Gender'] = $rdoGroupGender->SubmittedValue;
        $arrMemberInfo['IsSmoker'] = $rdoGroupSmoker->SubmittedValue;

        //Proceed with the update profile
        $_MemberInfo->updateProfile($arrMembers, $arrMemberInfo);

        if (!App::HasError())
        {
            $isSuccess = true;

            if (isset($_SESSION['MemberInfo']))
            {
                App::LoadModuleClass("Membership", "AuditTrail");
                App::LoadModuleClass("Membership", "AuditFunctions");

                $username = $_SESSION['MemberInfo']['UserName'];
                //$accounttypeid = $_SESSION['MemberInfo']['AccountTypeID'];
                $id = $_SESSION['MemberInfo']['MID'];
                $sessionid = $_SESSION['MemberInfo']['SessionID'];

                $_Log = new AuditTrail();
                $_Log->logEvent(AuditFunctions::UPDATE_PROFILE, $username, array('ID' => $id, 'SessionID' => $sessionid));
            }
        }
        else
        {
            $isSuccess = false;
        }

        /*
         * Load message dialog box
         */
        $isOpen = 'true';
    }
}
?>