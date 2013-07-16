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
// If question string is set, display success message 
if (isset($_REQUEST['success']))
{
    $openSuccessDialog = true;
    if (isset($_SESSION['MESSAGE']['SUCCESS']))
    {
        $msg = $_SESSION['MESSAGE']['SUCCESS'];
    }    
    unset ($_SESSION['MESSAGE']['SUCCESS']);
}
unset ($_SESSION['PromoID']);
?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
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
            jQuery('#promos').GridUnload();
            jQuery("#promos").jqGrid({
                    url:url,
                    datatype: "json",
                    colNames:['Promo Name', 'Description', 'Start Date','End Date','Status','Action'],
                    colModel:[
                            {name: 'PromoName', index: 'PromoName', align: 'left', width: 170},
                            {name: 'Description', index: 'Description', align: 'left', width: 350},
                            {name: 'StartDate', index: 'StartDate', align: 'left', width: 120},
                            {name: 'EndDate', index: 'EndDate', align: 'left', width: 110},
                            {name: 'Status', index: 'Status', align: 'center', width: 130},
                            {name: 'Action', index: 'action', align: 'center', width: 170}
                    ],
                            
                    rowNum: 10,
                    rowList: [10,20,30],
                    height: 250,
                    width: 970,
                    pager: "#pager",
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    loadtext: "Loading...",
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
     });
</script>    
<div align="center">
    </form>
    <form name="viewpromo" action="updatepromo.php" id="viewpromo" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                    <br><br>
                    <div class="title">View Promo</div>   
                    <br /><br />
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