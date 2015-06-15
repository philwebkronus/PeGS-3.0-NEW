<?php
/*
* Description: Change Password Views
* @author: aqdepliyan
* DateCreated: 2013-06-25 06:44:24 PM
*/

if(isset($_GET['CardNumber']) && $_GET['CardNumber'] != ''){
    require_once("init.inc.php");
    $pagetitle = "Change Password";
    $useCustomHeader = true;
    $HashedCardNumber = $_GET['CardNumber'];
    $CardNumber = base64_decode($HashedCardNumber);

    //controls
    App::LoadControl("TextBox");
    App::LoadControl("Button");
    
    //module
    App::LoadModuleClass("Membership", "Members");
    App::LoadModuleClass("Loyalty", "MemberCards");

    App::LoadCore('Validation.class.php');

    $fproc = new FormsProcessor();
    
    $Members = new Members();
    $Membercards = new MemberCards();
    $isOpen = 'false';
    $isVerified = false;
    $isAllowed = $Members->getForChangePasswordUsingCardNumber($CardNumber);
    
    $txtUserName = new TextBox("txtUserName", "txtUserName", "Username");
    $txtUserName->ShowCaption = false;
    $txtUserName->Text = $CardNumber;
    $txtUserName->ReadOnly = true;
    $txtUserName->CssClass = "validate[required]";
    $fproc->AddControl($txtUserName);

    $txtNewPassword = new TextBox("txtNewPassword", "txtNewPassword", "New Password");
    $txtNewPassword->ShowCaption = false;
    $txtNewPassword->Length = 40;
    $txtNewPassword->Size = 15;
    $txtNewPassword->Password = true;
    $txtNewPassword->CssClass = "validate[required, custom[onlyLetterNumber], minSize[5]]";
    $fproc->AddControl($txtNewPassword);

    $txtConfirmPassword = new TextBox("txtConfirmPassword", "txtConfirmPassword", "Confirm Password");
    $txtConfirmPassword->ShowCaption = false;
    $txtConfirmPassword->Length = 40;
    $txtConfirmPassword->Size = 15;
    $txtConfirmPassword->Password = true;
    $txtConfirmPassword->CssClass = "validate[required, custom[onlyLetterNumber], equals[txtNewPassword]]";
    $fproc->AddControl($txtConfirmPassword);

    $btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
    $btnSubmit->IsSubmit = true;
    $btnSubmit->CssClass = "btnDefault roundedcorners yellow-btn";
    $fproc->AddControl($btnSubmit);
            
    if($isAllowed == 1)
    {
            $fproc->ProcessForms();

            if ($fproc->IsPostBack)
            {
                if ($txtUserName->SubmittedValue != "" && $txtNewPassword->SubmittedValue != "" && $txtConfirmPassword->SubmittedValue != ""
                        && $btnSubmit->SubmittedValue == "Submit")
                {
                    $res = $Membercards->getMIDByCard($txtUserName->SubmittedValue);
                    $MID = $res[0]['MID'];
                    $Members->StartTransaction();
                    $Members->updatePasswordUsingMID($MID, $txtConfirmPassword->SubmittedValue);
                    $Members->updateForChangePasswordUsingMID($MID, 0);
                    $isOpen = 'true';
                    if(App::HasError()){
                        $Members->RollBackTransaction();
                        $isSuccess = false;
                    } else {
                        $Members->CommitTransaction();
                        $isSuccess = true;
                    }
                }
            }
    } else {
        $isOpen = 'true';
        $isSuccess = false;
        $isVerified = true;
    }
    ?>
    <?php include 'header.php'; ?>
    <script language="javascript" type='text/javascript'>
        $(document).ready(function(){
            $("#changepassword").validationEngine();

            $("#MessageDialog").dialog({
                autoOpen: <?php echo $isOpen; ?>,
                modal: true,
                width: '400',
                title: 'Change Password',
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
    <form name="changepassword" id="changepassword" method="POST">
        <center><div id="home-login-box" style="width: 370px; background-color: #7c0d08; height: 220px;"> 
            <span class="pagesubtitle">Change Password</span>
            <div style="margin-top: 20px;">
                <table class="tablename">
                    <tr>
                        <td class="labelname">Card Number&nbsp;&nbsp;</td>
                        <td><?php echo $txtUserName; ?></td>
                    </tr>
                    <tr>
                        <td class="labelname">New Password&nbsp;&nbsp;</td>
                        <td><?php echo $txtNewPassword; ?></td>
                    </tr>
                    <tr>
                        <td class="labelname">Confirm Password&nbsp;&nbsp;</td>
                        <td><?php echo $txtConfirmPassword; ?></td>
                    </tr>
                    <tr>
                        <td class="labelname"></td>
                        <td style="text-align: right;"><?php echo $btnSubmit; ?></td>
                    </tr>
                </table>
            </div>
        </div></center>
    </form>
    <div id="MessageDialog" name="MessageDialog">
        <?php if ($isOpen == 'true')
        { ?>
            <?php if ($isSuccess)
            { ?>
                <p style="text-align: justify; text-justify: inter-word; color: green;">
                    Your password has been successfully updated.<br />
                    Please log-in to verify your new password.
                </p>
            <?php }
            else
            { 
                if(!$isVerified) { ?>
                    <p style="text-align: justify; text-justify: inter-word; color: red;">
                        Reset password has failed.<br />
                        Please try again.
                    </p>
            <?php } else { ?>
                    <p style="text-align: justify; text-justify: inter-word; color: red;">
                        Your password has already been updated.
                    </p>
            <?php }
            }
        }
        ?>
    </div>
    
    <?php include 'footer.php'; ?>
<?php } else { ?>
  <HTML>
<head>
<meta HTTP-EQUIV="Refresh" CONTENT="0.1;URL=not404.htm">
</head>
</HTML>
<?php } ?>

