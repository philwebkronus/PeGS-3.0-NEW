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

if(isset($_GET['email']) && isset($_GET['tempcode']))
{
    $email = $_GET['email'];
    $tempcode = $_GET['tempcode'];
    
    $result = $_TempMembers->verifyEmailAccount($email, $tempcode);
    
    if($result == 1)
        $isSuccess = true;
    else
        $isSuccess = false;
    
    //Load status dialog box
    $isOpen = true;
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
                    alert('Redirect to the member page');
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
        <div id="StatusDialog">There is a problem verifying your email account. Please contact customer service at (02) 338 3388. Thank you.</div>
    <?php
    }?>
<?php
}?>

<?php include('footer.php'); ?>