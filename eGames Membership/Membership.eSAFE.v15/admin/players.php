<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Banned Players";
$currentpage = "Reports";

App::LoadControl("DataGrid");
App::LoadControl("Hidden");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");

$defaultsearchvalue = "Enter Card Number or Player Name";

$fproc = new FormsProcessor();

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->Args = 'placeholder="Enter Card Number or Player Name"';
$txtSearch->AutoComplete = false;
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
        function loadData(){
            var carddata = '<?php echo isset($_SESSION['CardData']); ?>';
            if(carddata != ''){
                getPlayerDetails('<?php 
                                                if(isset($_SESSION['CardData']['Name'])) {
                                                    $txtSearch->Text = $_SESSION['CardData']['Name'];
                                                    echo $_SESSION['CardData']['Name'];
                                                } else if(isset($_SESSION['CardData']['CardNumber'])) {
                                                    $txtSearch->Text = $_SESSION['CardData']['CardNumber'];
                                                    echo $_SESSION['CardData']['CardNumber'];
                                                }
                                            ?>////');
            } else {
                getPlayerDetails('');
            }
        }
        
        loadData();
        
        $('#btnSearch').live('click', function(){
            var txtsearch = $("#txtSearch").val();
            if (txtsearch.substr(0,1) === " "){
                alert("Trailing space/s is/are not allowed");
            }
            else{
                getPlayerDetails($("#txtSearch").val());
            }    
        });
        
        
        function getPlayerDetails(txtSearch)
        {
            var url = "Helper/helper.players.php";
            jQuery('#players').GridUnload();
            jQuery("#players").jqGrid({
                    url:url,
                    mtype: 'post',
                    postData: {
                                txtSearch : function() {return txtSearch}
                              },
                    datatype: "json",
                    colNames:['Name', 'Card Number', 'ID', 'Birthdate', 'Status', 
                              'Remarks'],
                    colModel:[
                            {name:'FullName',index:'FullName',align: 'left', width: 245},
                            {name:'CardNumber',index:'CardNumber', align: 'left', width: 150},
                            {name:'ID',index:'ID', align: 'left', width: 150},
                            {name:'Birthdate',index:'Birthdate', align: 'left', width: 90},
                            {name:'Status',index:'Status', align: 'center', width: 90},
                            {name:'Remarks',index:'Remarks', align: 'left', width: 245},
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
                    caption:"List Of Banned Players"
            });
            jQuery("#players").jqGrid('navGrid','#pager2',
                                {
                                    edit:false,add:false,del:false, search:false, refresh: true});
        }
    });
</script>
<div align="center">
    </form>
    <form name="bannedplayerlists" id="bannedplayerlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                <?php include('bannedcardsearch.php'); ?>
                    <br><br>
                    <div align="center" id="pagination">
                        <table border="1" id="players">

                        </table>
                        <div id="pager2"></div>
                        <span id="errorMessage"></span>
                    </div>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>
