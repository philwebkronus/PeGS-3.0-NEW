<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Reload Session</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
   
        <div class="row">
            <?php echo CHtml::label('TerminalName','tname'); ?>
            <?php echo CHtml::textField('terminalname'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Amount','amount'); ?>
            <?php echo CHtml::textField('amount'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('TrackingID','trackingid'); ?>
            <?php echo CHtml::textField('trackingid'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('StackerBatchID','stackerbatchid'); ?>
            <?php echo CHtml::textField('stackerbatchid'); ?>
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
