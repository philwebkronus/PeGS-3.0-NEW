<?php
/**
 * VIP Level Management
 * @author Joene Floresca
 * @date September 01, 2014
 * @copyright (c) 2014, Philweb Corporation
 */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "VIP Level Management";
$currentpage = "Administration";

App::LoadModuleClass("Membership", "MemberServices");
App::LoadModuleClass("Membership", "RefVip");
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("CasinoProvider", "CasinoProviders");
App::LoadModuleClass("CasinoProvider", "CasinoAPI");
App::LoadModuleClass("CasinoProvider", "RealtimeGamingCashierAPI2");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");

App::LoadControl("Button");
App::LoadControl("TextBox");
App::LoadControl("ComboBox");
App::LoadControl("Hidden");

$vip_levels = new RefVip();
$loyaltyMemberCard = new MemberCards();
$casinoAPI = new CasinoAPI();
$memberServices = new MemberServices();
$members = new Members();
$auditTrail = new AuditTrail();

$fproc = new FormsProcessor();

$btnAdd = new Button("btnAdd", "btnAdd", "Add VIP Level");
$btnAdd->ShowCaption = true;
$btnAdd->ID = "btnAdd";
$btnAdd->Style = "cursor:pointer";
$btnAdd->Args = "onclick = 'javascript: $('#addviplevel').dialog('open'); '";

$txtVipLevelID = new TextBox("txtVipLevelID", "txtVipLevelID", "VIP Level ID: ");
$txtVipLevelID->ShowCaption = false;
$txtVipLevelID->CssClass = "validate[required, custom[trailingSpaces], custom[onlyNumberSp], minSize[1]]";
$txtVipLevelID->Size = 15;
$txtVipLevelID->CssClass = "validate[required,maxSize[3]]";
$txtVipLevelID->Args = 'maxlength = 3; onkeypress="javascript: return numberonly(event)"';
$txtVipLevelID->Style = "height:20px;";

$classification_type = new TextBox("classification_type", "classification_type", "Classification Type: ");
$classification_type->ShowCaption = false;
$classification_type->CssClass = "validate[required, custom[trailingSpaces], custom[onlyLetterSp], minSize[2]]";
$classification_type->Size = 15;
$classification_type->CssClass = "validate[required,maxSize[30]]";
$classification_type->Args = 'maxlength = 30; onkeypress="javascript: return AlphaNumericOnlyWithSpace(event)"';
$classification_type->Style = "height:20px;";

$hdnProcess = new Hidden("hdnprocess", "hdnprocess");
$hdnProcess->Args = "value='1'";

$hdnVipLevel = new Hidden("hdnVipLevel", "hdnVipLevel");
$hdnName = new Hidden("hdnName", "hdnName");



$fproc->AddControl($txtVipLevelID);
$fproc->AddControl($btnAdd);
$fproc->AddControl($classification_type);
$fproc->AddControl($hdnProcess);
$fproc->ProcessForms();

if ($fproc->IsPostBack)
{
    $CommonPDOConnection = $members->getPDOConnection();
    $process = $hdnProcess->SubmittedValue;
    //Add VIP Level
    if($process == 1)
    {
        $vipLevelID   = $txtVipLevelID->SubmittedValue;
        $description   = formatName(mysql_escape_string($classification_type->SubmittedValue));
                    
                    // If success add to VIP Level
                        //Validate input if empty
                        if (($vipLevelID && $description) != "")
                        {
                            //Check if record exist
                            $isExisting = $vip_levels->checkIfExist($vipLevelID, $description);
                            if(count($isExisting) == 0)
                            {
                                $result = $vip_levels->addVipLevel($vipLevelID, $description);
                            }
                            else
                            {
                                $showdialog = true;
                                $msg = "Record already exist.";
                                $title = "ERROR MESSAGE";
                            }
                            //Check and show result
                            if (isset($result))
                            {
                                if ($result['TransCode'] == 1)
                                {
                                    $auditTrail->setPDOConnection($CommonPDOConnection);
                                    $auditTrail->logEvent(AuditFunctions::VIP_LEVEL_MANAGEMENT, "Add VIP Level " . $vipLevelID, array('ID' => '', 'SessionID' => $_SESSION["sessionID"]));
                                    $showdialog = true;
                                    $msg = $result['TransMsg'];
                                    $title = "MESSAGE";
                                }
                                else if ($result['TransCode'] == 0)
                                {
                                    $showdialog = true;
                                    $msg = $result['TransMsg'];
                                    $title = "ERROR MESSAGE";
                                }
                            }
                        }
                        else
                        {
                            $showdialog = true;
                            $msg = "Fill up all fields.";
                            $title = "ERROR MESSAGE";
                        }
    }
    //Update VIP Level
    elseif($process == 2)
    {
        $vipLevelID   = $_POST['hdnVipLevel'];//$txtVipLevelID->SubmittedValue;
        $description   = formatName(mysql_escape_string($_POST['hdnName']));//formatName(mysql_escape_string($classification_type->SubmittedValue));
        $status = $_POST['status'];
        
        //Check if VIP Level exist in MemberServices
        $isVipLevelExist = $memberServices->checkVIPLevel($vipLevelID);
        
        if(count($isVipLevelExist) == 0)
        {
            $result = $vip_levels->updateVipStatus($vipLevelID, $description, $status);
            
            if($result == 1)
            {
                $auditTrail->setPDOConnection($CommonPDOConnection);
                $auditTrail->logEvent(AuditFunctions::VIP_LEVEL_MANAGEMENT, "Change VIP Level Status to" . $status, array('ID' => null, 'SessionID' => $_SESSION["sessionID"]));
                $showdialog = true;
                $msg = "Successfully updated VIP Level Status.";
                $title = "MESSAGE";
            }
            else
            {
                $auditTrail->setPDOConnection($CommonPDOConnection);
                $auditTrail->logEvent(AuditFunctions::VIP_LEVEL_MANAGEMENT, "Failed change VIP Level Status to " . $status, array('ID' => null, 'SessionID' => $_SESSION["sessionID"]));
                $showdialog = true;
                $msg = "Failed to update VIP Level Status, Status Unchanged.";
                $title = "ERROR MESSAGE";
            }
        }
        else
        {
            $showdialog = true;
            $msg = "Updating VIP Level Status Failed, VIP level is being used in a player account/s.";
            $title = "ERROR MESSAGE";
        }

        
    }
}
?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>   
<script type='text/javascript'>

    $(document).ready(function() {

        function loadData() {
            getVipLevels();
        }

        loadData();

        function getVipLevels()
        {
            var url = "Helper/helper.viplevels.php";
            jQuery('#viplevels').GridUnload();
            jQuery("#viplevels").jqGrid({
                url: url,
                datatype: "json",
                mtype: "POST",
                colNames: ['VIPLevelID', 'Name', 'Status', 'Action' ],
                colModel: [
                    {name: 'VIPLevelID', index: 'VIPLevelID', align: 'left'},
                    {name: 'Name', index: 'Name', align: 'left'},
                    {name: 'Status', index: 'Status', align: 'left'},
                    {name: 'Action', index: 'Action', align: 'left'}
                ],
               
                rowNum: 10,
                        rowList: [10,20,30],
                        height: 300,
                        width: 770,
                        pager: "#pager2",
                        refresh: true,
                        loadonce: true,
                        loadText: "Loading...",
                        viewrecords: true,
                        sortorder: "desc",
                        caption:"VIP Level Management"
                        
            });
            jQuery("#viplevels").jqGrid('navGrid', '#pager2',
                    {
                        edit: false, add: false, del: false, search: false, refresh: true});
        }
        
        $("#editvip").live('click', function(e){
            e.preventDefault();
            var vipLevelID = $(this).attr('viplevelid');
            var name       = $(this).attr('name');
            var status     = $(this).attr('status');
            //Re-initialize Radio button to refresh selected value
            $("#active").prop("checked", false);
            $("#inactive").prop("checked", false);
            //Check the correct radio button
            if(status == 1)
            {
                $("#active").prop("checked", true);
            }
            else
            {
                $("#inactive").prop("checked", true);
            }
             $("#txtVipLevelID").val(vipLevelID).prop("disabled",true);
             $("#classification_type").val(name).prop("disabled",true);
             $("#hdnVipLevel").val(vipLevelID);
             $("#hdnName").val(name);
            $("#hdnprocess").val(2);
            var hidden_process  = $("#hdnprocess").val();
            if(hidden_process == 2)
            {
                $("#hiddenstatus").show();
            }
            else
            {
                $("#hiddenstatus").hide();
            }
            $("#addviplevel").dialog('open');
            $("#addviplevel").dialog('option','title','Update Details');
             
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
         $( "#dialog2" ).dialog({
            modal : true,
            title : 'Add new VIP Level',
            width : 350,
            height : 150,
            autoOpen : false,
            resizable : false,
            draggable :false,
            buttons : {
                'OK' : function(){
                    $(this).dialog('close');
                }
                
            }
        });
        
        $('#c').hide();
        $('#u').hide();
        $("#addviplevel").dialog({
            modal : true,
            title : 'Add new VIP Level',
            width : 420,
            height : 400,
            autoOpen : false,
            resizable : false,
            draggable :false,
            buttons : {
                'Save' : {
                    text: "Save",
                    id: "submitAddVip",
                    click: function(){
                        $('.formErrorContent').show();
                        $('.formErrorArrow').show();
                    }   
                },
                'Cancel' : function(){
                    $(this).dialog('close');
                }
                
            },
            beforeClose: function(){
                $('.formErrorContent').hide();
                $('.formErrorArrow').hide();
            },
        }).parent().appendTo($("#submitaddviplevel").validationEngine());
        
        $('#submitAddVip').live('click', function(e){
            e.preventDefault();
            var action = "";
            
            if($('#txtVipLevelID').val() == '' || $('#txtVipLevelID').val() == ''){
                $('#msg2').text('All fields are required.');
                $("#dialog2").dialog( "open" );
            }
            else{
                var hidden_process  = $("#hdnprocess").val();
                if(hidden_process == 1) //If add VIP Level
                {
                    $('#c').show();
                    action = "#confirmMsg";
                }
                else //If update VIP Level
                {
                    $('#u').show();
                    action = "#confirmUpdate";
                }


                $(action).dialog({
                    modal : true,
                    autoOpen : true,
                    title : 'CONFIRMATION',
                    resizable : false,
                    draggable :false,
                    buttons : {
                        'YES' : function(){
                            $('#submitaddviplevel').submit();
                        },
                        'NO' : function(){
                            $(this).dialog('close');
                        }
                    }
                });
            }
            
        });
        
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#btnAdd").click(function(){
        $('#txtVipLevelID').val("");
        $("#classification_type").val("");
        $("#hdnprocess").val(1);
        $("#txtVipLevelID").prop("disabled",false);
        $("#classification_type").prop("disabled",false);
        var hidden_process  = $("#hdnprocess").val();
        if(hidden_process == 2)
        {
            $("#hiddenstatus").show();
        }
        else
        {
            $("#hiddenstatus").hide();
        }
        $("#addviplevel").dialog('open');
        
       });
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
                        $(this).dialog('close');
                    }
                }
            });
        });
    <?php endif; ?>
</script>
<div align="center">
    <form id="submitform" name="submitform" method="post">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">
            <h2>
                VIP Level Management
            </h2>
            <br/>
            <div id="divlist">
                <table border="1" id="viplevels"></table>
                <div id="pager2"></div>
            </div>
            <div id="addviplvl" style="float : right; padding-right: 90px; padding-top: 20px">
                <?php echo $btnAdd; ?>
            </div>    
        </div>
    </div>
     </form>
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

<div id="dialog2">
    <label id="msg2">
    </label>
</div> 

<form action ="" id="submitaddviplevel" method="post" >
    <div id="addviplevel">
        <table id="tblviplevel" style="margin-top: 50px;">
            <tr>
                <td>VIP Level ID</td>
                <td><?php echo $txtVipLevelID; ?></td>
            </tr>
            <tr>
                <td>Description</td>
                <td><?php echo $classification_type; ?></td>
            </tr>
            <tr id="hiddenstatus" name="hiddenstatus" style="display : none">
                <td>Status</td>
                <td><input type="radio" id="active" name="status" value="1"/> Active 
                <input type="radio" id="inactive" name="status" value="0"/> Inactive</td>
                <?php echo $hdnName; echo $hdnVipLevel; ?>
            </tr>
        </table>
            
    </div>
    
    <?php echo $hdnProcess; ?>
</form>
<div id="confirmMsg">
    <p id="c">
        Proceed adding VIP Level?
    </p>
</div>
<div id="confirmUpdate">
    <p id="u">
        Proceed updating this VIP Level?
    </p>
</div>
<?php include("footer.php"); ?>
<?php
/**
 * Format names
 * @example Inputted lastname is 'dEla crUz' the ouput would be Dela Cruz
 * @param type $str Name
 * @return string reformatted name
 * @author Mark Kenneth Esguerra
 * @date November 12, 2013
 */
function formatName($str)
{
    $arrNames = explode(" ", $str);
    if (count($arrNames) > 1)
    {
        $name = "";
        foreach($arrNames as $names)
        {
           $n = trim(ucfirst(strtolower($names)));
           $name .= $n." ";
        }
        return trim($name);
    }
    else
    {
        $name = trim(ucfirst(strtolower($str)));
        return trim($name);
    }
}
?>