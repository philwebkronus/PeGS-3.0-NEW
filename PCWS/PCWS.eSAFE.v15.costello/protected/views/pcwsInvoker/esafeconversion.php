<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>e-SAFE Conversion</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('SystemUsername','SystemUsername'); ?>
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
            <?php echo CHtml::label('Card Number','CardNumber'); ?>
            <?php echo CHtml::textField('CardNumber'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Password','Password'); ?>
            <?php echo CHtml::textField('Password'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('PIN','lblCasinoCode'); ?>
            <?php echo CHtml::textField('PIN'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('ConfirmPIN','ConfirmPIN'); ?>
            <?php echo CHtml::textField('ConfirmPIN'); ?>
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
