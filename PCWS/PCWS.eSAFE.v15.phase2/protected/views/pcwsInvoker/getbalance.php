<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Get Balance</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('SystemUsername','lblUserName'); ?>
            <?php echo CHtml::textField('SystemUsername'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('AccessDate','AccessDate'); ?>
            <?php echo CHtml::textField('AccessDate'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Token','Token'); ?>
            <?php echo CHtml::textField('Token'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Card Number','lblpin'); ?>
            <?php echo CHtml::textField('CardNumber'); ?>
        </div>
    
        <!-- Changes here-->
      <div class="row">
            <?php echo CHtml::label('ServiceID','lblServiceID'); ?>
            <?php echo CHtml::textField('ServiceID'); ?>
        </div>
     <!-- Changes here-->
    
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
