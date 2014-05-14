<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Update Stacker Info</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('StackerTagID','StackerTagID'); ?>
            <?php echo CHtml::textField('StackerTagID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('SerialNumber','SerialNumber'); ?>
            <?php echo CHtml::textField('SerialNumber'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Status','Status'); ?>
            <?php echo CHtml::textField('Status'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('TerminalName','TerminalName'); ?>
            <?php echo CHtml::textField('TerminalName'); ?>
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
