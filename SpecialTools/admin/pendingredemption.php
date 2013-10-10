<?php


/**
 * Check Pending Redemption
 * @author Mark Kenneth Esguerra
 * @date October 9, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
require_once("../init.inc.php");
include 'sessionmanagetool.php';

$pagetitle = "Check Pending Redemption";
$currentpage = "Administration";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "CouponRedemptionLogs");
App::LoadModuleClass("Loyalty", "RaffleCoupons");
App::LoadModuleClass("Loyalty", "PendingRedemption");

App::LoadControl("TextBox");
App::LoadControl("Button");

$MemberCard = new MemberCards();
$CouponRedemptionLogs = new CouponRedemptionLogs();
$RaffleCoupons = new RaffleCoupons();
$PendingRedemption = new PendingRedemption();

$fproc = new FormsProcessor();

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search');

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->IsSubmit = true;
$btnSearch->ShowCaption = true;
$btnSearch->Enabled = true;

$fproc->AddControl($txtSearch);
$fproc->AddControl($btnSearch);

$fproc->ProcessForms();

unset($_SESSION['UBCard']);
if ($fproc->IsPostBack)
{
     //Get UB Card
     $UBcard = $txtSearch->SubmittedValue;
     //Check if UB Card is blank or NULL
     if ($UBcard == "" || is_null($UBcard))
     {
         $openErrorDialog = true;
         $errormsg = "Please enter the UB Card";
     }
     else
     {
         $memberinfo = $MemberCard->getMemberInfoByCard($UBcard);
         if (count($memberinfo) > 0)
         {
            $mid = $memberinfo[0]['MID'];
            $ubcard = $memberinfo[0]['CardNumber'];
            $_SESSION['UBCard'] = $ubcard;
            if ($mid != NULL)
            {    
               $check = $PendingRedemption->checkPendingRedemption($mid);
               if ($check)
               {
                   $openPendingDialog = true;
                   $msg = "There was a pending transaction in Coupon Redemption";
               }
               else
               {
                   $openErrorDialog = true;
                   $errormsg = "There was no pending transaction in Coupon Redemption";
               }
            }
            else
            {
               $openErrorDialog = true;
               $errormsg = "Please provide MID";
            }
         }
         else
         {
             $ctrtemp = $MemberCard->counterTempAccount($UBcard);
             if ($ctrtemp[0]['ctrtemp'] > 0)
             {
                $openErrorDialog = true;
                $errormsg = "Temporary account is not allowed";
             }
             else
             {
                $openErrorDialog = true;
                $errormsg = "Invalid Card Number"; 
             }
         }
     }
}
?>
<?php include("header.php"); ?>
</form>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>
    $(document).ready(function(){
        <?php if ($openErrorDialog): ?>
        $("#errorDialog").dialog({
          autoOpen: <?php echo $openErrorDialog; ?>,
          modal: true,
          resizable: false,
          buttons: {
                "OK": function(){
                    $(this).dialog("close");
                }
            }
        });
        <?php endif; ?>
    });
</script>
<script type='text/javascript'>
    $(document).ready(function(){
        <?php if ($openPendingDialog): ?>
        $("#pendingDialog").dialog({
          autoOpen: <?php echo $openPendingDialog; ?>,
          modal: true,
          resizable: false,
          buttons: {
                "OK": function(){
                    window.location.href = "alterpendingredemption.php";
                }
            }
        });
        <?php endif; ?>
    });
</script>
<div align="center">
    <form name="rewardoffersmgt" id="rewardoffersmgt" method="POST">
        <div class="maincontainer">
            <div style="float: right; padding-right: 10px;">
                <a href="logout.php">Logout</a>
            </div>
            <br />
            <div style="float: left;" class="title">&nbsp;&nbsp;&nbsp;&nbsp;Check Pending Redemption:</div>
            <div class="pad5" align="right"> </div>
            <br/><br/>
            <hr color="black">
            <br>
            &nbsp;&nbsp;UB Card:<?php echo $txtSearch; ?>&nbsp;&nbsp; <?php echo $btnSearch; ?>
            <div class="content">
                <div id="results">
                </div>
            </div>
        </div>
        <!---------error dialog box-------->
        <div id="errorDialog" title="Message">
            <p id="msgerror">
                <?php
                    if (isset($errormsg))
                        echo $errormsg; 
                ?>
            </p>
        </div>    
        <!--------------------------------->
        <!---------pending dialog box-------->
        <div id="pendingDialog" title="Message">
            <p id="msg">
                <?php
                    if (isset($msg))
                        echo $msg; 
                ?>
            </p>
        </div>    
        <!--------------------------------->
    </form>
</div>
<?php include("footer.php"); ?>
