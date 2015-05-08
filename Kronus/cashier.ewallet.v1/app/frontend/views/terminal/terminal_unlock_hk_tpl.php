<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('input.auto').autoNumeric();
    $('#UnlockTerminalFormModel_voucher_code').hide();
});
</script>
<form id="frmhotkey">
    <?php if($unlock->error_count): ?>
    <?php echo $unlock->getErrorMessages(); ?>
    <?php endif; ?>       
    <?php echo MI_HTML::inputHidden($unlock, 'max_deposit'); ?>
    <?php echo MI_HTML::inputHidden($unlock, 'min_deposit'); ?>
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <table id="tblstartsession">
        <tbody>
            <tr>
                <th><?php echo MI_HTML::label($unlock, 'terminal_id', 'TERMINAL:') ?></th>
                <td><?php echo MI_HTML::dropDownArray($unlock, 'terminal_id', $terminals, 'id', 'code', array(''=>'Select Terminal')) ?></td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($unlock, 'sel_amount', 'AMOUNT:') ?></th>
                <td>
                    <div>
                        <?php 
                            if($unlock->terminal_id != '') {
                                echo MI_HTML::dropDown($unlock, 'sel_amount', $denomination,array(''=>'Select Amount','0.00'=>'0.00'),array('--'=>'Other denomination','voucher'=>'Voucher'));
                            } else {
                                echo MI_HTML::dropDown($unlock, 'sel_amount', array(),array(''=>'Select Amount','0.00'=>'0.00'));
                            }
                        ?>
                    </div>
                        <?php echo MI_HTML::inputText($unlock, 'amount',array('readonly'=>'readonly','class'=>'auto','maxlength'=>8)); ?>
                        <?php echo MI_HTML::inputText($unlock, 'voucher_code',array('maxlength'=>20)); ?>
                </td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($unlock, 'casino', 'CASINO:'); ?></th>
                <td>
                    <?php 
                        if($unlock->terminal_id != '') {
                            echo MI_HTML::dropDown($unlock, 'casino', $casinos);
                        } else {
                             echo MI_HTML::dropDown($unlock, 'casino', array(''=>'Select Casino'));
                        }
                        
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($unlock, 'loyalty_card', 'Membership Card:') ?></th><td><?php echo MI_HTML::inputPassword($unlock, 'loyalty_card') ?></td>
            </tr>
            <tr>
                <th colspan ="2"><center><a href="javascript:void(0);" id="get_info_card2">Get Card Info</a></center></th>
                <!--<td><b><a href="javascript:void(0);" id="register">Register</a></b></td>-->
            </tr>    
            <tr>
                <th><input id="btnUnlockHk" type="button" value="Submit" /></th>
                <td><input class="btnClose" type="button" value="Cancel" /></td>
            </tr>
        </tbody>
    </table>
</form>
<?php Mirage::loadLibraries('LoyaltyScripts'); ?>