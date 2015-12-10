<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Member Registration BT No Email</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('*FirstName','FirstName'); ?>
            <?php echo CHtml::textField('FirstName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('*LastName','LastName'); ?>
            <?php echo CHtml::textField('LastName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('*MobileNo','MobileNo'); ?>
            <?php echo CHtml::textField('MobileNo'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('*Birthdate','Birthdate'); ?>
            <?php echo CHtml::textField('Birthdate'); ?>
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
