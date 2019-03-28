<?php Mirage::loadLibraries('LoyaltyScripts'); ?>
<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('input.auto').autoNumeric();
        $('#StartSessionFormModel_amount').autoNumeric();
        $('#StartSessionFormModel_voucher_code').hide();
        $('.bankContainer').hide();
    })
</script>
<div id="tm-reload-form">
    <div>
        <?php if ($startSessionFormModel->error_count): ?>
            <?php echo $startSessionFormModel->getErrorMessages(); ?>
        <?php endif; ?>        
        <form id="frmreload">
            <?php echo MI_HTML::inputHidden($startSessionFormModel, 'min_deposit') ?>
            <?php echo MI_HTML::inputHidden($startSessionFormModel, 'max_deposit') ?>
            <input type="hidden" id="tcode" value="<?php echo $tcode ?>" name="tcode" />
            <input type="hidden" id="tid" value="<?php echo $tid ?>" name="tid" />
            <input type="hidden" value="<?php echo $is_vip ?>" name="is_vip" />
            <input type="hidden" value="<?php echo $cid ?>" name="cid" />
            <input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
            <input type="hidden" name="mode" id="mode" value="<?php echo $casinoUserMode; ?>"/>
            <input type="hidden" name="eBingoDivisibleBy" id="eBingoDivisibleBy" value="<?php echo $eBingoDivisibleBy; ?>"/>
            <input type="hidden" name="eBingoMaxDeposit" id="eBingoMaxDeposit" value="<?php echo $eBingoMaxDeposit; ?>"/>
            <input type="hidden" name="eBingoMinDeposit" id="eBingoMinDeposit"  value="<?php echo $eBingoMinDeposit; ?>"/>
            <table>
                <tr>
                    <th style="width: 300px;" class="left">CURRENT PLAYING BALANCE:</td>
                    <td><?php echo toMoney($terminal_balance); ?></td>    
                </tr>
                <tr>
                    <th class="left">CURRENT CASINO:</th>
                    <td><?php echo $casino; ?></td>
                </tr>
                <tr>
                    <th class="left"><?php echo MI_HTML::label($startSessionFormModel, 'amount', 'AMOUNT:') ?></th>
                    <td>
                        <?php
                        if ($casinoUserMode == 4) {
                            echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', $eBingoDenomination, array('' => 'Select Amount'), array('--' => 'Other denomination', 'voucher' => 'Voucher', 'bancnet' => 'Bancnet'), array('class' => 'width154'));
                        } else {
                            echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', $denomination, array('' => 'Select Amount'), array('--' => 'Other denomination', 'voucher' => 'Voucher', 'bancnet' => 'Bancnet'), array('class' => 'width154'));
                        }
                        ?>

                        <?php echo MI_HTML::inputText($startSessionFormModel, 'amount', array('readonly' => 'readonly', 'class' => 'auto', 'maxlength' => 8, 'class' => 'width150')); ?>
                        <?php echo MI_HTML::inputText($startSessionFormModel, 'voucher_code', array('maxlength' => 20, 'class' => 'width150')); ?>
                    </td>
                </tr>

                <tr class="bankContainer">
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'lbl_traceNumber', 'TRACE NUMBER:') ?></th>
                    <td><?php echo MI_HTML::inputText($startSessionFormModel, 'trace_number', array('class' => 'width150', 'maxlength' => 20)); ?> <td>
                </tr>

                <tr class="bankContainer">
                    <th><?php echo MI_HTML::label($startSessionFormModel, 'lbl_refNumber', 'REFERENCE NUMBER:') ?></th>
                    <td><?php echo MI_HTML::inputText($startSessionFormModel, 'reference_number', array('class' => 'width150', 'maxlength' => 20)); ?><td>

                </tr>
                <tr>
                    <th><input id="btnReload" type="submit" value="Submit" /></th>
                    <td><input class="btnClose" type="button" value="Cancel" /></td>
                </tr>            
            </table>
        </form>    
    </div>
    <div class="details">
        <table border="1" style="width: 100%">
            <tr>
                <td colspan="5">LOGIN: <b><?php echo $tcode; ?></b></td>
            </tr>
            <tr>
                <td colspan="5">TIME IN: <b><?php echo date('Y-m-d h:i:s A', strtotime($terminal_session_data['DateStarted'])); ?></b></td>
            </tr>

<?php
$initial_deposit = 0;
$total_reload = 0;
$total_redeem = 0;
$tbody = '';
foreach ($trans_details as $trans_detail) {
    $tbody.='<tr>';
    $tbody.= '<td>' . $trans_detail['TransType'] . '</td>';
    if ($trans_detail['TransType'] == 'Deposit') {
        $initial_deposit = toMoney($trans_detail['Amount']);
        $tbody.= '<td class="amount">' . $initial_deposit . '</td>';
    } else if ($trans_detail['TransType'] == 'Reload') {
        $total_reload += toInt($trans_detail['Amount']);
        $tbody.= '<td class="amount">' . toMoney($trans_detail['Amount']) . '</td>';
    }
    $tbody.= '<td>' . $trans_detail['DateCreated'] . '</td>';
//                $tbody.= '<td>' . $trans_detail['TerminalType'] . '</td>';
//                $tbody.= '<td>' . $trans_detail['Name'] . '</td>';
    $tbody.='</tr>';
}
?>

            <tr>
                <td colspan="5">INITIAL DEPOSIT: <b><?php echo 'PhP ' . $initial_deposit; ?></b></td>
            </tr>
            <tr>
                <td colspan="5">TOTAL RELOAD: <b><?php echo 'PhP ' . toMoney($total_reload); ?></b></td>
            </tr>
            <tr>
                <th colspan="5" style="background-color: #62AF35">
                    <i>SESSION DETAILS</i>
                </th>
            </tr>
            <tr>
                <th style="width: 70px;">Type</th><th style="width: 100px;">Amount</th><th>Time</th><!--<th>Terminal Type</th><th>Source</th>-->
            </tr>
            <tbody>
<?php echo $tbody; ?>
            </tbody>
        </table>
    </div>
</div>

