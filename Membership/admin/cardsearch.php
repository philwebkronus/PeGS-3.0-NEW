<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-04
 * Company: Philweb
 * ***************** */
?>
<script language="javascript" type="text/javascript">
    $(document).ready(
    function()
    {
        defaultvalue = "<?php echo $defaultsearchvalue; ?>";
        $("#txtSearch").click(function(){
            $("#txtSearch").change();
            if ($("#txtSearch").val() == "")
            {
                $("#txtSearch").val("");
                $("#btnSearch").attr("disabled", "disabled");
            }
        });
        $("#txtSearch").keyup(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").blur(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").change(function(){
            if ($("#txtSearch").val() == "")
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
            //$("#txtSearch").change();
        });
        
    
    });
</script>
</form>
<form name="frmSearch" id="frmSearch" method="post" action="" >
<div class="searchbar formstyle">
        <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
</div>
</form>
<?php
if (isset($MID) && $MID != "" && $showcardinfo && $loyaltyinfo != null)
{
    ?>
    <div id="cardinfo">
        <table>
            <tr>
                <td colspan="4"><span class="title">Card Point Information</span></td>
            </tr>
            <tr>
                <td class="alternatingcolor">Card Number</td>
                <td align="right" class="alternatingcolor"><?php echo $CardNumber; ?></td>
                <td class="alternatingcolor">Card Type</td>
                <td align="right" class="alternatingcolor"><?php echo $cardType; ?></td>
            </tr>
            <tr>
                <td>Current Points</td>
                <td align="right"><?php echo number_format($currentPoints, 0); ?></td>
                <td>Lifetime Points</td>
                <td align="right"><?php echo number_format($lifetimePoints, 0); ?></td>
            </tr>
            <tr>
                <td class="alternatingcolor">Bonus Points</td>
                <td align="right" class="alternatingcolor"><?php echo number_format($bonusPoints, 0); ?></td>
                <td class="alternatingcolor">Redeemed Points</td>
                <td align="right" class="alternatingcolor"><?php echo number_format($redeemedPoints, 0); ?></td>
            </tr>
            <tr>
                <td>Last Played Site</td>
                <td align="right"><?php echo $siteName; ?></td>
                <td>Last Played Date</td>
                <td align="right"><?php echo $transDate; ?></td>
            </tr>
        </table>
    </div>
<?php } ?>
