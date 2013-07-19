<?php

/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-26
 * ***************** */

/* * ***************** 
 * Author: aqdepliyan
 * Date Updated: 2013-07-08 1:13PM
 * ***************** */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("init.inc.php");

$useCustomHeader = true;

$pagetitle = "Membership";

App::LoadCore("URL.class.php");
App::LoadCore("Hashing.class.php");
App::LoadCore("Validation.class.php");
App::LoadCore("File.class.php");
App::LoadCore("PHPMailer.class.php");

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberSessions");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "Cards");

App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("Hidden");

$_Log = new AuditTrail();

$fproc = new FormsProcessor();
$txtUsername = new TextBox("txtUsername", "txtUsername", "Username:");
$txtUsername->Length = 30;
$txtUsername->Size = 30;
$txtUsername->ShowCaption = false;
$txtUsername->CssClass = "validate[required] login-field";
$fproc->AddControl($txtUsername);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password:");
$txtPassword->Length = 40;
$txtPassword->Size = 40;
$txtPassword->Password = true;
$txtPassword->ShowCaption = false;
$txtPassword->CssClass = "validate[required] login-field";
$fproc->AddControl($txtPassword);

$btnLogin = new Button("btnSubmit", "btnSubmit", "Login");
$btnLogin->IsSubmit = true;
$btnLogin->CssClass = "yellow-btn";
$fproc->AddControl($btnLogin);

$fproc->ProcessForms();

if ($fproc->IsPostBack && $btnLogin->SubmittedValue == "Login")
{
    if (!$txtUsername->Text == "")
    {
        $username = $txtUsername->SubmittedValue;
    }
    
    if (!$txtPassword->Text == "")
    {
        $password = $txtPassword->SubmittedValue;
    }
    
    if (isset($username) && isset($password))
    {
        $_Members = new Members();
        $members = $_Members->Authenticate($username, $password, Hashing::MD5);

        if ($members)
        {
            $_MemberSessions = new MemberSessions();

            $datenow = "now_usec()";
            $remoteip = $_SERVER['REMOTE_ADDR'];

            $arrMemberSessions["MID"] = $members["MID"];
            $arrMemberSessions["SessionID"] = session_id();
            $arrMemberSessions["RemoteIP"] = $remoteip;
            $arrMemberSessions["DateStarted"] = $datenow;
            $arrMemberSessions["TransactionDate"] = $datenow;

            $activesession = $_MemberSessions->checkSession($members["MID"]);
            foreach ($activesession as $value) {
                foreach ($value as $value2) {
                    $activesession = $value2['Count'];
                }
            }
            
            if($activesession > 0)
            {
                $_MemberSessions->updateSession($arrMemberSessions["SessionID"], $members["MID"],$arrMemberSessions["RemoteIP"]);
            }
            else{
                $_MemberSessions->Insert($arrMemberSessions);
            }
            $_SESSION['sessionID'] = $arrMemberSessions["SessionID"];
            $_SESSION['MID'] = $members["MID"];

            $msresult = $_MemberSessions->getMemberSessions($members["MID"]);
            $membersessions = $msresult[0];
            $sessionid = $membersessions["SessionID"];
            $enddate = $membersessions["DateEnded"];

            $_MemberCards = new MemberCards();
            $mcresults = $_MemberCards->getActiveMemberCardInfo($members["MID"]);
            $membercards = $mcresults[0];

            $cardnumber = $membercards["CardNumber"];

            $_Cards = new Cards();
            $cresult = $_Cards->getCardInfo($cardnumber);
            $cards = $cresult[0];
            $cardtypeid = $cards["CardTypeID"];

            $_SESSION["MemberInfo"]["MID"] = $members["MID"];
            $_SESSION["MemberInfo"]["UserName"] = $username;
            $_SESSION["MemberInfo"]["Member"] = $members;
            $_SESSION["MemberInfo"]["SessionID"] = $sessionid;
            $_SESSION["MemberInfo"]["CardTypeID"] = $cardtypeid;
            $_SESSION["MemberInfo"]["DateEnded"] = $enddate;
            
            //Log to audittrail
            $_Log->logEvent(AuditFunctions::LOGIN, $username, array('ID' => $members["MID"], 'SessionID' => $sessionid));
            header("location:profile.php");
        }
    }
}
?>
