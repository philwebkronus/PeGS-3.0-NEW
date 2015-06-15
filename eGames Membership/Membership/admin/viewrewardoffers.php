<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "View Reward Offers";
$currentpage = "Administration";

App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Kronus", "TransactionSummary");

App::LoadControl("DatePicker");
App::LoadControl("ComboBox");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("DataGrid");
App::LoadControl("Hidden");

$fproc = new FormsProcessor();

App::LoadModuleClass('Kronus', 'Sites');

$hdnRewardItemID = new Hidden('hdnRewardItemID', 'hdnRewardItemID', '');
$fproc->AddControl($hdnRewardItemID);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Query");
$btnSubmit->ShowCaption = true;
$btnSubmit->Enabled = true;
$btnSubmit->IsSubmit = false;
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

if (isset($_SESSION['msg'])) {
    $isOpen = 'true';
    $isSuccess = true;
    $msg = $_SESSION['msg'];
} else {
    $isOpen = 'false';
    $isSuccess = false;
}
?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>

    $(document).ready(function() {

        function loadDetails()
        {
            var url = "Helper/helper.rewardoffers.php";

            jQuery('#players').GridUnload();
            jQuery("#players").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                    pager: function() {
                        return "ViewRewardOffers";
                    }
                },
                datatype: "json",
                colNames: ['Reward Item', 'Card Type', 'Promo', 'Partner', 'Required Points', 'Start Date', 'End Date', 'Status', 'Action'],
                colModel: [
                    {name: 'RewardItem', index: 'RewardItem', align: 'left', width: 245},
                    {name: 'CardType', index: 'CardType', align: 'left', width: 245},
                    {name: 'Promo', index: 'Promo'},
                    {name: 'Partner', index: 'Partner', align: 'left', width: 245},
                    {name: 'RequiredPoints', index: 'RequiredPoints', formatter: 'integer', align: 'right', width: 245},
                    {name: 'StartDate', index: 'StartDate', align: 'right', width: 245},
                    {name: 'EndDate', index: 'EndDate', align: 'right', width: 245},
                    {name: 'Status', index: 'Status'},
                    {name: 'Action', index: 'Action', align: 'center'}
                ],
                rowNum: 10,
                rowList: [10, 20, 30],
                height: 250,
                width: 970,
                pager: '#pager2',
                refresh: true,
                loadonce: true,
                viewrecords: true,
                sortorder: "asc",
                caption: "Reward Offers"
            });
            jQuery("#players").jqGrid('navGrid', '#pager2',
                    {
                        edit: false, add: false, del: false, search: false, refresh: true});
        }

        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title: 'Update Reward Details',
            closeOnEscape: true,
            buttons: {
                "Ok": function() {
                    if($(this).dialog("close")) {
                        <?php unset($_SESSION['msg']);  ?>
                    }
                }
            }
        });

        loadDetails();
        $(".btnUpdateRewardOffer").live('click', function() {
            var hdnRewardOfferID = document.getElementById('hdnRewardOfferID').value;
            var hdnRewardOfferStatus = document.getElementById('hdnRewardOfferStatus').value;
            if (hdnRewardOfferID == '') {
                alert('Unable to fetch Reward Offer ID!');
            }
            if (hdnRewardOfferStatus == '') {
                alert('Unable to fetch Reward Offer Status!');
            }
            else {
                $('#rewardoffersid').submit();
            }
        });
    });
</script>

<div align="center">

    <form name="bannedplayerlists" id="bannedplayerlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <br />
            <div class="title">&nbsp;&nbsp;&nbsp;&nbsp;View Reward Offers:</div>
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
                    <?php if ($isSuccess) { ?>
                        <p>
                            <?php echo $msg; ?>
                            <?php
                        }
                        ?>
                    </p>
                    <?php
                } else {
                    ?>
                <?php }
                ?>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>