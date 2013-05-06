<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('input.auto').autoNumeric();
    $('#StartSessionFormModel_voucher_code').hide();
})
</script>
<form id="frmstartsession">
    <?php echo MI_HTML::inputHidden($startSession, 'terminal_id') ?>
    <?php echo MI_HTML::inputHidden($startSession, 'min_deposit') ?>
    <?php echo MI_HTML::inputHidden($startSession, 'max_deposit') ?>
    <input type="hidden" name="isvip" value="<?php echo $is_vip; ?>" />
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <input type="hidden" name="tcode" id="tcode" value="<?php echo $tcode; ?>" />
    <?php //if($startSession->error_count): ?>
    <?php //echo $startSession->getErrorMessages(); ?>
    <?php //endif; ?>
    <table id="tblstartsession">
        <tbody>
            <tr>
                <th><?php echo MI_HTML::label($startSession, 'sel_amount', 'AMOUNT:') ?></th>
                <td>
                    <div>
                        <?php echo MI_HTML::dropDown($startSession, 'sel_amount', 
                                $denomination,array(''=>'Select Amount'),array('--'=>'Other denomination','voucher'=>'Voucher')); ?>
                    </div>
                        <?php echo MI_HTML::inputText($startSession, 'amount',array('readonly'=>'readonly','class'=>'auto','maxlength'=>8)); ?>
                        <?php echo MI_HTML::inputText($startSession, 'voucher_code',array('maxlength'=>20)); ?>
                </td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($startSession, 'casino', 'CASINO'); ?></th>
                <td><?php echo MI_HTML::dropDown($startSession, 'casino', $casinos) ?></td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($startSession, 'loyalty_card', 'Membership Card:') ?></th><td><?php echo MI_HTML::inputPassword($startSession, 'loyalty_card') ?></td>
            </tr>
            <tr>
                <th><a href="javascript:void(0);" id="get_info_card">Get Card Info</a></th><td><b><a href="javascript:void(0);" id="register">Register</a></b></td>
            </tr>    
            <tr>
                <th><input id="btnInitailDeposit" type="button" value="Submit" /></th>
                <td><input class="btnClose" type="button" value="Cancel" /></td>
            </tr>
        </tbody>
    </table>
</form>    

<?php Mirage::loadLibraries('LoyaltyScripts'); ?>