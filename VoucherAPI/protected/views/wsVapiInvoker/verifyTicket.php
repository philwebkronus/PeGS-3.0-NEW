<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Verify Ticket</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('Tracking ID','trackingid'); ?>
            <?php echo CHtml::textField('TrackingID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Terminal Name','tname'); ?>
            <?php echo CHtml::textField('TerminalName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Voucher Ticket Barcode','vcode'); ?>
            <?php echo CHtml::textField('VoucherTicketBarcode'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Source','source'); ?>
            <?php echo CHtml::textField('Source'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('AID','aid'); ?>
            <?php echo CHtml::textField('AID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Card Number','mcardnumber'); ?>
            <?php echo CHtml::textField('MembershipCardNumber'); ?>
        </div>
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>


<div class="result" style="margin-left: 20px;">
    <?php if (!is_null($result)) : ?>
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>