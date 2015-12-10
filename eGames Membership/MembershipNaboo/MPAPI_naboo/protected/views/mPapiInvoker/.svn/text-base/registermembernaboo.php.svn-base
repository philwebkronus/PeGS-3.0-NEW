<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Member Registration (Naboo)</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('MobileNo','MobileNo'); ?>
            <?php echo CHtml::textField('MobileNo'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('PlayerName','PlayerName'); ?>
            <?php echo CHtml::textField('PlayerName'); ?>
        </div>
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>

<div class="result" style="margin-left: 20px;">
    <?php if (!is_null($result)) : ?>
        <p><strong>JSON Result :</strong></p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
