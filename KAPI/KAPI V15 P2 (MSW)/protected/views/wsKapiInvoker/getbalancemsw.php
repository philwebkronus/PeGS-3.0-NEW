<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Get Balance MSW</h1>
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
