<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Remove EGM Session</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('CardNumber','memcardnum'); ?>
            <?php echo CHtml::textField('membershipcardnumber'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('TerminalName','tname'); ?>
            <?php echo CHtml::textField('terminalname'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('CasinoID','casinoID'); ?>
            <?php echo CHtml::textField('serviceid'); ?>
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