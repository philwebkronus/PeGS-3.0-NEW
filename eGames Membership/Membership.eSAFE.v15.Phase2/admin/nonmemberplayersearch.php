<?php

/**
 *@Description: Player Search for member and non member players.
 *@Author: Renz Tiratira
 *@DateCreated: 07/04/2017 10:00
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Non Member Player Search";
$currentpage = "Administration";

App::LoadCore("Validation.class.php");

App::LoadControl("DataGrid");
App::LoadControl("Hidden");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");

$defaultsearchvalue = "Enter Player Name";

$fproc = new FormsProcessor();

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required, custom[onlyLetterSp]]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->AutoComplete = false;
$txtSearch->Args = 'placeholder="Enter Player Name"';
$fproc->AddControl($txtSearch);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->Enabled = false;
$fproc->AddControl($btnSearch);

$btnClear = new Button('btnClear', 'btnClear', 'Clear');
$btnClear->ShowCaption = true;
$btnClear->IsSubmit = true;
$fproc->AddControl($btnClear);

$fproc->ProcessForms();
if ($fproc->IsPostBack)
{
    if ($btnClear->SubmittedValue == "Clear")
    {
        $txtSearch->Text = "";
    }
}

?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script language="javascript" type="text/javascript">
    $(document).ready(function(){
        $('#btnSearch').live('click',function(){
           var txtsearch = $('#txtSearch').val();
           if (txtsearch.substr(0,1) === " ")
           {
               alert("Leading space/s is/are not allowed");
           }
           else
           {
               jQuery.ajax(
               {
                   url: 'Helper/helper.nonmemberplayersearch.php',
                   type: 'post',
                   data: {pager: function(){ return "GetPlayerInfo";},
                          txtSearch: function(){return $('#txtSearch').val();}},
                   dataType: 'json',
                   success: function(data)
                   {
                       getPlayerDetails($('#txtSearch').val());
                   },
                   error: function()
                   {
                       alert('Player Not Found.');
                       $('#txtSearch').val("");
                   }
               });
           }
           
        });
                   
function getPlayerDetails(txtSearch)
{
    var url = "Helper/helper.nonmemberplayersearch.php";
    jQuery('#playerinfo').GridUnload();
    jQuery("#playerinfo").jqGrid(
    {
        url:url,
        mtype: 'post',
        postData: 
        {
            pager: function(){ return "GetPlayerInfoGrid";},
            txtSearch : function() {return txtSearch}
        },
        datatype: "json",
        colNames:['Name', 'Player Number', 'BirthDate', 'Mobile Number'],
        colModel:[
        {name:'FirstName',index:'FirstName',align: 'left', width: 200},
        {name:'PlayerNumber',index:'PlayerNumber', align: 'left', width: 150},
        {name:'BirthDate',index:'BirthDate', align: 'left', width: 90},
        {name:'MobileNumber',index:'MobileNumber', align: 'left', width: 100},
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
        caption:"Non Member Player Search"
    });
    
    jQuery("#playerinfo").jqGrid('navGrid','#pager2',
    {
        edit:false,add:false,del:false, search:false, refresh: true});
    }
    });
</script>
<script language="javascript" type="text/javascript">
    $(document).ready(function(){
        defaultvalue = "<?php echo $defaultsearchvalue; ?>";
        
        $('#txtSearch').keyup(function(){
            $('#btnSearch').removeAttr('disabled');
        });
        
        $("#txtSearch").blur(function()
        {
            $("#txtSearch").change();
        });
        
        $('#txtSearch').change(function(){
            if($('#txtSearch').val() === "")
            {
                $('#btnSearch').attr('disabled', 'disabled');                
                $('#txtSearch').val("");
            }
            else
            {
                $('#btnSearch').removeAttr('disabled');        
            }
        });
        
        $('#btnClear').click(function(){
            $('#txtSearch').val("");
        });
    });
</script>
<div align="center">
    </form>
    <form name="searchplayer" id="searchplayer" method="POST">
    <div class="maincontainer">
        <?php //include('menu.php'); ?>
        <div class="content">
            <h2>Non Member Player Search</h2>
            <br/>
            <div class="searchbar formstyle">
                <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
            </div>
            <br />
            <div align="center" id="pagination">
                <table border="1" id="playerinfo"></table>
                <div id="pager2"></div>
                <span id="errorMessage"></span>
            </div>
        </div>
    </div>
    </form>
</div>

<?php include("footer.php"); ?>
