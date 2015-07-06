<?php

/*
 * @author : owliber
 * @date : 2013-04-19
 */

require_once("init.inc.php");

/**
 * Load Module Class
 */

App::LoadModuleClass("Membership", "TempMembers");

$_TempMembers = new TempMembers();
$isOpen = false; //Prevent dialog to open
$useCustomHeader = false;

if(isset($_GET['email']) && isset($_GET['tempcode']))
{
    $email = $_GET['email'];
    $tempcode = $_GET['tempcode'];

    //check if email address already verified
    $isVerified = $_TempMembers->chkTmpVerifiedEmailAddress($email);
    $verifiedCode = $isVerified[0]['TemporaryAccountCode'];
    if($isVerified[0]['ctrtemp'] == 0){
        $result = $_TempMembers->verifyEmailAccount($email, $tempcode);

        if($result == 1)
        {
            $isSuccess = true;
            $result = "Success";
        }
        else
        {
            $isSuccess = false;
            $result = "Failed";
        }

        App::LoadModuleClass("Membership", "AuditTrail");
        App::LoadModuleClass("Membership", "AuditFunctions");

        $_Log = new AuditTrail();
        $_Log->logAPI(AuditFunctions::VERIFY_EMAIL, $result, $email);

        //Load status dialog box
        $isOpen = true;
    }
    else
    {
        //Load status dialog box
        $isOpen = true;
        $isSuccess = false;
    }
}
else
{
    //Load status dialog box
    $isOpen = true;
    $isSuccess = false;
}
?>
<?php include('header.php'); ?>
<script>
    $(document).ready(function(){
        $('#StatusDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Email Verification',
            closeOnEscape: true,
            buttons: {
                "Close": function() {
                    $(this).dialog("close");
                    window.location="index.php";
                }
            }
        });
    });
</script>

<?php if($isOpen)
{?>
    <?php if($isSuccess)
    {?>
        <div id="StatusDialog">You have successfully verified your email. Please wait for 24 hours in order for your account to be activated.</div>
    <?php }else{ ?>
        <div id="StatusDialog">This email <?php echo $email; ?> has already been verified using this tempcode: <?php echo $verifiedCode; ?>.</div>
    <?php
    }?>
<?php
}?>

<?php include('nofooter.php'); ?>