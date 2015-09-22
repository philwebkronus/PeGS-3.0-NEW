<?php $this->pageTitle = "Remove Terminal Session"; ?>
<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Remove Terminal Session</h1>
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
            <?php echo CHtml::label('TerminalCode','TerminalCode'); ?>
            <?php echo CHtml::textField('TerminalCode'); ?>
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
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
