<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>TransferWallet</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    <div class="row">
        <?php echo CHtml::label('TerminalCode', 'TerminalCode'); ?>
        <?php echo CHtml::textField('TerminalCode'); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label('ServiceID', 'ServiceID'); ?>
        <?php echo CHtml::textField('ServiceID'); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label('Usermode', 'Usermode'); ?>
        <?php echo CHtml::textField('Usermode'); ?>
    </div>
    <div class="row" style="margin-left: 225px;">
        <?php echo CHtml::submitButton('Invoke'); ?>
    </div>

    <?php echo CHtml::endForm(); ?>
</div>

<div class="result" style="margin-left: 20px;word-wrap: break-word;">
    <?php if (!is_null($result)) : ?>
        <p><strong>JSON Result :</strong></p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
