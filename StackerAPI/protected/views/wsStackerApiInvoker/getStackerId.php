<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Get Stacker ID</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('TerminalName','TerminalName'); ?>
            <?php echo CHtml::textField('TerminalName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('MembershipCardNumber','MembershipCardNumber'); ?>
            <?php echo CHtml::textField('MembershipCardNumber'); ?>
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
