<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('input.auto').autoNumeric();
    $('#UnlockTerminalFormModel_voucher_code').hide();
})
</script>
<form id="frmunlock">
    <input type="hidden" name="isvip" value="<?php echo $is_vip; ?>" />
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <input type="hidden" name="tcode" id="tcode" value="<?php echo $tcode; ?>" />
    <?php //if($unlock->error_count): ?>
    <?php //echo $unlock->getErrorMessages(); ?>
    <?php //endif; ?>
    <table id="tblstartsession">
        <tbody>
            
            <tr>
                <th><?php echo MI_HTML::label($unlock, 'loyalty_card', 'Membership Card:') ?></th><td><?php echo MI_HTML::inputPassword($unlock, 'loyalty_card') ?></td>
            </tr>
            <tr>
                <th colspan="2"><center><a href="javascript:void(0);" id="get_info_card2">Get Card Info</a></center></th>
                <!--<td><b><a style="display: none;" href="javascript:void(0);" id="register">Register</a></b></td>-->
            </tr>    
            <tr>
                <th><input id="btnUnlockTerminal" type="button" value="Submit" /></th>
                <td><input class="btnClose" type="button" value="Cancel" /></td>
            </tr>
        </tbody>
    </table>
</form>    

<?php Mirage::loadLibraries('LoyaltyScripts'); ?>