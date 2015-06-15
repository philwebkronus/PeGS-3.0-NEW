<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Get Balance</h1>
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
