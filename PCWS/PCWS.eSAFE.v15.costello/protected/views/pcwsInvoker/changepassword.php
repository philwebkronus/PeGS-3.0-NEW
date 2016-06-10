<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Change Password</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('System Username','lblSystemUsername'); ?>
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
            <?php echo CHtml::label('Usermode','lblUsermode'); ?>
            <?php echo CHtml::textField('Usermode'); ?>
        </div>       
        <div class="row">
            <?php echo CHtml::label('Login','lbllogin'); ?>
            <?php echo CHtml::textField('Login'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('ServiceID','lblServiceID'); ?>
            <?php echo CHtml::textField('ServiceID'); ?>
        </div>
        
        <div class="row">
            <?php echo CHtml::label('Source','lblPSource'); ?>
            <?php echo CHtml::textField('Source'); ?>
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
