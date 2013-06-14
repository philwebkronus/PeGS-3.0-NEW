<?php
/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-26
 * ***************** */

$_Log = new AuditTrail();

$fproc = new FormsProcessor();

$txtUsername = new TextBox("txtUsername", "txtUsername", "Username:");
$txtUsername->Length = 30;
$txtUsername->Size = 30;
$txtUsername->ShowCaption = false;
$txtUsername->CssClass = "validate[required] login-field";
$fproc->AddControl($txtUsername);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password:");
$txtPassword->Length = 30;
$txtPassword->Size = 30;
$txtPassword->Password = true;
$txtPassword->ShowCaption = false;
$txtPassword->CssClass = "validate[required] login-field";
$fproc->AddControl($txtPassword);

$btnLogin = new Button("btnSubmit", "btnSubmit", "Login");
$btnLogin->IsSubmit = true;
$btnLogin->CssClass = "yellow-btn";
$fproc->AddControl($btnLogin);

$fproc->ProcessForms();

/*
 * Reload parent (index) page upon successful login
 */

function reloadParent()
{
    echo "<script>parent.window.location.href='index.php';</script>";
}

if($fproc->IsPostBack)
{
    if(!$txtUsername->Text == "")
    {
        $username = $txtUsername->SubmittedValue;
    }
    if(!$txtPassword->Text == "")
    {
        $password = $txtPassword->SubmittedValue;
    }
    if(isset($username) && isset($password))
    {
        $_Members = new Members();
        $members = $_Members->Authenticate($username, $password, Hashing::MD5);
        
        if($members)
        {
            $_MemberSessions = new MemberSessions();
            
            $datenow = "now_usec()";
            $remoteip = $_SERVER['REMOTE_ADDR'];
            
            $arrMemberSessions["MID"] = $members["MID"];
            $arrMemberSessions["SessionID"] = "UUID()";
            $arrMemberSessions["RemoteIP"] = $remoteip;
            $arrMemberSessions["DateStarted"] = $datenow;
            $arrMemberSessions["TransactionDate"] = $datenow;
            
            $_MemberSessions->Insert($arrMemberSessions);
            
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
            $_Log->logEvent(AuditFunctions::LOGIN, $username, array('ID'=>$members["MID"], 'SessionID'=>$sessionid));
            
            reloadParent();
        }
    }
}
    
?>  
<script language="javascript" type="text/javascript">
    $(document).ready(function() 
    {
        $("#loginForm").validationEngine();
    });        
</script>
<form name="loginForm" method="post" action="" id="loginForm" />
<div class="login-error"><?php echo App::GetErrorMessage(); ?></div>
<div id="home-login-box">    
    <div id="home-login-wrapper">
        <div id="home-page-login-form">
            <div class="home-login-form-wrapper">
                <div class="home-login-form-label">Username</div>
                <div class="home-login-form-input"><?php echo $txtUsername; ?></div>
                <div class="clearfix"></div>
            </div>
            
            <div class="home-login-form-wrapper">
                <div class="home-login-form-label">Password:</div>
                <div class="home-login-form-input"><?php echo $txtPassword; ?></div>
                <div class="clearfix"></div>
            </div>
            
            <div class="home-login-form-wrapper">
                <div class="home-login-form-label">&nbsp;</div>
                <div class="home-login-form-input"><?php echo $btnLogin; ?></div>
                <div class="clearfix"></div>
            </div>
            
            <div id="home-login-form-link">Not yet a member? Sign up <a href="registration.php">here</a></div>
            
        </div>
    </div>
<!--</div>-->
</form>

