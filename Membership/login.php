<?php
/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-26
 * ***************** */

$fproc = new FormsProcessor();

$txtUsername = new TextBox("txtUsername", "txtUsername", "Username:");
$txtUsername->Length = 30;
$txtUsername->Size = 30;
$txtUsername->ShowCaption = false;
$txtUsername->CssClass = "validate[required]";
$fproc->AddControl($txtUsername);

$txtPassword = new TextBox("txtPassword", "txtPassword", "Password:");
$txtPassword->Length = 30;
$txtPassword->Size = 30;
$txtPassword->Password = true;
$txtPassword->ShowCaption = false;
$txtPassword->CssClass = "validate[required]";
$fproc->AddControl($txtPassword);

$btnLogin = new Button("btnSubmit", "btnSubmit", "Login");
$btnLogin->IsSubmit = true;
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
        
        if($members == false)
        {
            echo App::GetErrorMessage();
        }
        else
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
            //$cardid = $membercards["CardID"];
            $cardnumber = $membercards["CardNumber"];
            
            $_Cards = new Cards();
            $cresult = $_Cards->getCardInfo($cardnumber);
            $cards = $cresult[0];
            $cardtypeid = $cards["CardTypeID"];            
            
            $_SESSION["MemberInfo"]["UserName"] = $username;
            $_SESSION["MemberInfo"]["Member"] = $members;
            $_SESSION["MemberInfo"]["SessionID"] = $sessionid;
            $_SESSION["MemberInfo"]["CardTypeID"] = $cardtypeid;
            $_SESSION["MemberInfo"]["DateEnded"] = $enddate;
              
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
<?php echo $txtUsername; ?><br/>
<?php echo $txtPassword; ?><br/>
<?php echo $btnLogin; ?>
</form>

<div>Not yet a member? Register <a id="register" href="#" onclick="window.showModalDialog('registration.php', '', 'dialogWidth=800, dialogHeight=300, center=0, status=0, edge=sunken')">here</a>.</div>
