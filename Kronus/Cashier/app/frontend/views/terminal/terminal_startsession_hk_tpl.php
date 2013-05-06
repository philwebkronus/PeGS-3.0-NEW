<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('input.auto').autoNumeric();
    $('#StartSessionFormModel_voucher_code').hide();
})
</script>
<form id="frmhotkey">
    <?php if($startSessionFormModel->error_count): ?>
    <?php echo $startSessionFormModel->getErrorMessages(); ?>
    <?php endif; ?>       
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'max_deposit'); ?>
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'min_deposit'); ?>
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <table id="tblstartsession">
        <tbody>
            <tr>
                <th><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'TERMINAL:') ?></th>
                <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'Select Terminal')) ?></td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($startSessionFormModel, 'sel_amount', 'AMOUNT:') ?></th>
                <td>
                    <div>
                        <?php 
                            if($startSessionFormModel->terminal_id != '') {
                                echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', $denomination,array(''=>'Select Amount'),array('--'=>'Other denomination','voucher'=>'Voucher'));
                            } else {
                                echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', array(),array(''=>'Select Amount'));
                            }
                        ?>
                    </div>
                        <?php echo MI_HTML::inputText($startSessionFormModel, 'amount',array('readonly'=>'readonly','class'=>'auto','maxlength'=>8)); ?>
                        <?php echo MI_HTML::inputText($startSessionFormModel, 'voucher_code',array('maxlength'=>20)); ?>
                </td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($startSessionFormModel, 'casino', 'CASINO:'); ?></th>
                <td>
                    <?php 
                        if($startSessionFormModel->terminal_id != '') {
                            echo MI_HTML::dropDown($startSessionFormModel, 'casino', $casinos);
                        } else {
                             echo MI_HTML::dropDown($startSessionFormModel, 'casino', array(''=>'Select Casino'));
                        }
                        
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($startSessionFormModel, 'loyalty_card', 'Membership Card:') ?></th><td><?php echo MI_HTML::inputPassword($startSessionFormModel, 'loyalty_card') ?></td>
            </tr>
            <tr>
                <th><a href="javascript:void(0);" id="get_info_card">Get Card Info</a></th><td><b><a href="javascript:void(0);" id="register">Register</a></b></td>
            </tr>    
            <tr>
                <th><input id="btnInitailDepositHk" type="button" value="Submit" /></th>
                <td><input class="btnClose" type="button" value="Cancel" /></td>
            </tr>
        </tbody>
    </table>
</form>
<?php Mirage::loadLibraries('LoyaltyScripts'); ?>