<?php

/**
* Description: Card Search for redemption transaction.
* @author: aqdepliyan
* DateCreated: 2013-07-16 07:16 PM
*/
?>
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

    });
</script>
<div class="searchbar formstyle">
        <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
</div>
<div id="cardinfo" style="display: none;">
    <?php echo $hdnRewardID; ?>
    <table>
        <tr>
            <td colspan="4"><span class="title">Card Point Information</span></td>
        </tr>
        <tr>
            <td class="alternatingcolor">Card Number</td>
            <td align="right" class="alternatingcolor" id="idcardnumber"></td>
            <td class="alternatingcolor">Card Type</td>
            <td align="right" class="alternatingcolor" id="idcardtype"></td>
        </tr>
        <tr>
            <td>Current Points</td>
            <td align="right" id="idcurrentpoints"></td>
            <td>Lifetime Points</td>
            <td align="right" id="idlifetimepoints"></td>
        </tr>
        <tr>
            <td class="alternatingcolor">Bonus Points</td>
            <td align="right" class="alternatingcolor" id="idbonuspoints"></td>
            <td class="alternatingcolor">Redeemed Points</td>
            <td align="right" class="alternatingcolor" id="idredeemedpoints"></td>
        </tr>
        <tr>
            <td>Last Played Site</td>
            <td align="right" id="idsitename"></td>
            <td>Last Played Date</td>
            <td align="right" id="idtransdate"></td>
        </tr>
    </table>
</div>