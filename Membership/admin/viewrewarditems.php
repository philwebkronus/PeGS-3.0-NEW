<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Viewing of Reward Items";
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
?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>
    $(function() {
        $( "#datepicker" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });
    $(document).ready(function(){
        
        function loadDetails()
        {
            var url = "Helper/herlper.viewrewarditems.php";
            jQuery('#players').GridUnload();
            jQuery("#players").jqGrid({
                url:url,
                mtype: 'post',
                datatype: "json",
                colNames:['Reward Item Name', 'Reward Item Description', 'Item Price', 'Item Count', 'Item Code','Available Item Count', 
                    'Reward Type', 'Status', 'Action'],
                colModel:[
                    {name:'RewardItemName',index:'RewardItemName',align: 'center', width: 245},
                    {name:'RewardItemDescription',index:'RewardItemDescription', align: 'center', width: 270},
                    {name:'RewardItemPrice',index:'RewardItemPrice', align: 'right', width: 120},
                    {name:'RewardItemCount',index:'RewardItemCount', align: 'center', width: 120},
                    {name:'RewardItemCode',index:'RewardItemCode', align: 'center', width: 120},
                    {name:'AvailableItemCount',index:'AvailableItemCount', align: 'center', width: 120},
                    {name:'IsCoupon',index:'IsCoupon', align: 'center', width: 125},
                    {name:'Status',index:'Status', align: 'center', width: 145},
                    {name:'button', index: 'button', width:150, align: 'center'},
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
                caption:"View Reward Items"
            });
            jQuery("#players").jqGrid('navGrid','#pager2',
            {
                edit:false,add:false,del:false, search:false, refresh: true});
        }
        
        loadDetails();
       
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Update Reward Items',
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

    <div class="maincontainer">
<?php include('menu.php'); ?>
        <br />
        <div class="title" align="">View Reward Items</div>
        <br />
        <hr color="black" />
        <br />
        <div class="content">
            <br>
            <div align="center" id="pagination">
                <table border="1" id="players">

                </table>
                <div id="pager2"></div>
                <span id="errorMessage"></span>
            </div>
        </div>
        <div id="SuccessDialog" name="SuccessDialog">
<?php if ($isOpen == 'true') {
    ?>
                <?php if ($isSuccess) {
                    ?>
                    <p>
                    <?php echo $msgprompt; ?>
                    </p>
                        <?php
                    } else {
                        ?>
                    <p>
                    <?php echo $msgprompt; ?>
                    </p>
                    <?php }
                ?>
                    <?php }
                ?>
        </div>
    </div>
</div>
            <?php include("footer.php"); ?>
