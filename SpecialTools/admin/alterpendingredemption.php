<?php
/**
 * Alter Pending Redemption
 * @author Mark Kenneth Esguerra
 * @date October 9, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
require_once("../init.inc.php");

$pagetitle = "Alter Pending Redemption";
$currentpage = "Administration";
//Check if session MID is set
if (isset($_SESSION['UBCard']))
{
    $ubcard = $_SESSION['UBCard'];
}
else
{
    URL::Redirect('pendingredemption.php');
}
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "CouponRedemptionLogs");
App::LoadModuleClass("Loyalty", "RaffleCoupons");
App::LoadModuleClass("Loyalty", "PendingRedemption");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");

App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("RadioGroup");

$MemberCard = new MemberCards();
$CouponRedemptionLogs = new CouponRedemptionLogs();
$RaffleCoupons = new RaffleCoupons();
$PendingRedemption = new PendingRedemption();
$MemberInfo = new MemberInfo();
$AuditTrail = new AuditTrail();

$fproc = new FormsProcessor();

$rdoGroupValidity = new RadioGroup("rdoGroupValidity", "rdoGroupValidity","");
$rdoGroupValidity->AddRadio("A", "Void Raffle Coupons");
$rdoGroupValidity->AddRadio("B", "Valid Raffle Coupons");
$rdoGroupValidity->ShowCaption = true;
$rdoGroupValidity->Style = "margin-left: 0px;";
$rdoGroupValidity->Initialize();

$rdoGroupPoints = new RadioGroup("rdoGroupPoints", "rdoGroupPoints","");
$rdoGroupPoints->AddRadio("C", "Add Points");
$rdoGroupPoints->AddRadio("D", "Deduct Points");
$rdoGroupPoints->ShowCaption = true;
$rdoGroupPoints->Style = "margin-left: 0px;";
$rdoGroupPoints->Initialize();

$rdoGroupCRL = new RadioGroup("rdoGroupCRL", "rdoGroupCRL","");
$rdoGroupCRL->AddRadio("E", "Void Coupon Redemption Logs");
$rdoGroupCRL->AddRadio("F", "Valid Coupon Redemption Logs");
$rdoGroupCRL->ShowCaption = true;
$rdoGroupCRL->Style = "margin-left: 0px;";
$rdoGroupCRL->Initialize();


$btnSubmit = new Button('btnSubmit', 'btnSubmit', 'Submit');
$btnSubmit->IsSubmit = true;
$btnSubmit->ShowCaption = true;
$btnSubmit->Enabled = true;

$fproc->AddControl($rdoGroupValidity);
$fproc->AddControl($rdoGroupPoints);
$fproc->AddControl($rdoGroupCRL);
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

//GET MID
$arrmid = $MemberCard->getMID($ubcard);
$mid = $arrmid[0]['MID'];

if ($fproc->IsPostBack)
{
    $validity   = $rdoGroupValidity->SubmittedValue;
    $points     = $rdoGroupPoints->SubmittedValue;
    $crl        = $rdoGroupCRL->SubmittedValue;
    //Check if the Required Field has input
    if ($validity == NULL && $points == NULL && $crl == NULL)
    {
        $openErrorDialog = true;
        $errormsg = "Please choose an Option";
    }
    else
    {
        //Only RF is set
        if (isset($validity) && !isset($points) && !isset($crl))
        {
            if ($validity == "A") //Void
            {
                $status_raffle = 2;
            }
            else if ($validity == "B") //Valid
            {
                $status_raffle = 1;
            }
            if (isset($status_raffle))
            {
                $raffleResult = $PendingRedemption->updateRaffleStat($mid, $status_raffle);
                if($raffleResult){
                     $openSuccessDialog = true;
                     $errormsg = "Redemption Fulfilled";
                } else {
                     $openErrorDialog = true;
                     $errormsg = $raffleResult;
                }
            }
            else
            {
                $openErrorDialog = true;
                $errormsg = "Please choose whether to Void or Validate Raffle Coupons";
            }
        }
        else if (!isset($validity) && isset($points) && !isset($crl))
        {
            if ($points == "C") //ADD
            {
                $actpoints = 0;
            }
            else if ($points == "D") //LESS
            {
                $actpoints = 1;
            }
            
            $pointsResult = false;
            if (isset($actpoints))
            {
                $pointsResult = $PendingRedemption->updatePoints($mid, $actpoints);
                if($pointsResult){
                     $openSuccessDialog = true;
                     $errormsg = "Redemption Fulfilled";
                } else {
                     $openErrorDialog = true;
                     $errormsg = $pointsResult;
                }
            }
            else
            {
                $openErrorDialog = true;
                $errormsg = "Please choose whether to Add or Less Points";
            }
        }
        else if (!isset($validity) && !isset($points) && isset($crl))
        {
            if ($crl == "E")//Void
            {
                $status_crl = 2;
            }
            else if ($crl == "F") //Valid
            {
                $status_crl = 1;
            }
            if (isset($status_crl))
            {
                $crlResult = $PendingRedemption->updateCRL($mid, $status_crl);
                if($crlResult){
                     $openSuccessDialog = true;
                     $errormsg = "Redemption Fulfilled";
                } else {
                     $openErrorDialog = true;
                     $errormsg = $crlResult;
                }
            }
            else
            {
                $openErrorDialog = true;
                $errormsg = "Please choose whether to Void or Validate Coupon Redemption Logs";
            }
        }
        //Check if the selected radio button in each radio groups are the valid combination  
        //Void Transaction
        else if ($validity == "A" && $crl == "E" && $points == "") //Void RF and Void CRL
        {
            $transVoidResult = $PendingRedemption->manualPendingTrans($mid, 2, 2);
            if($transVoidResult){
                 $openSuccessDialog = true;
                 $errormsg = "Redemption Fulfilled";
            } else {
                 $openErrorDialog = true;
                 $errormsg = $transVoidResult;
            }
        }
        //Valid Transaction
        else if ($validity == "B" && $crl == "F" && $points == "")
        {
            $transValidResult = $PendingRedemption->manualPendingTrans($mid, 1, 1);
            if($transValidResult){
                 $openSuccessDialog = true;
                 $errormsg = "Redemption Fulfilled";
            } else {
                 $openErrorDialog = true;
                 $errormsg = $transVoidResult;
            }
        }
        //Void and Add Points
        else if ($validity == "A" && $points == "C" && $crl == "E")
        {
            $transVoidResult = $PendingRedemption->manualPendingTrans($mid, 2, 2, $actpoints = 0);
            if($transVoidResult){
                 $openSuccessDialog = true;
                 $errormsg = "Redemption Fulfilled";
            } else {
                 $openErrorDialog = true;
                 $errormsg = $transVoidResult;
            }
        }
        //Valid Transaction and Less Points
        else if ($validity == "B" && $points == "D" && $crl == "F")
        {
            $transValidResult = $PendingRedemption->manualPendingTrans($mid, 1, 1, $actpoints = 1);
            if($transValidResult){
                 $openSuccessDialog = true;
                 $errormsg = "Redemption Fulfilled";
            } else {
                 $openErrorDialog = true;
                 $errormsg = $transVoidResult;
            }
        }
        else
        {
            $openErrorDialog = true;
            $errormsg = "Invalid Combination of Options";
        }
        $AID = $_SESSION['userinfo']['AID'];
        $sessionID = $_SESSION['sessionID'];
        $AuditTrail->logEvent(AuditFunctions::MANUAL_REDEMPTION_FULFILLMENT,"MID ".$mid.":".$errormsg, array('ID'=>$AID, 'SessionID'=>$sessionID));
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
        <?php if ($openSuccessDialog): ?>
        $("#successDialog").dialog({
          autoOpen: <?php echo $openSuccessDialog; ?>,
          modal: true,
          resizable: false,
          buttons: {
                "OK": function(){
                    window.location.href = 'pendingredemption.php';
                }
            }
        });
        <?php endif; ?>
    });
</script>
<div align="center">
    <form name="rewardoffersmgt" id="rewardoffersmgt" method="POST">
        <div class="maincontainer">
            <br />
            <div style="float: left;" class="title">&nbsp;&nbsp;&nbsp;&nbsp;Check Pending Redemption:</div>
            <div class="pad5" align="right"> </div>
            <br/><br/>
            <hr color="black">
            <br>
            <div class="content">
                <div id="results">
                    <?php 
                        $memberinfo = $MemberCard->getMemberInfoByCard($ubcard);
                        $firstname  = $memberinfo[0]['FirstName'];
                        $lastname   = $memberinfo[0]['LastName'];
                        $email      = $memberinfo[0]['Email'];
                    ?>
                    <b>Player's Name:</b> <?php echo $firstname." ".$lastname; ?><br />
                    <b>Email:</b> <?php echo $email; ?><br />
                    <br />
                    <br />
                    <?php 
                    echo $rdoGroupValidity->Radios[0]."<br />";
                    echo $rdoGroupValidity->Radios[1]."<br /><br />";
                    echo $rdoGroupPoints->Radios[0]."<br />";
                    echo $rdoGroupPoints->Radios[1]."<br /><br />";
                    echo $rdoGroupCRL->Radios[0]."<br />";
                    echo $rdoGroupCRL->Radios[1]."<br /><br />";
                    echo $btnSubmit;
                    ?>
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
        <!---------error dialog box-------->
        <div id="successDialog" title="Message">
            <p id="msgerror">
                <?php
                    if (isset($errormsg))
                        echo $errormsg; 
                ?>
            </p>
        </div>    
        <!--------------------------------->
    </form>
</div>
<?php include("footer.php"); ?>
