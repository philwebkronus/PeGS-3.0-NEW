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
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass('Membership', 'MembershipSmsAPI');
App::LoadModuleClass('Membership', 'BlackLists');

App::LoadModuleClass("Rewards", "SMSRequestLogs");

// Load Controls
App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("DataGrid");
App::LoadControl("ComboBox");
App::LoadControl("Button");
App::LoadControl("RadioGroup");
App::LoadControl("Radio");
App::LoadControl("CheckBox");

//Load Core
App::LoadCore('ErrorLogger.php');

$fproc = new FormsProcessor();
$evt = new EventListener($fproc);
$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();
$autocomplete = false;
$isOpen = 'false';
$useCustomHeader = true;

$_TempMembers = new TempMembers();
$_Members = new Members();
$_AccountTypes = new AccountTypes();
$_Log = new AuditTrail();
$_SMSRequestLogs = new SMSRequestLogs();
$_BlackLists = new BlackLists();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

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

//Added by: MKGE 08-22-13
$txtReferrerCode = new TextBox("txtReferrerCode","txtReferrerCode","Referrer Code");
$txtReferrerCode->ShowCaption = false;
$txtReferrerCode->Length = 20;
$txtReferrerCode->Size = 15;
$txtReferrerCode->Args = "onkeypress = 'javascript: return AlphaNumericOnly(event)'";
$fproc->AddControl($txtReferrerCode);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Register");
$btnSubmit->IsSubmit = true;
$btnSubmit->CssClass = "btnDefault roundedcorners yellow-btn";
$fproc->AddControl($btnSubmit);

$btnCancel = new Button("btnCancel", "btnCancel", "Cancel");
$btnCancel->IsSubmit = false;
$btnCancel->CssClass = "btnDefault roundedcorners yellow-btn";
$fproc->AddControl($btnCancel);

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
$chkConfirmAge->Caption = "I hereby confirm that I am at least 21 years old and have read and accepted the <a href=".$_CONFIG['terms-conditions']." target='_blank'>Terms and Conditions</a>.";
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

$datecreated = "NOW(6)";
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
        $arrMemberInfo['ReferrerCode'] = $txtReferrerCode->SubmittedValue;
        $arrMemberInfo['Gender'] = $rdoGroupGender->SubmittedValue;
        $arrMemberInfo['IsSmoker'] = $rdoGroupSmoker->SubmittedValue;

        $arrMemberInfo['DateCreated'] = "NOW(6)";

        $chkEmailNotification->SubmittedValue == 1 ? $arrMemberInfo['EmailSubscription'] = 1 : $arrMemberInfo['EmailSubscription'] = 0;
        $chkSMSNotification->SubmittedValue == 1 ? $arrMemberInfo['SMSSubscription'] = 1 : $arrMemberInfo['SMSSubscription'] = 0;
        //Check if listed in blacklists
        $blacklistresult = $_BlackLists->checkIfExist(formatName($arrMemberInfo['LastName']), formatName($arrMemberInfo['FirstName']), formatName($arrMemberInfo['Birthdate']), 3);
        //check if email is active and existing in live membership db
        $activeEmail = $_Members->chkActiveVerifiedEmailAddress(trim($arrMemberInfo['Email']));
        if($activeEmail > 0){
            App::SetErrorMessage("Email already exists. Please choose a different email address.");
            $_Log->logAPI(AuditFunctions::PLAYER_EMAIL_VERIFICATION, $activeEmail.':'.$arrMemberInfo['Email'].':Failed', 0, 0);
            $isSuccess = false;
            $error = "Email already exists. Please choose a different email address.";
            $logger->logger($logdate, $logtype, $error);
        }
        else if ($blacklistresult[0]['Count'] > 0)
        {
            $message = "Registration cannot proceed. Please contact Customer Service";
            App::SetErrorMessage($message);
        }
        else{
            //check if email is already verified in temp table
            $tempEmail = $_TempMembers->chkTmpVerifiedEmailAddress(trim($arrMemberInfo['Email']));
            if($tempEmail > 0){
                App::SetErrorMessage("Email already verified. Please choose a different email address.");
                $_Log->logAPI(AuditFunctions::PLAYER_EMAIL_VERIFICATION, $tempEmail.':'.$arrMemberInfo['Email'].':Failed', 0, 0);
                $isSuccess = false;
                $error = "Email already exists. Please choose a different email address.";
                $logger->logger($logdate, $logtype, $error);
            }
            else{
                
                $lastinsertMID = $_TempMembers->Register($arrMembers, $arrMemberInfo);
                $isOpen = 'true';
                if (!App::HasError())
                {
                    $isSuccess = true;
                    if(isset($_SESSION['MemberInfo']['MID'])){
                        $id = $_SESSION['MemberInfo']['MID'];
                        $sessionid = $_SESSION['MemberInfo']['SessionID'];
                    }
                    else{
                        $id = 0;  
                        $sessionid = '';
                        $arrMemberInfo['UserName'] = 'guest';
                    }  

                    $memberInfos = $_TempMembers->getTempMemberInfoForSMS($lastinsertMID);

                    //match to 09 or 639 in mobile number
                    $match = substr($memberInfos["MobileNumber"], 0, 3);
                    if($match == "639"){
                        $mncount = count($memberInfos["MobileNumber"]);
                        if(!$mncount == 12){
                            $message = "Failed to send SMS: Invalid Mobile Number [MID: $lastinsertMID].";
                            $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                        } else {
                            $templateid = $_SMSRequestLogs->getSMSMethodTemplateID(SMSRequestLogs::PLAYER_REGISTRATION);
                            $methodid = SMSRequestLogs::PLAYER_REGISTRATION;
                            $mobileno = $memberInfos["MobileNumber"];
                            $smslastinsertedid = $_SMSRequestLogs->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                            if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                $trackingid = "SMSR".$smslastinsertedid;
                                $apiURL = App::getParam("SMSURI");    
                                $app_id = App::getParam("app_id");    
                                $_MembershipSmsAPI = new MembershipSmsAPI($apiURL, $app_id);
                                $smsresult = $_MembershipSmsAPI->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                if($smsresult['status'] != 1){
                                    $message = "Failed to send SMS [MID: $lastinsertMID].";
                                    $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                }
                            } else {
                                $message = "Failed to send SMS: Error on logging event in database [MID: $lastinsertMID].";
                                $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                            }
                        }
                    } else {
                        $match = substr($memberInfos["MobileNumber"], 0, 2);
                        if($match == "09"){
                            $mncount = count($memberInfos["MobileNumber"]);
                            if(!$mncount == 11){
                                 $message = "Failed to send SMS: Invalid Mobile Number [MID: $lastinsertMID].";
                                 $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                             } else {
                                $mobileno = str_replace("09", "639", $memberInfos["MobileNumber"]);
                                $templateid = $_SMSRequestLogs->getSMSMethodTemplateID(SMSRequestLogs::PLAYER_REGISTRATION);
                                $methodid = SMSRequestLogs::PLAYER_REGISTRATION;
                                $smslastinsertedid = $_SMSRequestLogs->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                    $trackingid = "SMSR".$smslastinsertedid;
                                    $apiURL = App::getParam("SMSURI");    
                                    $app_id = App::getParam("app_id");    
                                    $_MembershipSmsAPI = new MembershipSmsAPI($apiURL, $app_id);
                                    $smsresult = $_MembershipSmsAPI->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                    if($smsresult['status'] != 1){
                                        $message = "Failed to send SMS [MID: $lastinsertMID].";
                                        $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                    }
                                } else {
                                    $message = "Failed to send SMS: Error on logging event in database [MID: $lastinsertMID].";
                                    $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                }
                             }
                        } else {
                            $message = "Failed to send SMS: Invalid Mobile Number [MID: $lastinsertMID].";
                            $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                        }
                    }

                    $_Log->logEvent(AuditFunctions::PLAYER_REGISTRATION, $arrMemberInfo['UserName'], array('ID' => $id, 'SessionID' => $sessionid));
                }
                else
                {
                    //check if email is already verified in temp table
                    $tempEmail = $_TempMembers->chkTmpVerifiedEmailAddress(trim($arrMemberInfo['Email']));
                    if($tempEmail > 0){
                        App::SetErrorMessage("Email already verified. Please choose a different email address.");
                        $_Log->logAPI(AuditFunctions::PLAYER_EMAIL_VERIFICATION, $tempEmail.':'.$arrMemberInfo['Email'].':Failed', 0, 0);
                        $isSuccess = false;
                        $error = "Email already exists. Please choose a different email address.";
                        $logger->logger($logdate, $logtype, $error);
                    }
                    else
                    {
                        $_TempMembers->Register($arrMembers, $arrMemberInfo);
                        $isOpen = 'true';
                        if (!App::HasError())
                        {
                            $isSuccess = true;
                            $id = $_SESSION['MemberInfo']['MID'];
                            $sessionid = $_SESSION['MemberInfo']['SessionID'];

                            $memberInfos = $_TempMembers->getTempMemberInfoForSMS($lastinsertMID);

                            //match to 09 or 639 in mobile number
                            $match = substr($memberInfos["MobileNumber"], 0, 3);
                            if($match == "639"){
                                $mncount = count($memberInfos["MobileNumber"]);
                                if(!$mncount == 12){
                                    $message = "Failed to send SMS: Invalid Mobile Number [MID: $lastinsertMID].";
                                    $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                } else {
                                    $templateid = $_SMSRequestLogs->getSMSMethodTemplateID(SMSRequestLogs::PLAYER_REGISTRATION);
                                    $methodid = SMSRequestLogs::PLAYER_REGISTRATION;
                                    $mobileno = $memberInfos["MobileNumber"];
                                    $smslastinsertedid = $_SMSRequestLogs->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                    if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                        $trackingid = "SMSR".$smslastinsertedid;
                                        $apiURL = App::getParam("SMSURI");    
                                        $app_id = App::getParam("app_id");    
                                        $_MembershipSmsAPI = new MembershipSmsAPI($apiURL, $app_id);
                                        $smsresult = $_MembershipSmsAPI->sendRegistration($mobileno, $templateid, $datecreated, $memberInfos["TemporaryAccountCode"], $trackingid);
                                        if($smsresult['status'] != 1){
                                            $message = "Failed to send SMS [MID: $lastinsertMID].";
                                            $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                        }
                                    } else {
                                        $message = "Failed to send SMS: Error on logging event in database.";
                                        echo "<script type='text/javascript'>alert(".$message.");</script>";
                                        App::SetErrorMessage($message);
                                    }
                                }
                            } else {
                                $match = substr($memberInfos["MobileNumber"], 0, 2);
                                if($match == "09"){
                                    $mncount = count($memberInfos["MobileNumber"]);
                                    if(!$mncount == 11){
                                         $message = "Failed to send SMS: Invalid Mobile Number [MID: $lastinsertMID].";
                                         $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                     } else {
                                        $mobileno = str_replace("09", "639", $memberInfos["MobileNumber"]);
                                        $templateid = $_SMSRequestLogs->getSMSMethodTemplateID(SMSRequestLogs::PLAYER_REGISTRATION);
                                        $methodid = SMSRequestLogs::PLAYER_REGISTRATION;
                                        $smslastinsertedid = $_SMSRequestLogs->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                        if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                            $trackingid = "SMSR".$smslastinsertedid;
                                            $apiURL = App::getParam("SMSURI");    
                                            $app_id = App::getParam("app_id");    
                                            $_MembershipSmsAPI = new MembershipSmsAPI($apiURL, $app_id);
                                            $smsresult = $_MembershipSmsAPI->sendRegistration($mobileno, $templateid, $datecreated, $memberInfos["TemporaryAccountCode"], $trackingid);

                                            if($smsresult['status'] != 1){
                                                $message = "Failed to send SMS [MID: $lastinsertMID].";
                                                $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                            }
                                        } else {
                                            $message = "Failed to send SMS: Error on logging event in database [MID: $lastinsertMID].";
                                            $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                        }
                                     }
                                } else {
                                    $message = "Failed to send SMS: Invalid Mobile Number [MID: $lastinsertMID].";
                                    $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                }
                            }

                            $_Log->logEvent(AuditFunctions::PLAYER_REGISTRATION, $arrMemberInfo['UserName'], array('ID' => $id, 'SessionID' => $sessionid));
                        }
                        else
                        {
                            $isSuccess = false;
                            if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0)
                            {
                                $isEmailUnique = false;
                                App::SetErrorMessage("Email already exists. Please choose a different email address.");
                                $_Log->logAPI(AuditFunctions::PLAYER_EMAIL_VERIFICATION, $tempEmail.':'.$arrMemberInfo['Email'].':Failed', 0, 0);
                                $isOpen = false;
                                $error = "Email already exists. Please choose a different email address.";
                                $logger->logger($logdate, $logtype, $error);
                            }
                            else{
                                $isOpen = false;
                                App::SetErrorMessage("Registration Failed, Please try again.");
                                $error = "Failed to register new player.";
                                $logger->logger($logdate, $logtype, $error);
                            }
                        }
                    }
                }

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
        $("#btnCancel").click(function(){
           window.location = "index.php";
        });
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
        <td>Email Address*<span style="font-size: 10px; float: left;">(This will serve as your Username.)</span></td>
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
        <td>Referral Code<br/>
        <td><?php echo $txtReferrerCode; ?></td>
        <td>Nationality</td>
        <td><?php echo $cboNationality; ?></td>
    </tr>
    <tr>
        <td colspan="2">How did you hear about e-Games?<br/>
            <?php echo $cboHowDidYouHear; ?></td>
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
        <td colspan="4"><?php echo $btnCancel; ?></td>
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
<?php
/**
 * Format names
 * @example Inputted lastname is 'dEla crUz' the ouput would be Dela Cruz
 * @param type $str Name
 * @return string reformatted name
 * @author Mark Kenneth Esguerra
 * @date November 12, 2013
 */
function formatName($str)
{
    $arrNames = explode(" ", $str);
    if (count($arrNames) > 1)
    {
        $name = "";
        foreach($arrNames as $names)
        {
           $n = trim(ucfirst(strtolower($names)));
           $name .= $n." ";
        }
        return trim($name);
    }
    else
    {
        $name = trim(ucfirst(strtolower($str)));
        return trim($name);
    }
}
