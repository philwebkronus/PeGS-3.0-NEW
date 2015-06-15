<?php
/**
* Description: Forgot Password Views
* @author: aqdepliyan
* DateCreated: 2013-06-25 09:16:14 AM
* @modified: mgesguerra 07-04-2013
* @modified: mgesguerra 07-19-2013 @purpose Notice IsPostBack; to allow enter key as shortcut for submit (in IE)
*/

require_once("init.inc.php");

$pagetitle = "Forgot Password";
$useCustomHeader = true;

//controls
App::LoadControl("TextBox");
App::LoadControl("Button");

//modules
App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass('Membership', 'Members');
App::LoadModuleClass('Membership', 'Helper');
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "Cards");

App::LoadCore('Validation.class.php');

$fproc = new FormsProcessor();
$evt = new EventListener($fproc);
$isOpen = 'false';
$hasEmail = true;

$Memberinfo = new MemberInfo();
$Membercards = new MemberCards();
$Members = new Members();
$_Helper = new Helper();
$Cards = new Cards();

$txtUserName = new TextBox("txtUserName", "txtUserName", "Username");
$txtUserName->ShowCaption = false;
$txtUserName->CssClass = "validate[required, custom[emailAlphanumeric]]";
$txtUserName->Length = 40;
$txtUserName->Text = "";
$fproc->AddControl($txtUserName);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
$btnSubmit->IsSubmit = true;
$btnSubmit->CssClass = "btnDefault roundedcorners yellow-btn";
$fproc->AddControl($btnSubmit);

$btnCancel = new Button("btnCancel","btnCancel","Cancel");
$btnCancel->IsSubmit = false;
$btnCancel->CssClass = "btnDefault roundedcorners yellow-btn";
$btnCancel->Style = "margin-left: 36px;";
$fproc->AddControl($btnCancel);

$fproc->ProcessForms();

if ($fproc->IsPostBack || $btnSubmit->SubmittedValue == "Submit" && $txtUserName->SubmittedValue != "")
{
    $validate = new Validation();
    $tobevalidated = $txtUserName->SubmittedValue;
    if($validate->validateEmail($tobevalidated))
    {
        $Email = $tobevalidated;
        $data = $Memberinfo->getMIDFNameUsingEmail($Email);
        if (count($data) > 0)
        {
            $MID = $data[0]['MID'];
            $FullName = $data[0]['FirstName'].' '.$data[0]['LastName'];
            $UBCard = $Membercards->getCardNumberUsingMID($MID);
            $HashedUBCard = base64_encode($UBCard);
            $Members->StartTransaction();
            $Members->updateForChangePasswordUsingMID($MID, 1);
            $txtUserName->Text = "";
            $isOpen = 'true';
            if(!App::HasError()){
                $Members->CommitTransaction();
                $_Helper->sendEmailForgotPassword($Email, $FullName, $HashedUBCard);
                $isSuccess = true;
            } else {
                $Members->RollBackTransaction();
                $isSuccess = false;
            }
        }
        else
        {
             App::SetErrorMessage("E-mail Address is not registered");
        }
    } 
    else if($validate->validateAlphaNumeric($tobevalidated))
    {
        $UBCard = $tobevalidated;
        $isCardExist = $Cards->isExist($UBCard);
        $res = $Membercards->getMIDByCard($UBCard);
        if (count($res) > 0 && $isCardExist)
        {
            if (($res[0]['Status'] == 1) || ($res[0]['Status'] == 5))
            {
                $MID = $res[0]['MID'];
                $data = $Memberinfo->getEmailFNameUsingMID($MID);
                if(isset($data[0]['Email']) && $data[0]['Email'] != '')
                {
                    $FullName = $data[0]['FirstName'].' '.$data[0]['LastName'];
                    $Email = $data[0]['Email'];
                    $HashedUBCard = base64_encode($UBCard);

                    $Members->StartTransaction();
                    $Members->updateForChangePasswordUsingMID($MID, 1);

                    $txtUserName->Text = "";
                    $isOpen = 'true';
                    if(!App::HasError()){
                        $Members->CommitTransaction();
                        $_Helper->sendEmailForgotPassword($Email, $FullName, $HashedUBCard);
                        $isSuccess = true;
                    } else {
                        $Members->RollBackTransaction();
                        $isSuccess = false;
                    }
                 } 
                 else 
                 {
                     $isOpen = 'true';
                     $isSuccess = false;
                     $hasEmail = false;
                 }
              }
             else 
             {
                 Helper::SetErrorMsgForCardStatus($res[0]['Status']);
             }
        }
        else
        {
            if ($isCardExist == false)
                App::SetErrorMessage("Card is Invalid");
            else
                App::SetErrorMessage("Card is Inactive");
//                      echo "asfasfsa";exit;

        }

    } 
    else 
    {
        $txtUserName->Text = "";
        App::SetErrorMessage("Invalid Input");
    }
}

?>
<?php include 'header.php'; ?>
<script language="javascript" type='text/javascript'>
    $(document).ready(function(){
        $("#btnCancel").click(function(){
           window.location = "./"; 
        });
        $("#forgotpassword").validationEngine();
        
        $("#MessageDialog").dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title: 'Forgot Password',
            closeOnEscape: true,
            buttons: {
                "Ok": function(){
                    $(this).dialog("close");
                    window.location = "index.php";
                }
            }
        });
        
    });
</script>
<form name="forgotpassword" id="forgotpassword" method="POST">
    <center><div id="home-login-box" style="background-color: #7c0d08; height: 200px;"> 
        <br>
        <span class="pagesubtitle">Forgot Password</span>
        <div style="margin-top: 20px;">
            <label >Enter your Email Address/Card No.</label><?php echo $txtUserName; ?>
            <div style="float: right; padding-top: 10px;margin-right: 25px;"><?php echo $btnSubmit; echo $btnCancel; ?></div>
        </div>
    </div></center>
</form>
<div id="MessageDialog" name="MessageDialog">
    <?php if ($isOpen == 'true')
    { ?>
        <?php if ($isSuccess)
        { ?>
            <p style="text-align: justify; text-justify: inter-word; color: green;">
                Request for change password is successfully processed. 
                Please verify the link sent to your email to reset your password.
            </p>
        <?php }
        else
        { 
            if(!$hasEmail){
                $errormessage = "No Email Address found for this user. Please contact Philweb Customer Service Hotline 338-3388.";
            } else {
                $errormessage = "Request for change password has failed. Please try again.";
            }
        ?>
            <p style="text-align: justify; text-justify: inter-word; color: red;">
                <?php echo $errormessage; ?>
            </p>
        <?php }
    }
    ?>
</div>

<?php include 'footer.php'; ?>