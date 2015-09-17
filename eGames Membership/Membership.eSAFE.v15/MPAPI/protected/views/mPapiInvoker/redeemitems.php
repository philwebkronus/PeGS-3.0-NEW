<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Redeem Items</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('MPSessionID','MPSessionID'); ?>
            <?php echo CHtml::textField('MPSessionID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('CardNumber','CardNumber'); ?>
            <?php echo CHtml::textField('CardNumber'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('RewardID','RewardID'); ?>
            <?php echo CHtml::textField('RewardID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('RewardItemID','RewardItemID'); ?>
            <?php echo CHtml::textField('RewardItemID'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Quantity','Quantity'); ?>
            <?php echo CHtml::textField('Quantity'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Source','Source'); ?>
            <?php echo CHtml::textField('Source'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Tracking1','Tracking1'); ?>
            <?php echo CHtml::textField('Tracking1'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Tracking2','Tracking2'); ?>
            <?php echo CHtml::textField('Tracking2'); ?>
        </div> 
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>

<div class="result" style="margin-left: 20px; word-wrap: break-word;">
    <?php if (!is_null($result)) : ?>
        <p><strong>JSON Result :</strong></p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
