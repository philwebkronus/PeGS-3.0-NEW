<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Check Transaction</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('Terminal Name','tname'); ?>
            <?php echo CHtml::textField('terminalname'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Tracking ID','trackingid'); ?>
            <?php echo CHtml::textField('trackingid'); ?>
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