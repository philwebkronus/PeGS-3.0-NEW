<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Deposit MSW</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('MID:','MID'); ?>
            <?php echo CHtml::textField('MID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('ServiceID:','ServiceID'); ?>
            <?php echo CHtml::textField('ServiceID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Amount:','Amount'); ?>
            <?php echo CHtml::textField('Amount'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Method:','Method'); ?>
            <?php echo CHtml::textField('Method'); ?>
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
