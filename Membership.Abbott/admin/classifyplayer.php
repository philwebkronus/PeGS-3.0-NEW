<?php
/**
 * @author Noel Antonio
 * @dateCreated November 21, 2013
 * @Updated by Joene Floresca
 * @dateUpdated September 01, 2014
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$currentpage = "Administration";
$pagetitle = "Player Classification Assignment";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberServices");
App::LoadModuleClass("Membership", "RefVip");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("CasinoProvider", "CasinoProviders");
App::LoadModuleClass("CasinoProvider", "CasinoAPI");
App::LoadModuleClass("CasinoProvider", "RealtimeGamingCashierAPI2");
App::LoadModuleClass("Kronus", "TerminalSessions");
App::LoadModuleClass("Kronus", "EgmSessions");

App::LoadControl("Button");
App::LoadControl("TextBox");
App::LoadControl("DatePicker");
App::LoadControl("Hidden");
App::LoadControl("Radio");
App::LoadControl("ComboBox");

$loyaltyMemberCard = new MemberCards();
$members = new Members();
$memberServices = new MemberServices();
$auditTrail = new AuditTrail();
$casinoAPI = new CasinoAPI();
$vip_levels = new RefVip();
$terminal_sessions = new TerminalSessions();
$egm_sessions = new EgmSessions();

$fproc = new FormsProcessor();

$classification_type = new ComboBox("classification_type","classification_type","Classification Type: ");
$classification_type->ShowCaption = true;
$classification_types = $vip_levels->getVipName();
$alftnlist = new ArrayList();
$alftnlist->AddArray($classification_types);
$classification_type->ClearItems();
$litem = null;
$litem[] = new ListItem("Select One", "", true);
$classification_type->Items = $litem;
$classification_type->DataSource = $alftnlist;
$classification_type->DataSourceText = "Name";
$classification_type->DataSourceValue = "VIPLevelID";
$classification_type->DataBind();


$txtSearch = new TextBox("txtSearch", "txtSearch", "Membership Card Number");
$txtSearch->ShowCaption = false;
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->AutoComplete = false;
$txtSearch->CssClass = "validate[required,maxSize[15]]";
$txtSearch->Args = 'maxlength = 15; placeholder="Enter Card Number" onkeypress="javascript: return AlphaNumericOnly(event)"';

$btnSearch = new Button('btnSearch', 'btnSearch', 'SEARCH');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;

$btnClear = new Button('btnClear', 'btnClear', 'CLEAR');
$btnClear->ShowCaption = true;
$btnClear->IsSubmit = true;

$btnSubmit = new Button('btnSubmit', 'btnSubmit', 'SUBMIT');
$btnSubmit->ShowCaption = true;
$btnSubmit->IsSubmit = true;
$btnSubmit->Style = 'height: 35px; width: 80px;';

$hiddenMid = new Hidden("hiddenMid", "hiddenMid");
$hiddenCard = new Hidden("hiddenCard", "hiddenCard");

$fproc->AddControl($txtSearch);
$fproc->AddControl($btnSearch);
$fproc->AddControl($btnClear);
$fproc->AddControl($btnSubmit);
$fproc->AddControl($hiddenMid);
$fproc->AddControl($hiddenCard);
$fproc->AddControl($classification_type);
$fproc->ProcessForms();

//$isVip = '';
$success = '';
$isVIPLevel = '';
$cardNumber = trim($txtSearch->SubmittedValue);
if ($fproc->IsPostBack)
{
    
    $mid = '';
    if ($btnSearch->SubmittedValue == "SEARCH")
    { 
        $arrCards = $loyaltyMemberCard->getMIDByCard($cardNumber);
        if(isset($arrCards[0]['MID']))
        { 
            $mid = $arrCards[0]['MID'];
        }
        $hiddenMid->Text = $mid;
        $hiddenCard->Text = $cardNumber;
        
        //$arrStatus = $members->getVIP($mid);
        //$isVip = $arrStatus[0]['isVIP'];
        $arrVIPLevel = $memberServices->getVIPLevel($mid);
        if(isset($arrVIPLevel[0]['VIPLevel'])){
        $isVIPLevel = $arrVIPLevel[0]['VIPLevel'];
        }
        if(!empty($arrCards)){
            
            if($isVIPLevel == 0){
                $classification_type->SetSelectedValue('0');
            } 
            elseif ($isVIPLevel == 1) {
                $classification_type->SetSelectedValue('1');
            }
            elseif ($isVIPLevel == 2) {
                $classification_type->SetSelectedValue('2');
            }
        }
        else{
            $showdialog = true;
            $msg = "Invalid Card Number";
            $title = "Player Classification Assignment";
        }
        
    }
    
    if ($btnClear->SubmittedValue == "CLEAR")
    {
        unset($mid);
        $txtSearch->Text = "";
        $hiddenMid->Text = "";
    }
    else
    {
        
    }
    
    if (isset($_POST['classification_type']))
    {
        $card = $_POST['hiddenCard'];
        $MID = $_POST['hiddenMid'];
        //Check if Card Number has an Active Termninal Session
        $check_session = $terminal_sessions->isSessionExistsCard($card);

        //Check if Card Number has an active EGM Session
        $check_egm = $egm_sessions->checkEgmSession($MID);
        
        if(count($check_session) > 0 && count($check_egm) > 0)
        {
            $success = 'Player has existing EGM and Terminal Session.';
        }
        else if(count($check_session) > 0)
        {
            $success = 'Player has an existing Terminal Session.';
        }
        else if(count($check_egm) > 0)
        {
            $success = 'Player has existing EGM  Session.';
        }
        
        else
        {
            $type = $_POST['classification_type'];
            $get_type = $_POST['classification_type'];

            if($type == 0)
            {
               $status = "Regular"; 
               $classid = 0;
               $type = 0;
            }
            else if($type == 1)
            {
                $status = "VIP";
                $classid = 1;
                $type = 1;
            }
            else if($type == 2)
            {
                $status = "Classic";
                $classid = 2;
                $type = 0;
            }

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

                    $changeplayerclassresult = $casinoAPI->ChangePlayerClassification('RTG2', $pid, $classid, $userID, $serverID);
                    header("Content-Type:text/html");

                }

                if($changeplayerclassresult['IsSucceed'] == true){

                    $members->StartTransaction();
                    $members->changeIsVipByMid($type, $mid);
                    if (!App::HasError())
                    {
                        $CommonPDOConnection = $members->getPDOConnection();
                        $memberServices->setPDOConnection($CommonPDOConnection);            
                        $memberServices->changeIsVipByMid($type, $get_type, $mid);

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
                                $success = 'Failed to log event.';
                                App::ClearStatus();
                            }
                        }
                        else
                        {
                            $members->RollBackTransaction();
                            $success = '1 - Failed to update player classification.';
                        }
                    }
                    else
                    {
                        $members->RollBackTransaction();
                        $success =  '2 - Failed to update player classification.';
                    }
                }
                else{
                    $success =  '2 - Failed to update player classification.';
                }
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
    $('#c').hide();
    $('#s').hide();
    $('#prompt').hide();
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#prompt").dialog({
                    modal : true,
                    autoOpen : false,
                    title : 'Player Classification Assignment',
                    resizable : false,
                    draggable :false,
                    buttons : {
                        'YES' : function(){

                            $(this).dialog('close');
                        },
                    }
           });
           
        $('#btnSubmit').live('click', function(e){
        e.preventDefault();
        
            
        if($("#classification_type").val() == "")
        {
          $("#errormsg").text("Classification Type cannot be empty.");
          $('#prompt').dialog( "open" );
          return false;
        }
        else
        {
//            if(ifSessionActive == 0 && ifEgmActive == 0)
//            {
                $('#c').show();
                $("#confirmMsg").dialog({
                    modal : true,
                    autoOpen : true,
                    title : 'Player Classification Assignment',
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
//            }
        } 
        });
        
        <?php if ($success != ''): ?>
        $('#s').show();
        $("#successMsg").dialog({
                modal : true,
                autoOpen : true,
                title : 'Player Classification Assignment',
                resizable : false,
                draggable :false,
                buttons : {
                    'OK' : function(){
                        $('#div1').hide();
                        $(this).dialog('close');
                        
                    }
                }
            });
        <?php endif; ?>
    });
</script>
<script type="text/javascript">
    <?php if (isset($showdialog)): ?>
        $(document).ready(function(){
            $("#dialogmsg").dialog({
                modal : true,
                autoOpen : <?php echo $showdialog; ?>,
                title : '<?php echo $title?>',
                resizable : false,
                draggable :false,
                buttons : {
                    'OK' : function(){
                        $('#div1').hide();
                        $(this).dialog('close');
                        
                    }
                }
            });
        });
    <?php endif; ?>
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
                
                <?php if (!empty($mid)): ?>
                <form name="frmChange" id="frmChange" method="POST">
                    <div id="div1" style="margin-left: 20px;">
                        <h2><u>Classification Type:</u></h2><br/>
                        
                        <div style="margin-left: 40px">
                            <?php echo $classification_type; ?>
                            <?php echo $btnSubmit; echo $hiddenMid; echo $hiddenCard; ?>
                        </div>
                        
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
</div>
<div id="confirmMsg" style="display : none">
    <p id="c">
        Proceed with the changes in player classification?
    </p>
</div>
<div id="prompt" style="">
    <p id="promptpr">
        <label id="errormsg"></label>
    </p>
</div>
<div id="successMsg">
    <p id="s">
        <?php echo $success; ?>
    </p>
</div>
<!--error dialog-->
<div id="dialogmsg">
    <p id="msg">
        <?php 
            if (isset($msg))
            echo $msg; 
        ?>
    </p>
</div> 
<?php include("footer.php"); ?>
