<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Start Session</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('SystemUsername','lblUserName'); ?>
            <?php echo CHtml::textField('SystemUsername'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('CardNumber','lblcardnumber'); ?>
            <?php echo CHtml::textField('cardnumber'); ?>
        </div>
        
        <div class="row">
            <?php echo CHtml::label('TerminalName','lblterminalname'); ?>
            <?php echo CHtml::textField('terminalname'); ?>
        </div>
    
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>


<div class="result" style="margin-left: 20px; word-wrap: break-word;">
    <?php if (!is_null($result)) : ?>
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
