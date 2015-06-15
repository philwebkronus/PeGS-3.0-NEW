<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Verification of Reward Items";
$currentpage = "Administration";

//Clear the session for Redemtion
if(isset($_SESSION['CardRed'])){
    unset($_SESSION['CardRed']);
}

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
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
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
                    {name:'RewardItemName',index:'RewardItemName',align: 'left', width: 245},
                    {name:'RewardItemDescription',index:'RewardItemDescription', align: 'left', width: 270},
                    {name:'RewardItemPrice',index:'RewardItemPrice', align: 'right', width: 120},
                    {name:'RewardItemCount',index:'RewardItemCount', align: 'left', width: 120},
                    {name:'RewardItemCode',index:'RewardItemCode', align: 'left', width: 120},
                    {name:'AvailableItemCount',index:'AvailableItemCount', align: 'left', width: 120},
                    {name:'IsCoupon',index:'IsCoupon', align: 'left', width: 125},
                    {name:'Status',index:'Status', align: 'left', width: 145},
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
            <div class="title">&nbsp;&nbsp;&nbsp;<?php echo $pagetitle; ?></div>
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
