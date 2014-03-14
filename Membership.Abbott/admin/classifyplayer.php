<?php
/**
 * @author Noel Antonio
 * @dateCreated November 21, 2013
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$currentpage = "Administration";
$pagetitle = "Player Classification Assignment";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberServices");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("CasinoProvider", "CasinoProviders");
App::LoadModuleClass("CasinoProvider", "CasinoAPI");
App::LoadModuleClass("CasinoProvider", "RealtimeGamingCashierAPI2");

App::LoadControl("Button");
App::LoadControl("TextBox");
App::LoadControl("DatePicker");
App::LoadControl("Hidden");
App::LoadControl("Radio");

$loyaltyMemberCard = new MemberCards();
$members = new Members();
$memberServices = new MemberServices();
$auditTrail = new AuditTrail();
$casinoAPI = new CasinoAPI();

$fproc = new FormsProcessor();

$txtSearch = new TextBox("txtSearch", "txtSearch", "Membership Card Number");
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required, custom[emailAlphanumeric]]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->AutoComplete = false;
$txtSearch->Args = 'placeholder="Enter Membership Card Number"';

$btnSearch = new Button('btnSearch', 'btnSearch', 'SEARCH');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;

$btnClear = new Button('btnClear', 'btnClear', 'CLEAR');
$btnClear->ShowCaption = true;
$btnClear->IsSubmit = true;

$btnSubmit = new Button('btnSubmit', 'btnSubmit', 'SUBMIT');
$btnSubmit->ShowCaption = true;
//$btnSubmit->IsSubmit = true;
$btnSubmit->Style = 'height: 35px; width: 80px;';

$hiddenMid = new Hidden("hiddenMid", "hiddenMid");

$fproc->AddControl($txtSearch);
$fproc->AddControl($btnSearch);
$fproc->AddControl($btnClear);
$fproc->AddControl($btnSubmit);
$fproc->AddControl($hiddenMid);
$fproc->ProcessForms();

$isVip = '';
$success = '';

if ($fproc->IsPostBack)
{
    if ($btnSearch->SubmittedValue == "SEARCH")
    {
        $cardNumber = trim($txtSearch->SubmittedValue);
        $arrCards = $loyaltyMemberCard->getMIDByCard($cardNumber);
        $mid = $arrCards[0]['MID'];
        $hiddenMid->Text = $mid;
        $arrStatus = $members->getVIP($mid);
        $isVip = $arrStatus[0]['isVIP'];
    }
    
    if ($btnClear->SubmittedValue == "CLEAR")
    {
        unset($mid);
        $txtSearch->Text = "";
        $hiddenMid->Text = "";
    }
    
    if (isset($_POST['type']))
    {
        $type = $_POST['type'][0];
        $type == 0 ? $status = "Regular" : $status = "VIP";        
        $mid = $hiddenMid->SubmittedValue;    
        
        $abbottmemservice = $memberServices->CheckMemberService($mid, 19);
        
        if(!empty($abbottmemservice)){
            
            $serviceusername = $abbottmemservice[0]['ServiceUsername'];
            $serverID = $abbottmemservice[0]['ServiceID'];
            $serviceapi = App::getParam('service_api');
            
            $url = $serviceapi[$serverID - 1];
            $certFilePath = App::getParam('rtg_cert_dir').$serverID.'/cert.pem';
            $keyFilePath = App::getParam('rtg_cert_dir').$serverID.'/key.pem';
            
            $_RTGCashierAPI = new RealtimeGamingCashierAPI2($url, $certFilePath, $keyFilePath, '');

            $apiResult = $_RTGCashierAPI->GetPIDFromLogin($serviceusername);
       
            $pid = $apiResult['GetPIDFromLoginResult'];
            
            if(!empty($pid)){
                
                $userID = 0;
                
                $changeplayerclassresult = $casinoAPI->ChangePlayerClassification('RTG2', $pid, $type, $userID, $serverID);
                header("Content-Type:text/html");
                
            }
            
            if($changeplayerclassresult['IsSucceed'] == true){
                
                $members->StartTransaction();
                $members->changeIsVipByMid($type, $mid);
                if (!App::HasError())
                {
                    $CommonPDOConnection = $members->getPDOConnection();

                    $memberServices->setPDOConnection($CommonPDOConnection);            
                    $memberServices->changeIsVipByMid($type, $mid);
                    if (!App::HasError())
                    {
                        $auditTrail->setPDOConnection($CommonPDOConnection);
                        $auditTrail->logEvent(AuditFunctions::PLAYER_CLASSIFICATION_ASSIGNMENT, "Change Player Status to " . $status, array('ID' => $mid, 'SessionID' => $_SESSION["sessionID"]));
                        if (!App::HasError())
                        {
                            $members->CommitTransaction();
                            $success = "Player classification successfully updated";
                            $hiddenMid->Text = "";
                            unset($mid);
                        }
                        else
                        {
                            $members->RollBackTransaction();
                            App::SetErrorMessage('Failed to log event.');
                            App::ClearStatus();
                        }
                    }
                    else
                    {
                        $members->RollBackTransaction();
                        App::SetErrorMessage('1 - Failed to update player classification.');
                    }
                }
                else
                {
                    $members->RollBackTransaction();
                    App::SetErrorMessage('2 - Failed to update player classification.');
                }
            }
            else{
                App::SetErrorMessage('2 - Failed to update player classification.');
            }
        } 
        
            
    }
}
?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>   
<script type="text/javascript">
    $(document).ready(function(){
        $('#c').hide();
        $('#s').hide();
        
        $('#btnSubmit').live('click', function(){
            $('#c').show();
            $("#confirmMsg").dialog({
                modal : true,
                autoOpen : true,
                title : 'CONFIRMATION',
                resizable : false,
                draggable :false,
                buttons : {
                    'YES' : function(){
                        
                        $('#frmChange').submit();
                    },
                    'NO' : function(){
                        $(this).dialog('close');
                    }
                }
            });
        });
        
        <?php if ($success != ''): ?>
        $('#s').show();
        $("#successMsg").dialog({
                modal : true,
                autoOpen : true,
                title : 'SUCCESSFUL',
                resizable : false,
                draggable :false,
                buttons : {
                    'OK' : function(){
                        $(this).dialog('close');
                    }
                }
            });
        <?php endif; ?>
    });
</script>
<div align="center">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                <h2>Player Classification Assignment</h2><br/>   

                <div class="searchbar formstyle">
                    <form name="frmSearch" id="frmSearch" method="POST">
                        <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
                    </form>
                </div>
                
                <br/><br/>
                
                <?php if (isset($mid)): ?>
                <form name="frmChange" id="frmChange" method="POST">
                    <div style="margin-left: 20px;">
                        <h2><u>Classification Type:</u></h2><br/>
                        <div style="margin-left: 40px">
                            <input type="radio" id="reg" name="type[]" value="0" <?php echo ($isVip == 0) ? 'checked' : ''; ?> /> Regular &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" id="vip" name="type[]" value="1" <?php echo ($isVip == 1) ? 'checked' : ''; ?> /> VIP
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?php echo $btnSubmit; echo $hiddenMid; ?>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
</div>
<div id="confirmMsg">
    <p id="c">
        Proceed with the changes in player classification?
    </p>
</div>
<div id="successMsg">
    <p id="s">
        <?php echo $success; ?>
    </p>
</div>
<?php include("footer.php"); ?>
