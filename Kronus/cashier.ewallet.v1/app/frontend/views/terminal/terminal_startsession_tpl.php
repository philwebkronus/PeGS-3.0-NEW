<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('input.auto').autoNumeric();
    $('#StartSessionFormModel_voucher_code').hide();
    $('.bankContainer').hide();
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
                <th><?php echo MI_HTML::label($startSession, 'sel_amount', 'AMOUNT') ?></th>
                <td>
                    <div>
                        <?php echo MI_HTML::dropDown($startSession, 'sel_amount', 
                                $denomination,array(''=>'Select Amount'),array('--'=>'Other denomination','voucher'=>'Voucher', 'bancnet'=>'Bancnet'), array('class'=>'width204')); ?>
                    </div>
                        <?php echo MI_HTML::inputText($startSession, 'amount',array('readonly'=>'readonly','class'=>'auto width200','maxlength'=>8)); ?>
                    
                        <?php echo MI_HTML::inputText($startSession, 'voucher_code',array('maxlength'=>20, 'class'=>'width200')); ?>
                </td>
            </tr>
            
            <tr class="bankContainer">
                <th><?php echo MI_HTML::label($startSession, 'lbl_traceNumber', 'TRACE NUMBER') ?></th>
                <td><?php echo MI_HTML::inputText($startSession, 'trace_number',array('class'=>'width200','maxlength'=>20)); ?> <td>
            </tr>
            
            <tr class="bankContainer">
                <th><?php echo MI_HTML::label($startSession, 'lbl_refNumber', 'REFERENCE NUMBER') ?></th>
                <td><?php echo MI_HTML::inputText($startSession, 'reference_number',array('class'=>'width200','maxlength'=>20)); ?><td>
                    
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($startSession, 'casino', 'CASINO'); ?></th>
                <td><?php echo MI_HTML::dropDown($startSession, 'casino', $casinos, array(), array(), array('class'=>'width204')) ?></td>
            </tr>
            <tr>
                <th><?php echo MI_HTML::label($startSession, 'loyalty_card', 'Membership Card') ?></th><td><?php echo MI_HTML::inputPassword($startSession, 'loyalty_card',  array('class'=>'width200')); ?></td>
            </tr>
            <tr>
                <th colspan="2"><center><a href="javascript:void(0);" id="get_info_card">Get Card Info</a></center></th>
                <!--<td><b><a style="display: none;" href="javascript:void(0);" id="register">Register</a></b></td>-->
            </tr>    
            <tr>
                <th colspan="2" class="childtableCell center" style="padding-left:100px;">
                    <div><input id="btnInitailDeposit" type="button" value="Submit" /></div>
                    <div><input class="btnClose" type="button" value="Cancel" /></div>
                </th>
<!--                <td class="">
                    <div><input id="btnInitailDeposit" type="button" value="Submit" /></div>
                    <div><input class="btnClose" type="button" value="Cancel" /></div>
                </td>-->
            </tr>
        </tbody>
    </table>
</form>    

<?php Mirage::loadLibraries('LoyaltyScripts'); ?>