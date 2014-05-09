<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Do Transaction</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('Token','token'); ?>
            <?php echo CHtml::textField('token'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Transaction Type','transtype'); ?>
            <?php echo CHtml::textField('transtype'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Transaction Method','transmethod'); ?>
            <?php echo CHtml::textField('transmethod'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Service ID','serviceid'); ?>
            <?php echo CHtml::textField('serviceid'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Amount','amount'); ?>
            <?php echo CHtml::textField('amount'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Loyalty Barcode','barcode'); ?>
            <?php echo CHtml::textField('barcode'); ?>
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