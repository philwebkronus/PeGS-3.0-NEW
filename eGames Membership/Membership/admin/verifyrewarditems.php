<?php
require_once("../init.inc.php");
include('sessionmanager.php');
include("controller/verifyrewarditemscontroller.php");
$pagetitle = "Verification of Reward Items";
$currentpage = "Administration";

//Clear the session for Redemtion
if (isset($_SESSION['CardRed'])) {
    unset($_SESSION['CardRed']);
}
?>

<?php include("header.php"); ?>
<script>
    $(document).ready(function() {
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title: 'Verification of Reward Items',
            closeOnEscape: true,
            draggable: false,
            open: function(event, ui) {
                    $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
                },
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                    window.location = "verifyrewarditems.php";
                }
            }
        });
        window.parent.$("#SuccessDialog").dialog("option", "resizable", false);

        jQuery("#btnSubmit").click(function()
        {
            $("#accordion").accordion("activate", 0);
        });
    });
</script>
<style>
    <!--
    .tab { margin-left: 40px; }
    .tab2 { margin-left: 650px; }
    -->
</style>
<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <br />
        <div class="title">&nbsp;&nbsp;&nbsp;Verify Reward Item:</div>
        <br />
        <hr color="black" />
        <br />
        <div class="content">
            <table align="center">
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Serial Code &nbsp;</td>
                    <td><?php echo "$txtSerialCode"; ?></td>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Security Code &nbsp;</td>
                    <td><?php echo "$txtSecurityCode"; ?></td>
                    <td align="left"><?php echo "$btnSubmit"; ?></td>
                </tr>
            </table>
            <div id="SuccessDialog" name="SuccessDialog">
                <?php if ($isOpen == 'true') {
                    ?>
                    <p align="center">
                        <?php echo $resultmsg; ?>
                    </p>
                <?php }
                ?>
            </div>
        </div>
    </div>
</div>
<?php include("footer.php"); ?>