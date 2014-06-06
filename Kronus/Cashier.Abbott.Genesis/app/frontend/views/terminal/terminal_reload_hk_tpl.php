<?php Mirage::loadLibraries('LoyaltyScripts'); ?>
<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('input.auto').autoNumeric();
    $('#StartSessionFormModel_voucher_code').hide();
})
</script>
<div id="tm-reload-form">
    <div>
        <?php if($startSessionFormModel->error_count): ?>
        <?php echo $startSessionFormModel->getErrorMessages(); ?>
        <?php endif; ?>     
        <?php if($terminal_balance !== null): ?>
        <p id="curr_playing_bal">Current Playing Balance: PhP <?php echo toMoney($terminal_balance); ?></p>
        <?php endif; ?>
        <form id="frmhotkey">
            <?php echo MI_HTML::inputHidden($startSessionFormModel, 'min_deposit') ?>
            <?php echo MI_HTML::inputHidden($startSessionFormModel, 'max_deposit') ?>
            <input type="hidden" id="tcode" value="<?php //echo $tcode ?>" name="tcode" />
            <input type="hidden" id="tid" value="<?php //echo $tid ?>" name="tid" />
            <input type="hidden" value="<?php echo $is_vip; ?>" name="is_vip" />
            <input type="hidden" value="<?php //echo $cid ?>" name="cid" />
        <table>
            <tr>
                <th class="left"><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'TERMINAL:'); ?></th>
                <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'Select Terminal')) ?></td>
            </tr>
            <tr>
                <th style="width: 120px;" class="left">CURRENT CASINO:</th>
                <td id="current_casino"></td>
            </tr>
            <tr>
                <th class="left"><?php echo MI_HTML::label($startSessionFormModel, 'amount', 'AMOUNT:') ?></th>
                <td>
                    <?php 
                        //if($startSessionFormModel->terminal_id != '') {
                        //    echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', $denomination,array(''=>'Select Amount'),array('--'=>'Other denomination'));
                        //} else {
                            echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', array(),array(''=>'Select Amount'));
                        //}
                    ?>
                    <?php echo MI_HTML::inputText($startSessionFormModel, 'amount',array('readonly'=>'readonly','class'=>'auto','maxlength'=>8)); ?>
                    <?php echo MI_HTML::inputText($startSessionFormModel, 'voucher_code',array('maxlength'=>20)); ?>
                </td>
            </tr>
            <tr>
                <th><input id="btnReloadhk" type="submit" value="Submit" /></th>
                <td><input class="btnClose" type="button" value="Cancel" /></td>
            </tr>            
        </table>
        </form>    
    </div>
    <div class="details">
        <table border="1" style="width: 100%">
            <tr>
                <td colspan="5">LOGIN: <b id="reloadlogin"><?php //echo (($login)?$login:'') ?></b></td>
            </tr>
            <tr>
                <td colspan="5">TIME IN: <b id="reloadtimein"></b></td>
            </tr>
            
            <?php
//            $initial_deposit = 0;
//            $total_reload = 0;
//            $total_redeem = 0;
//            $tbody = '';
//            if($trans_details) {
//                foreach($trans_details as $trans_detail) {
//                    $tbody.='<tr>';
//                    $tbody.= '<td>' . $trans_detail['TransType'] . '</td>';
//                    if($trans_detail['TransType'] == 'Deposit') {
//                        $initial_deposit = toMoney($trans_detail['Amount']);
//                        $tbody.= '<td class="amount">' . $initial_deposit . '</td>';
//                    }else if($trans_detail['TransType'] == 'Reload') {
//                        $total_reload += toInt($trans_detail['Amount']);
//                        $tbody.= '<td class="amount">' . toMoney($trans_detail['Amount']) . '</td>';
//                    }
//                    $tbody.= '<td>' . $trans_detail['DateCreated'] . '</td>';
//                    $tbody.='</tr>';
//                } 
//            }
            ?>
            
            <tr>
                <td colspan="5">INITIAL DEPOSIT: <b id="reloadinitialdeposit"><?php //echo (($trans_details)?$initial_deposit:''); ?></b></td>
            </tr>
            <tr>
                <td colspan="5">TOTAL RELOAD: <b id="reloadtotalreload"><?php //echo (($trans_details)?toMoney($total_reload):''); ?></b></td>
            </tr>
            <tr>
                <th colspan="5" style="background-color: #62AF35">
                    <i>SESSION DETAILS</i>
                </th>
            </tr>
            <tr>
                <th style="width: 70px;">Type</th><th style="width: 100px;">Amount</th><th>Time</th><!--<th>Terminal Type</th><th>Source</th>-->
            </tr>
            <tbody id="reloadtbody">
                <?php //echo (($trans_details)?$tbody:''); ?>
            </tbody>
        </table>
    </div>
</div>