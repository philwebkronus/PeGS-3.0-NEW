<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Change Player Status";
$currentpage = "Administration";

if (isset($_SESSION['msg'])) {
    $isOpen = 'true';
    $isSuccess = $isOpen;
        $msgprompt = $_SESSION['msg'];
        unset($_SESSION['msg']);

} else {
    $isOpen = 'false';
    $isSuccess = 'false';
    $msgprompt = '';
}

App::LoadCore('Validation.class.php');

App::LoadControl("DataGrid");
App::LoadControl("Hidden");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");

$defaultsearchvalue = "Enter Card Number or Player Name";

$fproc = new FormsProcessor();

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required, custom[emailAlphanumeric]]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->AutoComplete = false;
$txtSearch->Args = 'placeholder="Enter Card Number or Player Name"';
$fproc->AddControl($txtSearch);

$hdnAID = new Hidden("AID", "AID", "AID: ");
$hdnAID->ShowCaption = true;
$hdnAID->Text = $_SESSION['userinfo']['AID'];
$fproc->AddControl($hdnAID);

$hdnSearchType = new Hidden("SearchType", "SearchType", "SearchType: ");
$hdnSearchType->ShowCaption = true;
$hdnSearchType->Text = "";
$fproc->AddControl($hdnSearchType);

$hdnMemberCardID = new Hidden("MemberCardID", "MemberCardID", "MemberCardID: ");
$hdnMemberCardID->ShowCaption = true;
$hdnMemberCardID->Text = "";
$fproc->AddControl($hdnMemberCardID);

$hdnCardNumber = new Hidden("CardNumber", "CardNumber", "Card Number: ");
$hdnCardNumber->ShowCaption = true;
$hdnCardNumber->Text = "";
$fproc->AddControl($hdnCardNumber);

$hdnMID = new Hidden("MID", "MID", "MID: ");
$hdnMID->ShowCaption = true;
$hdnMID->Text = "";
$fproc->AddControl($hdnMID);

$hdnStatus = new Hidden("Status", "Status", "Status: ");
$hdnStatus->ShowCaption = true;
$hdnStatus->Text = "";
$fproc->AddControl($hdnStatus);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->Enabled = false;
$fproc->AddControl($btnSearch);

$btnClear = new Button('btnClear', 'btnClear', 'Clear');
$btnClear->ShowCaption = true;
$btnClear->IsSubmit = true;
$fproc->AddControl($btnClear);

$fproc->ProcessForms();

//Clear the session for Redemtion
if(isset($_SESSION['CardRed'])){
    unset($_SESSION['CardRed']);
}

if ($fproc->IsPostBack)
{
    if ($btnClear->SubmittedValue == "Clear")
    {
        unset($_SESSION['CardData']);
        $txtSearch->Text = "";
    }
}

?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>
    
    $(document).ready(function(){
        
        $('#btnSearch').live('click', function(){
            var txtsearch = $("#txtSearch").val();
            if (txtsearch.substr(0,1) === " "){
                alert("Trailing space/s is/are not allowed");
            }
            else
            {
                jQuery.ajax(
                {
                   url: "Helper/helper.playerstatus.php",
                   type: 'post',
                   data: {pager: function(){ return "GetBanUnban";},
                          txtSearch : function() {return $("#txtSearch").val();}
                         },
                   dataType : 'json',     
                   success: function(data)
                   {
                       if(data > 0){
                           getPlayerDetails($("#txtSearch").val());
                       }
                       else{
                           $.each(data, function(i,user)
                          {
                           jQuery('.ui-dialog-content').html("<p><center><label>"+$(this).attr('ErrorMsg')+"</label></center></p>");
                           $("#SuccessDialog").dialog("open");
                           });
                           jQuery('#playerstatus').GridUnload();
                       }   
                       
                   },
                   error: function(XMLHttpRequest, e)
                   {
                         alert(XMLHttpRequest.responseText);
                         if(XMLHttpRequest.status == 401)
                         {
                             window.location.reload();
                         }
                   }
                });
            }    
        });
        
        
        
        
        function getPlayerDetails(txtSearch)
        {
            var url = "Helper/helper.playerstatus.php";
            jQuery('#playerstatus').GridUnload();
            jQuery("#playerstatus").jqGrid({
                    url:url,
                    mtype: 'post',
                    postData: {
                                pager: function(){ return "GetBanUnbanGrid";},
                                txtSearch : function() {return txtSearch}
                              },
                    datatype: "json",
                    colNames:['Name', 'Card Number', 'ID', 'Birthdate', 'Status', 
                              'Remarks'],
                    colModel:[
                            {name:'FullName',index:'FullName',align: 'left', width: 200},
                            {name:'CardNumber',index:'CardNumber', align: 'left', width: 150},
                            {name:'ID',index:'ID', align: 'left', width: 150, hidden: true},
                            {name:'Birthdate',index:'Birthdate', align: 'left', width: 90},
                            {name:'Status',index:'Status', align: 'left', width: 90},
                            {name:'Remarks',index:'Remarks', align: 'left', width: 290},
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 250,
                    width: 970,
                    pager: '#pager2',
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"Change Player Status"
            });
            jQuery("#playerstatus").jqGrid('navGrid','#pager2',
                                {
                                    edit:false,add:false,del:false, search:false, refresh: true});
        }
        
        $(".statuslink").live('click', function(){
            var cardnumber = $(this).attr('CardNumber');
            if(cardnumber != ''){
                $("#CardNumber").val(cardnumber);
                $("#MemberCardID").val($(this).attr('MemberCardID'));
                var status = $(this).attr('Status');
                if(status == 5 ){
                    $("#Status").val(1);
                } else {
                    $("#Status").val(5);
                }
                $("#MID").val($(this).attr('MID'));

                    if($("#updates").dialog('isOpen') !== true){
                        $("#updates").dialog({
                            modal: true,
                            width: 300,
                            height: 'auto',
                            position: 'center',
                            buttons: {
                                "Submit": function(){
                                    if($("#updatestatus").validationEngine("validate")){
                                        $.post("ajaxhandler.php",
                                        {
                                            'Module': 'Admin',
                                            'Class': 'PlayerBanningWrapper',
                                            'Method': 'updatePlayerStatus',
                                            'MethodArgs': $("#updatestatus").serialize()
                                        },
                                        function(data){
                                            if(data != "Change Player Status: Transaction Successful."){
                                                $("#failedmessage").html("<p>"+data+"</p>");
                                                $("#failedmessage").dialog({
                                                    modal: true,
                                                    width: 350,
                                                    height: 'auto',
                                                    position: 'center',
                                                    buttons: {
                                                        "Ok": function(){
                                                            $("#updatestatus").dialog('close');
                                                            $(this).dialog('close');
                                                            $("#updateplayerstatus").submit();
                                                        }
                                                    }
                                                });
                                            } else {
                                                $("#successmessage").html("<p>"+data+"</p>");
                                                $("#successmessage").dialog({
                                                    modal: true,
                                                    width: 350,
                                                    height: 'auto',
                                                    position: 'center',
                                                    buttons: {
                                                        "Ok": function(){
                                                            $("#updatestatus").dialog('close');
                                                            $(this).dialog('close');
                                                            $("#updateplayerstatus").submit();
                                                        }
                                                    }
                                                });
                                            }
                                        }, 'json');
                                    }
                                },
                                "Cancel": function(){
                                    $("#txtRemarks").val('');
                                    $(this).dialog('close');
                                }
                            },
                            title: 'Change Player Status'
                        }).parent().appendTo($("#updatestatus"));
                    }
            }

        });
    });
</script>

<script language="javascript" type="text/javascript">
    $(document).ready(
    function()
    {
        defaultvalue = "<?php echo $defaultsearchvalue; ?>";
        $("#txtSearch").click(function(){
            $("#txtSearch").change();
            if ($("#txtSearch").val() === "")
            {
                $("#txtSearch").val("");
            }
        });
        $("#txtSearch").keyup(function(){
            $("#txtSearch").change();
            $("#btnSearch").removeAttr("disabled");
        });
        $("#txtSearch").blur(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").change(function(){
            if ($("#txtSearch").val() === "")
            {
                $("#btnSearch").attr("disabled", "disabled");
                $("#txtSearch").val("");
            }
            else
            {
                $("#btnSearch").removeAttr("disabled");
            }
            
        });
        $("#btnClear").click(function(){
            $("#txtSearch").val("");
        });
        
        
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Player Banning/Unbanning',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                }
            }
        });
    
    });
</script>
<div align="center">
    </form>
    <form name="updateplayerstatus" id="updateplayerstatus" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                <h2>
                    Player Banning/Unbanning
                </h2>
                <br/>
<div class="searchbar formstyle">
        <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
</div>
                    <!--<br><br>-->
                    <div align="center" id="pagination">
                        <table border="1" id="playerstatus">

                        </table>
                        <div id="pager2"></div>
                        <span id="errorMessage"></span>
                    </div>
            </div>
                <div id="SuccessDialog" name="SuccessDialog">

                    <p>
                        <label id="label1"></label>
                    </p>
                </div>
        </div>
    </form>
    <form name="updatestatus" class="updatestatus" id="updatestatus">
        <div id="updates" class="updates" style="display:none;">
            <?php echo $hdnAID; ?>
            <?php echo $hdnMemberCardID; ?>
            <?php echo $hdnCardNumber; ?>
            <?php echo $hdnMID; ?>
            <?php echo $hdnStatus; ?>
            <p align="left">Do you wish to Ban/Unban this Player?</p>
            <p align="left">Please input remarks to continue.</p>
            <label style="float: left;width: 60px;display: block;">Remarks:</label>
            <div style="float:right;"><textarea name="txtRemarks" id="txtRemarks" cols="20" rows="1" class="validate[required]" maxlength="255" style="color: #666; width: 200px;"></textarea></div>
            <br>
        </div>
    </form>
    <div id="successmessage" style="display:none; color: green;">
        
    </div>
    <div id="failedmessage" style="display:none; color: red;">
        
    </div>
</div>
<?php include("footer.php"); ?>