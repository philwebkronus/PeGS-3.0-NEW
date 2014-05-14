<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Log Stacker Transaction</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <table>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('TrackingID','TrackingID'); ?></td>
                    <td><?php echo CHtml::textField('TrackingID'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('TerminalName','TerminalName'); ?></td>
                    <td><?php echo CHtml::textField('TerminalName'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('TransType','TransType'); ?></td>
                    <td><?php echo CHtml::textField('TransType'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('Amount','Amount'); ?></td>
                    <td><?php echo CHtml::textField('Amount'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('CashType','CashType'); ?></td>
                    <td><?php echo CHtml::textField('CashType'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('VoucherTicketBarcode','VoucherTicketBarcode'); ?></td>
                    <td><?php echo CHtml::textField('VoucherTicketBarcode'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('Source','Source'); ?></td>
                    <td><?php echo CHtml::textField('Source'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('StackerBatchID','StackerBatchID'); ?></td>
                    <td><?php echo CHtml::textField('StackerBatchID'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"><?php echo CHtml::label('MembershipCardNumber','MembershipCardNumber'); ?></td>
                    <td><?php echo CHtml::textField('MembershipCardNumber'); ?></td>
                </tr>
                <tr>
                    <td style = "width: 17%;"></td>
                    <td><?php echo CHtml::submitButton('Invoke'); ?></td>
                </tr>
            </table>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>

<div class="result" style="margin-left: 20px;">
    <?php if (!is_null($result)) : ?>
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
