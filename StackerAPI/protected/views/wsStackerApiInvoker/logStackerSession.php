<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Log Stacker Session</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('TerminalName','TerminalName'); ?>
            <?php echo CHtml::textField('TerminalName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('SerialNumber','SerialNumber'); ?>
            <?php echo CHtml::textField('SerialNumber'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Action','Action'); ?>
            <?php echo CHtml::textField('Action'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('CollectedBy','CollectedBy'); ?>
            <?php echo CHtml::textField('CollectedBy'); ?>
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
