<?php
/**
 * View Promo
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: July 11, 2013
 */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "View Promo";
$currentpage = "Promo Maintenance";

//Clear the session for Redemtion
if(isset($_SESSION['CardRed']))
{
    unset($_SESSION['CardRed']);
}

// If question string is set, display success message 
if (isset($_REQUEST['success']))
{
    $openSuccessDialog = true;
    if (isset($_SESSION['UPDATE']['SUCCESS']))
    {
        $msg = $_SESSION['UPDATE']['SUCCESS'];
    }
    else if (isset($_SESSION['CHANGE']['SUCCESS']))
    {
       $msg = $_SESSION['CHANGE']['SUCCESS'];
    }
}
unset ($_SESSION['UPDATE']['SUCCESS']);
unset ($_SESSION['CHANGE']['SUCCESS']);
unset ($_SESSION['PromoID']);
?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>
<script type='text/javascript'>
    $(document).ready(function(){
        function loadData(){
                getPromos();
        } 
        loadData();     
        function getPromos()
        {
            var url = "Helper/helper.promos.php";
            jQuery("#promos").jqGrid({
                    url:url,
                    datatype: "json",
                    colNames:['Promo Name', 'Description', 'Start Date','End Date','Status','Action'],
                    colModel:[
                            {name: 'PromoName', index: 'PromoName', align: 'left', width: 170, fixed: true},
                            {name: 'Description', index: 'Description', align: 'left', width: 330, fixed: true},
                            {name: 'StartDate', index: 'StartDate', align: 'left', width: 120, fixed: true},
                            {name: 'EndDate', index: 'EndDate', align: 'left', width: 120},
                            {name: 'Status', index: 'Status', align: 'center', width: 100},
                            {name: 'Action', index: 'action', align: 'center', width: 140}
                    ],
                            
                    rowNum: 10,
                    rowList: [10,20,30],
                    height: 250,
                    width: 970,
                    pager: "#pager",
                    refresh: true,
                    loadonce: true,
                    loadText: "Loading...",
                    viewrecords: true,
                    sortorder: "desc",
                    sortname: "PromoID",
                    caption:"Promo"
            });
            jQuery("#promos").jqGrid('navGrid','#pager',
            {
                edit:false,add:false,del:false, search:false, refresh: true});
            }
        $("#updatelink").live("click",function(){
            var id = $(this).attr('PromoID');
            $("#hdnpromoID").val(id);
            $("#viewpromo").submit();
        });
    });
</script>
<script type="text/javascript">
     $(document).ready(function(){
        <?php if (isset($msg) && isset($openSuccessDialog)): ?>
            $("#msg").html("<?php echo $msg; ?>");
            $( "#successDialog" ).dialog({
                modal:true,
                resizable: false,
                autoOpen: <?php echo $openSuccessDialog; ?>,
                buttons: {
                    "OK":function(){
                        $(this).dialog("close");
                    }
                }
            });
        <?php endif; ?>     
     });
</script>    
<div align="center">
    </form>
    <form name="viewpromo" action="updatepromo.php" id="viewpromo" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <br />
            <div class="content">
                    <div class="title">&nbsp;&nbsp;&nbsp;View Promo</div>   
                    <div class="pad5" align="right"></div>
                        <hr color="black">
                    <div class="pad5" align="right"></div>
                    <br>
                    <div align="center" id="pagination">
                        <table border="1" id="promos"></table>
                        <div id="pager"></div>
                        <span id="errorMessage"></span>
                    </div>
                    <input type="hidden" id="hdnpromoID" name="promoID" value="" />
            </div>
            <!----successful notification dialog box----->
            <div id="successDialog" title="Success Message">
                <p id="msg"></p>
            </div>
            <!------------------------------------------->
        </div>
    </form>
</div>
<?php include("footer.php"); ?>
